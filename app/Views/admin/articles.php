<?php
// Lấy dữ liệu từ mảng $data do Controller truyền sang
$baseUrl       = $data['baseUrl'] ?? '';
$articles      = $data['articles'] ?? [];
$categories    = $data['categories'] ?? [];
$editArticle   = $data['editArticle'] ?? null;
$page          = $data['page'] ?? 1;
$totalPages    = $data['totalPages'] ?? 1;
$totalArticles = $data['totalArticles'] ?? 0;

$formAction = $editArticle
    ? $baseUrl . '/ArticleController/update/' . $editArticle['id']
    : $baseUrl . '/ArticleController/store';
?>

<!-- Nhúng Admin Header / Sidebar của TV1 tại đây nếu có -->
<!-- <?php require_once __DIR__ . '/../layouts/admin_header.php'; ?> -->

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Thư viện jQuery & TinyMCE  -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js"></script>

<style>
    /* Biến CSS  */
    :root {
        --news-primary: #0f545a;
        --news-accent: #007bff;
        --news-bg: #f4f6f9;
    }
    .article-manager img {
        max-width: 100%;
        height: auto; /* Hình ảnh Responsive */
    }
    .preview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(75px, 1fr));
        gap: 10px;
        margin-top: 10px;
    }
    .preview-grid img {
        width: 75px;
        height: 60px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #dee2e6;
    }
</style>

