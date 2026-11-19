<?php
$data = is_array($data ?? null) ? $data : [];
$e = static fn($v): string => htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = (string)($data['baseUrl'] ?? '');
require_once __DIR__ . '/../../Core/EditorialSource.php';
$imageUrl = static function (array $article) use ($baseUrl, $e): ?string {
    $filename = basename(trim((string)($article['thumbnail'] ?? '')));
    if ($filename === '' || !is_file(__DIR__ . '/../../../public/uploads/articles/' . $filename)) return null;
    return $e($baseUrl . '/public/uploads/articles/' . rawurlencode($filename));
};
ob_start();
?>
<main id="main-content" class="news-home"><div class="container py-4">
    <h1>Kết quả tìm kiếm</h1>
    <form action="<?= $e($baseUrl . '/SearchController/index') ?>" method="get" class="mb-4">
        <label class="form-label" for="search-page-query">Tìm theo tiêu đề bài viết</label>
        <div class="input-group"><input class="form-control" id="search-page-query" name="q" type="search" autocomplete="off" value="<?= $e($data['query'] ?? '') ?>" maxlength="120"><button class="btn btn-primary" type="submit">Tìm kiếm</button></div>
    </form>
    <?php if (($data['query'] ?? '') === ''): ?><p>Nhập từ khóa để tìm bài viết.</p>
    <?php elseif (!$data['articles']): ?><p role="status">Không tìm thấy bài viết phù hợp với “<?= $e($data['query']) ?>”.</p>
    <?php else: ?><p role="status"><?= (int)$data['totalResults'] ?> kết quả cho “<?= $e($data['query']) ?>”</p><div class="latest-list">
        <?php foreach ($data['articles'] as $article): ?><article class="latest-story">
            <a class="latest-story__media" href="<?= $e($baseUrl . '/FrontendArticleController/show/' . (int)$article['id']) ?>">
                <?php $thumbnail = $imageUrl($article); if ($thumbnail !== null): ?><img src="<?= $thumbnail ?>" width="800" height="600" alt="Ảnh bài viết: <?= $e($article['title']) ?>" loading="lazy">
                <?php else: ?><span class="latest-story__placeholder">NEWS HUB</span><?php endif; ?>
            </a>
            <div class="latest-story__body"><p class="story-category"><?= $e($article['category_name']) ?></p>
            <h2 class="h4"><a href="<?= $e($baseUrl . '/FrontendArticleController/show/' . (int)$article['id']) ?>"><?= $e($article['title']) ?></a></h2>
            <?php if (!empty($article['summary'])): ?><p><?= $e($article['summary']) ?></p><?php endif; ?>
                    <?php include __DIR__ . '/partials/source-chip.php'; ?></div></article><?php endforeach; ?>
    </div>
    <?php if ($data['totalPages'] > 1): ?><nav aria-label="Phân trang"><ul class="pagination"><?php for ($i=1;$i<=$data['totalPages'];$i++): ?><li class="page-item<?= $i===$data['page']?' active':'' ?>"><a class="page-link" href="<?= $e($baseUrl . '/SearchController/index/' . $i . '?q=' . rawurlencode($data['query'])) ?>"<?= $i===$data['page']?' aria-current="page"':'' ?>><?= $i ?></a></li><?php endfor; ?></ul></nav><?php endif; ?>
    <?php endif; ?>
</div></main>
<?php $pageContent = (string)ob_get_clean(); require __DIR__ . '/../layouts/frontend.php';
