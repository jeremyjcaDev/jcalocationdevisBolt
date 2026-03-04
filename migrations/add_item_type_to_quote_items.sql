-- =====================================================
-- Add item_type column to jca_quote_items
-- =====================================================
-- This migration adds a new column to distinguish between
-- products and delivery/shipping items in quotes
-- =====================================================

-- Add item_type column to quote_items table
-- Values: 'product' (default) or 'delivery'
ALTER TABLE `PREFIX_jca_quote_items`
ADD COLUMN `item_type` ENUM('product', 'delivery') NOT NULL DEFAULT 'product'
AFTER `id_quote`;

-- Add index for better query performance
ALTER TABLE `PREFIX_jca_quote_items`
ADD KEY `idx_item_type` (`item_type`);

-- Update existing records to be 'product' type (safe default)
UPDATE `PREFIX_jca_quote_items`
SET `item_type` = 'product'
WHERE `item_type` IS NULL OR `item_type` = '';
