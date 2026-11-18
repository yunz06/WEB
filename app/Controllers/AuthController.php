<?php

declare(strict_types=1);

class AuthController extends Controller
{
    private User $users;
    private string $baseUrl;

    public function __construct()
    {
        $this->users = $this->model('User');
        $this->baseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/');
    }

    public function login(): void
    {
        if (isset($_SESSION['user']['id'])) $this->redirectTo($this->baseUrl . '/');
        $this->renderAuth('Đăng nhập', 'auth_login', []);
    }

    public function register(): void
    {
        if (isset($_SESSION['user']['id'])) $this->redirectTo($this->baseUrl . '/');
        $this->renderAuth('Tạo tài khoản', 'auth_register', []);
    }

    public function authenticate(): void
    {
        $emailInput = $_POST['email'] ?? '';
        $passwordInput = $_POST['password'] ?? '';
        $email = is_string($emailInput) ? strtolower(trim($emailInput)) : '';
        $password = is_string($passwordInput) ? $passwordInput : '';
        $attempts = $_SESSION['login_attempts'] ?? ['count' => 0, 'start' => time()];
        if (!is_array($attempts) || time() - (int)($attempts['start'] ?? 0) > 900) $attempts = ['count' => 0, 'start' => time()];
        if ((int)$attempts['count'] >= 8) {
            http_response_code(429);
            $this->renderAuth('Đăng nhập', 'auth_login', ['error' => 'Bạn đã thử quá nhiều lần. Hãy đợi 15 phút rồi thử lại.']);
            return;
        }
        $user = filter_var($email, FILTER_VALIDATE_EMAIL) ? $this->users->findByEmail($email) : null;
        if (!$user || $user['status'] !== 'active' || !password_verify($password, $user['password_hash'])) {
            $attempts['count'] = (int)$attempts['count'] + 1;
            $_SESSION['login_attempts'] = $attempts;
            $this->renderAuth('Đăng nhập', 'auth_login', ['error' => 'Email hoặc mật khẩu không đúng, hoặc tài khoản đã khóa.']);
            return;
        }
        session_regenerate_id(true);
        unset($_SESSION['login_attempts']);
        $_SESSION['user'] = ['id' => (int)$user['id'], 'full_name' => $user['full_name'], 'role_name' => $user['role_name']];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $this->redirectTo($this->baseUrl . ($user['role_name'] === 'admin' ? '/DashboardController/index' : '/'));
    }

    public function createAccount(): void
    {
        $nameInput = $_POST['full_name'] ?? '';
        $emailInput = $_POST['email'] ?? '';
        $passwordInput = $_POST['password'] ?? '';
        $name = is_string($nameInput) ? trim($nameInput) : '';
        $email = is_string($emailInput) ? strtolower(trim($emailInput)) : '';
        $password = is_string($passwordInput) ? $passwordInput : '';
        if ($name === '' || mb_strlen($name, 'UTF-8') > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)
            || strlen($email) > 255 || strlen($password) < 10 || strlen($password) > 4096) {
            http_response_code(422);
            $this->renderAuth('Tạo tài khoản', 'auth_register', ['error' => 'Kiểm tra họ tên, email và mật khẩu (tối thiểu 10 ký tự).']);
            return;
        }
        if ($this->users->findByEmail($email)) {
            http_response_code(422);
            $this->renderAuth('Tạo tài khoản', 'auth_register', ['error' => 'Email này đã được đăng ký.']);
            return;
        }
        $role = $this->users->roleIdByName('user');
        if (!$role) throw new RuntimeException('The user role is not configured.');
        try {
            $id = $this->users->create($name, $email, password_hash($password, PASSWORD_DEFAULT), $role);
        } catch (PDOException $exception) {
            if ((string)$exception->getCode() !== '23000') throw $exception;
            http_response_code(422);
            $this->renderAuth('Tạo tài khoản', 'auth_register', ['error' => 'Email này đã được đăng ký.']);
            return;
        }
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => $id, 'full_name' => $name, 'role_name' => 'user'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $this->redirectTo($this->baseUrl . '/');
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', ['expires' => time() - 42000, 'path' => $params['path'],
                'domain' => $params['domain'], 'secure' => $params['secure'], 'httponly' => true, 'samesite' => 'Lax']);
        }
        session_destroy();
        $this->redirectTo($this->baseUrl . '/');
    }

    private function renderAuth(string $title, string $mode, array $extra): void
    {
        $data = array_merge($extra, ['page_title' => $title . ' — NEWS HUB', 'baseUrl' => $this->baseUrl,
            'mode' => $mode, 'csrf_token' => $this->csrfToken(), 'categories' => $this->model('Category')->getPublicNavigation()]);
        $this->view('frontend/auth', $data);
    }
}
