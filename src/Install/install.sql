-- =====================================================
-- PrestaShop Quote Management Module - Installation
-- =====================================================
-- This SQL script creates all necessary tables for the
-- quote management system with rental capabilities.
-- =====================================================

-- Table: rental_configurations 
-- Stores pricing tiers and rates for rental durations
CREATE TABLE IF NOT EXISTS `PREFIX_jca_rental_configurations` (
  `id_rental_configuration` int(11) NOT NULL AUTO_INCREMENT,
  `price_min` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_max` decimal(10,2) NOT NULL DEFAULT 0.00,
  `duration_36_months` decimal(5,2) NOT NULL DEFAULT 0.00,
  `duration_60_months` decimal(5,2) NOT NULL DEFAULT 0.00,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY (`id_rental_configuration`),
  KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: quote_settings 
-- Global settings for quote management (validity, numbering, email notifications)
CREATE TABLE IF NOT EXISTS `PREFIX_jca_quote_settings` (
  `id_quote_settings` int(11) NOT NULL AUTO_INCREMENT,
  `validity_hours` int(11) NOT NULL DEFAULT 48,
  `quote_number_prefix` varchar(50) NOT NULL DEFAULT 'DEVIS',
  `quote_number_year_format` varchar(10) NOT NULL DEFAULT 'YYYY',
  `quote_number_separator` varchar(5) NOT NULL DEFAULT '-',
  `quote_number_padding` int(11) NOT NULL DEFAULT 3,
  `quote_number_counter` int(11) NOT NULL DEFAULT 0,
  `quote_number_reset_yearly` tinyint(1) NOT NULL DEFAULT 1,
  `quote_number_last_year` int(11) NOT NULL,
  `email_notifications_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `email_on_quote_created` tinyint(1) NOT NULL DEFAULT 0,
  `email_on_quote_validated` tinyint(1) NOT NULL DEFAULT 0,
  `email_on_quote_refused` tinyint(1) NOT NULL DEFAULT 0,
  `email_on_quote_expiring` tinyint(1) NOT NULL DEFAULT 0,
  `email_expiring_days_before` int(11) NOT NULL DEFAULT 3,
  `email_sender_name` varchar(255) NOT NULL DEFAULT 'Service Devis',
  `email_sender_email` varchar(255) NOT NULL DEFAULT '',
  `email_reply_to` varchar(255) NOT NULL DEFAULT '',
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY (`id_quote_settings`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO `PREFIX_jca_quote_settings` (
  `validity_hours`,
  `quote_number_prefix`,
  `quote_number_year_format`,
  `quote_number_separator`,
  `quote_number_padding`,
  `quote_number_counter`,
  `quote_number_reset_yearly`,
  `quote_number_last_year`,
  `date_add`,
  `date_upd`
) VALUES (
  48,
  'DEVIS',
  'YYYY',
  '-',
  3,
  0,
  1,
  YEAR(NOW()),
  NOW(),
  NOW()
);

-- Table: quotes
-- Main quotes table storing customer information and quote status
CREATE TABLE IF NOT EXISTS `PREFIX_jca_quotes` (
  `id_quote` int(11) NOT NULL AUTO_INCREMENT,
  `quote_number` varchar(100) NOT NULL,
  `quote_type` enum('standard','rental_only') NOT NULL DEFAULT 'standard',
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(50) DEFAULT NULL,
  `status` enum('draft','pending','validated','expired','refused') NOT NULL DEFAULT 'draft',
  `valid_until` datetime DEFAULT NULL,
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY (`id_quote`),
  UNIQUE KEY `quote_number` (`quote_number`),
  KEY `idx_customer_email` (`customer_email`),
  KEY `idx_status` (`status`),
  KEY `idx_quote_type` (`quote_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: quote_items
-- Line items for each quote with product and rental details
CREATE TABLE IF NOT EXISTS `PREFIX_jca_quote_items` (
  `id_quote_item` int(11) NOT NULL AUTO_INCREMENT,
  `id_quote` int(11) NOT NULL,
  `item_type` enum('product','delivery') NOT NULL DEFAULT 'product',
  `id_product` int(11) NOT NULL DEFAULT 0,
  `product_reference` varchar(100) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `id_rental_configuration` int(11) DEFAULT NULL,
  `is_rental` tinyint(1) NOT NULL DEFAULT 0,
  `duration_months` int(11) DEFAULT NULL,
  `rate_percentage` decimal(5,2) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `discount_percentage` decimal(5,2) DEFAULT NULL,
  `date_add` datetime NOT NULL,
  PRIMARY KEY (`id_quote_item`),
  KEY `idx_id_quote` (`id_quote`),
  KEY `idx_id_product` (`id_product`),
  KEY `idx_item_type` (`item_type`),
  CONSTRAINT `fk_quote_items_quote` FOREIGN KEY (`id_quote`) REFERENCES `PREFIX_jca_quotes` (`id_quote`) ON DELETE CASCADE,
  CONSTRAINT `fk_quote_items_rental_config` FOREIGN KEY (`id_rental_configuration`) REFERENCES `PREFIX_jca_rental_configurations` (`id_rental_configuration`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: product_rental_availability
-- Tracks which products are available for rental and their configurations
CREATE TABLE IF NOT EXISTS `PREFIX_jca_product_rental_availability` (
  `id_product_rental_availability` int(11) NOT NULL AUTO_INCREMENT,
  `id_product` int(11) NOT NULL,
  `product_reference` varchar(100) NOT NULL DEFAULT '',
  `product_name` varchar(255) NOT NULL,
  `product_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `rental_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `duration_36_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `duration_60_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `id_rental_configuration` int(11) DEFAULT NULL,
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY (`id_product_rental_availability`),
  UNIQUE KEY `id_product` (`id_product`),
  KEY `idx_rental_enabled` (`rental_enabled`),
  CONSTRAINT `fk_product_rental_config` FOREIGN KEY (`id_rental_configuration`) REFERENCES `PREFIX_jca_rental_configurations` (`id_rental_configuration`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: customers
-- Customer database for autocomplete and tracking
CREATE TABLE IF NOT EXISTS `PREFIX_jca_quote_customers` (
  `id_customer` int(11) NOT NULL AUTO_INCREMENT,
  `id_quote` int(11) NULL,
  `id_customer_prestashop` int(11) NULL,
  `email` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY (`id_customer`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_id_quote` (`id_quote`),
  CONSTRAINT `fk_quote_customer_quote` FOREIGN KEY (`id_quote`) REFERENCES `PREFIX_jca_quotes` (`id_quote`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
