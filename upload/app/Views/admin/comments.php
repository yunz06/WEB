<?php
$data = is_array($data ?? null) ? $data : [];
$e = static fn($v): string => htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
ob_start();
?>
<h1 class="h2 mb-3">Quản lý bình luận</h1>
<nav class="mb-3 d-flex gap-3"><a href="<?= $e($data['baseUrl'] . '/CommentController/index') ?>">Tất cả</a><?php foreach (['pending'=>'Chờ duyệt','approved'=>'Đã duyệt','hidden'=>'Đã ẩn'] as $status=>$label): ?><a href="<?= $e($data['baseUrl'] . '/CommentController/index?status=' . $status) ?>"><?= $e($label) ?></a><?php endforeach; ?></nav>
<div class="table-responsive"><table class="table table-striped align-middle"><thead><tr><th>Bài viết</th><th>Người gửi</th><th>Nội dung</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>
<?php foreach (($data['comments'] ?? []) as $comment): ?><tr><td><?= $e($comment['article_title']) ?></td><td><?= $e($comment['user_name']) ?></td><td><?= nl2br($e($comment['content'])) ?></td><td><?= $e($comment['status']) ?></td><td>
<form class="d-inline-flex gap-1" method="post" action="<?= $e($data['baseUrl'] . '/CommentController/updateStatus/' . (int)$comment['id']) ?>"><input type="hidden" name="csrf_token" value="<?= $e($data['csrf_token']) ?>"><select class="form-select form-select-sm" name="status" aria-label="Trạng thái bình luận"><option value="pending">Chờ</option><option value="approved">Duyệt</option><option value="hidden">Ẩn</option></select><button class="btn btn-sm btn-primary">Lưu</button></form>
<form class="d-inline" method="post" action="<?= $e($data['baseUrl'] . '/CommentController/delete/' . (int)$comment['id']) ?>" data-confirm="Bạn có chắc chắn muốn xóa bình luận này?"><input type="hidden" name="csrf_token" value="<?= $e($data['csrf_token']) ?>"><button class="btn btn-sm btn-danger" type="submit">Xóa</button></form></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php if (($data['totalPages'] ?? 1) > 1): ?><nav aria-label="Phân trang bình luận"><ul class="pagination"><?php for ($i=1;$i<=$data['totalPages'];$i++): ?><li class="page-item<?= $i===$data['page']?' active':'' ?>"><a class="page-link" href="<?= $e($data['baseUrl'] . '/CommentController/index/' . $i . (($data['status'] ?? '') !== '' ? '?status=' . rawurlencode($data['status']) : '')) ?>"><?= $i ?></a></li><?php endfor; ?></ul></nav><?php endif; ?>
<?php $pageContent = (string)ob_get_clean(); require __DIR__ . '/../layouts/admin.php';
