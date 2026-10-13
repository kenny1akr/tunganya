-- =====================================================================
-- BASE DE DONNÉES : next_db
-- Projet : DH TELECOM AND ADVERTISING HOUSE
-- Architecture : Microservices (10 MS, 13 modules)
-- Version : 2.0 - Adaptée au Plan de Développement du 10/10/2026
-- =====================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `next_db` 
  DEFAULT CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;
USE `tunganya`;

-- =====================================================================
-- MODULE #1 : MS-01 AUTHENTIFICATION & UTILISATEURS
-- =====================================================================

-- Catégories de rôles (Super Admin, Admin, Caissier, Agent, Utilisateur)
CREATE TABLE `cat_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `level_access` int(11) NOT NULL DEFAULT 1 COMMENT '1=Utilisateur, 5=Super Admin',
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `cat_users` (`id`, `category`, `description`, `level_access`, `status`) VALUES 
(1, 'super_admin',       'Contrôle total du système',                        6, 1),
(2, 'directeur_general', 'Vue globale et validation des opérations',         5, 1),
(3, 'comptable',         'Comptabilité, banque et rapports financiers',      4, 1),
(4, 'receptionniste',    'Accueil clients, commandes et facturation',        3, 1),
(5, 'magasinier',        'Gestion du stock et des livraisons',               2, 1),
(6, 'travailleur',       'Accès aux opérations courantes',                   1, 1);

-- Départements (Vente, Production, Administration, etc.)
CREATE TABLE `cat_departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `abbreviation` varchar(100) NOT NULL,
  `responsible` varchar(100) NOT NULL,
  `profil` varchar(100) DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `cat_departments` (`id`, `name`, `abbreviation`, `responsible`, `profil`, `status`) VALUES
(15, 'Vente', 'VNT', 'Alexis', '', 1);

