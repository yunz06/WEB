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

        $nameInput = $_POST['name'] ?? '';
        $descriptionInput = $_POST['description'] ?? '';
        $name = is_string($nameInput) ? trim($nameInput) : '';
        $description = is_string($descriptionInput) ? trim($descriptionInput) : '';

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
            'slug'        => $this->uniqueSlug($name),
            'description' => $description,
            'parent_id'   => $this->parentIdFromPost(),
            'sort_order'  => max(0, (int)($_POST['sort_order'] ?? 0)),
            'status'      => ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active'
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

        $nameInput = $_POST['name'] ?? '';
        $descriptionInput = $_POST['description'] ?? '';
        $name = is_string($nameInput) ? trim($nameInput) : '';
        $description = is_string($descriptionInput) ? trim($descriptionInput) : '';

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
            'slug'        => $this->uniqueSlug($name, $id),
            'description' => $description,
            'parent_id'   => $this->parentIdFromPost($id),
            'sort_order'  => max(0, (int)($_POST['sort_order'] ?? 0)),
            'status'      => ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active'
        ]);

        $_SESSION['success'] = "Cập nhật danh mục #$id thành công!";
        $this->redirect('/CategoryController/index');
    }

    // URL: /CategoryController/delete/{id}
    public function delete($id = 0) {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            return;
        }

        $id = (int)$id;

        if ($this->categoryModel->countArticles($id) > 0 || $this->categoryModel->countChildren($id) > 0) {
            $_SESSION['error'] = "Không thể xóa: danh mục đang có bài viết hoặc danh mục con.";
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
            'parentCategories' => $this->categoryModel->getChildren((int)($editCategory['id'] ?? 0)),
            'editCategory'    => $editCategory,
            'page'            => $page,
            'totalPages'      => max(1, (int)ceil($total / $limit)),
            'totalCategories' => $total,
            'csrf_token'      => $this->csrfToken()
        ]);
    }

    private function uniqueSlug(string $name, int $excludeId = 0): string {
        $base = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', mb_strtolower($name, 'UTF-8')) ?: $name;
        $base = trim(preg_replace('/[^a-z0-9]+/i', '-', strtolower($base)) ?? '', '-');
        if ($base === '') $base = 'category';
        $candidate = substr($base, 0, 240);
        for ($suffix = 2; $this->categoryModel->slugExists($candidate, $excludeId); $suffix++) {
            $tail = '-' . $suffix;
            $candidate = substr($base, 0, 255 - strlen($tail)) . $tail;
        }
        return $candidate;
    }

    private function parentIdFromPost(int $excludeId = 0): ?int {
        $parentInput = $_POST['parent_id'] ?? null;
        $parent = is_string($parentInput) ? filter_var($parentInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;
        if (!$parent) return null;
        $parentCategory = $this->categoryModel->getById((int)$parent);
        if (!$parentCategory || $parentCategory['parent_id'] !== null || (int)$parent === $excludeId) return null;
        return (int)$parent;
    }

    private function redirect($path) {
        header("Location: " . $this->baseUrl . $path);
        exit;
    }
}
