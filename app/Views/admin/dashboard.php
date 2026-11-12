<?php

declare(strict_types=1);

/** @var array $data - Được DashboardController truyền vào. */

$metrics = $data['metrics'] ?? [];
$latestArticles = $data['latest_articles'] ?? [];
$warnings = $data['warnings'] ?? [];
$sortColumn = $data['sort_column'] ?? null;

$metricTitles = [
    'articles' => 'Tổng bài viết',
    'published_articles' => 'Bài Published',
    'users' => 'Người dùng',
    'categories' => 'Danh mục',
    'comments' => 'Bình luận',
];

$escape = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
?>
<section class="dashboard-page" aria-labelledby="dashboard-title">
    <div class="dashboard-heading">
        <div>
            <p class="dashboard-eyebrow">Quản trị nội dung</p>
            <h1 id="dashboard-title">Dashboard</h1>
            <p class="dashboard-subtitle">Tổng hợp dữ liệu từ cơ sở dữ liệu MySQL của dự án.</p>
        </div>
        <button class="dashboard-refresh" type="button" data-dashboard-refresh>
            Tải lại số liệu
        </button>
    </div>

    <?php if (count($warnings) > 0): ?>
        <div class="dashboard-notice" role="status">
            <strong>Thông tin cần hoàn thiện</strong>
            <ul>
                <?php foreach ($warnings as $warning): ?>
                    <li><?= $escape((string) $warning) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="dashboard-metrics" aria-label="Thống kê hệ thống">
        <?php foreach ($metricTitles as $metricKey => $metricTitle): ?>
            <?php $value = $metrics[$metricKey] ?? null; ?>
            <article class="dashboard-metric">
                <p class="dashboard-metric-label"><?= $escape($metricTitle) ?></p>
                <?php if (is_int($value)): ?>
                    <p class="dashboard-metric-value"><?= number_format($value, 0, ',', '.') ?></p>
                <?php else: ?>
                    <p class="dashboard-metric-missing">Chưa xác minh</p>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>

    <section class="dashboard-panel" aria-labelledby="latest-title">
        <div class="dashboard-panel-header">
            <h2 id="latest-title">Bài viết mới nhất</h2>
            <?php if (is_string($sortColumn) && $sortColumn !== ''): ?>
                <span class="dashboard-sort-note">
                    Sắp xếp theo: <?= $escape($sortColumn) ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if (count($latestArticles) === 0): ?>
            <p class="dashboard-empty">
                Không có bài viết để hiển thị hoặc chưa cấu hình được truy vấn.
            </p>
        <?php else: ?>
            <div class="dashboard-table-wrapper">
                <table class="dashboard-table">
                    <thead>
                    <tr>
                        <th scope="col">STT</th>
                        <th scope="col">Tiêu đề bài viết</th>
                        <th scope="col">Giá trị cột sắp xếp</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($latestArticles as $index => $article): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= $escape((string) ($article['article_title'] ?? '')) ?></td>
                            <td><?= $escape((string) ($article['article_sort_value'] ?? '')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</section>
