<?php

declare(strict_types=1);

/**
 * Cấu hình chờ đối chiếu với database/news_hub.sql của nhóm.
 * Tên bảng lấy từ bảng phân công; các tên cột CHƯA được xác nhận.
 * Không tự điền cột hoặc giá trị trạng thái khi chưa đọc schema thật.
 */
return [
    'tables' => [
        'articles' => 'articles',
        'users' => 'users',
        'categories' => 'categories',
        'comments' => 'comments',
    ],
    'article' => [
        'id_column' => null,
        'title_column' => null,
        'sort_column' => null,
        'status_column' => null,
        'published_value' => null,
    ],
    'auth' => [
        'login_column' => null,
        'password_column' => null,
        'role_column' => null,
    ],
    'category' => [
        'id_column' => null,
        'article_category_column' => null,
    ],
    'comment' => [
        'article_column' => null,
        'user_column' => null,
        'status_column' => null,
        'approved_value' => null,
    ],
];
