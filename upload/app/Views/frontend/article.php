<?php
$data = is_array($data ?? null) ? $data : [];
$baseUrl = (string)($data['baseUrl'] ?? '');
$article = $data['article'] ?? null;
$escape = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$homeUrl = $baseUrl . '/';
require_once __DIR__ . '/../../Core/EditorialSource.php';
require_once __DIR__ . '/../../Core/HtmlSanitizer.php';

$articleImageUrl = static function (?string $path) use ($baseUrl, $escape): ?string {
    $path = trim((string)$path);
    if ($path === '') return null;
    $fileName = basename($path);
    if (!is_file(__DIR__ . '/../../../public/uploads/articles/' . $fileName)) return null;
    return $escape($baseUrl . '/public/uploads/articles/' . rawurlencode($fileName));
};

$publishedDate = static function (array $item): ?string {
    $timestamp = strtotime((string)($item['published_at'] ?? $item['created_at'] ?? ''));
    return $timestamp === false ? null : date('d/m/Y', $timestamp);
};

$storyFacts = [
    'sample-01-thoi-su' => [
        ['value' => '03', 'label' => 'lần Việt Nam đăng cai APEC'],
        ['value' => '21', 'label' => 'nền kinh tế thành viên'],
        ['value' => '2027', 'label' => 'năm Việt Nam làm chủ nhà'],
    ],
    'sample-05-the-gioi' => [
        ['value' => '19/11', 'label' => 'cuộc gặp bên lề APEC'],
        ['value' => '2027', 'label' => 'kỳ APEC Việt Nam đăng cai'],
        ['value' => 'Chưa có', 'label' => 'thỏa thuận thương mại mới được công bố'],
    ],
    'sample-07-tai-chinh' => [
        ['value' => 'Philippines', 'label' => 'thị trường được đề cập'],
        ['value' => 'Dài hạn', 'label' => 'định hướng nguồn cung được đề xuất'],
        ['value' => 'Chưa công bố', 'label' => 'sản lượng và điều khoản giao dịch'],
    ],
    'sample-03-moi-truong' => [
        ['value' => '11,5 triệu', 'label' => 'việc làm có thể chịu tác động'],
        ['value' => '20,8%', 'label' => 'tổng số việc làm tại Việt Nam'],
        ['value' => 'Không đồng nghĩa', 'label' => 'với số việc làm chắc chắn bị thay thế'],
    ],
    'sample-09-cong-nghe' => [
        ['value' => '108', 'label' => 'vị trí doanh nghiệp trên bản đồ'],
        ['value' => '11', 'label' => 'lĩnh vực công nghệ số'],
        ['value' => '51 / 33', 'label' => 'danh hiệu Top 10 / doanh nghiệp'],
    ],
    'sample-33-doi-song' => [
        ['value' => '19/11', 'label' => 'thông tin được công bố bên lề APEC'],
        ['value' => 'Việt Nam – Nga', 'label' => 'hai bên thống nhất tiếp tục hợp tác'],
        ['value' => 'Chưa công bố', 'label' => 'dự án, vốn đầu tư và công suất'],
    ],
    'sample-13-the-thao' => [
        ['value' => '48', 'label' => 'đội tuyển dự vòng chung kết'],
        ['value' => '19/11–13/12', 'label' => 'thời gian giải đấu năm 2026'],
        ['value' => 'Bỉ · New Zealand · Mali', 'label' => 'các đối thủ vòng bảng của Việt Nam'],
    ],
    'sample-21-giao-duc' => [
        ['value' => '99', 'label' => 'thủ khoa được tuyên dương năm 2026'],
        ['value' => '2.450', 'label' => 'thủ khoa được vinh danh trong 24 năm'],
        ['value' => '18/11', 'label' => 'thời điểm diễn ra buổi lễ'],
    ],
];

