<?php

class UserController extends Controller {
    private $userModel;
    private $baseUrl;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // User.php do TV1 quản lý, TV3 chỉ gọi các hàm của Model này
        $this->userModel = $this->model('User');

        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $this->baseUrl = rtrim($scriptDir, '/');
    }

    // URL: /UserController/index/{page}
    public function index($page = 1) { 
        $queryInput = $_GET['q'] ?? '';
        $query = is_string($queryInput) ? trim($queryInput) : '';
        if (mb_strlen($query, 'UTF-8') > 100) $query = mb_substr($query, 0, 100, 'UTF-8');
        $this->renderList($page, null, $this->userModel->countAll($query), $query);
    }

    // URL: /UserController/edit/{id}/{page}
    public function edit($id = 0, $page = 1) {
        $editUser = $this->userModel->getById((int)$id);
        if (!$editUser) {
            $_SESSION['error'] = "Không tìm thấy người dùng #" . (int)$id . "!";
            $this->redirect('/UserController/index');
        }
        $this->renderList($page, $editUser, $this->userModel->countAll());
    }

    // URL (POST): /UserController/update/{id}
    public function update($id = 0) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/UserController/index');
        }

        $id = (int)$id;
        if (!$this->userModel->getById($id)) {
            $_SESSION['error'] = "Người dùng không tồn tại!";
            $this->redirect('/UserController/index');
        }

        $nameInput = $_POST['full_name'] ?? '';
        $emailInput = $_POST['email'] ?? '';
        $roleInput = $_POST['role_id'] ?? '';
        $fullName = is_string($nameInput) ? trim($nameInput) : '';
        $email = is_string($emailInput) ? trim($emailInput) : '';
        $roleId = is_string($roleInput) ? (int)$roleInput : 0;

        if ($fullName === '' || mb_strlen($fullName, 'UTF-8') > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)
            || strlen($email) > 255 || $roleId <= 0) {
            $_SESSION['error'] = "Vui lòng nhập Họ tên, Email hợp lệ và chọn Vai trò!";
            $this->redirect('/UserController/edit/' . $id);
        }

        $target = $this->userModel->getById($id);
        $roles = $this->userModel->getRoles();
        $roleNames = [];
        foreach ($roles as $role) $roleNames[(int)$role['id']] = $role['name'];
        if (!isset($roleNames[$roleId]) || $this->userModel->emailExists($email, $id)) {
            $_SESSION['error'] = 'Email hoặc vai trò không hợp lệ.';
            $this->redirect('/UserController/edit/' . $id);
        }
        if ($this->isSelf($id) && $roleNames[$roleId] !== 'admin') {
            $_SESSION['error'] = 'Không thể tự hạ quyền Admin của chính mình.';
            $this->redirect('/UserController/edit/' . $id);
        }
        if (($target['role_name'] ?? '') === 'admin' && $roleNames[$roleId] !== 'admin'
            && $target['status'] === 'active' && $this->userModel->countAdmins() <= 1) {
            $_SESSION['error'] = 'Không thể hạ quyền Admin hoạt động cuối cùng.';
            $this->redirect('/UserController/edit/' . $id);
        }

        $this->userModel->updateBasic($id, [
            'full_name' => $fullName,
            'email'     => $email,
            'role_id'   => $roleId
        ]);

        $_SESSION['success'] = "Cập nhật người dùng #$id thành công!";
        $this->redirect('/UserController/index');
    }

    // URL: /UserController/toggleStatus/{id}/{page}  (khóa <-> mở khóa)
    public function toggleStatus($id = 0, $page = 1) {
        $id = (int)$id;
        $user = $this->userModel->getById($id);

        if (!$user) {
            $_SESSION['error'] = "Người dùng không tồn tại!";
        } elseif ($this->isSelf($id)) {
            $_SESSION['error'] = "Bạn không thể tự khóa tài khoản của chính mình!";
        } elseif ($user['role_name'] === 'admin' && $user['status'] === 'active' && $this->userModel->countAdmins() <= 1) {
            $_SESSION['error'] = 'Không thể khóa Admin hoạt động cuối cùng.';
        } else {
            $newStatus = ($user['status'] === 'active') ? 'locked' : 'active';
            $this->userModel->setStatus($id, $newStatus);
            $_SESSION['success'] = ($newStatus === 'locked' ? "Đã khóa" : "Đã mở khóa") . " tài khoản #$id!";
        }

        $this->redirect('/UserController/index/' . (int)$page);
    }

    private function renderList($page, $editUser, $total = null, string $query = '') {
        $limit = 10;
        $page = is_numeric($page) && (int)$page > 0 ? (int)$page : 1;
        $offset = ($page - 1) * $limit;

        $total ??= $this->userModel->countAll($query);

        $this->view('admin/users', [
            'page_title' => 'Quản lý người dùng - NEWS HUB',
            'baseUrl'    => $this->baseUrl,
            'users'      => $this->userModel->getAllWithRole($limit, $offset, $query),
            'roles'      => $this->userModel->getRoles(),
            'editUser'   => $editUser,
            'page'       => $page,
            'totalPages' => max(1, (int)ceil($total / $limit)),
            'totalUsers' => $total,
            'query'      => $query,
            'currentId'  => (int)($_SESSION['user']['id'] ?? 0),
            'csrf_token' => $this->csrfToken()
        ]);
    }

    private function isSelf($id) {
        return isset($_SESSION['user']['id']) && (int)$_SESSION['user']['id'] === (int)$id;
    }

    private function redirect($path) {
        header("Location: " . $this->baseUrl . $path);
        exit;
    }
}
