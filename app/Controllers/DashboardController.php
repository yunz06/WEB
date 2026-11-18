<?php

declare(strict_types=1);

class DashboardController extends Controller
{
    public function index(): void
    {
        $baseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/');
        $article = $this->model('Article');
        $this->view('admin/dashboard', ['page_title' => 'Tổng quan — NEWS HUB', 'baseUrl' => $baseUrl,
            'stats' => $article->getDashboardStats(), 'recentArticles' => $article->getAllAdmin(8, 0)]);
    }
}
