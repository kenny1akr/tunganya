ALTER TABLE `cat_prod`
  ADD COLUMN `icon` varchar(20) DEFAULT NULL AFTER `default_unit_id`;

-- Les anciennes catégories gardent NULL et utilisent l’icône « tag » par défaut.
-- Conserver NULL ici permet aux installations ayant des catégories personnalisées
-- de bénéficier du rendu par défaut sans modifier leurs données.
