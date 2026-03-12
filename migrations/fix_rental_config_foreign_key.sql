/*
  Fix foreign key constraint for id_rental_configuration

  This migration ensures that:
  1. The id_rental_configuration column accepts NULL values
  2. The foreign key constraint allows NULL values

  This is necessary because standard quotes (non-rental) don't have
  a rental configuration and should be able to insert NULL.
*/

-- Drop the existing foreign key constraint
ALTER TABLE `ps_jca_quote_items`
DROP FOREIGN KEY IF EXISTS `fk_quote_items_rental_config`;

-- Ensure the column is nullable
ALTER TABLE `ps_jca_quote_items`
MODIFY COLUMN `id_rental_configuration` int(11) NULL DEFAULT NULL;

-- Re-add the foreign key constraint (it will now properly allow NULL)
ALTER TABLE `ps_jca_quote_items`
ADD CONSTRAINT `fk_quote_items_rental_config`
FOREIGN KEY (`id_rental_configuration`)
REFERENCES `ps_jca_rental_configurations` (`id_rental_configuration`)
ON DELETE SET NULL;
