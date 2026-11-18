<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/Database.php';

class Category
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getInstance()->getConnection();
    }

    public function getAll($limit, $offset): array
    {
        $stmt = $this->pdo->prepare('SELECT c.*,
            (SELECT COUNT(*) FROM articles a WHERE a.category_id=c.id) AS article_count,
            (SELECT COUNT(*) FROM categories child WHERE child.parent_id=c.id) AS child_count
            FROM categories c ORDER BY c.parent_id IS NOT NULL, c.sort_order, c.name
            LIMIT :limit OFFSET :offset');
        $stmt->bindValue(':limit', max(1, (int)$limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, (int)$offset), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countAll(): int
    {
        return (int)$this->pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
    }

    public function getById($id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM categories WHERE id=:id');
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch() ?: null;
    }

    public function getBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare("SELECT c.* FROM categories c WHERE c.slug=:slug AND c.status='active'
            AND (c.parent_id IS NULL OR EXISTS (SELECT 1 FROM categories p WHERE p.id=c.parent_id AND p.status='active')) LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        return $stmt->fetch() ?: null;
    }

    public function getPublicNavigation(): array
    {
        return $this->pdo->query("SELECT c.id,c.parent_id,c.name,c.slug FROM categories c WHERE c.status='active'
            AND (c.parent_id IS NULL OR EXISTS (SELECT 1 FROM categories p WHERE p.id=c.parent_id AND p.status='active'))
            ORDER BY c.parent_id IS NOT NULL, c.sort_order, c.name")->fetchAll();
    }

    public function getPublicPage(int $id, int $limit, int $offset): array
    {
        $stmt = $this->pdo->prepare("SELECT a.*, c.name AS category_name, c.slug AS category_slug
            FROM articles a JOIN categories c ON c.id=a.category_id
            WHERE c.id=:id AND c.status='active'
            AND (c.parent_id IS NULL OR EXISTS (SELECT 1 FROM categories p WHERE p.id=c.parent_id AND p.status='active'))
            AND a.status='published'
            AND a.published_at IS NOT NULL AND a.published_at<=CURRENT_TIMESTAMP
            ORDER BY a.published_at DESC,a.id DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, min(100, $limit)), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countPublicArticles(int $id): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM articles a JOIN categories c ON c.id=a.category_id
            WHERE a.category_id=:id AND c.status='active'
            AND (c.parent_id IS NULL OR EXISTS (SELECT 1 FROM categories p WHERE p.id=c.parent_id AND p.status='active'))
            AND a.status='published' AND a.published_at IS NOT NULL AND a.published_at<=CURRENT_TIMESTAMP");
        $stmt->execute([':id' => $id]);
        return (int)$stmt->fetchColumn();
    }

    public function nameExists($name, $excludeId = 0): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM categories WHERE name=:name AND id<>:id LIMIT 1');
        $stmt->execute([':name' => $name, ':id' => (int)$excludeId]);
        return (bool)$stmt->fetchColumn();
    }

    public function slugExists(string $slug, int $excludeId = 0): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM categories WHERE slug=:slug AND id<>:id LIMIT 1');
        $stmt->execute([':slug' => $slug, ':id' => $excludeId]);
        return (bool)$stmt->fetchColumn();
    }

    public function countArticles($id): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM articles WHERE category_id=:id');
        $stmt->execute([':id' => (int)$id]);
        return (int)$stmt->fetchColumn();
    }

    public function countChildren(int $id): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM categories WHERE parent_id=:id');
        $stmt->execute([':id' => $id]);
        return (int)$stmt->fetchColumn();
    }

    public function getChildren(int $excludeId = 0): array
    {
        $stmt = $this->pdo->prepare('SELECT id,name,parent_id FROM categories WHERE id<>:id AND parent_id IS NULL ORDER BY sort_order,name');
        $stmt->execute([':id' => $excludeId]);
        return $stmt->fetchAll();
    }

    public function create($data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO categories
            (parent_id,name,slug,description,sort_order,status) VALUES (:parent_id,:name,:slug,:description,:sort_order,:status)');
        $stmt->execute([
            ':parent_id' => !empty($data['parent_id']) ? (int)$data['parent_id'] : null,
            ':name' => $data['name'], ':slug' => $data['slug'], ':description' => $data['description'] ?? null,
            ':sort_order' => max(0, (int)($data['sort_order'] ?? 0)),
            ':status' => ($data['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active',
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function update($id, $data): bool
    {
        $parentId = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;
        if ($parentId === (int)$id) throw new InvalidArgumentException('A category cannot be its own parent.');
        $stmt = $this->pdo->prepare('UPDATE categories SET parent_id=:parent_id,name=:name,slug=:slug,
            description=:description,sort_order=:sort_order,status=:status,updated_at=CURRENT_TIMESTAMP WHERE id=:id');
        return $stmt->execute([
            ':id' => (int)$id, ':parent_id' => $parentId, ':name' => $data['name'], ':slug' => $data['slug'],
            ':description' => $data['description'] ?? null, ':sort_order' => max(0, (int)($data['sort_order'] ?? 0)),
            ':status' => ($data['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active',
        ]);
    }

    public function delete($id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM categories WHERE id=:id');
        return $stmt->execute([':id' => (int)$id]);
    }
}
