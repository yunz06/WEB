<?php

declare(strict_types=1);

require_once __DIR__ . '/Database.php';

abstract class Controller
{
    protected function csrfToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new RuntimeException('Session is not active.');
        }

        $token = $_SESSION['csrf_token'] ?? null;
        if (!is_string($token) || !preg_match('/\A[a-f0-9]{64}\z/', $token)) {
            $token = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $token;
        }

        return $token;
    }

    public static function csrfTokenIsValid($submittedToken): bool
    {
        $expectedToken = $_SESSION['csrf_token'] ?? null;

        if (!is_string($expectedToken)
            || !is_string($submittedToken)
            || !preg_match('/\A[a-f0-9]{64}\z/', $expectedToken)
            || !preg_match('/\A[a-f0-9]{64}\z/', $submittedToken)) {
            return false;
        }

        return hash_equals($expectedToken, $submittedToken);
    }

    protected function model(string $modelName)
    {
        if (!preg_match('/\A[A-Za-z][A-Za-z0-9]*\z/', $modelName)) {
            throw new InvalidArgumentException('Invalid model name.');
        }

        $modelFile = __DIR__ . '/../Models/' . $modelName . '.php';
        if (!is_file($modelFile)) {
            throw new RuntimeException('Model file not found.');
        }

        require_once $modelFile;

        if (!class_exists($modelName, false)) {
            throw new RuntimeException('Model class is not implemented.');
        }

        $connection = Database::getInstance()->getConnection();
        return new $modelName($connection);
    }

    protected function view(string $viewName, array $data = []): void
    {
        if (!preg_match('/\A[A-Za-z0-9_-]+(?:\/[A-Za-z0-9_-]+)*\z/', $viewName)) {
            throw new InvalidArgumentException('Invalid view name.');
        }

        $viewsRoot = realpath(__DIR__ . '/../Views');
        $viewFile = realpath(__DIR__ . '/../Views/' . $viewName . '.php');

        if ($viewsRoot === false || $viewFile === false || !is_file($viewFile)) {
            throw new RuntimeException('View file not found.');
        }

        $rootPrefix = rtrim($viewsRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (strncmp($viewFile, $rootPrefix, strlen($rootPrefix)) !== 0) {
            throw new RuntimeException('View path is outside the views directory.');
        }

        require $viewFile;
    }

    // Existing controllers define their own private redirect() helper.
    protected function redirectTo(string $path, int $statusCode = 302): void
    {
        if (!str_starts_with($path, '/')
            || str_starts_with($path, '//')
            || preg_match('/[\r\n]/', $path)) {
            throw new InvalidArgumentException('Invalid redirect path.');
        }

        header('Location: ' . $path, true, $statusCode);
        exit;
    }
}
