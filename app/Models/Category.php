<?php

class Category {
    private $pdo;

    public function __construct($pdo = null) {
        if ($pdo) {
            $this->pdo = $pdo;
        } else {
            if (!class_exists('Database')) {
                $dbPaths = [
                    __DIR__ . '/../core/Database.php',
                    __DIR__ . '/../config/Database.php'
                ];
                foreach ($dbPaths as $path) {
                    if (file_exists($path)) {
                        require_once $path;
                        break;
                    }
                }
            }
            $db = new Database();
            $this->pdo = $db->pdo;
        }
    }

    // Lấy danh sách danh mục kèm số bài viết (phân trang)
    public function getAll($limit, $offset) {
        $sql = "SELECT c.*, COUNT(a.id) AS article_count
                FROM categories c
                LEFT JOIN articles a ON a.category_id = c.id
                GROUP BY c.id
                ORDER BY c.id DESC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll() {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    }

    // Lấy chi tiết 1 danh mục
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Kiểm tra trùng tên (bỏ qua id đang sửa)
    public function nameExists($name, $excludeId = 0) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM categories WHERE name = :name AND id <> :id");
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':id', (int)$excludeId, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }

    // Đếm số bài viết thuộc danh mục
    public function countArticles($id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM articles WHERE category_id = :id");
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function create($data) {
        $sql = "INSERT INTO categories (name, slug, description) VALUES (:name, :slug, :description)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
        $stmt->bindValue(':slug', $data['slug'], PDO::PARAM_STR);
        $stmt->bindValue(':description', $data['description'], PDO::PARAM_STR);
        $stmt->execute();
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE categories SET name = :name, slug = :slug, description = :description WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
        $stmt->bindValue(':slug', $data['slug'], PDO::PARAM_STR);
        $stmt->bindValue(':description', $data['description'], PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
