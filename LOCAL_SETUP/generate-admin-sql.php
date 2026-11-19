<?php

declare(strict_types=1);

$email = trim((string) getenv('NEWS_HUB_ADMIN_EMAIL'));
$name = trim((string) (getenv('NEWS_HUB_ADMIN_NAME') ?: 'NEWS HUB Administrator'));
$password = (string) getenv('NEWS_HUB_ADMIN_PASSWORD');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
    fwrite(STDERR, "Invalid admin email.\n");
    exit(1);
}
if ($name === '' || preg_match('//u', $name) !== 1 || preg_match_all('/./us', $name) > 150) {
    fwrite(STDERR, "Admin display name must be between 1 and 150 characters.\n");
    exit(1);
}
if (strlen($password) < 12 || strlen($password) > 72
    || preg_match('/[a-z]/', $password) !== 1
    || preg_match('/[A-Z]/', $password) !== 1
    || preg_match('/[0-9]/', $password) !== 1) {
    fwrite(STDERR, "Password must be 12-72 bytes and include lowercase, uppercase, and a number. Add a symbol too.\n");
    exit(1);
}

$quote = static function (string $value): string {
    return "'" . str_replace(["\\", "'"], ["\\\\", "\\'"], $value) . "'";
};
$hash = password_hash($password, PASSWORD_DEFAULT);
if (!is_string($hash)) {
    fwrite(STDERR, "Could not generate password hash.\n");
    exit(1);
}

$sql = "-- One-time Admin bootstrap for the new NEWS HUB production database.\n"
    . "-- Import after 01-schema-infinityfree.sql. Keep this generated file private and delete it after import.\n"
    . "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;\n"
    . "INSERT INTO users (role_id, full_name, email, password_hash, status) VALUES (\n"
    . "  (SELECT id FROM roles WHERE name = 'admin' LIMIT 1),\n"
    . "  " . $quote($name) . ",\n"
    . "  " . $quote($email) . ",\n"
    . "  " . $quote($hash) . ",\n"
    . "  'active'\n"
    . ");\n";

$output = __DIR__ . '/admin-bootstrap.generated.sql';
if (file_put_contents($output, $sql, LOCK_EX) === false) {
    fwrite(STDERR, "Could not write generated SQL file.\n");
    exit(1);
}
if (function_exists('chmod')) {
    @chmod($output, 0600);
}
putenv('NEWS_HUB_ADMIN_PASSWORD');
$password = '';
$hash = '';
echo "Created one-time SQL: {$output}\n";
echo "Import it in the selected production database, then delete the generated file.\n";
