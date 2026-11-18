<?php

class HomeController extends Controller
{
    public function index(): void
    {
        $articleModel = $this->model('Article');
        $categories = $articleModel->getCategories();
        $selectedCategoryId = null;
        $featuredArticles = $articleModel->getFeaturedPublished(5);
        $featuredArticle = $featuredArticles[0] ?? null;
        $latestArticles = $articleModel->getPublished(12, 0);

        $scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
        $baseUrl = rtrim($scriptDirectory, '/');

        $this->view('frontend/home', [
            'page_title' => 'NEWS HUB — The World, In Focus.',
            'baseUrl' => $baseUrl,
            'csrf_token' => $this->csrfToken(),
            'categories' => $categories,
            'selectedCategoryId' => $selectedCategoryId,
            'featuredArticle' => $featuredArticle,
            'featuredArticles' => array_slice($featuredArticles, 1),
            'latestArticles' => $latestArticles,
        ]);
    }

    private function getSelectedCategoryId(array $categories): ?int
    {
        $requestedId = $_GET['category'] ?? null;
        if (!is_string($requestedId) || !preg_match('/\A[1-9][0-9]{0,9}\z/', $requestedId)) {
            return null;
        }

        $categoryId = (int)$requestedId;
        if ($categoryId > 4294967295) {
            return null;
        }

        foreach ($categories as $category) {
            if ((int)$category['id'] === $categoryId) {
                return $categoryId;
            }
        }

        return null;
    }
}
