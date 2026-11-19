<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/Database.php';

class Article
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getInstance()->getConnection();
    }

    public function getAllAdmin($limit, $offset, array $filters = []): array
    {
        $where = [];
        $params = [];
        if (!empty($filters['status']) && in_array($filters['status'], ['draft', 'published'], true)) {
            $where[] = 'a.status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['category_id'])) {
            $where[] = 'a.category_id = :category_id';
            $params[':category_id'] = (int)$filters['category_id'];
        }
        if (!empty($filters['query'])) {
            $where[] = '(a.title LIKE :title_query OR a.summary LIKE :summary_query)';
            $params[':title_query'] = '%' . $filters['query'] . '%';
            $params[':summary_query'] = '%' . $filters['query'] . '%';
        }
        $sql = 'SELECT a.*, c.name AS category_name, u.full_name AS author_name
                FROM articles a LEFT JOIN categories c ON c.id = a.category_id
                LEFT JOIN users u ON u.id = a.user_id';
        if ($where !== []) $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= ' ORDER BY a.created_at DESC, a.id DESC LIMIT :limit OFFSET :offset';
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', max(1, (int)$limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, (int)$offset), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countAllAdmin(array $filters = []): int
    {
        [$where, $params] = $this->adminFilterSql($filters);
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM articles a' . ($where ? ' WHERE ' . $where : ''));
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    private function adminFilterSql(array $filters): array
    {
        $where = [];
        $params = [];
        if (!empty($filters['status']) && in_array($filters['status'], ['draft', 'published'], true)) {
            $where[] = 'a.status = :status'; $params[':status'] = $filters['status'];
        }
        if (!empty($filters['category_id'])) {
            $where[] = 'a.category_id = :category_id'; $params[':category_id'] = (int)$filters['category_id'];
        }
        if (!empty($filters['query'])) {
            $where[] = '(a.title LIKE :title_query OR a.summary LIKE :summary_query)';
            $params[':title_query'] = '%' . $filters['query'] . '%'; $params[':summary_query'] = '%' . $filters['query'] . '%';
        }
        return [implode(' AND ', $where), $params];
    }

    public function countPublished($categoryId = null): int
    {
        $sql = "SELECT COUNT(*) FROM articles a JOIN categories c ON c.id=a.category_id
                WHERE a.status='published' AND a.published_at IS NOT NULL AND a.published_at <= CURRENT_TIMESTAMP
                AND c.status='active'
                AND (c.parent_id IS NULL OR EXISTS (SELECT 1 FROM categories p WHERE p.id=c.parent_id AND p.status='active'))";
        if ($categoryId !== null) $sql .= ' AND a.category_id = :category_id';
        $stmt = $this->pdo->prepare($sql);
        if ($categoryId !== null) $stmt->bindValue(':category_id', (int)$categoryId, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function getPublished($limit = 10, $offset = 0, $categoryId = null): array
    {
        $sql = "SELECT a.*, c.name AS category_name, c.slug AS category_slug, u.full_name AS author_name
                FROM articles a JOIN categories c ON c.id=a.category_id LEFT JOIN users u ON u.id=a.user_id
                WHERE a.status='published' AND a.published_at IS NOT NULL AND a.published_at <= CURRENT_TIMESTAMP
                AND c.status='active'
                AND (c.parent_id IS NULL OR EXISTS
                    (SELECT 1 FROM categories p WHERE p.id=c.parent_id AND p.status='active'))";
        if ($categoryId !== null) $sql .= ' AND a.category_id = :category_id';
        $sql .= ' ORDER BY a.published_at DESC, a.id DESC LIMIT :limit OFFSET :offset';
        $stmt = $this->pdo->prepare($sql);
        if ($categoryId !== null) $stmt->bindValue(':category_id', (int)$categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, min(100, (int)$limit)), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, (int)$offset), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getLatestPublishedExcluding(array $excludedIds, int $limit = 10, int $offset = 0): array
    {
        $excludedIds = array_values(array_unique(array_filter(array_map('intval', $excludedIds), static fn(int $id): bool => $id > 0)));
        $sql = "SELECT a.*, c.name AS category_name, c.slug AS category_slug, u.full_name AS author_name
                FROM articles a JOIN categories c ON c.id=a.category_id LEFT JOIN users u ON u.id=a.user_id
                WHERE a.status='published' AND a.published_at IS NOT NULL AND a.published_at <= CURRENT_TIMESTAMP
                AND c.status='active'
                AND (c.parent_id IS NULL OR EXISTS
                    (SELECT 1 FROM categories p WHERE p.id=c.parent_id AND p.status='active'))";
        $params = [];
        if ($excludedIds !== []) {
            $placeholders = [];
            foreach ($excludedIds as $index => $id) {
                $placeholder = ':excluded_' . $index;
                $placeholders[] = $placeholder;
                $params[$placeholder] = $id;
            }
            $sql .= ' AND a.id NOT IN (' . implode(', ', $placeholders) . ')';
        }
        $sql .= ' ORDER BY a.published_at DESC, a.id DESC LIMIT :limit OFFSET :offset';
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit', max(1, min(100, $limit)), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countLatestPublishedExcluding(array $excludedIds): int
    {
        $excludedIds = array_values(array_unique(array_filter(
            array_map('intval', $excludedIds),
            static fn(int $id): bool => $id > 0
        )));
        $sql = "SELECT COUNT(*) FROM articles a JOIN categories c ON c.id=a.category_id
            WHERE a.status='published' AND a.published_at IS NOT NULL AND a.published_at <= CURRENT_TIMESTAMP
            AND c.status='active'
            AND (c.parent_id IS NULL OR EXISTS
                (SELECT 1 FROM categories p WHERE p.id=c.parent_id AND p.status='active'))";
        $params = [];
        if ($excludedIds !== []) {
            $placeholders = [];
            foreach ($excludedIds as $index => $id) {
                $placeholder = ':excluded_' . $index;
                $placeholders[] = $placeholder;
                $params[$placeholder] = $id;
            }
            $sql .= ' AND a.id NOT IN (' . implode(', ', $placeholders) . ')';
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getPublishedCategorySections(array $categoryIds, int $limitPerCategory = 3): array
    {
        $categoryIds = array_values(array_unique(array_filter(array_map('intval', $categoryIds), static fn(int $id): bool => $id > 0)));
        if ($categoryIds === []) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach ($categoryIds as $index => $id) {
            $placeholder = ':category_' . $index;
            $placeholders[] = $placeholder;
            $params[$placeholder] = $id;
        }
        $params[':limit_per_category'] = max(1, min(10, $limitPerCategory));

        $sql = "SELECT * FROM (
                    SELECT root.id AS section_id, root.name AS section_name, root.slug AS section_slug,
                        a.*, content_category.name AS category_name, content_category.slug AS category_slug,
                        u.full_name AS author_name,
                        ROW_NUMBER() OVER (PARTITION BY root.id ORDER BY a.published_at DESC, a.id DESC) AS section_position
                    FROM categories root
                    JOIN categories content_category
                        ON content_category.id = root.id OR content_category.parent_id = root.id
                    JOIN articles a ON a.category_id = content_category.id
                    LEFT JOIN users u ON u.id = a.user_id
                    WHERE root.id IN (" . implode(', ', $placeholders) . ")
                        AND root.status = 'active'
                        AND content_category.status = 'active'
                        AND (content_category.parent_id IS NULL OR EXISTS
                            (SELECT 1 FROM categories parent
                                WHERE parent.id = content_category.parent_id AND parent.status = 'active'))
                        AND a.status = 'published'
                        AND a.published_at IS NOT NULL
                        AND a.published_at <= CURRENT_TIMESTAMP
                ) AS ranked
                WHERE section_position <= :limit_per_category
                ORDER BY section_id, section_position";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        }
        $stmt->execute();

        $sections = [];
        foreach ($stmt->fetchAll() as $row) {
            $sectionId = (int)$row['section_id'];
            if (!isset($sections[$sectionId])) {
                $sections[$sectionId] = [
                    'id' => $sectionId,
                    'name' => $row['section_name'],
                    'slug' => $row['section_slug'],
                    'articles' => [],
                ];
            }
            unset($row['section_id'], $row['section_name'], $row['section_slug'], $row['section_position']);
            $sections[$sectionId]['articles'][] = $row;
        }

        return $sections;
    }

    public function getRelatedPublished(int $articleId, int $categoryId, int $limit = 3): array
    {
        $stmt = $this->pdo->prepare("SELECT a.*, c.name AS category_name, c.slug AS category_slug,
                    u.full_name AS author_name
                FROM articles a
                JOIN categories c ON c.id = a.category_id
                LEFT JOIN users u ON u.id = a.user_id
                WHERE a.id <> :article_id
                    AND a.category_id = :category_id
                    AND a.status = 'published'
                    AND a.published_at IS NOT NULL
                    AND a.published_at <= CURRENT_TIMESTAMP
                    AND c.status = 'active'
                    AND (c.parent_id IS NULL OR EXISTS
                        (SELECT 1 FROM categories parent
                            WHERE parent.id = c.parent_id AND parent.status = 'active'))
                ORDER BY a.published_at DESC, a.id DESC
                LIMIT :limit");
        $stmt->bindValue(':article_id', $articleId, PDO::PARAM_INT);
        $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, min(10, $limit)), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getFeaturedPublished(int $limit = 6): array
    {
        $stmt = $this->pdo->prepare("SELECT a.*, c.name AS category_name, c.slug AS category_slug
            FROM articles a JOIN categories c ON c.id=a.category_id
            WHERE a.status='published' AND a.is_featured=1 AND a.published_at <= CURRENT_TIMESTAMP AND c.status='active'
            AND (c.parent_id IS NULL OR EXISTS (SELECT 1 FROM categories p WHERE p.id=c.parent_id AND p.status='active'))
            ORDER BY a.published_at DESC, a.id DESC LIMIT :limit");
        $stmt->bindValue(':limit', max(1, min(20, $limit)), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT a.*, c.name AS category_name, c.slug AS category_slug, u.full_name AS author_name
            FROM articles a LEFT JOIN categories c ON c.id=a.category_id LEFT JOIN users u ON u.id=a.user_id WHERE a.id=:id');
        $stmt->execute([':id' => (int)$id]);
        $article = $stmt->fetch();
        if (!$article) return null;
        $article['images'] = $this->getArticleImages((int)$id);
        return $article;
    }

    public function getPublishedById($id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT a.*, c.name AS category_name, c.slug AS category_slug, u.full_name AS author_name
            FROM articles a JOIN categories c ON c.id=a.category_id LEFT JOIN users u ON u.id=a.user_id
            WHERE a.id=:id AND a.status='published' AND a.published_at IS NOT NULL
            AND a.published_at <= CURRENT_TIMESTAMP AND c.status='active'
            AND (c.parent_id IS NULL OR EXISTS (SELECT 1 FROM categories p WHERE p.id=c.parent_id AND p.status='active')) LIMIT 1");
        $stmt->execute([':id' => (int)$id]);
        $article = $stmt->fetch();
        if (!$article) return null;
        $article['images'] = $this->getArticleImages((int)$id);
        return $article;
    }

    public function getArticleImages($articleId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM article_images WHERE article_id=:id ORDER BY sort_order, id');
        $stmt->execute([':id' => (int)$articleId]);
        return $stmt->fetchAll();
    }

    public function getCategories(): array
    {
        return $this->pdo->query("SELECT c.id, c.name, c.slug, c.parent_id FROM categories c
            WHERE c.status='active' AND (c.parent_id IS NULL OR EXISTS
            (SELECT 1 FROM categories p WHERE p.id=c.parent_id AND p.status='active'))
            ORDER BY c.parent_id IS NOT NULL, c.sort_order, c.name")->fetchAll();
    }

    public function searchPublished(string $query, int $limit = 10, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare("SELECT a.*, c.name AS category_name, c.slug AS category_slug
            FROM articles a JOIN categories c ON c.id=a.category_id
            WHERE a.status='published' AND a.published_at IS NOT NULL AND a.published_at <= CURRENT_TIMESTAMP
            AND c.status='active' AND (c.parent_id IS NULL OR EXISTS
            (SELECT 1 FROM categories p WHERE p.id=c.parent_id AND p.status='active')) AND a.title LIKE :query
            ORDER BY a.published_at DESC, a.id DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':query', '%' . $query . '%');
        $stmt->bindValue(':limit', max(1, min(100, $limit)), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countSearchPublished(string $query): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM articles a JOIN categories c ON c.id=a.category_id
            WHERE a.status='published' AND a.published_at IS NOT NULL AND a.published_at <= CURRENT_TIMESTAMP
            AND c.status='active' AND (c.parent_id IS NULL OR EXISTS
            (SELECT 1 FROM categories p WHERE p.id=c.parent_id AND p.status='active')) AND a.title LIKE :query");
        $stmt->execute([':query' => '%' . $query . '%']);
        return (int)$stmt->fetchColumn();
    }

    public function slugExists(string $slug, int $excludeId = 0): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM articles WHERE slug=:slug AND id<>:id LIMIT 1');
        $stmt->execute([':slug' => $slug, ':id' => $excludeId]);
        return (bool)$stmt->fetchColumn();
    }

    public function create($data): int
    {
        $status = ($data['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
        $publishedAt = $status === 'published' ? ($data['published_at'] ?? date('Y-m-d H:i:s')) : null;
        $stmt = $this->pdo->prepare('INSERT INTO articles
            (category_id,user_id,title,slug,summary,content,thumbnail,status,is_featured,published_at)
            VALUES (:category_id,:user_id,:title,:slug,:summary,:content,:thumbnail,:status,:is_featured,:published_at)');
        $stmt->execute([
            ':category_id' => (int)$data['category_id'], ':user_id' => (int)$data['user_id'],
            ':title' => $data['title'], ':slug' => $data['slug'], ':summary' => $data['summary'] ?? null,
            ':content' => $data['content'], ':thumbnail' => $data['thumbnail'] ?? null, ':status' => $status,
            ':is_featured' => $status === 'published' ? (int)($data['is_featured'] ?? 0) : 0,
            ':published_at' => $publishedAt,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function addArticleImage($articleId, $imagePath, ?string $caption = null, ?string $altText = null, int $sortOrder = 0): bool
    {
        $stmt = $this->pdo->prepare('INSERT INTO article_images (article_id,image_path,caption,alt_text,sort_order)
            VALUES (:article_id,:image_path,:caption,:alt_text,:sort_order)');
        return $stmt->execute([':article_id' => (int)$articleId, ':image_path' => $imagePath,
            ':caption' => $caption, ':alt_text' => $altText, ':sort_order' => $sortOrder]);
    }

    public function update($id, $data): bool
    {
        $status = ($data['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
        $publishedAt = $status === 'published' ? ($data['published_at'] ?? date('Y-m-d H:i:s')) : null;
        $stmt = $this->pdo->prepare('UPDATE articles SET category_id=:category_id,title=:title,slug=:slug,
            summary=:summary,content=:content,thumbnail=:thumbnail,status=:status,is_featured=:is_featured,
            published_at=:published_at,updated_at=CURRENT_TIMESTAMP WHERE id=:id');
        return $stmt->execute([
            ':id' => (int)$id, ':category_id' => (int)$data['category_id'], ':title' => $data['title'],
            ':slug' => $data['slug'], ':summary' => $data['summary'] ?? null, ':content' => $data['content'],
            ':thumbnail' => $data['thumbnail'] ?? null, ':status' => $status,
            ':is_featured' => $status === 'published' ? (int)($data['is_featured'] ?? 0) : 0,
            ':published_at' => $publishedAt,
        ]);
    }

    public function delete($id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM articles WHERE id=:id');
        return $stmt->execute([':id' => (int)$id]);
    }

    public function deleteArticleImage($imageId, ?int $articleId = null)
    {
        $sql = 'SELECT image_path FROM article_images WHERE id=:id';
        if ($articleId !== null) $sql .= ' AND article_id=:article_id';
        $stmt = $this->pdo->prepare($sql);
        $params = [':id' => (int)$imageId];
        if ($articleId !== null) $params[':article_id'] = $articleId;
        $stmt->execute($params);
        $image = $stmt->fetch();
        if (!$image) return false;
        $deleteSql = 'DELETE FROM article_images WHERE id=:id';
        if ($articleId !== null) $deleteSql .= ' AND article_id=:article_id';
        $delete = $this->pdo->prepare($deleteSql);
        $delete->execute($params);
        return $image['image_path'];
    }

    public function fileIsReferenced(string $path): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM articles WHERE thumbnail=:thumb
            UNION ALL SELECT 1 FROM article_images WHERE image_path=:image LIMIT 1');
        $stmt->execute([':thumb' => $path, ':image' => $path]);
        return (bool)$stmt->fetchColumn();
    }

    public function updateImageOrder(int $articleId, array $imageIds): void
    {
        $stmt = $this->pdo->prepare('UPDATE article_images SET sort_order=:sort WHERE id=:id AND article_id=:article');
        foreach (array_values($imageIds) as $order => $imageId) {
            $stmt->execute([':sort' => $order, ':id' => (int)$imageId, ':article' => $articleId]);
        }
    }

    public function getDashboardStats(): array
    {
        return [
            'articles' => (int)$this->pdo->query('SELECT COUNT(*) FROM articles')->fetchColumn(),
            'published' => (int)$this->pdo->query("SELECT COUNT(*) FROM articles WHERE status='published' AND published_at<=CURRENT_TIMESTAMP")->fetchColumn(),
            'drafts' => (int)$this->pdo->query("SELECT COUNT(*) FROM articles WHERE status='draft'")->fetchColumn(),
            'categories' => (int)$this->pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn(),
            'users' => (int)$this->pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
            'pending_comments' => (int)$this->pdo->query("SELECT COUNT(*) FROM comments WHERE status='pending'")->fetchColumn(),
        ];
    }
}
