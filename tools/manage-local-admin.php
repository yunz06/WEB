<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Core/Database.php';

function prompt(string $label): string
{
    fwrite(STDOUT, $label);
    $value = fgets(STDIN);
    return $value === false ? '' : trim($value);
}

$email = strtolower(prompt("Admin email: "));
$name = prompt("Admin display name: ");
$password = prompt("New admin password (minimum 12 characters): ");
$confirmation = prompt("Confirm password: ");

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $name === '' || strlen($name) > 150) {
    fwrite(STDERR, "Invalid email or display name.\n");
    exit(1);
}

if (strlen($password) < 12 || strlen($password) > 72 || !hash_equals($password, $confirmation)) {
    fwrite(STDERR, "Password must be 12-72 bytes and both entries must match.\n");
    exit(1);
}

$classes = [
    preg_match('/[a-z]/', $password) === 1,
    preg_match('/[A-Z]/', $password) === 1,
    preg_match('/[0-9]/', $password) === 1,
    preg_match('/[^a-zA-Z0-9]/', $password) === 1,
];
if (count(array_filter($classes)) < 3) {
    fwrite(STDERR, "Password must include at least three character groups: lowercase, uppercase, number, symbol.\n");
    exit(1);
}

try {
    $pdo = Database::getInstance()->getConnection();
    $pdo->beginTransaction();

    $roleStatement = $pdo->prepare("SELECT id FROM roles WHERE name='admin' LIMIT 1");
    $roleStatement->execute();
    $adminRoleId = $roleStatement->fetchColumn();
    if ($adminRoleId === false) {
        throw new RuntimeException("The admin role is missing. Import the schema and seed first.");
    }

    $userStatement = $pdo->prepare('SELECT id,role_id FROM users WHERE email=:email LIMIT 1 FOR UPDATE');
    $userStatement->execute([':email' => $email]);
    $existing = $userStatement->fetch();

    $hash = password_hash($password, PASSWORD_DEFAULT);
    if ($existing) {
        if ((int)$existing['role_id'] !== (int)$adminRoleId) {
            throw new RuntimeException("That email already belongs to a non-admin account. No changes were made.");
        }
        $update = $pdo->prepare("UPDATE users SET full_name=:name,password_hash=:hash,status='active',updated_at=CURRENT_TIMESTAMP WHERE id=:id");
        $update->execute([':name' => $name, ':hash' => $hash, ':id' => (int)$existing['id']]);
        $action = 'Updated the selected existing Admin account.';
    } else {
        $insert = $pdo->prepare("INSERT INTO users (role_id,full_name,email,password_hash,status) VALUES (:role,:name,:email,:hash,'active')");
        $insert->execute([':role' => (int)$adminRoleId, ':name' => $name, ':email' => $email, ':hash' => $hash]);
        $action = 'Created the Admin account.';
    }

    $pdo->commit();
    fwrite(STDOUT, $action . "\n");
    fwrite(STDOUT, "Login URL: /AuthController/login?area=admin\n");
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "Admin setup failed: " . $exception->getMessage() . "\n");
    exit(1);
}
