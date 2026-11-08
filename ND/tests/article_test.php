<?php

declare(strict_types=1);

use App\Models\Dashboard;

function runArticleTests(PDO $pdo, array $config, TestReporter $report): void
{
    $table = testTableName($config, 'articles');

    if (!testTableExists($pdo, $table)) {
        $report->fail('ART-01: Có bảng articles truy vấn được', 'Không tìm thấy hoặc không có quyền đọc bảng.');
        return;
    }

    $report->pass('ART-01: Có bảng articles truy vấn được');

    $model = new Dashboard($pdo, $config);
    $count = $model->countRows('articles');
    $report->check('ART-02: Tổng bài viết là số không âm', $count >= 0, 'Số bản ghi không hợp lệ.');

    $status = testColumnName($config, 'article', 'status_column');
    $published = testColumnName($config, 'article', 'published_value');

    if ($status === null || $published === null) {
        $report->block('ART-03: Đếm bài Published', 'Chưa xác nhận cột và giá trị trạng thái từ schema.');
    } elseif (!testColumnExists($pdo, $table, $status)) {
        $report->fail('ART-03: Đếm bài Published', 'Không tồn tại cột trạng thái đã cấu hình.');
    } else {
        $publishedCount = $model->countPublishedArticles($status, $published);
        $report->check(
            'ART-03: Số Published không vượt tổng bài',
            $publishedCount >= 0 && $publishedCount <= $count,
            'Số liệu trả về không hợp lệ.'
        );
    }

    $title = testColumnName($config, 'article', 'title_column');
    $sort = testColumnName($config, 'article', 'sort_column');

    if ($title === null || $sort === null) {
        $report->block('ART-04: Danh sách 5 bài mới', 'Chưa xác nhận cột tiêu đề và sắp xếp.');
    } elseif (!testColumnExists($pdo, $table, $title) || !testColumnExists($pdo, $table, $sort)) {
        $report->fail('ART-04: Danh sách 5 bài mới', 'Một trong các cột cấu hình không tồn tại.');
    } else {
        $rows = $model->getLatestArticles($title, $sort, 5);
        $report->check('ART-04: Truy vấn tối đa 5 bài', count($rows) <= 5, 'Truy vấn vượt số lượng.');
    }

    $report->block(
        'ART-05: CRUD, upload nhiều ảnh, editor, phân trang',
        'Cần project chạy thật và dữ liệu test; xem TEST_CASES.md.'
    );
}
