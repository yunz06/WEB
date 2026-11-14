<?php

class ArticleController extends Controller {
    private $articleModel;
    private $uploadDir;
    private $baseUrl;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Gọi Model theo chuẩn lớp cơ sở Controller 
        $this->articleModel = $this->model('Article');

        // Thư mục lưu ảnh vật lý: public/uploads/articles/
        $this->uploadDir = __DIR__ . '/../../public/uploads/articles/';
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }

        // Tự động xác định Base URL
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $this->baseUrl = rtrim($scriptDir, '/');
    }

    // URL mặc định: /ArticleController/index/1
    public function index($page = 1) {
        $limit = 8;
        $page = is_numeric($page) && (int)$page > 0 ? (int)$page : 1;
        $offset = ($page - 1) * $limit;

        $articles = $this->articleModel->getAllAdmin($limit, $offset);
        $totalArticles = $this->articleModel->countAllAdmin();
        $totalPages = max(1, ceil($totalArticles / $limit));
        $categories = $this->articleModel->getCategories();

        // Truyền dữ liệu sang View thông qua mảng $data
        $this->view('admin/articles', [
            'page_title'    => 'Quản lý bài viết - NEWS HUB',
            'baseUrl'       => $this->baseUrl,
            'articles'      => $articles,
            'categories'    => $categories,
            'editArticle'   => null,
            'page'          => $page,
            'totalPages'    => $totalPages,
            'totalArticles' => $totalArticles
        ]);
    }

    // URL mở form sửa: /ArticleController/edit/{id}/{page}
    public function edit($id = 0, $page = 1) {
        $id = (int)$id;
        $editArticle = $this->articleModel->getById($id);

        if (!$editArticle) {
            $_SESSION['error'] = "Không tìm thấy bài viết #$id!";
            $this->redirect('/ArticleController/index');
        }

        $limit = 8;
        $page = is_numeric($page) && (int)$page > 0 ? (int)$page : 1;
        $offset = ($page - 1) * $limit;

        $articles = $this->articleModel->getAllAdmin($limit, $offset);
        $totalArticles = $this->articleModel->countAllAdmin();
        $totalPages = max(1, ceil($totalArticles / $limit));
        $categories = $this->articleModel->getCategories();

        $this->view('admin/articles', [
            'page_title'    => 'Chỉnh sửa bài viết #' . $id,
            'baseUrl'       => $this->baseUrl,
            'articles'      => $articles,
            'categories'    => $categories,
            'editArticle'   => $editArticle,
            'page'          => $page,
            'totalPages'    => $totalPages,
            'totalArticles' => $totalArticles
        ]);
    }

    // URL lưu bài viết mới (POST): /ArticleController/store
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/ArticleController/index');
        }

        $title      = trim($_POST['title'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $summary    = trim($_POST['summary'] ?? '');
        $content    = $_POST['content'] ?? '';
        $status     = in_array($_POST['status'] ?? '', ['draft', 'published']) ? $_POST['status'] : 'draft';

        if (empty($title) || empty($content) || $categoryId <= 0) {
            $_SESSION['error'] = "Vui lòng nhập đầy đủ Tiêu đề, Danh mục và Nội dung!";
            $this->redirect('/ArticleController/index');
        }

        // Xử lý upload ảnh đại diện (thumbnail)
        $thumbnailName = null;
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->uploadSingleFile($_FILES['thumbnail']);
            if ($uploadResult['success']) {
                $thumbnailName = $uploadResult['filename'];
            } else {
                $_SESSION['error'] = $uploadResult['message'];
                $this->redirect('/ArticleController/index');
            }
        }

        $articleId = $this->articleModel->create([
            'category_id' => $categoryId,
            'title'       => $title,
            'slug'        => $this->createSlug($title),
            'summary'     => $summary,
            'content'     => $content,
            'thumbnail'   => $thumbnailName,
            'status'      => $status
        ]);

        // Xử lý upload nhiều ảnh phụ (images[])
        if ($articleId && isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $this->handleMultipleUploads($articleId, $_FILES['images']);
        }

        $_SESSION['success'] = "Thêm bài viết thành công!";
        $this->redirect('/ArticleController/index');
    }

    // URL cập nhật bài viết (POST): /ArticleController/update/{id}
    public function update($id = 0) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/ArticleController/index');
        }

        $id = (int)$id;
        $existingArticle = $this->articleModel->getById($id);
        if (!$existingArticle) {
            $_SESSION['error'] = "Bài viết không tồn tại!";
            $this->redirect('/ArticleController/index');
        }

        $title      = trim($_POST['title'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $summary    = trim($_POST['summary'] ?? '');
        $content    = $_POST['content'] ?? '';
        $status     = in_array($_POST['status'] ?? '', ['draft', 'published']) ? $_POST['status'] : 'draft';

        $thumbnailName = $existingArticle['thumbnail'];

        // Nếu có upload ảnh đại diện mới
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->uploadSingleFile($_FILES['thumbnail']);
            if ($uploadResult['success']) {
                if ($thumbnailName && file_exists($this->uploadDir . $thumbnailName)) {
                    unlink($this->uploadDir . $thumbnailName);
                }
                $thumbnailName = $uploadResult['filename'];
            } else {
                $_SESSION['error'] = $uploadResult['message'];
                $this->redirect('/ArticleController/edit/' . $id);
            }
        }

        $this->articleModel->update($id, [
            'category_id' => $categoryId,
            'title'       => $title,
            'slug'        => $this->createSlug($title),
            'summary'     => $summary,
            'content'     => $content,
            'thumbnail'   => $thumbnailName,
            'status'      => $status
        ]);

        // Upload thêm nhiều ảnh phụ nếu người dùng chọn thêm
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $this->handleMultipleUploads($id, $_FILES['images']);
        }

        $_SESSION['success'] = "Cập nhật bài viết #$id thành công!";
        $this->redirect('/ArticleController/index');
    }

    // URL xóa bài viết: /ArticleController/delete/{id}
    public function delete($id = 0) {
        $id = (int)$id;
        $article = $this->articleModel->getById($id);

        if ($article) {
            if (!empty($article['thumbnail']) && file_exists($this->uploadDir . $article['thumbnail'])) {
                unlink($this->uploadDir . $article['thumbnail']);
            }
            if (!empty($article['images'])) {
                foreach ($article['images'] as $img) {
                    if (file_exists($this->uploadDir . $img['image_path'])) {
                        unlink($this->uploadDir . $img['image_path']);
                    }
                }
            }
            $this->articleModel->delete($id);
            $_SESSION['success'] = "Đã xóa bài viết #$id!";
        }

        $this->redirect('/ArticleController/index');
    }

    // URL xóa 1 ảnh phụ: /ArticleController/deleteImage/{imageId}/{articleId}
    public function deleteImage($imageId = 0, $articleId = 0) {
        $filePath = $this->articleModel->deleteArticleImage((int)$imageId);
        if ($filePath && file_exists($this->uploadDir . $filePath)) {
            unlink($this->uploadDir . $filePath);
        }
        $this->redirect('/ArticleController/edit/' . (int)$articleId);
    }

    // Kiểm tra định dạng, dung lượng và đổi tên file an toàn
    private function uploadSingleFile($file) {
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $mimeType = mime_content_type($file['tmp_name']);

        if (!in_array($ext, $allowedExts) || !in_array($mimeType, $allowedMimes)) {
            return ['success' => false, 'message' => 'Chỉ chấp nhận file ảnh JPG, PNG, WEBP, GIF!'];
        }

        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'Dung lượng ảnh tối đa là 5MB!'];
        }

        $newFilename = 'art_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $this->uploadDir . $newFilename)) {
            return ['success' => true, 'filename' => $newFilename];
        }

        return ['success' => false, 'message' => 'Không thể lưu file ảnh lên máy chủ!'];
    }

    // Vòng lặp xử lý upload nhiều ảnh 
    private function handleMultipleUploads($articleId, $files) {
        foreach ($files['tmp_name'] as $key => $tmpName) {
            if ($files['error'][$key] === UPLOAD_ERR_OK) {
                $singleFile = [
                    'name'     => $files['name'][$key],
                    'type'     => $files['type'][$key],
                    'tmp_name' => $tmpName,
                    'error'    => $files['error'][$key],
                    'size'     => $files['size'][$key]
                ];
                $res = $this->uploadSingleFile($singleFile);
                if ($res['success']) {
                    $this->articleModel->addArticleImage($articleId, $res['filename']);
                }
            }
        }
    }

    // Tạo slug URL chuẩn SEO từ tiêu đề
    private function createSlug($str) {
        $str = mb_strtolower($str, 'UTF-8');
        $unicode = [
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
        ];
        foreach ($unicode as $nonUnicode => $uni) {
            $str = preg_replace("/($uni)/i", $nonUnicode, $str);
        }
        $str = preg_replace('/[^a-z0-9\s-]/', '', $str);
        $str = preg_replace('/[\s-]+/', '-', trim($str));
        return $str . '-' . time();
    }

    private function redirect($path) {
        header("Location: " . $this->baseUrl . $path);
        exit;
    }
}
