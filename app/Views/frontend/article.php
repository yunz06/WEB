<?php
$data = is_array($data ?? null) ? $data : [];
$baseUrl = (string)($data['baseUrl'] ?? '');
$categories = $data['categories'] ?? [];
$article = $data['article'] ?? null;
$escape = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$homeUrl = $baseUrl . '/';

$articleImageUrl = static function (?string $path) use ($baseUrl, $escape): ?string {
    $path = trim((string)$path);
    if ($path === '') {
        return null;
    }
    $fileName = basename($path);
    if (!is_file(__DIR__ . '/../../../public/uploads/articles/' . $fileName)) return null;
    return $escape($baseUrl . '/public/uploads/articles/' . rawurlencode($fileName));
};

$publishedDate = static function (array $article): ?string {
    $timestamp = strtotime((string)($article['published_at'] ?? $article['created_at'] ?? ''));
    return $timestamp === false ? null : date('d/m/Y', $timestamp);
};

ob_start();
?>
<main id="main-content" class="news-home article-page">
    <div class="container">
        <?php if (!is_array($article)): ?>
            <section class="article-not-found" role="status" aria-labelledby="article-not-found-heading">
                <h1 id="article-not-found-heading">Không tìm thấy bài viết</h1>
                <p>Bài viết không tồn tại hoặc chưa được xuất bản.</p>
                <a class="article-back-link" href="<?= $escape($homeUrl) ?>">Quay lại trang chủ</a>
            </section>
        <?php else: ?>
            <?php
            $thumbnailUrl = $articleImageUrl($article['thumbnail'] ?? null);
            $categoryId = (int)($article['category_id'] ?? 0);
            $categorySlug = trim((string)($article['category_slug'] ?? ''));
            $categoryUrl = $categoryId > 0
                ? $baseUrl . '/PublicCategoryController/show/' . rawurlencode($categorySlug)
                : $homeUrl;
            $date = $publishedDate($article);
            $images = is_array($article['images'] ?? null) ? $article['images'] : [];
            require_once __DIR__ . '/../../Core/HtmlSanitizer.php';
            $safeContent = HtmlSanitizer::sanitizeArticleContent((string)($article['content'] ?? ''));
            ?>
            <article class="article-detail">
                <nav aria-label="Đường dẫn trang">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= $escape($homeUrl) ?>">Trang chủ</a></li>
                        <?php if ($categoryId > 0): ?><li class="breadcrumb-item"><a href="<?= $escape($categoryUrl) ?>"><?= $escape($article['category_name'] ?? 'Chuyên mục') ?></a></li><?php endif; ?>
                        <li class="breadcrumb-item active" aria-current="page"><?= $escape($article['title'] ?? '') ?></li>
                    </ol>
                </nav>
                <a class="article-back-link" href="<?= $escape($categoryUrl) ?>">
                    Quay lại <?= $escape($article['category_name'] ?? 'danh mục') ?>
                </a>

                <header class="article-detail__header">
                    <?php if (!empty($article['category_name'])): ?>
                        <p class="story-category"><?= $escape($article['category_name']) ?></p>
                    <?php endif; ?>
                    <h1 class="article-detail__title"><?= $escape($article['title'] ?? '') ?></h1>
                    <?php if ($date !== null): ?>
                        <time class="story-date" datetime="<?= $escape($article['published_at'] ?? $article['created_at'] ?? '') ?>"><?= $escape($date) ?></time>
                    <?php endif; ?>
                    <?php if (!empty($article['author_name'])): ?><p class="article-author">Tác giả: <?= $escape($article['author_name']) ?></p><?php endif; ?>
                </header>

                <?php if ($thumbnailUrl !== null): ?>
                    <figure class="article-detail__hero">
                        <img src="<?= $thumbnailUrl ?>" width="1200" height="800" alt="Ảnh đại diện: <?= $escape($article['title'] ?? '') ?>" fetchpriority="high">
                    </figure>
                <?php endif; ?>

                <div class="article-detail__content">
                    <?php if ($safeContent !== ''): ?>
                        <?= $safeContent ?>
                    <?php else: ?>
                        <p>Bài viết chưa có nội dung.</p>
                    <?php endif; ?>
                </div>

                <?php if ($images !== []): ?>
                    <section class="article-detail__gallery" aria-label="Ảnh trong bài viết">
                        <?php foreach ($images as $image): ?>
                            <?php $imageUrl = $articleImageUrl($image['image_path'] ?? null); ?>
                            <?php if ($imageUrl !== null && is_file(__DIR__ . '/../../../public/uploads/articles/' . basename((string)$image['image_path']))): ?>
                                <figure>
                                    <img src="<?= $imageUrl ?>" width="800" height="600" alt="<?= $escape($image['alt_text'] ?: ('Ảnh nội dung: ' . ($article['title'] ?? ''))) ?>" loading="lazy">
                                    <?php if (!empty($image['caption'])): ?><figcaption><?= $escape($image['caption']) ?></figcaption><?php endif; ?>
                                </figure>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </section>
                <?php endif; ?>

                <footer class="article-detail__footer">
                    <a class="article-back-link" href="<?= $escape($categoryUrl) ?>">Quay lại danh mục</a>
                    <a class="article-back-link" href="<?= $escape($homeUrl) ?>">Về trang chủ</a>
                </footer>
                <?php if (!empty($data['relatedArticles'])): ?>
                    <section class="related-stories" aria-labelledby="related-stories-heading">
                        <h2 id="related-stories-heading">Bài viết liên quan</h2>
                        <div class="related-stories__list">
                            <?php foreach ($data['relatedArticles'] as $relatedArticle): ?>
                                <article>
                                    <p class="story-category"><?= $escape($relatedArticle['category_name'] ?? '') ?></p>
                                    <h3><a href="<?= $escape($baseUrl . '/FrontendArticleController/show/' . (int)$relatedArticle['id']) ?>"><?= $escape($relatedArticle['title'] ?? '') ?></a></h3>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
                <section class="article-comments" aria-labelledby="comments-heading">
                    <h2 id="comments-heading">Bình luận</h2>
                    <?php if (!empty($_SESSION['flash_success'])): ?><p class="alert alert-success" role="status"><?= $escape($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></p><?php endif; ?>
                    <?php if (!empty($_SESSION['flash_error'])): ?><p class="alert alert-danger" role="alert"><?= $escape($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></p><?php endif; ?>
                    <?php foreach (($data['comments'] ?? []) as $comment): ?>
                        <article class="comment-entry"><p><strong><?= $escape($comment['author_name']) ?></strong>
                            <time datetime="<?= $escape($comment['created_at']) ?>"><?= $escape($comment['created_at']) ?></time></p>
                            <p><?= nl2br($escape($comment['content'])) ?></p></article>
                    <?php endforeach; ?>
                    <?php if (isset($_SESSION['user']['id'])): ?>
                        <form method="post" action="<?= $escape($baseUrl . '/CommentController/store/' . (int)$article['id']) ?>">
                            <input type="hidden" name="csrf_token" value="<?= $escape($data['csrf_token'] ?? '') ?>">
                            <label for="comment-content">Thêm bình luận</label>
                            <textarea class="form-control" id="comment-content" name="content" maxlength="3000" required></textarea>
                            <button class="btn btn-primary mt-2" type="submit">Gửi bình luận</button>
                        </form>
                        <p class="comment-guidance">Bình luận mới sẽ chờ duyệt. Theo dõi trạng thái tại <a href="<?= $escape($baseUrl . '/AccountController/index') ?>">Tài khoản</a>.</p>
                    <?php else: ?>
                        <p class="comment-guidance">Đăng nhập để gửi bình luận. Bình luận mới sẽ chờ duyệt; sau đó bạn có thể theo dõi trạng thái trong tài khoản.</p>
                        <p><a href="<?= $escape($baseUrl . '/AuthController/login') ?>">Đăng nhập</a>
                            <span aria-hidden="true">·</span>
                            <a href="<?= $escape($baseUrl . '/AuthController/register') ?>">Tạo tài khoản</a></p>
                    <?php endif; ?>
                </section>
            </article>
        <?php endif; ?>
    </div>
</main>
<?php
$pageContent = (string)ob_get_clean();
require __DIR__ . '/../layouts/frontend.php';
