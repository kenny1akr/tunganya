-- Preserve the existing category text while adding a stable FK to cat_prod.
-- Category links are backfilled only when the normalized category name is unique.
ALTER TABLE `stock`
  ADD COLUMN `category_id` int(11) DEFAULT NULL AFTER `category`,
  ADD COLUMN `is_archived` tinyint(1) NOT NULL DEFAULT 0 AFTER `status`,
  ADD COLUMN `archived_at` datetime DEFAULT NULL AFTER `is_archived`,
  ADD COLUMN `archived_by` varchar(100) DEFAULT NULL AFTER `archived_at`,
  ADD COLUMN `archive_reason` varchar(255) DEFAULT NULL AFTER `archived_by`,
  ADD KEY `idx_stock_category_id` (`category_id`),
  ADD KEY `idx_stock_archived` (`is_archived`),
  ADD CONSTRAINT `fk_stock_category` FOREIGN KEY (`category_id`) REFERENCES `cat_prod` (`id`) ON DELETE SET NULL;

UPDATE `stock` s
JOIN (
  SELECT
    LOWER(TRIM(REPLACE(REPLACE(REPLACE(REPLACE(`category`, '  ', ' '), '  ', ' '), '  ', ' '), '  ', ' '))) AS normalized_name,
    MIN(`id`) AS category_id
  FROM `cat_prod`
  GROUP BY normalized_name
  HAVING COUNT(*) = 1
) c ON c.normalized_name = LOWER(TRIM(REPLACE(REPLACE(REPLACE(REPLACE(s.`category`, '  ', ' '), '  ', ' '), '  ', ' '), '  ', ' ')))
SET s.`category_id` = c.`category_id`;

-- `seuil` remains the canonical alert threshold used by the application.
-- Legacy min_stock_alert and reorder_level columns remain untouched for compatibility.
