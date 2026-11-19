<?php

declare(strict_types=1);

class PublicCategoryController extends Controller
{
    public function show(string $slug, int $page = 1): void
    {
        $categories = $this->model('Category');
        $category = $categories->getBySlug($slug);
        $navigationCategories = $categories->getPublicNavigation();
        $parentCategory = null;
        $childCategories = [];
        if ($category !== null) {
            foreach ($navigationCategories as $navigationCategory) {
                if ((int)($navigationCategory['id'] ?? 0) === (int)($category['parent_id'] ?? 0)) {
                    $parentCategory = $navigationCategory;
                }
                if ((int)($navigationCategory['parent_id'] ?? 0) === (int)$category['id']) {
                    $childCategories[] = $navigationCategory;
                }
            }
        }
        if (!$category) { http_response_code(404); $category = null; $articles = []; $total = 0; }
        else {
            $total = $categories->countPublicArticles((int)$category['id']);
            $offset = (max(1, $page) - 1) * 10;
            $articles = $categories->getPublicPage((int)$category['id'], 10, $offset);
        }
        $baseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/');
        $this->view('frontend/category', ['page_title' => ($category['name'] ?? 'Không tìm thấy chuyên mục') . ' — NEWS HUB',
            'baseUrl' => $baseUrl, 'categories' => $navigationCategories, 'category' => $category,
            'parentCategory' => $parentCategory, 'childCategories' => $childCategories,
            'selectedCategoryId' => $category === null ? null : (int)$category['id'],
            'articles' => $articles, 'page' => max(1, $page), 'totalPages' => max(1, (int)ceil($total / 10)),
            'csrf_token' => $this->csrfToken()]);
    }
}
