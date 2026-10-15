ALTER TABLE `sales`
  ADD COLUMN `source_reference` varchar(100) DEFAULT NULL COMMENT 'Référence de la sortie client avant facturation' AFTER `origin`;