<main class="container-fluid py-4 article-manager">
    <header class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0" style="color: var(--news-primary);">Quản Lý Bài Viết (NEWS HUB)</h2>
        <?php if ($editArticle): ?>
            <a href="<?= $baseUrl ?>/ArticleController/index" class="btn btn-outline-secondary">+ Quay lại thêm bài mới</a>
        <?php endif; ?>
    </header>

    <!-- Thông báo Session -->
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

    <!-- SECTION 1: FORM THÊM / SỬA BÀI VIẾT -->
    <section class="card shadow-sm mb-5">
        <div class="card-header text-white fw-semibold" style="background-color: var(--news-primary);">
            <?= $editArticle ? 'Chỉnh sửa bài viết #' . $editArticle['id'] : 'Soạn thảo bài viết mới' ?>
        </div>
        <div class="card-body">
            <form id="articleForm" action="<?= $formAction ?>" method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <!-- Tiêu đề -->
                    <div class="col-md-8">
                        <label for="title" class="form-label fw-semibold">Tiêu đề bài viết <span class="text-danger">*</span></label>
                        <input type="text" id="title" name="title" class="form-control" required
                               value="<?= htmlspecialchars($editArticle['title'] ?? '') ?>"
                               placeholder="Nhập tiêu đề bài báo...">
                    </div>

                    <!-- Danh mục -->
                    <div class="col-md-4">
                        <label for="category_id" class="form-label fw-semibold">Chuyên mục <span class="text-danger">*</span></label>
                        <select id="category_id" name="category_id" class="form-select" required>
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"
                                    <?= (isset($editArticle['category_id']) && $editArticle['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Tóm tắt -->
                    <div class="col-12">
                        <label for="summary" class="form-label fw-semibold">Tóm tắt ngắn (Sapo)</label>
                        <textarea id="summary" name="summary" class="form-control" rows="2"
                                  placeholder="Mô tả ngắn gọn nội dung hiển thị ngoài danh sách..."><?= htmlspecialchars($editArticle['summary'] ?? '') ?></textarea>
                    </div>

                    <!-- Nội dung chi tiết tích hợp TinyMCE -->
                    <div class="col-12">
                        <label for="editor" class="form-label fw-semibold">Nội dung bài viết <span class="text-danger">*</span></label>
                        <textarea id="editor" name="content"><?= $editArticle['content'] ?? '' ?></textarea>
                    </div>

                    <!-- Upload Ảnh đại diện -->
                    <div class="col-md-4">
                        <label for="thumbnail" class="form-label fw-semibold">Ảnh đại diện (Thumbnail)</label>
                        <input type="file" id="thumbnail" name="thumbnail" class="form-control" accept="image/*">
                        <div id="thumbnailPreview" class="mt-2">
                            <?php if (!empty($editArticle['thumbnail'])): ?>
                                <img src="<?= $baseUrl ?>/uploads/articles/<?= htmlspecialchars($editArticle['thumbnail']) ?>"
                                     alt="Thumbnail" class="img-thumbnail" style="max-height: 90px;">
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Upload Nhiều ảnh phụ -->
                    <div class="col-md-5">
                        <label for="images" class="form-label fw-semibold">Thư viện ảnh đính kèm (Chọn nhiều ảnh)</label>
                        <input type="file" id="images" name="images[]" class="form-control" accept="image/*" multiple>
                        
                        <!-- Preview ảnh mới chọn bằng jQuery -->
                        <div id="multiImagePreview" class="preview-grid"></div>

                        <!-- Danh sách ảnh phụ đã lưu trong DB (khi Sửa) -->
                        <?php if (!empty($editArticle['images'])): ?>
                            <div class="preview-grid">
                                <?php foreach ($editArticle['images'] as $img): ?>
                                    <div class="position-relative">
                                        <img src="<?= $baseUrl ?>/uploads/articles/<?= htmlspecialchars($img['image_path']) ?>" alt="Ảnh phụ">
                                        <a href="<?= $baseUrl ?>/ArticleController/deleteImage/<?= $img['id'] ?>/<?= $editArticle['id'] ?>"
                                           class="badge bg-danger text-decoration-none position-absolute top-0 end-0"
                                           onclick="return confirm('Xóa ảnh này khỏi bài viết?')">×</a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Trạng thái Draft / Published -->
                    <div class="col-md-3">
                        <label for="status" class="form-label fw-semibold">Trạng thái bài viết</label>
                        <select id="status" name="status" class="form-select">
                            <option value="draft" <?= (($editArticle['status'] ?? '') === 'draft') ? 'selected' : '' ?>>Draft (Bản nháp - Ẩn)</option>
                            <option value="published" <?= (($editArticle['status'] ?? '') === 'published') ? 'selected' : '' ?>>Published (Xuất bản công khai)</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <?= $editArticle ? 'Cập nhật bài viết' : 'Đăng bài viết' ?>
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- SECTION 2: DANH SÁCH BÀI VIẾT & PHÂN TRANG -->
    <section class="card shadow-sm">
        <div class="card-header bg-light fw-bold">
            Danh sách bài viết (Tổng cộng: <?= $totalArticles ?> bài)
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Ảnh</th>
                        <th>Tiêu đề</th>
                        <th>Danh mục</th>
                        <th>Trạng thái</th>
                        <th>Ngày đăng</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($articles)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Chưa có bài viết nào trong cơ sở dữ liệu.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($articles as $row): ?>
                            <tr>
                                <td>#<?= $row['id'] ?></td>
                                <td>
                                    <?php if (!empty($row['thumbnail'])): ?>
                                        <img src="<?= $baseUrl ?>/uploads/articles/<?= htmlspecialchars($row['thumbnail']) ?>"
                                             style="width: 64px; height: 44px; object-fit: cover;" class="rounded" alt="Thumb">
                                    <?php else: ?>
                                        <span class="badge bg-secondary">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-semibold" style="max-width: 320px;">
                                    <?= htmlspecialchars($row['title']) ?>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark">
                                        <?= htmlspecialchars($row['category_name'] ?? 'Chưa phân loại') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($row['status'] === 'published'): ?>
                                        <span class="badge bg-success">Published</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Draft</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                                <td class="text-end">
                                    <a href="<?= $baseUrl ?>/ArticleController/edit/<?= $row['id'] ?>/<?= $page ?>"
                                       class="btn btn-sm btn-outline-primary">Sửa</a>
                                    <a href="<?= $baseUrl ?>/ArticleController/delete/<?= $row['id'] ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Bạn có chắc chắn muốn xóa bài viết này?')">Xóa</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Phân trang theo URL chuẩn MVC: /ArticleController/index/{page} -->
        <?php if ($totalPages > 1): ?>
            <nav class="card-footer d-flex justify-content-center">
                <ul class="pagination mb-0">
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $baseUrl ?>/ArticleController/index/<?= $page - 1 ?>">Trước</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a class="page-link" href="<?= $baseUrl ?>/ArticleController/index/<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $baseUrl ?>/ArticleController/index/<?= $page + 1 ?>">Sau</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </section>
</main>

<script>
    // 1. Khởi tạo TinyMCE Rich Text Editor 
    tinymce.init({
        selector: '#editor',
        height: 320,
        menubar: false,
        plugins: 'lists link image table code wordcount',
        toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image table | code'
    });

    // 2. Xử lý sự kiện Form & Preview ảnh bằng jQuery 
    $(function() {
        // Preview ảnh đại diện ngay khi chọn file
        $('#thumbnail').on('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#thumbnailPreview').html(
                        `<img src="${e.target.result}" class="img-thumbnail" style="max-height: 90px;" alt="Preview">`
                    );
                };
                reader.readAsDataURL(file);
            }
        });

        // Preview nhiều ảnh đính kèm
        $('#images').on('change', function() {
            const container = $('#multiImagePreview');
            container.empty();
            Array.from(this.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    container.append(`<img src="${e.target.result}" alt="Preview">`);
                };
                reader.readAsDataURL(file);
            });
        });

        // Kiểm tra nội dung TinyMCE trước khi submit
        $('#articleForm').on('submit', function(e) {
            tinymce.triggerSave(); // Đồng bộ nội dung từ TinyMCE vào textarea #editor
            const contentVal = $('#editor').val().trim();
            if (!contentVal) {
                alert('Vui lòng nhập nội dung chi tiết bài viết!');
                e.preventDefault();
            }
        });
    });
</script>

<!-- Nhúng Admin Footer của TV1 tại đây nếu có -->
<!-- <?php require_once __DIR__ . '/../layouts/admin_footer.php'; ?> -->
