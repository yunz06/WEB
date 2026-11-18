<?php
$data = is_array($data ?? null) ? $data : [];
$baseUrl = (string)($data['baseUrl'] ?? '');
$categories = $data['categories'] ?? [];
$featuredArticle = $data['featuredArticle'] ?? null;
$latestArticles = $data['latestArticles'] ?? [];
$featuredArticles = $data['featuredArticles'] ?? [];
$selectedCategoryId = $data['selectedCategoryId'] ?? null;
$escape = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');

$articleImageUrl = static function (array $article) use ($baseUrl, $escape): ?string {
    $thumbnail = trim((string)($article['thumbnail'] ?? ''));
    if ($thumbnail === '') {
        return null;
    }

    $fileName = basename($thumbnail);
    if (!is_file(__DIR__ . '/../../../public/uploads/articles/' . $fileName)) return null;
    return $escape($baseUrl . '/public/uploads/articles/' . rawurlencode($fileName));
};

$publishedDate = static function (array $article): string {
    $timestamp = strtotime((string)($article['published_at'] ?? $article['created_at'] ?? ''));
    return $timestamp === false ? '' : date('d/m/Y', $timestamp);
};

ob_start();
?>
<main id="main-content" class="news-home">
    <div class="container">
        <section class="featured-section" aria-labelledby="featured-heading">
            <div class="section-heading">
                <h1 id="featured-heading">Tin nổi bật</h1>
                <span class="section-heading__rule" aria-hidden="true"></span>
            </div>

            <?php if (is_array($featuredArticle)): ?>
                <article class="featured-story">
                    <div class="featured-story__visual">
                        <?php $featuredImage = $articleImageUrl($featuredArticle); ?>
                        <a href="<?= $escape($baseUrl . '/FrontendArticleController/show/' . (int)$featuredArticle['id']) ?>" aria-label="Đọc: <?= $escape($featuredArticle['title'] ?? '') ?>">
                        <?php if ($featuredImage !== null): ?>
                            <img src="<?= $featuredImage ?>" width="1200" height="800" alt="Ảnh đại diện: <?= $escape($featuredArticle['title'] ?? '') ?>" fetchpriority="high">
                        <?php else: ?>
                            <div class="story-placeholder" role="img" aria-label="Bài viết chưa có ảnh đại diện">
                                <span class="story-placeholder__wordmark">NH</span>
                                <span class="story-placeholder__caption">NEWS HUB · THE WORLD, IN FOCUS</span>
                            </div>
                        <?php endif; ?>
                        </a>
                    </div>
                    <div class="featured-story__copy">
                        <?php if (!empty($featuredArticle['category_name'])): ?>
                            <p class="story-category"><?= $escape($featuredArticle['category_name']) ?></p>
                        <?php endif; ?>
                        <h2><a href="<?= $escape($baseUrl . '/FrontendArticleController/show/' . (int)$featuredArticle['id']) ?>"><?= $escape($featuredArticle['title'] ?? '') ?></a></h2>
                        <?php if (!empty($featuredArticle['summary'])): ?>
                            <p class="featured-story__summary"><?= nl2br($escape($featuredArticle['summary'])) ?></p>
                        <?php endif; ?>
                        <?php if ($publishedDate($featuredArticle) !== ''): ?>
                            <time class="story-date" datetime="<?= $escape($featuredArticle['created_at'] ?? '') ?>"><?= $escape($publishedDate($featuredArticle)) ?></time>
                        <?php endif; ?>
                    </div>
                </article>
            <?php else: ?>
                <div class="empty-feature" role="status">
                    <div class="empty-feature__mark" aria-hidden="true">NEWS<br>HUB</div>
                    <div>
                        <h2>Chưa có bài viết đã xuất bản</h2>
                        <p>Tin nổi bật sẽ xuất hiện tại đây khi có bài viết được xuất bản.</p>
                    </div>
                </div>
            <?php endif; ?>
        </section>

        <?php if ($featuredArticles !== []): ?>
            <section class="featured-supporting" aria-labelledby="featured-supporting-heading">
                <h2 id="featured-supporting-heading" class="visually-hidden">Tin nổi bật khác</h2>
                <?php foreach ($featuredArticles as $article): ?>
                    <article><p class="story-category"><?= $escape($article['category_name'] ?? '') ?></p>
                        <h3><a href="<?= $escape($baseUrl . '/FrontendArticleController/show/' . (int)$article['id']) ?>"><?= $escape($article['title'] ?? '') ?></a></h3>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

        <section id="latest" class="latest-section" aria-labelledby="latest-heading">
            <div class="section-heading section-heading--latest">
                <div>
                    <h2 id="latest-heading">
                        <?= $selectedCategoryId === null ? 'Tin mới nhất' : 'Tin mới theo danh mục' ?>
                    </h2>
                </div>
                <?php if ($selectedCategoryId !== null): ?>
                    <a class="clear-filter" href="<?= $escape($baseUrl . '/') ?>">Xem tất cả tin</a>
                <?php endif; ?>
            </div>

            <?php if (!empty($latestArticles)): ?>
                <div class="latest-list">
                    <?php foreach ($latestArticles as $article): ?>
                        <article class="latest-story">
                            <div class="latest-story__media">
                                <?php $latestImage = $articleImageUrl($article); ?>
                                <?php if ($latestImage !== null): ?>
                                    <img src="<?= $latestImage ?>" width="800" height="600" alt="Ảnh đại diện: <?= $escape($article['title'] ?? '') ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="latest-story__placeholder" role="img" aria-label="Bài viết chưa có ảnh đại diện">
                                        <span>NEWS HUB</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="latest-story__body">
                                <?php if (!empty($article['category_name'])): ?>
                                    <p class="story-category"><?= $escape($article['category_name']) ?></p>
                                <?php endif; ?>
                                <h3><a href="<?= $escape($baseUrl . '/FrontendArticleController/show/' . (int)$article['id']) ?>"><?= $escape($article['title'] ?? '') ?></a></h3>
                                <?php if (!empty($article['summary'])): ?>
                                    <p class="latest-story__summary"><?= $escape($article['summary']) ?></p>
                                <?php endif; ?>
                                <?php if ($publishedDate($article) !== ''): ?>
                                    <time class="story-date" datetime="<?= $escape($article['created_at'] ?? '') ?>"><?= $escape($publishedDate($article)) ?></time>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php elseif (is_array($featuredArticle)): ?>
                <p class="latest-empty">Chưa có thêm bài viết đã xuất bản trong danh mục này.</p>
            <?php else: ?>
                <p class="latest-empty">Các bài viết mới sẽ xuất hiện tại đây sau khi được xuất bản.</p>
            <?php endif; ?>
        </section>
    </div>
</main>
<?php
$pageContent = (string)ob_get_clean();
require __DIR__ . '/../layouts/frontend.php';
