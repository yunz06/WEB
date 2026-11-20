<?php

declare(strict_types=1);

require_once __DIR__ . '/../upload/app/Core/ArticleImage.php';

$seedImages = [
    'seed_business.jpg', 'seed_education.jpg', 'seed_energy.jpg', 'seed_lifestyle.jpg',
    'seed_sports.jpg', 'seed_technology.jpg', 'seed_work.jpg', 'seed_world.jpg',
];
$allSeedImagesExist = true;
foreach ($seedImages as $seedImage) {
    $allSeedImagesExist = $allSeedImagesExist && ArticleImage::exists($seedImage);
}
$educationUrl = ArticleImage::url('seed_education.jpg');

$checks = [
    'all eight article seed photos exist in the canonical folder' => $allSeedImagesExist,
    'seed_education.jpg resolves to a cache-busted local article URL' => is_string($educationUrl)
        && str_contains($educationUrl, '/public/uploads/articles/seed_education.jpg?v='),
    'missing filename does not resolve' => !ArticleImage::exists('not-a-real-image.jpg'),
    'path traversal is rejected' => ArticleImage::url('../config/database.php') === null,
    'external URL is rejected' => ArticleImage::url('https://example.invalid/thumbnail.jpg') === null,
    'legacy editorial filename does not resolve' => ArticleImage::url('world.jpg') === null,
];

$failed = [];
foreach ($checks as $label => $passed) {
    echo ($passed ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL;
    if (!$passed) $failed[] = $label;
}

echo PHP_EOL . count($checks) . ' checks; ' . count($failed) . ' failed.' . PHP_EOL;
exit($failed === [] ? 0 : 1);
