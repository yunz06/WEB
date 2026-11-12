<?php

declare(strict_types=1);

use App\Models\Dashboard;

function runCategoryTests(PDO $pdo, array $config, TestReporter $report): void
{
    $table = testTableName($config, 'categories');

    if (!testTableExists($pdo, $table)) {
        $report->fail('CAT-01: Có bảng categories truy vấn được', 'Không tìm thấy hoặc không có quyền đọc bảng.');
        return;
    }

    $report->pass('CAT-01: Có bảng categories truy vấn được');

    $model = new Dashboard($pdo, $config);
    $count = $model->countRows('categories');
    $report->check('CAT-02: Đếm danh mục MySQL', $count >= 0, 'Số lượng trả về không hợp lệ.');

    $idColumn = testColumnName($config, 'category', 'id_column');

    if ($idColumn === null) {
        $report->block('CAT-03: Kiểm tra ID danh mục', 'Chưa xác nhận cột khóa chính categories.');
    } elseif (!testColumnExists($pdo, $table, $idColumn)) {
        $report->fail('CAT-03: Kiểm tra ID danh mục', 'Cột ID đã cấu hình không tồn tại.');
    } else {
        $query = 'SELECT COUNT(DISTINCT ' . testIdentifier($idColumn) . ') FROM '
            . testIdentifier($table);
        $distinctCount = (int) $pdo->query($query)->fetchColumn();

        $report->check(
            'CAT-03: ID danh mục không trùng và không NULL',
            $count === $distinctCount,
            'Có ID trùng/NULL hoặc dữ liệu không nhất quán.'
        );
    }

    $report->block(
        'CAT-04: CRUD danh mục, liên kết bài viết',
        'Cần CategoryController, ArticleController và ứng dụng chạy thật.'
    );
}
