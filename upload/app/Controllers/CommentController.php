<?php

declare(strict_types=1);

class CommentController extends Controller
{
    public function store(int $articleId): void
    {
        $baseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/');
        $userId = (int)($_SESSION['user']['id'] ?? 0);
        if ($userId < 1) $this->redirectTo($baseUrl . '/AuthController/login');
        $article = $this->model('Article')->getPublishedById($articleId);
        if (!$article) { http_response_code(404); return; }
        $contentInput = $_POST['content'] ?? '';
        $content = is_string($contentInput) ? trim($contentInput) : '';
        if ($content === '' || mb_strlen($content, 'UTF-8') > 3000) {
            $_SESSION['flash_error'] = 'Bình luận phải có từ 1 đến 3.000 ký tự.';
        } else {
            $this->model('Comment')->create($articleId, $userId, $content);
            $_SESSION['flash_success'] = 'Bình luận đã gửi và đang chờ duyệt.';
        }
        $this->redirectTo($baseUrl . '/FrontendArticleController/show/' . $articleId);
    }

    public function index(int $page = 1): void
    {
        $model = $this->model('Comment');
        $statusInput = $_GET['status'] ?? '';
        $status = is_string($statusInput) ? $statusInput : '';
        $limit = 20;
        $total = $model->countAll($status);
        $this->view('admin/comments', ['page_title' => 'Quản lý bình luận — NEWS HUB',
            'baseUrl' => rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/'),
            'comments' => $model->getAll($limit, (max(1, $page) - 1) * $limit, $status),
            'status' => $status, 'page' => max(1, $page), 'totalPages' => max(1, (int)ceil($total / $limit)),
            'csrf_token' => $this->csrfToken()]);
    }

    public function updateStatus(int $id): void
    {
        $statusInput = $_POST['status'] ?? '';
        $status = is_string($statusInput) ? $statusInput : '';
        $this->model('Comment')->setStatus($id, $status);
        $this->redirectTo(rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/') . '/CommentController/index');
    }

    public function delete(int $id): void
    {
        $this->model('Comment')->delete($id);
        $this->redirectTo(rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/') . '/CommentController/index');
    }
}
