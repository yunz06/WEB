<?php
$data = is_array($data ?? null) ? $data : [];
$baseUrl = rtrim((string)($data['baseUrl'] ?? ''), '/');
$pageTitle = htmlspecialchars((string)($data['page_title'] ?? 'NEWS HUB Admin'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$token = htmlspecialchars((string)($data['csrf_token'] ?? ''), ENT_QUOTES, 'UTF-8');
$content = (string)($pageContent ?? '');
$adminUser = is_array($_SESSION['user'] ?? null) ? $_SESSION['user'] : [];
$escape = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$adminLinks = [
    'DashboardController' => ['Tổng quan', '/DashboardController/index'],
    'ArticleController' => ['Bài viết', '/ArticleController/index'],
    'CategoryController' => ['Danh mục', '/CategoryController/index'],
    'UserController' => ['Tài khoản', '/UserController/index'],
    'CommentController' => ['Bình luận', '/CommentController/index'],
];
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$requestSegments = is_string($requestPath) ? explode('/', trim($requestPath, '/')) : [];
$currentSection = 'DashboardController';
foreach (array_keys($adminLinks) as $controllerName) {
    if (in_array($controllerName, $requestSegments, true)) {
        $currentSection = $controllerName;
        break;
    }
}
$currentLabel = $adminLinks[$currentSection][0];
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b1d33">
    <meta name="color-scheme" content="light">
    <title><?= $pageTitle ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $escape($baseUrl . '/public/css/style.css?v=20261119-4') ?>" rel="stylesheet">
    <link href="<?= $escape($baseUrl . '/public/css/admin.css?v=20261119-4') ?>" rel="stylesheet">
    <link href="<?= $escape($baseUrl . '/public/css/finish.css?v=20261119-4') ?>" rel="stylesheet">
</head>
<body class="admin-app">
    <a class="skip-link" href="#admin-main">Bỏ qua điều hướng</a>
    <header class="admin-bar">
        <div class="admin-bar__inner container-fluid">
            <a class="admin-brand" href="<?= $escape($baseUrl . '/DashboardController/index') ?>">
                <span>NEWS HUB</span><small>Trung tâm biên tập</small>
            </a>
            <div class="admin-account">
                <?php if (!empty($adminUser['full_name'])): ?><span class="admin-account__name"><?= $escape($adminUser['full_name']) ?></span><?php endif; ?>
                <a class="admin-public-link" href="<?= $escape($baseUrl . '/') ?>">Trang tin</a>
                <form method="post" action="<?= $escape($baseUrl . '/AuthController/logout') ?>">
                    <input type="hidden" name="csrf_token" value="<?= $token ?>">
                    <button class="btn btn-sm btn-outline-light" type="submit">Đăng xuất</button>
                </form>
            </div>
        </div>
    </header>
    <div class="admin-shell">
        <aside class="admin-sidebar" aria-label="Thanh bên quản trị">
            <p class="admin-sidebar__title">Quản lý nội dung</p>
            <nav class="admin-nav" aria-label="Điều hướng quản trị">
                <?php foreach ($adminLinks as $controllerName => [$label, $path]): ?>
                    <a href="<?= $escape($baseUrl . $path) ?>"<?= $currentSection === $controllerName ? ' aria-current="page"' : '' ?>><?= $escape($label) ?></a>
                <?php endforeach; ?>
            </nav>
        </aside>
        <div class="admin-workspace">
            <nav class="admin-breadcrumb" aria-label="Breadcrumb">
                <a href="<?= $escape($baseUrl . '/DashboardController/index') ?>">Quản trị</a>
                <span aria-hidden="true">/</span>
                <span aria-current="page"><?= $escape($currentLabel) ?></span>
            </nav>
            <main id="admin-main" class="admin-main container-fluid"><?= $content ?></main>
        </div>
    </div>
    <footer class="admin-footer"><div class="container-fluid"><span>NEWS HUB · Quản trị nội dung</span><a href="<?= $escape($baseUrl . '/') ?>">Mở trang tin công khai</a></div></footer>
    <script src="<?= $escape($baseUrl . '/public/js/main.js') ?>" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
</body>
</html>
