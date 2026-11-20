<?php
$baseUrl = $data['baseUrl'] ?? '';
$pageTitle = $data['page_title'] ?? 'NEWS HUB — The World, In Focus.';
$categories = $data['categories'] ?? [];
$selectedCategoryId = $data['selectedCategoryId'] ?? null;
$categoryGroups = [];
foreach ($categories as $category) {
    if (($category['parent_id'] ?? null) === null) {
        $categoryId = (int)$category['id'];
        $categoryGroups[$categoryId] = ['category' => $category, 'children' => []];
    }
}
foreach ($categories as $category) {
    $parentId = (int)($category['parent_id'] ?? 0);
    if ($parentId > 0 && isset($categoryGroups[$parentId])) {
        $categoryGroups[$parentId]['children'][] = $category;
    }
}
$authUser = $_SESSION['user'] ?? null;
$isAdminEntry = !empty($data['adminEntry']);
$csrfToken = (string)($data['csrf_token'] ?? '');
$homeUrl = $baseUrl . '/';
$escape = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0b1d33">
    <meta name="color-scheme" content="light">
    <meta name="news-hub-build" content="20261120-image-pipeline-1">
    <meta name="description" content="NEWS HUB — The World, In Focus. Tin tức được chọn lọc, cập nhật từ nhiều lĩnh vực.">
    <title><?= $escape($pageTitle) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&family=Newsreader:opsz,wght@6..72,400;6..72,500;6..72,600;6..72,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $escape($baseUrl . '/public/css/style.css?v=20261120-image-pipeline-1') ?>" rel="stylesheet">
    <link href="<?= $escape($baseUrl . '/public/css/finish.css?v=20261120-image-pipeline-1') ?>" rel="stylesheet">
    <link href="<?= $escape($baseUrl . '/public/css/editorial-refresh.css?v=20261120-image-pipeline-1') ?>" rel="stylesheet">
    <link href="<?= $escape($baseUrl . '/public/css/editorial-redesign.css?v=20261120-image-pipeline-1') ?>" rel="stylesheet">
</head>
<body>
    <a class="skip-link" href="#main-content">Bỏ qua điều hướng</a>

    <div class="site-ribbon">
        <div class="container site-ribbon__inner">
            <span>NEWS HUB</span>
            <span class="site-ribbon__motto">The World, In Focus.</span>
        </div>
    </div>

    <header class="site-header">
        <div class="container">
            <div class="masthead">
                <a class="brand-lockup" href="<?= $escape($homeUrl) ?>" aria-label="NEWS HUB — Trang chủ">
                    <span class="brand-name">NEWS<span>HUB</span></span>
                    <span class="brand-tagline">The World, In Focus.</span>
                </a>
                <p class="masthead-note">Tin trong nước. Chuyện thế giới.</p>
                <div class="masthead-actions">
                    <form class="header-search" action="<?= $escape($baseUrl . '/SearchController/index') ?>" method="get" role="search">
                        <label class="visually-hidden" for="header-query">Tìm bài viết</label>
                        <input id="header-query" name="q" type="search" maxlength="120" autocomplete="off" placeholder="Tìm bài viết…">
                        <button type="submit">Tìm</button>
                    </form>
                    <?php if (is_array($authUser)): ?>
                        <span class="masthead-user"><?= $escape($authUser['full_name'] ?? 'Tài khoản') ?></span>
                        <?php if (($authUser['role_name'] ?? '') === 'admin'): ?>
                            <a href="<?= $escape($baseUrl . '/DashboardController/index') ?>">Quản trị</a>
                        <?php else: ?>
                            <a href="<?= $escape($baseUrl . '/AccountController/index') ?>">Tài khoản</a>
                        <?php endif; ?>
                        <form method="post" action="<?= $escape($baseUrl . '/AuthController/logout') ?>">
                            <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                            <button type="submit">Đăng xuất</button>
                        </form>
                    <?php else: ?>
                        <?php if ($isAdminEntry): ?>
                            <a href="<?= $escape($homeUrl) ?>">Trang tin</a>
                            <a href="<?= $escape($baseUrl . '/AuthController/login') ?>">Đăng nhập độc giả</a>
                        <?php else: ?>
                            <a href="<?= $escape($baseUrl . '/AuthController/login') ?>">Đăng nhập</a>
                            <a href="<?= $escape($baseUrl . '/AuthController/register') ?>">Đăng ký</a>
                            <a class="masthead-admin-entry" href="<?= $escape($baseUrl . '/AuthController/login?area=admin') ?>" aria-label="Mở cổng đăng nhập quản trị viên">Cổng quản trị</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <nav class="navbar navbar-expand-xl category-nav" aria-label="Điều hướng danh mục">
            <div class="container">
                <a class="navbar-brand category-nav__label" href="<?= $escape($homeUrl) ?>">Chuyên mục</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#newsCategories" aria-controls="newsCategories" aria-expanded="false" aria-label="Mở danh mục">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="newsCategories">
                    <ul class="navbar-nav category-nav__items">
                        <li class="nav-item">
                            <a class="nav-link<?= $selectedCategoryId === null ? ' active' : '' ?>" href="<?= $escape($homeUrl) ?>"<?= $selectedCategoryId === null ? ' aria-current="page"' : '' ?>>Tất cả tin</a>
                        </li>
                        <?php foreach ($categoryGroups as $categoryId => $group): ?>
                            <?php
                            $category = $group['category'];
                            $children = $group['children'];
                            $isCurrentGroup = (int)$selectedCategoryId === (int)$categoryId
                                || in_array((int)$selectedCategoryId, array_map(static fn(array $child): int => (int)$child['id'], $children), true);
                            $categoryUrl = $baseUrl . '/PublicCategoryController/show/' . rawurlencode((string)$category['slug']);
                            ?>
                            <?php if ($children !== []): ?>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle<?= $isCurrentGroup ? ' active' : '' ?>" href="#category-menu-<?= (int)$categoryId ?>" id="category-toggle-<?= (int)$categoryId ?>" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <?= $escape($category['name']) ?>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="category-toggle-<?= (int)$categoryId ?>">
                                        <li><a class="dropdown-item" href="<?= $escape($categoryUrl) ?>">Tất cả tin <?= $escape($category['name']) ?></a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <?php foreach ($children as $child): ?>
                                            <?php $childUrl = $baseUrl . '/PublicCategoryController/show/' . rawurlencode((string)$child['slug']); ?>
                                            <li><a class="dropdown-item" href="<?= $escape($childUrl) ?>"<?= (int)$selectedCategoryId === (int)$child['id'] ? ' aria-current="page"' : '' ?>><?= $escape($child['name']) ?></a></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php else: ?>
                                <li class="nav-item">
                                    <a class="nav-link<?= $isCurrentGroup ? ' active' : '' ?>" href="<?= $escape($categoryUrl) ?>"<?= $isCurrentGroup ? ' aria-current="page"' : '' ?>><?= $escape($category['name']) ?></a>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                    <?php if (empty($categories)): ?>
                        <span class="category-nav__empty">Danh mục sẽ xuất hiện tại đây</span>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <?= $pageContent ?? '' ?>

    <footer class="site-footer">
        <div class="container site-footer__inner">
            <a class="site-footer__brand" href="<?= $escape($homeUrl) ?>">NEWS HUB</a>
            <p>The World, In Focus.</p>
            <small>© <?= date('Y') ?> NEWS HUB</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="<?= $escape($baseUrl . '/public/js/main.js?v=20261120-image-pipeline-1') ?>" defer></script>
</body>
</html>
