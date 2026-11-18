<?php

declare(strict_types=1);

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
    $assert($articleModel->countSearchPublished('Unique headline ' . $suffix) === 1, 'Published search did not match title.');
    $assert($articleModel->countSearchPublished("' OR 1=1 --") === 0, 'Search input changed SQL behavior.');

    $categoryModel->update($parentId, ['name' => 'Integration ' . $suffix, 'slug' => 'integration-' . $suffix,
        'description' => null, 'parent_id' => null, 'status' => 'inactive']);
    $assert($categoryModel->getBySlug('child-' . $suffix) === null, 'Child category remained public under inactive parent.');
    $assert($categoryModel->countPublicArticles($childId) === 0, 'Public category count exposed child under inactive parent.');
    $assert($articleModel->getPublishedById($publishedId) === null, 'Article remained public under inactive parent.');
    $assert($articleModel->countSearchPublished('Unique headline ' . $suffix) === 0, 'Search exposed article under inactive parent.');
    $categoryModel->update($parentId, ['name' => 'Integration ' . $suffix, 'slug' => 'integration-' . $suffix,
        'description' => null, 'parent_id' => null, 'status' => 'active']);

    $commentId = $commentModel->create($publishedId, $userId, 'Pending sample comment');
    $assert($commentModel->getApprovedForArticle($publishedId) === [], 'Pending comment appeared publicly.');
    $commentModel->setStatus($commentId, 'approved');
    $assert(count($commentModel->getApprovedForArticle($publishedId)) === 1, 'Approved comment was not public.');

    $assert($articleModel->countAllAdmin(['query' => 'Unique headline ' . $suffix]) === 1, 'Admin search failed.');
    echo "PASS: transactional model integration checks.\n";
} finally {
    if ($pdo->inTransaction()) $pdo->rollBack();
}
