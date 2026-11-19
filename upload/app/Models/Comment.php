<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/Database.php';

class Comment
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getInstance()->getConnection();
    }

    public function create(int $articleId, int $userId, string $content): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO comments (article_id,user_id,content,status) VALUES (:article,:user,:content,\'pending\')');
        $stmt->execute([':article' => $articleId, ':user' => $userId, ':content' => $content]);
        return (int)$this->pdo->lastInsertId();
    }

    public function getApprovedForArticle(int $articleId): array
    {
        $stmt = $this->pdo->prepare("SELECT c.id,c.content,c.created_at,u.full_name AS author_name FROM comments c
            JOIN users u ON u.id=c.user_id WHERE c.article_id=:article AND c.status='approved'
            AND u.status='active' ORDER BY c.created_at ASC,c.id ASC");
        $stmt->execute([':article' => $articleId]);
        return $stmt->fetchAll();
    }

    public function getForUser(int $userId, int $limit, int $offset): array
    {
        if ($userId < 1) return [];

        $stmt = $this->pdo->prepare('SELECT c.id,c.article_id,c.user_id,c.content,c.status,c.created_at,
                a.title AS article_title,a.slug AS article_slug,a.status AS article_status,
                a.published_at AS article_published_at
            FROM comments c JOIN articles a ON a.id=c.article_id
            WHERE c.user_id=:user_id
            ORDER BY c.created_at DESC,c.id DESC LIMIT :limit OFFSET :offset');
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, min(100, $limit)), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countForUser(int $userId): int
    {
        if ($userId < 1) return 0;

        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM comments WHERE user_id=:user_id');
        $stmt->execute([':user_id' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    public function getAll(int $limit, int $offset, string $status = ''): array
    {
        $sql = 'SELECT c.*,u.full_name AS user_name,a.title AS article_title FROM comments c
            JOIN users u ON u.id=c.user_id JOIN articles a ON a.id=c.article_id';
        $valid = in_array($status, ['pending','approved','hidden'], true);
        if ($valid) $sql .= ' WHERE c.status=:status';
        $sql .= ' ORDER BY c.created_at DESC,c.id DESC LIMIT :limit OFFSET :offset';
        $stmt = $this->pdo->prepare($sql);
        if ($valid) $stmt->bindValue(':status', $status);
        $stmt->bindValue(':limit', max(1, min(100, $limit)), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countAll(string $status = ''): int
    {
        $valid = in_array($status, ['pending','approved','hidden'], true);
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM comments' . ($valid ? ' WHERE status=:status' : ''));
        if ($valid) $stmt->bindValue(':status', $status);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function setStatus(int $id, string $status): bool
    {
        if (!in_array($status, ['pending','approved','hidden'], true)) throw new InvalidArgumentException('Invalid comment status.');
        $stmt = $this->pdo->prepare('UPDATE comments SET status=:status,updated_at=CURRENT_TIMESTAMP WHERE id=:id');
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM comments WHERE id=:id');
        return $stmt->execute([':id' => $id]);
    }
}
