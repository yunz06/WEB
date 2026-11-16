<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Core/Controller.php';

function newsHubNotFound(): void
{
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Trang không tồn tại.';
}

function newsHubServerError(Throwable $exception): void
{
    error_log('NEWS HUB request failed: ' . $exception->getMessage());
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Đã xảy ra lỗi khi xử lý yêu cầu.';
}

try {
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (!is_string($requestPath)) {
        newsHubNotFound();
        exit;
    }

    $scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    $scriptDirectory = rtrim($scriptDirectory, '/');

    if ($scriptDirectory !== ''
        && ($requestPath === $scriptDirectory
            || strncmp($requestPath, $scriptDirectory . '/', strlen($scriptDirectory) + 1) === 0)) {
        $requestPath = substr($requestPath, strlen($scriptDirectory));
    }

    $segments = array_values(array_filter(
        explode('/', trim($requestPath, '/')),
        static fn(string $segment): bool => $segment !== ''
    ));

    $scriptFile = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
    if (isset($segments[0]) && $segments[0] === $scriptFile) {
        array_shift($segments);
    }

    $controllerName = isset($segments[0]) ? rawurldecode($segments[0]) : '';
    $action = isset($segments[1]) ? rawurldecode($segments[1]) : 'index';
    $parameters = array_map('rawurldecode', array_slice($segments, 2));

    if (!preg_match('/\A[A-Za-z][A-Za-z0-9]*Controller\z/', $controllerName)
        || !preg_match('/\A[A-Za-z][A-Za-z0-9]*\z/', $action)) {
        newsHubNotFound();
        exit;
    }

    $controllerFile = __DIR__ . '/app/Controllers/' . $controllerName . '.php';
    if (!is_file($controllerFile)) {
        newsHubNotFound();
        exit;
    }

    require_once $controllerFile;
    if (!class_exists($controllerName, false)) {
        newsHubNotFound();
        exit;
    }

    $controller = new $controllerName();
    if (!is_callable([$controller, $action])) {
        newsHubNotFound();
        exit;
    }

    call_user_func_array([$controller, $action], $parameters);
} catch (Throwable $exception) {
    newsHubServerError($exception);
}