ob_start();
?>
<main id="main-content" class="article-stage">
    <div class="container article-page-shell">
        <?php if (!is_array($article)): ?>
            <section class="article-not-found" role="status" aria-labelledby="article-not-found-heading">
                <span class="article-section-index">NEWS HUB / 404</span>
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
            $rawContent = (string)($article['content'] ?? '');
            $sourceInfo = EditorialSource::fromContent($rawContent);
            // Nguồn được hiển thị một lần ở cuối bài, không chèn badge lặp trong tiêu đề/thẻ tin.
            $bodyContent = preg_replace('~<blockquote\b[^>]*>.*?</blockquote>~is', '', $rawContent, 1) ?? $rawContent;
            $safeContent = HtmlSanitizer::sanitizeArticleContent($bodyContent);
            $plainText = trim(preg_replace('/\s+/u', ' ', strip_tags($bodyContent)) ?? '');
            preg_match_all('/[\p{L}\p{N}]+/u', $plainText, $wordMatches);
            $readMinutes = max(2, (int)ceil(count($wordMatches[0] ?? []) / 220));
            $slug = (string)($article['slug'] ?? '');
            $facts = $storyFacts[$slug] ?? [];
            $summary = trim((string)($article['summary'] ?? ''));
            $relatedArticles = is_array($data['relatedArticles'] ?? null) ? $data['relatedArticles'] : [];
            ?>
            <nav class="article-breadcrumb" aria-label="Đường dẫn trang">
                <a href="<?= $escape($homeUrl) ?>">Trang chủ</a>
                <span aria-hidden="true">/</span>
                <?php if ($categoryId > 0): ?><a href="<?= $escape($categoryUrl) ?>"><?= $escape($article['category_name'] ?? 'Chuyên mục') ?></a><span aria-hidden="true">/</span><?php endif; ?>
                <span aria-current="page"><?= $escape($article['title'] ?? '') ?></span>
            </nav>

            <article class="article-detail article-feature">
                <header class="article-title-block">
                    <div class="article-title-kicker">
                        <span class="article-category-label"><?= $escape($article['category_name'] ?? 'Tin tức') ?></span>
                        <span class="article-edition">NEWS HUB <i></i> GÓC NHÌN</span>
                    </div>
                    <h1 class="article-detail__title"><?= $escape($article['title'] ?? '') ?></h1>
                    <?php if ($summary !== ''): ?><p class="article-standfirst"><?= $escape($summary) ?></p><?php endif; ?>
                    <div class="article-byline-row">
                        <div class="article-byline-brand" aria-hidden="true">N</div>
                        <div class="article-byline-copy"><strong>NEWS HUB</strong><span>Ban biên tập</span></div>
                        <span class="article-meta-divider" aria-hidden="true"></span>
                        <?php if ($date !== null): ?><time class="article-date" datetime="<?= $escape($article['published_at'] ?? $article['created_at'] ?? '') ?>"><?= $escape($date) ?></time><?php endif; ?>
                        <span class="article-reading-time"><?= $readMinutes ?> phút đọc</span>
                    </div>
                </header>

                <?php if ($thumbnailUrl !== null): ?>
                    <figure class="article-detail__hero">
                        <img src="<?= $thumbnailUrl ?>" width="1600" height="800" alt="Ảnh minh họa: <?= $escape($article['title'] ?? '') ?>" fetchpriority="high">
                        <figcaption><span>Ảnh minh họa</span></figcaption>
                    </figure>
                <?php endif; ?>

                <div class="article-reading-layout">
                    <div class="article-main-column">
                        <div class="article-detail__content">
                            <?php if ($safeContent !== ''): ?>
                                <?= $safeContent ?>
                            <?php else: ?>
                                <p>Bài viết chưa có nội dung.</p>
                            <?php endif; ?>
                        </div>

                        <?php if ($sourceInfo !== null): ?>
                            <p class="article-source-line">Nguồn đối chiếu: <a href="<?= $escape($sourceInfo['url']) ?>" target="_blank" rel="noopener noreferrer"><?= $escape($sourceInfo['publisher']) ?> <span aria-hidden="true">↗</span></a></p>
                        <?php endif; ?>
                    </div>

                    <aside class="article-side-column" aria-label="Thông tin liên quan">
                        <?php if ($facts !== []): ?>
                            <section class="story-facts-card" aria-labelledby="story-facts-title">
                                <div class="side-card-heading"><span>01</span><h2 id="story-facts-title">Những con số cần biết</h2></div>
                                <?php foreach ($facts as $index => $fact): ?>
                                    <div class="story-fact-row">
                                        <strong><?= $escape($fact['value']) ?></strong>
                                        <span><?= $escape($fact['label']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </section>
                        <?php endif; ?>

                        <?php if ($relatedArticles !== []): ?>
                            <section class="article-related-card" aria-labelledby="related-stories-heading">
                                <div class="side-card-heading"><span>02</span><h2 id="related-stories-heading">Đọc tiếp</h2></div>
                                <?php foreach ($relatedArticles as $relatedArticle): ?>
                                    <?php $relatedImage = $articleImageUrl($relatedArticle['thumbnail'] ?? null); ?>
                                    <article class="article-related-item">
                                        <a class="article-related-image" href="<?= $escape($baseUrl . '/FrontendArticleController/show/' . (int)$relatedArticle['id']) ?>">
                                            <?php if ($relatedImage !== null): ?><img src="<?= $relatedImage ?>" alt="Ảnh minh họa: <?= $escape($relatedArticle['title'] ?? '') ?>" loading="lazy"><?php else: ?><span>NEWS HUB</span><?php endif; ?>
                                        </a>
                                        <div><small><?= $escape($relatedArticle['category_name'] ?? '') ?></small><h3><a href="<?= $escape($baseUrl . '/FrontendArticleController/show/' . (int)$relatedArticle['id']) ?>"><?= $escape($relatedArticle['title'] ?? '') ?></a></h3></div>
                                    </article>
                                <?php endforeach; ?>
                            </section>
                        <?php endif; ?>
                    </aside>
                </div>

                <?php if ($images !== []): ?>
                    <section class="article-detail__gallery" aria-label="Ảnh trong bài viết">
                        <div class="article-gallery-heading"><span>HÌNH ẢNH</span><h2>Trong bài viết</h2></div>
                        <div class="article-gallery-grid">
                            <?php foreach ($images as $image): ?>
                                <?php $imageUrl = $articleImageUrl($image['image_path'] ?? null); ?>
                                <?php if ($imageUrl !== null): ?>
                                    <figure>
                                        <img src="<?= $imageUrl ?>" width="800" height="600" alt="<?= $escape($image['alt_text'] ?: ('Ảnh nội dung: ' . ($article['title'] ?? ''))) ?>" loading="lazy">
                                        <?php if (!empty($image['caption'])): ?><figcaption><?= $escape($image['caption']) ?></figcaption><?php endif; ?>
                                    </figure>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <section class="article-comments" aria-labelledby="comments-heading">
                    <div class="comments-heading-row"><span>GÓC ĐỌC GIẢ</span><h2 id="comments-heading">Bạn nghĩ sao?</h2></div>
                    <?php if (!empty($_SESSION['flash_success'])): ?><p class="alert alert-success" role="status"><?= $escape($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></p><?php endif; ?>
                    <?php if (!empty($_SESSION['flash_error'])): ?><p class="alert alert-danger" role="alert"><?= $escape($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></p><?php endif; ?>
                    <?php foreach (($data['comments'] ?? []) as $comment): ?>
                        <article class="comment-entry"><p><strong><?= $escape($comment['author_name']) ?></strong><time datetime="<?= $escape($comment['created_at']) ?>"><?= $escape($comment['created_at']) ?></time></p><p><?= nl2br($escape($comment['content'])) ?></p></article>
                    <?php endforeach; ?>
                    <?php if (isset($_SESSION['user']['id'])): ?>
                        <form class="article-comment-form" method="post" action="<?= $escape($baseUrl . '/CommentController/store/' . (int)$article['id']) ?>">
                            <input type="hidden" name="csrf_token" value="<?= $escape($data['csrf_token'] ?? '') ?>">
                            <label for="comment-content">Viết bình luận</label>
                            <textarea class="form-control" id="comment-content" name="content" maxlength="3000" placeholder="Chia sẻ góc nhìn của bạn…" required></textarea>
                            <button class="btn btn-primary mt-2" type="submit">Gửi bình luận</button>
                        </form>
                        <p class="comment-guidance">Bình luận sẽ được kiểm duyệt trước khi hiển thị công khai.</p>
                    <?php else: ?>
                        <p class="comment-guidance">Đăng nhập để tham gia trao đổi về bài viết.</p>
                        <div class="comment-auth-links"><a href="<?= $escape($baseUrl . '/AuthController/login') ?>">Đăng nhập</a><a href="<?= $escape($baseUrl . '/AuthController/register') ?>">Tạo tài khoản</a></div>
                    <?php endif; ?>
                </section>

                <footer class="article-detail__footer"><a class="article-back-link" href="<?= $escape($categoryUrl) ?>">← Quay lại <?= $escape($article['category_name'] ?? 'danh mục') ?></a><a class="article-back-link" href="<?= $escape($homeUrl) ?>">Về trang chủ</a></footer>
            </article>
        <?php endif; ?>
    </div>
</main>
<?php
$pageContent = (string)ob_get_clean();
require __DIR__ . '/../layouts/frontend.php';
