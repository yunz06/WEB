<?php

class HomeController extends Controller
{
    public function index(int $page = 1): void
    {
        $articleModel = $this->model('Article');
        $categories = $articleModel->getCategories();
        $selectedCategoryId = null;
        $featuredArticles = $articleModel->getFeaturedPublished(5);
        $featuredArticle = $featuredArticles[0] ?? null;
        $featuredIds = array_map('intval', array_column($featuredArticles, 'id'));
        $latestLimit = 12;
        $latestTotal = $articleModel->countLatestPublishedExcluding($featuredIds);
        $latestTotalPages = max(1, (int)ceil($latestTotal / $latestLimit));
        $page = min(max(1, $page), $latestTotalPages);
        $latestArticles = $articleModel->getLatestPublishedExcluding($featuredIds, $latestLimit, ($page - 1) * $latestLimit);
        $rootCategories = array_values(array_filter($categories, static fn(array $category): bool => $category['parent_id'] === null));
        $sectionCategoryIds = array_map('intval', array_column(array_slice($rootCategories, 0, 8), 'id'));
        $categorySections = $articleModel->getPublishedCategorySections($sectionCategoryIds, 3);

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
            'latestPage' => $page,
            'latestTotalPages' => $latestTotalPages,
            'categorySections' => $categorySections,
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
