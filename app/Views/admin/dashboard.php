<?php
$data = is_array($data ?? null) ? $data : [];
$e = static fn($v): string => htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$s = $data['stats'] ?? [];
$baseUrl = rtrim((string)($data['baseUrl'] ?? ''), '/');
ob_start();
?>
<header class="admin-page-heading">
    <div><h1 class="h2 mb-1">Tổng quan</h1><p class="text-secondary mb-0">Tình hình nội dung và hoạt động cần xử lý.</p></div>
</header>
<nav class="dashboard-quick-actions" aria-label="Tác vụ nhanh">
    <a class="btn btn-primary" href="<?= $e($baseUrl . '/ArticleController/index#articleForm') ?>">Soạn bài viết</a>
    <a class="btn btn-outline-primary" href="<?= $e($baseUrl . '/CommentController/index?status=pending') ?>">Duyệt bình luận</a>
    <a class="btn btn-outline-primary" href="<?= $e($baseUrl . '/CategoryController/index') ?>">Quản lý danh mục</a>
    <a class="btn btn-outline-primary" href="<?= $e($baseUrl . '/UserController/index') ?>">Quản lý tài khoản</a>
</nav>
<div class="row g-3 mb-4">
<?php foreach (['articles'=>'Tổng bài viết','published'=>'Đã xuất bản','drafts'=>'Bản nháp','categories'=>'Danh mục','users'=>'Tài khoản','pending_comments'=>'Chờ duyệt'] as $key=>$label): ?>
<div class="col-6 col-lg-4 col-xl-2"><section class="card h-100"><div class="card-body"><p class="text-secondary mb-2"><?= $e($label) ?></p><strong class="fs-3"><?= (int)($s[$key] ?? 0) ?></strong><?php if ($key === 'pending_comments'): ?><a class="dashboard-stat-link" href="<?= $e($baseUrl . '/CommentController/index?status=pending') ?>">Mở hàng chờ</a><?php endif; ?></div></section></div>
<?php endforeach; ?></div>
<div class="dashboard-work-queues">
    <section class="card" aria-labelledby="recent-articles-heading">
        <div class="card-header d-flex align-items-center justify-content-between gap-3"><h2 id="recent-articles-heading" class="h6 mb-0">Bài viết gần đây</h2><a href="<?= $e($baseUrl . '/ArticleController/index') ?>">Tất cả bài viết</a></div>
        <div class="table-responsive"><table class="table mb-0"><thead><tr><th>Tiêu đề</th><th>Danh mục</th><th>Trạng thái</th><th><span class="visually-hidden">Thao tác</span></th></tr></thead><tbody>
        <?php foreach (($data['recentArticles'] ?? []) as $article): ?><tr><td><?= $e($article['title']) ?></td><td><?= $e($article['category_name'] ?? '') ?></td><td><?= $e($article['status']) ?></td><td><a href="<?= $e($baseUrl . '/ArticleController/edit/' . (int)$article['id']) ?>">Sửa</a></td></tr><?php endforeach; ?>
        <?php if (empty($data['recentArticles'])): ?><tr><td colspan="4" class="text-secondary">Chưa có bài viết.</td></tr><?php endif; ?>
        </tbody></table></div>
    </section>
    <section class="card" aria-labelledby="pending-comments-heading">
        <div class="card-header d-flex align-items-center justify-content-between gap-3"><h2 id="pending-comments-heading" class="h6 mb-0">Bình luận chờ duyệt</h2><a href="<?= $e($baseUrl . '/CommentController/index?status=pending') ?>">Mở hàng chờ</a></div>
        <div class="dashboard-pending-list">
            <?php foreach (($data['pendingComments'] ?? []) as $comment): ?>
                <article class="dashboard-pending-item">
                    <p><strong><?= $e($comment['user_name'] ?? 'Độc giả') ?></strong> · <?= $e($comment['article_title'] ?? 'Bài viết') ?></p>
                    <p><?= $e(mb_substr((string)($comment['content'] ?? ''), 0, 180, 'UTF-8')) ?></p>
                </article>
            <?php endforeach; ?>
            <?php if (empty($data['pendingComments'])): ?><p class="dashboard-queue-empty">Không có bình luận nào đang chờ duyệt.</p><?php endif; ?>
        </div>
    </section>
</div>
<?php $pageContent = (string)ob_get_clean(); require __DIR__ . '/../layouts/admin.php';
