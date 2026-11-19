<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$css = file_get_contents($projectRoot . '/public/css/style.css');
$finishCss = file_get_contents($projectRoot . '/public/css/finish.css');
$layout = file_get_contents($projectRoot . '/app/Views/layouts/frontend.php');
if (!is_string($css)) {
    throw new RuntimeException('Could not read the public stylesheet.');
}

if (!preg_match('/\.story-placeholder__caption\s*\{([^}]*)\}/s', $css, $matches)) {
    throw new RuntimeException('Story placeholder caption style is missing.');
}

if (!preg_match('/font-size\s*:\s*([0-9.]+)rem\s*;/', $matches[1], $fontSize)) {
    throw new RuntimeException('Story placeholder caption needs an explicit readable font size.');
}

$minimumRem = 11 / 16;
if ((float)$fontSize[1] < $minimumRem) {
    throw new RuntimeException(sprintf(
        'Story placeholder caption is %.2fpx; minimum is 11px.',
        (float)$fontSize[1] * 16
    ));
}

echo "PASS: story placeholder caption meets the 11px readability floor.\n";
