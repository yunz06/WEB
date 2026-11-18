<?php

declare(strict_types=1);

function newsHubRespond(int $statusCode, string $message, array $headers = []): void
{
    http_response_code($statusCode);
    header('Content-Type: text/plain; charset=utf-8');

    foreach ($headers as $header) {
        header($header);
    }

    echo $message;
    exit;
}

function newsHubCsrfTokenIsValid(): bool
{
    $submitted = $_POST['csrf_token'] ?? null;
    return Controller::csrfTokenIsValid($submitted);
}

function newsHubIsAdminAuthenticated(): bool
{
    return (int)($_SESSION['user']['id'] ?? 0) > 0
        && ($_SESSION['user']['role_name'] ?? '') === 'admin';
}

function newsHubRefreshSessionUser(): void
{
    $userId = (int)($_SESSION['user']['id'] ?? 0);
    if ($userId < 1) return;
    $stmt = Database::getInstance()->getConnection()->prepare(
        "SELECT u.id,u.full_name,u.status,r.name AS role_name FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id=:id LIMIT 1"
    );
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch();
    if (!$user || $user['status'] !== 'active') {
        unset($_SESSION['user']);
        return;
    }
    $_SESSION['user'] = ['id' => (int)$user['id'], 'full_name' => $user['full_name'], 'role_name' => $user['role_name']];
}

function newsHubServerError(Throwable $exception): void
{
    error_log('NEWS HUB request failed: ' . $exception);
    newsHubRespond(500, 'Đã xảy ra lỗi khi xử lý yêu cầu.');
}