-- Utilisateurs
CREATE TABLE `users` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `profil` varchar(255) NOT NULL DEFAULT '',
  `user_name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'bcrypt hash',
  `secret_code` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL COMMENT 'Réf cat_users.category',
  `f_name` varchar(100) DEFAULT NULL,
  `l_name` varchar(100) DEFAULT NULL,
  `telephone` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `adress` varchar(100) NOT NULL DEFAULT '',
  `level` varchar(100) NOT NULL DEFAULT '',
  `salary` decimal(10,0) NOT NULL DEFAULT 0,
  `status` varchar(100) NOT NULL DEFAULT 'Disponible',
  `session_id` varchar(1000) NOT NULL DEFAULT '',
  `admission` date NOT NULL DEFAULT (CURRENT_DATE),
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT CURRENT_TIMESTAMP,
  `jwt_secret` varchar(255) DEFAULT NULL COMMENT 'Secret personnel pour JWT',
  `last_login` datetime DEFAULT NULL,
  `failed_attempts` int(11) DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_name` (`user_name`),
  UNIQUE KEY `uk_email` (`email`),
  KEY `idx_category` (`category`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reset password tokens (MS-01 Tâche 1.3a)
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(4) DEFAULT 0,
  `ip_request` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_token` (`token`),
  CONSTRAINT `fk_reset_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Journal des connexions (MS-01 - logintos)
CREATE TABLE `logintos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(100) NOT NULL,
  `action_type` varchar(100) NOT NULL COMMENT 'Connexion, Déconnexion, Échec',
  `session_id` varchar(255) DEFAULT NULL,
  `ip_adress` varchar(255) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `descrip` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_date` (`user_id`, `created_at`),
  KEY `idx_action` (`action_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Paramètres sonores par utilisateur (MS-08 Tâche 3.3)
CREATE TABLE `user_sound_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `sound_status` tinyint(1) DEFAULT 1,
  `notification_sound` varchar(100) DEFAULT 'default',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- MODULE #2 : MS-02 PARTENAIRES (Clients/Fournisseurs)
-- =====================================================================

CREATE TABLE `cat_partners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(100) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `cat_partners` (`id`, `category`, `status`) VALUES
(1, 'Client', 1),
(2, 'Fournisseur', 1);

-- Partenaires avec système VIP (Module #11) et anniversaires (Module #12)
CREATE TABLE `partners` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `abbr` varchar(25) DEFAULT NULL,
  `category` enum('client','fournisseur') DEFAULT 'client',
  `address` varchar(1000) NOT NULL DEFAULT '',
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL DEFAULT '',
  `portal_username` varchar(100) DEFAULT NULL,
  `portal_password` varchar(255) DEFAULT NULL COMMENT 'Hash du mot de passe du portail client',
  `RC` varchar(100) NOT NULL DEFAULT '',
  `NIF` varchar(255) NOT NULL DEFAULT '',
  `profil` varchar(100) DEFAULT NULL,
  -- Module #11 : Système VIP
  `debt_limit` bigint(10) NOT NULL DEFAULT 0,
  `debt` bigint(10) NOT NULL DEFAULT 0,
  `status_client` enum('Nouveau','Régulier','VIP') DEFAULT 'Nouveau',
  `total_achats` bigint(15) DEFAULT 0,
  `frequence_achats` int(10) DEFAULT 0,
  `dernier_achat` date DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  -- Module #12 : Anniversaires
  `annif` varchar(10) DEFAULT NULL COMMENT 'Format JJ-MM',
  `birthday_promo_used` date DEFAULT NULL,
  -- Alertes
  `alert_debt` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_partners_portal_username` (`portal_username`),
  KEY `idx_name` (`name`),
  KEY `idx_category` (`category`),
  KEY `idx_status_client` (`status_client`),
  KEY `idx_annif` (`annif`),
  KEY `idx_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- MODULE #3 : MS-03 PRODUITS / STOCKS
-- =====================================================================

CREATE TABLE `cat_prod` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(100) NOT NULL,
  `adder` varchar(100) NOT NULL,
  `cat_date` date NOT NULL DEFAULT (CURRENT_DATE),
  `status` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `cat_prod` (`id`, `category`, `adder`, `status`) VALUES
(135, 'Service', 'Isabelle', 1),
(136, 'Produit', 'Isabelle', 1);

CREATE TABLE `sell_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(100) NOT NULL,
  `adder` varchar(100) NOT NULL,
  `cat_date` date NOT NULL DEFAULT (CURRENT_DATE),
  `status` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `sell_type` (`id`, `category`, `adder`, `status`) VALUES
(34, 'm', 'Isabelle', 1),
(35, 'piece', 'Isabelle', 1),
(36, 'rouleau', 'Isabelle', 1),
(37, 'Forfait', 'Isabelle', 1);

-- Stock principal
CREATE TABLE `stock` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `bar_code` int(11) NOT NULL DEFAULT 0,
  `medicine_name` varchar(100) NOT NULL,
  `color` varchar(10) NOT NULL DEFAULT '#000000',
  `category` varchar(255) NOT NULL,
  `shelf` text DEFAULT NULL,
  `registered_quantity` decimal(10,2) NOT NULL DEFAULT 0,
  `used_quantity` decimal(10,2) NOT NULL DEFAULT 0,
  `actual_quantity` decimal(10,2) NOT NULL DEFAULT 0,
  `register_date` varchar(100) NOT NULL,
  `expire_date` varchar(100) DEFAULT NULL,
  `sell_type` varchar(100) NOT NULL,
  `origin` varchar(50) NOT NULL DEFAULT '',
  `quality` varchar(50) NOT NULL DEFAULT 'Standard',
  `actual_price` decimal(10,0) NOT NULL DEFAULT 0,
  `selling_price` decimal(10,0) NOT NULL DEFAULT 0,
  `profit_price` decimal(10,0) NOT NULL DEFAULT 0,
  `status` varchar(100) NOT NULL DEFAULT 'Disponible',
  `inventory` decimal(10,2) NOT NULL DEFAULT 0,
  `storage_conditions` varchar(255) NOT NULL DEFAULT '',
  `profil` varchar(100) NOT NULL DEFAULT '',
  `guard` decimal(10,0) NOT NULL DEFAULT 0,
  `requisition` decimal(10,2) DEFAULT 0,
  `seuil` decimal(10,0) NOT NULL DEFAULT 3 COMMENT 'Seuil alerte stock',
  -- Colonne calculée
  `totalPrice` decimal(15,0) GENERATED ALWAYS AS (`actual_quantity` * `actual_price`) STORED,
  -- Alertes automatiques (MS-10)
  `low_stock_alert` tinyint(1) DEFAULT 0,
  `expiring_alert` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`medicine_name`),
  KEY `idx_category` (`category`),
  KEY `idx_status` (`status`),
  KEY `idx_expire` (`expire_date`),
  KEY `idx_seuil` (`actual_quantity`, `seuil`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Entrées de stock
CREATE TABLE `entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator` varchar(25) NOT NULL,
  `invoice_in` varchar(100) DEFAULT NULL,
  `bar_code` int(11) NOT NULL DEFAULT 0,
  `medicine_name` varchar(100) NOT NULL,
  `category` varchar(255) NOT NULL,
  `quantity` decimal(10,1) NOT NULL,
  `register_date` date NOT NULL DEFAULT (CURRENT_DATE),
  `expire_date` date DEFAULT NULL,
  `sell_type` varchar(100) NOT NULL,
  `actual_price` decimal(10,0) NOT NULL DEFAULT 0,
  `total_amount` decimal(10,0) NOT NULL DEFAULT 0,
  `status` varchar(100) NOT NULL DEFAULT 'Disponible',
  PRIMARY KEY (`id`),
  KEY `idx_product` (`medicine_name`),
  KEY `idx_date` (`register_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Corrections de quantité
CREATE TABLE `quantity_corrections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` varchar(255) NOT NULL,
  `medicine_name` varchar(255) NOT NULL,
  `old_entry_quantity` decimal(10,2) NOT NULL,
  `new_entry_quantity` decimal(10,2) NOT NULL,
  `old_stock_quantity` decimal(10,2) NOT NULL,
  `new_stock_quantity` decimal(10,2) NOT NULL,
  `difference` decimal(10,2) NOT NULL,
  `reason` text NOT NULL,
  `invoice_ref` varchar(255) DEFAULT NULL,
  `corrected_by` varchar(255) NOT NULL,
  `correction_date` varchar(20) NOT NULL,
  `correction_time` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_corrected_by` (`corrected_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `stock_movements` (
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

-- =====================================================================
-- MODULE #4 : MS-04 VENTES / FACTURES / DEVIS / COMMANDES
-- =====================================================================

-- Ventes et factures
CREATE TABLE `sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(100) NOT NULL,
  `operator` varchar(255) NOT NULL,
  `agent` varchar(255) DEFAULT NULL,
  `client_name` varchar(255) NOT NULL,
  `client_id` int(100) DEFAULT NULL COMMENT 'Réf partners.id',
  `phone` varchar(15) DEFAULT '',
  `medicines` text NOT NULL,
  `quantity` text NOT NULL,
  `unit_cost` text DEFAULT NULL,
  `actual_price` text DEFAULT NULL,
  `tot_htva` bigint(20) NOT NULL DEFAULT 0,
  `tva` bigint(20) NOT NULL DEFAULT 0,
  `tva_paid` bigint(20) NOT NULL DEFAULT 0,
  `tot_tvac` bigint(20) NOT NULL DEFAULT 0,
  `debt` bigint(11) NOT NULL DEFAULT 0,
  `total_amount` bigint(11) NOT NULL DEFAULT 0,
  `total_profit` bigint(11) NOT NULL DEFAULT 0,
  `discount` bigint(11) NOT NULL DEFAULT 0,
  `commission` bigint(11) NOT NULL DEFAULT 0,
  `date` date NOT NULL DEFAULT (CURRENT_DATE),
  `status` enum('Active','Supprimée') DEFAULT 'Active',
  `origin` varchar(50) NOT NULL DEFAULT 'Interne',
  `guard` varchar(500) NOT NULL DEFAULT '',
  `netcash` decimal(10,0) NOT NULL DEFAULT 0,
  `payment_method` varchar(200) NOT NULL DEFAULT 'Espèces',
  `ext_paid_amount` decimal(10,0) DEFAULT NULL,
  `ext_paid_status` varchar(50) DEFAULT NULL,
  `cashier` varchar(255) DEFAULT NULL,
  `document_type` enum('Facture','Proforma','Devis','Commande','Livraison') DEFAULT 'Facture',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_invoice` (`invoice_number`),
  KEY `idx_date` (`date`),
  KEY `idx_client` (`client_id`, `client_name`),
  KEY `idx_status` (`status`),
  KEY `idx_type` (`document_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ventes supprimées (soft delete)
CREATE TABLE `deleted_sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `original_sale_id` int(11) NOT NULL,
  `invoice_number` varchar(100) DEFAULT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `medicines` text DEFAULT NULL,
  `quantity` text DEFAULT NULL,
  `unit_cost` text DEFAULT NULL,
  `actual_price` text DEFAULT NULL,
  `origin` text DEFAULT NULL,
  `guard` text DEFAULT NULL,
  `tot_htva` decimal(10,2) DEFAULT NULL,
  `tot_tvac` decimal(10,2) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `commission` decimal(10,2) DEFAULT NULL,
  `deleted_by` varchar(100) DEFAULT NULL,
  `deleted_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_original` (`original_sale_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Paramètres de facturation (MS-09)
CREATE TABLE `invoice_infos` (
  `invoice_number` varchar(50) NOT NULL,
  `delai` varchar(100) NOT NULL DEFAULT '14 jours',
  `mode_paiement` varchar(100) NOT NULL DEFAULT 'Virement BCB 00200105087',
  `penalite` varchar(100) NOT NULL DEFAULT '10% sur 14 jours',
  `tva` varchar(50) NOT NULL DEFAULT '18% strictement applicable',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`invoice_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Panier en attente (on_hold)
CREATE TABLE `on_hold` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(100) NOT NULL,
  `medicine_name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `expire_date` date DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL,
  `type` varchar(10) NOT NULL,
  `actual_price` decimal(10,0) NOT NULL DEFAULT 0,
  `cost` decimal(10,0) NOT NULL DEFAULT 0,
  `amount` decimal(10,0) NOT NULL DEFAULT 0,
  `discount` decimal(10,0) NOT NULL DEFAULT 0,
  `tot_after_discount` decimal(10,0) NOT NULL DEFAULT 0,
  `guard_total` decimal(10,0) NOT NULL DEFAULT 0,
  `origin` tinyint(1) NOT NULL DEFAULT 0,
  `date` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_invoice` (`invoice_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Commandes (MS-04 Tâche 2.3c)
CREATE TABLE `on_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `doc_number` varchar(100) NOT NULL,
  `medicine_name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `expire_date` varchar(255) DEFAULT NULL,
  `qty` bigint(11) NOT NULL,
  `type` varchar(10) NOT NULL,
  `cost` bigint(11) NOT NULL DEFAULT 0,
  `amount` bigint(11) NOT NULL DEFAULT 0,
  `profit_amount` bigint(11) NOT NULL DEFAULT 0,
  `date` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_doc` (`doc_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Devis (MS-04 Tâche 2.3b)
CREATE TABLE `on_quot` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `doc_number` varchar(100) NOT NULL,
  `medicine_name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `expire_date` date DEFAULT NULL,
  `qty` bigint(11) NOT NULL,
  `type` varchar(10) NOT NULL,
  `cost` bigint(11) NOT NULL DEFAULT 0,
  `amount` bigint(11) NOT NULL DEFAULT 0,
  `profit_amount` bigint(11) NOT NULL DEFAULT 0,
  `date` varchar(255) NOT NULL,
  `validity_date` date DEFAULT NULL COMMENT 'Validité du devis',
  `converted` tinyint(1) DEFAULT 0 COMMENT 'Converti en facture',
  PRIMARY KEY (`id`),
  KEY `idx_doc` (`doc_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bons de livraison (MS-04 Tâche 2.3c)
CREATE TABLE `on_exp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `doc_number` varchar(100) NOT NULL,
  `medicine_name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `expire_date` varchar(255) DEFAULT NULL,
  `qty` bigint(11) NOT NULL,
  `type` varchar(10) NOT NULL,
  `cost` bigint(11) NOT NULL DEFAULT 0,
  `amount` bigint(11) NOT NULL DEFAULT 0,
  `profit_amount` bigint(11) NOT NULL DEFAULT 0,
  `date` varchar(255) NOT NULL,
  `delivery_status` enum('En préparation','Expédiée','Livrée') DEFAULT 'En préparation',
  PRIMARY KEY (`id`),
  KEY `idx_doc` (`doc_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Remises
CREATE TABLE `discounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator` varchar(255) DEFAULT NULL,
  `client` varchar(255) DEFAULT NULL,
  `invoice_number` varchar(100) NOT NULL,
  `amount` bigint(11) DEFAULT NULL,
  `type` enum('Manuelle','VIP','Anniversaire','Promotion') DEFAULT 'Manuelle',
  `reason` varchar(255) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_invoice` (`invoice_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Paiements externes (assurances/mutuelles)
CREATE TABLE `external_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `agent` varchar(100) NOT NULL,
  `medicine` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `origin` varchar(100) DEFAULT NULL,
  `amount_paid` decimal(15,2) NOT NULL,
  `payment_date` datetime NOT NULL,
  `ext_paid_status` varchar(50) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_invoice` (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Détails mutuelle
CREATE TABLE `mutuelle_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) NOT NULL,
  `partner_id` int(11) DEFAULT NULL,
  `partner_name` varchar(255) NOT NULL,
  `mutuality` varchar(255) NOT NULL,
  `employer` varchar(255) DEFAULT NULL,
  `cni` varchar(100) DEFAULT NULL,
  `beneficiary` varchar(255) DEFAULT NULL,
  `mutuelle_percent` decimal(5,2) DEFAULT 0.00,
  `discount_amount` decimal(15,2) DEFAULT 0.00,
  `original_amount` decimal(15,2) DEFAULT 0.00,
  `amount_after_mutuelle` decimal(15,2) DEFAULT 0.00,
  `sales_date` date NOT NULL,
  `operator` varchar(100) NOT NULL,
  `customer_type` varchar(20) DEFAULT 'walkin',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_invoice` (`invoice_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Temp (numérotation documents)
CREATE TABLE `temp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `doc_number` varchar(100) NOT NULL,
  `doc_type` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Paramètres de livraison/facturation (sett)
CREATE TABLE `sett` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `delivery_adress` varchar(255) NOT NULL DEFAULT 'Bujumbura',
  `delivery_time` varchar(255) NOT NULL DEFAULT '7 jours',
  `delivery_cost` varchar(255) NOT NULL DEFAULT '10.000FBU',
  `payment_time` varchar(255) NOT NULL DEFAULT '30 jours',
  `payment_mode` varchar(1000) NOT NULL DEFAULT 'Virement bancaire / BANCOBU',
  `payment_conditions` varchar(1000) NOT NULL DEFAULT 'Livraison partielle ou complete; Poids:....',
  `property_mention` varchar(1000) NOT NULL DEFAULT 'Camion, moto, velo, pied',
  `legal_mentions` varchar(1000) NOT NULL DEFAULT 'Nouvelles mentions speciales',
  `conformity_mention` varchar(1000) NOT NULL DEFAULT 'Marchandises conformes à la celles commandées.',
  `guard_amount` varchar(25) NOT NULL DEFAULT '5000',
  `authorizer` varchar(50) DEFAULT NULL,
  `filer` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- MODULE #5 : MS-05 DETTES
-- =====================================================================

CREATE TABLE `debts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `amount` bigint(20) DEFAULT NULL,
  `paid_amount` bigint(20) DEFAULT 0,
  `client_indebted` varchar(255) DEFAULT NULL,
  `client_id` int(100) DEFAULT NULL COMMENT 'Réf partners.id',
  `operator` varchar(255) DEFAULT NULL,
  `date_reported` date NOT NULL DEFAULT (CURRENT_DATE),
  `invoice_number` varchar(100) NOT NULL,
  `due_date` date DEFAULT NULL COMMENT 'Échéance de la dette',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0=Impayée, 1=Payée, 2=Partielle',
  `alert_sent` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_client` (`client_id`, `client_indebted`),
  KEY `idx_invoice` (`invoice_number`),
  KEY `idx_status` (`status`),
  KEY `idx_due_date` (`due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `debts_paid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `debt_id` int(11) NOT NULL,
  `amount` bigint(20) DEFAULT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `operator` varchar(255) DEFAULT NULL,
  `payment_method` varchar(100) NOT NULL DEFAULT 'Espèces',
  `invoice_number` varchar(100) NOT NULL,
  `date` date DEFAULT (CURRENT_DATE),
  `reference` varchar(100) DEFAULT NULL COMMENT 'Réf transaction bancaire',
  PRIMARY KEY (`id`),
  KEY `idx_debt` (`debt_id`),
  KEY `idx_client` (`client_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- MODULE #2, #3, #13 : MS-06 FINANCES (Caisse, Cash, Banques, Dépenses)
-- =====================================================================

-- Caisse journalière (Module #2)
CREATE TABLE `caisse` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `initial` bigint(10) NOT NULL DEFAULT 0,
  `montant_in` bigint(10) NOT NULL DEFAULT 0,
  `montant_out` bigint(10) NOT NULL DEFAULT 0,
  `date` date NOT NULL DEFAULT (CURRENT_DATE),
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=Ouverte, 1=Fermée',
  `opened_by` varchar(100) DEFAULT NULL,
  `closed_by` varchar(100) DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `balance` decimal(15,0) GENERATED ALWAYS AS (`initial` + `montant_in` - `montant_out`) STORED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Entrées caisse libres
CREATE TABLE `cash_in` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trans_motive` varchar(50) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `operator` varchar(100) NOT NULL,
  `date_created` date NOT NULL DEFAULT (CURRENT_DATE),
  `reference` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_date` (`date_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sorties caisse
CREATE TABLE `cash_out` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(50) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` date NOT NULL DEFAULT (CURRENT_DATE),
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `cashout_type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Banques (Module #13)
CREATE TABLE `banks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bank_name` varchar(100) NOT NULL,
  `account_number` varchar(100) DEFAULT NULL,
  `balance` decimal(15,2) DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'FBu',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_account` (`account_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Catégories de dépenses
CREATE TABLE `cat_expens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(100) NOT NULL,
  `adder` varchar(100) NOT NULL,
  `cat_date` date NOT NULL DEFAULT (CURRENT_DATE),
  `status` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dépenses (Module #13)
CREATE TABLE `expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `amount` bigint(11) DEFAULT NULL,
  `expense_cat` varchar(50) NOT NULL,
  `purpose` text NOT NULL,
  `id_proof` varchar(255) NOT NULL,
  `proof` varchar(255) DEFAULT NULL,
  `operator` varchar(255) DEFAULT NULL,
  `date` date NOT NULL DEFAULT (CURRENT_DATE),
  `status` enum('Active','Supprimée') DEFAULT 'Active',
  `deleted_reason` varchar(255) DEFAULT NULL,
  `deleted_by` varchar(100) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_date` (`date`),
  KEY `idx_cat` (`expense_cat`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Transactions (toutes opérations financières)
CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(100) NOT NULL,
  `action_type` varchar(100) NOT NULL,
  `ref_id` varchar(25) DEFAULT NULL,
  `montant` varchar(255) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` enum('entrée','sortie') DEFAULT NULL,
  `status` enum('Active','Supprimée') NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- MODULE #7 : MS-07 DOCUMENTS & ARCHIVES
-- =====================================================================

CREATE TABLE `arch_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(100) NOT NULL,
  `adder` varchar(100) NOT NULL,
  `cat_date` date NOT NULL DEFAULT (CURRENT_DATE),
  `status` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `arch_types` (`id`, `type`, `adder`, `status`) VALUES
(3, 'Facture', 'Alexis', 1),
(4, 'Bon de Commande', 'Alexis', 1),
(5, 'Bordereau de Livraison', 'Alexis', 1),
(6, 'Proforma', 'Alexis', 1),
(7, 'Autre', 'Alexis', 1),
(8, 'Lettre', 'Alexis', 1);

CREATE TABLE `documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `doc_type` varchar(100) NOT NULL,
  `doc_number` varchar(100) NOT NULL,
  `operator` varchar(255) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `client_id` int(100) DEFAULT NULL,
  `phone` varchar(15) NOT NULL DEFAULT '',
  `medicines` varchar(255) DEFAULT NULL,
  `quantity` varchar(255) DEFAULT NULL,
  `tot_htva` bigint(20) DEFAULT NULL,
  `tva` bigint(20) DEFAULT NULL,
  `tva_paid` bigint(20) DEFAULT NULL,
  `tot_tvac` bigint(20) DEFAULT NULL,
  `total_amount` bigint(11) DEFAULT NULL,
  `date` date NOT NULL DEFAULT (CURRENT_DATE),
  `doc_fields` text DEFAULT NULL COMMENT 'JSON metadata',
  `file` varchar(255) DEFAULT NULL COMMENT 'Chemin fichier uploadé',
  `file_size` int(11) DEFAULT NULL COMMENT 'Taille en octets',
  `file_type` varchar(50) DEFAULT NULL,
  `category` enum('Archive','Interne') NOT NULL DEFAULT 'Interne',
  `status` varchar(20) DEFAULT 'En cours',
  `contact_person` varchar(100) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0 COMMENT 'Soft delete',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_doc_number` (`doc_number`),
  KEY `idx_client` (`client_id`, `client_name`),
  KEY `idx_type` (`doc_type`),
  KEY `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Logs emails
CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin` varchar(100) DEFAULT NULL,
  `client_email` varchar(255) DEFAULT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `template` varchar(100) DEFAULT NULL COMMENT 'anniversaire, facture, relance',
  `sent_at` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL COMMENT 'sent, failed, pending',
  `error_message` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_client` (`client_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- MODULE #9 : MS-08 TÂCHES & CHAT INTERNE
-- =====================================================================

CREATE TABLE `cat_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(100) NOT NULL,
  `adder` varchar(100) NOT NULL,
  `cat_date` date NOT NULL DEFAULT (CURRENT_DATE),
  `status` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `cat_task` (`id`, `category`, `adder`, `status`) VALUES
(1, 'Visite', 'Cynthia', 1);

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_no` varchar(50) NOT NULL COMMENT 'Format: VNT-202606-001',
  `client` varchar(255) NOT NULL,
  `client_id` int(100) DEFAULT NULL,
  `category` varchar(100) NOT NULL,
  `priority` enum('Basse','Moyenne','Haute','Critique') DEFAULT 'Moyenne',
  `created_by` varchar(25) NOT NULL,
  `assigned_to` varchar(25) NOT NULL,
  `contributor` varchar(25) DEFAULT NULL,
  `created_at` date NOT NULL DEFAULT (CURRENT_DATE),
  `updated_at` datetime DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('Nouveau','Consulté','En cours','Résolu','Annulé') NOT NULL DEFAULT 'Nouveau',
  `description` text DEFAULT NULL,
  `sound_status` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ticket` (`ticket_no`),
  KEY `idx_assigned` (`assigned_to`),
  KEY `idx_status` (`status`),
  KEY `idx_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `chats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `msg_id` int(25) NOT NULL DEFAULT 0,
  `sender` varchar(255) NOT NULL,
  `receiver` varchar(255) NOT NULL,
  `msg_content` text NOT NULL,
  `ticket_no` varchar(100) DEFAULT NULL,
  `date_sent` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=Non lu, 1=Lu',
  `attachment` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ticket` (`ticket_no`),
  KEY `idx_receiver_status` (`receiver`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- MODULE #4, #8, #10 : MS-09 CONFIGURATION & MS-10 RAPPORTS
-- =====================================================================

-- Paramètres application (MS-09)
CREATE TABLE `app_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `license_key` varchar(100) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `company_name` varchar(255) DEFAULT 'DH TELECOM AND ADVERTISING HOUSE',
  `company_logo` varchar(255) DEFAULT NULL,
  `primary_color` varchar(10) DEFAULT '#000000',
  `secondary_color` varchar(10) DEFAULT '#FFFFFF',
  `currency` varchar(10) DEFAULT 'FBu',
  `tva_default` decimal(5,2) DEFAULT 18.00,
  `maintenance_mode` tinyint(1) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `app_settings` (`id`, `license_key`, `status`) VALUES
(1, 'DH-9195-2653', 0);

-- Licences (MS-09)
CREATE TABLE `licenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `drive_link` text NOT NULL DEFAULT '',
  `license_key` varchar(100) DEFAULT NULL,
  `activated_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','ready','active','expired') DEFAULT 'pending',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_email` (`email`),
  UNIQUE KEY `uk_license` (`license_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Contrôle système (MS-09)
CREATE TABLE `sys_control` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `last_time` datetime DEFAULT NULL,
  `last_check_ts` bigint(20) DEFAULT NULL,
  `version` varchar(20) DEFAULT '2.0',
  `db_version` varchar(20) DEFAULT '2.0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Logs généraux (MS-10)
CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(100) NOT NULL,
  `action_type` varchar(255) NOT NULL,
  `facture` varchar(100) DEFAULT NULL,
  `montant` varchar(255) DEFAULT NULL,
  `descrip` text DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` enum('entrée','sortie') DEFAULT NULL,
  `status` enum('Active','Supprimée') NOT NULL DEFAULT 'Active',
  `viewed` tinyint(1) DEFAULT 0,
  `module` varchar(50) DEFAULT NULL COMMENT 'MS-01, MS-02, ...',
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_date` (`date`),
  KEY `idx_type` (`type`),
  KEY `idx_module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Commentaires rapports (MS-10)
CREATE TABLE `report_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment_text` text NOT NULL,
  `created_by` varchar(100) NOT NULL,
  `created_date` datetime NOT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `report_type` enum('Ventes','Stock','Financier','Global') DEFAULT 'Global',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Alertes automatiques (MS-10 Module #10)
CREATE TABLE `alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alert_type` enum('Stock','Expiration','Dette','Anniversaire','Facture','Caisse') NOT NULL,
  `priority` enum('Basse','Moyenne','Haute','Critique') DEFAULT 'Moyenne',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `reference_id` int(11) DEFAULT NULL COMMENT 'ID de l''élément concerné',
  `reference_table` varchar(50) DEFAULT NULL,
  `target_role` varchar(100) DEFAULT NULL COMMENT 'Rôle ciblé par l''alerte',
  `is_read` tinyint(1) DEFAULT 0,
  `is_dismissed` tinyint(1) DEFAULT 0,
  `dismissed_by` varchar(100) DEFAULT NULL,
  `dismissed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`alert_type`),
  KEY `idx_read` (`is_read`),
  KEY `idx_priority` (`priority`),
  KEY `idx_target` (`target_role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


INSERT INTO `users` (`profil`, `user_name`, `password`, `category`, `f_name`, `l_name`, `telephone`, `email`, `level`, `status`) VALUES
('', 'admin', '$2y$12$5Jb3ZZMSa3F9ZJHvNMVJeeweJ5GDf4zTMYSHRGPCmoPOOedejR.0i', 'Super Admin', 'Super', 'Admin', '00000000', 'admin@dh.bi', 'Dev', 'Disponible');

INSERT INTO `sys_control` (`last_time`, `last_check_ts`, `version`, `db_version`) VALUES
(NOW(), UNIX_TIMESTAMP(), '2.0', '2.0');

INSERT INTO `sett` (`id`, `delivery_adress`, `delivery_time`, `delivery_cost`, `payment_time`, `payment_mode`, `payment_conditions`, `property_mention`, `legal_mentions`, `conformity_mention`, `guard_amount`) VALUES
(1, 'Bujumbura', '7 jours', '10.000FBu', '30 jours', 'Virement bancaire / BANCOBU', 'Livraison partielle ou complete; Poids:....', 'Camion, moto, velo, pied', 'Nouvelles mentions speciales', 'Marchandises conformes à la celles commandées.', '5000');

COMMIT;

-- =====================================================================
-- INDEX SUPPLÉMENTAIRES POUR PERFORMANCE
-- =====================================================================

CREATE INDEX idx_sales_date_status ON sales(date, status);
CREATE INDEX idx_expenses_date_cat ON expenses(date, expense_cat);
CREATE INDEX idx_debts_status_due ON debts(status, due_date);
CREATE INDEX idx_partners_vip ON partners(status_client, total_achats);
CREATE INDEX idx_stock_alert ON stock(actual_quantity, seuil, status);

-- =====================================================================
-- VUES POUR RAPPORTS (MS-10)
-- =====================================================================

-- Vue : Clients VIP
CREATE VIEW v_vip_clients AS
SELECT id, name, phone, email, total_achats, frequence_achats, dernier_achat
FROM partners
WHERE status_client = 'VIP' AND category = 'client'
ORDER BY total_achats DESC;

-- Vue : Produits en alerte stock
CREATE VIEW v_low_stock AS
SELECT id, medicine_name, category, actual_quantity, seuil, status
FROM stock
WHERE actual_quantity <= seuil AND status = 'Disponible'
ORDER BY actual_quantity ASC;

-- Vue : Dettes échues
CREATE VIEW v_overdue_debts AS
SELECT d.id, d.invoice_number, d.client_indebted, d.amount, d.paid_amount,
       (d.amount - d.paid_amount) AS solde_restant, d.due_date,
       DATEDIFF(CURDATE(), d.due_date) AS jours_retard
FROM debts d
WHERE d.status != 1 AND d.due_date < CURDATE()
ORDER BY jours_retard DESC;

-- Vue : Anniversaires du mois
CREATE VIEW v_birthdays_this_month AS
SELECT id, name, phone, email, annif, status_client
FROM partners
WHERE annif LIKE CONCAT('%-', LPAD(MONTH(CURDATE()), 2, '0'))
ORDER BY annif;

-- Vue : KPIs journaliers
CREATE VIEW v_daily_kpis AS
SELECT
  DATE(date) AS jour,
  COUNT(*) AS nb_ventes,
  SUM(total_amount) AS ca_total,
  SUM(total_profit) AS marge_totale,
  SUM(debt) AS dettes_creees,
  SUM(tva_paid) AS tva_collectee
FROM sales
WHERE status = 'Active'
GROUP BY DATE(date)
ORDER BY jour DESC;