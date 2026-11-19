<?php
ob_start();
$baseUrl         = $data['baseUrl'] ?? '';
$categories      = $data['categories'] ?? [];
$editCategory    = $data['editCategory'] ?? null;
$page            = $data['page'] ?? 1;
$totalPages      = $data['totalPages'] ?? 1;
$totalCategories = $data['totalCategories'] ?? 0;
$csrfToken       = $data['csrf_token'] ?? '';

$formAction = $editCategory
    ? $baseUrl . '/CategoryController/update/' . $editCategory['id']
    : $baseUrl . '/CategoryController/store';
?>

<!-- Admin header and sidebar can be added here when available. -->

<style>
    :root { --news-primary: #102747; }
</style>

<section class="container-fluid py-4 admin-page">
    <header class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold mb-0" style="color: var(--news-primary);">Quản Lý Danh Mục (NEWS HUB)</h1>
        <?php if ($editCategory): ?>
            <a href="<?= $baseUrl ?>/CategoryController/index" class="btn btn-outline-secondary">+ Quay lại thêm danh mục mới</a>
        <?php endif; ?>
    </header>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- FORM THÊM / SỬA -->
    <section class="card shadow-sm mb-5">
        <div class="card-header text-white fw-semibold" style="background-color: var(--news-primary);">
            <?= $editCategory ? 'Chỉnh sửa danh mục #' . $editCategory['id'] : 'Thêm danh mục mới' ?>
        </div>
        <div class="card-body">
            <form action="<?= $formAction ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="name" class="form-label fw-semibold">Tên danh mục <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" required maxlength="100"
                               value="<?= htmlspecialchars($editCategory['name'] ?? '') ?>"
                               placeholder="Ví dụ: Thể thao">
                    </div>
                    <div class="col-md-8">
                        <label for="description" class="form-label fw-semibold">Mô tả</label>
                        <input type="text" id="description" name="description" class="form-control"
                               value="<?= htmlspecialchars($editCategory['description'] ?? '') ?>"
                               placeholder="Mô tả ngắn về danh mục...">
                    </div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-4"><label class="form-label" for="parent_id">Danh mục cha</label>
                        <select class="form-select" id="parent_id" name="parent_id"><option value="">Không có (danh mục chính)</option>
                            <?php foreach (($data['parentCategories'] ?? []) as $parent): ?><option value="<?= (int)$parent['id'] ?>" <?= (int)($editCategory['parent_id'] ?? 0) === (int)$parent['id'] ? 'selected' : '' ?>><?= htmlspecialchars($parent['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4"><label class="form-label" for="sort_order">Thứ tự</label><input class="form-control" id="sort_order" name="sort_order" type="number" min="0" max="65535" value="<?= (int)($editCategory['sort_order'] ?? 0) ?>"></div>
                    <div class="col-md-4"><label class="form-label" for="status">Trạng thái</label><select class="form-select" id="status" name="status"><option value="active" <?= ($editCategory['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Đang hoạt động</option><option value="inactive" <?= ($editCategory['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Tạm ẩn</option></select></div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <?= $editCategory ? 'Cập nhật danh mục' : 'Thêm danh mục' ?>
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- DANH SÁCH -->
    <section class="card shadow-sm">
        <div class="card-header bg-light fw-bold">Danh sách danh mục (Tổng cộng: <?= $totalCategories ?>)</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Tên</th>
                        <th>Slug</th>
                        <th>Mô tả</th>
                        <th>Số bài viết</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">Chưa có danh mục nào.</td></tr>
                    <?php else: ?>
                        <?php foreach ($categories as $row): ?>
                            <tr>
                                <td>#<?= $row['id'] ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($row['name']) ?></td>
                                <td class="text-muted"><?= htmlspecialchars($row['slug'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['description'] ?? '') ?></td>
                                <td><span class="badge bg-info text-dark"><?= (int)$row['article_count'] ?></span></td>
                                <td class="text-end">
                                    <a href="<?= $baseUrl ?>/CategoryController/edit/<?= $row['id'] ?>/<?= $page ?>"
                                       class="btn btn-sm btn-outline-primary">Sửa</a>
                                    <form method="POST" action="<?= $baseUrl ?>/CategoryController/delete/<?= (int)$row['id'] ?>"
                                          class="d-inline"
                                          data-confirm="Bạn có chắc chắn muốn xóa danh mục này?">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="card-footer d-flex justify-content-center">
                <ul class="pagination mb-0">
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $baseUrl ?>/CategoryController/index/<?= $page - 1 ?>">Trước</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a class="page-link" href="<?= $baseUrl ?>/CategoryController/index/<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $baseUrl ?>/CategoryController/index/<?= $page + 1 ?>">Sau</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </section>
</section>

<?php $pageContent = (string)ob_get_clean(); require __DIR__ . '/../layouts/admin.php'; ?>
