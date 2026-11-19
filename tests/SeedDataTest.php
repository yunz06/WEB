<?php

declare(strict_types=1);

if (!in_array('mysql', PDO::getAvailableDrivers(), true)) { echo "SKIP: pdo_mysql is required for seed data tests.\n"; exit(0); }

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Core/Database.php';
$pdo = Database::getInstance()->getConnection();
$failures = [];
$scalar = static function (string $sql) use ($pdo): int { return (int)$pdo->query($sql)->fetchColumn(); };
$expect = static function (bool $ok, string $message) use (&$failures): void { if (!$ok) $failures[] = $message; };

$expect($scalar('SELECT COUNT(*) FROM categories WHERE parent_id IS NULL') >= 10, 'Seed should include 10 parent categories.');
$expect($scalar('SELECT COUNT(*) FROM categories WHERE parent_id IS NOT NULL') >= 15, 'Seed should include 15 child categories.');
$expect($scalar("SELECT COUNT(*) FROM articles WHERE slug LIKE 'sample-%'") >= 40, 'Seed should include at least 40 sample articles.');
$expect($scalar("SELECT COUNT(*) FROM articles WHERE slug LIKE 'sample-%' AND status='published'") >= 32, 'Seed should include published samples.');
$expect($scalar("SELECT COUNT(*) FROM articles WHERE slug LIKE 'sample-%' AND status='draft'") >= 4, 'Seed should include draft samples.');
$expect($scalar("SELECT COUNT(*) FROM articles WHERE slug LIKE 'sample-%' AND is_featured=1 AND status='published'") >= 5, 'Seed should include featured published samples.');
$expect($scalar("SELECT COUNT(*) FROM articles WHERE slug LIKE 'sample-%' AND (title NOT LIKE '[%' OR summary NOT LIKE 'N%' OR content NOT LIKE '%<strong>%')") === 0, 'Every seeded story must be visibly labeled sample content.');
$expect($scalar("SELECT COUNT(*) FROM comments WHERE content LIKE 'SAMPLE:%' AND status='pending'") >= 1, 'Seed should include pending comment.');
$expect($scalar("SELECT COUNT(*) FROM comments WHERE content LIKE 'SAMPLE:%' AND status='approved'") >= 1, 'Seed should include approved comment.');
$expect($scalar("SELECT COUNT(*) FROM comments WHERE content LIKE 'SAMPLE:%' AND status='hidden'") >= 1, 'Seed should include hidden comment.');
$expect($scalar("SELECT COUNT(*) FROM articles a LEFT JOIN users u ON u.id=a.user_id WHERE a.slug LIKE 'sample-%' AND u.id IS NULL") === 0, 'Seeded articles must have valid authors.');
$expect($scalar("SELECT COUNT(*) FROM articles a LEFT JOIN categories c ON c.id=a.category_id WHERE a.slug LIKE 'sample-%' AND c.id IS NULL") === 0, 'Seeded articles must have valid categories.');
$expect($scalar("SELECT COUNT(*) FROM article_images i LEFT JOIN articles a ON a.id=i.article_id WHERE i.image_path LIKE 'seed_%' AND a.id IS NULL") === 0, 'Seed images must belong to an article.');

$roleNames = $pdo->query('SELECT name FROM roles ORDER BY name')->fetchAll(PDO::FETCH_COLUMN);
$expect($roleNames === ['admin', 'user'], 'Seed should retain the admin and user role records.');

$imageRows = $pdo->query("SELECT DISTINCT thumbnail FROM articles WHERE slug LIKE 'sample-%'")->fetchAll(PDO::FETCH_COLUMN);
foreach ($imageRows as $image) {
    $path = __DIR__ . '/../public/uploads/articles/' . basename((string)$image);
    $expect(is_file($path), 'Seed thumbnail file is missing: ' . basename((string)$image));
}

if ($failures !== []) { fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL); exit(1); }
echo "PASS: seed content, roles, authorship, and local image checks.\n";
