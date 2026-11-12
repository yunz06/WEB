<?php

declare(strict_types=1);

// CHỈ dùng để kiểm tra giao diện. TẤT CẢ số liệu dưới đây là giả lập thử nghiệm.
$data = [
    'metrics' => [
        'articles' => 128,
        'published_articles' => 106,
        'users' => 42,
        'categories' => 8,
        'comments' => 313,
    ],
    'latest_articles' => [
        ['article_title' => 'Bản tin công nghệ mới nhất', 'article_sort_value' => '2026-11-11 08:30:00'],
        ['article_title' => 'Hoạt động sinh viên tuần này', 'article_sort_value' => '2026-11-10 17:15:00'],
        ['article_title' => 'Tin tức cộng đồng và giáo dục', 'article_sort_value' => '2026-11-09 10:45:00'],
        ['article_title' => 'Sự kiện hôm nay tại trường', 'article_sort_value' => '2026-11-08 14:00:00'],
        ['article_title' => 'Thông báo dành cho độc giả', 'article_sort_value' => '2026-11-07 09:00:00'],
    ],
    'warnings' => [],
    'sort_column' => 'created_at',
];
ob_start();
require dirname(__DIR__, 2) . '/app/Views/admin/dashboard.php';
$content = (string)ob_get_clean();
$css = file_get_contents(dirname(__DIR__, 2) . '/public/css/dashboard.css');
?><!doctype html>
<html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>TV5 Dashboard – Chỉ kiểm thử giao diện</title><style><?= $css ?></style>
<style>body{margin:0;background:#f5f7fb;color:#17243c}.preview-box{font:600 13px sans-serif;text-align:center;padding:14px;color:#7a3917;background:#fff5dd;border-bottom:1px solid #e6c68b}.preview-wrap{max-width:1200px;margin:auto;padding:16px}</style>
</head><body><div class="preview-box">GIAO DIỆN THỬ NGHIỆM — SỐ LIỆU GIẢ LẬP — KHÔNG PHẢI DATABASE NHÓM</div><main class="preview-wrap"><?= $content ?></main></body></html>
