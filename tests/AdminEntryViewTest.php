<?php

declare(strict_types=1);

$root = dirname(__DIR__);

function renderAuthView(array $data): string
{
    $_SESSION = [];
    ob_start();
    require dirname(__DIR__) . '/app/Views/frontend/auth.php';
    return (string)ob_get_clean();
}

$adminHtml = renderAuthView([
    'baseUrl' => '/Webtintuc',
    'mode' => 'auth_login',
    'adminEntry' => true,
    'page_title' => 'Đăng nhập quản trị — NEWS HUB',
    'csrf_token' => 'test-token',
    'categories' => [],
]);

$readerHtml = renderAuthView([
    'baseUrl' => '/Webtintuc',
    'mode' => 'auth_login',
    'adminEntry' => false,
    'page_title' => 'Đăng nhập — NEWS HUB',
    'csrf_token' => 'test-token',
    'categories' => [],
]);

$checks = [
    'Admin login has a clear heading' => str_contains($adminHtml, 'Đăng nhập quản trị'),
    'Admin page explains local account setup' => str_contains($adminHtml, 'manage-local-admin.php'),
    'Admin page does not link to public registration' => !str_contains($adminHtml, '/AuthController/register'),
    'Admin page offers reader login' => str_contains($adminHtml, 'Đăng nhập độc giả'),
    'Reader login retains registration link' => str_contains($readerHtml, '/AuthController/register'),
    'Reader header exposes Admin portal' => str_contains($readerHtml, 'Cổng quản trị'),
];

$failed = [];
foreach ($checks as $label => $passed) {
    if (!$passed) $failed[] = $label;
}
if ($failed !== []) {
    fwrite(STDERR, 'FAIL: ' . implode('; ', $failed) . PHP_EOL);
    exit(1);
}
echo "PASS: Rendered Admin and reader login entry points.\n";
