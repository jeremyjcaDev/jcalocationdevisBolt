-- =====================================================
-- Add quote_number_last_year field if missing
-- =====================================================
-- This migration adds the quote_number_last_year field
-- to track the last year for counter reset logic
-- =====================================================

-- Add the column if it doesn't exist
ALTER TABLE ps_jca_quote_settings
ADD COLUMN IF NOT EXISTS quote_number_last_year INT(11) NOT NULL DEFAULT 0
AFTER quote_number_reset_yearly;

-- Set the current year for existing records
UPDATE ps_jca_quote_settings
SET quote_number_last_year = YEAR(NOW())
WHERE quote_number_last_year = 0;
