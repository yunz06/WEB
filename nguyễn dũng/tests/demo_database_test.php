<?php

declare(strict_types=1);

/**
 * Chạy khi Docker/MySQL demo đã lên, kiểm chứng số liệu so với seed SQL tự tạo.
 * Không thay thế test dự án nhóm hoặc các module CRUD thật.
 */
require_once dirname(__DIR__) . '/app/Models/Dashboard.php';
require_once __DIR__ . '/TestReporter.php';

use App\Models\Dashboard;

$report = new TestReporter();

if (!in_array('mysql', PDO::getAvailableDrivers(), true)) {
    $report->block('MySQL demo', 'Chưa có pdo_mysql');
    exit($report->summary());
}

try {
    $pdo = new PDO(
        (string) getenv('TEST_DB_DSN'),
        (string) getenv('TEST_DB_USER'),
        (string) getenv('TEST_DB_PASSWORD'),
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false]
    );
    $dashboard = new Dashboard($pdo, require dirname(__DIR__) . '/config/dashboard.demo.php');
    $stats = $dashboard->getOverview(5);

    // EXPECTED ONLY for fresh DB seeded exactly once with demo_seed.sql.
    $expected = [
        'articles' => 6,
        'published_articles' => 5,
        'users' => 3,
        'categories' => 3,
        'comments' => 4,
    ];

    foreach ($expected as $key => $value) {
        $report->check('Demo ' . $key, $stats['metrics'][$key] === $value,
            'Số liệu lệch seed demo. Có thể database đã bị sửa; kiểm tra trước khi reset volume.');
    }
    $report->check('Không có cảnh báo SQL', $stats['warnings'] === [], implode('; ', $stats['warnings']));
    $report->check('Đúng 5 bài mới', count($stats['latest_articles']) === 5, 'Số bài mới không đúng');
    $first = $stats['latest_articles'][0]['article_title'] ?? '';
    $report->check('Bài mới nhất đúng', $first === 'Bài mới kiểm thử 06', 'Bài mới nhất không đúng seed');
} catch (Throwable $exception) {
    $report->fail('Kết nối và SELECT DB demo', $exception->getMessage());
}

exit($report->summary());
