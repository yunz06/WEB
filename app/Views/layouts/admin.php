<?php
$data = is_array($data ?? null) ? $data : [];
$baseUrl = rtrim((string)($data['baseUrl'] ?? ''), '/');
$title = htmlspecialchars((string)($data['page_title'] ?? 'NEWS HUB Admin'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$token = htmlspecialchars((string)($data['csrf_token'] ?? ''), ENT_QUOTES, 'UTF-8');
$content = (string)($pageContent ?? '');
?>
<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $title ?></title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:#f3f6fa;color:#14263c}.admin-bar{background:#0b1d33}.admin-bar a{color:#fff;text-decoration:none}.admin-main{max-width:1440px;margin:auto}.admin-nav{display:flex;gap:.75rem;flex-wrap:wrap}.admin-nav a{padding:.5rem .7rem;border-radius:.4rem}.admin-nav a:hover,.admin-nav a:focus-visible{background:#284565}.admin-main img{max-width:100%;height:auto}</style></head><body>
<header class="admin-bar text-white"><div class="container-fluid py-3 admin-main d-flex justify-content-between align-items-center flex-wrap gap-3">
<a class="fw-bold fs-5" href="<?= htmlspecialchars($baseUrl . '/DashboardController/index', ENT_QUOTES, 'UTF-8') ?>">NEWS HUB · Quản trị</a>
<nav class="admin-nav" aria-label="Quản trị"><a href="<?= htmlspecialchars($baseUrl . '/ArticleController/index', ENT_QUOTES, 'UTF-8') ?>">Bài viết</a><a href="<?= htmlspecialchars($baseUrl . '/CategoryController/index', ENT_QUOTES, 'UTF-8') ?>">Danh mục</a><a href="<?= htmlspecialchars($baseUrl . '/UserController/index', ENT_QUOTES, 'UTF-8') ?>">Người dùng</a><a href="<?= htmlspecialchars($baseUrl . '/CommentController/index', ENT_QUOTES, 'UTF-8') ?>">Bình luận</a><a href="<?= htmlspecialchars($baseUrl . '/', ENT_QUOTES, 'UTF-8') ?>">Xem trang tin</a></nav>
<form method="post" action="<?= htmlspecialchars($baseUrl . '/AuthController/logout', ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="csrf_token" value="<?= $token ?>"><button class="btn btn-sm btn-outline-light">Đăng xuất</button></form>
</div></header><a class="visually-hidden-focusable position-absolute top-0 start-0 bg-white p-2" href="#admin-main">Bỏ qua điều hướng</a><main id="admin-main" class="admin-main container-fluid py-4"><?= $content ?></main><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script></body></html>
