CREATE TABLE IF NOT EXISTS `stock_movements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `movement_type` enum('entree','sortie','correction') NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `quantity_before` decimal(10,2) NOT NULL,
  `quantity_after` decimal(10,2) NOT NULL,
  `unit_cost` decimal(10,0) NOT NULL DEFAULT 0,
  `total_amount` decimal(15,0) NOT NULL DEFAULT 0,
  `reference` varchar(100) DEFAULT NULL,
  `reason` varchar(500) DEFAULT NULL,
  `operator` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_stock_movement_product` (`product_id`),
  KEY `idx_stock_movement_type_date` (`movement_type`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;