<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/ArticleImage.php';

class ArticleController extends Controller
{
    private Article $articleModel;
    private Category $categoryModel;
    private PDO $pdo;
    private string $uploadDir;
    private string $baseUrl;
    private const MAX_SINGLE_IMAGE_BYTES = 5 * 1024 * 1024;
    private const MAX_TOTAL_UPLOAD_BYTES = 18 * 1024 * 1024;

    public function __construct()
    {
        $this->articleModel = $this->model('Article');
        $this->categoryModel = $this->model('Category');
        $this->pdo = Database::getInstance()->getConnection();
        $this->uploadDir = dirname(__DIR__, 2) . '/public/uploads/articles';
        $this->baseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/');
    }

    public function index(int $page = 1): void
    {
        $statusInput = $_GET['status'] ?? '';
        $queryInput = $_GET['q'] ?? '';
        $filters = ['status' => is_string($statusInput) ? $statusInput : '', 'query' => is_string($queryInput) ? trim($queryInput) : '',
            'category_id' => filter_var($_GET['category_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null];
        if (mb_strlen($filters['query'], 'UTF-8') > 100) $filters['query'] = mb_substr($filters['query'], 0, 100, 'UTF-8');
        $limit = 10; $page = max(1, $page); $total = $this->articleModel->countAllAdmin($filters);
        $this->renderList($page, null, $filters, $total, $limit);
    }

    public function edit(int $id, int $page = 1): void
    {
        $article = $this->articleModel->getById($id);
        if (!$article) { http_response_code(404); return; }
        $this->renderList(max(1, $page), $article, [], $this->articleModel->countAllAdmin(), 10);
    }

    public function store(): void
    {
        $data = $this->validatedInput();
        $this->validateTotalUploadSize();
        $data['slug'] = $this->uniqueSlug($data['slug'] !== '' ? $data['slug'] : $data['title']);
        $data['user_id'] = (int)$_SESSION['user']['id'];
        $files = [];
        try {
            $thumbnail = $this->uploadOne($_FILES['thumbnail'] ?? null);
            if ($thumbnail !== null) { $files[] = $thumbnail; $data['thumbnail'] = basename($thumbnail); }
            $gallery = $this->uploadMany($_FILES['images'] ?? null);
            $files = array_merge($files, $gallery);
            $this->pdo->beginTransaction();
            $id = $this->articleModel->create($data);
            foreach ($gallery as $order => $path) $this->articleModel->addArticleImage($id, basename($path), null, $data['title'], $order);
            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            $this->deleteFiles($files);
            throw $e;
        }
        $_SESSION['success'] = 'Đã lưu bài viết. Ảnh được lưu trong thư mục uploads/articles.';
        $this->redirect('/ArticleController/edit/' . $id);
    }

    public function update(int $id): void
    {
        $old = $this->articleModel->getById($id);
        if (!$old) { http_response_code(404); return; }
        $data = $this->validatedInput();
        $this->validateTotalUploadSize();
        $data['slug'] = $this->uniqueSlug($data['slug'] !== '' ? $data['slug'] : $data['title'], $id);
        $data['user_id'] = (int)$old['user_id'];
        $data['published_at'] = $data['status'] === 'published'
            ? (($old['status'] === 'published' && !empty($old['published_at'])) ? $old['published_at'] : date('Y-m-d H:i:s'))
            : null;
        $oldThumbnail = $old['thumbnail'] ?? null;
        $removeThumbnail = isset($_POST['remove_thumbnail']) && $_POST['remove_thumbnail'] === '1';
        $files = [];
        try {
            $thumbnail = $this->uploadOne($_FILES['thumbnail'] ?? null);
            if ($thumbnail !== null) {
                $data['thumbnail'] = basename($thumbnail);
                $files[] = $thumbnail;
            } else {
                $data['thumbnail'] = $removeThumbnail ? null : $oldThumbnail;
            }
            $gallery = $this->uploadMany($_FILES['images'] ?? null);
            $files = array_merge($files, $gallery);
            $this->pdo->beginTransaction();
            $this->articleModel->update($id, $data);
            $imageOrder = $_POST['image_order'] ?? [];
            if (is_array($imageOrder)) {
                asort($imageOrder, SORT_NUMERIC);
                $this->articleModel->updateImageOrder($id, array_map('intval', array_keys($imageOrder)));
            }
            foreach ($gallery as $order => $path) $this->articleModel->addArticleImage($id, basename($path), null, $data['title'], $order);
            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            $this->deleteFiles($files);
            throw $e;
        }
        if (($thumbnail !== null || $removeThumbnail) && is_string($oldThumbnail) && $oldThumbnail !== ''
            && $oldThumbnail !== ($data['thumbnail'] ?? null)) {
            $this->deleteIfUnreferenced($oldThumbnail);
        }
        $_SESSION['success'] = $thumbnail !== null
            ? 'Đã cập nhật bài viết và thay ảnh đại diện.'
            : ($removeThumbnail ? 'Đã cập nhật bài viết và gỡ ảnh đại diện.' : 'Đã cập nhật bài viết.');
        $this->redirect('/ArticleController/edit/' . $id);
    }

    public function delete(int $id): void
    {
        $article = $this->articleModel->getById($id);
        if ($article) {
            $paths = array_column($article['images'] ?? [], 'image_path');
            if (!empty($article['thumbnail'])) $paths[] = $article['thumbnail'];
            $this->articleModel->delete($id);
            foreach (array_unique($paths) as $path) $this->deleteIfUnreferenced((string)$path);
        }
        $_SESSION['success'] = 'Đã xóa bài viết.';
        $this->redirect('/ArticleController/index');
    }

    public function deleteImage(int $imageId, int $articleId): void
    {
        $path = $this->articleModel->deleteArticleImage($imageId, $articleId);
        if ($path) {
            $this->deleteIfUnreferenced((string)$path);
            $_SESSION['success'] = 'Đã xóa ảnh nội dung.';
        }
        $this->redirect('/ArticleController/edit/' . $articleId);
    }

    private function renderList(int $page, ?array $editArticle, array $filters = [], ?int $total = null, int $limit = 10): void
    {
        $total ??= $this->articleModel->countAllAdmin($filters);
        $storageReady = is_dir($this->uploadDir)
            ? is_writable($this->uploadDir)
            : (is_dir(dirname($this->uploadDir)) && is_writable(dirname($this->uploadDir)));
        $this->view('admin/articles', ['page_title' => 'Quản lý bài viết — NEWS HUB', 'baseUrl' => $this->baseUrl,
            'imageStorageReady' => $storageReady,
            'articles' => $this->articleModel->getAllAdmin($limit, ($page - 1) * $limit, $filters),
            'categories' => $this->categoryModel->getAll(200, 0), 'editArticle' => $editArticle, 'page' => $page,
            'totalPages' => max(1, (int)ceil($total / $limit)), 'totalArticles' => $total,
            'filters' => $filters, 'csrf_token' => $this->csrfToken()]);
    }

    private function validatedInput(): array
    {
        $titleInput = $_POST['title'] ?? '';
        $summaryInput = $_POST['summary'] ?? '';
        $contentInput = $_POST['content'] ?? '';
        if (!is_string($titleInput) || !is_string($summaryInput) || !is_string($contentInput)) {
            throw new InvalidArgumentException('Invalid article fields.');
        }
        $title = trim($titleInput);
        $categoryInput = $_POST['category_id'] ?? null;
        $categoryId = is_string($categoryInput) ? filter_var($categoryInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;
        $content = $contentInput;
        if ($title === '' || mb_strlen($title, 'UTF-8') > 255 || !$categoryId || trim(strip_tags($content)) === ''
            || mb_strlen($summaryInput, 'UTF-8') > 10000) {
            throw new InvalidArgumentException('Invalid article fields.');
        }
        $category = $this->categoryModel->getById((int)$categoryId);
        if (!$category || $category['status'] !== 'active') throw new InvalidArgumentException('Choose an active category.');
        $status = in_array($_POST['status'] ?? '', ['draft','published'], true) ? $_POST['status'] : 'draft';
        return ['title' => $title, 'category_id' => (int)$categoryId,
            'summary' => trim($summaryInput), 'content' => $content,
            'status' => $status, 'is_featured' => $status === 'published' && isset($_POST['is_featured']) ? 1 : 0,
            'slug' => is_string($_POST['slug'] ?? '') ? trim($_POST['slug'] ?? '') : ''];
    }

    private function uniqueSlug(string $source, int $excludeId = 0): string
    {
        $base = $this->slugify($source);
        if ($base === '') $base = 'article';
        $candidate = substr($base, 0, 240);
        for ($suffix = 2; $this->articleModel->slugExists($candidate, $excludeId); $suffix++) {
            $tail = '-' . $suffix;
            $candidate = substr($base, 0, 255 - strlen($tail)) . $tail;
        }
        return $candidate;
    }

    private function slugify(string $source): string
    {
        $source = mb_strtolower(trim($source), 'UTF-8');
        $source = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $source) ?: $source;
        $source = preg_replace('/[^a-z0-9]+/', '-', strtolower($source)) ?? '';
        return trim($source, '-');
    }

    private function validateTotalUploadSize(): void
    {
        $totalBytes = 0;

        $thumbnailSize = $_FILES['thumbnail']['size'] ?? 0;
        if (is_numeric($thumbnailSize)) {
            $totalBytes += max(0, (int) $thumbnailSize);
        }

        $gallerySizes = $_FILES['images']['size'] ?? [];
        if (is_array($gallerySizes)) {
            foreach ($gallerySizes as $size) {
                if (is_numeric($size)) {
                    $totalBytes += max(0, (int) $size);
                }
            }
        }

        if ($totalBytes > self::MAX_TOTAL_UPLOAD_BYTES) {
            throw new InvalidArgumentException('Tổng dung lượng ảnh trong một lần gửi không được vượt 18 MB trên hosting hiện tại.');
        }
    }

    private function uploadOne(?array $file): ?string
    {
        if ($file === null) {
            return null;
        }

        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($error === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (!is_int($error) && !ctype_digit((string)$error)) {
            throw new InvalidArgumentException('Thông tin ảnh tải lên không hợp lệ.');
        }
        $error = (int)$error;
        if ($error !== UPLOAD_ERR_OK) {
            $messages = [
                UPLOAD_ERR_INI_SIZE => 'Ảnh vượt giới hạn upload_max_filesize của hosting.',
                UPLOAD_ERR_FORM_SIZE => 'Ảnh vượt giới hạn dung lượng của biểu mẫu.',
                UPLOAD_ERR_PARTIAL => 'Ảnh chỉ được tải lên một phần. Vui lòng chọn lại.',
                UPLOAD_ERR_NO_TMP_DIR => 'Hosting thiếu thư mục tạm để nhận ảnh.',
                UPLOAD_ERR_CANT_WRITE => 'Hosting không thể ghi ảnh lên ổ đĩa.',
                UPLOAD_ERR_EXTENSION => 'Một tiện ích PHP đã chặn việc tải ảnh lên.',
            ];
            throw new InvalidArgumentException($messages[$error] ?? 'Không thể nhận ảnh tải lên.');
        }

        $tmpName = $file['tmp_name'] ?? null;
        $size = filter_var($file['size'] ?? null, FILTER_VALIDATE_INT);
        if (!is_string($tmpName) || $tmpName === '' || $size === false || $size < 1
            || $size > self::MAX_SINGLE_IMAGE_BYTES || !is_uploaded_file($tmpName)) {
            throw new InvalidArgumentException('Ảnh không hợp lệ hoặc vượt giới hạn 5 MB mỗi ảnh.');
        }

        $imageInfo = @getimagesize($tmpName);
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmpName);
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];
        $extension = is_string($mime) ? ($extensions[$mime] ?? null) : null;
        if ($extension === null || !is_array($imageInfo) || empty($imageInfo[0]) || empty($imageInfo[1])) {
            throw new InvalidArgumentException('Chỉ nhận ảnh JPG, PNG, WEBP hoặc GIF hợp lệ.');
        }
        if ((int)$imageInfo[0] * (int)$imageInfo[1] > 40000000) {
            throw new InvalidArgumentException('Ảnh có độ phân giải quá lớn. Vui lòng chọn ảnh tối đa 40 megapixel.');
        }

        if (!is_dir($this->uploadDir) && !mkdir($this->uploadDir, 0755, true) && !is_dir($this->uploadDir)) {
            throw new RuntimeException('Không thể tạo thư mục lưu ảnh.');
        }
        if (!is_writable($this->uploadDir)) {
            throw new RuntimeException('Thư mục public/uploads/articles không có quyền ghi.');
        }

        $target = $this->uploadDir . '/' . bin2hex(random_bytes(20)) . '.' . $extension;
        if (!move_uploaded_file($tmpName, $target)) {
            throw new RuntimeException('Không thể lưu ảnh vào public/uploads/articles.');
        }

        return $target;
    }

    private function uploadMany(?array $files): array
    {
        if ($files === null) {
            return [];
        }
        if (!isset($files['name'], $files['tmp_name'], $files['error'], $files['size'])
            || !is_array($files['name']) || !is_array($files['tmp_name'])
            || !is_array($files['error']) || !is_array($files['size'])) {
            throw new InvalidArgumentException('Danh sách ảnh nội dung không hợp lệ.');
        }
        if (count($files['name']) > 10) {
            throw new InvalidArgumentException('Tối đa 10 ảnh nội dung mỗi lần tải.');
        }

        $stored = [];
        try {
            foreach ($files['name'] as $index => $name) {
                $error = $files['error'][$index] ?? UPLOAD_ERR_NO_FILE;
                if ((int)$error === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                $storedPath = $this->uploadOne([
                    'name' => $name,
                    'tmp_name' => $files['tmp_name'][$index] ?? '',
                    'error' => $error,
                    'size' => $files['size'][$index] ?? 0,
                ]);
                if ($storedPath !== null) {
                    $stored[] = $storedPath;
                }
            }
        } catch (Throwable $exception) {
            $this->deleteFiles($stored);
            throw $exception;
        }

        return $stored;
    }

    private function deleteFiles(array $files): void
    {
        foreach ($files as $file) if (is_string($file) && is_file($file)) unlink($file);
    }

    private function deleteIfUnreferenced(string $path): void
    {
        if (ArticleImage::filename($path) === null
            || !preg_match('/\A[a-f0-9]{40}\.(?:jpg|png|webp|gif)\z/i', $path)) {
            return;
        }
        if (!$this->articleModel->fileIsReferenced($path)) {
            $full = $this->uploadDir . '/' . $path;
            if (is_file($full)) unlink($full);
        }
    }

    private function redirect(string $path): void
    {
        $this->redirectTo($this->baseUrl . $path);
    }
}
