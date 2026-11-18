<?php

declare(strict_types=1);

class SearchController extends Controller
{
    public function index(int $page = 1): void
    {
        $queryInput = $_GET['q'] ?? '';
        $query = is_string($queryInput) ? trim($queryInput) : '';
        if (mb_strlen($query, 'UTF-8') > 120) $query = mb_substr($query, 0, 120, 'UTF-8');
        $articlesModel = $this->model('Article');
        $total = $query === '' ? 0 : $articlesModel->countSearchPublished($query);
        $articles = $query === '' ? [] : $articlesModel->searchPublished($query, 10, (max(1, $page) - 1) * 10);
        $baseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/');
        $this->view('frontend/search', ['page_title' => 'Tìm kiếm — NEWS HUB', 'baseUrl' => $baseUrl,
            'categories' => $articlesModel->getCategories(), 'query' => $query, 'articles' => $articles,
            'page' => max(1, $page), 'totalPages' => max(1, (int)ceil($total / 10)), 'totalResults' => $total,
            'csrf_token' => $this->csrfToken()]);
    }
}
