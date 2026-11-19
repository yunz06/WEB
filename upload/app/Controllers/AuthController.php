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
        if (isset($_SESSION['user']['id'])) {
            if (($_SESSION['user']['role_name'] ?? '') === 'admin') {
                $this->redirectTo($this->baseUrl . '/DashboardController/index');
            }
            if ($this->isAdminEntry()) {
                $this->renderAuth('Đăng nhập quản trị', 'auth_login', [
                    'adminEntry' => true,
                    'error' => 'Bạn đang đăng nhập bằng tài khoản độc giả. Hãy đăng xuất trước khi đăng nhập tài khoản quản trị.',
                ]);
                return;
            }
            $this->redirectTo($this->baseUrl . '/AccountController/index');
        }
        $this->renderAuth('Đăng nhập', 'auth_login', ['adminEntry' => $this->isAdminEntry()]);
    }

    public function register(): void
    {
        if (isset($_SESSION['user']['id'])) $this->redirectTo($this->baseUrl . '/AccountController/index');
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
            $this->renderAuth('Đăng nhập', 'auth_login', [
                'error' => 'Bạn đã thử quá nhiều lần. Hãy đợi 15 phút rồi thử lại.',
                'email' => $email,
                'adminEntry' => $this->isAdminEntry(),
            ]);
            return;
        }
        $user = filter_var($email, FILTER_VALIDATE_EMAIL) ? $this->users->findByEmail($email) : null;
        if (!$user || $user['status'] !== 'active' || !password_verify($password, $user['password_hash'])) {
            $attempts['count'] = (int)$attempts['count'] + 1;
            $_SESSION['login_attempts'] = $attempts;
            $this->renderAuth('Đăng nhập', 'auth_login', [
                'error' => 'Email hoặc mật khẩu không đúng, hoặc tài khoản đã khóa.',
                'email' => $email,
                'adminEntry' => $this->isAdminEntry(),
            ]);
            return;
        }
        if ($this->isAdminEntry() && $user['role_name'] !== 'admin') {
            $this->renderAuth('Đăng nhập quản trị', 'auth_login', [
                'error' => 'Tài khoản này chưa được cấp quyền quản trị. Hãy đăng nhập bằng tài khoản Admin.',
                'email' => $email,
                'adminEntry' => true,
            ]);
            return;
        }
        session_regenerate_id(true);
        unset($_SESSION['login_attempts']);
        $_SESSION['user'] = ['id' => (int)$user['id'], 'full_name' => $user['full_name'], 'role_name' => $user['role_name']];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $this->redirectTo($this->baseUrl . ($user['role_name'] === 'admin'
            ? '/DashboardController/index'
            : '/AccountController/index'));
    }

    public function createAccount(): void
    {
        $nameInput = $_POST['full_name'] ?? '';
        $emailInput = $_POST['email'] ?? '';
        $passwordInput = $_POST['password'] ?? '';
        $confirmationInput = $_POST['password_confirm'] ?? '';
        $name = is_string($nameInput) ? trim($nameInput) : '';
        $email = is_string($emailInput) ? strtolower(trim($emailInput)) : '';
        $password = is_string($passwordInput) ? $passwordInput : '';
        $passwordConfirmation = is_string($confirmationInput) ? $confirmationInput : '';
        $passwordIsStrong = self::passwordIsAcceptable($password, $passwordConfirmation);
        $formValues = ['full_name' => $name, 'email' => $email];
        if ($name === '' || mb_strlen($name, 'UTF-8') > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)
            || strlen($email) > 255 || !$passwordIsStrong || $password !== $passwordConfirmation) {
            http_response_code(422);
            $this->renderAuth('Tạo tài khoản', 'auth_register', [
                'error' => 'Kiểm tra họ tên, email và mật khẩu. Mật khẩu cần từ 12 ký tự, có ít nhất 3 nhóm chữ/số/ký hiệu, tối đa 72 byte và phần xác nhận phải khớp.',
                'formValues' => $formValues,
            ]);
            return;
        }
        if ($this->users->findByEmail($email)) {
            http_response_code(422);
            $this->renderAuth('Tạo tài khoản', 'auth_register', [
                'error' => 'Email này đã được đăng ký.',
                'formValues' => $formValues,
            ]);
            return;
        }
        $role = $this->users->roleIdByName('user');
        if (!$role) throw new RuntimeException('The user role is not configured.');
        try {
            $id = $this->users->create($name, $email, password_hash($password, PASSWORD_DEFAULT), $role);
        } catch (PDOException $exception) {
            if ((string)$exception->getCode() !== '23000') throw $exception;
            http_response_code(422);
            $this->renderAuth('Tạo tài khoản', 'auth_register', [
                'error' => 'Email này đã được đăng ký.',
                'formValues' => $formValues,
            ]);
            return;
        }
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => $id, 'full_name' => $name, 'role_name' => 'user'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $this->redirectTo($this->baseUrl . '/AccountController/index');
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
        $adminEntry = $mode === 'auth_login' && (bool)($extra['adminEntry'] ?? $this->isAdminEntry());
        $data = array_merge($extra, ['page_title' => $title . ' — NEWS HUB', 'baseUrl' => $this->baseUrl,
            'mode' => $mode, 'adminEntry' => $adminEntry, 'csrf_token' => $this->csrfToken(),
            'categories' => $this->model('Category')->getPublicNavigation()]);
        $this->view('frontend/auth', $data);
    }

    private function isAdminEntry(): bool
    {
        return is_string($_GET['area'] ?? null) && $_GET['area'] === 'admin';
    }

    private static function passwordIsAcceptable(string $password, string $confirmation): bool
    {
        if (!mb_check_encoding($password, 'UTF-8')
            || strlen($password) > 72
            || mb_strlen($password, 'UTF-8') < 12
            || !hash_equals($password, $confirmation)) {
            return false;
        }

        $classes = [
            preg_match('/\p{Ll}/u', $password) === 1,
            preg_match('/\p{Lu}/u', $password) === 1,
            preg_match('/\p{N}/u', $password) === 1,
            preg_match('/[\p{P}\p{S}]/u', $password) === 1,
        ];
        return count(array_filter($classes)) >= 3;
    }
}
