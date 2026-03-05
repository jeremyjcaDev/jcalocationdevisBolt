-- =====================================================
-- Check and initialize quote settings
-- =====================================================

-- View current settings
SELECT * FROM ps_jca_quote_settings;

-- If no settings exist, insert defaults
INSERT INTO ps_jca_quote_settings (
    quote_number_prefix,
    validity_hours,
    terms_conditions,
    footer_text,
    date_add,
    date_upd
)
SELECT 'DEVIS', 720, '', '', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM ps_jca_quote_settings LIMIT 1);
