$ErrorActionPreference = 'Stop'
$base = 'http://localhost/Webtintuc'
$adminEmail = $env:NEWS_HUB_TEST_ADMIN_EMAIL
$adminPassword = $env:NEWS_HUB_TEST_ADMIN_PASSWORD
$readerEmail = $env:NEWS_HUB_TEST_READER_EMAIL
$readerPassword = $env:NEWS_HUB_TEST_READER_PASSWORD
if ([string]::IsNullOrWhiteSpace($adminEmail) -or [string]::IsNullOrWhiteSpace($adminPassword) -or
    [string]::IsNullOrWhiteSpace($readerEmail) -or [string]::IsNullOrWhiteSpace($readerPassword)) {
    throw 'Set NEWS_HUB_TEST_ADMIN_EMAIL/PASSWORD and NEWS_HUB_TEST_READER_EMAIL/PASSWORD in the local test shell.'
}
$tempRoot = [System.IO.Path]::GetFullPath([System.IO.Path]::GetTempPath()).TrimEnd('\') + '\'
$scratch = Join-Path $tempRoot ('news-hub-http-test-' + [guid]::NewGuid().ToString('N'))
$resolvedScratch = [System.IO.Path]::GetFullPath($scratch)
$scratchLeaf = Split-Path -Leaf $resolvedScratch
if (-not $resolvedScratch.StartsWith($tempRoot, [System.StringComparison]::OrdinalIgnoreCase) -or
    $scratchLeaf -notmatch '^news-hub-http-test-[a-f0-9]{32}$' -or
    (Test-Path -LiteralPath $resolvedScratch)) {
    throw 'Unsafe or pre-existing HTTP test scratch directory.'
}
New-Item -ItemType Directory -Force -Path $scratch | Out-Null
try {
$adminJar = Join-Path $scratch 'admin.cookies'
$readerJar = Join-Path $scratch 'reader.cookies'
$registerJar = Join-Path $scratch 'register.cookies'
$loginHtml = Join-Path $scratch 'login.html'

function Get-Status([string]$url, [string]$method = 'GET', [string]$jar = '', [string[]]$body = @()) {
    $args = @('--silent','--show-error','--max-time','15','--output','NUL','--write-out','%{http_code}','--request',$method)
    if ($jar) { $args += @('--cookie',$jar,'--cookie-jar',$jar) }
    foreach ($item in $body) { $args += @('--data-urlencode',$item) }
    $args += $url
    $status = & curl.exe @args
    if ($LASTEXITCODE -ne 0) { throw "curl failed for $url ($LASTEXITCODE)" }
    return [int]$status
}

function Assert-Status([string]$url, [int]$expected, [string]$method = 'GET', [string]$jar = '', [string[]]$body = @()) {
    $actual = Get-Status $url $method $jar $body
    if ($actual -ne $expected) { throw "Expected $expected, got $actual for $method $url" }
    "PASS $actual $method $url"
}

$ids = & 'D:\xampp\mysql\bin\mysql.exe' --silent --skip-column-names --host=127.0.0.1 --port=3306 --user=root news_hub --execute="SELECT (SELECT id FROM articles WHERE status='published' ORDER BY id LIMIT 1),(SELECT id FROM articles WHERE status='draft' ORDER BY id LIMIT 1)"
if ($LASTEXITCODE -ne 0) { throw 'Could not read integration IDs.' }
$parts = ($ids -split "`t")
$publishedId = [int]$parts[0]
$draftId = [int]$parts[1]
$testCommentId = [int](& 'D:\xampp\mysql\bin\mysql.exe' --silent --skip-column-names --host=127.0.0.1 --port=3306 --user=root news_hub --execute='SELECT COALESCE(MAX(id),0)+1 FROM comments')
if ($LASTEXITCODE -ne 0 -or $testCommentId -lt 1 -or $testCommentId -gt 4294967295) { throw 'Could not reserve an unused comment ID.' }

Assert-Status "$base/" 200
Assert-Status "$base/index.php" 200
Assert-Status "$base/HomeController/index/2" 200
Assert-Status "$base/AccountController/index" 302
Assert-Status "$base/FrontendArticleController/show/$publishedId" 200
Assert-Status "$base/FrontendArticleController/show/$draftId" 404
Assert-Status "$base/FrontendArticleController/show/0" 404
Assert-Status "$base/PublicCategoryController/show/cong-nghe" 200
Assert-Status "$base/PublicCategoryController/show/cong-nghe/2" 200
Assert-Status "$base/PublicCategoryController/show/missing-category" 404
Assert-Status "$base/SearchController/index?q=sample" 200
Assert-Status "$base/SearchController/index/2?q=sample" 200
Assert-Status "$base/SearchController/index?q=%27%20OR%201%3D1%20--" 200
Assert-Status "$base/UnknownController/index" 404
Assert-Status "$base/ArticleController/noSuchAction" 404
Assert-Status "$base/ArticleController/index/nope" 404
Assert-Status "$base/ArticleController/index/1/extra" 404
Assert-Status "$base/ArticleController/index" 403
Assert-Status "$base/ArticleController/delete/$publishedId" 405
Assert-Status "$base/ArticleController/store" 403 'POST' '' @('csrf_token=')
Assert-Status "$base/AuthController/logout" 405

$adminEntryHtml = Join-Path $scratch 'admin-entry.html'
$null = & curl.exe --silent --show-error --max-time 15 --output $adminEntryHtml "$base/AuthController/login?area=admin"
$adminEntryMarkup = [System.IO.File]::ReadAllText($adminEntryHtml, [System.Text.Encoding]::UTF8)
if ($LASTEXITCODE -ne 0 -or -not $adminEntryMarkup.Contains('auth-context') -or -not $adminEntryMarkup.Contains('?area=admin')) {
    throw 'Admin entry page did not render its role-specific sign-in context.'
}
"PASS 200 GET $base/AuthController/login?area=admin"

$null = & curl.exe --silent --show-error --max-time 15 --cookie-jar $adminJar --output $loginHtml "$base/AuthController/login"
if ($LASTEXITCODE -ne 0) { throw 'Login form could not load.' }
$html = Get-Content $loginHtml -Raw
$tokenMatch = [regex]::Match($html, 'name="csrf_token" value="([a-f0-9]{64})"')
if (-not $tokenMatch.Success) { throw 'Login form has no CSRF token.' }
$token = $tokenMatch.Groups[1].Value
Assert-Status "$base/AuthController/authenticate" 403 'POST' $adminJar @('csrf_token=' + ('a' * 64),"email=$adminEmail","password=$adminPassword")
Assert-Status "$base/AuthController/authenticate" 302 'POST' $adminJar @("csrf_token=$token","email=$adminEmail","password=$adminPassword")
Assert-Status "$base/DashboardController/index" 200 'GET' $adminJar
Assert-Status "$base/ArticleController/index" 200 'GET' $adminJar
Assert-Status "$base/ArticleController/index/2" 200 'GET' $adminJar
Assert-Status "$base/CategoryController/index" 200 'GET' $adminJar
Assert-Status "$base/CategoryController/index/2" 200 'GET' $adminJar
Assert-Status "$base/UserController/index?q=reader" 200 'GET' $adminJar
$articleAdminHtml = Join-Path $scratch 'articles.html'
$null = & curl.exe --silent --show-error --cookie $adminJar --output $articleAdminHtml "$base/ArticleController/index"
if ($LASTEXITCODE -ne 0) { throw 'Admin article form could not load.' }
$articleAdminMarkup = [System.IO.File]::ReadAllText($articleAdminHtml, [System.Text.Encoding]::UTF8)
if (-not $articleAdminMarkup.Contains('id="editorFallback"') -or -not $articleAdminMarkup.Contains('name="content"') -or -not $articleAdminMarkup.Contains('window.tinymce')) {
    throw 'Admin article editor is missing the native textarea or its TinyMCE fallback.'
}
'PASS Admin article form retains native content field and TinyMCE fallback'
$dashboardHtml = Join-Path $scratch 'dashboard.html'
$null = & curl.exe --silent --show-error --cookie $adminJar --output $dashboardHtml "$base/DashboardController/index"
if ($LASTEXITCODE -ne 0) { throw 'Admin dashboard could not load.' }
$dashboardMarkup = [System.IO.File]::ReadAllText($dashboardHtml, [System.Text.Encoding]::UTF8)
foreach ($requiredText in @('admin-sidebar', 'aria-label="Breadcrumb"', 'CommentController/index?status=pending', 'ArticleController/index#articleForm')) {
    if (-not $dashboardMarkup.Contains($requiredText)) { throw "Admin dashboard is missing required navigation content: $requiredText" }
}
"PASS Admin shell, breadcrumb, and dashboard shortcuts"
$adminHtml = Join-Path $scratch 'admin.html'
$null = & curl.exe --silent --show-error --cookie $adminJar --cookie-jar $adminJar --output $adminHtml "$base/CommentController/index"
if ($LASTEXITCODE -ne 0) { throw 'Admin comments page could not load.' }
$adminTokenMatch = [regex]::Match((Get-Content $adminHtml -Raw), 'name="csrf_token" value="([a-f0-9]{64})"')
if (-not $adminTokenMatch.Success) { throw 'Admin form has no CSRF token.' }
$validStatus = & curl.exe --silent --show-error --max-time 15 --cookie $adminJar --cookie-jar $adminJar --output NUL --write-out '%{http_code}' --data-urlencode ('csrf_token=' + $adminTokenMatch.Groups[1].Value) --data-urlencode 'status=pending' "$base/CommentController/updateStatus/$testCommentId"
if ($LASTEXITCODE -ne 0 -or [int]$validStatus -ne 302) { throw "Expected 302 with valid CSRF, got $validStatus." }
"PASS 302 POST $base/CommentController/updateStatus/$testCommentId (valid CSRF; reserved nonexistent ID)"
Assert-Status "$base/CommentController/updateStatus/$testCommentId" 403 'POST' $adminJar @('status=pending')
Assert-Status "$base/CommentController/updateStatus/$testCommentId" 403 'POST' $adminJar @('csrf_token=' + ('b' * 64),'status=pending')
$commentExistsAfter = [int](& 'D:\xampp\mysql\bin\mysql.exe' --silent --skip-column-names --host=127.0.0.1 --port=3306 --user=root news_hub --execute="SELECT COUNT(*) FROM comments WHERE id=$testCommentId")
if ($LASTEXITCODE -ne 0 -or $commentExistsAfter -ne 0) { throw 'The HTTP smoke test changed comment rows.' }

$null = & curl.exe --silent --show-error --cookie-jar $readerJar --output $loginHtml "$base/AuthController/login"
$readerTokenMatch = [regex]::Match((Get-Content $loginHtml -Raw), 'name="csrf_token" value="([a-f0-9]{64})"')
if (-not $readerTokenMatch.Success) { throw 'Reader login form has no CSRF token.' }
$readerLogin = & curl.exe --silent --show-error --max-time 15 --cookie $readerJar --cookie-jar $readerJar --output NUL --write-out '%{http_code}' --data-urlencode ('csrf_token=' + $readerTokenMatch.Groups[1].Value) --data-urlencode ('email=' + $readerEmail) --data-urlencode ('password=' + $readerPassword) "$base/AuthController/authenticate"
if ($LASTEXITCODE -ne 0 -or [int]$readerLogin -ne 302) { throw "Expected reader login 302, got $readerLogin." }
"PASS 302 POST $base/AuthController/authenticate (reader login)"
Assert-Status "$base/AccountController/index" 200 'GET' $readerJar
$accountHtml = Join-Path $scratch 'account.html'
$null = & curl.exe --silent --show-error --cookie $readerJar --output $accountHtml "$base/AccountController/index"
if ($LASTEXITCODE -ne 0) { throw 'Reader account page could not load.' }
$accountMarkup = [System.IO.File]::ReadAllText($accountHtml, [System.Text.Encoding]::UTF8)
if (-not $accountMarkup.Contains('account-profile') -or -not $accountMarkup.Contains('account-comments') -or $accountMarkup.Contains('password_hash')) {
    throw 'Reader account page is missing its profile/comment state or exposed a password hash.'
}
'PASS reader account renders profile and private comment area without password hash'
Assert-Status "$base/DashboardController/index" 403 'GET' $readerJar

$registerHtml = Join-Path $scratch 'register.html'
$null = & curl.exe --silent --show-error --max-time 15 --cookie-jar $registerJar --output $registerHtml "$base/AuthController/register"
if ($LASTEXITCODE -ne 0) { throw 'Registration form could not load.' }
$registerTokenMatch = [regex]::Match([System.IO.File]::ReadAllText($registerHtml, [System.Text.Encoding]::UTF8), 'name="csrf_token" value="([a-f0-9]{64})"')
if (-not $registerTokenMatch.Success) { throw 'Registration form has no CSRF token.' }
$invalidRegistration = & curl.exe --silent --show-error --max-time 15 --cookie $registerJar --cookie-jar $registerJar --output NUL --write-out '%{http_code}' --data-urlencode ('csrf_token=' + $registerTokenMatch.Groups[1].Value) --data-urlencode 'full_name=Smoke Test' --data-urlencode ('email=' + $adminEmail) --data-urlencode ('password=' + $adminPassword) --data-urlencode ('password_confirm=' + $adminPassword + 'mismatch') "$base/AuthController/createAccount"
if ($LASTEXITCODE -ne 0 -or [int]$invalidRegistration -ne 422) { throw "Expected invalid registration 422, got $invalidRegistration." }
'PASS 422 POST registration rejects mismatched password confirmation without creating an account'
$duplicateRegistration = & curl.exe --silent --show-error --max-time 15 --cookie $registerJar --cookie-jar $registerJar --output NUL --write-out '%{http_code}' --data-urlencode ('csrf_token=' + $registerTokenMatch.Groups[1].Value) --data-urlencode 'full_name=Smoke Test' --data-urlencode ('email=' + $adminEmail) --data-urlencode ('password=' + $adminPassword) --data-urlencode ('password_confirm=' + $adminPassword) "$base/AuthController/createAccount"
if ($LASTEXITCODE -ne 0 -or [int]$duplicateRegistration -ne 422) { throw "Expected duplicate email registration 422, got $duplicateRegistration." }
'PASS 422 POST registration rejects an existing email'

$dashboardTokenMatch = [regex]::Match($dashboardMarkup, 'name="csrf_token" value="([a-f0-9]{64})"')
if (-not $dashboardTokenMatch.Success) { throw 'Admin shell has no logout CSRF token.' }
Assert-Status "$base/AuthController/logout" 302 'POST' $adminJar @('csrf_token=' + $dashboardTokenMatch.Groups[1].Value)
Assert-Status "$base/DashboardController/index" 403 'GET' $adminJar

} finally {
    $resolvedScratch = [System.IO.Path]::GetFullPath($scratch)
    $scratchLeaf = Split-Path -Leaf $resolvedScratch
    if (-not $resolvedScratch.StartsWith($tempRoot, [System.StringComparison]::OrdinalIgnoreCase) -or
        $scratchLeaf -notmatch '^news-hub-http-test-[a-f0-9]{32}$') {
        throw 'Refusing to remove an unverified HTTP test scratch path.'
    }
    if (Test-Path -LiteralPath $resolvedScratch) {
        Remove-Item -LiteralPath $resolvedScratch -Recurse -Force
    }
}
