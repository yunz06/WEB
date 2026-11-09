<?php

declare(strict_types=1);

/**
 * Unit test thực thi model/controller/view PHP bằng giả lập PDO (không MySQL).
 * KHÔNG xác nhận cú pháp SQL chạy trên server MySQL, hay Auth/Router của nhóm.
 */
require_once dirname(__DIR__) . '/app/Models/Dashboard.php';
require_once dirname(__DIR__) . '/app/Controllers/Admin/DashboardController.php';
require_once __DIR__ . '/TestReporter.php';

use App\Models\Dashboard;
use App\Controllers\Admin\DashboardController;

final class StubPDO extends PDO
{
    public array $data;
    public array $queryLog = [];
    public ?string $failTable = null;
    public bool $returnFalseOnExecute = false;
    public bool $returnFalseOnFetchCount = false;

    public function __construct()
    {
        $this->data = [
            'articles' => [
                ['id'=>1, 'title'=>'Bài 01', 'status'=>'published', 'created_at'=>'2026-11-01 00:00:00'],
                ['id'=>2, 'title'=>'Bài 02', 'status'=>'published', 'created_at'=>'2026-11-02 00:00:00'],
                ['id'=>3, 'title'=>'Bài nháp', 'status'=>'draft', 'created_at'=>'2026-11-03 00:00:00'],
                ['id'=>4, 'title'=>'Bài 04', 'status'=>'published', 'created_at'=>'2026-11-04 00:00:00'],
                ['id'=>5, 'title'=>'Bài 05', 'status'=>'published', 'created_at'=>'2026-11-05 00:00:00'],
                ['id'=>6, 'title'=>'<script>alert("x")</script>', 'status'=>'published', 'created_at'=>'2026-11-06 00:00:00'],
            ],
            'users' => [['id'=>1], ['id'=>2], ['id'=>3]],
            'categories' => [['id'=>1], ['id'=>2], ['id'=>3]],
            'comments' => [['id'=>1], ['id'=>2], ['id'=>3], ['id'=>4]],
        ];
    }

    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false
    {
        $this->queryLog[] = $query;
        if (preg_match('/^SELECT COUNT\(\*\) FROM `([a-z_]+)`$/', $query, $matches) !== 1) {
            throw new PDOException('Stub only accepts count queries here.');
        }
        $table = $matches[1];
        if ($table === $this->failTable) {
            throw new PDOException('Simulated unreadable table.');
        }
        return new StubPDOStatement($this, $query);
    }

    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        $this->queryLog[] = $query;
        return new StubPDOStatement($this, $query);
    }
}

final class StubPDOStatement extends PDOStatement
{
    private StubPDO $database;
    private string $sql;
    private array $parameters = [];

    public function __construct(StubPDO $database, string $sql)
    {
        $this->database = $database;
        $this->sql = $sql;
    }

    public function bindValue(string|int $param, mixed $value, int $type = PDO::PARAM_STR): bool
    {
        $this->parameters[$param] = $value;
        return true;
    }

    public function execute(?array $params = null): bool
    {
        if ($this->database->returnFalseOnExecute) {
            return false;
        }
        if ($params !== null) {
            $this->parameters = array_merge($this->parameters, $params);
        }
        return true;
    }

    public function fetchColumn(int $column = 0): mixed
    {
        if ($this->database->returnFalseOnFetchCount) {
            return false;
        }
        if (preg_match('/^SELECT COUNT\(\*\) FROM `([a-z_]+)`$/', $this->sql, $match) === 1) {
            return count($this->database->data[$match[1]]);
        }
        if (preg_match('/^SELECT COUNT\(\*\) FROM `articles` WHERE `status` = :published_value$/', $this->sql) === 1) {
            $counter = 0;
            foreach ($this->database->data['articles'] as $article) {
                if ($article['status'] === ($this->parameters[':published_value'] ?? null)) {
                    $counter++;
                }
            }
            return $counter;
        }
        throw new PDOException('SQL not recognized by stub: ' . $this->sql);
    }

    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array
    {
        if (!str_contains($this->sql, 'ORDER BY `created_at` DESC')
            || !str_contains($this->sql, 'LIMIT :row_limit')) {
            throw new PDOException('Latest-article query not recognized by stub.');
        }
        $articles = $this->database->data['articles'];
        usort($articles, static function (array $left, array $right): int {
            $dateComparison = strcmp($right['created_at'], $left['created_at']);
            return $dateComparison !== 0 ? $dateComparison : ($right['id'] <=> $left['id']);
        });
        $articles = array_slice($articles, 0, (int) ($this->parameters[':row_limit'] ?? 0));
        return array_map(static fn (array $article): array => [
            'article_title' => $article['title'],
            'article_sort_value' => $article['created_at'],
        ], $articles);
    }
}

