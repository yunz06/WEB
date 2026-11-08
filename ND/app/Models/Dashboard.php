<?php

declare(strict_types=1);

namespace App\Models;

use InvalidArgumentException;
use RuntimeException;
use PDO;
use PDOException;
use Throwable;

/**
 * Đọc số liệu thật, chỉ SELECT, không tạo dữ liệu giả.
 * Những số liệu không thể xác thực trả về null, không tráo thành 0.
 */
final class Dashboard
{
    private PDO $database;
    private array $configuration;

    public function __construct(PDO $database, array $configuration)
    {
        $this->database = $database;
        $this->configuration = $configuration;
    }

    public function getOverview(int $latestLimit = 5): array
    {
        if ($latestLimit < 1 || $latestLimit > 20) {
            throw new InvalidArgumentException('Số bài viết mới nhất phải từ 1 đến 20.');
        }

        $metrics = [
            'articles' => null,
            'published_articles' => null,
            'users' => null,
            'categories' => null,
            'comments' => null,
        ];
        $latestArticles = [];
        $warnings = [];

        foreach (['articles', 'users', 'categories', 'comments'] as $tableKey) {
            try {
                $metrics[$tableKey] = $this->countRows($tableKey);
            } catch (Throwable $error) {
                error_log('Dashboard count ' . $tableKey . ': ' . $error->getMessage());
                $warnings[] = 'Chưa đọc được thống kê ' . $tableKey . ' từ MySQL.';
            }
        }

        $statusColumn = $this->setting('article', 'status_column');
        $publishedValue = $this->setting('article', 'published_value');

        if ($statusColumn === null || $publishedValue === null) {
            $warnings[] = 'Chưa đối chiếu cột trạng thái và giá trị Published với schema.';
        } else {
            try {
                $metrics['published_articles'] = $this->countPublishedArticles(
                    $statusColumn,
                    $publishedValue
                );
            } catch (Throwable $error) {
                error_log('Dashboard published articles: ' . $error->getMessage());
                $warnings[] = 'Chưa đọc được số bài Published từ MySQL.';
            }
        }

        $titleColumn = $this->setting('article', 'title_column');
        $sortColumn = $this->setting('article', 'sort_column');

        if ($titleColumn === null || $sortColumn === null) {
            $warnings[] = 'Chưa đối chiếu cột tiêu đề và cột sắp xếp bài mới nhất với schema.';
        } else {
            try {
                $latestArticles = $this->getLatestArticles(
                    $titleColumn,
                    $sortColumn,
                    $latestLimit
                );
            } catch (Throwable $error) {
                error_log('Dashboard latest articles: ' . $error->getMessage());
                $warnings[] = 'Chưa đọc được danh sách bài viết mới nhất từ MySQL.';
            }
        }

        return [
            'metrics' => $metrics,
            'latest_articles' => $latestArticles,
            'warnings' => $warnings,
            'sort_column' => $sortColumn,
        ];
    }

    /**
     * Trả về số dòng thực từ bảng được nêu trong ảnh phân công.
     */
    public function countRows(string $tableKey): int
    {
        $table = $this->getTableName($tableKey);
        $query = 'SELECT COUNT(*) FROM ' . $this->quoteIdentifier($table);
        $statement = $this->database->query($query);

        if ($statement === false) {
            throw new PDOException('Không thực hiện được SELECT COUNT(*).');
        }

        return $this->readCount($statement);
    }

    public function countPublishedArticles(string $column, string $value): int
    {
        $table = $this->getTableName('articles');
        $query = 'SELECT COUNT(*) FROM ' . $this->quoteIdentifier($table)
            . ' WHERE ' . $this->quoteIdentifier($column) . ' = :published_value';

        $statement = $this->database->prepare($query);
        $statement->bindValue(':published_value', $value, PDO::PARAM_STR);
        if ($statement->execute() !== true) {
            throw new RuntimeException('Không thực hiện được truy vấn Published.');
        }

        return $this->readCount($statement);
    }

    public function getLatestArticles(string $titleColumn, string $sortColumn, int $limit): array
    {
        if ($limit < 1 || $limit > 20) {
            throw new InvalidArgumentException('Giới hạn bài viết không hợp lệ.');
        }

        $table = $this->getTableName('articles');
        $idColumn = $this->setting('article', 'id_column');
        $query = 'SELECT '
            . $this->quoteIdentifier($titleColumn) . ' AS article_title, '
            . $this->quoteIdentifier($sortColumn) . ' AS article_sort_value '
            . 'FROM ' . $this->quoteIdentifier($table) . ' '
            . 'ORDER BY ' . $this->quoteIdentifier($sortColumn) . ' DESC ';

        if ($idColumn !== null && $idColumn !== $sortColumn) {
            $query .= ', ' . $this->quoteIdentifier($idColumn) . ' DESC ';
        }

        $query .= 'LIMIT :row_limit';

        $statement = $this->database->prepare($query);
        $statement->bindValue(':row_limit', $limit, PDO::PARAM_INT);
        if ($statement->execute() !== true) {
            throw new RuntimeException('Không thực hiện được truy vấn bài viết mới nhất.');
        }

        $articles = $statement->fetchAll(PDO::FETCH_ASSOC);
        if (!is_array($articles)) {
            throw new RuntimeException('Không đọc được kết quả bài viết mới nhất.');
        }

        return $articles;
    }

    /**
     * PDO ERRMODE_SILENT có thể trả false khi SELECT lỗi. Không được biến false
     * thành 0 vì số liệu 0 có nghĩa nghiệp vụ hoàn toàn khác với lỗi MySQL.
     */
    private function readCount(\PDOStatement $statement): int
    {
        $value = $statement->fetchColumn();

        if ($value === false || !is_numeric($value)) {
            throw new RuntimeException('Không đọc được COUNT(*) từ MySQL.');
        }

        $count = filter_var((string) $value, FILTER_VALIDATE_INT);
        if ($count === false || $count < 0) {
            throw new RuntimeException('Giá trị COUNT(*) từ MySQL không hợp lệ.');
        }

        return $count;
    }

    private function getTableName(string $tableKey): string
    {
        $tables = $this->configuration['tables'] ?? [];

        if (!is_array($tables) || !isset($tables[$tableKey])) {
            throw new InvalidArgumentException('Thiếu cấu hình bảng: ' . $tableKey);
        }

        $tableName = $tables[$tableKey];

        if (!is_string($tableName) || $tableName === '') {
            throw new InvalidArgumentException('Tên bảng chưa được cấu hình: ' . $tableKey);
        }

        return $tableName;
    }

    private function setting(string $sectionName, string $settingName): ?string
    {
        $section = $this->configuration[$sectionName] ?? [];

        if (!is_array($section)) {
            return null;
        }

        $value = $section[$settingName] ?? null;

        if (!is_string($value) || $value === '') {
            return null;
        }

        return $value;
    }

    private function quoteIdentifier(string $identifier): string
    {
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $identifier) !== 1) {
            throw new InvalidArgumentException('Tên bảng/cột SQL không hợp lệ.');
        }

        return '`' . $identifier . '`';
    }
}
