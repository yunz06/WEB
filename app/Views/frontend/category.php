<?php
$data = is_array($data ?? null) ? $data : [];
$e = static fn($v): string => htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = (string)($data['baseUrl'] ?? '');
$category = $data['category'] ?? null;
$articles = $data['articles'] ?? [];
ob_start();
?>
<main id="main-content" class="news-home"><div class="container py-4">
    <?php if (!$category): ?><h1>Không tìm thấy chuyên mục</h1><p>Chuyên mục không tồn tại hoặc đã tạm ẩn.</p>
    <?php else: ?>
        <header class="section-heading"><h1><?= $e($category['name']) ?></h1></header>
        <?php if (!empty($category['description'])): ?><p><?= $e($category['description']) ?></p><?php endif; ?>
        <?php if ($articles): ?><div class="latest-list">
            <?php foreach ($articles as $article): ?>
                <article class="latest-story"><a class="latest-story__media" href="<?= $e($baseUrl . '/FrontendArticleController/show/' . (int)$article['id']) ?>">
                    <?php if (!empty($article['thumbnail']) && is_file(__DIR__ . '/../../../public/uploads/articles/' . basename($article['thumbnail']))): ?>
                        <img src="<?= $e($baseUrl . '/public/uploads/articles/' . rawurlencode(basename($article['thumbnail']))) ?>" width="800" height="600" alt="<?= $e($article['title']) ?>" loading="lazy">
                    <?php else: ?><span class="latest-story__placeholder">NEWS HUB</span><?php endif; ?></a>
                    <div class="latest-story__body"><h2 class="h4"><a href="<?= $e($baseUrl . '/FrontendArticleController/show/' . (int)$article['id']) ?>"><?= $e($article['title']) ?></a></h2>
                    <p><?= $e($article['summary'] ?? '') ?></p><time><?= $e($article['published_at'] ?? '') ?></time></div>
                </article>
            <?php endforeach; ?>
        </div><?php else: ?><p role="status">Chuyên mục chưa có bài viết đã xuất bản.</p><?php endif; ?>
        <?php if (($data['totalPages'] ?? 1) > 1): ?><nav aria-label="Phân trang"><ul class="pagination">
            <?php for ($i=1; $i <= (int)$data['totalPages']; $i++): ?><li class="page-item<?= $i === (int)$data['page'] ? ' active' : '' ?>"><a class="page-link" href="<?= $e($baseUrl . '/PublicCategoryController/show/' . rawurlencode($category['slug']) . '/' . $i) ?>"><?= $i ?></a></li><?php endfor; ?>
        </ul></nav><?php endif; ?>
    <?php endif; ?>
</div></main>
<?php $pageContent = (string)ob_get_clean(); require __DIR__ . '/../layouts/frontend.php';
