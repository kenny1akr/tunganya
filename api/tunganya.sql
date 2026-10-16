-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : ven. 16 oct. 2026 à 10:11
-- Version du serveur : 10.4.28-MariaDB
-- Version de PHP : 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `tunganya`
--

-- --------------------------------------------------------

--
-- Structure de la table `alerts`
--

CREATE TABLE `alerts` (
  `id` int(11) NOT NULL,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `app_settings`
--

CREATE TABLE `app_settings` (
  `id` int(11) NOT NULL,
  `license_key` varchar(100) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `company_name` varchar(255) DEFAULT 'DH TELECOM AND ADVERTISING HOUSE',
  `company_logo` varchar(255) DEFAULT NULL,
  `primary_color` varchar(10) DEFAULT '#000000',
  `secondary_color` varchar(10) DEFAULT '#FFFFFF',
  `currency` varchar(10) DEFAULT 'FBu',
  `tva_default` decimal(5,2) DEFAULT 18.00,
  `maintenance_mode` tinyint(1) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `app_settings`
--

INSERT INTO `app_settings` (`id`, `license_key`, `status`, `company_name`, `company_logo`, `primary_color`, `secondary_color`, `currency`, `tva_default`, `maintenance_mode`, `updated_at`) VALUES
(1, 'DH-9195-2653', 0, 'DH TELECOM AND ADVERTISING HOUSE', NULL, '#000000', '#FFFFFF', 'FBu', 18.00, 0, '2026-10-12 21:27:21');

-- --------------------------------------------------------

--
-- Structure de la table `arch_types`
--

CREATE TABLE `arch_types` (
  `id` int(11) NOT NULL,
  `type` varchar(100) NOT NULL,
  `adder` varchar(100) NOT NULL,
  `cat_date` date NOT NULL DEFAULT curdate(),
  `status` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `arch_types`
--

INSERT INTO `arch_types` (`id`, `type`, `adder`, `cat_date`, `status`) VALUES
(3, 'Facture', 'Alexis', '2026-10-12', 1),
(4, 'Bon de Commande', 'Alexis', '2026-10-12', 1),
(5, 'Bordereau de Livraison', 'Alexis', '2026-10-12', 1),
(6, 'Proforma', 'Alexis', '2026-10-12', 1),
(7, 'Autre', 'Alexis', '2026-10-12', 1),
(8, 'Lettre', 'Alexis', '2026-10-12', 1);

-- --------------------------------------------------------

--
-- Structure de la table `banks`
--

CREATE TABLE `banks` (
  `id` int(11) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `account_number` varchar(100) DEFAULT NULL,
  `balance` decimal(15,2) DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'FBu',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `banks`
--

INSERT INTO `banks` (`id`, `bank_name`, `account_number`, `balance`, `currency`, `created_at`, `updated_at`) VALUES
(1, 'BANCOBU', '00200105087', 0.00, 'FBu', '2026-10-16 08:00:35', '2026-10-16 08:00:35');

-- --------------------------------------------------------

--
-- Structure de la table `caisse`
--

CREATE TABLE `caisse` (
  `id` int(11) NOT NULL,
  `initial` bigint(10) NOT NULL DEFAULT 0,
  `montant_in` bigint(10) NOT NULL DEFAULT 0,
  `montant_out` bigint(10) NOT NULL DEFAULT 0,
  `date` date NOT NULL DEFAULT curdate(),
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=Ouverte, 1=Fermée',
  `opened_by` varchar(100) DEFAULT NULL,
  `closed_by` varchar(100) DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `balance` decimal(15,0) GENERATED ALWAYS AS (`initial` + `montant_in` - `montant_out`) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `caisse`
--

INSERT INTO `caisse` (`id`, `initial`, `montant_in`, `montant_out`, `date`, `status`, `opened_by`, `closed_by`, `closed_at`) VALUES
(1, 0, 5000, 0, '2026-10-14', 0, 'kemal NDAYISABA', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `cash_in`
--

CREATE TABLE `cash_in` (
  `id` int(11) NOT NULL,
  `trans_motive` varchar(50) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `operator` varchar(100) NOT NULL,
  `date_created` date NOT NULL DEFAULT curdate(),
  `reference` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `cash_in`
--

INSERT INTO `cash_in` (`id`, `trans_motive`, `amount`, `operator`, `date_created`, `reference`, `created_at`) VALUES
(1, 'Paiement partiel facture', 5000.00, 'kemal NDAYISABA', '2026-10-14', 'FCT-20261014-542B8B06', '2026-10-14 10:33:36');

-- --------------------------------------------------------

--
-- Structure de la table `cash_out`
--

CREATE TABLE `cash_out` (
  `id` int(11) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` date NOT NULL DEFAULT curdate(),
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `cashout_type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `category_attributes`
--

CREATE TABLE `category_attributes` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL COMMENT 'Réf cat_prod.id',
  `sub_category_id` int(11) DEFAULT NULL COMMENT 'Réf sub_categories.id',
  `code` varchar(50) NOT NULL,
  `label` varchar(100) NOT NULL,
  `field_type` enum('texte','nombre','decimal','date','liste','booleen') DEFAULT 'texte',
  `possible_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`possible_values`)),
  `is_required` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `category_attributes`
--

INSERT INTO `category_attributes` (`id`, `category_id`, `sub_category_id`, `code`, `label`, `field_type`, `possible_values`, `is_required`, `display_order`, `status`) VALUES
(1, 137, NULL, 'grammage', 'Grammage (g/m²)', 'nombre', NULL, 1, 1, 1),
(2, 137, NULL, 'format_papier', 'Format', 'liste', NULL, 1, 2, 1),
(3, 137, NULL, 'type_papier', 'Type de papier', 'liste', NULL, 1, 3, 1),
(4, 138, NULL, 'ink_type', 'Type d\'encre', 'liste', NULL, 1, 1, 1),
(5, 138, NULL, 'ink_color_attr', 'Couleur', 'liste', NULL, 1, 2, 1),
(6, 140, NULL, 'laize_attr', 'Laize (m)', 'decimal', NULL, 1, 1, 1),
(7, 140, NULL, 'support_type_attr', 'Type de support', 'liste', NULL, 1, 2, 1);

-- --------------------------------------------------------

--
-- Structure de la table `cat_departments`
--

CREATE TABLE `cat_departments` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `abbreviation` varchar(100) NOT NULL,
  `responsible` varchar(100) NOT NULL,
  `profil` varchar(100) DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `cat_departments`
--

INSERT INTO `cat_departments` (`id`, `name`, `abbreviation`, `responsible`, `profil`, `status`) VALUES
(15, 'Vente', 'VNT', 'Alexis', '', 1);

-- --------------------------------------------------------

--
-- Structure de la table `cat_expens`
--

CREATE TABLE `cat_expens` (
  `id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `adder` varchar(100) NOT NULL,
  `cat_date` date NOT NULL DEFAULT curdate(),
  `status` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `cat_partners`
--

CREATE TABLE `cat_partners` (
  `id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `cat_partners`
--

INSERT INTO `cat_partners` (`id`, `category`, `status`) VALUES
(1, 'Client', 1),
(2, 'Fournisseur', 1);

-- --------------------------------------------------------

--
-- Structure de la table `cat_prod`
--

CREATE TABLE `cat_prod` (
  `id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `family` varchar(100) DEFAULT NULL,
  `default_unit_id` int(11) DEFAULT NULL,
  `adder` varchar(100) NOT NULL,
  `cat_date` date NOT NULL DEFAULT curdate(),
  `status` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `cat_prod`
--

INSERT INTO `cat_prod` (`id`, `category`, `description`, `family`, `default_unit_id`, `adder`, `cat_date`, `status`) VALUES
(135, 'Service', NULL, NULL, NULL, 'Isabelle', '2026-10-12', 1),
(136, 'Produit', NULL, NULL, NULL, 'Isabelle', '2026-10-12', 1),
(137, 'Papiers & Supports', NULL, NULL, NULL, 'system', '2026-10-13', 1),
(138, 'Encres & Consommables', NULL, NULL, NULL, 'system', '2026-10-13', 1),
(139, 'Plaques & Formes', NULL, NULL, NULL, 'system', '2026-10-13', 1),
(140, 'Grand Format', NULL, NULL, NULL, 'system', '2026-10-13', 1),
(141, 'Finition & Façonnage', NULL, NULL, NULL, 'system', '2026-10-13', 1),
(142, 'Produits Finis', NULL, NULL, NULL, 'system', '2026-10-13', 1),
(143, 'Machines & Pièces', NULL, NULL, NULL, 'system', '2026-10-13', 1),
(144, 'Services Impression', NULL, NULL, NULL, 'system', '2026-10-13', 1),
(145, 'Emballages & Logistique', NULL, NULL, NULL, 'system', '2026-10-13', 1),
(146, 'Chimie & Atelier', NULL, NULL, NULL, 'system', '2026-10-13', 1),
(147, 'Wliper for 1,8', 'Material Machine', NULL, NULL, 'kamana', '2026-10-15', 1),
(151, 'Wliper for 1,9', 'Material', 'Matériel Machine', 28, 'kamana', '2026-10-15', 1);

-- --------------------------------------------------------

--
-- Structure de la table `cat_task`
--

CREATE TABLE `cat_task` (
  `id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `adder` varchar(100) NOT NULL,
  `cat_date` date NOT NULL DEFAULT curdate(),
  `status` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `cat_task`
--

INSERT INTO `cat_task` (`id`, `category`, `adder`, `cat_date`, `status`) VALUES
(1, 'Visite', 'Cynthia', '2026-10-12', 1),
(2, 'Commande', 'system', '2026-10-16', 1);

-- --------------------------------------------------------

--
-- Structure de la table `cat_users`
--

CREATE TABLE `cat_users` (
  `id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `level_access` int(11) NOT NULL DEFAULT 1 COMMENT '1=Utilisateur, 5=Super Admin',
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `cat_users`
--

INSERT INTO `cat_users` (`id`, `category`, `description`, `level_access`, `status`, `created_at`) VALUES
(1, 'super_admin', 'Contrôle total du système', 6, 1, '2026-10-12 21:27:20'),
(2, 'directeur_general', 'Vue globale et validation des opérations', 5, 1, '2026-10-12 21:27:20'),
(3, 'comptable', 'Comptabilité, banque et rapports financiers', 4, 1, '2026-10-12 21:27:20'),
(4, 'receptionniste', 'Accueil clients, commandes et facturation', 3, 1, '2026-10-12 21:27:20'),
(5, 'magasinier', 'Gestion du stock et des livraisons', 2, 1, '2026-10-12 21:27:20'),
(6, 'travailleur', 'Accès aux opérations courantes', 1, 1, '2026-10-12 21:27:20');

-- --------------------------------------------------------

--
-- Structure de la table `chats`
--

CREATE TABLE `chats` (
  `id` int(11) NOT NULL,
  `msg_id` int(25) NOT NULL DEFAULT 0,
  `sender` varchar(255) NOT NULL,
  `receiver` varchar(255) NOT NULL,
  `msg_content` text NOT NULL,
  `ticket_no` varchar(100) DEFAULT NULL,
  `date_sent` datetime NOT NULL DEFAULT current_timestamp(),
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=Non lu, 1=Lu',
  `attachment` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `debts`
--

CREATE TABLE `debts` (
  `id` int(11) NOT NULL,
  `amount` bigint(20) DEFAULT NULL,
  `paid_amount` bigint(20) DEFAULT 0,
  `client_indebted` varchar(255) DEFAULT NULL,
  `client_id` int(100) DEFAULT NULL COMMENT 'Réf partners.id',
  `operator` varchar(255) DEFAULT NULL,
  `date_reported` date NOT NULL DEFAULT curdate(),
  `invoice_number` varchar(100) NOT NULL,
  `due_date` date DEFAULT NULL COMMENT 'Échéance de la dette',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0=Impayée, 1=Payée, 2=Partielle',
  `alert_sent` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `debts`
--

INSERT INTO `debts` (`id`, `amount`, `paid_amount`, `client_indebted`, `client_id`, `operator`, `date_reported`, `invoice_number`, `due_date`, `status`, `alert_sent`, `created_at`, `updated_at`) VALUES
(1, 11800, 5000, 'Odon', 8, 'kemal NDAYISABA', '2026-10-14', 'FCT-20261014-542B8B06', '2026-10-31', 2, 0, '2026-10-14 09:45:17', '2026-10-14 10:33:36');

-- --------------------------------------------------------

--
-- Structure de la table `debts_paid`
--

CREATE TABLE `debts_paid` (
  `id` int(11) NOT NULL,
  `debt_id` int(11) NOT NULL,
  `amount` bigint(20) DEFAULT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `operator` varchar(255) DEFAULT NULL,
  `payment_method` varchar(100) NOT NULL DEFAULT 'Espèces',
  `invoice_number` varchar(100) NOT NULL,
  `date` date DEFAULT curdate(),
  `reference` varchar(100) DEFAULT NULL COMMENT 'Réf transaction bancaire'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `debts_paid`
--

INSERT INTO `debts_paid` (`id`, `debt_id`, `amount`, `client_name`, `operator`, `payment_method`, `invoice_number`, `date`, `reference`) VALUES
(1, 1, 5000, 'Odon', 'kemal NDAYISABA', 'À crédit', 'FCT-20261014-542B8B06', '2026-10-14', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `deleted_sales`
--

CREATE TABLE `deleted_sales` (
  `id` int(11) NOT NULL,
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
  `deleted_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `discounts`
--

CREATE TABLE `discounts` (
  `id` int(11) NOT NULL,
  `operator` varchar(255) DEFAULT NULL,
  `client` varchar(255) DEFAULT NULL,
  `invoice_number` varchar(100) NOT NULL,
  `amount` bigint(11) DEFAULT NULL,
  `type` enum('Manuelle','VIP','Anniversaire','Promotion') DEFAULT 'Manuelle',
  `reason` varchar(255) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
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
  `date` date NOT NULL DEFAULT curdate(),
  `doc_fields` text DEFAULT NULL COMMENT 'JSON metadata',
  `file` varchar(255) DEFAULT NULL COMMENT 'Chemin fichier uploadé',
  `file_size` int(11) DEFAULT NULL COMMENT 'Taille en octets',
  `file_type` varchar(50) DEFAULT NULL,
  `category` enum('Archive','Interne') NOT NULL DEFAULT 'Interne',
  `status` varchar(20) DEFAULT 'En cours',
  `contact_person` varchar(100) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0 COMMENT 'Soft delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `documents`
--

INSERT INTO `documents` (`id`, `doc_type`, `doc_number`, `operator`, `client_name`, `client_id`, `phone`, `medicines`, `quantity`, `tot_htva`, `tva`, `tva_paid`, `tot_tvac`, `total_amount`, `date`, `doc_fields`, `file`, `file_size`, `file_type`, `category`, `status`, `contact_person`, `is_deleted`, `created_at`) VALUES
(1, 'Facture', 'FCT-20261014-542B8B06', 'kemal NDAYISABA', 'Odon', 8, '', 'Bâche publicitaire (m²)', '[2]', 20000, 1800, 0, 11800, 11800, '2026-10-14', '{\"from_task\":\"CMD-20261014-8B5B51\",\"items\":[{\"stock_id\":11,\"name\":\"Bâche publicitaire (m²)\",\"quantity\":2,\"unit\":\"m²\",\"unit_price\":10000,\"line_total\":20000,\"note\":\"ddddddddddghfh\"}],\"subtotal\":20000,\"tax_rate\":18,\"tax_amount\":1800,\"total\":11800,\"paid\":0,\"remaining\":11800,\"payment_method\":\"À crédit\"}', NULL, NULL, NULL, 'Interne', 'Impayée', 'kemal NDAYISABA', 0, '2026-10-14 09:45:17');

-- --------------------------------------------------------

--
-- Structure de la table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL,
  `admin` varchar(100) DEFAULT NULL,
  `client_email` varchar(255) DEFAULT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `template` varchar(100) DEFAULT NULL COMMENT 'anniversaire, facture, relance',
  `sent_at` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL COMMENT 'sent, failed, pending',
  `error_message` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `entries`
--

CREATE TABLE `entries` (
  `id` int(11) NOT NULL,
  `entry_number` varchar(50) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `supplier_id` int(100) DEFAULT NULL,
  `entry_type` varchar(50) DEFAULT NULL,
  `operator` varchar(25) NOT NULL,
  `received_by` varchar(100) DEFAULT NULL,
  `invoice_in` varchar(100) DEFAULT NULL,
  `bar_code` int(11) NOT NULL DEFAULT 0,
  `medicine_name` varchar(100) NOT NULL,
  `category` varchar(255) NOT NULL,
  `quantity` decimal(10,1) NOT NULL,
  `register_date` date NOT NULL DEFAULT curdate(),
  `expire_date` date DEFAULT NULL,
  `sell_type` varchar(100) NOT NULL,
  `actual_price` decimal(10,0) NOT NULL DEFAULT 0,
  `total_amount` decimal(10,0) NOT NULL DEFAULT 0,
  `status` varchar(100) NOT NULL DEFAULT 'Disponible'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `entries`
--

INSERT INTO `entries` (`id`, `entry_number`, `product_id`, `supplier_id`, `entry_type`, `operator`, `received_by`, `invoice_in`, `bar_code`, `medicine_name`, `category`, `quantity`, `register_date`, `expire_date`, `sell_type`, `actual_price`, `total_amount`, `status`) VALUES
(3, NULL, NULL, NULL, NULL, 'kamana', NULL, NULL, 0, 'Lou', 'Produit', 40.0, '2026-10-13', '2026-10-15', 'm²', 3000, 120000, 'Disponible'),
(4, NULL, NULL, NULL, NULL, 'kamana', NULL, NULL, 0, 'Lou', 'Produit', 1.0, '2026-10-13', '2026-10-15', 'm²', 0, 0, 'Disponible'),
(5, NULL, NULL, NULL, NULL, 'kamana', NULL, NULL, 0, 'produit1test', 'Wliper for 1,9', 5000.0, '2026-10-15', '2026-10-30', 'piece', 0, 0, 'Disponible'),
(6, 'REC-20261015-42545E74', 6, 3, 'Réception fournisseur', 'kamana', 'KAMANA Holy', NULL, 0, 'produit1test', 'Wliper for 1,9', 4500.0, '2026-10-15', '2026-10-15', 'piece', 3000, 13500000, 'Disponible'),
(7, 'REC-20261015-F8203492', 6, 3, 'Réception fournisseur', 'kamana', 'KAMANA Holy', NULL, 0, 'produit1test', 'Wliper for 1,9', 8000.0, '2026-10-15', '2026-10-23', 'piece', 150000, 1200000000, 'Disponible');

-- --------------------------------------------------------

--
-- Structure de la table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `amount` bigint(11) DEFAULT NULL,
  `expense_cat` varchar(50) NOT NULL,
  `purpose` text NOT NULL,
  `id_proof` varchar(255) NOT NULL,
  `proof` varchar(255) DEFAULT NULL,
  `operator` varchar(255) DEFAULT NULL,
  `date` date NOT NULL DEFAULT curdate(),
  `status` enum('Active','Supprimée') DEFAULT 'Active',
  `deleted_reason` varchar(255) DEFAULT NULL,
  `deleted_by` varchar(100) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `external_payments`
--

CREATE TABLE `external_payments` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `agent` varchar(100) NOT NULL,
  `medicine` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `origin` varchar(100) DEFAULT NULL,
  `amount_paid` decimal(15,2) NOT NULL,
  `payment_date` datetime NOT NULL,
  `ext_paid_status` varchar(50) NOT NULL,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `invoice_infos`
--

CREATE TABLE `invoice_infos` (
  `invoice_number` varchar(50) NOT NULL,
  `delai` varchar(100) NOT NULL DEFAULT '14 jours',
  `mode_paiement` varchar(100) NOT NULL DEFAULT 'Virement BCB 00200105087',
  `penalite` varchar(100) NOT NULL DEFAULT '10% sur 14 jours',
  `tva` varchar(50) NOT NULL DEFAULT '18% strictement applicable',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `licenses`
--

CREATE TABLE `licenses` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `drive_link` text NOT NULL DEFAULT '',
  `license_key` varchar(100) DEFAULT NULL,
  `activated_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','ready','active','expired') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `logintos`
--

CREATE TABLE `logintos` (
  `id` int(11) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `action_type` varchar(100) NOT NULL COMMENT 'Connexion, Déconnexion, Échec',
  `session_id` varchar(255) DEFAULT NULL,
  `ip_adress` varchar(255) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `descrip` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `logintos`
--

INSERT INTO `logintos` (`id`, `user_id`, `action_type`, `session_id`, `ip_adress`, `user_agent`, `descrip`, `created_at`) VALUES
(1, 'probe', 'Échec', '-', '::1', 'curl/8.7.1', 'Utilisateur inconnu - 13/10/2026 00:23', '2026-10-12 22:23:01'),
(2, 'probe', 'Échec', '-', '::1', 'curl/8.7.1', 'Utilisateur inconnu - 13/10/2026 00:26', '2026-10-12 22:26:13'),
(3, 'admin@burundi-booking.com', 'Échec', '-', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Utilisateur inconnu - 13/10/2026 00:44', '2026-10-12 22:44:05'),
(4, 'admin@burundi-booking.com', 'Échec', '-', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Utilisateur inconnu - 13/10/2026 00:57', '2026-10-12 22:57:55'),
(5, 'admin@burundi-booking.com', 'Échec', '-', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Utilisateur inconnu - 13/10/2026 00:57', '2026-10-12 22:57:56'),
(6, 'admin', 'Connexion au système', '-', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Succès - 13/10/2026 00:58', '2026-10-12 22:58:46'),
(7, '__probe__', 'Échec', '-', '::1', 'curl/8.7.1', 'Utilisateur inconnu - 13/10/2026 01:01', '2026-10-12 23:01:50'),
(8, 'admin', 'Connexion au système', '-', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.136.1 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36', 'Succès - 13/10/2026 01:02', '2026-10-12 23:02:16'),
(9, 'admin', 'Connexion au système', '-', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.136.1 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36', 'Succès - 13/10/2026 01:13', '2026-10-12 23:13:42'),
(10, 'kemal', 'Connexion au système', '-', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Succès - 13/10/2026 01:18', '2026-10-12 23:18:33'),
(11, 'admin', 'Connexion au système', '-', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Succès - 13/10/2026 02:05', '2026-10-13 00:05:02'),
(12, 'kamana', 'Connexion au système', '-', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Succès - 13/10/2026 02:19', '2026-10-13 00:19:10'),
(13, 'admin', 'Connexion au système', '-', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Succès - 13/10/2026 11:59', '2026-10-13 09:59:42'),
(14, 'kamana', 'Connexion au système', '-', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Succès - 13/10/2026 14:26', '2026-10-13 12:26:39'),
(15, 'kemal', 'Connexion au système', '-', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Succès - 13/10/2026 14:30', '2026-10-13 12:30:30'),
(16, 'admin', 'Connexion au système', '-', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Succès - 13/10/2026 14:36', '2026-10-13 12:36:28'),
(17, 'kamana', 'Connexion au système', '-', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Succès - 13/10/2026 15:10', '2026-10-13 13:10:23'),
(18, 'admin', 'Connexion au système', '-', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Succès - 13/10/2026 15:33', '2026-10-13 13:33:49'),
(19, 'kamana', 'Connexion au système', '-', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Succès - 13/10/2026 16:52', '2026-10-13 14:52:47'),
(20, 'admin', 'Connexion au système', '-', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', 'Succès - 13/10/2026 17:42', '2026-10-13 15:42:05'),
(21, 'kamana', 'Connexion au système', '-', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', 'Succès - 13/10/2026 18:13', '2026-10-13 16:13:48'),
(22, 'kamana', 'Connexion au système', '-', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', 'Succès - 13/10/2026 18:17', '2026-10-13 16:17:02'),
(23, 'kamana', 'Connexion au système', '-', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', 'Succès - 13/10/2026 18:22', '2026-10-13 16:22:59'),
(24, 'kamana', 'Connexion au système', '-', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', 'Succès - 13/10/2026 18:23', '2026-10-13 16:23:07'),
(25, 'kamana', 'Connexion au système', '-', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', 'Succès - 13/10/2026 18:23', '2026-10-13 16:23:31'),
(26, 'kamana', 'Connexion au système', '-', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 'Succès - 15/10/2026 10:20', '2026-10-15 08:20:06'),
(27, 'kamana', 'Connexion au système', '-', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.140.0 Chrome/150.0.7871.250 Electron/43.7.3 Safari/537.36', 'Succès - 15/10/2026 10:35', '2026-10-15 08:35:20'),
(28, 'kemal', 'Connexion au système', '-', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 'Succès - 15/10/2026 15:01', '2026-10-15 13:01:42'),
(29, 'kamana', 'Connexion au système', '-', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 'Succès - 15/10/2026 15:03', '2026-10-15 13:03:57'),
(30, 'kamal', 'Échec', '-', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 'Utilisateur inconnu - 15/10/2026 15:07', '2026-10-15 13:07:08'),
(31, 'kamal', 'Échec', '-', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 'Utilisateur inconnu - 15/10/2026 15:07', '2026-10-15 13:07:23'),
(32, 'kemal', 'Connexion au système', '-', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 'Succès - 15/10/2026 15:07', '2026-10-15 13:07:32'),
(33, 'kamana', 'Connexion au système', '-', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 'Succès - 15/10/2026 15:10', '2026-10-15 13:10:36'),
(34, 'kemal', 'Connexion au système', '-', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 'Succès - 15/10/2026 15:45', '2026-10-15 13:45:59');

-- --------------------------------------------------------

--
-- Structure de la table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `action_type` varchar(255) NOT NULL,
  `facture` varchar(100) DEFAULT NULL,
  `montant` varchar(255) DEFAULT NULL,
  `descrip` text DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` enum('entrée','sortie') DEFAULT NULL,
  `status` enum('Active','Supprimée') NOT NULL DEFAULT 'Active',
  `viewed` tinyint(1) DEFAULT 0,
  `module` varchar(50) DEFAULT NULL COMMENT 'MS-01, MS-02, ...'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `logs`
--

INSERT INTO `logs` (`id`, `user_id`, `action_type`, `facture`, `montant`, `descrip`, `date`, `type`, `status`, `viewed`, `module`) VALUES
(1, 'kemal', 'Facture (depuis commande)', 'FCT-20261014-542B8B06', '11800', 'Facture FCT-20261014-542B8B06 créée depuis CMD-20261014-8B5B51', '2026-10-14 09:45:17', 'entrée', 'Active', 0, 'FCT-02');

-- --------------------------------------------------------

--
-- Structure de la table `machines`
--

CREATE TABLE `machines` (
  `id` int(11) NOT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(150) NOT NULL,
  `machine_type` enum('offset','numerique','grand_format','finition','decoupe','reliure','autre') NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `year_acquired` year(4) DEFAULT NULL,
  `hourly_cost` decimal(15,2) DEFAULT 0.00 COMMENT 'Coût horaire FBu',
  `status` enum('operationnelle','maintenance','panne','arret') DEFAULT 'operationnelle',
  `location` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `machine_maintenance`
--

CREATE TABLE `machine_maintenance` (
  `id` int(11) NOT NULL,
  `machine_id` int(11) NOT NULL,
  `maintenance_type` enum('preventive','corrective','urgence') NOT NULL,
  `description` text NOT NULL,
  `cost` decimal(15,2) DEFAULT 0.00,
  `technician` varchar(150) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('planifiee','en_cours','terminee','annulee') DEFAULT 'planifiee',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `mutuelle_details`
--

CREATE TABLE `mutuelle_details` (
  `id` int(11) NOT NULL,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `on_exp`
--

CREATE TABLE `on_exp` (
  `id` int(11) NOT NULL,
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
  `delivery_status` enum('En préparation','Expédiée','Livrée') DEFAULT 'En préparation'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `on_hold`
--

CREATE TABLE `on_hold` (
  `id` int(11) NOT NULL,
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
  `date` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `on_order`
--

CREATE TABLE `on_order` (
  `id` int(11) NOT NULL,
  `doc_number` varchar(100) NOT NULL,
  `medicine_name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `expire_date` varchar(255) DEFAULT NULL,
  `qty` bigint(11) NOT NULL,
  `type` varchar(10) NOT NULL,
  `cost` bigint(11) NOT NULL DEFAULT 0,
  `amount` bigint(11) NOT NULL DEFAULT 0,
  `profit_amount` bigint(11) NOT NULL DEFAULT 0,
  `date` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `on_quot`
--

CREATE TABLE `on_quot` (
  `id` int(11) NOT NULL,
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
  `converted` tinyint(1) DEFAULT 0 COMMENT 'Converti en facture'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `partners`
--

CREATE TABLE `partners` (
  `id` int(100) NOT NULL,
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
  `debt_limit` bigint(10) NOT NULL DEFAULT 0,
  `debt` bigint(10) NOT NULL DEFAULT 0,
  `status_client` enum('Nouveau','Régulier','VIP') DEFAULT 'Nouveau',
  `total_achats` bigint(15) DEFAULT 0,
  `frequence_achats` int(10) DEFAULT 0,
  `dernier_achat` date DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `annif` varchar(10) DEFAULT NULL COMMENT 'Format JJ-MM',
  `birthday_promo_used` date DEFAULT NULL,
  `alert_debt` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `partners`
--

INSERT INTO `partners` (`id`, `name`, `abbr`, `category`, `address`, `phone`, `email`, `portal_username`, `portal_password`, `RC`, `NIF`, `profil`, `debt_limit`, `debt`, `status_client`, `total_achats`, `frequence_achats`, `dernier_achat`, `rating`, `annif`, `birthday_promo_used`, `alert_debt`, `created_at`, `updated_at`) VALUES
(3, 'KIM FLY TECH', NULL, 'fournisseur', 'Gituro,2,87', '+25768479247', 'kimfly@gmail.com', NULL, NULL, '', '', NULL, 0, 0, 'Nouveau', 0, 0, NULL, NULL, NULL, NULL, 0, '2026-10-13 00:11:02', '2026-10-13 00:11:02'),
(7, 'Mike MANEKO', NULL, 'client', 'Gasarara', '+25767530006', 'mike@gmail.com', 'maneko', '$2y$10$cYgF77x6Xkdedzridjso2OF.HedL5OnwwlEm9xkhSTmyv11HZlrU6', '', '', NULL, 0, 0, 'Nouveau', 0, 0, NULL, NULL, '01-01', NULL, 0, '2026-10-13 12:32:08', '2026-10-13 12:32:08'),
(8, 'Odon', NULL, 'client', 'kgfjdhfghf', '64365332', 'hackkalim@gmail.com', 'odon', '$2y$10$DF6JZCJ8Pk/U9btz9z9TDetTw2gxd5b8q66zIKSHpmVdDS3DkFv7G', 'dfh', '456354yg', NULL, 0, 11800, 'Nouveau', 11800, 1, '2026-10-14', NULL, '20-08', NULL, 0, '2026-10-13 17:23:16', '2026-10-14 09:45:17');

-- --------------------------------------------------------

--
-- Structure de la table `partners_merge_backup`
--

CREATE TABLE `partners_merge_backup` (
  `id` int(100) NOT NULL DEFAULT 0,
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
  `debt_limit` bigint(10) NOT NULL DEFAULT 0,
  `debt` bigint(10) NOT NULL DEFAULT 0,
  `status_client` enum('Nouveau','Régulier','VIP') DEFAULT 'Nouveau',
  `total_achats` bigint(15) DEFAULT 0,
  `frequence_achats` int(10) DEFAULT 0,
  `dernier_achat` date DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `annif` varchar(10) DEFAULT NULL COMMENT 'Format JJ-MM',
  `birthday_promo_used` date DEFAULT NULL,
  `alert_debt` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `partners_merge_backup`
--

INSERT INTO `partners_merge_backup` (`id`, `name`, `abbr`, `category`, `address`, `phone`, `email`, `portal_username`, `portal_password`, `RC`, `NIF`, `profil`, `debt_limit`, `debt`, `status_client`, `total_achats`, `frequence_achats`, `dernier_achat`, `rating`, `annif`, `birthday_promo_used`, `alert_debt`, `created_at`, `updated_at`) VALUES
(3, 'KIM FLY TECH', NULL, 'fournisseur', 'Gituro,2,87', '+25768479247', 'kimfly@gmail.com', NULL, NULL, '', '', NULL, 0, 0, 'Nouveau', 0, 0, NULL, NULL, NULL, NULL, 0, '2026-10-13 00:11:02', '2026-10-13 00:11:02'),
(7, 'Mike MANEKO', NULL, 'client', 'Gasarara', '+25767530006', 'mike@gmail.com', 'maneko', '$2y$10$cYgF77x6Xkdedzridjso2OF.HedL5OnwwlEm9xkhSTmyv11HZlrU6', '', '', NULL, 0, 0, 'Nouveau', 0, 0, NULL, NULL, '01-01', NULL, 0, '2026-10-13 12:32:08', '2026-10-13 12:32:08'),
(8, 'Nininahazwe Ladouce', NULL, 'client', 'Kinindo', '76000045', 'nininahazweladouce322@gmail.com', 'kemal', '$2y$10$NpngOdQrdZ3t4yeOzMK/seTnaVT5Ee0zMrYBiIT6hqZ26E8YeJZyS', '', '', NULL, 0, 0, 'Nouveau', 0, 0, NULL, NULL, NULL, NULL, 0, '2026-10-15 13:03:17', '2026-10-15 13:03:17');

-- --------------------------------------------------------

--
-- Structure de la table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(4) DEFAULT 0,
  `ip_request` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `quantity_corrections`
--

CREATE TABLE `quantity_corrections` (
  `id` int(11) NOT NULL,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `report_comments`
--

CREATE TABLE `report_comments` (
  `id` int(11) NOT NULL,
  `comment_text` text NOT NULL,
  `created_by` varchar(100) NOT NULL,
  `created_date` datetime NOT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `report_type` enum('Ventes','Stock','Financier','Global') DEFAULT 'Global'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(100) NOT NULL,
  `operator` varchar(255) NOT NULL,
  `agent` varchar(255) DEFAULT NULL,
  `client_name` varchar(255) NOT NULL,
  `client_id` int(100) DEFAULT NULL COMMENT 'Réf partners.id',
  `phone` varchar(15) DEFAULT '',
  `medicines` text NOT NULL,
  `products_json` text DEFAULT NULL COMMENT 'JSON détaillé des produits (réf stock.id)',
  `quantity` text NOT NULL,
  `units_json` text DEFAULT NULL COMMENT 'JSON des unités utilisées',
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
  `date` date NOT NULL DEFAULT curdate(),
  `status` enum('Active','Supprimée') DEFAULT 'Active',
  `offer_status` enum('Brouillon','Envoyée','Acceptée','Refusée','Expirée') DEFAULT NULL COMMENT 'Statut uniquement pour document_type=Devis',
  `valid_until` date DEFAULT NULL COMMENT 'Date de validité de l''offre',
  `offer_notes` text DEFAULT NULL COMMENT 'Notes libres (conditions, mentions spéciales)',
  `origin` varchar(50) NOT NULL DEFAULT 'Interne',
  `guard` varchar(500) NOT NULL DEFAULT '',
  `netcash` decimal(10,0) NOT NULL DEFAULT 0,
  `payment_method` varchar(200) NOT NULL DEFAULT 'Espèces',
  `ext_paid_amount` decimal(10,0) DEFAULT NULL,
  `ext_paid_status` varchar(50) DEFAULT NULL,
  `cashier` varchar(255) DEFAULT NULL,
  `document_type` enum('Facture','Proforma','Devis','Commande','Livraison') DEFAULT 'Facture',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `machine_id` int(11) DEFAULT NULL COMMENT 'Machine utilisée (services impression)',
  `delivery_status` enum('En préparation','Expédiée','Livrée','Annulée') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `sales`
--

INSERT INTO `sales` (`id`, `invoice_number`, `operator`, `agent`, `client_name`, `client_id`, `phone`, `medicines`, `products_json`, `quantity`, `units_json`, `unit_cost`, `actual_price`, `tot_htva`, `tva`, `tva_paid`, `tot_tvac`, `debt`, `total_amount`, `total_profit`, `discount`, `commission`, `date`, `status`, `offer_status`, `valid_until`, `offer_notes`, `origin`, `guard`, `netcash`, `payment_method`, `ext_paid_amount`, `ext_paid_status`, `cashier`, `document_type`, `created_at`, `machine_id`, `delivery_status`) VALUES
(1, 'CLI-20261015-6E5B4B8D', 'kamana', 'KAMANA Holy', 'Nininahazwe Ladouce', 8, '76000045', 'produit1test', '[{\"stock_id\":6,\"name\":\"produit1test\",\"quantity\":17000,\"unit\":\"piece\",\"unit_cost\":69343,\"unit_price\":0,\"line_total\":0}]', '[17000]', '[\"piece\"]', '[69343]', '[0]', 0, 0, 0, 0, 0, 0, -1178831000, 0, 0, '2026-10-15', 'Active', NULL, NULL, NULL, 'Sortie client magasin', '', 0, 'À facturer', NULL, NULL, NULL, 'Commande', '2026-10-15 13:06:41', NULL, NULL),
(2, 'CLI-20261015-C2853178', 'kamana', 'KAMANA Holy', 'Mike MANEKO', 7, '+25767530006', 'produit1test', '[{\"stock_id\":6,\"name\":\"produit1test\",\"quantity\":499,\"unit\":\"piece\",\"unit_cost\":69343,\"unit_price\":0,\"line_total\":0}]', '[499]', '[\"piece\"]', '[69343]', '[0]', 0, 0, 0, 0, 0, 0, -34602157, 0, 0, '2026-10-15', 'Active', NULL, NULL, NULL, 'Sortie client magasin', '', 0, 'À facturer', NULL, NULL, NULL, 'Commande', '2026-10-15 13:24:48', NULL, NULL),
(3, 'OFF-20261014-0002', 'kemal', 'kemal NDAYISABA', 'Mike MANEKO', 7, '+25767530006', 'Bâche publicitaire, Kakémono', '[{\"stock_id\": null, \"name\": \"Bâche publicitaire 2×1 m\", \"quantity\": 1, \"unit\": \"m²\", \"unit_price\": 25000, \"line_total\": 50000, \"note\": \"Impression UV, œillets tous les 50 cm\"}, {\"stock_id\": null, \"name\": \"Kakémono 80×200 cm\", \"quantity\": 2, \"unit\": \"pièce\", \"unit_price\": 85000, \"line_total\": 170000, \"note\": \"Structure alu + sac de transport\"}]', '[1, 2]', '[\"m²\", \"pièce\"]', '[0, 0]', '[25000, 85000]', 220000, 39600, 0, 259600, 0, 259600, 259600, 0, 0, '2026-10-09', 'Active', 'Envoyée', '2026-10-24', 'Valable 15 jours. Livraison gratuite à Bujumbura.', 'Interne', '', 0, 'À crédit', NULL, NULL, NULL, 'Devis', '2026-10-14 11:05:50', NULL, NULL),
(4, 'OFF-20261014-0003', 'kemal', 'kemal NDAYISABA', 'Mike MANEKO', 7, '+25767530006', 'Affiche A2 quadri + Pose', '[{\"stock_id\": null, \"name\": \"Affiche A2 quadri\", \"quantity\": 50, \"unit\": \"exemplaire\", \"unit_price\": 3500, \"line_total\": 175000, \"note\": \"Papier 170g couché\"}, {\"stock_id\": null, \"name\": \"Pose affiche\", \"quantity\": 50, \"unit\": \"forfait\", \"unit_price\": 500, \"line_total\": 25000, \"note\": \"\"}]', '[50, 50]', '[\"exemplaire\", \"forfait\"]', '[0, 0]', '[3500, 500]', 200000, 36000, 0, 236000, 0, 236000, 236000, 0, 0, '2026-10-02', 'Active', 'Acceptée', '2026-10-12', 'Acceptée verbalement — facture à émettre.', 'Interne', '', 0, 'À crédit', NULL, NULL, NULL, 'Devis', '2026-10-14 11:05:50', NULL, NULL),
(5, 'OFF-20261014-0004', 'kemal', 'kemal NDAYISABA', 'Mike MANEKO', 7, '+25767530006', 'Brochure 32 pages', '[{\"stock_id\": null, \"name\": \"Brochure A4 32 pages\", \"quantity\": 500, \"unit\": \"exemplaire\", \"unit_price\": 4500, \"line_total\": 2250000, \"note\": \"Couverture 250g pelliculée, intérieur 135g\"}]', '[500]', '[\"exemplaire\"]', '[0]', '[4500]', 2250000, 405000, 0, 2655000, 0, 2655000, 2655000, 0, 0, '2026-09-24', 'Active', 'Refusée', '2026-10-09', 'Client a préféré un autre prestataire.', 'Interne', '', 0, 'À crédit', NULL, NULL, NULL, 'Devis', '2026-10-14 11:05:50', NULL, NULL),
(6, 'OFF-20261014-0005', 'kemal', 'kemal NDAYISABA', 'Mike MANEKO', 7, '+25767530006', 'Carte de fidélité plastique', '[{\"stock_id\": null, \"name\": \"Carte PVC personnalisée\", \"quantity\": 2000, \"unit\": \"exemplaire\", \"unit_price\": 1200, \"line_total\": 2400000, \"note\": \"Impression quadri + vernis sélectif\"}]', '[2000]', '[\"exemplaire\"]', '[0]', '[1200]', 2400000, 432000, 0, 2832000, 0, 2832000, 2832000, 0, 0, '2026-08-30', 'Active', 'Expirée', '2026-09-29', 'Offre expirée sans réponse.', 'Interne', '', 0, 'À crédit', NULL, NULL, NULL, 'Devis', '2026-10-14 11:05:51', NULL, NULL),
(7, 'OFF-20261014-0006', 'kemal', 'kemal NDAYISABA', 'Mike MANEKO', 7, '+25767530006', 'Pack enseigne magasin', '[{\"stock_id\": null, \"name\": \"Enseigne lumineuse 3×0.8 m\", \"quantity\": 1, \"unit\": \"pièce\", \"unit_price\": 850000, \"line_total\": 850000, \"note\": \"LED + structure alu\"}, {\"stock_id\": null, \"name\": \"Vinyle adhésif vitrine\", \"quantity\": 8, \"unit\": \"m²\", \"unit_price\": 18000, \"line_total\": 144000, \"note\": \"Découpe + pose\"}]', '[1, 8]', '[\"pièce\", \"m²\"]', '[0, 0]', '[850000, 18000]', 994000, 178920, 0, 1172920, 0, 1172920, 1172920, 50000, 0, '2026-10-11', 'Active', 'Acceptée', '2026-10-26', 'Remise commerciale accordée au client VIP.', 'Interne', '', 0, 'À crédit', NULL, NULL, NULL, 'Devis', '2026-10-14 11:05:51', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `sales_merge_backup`
--

CREATE TABLE `sales_merge_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `invoice_number` varchar(100) NOT NULL,
  `operator` varchar(255) NOT NULL,
  `agent` varchar(255) DEFAULT NULL,
  `client_name` varchar(255) NOT NULL,
  `client_id` int(100) DEFAULT NULL COMMENT 'Réf partners.id',
  `phone` varchar(15) DEFAULT '',
  `medicines` text NOT NULL,
  `products_json` text DEFAULT NULL COMMENT 'JSON détaillé des produits (réf stock.id)',
  `quantity` text NOT NULL,
  `units_json` text DEFAULT NULL COMMENT 'JSON des unités utilisées',
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
  `date` date NOT NULL DEFAULT curdate(),
  `status` enum('Active','Supprimée') DEFAULT 'Active',
  `origin` varchar(50) NOT NULL DEFAULT 'Interne',
  `guard` varchar(500) NOT NULL DEFAULT '',
  `netcash` decimal(10,0) NOT NULL DEFAULT 0,
  `payment_method` varchar(200) NOT NULL DEFAULT 'Espèces',
  `ext_paid_amount` decimal(10,0) DEFAULT NULL,
  `ext_paid_status` varchar(50) DEFAULT NULL,
  `cashier` varchar(255) DEFAULT NULL,
  `document_type` enum('Facture','Proforma','Devis','Commande','Livraison') DEFAULT 'Facture',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `machine_id` int(11) DEFAULT NULL COMMENT 'Machine utilisée (services impression)',
  `delivery_status` enum('En préparation','Expédiée','Livrée','Annulée') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `sales_merge_backup`
--

INSERT INTO `sales_merge_backup` (`id`, `invoice_number`, `operator`, `agent`, `client_name`, `client_id`, `phone`, `medicines`, `products_json`, `quantity`, `units_json`, `unit_cost`, `actual_price`, `tot_htva`, `tva`, `tva_paid`, `tot_tvac`, `debt`, `total_amount`, `total_profit`, `discount`, `commission`, `date`, `status`, `origin`, `guard`, `netcash`, `payment_method`, `ext_paid_amount`, `ext_paid_status`, `cashier`, `document_type`, `created_at`, `machine_id`, `delivery_status`) VALUES
(1, 'CLI-20261015-6E5B4B8D', 'kamana', 'KAMANA Holy', 'Nininahazwe Ladouce', 8, '76000045', 'produit1test', '[{\"stock_id\":6,\"name\":\"produit1test\",\"quantity\":17000,\"unit\":\"piece\",\"unit_cost\":69343,\"unit_price\":0,\"line_total\":0}]', '[17000]', '[\"piece\"]', '[69343]', '[0]', 0, 0, 0, 0, 0, 0, -1178831000, 0, 0, '2026-10-15', 'Active', 'Sortie client magasin', '', 0, 'À facturer', NULL, NULL, NULL, 'Commande', '2026-10-15 13:06:41', NULL, NULL),
(2, 'CLI-20261015-C2853178', 'kamana', 'KAMANA Holy', 'Mike MANEKO', 7, '+25767530006', 'produit1test', '[{\"stock_id\":6,\"name\":\"produit1test\",\"quantity\":499,\"unit\":\"piece\",\"unit_cost\":69343,\"unit_price\":0,\"line_total\":0}]', '[499]', '[\"piece\"]', '[69343]', '[0]', 0, 0, 0, 0, 0, 0, -34602157, 0, 0, '2026-10-15', 'Active', 'Sortie client magasin', '', 0, 'À facturer', NULL, NULL, NULL, 'Commande', '2026-10-15 13:24:48', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `sell_type`
--

CREATE TABLE `sell_type` (
  `id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `adder` varchar(100) NOT NULL,
  `cat_date` date NOT NULL DEFAULT curdate(),
  `status` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `sell_type`
--

INSERT INTO `sell_type` (`id`, `category`, `adder`, `cat_date`, `status`) VALUES
(34, 'm', 'Isabelle', '2026-10-12', 1),
(35, 'piece', 'Isabelle', '2026-10-12', 1),
(36, 'rouleau', 'Isabelle', '2026-10-12', 1),
(37, 'Forfait', 'Isabelle', '2026-10-12', 1),
(38, 'kg', 'system', '2026-10-13', 1),
(39, 'g', 'system', '2026-10-13', 1),
(40, 'L', 'system', '2026-10-13', 1),
(41, 'mL', 'system', '2026-10-13', 1),
(42, 'm²', 'system', '2026-10-13', 1),
(43, 'mètre linéaire', 'system', '2026-10-13', 1),
(44, 'feuille', 'system', '2026-10-13', 1),
(45, 'rame', 'system', '2026-10-13', 1),
(46, 'plaque', 'system', '2026-10-13', 1),
(47, 'cartouche', 'system', '2026-10-13', 1),
(48, 'exemplaire', 'system', '2026-10-13', 1),
(49, 'lot', 'system', '2026-10-13', 1),
(50, 'kit', 'system', '2026-10-13', 1),
(51, 'heure', 'system', '2026-10-13', 1),
(52, 'heure machine', 'system', '2026-10-13', 1),
(53, 'carton', 'system', '2026-10-13', 1),
(54, 'palette', 'system', '2026-10-13', 1);

-- --------------------------------------------------------

--
-- Structure de la table `sett`
--

CREATE TABLE `sett` (
  `id` int(11) NOT NULL,
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
  `filer` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `sett`
--

INSERT INTO `sett` (`id`, `delivery_adress`, `delivery_time`, `delivery_cost`, `payment_time`, `payment_mode`, `payment_conditions`, `property_mention`, `legal_mentions`, `conformity_mention`, `guard_amount`, `authorizer`, `filer`) VALUES
(1, 'Bujumbura', '7 jours', '10.000FBu', '30 jours', 'Virement bancaire / BANCOBU', 'Livraison partielle ou complete; Poids:....', 'Camion, moto, velo, pied', 'Nouvelles mentions speciales', 'Marchandises conformes à la celles commandées.', '5000', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `stock`
--

CREATE TABLE `stock` (
  `id` int(100) NOT NULL,
  `reference` varchar(50) DEFAULT NULL,
  `barcode` varchar(50) DEFAULT NULL,
  `bar_code` int(11) NOT NULL DEFAULT 0,
  `medicine_name` varchar(100) NOT NULL,
  `color` varchar(10) NOT NULL DEFAULT '#000000',
  `category` varchar(255) NOT NULL,
  `sub_category_id` int(11) DEFAULT NULL,
  `shelf` text DEFAULT NULL,
  `registered_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `used_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `actual_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `register_date` varchar(100) NOT NULL,
  `expire_date` varchar(100) DEFAULT NULL,
  `sell_type` varchar(100) NOT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `purchase_unit_id` int(11) DEFAULT NULL,
  `sale_unit_id` int(11) DEFAULT NULL,
  `origin` varchar(50) NOT NULL DEFAULT '',
  `quality` varchar(50) NOT NULL DEFAULT 'Standard',
  `actual_price` decimal(10,0) NOT NULL DEFAULT 0,
  `selling_price` decimal(10,0) NOT NULL DEFAULT 0,
  `profit_price` decimal(10,0) NOT NULL DEFAULT 0,
  `status` varchar(100) NOT NULL DEFAULT 'Disponible',
  `inventory` decimal(10,2) NOT NULL DEFAULT 0.00,
  `storage_conditions` varchar(255) NOT NULL DEFAULT '',
  `profil` varchar(100) NOT NULL DEFAULT '',
  `guard` decimal(10,0) NOT NULL DEFAULT 0,
  `requisition` decimal(10,2) DEFAULT 0.00,
  `seuil` decimal(10,0) NOT NULL DEFAULT 3 COMMENT 'Seuil alerte stock',
  `totalPrice` decimal(15,0) GENERATED ALWAYS AS (`actual_quantity` * `actual_price`) STORED,
  `low_stock_alert` tinyint(1) DEFAULT 0,
  `expiring_alert` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `grammage` int(11) DEFAULT NULL COMMENT 'g/m² pour papier',
  `format` varchar(50) DEFAULT NULL COMMENT 'A4, A3, SRA3...',
  `dimensions_mm` varchar(50) DEFAULT NULL,
  `laize` decimal(10,2) DEFAULT NULL COMMENT 'Largeur rouleau (m)',
  `thickness_mm` decimal(10,2) DEFAULT NULL COMMENT 'Épaisseur (mm)',
  `ink_color` varchar(50) DEFAULT NULL COMMENT 'Couleur encre',
  `support_type` varchar(100) DEFAULT NULL,
  `finish_type` varchar(100) DEFAULT NULL COMMENT 'Brillant, mat, soft touch',
  `brand` varchar(100) DEFAULT NULL,
  `supplier_id` int(100) DEFAULT NULL COMMENT 'Réf partners.id',
  `location` varchar(100) DEFAULT NULL COMMENT 'Emplacement entrepôt',
  `min_stock_alert` decimal(10,2) DEFAULT 0.00 COMMENT 'Seuil alerte',
  `reorder_level` decimal(10,2) DEFAULT 0.00 COMMENT 'Seuil réappro',
  `purchase_price` decimal(15,2) DEFAULT 0.00,
  `photo_url` varchar(500) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `stock`
--

INSERT INTO `stock` (`id`, `reference`, `barcode`, `bar_code`, `medicine_name`, `color`, `category`, `sub_category_id`, `shelf`, `registered_quantity`, `used_quantity`, `actual_quantity`, `register_date`, `expire_date`, `sell_type`, `unit_id`, `purchase_unit_id`, `sale_unit_id`, `origin`, `quality`, `actual_price`, `selling_price`, `profit_price`, `status`, `inventory`, `storage_conditions`, `profil`, `guard`, `requisition`, `seuil`, `low_stock_alert`, `expiring_alert`, `created_at`, `updated_at`, `grammage`, `format`, `dimensions_mm`, `laize`, `thickness_mm`, `ink_color`, `support_type`, `finish_type`, `brand`, `supplier_id`, `location`, `min_stock_alert`, `reorder_level`, `purchase_price`, `photo_url`, `notes`) VALUES
(5, NULL, NULL, 0, 'Lou', '#000000', 'Produit', NULL, '', 41.00, 1.06, 39.94, '2026-10-13', '2026-10-15', 'm²', NULL, NULL, NULL, 'Ajout manuel', 'Standard', 2927, 4000, 1073, 'Disponible', 40.00, '', '', 0, 0.00, 3, 0, 0, '2026-10-13 17:56:32', '2026-10-13 18:03:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, 0.00, NULL, NULL),
(6, NULL, NULL, 0, 'produit1test', '#000000', 'Wliper for 1,9', NULL, '', 17500.00, 17499.00, 1.00, '2026-10-15', '2026-10-23', 'piece', NULL, NULL, NULL, 'Ajout manuel', 'Standard', 69343, 0, -69343, 'Disponible', 5000.00, '', '', 0, 0.00, 100, 0, 0, '2026-10-15 12:44:24', '2026-10-15 13:24:48', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, 0.00, NULL, NULL),
(7, 'PAP-CC-A4', NULL, 0, 'Papier couché brillant A4', '#000000', 'Papiers & Supports', NULL, NULL, 40.00, 0.00, 40.00, '2026-10-14', NULL, 'rame', NULL, NULL, NULL, 'Achat local', 'Premium', 18000, 26000, 8000, 'Disponible', 0.00, '', '', 0, 0.00, 5, 0, 0, '2026-10-14 08:59:42', '2026-10-14 08:59:42', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, 0.00, NULL, NULL),
(8, 'ENC-NOIR', NULL, 0, 'Encre offset noire (kg)', '#000000', 'Encres & Consommables', NULL, NULL, 20.00, 0.00, 20.00, '2026-10-14', NULL, 'kg', NULL, NULL, NULL, 'Import', 'Standard', 25000, 35000, 10000, 'Disponible', 0.00, '', '', 0, 0.00, 3, 0, 0, '2026-10-14 08:59:42', '2026-10-14 08:59:42', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, 0.00, NULL, NULL),
(9, 'ENC-CMYK', NULL, 0, 'Encre offset quadri (kg)', '#000000', 'Encres & Consommables', NULL, NULL, 10.00, 0.00, 10.00, '2026-10-14', NULL, 'kg', NULL, NULL, NULL, 'Import', 'Standard', 32000, 45000, 13000, 'Disponible', 0.00, '', '', 0, 0.00, 2, 0, 0, '2026-10-14 08:59:42', '2026-10-14 08:59:42', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, 0.00, NULL, NULL),
(10, 'PLQ-CTP', NULL, 0, 'Plaque CTP offset', '#000000', 'Plaques & Formes', NULL, NULL, 80.00, 0.00, 80.00, '2026-10-14', NULL, 'plaque', NULL, NULL, NULL, 'Import', 'Standard', 12000, 18000, 6000, 'Disponible', 0.00, '', '', 0, 0.00, 10, 0, 0, '2026-10-14 08:59:42', '2026-10-14 08:59:42', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, 0.00, NULL, NULL),
(11, 'GDF-BACHE', NULL, 0, 'Bâche publicitaire (m²)', '#000000', 'Grand Format', NULL, NULL, 200.00, 2.00, 198.00, '2026-10-14', NULL, 'm²', NULL, NULL, NULL, 'Achat local', 'Standard', 6000, 10000, 4000, 'Disponible', 0.00, '', '', 0, 0.00, 20, 0, 0, '2026-10-14 08:59:42', '2026-10-14 09:45:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, 0.00, NULL, NULL),
(12, 'SRV-IMPR', NULL, 0, 'Service impression (heure)', '#000000', 'Services Impression', NULL, NULL, 100.00, 0.00, 100.00, '2026-10-14', NULL, 'heure', NULL, NULL, NULL, 'Interne', 'Standard', 0, 25000, 25000, 'Disponible', 0.00, '', '', 0, 0.00, 5, 0, 0, '2026-10-14 08:59:42', '2026-10-14 08:59:42', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, 0.00, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `stock_movements`
--

INSERT INTO `stock_movements` (`id`, `product_id`, `product_name`, `movement_type`, `quantity`, `quantity_before`, `quantity_after`, `unit_cost`, `total_amount`, `reference`, `reason`, `operator`, `created_at`) VALUES
(5, 5, 'Lou', 'entree', 40.00, 0.00, 40.00, 3000, 120000, NULL, 'Stock initial à la création du produit', 'KAMANA Holy', '2026-10-13 17:56:32'),
(6, 5, 'Lou', 'entree', 1.00, 40.00, 41.00, 0, 0, NULL, 'Réception fournisseur', 'KAMANA Holy', '2026-10-13 17:57:23'),
(7, 5, 'Lou', 'sortie', 1.06, 41.00, 39.94, 2927, 3103, 'ew', 'wd', 'KAMANA Holy', '2026-10-13 18:03:36'),
(8, 6, 'produit1test', 'entree', 5000.00, 0.00, 5000.00, 0, 0, NULL, 'Stock initial à la création du produit', 'KAMANA Holy', '2026-10-15 12:44:24'),
(9, 6, 'produit1test', 'entree', 4500.00, 5000.00, 9500.00, 3000, 13500000, 'REC-20261015-42545E74', 'Réception fournisseur; Réception fournisseur; Fournisseur: KIM FLY TECH', 'KAMANA Holy', '2026-10-15 12:48:10'),
(10, 6, 'produit1test', 'entree', 8000.00, 9500.00, 17500.00, 150000, 1200000000, 'REC-20261015-F8203492', 'Réception fournisseur; Réception fournisseur; Fournisseur: KIM FLY TECH', 'KAMANA Holy', '2026-10-15 12:49:49'),
(11, 6, 'produit1test', 'sortie', 17000.00, 17500.00, 500.00, 69343, 1178831000, 'CLI-20261015-6E5B4B8D', 'Sortie client à facturer; Atelier: KIRIRI Garden; Bénéficiaire: 12; Motif: Papier et autre matériel des empirements; Réf. travail: 334', 'KAMANA Holy', '2026-10-15 13:06:41'),
(12, 6, 'produit1test', 'sortie', 499.00, 500.00, 1.00, 69343, 34602157, 'CLI-20261015-C2853178', 'Sortie client à facturer; Atelier: KIRIRI Garden; Bénéficiaire: Test; Motif: Commande experts; Réf. travail: —', 'KAMANA Holy', '2026-10-15 13:24:48');

-- --------------------------------------------------------

--
-- Structure de la table `sub_categories`
--

CREATE TABLE `sub_categories` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL COMMENT 'Réf cat_prod.id',
  `code` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `base_unit_id` int(11) DEFAULT NULL COMMENT 'Réf units.id',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `sub_categories`
--

INSERT INTO `sub_categories` (`id`, `category_id`, `code`, `name`, `description`, `base_unit_id`, `status`, `created_at`) VALUES
(1, 137, 'PAP_OFFSET', 'Papier offset (rames & palettes)', NULL, 19, 1, '2026-10-13 14:51:35'),
(2, 137, 'PAP_COUCHE', 'Papier couché / brillant', NULL, 19, 1, '2026-10-13 14:51:35'),
(3, 137, 'PAP_CARTON', 'Carton & bristol', NULL, 19, 1, '2026-10-13 14:51:35'),
(4, 137, 'PAP_ADHESIF', 'Papier autocollant (adhésif)', NULL, 16, 1, '2026-10-13 14:51:35'),
(5, 137, 'PAP_SPECIAL', 'Papier spécial (créatif, texture, recyclé)', NULL, 19, 1, '2026-10-13 14:51:35'),
(6, 138, 'ENC_OFFSET', 'Encres offset (CMYK + Pantone)', NULL, 3, 1, '2026-10-13 14:51:35'),
(7, 138, 'ENC_NUM', 'Encres numériques (toner/jet d\'encre)', NULL, 27, 1, '2026-10-13 14:51:35'),
(8, 138, 'ENC_GDF', 'Encres grand format (solvant, UV, latex)', NULL, 7, 1, '2026-10-13 14:51:35'),
(9, 138, 'ENC_CHIM', 'Produits chimiques (solvants, nettoyants)', NULL, 7, 1, '2026-10-13 14:51:35'),
(10, 139, 'PLQ_OFFSET', 'Plaques offset (CIP, analogique)', NULL, 26, 1, '2026-10-13 14:51:35'),
(11, 139, 'PLQ_CTP', 'Plaques numériques (CTP)', NULL, 26, 1, '2026-10-13 14:51:35'),
(12, 139, 'PLQ_FLEXO', 'Formes flexo / sérigraphie', NULL, 17, 1, '2026-10-13 14:51:35'),
(13, 140, 'GDF_BACHE', 'Bâches & Toiles', NULL, 16, 1, '2026-10-13 14:51:35'),
(14, 140, 'GDF_VINYLE', 'Vinyle adhésif', NULL, 16, 1, '2026-10-13 14:51:35'),
(15, 140, 'GDF_PANNEAU', 'Panneaux rigides (Forex, PVC, Alu, Dibond)', NULL, 17, 1, '2026-10-13 14:51:35'),
(16, 140, 'GDF_TOILE', 'Toiles tendues / Textiles', NULL, 16, 1, '2026-10-13 14:51:35'),
(17, 141, 'FIN_PELL', 'Pelliculage & Vernissage', NULL, 16, 1, '2026-10-13 14:51:35'),
(18, 141, 'FIN_RELIURE', 'Reliure & Assemblage', NULL, 17, 1, '2026-10-13 14:51:35'),
(19, 141, 'FIN_DECOUPE', 'Découpe & Forme', NULL, 17, 1, '2026-10-13 14:51:35'),
(20, 141, 'FIN_DORURE', 'Dorure & Marquage à chaud', NULL, 17, 1, '2026-10-13 14:51:35'),
(21, 142, 'PRD_COMM', 'Imprimés commerciaux', NULL, 18, 1, '2026-10-13 14:51:35'),
(22, 142, 'PRD_PACK', 'Packaging & Étiquettes', NULL, 17, 1, '2026-10-13 14:51:35'),
(23, 142, 'PRD_PLV', 'Signalétique & PLV', NULL, 17, 1, '2026-10-13 14:51:35'),
(24, 142, 'PRD_LIVRE', 'Livres & Éditions', NULL, 18, 1, '2026-10-13 14:51:35'),
(25, 143, 'MCH_OFFSET', 'Presses offset', NULL, 17, 1, '2026-10-13 14:51:35'),
(26, 143, 'MCH_NUM', 'Presses numériques', NULL, 17, 1, '2026-10-13 14:51:35'),
(27, 143, 'MCH_GDF', 'Imprimantes grand format', NULL, 17, 1, '2026-10-13 14:51:35'),
(28, 143, 'MCH_PIECE', 'Pièces détachées', NULL, 17, 1, '2026-10-13 14:51:35'),
(29, 144, 'SRV_PREPRESSE', 'Pré-presse (PAO, CTP)', NULL, 31, 1, '2026-10-13 14:51:35'),
(30, 144, 'SRV_IMPRESSION', 'Impression', NULL, 32, 1, '2026-10-13 14:51:35'),
(31, 144, 'SRV_FINITION', 'Finition & Livraison', NULL, 34, 1, '2026-10-13 14:51:35'),
(32, 145, 'LOG_EMBALLAGE', 'Emballages (cartons, films, palettes)', NULL, 17, 1, '2026-10-13 14:51:35'),
(33, 145, 'LOG_CONSOMMABLE', 'Consommables d\'atelier', NULL, 17, 1, '2026-10-13 14:51:35'),
(34, 146, 'CHM_SOLVANT', 'Solvants & nettoyants', NULL, 7, 1, '2026-10-13 14:51:35');

-- --------------------------------------------------------

--
-- Structure de la table `sys_control`
--

CREATE TABLE `sys_control` (
  `id` int(11) NOT NULL,
  `last_time` datetime DEFAULT NULL,
  `last_check_ts` bigint(20) DEFAULT NULL,
  `version` varchar(20) DEFAULT '2.0',
  `db_version` varchar(20) DEFAULT '2.0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `sys_control`
--

INSERT INTO `sys_control` (`id`, `last_time`, `last_check_ts`, `version`, `db_version`) VALUES
(1, '2026-10-12 21:27:21', 1791840441, '2.0', '2.0');

-- --------------------------------------------------------

--
-- Structure de la table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `ticket_no` varchar(50) NOT NULL COMMENT 'Format: VNT-202606-001',
  `client` varchar(255) NOT NULL,
  `client_id` int(100) DEFAULT NULL,
  `walk_in` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Client de passage non enregistré',
  `client_reference` varchar(100) DEFAULT NULL COMMENT 'Référence client (PO, réf. téléphonique…)',
  `category` varchar(100) NOT NULL,
  `priority` enum('Basse','Moyenne','Haute','Critique') DEFAULT 'Moyenne',
  `created_by` varchar(25) NOT NULL,
  `assigned_to` varchar(25) NOT NULL,
  `contributor` varchar(25) DEFAULT NULL,
  `created_at` date NOT NULL DEFAULT curdate(),
  `order_date` date DEFAULT NULL COMMENT 'Date de la commande (peut différer de created_at)',
  `updated_at` datetime DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `delivery_address` varchar(255) DEFAULT NULL COMMENT 'Adresse de livraison',
  `status` enum('Nouveau','Consulté','En cours','Résolu','Annulé') NOT NULL DEFAULT 'Nouveau',
  `delivery_status` enum('En préparation','Expédiée','Livrée','Annulée') NOT NULL DEFAULT 'En préparation',
  `invoice_number` varchar(100) DEFAULT NULL COMMENT 'Numéro de facture si la commande a été convertie',
  `converted_at` datetime DEFAULT NULL COMMENT 'Horodatage de conversion en facture',
  `description` text DEFAULT NULL,
  `items_json` text DEFAULT NULL COMMENT 'JSON détaillé des lignes commandées',
  `subtotal` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Sous-total HT avant remise',
  `discount_type` enum('Aucune','Pourcentage','Montant fixe') NOT NULL DEFAULT 'Aucune',
  `discount_value` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Valeur saisie (%, ou montant fixe)',
  `discount_amount` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Remise calculée en FBu',
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 18.00 COMMENT 'Taux TVA appliqué (%)',
  `tax_amount` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Montant TVA en FBu',
  `total_amount` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Total TTC',
  `payment_terms` varchar(50) DEFAULT NULL COMMENT 'À la commande, À la livraison, 30 jours, À crédit',
  `deposit_amount` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Acompte reçu en FBu',
  `deposit_method` varchar(50) DEFAULT NULL COMMENT 'Espèces, Virement bancaire',
  `bank_id` int(11) DEFAULT NULL COMMENT 'Réf banks.id si virement',
  `sound_status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `tasks`
--

INSERT INTO `tasks` (`id`, `ticket_no`, `client`, `client_id`, `walk_in`, `client_reference`, `category`, `priority`, `created_by`, `assigned_to`, `contributor`, `created_at`, `order_date`, `updated_at`, `due_date`, `delivery_address`, `status`, `delivery_status`, `invoice_number`, `converted_at`, `description`, `items_json`, `subtotal`, `discount_type`, `discount_value`, `discount_amount`, `tax_rate`, `tax_amount`, `total_amount`, `payment_terms`, `deposit_amount`, `deposit_method`, `bank_id`, `sound_status`) VALUES
(1, 'CMD-20261014-F15A86', 'Odon', 8, 0, 'BON DE COMMANDE', 'Commande', 'Basse', 'kemal', 'ishimwe', NULL, '2026-10-14', '2026-10-14', '2026-10-14 11:02:45', '2026-10-15', 'Kanyosha', 'Résolu', 'Livrée', NULL, NULL, 'Hello hello hello', '[{\"stock_id\":5,\"name\":\"Papier A4 80g (rame)\",\"quantity\":1,\"unit\":\"rame\",\"unit_price\":12000,\"line_total\":12000,\"note\":\"good one\"}]', 12000, 'Aucune', 0.00, 0, 18.00, 2160, 14160, 'À la commande', 0, 'Espèces', NULL, 1),
(2, 'CMD-20261014-8B5B51', 'Odon', 8, 0, 'BON DE COMMANDE', 'Commande', 'Moyenne', 'kemal', 'ishimwe', NULL, '2026-10-14', '2026-10-14', '2026-10-14 11:45:17', '2026-10-31', 'Kanyosha', 'Résolu', 'Livrée', 'FCT-20261014-542B8B06', '2026-10-14 11:45:17', 'dfsgsdf', '[{\"stock_id\":11,\"name\":\"Bâche publicitaire (m²)\",\"quantity\":2,\"unit\":\"m²\",\"unit_price\":10000,\"line_total\":20000,\"note\":\"ddddddddddghfh\"}]', 20000, 'Montant fixe', 10000.00, 10000, 18.00, 1800, 11800, 'À la commande', 0, 'Espèces', NULL, 1);

-- --------------------------------------------------------

--
-- Structure de la table `tasks_merge_backup`
--

CREATE TABLE `tasks_merge_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `ticket_no` varchar(50) NOT NULL COMMENT 'Format: VNT-202606-001',
  `client` varchar(255) NOT NULL,
  `client_id` int(100) DEFAULT NULL,
  `category` varchar(100) NOT NULL,
  `priority` enum('Basse','Moyenne','Haute','Critique') DEFAULT 'Moyenne',
  `created_by` varchar(25) NOT NULL,
  `assigned_to` varchar(25) NOT NULL,
  `contributor` varchar(25) DEFAULT NULL,
  `created_at` date NOT NULL DEFAULT curdate(),
  `updated_at` datetime DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('Nouveau','Consulté','En cours','Résolu','Annulé') NOT NULL DEFAULT 'Nouveau',
  `description` text DEFAULT NULL,
  `sound_status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `temp`
--

CREATE TABLE `temp` (
  `id` int(11) NOT NULL,
  `doc_number` varchar(100) NOT NULL,
  `doc_type` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `action_type` varchar(100) NOT NULL,
  `ref_id` varchar(25) DEFAULT NULL,
  `montant` varchar(255) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` enum('entrée','sortie') DEFAULT NULL,
  `status` enum('Active','Supprimée') NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `action_type`, `ref_id`, `montant`, `date`, `type`, `status`) VALUES
(1, 'kemal', 'Paiement partiel facture', 'FCT-20261014-542B8B06', '5000', '2026-10-14 10:33:36', 'entrée', 'Active');

-- --------------------------------------------------------

--
-- Structure de la table `units`
--

CREATE TABLE `units` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `label` varchar(100) NOT NULL,
  `symbol` varchar(20) DEFAULT NULL,
  `type_unit` enum('masse','volume','longueur','surface','quantite','temps','forfait') NOT NULL,
  `base_unit_id` int(11) DEFAULT NULL,
  `conversion_factor` decimal(15,6) DEFAULT 1.000000,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `units`
--

INSERT INTO `units` (`id`, `code`, `label`, `symbol`, `type_unit`, `base_unit_id`, `conversion_factor`, `status`, `created_at`) VALUES
(1, 'MG', 'Milligramme', 'mg', 'masse', NULL, 0.000001, 1, '2026-10-13 14:51:35'),
(2, 'G', 'Gramme', 'g', 'masse', NULL, 0.001000, 1, '2026-10-13 14:51:35'),
(3, 'KG', 'Kilogramme', 'kg', 'masse', NULL, 1.000000, 1, '2026-10-13 14:51:35'),
(4, 'T', 'Tonne', 't', 'masse', NULL, 1000.000000, 1, '2026-10-13 14:51:35'),
(5, 'ML', 'Millilitre', 'mL', 'volume', NULL, 0.001000, 1, '2026-10-13 14:51:35'),
(6, 'CL', 'Centilitre', 'cL', 'volume', NULL, 0.010000, 1, '2026-10-13 14:51:35'),
(7, 'L', 'Litre', 'L', 'volume', NULL, 1.000000, 1, '2026-10-13 14:51:35'),
(8, 'M3', 'Mètre cube', 'm³', 'volume', NULL, 1000.000000, 1, '2026-10-13 14:51:35'),
(9, 'MM', 'Millimètre', 'mm', 'longueur', NULL, 0.001000, 1, '2026-10-13 14:51:35'),
(10, 'CM', 'Centimètre', 'cm', 'longueur', NULL, 0.010000, 1, '2026-10-13 14:51:35'),
(11, 'M', 'Mètre', 'm', 'longueur', NULL, 1.000000, 1, '2026-10-13 14:51:35'),
(12, 'MLIN', 'Mètre linéaire', 'ml', 'longueur', NULL, 1.000000, 1, '2026-10-13 14:51:35'),
(13, 'KM', 'Kilomètre', 'km', 'longueur', NULL, 1000.000000, 1, '2026-10-13 14:51:35'),
(14, 'IN', 'Pouce', 'in', 'longueur', NULL, 0.025400, 1, '2026-10-13 14:51:35'),
(15, 'CM2', 'Centimètre carré', 'cm²', 'surface', NULL, 0.000100, 1, '2026-10-13 14:51:35'),
(16, 'M2', 'Mètre carré', 'm²', 'surface', NULL, 1.000000, 1, '2026-10-13 14:51:35'),
(17, 'PCE', 'Pièce', 'pce', 'quantite', NULL, 1.000000, 1, '2026-10-13 14:51:35'),
(18, 'EX', 'Exemplaire', 'ex', 'quantite', NULL, 1.000000, 1, '2026-10-13 14:51:35'),
(19, 'FF', 'Feuille', 'ff', 'quantite', NULL, 1.000000, 1, '2026-10-13 14:51:35'),
(20, 'RAME', 'Rame', 'rame', 'quantite', NULL, 500.000000, 1, '2026-10-13 14:51:35'),
(21, 'CTN', 'Carton', 'ctn', 'quantite', NULL, 1.000000, 1, '2026-10-13 14:51:35'),
(22, 'PAL', 'Palette', 'pal', 'quantite', NULL, 1.000000, 1, '2026-10-13 14:51:35'),
(23, 'ROULEAU', 'Rouleau', 'rouleau', 'quantite', NULL, 1.000000, 1, '2026-10-13 14:51:35'),
(24, 'LOT', 'Lot', 'lot', 'quantite', NULL, 1.000000, 1, '2026-10-13 14:51:35'),
(25, 'KIT', 'Kit', 'kit', 'quantite', NULL, 1.000000, 1, '2026-10-13 14:51:35'),
(26, 'PLQ', 'Plaque', 'plq', 'quantite', NULL, 1.000000, 1, '2026-10-13 14:51:35'),
(27, 'CART', 'Cartouche', 'cart', 'quantite', NULL, 1.000000, 1, '2026-10-13 14:51:35'),
(28, 'BOITE', 'Boîte', 'boîte', 'quantite', NULL, 1.000000, 1, '2026-10-13 14:51:35'),
(29, 'SACHET', 'Sachet', 'sachet', 'quantite', NULL, 1.000000, 1, '2026-10-13 14:51:35'),
(30, 'MIN', 'Minute', 'min', 'temps', NULL, 0.016667, 1, '2026-10-13 14:51:35'),
(31, 'H', 'Heure', 'h', 'temps', NULL, 1.000000, 1, '2026-10-13 14:51:35'),
(32, 'HM', 'Heure machine', 'hm', 'temps', NULL, 1.000000, 1, '2026-10-13 14:51:35'),
(33, 'J', 'Jour', 'j', 'temps', NULL, 8.000000, 1, '2026-10-13 14:51:35'),
(34, 'FORFAIT', 'Forfait', 'forfait', 'forfait', NULL, 1.000000, 1, '2026-10-13 14:51:35');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(100) NOT NULL,
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
  `admission` date NOT NULL DEFAULT curdate(),
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT current_timestamp(),
  `jwt_secret` varchar(255) DEFAULT NULL COMMENT 'Secret personnel pour JWT',
  `last_login` datetime DEFAULT NULL,
  `failed_attempts` int(11) DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `profil`, `user_name`, `password`, `secret_code`, `category`, `f_name`, `l_name`, `telephone`, `email`, `adress`, `level`, `salary`, `status`, `session_id`, `admission`, `reset_token`, `token_expiry`, `jwt_secret`, `last_login`, `failed_attempts`, `locked_until`, `created_at`, `updated_at`) VALUES
(1, '', 'admin', '$2a$12$KzBU76x51ZEI6NtR9FvLzePaB3xy57aT8y356n4V.qoV3eDQbkp3i', NULL, 'Super Admin', 'Super', 'Admin', '00000000', 'admin@dh.bi', '', 'Dev', 0, 'Disponible', '7ce777bed0bd6263bac77eb06455f3e7', '2026-10-12', NULL, '2026-10-12 21:27:21', NULL, '2026-10-13 17:42:05', 0, NULL, '2026-10-12 21:27:21', '2026-10-13 15:42:05'),
(2, '', 'kemal', '$2y$10$3/Nxsl7diaGEzC9x.ayXB.wKujk2iVBVCf48KHTb92PlNAvC9zblG', NULL, 'receptionniste', 'kemal', 'NDAYISABA', '+25767530006', 'kemal@dh.bi', '', 'Réceptionniste', 0, 'Disponible', 'dc4fd6d9d6f6a011815cb27cd0fc86cc', '2026-10-13', NULL, '2026-10-13 01:18:01', NULL, '2026-10-15 15:45:59', 0, NULL, '2026-10-12 23:18:01', '2026-10-15 13:45:59'),
(3, '/tunganya/uploads/avatars/avatar_3_1792071343.png', 'kamana', '$2y$10$v0.9/8KVjgt9eY5s5Tx/yeCuiDPZKqNtRsDMBn8serHn7QdLUxMza', NULL, 'magasinier', 'KAMANA', 'Holy', NULL, 'kamana@dh.bi', '', 'Magasinier', 0, 'Disponible', 'f8d60c641468f12e264c548ac160b1a0', '2026-10-13', NULL, '2026-10-13 02:18:07', NULL, '2026-10-15 15:10:36', 0, NULL, '2026-10-13 00:18:07', '2026-10-15 13:35:43');

-- --------------------------------------------------------

--
-- Structure de la table `user_sound_settings`
--

CREATE TABLE `user_sound_settings` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `sound_status` tinyint(1) DEFAULT 1,
  `notification_sound` varchar(100) DEFAULT 'default',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_birthdays_this_month`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_birthdays_this_month` (
`id` int(100)
,`name` varchar(100)
,`phone` varchar(255)
,`email` varchar(255)
,`annif` varchar(10)
,`status_client` enum('Nouveau','Régulier','VIP')
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_daily_kpis`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_daily_kpis` (
`jour` date
,`nb_ventes` bigint(21)
,`ca_total` decimal(41,0)
,`marge_totale` decimal(41,0)
,`dettes_creees` decimal(41,0)
,`tva_collectee` decimal(41,0)
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_low_stock`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_low_stock` (
`id` int(100)
,`medicine_name` varchar(100)
,`category` varchar(255)
,`actual_quantity` decimal(10,2)
,`seuil` decimal(10,0)
,`status` varchar(100)
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_overdue_debts`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_overdue_debts` (
`id` int(11)
,`invoice_number` varchar(100)
,`client_indebted` varchar(255)
,`amount` bigint(20)
,`paid_amount` bigint(20)
,`solde_restant` bigint(21)
,`due_date` date
,`jours_retard` int(7)
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_vip_clients`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_vip_clients` (
`id` int(100)
,`name` varchar(100)
,`phone` varchar(255)
,`email` varchar(255)
,`total_achats` bigint(15)
,`frequence_achats` int(10)
,`dernier_achat` date
);

-- --------------------------------------------------------

--
-- Structure de la vue `v_birthdays_this_month`
--
DROP TABLE IF EXISTS `v_birthdays_this_month`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_birthdays_this_month`  AS SELECT `partners`.`id` AS `id`, `partners`.`name` AS `name`, `partners`.`phone` AS `phone`, `partners`.`email` AS `email`, `partners`.`annif` AS `annif`, `partners`.`status_client` AS `status_client` FROM `partners` WHERE `partners`.`annif` like concat('%-',lpad(month(curdate()),2,'0')) ORDER BY `partners`.`annif` ASC ;

-- --------------------------------------------------------

--
-- Structure de la vue `v_daily_kpis`
--
DROP TABLE IF EXISTS `v_daily_kpis`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_daily_kpis`  AS SELECT cast(`sales`.`date` as date) AS `jour`, count(0) AS `nb_ventes`, sum(`sales`.`total_amount`) AS `ca_total`, sum(`sales`.`total_profit`) AS `marge_totale`, sum(`sales`.`debt`) AS `dettes_creees`, sum(`sales`.`tva_paid`) AS `tva_collectee` FROM `sales` WHERE `sales`.`status` = 'Active' GROUP BY cast(`sales`.`date` as date) ORDER BY cast(`sales`.`date` as date) DESC ;

-- --------------------------------------------------------

--
-- Structure de la vue `v_low_stock`
--
DROP TABLE IF EXISTS `v_low_stock`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_low_stock`  AS SELECT `stock`.`id` AS `id`, `stock`.`medicine_name` AS `medicine_name`, `stock`.`category` AS `category`, `stock`.`actual_quantity` AS `actual_quantity`, `stock`.`seuil` AS `seuil`, `stock`.`status` AS `status` FROM `stock` WHERE `stock`.`actual_quantity` <= `stock`.`seuil` AND `stock`.`status` = 'Disponible' ORDER BY `stock`.`actual_quantity` ASC ;

-- --------------------------------------------------------

--
-- Structure de la vue `v_overdue_debts`
--
DROP TABLE IF EXISTS `v_overdue_debts`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_overdue_debts`  AS SELECT `d`.`id` AS `id`, `d`.`invoice_number` AS `invoice_number`, `d`.`client_indebted` AS `client_indebted`, `d`.`amount` AS `amount`, `d`.`paid_amount` AS `paid_amount`, `d`.`amount`- `d`.`paid_amount` AS `solde_restant`, `d`.`due_date` AS `due_date`, to_days(curdate()) - to_days(`d`.`due_date`) AS `jours_retard` FROM `debts` AS `d` WHERE `d`.`status` <> 1 AND `d`.`due_date` < curdate() ORDER BY to_days(curdate()) - to_days(`d`.`due_date`) DESC ;

-- --------------------------------------------------------

--
-- Structure de la vue `v_vip_clients`
--
DROP TABLE IF EXISTS `v_vip_clients`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_vip_clients`  AS SELECT `partners`.`id` AS `id`, `partners`.`name` AS `name`, `partners`.`phone` AS `phone`, `partners`.`email` AS `email`, `partners`.`total_achats` AS `total_achats`, `partners`.`frequence_achats` AS `frequence_achats`, `partners`.`dernier_achat` AS `dernier_achat` FROM `partners` WHERE `partners`.`status_client` = 'VIP' AND `partners`.`category` = 'client' ORDER BY `partners`.`total_achats` DESC ;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `alerts`
--
ALTER TABLE `alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`alert_type`),
  ADD KEY `idx_read` (`is_read`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_target` (`target_role`);

--
-- Index pour la table `app_settings`
--
ALTER TABLE `app_settings`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `arch_types`
--
ALTER TABLE `arch_types`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `banks`
--
ALTER TABLE `banks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_account` (`account_number`);

--
-- Index pour la table `caisse`
--
ALTER TABLE `caisse`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_date` (`date`);

--
-- Index pour la table `cash_in`
--
ALTER TABLE `cash_in`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_date` (`date_created`);

--
-- Index pour la table `cash_out`
--
ALTER TABLE `cash_out`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `category_attributes`
--
ALTER TABLE `category_attributes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_attr_code` (`code`),
  ADD KEY `idx_cat` (`category_id`),
  ADD KEY `idx_subcat` (`sub_category_id`);

--
-- Index pour la table `cat_departments`
--
ALTER TABLE `cat_departments`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `cat_expens`
--
ALTER TABLE `cat_expens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_category` (`category`);

--
-- Index pour la table `cat_partners`
--
ALTER TABLE `cat_partners`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `cat_prod`
--
ALTER TABLE `cat_prod`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cat_prod_default_unit` (`default_unit_id`);

--
-- Index pour la table `cat_task`
--
ALTER TABLE `cat_task`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `cat_users`
--
ALTER TABLE `cat_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_category` (`category`);

--
-- Index pour la table `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ticket` (`ticket_no`),
  ADD KEY `idx_receiver_status` (`receiver`,`status`);

--
-- Index pour la table `debts`
--
ALTER TABLE `debts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_client` (`client_id`,`client_indebted`),
  ADD KEY `idx_invoice` (`invoice_number`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_due_date` (`due_date`),
  ADD KEY `idx_debts_status_due` (`status`,`due_date`);

--
-- Index pour la table `debts_paid`
--
ALTER TABLE `debts_paid`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_debt` (`debt_id`),
  ADD KEY `idx_client` (`client_name`);

--
-- Index pour la table `deleted_sales`
--
ALTER TABLE `deleted_sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_original` (`original_sale_id`);

--
-- Index pour la table `discounts`
--
ALTER TABLE `discounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice` (`invoice_number`);

--
-- Index pour la table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_doc_number` (`doc_number`),
  ADD KEY `idx_client` (`client_id`,`client_name`),
  ADD KEY `idx_type` (`doc_type`),
  ADD KEY `idx_category` (`category`);

--
-- Index pour la table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_client` (`client_email`);

--
-- Index pour la table `entries`
--
ALTER TABLE `entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product` (`medicine_name`),
  ADD KEY `idx_date` (`register_date`),
  ADD KEY `idx_entries_entry_number` (`entry_number`),
  ADD KEY `idx_entries_product_id` (`product_id`),
  ADD KEY `idx_entries_supplier_id` (`supplier_id`);

--
-- Index pour la table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_date` (`date`),
  ADD KEY `idx_cat` (`expense_cat`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_expenses_date_cat` (`date`,`expense_cat`);

--
-- Index pour la table `external_payments`
--
ALTER TABLE `external_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice` (`invoice_id`);

--
-- Index pour la table `invoice_infos`
--
ALTER TABLE `invoice_infos`
  ADD PRIMARY KEY (`invoice_number`);

--
-- Index pour la table `licenses`
--
ALTER TABLE `licenses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_email` (`email`),
  ADD UNIQUE KEY `uk_license` (`license_key`);

--
-- Index pour la table `logintos`
--
ALTER TABLE `logintos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_date` (`user_id`,`created_at`),
  ADD KEY `idx_action` (`action_type`);

--
-- Index pour la table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_date` (`date`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_module` (`module`);

--
-- Index pour la table `machines`
--
ALTER TABLE `machines`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_machine_code` (`code`),
  ADD KEY `idx_type` (`machine_type`),
  ADD KEY `idx_status` (`status`);

--
-- Index pour la table `machine_maintenance`
--
ALTER TABLE `machine_maintenance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_machine` (`machine_id`),
  ADD KEY `idx_status` (`status`);

--
-- Index pour la table `mutuelle_details`
--
ALTER TABLE `mutuelle_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice` (`invoice_number`);

--
-- Index pour la table `on_exp`
--
ALTER TABLE `on_exp`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_doc` (`doc_number`);

--
-- Index pour la table `on_hold`
--
ALTER TABLE `on_hold`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice` (`invoice_number`);

--
-- Index pour la table `on_order`
--
ALTER TABLE `on_order`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_doc` (`doc_number`);

--
-- Index pour la table `on_quot`
--
ALTER TABLE `on_quot`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_doc` (`doc_number`);

--
-- Index pour la table `partners`
--
ALTER TABLE `partners`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_partners_portal_username` (`portal_username`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status_client` (`status_client`),
  ADD KEY `idx_annif` (`annif`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_partners_vip` (`status_client`,`total_achats`);

--
-- Index pour la table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_token` (`token`);

--
-- Index pour la table `quantity_corrections`
--
ALTER TABLE `quantity_corrections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_corrected_by` (`corrected_by`);

--
-- Index pour la table `report_comments`
--
ALTER TABLE `report_comments`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice` (`invoice_number`),
  ADD KEY `idx_date` (`date`),
  ADD KEY `idx_client` (`client_id`,`client_name`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_type` (`document_type`),
  ADD KEY `idx_sales_date_status` (`date`,`status`),
  ADD KEY `idx_machine` (`machine_id`),
  ADD KEY `idx_offers_status` (`document_type`,`offer_status`),
  ADD KEY `idx_offers_valid` (`valid_until`);

--
-- Index pour la table `sell_type`
--
ALTER TABLE `sell_type`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `sett`
--
ALTER TABLE `sett`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_name` (`medicine_name`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_expire` (`expire_date`),
  ADD KEY `idx_seuil` (`actual_quantity`,`seuil`),
  ADD KEY `idx_stock_alert` (`actual_quantity`,`seuil`,`status`),
  ADD KEY `idx_ref` (`reference`),
  ADD KEY `idx_barcode` (`barcode`),
  ADD KEY `idx_subcat` (`sub_category_id`),
  ADD KEY `idx_unit` (`unit_id`),
  ADD KEY `idx_supplier` (`supplier_id`),
  ADD KEY `fk_stock_purchase_unit` (`purchase_unit_id`),
  ADD KEY `fk_stock_sale_unit` (`sale_unit_id`);

--
-- Index pour la table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_stock_movement_product` (`product_id`),
  ADD KEY `idx_stock_movement_type_date` (`movement_type`,`created_at`);

--
-- Index pour la table `sub_categories`
--
ALTER TABLE `sub_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_subcat_code` (`code`),
  ADD KEY `idx_cat` (`category_id`),
  ADD KEY `idx_unit` (`base_unit_id`);

--
-- Index pour la table `sys_control`
--
ALTER TABLE `sys_control`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_ticket` (`ticket_no`),
  ADD KEY `idx_assigned` (`assigned_to`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_tasks_ticket` (`ticket_no`),
  ADD KEY `idx_tasks_status` (`status`),
  ADD KEY `idx_tasks_assigned` (`assigned_to`,`status`),
  ADD KEY `idx_tasks_category` (`category`),
  ADD KEY `idx_tasks_due` (`due_date`),
  ADD KEY `idx_tasks_client` (`client_id`),
  ADD KEY `fk_tasks_bank` (`bank_id`);

--
-- Index pour la table `temp`
--
ALTER TABLE `temp`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_date` (`date`);

--
-- Index pour la table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_unit_code` (`code`),
  ADD KEY `idx_type` (`type_unit`),
  ADD KEY `idx_base` (`base_unit_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_user_name` (`user_name`),
  ADD UNIQUE KEY `uk_email` (`email`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`);

--
-- Index pour la table `user_sound_settings`
--
ALTER TABLE `user_sound_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_username` (`username`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `alerts`
--
ALTER TABLE `alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `app_settings`
--
ALTER TABLE `app_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `arch_types`
--
ALTER TABLE `arch_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `banks`
--
ALTER TABLE `banks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `caisse`
--
ALTER TABLE `caisse`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `cash_in`
--
ALTER TABLE `cash_in`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `cash_out`
--
ALTER TABLE `cash_out`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `category_attributes`
--
ALTER TABLE `category_attributes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `cat_departments`
--
ALTER TABLE `cat_departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `cat_expens`
--
ALTER TABLE `cat_expens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `cat_partners`
--
ALTER TABLE `cat_partners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `cat_prod`
--
ALTER TABLE `cat_prod`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=152;

--
-- AUTO_INCREMENT pour la table `cat_task`
--
ALTER TABLE `cat_task`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `cat_users`
--
ALTER TABLE `cat_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `chats`
--
ALTER TABLE `chats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `debts`
--
ALTER TABLE `debts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `debts_paid`
--
ALTER TABLE `debts_paid`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `deleted_sales`
--
ALTER TABLE `deleted_sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `discounts`
--
ALTER TABLE `discounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `entries`
--
ALTER TABLE `entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `external_payments`
--
ALTER TABLE `external_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `licenses`
--
ALTER TABLE `licenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `logintos`
--
ALTER TABLE `logintos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT pour la table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `machines`
--
ALTER TABLE `machines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `machine_maintenance`
--
ALTER TABLE `machine_maintenance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `mutuelle_details`
--
ALTER TABLE `mutuelle_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `on_exp`
--
ALTER TABLE `on_exp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `on_hold`
--
ALTER TABLE `on_hold`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `on_order`
--
ALTER TABLE `on_order`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `on_quot`
--
ALTER TABLE `on_quot`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `partners`
--
ALTER TABLE `partners`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `quantity_corrections`
--
ALTER TABLE `quantity_corrections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `report_comments`
--
ALTER TABLE `report_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `sell_type`
--
ALTER TABLE `sell_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT pour la table `sett`
--
ALTER TABLE `sett`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `sub_categories`
--
ALTER TABLE `sub_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT pour la table `sys_control`
--
ALTER TABLE `sys_control`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `temp`
--
ALTER TABLE `temp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `units`
--
ALTER TABLE `units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `user_sound_settings`
--
ALTER TABLE `user_sound_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `category_attributes`
--
ALTER TABLE `category_attributes`
  ADD CONSTRAINT `fk_attr_cat` FOREIGN KEY (`category_id`) REFERENCES `cat_prod` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_attr_subcat` FOREIGN KEY (`sub_category_id`) REFERENCES `sub_categories` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `cat_prod`
--
ALTER TABLE `cat_prod`
  ADD CONSTRAINT `fk_cat_prod_default_unit` FOREIGN KEY (`default_unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `entries`
--
ALTER TABLE `entries`
  ADD CONSTRAINT `fk_entries_product` FOREIGN KEY (`product_id`) REFERENCES `stock` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_entries_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `partners` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `machine_maintenance`
--
ALTER TABLE `machine_maintenance`
  ADD CONSTRAINT `fk_maint_machine` FOREIGN KEY (`machine_id`) REFERENCES `machines` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_reset_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `stock`
--
ALTER TABLE `stock`
  ADD CONSTRAINT `fk_stock_purchase_unit` FOREIGN KEY (`purchase_unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_stock_sale_unit` FOREIGN KEY (`sale_unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_stock_subcat` FOREIGN KEY (`sub_category_id`) REFERENCES `sub_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_stock_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `partners` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_stock_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `sub_categories`
--
ALTER TABLE `sub_categories`
  ADD CONSTRAINT `fk_subcat_cat` FOREIGN KEY (`category_id`) REFERENCES `cat_prod` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_subcat_unit` FOREIGN KEY (`base_unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `fk_tasks_bank` FOREIGN KEY (`bank_id`) REFERENCES `banks` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `units`
--
ALTER TABLE `units`
  ADD CONSTRAINT `fk_unit_base` FOREIGN KEY (`base_unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
