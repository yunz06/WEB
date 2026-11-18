-- Additive upgrade for the original NEWS HUB six-table schema.
-- MariaDB 10.4 supports IF NOT EXISTS for ADD COLUMN, ADD INDEX and ADD FOREIGN KEY.
-- Back up the database before applying this migration.
USE `news_hub`;

ALTER TABLE `categories`
    ADD COLUMN IF NOT EXISTS `parent_id` INT UNSIGNED NULL DEFAULT NULL AFTER `id`,
    ADD COLUMN IF NOT EXISTS `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER `description`,
    ADD COLUMN IF NOT EXISTS `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active' AFTER `sort_order`,
    ADD COLUMN IF NOT EXISTS `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `status`,
    ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;

ALTER TABLE `categories`
    ADD INDEX IF NOT EXISTS `idx_categories_parent_id` (`parent_id`),
    ADD INDEX IF NOT EXISTS `idx_categories_status_sort_order` (`status`, `sort_order`),
    ADD FOREIGN KEY IF NOT EXISTS `fk_categories_parent` (`parent_id`) REFERENCES `categories` (`id`)
        ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `articles`
    ADD COLUMN IF NOT EXISTS `is_featured` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`,
    ADD COLUMN IF NOT EXISTS `published_at` TIMESTAMP NULL DEFAULT NULL AFTER `is_featured`,
    ADD INDEX IF NOT EXISTS `idx_articles_featured_published_at` (`status`, `is_featured`, `published_at`);

-- Preflight: this constraint fails safely if legacy articles still have no author.
ALTER TABLE `articles`
    MODIFY COLUMN IF EXISTS `user_id` INT UNSIGNED NOT NULL;

ALTER TABLE `article_images`
    ADD COLUMN IF NOT EXISTS `caption` VARCHAR(255) NULL AFTER `image_path`,
    ADD COLUMN IF NOT EXISTS `alt_text` VARCHAR(255) NULL AFTER `caption`,
    ADD COLUMN IF NOT EXISTS `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER `alt_text`;

ALTER TABLE `comments`
    ADD INDEX IF NOT EXISTS `idx_comments_status_created_at` (`status`, `created_at`);
