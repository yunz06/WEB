<?php

declare(strict_types=1);

class AccountController extends Controller
{
    public function index(int $page = 1): void
    {
        $userId = (int)($_SESSION['user']['id'] ?? 0);
        if ($userId < 1) {
            $this->redirectTo($this->baseUrl() . '/AuthController/login');
        }

        $users = $this->model('User');
        $profile = $users->getProfileById($userId);
        if ($profile === null || $profile['status'] !== 'active') {
            unset($_SESSION['user']);
            $this->redirectTo($this->baseUrl() . '/AuthController/login');
        }

        $comments = $this->model('Comment');
        $limit = 10;
        $total = $comments->countForUser($userId);
        $totalPages = max(1, (int)ceil($total / $limit));
        $page = min(max(1, $page), $totalPages);
        $baseUrl = $this->baseUrl();

        $this->view('frontend/account', [
            'page_title' => 'Tài khoản — NEWS HUB',
            'baseUrl' => $baseUrl,
            'categories' => $this->model('Category')->getPublicNavigation(),
            'selectedCategoryId' => null,
            'csrf_token' => $this->csrfToken(),
            'profile' => $profile,
            'comments' => $comments->getForUser($userId, $limit, ($page - 1) * $limit),
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalComments' => $total,
        ]);
    }

    private function baseUrl(): string
    {
        return rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/');
    }
}
