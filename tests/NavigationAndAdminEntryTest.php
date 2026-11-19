<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$layout = file_get_contents($root . '/app/Views/layouts/frontend.php');
$css = file_get_contents($root . '/public/css/finish.css');
$auth = file_get_contents($root . '/app/Controllers/AuthController.php');
$authView = file_get_contents($root . '/app/Views/frontend/auth.php');
$sql = file_get_contents($root . '/database/editorial_content_update.sql');
$readme = file_get_contents($root . '/README.md');
$adminTool = file_get_contents($root . '/tools/manage-local-admin.php');
$seed = file_get_contents($root . '/database/seed.sql');

$checks = [
    'Admin entry is in masthead' => str_contains($layout, 'masthead-admin-entry') && str_contains($layout, 'Cổng quản trị'),
    'Admin login header avoids reader registration links' => str_contains($layout, '$isAdminEntry') && str_contains($layout, 'Đăng nhập độc giả'),
    'Admin portal is not footer-only' => !str_contains($layout, 'site-footer__admin-entry'),
    'Fresh seed demo Admin credential matches its stored hash' => str_contains($seed, 'admin@newshub.local') && str_contains($seed, '$2y$10$kRlTmeOWlheKVZajH51p7O1g4UpNCiQvP.jgginREn3Vs6FQaHa6C') && password_verify('NewsHubDemo!2026', '$2y$10$kRlTmeOWlheKVZajH51p7O1g4UpNCiQvP.jgginREn3Vs6FQaHa6C'),
    'Navbar container and child surfaces stay navy' => str_contains($css, '.category-nav .navbar-collapse') && str_contains($css, 'background-color: #102747 !important;'),
    'Navbar hover/focus uses readable white text on blue surface' => str_contains($css, '.category-nav .nav-link:hover') && str_contains($css, 'background-color: #294f78 !important;') && str_contains($css, 'color: #ffffff !important;'),
    'Dropdown hover uses dark text on light surface' => str_contains($css, '.category-nav .dropdown-item:hover') && str_contains($css, 'background-color: #e8f1ff !important;'),
    'Reader cannot pass Admin portal with User role' => str_contains($auth, "\$user['role_name'] !== 'admin'"),
    'Admin login view has a dedicated Admin branch' => str_contains($authView, 'if ($adminEntry):') && str_contains($authView, 'Đăng nhập độc giả') && str_contains($authView, 'Không có chức năng đăng ký Admin công khai.'),
    'Admin CLI requires explicit confirmation to promote existing User' => str_contains($adminTool, "Type PROMOTE to confirm") && str_contains($adminTool, "UPDATE users SET role_id=:role"),
    'SQL declares MariaDB/MySQL dialect' => str_contains($sql, 'MariaDB 10.4 / MySQL') && str_contains($sql, 'Do not run this file through a Microsoft SQL Server'),
    'README explains Admin local setup' => str_contains($readme, 'tools\\manage-local-admin.php') && str_contains($readme, 'Cổng Admin'),
    'README explains local/cloud database boundary' => str_contains($readme, 'Database hoạt động như thế nào khi chạy local') && str_contains($readme, 'Không dùng database XAMPP local làm production database'),
];

$failed = [];
foreach ($checks as $label => $passed) {
    if (!$passed) $failed[] = $label;
}
if ($failed !== []) {
    fwrite(STDERR, 'FAIL: ' . implode('; ', $failed) . PHP_EOL);
    exit(1);
}
echo "PASS: Admin header entry, navigation contrast, Admin role workflow, SQL dialect guidance, and deployment/data-flow docs.\n";
