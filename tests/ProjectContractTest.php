<?php

declare(strict_types=1);

if (!in_array('mysql', PDO::getAvailableDrivers(), true)) { echo "SKIP: pdo_mysql is required for database contract tests.\n"; exit(0); }

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Core/Database.php';

$pdo = Database::getInstance()->getConnection();
$tableNames = $pdo->query("SHOW TABLES FROM `" . DB_NAME . "`")->fetchAll(PDO::FETCH_COLUMN);
$expectedTables = ['articles', 'article_images', 'categories', 'comments', 'roles', 'users'];
sort($tableNames);
sort($expectedTables);
$failures = [];

if ($tableNames !== $expectedTables) {
    $failures[] = 'Database must contain exactly the six approved tables.';
}

$requiredColumns = [
    'categories' => ['parent_id', 'sort_order', 'status', 'created_at', 'updated_at'],
    'articles' => ['is_featured', 'published_at'],
    'article_images' => ['caption', 'alt_text', 'sort_order'],
];

foreach ($requiredColumns as $table => $columns) {
    $existing = $pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($columns as $column) {
        if (!in_array($column, $existing, true)) {
            $failures[] = "Missing required column {$table}.{$column}.";
        }
    }
}
$authorColumn = $pdo->query("SHOW COLUMNS FROM articles LIKE 'user_id'")->fetch();
if (!$authorColumn || strtoupper((string)$authorColumn['Null']) !== 'NO') {
    $failures[] = 'New articles must have a required user_id author.';
}

$fkCount = (int)$pdo->query(
    "SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
     WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL"
)->fetchColumn();
if ($fkCount < 7) {
    $failures[] = 'The six-table schema must retain its six foreign keys and add the category parent key.';
}

$engines = $pdo->query("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_TYPE='BASE TABLE'")->fetchAll(PDO::FETCH_COLUMN);
if (count($engines) !== 6 || array_filter($engines, static fn($engine) => strtoupper((string)$engine) !== 'INNODB')) {
    $failures[] = 'All six tables must use InnoDB.';
}
$collations = $pdo->query("SELECT TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_TYPE='BASE TABLE'")->fetchAll(PDO::FETCH_COLUMN);
if (count($collations) !== 6 || array_filter($collations, static fn($collation) => $collation !== 'utf8mb4_unicode_ci')) {
    $failures[] = 'All six tables must use utf8mb4_unicode_ci.';
}
$indexNames = $pdo->query("SELECT DISTINCT INDEX_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE()")
    ->fetchAll(PDO::FETCH_COLUMN);
foreach (['uq_users_email','uq_articles_slug','uq_categories_slug','idx_articles_featured_published_at',
    'idx_categories_parent_id','idx_comments_status_created_at'] as $requiredIndex) {
    if (!in_array($requiredIndex, $indexNames, true)) $failures[] = "Missing index {$requiredIndex}.";
}

if ($failures !== []) {
    fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL);
    exit(1);
}

echo "PASS: six-table schema contract.\n";
