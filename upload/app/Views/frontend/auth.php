<?php
$data = is_array($data ?? null) ? $data : [];
$escape = static fn($v): string => htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = (string)($data['baseUrl'] ?? '');
$register = ($data['mode'] ?? '') === 'auth_register';
$adminEntry = !empty($data['adminEntry']) && !$register;
$formValues = is_array($data['formValues'] ?? null) ? $data['formValues'] : [];
$authAction = $baseUrl . ($register ? '/AuthController/createAccount' : '/AuthController/authenticate');
if ($adminEntry) $authAction .= '?area=admin';
ob_start();
?>
<main id="main-content" class="container py-5">
    <section class="auth-panel mx-auto" style="max-width: 34rem" aria-labelledby="auth-title">
        <h1 id="auth-title" class="h2 mb-4"><?= $register ? 'Tạo tài khoản NEWS HUB' : ($adminEntry ? 'Đăng nhập quản trị' : 'Đăng nhập') ?></h1>
        <?php if ($adminEntry): ?>
            <p class="auth-context">Cổng dành riêng cho quản trị viên. Hệ thống xác minh role Admin từ database sau khi đăng nhập; tài khoản độc giả không thể vào khu vực này.</p>
            <p class="auth-admin-note">Chỉ tài khoản đã được cấp quyền Admin mới có thể đăng nhập khu vực quản trị.</p>

        <?php endif; ?>
        <?php if (!empty($data['error'])): ?><p class="alert alert-danger" role="alert" aria-live="polite"><?= $escape($data['error']) ?></p><?php endif; ?>
        <form method="post" action="<?= $escape($authAction) ?>">
            <input type="hidden" name="csrf_token" value="<?= $escape($data['csrf_token'] ?? '') ?>">
            <?php if ($register): ?>
                <label class="form-label" for="full_name">Họ tên</label>
                <input class="form-control mb-3" id="full_name" name="full_name" maxlength="150" autocomplete="name" value="<?= $escape($formValues['full_name'] ?? '') ?>" required>
            <?php endif; ?>
            <label class="form-label" for="email">Email</label>
            <input class="form-control mb-3" id="email" name="email" type="email" maxlength="255" autocomplete="email" spellcheck="false" value="<?= $escape($register ? ($formValues['email'] ?? '') : ($data['email'] ?? '')) ?>" required>
            <label class="form-label" for="password">Mật khẩu</label>
            <input class="form-control<?= $register ? '' : ' mb-4' ?>" id="password" name="password" type="password"<?= $register ? ' minlength="12" maxlength="72" aria-describedby="password-requirements"' : '' ?> autocomplete="<?= $register ? 'new-password' : 'current-password' ?>" required>
            <?php if ($register): ?>
                <small id="password-requirements" class="form-text d-block mb-3">Tối thiểu 12 ký tự, gồm ít nhất 3 nhóm chữ thường, chữ hoa, số hoặc ký hiệu. Giới hạn 72 byte.</small>
                <label class="form-label" for="password_confirm">Xác nhận mật khẩu</label>
                <input class="form-control mb-4" id="password_confirm" name="password_confirm" type="password" minlength="12" maxlength="72" autocomplete="new-password" required>
            <?php endif; ?>
            <button class="btn btn-primary" type="submit"><?= $register ? 'Tạo tài khoản' : 'Đăng nhập' ?></button>
        </form>
        <?php if ($adminEntry): ?>
            <p class="mt-4 mb-0">Đăng nhập bằng tài khoản đã được cấp role Admin. <a href="<?= $escape($baseUrl . '/AuthController/login') ?>">Đăng nhập độc giả</a></p>
        <?php else: ?>
            <p class="mt-4 mb-0">
                <?php if ($register): ?>Đã có tài khoản? <a href="<?= $escape($baseUrl . '/AuthController/login') ?>">Đăng nhập</a>
                <?php else: ?>Chưa có tài khoản? <a href="<?= $escape($baseUrl . '/AuthController/register') ?>">Đăng ký</a><?php endif; ?>
            </p>
        <?php endif; ?>
    </section>
</main>
<?php
$pageContent = (string)ob_get_clean();
require __DIR__ . '/../layouts/frontend.php';
