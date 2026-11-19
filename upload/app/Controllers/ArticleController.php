<?php

declare(strict_types=1);

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
        $oldThumbnail = $old['thumbnail'];
        $files = [];
        try {
            $thumbnail = $this->uploadOne($_FILES['thumbnail'] ?? null);
            $data['thumbnail'] = $thumbnail === null ? $oldThumbnail : basename($thumbnail);
            if ($thumbnail !== null) $files[] = $thumbnail;
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
        if ($thumbnail !== null && $oldThumbnail) $this->deleteIfUnreferenced((string)$oldThumbnail);
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
        $this->redirect('/ArticleController/index');
    }

    public function deleteImage(int $imageId, int $articleId): void
    {
        $path = $this->articleModel->deleteArticleImage($imageId, $articleId);
        if ($path) $this->deleteIfUnreferenced((string)$path);
        $this->redirect('/ArticleController/edit/' . $articleId);
    }

    private function renderList(int $page, ?array $editArticle, array $filters = [], ?int $total = null, int $limit = 10): void
    {
        $total ??= $this->articleModel->countAllAdmin($filters);
        $this->view('admin/articles', ['page_title' => 'Quản lý bài viết — NEWS HUB', 'baseUrl' => $this->baseUrl,
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
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
        if (!isset($file['error'], $file['tmp_name'], $file['name'], $file['size']) || is_array($file['error'])
            || $file['error'] !== UPLOAD_ERR_OK || (int)$file['size'] > self::MAX_SINGLE_IMAGE_BYTES
            || !is_uploaded_file($file['tmp_name'])) throw new InvalidArgumentException('Ảnh tải lên không hợp lệ hoặc vượt giới hạn 5 MB mỗi ảnh.');
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        $extension = $extensions[$mime] ?? null;
        $givenExtension = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
        if (!$extension || !in_array($givenExtension, ['jpg','jpeg','png','webp','gif'], true) || !getimagesize($file['tmp_name']))
            throw new InvalidArgumentException('Chỉ nhận ảnh JPG, PNG, WEBP hoặc GIF hợp lệ.');
        if (!is_dir($this->uploadDir) && !mkdir($this->uploadDir, 0755, true) && !is_dir($this->uploadDir))
            throw new RuntimeException('Upload directory unavailable.');
        $target = $this->uploadDir . '/' . bin2hex(random_bytes(20)) . '.' . $extension;
        if (!move_uploaded_file($file['tmp_name'], $target)) throw new RuntimeException('Image upload failed.');
        return $target;
    }

    private function uploadMany(?array $files): array
    {
        if ($files === null || !isset($files['name']) || !is_array($files['name'])) return [];
        if (count($files['name']) > 10) throw new InvalidArgumentException('Tối đa 10 ảnh nội dung mỗi lần tải.');
        $stored = [];
        foreach ($files['name'] as $index => $name) {
            $error = $files['error'][$index] ?? UPLOAD_ERR_NO_FILE;
            if ($error === UPLOAD_ERR_NO_FILE) continue;
            try {
                $stored[] = $this->uploadOne(['name' => $name, 'tmp_name' => $files['tmp_name'][$index] ?? '',
                    'error' => $error, 'size' => $files['size'][$index] ?? 0]);
            } catch (Throwable $e) {
                $this->deleteFiles($stored);
                throw $e;
            }
        }
        return $stored;
    }

    private function deleteFiles(array $files): void
    {
        foreach ($files as $file) if (is_string($file) && is_file($file)) unlink($file);
    }

    private function deleteIfUnreferenced(string $path): void
    {
        if (basename($path) !== $path || !preg_match('/\A[a-f0-9]{40}\.(?:jpg|png|webp|gif)\z/i', $path)) return;
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