$report = new TestReporter();
$config = require dirname(__DIR__) . '/config/dashboard.demo.php';
$pdo = new StubPDO();
$model = new Dashboard($pdo, $config);

function checkThrows(TestReporter $r, string $title, callable $action, string $exceptionType): void
{
    try {
        $action();
        $r->fail($title, 'Đáng ra phải phát sinh ngoại lệ.');
    } catch (Throwable $exception) {
        $r->check($title, $exception instanceof $exceptionType,
            'Ngoại lệ không đúng: ' . get_class($exception));
    }
}

$report->check('M01: Đếm bài', $model->countRows('articles') === 6, 'Sai số dòng');
$report->check('M02: Đếm user', $model->countRows('users') === 3, 'Sai số dòng');
$report->check('M03: Đếm danh mục', $model->countRows('categories') === 3, 'Sai số dòng');
$report->check('M04: Đếm bình luận', $model->countRows('comments') === 4, 'Sai số dòng');
$report->check('M05: Đếm Published', $model->countPublishedArticles('status', 'published') === 5, 'Sai Published');
$report->check('M06: Đếm Draft', $model->countPublishedArticles('status', 'draft') === 1, 'Sai Draft');
$latest = $model->getLatestArticles('title', 'created_at', 5);
$report->check('M07: Giới hạn 5', count($latest) === 5, 'Không đúng 5 bài');
$report->check('M08: Thứ tự mới nhất', $latest[0]['article_title'] === '<script>alert("x")</script>', 'Sai thứ tự');
$report->check('M09: Thứ tự cũ hơn', $latest[4]['article_title'] === 'Bài 02', 'Sai thứ tự');
$hasIdTieBreaker = false;
foreach ($pdo->queryLog as $recordedQuery) {
    if (str_contains($recordedQuery, '`id` DESC')) {
        $hasIdTieBreaker = true;
        break;
    }
}
$report->check('M10: Có phân xử khi trùng thời gian',
    $hasIdTieBreaker, 'Thiếu sort dự phòng');
$overview = $model->getOverview(5);
$report->check('M11: Tổng hợp 5 thẻ', $overview['metrics'] === [
    'articles'=>6, 'published_articles'=>5, 'users'=>3, 'categories'=>3, 'comments'=>4
], 'Sai số liệu thống kê');
$report->check('M12: Không cảnh báo khi đủ cấu hình', $overview['warnings'] === [], 'Có cảnh báo ngoài ý muốn');
checkThrows($report, 'M13: Chặn LIMIT=0', static fn () => $model->getOverview(0), InvalidArgumentException::class);
checkThrows($report, 'M14: Chặn LIMIT=21', static fn () => $model->getLatestArticles('title','created_at',21), InvalidArgumentException::class);
checkThrows($report, 'M15: Chặn tên bảng injection', static fn () => (new Dashboard($pdo, [
    'tables'=>['articles'=>'articles; DROP TABLE users']]))->countRows('articles'), InvalidArgumentException::class);