try {
    set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        throw new ErrorException($message, 0, $severity, $file, $line);
    });

    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/app/Core/Database.php';
    require_once __DIR__ . '/app/Core/Controller.php';

    $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/');
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => $basePath === '' ? '/' : $basePath . '/',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_start();
    }
    newsHubRefreshSessionUser();

    // Only explicitly listed controller actions can be dispatched.
    $routes = [
        'HomeController' => [
            'index' => ['GET', [], false],
        ],
        'FrontendArticleController' => [
            'show' => ['GET', ['id'], false],
        ],
        'PublicCategoryController' => [
            'show' => ['GET', ['slug', 'page?'], false],
        ],
        'SearchController' => [
            'index' => ['GET', ['page?'], false],
        ],
        'AuthController' => [
            'login' => ['GET', [], false],
            'register' => ['GET', [], false],
            'authenticate' => ['POST', [], false],
            'createAccount' => ['POST', [], false],
            'logout' => ['POST', [], false],
        ],
        'DashboardController' => [
            'index' => ['GET', [], true],
        ],
        'CommentController' => [
            'store' => ['POST', ['id'], false],
            'index' => ['GET', ['page?'], true],
            'updateStatus' => ['POST', ['id'], true],
            'delete' => ['POST', ['id'], true],
        ],
        'ArticleController' => [
            'index'       => ['GET',  ['page?'], true],
            'edit'        => ['GET',  ['id', 'page?'], true],
            'store'       => ['POST', [], true],
            'update'      => ['POST', ['id'], true],
            'delete'      => ['POST', ['id'], true],
            'deleteImage' => ['POST', ['id', 'id'], true],
        ],
        'CategoryController' => [
            'index'  => ['GET',  ['page?'], true],
            'edit'   => ['GET',  ['id', 'page?'], true],
            'store'  => ['POST', [], true],
            'update' => ['POST', ['id'], true],
            'delete' => ['POST', ['id'], true],
        ],
        'UserController' => [
            'index'        => ['GET',  ['page?'], true],
            'edit'         => ['GET',  ['id', 'page?'], true],
            'update'       => ['POST', ['id'], true],
            'toggleStatus' => ['POST', ['id', 'page?'], true],
        ],
    ];

    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (!is_string($requestPath) || str_contains($requestPath, '//')) {
        newsHubRespond(404, 'Trang không tồn tại.');
    }

    $scriptDirectory = $basePath;
    $scriptDirectory = rtrim($scriptDirectory, '/');

    if ($scriptDirectory !== ''
        && ($requestPath === $scriptDirectory
            || strncmp($requestPath, $scriptDirectory . '/', strlen($scriptDirectory) + 1) === 0)) {
        $requestPath = substr($requestPath, strlen($scriptDirectory));
    }

    $trimmedPath = trim($requestPath, '/');
    $segments = $trimmedPath === '' ? [] : explode('/', $trimmedPath);

    $scriptFile = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
    if (isset($segments[0]) && $segments[0] === $scriptFile) {
        array_shift($segments);
    }

    if ($segments === []) {
        $segments = ['HomeController', 'index'];
    }

    if (count($segments) < 1 || count($segments) > 4 || in_array('', $segments, true)) {
        newsHubRespond(404, 'Trang không tồn tại.');
    }

    $segments = array_map('rawurldecode', $segments);
    $controllerName = $segments[0];
    $action = $segments[1] ?? 'index';
    $route = $routes[$controllerName][$action] ?? null;

    if ($route === null) {
        newsHubRespond(404, 'Trang không tồn tại.');
    }

    [$allowedMethod, $parameterSchema, $adminOnly] = $route;
    $requestMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

    if ($requestMethod !== $allowedMethod) {
        newsHubRespond(405, 'Phương thức yêu cầu không được hỗ trợ.', ['Allow: ' . $allowedMethod]);
    }

    $routeParameters = array_slice($segments, 2);
    $requiredParameters = count(array_filter(
        $parameterSchema,
        static fn(string $parameter): bool => !str_ends_with($parameter, '?')
    ));

    if (count($routeParameters) < $requiredParameters
        || count($routeParameters) > count($parameterSchema)) {
        newsHubRespond(404, 'Trang không tồn tại.');
    }

    $parameters = [];
    foreach ($routeParameters as $index => $value) {
        $parameterType = rtrim($parameterSchema[$index], '?');
        if ($parameterType === 'slug') {
            if (strlen($value) > 255 || !preg_match('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/', $value)) {
                newsHubRespond(404, 'Trang không tồn tại.');
            }
            $parameters[] = $value;
            continue;
        }
        $maximum = $parameterType === 'id' ? 4294967295 : 1000000;
        if (!preg_match('/\A[1-9][0-9]{0,9}\z/', $value) || (int)$value > $maximum) {
            newsHubRespond(404, 'Trang không tồn tại.');
        }
        $parameters[] = (int)$value;
    }

    $controllerFile = __DIR__ . '/app/Controllers/' . $controllerName . '.php';
    if (!is_file($controllerFile)) {
        newsHubRespond(404, 'Trang không tồn tại.');
    }

    require_once $controllerFile;
    if (!class_exists($controllerName, false) || !method_exists($controllerName, $action)) {
        newsHubRespond(404, 'Trang không tồn tại.');
    }

    $actionMethod = new ReflectionMethod($controllerName, $action);
    if (!$actionMethod->isPublic()
        || $actionMethod->isStatic()
        || $actionMethod->isConstructor()
        || $actionMethod->isDestructor()
        || str_starts_with($action, '__')) {
        newsHubRespond(404, 'Trang không tồn tại.');
    }

    if ($requestMethod === 'POST' && !newsHubCsrfTokenIsValid()) {
        newsHubRespond(403, 'Yêu cầu bị từ chối.');
    }

    if ($adminOnly && !newsHubIsAdminAuthenticated()) {
        newsHubRespond(403, 'Yêu cầu bị từ chối.');
    }

    $controller = new $controllerName();
    if (!is_callable([$controller, $action])) {
        newsHubRespond(404, 'Trang không tồn tại.');
    }

    call_user_func_array([$controller, $action], $parameters);
} catch (Throwable $exception) {
    if ($exception instanceof InvalidArgumentException) {
        newsHubRespond(422, 'Dữ liệu gửi lên không hợp lệ.');
    }
    newsHubServerError($exception);
}
