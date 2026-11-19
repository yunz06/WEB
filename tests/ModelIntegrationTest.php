<?php

declare(strict_types=1);

if (!in_array('mysql', PDO::getAvailableDrivers(), true)) { echo "SKIP: pdo_mysql is required for model integration tests.\n"; exit(0); }

require_once __DIR__ . '/../app/Models/Article.php';
require_once __DIR__ . '/../app/Models/Category.php';
require_once __DIR__ . '/../app/Models/User.php';
require_once __DIR__ . '/../app/Models/Comment.php';

$pdo = Database::getInstance()->getConnection();
$articleModel = new Article($pdo);
$categoryModel = new Category($pdo);
$userModel = new User($pdo);
$commentModel = new Comment($pdo);
$assert = static function (bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
};

$pdo->beginTransaction();
try {
    $suffix = bin2hex(random_bytes(6));
    $roleId = $userModel->roleIdByName('user');
    $userId = $userModel->create('Integration User', 'integration-' . $suffix . '@example.test', password_hash('integration-password', PASSWORD_DEFAULT), (int)$roleId);
    $parentId = $categoryModel->create(['name' => 'Integration ' . $suffix, 'slug' => 'integration-' . $suffix]);
    $childId = $categoryModel->create(['parent_id' => $parentId, 'name' => 'Child ' . $suffix, 'slug' => 'child-' . $suffix]);
    $assert(($categoryModel->getBySlug('child-' . $suffix)['parent_id'] ?? null) == $parentId, 'Category parent relation failed.');

    $publishedId = $articleModel->create(['category_id' => $childId, 'user_id' => $userId, 'title' => 'Unique headline ' . $suffix,
        'slug' => 'unique-headline-' . $suffix, 'summary' => 'Summary', 'content' => '<p>Original sample body.</p>',
        'status' => 'published', 'is_featured' => 1]);
    $draftId = $articleModel->create(['category_id' => $childId, 'user_id' => $userId, 'title' => 'Draft ' . $suffix,
        'slug' => 'draft-' . $suffix, 'content' => '<p>Draft body.</p>', 'status' => 'draft']);
    $futureId = $articleModel->create(['category_id' => $childId, 'user_id' => $userId, 'title' => 'Future ' . $suffix,
        'slug' => 'future-' . $suffix, 'content' => '<p>Future body.</p>', 'status' => 'published',
        'published_at' => date('Y-m-d H:i:s', time() + 86400)]);

    $assert($articleModel->getPublishedById($publishedId) !== null, 'Published article should be readable.');
    $assert($articleModel->getPublishedById($draftId) === null, 'Draft article leaked to public query.');
    $assert($articleModel->getPublishedById($futureId) === null, 'Future article leaked to public query.');
    $assert(count($articleModel->getFeaturedPublished()) >= 1, 'Featured article query returned no sample.');
    $withoutFeatured = $articleModel->getLatestPublishedExcluding([$publishedId], 100, 0);
    $assert(!in_array($publishedId, array_map('intval', array_column($withoutFeatured, 'id')), true), 'Featured article was duplicated in latest stories.');
    $assert($articleModel->countSearchPublished('Unique headline ' . $suffix) === 1, 'Published search did not match title.');
    $assert($articleModel->countSearchPublished("' OR 1=1 --") === 0, 'Search input changed SQL behavior.');

    $categoryModel->update($parentId, ['name' => 'Integration ' . $suffix, 'slug' => 'integration-' . $suffix,
        'description' => null, 'parent_id' => null, 'status' => 'inactive']);
    $assert($categoryModel->getBySlug('child-' . $suffix) === null, 'Child category remained public under inactive parent.');
    $assert($categoryModel->countPublicArticles($childId) === 0, 'Public category count exposed child under inactive parent.');
    $assert($articleModel->getPublished(100, 0, $childId) === [], 'Home feed exposed an article under an inactive parent category.');
    $assert($articleModel->getPublishedById($publishedId) === null, 'Article remained public under inactive parent.');
    $assert($articleModel->countSearchPublished('Unique headline ' . $suffix) === 0, 'Search exposed article under inactive parent.');
    $categoryModel->update($parentId, ['name' => 'Integration ' . $suffix, 'slug' => 'integration-' . $suffix,
        'description' => null, 'parent_id' => null, 'status' => 'active']);
    $sections = $articleModel->getPublishedCategorySections([$parentId], 3);
    $sectionArticles = $sections[$parentId]['articles'] ?? [];
    $assert(in_array($publishedId, array_map('intval', array_column($sectionArticles, 'id')), true), 'Category section did not include its published child-category article.');
    $related = $articleModel->getRelatedPublished($publishedId, $childId, 3);
    $assert(!in_array($publishedId, array_map('intval', array_column($related, 'id')), true), 'Related-story query included the current article.');

    $commentId = $commentModel->create($publishedId, $userId, 'Pending sample comment');
    $assert($commentModel->getApprovedForArticle($publishedId) === [], 'Pending comment appeared publicly.');
    $commentModel->setStatus($commentId, 'approved');
    $assert(count($commentModel->getApprovedForArticle($publishedId)) === 1, 'Approved comment was not public.');

    $otherUserId = $userModel->create('Other Integration User', 'other-integration-' . $suffix . '@example.test',
        password_hash('other-integration-password', PASSWORD_DEFAULT), (int)$roleId);
    $secondCommentId = $commentModel->create($publishedId, $userId, 'Second private comment');
    $otherCommentId = $commentModel->create($publishedId, $otherUserId, 'Other reader comment');
    $commentModel->setStatus($secondCommentId, 'hidden');
    $ownComments = $commentModel->getForUser($userId, 10, 0);
    $assert($commentModel->countForUser($userId) === 2, 'Reader comment count included another account or missed an owned comment.');
    $assert(count($ownComments) === 2, 'Reader comment list did not return all owned moderation states.');
    $assert(count(array_filter($ownComments, static fn(array $comment): bool => (int)$comment['user_id'] === $otherUserId)) === 0,
        'Reader account exposed another user’s comment.');
    $assert(in_array($commentId, array_map('intval', array_column($ownComments, 'id')), true)
        && in_array($secondCommentId, array_map('intval', array_column($ownComments, 'id')), true),
        'Reader account omitted an owned comment status.');
    $firstCommentPage = $commentModel->getForUser($userId, 1, 0);
    $secondCommentPage = $commentModel->getForUser($userId, 1, 1);
    $assert(count($firstCommentPage) === 1 && count($secondCommentPage) === 1
        && (int)$firstCommentPage[0]['id'] !== (int)$secondCommentPage[0]['id'],
        'Reader comment pagination repeated or skipped an owned comment.');
    $assert($commentModel->countForUser($otherUserId) === 1
        && (int)$commentModel->getForUser($otherUserId, 10, 0)[0]['id'] === $otherCommentId,
        'Reader comment query did not isolate a second account.');

    $profile = $userModel->getProfileById($userId);
    $assert(is_array($profile) && (int)($profile['id'] ?? 0) === $userId
        && !array_key_exists('password_hash', $profile), 'Reader profile query exposed a password hash.');
    $adminEditRecord = $userModel->getById($userId);
    $assert(is_array($adminEditRecord) && !array_key_exists('password_hash', $adminEditRecord),
        'User edit query exposed a password hash to its view model.');

    $latestOneId = $articleModel->create(['category_id' => $childId, 'user_id' => $userId,
        'title' => 'Latest one ' . $suffix, 'slug' => 'latest-one-' . $suffix,
        'content' => '<p>Latest page one.</p>', 'status' => 'published', 'is_featured' => 0]);
    $latestTwoId = $articleModel->create(['category_id' => $childId, 'user_id' => $userId,
        'title' => 'Latest two ' . $suffix, 'slug' => 'latest-two-' . $suffix,
        'content' => '<p>Latest page two.</p>', 'status' => 'published', 'is_featured' => 0]);
    $featuredIds = array_map('intval', array_column($articleModel->getFeaturedPublished(100), 'id'));
    $latestCount = $articleModel->countLatestPublishedExcluding($featuredIds);
    $latestPageOne = $articleModel->getLatestPublishedExcluding($featuredIds, 1, 0);
    $latestPageTwo = $articleModel->getLatestPublishedExcluding($featuredIds, 1, 1);
    $latestPageIds = array_map('intval', array_merge(array_column($latestPageOne, 'id'), array_column($latestPageTwo, 'id')));
    $assert($latestCount >= 2 && count($latestPageOne) === 1 && count($latestPageTwo) === 1,
        'Home latest pagination count or page size is incorrect.');
    $assert(count(array_unique($latestPageIds)) === 2, 'Home latest pages repeated an article.');
    $assert(in_array($latestOneId, $latestPageIds, true) && in_array($latestTwoId, $latestPageIds, true),
        'Home latest query omitted newly published, non-featured articles.');

    $assert($articleModel->countAllAdmin(['query' => 'Unique headline ' . $suffix]) === 1, 'Admin search failed.');
    echo "PASS: transactional model integration checks.\n";
} finally {
    if ($pdo->inTransaction()) $pdo->rollBack();
}
