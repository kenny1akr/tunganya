ALTER TABLE `entries`
  ADD COLUMN `entry_number` varchar(50) DEFAULT NULL AFTER `id`,
  ADD COLUMN `product_id` int(11) DEFAULT NULL AFTER `entry_number`,
  ADD COLUMN `supplier_id` int(100) DEFAULT NULL AFTER `product_id`,
  ADD COLUMN `entry_type` varchar(50) DEFAULT NULL AFTER `supplier_id`,
  ADD COLUMN `received_by` varchar(100) DEFAULT NULL AFTER `operator`,
  ADD KEY `idx_entries_entry_number` (`entry_number`),
  ADD KEY `idx_entries_product_id` (`product_id`),
  ADD KEY `idx_entries_supplier_id` (`supplier_id`),
  ADD CONSTRAINT `fk_entries_product` FOREIGN KEY (`product_id`) REFERENCES `stock` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_entries_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `partners` (`id`) ON DELETE SET NULL;
