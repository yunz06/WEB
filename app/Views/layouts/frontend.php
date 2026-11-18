<?php
$baseUrl = $data['baseUrl'] ?? '';
$pageTitle = $data['page_title'] ?? 'NEWS HUB — The World, In Focus.';
$categories = $data['categories'] ?? [];
$selectedCategoryId = $data['selectedCategoryId'] ?? null;
$authUser = $_SESSION['user'] ?? null;
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
    <meta name="description" content="NEWS HUB — The World, In Focus. Tin tức được chọn lọc, cập nhật từ nhiều lĩnh vực.">
    <title><?= $escape($pageTitle) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $escape($baseUrl . '/public/css/style.css') ?>" rel="stylesheet">
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
                <p class="masthead-note">Góc nhìn rõ ràng về những điều đang diễn ra.</p>
                <div class="masthead-actions">
                    <form class="header-search" action="<?= $escape($baseUrl . '/SearchController/index') ?>" method="get" role="search">
                        <label class="visually-hidden" for="header-query">Tìm bài viết</label>
                        <input id="header-query" name="q" type="search" maxlength="120" placeholder="Tìm bài viết…">
                        <button type="submit">Tìm</button>
                    </form>
                    <?php if (is_array($authUser)): ?>
                        <span><?= $escape($authUser['full_name'] ?? 'Tài khoản') ?></span>
                        <form method="post" action="<?= $escape($baseUrl . '/AuthController/logout') ?>">
                            <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                            <button type="submit">Đăng xuất</button>
                        </form>
                        <?php if (($authUser['role_name'] ?? '') === 'admin'): ?><a href="<?= $escape($baseUrl . '/DashboardController/index') ?>">Quản trị</a><?php endif; ?>
                    <?php else: ?>
                        <a href="<?= $escape($baseUrl . '/AuthController/login') ?>">Đăng nhập</a>
                        <a href="<?= $escape($baseUrl . '/AuthController/register') ?>">Đăng ký</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <nav class="navbar navbar-expand-lg category-nav" aria-label="Điều hướng danh mục">
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
                        <?php foreach ($categories as $category): ?>
                            <?php $categoryId = (int)($category['id'] ?? 0); ?>
                            <?php if ($categoryId > 0): ?>
                                <li class="nav-item">
                            <a class="nav-link<?= !empty($category['parent_id']) ? ' category-child' : '' ?><?= $selectedCategoryId === $categoryId ? ' active' : '' ?>"
                                       href="<?= $escape($baseUrl . '/PublicCategoryController/show/' . rawurlencode((string)($category['slug'] ?? ''))) ?>"<?= $selectedCategoryId === $categoryId ? ' aria-current="page"' : '' ?>><?= $escape($category['name'] ?? '') ?></a>
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
</body>
</html>
