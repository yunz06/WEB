<?php
ob_start();
// Lấy dữ liệu từ mảng $data do Controller truyền sang
$baseUrl       = $data['baseUrl'] ?? '';
$articles      = $data['articles'] ?? [];
$categories    = $data['categories'] ?? [];
$editArticle   = $data['editArticle'] ?? null;
$page          = $data['page'] ?? 1;
$totalPages    = $data['totalPages'] ?? 1;
$totalArticles = $data['totalArticles'] ?? 0;
$csrfToken     = $data['csrf_token'] ?? '';
$filters       = $data['filters'] ?? [];
$filterQuery   = http_build_query(array_filter(['q' => $filters['query'] ?? '', 'status' => $filters['status'] ?? '', 'category_id' => $filters['category_id'] ?? null], static fn($v) => $v !== '' && $v !== null));

$formAction = $editArticle
    ? $baseUrl . '/ArticleController/update/' . $editArticle['id']
    : $baseUrl . '/ArticleController/store';
?>

<!-- Nhúng Admin Header / Sidebar của TV1 tại đây nếu có -->
<!-- Admin header and sidebar can be added here when available. -->

<!-- TinyMCE enriches the native textarea; the form remains usable if the CDN is offline. -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js"></script>

<style>
    /* Biến CSS  */
    :root {
        --news-primary: #102747;
        --news-accent: #2563eb;
        --news-bg: #f5f7fa;
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

<section class="container-fluid py-4 article-manager admin-page">
    <header class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold mb-0" style="color: var(--news-primary);">Quản Lý Bài Viết (NEWS HUB)</h1>
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
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <div class="row g-3">
                    <!-- Tiêu đề -->
                    <div class="col-md-8">
                        <label for="title" class="form-label fw-semibold">Tiêu đề bài viết <span class="text-danger">*</span></label>
                        <input type="text" id="title" name="title" class="form-control" required
                               value="<?= htmlspecialchars($editArticle['title'] ?? '') ?>"
                               placeholder="Nhập tiêu đề bài báo...">
                    </div>

                    <div class="col-md-4"><label class="form-label" for="slug">Slug tùy chỉnh</label>
                        <input class="form-control" id="slug" name="slug" maxlength="255" value="<?= htmlspecialchars($editArticle['slug'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" placeholder="Để trống để tự tạo từ tiêu đề">
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
                        <textarea id="editor" name="content"><?= htmlspecialchars($editArticle['content'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>
                        <p id="editorFallback" class="form-text" role="status" aria-live="polite" hidden>Trình soạn thảo nâng cao chưa tải được. Bạn vẫn có thể nhập nội dung tại đây.</p>
                    </div>

                    <!-- Upload Ảnh đại diện -->
                    <div class="col-md-4">
                        <label for="thumbnail" class="form-label fw-semibold">Ảnh đại diện (Thumbnail)</label>
                        <input type="file" id="thumbnail" name="thumbnail" class="form-control" accept="image/*">
                        <div id="thumbnailPreview" class="mt-2">
                            <?php if (!empty($editArticle['thumbnail'])): ?>
                                <img src="<?= $baseUrl ?>/public/uploads/articles/<?= rawurlencode(basename($editArticle['thumbnail'])) ?>"
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
                                        <img src="<?= $baseUrl ?>/public/uploads/articles/<?= rawurlencode(basename($img['image_path'])) ?>" alt="<?= htmlspecialchars($img['alt_text'] ?? 'Ảnh phụ', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                                        <label class="visually-hidden" for="image-order-<?= (int)$img['id'] ?>">Thứ tự ảnh</label>
                                        <input class="form-control form-control-sm mt-1" id="image-order-<?= (int)$img['id'] ?>" type="number" min="0" name="image_order[<?= (int)$img['id'] ?>]" value="<?= (int)($img['sort_order'] ?? 0) ?>">
                                        <button type="submit"
                                                formaction="<?= $baseUrl ?>/ArticleController/deleteImage/<?= (int)$img['id'] ?>/<?= (int)$editArticle['id'] ?>"
                                                formmethod="POST"
                                                formenctype="application/x-www-form-urlencoded"
                                                class="badge bg-danger border-0 position-absolute top-0 end-0"
                                                aria-label="Xóa ảnh phụ"
                                                data-confirm="Bạn có chắc chắn muốn xóa ảnh phụ này?">×</button>
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
                    <div class="col-md-3 form-check ms-2 align-self-end mb-2">
                        <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" <?= !empty($editArticle['is_featured']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_featured">Tin nổi bật (chỉ hiện khi xuất bản)</label>
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
        <form class="row g-2 p-3" method="get" action="<?= htmlspecialchars($baseUrl . '/ArticleController/index', ENT_QUOTES, 'UTF-8') ?>">
            <div class="col-md-5"><label class="visually-hidden" for="article-query">Tìm bài</label><input class="form-control" id="article-query" name="q" value="<?= htmlspecialchars($filters['query'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" placeholder="Tìm tiêu đề"></div>
            <div class="col-md-3"><label class="visually-hidden" for="article-status-filter">Lọc trạng thái</label><select class="form-select" id="article-status-filter" name="status"><option value="">Mọi trạng thái</option><option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Bản nháp</option><option value="published" <?= ($filters['status'] ?? '') === 'published' ? 'selected' : '' ?>>Đã xuất bản</option></select></div>
            <div class="col-md-3"><label class="visually-hidden" for="article-category-filter">Lọc danh mục</label><select class="form-select" id="article-category-filter" name="category_id"><option value="">Mọi danh mục</option><?php foreach ($categories as $category): ?><option value="<?= (int)$category['id'] ?>" <?= (int)($filters['category_id'] ?? 0) === (int)$category['id'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></option><?php endforeach; ?></select></div>
            <div class="col-md-1"><button class="btn btn-outline-primary w-100">Lọc</button></div>
        </form>
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
                                        <img src="<?= $baseUrl ?>/public/uploads/articles/<?= rawurlencode(basename($row['thumbnail'])) ?>"
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
                                    <form method="POST" action="<?= $baseUrl ?>/ArticleController/delete/<?= (int)$row['id'] ?>"
                                          class="d-inline"
                                          data-confirm="Bạn có chắc chắn muốn xóa bài viết này?">
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

        <!-- Phân trang theo URL chuẩn MVC: /ArticleController/index/{page} -->
        <?php if ($totalPages > 1): ?>
            <nav class="card-footer d-flex justify-content-center">
                <ul class="pagination mb-0">
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $baseUrl ?>/ArticleController/index/<?= $page - 1 ?><?= $filterQuery !== '' ? '?' . htmlspecialchars($filterQuery, ENT_QUOTES, 'UTF-8') : '' ?>">Trước</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a class="page-link" href="<?= $baseUrl ?>/ArticleController/index/<?= $i ?><?= $filterQuery !== '' ? '?' . htmlspecialchars($filterQuery, ENT_QUOTES, 'UTF-8') : '' ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $baseUrl ?>/ArticleController/index/<?= $page + 1 ?><?= $filterQuery !== '' ? '?' . htmlspecialchars($filterQuery, ENT_QUOTES, 'UTF-8') : '' ?>">Sau</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </section>
</section>

<script>
    (() => {
        const editor = document.getElementById('editor');
        const editorFallback = document.getElementById('editorFallback');
        const showEditorFallback = () => {
            if (editorFallback) editorFallback.hidden = false;
            if (editor) editor.required = true;
        };

        if (window.tinymce && typeof window.tinymce.init === 'function' && editor) {
            try {
                Promise.resolve(window.tinymce.init({
                    selector: '#editor',
                    height: 320,
                    menubar: false,
                    plugins: 'lists link image table code wordcount',
                    toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image table | code'
                })).then(() => {
                    editor.required = false;
                }).catch(showEditorFallback);
            } catch (error) {
                showEditorFallback();
            }
        } else {
            showEditorFallback();
        }

        const articleForm = document.getElementById('articleForm');
        articleForm?.addEventListener('submit', (event) => {
            if (window.tinymce && typeof window.tinymce.triggerSave === 'function') {
                window.tinymce.triggerSave();
            }
            if (editor && editor.value.trim() === '') {
                event.preventDefault();
                editor.focus();
                if (editorFallback) {
                    editorFallback.hidden = false;
                    editorFallback.textContent = 'Nhập nội dung bài viết trước khi lưu.';
                }
            }
        });

        const previewImage = (file, container, alt) => {
            if (!file || !container) return;
            const reader = new FileReader();
            reader.addEventListener('load', () => {
                if (typeof reader.result !== 'string') return;
                const image = document.createElement('img');
                image.src = reader.result;
                image.alt = alt;
                image.className = 'img-thumbnail';
                image.width = 150;
                image.height = 100;
                container.replaceChildren(image);
            });
            reader.readAsDataURL(file);
        };

        document.getElementById('thumbnail')?.addEventListener('change', (event) => {
            const input = event.currentTarget;
            previewImage(input.files?.[0], document.getElementById('thumbnailPreview'), 'Ảnh đại diện xem trước');
        });

        document.getElementById('images')?.addEventListener('change', (event) => {
            const input = event.currentTarget;
            const container = document.getElementById('multiImagePreview');
            if (!container) return;
            container.replaceChildren();
            Array.from(input.files ?? []).forEach((file, index) => {
                const reader = new FileReader();
                reader.addEventListener('load', () => {
                    if (typeof reader.result !== 'string') return;
                    const image = document.createElement('img');
                    image.src = reader.result;
                    image.alt = `Ảnh nội dung xem trước ${index + 1}`;
                    image.width = 75;
                    image.height = 60;
                    container.append(image);
                });
                reader.readAsDataURL(file);
            });
        });
    })();
</script>

<!-- Nhúng Admin Footer của TV1 tại đây nếu có -->
<?php $pageContent = (string)ob_get_clean(); require __DIR__ . '/../layouts/admin.php'; ?>
