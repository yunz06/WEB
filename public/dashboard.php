<?php

declare(strict_types=1);

// Entry point riêng cho thử nghiệm cục bộ. Dự án nhóm phải dùng Router/Auth chung.
if (getenv('DASHBOARD_STANDALONE_DEMO') !== 'true') {
    http_response_code(404);
    exit('Not Found');
}

$expectedUser = getenv('DEMO_ADMIN_USER');
$expectedPass = getenv('DEMO_ADMIN_PASSWORD');
$receivedUser = $_SERVER['PHP_AUTH_USER'] ?? '';
$receivedPass = $_SERVER['PHP_AUTH_PW'] ?? '';

$authorized = is_string($expectedUser) && $expectedUser !== ''
    && is_string($expectedPass) && $expectedPass !== ''
    && hash_equals($expectedUser, (string) $receivedUser)
    && hash_equals($expectedPass, (string) $receivedPass);

if (!$authorized) {
    header('WWW-Authenticate: Basic realm="Dashboard Demo Local"');
    http_response_code(401);
    exit('Chỉ dành cho Admin demo.');
}

require_once dirname(__DIR__) . '/app/Models/Dashboard.php';
require_once dirname(__DIR__) . '/app/Controllers/Admin/DashboardController.php';

use App\Models\Dashboard;
use App\Controllers\Admin\DashboardController;

$host = getenv('DB_HOST') ?: 'db';
$port = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_NAME') ?: 'news_hub_demo';
$dbUser = getenv('DB_USER');
$dbPass = getenv('DB_PASSWORD');

try {
    if (!in_array('mysql', PDO::getAvailableDrivers(), true)) {
        throw new RuntimeException('PHP chưa có driver pdo_mysql.');
    }
    $pdo = new PDO(
        'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $dbName . ';charset=utf8mb4',
        is_string($dbUser) ? $dbUser : '',
        is_string($dbPass) ? $dbPass : '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    $model = new Dashboard($pdo, require dirname(__DIR__) . '/config/dashboard.demo.php');
    // Đã kiểm tra HTTP Basic Auth ở phía trên. Callback chỉ áp dụng trong demo.
    $controller = new DashboardController($model, static fn (): bool => $authorized);
    $content = $controller->index();
} catch (Throwable $exception) {
    error_log('Dashboard demo error: ' . $exception->getMessage());
    http_response_code(503);
    $content = '<main style="padding:2rem;font-family:sans-serif">'
        . '<h1>Không thể tải Dashboard</h1>'
        . '<p>Kiểm tra MySQL, thông tin kết nối và log của môi trường demo.</p></main>';
}
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard — Demo cục bộ</title>
    <link rel="stylesheet" href="/css/dashboard.css">
</head>
<body style="margin:0;background:#f5f7fb;color:#17243c">
    <div style="max-width:1200px;margin:0 auto;padding:20px">
        <p style="font:14px sans-serif;color:#4f5b70">DEMO CỤC BỘ — số liệu từ database mẫu, không phải dự án nhóm</p>
        <?= $content ?>
    </div>
    <script src="/js/dashboard.js"></script>
</body>
</html>
