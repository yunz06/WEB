<?php

declare(strict_types=1);

function runAuthTests(PDO $pdo, array $config, TestReporter $report): void
{
    $table = testTableName($config, 'users');

    if (!testTableExists($pdo, $table)) {
        $report->fail('AUTH-01: Có bảng users truy vấn được', 'Không tìm thấy hoặc không có quyền đọc bảng.');
        return;
    }

    $report->pass('AUTH-01: Có bảng users truy vấn được');

    $required = [
        'login_column' => 'cột đăng nhập',
        'password_column' => 'cột mật khẩu',
        'role_column' => 'cột phân quyền',
    ];

    foreach ($required as $key => $label) {
        $column = testColumnName($config, 'auth', $key);

        if ($column === null) {
            $report->block('AUTH-02 ' . $label, 'Chưa đọc schema để xác định tên cột.');
            continue;
        }

        $report->check(
            'AUTH-02 ' . $label,
            testColumnExists($pdo, $table, $column),
            'Cột cấu hình không tồn tại trong bảng users.'
        );
    }

    $report->block(
        'AUTH-03: Đăng nhập/đăng xuất, kiểm tra Admin/User',
        'Cần Router, Auth và tài khoản test thật của nhóm; xem TEST_CASES.md.'
    );
}
