<?php

class Article {
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

    // Lấy danh sách bài viết cho trang Admin (kèm tên danh mục & phân trang)
    public function getAllAdmin($limit, $offset) {
        $sql = "SELECT a.*, c.name AS category_name 
                FROM articles a 
                LEFT JOIN categories c ON a.category_id = c.id 
                ORDER BY a.created_at DESC 
                LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Đếm tổng số bài viết trong hệ thống
    public function countAllAdmin() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM articles");
        return (int)$stmt->fetchColumn();
    }

    // Lấy danh sách bài viết ĐÃ XUẤT BẢN (published) cho trang User
    public function getPublished($limit = 10, $offset = 0, $categoryId = null) {
        $sql = "SELECT a.*, c.name AS category_name 
                FROM articles a 
                LEFT JOIN categories c ON a.category_id = c.id 
                WHERE a.status = 'published'";
        if ($categoryId) {
            $sql .= " AND a.category_id = :category_id";
        }
        $sql .= " ORDER BY a.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        if ($categoryId) {
            $stmt->bindValue(':category_id', (int)$categoryId, PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Đếm tổng bài viết đã xuất bản
    public function countPublished($categoryId = null) {
        $sql = "SELECT COUNT(*) FROM articles WHERE status = 'published'";
        if ($categoryId) {
            $sql .= " AND category_id = :category_id";
        }
        $stmt = $this->pdo->prepare($sql);
        if ($categoryId) {
            $stmt->bindValue(':category_id', (int)$categoryId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    // Lấy chi tiết 1 bài viết kèm danh sách ảnh phụ
    public function getById($id) {
        $sql = "SELECT a.*, c.name AS category_name 
                FROM articles a 
                LEFT JOIN categories c ON a.category_id = c.id 
                WHERE a.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        $article = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($article) {
            $article['images'] = $this->getArticleImages($id);
        }
        return $article;
    }

    // Lấy danh sách ảnh trong bảng article_images
    public function getArticleImages($articleId) {
        $stmt = $this->pdo->prepare("SELECT * FROM article_images WHERE article_id = :article_id");
        $stmt->bindValue(':article_id', (int)$articleId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy danh sách danh mục từ bảng categories
    public function getCategories() {
        $stmt = $this->pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Thêm bài viết mới
    public function create($data) {
        $sql = "INSERT INTO articles (category_id, title, slug, summary, content, thumbnail, status) 
                VALUES (:category_id, :title, :slug, :summary, :content, :thumbnail, :status)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':category_id', (int)$data['category_id'], PDO::PARAM_INT);
        $stmt->bindValue(':title', $data['title'], PDO::PARAM_STR);
        $stmt->bindValue(':slug', $data['slug'], PDO::PARAM_STR);
        $stmt->bindValue(':summary', $data['summary'], PDO::PARAM_STR);
        $stmt->bindValue(':content', $data['content'], PDO::PARAM_STR);
        $stmt->bindValue(':thumbnail', $data['thumbnail'], PDO::PARAM_STR);
        $stmt->bindValue(':status', $data['status'], PDO::PARAM_STR);
        $stmt->execute();
        return $this->pdo->lastInsertId();
    }

    // Lưu từng ảnh vào bảng article_images
    public function addArticleImage($articleId, $imagePath) {
        $sql = "INSERT INTO article_images (article_id, image_path) VALUES (:article_id, :image_path)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':article_id', (int)$articleId, PDO::PARAM_INT);
        $stmt->bindValue(':image_path', $imagePath, PDO::PARAM_STR);
        return $stmt->execute();
    }

    // Cập nhật bài viết
    public function update($id, $data) {
        $sql = "UPDATE articles SET 
                    category_id = :category_id,
                    title = :title,
                    slug = :slug,
                    summary = :summary,
                    content = :content,
                    thumbnail = :thumbnail,
                    status = :status
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->bindValue(':category_id', (int)$data['category_id'], PDO::PARAM_INT);
        $stmt->bindValue(':title', $data['title'], PDO::PARAM_STR);
        $stmt->bindValue(':slug', $data['slug'], PDO::PARAM_STR);
        $stmt->bindValue(':summary', $data['summary'], PDO::PARAM_STR);
        $stmt->bindValue(':content', $data['content'], PDO::PARAM_STR);
        $stmt->bindValue(':thumbnail', $data['thumbnail'], PDO::PARAM_STR);
        $stmt->bindValue(':status', $data['status'], PDO::PARAM_STR);
        return $stmt->execute();
    }

    // Xóa bài viết
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM articles WHERE id = :id");
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Xóa 1 ảnh phụ và trả về tên file để xóa trên ổ cứng
    public function deleteArticleImage($imageId) {
        $stmt = $this->pdo->prepare("SELECT image_path FROM article_images WHERE id = :id");
        $stmt->bindValue(':id', (int)$imageId, PDO::PARAM_INT);
        $stmt->execute();
        $img = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($img) {
            $delStmt = $this->pdo->prepare("DELETE FROM article_images WHERE id = :id");
            $delStmt->bindValue(':id', (int)$imageId, PDO::PARAM_INT);
            $delStmt->execute();
            return $img['image_path'];
        }
        return false;
    }
}
