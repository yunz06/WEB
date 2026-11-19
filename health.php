<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store, max-age=0');

try {
    require_once __DIR__ . '/app/Core/Database.php';
    Database::getInstance()->getConnection()->query('SELECT 1')->fetchColumn();
    http_response_code(200);
    echo "ok\n";
} catch (Throwable $exception) {
    error_log('NEWS HUB health check failed.');
    http_response_code(503);
    echo "unavailable\n";
}
