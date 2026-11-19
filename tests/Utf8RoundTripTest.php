<?php

declare(strict_types=1);

if (!extension_loaded('mbstring') || !in_array('mysql', PDO::getAvailableDrivers(), true)) { echo "SKIP: mbstring and pdo_mysql are required for UTF-8 database round-trip tests.\n"; exit(0); }

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Core/Database.php';

$failures = [];
$expect = static function (bool $condition, string $message) use (&$failures): void {
    if (!$condition) $failures[] = $message;
};

foreach (['news_hub.sql', 'seed.sql'] as $file) {
    $path = __DIR__ . '/../database/' . $file;
    $contents = file_get_contents($path);
    $expect(is_string($contents) && mb_check_encoding($contents, 'UTF-8'), "$file must be valid UTF-8.");
    $expect(is_string($contents) && preg_match('/\bSET\s+NAMES\s+utf8mb4\b/i', $contents) === 1,
        "$file must set the SQL client/session character set to utf8mb4.");
}

$pdo = Database::getInstance()->getConnection();
$charsetVars = $pdo->query("SHOW VARIABLES WHERE Variable_name IN (
    'character_set_client','character_set_connection','character_set_results'
)")->fetchAll(PDO::FETCH_KEY_PAIR);
foreach (['character_set_client', 'character_set_connection', 'character_set_results'] as $name) {
    $expect(($charsetVars[$name] ?? null) === 'utf8mb4', "PDO $name must be utf8mb4.");
}

$expected = 'Tiếng Việt: ă â ê ô ơ ư đ; sắc huyền hỏi ngã nặng; Ă Â Ê Ô Ơ Ư Đ';
$statement = $pdo->prepare('SELECT CAST(:value AS CHAR CHARACTER SET utf8mb4)');
$statement->execute([':value' => $expected]);
$actual = $statement->fetchColumn();
$expect(is_string($actual) && hash_equals($expected, $actual), 'PDO UTF-8 round-trip changed Vietnamese text.');

if ($failures !== []) {
    fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL);
    exit(1);
}

echo "PASS: SQL import guards and PDO utf8mb4 round-trip.\n";
