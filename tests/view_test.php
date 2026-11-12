<?php

declare(strict_types=1);

// Test hiển thị độc lập, KHÔNG phải test hệ thống/DB thật.
$data = [
    'metrics' => [
        'articles' => 3,
        'published_articles' => null,
        'users' => 0,
        'categories' => 1,
        'comments' => 2,
    ],
    'latest_articles' => [
        [
            'article_title' => '<script>alert("x")</script>',
            'article_sort_value' => '2026-11-08 10:00:00',
        ],
    ],
    'warnings' => ['Thiếu cấu hình Published.'],
    'sort_column' => 'created_at',
];

ob_start();
require dirname(__DIR__) . '/app/Views/admin/dashboard.php';
$html = (string) ob_get_clean();

$checks = [
    'Có heading Dashboard' => str_contains($html, 'Dashboard'),
    'Có tiêu đề bài viết đã escape' => str_contains(
        $html,
        '&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;'
    ),
    'Không chèn script trực tiếp' => !str_contains($html, '<script>alert("x")</script>'),
    'Giá trị không xác minh không bị đổi thành 0' => str_contains($html, 'Chưa xác minh'),
    'Hiển thị cảnh báo' => str_contains($html, 'Thiếu cấu hình Published.'),
];

$failed = 0;

foreach ($checks as $name => $passed) {
    echo '[' . ($passed ? 'PASS' : 'FAIL') . '] ' . $name . PHP_EOL;

    if (!$passed) {
        $failed++;
    }
}

exit($failed > 0 ? 1 : 0);
