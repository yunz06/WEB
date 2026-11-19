<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$update = file_get_contents($root . '/database/editorial_content_update.sql');
$seed = file_get_contents($root . '/database/seed.sql');
if (!is_string($update) || !is_string($seed)) {
    throw new RuntimeException('Editorial seed files are missing.');
}

$expectedSlugs = [
    'sample-01-thoi-su',
    'sample-05-the-gioi',
    'sample-07-tai-chinh',
    'sample-09-cong-nghe',
    'sample-21-giao-duc',
    'sample-33-doi-song',
    'sample-03-moi-truong',
    'sample-13-the-thao',
];

foreach ($expectedSlugs as $slug) {
    if (!str_contains($update, "WHERE slug='" . $slug . "'")) {
        throw new RuntimeException("Editorial update is missing expected slug: {$slug}");
    }
}

if (substr_count($update, 'UPDATE articles SET') !== 9) {
    throw new RuntimeException('Expected one featured reset and eight source-attributed article updates.');
}

if (!str_contains($update, 'Nguồn tham khảo:') || !str_contains($update, 'https://')) {
    throw new RuntimeException('Editorial stories must include source links.');
}

if (!preg_match('/\bSET\s+NAMES\s+utf8mb4\b/i', $update)
    || !preg_match('/\bSET\s+NAMES\s+utf8mb4\b/i', $seed)) {
    throw new RuntimeException('Seed scripts must explicitly set utf8mb4.');
}

if (!str_contains($seed, 'Curated, source-attributed editorial summaries')) {
    throw new RuntimeException('Main seed must include the source-attributed editorial content update.');
}

echo "PASS: eight source-attributed editorial stories are included in the seed and targeted update script.\n";
