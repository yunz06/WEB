<?php

declare(strict_types=1);

use App\Models\Dashboard;

function runCommentTests(PDO $pdo, array $config, TestReporter $report): void
{
    $table = testTableName($config, 'comments');

    if (!testTableExists($pdo, $table)) {
        $report->fail('COM-01: Có bảng comments truy vấn được', 'Không tìm thấy hoặc không có quyền đọc bảng.');
        return;
    }

    $report->pass('COM-01: Có bảng comments truy vấn được');

    $model = new Dashboard($pdo, $config);
    $count = $model->countRows('comments');
    $report->check('COM-02: Đếm bình luận MySQL', $count >= 0, 'Số lượng trả về không hợp lệ.');

    $required = [
        'article_column' => 'liên kết bài viết',
        'user_column' => 'liên kết người dùng',
        'status_column' => 'trạng thái duyệt',
    ];

    foreach ($required as $key => $label) {
        $column = testColumnName($config, 'comment', $key);

        if ($column === null) {
            $report->block('COM-03: Cột ' . $label, 'Chưa xác định từ schema.');
            continue;
        }

        $report->check(
            'COM-03: Cột ' . $label,
            testColumnExists($pdo, $table, $column),
            'Cột cấu hình không tồn tại trong comments.'
        );
    }

    $report->block(
        'COM-04: Guest bị chặn; user gửi; admin duyệt/ẩn/xóa',
        'Cần ứng dụng chạy thật và các tài khoản phân quyền để kiểm tra.'
    );
}
