-- =====================================================
-- Fix jca_quote_items table structure
-- =====================================================
-- This migration ensures the quote_items table has all
-- necessary columns for the current version
-- =====================================================

-- Add item_type column if it doesn't exist
SET @dbname = DATABASE();
SET @tablename = "ps_jca_quote_items";
SET @columnname = "item_type";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " ENUM('product','delivery') NOT NULL DEFAULT 'product' AFTER id_quote")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add index for item_type if it doesn't exist
SET @indexname = "idx_item_type";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (index_name = @indexname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD KEY ", @indexname, " (", @columnname, ")")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Make id_product nullable with default 0 (for delivery items)
ALTER TABLE ps_jca_quote_items MODIFY COLUMN id_product int(11) NOT NULL DEFAULT 0;
