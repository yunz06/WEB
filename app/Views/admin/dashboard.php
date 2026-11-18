<?php
$data = is_array($data ?? null) ? $data : [];
$e = static fn($v): string => htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$s = $data['stats'] ?? [];
ob_start();
?>
<h1 class="h2 mb-4">Tổng quan</h1>
<div class="row g-3 mb-4">
<?php foreach (['articles'=>'Tổng bài viết','published'=>'Đã xuất bản','drafts'=>'Bản nháp','categories'=>'Danh mục','users'=>'Tài khoản','pending_comments'=>'Chờ duyệt'] as $key=>$label): ?>
<div class="col-6 col-lg-4 col-xl-2"><section class="card h-100"><div class="card-body"><p class="text-secondary mb-2"><?= $e($label) ?></p><strong class="fs-3"><?= (int)($s[$key] ?? 0) ?></strong></div></section></div>
<?php endforeach; ?></div>
<section class="card"><div class="card-header fw-semibold">Bài viết gần đây</div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Tiêu đề</th><th>Danh mục</th><th>Trạng thái</th><th></th></tr></thead><tbody>
<?php foreach (($data['recentArticles'] ?? []) as $article): ?><tr><td><?= $e($article['title']) ?></td><td><?= $e($article['category_name'] ?? '') ?></td><td><?= $e($article['status']) ?></td><td><a href="<?= $e($data['baseUrl'] . '/ArticleController/edit/' . (int)$article['id']) ?>">Sửa</a></td></tr><?php endforeach; ?>
</tbody></table></div></section>
<?php $pageContent = (string)ob_get_clean(); require __DIR__ . '/../layouts/admin.php';
