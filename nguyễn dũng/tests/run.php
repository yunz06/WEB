<?php

declare(strict_types=1);

require_once __DIR__ . '/TestReporter.php';
require_once __DIR__ . '/helpers.php';
require_once dirname(__DIR__) . '/app/Models/Dashboard.php';
require_once __DIR__ . '/auth_test.php';
require_once __DIR__ . '/article_test.php';
require_once __DIR__ . '/category_test.php';
require_once __DIR__ . '/comment_test.php';

$report = new TestReporter();
$dsn = getenv('TEST_DB_DSN');
$username = getenv('TEST_DB_USER');
$password = getenv('TEST_DB_PASSWORD');

if (!is_string($dsn) || $dsn === '') {
    $report->block('DB: Kết nối cơ sở dữ liệu', 'Chưa cung cấp TEST_DB_DSN.');
    exit($report->summary());
}

if (!in_array('mysql', PDO::getAvailableDrivers(), true)) {
    $report->block('DB: PHP PDO MySQL', 'Môi trường này chưa cài driver pdo_mysql.');
    exit($report->summary());
}

try {
    $pdo = new PDO(
        $dsn,
        is_string($username) ? $username : '',
        is_string($password) ? $password : '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    $report->pass('DB: Kết nối MySQL');
    // Chỉ chọn schema demo khi người chạy yêu cầu rõ ràng.
    $configFile = getenv('TEST_USE_DEMO_SCHEMA') === 'true'
        ? 'dashboard.demo.php' : 'dashboard.php';
    $config = require dirname(__DIR__) . '/config/' . $configFile;

    runAuthTests($pdo, $config, $report);
    runArticleTests($pdo, $config, $report);
    runCategoryTests($pdo, $config, $report);
    runCommentTests($pdo, $config, $report);
} catch (Throwable $error) {
    $report->fail('Không thể hoàn tất kiểm thử', $error->getMessage());
}

exit($report->summary());
