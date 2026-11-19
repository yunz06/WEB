<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$viewRoutes = [
    'app/Views/admin/articles.php' => ['ArticleController/delete/'],
    'app/Views/admin/categories.php' => ['CategoryController/delete/'],
    'app/Views/admin/comments.php' => ['CommentController/delete/'],
];

foreach ($viewRoutes as $relativePath => $routes) {
    $view = file_get_contents($projectRoot . '/' . $relativePath);
    if (!is_string($view)) {
        throw new RuntimeException('Could not read admin view: ' . $relativePath);
    }
    foreach ($routes as $route) {
        $routePosition = strpos($view, $route);
        $tagContext = $routePosition === false ? '' : substr($view, max(0, $routePosition - 180), 420);
        if ($routePosition === false || !str_contains($tagContext, 'data-confirm="')) {
            throw new RuntimeException('Destructive action lacks confirmation copy: ' . $route);
        }
    }
}

$articleView = file_get_contents($projectRoot . '/app/Views/admin/articles.php');
$imageRoutePosition = is_string($articleView) ? strpos($articleView, 'ArticleController/deleteImage/') : false;
$imageTagContext = $imageRoutePosition === false ? '' : substr($articleView, max(0, $imageRoutePosition - 400), 1000);
if ($imageRoutePosition === false || !str_contains($imageTagContext, 'data-confirm="')) {
    throw new RuntimeException('Deleting an article image lacks confirmation copy.');
}

$script = file_get_contents($projectRoot . '/public/js/main.js');
if (!is_string($script)
    || !str_contains($script, "addEventListener('submit'")
    || !str_contains($script, 'dataset.confirm')
    || !str_contains($script, 'preventDefault')) {
    throw new RuntimeException('Shared script does not cancel destructive form submission when confirmation is declined.');
}

echo "PASS: Admin delete forms require a confirmation handler.\n";
