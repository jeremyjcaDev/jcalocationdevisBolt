-- =====================================================
-- PrestaShop Quote Management Module - Uninstallation
-- =====================================================
-- This SQL script removes all tables created by the
-- quote management module in reverse order to respect
-- foreign key constraints.
-- =====================================================

-- Drop tables in reverse order to avoid foreign key constraint violations

-- Drop quote_items first (has foreign keys to quotes and rental_configurations)
DROP TABLE IF EXISTS `PREFIX_jca_quote_items`;

-- Drop quotes table
DROP TABLE IF EXISTS `PREFIX_jca_quotes`;

-- Drop product_rental_availability (has foreign key to rental_configurations)
DROP TABLE IF EXISTS `PREFIX_jca_product_rental_availability`;

-- Drop customers table
DROP TABLE IF EXISTS `PREFIX_jca_quote_customers`;

-- Drop quote_settings table
DROP TABLE IF EXISTS `PREFIX_jca_quote_settings`;

-- Drop rental_configurations table (referenced by other tables)
DROP TABLE IF EXISTS `PREFIX_jca_rental_configurations`;
