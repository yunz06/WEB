<?php
$data = is_array($data ?? null) ? $data : [];
$baseUrl = (string)($data['baseUrl'] ?? '');
$profile = is_array($data['profile'] ?? null) ? $data['profile'] : [];
$comments = is_array($data['comments'] ?? null) ? $data['comments'] : [];
$currentPage = max(1, (int)($data['currentPage'] ?? 1));
$totalPages = max(1, (int)($data['totalPages'] ?? 1));
$totalComments = max(0, (int)($data['totalComments'] ?? 0));
$escape = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$statusLabels = ['pending' => 'Đang chờ duyệt', 'approved' => 'Đã duyệt', 'hidden' => 'Đang ẩn'];
ob_start();
?>
<main id="main-content" class="account-page">
    <div class="container">
        <header class="account-heading">
            <h1>Tài khoản của <?= $escape($profile['full_name'] ?? '') ?></h1>
            <p>Thông tin độc giả và trạng thái bình luận của bạn.</p>
        </header>

        <div class="account-grid">
            <section class="account-panel account-profile" aria-labelledby="account-profile-heading">
                <h2 id="account-profile-heading">Thông tin tài khoản</h2>
                <dl>
                    <div><dt>Họ tên</dt><dd><?= $escape($profile['full_name'] ?? '') ?></dd></div>
                    <div><dt>Email</dt><dd class="account-email"><?= $escape($profile['email'] ?? '') ?></dd></div>
                    <?php if (!empty($profile['created_at'])): ?>
                        <div><dt>Tham gia từ</dt><dd><time datetime="<?= $escape($profile['created_at']) ?>"><?= $escape(date('d/m/Y', strtotime((string)$profile['created_at']))) ?></time></dd></div>
                    <?php endif; ?>
                </dl>
                <a class="account-home-link" href="<?= $escape($baseUrl . '/') ?>">Quay lại trang tin</a>
            </section>

            <section id="account-comments" class="account-panel account-comments" aria-labelledby="account-comments-heading">
                <div class="account-comments__heading">
                    <div>
                        <h2 id="account-comments-heading">Bình luận của bạn</h2>
                        <p><?= $totalComments ?> bình luận · Mỗi bình luận mới được xem xét trước khi hiển thị công khai.</p>
                    </div>
                    <?php if ($totalPages > 1): ?><span>Trang <?= $currentPage ?> / <?= $totalPages ?></span><?php endif; ?>
                </div>

                <?php if ($comments !== []): ?>
                    <ol class="account-comment-list">
                        <?php foreach ($comments as $comment): ?>
                            <?php
                            $status = (string)($comment['status'] ?? 'pending');
                            $createdAt = (string)($comment['created_at'] ?? '');
                            $publishedAt = strtotime((string)($comment['article_published_at'] ?? ''));
                            $articleIsPublic = ($comment['article_status'] ?? '') === 'published'
                                && $publishedAt !== false && $publishedAt <= time();
                            ?>
                            <li class="account-comment">
                                <div class="account-comment__meta">
                                    <span class="comment-status comment-status--<?= $escape($status) ?>"><?= $escape($statusLabels[$status] ?? 'Trạng thái chưa xác định') ?></span>
                                    <?php if ($createdAt !== ''): ?><time datetime="<?= $escape($createdAt) ?>"><?= $escape(date('d/m/Y H:i', strtotime($createdAt))) ?></time><?php endif; ?>
                                </div>
                                <p class="account-comment__article">
                                    <?php if ($articleIsPublic): ?>
                                        <a href="<?= $escape($baseUrl . '/FrontendArticleController/show/' . (int)$comment['article_id']) ?>"><?= $escape($comment['article_title'] ?? 'Bài viết') ?></a>
                                    <?php else: ?>
                                        <?= $escape($comment['article_title'] ?? 'Bài viết') ?>
                                        <span class="account-comment__unavailable">(hiện không công khai)</span>
                                    <?php endif; ?>
                                </p>
                                <p class="account-comment__content"><?= nl2br($escape($comment['content'] ?? '')) ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php else: ?>
                    <p class="account-empty" role="status">Bạn chưa gửi bình luận nào. Mở một bài viết để tham gia trao đổi.</p>
                <?php endif; ?>

                <?php if ($totalPages > 1): ?>
                    <nav class="account-pagination" aria-label="Trang bình luận của bạn">
                        <?php if ($currentPage > 1): ?>
                            <a href="<?= $escape($baseUrl . '/AccountController/index/' . ($currentPage - 1) . '#account-comments') ?>">Trước</a>
                        <?php else: ?>
                            <span class="account-pagination__disabled" aria-disabled="true">Trước</span>
                        <?php endif; ?>
                        <span aria-current="page">Trang <?= $currentPage ?> / <?= $totalPages ?></span>
                        <?php if ($currentPage < $totalPages): ?>
                            <a href="<?= $escape($baseUrl . '/AccountController/index/' . ($currentPage + 1) . '#account-comments') ?>">Sau</a>
                        <?php else: ?>
                            <span class="account-pagination__disabled" aria-disabled="true">Sau</span>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            </section>
        </div>
    </div>
</main>
<?php
$pageContent = (string)ob_get_clean();
require __DIR__ . '/../layouts/frontend.php';
