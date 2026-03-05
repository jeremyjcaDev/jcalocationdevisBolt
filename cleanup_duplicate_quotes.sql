-- =====================================================
-- Cleanup duplicate quote numbers
-- =====================================================
-- This script removes quotes with malformed quote numbers
-- containing scientific notation (e.g., DEVIS9.2233720368548E+18)
-- =====================================================

-- First, let's see what we have
-- SELECT quote_number, COUNT(*) as count
-- FROM ps_jca_quotes
-- WHERE quote_number LIKE '%E+%'
-- GROUP BY quote_number;

-- Delete quotes with scientific notation in the quote_number
DELETE FROM ps_jca_quotes
WHERE quote_number LIKE '%E+%'
   OR quote_number LIKE '%.%E%';

-- Verify the cleanup
SELECT COUNT(*) as remaining_quotes
FROM ps_jca_quotes;
