<?php

declare(strict_types=1);

$sanitizerFile = __DIR__ . '/../app/Core/HtmlSanitizer.php';
if (!is_file($sanitizerFile)) {
    fwrite(STDERR, "FAIL: article HTML sanitizer is not implemented.\n");
    exit(1);
}

require_once $sanitizerFile;

$input = '<h2>News</h2><p>Hello <strong>world</strong>.</p>'
    . '<script>alert(1)</script><img src="x" onerror="alert(2)">'
    . '<a href="javascript:alert(3)" onclick="alert(4)">unsafe</a>'
    . '<a href="https://example.com" target="_blank">safe</a>'
    . '<iframe srcdoc="<script>alert(5)</script>">embed</iframe>';
$output = HtmlSanitizer::sanitizeArticleContent($input);

if (class_exists(DOMDocument::class)) {
    $checks = [
        'keeps allowed rich text' => str_contains($output, '<h2>News</h2>')
            && str_contains($output, '<strong>world</strong>'),
        'removes executable elements and attributes' => !preg_match('/<\/?script\b|<iframe\b|onerror\s*=|onclick\s*=|srcdoc\s*=/i', $output),
        'removes unsafe links and images' => !preg_match('/javascript:|<img\b/i', $output),
        'keeps safe links without extra attributes' => str_contains($output, 'href="https://example.com"')
            && !str_contains($output, 'target='),
    ];
} else {
    $checks = [
        'uses safe text fallback without DOM' => str_contains($output, '&lt;h2&gt;News&lt;/h2&gt;')
            && str_contains($output, '&lt;strong&gt;world&lt;/strong&gt;'),
        'fallback cannot execute supplied markup' => !str_contains($output, '<') && str_contains($output, '&lt;script&gt;'),
        'fallback escapes unsafe URLs' => str_contains($output, 'javascript:') && str_contains($output, '&lt;a'),
    ];
}

foreach ($checks as $name => $passed) {
    if (!$passed) {
        fwrite(STDERR, "FAIL: {$name}\n");
        exit(1);
    }
}

echo "PASS: " . count($checks) . " article HTML sanitizer checks.\n";