checkThrows($report, 'M16: Chặn tên cột injection', static fn () => $model->countPublishedArticles('status; DROP', 'published'), InvalidArgumentException::class);
$withoutPublished = $config;
$withoutPublished['article']['status_column'] = null;
$missing = (new Dashboard($pdo, $withoutPublished))->getOverview();
$report->check('M17: Thiếu Published trả null', $missing['metrics']['published_articles'] === null, 'Giả số liệu khi thiếu schema');
$report->check('M18: Thiếu Published có cảnh báo', count($missing['warnings']) >= 1, 'Không có cảnh báo');
$pdo->failTable = 'comments';
$broken = $model->getOverview();
$report->check('M19: MySQL hỏng bảng không ra số 0', $broken['metrics']['comments'] === null, 'Không chặn lỗi SQL');
$report->check('M20: MySQL hỏng bảng có cảnh báo', count($broken['warnings']) >= 1, 'Thiếu cảnh báo');
$pdo->failTable = null;
$denied = new DashboardController($model, static fn (): bool => false);
$before = count($pdo->queryLog);
checkThrows($report, 'C01: User không được xem Dashboard', static fn () => $denied->index(), RuntimeException::class);
$report->check('C02: Chặn quyền trước khi SELECT', count($pdo->queryLog) === $before, 'Đã SELECT trước Auth');
checkThrows($report, 'C03: Callback kiểu chuỗi không cho qua',
    static fn () => (new DashboardController($model, static fn (): string => '1'))->index(), RuntimeException::class);
$authorized = new DashboardController($model, static fn (): bool => true);
$html = $authorized->index();
$report->check('C04: Admin render view được', str_contains($html,'Dashboard') && str_contains($html,'Bài viết mới nhất'), 'Render sai');
$report->check('V01: Escape XSS trong title', str_contains($html,'&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;'), 'Chưa escape');
$report->check('V02: Không có XSS raw', !str_contains($html,'<script>alert("x")</script>'), 'Có XSS raw');
$report->check('V03: Metric lấy từ datasource thử', str_contains($html,'Tổng bài viết') && str_contains($html,'>6</p>'), 'Không hiển thị metrics');
$report->check('V04: Có nút reload', str_contains($html, 'data-dashboard-refresh'), 'Thiếu reload');


// Các trường hợp lỗi PDO silent-mode và dữ liệu rỗng:
$pdo->returnFalseOnFetchCount = true;
$badCount = $model->getOverview();
$report->check('M21: SELECT COUNT bị false => null', $badCount['metrics']['articles'] === null,
    'Trạng thái lỗi không được chuyển thành 0');
$report->check('M22: SELECT COUNT bị false => cảnh báo', count($badCount['warnings']) >= 1,
    'Thiếu cảnh báo lỗi đọc COUNT');
$pdo->returnFalseOnFetchCount = false;
$pdo->returnFalseOnExecute = true;
$badExecute = $model->getOverview();
$report->check('M23: Execute Published lỗi => null', $badExecute['metrics']['published_articles'] === null,
    'Đã hiện số giả khi execute() thất bại');
$report->check('M24: Execute latest lỗi => cảnh báo', count($badExecute['warnings']) >= 1
    && $badExecute['latest_articles'] === [], 'Thiếu cảnh báo danh sách hoặc vẫn hiện kết quả');
$pdo->returnFalseOnExecute = false;
$pdo->data['articles'] = [];
$empty = $model->getOverview();
$report->check('M25: Bảng articles rỗng => COUNT 0 là hợp lệ',
    $empty['metrics']['articles'] === 0 && $empty['metrics']['published_articles'] === 0,
    'Bảng rỗng phải trả 0');
$report->check('M26: Không bài thì danh sách rỗng',
    $empty['latest_articles'] === [], 'Danh sách không rỗng');
$report->check('M27: Không bài và có cấu hình thì không cảnh báo',
    $empty['warnings'] === [], 'Cảnh báo khi dữ liệu rỗng hợp lệ');
$pdo->data['articles'] = [['id'=>1, 'title'=>'<img src=x onerror=alert(1)>',
    'status'=>'published', 'created_at'=>'2026-11-08 12:00:00']];
$render = (new DashboardController($model, static fn (): bool => true))->index();
$report->check('V05: Escape payload img onerror',
    str_contains($render, '&lt;img src=x onerror=alert(1)&gt;')
        && !str_contains($render, '<img src=x onerror=alert(1)>'),
    'Payload HTML chưa được escape');

exit($report->summary());
