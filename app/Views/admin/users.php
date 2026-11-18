<?php
$data = is_array($data ?? null) ? $data : [];
$e = static fn($v): string => htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
ob_start();
?>
<h1 class="h2 mb-3">Quản lý người dùng</h1>
<form class="row g-2 mb-3" method="get"><div class="col-sm-8"><label class="visually-hidden" for="user-query">Tìm tài khoản</label><input class="form-control" id="user-query" name="q" value="<?= $e($data['query'] ?? '') ?>" placeholder="Tên hoặc email…"></div><div class="col-sm-4"><button class="btn btn-outline-primary" type="submit">Tìm</button></div></form>
<div class="table-responsive"><table class="table table-striped align-middle"><thead><tr><th>Họ tên</th><th>Email</th><th>Vai trò</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>
<?php foreach (($data['users'] ?? []) as $user): ?><tr><td><?= $e($user['full_name']) ?></td><td><?= $e($user['email']) ?></td><td><?= $e($user['role_name']) ?></td><td><?= $e($user['status']) ?></td><td>
<a class="btn btn-sm btn-outline-primary" href="<?= $e($data['baseUrl'] . '/UserController/edit/' . (int)$user['id']) ?>">Sửa</a>
<?php if ((int)$user['id'] !== (int)($data['currentId'] ?? 0)): ?><form class="d-inline" method="post" action="<?= $e($data['baseUrl'] . '/UserController/toggleStatus/' . (int)$user['id'] . '/' . (int)($data['page'] ?? 1)) ?>"><input type="hidden" name="csrf_token" value="<?= $e($data['csrf_token']) ?>"><button class="btn btn-sm btn-outline-secondary"><?= $user['status']==='active'?'Khóa':'Mở khóa' ?></button></form><?php endif; ?>
</td></tr><?php endforeach; ?>
</tbody></table></div>
<?php if (!empty($data['editUser'])): $user=$data['editUser']; ?><section class="card mt-4"><div class="card-header">Sửa tài khoản #<?= (int)$user['id'] ?></div><div class="card-body"><form method="post" action="<?= $e($data['baseUrl'] . '/UserController/update/' . (int)$user['id']) ?>"><input type="hidden" name="csrf_token" value="<?= $e($data['csrf_token']) ?>">
<label class="form-label" for="full_name">Họ tên</label><input class="form-control mb-3" id="full_name" name="full_name" maxlength="150" value="<?= $e($user['full_name']) ?>" required>
<label class="form-label" for="email">Email</label><input class="form-control mb-3" id="email" name="email" type="email" maxlength="255" value="<?= $e($user['email']) ?>" required>
<label class="form-label" for="role_id">Vai trò</label><select class="form-select mb-3" id="role_id" name="role_id"><?php foreach (($data['roles'] ?? []) as $role): ?><option value="<?= (int)$role['id'] ?>" <?= (int)$user['role_id']===(int)$role['id']?'selected':'' ?>><?= $e($role['name']) ?></option><?php endforeach; ?></select><button class="btn btn-primary">Lưu thay đổi</button></form></div></section><?php endif; ?>
<?php if (($data['totalPages'] ?? 1) > 1): ?><nav aria-label="Phân trang người dùng"><ul class="pagination"><?php for ($i=1;$i<=$data['totalPages'];$i++): ?><li class="page-item<?= $i===$data['page']?' active':'' ?>"><a class="page-link" href="<?= $e($data['baseUrl'] . '/UserController/index/' . $i . (($data['query'] ?? '') !== '' ? '?q=' . rawurlencode($data['query']) : '')) ?>"><?= $i ?></a></li><?php endfor; ?></ul></nav><?php endif; ?>
<?php $pageContent = (string)ob_get_clean(); require __DIR__ . '/../layouts/admin.php';
