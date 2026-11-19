$ErrorActionPreference = 'Stop'
$base = 'http://localhost/Webtintuc'
$failures = @()

foreach ($path in @('/README.md', '/CHANGELOG.md', '/DESIGN.md', '/TEST_REPORT.md', '/PROJECT_COMPLETION_REPORT.md', '/QA_REMEDIATION_REPORT.md', '/docs/architecture/PROJECT_ARCHITECTURE.md', '/docs/local-only/ADMIN_ACCESS.md', '/.impeccable/design.json', '/.gitignore', '/database/seed.sql', '/config/database.php', '/app/Core/Database.php')) {
    $status = & curl.exe --silent --show-error --max-time 10 --output NUL --write-out '%{http_code}' ($base + $path)
    if ($LASTEXITCODE -ne 0) { throw "curl failed for $path ($LASTEXITCODE)" }
    if ([int]$status -ne 403) { $failures += "Expected 403, got $status for $path" }
}

foreach ($path in @('/', '/index.php', '/public/css/style.css', '/public/css/admin.css', '/public/js/main.js', '/public/uploads/articles/seed_world.jpg')) {
    $status = & curl.exe --silent --show-error --max-time 10 --output NUL --write-out '%{http_code}' ($base + $path)
    if ($LASTEXITCODE -ne 0) { throw "curl failed for $path ($LASTEXITCODE)" }
    if ([int]$status -ne 200) { $failures += "Expected 200, got $status for $path" }
}

if ($failures.Count -gt 0) {
    $failures | ForEach-Object { Write-Output "FAIL: $_" }
    exit 1
}

Write-Output 'PASS: root metadata and protected source paths return 403; public app assets remain available.'
