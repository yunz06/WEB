<?php
$data = is_array($data ?? null) ? $data : [];
$baseUrl = (string)($data['baseUrl'] ?? '');
$featuredArticle = $data['featuredArticle'] ?? null;
$latestArticles = is_array($data['latestArticles'] ?? null) ? $data['latestArticles'] : [];
$featuredArticles = is_array($data['featuredArticles'] ?? null) ? $data['featuredArticles'] : [];
$categorySections = is_array($data['categorySections'] ?? null) ? $data['categorySections'] : [];
$selectedCategoryId = $data['selectedCategoryId'] ?? null;
$latestPage = max(1, (int)($data['latestPage'] ?? 1));
$latestTotalPages = max(1, (int)($data['latestTotalPages'] ?? 1));
$escape = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
require_once __DIR__ . '/../../Core/ArticleImage.php';
$articleImageUrl = static fn(array $article): ?string => ArticleImage::url($article['thumbnail'] ?? null, $baseUrl);
$publishedDate = static function (array $article): string {
    $timestamp = strtotime((string)($article['published_at'] ?? $article['created_at'] ?? ''));
    return $timestamp === false ? '' : date('d/m/Y', $timestamp);
};
$formatDate = date('d.m.Y');
$railStories = array_values(array_filter($featuredArticles, static fn(array $item): bool => (int)($item['id'] ?? 0) !== (int)($featuredArticle['id'] ?? 0)));
if ($railStories === []) $railStories = array_slice($latestArticles, 0, 3);

