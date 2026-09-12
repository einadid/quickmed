-- ============================================================================
-- QuickMed — Complete Database Schema  (v2.0)
-- ============================================================================
-- HOW TO USE (InfinityFree / cPanel / phpMyAdmin):
--   1. Open phpMyAdmin, SELECT your database (e.g. if0_40419807_quickmed_db).
--      Do NOT "create" a new one on InfinityFree — use the one from Client Area.
--   2. Go to the "Import" tab, choose this file, click "Go".
--   3. After import, run the one-time admin seeder in your browser:
--         https://quickmed.free.nf/database/seed_admin.php
--      ...then DELETE seed_admin.php from the server.
--
-- LOCALHOST (XAMPP):
--   1. Create database `quickmed` (utf8mb4) in phpMyAdmin.
--   2. Import this file into it.
--   3. Run http://localhost/quickmed/database/seed_admin.php once, then delete it.
--
-- NOTE: Tables use indexes instead of foreign keys on purpose, because the
-- application deletes records directly (e.g. users) and FK constraints would
-- block those operations.
-- ============================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+06:00";

-- ============================================================================
-- 1. ROLES
-- ============================================================================
CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `display_name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_roles_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 2. USERS
-- ============================================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `shop_id` INT UNSIGNED NULL DEFAULT NULL,
  `username` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `member_id` VARCHAR(100) NULL DEFAULT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(30) NOT NULL DEFAULT '',
  `address` TEXT NULL DEFAULT NULL,
  `profile_image` VARCHAR(255) NULL DEFAULT NULL,
  `points` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `is_banned` TINYINT(1) NOT NULL DEFAULT 0,
  `last_login` DATETIME NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  UNIQUE KEY `uq_users_username` (`username`),
  UNIQUE KEY `uq_users_member` (`member_id`),
  KEY `ix_users_role` (`role_id`),
  KEY `ix_users_shop` (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. SHOPS (branches)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `shops` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `location` VARCHAR(255) NOT NULL DEFAULT '',
  `city` VARCHAR(100) NOT NULL DEFAULT '',
  `phone` VARCHAR(30) NOT NULL DEFAULT '',
  `email` VARCHAR(150) NULL DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_shops_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. MEDICINES (master catalogue)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `medicines` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(200) NOT NULL,
  `generic_name` VARCHAR(200) NULL DEFAULT NULL,
  `brand` VARCHAR(150) NULL DEFAULT NULL,
  `category` VARCHAR(100) NULL DEFAULT NULL,
  `power` VARCHAR(100) NULL DEFAULT NULL,
  `form` VARCHAR(100) NULL DEFAULT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `image` VARCHAR(255) NULL DEFAULT NULL,
  `manufacturer` VARCHAR(200) NULL DEFAULT NULL,
  `requires_prescription` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_med_category` (`category`),
  KEY `ix_med_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 5. SHOP_MEDICINES (per-shop price & stock)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `shop_medicines` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `shop_id` INT UNSIGNED NOT NULL,
  `medicine_id` INT UNSIGNED NOT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `purchase_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `stock_quantity` INT NOT NULL DEFAULT 0,
  `reorder_level` INT NOT NULL DEFAULT 10,
  `expiry_date` DATE NULL DEFAULT NULL,
  `last_restocked` DATETIME NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_shop_med` (`shop_id`, `medicine_id`),
  KEY `ix_sm_med` (`medicine_id`),
  KEY `ix_sm_stock` (`stock_quantity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 6. CART
-- ============================================================================
CREATE TABLE IF NOT EXISTS `cart` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `medicine_id` INT UNSIGNED NOT NULL,
  `shop_id` INT UNSIGNED NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cart` (`user_id`, `medicine_id`, `shop_id`),
  KEY `ix_cart_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 7. ORDERS
-- ============================================================================
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL DEFAULT NULL,
  `order_number` VARCHAR(50) NOT NULL,
  `customer_name` VARCHAR(150) NOT NULL,
  `customer_phone` VARCHAR(30) NULL DEFAULT NULL,
  `customer_address` TEXT NULL DEFAULT NULL,
  `delivery_type` VARCHAR(20) NOT NULL DEFAULT 'home',
  `delivery_charge` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `points_used` INT NOT NULL DEFAULT 0,
  `points_discount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `points_earned` INT NOT NULL DEFAULT 0,
  `payment_status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_orders_number` (`order_number`),
  KEY `ix_orders_user` (`user_id`),
  KEY `ix_orders_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 8. PARCELS (one parcel per shop inside an order)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `parcels` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT UNSIGNED NOT NULL,
  `shop_id` INT UNSIGNED NOT NULL,
  `parcel_number` VARCHAR(60) NOT NULL,
  `items_count` INT NOT NULL DEFAULT 0,
  `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status` VARCHAR(30) NOT NULL DEFAULT 'processing',
  `updated_by` INT UNSIGNED NULL DEFAULT NULL,
  `delivered_at` DATETIME NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_parcel_number` (`parcel_number`),
  KEY `ix_parcels_order` (`order_id`),
  KEY `ix_parcels_shop` (`shop_id`),
  KEY `ix_parcels_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 9. ORDER_ITEMS
-- ============================================================================
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT UNSIGNED NOT NULL,
  `parcel_id` INT UNSIGNED NULL DEFAULT NULL,
  `medicine_id` INT UNSIGNED NOT NULL,
  `shop_id` INT UNSIGNED NOT NULL,
  `medicine_name` VARCHAR(200) NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_oi_order` (`order_id`),
  KEY `ix_oi_parcel` (`parcel_id`),
  KEY `ix_oi_med` (`medicine_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 10. PARCEL_STATUS_LOGS
