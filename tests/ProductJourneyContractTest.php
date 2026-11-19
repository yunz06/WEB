<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$checks = [
    'index.php' => ["'HomeController' => [", "'index' => ['GET', ['page?'], false]", "'AccountController' => ["],
    'app/Controllers/AuthController.php' => ['password_confirm', 'AccountController/index'],
    'app/Views/frontend/auth.php' => ['?area=admin'],
    'app/Controllers/HomeController.php' => ['index(int $page', 'countLatestPublishedExcluding'],
    'app/Controllers/AccountController.php' => ['getProfileById', 'getForUser', 'countForUser'],
    'app/Views/frontend/account.php' => ['Bình luận của bạn', 'currentPage'],
    'app/Views/layouts/frontend.php' => ['/AccountController/index', '/AuthController/login?area=admin'],
    'app/Views/layouts/admin.php' => ['admin-sidebar', 'aria-label="Breadcrumb"', 'ArticleController/index', 'CommentController/index'],
    'app/Controllers/DashboardController.php' => ["getAll(5, 0, 'pending')", 'pendingComments', "'csrf_token' => \$this->csrfToken()"],
    'app/Views/admin/dashboard.php' => ['CommentController/index?status=pending', 'ArticleController/index#articleForm'],
    'app/Views/admin/articles.php' => ['editorFallback', 'window.tinymce'],
];

$failures = [];
foreach ($checks as $relativePath => $needles) {
    $path = $root . '/' . $relativePath;
    $contents = is_file($path) ? file_get_contents($path) : false;
    if (!is_string($contents)) {
        $failures[] = 'Missing product journey file: ' . $relativePath;
        continue;
    }
    foreach ($needles as $needle) {
        if (!str_contains($contents, $needle)) {
            $failures[] = 'Missing contract in ' . $relativePath . ': ' . $needle;
        }
    }
}

$accountView = file_get_contents($root . '/app/Views/frontend/account.php');
if (is_string($accountView) && str_contains($accountView, 'password_hash')) {
    $failures[] = 'Reader account view must not expose password hashes.';
}

if ($failures !== []) {
    fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL);
    exit(1);
}

echo "PASS: reader, Admin, pagination, and editor fallback contracts.\n";
