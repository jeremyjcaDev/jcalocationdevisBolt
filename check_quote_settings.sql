-- =====================================================
-- Check and initialize quote settings
-- =====================================================

-- View current settings
SELECT * FROM ps_jca_quote_settings;

-- If no settings exist, insert defaults
INSERT INTO ps_jca_quote_settings (
    validity_hours,
    quote_number_prefix,
    quote_number_year_format,
    quote_number_separator,
    quote_number_padding,
    quote_number_counter,
    quote_number_reset_yearly,
    quote_number_last_year,
    date_add,
    date_upd
)
SELECT 48, 'DEVIS', 'YYYY', '-', 3, 0, 1, YEAR(NOW()), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM ps_jca_quote_settings LIMIT 1);

-- Ensure quote_number_last_year is set for existing records
UPDATE ps_jca_quote_settings
SET quote_number_last_year = YEAR(NOW())
WHERE quote_number_last_year IS NULL OR quote_number_last_year = 0;
