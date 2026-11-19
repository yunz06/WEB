<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Core/Database.php';

function promptLine(string $label): string
{
    fwrite(STDOUT, $label);
    $value = fgets(STDIN);
    return $value === false ? '' : rtrim($value, "\r\n");
}

function promptSecret(string $label): string
{
    if (PHP_OS_FAMILY === 'Windows' && function_exists('proc_open')) {
        $escapedLabel = str_replace("'", "''", $label);
        $powershell = '$secret = Read-Host -Prompt ' . "'" . $escapedLabel . "'"
            . ' -AsSecureString; $ptr = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secret); '
            . 'try { [Console]::Out.Write([Runtime.InteropServices.Marshal]::PtrToStringBSTR($ptr)) } '
            . 'finally { [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($ptr) }';
        $process = proc_open(
            ['powershell.exe', '-NoProfile', '-Command', $powershell],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes
        );
        if (is_resource($process)) {
            fclose($pipes[0]);
            $value = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $error = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            $status = proc_close($process);
            if ($status === 0 && is_string($value)) {
                return rtrim($value, "\r\n");
            }
            throw new RuntimeException('Could not read the password securely in PowerShell. ' . trim((string)$error));
        }
    }

    if (PHP_OS_FAMILY !== 'Windows' && function_exists('shell_exec')) {
        $terminalState = trim((string)shell_exec('stty -g 2>/dev/null'));
        if ($terminalState !== '') {
            fwrite(STDOUT, $label);
            shell_exec('stty -echo');
            try {
                $value = fgets(STDIN);
            } finally {
                shell_exec('stty ' . escapeshellarg($terminalState));
                fwrite(STDOUT, PHP_EOL);
            }
            return $value === false ? '' : rtrim($value, "\r\n");
        }
    }

    fwrite(STDOUT, $label . " (input may be visible in this terminal): ");
    $value = fgets(STDIN);
    return $value === false ? '' : rtrim($value, "\r\n");
}

$email = strtolower(trim(promptLine('Admin email: ')));
$name = trim(promptLine('Admin display name: '));
$password = promptSecret('New admin password: ');
$confirmation = promptSecret('Confirm password: ');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $name === '' || mb_strlen($name, 'UTF-8') > 150) {
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
        throw new RuntimeException('The admin role is missing. Import the schema and seed first.');
    }

    $userStatement = $pdo->prepare('SELECT id,role_id FROM users WHERE email=:email LIMIT 1 FOR UPDATE');
    $userStatement->execute([':email' => $email]);
    $existing = $userStatement->fetch();

    $hash = password_hash($password, PASSWORD_DEFAULT);
    if ($existing) {
        if ((int)$existing['role_id'] !== (int)$adminRoleId) {
            fwrite(STDOUT, "This email already belongs to a regular User account.\n");
            fwrite(STDOUT, "Promoting it grants full Admin access to this account.\n");
            $confirmationText = promptLine('Type PROMOTE to confirm, anything else to cancel: ');
            if ($confirmationText !== 'PROMOTE') {
                throw new RuntimeException('Promotion cancelled; no account changes were made.');
            }
            $update = $pdo->prepare('UPDATE users SET role_id=:role,full_name=:name,password_hash=:hash,status=\'active\',updated_at=CURRENT_TIMESTAMP WHERE id=:id');
            $update->execute([':role' => (int)$adminRoleId, ':name' => $name, ':hash' => $hash, ':id' => (int)$existing['id']]);
            $action = 'Promoted the explicitly confirmed existing account to Admin and updated its password.';
        } else {
            $update = $pdo->prepare('UPDATE users SET full_name=:name,password_hash=:hash,status=\'active\',updated_at=CURRENT_TIMESTAMP WHERE id=:id');
            $update->execute([':name' => $name, ':hash' => $hash, ':id' => (int)$existing['id']]);
            $action = 'Updated the selected existing Admin account.';
        }
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
    fwrite(STDERR, 'Admin setup failed: ' . $exception->getMessage() . "\n");
    exit(1);
}
