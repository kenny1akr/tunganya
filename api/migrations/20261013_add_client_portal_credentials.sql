ALTER TABLE `partners`
  ADD COLUMN `portal_username` varchar(100) DEFAULT NULL AFTER `email`,
  ADD COLUMN `portal_password` varchar(255) DEFAULT NULL COMMENT 'Hash du mot de passe du portail client' AFTER `portal_username`,
  ADD UNIQUE KEY `uk_partners_portal_username` (`portal_username`);