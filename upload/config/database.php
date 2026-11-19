<?php

declare(strict_types=1);

/**
 * NEWS HUB database settings.
 * Priority: hosting environment variables -> config/database.local.php -> XAMPP defaults.
 * Never commit database.local.php; it contains private hosting credentials.
 */
$localConfigPath = __DIR__ . '/database.local.php';
$localConfig = [];
if (is_file($localConfigPath)) {
    $loadedConfig = require $localConfigPath;
    if (is_array($loadedConfig)) {
        $localConfig = $loadedConfig;
    }
}

$readSetting = static function (string $key, string $envName, string $default) use ($localConfig): string {
    $environmentValue = getenv($envName);
    if ($environmentValue !== false && $environmentValue !== '') {
        return (string) $environmentValue;
    }

    $localValue = $localConfig[$key] ?? null;
    if (is_scalar($localValue) && (string) $localValue !== '') {
        return (string) $localValue;
    }

    return $default;
};

if (!defined('DB_HOST')) {
    define('DB_HOST', $readSetting('host', 'NEWS_HUB_DB_HOST', '127.0.0.1'));
}

if (!defined('DB_PORT')) {
    $portValue = $readSetting('port', 'NEWS_HUB_DB_PORT', '3306');
    $port = filter_var($portValue, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 65535],
    ]);
    define('DB_PORT', $port === false ? 3306 : $port);
}

if (!defined('DB_NAME')) {
    define('DB_NAME', $readSetting('name', 'NEWS_HUB_DB_NAME', 'news_hub'));
}

if (!defined('DB_USER')) {
    define('DB_USER', $readSetting('user', 'NEWS_HUB_DB_USER', 'root'));
}

if (!defined('DB_PASS')) {
    define('DB_PASS', $readSetting('pass', 'NEWS_HUB_DB_PASS', ''));
}

if (!defined('DB_CHARSET')) {
    $charset = $readSetting('charset', 'NEWS_HUB_DB_CHARSET', 'utf8mb4');
    define('DB_CHARSET', preg_match('/\A[A-Za-z0-9_]+\z/', $charset) ? $charset : 'utf8mb4');
}
