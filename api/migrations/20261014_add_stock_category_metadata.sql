ALTER TABLE `cat_prod`
  ADD COLUMN `code` varchar(50) DEFAULT NULL AFTER `category`,
  ADD COLUMN `description` text DEFAULT NULL AFTER `code`,
  ADD COLUMN `family` varchar(100) DEFAULT NULL AFTER `description`,
  ADD COLUMN `default_unit_id` int(11) DEFAULT NULL AFTER `family`;

UPDATE `cat_prod`
SET `code` = CONCAT('LEGACY-', LPAD(`id`, 6, '0'))
WHERE `code` IS NULL OR TRIM(`code`) = '';

ALTER TABLE `cat_prod`
  MODIFY COLUMN `code` varchar(50) NOT NULL,
  ADD UNIQUE KEY `uk_cat_prod_code` (`code`),
  ADD KEY `idx_cat_prod_default_unit` (`default_unit_id`),
  ADD CONSTRAINT `fk_cat_prod_default_unit`
    FOREIGN KEY (`default_unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL;
