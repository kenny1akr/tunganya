-- Script réexécutable pour tunganya. Exécuter après 20261017_add_stock_category_icon.sql.
-- La comparaison ignore la casse, les espaces de début/fin et les espaces répétés.
DROP TEMPORARY TABLE IF EXISTS `_seed_stock_categories`;
CREATE TEMPORARY TABLE `_seed_stock_categories` (
  `category` varchar(100) NOT NULL,
  `icon` varchar(20) NOT NULL
) ENGINE=Memory;

INSERT INTO `_seed_stock_categories` (`category`, `icon`) VALUES
('Foam Board','image'),('Carton 2 mm','box'),('Roll-up Standard','roll'),('X-Stand','image'),('Big Stand','image'),('Tarpaulin / Bâche','roll'),('L-Shape','image'),('Oval','image'),('Roll-up Material','roll'),('Sticker','image'),('Banner','image'),('Flag Material','image'),('One Way Vision','image'),('Backlight','image'),('CMYK','ink'),('Large Format','printer'),('DTF','printer'),('UV','ink'),('Powder (Secabot)','ink'),('XP 600 Heads','printer'),('Cup Head XP 600','tool'),('Cup Head DX5','tool'),('Cable Head XP 600','tool'),('Cable Head DX5','tool'),('Wliper for 1,8','tool'),('Wliper for 3,3','tool'),('Duper DX5','tool'),('Duper XP600','tool'),('Duper DTF','tool'),('DHL','truck');

INSERT INTO `cat_prod` (`category`, `description`, `family`, `default_unit_id`, `icon`, `adder`, `cat_date`, `status`)
SELECT s.`category`, NULL, NULL, NULL, s.`icon`, 'system', CURDATE(), 1
FROM `_seed_stock_categories` s
WHERE NOT EXISTS (
  SELECT 1 FROM `cat_prod` c
  WHERE LOWER(TRIM(REPLACE(REPLACE(REPLACE(REPLACE(c.`category`, '  ', ' '), '  ', ' '), '  ', ' '), '  ', ' ')))
      = LOWER(TRIM(REPLACE(REPLACE(REPLACE(REPLACE(s.`category`, '  ', ' '), '  ', ' '), '  ', ' '), '  ', ' ')))
);

-- Enrichir les correspondances déjà présentes, sans remplacer une icône choisie.
UPDATE `cat_prod` c
JOIN `_seed_stock_categories` s
  ON LOWER(TRIM(REPLACE(REPLACE(REPLACE(REPLACE(c.`category`, '  ', ' '), '  ', ' '), '  ', ' '), '  ', ' ')))
   = LOWER(TRIM(REPLACE(REPLACE(REPLACE(REPLACE(s.`category`, '  ', ' '), '  ', ' '), '  ', ' '), '  ', ' ')))
SET c.`icon` = s.`icon`
WHERE c.`icon` IS NULL OR TRIM(c.`icon`) = '';

DROP TEMPORARY TABLE `_seed_stock_categories`;
