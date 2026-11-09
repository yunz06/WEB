<?php

declare(strict_types=1);

/**
 * CHỈ DÙNG CHO MÔI TRƯỜNG DEMO MYSQL RIÊNG — KHÔNG PHẢI SCHEMA CỦA NHÓM.
 * Các cột đối chiếu được với database/demo_schema.sql trong gói này.
 */
return [
    'tables' => [
        'articles' => 'articles',
        'users' => 'users',
        'categories' => 'categories',
        'comments' => 'comments',
    ],
    'article' => [
        'id_column' => 'id',
        'title_column' => 'title',
        'sort_column' => 'created_at',
        'status_column' => 'status',
        'published_value' => 'published',
    ],
    'auth' => [
        'login_column' => 'username',
        'password_column' => 'password_hash',
        'role_column' => 'role',
    ],
    'category' => [
        'id_column' => 'id',
        'article_category_column' => 'category_id',
    ],
    'comment' => [
        'article_column' => 'article_id',
        'user_column' => 'user_id',
        'status_column' => 'status',
        'approved_value' => 'approved',
    ],
];
