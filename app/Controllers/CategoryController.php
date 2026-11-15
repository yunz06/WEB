<?php

class CategoryController extends Controller {
    private $categoryModel;
    private $baseUrl;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->categoryModel = $this->model('Category');

        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $this->baseUrl = rtrim($scriptDir, '/');
    }

    // URL: /CategoryController/index/{page}
    public function index($page = 1) {
        $this->renderList($page, null);
    }

    // URL: /CategoryController/edit/{id}/{page}
    public function edit($id = 0, $page = 1) {
        $editCategory = $this->categoryModel->getById((int)$id);
        if (!$editCategory) {
            $_SESSION['error'] = "Không tìm thấy danh mục #" . (int)$id . "!";
            $this->redirect('/CategoryController/index');
        }
        $this->renderList($page, $editCategory);
    }

    // URL (POST): /CategoryController/store
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/CategoryController/index');
        }

        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($name === '') {
            $_SESSION['error'] = "Vui lòng nhập tên danh mục!";
            $this->redirect('/CategoryController/index');
        }
        if ($this->categoryModel->nameExists($name)) {
            $_SESSION['error'] = "Tên danh mục đã tồn tại!";
            $this->redirect('/CategoryController/index');
        }

        $this->categoryModel->create([
            'name'        => $name,
            'slug'        => $this->createSlug($name),
            'description' => $description
        ]);

        $_SESSION['success'] = "Thêm danh mục thành công!";
        $this->redirect('/CategoryController/index');
    }

    // URL (POST): /CategoryController/update/{id}
    public function update($id = 0) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/CategoryController/index');
        }

        $id = (int)$id;
        if (!$this->categoryModel->getById($id)) {
            $_SESSION['error'] = "Danh mục không tồn tại!";
            $this->redirect('/CategoryController/index');
        }

        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($name === '') {
            $_SESSION['error'] = "Vui lòng nhập tên danh mục!";
            $this->redirect('/CategoryController/edit/' . $id);
        }
        if ($this->categoryModel->nameExists($name, $id)) {
            $_SESSION['error'] = "Tên danh mục đã tồn tại!";
            $this->redirect('/CategoryController/edit/' . $id);
        }

        $this->categoryModel->update($id, [
            'name'        => $name,
            'slug'        => $this->createSlug($name),
            'description' => $description
        ]);

        $_SESSION['success'] = "Cập nhật danh mục #$id thành công!";
        $this->redirect('/CategoryController/index');
    }

    // URL: /CategoryController/delete/{id}
    public function delete($id = 0) {
        $id = (int)$id;

        if ($this->categoryModel->countArticles($id) > 0) {
            $_SESSION['error'] = "Không thể xóa: danh mục đang có bài viết. Hãy chuyển bài viết sang danh mục khác trước!";
            $this->redirect('/CategoryController/index');
        }

        if ($this->categoryModel->getById($id)) {
            $this->categoryModel->delete($id);
            $_SESSION['success'] = "Đã xóa danh mục #$id!";
        }
        $this->redirect('/CategoryController/index');
    }

    private function renderList($page, $editCategory) {
        $limit = 8;
        $page = is_numeric($page) && (int)$page > 0 ? (int)$page : 1;
        $offset = ($page - 1) * $limit;

        $total = $this->categoryModel->countAll();

        $this->view('admin/categories', [
            'page_title'      => 'Quản lý danh mục - NEWS HUB',
            'baseUrl'         => $this->baseUrl,
            'categories'      => $this->categoryModel->getAll($limit, $offset),
            'editCategory'    => $editCategory,
            'page'            => $page,
            'totalPages'      => max(1, (int)ceil($total / $limit)),
            'totalCategories' => $total
        ]);
    }

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
        return trim(preg_replace('/[\s-]+/', '-', trim($str)), '-');
    }

    private function redirect($path) {
        header("Location: " . $this->baseUrl . $path);
        exit;
    }
}
