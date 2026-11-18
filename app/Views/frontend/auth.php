<?php
$data = is_array($data ?? null) ? $data : [];
$escape = static fn($v): string => htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = (string)($data['baseUrl'] ?? '');
$register = ($data['mode'] ?? '') === 'auth_register';
ob_start();
?>
<main id="main-content" class="container py-5">
    <section class="auth-panel mx-auto" style="max-width: 34rem" aria-labelledby="auth-title">
        <h1 id="auth-title" class="h2 mb-4"><?= $register ? 'Tạo tài khoản NEWS HUB' : 'Đăng nhập' ?></h1>
        <?php if (!empty($data['error'])): ?><p class="alert alert-danger" role="alert"><?= $escape($data['error']) ?></p><?php endif; ?>
        <form method="post" action="<?= $escape($baseUrl . ($register ? '/AuthController/createAccount' : '/AuthController/authenticate')) ?>">
            <input type="hidden" name="csrf_token" value="<?= $escape($data['csrf_token'] ?? '') ?>">
            <?php if ($register): ?>
                <label class="form-label" for="full_name">Họ tên</label>
                <input class="form-control mb-3" id="full_name" name="full_name" maxlength="150" autocomplete="name" required>
            <?php endif; ?>
            <label class="form-label" for="email">Email</label>
            <input class="form-control mb-3" id="email" name="email" type="email" maxlength="255" autocomplete="email" required>
            <label class="form-label" for="password">Mật khẩu</label>
            <input class="form-control mb-4" id="password" name="password" type="password" minlength="10" autocomplete="<?= $register ? 'new-password' : 'current-password' ?>" required>
            <button class="btn btn-primary" type="submit"><?= $register ? 'Tạo tài khoản' : 'Đăng nhập' ?></button>
        </form>
        <p class="mt-4 mb-0">
            <?php if ($register): ?>Đã có tài khoản? <a href="<?= $escape($baseUrl . '/AuthController/login') ?>">Đăng nhập</a>
            <?php else: ?>Chưa có tài khoản? <a href="<?= $escape($baseUrl . '/AuthController/register') ?>">Đăng ký</a><?php endif; ?>
        </p>
    </section>
</main>
<?php
$pageContent = (string)ob_get_clean();
require __DIR__ . '/../layouts/frontend.php';
