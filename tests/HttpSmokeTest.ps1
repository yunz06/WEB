$ErrorActionPreference = 'Stop'
$base = 'http://localhost/Webtintuc'
$scratch = 'D:\python\news-hub-http-test'
New-Item -ItemType Directory -Force -Path $scratch | Out-Null
$adminJar = Join-Path $scratch 'admin.cookies'
$readerJar = Join-Path $scratch 'reader.cookies'
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

$ids = & 'D:\xampp\mysql\bin\mysql.exe' --silent --skip-column-names --host=127.0.0.1 --port=3306 --user=root news_hub --execute="SELECT (SELECT id FROM articles WHERE status='published' ORDER BY id LIMIT 1),(SELECT id FROM articles WHERE status='draft' ORDER BY id LIMIT 1),(SELECT id FROM comments WHERE status='pending' ORDER BY id LIMIT 1)"
if ($LASTEXITCODE -ne 0) { throw 'Could not read integration IDs.' }
$parts = ($ids -split "`t")
$publishedId = [int]$parts[0]
$draftId = [int]$parts[1]
$pendingCommentId = [int]$parts[2]

Assert-Status "$base/" 200
Assert-Status "$base/index.php" 200
Assert-Status "$base/FrontendArticleController/show/$publishedId" 200
Assert-Status "$base/FrontendArticleController/show/$draftId" 404
Assert-Status "$base/FrontendArticleController/show/0" 404
Assert-Status "$base/PublicCategoryController/show/cong-nghe" 200
Assert-Status "$base/PublicCategoryController/show/missing-category" 404
Assert-Status "$base/SearchController/index?q=sample" 200
Assert-Status "$base/SearchController/index?q=%27%20OR%201%3D1%20--" 200
Assert-Status "$base/UnknownController/index" 404
Assert-Status "$base/ArticleController/noSuchAction" 404
Assert-Status "$base/ArticleController/index/nope" 404
Assert-Status "$base/ArticleController/index/1/extra" 404
Assert-Status "$base/ArticleController/index" 403
Assert-Status "$base/ArticleController/delete/$publishedId" 405
Assert-Status "$base/ArticleController/store" 403 'POST' '' @('csrf_token=')

$null = & curl.exe --silent --show-error --max-time 15 --cookie-jar $adminJar --output $loginHtml "$base/AuthController/login"
if ($LASTEXITCODE -ne 0) { throw 'Login form could not load.' }
$html = Get-Content $loginHtml -Raw
$tokenMatch = [regex]::Match($html, 'name="csrf_token" value="([a-f0-9]{64})"')
if (-not $tokenMatch.Success) { throw 'Login form has no CSRF token.' }
$token = $tokenMatch.Groups[1].Value
Assert-Status "$base/AuthController/authenticate" 403 'POST' $adminJar @('csrf_token=' + ('a' * 64),'email=admin@newshub.local','password=NewsHubDemo!2026')
Assert-Status "$base/AuthController/authenticate" 302 'POST' $adminJar @("csrf_token=$token",'email=admin@newshub.local','password=NewsHubDemo!2026')
Assert-Status "$base/DashboardController/index" 200 'GET' $adminJar
Assert-Status "$base/ArticleController/index" 200 'GET' $adminJar
Assert-Status "$base/CategoryController/index" 200 'GET' $adminJar
Assert-Status "$base/UserController/index?q=reader" 200 'GET' $adminJar
$statusBefore = (& 'D:\xampp\mysql\bin\mysql.exe' --silent --skip-column-names --host=127.0.0.1 --port=3306 --user=root news_hub --execute="SELECT status FROM comments WHERE id=$pendingCommentId") -join ''
$adminHtml = Join-Path $scratch 'admin.html'
$null = & curl.exe --silent --show-error --cookie $adminJar --cookie-jar $adminJar --output $adminHtml "$base/CommentController/index"
if ($LASTEXITCODE -ne 0) { throw 'Admin comments page could not load.' }
$adminTokenMatch = [regex]::Match((Get-Content $adminHtml -Raw), 'name="csrf_token" value="([a-f0-9]{64})"')
if (-not $adminTokenMatch.Success) { throw 'Admin form has no CSRF token.' }
$validStatus = & curl.exe --silent --show-error --max-time 15 --cookie $adminJar --cookie-jar $adminJar --output NUL --write-out '%{http_code}' --data-urlencode ('csrf_token=' + $adminTokenMatch.Groups[1].Value) --data-urlencode 'status=pending' "$base/CommentController/updateStatus/$pendingCommentId"
if ($LASTEXITCODE -ne 0 -or [int]$validStatus -ne 302) { throw "Expected 302 with valid CSRF, got $validStatus." }
"PASS 302 POST $base/CommentController/updateStatus/$pendingCommentId (valid CSRF)"
Assert-Status "$base/CommentController/updateStatus/$pendingCommentId" 403 'POST' $adminJar @('status=pending')
Assert-Status "$base/CommentController/updateStatus/$pendingCommentId" 403 'POST' $adminJar @('csrf_token=' + ('b' * 64),'status=pending')
$statusAfter = (& 'D:\xampp\mysql\bin\mysql.exe' --silent --skip-column-names --host=127.0.0.1 --port=3306 --user=root news_hub --execute="SELECT status FROM comments WHERE id=$pendingCommentId") -join ''
if ($statusBefore -ne $statusAfter -or $statusAfter -ne 'pending') { throw 'CSRF-valid same-status update changed comment state.' }

$null = & curl.exe --silent --show-error --cookie-jar $readerJar --output $loginHtml "$base/AuthController/login"
$readerTokenMatch = [regex]::Match((Get-Content $loginHtml -Raw), 'name="csrf_token" value="([a-f0-9]{64})"')
if (-not $readerTokenMatch.Success) { throw 'Reader login form has no CSRF token.' }
$readerLogin = & curl.exe --silent --show-error --max-time 15 --cookie $readerJar --cookie-jar $readerJar --output NUL --write-out '%{http_code}' --data-urlencode ('csrf_token=' + $readerTokenMatch.Groups[1].Value) --data-urlencode 'email=reader@newshub.local' --data-urlencode 'password=NewsHubReader!2026' "$base/AuthController/authenticate"
if ($LASTEXITCODE -ne 0 -or [int]$readerLogin -ne 302) { throw "Expected reader login 302, got $readerLogin." }
"PASS 302 POST $base/AuthController/authenticate (reader login)"
Assert-Status "$base/DashboardController/index" 403 'GET' $readerJar

Remove-Item -LiteralPath $scratch -Recurse -Force
