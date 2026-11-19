-- NEWS HUB schema for InfinityFree MySQL 8 / MariaDB 11.4
-- Import this file only after selecting the database created in the InfinityFree Control Panel.
-- It creates the six application tables and base roles; it does not create or select a database.

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(20) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_roles_name` (`name`)
) ENGINE=InnoDB
  DEFAULT CHARACTER SET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `role_id` INT UNSIGNED NOT NULL,
    `full_name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `status` ENUM('active', 'locked') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email` (`email`),
    KEY `idx_users_role_id` (`role_id`),
    CONSTRAINT `fk_users_role`
        FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARACTER SET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `parent_id` INT UNSIGNED NULL DEFAULT NULL,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_categories_name` (`name`),
    UNIQUE KEY `uq_categories_slug` (`slug`),
    KEY `idx_categories_parent_id` (`parent_id`),
    KEY `idx_categories_status_sort_order` (`status`, `sort_order`),
    CONSTRAINT `fk_categories_parent`
        FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARACTER SET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `articles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `category_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `summary` TEXT NULL,
    `content` LONGTEXT NOT NULL,
    `thumbnail` VARCHAR(255) NULL,
    `status` ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `published_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_articles_slug` (`slug`),
    KEY `idx_articles_user_id` (`user_id`),
    KEY `idx_articles_status_created_at` (`status`, `created_at`),
    KEY `idx_articles_category_status_created_at` (`category_id`, `status`, `created_at`),
    KEY `idx_articles_featured_published_at` (`status`, `is_featured`, `published_at`),
    CONSTRAINT `fk_articles_category`
        FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT,
    CONSTRAINT `fk_articles_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARACTER SET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

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
    CONSTRAINT `fk_article_images_article`
        FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`)
        ON DELETE CASCADE
        ON UPDATE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARACTER SET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `comments` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `article_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `content` TEXT NOT NULL,
    `status` ENUM('pending', 'approved', 'hidden') NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_comments_user_id` (`user_id`),
    KEY `idx_comments_article_status_created_at` (`article_id`, `status`, `created_at`),
    KEY `idx_comments_status_created_at` (`status`, `created_at`),
    CONSTRAINT `fk_comments_article`
        FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`)
        ON DELETE CASCADE
        ON UPDATE RESTRICT,
    CONSTRAINT `fk_comments_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARACTER SET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` (`name`)
VALUES ('user'), ('admin')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);
