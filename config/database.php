<?php

// XAMPP local defaults. Override with NEWS_HUB_DB_* environment variables
// if the local MariaDB host, port, database, username, password, or charset differs.
$readNewsHubDbEnv = static function (string $key, string $default): string {
    $value = getenv($key);
    return ($value === false || $value === '') ? $default : $value;
};

if (!defined('DB_HOST')) {
    define('DB_HOST', $readNewsHubDbEnv('NEWS_HUB_DB_HOST', '127.0.0.1'));
}

if (!defined('DB_PORT')) {
    $portValue = $readNewsHubDbEnv('NEWS_HUB_DB_PORT', '3306');
    $port = filter_var($portValue, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 65535],
    ]);
    define('DB_PORT', $port === false ? 3306 : $port);
}

if (!defined('DB_NAME')) {
    define('DB_NAME', $readNewsHubDbEnv('NEWS_HUB_DB_NAME', 'news_hub'));
}

if (!defined('DB_USER')) {
    define('DB_USER', $readNewsHubDbEnv('NEWS_HUB_DB_USER', 'root'));
}

if (!defined('DB_PASS')) {
    $password = getenv('NEWS_HUB_DB_PASS');
    define('DB_PASS', $password === false ? '' : $password);
}

if (!defined('DB_CHARSET')) {
    $charset = $readNewsHubDbEnv('NEWS_HUB_DB_CHARSET', 'utf8mb4');
    define('DB_CHARSET', preg_match('/\A[A-Za-z0-9_]+\z/', $charset) ? $charset : 'utf8mb4');
}
