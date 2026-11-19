<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/HtmlSanitizer.php';

class FrontendArticleController extends Controller
{
    public function show(int $id): void
    {
        $articleModel = $this->model('Article');
        $article = $articleModel->getPublishedById($id);
        $baseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/');

        if ($article === null) {
            http_response_code(404);
        }

        $this->view('frontend/article', [
            'page_title' => $article === null
                ? 'Không tìm thấy bài viết — NEWS HUB'
                : $article['title'] . ' — NEWS HUB',
            'baseUrl' => $baseUrl,
            'categories' => $articleModel->getCategories(),
            'comments' => $article === null ? [] : $this->model('Comment')->getApprovedForArticle($id),
            'relatedArticles' => $article === null
                ? []
                : $articleModel->getRelatedPublished($id, (int)$article['category_id'], 3),
            'csrf_token' => $this->csrfToken(),
            'article' => $article,
        ]);
    }
}
