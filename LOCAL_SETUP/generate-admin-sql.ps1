$ErrorActionPreference = 'Stop'

$php = 'D:\xampp\php\php.exe'
if (-not (Test-Path $php)) {
    $php = Read-Host 'Enter the full path to php.exe (for example D:\xampp\php\php.exe)'
}
if (-not (Test-Path $php)) { throw 'php.exe was not found.' }

$email = Read-Host 'Admin email'
$name = Read-Host 'Admin display name (for example Nhom 4)'
$secure = Read-Host 'New production Admin password (input is hidden)' -AsSecureString
$bstr = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secure)
try {
    $plain = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($bstr)
    $env:NEWS_HUB_ADMIN_EMAIL = $email
    $env:NEWS_HUB_ADMIN_NAME = $name
    $env:NEWS_HUB_ADMIN_PASSWORD = $plain
    & $php (Join-Path $PSScriptRoot 'generate-admin-sql.php')
    if ($LASTEXITCODE -ne 0) { throw 'Admin SQL generation failed.' }
}
finally {
    if ($bstr -ne [IntPtr]::Zero) { [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($bstr) }
    Remove-Item Env:NEWS_HUB_ADMIN_EMAIL -ErrorAction SilentlyContinue
    Remove-Item Env:NEWS_HUB_ADMIN_NAME -ErrorAction SilentlyContinue
    Remove-Item Env:NEWS_HUB_ADMIN_PASSWORD -ErrorAction SilentlyContinue
    $plain = $null
    $secure = $null
}
Write-Host 'Import LOCAL_SETUP\admin-bootstrap.generated.sql through InfinityFree phpMyAdmin, then delete that generated file.' -ForegroundColor Green
