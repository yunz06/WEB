$ErrorActionPreference = 'Stop'

function Get-Utf8Text([string]$Base64) {
    return [System.Text.Encoding]::UTF8.GetString([System.Convert]::FromBase64String($Base64))
}

function Replace-Once([string]$Source, [string]$Search, [string]$Replacement, [string]$Description) {
    $first = $Source.IndexOf($Search, [System.StringComparison]::Ordinal)
    if ($first -lt 0) {
        throw "Expected text was not found: $Description. No files were written."
    }
    $last = $Source.LastIndexOf($Search, [System.StringComparison]::Ordinal)
    if ($first -ne $last) {
        throw "Expected text occurs more than once: $Description. No files were written."
    }
    return $Source.Substring(0, $first) + $Replacement + $Source.Substring($first + $Search.Length)
}

$root = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$frontPath = Join-Path $root 'upload/app/Views/layouts/frontend.php'
$adminPath = Join-Path $root 'upload/app/Views/layouts/admin.php'

if (!(Test-Path $frontPath) -or !(Test-Path $adminPath)) {
    throw 'NEWS HUB layout files were not found. Extract this package into the Webtintuc repository first.'
}

# Read UTF-8 source files explicitly. This script is ASCII-only so Windows PowerShell 5.1
# cannot misread Vietnamese characters in the script source itself.
$utf8NoBom = New-Object System.Text.UTF8Encoding($false)
$front = [System.IO.File]::ReadAllText($frontPath, [System.Text.Encoding]::UTF8)
$admin = [System.IO.File]::ReadAllText($adminPath, [System.Text.Encoding]::UTF8)
$nl = if ($front.Contains("`r`n")) { "`r`n" } else { "`n" }

$oldFonts = 'https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap'
$newFonts = 'https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&family=Newsreader:opsz,wght@6..72,400;6..72,500;6..72,600;6..72,700&display=swap'
$front = Replace-Once $front $oldFonts $newFonts 'frontend font import'
$admin = Replace-Once $admin $oldFonts $newFonts 'admin font import'

$frontFinish = '    <link href="<?= $escape($baseUrl . ''/public/css/finish.css?v=20261119-6'') ?>" rel="stylesheet">'
$frontCss = '    <link href="<?= $escape($baseUrl . ''/public/css/editorial-refresh.css?v=20261120-1'') ?>" rel="stylesheet">'
$front = Replace-Once $front $frontFinish ($frontFinish + $nl + $frontCss) 'frontend editorial stylesheet'

$adminFinish = '    <link href="<?= $escape($baseUrl . ''/public/css/finish.css?v=20261119-6'') ?>" rel="stylesheet">'
$adminCss = '    <link href="<?= $escape($baseUrl . ''/public/css/editorial-refresh.css?v=20261120-1'') ?>" rel="stylesheet">'
$admin = Replace-Once $admin $adminFinish ($adminFinish + $nl + $adminCss) 'admin editorial stylesheet'

$oldMasthead = Get-Utf8Text 'PHAgY2xhc3M9Im1hc3RoZWFkLW5vdGUiPkfDs2MgbmjDrG4gcsO1IHLDoG5nIHbhu4Egbmjhu69uZyDEkWnhu4F1IMSRYW5nIGRp4buFbiByYS48L3A+'
$newMasthead = Get-Utf8Text 'PHAgY2xhc3M9Im1hc3RoZWFkLW5vdGUiPlRpbiB0cm9uZyBuxrDhu5tjLiBDaHV54buHbiB0aOG6vyBnaeG7m2kuPC9wPg=='
$front = Replace-Once $front $oldMasthead $newMasthead 'frontend masthead note'

$mainScript = '    <script src="<?= $escape($baseUrl . ''/public/js/main.js?v=20261119-6'') ?>" defer></script>'
$editorialScript = '    <script src="<?= $escape($baseUrl . ''/public/js/editorial-refresh.js?v=20261120-1'') ?>" defer></script>'
$front = Replace-Once $front $mainScript ($mainScript + $nl + $editorialScript) 'frontend editorial script'

# Write only after every required original snippet has passed its exact-match check.
[System.IO.File]::WriteAllText($frontPath, $front, $utf8NoBom)
[System.IO.File]::WriteAllText($adminPath, $admin, $utf8NoBom)
Write-Host 'Editorial styles and image handling have been connected to both layouts.' -ForegroundColor Green
Write-Host 'No route, form action, controller, model, database schema, or application function was changed.'