ob_start();
?>
<main id="main-content" class="news-home editorial-home">
    <div class="container">
        <div class="home-edition-line"><span>BẢN TIN NGÀY <?= $escape($formatDate) ?></span><span>THÔNG TIN · PHÂN TÍCH · GÓC NHÌN</span></div>

        <section class="home-lead-grid" aria-labelledby="featured-heading">
            <div class="home-lead-main">
                <div class="home-section-label"><span>01</span><h1 id="featured-heading">Tiêu điểm</h1><i></i></div>
                <?php if (is_array($featuredArticle)): ?>
                    <?php $featuredImage = $articleImageUrl($featuredArticle); ?>
                    <article class="featured-story">
                        <a class="featured-story__visual" href="<?= $escape($baseUrl . '/FrontendArticleController/show/' . (int)$featuredArticle['id']) ?>" aria-label="Đọc: <?= $escape($featuredArticle['title'] ?? '') ?>">
                            <?php if ($featuredImage !== null): ?><img src="<?= $escape($featuredImage) ?>" width="1400" height="700" alt="Ảnh minh họa: <?= $escape($featuredArticle['title'] ?? '') ?>" fetchpriority="high"><?php else: ?><div class="story-placeholder"><span>NEWS HUB</span></div><?php endif; ?>
                            <span class="image-context-label">Ảnh minh họa</span>
                        </a>
                        <div class="featured-story__copy">
                            <p class="story-category"><?= $escape($featuredArticle['category_name'] ?? 'Tin mới') ?></p>
                            <h2><a href="<?= $escape($baseUrl . '/FrontendArticleController/show/' . (int)$featuredArticle['id']) ?>"><?= $escape($featuredArticle['title'] ?? '') ?></a></h2>
                            <?php if (!empty($featuredArticle['summary'])): ?><p class="featured-story__summary"><?= $escape($featuredArticle['summary']) ?></p><?php endif; ?>
                            <div class="story-card-meta"><time datetime="<?= $escape($featuredArticle['published_at'] ?? '') ?>"><?= $escape($publishedDate($featuredArticle)) ?></time><a href="<?= $escape($baseUrl . '/FrontendArticleController/show/' . (int)$featuredArticle['id']) ?>">Đọc bài <span aria-hidden="true">↗</span></a></div>
                        </div>
                    </article>
                <?php else: ?>
                    <div class="empty-feature"><h2>Chưa có bài viết nổi bật</h2><p>Bài viết đã xuất bản sẽ xuất hiện tại đây.</p></div>
                <?php endif; ?>
            </div>

            <aside class="home-lead-rail" aria-labelledby="rail-heading">
                <div class="home-section-label home-section-label--rail"><span>02</span><h2 id="rail-heading">Đang được chú ý</h2></div>
                <?php foreach (array_slice($railStories, 0, 4) as $index => $article): ?>
                    <?php $railImage = $articleImageUrl($article); ?>
                    <article class="rail-story">
                        <span class="rail-story-number">0<?= $index + 1 ?></span>
                        <div class="rail-story-copy"><p class="story-category"><?= $escape($article['category_name'] ?? '') ?></p><h3><a href="<?= $escape($baseUrl . '/FrontendArticleController/show/' . (int)$article['id']) ?>"><?= $escape($article['title'] ?? '') ?></a></h3><time datetime="<?= $escape($article['published_at'] ?? '') ?>"><?= $escape($publishedDate($article)) ?></time></div>
                        <?php if ($railImage !== null): ?><a class="rail-story-image" href="<?= $escape($baseUrl . '/FrontendArticleController/show/' . (int)$article['id']) ?>"><img src="<?= $escape($railImage) ?>" alt="Ảnh minh họa: <?= $escape($article['title'] ?? '') ?>" loading="lazy"></a><?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </aside>
        </section>

        <section class="home-latest-section" id="latest" aria-labelledby="latest-heading">
            <div class="home-section-heading">
                <div><span class="section-overline">CẬP NHẬT LIÊN TỤC</span><h2 id="latest-heading"><?= $selectedCategoryId === null ? 'Tin mới nhất' : 'Tin mới theo danh mục' ?></h2></div>
                <?php if ($selectedCategoryId !== null): ?><a class="home-view-all" href="<?= $escape($baseUrl . '/') ?>">Xem tất cả tin <span aria-hidden="true">↗</span></a><?php endif; ?>
            </div>
            <?php if ($latestArticles !== []): ?>
                <div class="latest-list">
                    <?php foreach ($latestArticles as $article): ?>
                        <?php $latestImage = $articleImageUrl($article); ?>
                        <article class="latest-story">
                            <a class="latest-story__media" href="<?= $escape($baseUrl . '/FrontendArticleController/show/' . (int)$article['id']) ?>" aria-label="Đọc: <?= $escape($article['title'] ?? '') ?>">
                                <?php if ($latestImage !== null): ?><img src="<?= $escape($latestImage) ?>" width="800" height="500" alt="Ảnh minh họa: <?= $escape($article['title'] ?? '') ?>" loading="lazy"><?php else: ?><span class="latest-story__placeholder">NEWS HUB</span><?php endif; ?>
                            </a>
                            <div class="latest-story__body"><p class="story-category"><?= $escape($article['category_name'] ?? '') ?></p>
                                <h3><a href="<?= $escape($baseUrl . '/FrontendArticleController/show/' . (int)$article['id']) ?>"><?= $escape($article['title'] ?? '') ?></a></h3>
                                <?php if (!empty($article['summary'])): ?><p class="latest-story__summary"><?= $escape($article['summary']) ?></p><?php endif; ?>
                                <div class="story-card-meta"><time datetime="<?= $escape($article['published_at'] ?? '') ?>"><?= $escape($publishedDate($article)) ?></time><a href="<?= $escape($baseUrl . '/FrontendArticleController/show/' . (int)$article['id']) ?>">Chi tiết <span aria-hidden="true">↗</span></a></div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php elseif (is_array($featuredArticle)): ?>
                <p class="latest-empty">Chưa có thêm bài viết trong danh mục này.</p>
            <?php else: ?>
                <p class="latest-empty">Bài viết mới sẽ xuất hiện tại đây sau khi được xuất bản.</p>
            <?php endif; ?>
            <?php if ($latestTotalPages > 1): ?>
                <nav class="news-pagination" aria-label="Phân trang tin mới nhất">
                    <?php if ($latestPage > 1): ?><a href="<?= $escape($baseUrl . '/HomeController/index/' . ($latestPage - 1) . '#latest') ?>">← Tin mới hơn</a><?php endif; ?>
                    <span aria-current="page">Trang <?= $latestPage ?> / <?= $latestTotalPages ?></span>
                    <?php if ($latestPage < $latestTotalPages): ?><a href="<?= $escape($baseUrl . '/HomeController/index/' . ($latestPage + 1) . '#latest') ?>">Tin cũ hơn →</a><?php endif; ?>
                </nav>
            <?php endif; ?>
        </section>

        <?php if ($categorySections !== []): ?>
            <div class="home-category-sections" aria-label="Tin theo chuyên mục">
                <?php foreach ($categorySections as $section): ?>
                    <section class="category-section" aria-labelledby="category-section-<?= (int)$section['id'] ?>">
                        <div class="home-section-heading"><div><span class="section-overline">CHUYÊN MỤC</span><h2 id="category-section-<?= (int)$section['id'] ?>"><a href="<?= $escape($baseUrl . '/PublicCategoryController/show/' . rawurlencode((string)$section['slug'])) ?>"><?= $escape($section['name']) ?></a></h2></div><a class="home-view-all" href="<?= $escape($baseUrl . '/PublicCategoryController/show/' . rawurlencode((string)$section['slug'])) ?>">Xem chuyên mục <span aria-hidden="true">↗</span></a></div>
                        <div class="category-section__stories">
                            <?php foreach ($section['articles'] as $article): ?>
                                <?php $sectionImage = $articleImageUrl($article); ?>
                                <article class="category-section__story">
                                    <?php if ($sectionImage !== null): ?><a class="category-section__media" href="<?= $escape($baseUrl . '/FrontendArticleController/show/' . (int)$article['id']) ?>"><img src="<?= $escape($sectionImage) ?>" alt="Ảnh minh họa: <?= $escape($article['title'] ?? '') ?>" loading="lazy"></a><?php endif; ?>
                                    <p class="story-category"><?= $escape($article['category_name'] ?? $section['name']) ?></p>
                                    <h3><a href="<?= $escape($baseUrl . '/FrontendArticleController/show/' . (int)$article['id']) ?>"><?= $escape($article['title'] ?? '') ?></a></h3>
                                    <?php if (!empty($article['summary'])): ?><p><?= $escape($article['summary']) ?></p><?php endif; ?>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>
<?php
$pageContent = (string)ob_get_clean();
require __DIR__ . '/../layouts/frontend.php';
