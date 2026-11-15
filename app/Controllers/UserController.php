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
        $this->renderList($page, null);
    }

    // URL: /UserController/edit/{id}/{page}
    public function edit($id = 0, $page = 1) {
        $editUser = $this->userModel->getById((int)$id);
        if (!$editUser) {
            $_SESSION['error'] = "Không tìm thấy người dùng #" . (int)$id . "!";
            $this->redirect('/UserController/index');
        }
        $this->renderList($page, $editUser);
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

        $fullName = trim($_POST['full_name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $roleId   = (int)($_POST['role_id'] ?? 0);

        if ($fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $roleId <= 0) {
            $_SESSION['error'] = "Vui lòng nhập Họ tên, Email hợp lệ và chọn Vai trò!";
            $this->redirect('/UserController/edit/' . $id);
        }

        // Không cho admin tự hạ quyền chính mình
        if ($this->isSelf($id) && $roleId !== (int)($_SESSION['user']['role_id'] ?? $roleId)) {
            $_SESSION['error'] = "Bạn không thể tự đổi vai trò của chính mình!";
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
        } else {
            $newStatus = ($user['status'] === 'active') ? 'locked' : 'active';
            $this->userModel->setStatus($id, $newStatus);
            $_SESSION['success'] = ($newStatus === 'locked' ? "Đã khóa" : "Đã mở khóa") . " tài khoản #$id!";
        }

        $this->redirect('/UserController/index/' . (int)$page);
    }

    private function renderList($page, $editUser) {
        $limit = 10;
        $page = is_numeric($page) && (int)$page > 0 ? (int)$page : 1;
        $offset = ($page - 1) * $limit;

        $total = $this->userModel->countAll();

        $this->view('admin/users', [
            'page_title' => 'Quản lý người dùng - NEWS HUB',
            'baseUrl'    => $this->baseUrl,
            'users'      => $this->userModel->getAllWithRole($limit, $offset),
            'roles'      => $this->userModel->getRoles(),
            'editUser'   => $editUser,
            'page'       => $page,
            'totalPages' => max(1, (int)ceil($total / $limit)),
            'totalUsers' => $total,
            'currentId'  => (int)($_SESSION['user']['id'] ?? 0)
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
