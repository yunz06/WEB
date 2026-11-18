<?php
$data = is_array($data ?? null) ? $data : [];
$e = static fn($v): string => htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = (string)($data['baseUrl'] ?? '');
ob_start();
?>
<main id="main-content" class="news-home"><div class="container py-4">
    <h1>Kết quả tìm kiếm</h1>
    <form action="<?= $e($baseUrl . '/SearchController/index') ?>" method="get" class="mb-4">
        <label class="form-label" for="search-page-query">Tìm theo tiêu đề bài viết</label>
        <div class="input-group"><input class="form-control" id="search-page-query" name="q" value="<?= $e($data['query'] ?? '') ?>" maxlength="120"><button class="btn btn-primary">Tìm kiếm</button></div>
    </form>
    <?php if (($data['query'] ?? '') === ''): ?><p>Nhập từ khóa để tìm bài viết.</p>
    <?php elseif (!$data['articles']): ?><p role="status">Không tìm thấy bài viết phù hợp với “<?= $e($data['query']) ?>”.</p>
    <?php else: ?><p><?= (int)$data['totalResults'] ?> kết quả cho “<?= $e($data['query']) ?>”</p><div class="latest-list">
        <?php foreach ($data['articles'] as $article): ?><article class="latest-story"><div class="latest-story__body"><p class="story-category"><?= $e($article['category_name']) ?></p>
            <h2 class="h4"><a href="<?= $e($baseUrl . '/FrontendArticleController/show/' . (int)$article['id']) ?>"><?= $e($article['title']) ?></a></h2><p><?= $e($article['summary'] ?? '') ?></p></div></article><?php endforeach; ?>
    </div>
    <?php if ($data['totalPages'] > 1): ?><nav aria-label="Phân trang"><ul class="pagination"><?php for ($i=1;$i<=$data['totalPages'];$i++): ?><li class="page-item<?= $i===$data['page']?' active':'' ?>"><a class="page-link" href="<?= $e($baseUrl . '/SearchController/index/' . $i . '?q=' . rawurlencode($data['query'])) ?>"><?= $i ?></a></li><?php endfor; ?></ul></nav><?php endif; ?>
    <?php endif; ?>
</div></main>
<?php $pageContent = (string)ob_get_clean(); require __DIR__ . '/../layouts/frontend.php';
