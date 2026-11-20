-- NEWS HUB: idempotent image-storage schema migration and reference audit.
-- Run after backing up an existing database with the main NEWS HUB tables.
-- Adds only missing image schema pieces; does NOT change article content or thumbnails.

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Ensure articles.thumbnail exists for the article's featured image filename.
SELECT COUNT(*) INTO @nh_thumbnail_column
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'articles'
  AND COLUMN_NAME = 'thumbnail';
SET @nh_sql = IF(
    @nh_thumbnail_column = 0,
    'ALTER TABLE `articles` ADD COLUMN `thumbnail` VARCHAR(255) NULL AFTER `content`',
    'SELECT 1'
);
PREPARE nh_image_schema_stmt FROM @nh_sql;
EXECUTE nh_image_schema_stmt;
DEALLOCATE PREPARE nh_image_schema_stmt;

-- Ensure the article gallery table exists. The thumbnail itself remains in articles.thumbnail.
CREATE TABLE IF NOT EXISTS `article_images` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `article_id` INT UNSIGNED NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `caption` VARCHAR(255) NULL,
    `alt_text` VARCHAR(255) NULL,
    `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_article_images_article_id` (`article_id`),
    KEY `idx_article_images_image_path` (`image_path`),
    CONSTRAINT `fk_article_images_article`
        FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`)
        ON DELETE CASCADE
        ON UPDATE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARACTER SET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- Add lookup indexes only when they are missing (compatible with shared-hosting MySQL).
SELECT COUNT(*) INTO @nh_articles_thumbnail_index
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'articles'
  AND INDEX_NAME = 'idx_articles_thumbnail';
SET @nh_sql = IF(
    @nh_articles_thumbnail_index = 0,
    'ALTER TABLE `articles` ADD INDEX `idx_articles_thumbnail` (`thumbnail`)',
    'SELECT 1'
);
PREPARE nh_image_index_stmt FROM @nh_sql;
EXECUTE nh_image_index_stmt;
DEALLOCATE PREPARE nh_image_index_stmt;

SELECT COUNT(*) INTO @nh_article_images_path_index
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'article_images'
  AND INDEX_NAME = 'idx_article_images_image_path';
SET @nh_sql = IF(
    @nh_article_images_path_index = 0,
    'ALTER TABLE `article_images` ADD INDEX `idx_article_images_image_path` (`image_path`)',
    'SELECT 1'
);
PREPARE nh_image_index_stmt FROM @nh_sql;
EXECUTE nh_image_index_stmt;
DEALLOCATE PREPARE nh_image_index_stmt;

-- Main thumbnail references. Database stores filenames, not image binary data.
SELECT
    a.id,
    a.title,
    a.status,
    a.thumbnail,
    CASE
        WHEN a.thumbnail IS NULL OR TRIM(a.thumbnail) = '' THEN 'NO_THUMBNAIL'
        WHEN a.thumbnail LIKE 'http://%' OR a.thumbnail LIKE 'https://%' THEN 'LEGACY_EXTERNAL_URL'
        WHEN LOCATE('/', a.thumbnail) > 0 OR LOCATE(CHAR(92), a.thumbnail) > 0 THEN 'LEGACY_PATH_VALUE'
        ELSE 'LOCAL_FILENAME_EXPECTED'
    END AS thumbnail_reference_status
FROM articles AS a
ORDER BY a.updated_at DESC, a.id DESC;

-- Gallery image references. MySQL cannot check whether the actual disk file exists.
SELECT
    ai.id,
    ai.article_id,
    a.title,
    ai.image_path,
    ai.caption,
    ai.alt_text,
    ai.sort_order
FROM article_images AS ai
JOIN articles AS a ON a.id = ai.article_id
ORDER BY ai.article_id, ai.sort_order, ai.id;

-- Confirm image columns and indexes.
SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND ((TABLE_NAME = 'articles' AND COLUMN_NAME = 'thumbnail')
    OR (TABLE_NAME = 'article_images' AND COLUMN_NAME = 'image_path'))
ORDER BY TABLE_NAME, ORDINAL_POSITION;

SELECT TABLE_NAME, INDEX_NAME, COLUMN_NAME
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
  AND ((TABLE_NAME = 'articles' AND INDEX_NAME = 'idx_articles_thumbnail')
    OR (TABLE_NAME = 'article_images' AND INDEX_NAME = 'idx_article_images_image_path'))
ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;
