<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/Database.php';

class User
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getInstance()->getConnection();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON r.id=u.role_id WHERE u.email=:email LIMIT 1');
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id=:id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $name, string $email, string $passwordHash, int $roleId): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (role_id,full_name,email,password_hash) VALUES (:role,:name,:email,:hash)');
        $stmt->execute([':role' => $roleId, ':name' => $name, ':email' => $email, ':hash' => $passwordHash]);
        return (int)$this->pdo->lastInsertId();
    }

    public function getAllWithRole(int $limit, int $offset, string $query = ''): array
    {
        $sql = 'SELECT u.id,u.role_id,u.full_name,u.email,u.status,u.created_at,r.name AS role_name
                FROM users u JOIN roles r ON r.id=u.role_id';
        if ($query !== '') $sql .= ' WHERE u.full_name LIKE :name_q OR u.email LIKE :email_q';
        $sql .= ' ORDER BY u.id DESC LIMIT :limit OFFSET :offset';
        $stmt = $this->pdo->prepare($sql);
        if ($query !== '') {
            $stmt->bindValue(':name_q', '%' . $query . '%');
            $stmt->bindValue(':email_q', '%' . $query . '%');
        }
        $stmt->bindValue(':limit', max(1, min(100, $limit)), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countAll(string $query = ''): int
    {
        if ($query === '') return (int)$this->pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM users WHERE full_name LIKE :name_q OR email LIKE :email_q');
        $stmt->execute([':name_q' => '%' . $query . '%', ':email_q' => '%' . $query . '%']);
        return (int)$stmt->fetchColumn();
    }

    public function getRoles(): array
    {
        return $this->pdo->query('SELECT id,name FROM roles ORDER BY id')->fetchAll();
    }

    public function roleIdByName(string $name): ?int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM roles WHERE name=:name LIMIT 1');
        $stmt->execute([':name' => $name]);
        $id = $stmt->fetchColumn();
        return $id === false ? null : (int)$id;
    }

    public function updateBasic(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare('UPDATE users SET full_name=:name,email=:email,role_id=:role,updated_at=CURRENT_TIMESTAMP WHERE id=:id');
        return $stmt->execute([':id' => $id, ':name' => $data['full_name'], ':email' => $data['email'], ':role' => (int)$data['role_id']]);
    }

    public function setStatus(int $id, string $status): bool
    {
        if (!in_array($status, ['active', 'locked'], true)) throw new InvalidArgumentException('Invalid user status.');
        $stmt = $this->pdo->prepare('UPDATE users SET status=:status,updated_at=CURRENT_TIMESTAMP WHERE id=:id');
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    public function countAdmins(): int
    {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM users u JOIN roles r ON r.id=u.role_id WHERE r.name='admin' AND u.status='active'")->fetchColumn();
    }

    public function emailExists(string $email, int $excludeId = 0): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM users WHERE email=:email AND id<>:id LIMIT 1');
        $stmt->execute([':email' => $email, ':id' => $excludeId]);
        return (bool)$stmt->fetchColumn();
    }
}