-- ============================================================================
CREATE TABLE IF NOT EXISTS `parcel_status_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `parcel_id` INT UNSIGNED NOT NULL,
  `status` VARCHAR(30) NOT NULL,
  `remarks` VARCHAR(255) NULL DEFAULT NULL,
  `updated_by` INT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_psl_parcel` (`parcel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 11. PRESCRIPTIONS
-- ============================================================================
CREATE TABLE IF NOT EXISTS `prescriptions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `customer_name` VARCHAR(150) NULL DEFAULT NULL,
  `customer_phone` VARCHAR(30) NULL DEFAULT NULL,
  `customer_address` TEXT NULL DEFAULT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `notes` TEXT NULL DEFAULT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `order_id` INT UNSIGNED NULL DEFAULT NULL,
  `reviewed_by` INT UNSIGNED NULL DEFAULT NULL,
  `review_notes` TEXT NULL DEFAULT NULL,
  `reviewed_at` DATETIME NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_rx_user` (`user_id`),
  KEY `ix_rx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 12. REVIEWS
-- ============================================================================
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `order_id` INT UNSIGNED NULL DEFAULT NULL,
  `rating` TINYINT UNSIGNED NOT NULL DEFAULT 5,
  `review_text` TEXT NULL DEFAULT NULL,
  `is_approved` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_rev_user` (`user_id`),
  KEY `ix_rev_approved` (`is_approved`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 13. NEWS
-- ============================================================================
CREATE TABLE IF NOT EXISTS `news` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `image` VARCHAR(255) NULL DEFAULT NULL,
  `source_link` VARCHAR(500) NULL DEFAULT NULL,
  `author_id` INT UNSIGNED NULL DEFAULT NULL,
  `is_published` TINYINT(1) NOT NULL DEFAULT 0,
  `published_at` DATETIME NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_news_pub` (`is_published`, `published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 14. HEALTH_POSTS (blog)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `health_posts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) NOT NULL DEFAULT 'General',
  `content` LONGTEXT NOT NULL,
  `image` VARCHAR(255) NULL DEFAULT NULL,
  `author_id` INT UNSIGNED NULL DEFAULT NULL,
  `views` INT UNSIGNED NOT NULL DEFAULT 0,
  `is_published` TINYINT(1) NOT NULL DEFAULT 0,
  `published_at` DATETIME NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_hp_pub` (`is_published`, `created_at`),
  KEY `ix_hp_cat` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 15. CONTACT_MESSAGES
-- ============================================================================
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL DEFAULT '',
  `message` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 16. SIGNUP_CODES (staff invitation codes)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `signup_codes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(50) NOT NULL,
  `role_id` INT UNSIGNED NOT NULL,
  `shop_id` INT UNSIGNED NULL DEFAULT NULL,
  `is_used` TINYINT(1) NOT NULL DEFAULT 0,
  `used_by` INT UNSIGNED NULL DEFAULT NULL,
  `expires_at` DATETIME NOT NULL,
  `used_at` DATETIME NULL DEFAULT NULL,
  `created_by` INT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_signup_code` (`code`),
  KEY `ix_sc_role` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 17. POINTS_LOG
-- ============================================================================
CREATE TABLE IF NOT EXISTS `points_log` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `order_id` INT UNSIGNED NULL DEFAULT NULL,
  `points` INT NOT NULL DEFAULT 0,
  `type` VARCHAR(30) NOT NULL DEFAULT 'order',
  `description` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_pl_user` (`user_id`),
  KEY `ix_pl_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 18. SESSIONS ("remember me" tokens)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `token` VARCHAR(128) NOT NULL,
  `user_agent` VARCHAR(255) NULL DEFAULT NULL,
  `ip_address` VARCHAR(60) NULL DEFAULT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_session_token` (`token`),
  KEY `ix_sess_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 19. AUDIT_LOGS
-- ============================================================================
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `table_name` VARCHAR(100) NULL DEFAULT NULL,
  `record_id` INT UNSIGNED NULL DEFAULT NULL,
  `old_values` TEXT NULL DEFAULT NULL,
  `new_values` TEXT NULL DEFAULT NULL,
  `ip_address` VARCHAR(60) NULL DEFAULT NULL,
  `user_agent` TEXT NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_audit_user` (`user_id`),
  KEY `ix_audit_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 20. FLASH_SALES
-- ============================================================================
CREATE TABLE IF NOT EXISTS `flash_sales` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `medicine_id` INT UNSIGNED NOT NULL,
  `shop_id` INT UNSIGNED NOT NULL,
  `discount_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `original_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `sale_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `stock_limit` INT NOT NULL DEFAULT 0,
  `sold_count` INT NOT NULL DEFAULT 0,
  `starts_at` DATETIME NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_fs_active` (`is_active`, `expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SEED DATA
-- ============================================================================

-- Roles (IDs matter: 1 = customer, used across the codebase)
INSERT INTO `roles` (`id`, `name`, `display_name`) VALUES
  (1, 'customer', 'Customer'),
  (2, 'admin', 'Administrator'),
  (3, 'shop_manager', 'Shop Manager'),
  (4, 'doctor', 'Doctor'),
  (5, 'salesman', 'Salesman')
ON DUPLICATE KEY UPDATE `display_name` = VALUES(`display_name`);

-- Demo branch (IDs referenced by seed instructions)
INSERT INTO `shops` (`id`, `name`, `location`, `city`, `phone`, `email`, `is_active`) VALUES
  (1, 'QuickMed Main Branch', 'GEC Circle, CDA Avenue', 'Chattogram', '09678-100100', 'support@quickmed.com', 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- NOTE: The administrator account is created by database/seed_admin.php
-- (run once in the browser, then delete the file). This keeps password
-- hashing safe instead of shipping a pre-made hash.
