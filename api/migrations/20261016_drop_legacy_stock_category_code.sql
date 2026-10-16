-- Idempotent compatibility migration for databases where 20261015 was already applied.
DELIMITER //
CREATE PROCEDURE `drop_legacy_cat_prod_code`()
BEGIN
  IF EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'cat_prod' AND column_name = 'code'
  ) THEN
    ALTER TABLE `cat_prod` DROP COLUMN `code`;
  END IF;
END//
CALL `drop_legacy_cat_prod_code`()//
DROP PROCEDURE `drop_legacy_cat_prod_code`//
DELIMITER ;
