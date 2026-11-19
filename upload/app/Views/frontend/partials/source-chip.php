<?php
require_once __DIR__ . '/../../../Core/EditorialSource.php';

$sourceArticle = is_array($article ?? null) ? $article : (is_array($featuredArticle ?? null) ? $featuredArticle : []);
$sourceEscape = $escape ?? $e ?? static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$sourceInfo = EditorialSource::fromContent((string) ($sourceArticle['content'] ?? ''));
if ($sourceInfo !== null):
?>
<span class="story-source-chip">
    <span class="story-source-chip__mark" aria-hidden="true"><?= $sourceEscape($sourceInfo['mark']) ?></span>
    <a href="<?= $sourceEscape($sourceInfo['url']) ?>" target="_blank" rel="noopener noreferrer" aria-label="Đọc nguồn tham khảo tại <?= $sourceEscape($sourceInfo['publisher']) ?>">
        <?= $sourceEscape($sourceInfo['publisher']) ?>
    </a>
</span>
<?php endif; ?>
