-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 06 oct. 2026 à 11:36
-- Version du serveur : 10.4.24-MariaDB
-- Version de PHP : 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `next_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `app_settings`
--

CREATE TABLE `app_settings` (
  `id` int(11) NOT NULL,
  `license_key` varchar(100) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `app_settings`
--

INSERT INTO `app_settings` (`id`, `license_key`, `status`) VALUES
(1, 'DH-9195-2653', 0);

-- --------------------------------------------------------

--
-- Structure de la table `arch_types`
--

CREATE TABLE `arch_types` (
  `id` int(11) NOT NULL,
  `type` varchar(100) NOT NULL,
  `adder` varchar(100) NOT NULL,
  `cat_date` date NOT NULL DEFAULT current_timestamp(),
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `arch_types`
--

INSERT INTO `arch_types` (`id`, `type`, `adder`, `cat_date`, `status`) VALUES
(3, 'Facture', 'Alexis', '2025-01-05', 1),
(4, 'Bon de Commande', 'Alexis', '2025-01-05', 1),
(5, 'Borderau de  Livraison', 'Alexis', '2025-01-05', 1),
(6, 'Proforma', 'Alexis', '2025-01-06', 1),
(7, 'Autre', 'Alexis', '2025-01-15', 1),
(8, 'Lettre', 'Alexis', '2025-04-18', 1);

-- --------------------------------------------------------

--
-- Structure de la table `banks`
--

CREATE TABLE `banks` (
  `id` int(11) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `account_number` varchar(100) DEFAULT NULL,
  `balance` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `banks`
--

INSERT INTO `banks` (`id`, `bank_name`, `account_number`, `balance`, `created_at`) VALUES
(23, 'CAISSE DH', '-', '0.00', '2026-03-05 14:31:42');

-- --------------------------------------------------------

--
-- Structure de la table `caisse`
--

CREATE TABLE `caisse` (
  `id` int(11) NOT NULL,
  `initial` bigint(10) NOT NULL,
  `montant_in` bigint(10) NOT NULL DEFAULT 0,
  `montant_out` bigint(10) NOT NULL DEFAULT 0,
  `date` date NOT NULL DEFAULT current_timestamp(),
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `balance` decimal(10,0) GENERATED ALWAYS AS (`initial` + `montant_in` - `montant_out`) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `caisse`
--

INSERT INTO `caisse` (`id`, `initial`, `montant_in`, `montant_out`, `date`, `status`) VALUES
(1, 0, 508969, 0, '2026-06-24', 0),
(2, 0, 53000, 371000, '2026-06-25', 0),
(3, 0, 734200, 483300, '2026-06-26', 0),
(4, 0, 2502000, 2200200, '2026-06-29', 0),
(5, 0, 7139650, 7181500, '2026-06-30', 0),
(6, 0, 167200, 573600, '2026-07-02', 0),
(7, 0, 2439762, 2428356, '2026-07-06', 0),
(8, 0, 166820, 151500, '2026-07-07', 0),
(9, 0, 1379430, 837500, '2026-07-08', 0),
(10, 0, 270000, 421900, '2026-07-09', 0),
(11, 0, 548000, 818500, '2026-07-10', 0),
(12, 0, 426250, 650000, '2026-07-13', 0),
(13, 0, 958440, 527000, '2026-07-14', 0),
(14, 0, 1569200, 2071900, '2026-07-15', 0),
(15, 0, 1630000, 776700, '2026-07-16', 0),
(16, 0, 1446400, 888500, '2026-07-17', 0),
(17, 0, 973736, 1829100, '2026-07-20', 0),
(18, 0, 125000, 410300, '2026-07-21', 0),
(19, 0, 437500, 525200, '2026-07-22', 0),
(20, 0, 215100, 259000, '2026-07-23', 0),
(21, 0, 15625900, 15778500, '2026-07-24', 0),
(22, 0, 326800, 191000, '2026-07-25', 0),
(23, 0, 553000, 697500, '2026-07-27', 0),
(24, 0, 1410700, 420500, '2026-07-28', 0),
(25, 0, 273000, 1103500, '2026-07-29', 0),
(26, 0, 343000, 515000, '2026-07-30', 0),
(27, 0, 205000, 122500, '2026-07-31', 0),
(28, 0, 476000, 546700, '2026-08-05', 0),
(29, 0, 191000, 287000, '2026-08-06', 0),
(30, 0, 3151600, 2406500, '2026-08-10', 0),
(31, 0, 644000, 1212840, '2026-08-11', 0),
(32, 0, 1770000, 1419700, '2026-08-18', 0);

-- --------------------------------------------------------

--
-- Structure de la table `cash_in`
--

CREATE TABLE `cash_in` (
  `id` int(11) NOT NULL,
  `trans_motive` varchar(50) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `operator` varchar(100) NOT NULL,
  `date_created` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `cash_in`
--

INSERT INTO `cash_in` (`id`, `trans_motive`, `amount`, `operator`, `date_created`) VALUES
(1, 'Encaissement apres migration db', '508969.00', 'Cynthia', '2026-06-24'),
(2, 'alimentation caisse', '500000.00', 'Cynthia', '2026-06-26'),
(3, 'retrait de la banque le 17/6/2026', '1090000.00', 'Cynthia', '2026-06-30'),
(4, 'retrait de la banque le24/6/2026', '2010000.00', 'Cynthia', '2026-06-30'),
(5, 'retrait de la banque', '3320000.00', 'Cynthia', '2026-06-30'),
(6, 'autre frais disponibles', '56.00', 'Cynthia', '2026-07-06'),
(7, 'autre frais disponibles', '56.00', 'Cynthia', '2026-07-06'),
(8, 'autes frais disponible', '30.00', 'Cynthia', '2026-07-08'),
(9, 'autre frais dispnible', '120.00', 'Cynthia', '2026-07-08'),
(10, 'autre frais', '10.00', 'Cynthia', '2026-07-13'),
(11, 'paiement dette mahoro', '15000.00', 'Cynthia', '2026-07-14'),
(12, 'dette payé Mamert', '1000000.00', 'Cynthia', '2026-07-15'),
(13, 'dette paye', '1100000.00', 'Cynthia', '2026-07-16'),
(14, 'autres frais dispnible', '140.00', 'Cynthia', '2026-07-17'),
(15, 'alimentation caisse', '460000.00', 'Cynthia', '2026-07-17'),
(16, 'FRAIS DISPONIBLE', '36.00', 'Cynthia', '2026-07-20'),
(17, 'retrait de la banque le ', '9800000.00', 'Cynthia', '2026-07-24'),
(18, 'retrait de la banque du 23/7/2026', '5250000.00', 'Cynthia', '2026-07-24'),
(19, 'alimentation caisse', '160000.00', 'Cynthia', '2026-08-06'),
(20, 'prêt ndaha', '50000.00', 'Cynthia', '2026-08-10'),
(21, 'tva pour10m ', '400.00', 'Cynthia', '2026-08-10'),
(22, 'contravision de 3.048', '280.00', 'Cynthia', '2026-08-10'),
(23, 'frais disponible', '28.00', 'Cynthia', '2026-08-18'),
(24, 'autres frais disponibles', '100.00', 'Cynthia', '2026-08-18');

-- --------------------------------------------------------

--
-- Structure de la table `cash_out`
--

CREATE TABLE `cash_out` (
  `id` int(11) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp(),
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `cashout_type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `cat_departments`
--

CREATE TABLE `cat_departments` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `abbreviation` varchar(100) NOT NULL,
  `responsible` varchar(100) NOT NULL,
  `profil` varchar(100) NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  `cat_date` date NOT NULL DEFAULT current_timestamp(),
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `cat_partners`
--

CREATE TABLE `cat_partners` (
  `id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `cat_partners`
--

INSERT INTO `cat_partners` (`id`, `category`, `status`) VALUES
(1, 'Client', 0),
(2, 'Fournisseur', 0);

-- --------------------------------------------------------

--
-- Structure de la table `cat_prod`
--

CREATE TABLE `cat_prod` (
  `id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `adder` varchar(100) NOT NULL,
  `cat_date` date NOT NULL DEFAULT current_timestamp(),
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `cat_prod`
--

INSERT INTO `cat_prod` (`id`, `category`, `adder`, `cat_date`, `status`) VALUES
(135, 'Service', 'Isabelle', '2026-06-24', 1),
(136, 'Produit', 'Isabelle', '2026-06-24', 1);

-- --------------------------------------------------------

--
-- Structure de la table `cat_task`
--

CREATE TABLE `cat_task` (
  `id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `adder` varchar(100) NOT NULL,
  `cat_date` date NOT NULL DEFAULT current_timestamp(),
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `cat_task`
--

INSERT INTO `cat_task` (`id`, `category`, `adder`, `cat_date`, `status`) VALUES
(1, 'Visite', 'Cynthia', '2026-06-10', 1);

-- --------------------------------------------------------

--
-- Structure de la table `cat_users`
--

CREATE TABLE `cat_users` (
  `id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `cat_users`
--

INSERT INTO `cat_users` (`id`, `category`, `status`) VALUES
(1, 'Super Admin', 1),
(2, 'Admin', 1),
(3, 'Utilisateur', 1),
(4, 'agent', 1),
(5, 'Admin', 1),
(6, 'caissier', 1);

-- --------------------------------------------------------

--
-- Structure de la table `chats`
--

CREATE TABLE `chats` (
  `id` int(11) NOT NULL,
  `msg_id` int(25) NOT NULL,
  `sender` varchar(255) NOT NULL,
  `receiver` varchar(255) NOT NULL,
  `msg_content` text NOT NULL,
  `ticket_no` varchar(100) DEFAULT NULL,
  `date_sent` datetime NOT NULL DEFAULT current_timestamp(),
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `chats`
--

INSERT INTO `chats` (`id`, `msg_id`, `sender`, `receiver`, `msg_content`, `ticket_no`, `date_sent`, `status`) VALUES
(1, 0, 'Cynthia', 'Cynthia', 'Conversation démarrée depuis la tâche #1', 'VNT-202606-001', '2026-06-10 09:23:13', 0),
(2, 0, 'Cynthia', 'Cynthia', 'hello\r\n', 'VNT-202606-001', '2026-06-10 09:23:37', 0),
(3, 0, 'Cynthia', 'Cynthia', 'hello\r\n', 'VNT-202606-001', '2026-06-10 09:23:37', 1);

-- --------------------------------------------------------

--
-- Structure de la table `debts`
--

CREATE TABLE `debts` (
  `id` int(11) NOT NULL,
  `amount` bigint(20) DEFAULT NULL,
  `client_indebted` varchar(255) DEFAULT NULL,
  `operator` varchar(255) DEFAULT NULL,
  `date_reported` date NOT NULL DEFAULT current_timestamp(),
  `invoice_number` varchar(100) NOT NULL,
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `debts`
--

INSERT INTO `debts` (`id`, `amount`, `client_indebted`, `operator`, `date_reported`, `invoice_number`, `status`) VALUES
(3, 200, 'Walk-in-customer', 'Cynthia', '2026-05-11', '0010', 0),
(5, 35000, 'dg', 'Cynthia', '2026-05-13', '0024', 0),
(6, 50, 'fao', 'Cynthia', '2026-05-15', '0026', 0),
(10, 840, 'KINGS PRINTING', 'Cynthia', '2026-05-20', '0022', 0),
(11, 100, 'Walk-in-customer', 'Cynthia', '2026-05-20', '0026', 0),
(15, 10, 'Walk-in-customer', 'Cynthia', '2026-05-29', '0076', 0),
(19, 84280, 'edd', 'Cynthia', '2026-06-01', '0093', 0),
(21, 8500, 'KINGS ', 'Cynthia', '2026-06-02', '0096', 0),
(27, 13000, 'ELSHADAI', 'Cynthia', '2026-06-16', '0162', 0),
(30, 5000, 'Walk-in-customer', 'Cynthia', '2026-06-24', '0207', 0),
(32, 2, 'Client de passage', 'Cynthia', '2026-07-06', '0045', 0),
(35, 20000, 'sammy', 'Cynthia', '2026-07-06', '0056', 0),
(43, 445000, 'Client de passage', 'Cynthia', '2026-07-29', '0207', 0),
(47, 118000, 'hamza amababa', 'Cynthia', '2026-08-18', '0271', 0);

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
  `payment_method` varchar(100) NOT NULL,
  `invoice_number` varchar(100) NOT NULL,
  `date` date DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `debts_paid`
--

INSERT INTO `debts_paid` (`id`, `debt_id`, `amount`, `client_name`, `operator`, `payment_method`, `invoice_number`, `date`) VALUES
(2, 7, 40000, '61246433', 'Cynthia', 'Espèces', '0003', '2026-05-20'),
(3, 8, 90000, 'fatiri', 'Cynthia', 'Espèces', '0008', '2026-05-21'),
(4, 13, 3000, 'kings', 'Cynthia', 'Espèces', '0032', '2026-05-22'),
(5, 12, 10000, 'farit', 'Cynthia', 'Espèces', '0028', '2026-05-25'),
(6, 18, 31150, 'KINGS ', 'Cynthia', 'Espèces', '0091', '2026-06-01'),
(7, 17, 24000, 'CFR DON FLEURY', 'Cynthia', 'Espèces', '0090', '2026-06-02'),
(8, 2, 2500, 'cfr don', 'Cynthia', 'Espèces', '0008', '2026-06-02'),
(9, 9, 1600, 'FLEURY', 'Cynthia', 'Espèces', '0021', '2026-06-02'),
(15, 22, 44250, 'METALUSA', 'Cynthia', 'Espèces', '0099', '2026-06-03'),
(16, 14, 200000, 'fanny lavine', 'Cynthia', 'Espèces', '0036', '2026-06-03'),
(17, 20, 2000, 'STAR ', 'Cynthia', 'Espèces', '0094', '2026-06-05'),
(18, 23, 30000, 'KINGS ', 'Cynthia', 'Espèces', '0105', '2026-06-10'),
(19, 24, 240000, 'josette', 'Cynthia', 'Espèces', '0144', '2026-06-12'),
(20, 16, 10000, 'horizon', 'Cynthia', 'Espèces', '0080', '2026-06-13'),
(21, 19, 200000, 'edd', 'Cynthia', 'Espèces', '0093', '2026-06-15'),
(22, 26, 101070, 'DON FLEURY', 'Cynthia', 'Espèces', '0158', '2026-06-16'),
(23, 25, 10000, 'Walk-in-customer', 'Cynthia', 'Espèces', '0156', '2026-06-17'),
(24, 4, 5000, 'JUSTIN', 'Cynthia', 'Espèces', '0012', '2026-06-24'),
(25, 29, 25000, 'HAM', 'Cynthia', 'Espèces', '0194', '2026-06-26'),
(26, 31, 260000, 'séraphine', 'Cynthia', 'Espèces', '0021', '2026-06-30'),
(27, 34, 50000, 'Client de passage', 'Cynthia', 'Espèces', '0053', '2026-07-06'),
(28, 36, 124000, 'Client de passage', 'Cynthia', 'Espèces', '0064', '2026-07-08'),
(29, 33, 758000, 'Client de passage', 'Cynthia', 'Espèces', '0049', '2026-07-08'),
(30, 37, 275000, 'Client de passage', 'Cynthia', 'Espèces', '0105', '2026-07-13'),
(31, 38, 20000, 'Client de passage', 'Cynthia', 'Espèces', '0139', '2026-07-17'),
(32, 39, 370000, 'Client de passage', 'Cynthia', 'Espèces', '0143', '2026-07-17'),
(33, 42, 154000, 'Victory', 'Cynthia', 'Espèces', '0188', '2026-07-25'),
(34, 41, 1350700, 'patient', 'Cynthia', 'Espèces', '0187', '2026-07-28'),
(35, 44, 10000, '79032914', 'Cynthia', 'Espèces', '0213', '2026-07-30'),
(36, 40, 26000, 'Client de passage', 'Cynthia', 'Espèces', '0169', '2026-08-06'),
(37, 43, 745000, 'Client de passage', 'Cynthia', 'Espèces', '0207', '2026-08-10'),
(38, 45, 80000, 'cartas', 'Cynthia', 'Espèces', '0232', '2026-08-10'),
(39, 46, 2, 'Client de passage', 'Cynthia', 'Espèces', '0265', '2026-08-18'),
(40, 48, 160000, 'Client de passage', 'Cynthia', 'Espèces', '0285', '2026-08-18');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  `phone` varchar(15) NOT NULL,
  `medicines` varchar(255) DEFAULT NULL,
  `quantity` varchar(255) DEFAULT NULL,
  `tot_htva` bigint(20) DEFAULT NULL,
  `tva` bigint(20) DEFAULT NULL,
  `tva_paid` bigint(20) DEFAULT NULL,
  `tot_tvac` bigint(20) DEFAULT NULL,
  `total_amount` bigint(11) DEFAULT NULL,
  `date` date NOT NULL DEFAULT current_timestamp(),
  `doc_fields` text DEFAULT NULL,
  `file` varchar(100) DEFAULT NULL,
  `category` enum('Archive','Interne') NOT NULL,
  `status` varchar(20) DEFAULT 'En cours',
  `contact_person` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `documents`
--

INSERT INTO `documents` (`id`, `doc_type`, `doc_number`, `operator`, `client_name`, `phone`, `medicines`, `quantity`, `tot_htva`, `tva`, `tva_paid`, `tot_tvac`, `total_amount`, `date`, `doc_fields`, `file`, `category`, `status`, `contact_person`) VALUES
(3, 'Proforma', 'PROF-0001', 'Cynthia', 'Alexis', '69788400', 'ABS A4', '1', 30000, 0, 0, 30000, 30000, '2026-05-18', 'payment_conditions:Livraison partielle ou complete; Poids:....', NULL, 'Interne', 'En cours', '-'),
(4, 'Proforma', 'PROF-0004', 'Cynthia', 'Alexis', '69788400', 'AGENDA A4', '5', 250000, 18, 45000, 295000, 295000, '2026-05-18', 'delivery_time:7 jours,delivery_cost:10.000FBU,payment_mode:Virement bancaire / BANCOBU', NULL, 'Interne', 'En cours', '-'),
(5, 'Proforma', 'PROF-0005', 'Cynthia', '', '', 'ABS A4', '2', 60000, 18, 10800, 70800, 70800, '2026-05-21', '', NULL, 'Interne', 'En cours', 'elie');

-- --------------------------------------------------------

--
-- Structure de la table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL,
  `admin` varchar(100) DEFAULT NULL,
  `client_email` varchar(255) DEFAULT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `entries`
--

CREATE TABLE `entries` (
  `id` int(11) NOT NULL,
  `operator` varchar(25) NOT NULL,
  `invoice_in` varchar(100) DEFAULT NULL,
  `bar_code` int(11) NOT NULL,
  `medicine_name` varchar(100) NOT NULL,
  `category` varchar(255) NOT NULL,
  `quantity` decimal(10,1) NOT NULL,
  `register_date` date NOT NULL DEFAULT current_timestamp(),
  `expire_date` date DEFAULT current_timestamp(),
  `sell_type` varchar(100) NOT NULL,
  `actual_price` decimal(10,0) NOT NULL,
  `total_amount` decimal(10,0) NOT NULL,
  `status` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `entries`
--

INSERT INTO `entries` (`id`, `operator`, `invoice_in`, `bar_code`, `medicine_name`, `category`, `quantity`, `register_date`, `expire_date`, `sell_type`, `actual_price`, `total_amount`, `status`) VALUES
(1, 'Isabelle', '-', 0, 'Autocollant18*10', 'Produit', '60.0', '2026-08-10', '0000-00-00', 'm', '0', '0', 'Disponible'),
(2, 'Isabelle', '-', 0, 'Autocollant18*10', 'Produit', '75.0', '2026-08-18', '0000-00-00', 'm', '0', '0', 'Disponible');

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
  `date` date NOT NULL DEFAULT current_timestamp(),
  `status` enum('Active','Supprimée') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `expenses`
--

INSERT INTO `expenses` (`id`, `amount`, `expense_cat`, `purpose`, `id_proof`, `proof`, `operator`, `date`, `status`) VALUES
(1, 25000, 'Consommation', 'transport soir', 'BS00001', NULL, 'Cynthia', '2026-06-25', 'Active'),
(2, 5000, 'Consommation', 'ajout sablimation', 'BS00002', NULL, 'Isabelle', '2026-06-25', 'Active'),
(3, 50000, 'Consommation', 'cash power', 'BS00003', NULL, 'Isabelle', '2026-06-25', 'Active'),
(4, 31500, 'Consommation', 'ratio', 'BS00004', NULL, 'Isabelle', '2026-06-25', 'Active'),
(5, 1500, 'Consommation', 'mega', 'BS00005', NULL, 'Isabelle', '2026-06-25', 'Active'),
(6, 35000, 'Consommation', 'sikouchi', 'BS00006', NULL, 'Isabelle', '2026-06-25', 'Active'),
(7, 10000, 'Consommation', 'frais pour terrain ', 'BS00007', NULL, 'Isabelle', '2026-06-25', 'Active'),
(8, 9000, 'Consommation', 'water', 'BS00008', NULL, 'Isabelle', '2026-06-25', 'Active'),
(9, 30000, 'Consommation', 'tear drop bubet', 'BS00009', NULL, 'Isabelle', '2026-06-25', 'Active'),
(10, 82500, 'Consommation', 'representation dg', 'BS00010', NULL, 'Isabelle', '2026-06-25', 'Active'),
(11, 10000, 'Consommation', 'AJOUT TEAR DROP BUBET', 'BS00011', NULL, 'Isabelle', '2026-06-25', 'Active'),
(12, 5000, 'Consommation', 'POMPE MOTO', 'BS00012', NULL, 'Isabelle', '2026-06-25', 'Active'),
(13, 22000, 'Consommation', 'PROFORMA MANUEL', 'BS00013', NULL, 'Isabelle', '2026-06-25', 'Active'),
(14, 50000, 'Consommation', 'REPRESENTATION DG', 'BS00014', NULL, 'Isabelle', '2026-06-25', 'Active'),
(15, 1000, 'Consommation', 'UNITE', 'BS00015', NULL, 'Isabelle', '2026-06-25', 'Active'),
(16, 3500, 'Consommation', 'ratio', 'BS00016', NULL, 'Cynthia', '2026-06-25', 'Active'),
(17, 10000, 'Consommation', 'ratio', 'BS00017', NULL, 'Cynthia', '2026-06-26', 'Active'),
(18, 5000, 'Consommation', 'deplacement matin', 'BS00018', NULL, 'Cynthia', '2026-06-26', 'Active'),
(19, 2500, 'Consommation', 'representation dg', 'BS00019', NULL, 'Cynthia', '2026-06-26', 'Active'),
(20, 20000, 'Consommation', 'representation finance', 'BS00020', NULL, 'Cynthia', '2026-06-26', 'Active'),
(22, 14000, 'Consommation', 'FRAIS DE REPRESENTATION DG', 'BS00022', NULL, 'Cynthia', '2026-06-26', 'Active'),
(23, 21800, 'Consommation', 'RATIO', 'BS00023', NULL, 'Cynthia', '2026-06-26', 'Active'),
(24, 45000, 'Consommation', 'representation dg', 'BS00024', NULL, 'Cynthia', '2026-06-26', 'Active'),
(25, 30000, 'Consommation', 'representation finance', 'BS00025', NULL, 'Cynthia', '2026-06-26', 'Active'),
(26, 10000, 'Consommation', 'aide', 'BS00026', NULL, 'Cynthia', '2026-06-26', 'Active'),
(27, 10000, 'Consommation', 'cadre whh', 'BS00027', NULL, 'Cynthia', '2026-06-26', 'Active'),
(28, 100000, 'Consommation', 'autocollant PNUD', 'BS00028', NULL, 'Cynthia', '2026-06-26', 'Active'),
(29, 5000, 'Consommation', 'CITY', 'BS00029', NULL, 'Cynthia', '2026-06-26', 'Active'),
(30, 20000, 'Consommation', 'aide', 'BS00030', NULL, 'Cynthia', '2026-06-26', 'Active'),
(31, 10000, 'Consommation', 'terrain ', 'BS00031', NULL, 'Cynthia', '2026-06-26', 'Active'),
(32, 150000, 'Consommation', 'frs cash power unite', 'BS00032', NULL, 'Cynthia', '2026-06-26', 'Active'),
(33, 10000, 'Consommation', 'branding', 'BS00033', NULL, 'Cynthia', '2026-06-26', 'Active'),
(34, 20000, 'Consommation', 'frais de representation dg', 'BS00034', NULL, 'Cynthia', '2026-06-26', 'Active'),
(35, 8000, 'Consommation', 'aide', 'BS00035', NULL, 'Cynthia', '2026-06-29', 'Active'),
(36, 30000, 'Consommation', 'terrain bbci', 'BS00036', NULL, 'Cynthia', '2026-06-29', 'Active'),
(37, 5000, 'Consommation', 'representation dg', 'BS00037', NULL, 'Cynthia', '2026-06-29', 'Active'),
(38, 25000, 'Consommation', 'transport vendredi', 'BS00038', NULL, 'Cynthia', '2026-06-29', 'Active'),
(39, 15000, 'Consommation', 'ratio', 'BS00039', NULL, 'Cynthia', '2026-06-29', 'Active'),
(40, 500000, 'Consommation', 'achat invitations', 'BS00040', NULL, 'Cynthia', '2026-06-29', 'Active'),
(41, 6000, 'Consommation', 'achat sachet', 'BS00041', NULL, 'Cynthia', '2026-06-29', 'Active'),
(42, 50000, 'Consommation', 'transport samedi', 'BS00042', NULL, 'Cynthia', '2026-06-29', 'Active'),
(43, 35000, 'Consommation', 'ratio', 'BS00043', NULL, 'Cynthia', '2026-06-29', 'Active'),
(44, 47700, 'Consommation', 'representation dg', 'BS00044', NULL, 'Cynthia', '2026-06-29', 'Active'),
(45, 7000, 'Consommation', 'reparation pneu', 'BS00045', NULL, 'Cynthia', '2026-06-29', 'Active'),
(46, 10000, 'Consommation', 'finition calendrier', 'BS00046', NULL, 'Cynthia', '2026-06-29', 'Active'),
(47, 20000, 'Consommation', 'aide', 'BS00047', NULL, 'Cynthia', '2026-06-29', 'Active'),
(48, 288000, 'Approvisionnement', 'achat lacoste 9', 'BS00048', NULL, 'Cynthia', '2026-06-29', 'Active'),
(49, 3500, 'Consommation', 'bassin', 'BS00049', NULL, 'Cynthia', '2026-06-29', 'Active'),
(50, 6000, 'Approvisionnement', 'achat stylos', 'BS00050', NULL, 'Cynthia', '2026-06-29', 'Active'),
(51, 924000, 'Approvisionnement', 'achat 28lacoste', 'BS00051', NULL, 'Cynthia', '2026-06-29', 'Active'),
(52, 10000, 'Consommation', 'representation dg', 'BS00052', NULL, 'Cynthia', '2026-06-29', 'Active'),
(53, 2000, 'Consommation', 'sylo', 'BS00053', NULL, 'Cynthia', '2026-06-29', 'Active'),
(54, 4000, 'Consommation', 'aide;unite', 'BS00054', NULL, 'Cynthia', '2026-06-29', 'Active'),
(55, 4000, 'Consommation', 'water', 'BS00055', NULL, 'Cynthia', '2026-06-29', 'Active'),
(56, 3000, 'Consommation', 'city', 'BS00056', NULL, 'Cynthia', '2026-06-29', 'Active'),
(57, 150000, 'Consommation', 'representation dg', 'BS00057', NULL, 'Cynthia', '2026-06-29', 'Active'),
(58, 2000, 'Consommation', 'water', 'BS00058', NULL, 'Cynthia', '2026-06-29', 'Active'),
(59, 45000, 'Consommation', 'aide', 'BS00059', NULL, 'Cynthia', '2026-06-29', 'Active'),
(60, 20000, 'Consommation', 'transport soir', 'BS00060', NULL, 'Cynthia', '2026-06-30', 'Active'),
(61, 3000, 'Consommation', 'cutting', 'BS00061', NULL, 'Cynthia', '2026-06-30', 'Active'),
(62, 10000, 'Consommation', 'terrain bubet', 'BS00062', NULL, 'Cynthia', '2026-06-30', 'Active'),
(63, 31000, 'Consommation', 'ratio', 'BS00063', NULL, 'Cynthia', '2026-06-30', 'Active'),
(64, 15000, 'Consommation', 'superglue', 'BS00064', NULL, 'Cynthia', '2026-06-30', 'Active'),
(65, 1500, 'Consommation', 'sachet ', 'BS00065', NULL, 'Cynthia', '2026-06-30', 'Active'),
(66, 100000, 'Consommation', 'relieure calendrier', 'BS00066', NULL, 'Cynthia', '2026-06-30', 'Active'),
(67, 50000, 'Consommation', 'cash power', 'BS00067', NULL, 'Cynthia', '2026-06-30', 'Active'),
(68, 200000, 'Consommation', 'frs materiels', 'BS00068', NULL, 'Cynthia', '2026-06-30', 'Active'),
(69, 6000, 'Consommation', 'superglue', 'BS00069', NULL, 'Cynthia', '2026-06-30', 'Active'),
(70, 5000, 'Consommation', 'serviette', 'BS00070', NULL, 'Cynthia', '2026-06-30', 'Active'),
(71, 300000, 'Approvisionnement', 'versement bcb', 'BS00071', NULL, 'Cynthia', '2026-06-30', 'Active'),
(76, 10000, 'Consommation', 'aide', 'BS00072', NULL, 'Cynthia', '2026-06-30', 'Active'),
(77, 400000, 'Consommation', 'representation bbci', 'BS00073', NULL, 'Cynthia', '2026-06-30', 'Active'),
(78, 400000, 'Consommation', 'representation boss', 'BS00074', NULL, 'Cynthia', '2026-06-30', 'Active'),
(79, 290000, 'Consommation', 'carburent', 'BS00075', NULL, 'Cynthia', '2026-06-30', 'Active'),
(80, 209000, 'Consommation', 'facture fixe', 'BS00076', NULL, 'Cynthia', '2026-06-30', 'Active'),
(81, 240000, 'Consommation', 'frs materiels', 'BS00077', NULL, 'Cynthia', '2026-06-30', 'Active'),
(82, 850000, 'Consommation', 'encre bizhub', 'BS00078', NULL, 'Cynthia', '2026-06-30', 'Active'),
(83, 711000, 'Consommation', 'representation bic et fle microfinance', 'BS00079', NULL, 'Cynthia', '2026-06-30', 'Active'),
(84, 1092500, 'Consommation', 'auto pnud', 'BS00080', NULL, 'Cynthia', '2026-06-30', 'Active'),
(85, 450000, 'Consommation', 'representation boss', 'BS00081', NULL, 'Cynthia', '2026-06-30', 'Active'),
(86, 780000, 'Consommation', 'representation dg fle', 'BS00082', NULL, 'Cynthia', '2026-06-30', 'Active'),
(87, 160000, 'Consommation', 'cadrement whh', 'BS00083', NULL, 'Cynthia', '2026-06-30', 'Active'),
(88, 315000, 'Consommation', 'representation pafen', 'BS00084', NULL, 'Cynthia', '2026-06-30', 'Active'),
(89, 20000, 'Consommation', 'representation bcb', 'BS00085', NULL, 'Cynthia', '2026-06-30', 'Active'),
(90, 500000, 'Consommation', 'alimentation caisse', 'BS00086', NULL, 'Cynthia', '2026-06-30', 'Active'),
(91, 2500, 'Consommation', 'representation dg', 'BS00087', NULL, 'Cynthia', '2026-06-30', 'Active'),
(92, 10000, 'Consommation', 'aide', 'BS00088', NULL, 'Cynthia', '2026-06-30', 'Active'),
(93, 20000, 'Consommation', 'transport soir', 'BS00089', NULL, 'Cynthia', '2026-07-02', 'Active'),
(96, 10000, 'Consommation', 'pret destin', 'BS00090', NULL, 'Cynthia', '2026-07-02', 'Active'),
(97, 10500, 'Consommation', 'ratio', 'BS00091', NULL, 'Cynthia', '2026-07-02', 'Active'),
(98, 32000, 'Consommation', 'ratio', 'BS00092', NULL, 'Cynthia', '2026-07-02', 'Active'),
(99, 5000, 'Consommation', 'representation dg', 'BS00093', NULL, 'Cynthia', '2026-07-02', 'Active'),
(100, 85000, 'Consommation', 'representation febabu', 'BS00094', NULL, 'Cynthia', '2026-07-02', 'Active'),
(101, 10000, 'Consommation', 'pret destin', 'BS00095', NULL, 'Cynthia', '2026-07-02', 'Active'),
(102, 30000, 'Consommation', 'vidange MOTO', 'BS00096', NULL, 'Cynthia', '2026-07-02', 'Active'),
(105, 11000, 'Consommation', 'terrain unite', 'BS00097', NULL, 'Cynthia', '2026-07-02', 'Active'),
(106, 5000, 'Consommation', 'aide', 'BS00098', NULL, 'Cynthia', '2026-07-02', 'Active'),
(107, 10000, 'Consommation', 'water', 'BS00099', NULL, 'Cynthia', '2026-07-02', 'Active'),
(108, 69100, 'Consommation', 'assurance automobile', 'BS00100', NULL, 'Cynthia', '2026-07-02', 'Active'),
(109, 170000, 'Consommation', 'finition calendrier', 'BS00101', NULL, 'Cynthia', '2026-07-02', 'Active'),
(110, 106000, 'Consommation', 'representation dg', 'BS00102', NULL, 'Cynthia', '2026-07-02', 'Active'),
(111, 20000, 'Consommation', 'transport soir', 'BS00103', NULL, 'Cynthia', '2026-07-06', 'Active'),
(112, 48800, 'Consommation', 'bbci fumee', 'BS00104', NULL, 'Cynthia', '2026-07-06', 'Active'),
(113, 20000, 'Consommation', 'representation dg', 'BS00105', NULL, 'Cynthia', '2026-07-06', 'Active'),
(114, 230000, 'Consommation', 'bbci fumee', 'BS00106', NULL, 'Cynthia', '2026-07-06', 'Active'),
(115, 15000, 'Consommation', 'savon clear', 'BS00107', NULL, 'Cynthia', '2026-07-06', 'Active'),
(116, 10000, 'Consommation', 'frais pour terrain', 'BS00108', NULL, 'Cynthia', '2026-07-06', 'Active'),
(117, 33000, 'Consommation', 'ratio', 'BS00109', NULL, 'Cynthia', '2026-07-06', 'Active'),
(118, 6800, 'Consommation', 'representation dg', 'BS00110', NULL, 'Cynthia', '2026-07-06', 'Active'),
(119, 760000, 'Consommation', 'achat tshir13et lacoste10', 'BS00111', NULL, 'Cynthia', '2026-07-06', 'Active'),
(120, 399000, 'Consommation', 'achat 14tshirtet lacoste2', 'BS00112', NULL, 'Cynthia', '2026-07-06', 'Active'),
(121, 80000, 'Consommation', 'achat 6chapeau et aide', 'BS00113', NULL, 'Cynthia', '2026-07-06', 'Active'),
(122, 1000, 'Consommation', 'allumette', 'BS00114', NULL, 'Cynthia', '2026-07-06', 'Active'),
(123, 153000, 'Consommation', 'representation dg', 'BS00115', NULL, 'Cynthia', '2026-07-06', 'Active'),
(124, 8000, 'Consommation', 'ratio', 'BS00116', NULL, 'Cynthia', '2026-07-06', 'Active'),
(125, 10000, 'Consommation', 'transport soir', 'BS00117', NULL, 'Cynthia', '2026-07-06', 'Active'),
(126, 97200, 'Consommation', 'frs unite cash power', 'BS00118', NULL, 'Cynthia', '2026-07-06', 'Active'),
(127, 60000, 'Consommation', 'branding probox', 'BS00119', NULL, 'Cynthia', '2026-07-06', 'Active'),
(128, 40000, 'Consommation', 'transport', 'BS00120', NULL, 'Cynthia', '2026-07-06', 'Active'),
(129, 3000, 'Consommation', 'sachet', 'BS00121', NULL, 'Cynthia', '2026-07-06', 'Active'),
(130, 56, 'Consommation', 'autres charges', 'BS00122', NULL, 'Cynthia', '2026-07-06', 'Active'),
(131, 30000, 'Consommation', 'ratio', 'BS00123', NULL, 'Cynthia', '2026-07-06', 'Active'),
(132, 120000, 'Consommation', 'superglue', 'BS00124', NULL, 'Cynthia', '2026-07-06', 'Active'),
(133, 14000, 'Consommation', 'cutteur', 'BS00125', NULL, 'Cynthia', '2026-07-06', 'Active'),
(134, 23000, 'Consommation', 'aide', 'BS00126', NULL, 'Cynthia', '2026-07-06', 'Active'),
(135, 12000, 'Consommation', 'representation dg', 'BS00127', NULL, 'Cynthia', '2026-07-06', 'Active'),
(136, 90000, 'Consommation', 'depense du 5/7/2026 dimanche', 'BS00128', NULL, 'Cynthia', '2026-07-06', 'Active'),
(137, 50000, 'Consommation', 'journal', 'BS00129', NULL, 'Cynthia', '2026-07-06', 'Active'),
(138, 4500, 'Consommation', 'representationn dg', 'BS00130', NULL, 'Cynthia', '2026-07-06', 'Active'),
(139, 30000, 'Consommation', 'representation dg', 'BS00131', NULL, 'Cynthia', '2026-07-06', 'Active'),
(140, 30000, 'Consommation', 'boulette', 'BS00132', NULL, 'Cynthia', '2026-07-06', 'Active'),
(141, 30000, 'Consommation', 'pret destin', 'BS00133', NULL, 'Cynthia', '2026-07-06', 'Active'),
(142, 4000, 'Consommation', 'superglue', 'BS00134', NULL, 'Cynthia', '2026-07-07', 'Active'),
(144, 9000, 'Consommation', 'water', 'BS00136', NULL, 'Cynthia', '2026-07-07', 'Active'),
(145, 31000, 'Consommation', 'ratio', 'BS00137', NULL, 'Cynthia', '2026-07-07', 'Active'),
(146, 13500, 'Consommation', 'representation dg', 'BS00138', NULL, 'Cynthia', '2026-07-07', 'Active'),
(147, 10000, 'Consommation', 'transport soir', 'BS00139', NULL, 'Cynthia', '2026-07-07', 'Active'),
(148, 37000, 'Approvisionnement', 'lacoste 1', 'BS00140', NULL, 'Cynthia', '2026-07-07', 'Active'),
(149, 20000, 'Consommation', 'avance broderie obr', 'BS00141', NULL, 'Cynthia', '2026-07-07', 'Active'),
(150, 27000, 'Approvisionnement', 'tshirt simple', 'BS00142', NULL, 'Cynthia', '2026-07-07', 'Active'),
(151, 74000, 'Consommation', 'obr broderie', 'BS00143', NULL, 'Cynthia', '2026-07-08', 'Active'),
(152, 20000, 'Consommation', 'representation dg', 'BS00144', NULL, 'Cynthia', '2026-07-08', 'Active'),
(153, 9000, 'Consommation', 'water', 'BS00145', NULL, 'Cynthia', '2026-07-08', 'Active'),
(154, 20000, 'Consommation', 'cordes', 'BS00146', NULL, 'Cynthia', '2026-07-08', 'Active'),
(155, 30000, 'Consommation', 'aide', 'BS00147', NULL, 'Cynthia', '2026-07-08', 'Active'),
(156, 5000, 'Consommation', 'cutting', 'BS00148', NULL, 'Cynthia', '2026-07-08', 'Active'),
(157, 25000, 'Consommation', 'transport soir', 'BS00149', NULL, 'Cynthia', '2026-07-08', 'Active'),
(158, 10000, 'Consommation', 'terrain bubet', 'BS00150', NULL, 'Cynthia', '2026-07-08', 'Active'),
(159, 10000, 'Consommation', 'representation dg', 'BS00151', NULL, 'Cynthia', '2026-07-08', 'Active'),
(160, 1500, 'Consommation', 'sachet', 'BS00152', NULL, 'Cynthia', '2026-07-08', 'Active'),
(161, 1000, 'Consommation', 'unite', 'BS00153', NULL, 'Cynthia', '2026-07-08', 'Active'),
(162, 2000, 'Consommation', 'water', 'BS00154', NULL, 'Cynthia', '2026-07-08', 'Active'),
(163, 377200, 'Consommation', 'representation dg', 'BS00155', NULL, 'Cynthia', '2026-07-08', 'Active'),
(164, 78300, 'Consommation', 'cash power unite', 'BS00156', NULL, 'Cynthia', '2026-07-08', 'Active'),
(165, 50000, 'Consommation', 'assistance sociale', 'BS00157', NULL, 'Cynthia', '2026-07-08', 'Active'),
(166, 49000, 'Consommation', 'frais de representation dg', 'BS00158', NULL, 'Cynthia', '2026-07-08', 'Active'),
(167, 20000, 'Consommation', 'lavage voiture', 'BS00159', NULL, 'Cynthia', '2026-07-08', 'Active'),
(168, 34000, 'Consommation', 'ratio', 'BS00160', NULL, 'Cynthia', '2026-07-08', 'Active'),
(169, 1500, 'Consommation', 'sachet', 'BS00161', NULL, 'Cynthia', '2026-07-08', 'Active'),
(170, 20000, 'Consommation', 'representation dg', 'BS00162', NULL, 'Cynthia', '2026-07-08', 'Active'),
(171, 3000, 'Consommation', 'city', 'BS00163', NULL, 'Cynthia', '2026-07-09', 'Active'),
(172, 10000, 'Consommation', 'aide life', 'BS00164', NULL, 'Cynthia', '2026-07-09', 'Active'),
(173, 20000, 'Consommation', 'transport soir', 'BS00165', NULL, 'Cynthia', '2026-07-09', 'Active'),
(174, 20000, 'Consommation', 'superglue', 'BS00166', NULL, 'Cynthia', '2026-07-09', 'Active'),
(177, 5000, 'Consommation', 'terrain bubet', 'BS00167', NULL, 'Cynthia', '2026-07-09', 'Active'),
(178, 14000, 'Consommation', 'cutteur', 'BS00168', NULL, 'Cynthia', '2026-07-09', 'Active'),
(179, 11000, 'Consommation', 'water', 'BS00169', NULL, 'Cynthia', '2026-07-09', 'Active'),
(180, 20000, 'Consommation', 'porte cle', 'BS00170', NULL, 'Cynthia', '2026-07-09', 'Active'),
(181, 1500, 'Consommation', 'MEGA', 'BS00171', NULL, 'Cynthia', '2026-07-09', 'Active'),
(182, 10000, 'Consommation', 'couche 130g', 'BS00172', NULL, 'Cynthia', '2026-07-09', 'Active'),
(183, 32500, 'Consommation', 'assurance moto', 'BS00173', NULL, 'Cynthia', '2026-07-09', 'Active'),
(184, 41000, 'Consommation', 'ratio', 'BS00174', NULL, 'Cynthia', '2026-07-09', 'Active'),
(185, 22000, 'Consommation', 'pret niyo', 'BS00175', NULL, 'Cynthia', '2026-07-09', 'Active'),
(186, 32400, 'Consommation', 'lavage noah', 'BS00176', NULL, 'Cynthia', '2026-07-09', 'Active'),
(187, 100000, 'Consommation', 'M O soudeur', 'BS00177', NULL, 'Cynthia', '2026-07-09', 'Active'),
(188, 46500, 'Consommation', 'representation dg', 'BS00178', NULL, 'Cynthia', '2026-07-09', 'Active'),
(189, 15000, 'Consommation', 'moto', 'BS00179', NULL, 'Cynthia', '2026-07-09', 'Active'),
(190, 5000, 'Consommation', 'representation dg', 'BS00180', NULL, 'Cynthia', '2026-07-09', 'Active'),
(191, 3000, 'Consommation', 'ratio', 'BS00181', NULL, 'Cynthia', '2026-07-09', 'Active'),
(192, 10000, 'Consommation', 'representation dg', 'BS00182', NULL, 'Cynthia', '2026-07-09', 'Active'),
(193, 40000, 'Consommation', 'transport soir', 'BS00183', NULL, 'Cynthia', '2026-07-10', 'Active'),
(194, 140000, 'Consommation', 'achat 70stylos', 'BS00184', NULL, 'Cynthia', '2026-07-10', 'Active'),
(195, 8000, 'Consommation', 'superglue', 'BS00185', NULL, 'Cynthia', '2026-07-10', 'Active'),
(196, 37000, 'Approvisionnement', 'achat lacoste', 'BS00186', NULL, 'Cynthia', '2026-07-10', 'Active'),
(197, 10000, 'Consommation', 'noah symonise', 'BS00187', NULL, 'Cynthia', '2026-07-10', 'Active'),
(198, 25000, 'Consommation', 'reliure', 'BS00188', NULL, 'Cynthia', '2026-07-10', 'Active'),
(199, 23000, 'Consommation', 'representation dg', 'BS00189', NULL, 'Cynthia', '2026-07-10', 'Active'),
(200, 32000, 'Consommation', 'ratio', 'BS00190', NULL, 'Cynthia', '2026-07-10', 'Active'),
(201, 45500, 'Consommation', 'representation dg', 'BS00191', NULL, 'Cynthia', '2026-07-10', 'Active'),
(202, 220000, 'Approvisionnement', 'cordes febabu badge', 'BS00192', NULL, 'Cynthia', '2026-07-10', 'Active'),
(203, 5000, 'Consommation', 'ratio', 'BS00193', NULL, 'Cynthia', '2026-07-10', 'Active'),
(204, 10000, 'Consommation', 'terrain bubet', 'BS00194', NULL, 'Cynthia', '2026-07-10', 'Active'),
(205, 15000, 'Consommation', 'representation dg', 'BS00195', NULL, 'Cynthia', '2026-07-10', 'Active'),
(206, 208000, 'Approvisionnement', 'fixation pancarte bubet', 'BS00196', NULL, 'Cynthia', '2026-07-10', 'Active'),
(207, 70000, 'Consommation', 'transport soir', 'BS00197', NULL, 'Cynthia', '2026-07-13', 'Active'),
(208, 3000, 'Approvisionnement', 'frais de retrait', 'BS00198', NULL, 'Cynthia', '2026-07-13', 'Active'),
(209, 250000, 'Consommation', 'encourragement', 'BS00199', NULL, 'Cynthia', '2026-07-13', 'Active'),
(210, 5000, 'Consommation', 'serviette', 'BS00200', NULL, 'Cynthia', '2026-07-13', 'Active'),
(211, 9000, 'Approvisionnement', 'cordes', 'BS00201', NULL, 'Cynthia', '2026-07-13', 'Active'),
(212, 20000, 'Consommation', 'transport', 'BS00202', NULL, 'Cynthia', '2026-07-13', 'Active'),
(213, 1000, 'Consommation', 'unite', 'BS00203', NULL, 'Cynthia', '2026-07-13', 'Active'),
(214, 30000, 'Consommation', 'controle technique', 'BS00204', NULL, 'Cynthia', '2026-07-13', 'Active'),
(215, 17000, 'Consommation', 'representation dg', 'BS00205', NULL, 'Cynthia', '2026-07-13', 'Active'),
(216, 2000, 'Consommation', 'unite', 'BS00206', NULL, 'Cynthia', '2026-07-13', 'Active'),
(217, 2000, 'Consommation', 'representation dg', 'BS00207', NULL, 'Cynthia', '2026-07-13', 'Active'),
(218, 33000, 'Consommation', 'ratio', 'BS00208', NULL, 'Cynthia', '2026-07-13', 'Active'),
(219, 2000, 'Consommation', 'deplacement', 'BS00209', NULL, 'Cynthia', '2026-07-13', 'Active'),
(220, 150000, 'Approvisionnement', 'avance conception 30 carnet utema', 'BS00210', NULL, 'Cynthia', '2026-07-13', 'Active'),
(221, 5000, 'Approvisionnement', 'representation dg', 'BS00211', NULL, 'Cynthia', '2026-07-13', 'Active'),
(222, 5000, 'Consommation', 'ratio', 'BS00212', NULL, 'Cynthia', '2026-07-13', 'Active'),
(223, 10000, 'Consommation', 'FRAIS DE REPRESENTATION DG', 'BS00213', NULL, 'Cynthia', '2026-07-13', 'Active'),
(224, 3000, 'Consommation', 'CITY', 'BS00214', NULL, 'Cynthia', '2026-07-13', 'Active'),
(225, 10000, 'Consommation', 'RATIO DU 14/7/2026', 'BS00215', NULL, 'Cynthia', '2026-07-13', 'Active'),
(226, 20000, 'Consommation', 'CONTROLE TECHNIQUE', 'BS00216', NULL, 'Cynthia', '2026-07-13', 'Active'),
(227, 3000, 'Consommation', 'SABLIMATION', 'BS00217', NULL, 'Cynthia', '2026-07-13', 'Active'),
(229, 20000, 'Consommation', 'transport soir', 'BS00218', NULL, 'Cynthia', '2026-07-14', 'Active'),
(230, 15000, 'Consommation', 'frais de representation dg', 'BS00219', NULL, 'Cynthia', '2026-07-14', 'Active'),
(231, 23000, 'Consommation', 'ratio', 'BS00220', NULL, 'Cynthia', '2026-07-14', 'Active'),
(232, 15000, 'Consommation', 'colle', 'BS00221', NULL, 'Cynthia', '2026-07-14', 'Active'),
(234, 2000, 'Consommation', 'superlue', 'BS00222', NULL, 'Cynthia', '2026-07-14', 'Active'),
(235, 3000, 'Consommation', 'brosse', 'BS00223', NULL, 'Cynthia', '2026-07-14', 'Active'),
(236, 70000, 'Consommation', 'reparation chargeur moto', 'BS00224', NULL, 'Cynthia', '2026-07-14', 'Active'),
(237, 100000, 'Consommation', 'representation bubet', 'BS00225', NULL, 'Cynthia', '2026-07-14', 'Active'),
(238, 35000, 'Consommation', 'cadenat', 'BS00226', NULL, 'Cynthia', '2026-07-14', 'Active'),
(239, 1000, 'Consommation', 'clé', 'BS00227', NULL, 'Cynthia', '2026-07-14', 'Active'),
(240, 50000, 'Consommation', 'represenation dg', 'BS00228', NULL, 'Cynthia', '2026-07-14', 'Active'),
(241, 50000, 'Approvisionnement', 'bordereaux utema', 'BS00229', NULL, 'Cynthia', '2026-07-14', 'Active'),
(242, 5000, 'Consommation', 'ratio', 'BS00230', NULL, 'Cynthia', '2026-07-14', 'Active'),
(243, 40000, 'Consommation', 'representationdg', 'BS00231', NULL, 'Cynthia', '2026-07-14', 'Active'),
(245, 15000, 'Consommation', 'gilet', 'BS00233', NULL, 'Cynthia', '2026-07-14', 'Active'),
(246, 20000, 'Consommation', 'porte menteau', 'BS00234', NULL, 'Cynthia', '2026-07-14', 'Active'),
(247, 5000, 'Consommation', 'ratio', 'BS00235', NULL, 'Cynthia', '2026-07-14', 'Active'),
(248, 10000, 'Consommation', 'ratio du 15/6', 'BS00236', NULL, 'Cynthia', '2026-07-14', 'Active'),
(249, 17000, 'Consommation', 'representaton dg', 'BS00237', NULL, 'Cynthia', '2026-07-14', 'Active'),
(250, 5000, 'Consommation', 'autres charges', 'BS00238', NULL, 'Cynthia', '2026-07-14', 'Active'),
(251, 7000, 'Consommation', 'representation dg', 'BS00239', NULL, 'Cynthia', '2026-07-14', 'Active'),
(252, 10000, 'Consommation', 'superglue', 'BS00240', NULL, 'Cynthia', '2026-07-14', 'Active'),
(253, 9000, 'Consommation', 'water', 'BS00241', NULL, 'Cynthia', '2026-07-14', 'Active'),
(254, 409500, 'Consommation', 'inss', 'BS00242', NULL, 'Cynthia', '2026-07-15', 'Active'),
(255, 82200, 'Consommation', 'frs cash power', 'BS00243', NULL, 'Cynthia', '2026-07-15', 'Active'),
(256, 23000, 'Consommation', 'ratio', 'BS00244', NULL, 'Cynthia', '2026-07-15', 'Active'),
(257, 1000, 'Consommation', 'enveloppe', 'BS00245', NULL, 'Cynthia', '2026-07-15', 'Active'),
(258, 10000, 'Consommation', 'terrain bubet', 'BS00246', NULL, 'Cynthia', '2026-07-15', 'Active'),
(259, 3000, 'Consommation', 'entretien', 'BS00247', NULL, 'Cynthia', '2026-07-15', 'Active'),
(260, 6000, 'Consommation', 'sablimation', 'BS00248', NULL, 'Cynthia', '2026-07-15', 'Active'),
(261, 1000, 'Consommation', 'unite', 'BS00249', NULL, 'Cynthia', '2026-07-15', 'Active'),
(262, 13000, 'Consommation', 'achat casquette', 'BS00250', NULL, 'Cynthia', '2026-07-15', 'Active'),
(263, 10000, 'Consommation', 'representation dg', 'BS00251', NULL, 'Cynthia', '2026-07-15', 'Active'),
(264, 1092000, 'Approvisionnement', 'achat casquette et gilet', 'BS00252', NULL, 'Cynthia', '2026-07-15', 'Active'),
(266, 60000, 'Consommation', 'sublimation', 'BS00253', NULL, 'Cynthia', '2026-07-15', 'Active'),
(267, 14200, 'Consommation', 'achat chapeau,', 'BS00254', NULL, 'Cynthia', '2026-07-15', 'Active'),
(268, 202000, 'Consommation', 'encre dtf', 'BS00255', NULL, 'Cynthia', '2026-07-15', 'Active'),
(269, 5000, 'Consommation', 'ratio', 'BS00256', NULL, 'Cynthia', '2026-07-15', 'Active'),
(270, 80000, 'Consommation', 'representation dg', 'BS00257', NULL, 'Cynthia', '2026-07-15', 'Active'),
(273, 40000, 'Consommation', 'enveloppe bic', 'BS00258', NULL, 'Cynthia', '2026-07-15', 'Active'),
(274, 16000, 'Consommation', 'superglue', 'BS00259', NULL, 'Cynthia', '2026-07-15', 'Active'),
(275, 4000, 'Consommation', 'superglue', 'BS00260', NULL, 'Cynthia', '2026-07-15', 'Active'),
(276, 45000, 'Approvisionnement', 'transport soir', 'BS00261', NULL, 'Cynthia', '2026-07-16', 'Active'),
(277, 15500, 'Consommation', 'représentation dg', 'BS00262', NULL, 'Cynthia', '2026-07-16', 'Active'),
(278, 20000, 'Consommation', 'bat utema', 'BS00263', NULL, 'Cynthia', '2026-07-16', 'Active'),
(279, 25000, 'Consommation', 'representation dg', 'BS00264', NULL, 'Cynthia', '2026-07-16', 'Active'),
(280, 31000, 'Consommation', 'ratio', 'BS00265', NULL, 'Cynthia', '2026-07-16', 'Active'),
(281, 80000, 'Consommation', 'representation dg', 'BS00266', NULL, 'Cynthia', '2026-07-16', 'Active'),
(282, 5000, 'Consommation', 'cutting', 'BS00267', NULL, 'Cynthia', '2026-07-16', 'Active'),
(283, 18000, 'Consommation', 'transport materiels', 'BS00268', NULL, 'Cynthia', '2026-07-16', 'Active'),
(284, 45000, 'Approvisionnement', 'porte badge', 'BS00269', NULL, 'Cynthia', '2026-07-16', 'Active'),
(285, 20000, 'Consommation', 'conception cashet', 'BS00270', NULL, 'Cynthia', '2026-07-16', 'Active'),
(286, 300000, 'Consommation', 'frais de representaton finance', 'BS00271', NULL, 'Cynthia', '2026-07-16', 'Active'),
(287, 30000, 'Consommation', 'latte', 'BS00272', NULL, 'Cynthia', '2026-07-16', 'Active'),
(288, 3000, 'Consommation', 'city', 'BS00273', NULL, 'Cynthia', '2026-07-16', 'Active'),
(289, 5000, 'Consommation', 'sac', 'BS00274', NULL, 'Cynthia', '2026-07-16', 'Active'),
(290, 4000, 'Consommation', 'frais de retrait', 'BS00275', NULL, 'Cynthia', '2026-07-16', 'Active'),
(291, 16200, 'Consommation', 'tube', 'BS00276', NULL, 'Cynthia', '2026-07-16', 'Active'),
(292, 114000, 'Consommation', 'representatin dg', 'BS00277', NULL, 'Cynthia', '2026-07-16', 'Active'),
(293, 10000, 'Consommation', 'finition livre', 'BS00278', NULL, 'Cynthia', '2026-07-17', 'Active'),
(295, 20000, 'Consommation', 'impression pvc10', 'BS00279', NULL, 'Cynthia', '2026-07-17', 'Active'),
(297, 30000, 'Consommation', 'representqtin dg', 'BS00281', NULL, 'Cynthia', '2026-07-17', 'Active'),
(298, 10000, 'Consommation', 'colle', 'BS00282', NULL, 'Cynthia', '2026-07-17', 'Active'),
(299, 100000, 'Consommation', 'cartouche', 'BS00283', NULL, 'Cynthia', '2026-07-17', 'Active'),
(300, 50000, 'Consommation', 'representqtion brb', 'BS00284', NULL, 'Cynthia', '2026-07-17', 'Active'),
(301, 32000, 'Consommation', 'ratio', 'BS00285', NULL, 'Cynthia', '2026-07-17', 'Active'),
(302, 5000, 'Consommation', 'transport matériels', 'BS00286', NULL, 'Cynthia', '2026-07-17', 'Active'),
(303, 1000, 'Consommation', 'unite', 'BS00287', NULL, 'Cynthia', '2026-07-17', 'Active'),
(304, 25000, 'Approvisionnement', 'tshirt', 'BS00288', NULL, 'Cynthia', '2026-07-17', 'Active'),
(306, 2500, 'Consommation', 'autres charges', 'BS00289', NULL, 'Cynthia', '2026-07-17', 'Active'),
(307, 46500, 'Consommation', 'representation dg', 'BS00290', NULL, 'Cynthia', '2026-07-17', 'Active'),
(308, 35000, 'Consommation', 'aqllonge', 'BS00291', NULL, 'Cynthia', '2026-07-17', 'Active'),
(309, 54000, 'Approvisionnement', 'tshirt 2', 'BS00292', NULL, 'Cynthia', '2026-07-17', 'Active'),
(310, 3000, 'Consommation', 'cutting', 'BS00293', NULL, 'Cynthia', '2026-07-17', 'Active'),
(311, 2000, 'Consommation', 'cutting', 'BS00294', NULL, 'Cynthia', '2026-07-17', 'Active'),
(312, 85000, 'Consommation', 'representation dg', 'BS00295', NULL, 'Cynthia', '2026-07-17', 'Active'),
(313, 8000, 'Consommation', 'autres charges', 'BS00296', NULL, 'Cynthia', '2026-07-17', 'Active'),
(314, 2500, 'Consommation', 'frais de representation dg', 'BS00297', NULL, 'Cynthia', '2026-07-17', 'Active'),
(315, 50000, 'Consommation', 'pret cynthia', 'BS00298', NULL, 'Cynthia', '2026-07-17', 'Active'),
(316, 40000, 'Consommation', 'pret alix', 'BS00299', NULL, 'Cynthia', '2026-07-17', 'Active'),
(318, 10000, 'Consommation', 'autres charges aide', 'BS00300', NULL, 'Cynthia', '2026-07-17', 'Active'),
(319, 5000, 'Consommation', 'installation tube', 'BS00301', NULL, 'Cynthia', '2026-07-17', 'Active'),
(320, 252000, 'Consommation', 'representation dg', 'BS00302', NULL, 'Cynthia', '2026-07-17', 'Active'),
(321, 10000, 'Consommation', 'transport soir', 'BS00303', NULL, 'Cynthia', '2026-07-17', 'Active'),
(322, 115000, 'Consommation', 'transport', 'BS00304', NULL, 'Cynthia', '2026-07-20', 'Active'),
(323, 75000, 'Approvisionnement', 'achat 3 tshirt', 'BS00305', NULL, 'Cynthia', '2026-07-20', 'Active'),
(324, 643600, 'Consommation', 'frais de representation dg', 'BS00306', NULL, 'Cynthia', '2026-07-20', 'Active'),
(325, 2000, 'Consommation', 'aide', 'BS00307', NULL, 'Cynthia', '2026-07-20', 'Active'),
(326, 40000, 'Consommation', 'broderie ', 'BS00308', NULL, 'Cynthia', '2026-07-20', 'Active'),
(327, 30000, 'Consommation', 'transport soir', 'BS00309', NULL, 'Cynthia', '2026-07-20', 'Active'),
(328, 20000, 'Consommation', 'tear drop', 'BS00310', NULL, 'Cynthia', '2026-07-20', 'Active'),
(329, 10000, 'Consommation', 'ratio', 'BS00311', NULL, 'Cynthia', '2026-07-20', 'Active'),
(331, 26000, 'Approvisionnement', 'achat t-shirt Bic', 'BS00312', NULL, 'Cynthia', '2026-07-20', 'Active'),
(332, 30300, 'Consommation', 'ratio', 'BS00313', NULL, 'Cynthia', '2026-07-20', 'Active'),
(333, 1700, 'Consommation', 'représentation dg', 'BS00314', NULL, 'Cynthia', '2026-07-20', 'Active'),
(334, 135000, 'Approvisionnement', 'achat 5 tshirt', 'BS00315', NULL, 'Cynthia', '2026-07-20', 'Active'),
(335, 16000, 'Consommation', 'coll plus tournevis Bic', 'BS00316', NULL, 'Cynthia', '2026-07-20', 'Active'),
(336, 270000, 'Approvisionnement', 'tshirt 10bic', 'BS00317', NULL, 'Cynthia', '2026-07-20', 'Active'),
(337, 2000, 'Consommation', 'aIDE', 'BS00318', NULL, 'Cynthia', '2026-07-20', 'Active'),
(338, 36000, 'Consommation', 'REPRESENTATION DG', 'BS00319', NULL, 'Cynthia', '2026-07-20', 'Active'),
(339, 12000, 'Consommation', 'REPRESENTATION DG', 'BS00320', NULL, 'Cynthia', '2026-07-20', 'Active'),
(340, 31000, 'Consommation', 'TUBE PLUS INSTALLATION', 'BS00321', NULL, 'Cynthia', '2026-07-20', 'Active'),
(341, 15000, 'Consommation', 'COLLE', 'BS00322', NULL, 'Cynthia', '2026-07-20', 'Active'),
(342, 250000, 'Consommation', 'CARNET UTEMA', 'BS00323', NULL, 'Cynthia', '2026-07-20', 'Active'),
(343, 10000, 'Consommation', 'TERRAN BUBET', 'BS00324', NULL, 'Cynthia', '2026-07-20', 'Active'),
(344, 20000, 'Consommation', 'AGENDA', 'BS00325', NULL, 'Cynthia', '2026-07-20', 'Active'),
(345, 3000, 'Consommation', 'RATIO', 'BS00326', NULL, 'Cynthia', '2026-07-20', 'Active'),
(346, 5000, 'Approvisionnement', 'RATIO', 'BS00327', NULL, 'Cynthia', '2026-07-20', 'Active'),
(347, 10000, 'Consommation', 'RATIO DU 21/6/2026', 'BS00328', NULL, 'Cynthia', '2026-07-20', 'Active'),
(349, 20500, 'Consommation', 'REPRESENTATION DG', 'BS00329', NULL, 'Cynthia', '2026-07-20', 'Active'),
(350, 23000, 'Consommation', 'ratio', 'BS00330', NULL, 'Cynthia', '2026-07-21', 'Active'),
(351, 14000, 'Consommation', 'transport soir', 'BS00331', NULL, 'Cynthia', '2026-07-21', 'Active'),
(352, 15000, 'Approvisionnement', 'corde bubet', 'BS00332', NULL, 'Cynthia', '2026-07-21', 'Active'),
(353, 60000, 'Approvisionnement', 'pvc jrs', 'BS00333', NULL, 'Cynthia', '2026-07-21', 'Active'),
(354, 2000, 'Consommation', 'represntaton dg', 'BS00334', NULL, 'Cynthia', '2026-07-21', 'Active'),
(355, 5000, 'Consommation', 'représentation dg', 'BS00335', NULL, 'Cynthia', '2026-07-21', 'Active'),
(356, 95000, 'Consommation', 'carnet utema et job card', 'BS00336', NULL, 'Cynthia', '2026-07-21', 'Active'),
(357, 34300, 'Consommation', 'impression pvc', 'BS00337', NULL, 'Cynthia', '2026-07-21', 'Active'),
(358, 27000, 'Approvisionnement', 'tshirt', 'BS00338', NULL, 'Cynthia', '2026-07-21', 'Active'),
(359, 41000, 'Consommation', 'représentation dg', 'BS00339', NULL, 'Cynthia', '2026-07-21', 'Active'),
(360, 40000, 'Consommation', 'representation dg', 'BS00340', NULL, 'Cynthia', '2026-07-21', 'Active'),
(361, 5000, 'Consommation', 'city', 'BS00341', NULL, 'Cynthia', '2026-07-21', 'Active'),
(363, 27000, 'Approvisionnement', 'ajout achat tshirt fnuap', 'BS00342', NULL, 'Cynthia', '2026-07-21', 'Active'),
(364, 20000, 'Consommation', 'Superglue ,déplacement', 'BS00343', NULL, 'Cynthia', '2026-07-21', 'Active'),
(365, 2000, 'Consommation', 'unité', 'BS00344', NULL, 'Cynthia', '2026-07-21', 'Active'),
(366, 5000, 'Consommation', 'representation dg', 'BS00345', NULL, 'Cynthia', '2026-07-22', 'Active'),
(367, 12000, 'Consommation', 'porte badge bubet', 'BS00346', NULL, 'Cynthia', '2026-07-22', 'Active'),
(368, 3000, 'Consommation', 'unite', 'BS00347', NULL, 'Cynthia', '2026-07-22', 'Active'),
(369, 5000, 'Consommation', 'serviette', 'BS00348', NULL, 'Cynthia', '2026-07-22', 'Active'),
(370, 7000, 'Consommation', 'vice', 'BS00349', NULL, 'Cynthia', '2026-07-22', 'Active'),
(371, 33000, 'Consommation', 'ratio', 'BS00350', NULL, 'Cynthia', '2026-07-22', 'Active'),
(372, 10000, 'Consommation', 'metre', 'BS00351', NULL, 'Cynthia', '2026-07-22', 'Active'),
(373, 25000, 'Approvisionnement', 't-shirt 1', 'BS00352', NULL, 'Cynthia', '2026-07-22', 'Active'),
(374, 6000, 'Consommation', 'moto', 'BS00353', NULL, 'Cynthia', '2026-07-22', 'Active'),
(375, 20000, 'Consommation', 'inss', 'BS00354', NULL, 'Cynthia', '2026-07-22', 'Active'),
(376, 240000, 'Consommation', 'carburent', 'BS00355', NULL, 'Cynthia', '2026-07-22', 'Active'),
(377, 40200, 'Approvisionnement', 'tasse4', 'BS00356', NULL, 'Cynthia', '2026-07-22', 'Active'),
(378, 20000, 'Consommation', 'representation dg', 'BS00357', NULL, 'Cynthia', '2026-07-22', 'Active'),
(379, 10000, 'Consommation', 'ndaha pret', 'BS00358', NULL, 'Cynthia', '2026-07-22', 'Active'),
(380, 10000, 'Consommation', 'water', 'BS00359', NULL, 'Cynthia', '2026-07-22', 'Active'),
(381, 1000, 'Consommation', 'représentation dg', 'BS00360', NULL, 'Cynthia', '2026-07-22', 'Active'),
(382, 7000, 'Consommation', 'représentation dg', 'BS00361', NULL, 'Cynthia', '2026-07-22', 'Active'),
(383, 25000, 'Consommation', 'parapluie', 'BS00362', NULL, 'Cynthia', '2026-07-22', 'Active'),
(384, 3000, 'Consommation', 'sabliamation', 'BS00363', NULL, 'Cynthia', '2026-07-22', 'Active'),
(385, 3000, 'Consommation', 'representation dg', 'BS00364', NULL, 'Cynthia', '2026-07-22', 'Active'),
(386, 20000, 'Consommation', 'bat carnet  Bic ', 'BS00365', NULL, 'Cynthia', '2026-07-22', 'Active'),
(387, 10000, 'Consommation', 'sablimation', 'BS00366', NULL, 'Cynthia', '2026-07-22', 'Active'),
(388, 10000, 'Approvisionnement', 'autres charges', 'BS00367', NULL, 'Cynthia', '2026-07-22', 'Active'),
(389, 30000, 'Consommation', 'assistance sociale', 'BS00368', NULL, 'Cynthia', '2026-07-23', 'Active'),
(390, 25000, 'Consommation', 'porte clé soutraitance ', 'BS00369', NULL, 'Cynthia', '2026-07-23', 'Active'),
(391, 3000, 'Consommation', 'city', 'BS00370', NULL, 'Cynthia', '2026-07-23', 'Active'),
(392, 5000, 'Consommation', 'ratio', 'BS00371', NULL, 'Cynthia', '2026-07-23', 'Active'),
(393, 2000, 'Consommation', 'sablimation', 'BS00372', NULL, 'Cynthia', '2026-07-23', 'Active'),
(394, 35500, 'Consommation', 'ratio', 'BS00373', NULL, 'Cynthia', '2026-07-23', 'Active'),
(395, 99000, 'Consommation', 'représentation dg', 'BS00374', NULL, 'Cynthia', '2026-07-23', 'Active'),
(396, 35000, 'Consommation', 'reparation machine', 'BS00375', NULL, 'Cynthia', '2026-07-23', 'Active'),
(397, 22500, 'Consommation', 'représentation dg', 'BS00376', NULL, 'Cynthia', '2026-07-23', 'Active'),
(398, 2000, 'Consommation', 'representation dg', 'BS00377', NULL, 'Cynthia', '2026-07-23', 'Active'),
(399, 5000000, 'Consommation', 'représentantion ,msp', 'BS00378', NULL, 'Cynthia', '2026-07-24', 'Active'),
(400, 350000, 'Consommation', 'batterie vitz', 'BS00379', NULL, 'Cynthia', '2026-07-24', 'Active'),
(401, 250000, 'Consommation', 'frais de representation canal', 'BS00380', NULL, 'Cynthia', '2026-07-24', 'Active'),
(402, 50000, 'Consommation', 'frais de represntation toursime', 'BS00381', NULL, 'Cynthia', '2026-07-24', 'Active'),
(403, 1315000, 'Consommation', 'encouragement staff', 'BS00382', NULL, 'Cynthia', '2026-07-24', 'Active'),
(405, 2835000, 'Approvisionnement', 'achat t-shirt 105', 'BS00383', NULL, 'Cynthia', '2026-07-24', 'Active'),
(406, 3500000, 'Consommation', '400livres bic', 'BS00384', NULL, 'Cynthia', '2026-07-24', 'Active'),
(407, 300000, 'Consommation', 'représentation dg', 'BS00385', NULL, 'Cynthia', '2026-07-24', 'Active'),
(408, 600000, 'Consommation', 'representation bbci', 'BS00386', NULL, 'Cynthia', '2026-07-24', 'Active'),
(409, 443000, 'Consommation', 'mecanicien noah', 'BS00387', NULL, 'Cynthia', '2026-07-24', 'Active'),
(410, 125000, 'Consommation', 'frais de mission tamba bic', 'BS00388', NULL, 'Cynthia', '2026-07-24', 'Active'),
(411, 12000, 'Consommation', 'banquier', 'BS00389', NULL, 'Cynthia', '2026-07-24', 'Active'),
(412, 270000, 'Consommation', 'batterie', 'BS00390', NULL, 'Cynthia', '2026-07-24', 'Active'),
(413, 15000, 'Consommation', 'transport soir', 'BS00391', NULL, 'Cynthia', '2026-07-24', 'Active'),
(414, 40000, 'Consommation', 'représentation finance', 'BS00392', NULL, 'Cynthia', '2026-07-24', 'Active'),
(415, 4000, 'Consommation', 'représentation dg', 'BS00393', NULL, 'Cynthia', '2026-07-24', 'Active'),
(416, 17000, 'Consommation', 'ratio', 'BS00394', NULL, 'Cynthia', '2026-07-24', 'Active'),
(417, 40000, 'Consommation', 'tear drop', 'BS00395', NULL, 'Cynthia', '2026-07-24', 'Active'),
(419, 40000, 'Consommation', 'representation dg', 'BS00397', NULL, 'Cynthia', '2026-07-24', 'Active'),
(420, 10000, 'Consommation', 'deplacement', 'BS00398', NULL, 'Cynthia', '2026-07-24', 'Active'),
(421, 131000, 'Consommation', 'representation dg', 'BS00399', NULL, 'Cynthia', '2026-07-24', 'Active'),
(422, 25000, 'Consommation', 'reparation noah', 'BS00400', NULL, 'Cynthia', '2026-07-24', 'Active'),
(423, 20000, 'Consommation', 'superglue', 'BS00401', NULL, 'Cynthia', '2026-07-24', 'Active'),
(424, 3000, 'Consommation', 'ratio', 'BS00402', NULL, 'Cynthia', '2026-07-24', 'Active'),
(425, 2500, 'Consommation', 'unite', 'BS00403', NULL, 'Cynthia', '2026-07-24', 'Active'),
(426, 165000, 'Approvisionnement', 'achat 6tshirt', 'BS00404', NULL, 'Cynthia', '2026-07-24', 'Active'),
(427, 10000, 'Consommation', 'pret don', 'BS00405', NULL, 'Cynthia', '2026-07-24', 'Active'),
(428, 2000, 'Consommation', 'water', 'BS00406', NULL, 'Cynthia', '2026-07-24', 'Active'),
(429, 24000, 'Consommation', 'representation dg', 'BS00407', NULL, 'Cynthia', '2026-07-24', 'Active'),
(430, 100000, 'Consommation', 'frs cash power', 'BS00408', NULL, 'Cynthia', '2026-07-24', 'Active'),
(431, 35000, 'Consommation', 'transport banner autocollant couche', 'BS00409', NULL, 'Cynthia', '2026-07-24', 'Active'),
(432, 10000, 'Consommation', 'terrain bbci', 'BS00410', NULL, 'Cynthia', '2026-07-24', 'Active'),
(433, 25000, 'Consommation', 'representation dg', 'BS00411', NULL, 'Cynthia', '2026-07-24', 'Active'),
(434, 7000, 'Consommation', 'water', 'BS00412', NULL, 'Cynthia', '2026-07-24', 'Active'),
(435, 3000, 'Consommation', 'cutting', 'BS00413', NULL, 'Cynthia', '2026-07-24', 'Active'),
(436, 60000, 'Consommation', 'superglue', 'BS00414', NULL, 'Cynthia', '2026-07-25', 'Active'),
(437, 90000, 'Consommation', 'ratio soir plus deplacement', 'BS00415', NULL, 'Cynthia', '2026-07-25', 'Active'),
(438, 21000, 'Consommation', 'representation dg', 'BS00416', NULL, 'Cynthia', '2026-07-25', 'Active'),
(439, 10000, 'Consommation', 'bbci terrain', 'BS00417', NULL, 'Cynthia', '2026-07-25', 'Active'),
(440, 10000, 'Consommation', 'deplacement', 'BS00418', NULL, 'Cynthia', '2026-07-25', 'Active'),
(441, 1000, 'Consommation', 'marqeur', 'BS00419', NULL, 'Cynthia', '2026-07-27', 'Active'),
(442, 10000, 'Consommation', 'ratio', 'BS00420', NULL, 'Cynthia', '2026-07-27', 'Active'),
(443, 10000, 'Consommation', 'représentation dg', 'BS00421', NULL, 'Cynthia', '2026-07-27', 'Active'),
(444, 5000, 'Consommation', 'moto', 'BS00422', NULL, 'Cynthia', '2026-07-27', 'Active'),
(445, 515000, 'Consommation', 'représentation dg', 'BS00423', NULL, 'Cynthia', '2026-07-27', 'Active'),
(446, 21500, 'Consommation', 'ratio', 'BS00424', NULL, 'Cynthia', '2026-07-27', 'Active'),
(447, 2000, 'Consommation', 'représentation dg', 'BS00425', NULL, 'Cynthia', '2026-07-27', 'Active'),
(448, 2000, 'Consommation', 'unite', 'BS00426', NULL, 'Cynthia', '2026-07-27', 'Active'),
(449, 12000, 'Consommation', 'representation dg', 'BS00427', NULL, 'Cynthia', '2026-07-27', 'Active'),
(450, 25000, 'Consommation', 'scoth', 'BS00428', NULL, 'Cynthia', '2026-07-27', 'Active'),
(451, 6000, 'Consommation', 'unite et entretien', 'BS00429', NULL, 'Cynthia', '2026-07-27', 'Active'),
(452, 83000, 'Approvisionnement', 'représentation dg', 'BS00430', NULL, 'Cynthia', '2026-07-27', 'Active'),
(453, 5000, 'Consommation', 'representation dg', 'BS00431', NULL, 'Cynthia', '2026-07-27', 'Active'),
(454, 25000, 'Consommation', 'transport soir', 'BS00432', NULL, 'Cynthia', '2026-07-28', 'Active'),
(455, 30000, 'Consommation', 'ratio', 'BS00433', NULL, 'Cynthia', '2026-07-28', 'Active'),
(456, 8000, 'Consommation', 'representation dg', 'BS00434', NULL, 'Cynthia', '2026-07-28', 'Active'),
(458, 9000, 'Consommation', 'water', 'BS00435', NULL, 'Cynthia', '2026-07-28', 'Active'),
(460, 35000, 'Consommation', 'porte clé jrs', 'BS00436', NULL, 'Cynthia', '2026-07-28', 'Active'),
(462, 16000, 'Consommation', 'cutteur', 'BS00437', NULL, 'Cynthia', '2026-07-28', 'Active'),
(463, 5000, 'Consommation', 'terrain bbci', 'BS00438', NULL, 'Cynthia', '2026-07-28', 'Active'),
(464, 26000, 'Approvisionnement', 'tshirt', 'BS00439', NULL, 'Cynthia', '2026-07-28', 'Active'),
(465, 40000, 'Consommation', 'porte cle', 'BS00440', NULL, 'Cynthia', '2026-07-28', 'Active'),
(466, 14000, 'Consommation', 'cutteur', 'BS00441', NULL, 'Cynthia', '2026-07-28', 'Active'),
(467, 9000, 'Consommation', 'water', 'BS00442', NULL, 'Cynthia', '2026-07-28', 'Active'),
(468, 3000, 'Consommation', 'city', 'BS00443', NULL, 'Cynthia', '2026-07-28', 'Active'),
(469, 8500, 'Consommation', 'representation dg', 'BS00444', NULL, 'Cynthia', '2026-07-28', 'Active'),
(470, 10000, 'Approvisionnement', 'aide', 'BS00445', NULL, 'Cynthia', '2026-07-28', 'Active'),
(471, 10000, 'Consommation', 'transport materiel', 'BS00446', NULL, 'Cynthia', '2026-07-28', 'Active'),
(472, 172000, 'Consommation', 'cash power unite', 'BS00447', NULL, 'Cynthia', '2026-07-28', 'Active'),
(473, 4500, 'Consommation', 'cordes 3', 'BS00448', NULL, 'Cynthia', '2026-07-29', 'Active'),
(474, 7500, 'Consommation', 'porte badges', 'BS00449', NULL, 'Cynthia', '2026-07-29', 'Active'),
(475, 350000, 'Consommation', 'pvc', 'BS00450', NULL, 'Cynthia', '2026-07-29', 'Active'),
(478, 25000, 'Consommation', 'papier photo autocollant', 'BS00451', NULL, 'Cynthia', '2026-07-29', 'Active'),
(479, 164000, 'Approvisionnement', 't-shirt 6', 'BS00452', NULL, 'Cynthia', '2026-07-29', 'Active'),
(480, 20000, 'Consommation', 'transport soir', 'BS00453', NULL, 'Cynthia', '2026-07-29', 'Active'),
(481, 5000, 'Consommation', 'terrain bbci', 'BS00454', NULL, 'Cynthia', '2026-07-29', 'Active'),
(482, 15000, 'Consommation', 'bbci terrain', 'BS00455', NULL, 'Cynthia', '2026-07-29', 'Active'),
(483, 100000, 'Consommation', 'achat DAO', 'BS00456', NULL, 'Cynthia', '2026-07-29', 'Active'),
(484, 33000, 'Consommation', 'RATIO', 'BS00457', NULL, 'Cynthia', '2026-07-29', 'Active'),
(485, 16000, 'Consommation', 'colle', 'BS00458', NULL, 'Cynthia', '2026-07-29', 'Active'),
(486, 5000, 'Consommation', 'ratio', 'BS00459', NULL, 'Cynthia', '2026-07-29', 'Active'),
(487, 9000, 'Consommation', 'representation dg', 'BS00460', NULL, 'Cynthia', '2026-07-29', 'Active'),
(488, 45000, 'Consommation', 'transport matériels', 'BS00461', NULL, 'Cynthia', '2026-07-29', 'Active'),
(489, 33500, 'Consommation', 'Assurance', 'BS00462', NULL, 'Cynthia', '2026-07-29', 'Active'),
(490, 5000, 'Consommation', 'bbci', 'BS00463', NULL, 'Cynthia', '2026-07-29', 'Active'),
(491, 266000, 'Consommation', 'dtf', 'BS00464', NULL, 'Cynthia', '2026-07-29', 'Active'),
(492, 70000, 'Consommation', 'transport ', 'BS00465', NULL, 'Cynthia', '2026-07-30', 'Active'),
(493, 10000, 'Consommation', 'superglue', 'BS00466', NULL, 'Cynthia', '2026-07-30', 'Active'),
(494, 5000, 'Consommation', 'serviette', 'BS00467', NULL, 'Cynthia', '2026-07-30', 'Active'),
(495, 100000, 'Consommation', 'commission de réception onpr ', 'BS00468', NULL, 'Cynthia', '2026-07-30', 'Active'),
(496, 31000, 'Consommation', 'ratio', 'BS00469', NULL, 'Cynthia', '2026-07-30', 'Active'),
(497, 10000, 'Consommation', 'mètre', 'BS00470', NULL, 'Cynthia', '2026-07-30', 'Active'),
(498, 2000, 'Consommation', 'stylos', 'BS00471', NULL, 'Cynthia', '2026-07-30', 'Active'),
(500, 15500, 'Consommation', 'tasse et sublimation', 'BS00473', NULL, 'Cynthia', '2026-07-30', 'Active'),
(502, 10000, 'Consommation', 'banque', 'BS00474', NULL, 'Cynthia', '2026-07-30', 'Active'),
(503, 111000, 'Consommation', 'lacoste3', 'BS00475', NULL, 'Cynthia', '2026-07-30', 'Active'),
(504, 20000, 'Consommation', 'superglue', 'BS00476', NULL, 'Cynthia', '2026-07-30', 'Active'),
(505, 2000, 'Consommation', 'autres charges', 'BS00477', NULL, 'Cynthia', '2026-07-30', 'Active'),
(507, 10500, 'Consommation', 'representation dg', 'BS00479', NULL, 'Cynthia', '2026-07-30', 'Active'),
(508, 10000, 'Consommation', 'moto', 'BS00480', NULL, 'Cynthia', '2026-07-30', 'Active'),
(510, 9000, 'Consommation', 'water', 'BS00481', NULL, 'Cynthia', '2026-07-30', 'Active'),
(511, 15000, 'Consommation', 'aide', 'BS00482', NULL, 'Cynthia', '2026-07-30', 'Active'),
(512, 24000, 'Consommation', 'representationdg', 'BS00483', NULL, 'Cynthia', '2026-07-30', 'Active'),
(513, 20000, 'Consommation', 'autres charges', 'BS00484', NULL, 'Cynthia', '2026-07-30', 'Active'),
(514, 40000, 'Consommation', 'facturier', 'BS00485', NULL, 'Cynthia', '2026-07-30', 'Active'),
(515, 1500, 'Consommation', 'unite', 'BS00486', NULL, 'Cynthia', '2026-07-31', 'Active'),
(516, 31000, 'Consommation', 'ratio', 'BS00487', NULL, 'Cynthia', '2026-07-31', 'Active'),
(518, 71000, 'Consommation', 'représentation dg', 'BS00489', NULL, 'Cynthia', '2026-07-31', 'Active'),
(519, 8000, 'Consommation', 'transport materiel', 'BS00490', NULL, 'Cynthia', '2026-07-31', 'Active'),
(520, 1000, 'Consommation', 'unite', 'BS00491', NULL, 'Cynthia', '2026-07-31', 'Active'),
(521, 10000, 'Consommation', 'échelle bbci', 'BS00492', NULL, 'Cynthia', '2026-07-31', 'Active'),
(522, 30000, 'Consommation', 'transport soir', 'BS00493', NULL, 'Cynthia', '2026-08-05', 'Active'),
(523, 15000, 'Consommation', 'paccy', 'BS00494', NULL, 'Cynthia', '2026-08-05', 'Active'),
(524, 15000, 'Consommation', 'lavage vitz', 'BS00495', NULL, 'Cynthia', '2026-08-05', 'Active'),
(525, 10000, 'Consommation', 'representation dg', 'BS00496', NULL, 'Cynthia', '2026-08-05', 'Active'),
(526, 2500, 'Consommation', 'mega', 'BS00497', NULL, 'Cynthia', '2026-08-05', 'Active'),
(527, 10000, 'Consommation', 'autres charges', 'BS00498', NULL, 'Cynthia', '2026-08-05', 'Active'),
(528, 10500, 'Consommation', 'water', 'BS00499', NULL, 'Cynthia', '2026-08-05', 'Active'),
(529, 33000, 'Consommation', 'branding', 'BS00500', NULL, 'Cynthia', '2026-08-05', 'Active'),
(530, 10000, 'Consommation', 'mairie', 'BS00501', NULL, 'Cynthia', '2026-08-05', 'Active'),
(531, 38000, 'Consommation', 'ratio', 'BS00502', NULL, 'Cynthia', '2026-08-05', 'Active'),
(532, 23000, 'Consommation', 'cutteur', 'BS00503', NULL, 'Cynthia', '2026-08-05', 'Active'),
(533, 20000, 'Consommation', 'desinfecta', 'BS00504', NULL, 'Cynthia', '2026-08-05', 'Active'),
(534, 42500, 'Consommation', 'assurance', 'BS00505', NULL, 'Cynthia', '2026-08-05', 'Active'),
(535, 23700, 'Consommation', 'representationn dg', 'BS00506', NULL, 'Cynthia', '2026-08-05', 'Active'),
(536, 50000, 'Consommation', 'encre cashet bic', 'BS00507', NULL, 'Cynthia', '2026-08-05', 'Active'),
(537, 35000, 'Consommation', 'transport soir', 'BS00508', NULL, 'Cynthia', '2026-08-05', 'Active'),
(538, 20000, 'Consommation', 'ratio aide', 'BS00509', NULL, 'Cynthia', '2026-08-05', 'Active'),
(539, 1500, 'Consommation', 'mega', 'BS00510', NULL, 'Cynthia', '2026-08-05', 'Active'),
(540, 3000, 'Consommation', 'cutting', 'BS00511', NULL, 'Cynthia', '2026-08-05', 'Active'),
(541, 9000, 'Consommation', 'water', 'BS00512', NULL, 'Cynthia', '2026-08-05', 'Active'),
(542, 20000, 'Consommation', 'reluire', 'BS00513', NULL, 'Cynthia', '2026-08-05', 'Active'),
(543, 5000, 'Consommation', 'prêt destin', 'BS00514', NULL, 'Cynthia', '2026-08-05', 'Active'),
(544, 6000, 'Consommation', 'pret don', 'BS00515', NULL, 'Cynthia', '2026-08-05', 'Active'),
(545, 30000, 'Consommation', 'représentation dg', 'BS00516', NULL, 'Cynthia', '2026-08-05', 'Active'),
(546, 53000, 'Consommation', 'frs materiels', 'BS00517', NULL, 'Cynthia', '2026-08-05', 'Active'),
(547, 31000, 'Consommation', 'représentation dg', 'BS00518', NULL, 'Cynthia', '2026-08-05', 'Active'),
(548, 15000, 'Consommation', 'transport soir', 'BS00519', NULL, 'Cynthia', '2026-08-06', 'Active'),
(549, 31000, 'Consommation', 'ratio', 'BS00520', NULL, 'Cynthia', '2026-08-06', 'Active'),
(550, 25000, 'Consommation', 'collé', 'BS00521', NULL, 'Cynthia', '2026-08-06', 'Active'),
(551, 2000, 'Consommation', 'savon', 'BS00522', NULL, 'Cynthia', '2026-08-06', 'Active'),
(552, 5000, 'Consommation', 'serviette', 'BS00523', NULL, 'Cynthia', '2026-08-06', 'Active'),
(553, 5000, 'Consommation', 'water', 'BS00524', NULL, 'Cynthia', '2026-08-06', 'Active'),
(555, 23000, 'Consommation', 'porte badge', 'BS00525', NULL, 'Cynthia', '2026-08-06', 'Active'),
(556, 15000, 'Consommation', 'moto', 'BS00526', NULL, 'Cynthia', '2026-08-06', 'Active'),
(557, 6000, 'Consommation', 'representation dg', 'BS00527', NULL, 'Cynthia', '2026-08-06', 'Active');
INSERT INTO `expenses` (`id`, `amount`, `expense_cat`, `purpose`, `id_proof`, `proof`, `operator`, `date`, `status`) VALUES
(558, 160000, 'Approvisionnement', 'achat 5lacostes', 'BS00528', NULL, 'Cynthia', '2026-08-06', 'Active'),
(559, 7500, 'Consommation', 'cordes ', 'BS00529', NULL, 'Cynthia', '2026-08-10', 'Active'),
(560, 42000, 'Consommation', 'ratio', 'BS00530', NULL, 'Cynthia', '2026-08-10', 'Active'),
(561, 20000, 'Approvisionnement', 'couche 350g', 'BS00531', NULL, 'Cynthia', '2026-08-10', 'Active'),
(562, 46000, 'Consommation', 'colle', 'BS00532', NULL, 'Cynthia', '2026-08-10', 'Active'),
(564, 135000, 'Consommation', 'autocollant soutraitance', 'BS00533', NULL, 'Cynthia', '2026-08-10', 'Active'),
(565, 97000, 'Consommation', 'contrôle technique', 'BS00534', NULL, 'Cynthia', '2026-08-10', 'Active'),
(566, 15000, 'Consommation', 'cordes', 'BS00535', NULL, 'Cynthia', '2026-08-10', 'Active'),
(567, 10000, 'Consommation', 'frais de terrain', 'BS00536', NULL, 'Cynthia', '2026-08-10', 'Active'),
(568, 3000, 'Consommation', 'city', 'BS00537', NULL, 'Cynthia', '2026-08-10', 'Active'),
(570, 12000, 'Consommation', 'transport materiels et entrertien', 'BS00539', NULL, 'Cynthia', '2026-08-10', 'Active'),
(571, 50000, 'Consommation', 'rembourssement ndaha', 'BS00540', NULL, 'Cynthia', '2026-08-10', 'Active'),
(572, 16000, 'Consommation', 'brosse', 'BS00541', NULL, 'Cynthia', '2026-08-10', 'Active'),
(573, 20500, 'Consommation', 'representation dg', 'BS00542', NULL, 'Cynthia', '2026-08-10', 'Active'),
(574, 3000, 'Consommation', 'unité ', 'BS00543', NULL, 'Cynthia', '2026-08-10', 'Active'),
(575, 2000, 'Consommation', 'water', 'BS00544', NULL, 'Cynthia', '2026-08-10', 'Active'),
(576, 10000, 'Consommation', 'aide', 'BS00545', NULL, 'Cynthia', '2026-08-10', 'Active'),
(577, 60000, 'Consommation', 'transport soir', 'BS00546', NULL, 'Cynthia', '2026-08-10', 'Active'),
(578, 5000, 'Consommation', 'city', 'BS00547', NULL, 'Cynthia', '2026-08-10', 'Active'),
(579, 50000, 'Consommation', 'renouveau', 'BS00548', NULL, 'Cynthia', '2026-08-10', 'Active'),
(580, 31000, 'Consommation', 'ratio', 'BS00549', NULL, 'Cynthia', '2026-08-10', 'Active'),
(581, 10000, 'Consommation', 'water', 'BS00550', NULL, 'Cynthia', '2026-08-10', 'Active'),
(582, 37000, 'Consommation', 'lacoste', 'BS00551', NULL, 'Cynthia', '2026-08-10', 'Active'),
(583, 25000, 'Consommation', 'tshirt simple', 'BS00552', NULL, 'Cynthia', '2026-08-10', 'Active'),
(585, 25000, 'Consommation', 'porte clé ', 'BS00553', NULL, 'Cynthia', '2026-08-10', 'Active'),
(586, 40000, 'Consommation', 'finition livre 4', 'BS00554', NULL, 'Cynthia', '2026-08-10', 'Active'),
(587, 42000, 'Consommation', 'moto', 'BS00555', NULL, 'Cynthia', '2026-08-10', 'Active'),
(588, 15000, 'Consommation', 'superglue', 'BS00556', NULL, 'Cynthia', '2026-08-10', 'Active'),
(589, 175000, 'Approvisionnement', 'achat 7 tshirt', 'BS00557', NULL, 'Cynthia', '2026-08-10', 'Active'),
(590, 435000, 'Consommation', 'frs matériels ', 'BS00558', NULL, 'Cynthia', '2026-08-10', 'Active'),
(591, 100000, 'Consommation', 'représentation bic', 'BS00559', NULL, 'Cynthia', '2026-08-10', 'Active'),
(592, 100000, 'Consommation', 'cash power', 'BS00560', NULL, 'Cynthia', '2026-08-10', 'Active'),
(593, 8000, 'Consommation', 'adaptateur', 'BS00561', NULL, 'Cynthia', '2026-08-10', 'Active'),
(594, 72500, 'Consommation', 'representation dg', 'BS00562', NULL, 'Cynthia', '2026-08-10', 'Active'),
(595, 2000, 'Consommation', 'unité ', 'BS00563', NULL, 'Cynthia', '2026-08-10', 'Active'),
(597, 30000, 'Consommation', 'transport soir', 'BS00564', NULL, 'Cynthia', '2026-08-10', 'Active'),
(598, 189000, 'Approvisionnement', 'tshirt 7', 'BS00565', NULL, 'Cynthia', '2026-08-10', 'Active'),
(599, 250000, 'Consommation', 'salaire', 'BS00566', NULL, 'Cynthia', '2026-08-10', 'Active'),
(600, 100000, 'Consommation', 'assistance sociale', 'BS00567', NULL, 'Cynthia', '2026-08-10', 'Active'),
(601, 5000, 'Consommation', 'impression', 'BS00568', NULL, 'Cynthia', '2026-08-10', 'Active'),
(602, 20000, 'Consommation', 'aide', 'BS00569', NULL, 'Cynthia', '2026-08-10', 'Active'),
(604, 10000, 'Consommation', 'isolant', 'BS00570', NULL, 'Cynthia', '2026-08-10', 'Active'),
(605, 10000, 'Consommation', 'représentation dg', 'BS00571', NULL, 'Cynthia', '2026-08-10', 'Active'),
(607, 5000, 'Consommation', 'déplacement', 'BS00573', NULL, 'Cynthia', '2026-08-10', 'Active'),
(608, 31000, 'Consommation', 'ratio', 'BS00574', NULL, 'Cynthia', '2026-08-10', 'Active'),
(609, 15000, 'Consommation', 'colle', 'BS00575', NULL, 'Cynthia', '2026-08-10', 'Active'),
(610, 15000, 'Consommation', 'sylicone', 'BS00576', NULL, 'Cynthia', '2026-08-10', 'Active'),
(611, 5000, 'Consommation', 'representation dg', 'BS00577', NULL, 'Cynthia', '2026-08-10', 'Active'),
(612, 56000, 'Consommation', 'représentation dg', 'BS00578', NULL, 'Cynthia', '2026-08-11', 'Active'),
(613, 207500, 'Consommation', 'frs cash power', 'BS00579', NULL, 'Cynthia', '2026-08-11', 'Active'),
(614, 327840, 'Consommation', 'frs matériels ', 'BS00580', NULL, 'Cynthia', '2026-08-11', 'Active'),
(615, 6000, 'Consommation', 'autres charges', 'BS00581', NULL, 'Cynthia', '2026-08-11', 'Active'),
(616, 10000, 'Consommation', 'ratio', 'BS00582', NULL, 'Cynthia', '2026-08-11', 'Active'),
(617, 40000, 'Consommation', 'transport soir', 'BS00583', NULL, 'Cynthia', '2026-08-11', 'Active'),
(618, 222000, 'Approvisionnement', 'achat 6tshirt', 'BS00584', NULL, 'Cynthia', '2026-08-11', 'Active'),
(619, 33000, 'Consommation', 'ratio', 'BS00585', NULL, 'Cynthia', '2026-08-11', 'Active'),
(620, 5000, 'Consommation', 'serviette', 'BS00586', NULL, 'Cynthia', '2026-08-11', 'Active'),
(621, 1000, 'Consommation', 'pille crayon', 'BS00587', NULL, 'Cynthia', '2026-08-11', 'Active'),
(622, 109000, 'Consommation', 'poudre dtf', 'BS00588', NULL, 'Cynthia', '2026-08-11', 'Active'),
(623, 50000, 'Consommation', 'sérigraphie ', 'BS00589', NULL, 'Cynthia', '2026-08-11', 'Active'),
(625, 22500, 'Consommation', 'transport materiel', 'BS00590', NULL, 'Cynthia', '2026-08-11', 'Active'),
(626, 20000, 'Consommation', 'représentation dg', 'BS00591', NULL, 'Cynthia', '2026-08-11', 'Active'),
(627, 10500, 'Approvisionnement', 'autres charges', 'BS00592', NULL, 'Cynthia', '2026-08-11', 'Active'),
(628, 27000, 'Approvisionnement', 't-shirt ', 'BS00593', NULL, 'Cynthia', '2026-08-11', 'Active'),
(629, 60000, 'Consommation', 'représentation dg', 'BS00594', NULL, 'Cynthia', '2026-08-11', 'Active'),
(630, 5500, 'Consommation', 'représentation dg', 'BS00595', NULL, 'Cynthia', '2026-08-11', 'Active'),
(631, 3000, 'Consommation', 'autre charges', 'BS00596', NULL, 'Cynthia', '2026-08-18', 'Active'),
(632, 20000, 'Consommation', 'transport soir', 'BS00597', NULL, 'Cynthia', '2026-08-18', 'Active'),
(633, 65000, 'Consommation', 'transport', 'BS00598', NULL, 'Cynthia', '2026-08-18', 'Active'),
(634, 37000, 'Consommation', 'ratio', 'BS00599', NULL, 'Cynthia', '2026-08-18', 'Active'),
(635, 13000, 'Consommation', 'autres charges', 'BS00600', NULL, 'Cynthia', '2026-08-18', 'Active'),
(636, 12000, 'Consommation', 'transport materiel', 'BS00601', NULL, 'Cynthia', '2026-08-18', 'Active'),
(637, 7000, 'Consommation', 'aide', 'BS00602', NULL, 'Cynthia', '2026-08-18', 'Active'),
(638, 10000, 'Consommation', 'serigraphie', 'BS00603', NULL, 'Cynthia', '2026-08-18', 'Active'),
(639, 280000, 'Approvisionnement', 'achat t-shirt 12', 'BS00604', NULL, 'Cynthia', '2026-08-18', 'Active'),
(640, 62500, 'Consommation', 'représentation dg', 'BS00605', NULL, 'Cynthia', '2026-08-18', 'Active'),
(641, 3200, 'Consommation', 'frais de retrait', 'BS00606', NULL, 'Cynthia', '2026-08-18', 'Active'),
(643, 35500, 'Consommation', 'rario', 'BS00607', NULL, 'Cynthia', '2026-08-18', 'Active'),
(644, 24000, 'Consommation', 'gullotte fiche mol', 'BS00608', NULL, 'Cynthia', '2026-08-18', 'Active'),
(645, 20000, 'Consommation', 'serigraphie', 'BS00609', NULL, 'Cynthia', '2026-08-18', 'Active'),
(646, 5000, 'Consommation', 'sac', 'BS00610', NULL, 'Cynthia', '2026-08-18', 'Active'),
(647, 6000, 'Consommation', 'matériel ,entretien', 'BS00611', NULL, 'Cynthia', '2026-08-18', 'Active'),
(648, 5000, 'Consommation', 'transport matin', 'BS00612', NULL, 'Cynthia', '2026-08-18', 'Active'),
(649, 121000, 'Consommation', 'representation dg', 'BS00613', NULL, 'Cynthia', '2026-08-18', 'Active'),
(650, 31000, 'Consommation', 'ratio', 'BS00614', NULL, 'Cynthia', '2026-08-18', 'Active'),
(651, 70000, 'Consommation', 'gourde', 'BS00615', NULL, 'Cynthia', '2026-08-18', 'Active'),
(652, 2500, 'Approvisionnement', 'représentation dg', 'BS00616', NULL, 'Cynthia', '2026-08-18', 'Active'),
(653, 50000, 'Consommation', 'finition livre enabel', 'BS00617', NULL, 'Cynthia', '2026-08-18', 'Active'),
(654, 20000, 'Consommation', 'entretien', 'BS00618', NULL, 'Cynthia', '2026-08-18', 'Active'),
(655, 100000, 'Consommation', 'frais des honoraires', 'BS00619', NULL, 'Cynthia', '2026-08-18', 'Active'),
(656, 35000, 'Consommation', 'transport', 'BS00620', NULL, 'Cynthia', '2026-08-18', 'Active'),
(657, 100000, 'Consommation', 'cash power', 'BS00621', NULL, 'Cynthia', '2026-08-18', 'Active'),
(658, 10000, 'Consommation', 'transport', 'BS00622', NULL, 'Cynthia', '2026-08-18', 'Active'),
(659, 41000, 'Consommation', 'ratio', 'BS00623', NULL, 'Cynthia', '2026-08-18', 'Active'),
(660, 20000, 'Consommation', 'clous', 'BS00624', NULL, 'Cynthia', '2026-08-18', 'Active'),
(661, 25000, 'Consommation', 'facturier', 'BS00625', NULL, 'Cynthia', '2026-08-18', 'Active'),
(662, 10000, 'Consommation', 'autres charges', 'BS00626', NULL, 'Cynthia', '2026-08-18', 'Active'),
(663, 92000, 'Consommation', 'achat t-shirt ', 'BS00627', NULL, 'Cynthia', '2026-08-18', 'Active'),
(664, 2000, 'Consommation', 'transport materiel', 'BS00628', NULL, 'Cynthia', '2026-08-18', 'Active'),
(665, 20000, 'Consommation', 'cashet', 'BS00629', NULL, 'Cynthia', '2026-08-18', 'Active'),
(666, 62000, 'Consommation', 'representation dg', 'BS00630', NULL, 'Cynthia', '2026-08-18', 'Active');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `invoice_infos`
--

CREATE TABLE `invoice_infos` (
  `invoice_number` varchar(50) NOT NULL,
  `delai` varchar(100) NOT NULL,
  `mode_paiement` varchar(100) NOT NULL,
  `penalite` varchar(100) NOT NULL,
  `tva` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `invoice_infos`
--

INSERT INTO `invoice_infos` (`invoice_number`, `delai`, `mode_paiement`, `penalite`, `tva`, `created_at`) VALUES
('0001', '14 jours', 'Virement BCB 00200105087', '10% sur 14 jours', '18% strictement applicable', '2026-03-05 23:48:30'),
('0002', '14 jours', 'Virement BCB 00200105087', '10% sur 14 jours', '18% strictement applicable', '2026-06-05 10:54:12'),
('0003', '14 jours', 'Virement BCB 00200105087', '10% sur 14 jours', '18% strictement applicable', '2026-06-05 10:55:10'),
('0008', '14 jours', 'Virement BCB 00200105087', '10% sur 14 jours', '18% strictement applicable', '2026-05-11 13:40:51'),
('0019', '14 jours', 'Virement BCB 00200105087', '10% sur 14 jours', '18% strictement applicable', '2026-06-29 10:25:36'),
('0030', '14 jours', 'Virement BCB 00200105087', '10% sur 14 jours', '18% strictement applicable', '2026-06-30 16:09:01'),
('0031', '14 jours', 'Virement BCB 00200105087', '10% sur 14 jours', '18% strictement applicable', '2026-05-22 15:28:38'),
('0053', '14 jours', 'Virement BCB 00200105087', '10% sur 14 jours', '18% strictement applicable', '2026-05-25 16:00:05'),
('0070', '14 jours', 'Virement BCB 00200105087', '10% sur 14 jours', '18% strictement applicable', '2026-07-08 13:05:29'),
('0071', '14 jours', 'Virement BCB 00200105087', '10% sur 14 jours', '18% strictement applicable', '2026-05-28 16:55:19'),
('0072', '14 jours', 'Virement BCB 00200105087', '10% sur 14 jours', '18% strictement applicable', '2026-07-08 13:06:41'),
('0079', '14 jours', 'Virement BCB 00200105087', '10% sur 14 jours', '18% strictement applicable', '2026-05-29 16:45:05'),
('0086', '14 jours', 'Virement BCB 00200105087', '10% sur 14 jours', '18% strictement applicable', '2026-07-09 16:20:17'),
('0113', '14 jours', 'Virement BCB 00200105087', '10% sur 14 jours', '18% strictement applicable', '2026-07-13 08:45:21'),
('0115', '14 jours', 'Virement BCB 00200105087', '10% sur 14 jours', '18% strictement applicable', '2026-06-05 15:05:05'),
('0117', '14 jours', 'Virement BCB 00200105087', '10% sur 14 jours', '18% strictement applicable', '2026-06-05 15:36:00'),
('0251', '14 jours', 'Virement BCB 00200105087', '10% sur 14 jours', '18% strictement applicable', '2026-08-10 09:55:00');

-- --------------------------------------------------------

--
-- Structure de la table `licenses`
--

CREATE TABLE `licenses` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `drive_link` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','ready') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `licenses`
--

INSERT INTO `licenses` (`id`, `email`, `drive_link`, `created_at`, `status`) VALUES
(1, 'nimubona71@gmail.com', '', '2026-03-01 23:59:15', 'pending');

-- --------------------------------------------------------

--
-- Structure de la table `logintos`
--

CREATE TABLE `logintos` (
  `id` int(11) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `action_type` varchar(100) NOT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `ip_adress` varchar(255) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `descrip` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `logintos`
--

INSERT INTO `logintos` (`id`, `user_id`, `action_type`, `session_id`, `ip_adress`, `user_agent`, `descrip`) VALUES
(1, 'Alexis', 'Déconnexion', NULL, NULL, NULL, '24/06/2026 16:19'),
(2, 'Cynthia', 'Connexion au système', 'u0mc3470mlh2fmupc987tbc93c', '192.168.88.37', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.75 Safari/537.36 Edg/100.0.1185.36', '24/06/2026 16:19 - Redirect: license_valid'),
(3, 'Cynthia', 'Connexion au système', 'cf5usc659bfuu83nvbruehss5l', '192.168.88.36', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', '24/06/2026 16:21 - Redirect: license_valid'),
(4, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '24/06/2026 16:36'),
(5, 'Isabelle', 'Connexion au système', '1kuebcb9938sr0buqibjkojk4g', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '24/06/2026 16:42 - Redirect: license_valid'),
(6, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '24/06/2026 16:49'),
(7, 'Cynthia', 'Connexion au système', 'cf5usc659bfuu83nvbruehss5l', '192.168.88.36', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', '24/06/2026 16:49 - Redirect: license_valid'),
(8, 'Isabelle', 'Connexion au système', 'cf5usc659bfuu83nvbruehss5l', '192.168.88.36', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', '24/06/2026 16:49 - Redirect: license_valid'),
(9, 'Cynthia', 'Connexion au système', '4du6bv0rmvfja52bc0jvfg1urp', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '24/06/2026 16:51 - Redirect: license_valid'),
(10, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '24/06/2026 16:52'),
(11, 'Isabelle', 'Connexion au système', '4du6bv0rmvfja52bc0jvfg1urp', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '24/06/2026 16:52 - Redirect: license_valid'),
(12, 'Cynthia', 'Connexion au système', '712i522q0kq8a2sc9kd63et6s4', '192.168.88.38', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', '24/06/2026 16:57 - Redirect: license_valid'),
(13, 'Isabelle', 'Connexion au système', 'en4hiboo51bji58auftpkcmkm8', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '24/06/2026 17:12 - Redirect: license_valid'),
(14, 'Isabelle', 'Connexion au système', '4rghmtmgt4e6e828kf9qcdb3f3', '192.168.88.37', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.75 Safari/537.36 Edg/100.0.1185.36', '24/06/2026 17:32 - Redirect: license_valid'),
(15, 'Isabelle', 'Connexion au système', 'vk8315n1iqhbf3n0kuulb7qubb', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '24/06/2026 17:35 - Redirect: license_valid'),
(16, 'Isabelle', 'Déconnexion', NULL, NULL, NULL, '24/06/2026 17:37'),
(17, 'Isabelle', 'Déconnexion', NULL, NULL, NULL, '24/06/2026 17:40'),
(18, 'Cynthia', 'Connexion au système', '4du6bv0rmvfja52bc0jvfg1urp', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '24/06/2026 17:41 - Redirect: license_valid'),
(19, 'Isabelle', 'Connexion au système', '13agun3u21hr5p4mkstibkqkho', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '25/06/2026 10:32 - Redirect: license_valid'),
(20, 'Cynthia', 'Connexion au système', 'ne8ii0l8jlvbtdhevvoo496g3t', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '25/06/2026 10:35 - Redirect: license_valid'),
(21, 'Cynthia', 'Connexion au système', 'ne8ii0l8jlvbtdhevvoo496g3t', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '25/06/2026 10:40 - Redirect: license_valid'),
(22, 'Cynthia', 'Connexion au système', 'ne8ii0l8jlvbtdhevvoo496g3t', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '25/06/2026 10:41 - Redirect: license_valid'),
(23, 'Isabelle', 'Connexion au système', '1m1llm2qv69rffmg6ajo97fhbl', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '25/06/2026 10:43 - Redirect: license_valid'),
(24, 'Cynthia', 'Connexion au système', 'ne8ii0l8jlvbtdhevvoo496g3t', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '25/06/2026 10:55 - Redirect: license_valid'),
(25, 'Isabelle', 'Connexion au système', '0c1j4hiu1v2r9nsd4196jhcd3t', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '25/06/2026 11:50 - Redirect: license_valid'),
(26, 'Cynthia', 'Connexion au système', 'ne8ii0l8jlvbtdhevvoo496g3t', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '25/06/2026 12:55 - Redirect: license_valid'),
(27, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '25/06/2026 13:54'),
(28, 'Isabelle', 'Connexion au système', 'ne8ii0l8jlvbtdhevvoo496g3t', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '25/06/2026 13:54 - Redirect: license_valid'),
(29, 'Isabelle', 'Déconnexion', NULL, NULL, NULL, '25/06/2026 13:55'),
(30, 'Cynthia', 'Connexion au système', 'ne8ii0l8jlvbtdhevvoo496g3t', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '25/06/2026 13:55 - Redirect: license_valid'),
(31, 'Isabelle', 'Connexion au système', 'ne8ii0l8jlvbtdhevvoo496g3t', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '25/06/2026 13:55 - Redirect: license_valid'),
(32, 'Isabelle', 'Déconnexion', NULL, NULL, NULL, '25/06/2026 13:58'),
(33, 'Cynthia', 'Connexion au système', 'ne8ii0l8jlvbtdhevvoo496g3t', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '25/06/2026 13:58 - Redirect: license_valid'),
(34, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '25/06/2026 13:59'),
(35, 'Isabelle', 'Connexion au système', 'ne8ii0l8jlvbtdhevvoo496g3t', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '25/06/2026 13:59 - Redirect: license_valid'),
(36, 'Isabelle', 'Déconnexion', NULL, NULL, NULL, '25/06/2026 14:00'),
(37, 'Cynthia', 'Connexion au système', 'ne8ii0l8jlvbtdhevvoo496g3t', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '25/06/2026 14:01 - Redirect: license_valid'),
(38, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '25/06/2026 14:04'),
(39, 'Isabelle', 'Connexion au système', 'ne8ii0l8jlvbtdhevvoo496g3t', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '25/06/2026 14:05 - Redirect: license_valid'),
(40, 'Isabelle', 'Connexion au système', '00s7kmq2ddgv057abbu63gsor5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '25/06/2026 14:08 - Redirect: license_valid'),
(41, 'Cynthia', 'Connexion au système', 'ivsaogbh8bk8qo59ipqt1u0ira', '192.168.88.31', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', '25/06/2026 14:08 - Redirect: license_valid'),
(42, 'Isabelle', 'Connexion au système', '00s7kmq2ddgv057abbu63gsor5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '25/06/2026 14:10 - Redirect: license_valid'),
(43, 'Isabelle', 'Déconnexion', NULL, NULL, NULL, '25/06/2026 14:11'),
(44, 'Cynthia', 'Connexion au système', 'ne8ii0l8jlvbtdhevvoo496g3t', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '25/06/2026 14:11 - Redirect: license_valid'),
(45, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '25/06/2026 14:11'),
(46, 'Isabelle', 'Connexion au système', 'ne8ii0l8jlvbtdhevvoo496g3t', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '25/06/2026 14:11 - Redirect: license_valid'),
(47, 'Isabelle', 'Déconnexion', NULL, NULL, NULL, '25/06/2026 14:13'),
(48, 'Cynthia', 'Connexion au système', 'ne8ii0l8jlvbtdhevvoo496g3t', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '25/06/2026 14:14 - Redirect: license_valid'),
(49, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '25/06/2026 14:14'),
(50, 'Isabelle', 'Connexion au système', 'ne8ii0l8jlvbtdhevvoo496g3t', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '25/06/2026 14:14 - Redirect: license_valid'),
(51, 'Cynthia', 'Connexion au système', 'eidtquvngaraadqnl42iu3le0n', '192.168.88.31', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36 EdgA/149.0.0.0', '25/06/2026 14:30 - Redirect: license_valid'),
(52, 'Isabelle', 'Déconnexion', NULL, NULL, NULL, '25/06/2026 14:31'),
(53, 'Cynthia', 'Connexion au système', 'ne8ii0l8jlvbtdhevvoo496g3t', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '25/06/2026 14:31 - Redirect: license_valid'),
(54, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '25/06/2026 14:32'),
(55, 'Isabelle', 'Connexion au système', 'ne8ii0l8jlvbtdhevvoo496g3t', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '25/06/2026 14:32 - Redirect: license_valid'),
(56, 'Isabelle', 'Déconnexion', NULL, NULL, NULL, '25/06/2026 14:37'),
(57, 'Cynthia', 'Connexion au système', 'ne8ii0l8jlvbtdhevvoo496g3t', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '25/06/2026 14:37 - Redirect: license_valid'),
(58, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '25/06/2026 14:38'),
(59, 'Isabelle', 'Connexion au système', 'eidtquvngaraadqnl42iu3le0n', '192.168.88.31', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36 EdgA/149.0.0.0', '25/06/2026 14:38 - Redirect: license_valid'),
(60, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '25/06/2026 14:39'),
(61, 'Isabelle', 'Connexion au système', 'ivsaogbh8bk8qo59ipqt1u0ira', '192.168.88.31', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', '25/06/2026 14:39 - Redirect: license_valid'),
(62, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '25/06/2026 14:43'),
(63, 'Cynthia', 'Connexion au système', 'ne8ii0l8jlvbtdhevvoo496g3t', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '25/06/2026 14:43 - Redirect: license_valid'),
(64, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '25/06/2026 14:47'),
(65, 'Isabelle', 'Connexion au système', 'ne8ii0l8jlvbtdhevvoo496g3t', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '25/06/2026 14:47 - Redirect: license_valid'),
(66, 'Isabelle', 'Déconnexion', NULL, NULL, NULL, '25/06/2026 18:15'),
(67, 'Cynthia', 'Connexion au système', 'ne8ii0l8jlvbtdhevvoo496g3t', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '25/06/2026 18:16 - Redirect: license_valid'),
(68, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '25/06/2026 19:01'),
(69, 'Cynthia', 'Connexion au système', '84jjbh86s6nvis1ob7clt146ff', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '26/06/2026 09:12 - Redirect: license_valid'),
(70, 'Isabelle', 'Connexion au système', '06eq78l7h1ldai7lbqjn6n92v0', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '26/06/2026 09:27 - Redirect: license_valid'),
(71, 'Isabelle', 'Connexion au système', 'rs709rgf78510egcn0s2d7qq6h', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '26/06/2026 16:39 - Redirect: license_valid'),
(72, 'Isabelle', 'Connexion au système', 'ue53423qrb37660njv5vior6nq', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '26/06/2026 17:48 - Redirect: license_valid'),
(73, 'Isabelle', 'Connexion au système', 'rp0hd7sjmf041ublrbiu8nbb1v', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '26/06/2026 17:56 - Redirect: license_valid'),
(74, 'Isabelle', 'Connexion au système', 'lbc3c2vbh6np96od631cmrjq69', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '26/06/2026 18:07 - Redirect: license_valid'),
(75, 'Isabelle', 'Connexion au système', 'v8j55mrronije3olu902qjgfns', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '26/06/2026 18:12 - Redirect: license_valid'),
(76, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '26/06/2026 20:03'),
(77, 'Isabelle', 'Connexion au système', 'cp52o933sdsl9ac3bjjmdclfn2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '29/06/2026 08:49 - Redirect: license_valid'),
(78, 'Isabelle', 'Connexion au système', 'ensfcjasq670fd42bvufjb8eva', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '29/06/2026 11:13 - Redirect: license_valid'),
(79, 'Cynthia', 'Connexion au système', 'vl908u936osv4bbod8nnb6st6c', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '29/06/2026 11:17 - Redirect: license_valid'),
(80, 'Isabelle', 'Connexion au système', 'oleobc0jkbqimv2j2r6lahob8j', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '29/06/2026 12:23 - Redirect: license_valid'),
(81, 'Isabelle', 'Connexion au système', '09j2j0b2p2jq320ccdimj9bibr', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '29/06/2026 12:54 - Redirect: license_valid'),
(82, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '29/06/2026 13:30'),
(83, 'Isabelle', 'Connexion au système', 'vl908u936osv4bbod8nnb6st6c', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '29/06/2026 13:30 - Redirect: license_valid'),
(84, 'Isabelle', 'Déconnexion', NULL, NULL, NULL, '29/06/2026 13:32'),
(85, 'Cynthia', 'Connexion au système', 'vl908u936osv4bbod8nnb6st6c', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '29/06/2026 13:32 - Redirect: license_valid'),
(86, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '29/06/2026 13:34'),
(87, 'Isabelle', 'Connexion au système', 'vl908u936osv4bbod8nnb6st6c', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '29/06/2026 13:34 - Redirect: license_valid'),
(88, 'Isabelle', 'Déconnexion', NULL, NULL, NULL, '29/06/2026 13:36'),
(89, 'Cynthia', 'Connexion au système', 'vl908u936osv4bbod8nnb6st6c', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '29/06/2026 13:37 - Redirect: license_valid'),
(90, 'Isabelle', 'Connexion au système', '84r5vb3676qbujshvfe2pej859', '192.168.88.43', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '29/06/2026 13:37 - Redirect: license_valid'),
(91, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '29/06/2026 13:37'),
(92, 'Isabelle', 'Connexion au système', 'vl908u936osv4bbod8nnb6st6c', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '29/06/2026 13:38 - Redirect: license_valid'),
(93, 'Isabelle', 'Déconnexion', NULL, NULL, NULL, '29/06/2026 13:50'),
(94, 'Cynthia', 'Connexion au système', 'vl908u936osv4bbod8nnb6st6c', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '29/06/2026 13:50 - Redirect: license_valid'),
(95, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '29/06/2026 14:17'),
(96, 'Isabelle', 'Connexion au système', 'vl908u936osv4bbod8nnb6st6c', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '29/06/2026 14:17 - Redirect: license_valid'),
(97, 'Isabelle', 'Déconnexion', NULL, NULL, NULL, '29/06/2026 14:20'),
(98, 'Cynthia', 'Connexion au système', 'vl908u936osv4bbod8nnb6st6c', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '29/06/2026 14:20 - Redirect: license_valid'),
(99, 'Isabelle', 'Connexion au système', 'po03rp9hs61v00hpb8uovrdpl2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '29/06/2026 15:15 - Redirect: license_valid'),
(100, 'Cynthia', 'Connexion au système', '8j3gm943o82q3nnvp5peoktu8l', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '29/06/2026 15:25 - Redirect: license_valid'),
(101, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '29/06/2026 19:05'),
(102, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '29/06/2026 19:05'),
(103, 'Cynthia', 'Connexion au système', 'l51jkien3hac24ht0qsseabftn', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '30/06/2026 12:20 - Redirect: license_valid'),
(104, 'Cynthia', 'Connexion au système', 'l51jkien3hac24ht0qsseabftn', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '30/06/2026 15:16 - Redirect: license_valid'),
(105, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '30/06/2026 16:37'),
(106, 'Dev', 'Connexion au système', 'l51jkien3hac24ht0qsseabftn', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '30/06/2026 16:38 - Redirect: license_valid'),
(107, 'Dev', 'Déconnexion', NULL, NULL, NULL, '30/06/2026 16:40'),
(108, 'Cynthia', 'Connexion au système', 'l51jkien3hac24ht0qsseabftn', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '30/06/2026 16:40 - Redirect: license_valid'),
(109, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '30/06/2026 19:42'),
(110, 'Isabelle', 'Connexion au système', '3mmhc170c9mkp848usmpo3uejp', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '02/07/2026 10:52 - Redirect: license_valid'),
(111, 'Cynthia', 'Connexion au système', 'uoca87bfuslsrtjg8ap9iftf9a', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '02/07/2026 11:37 - Redirect: license_valid'),
(112, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '02/07/2026 19:35'),
(113, 'Isabelle', 'Connexion au système', 'p4a58r91f3gfc9surpjgc8dkq4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '06/07/2026 12:24 - Redirect: license_valid'),
(114, 'Cynthia', 'Connexion au système', 'q6qocek30o8rc2vb8v6slb3tgn', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '06/07/2026 12:46 - Redirect: license_valid'),
(115, 'Isabelle', 'Connexion au système', '6fv92lvst8226u00osu0lcor7a', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '06/07/2026 12:48 - Redirect: license_valid'),
(116, 'Isabelle', 'Connexion au système', '35qbv1vsnosi487ncpe96gilof', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '06/07/2026 16:19 - Redirect: license_valid'),
(117, 'Isabelle', 'Connexion au système', 'pgedomk4e6qql7svvcef0ussta', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '06/07/2026 16:20 - Redirect: license_valid'),
(118, 'Isabelle', 'Connexion au système', 'nc7udgco83nehckgif6kjemtpk', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '06/07/2026 18:04 - Redirect: license_valid'),
(119, 'Isabelle', 'Connexion au système', '60qfinh9b2uoa3c2qa9ie81iuf', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '07/07/2026 09:05 - Redirect: license_valid'),
(120, 'Isabelle', 'Connexion au système', 'i6qel4his1h2jb4s1358fi9h4u', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '07/07/2026 09:31 - Redirect: license_valid'),
(121, 'Cynthia', 'Connexion au système', 'vgd7mnkh4csqojrlapvu520nfv', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '07/07/2026 10:21 - Redirect: license_valid'),
(122, 'Isabelle', 'Connexion au système', 'm9of43a8bag23g8i3q5e3fo2rc', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '07/07/2026 12:27 - Redirect: license_valid'),
(123, 'Isabelle', 'Connexion au système', 'orgklc1jr9sbdc5dtc2b1mul2f', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '07/07/2026 13:30 - Redirect: license_valid'),
(124, 'Isabelle', 'Connexion au système', '2vkesmj4hj1lefe1io1m2kqd4l', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '07/07/2026 16:22 - Redirect: license_valid'),
(125, 'Isabelle', 'Connexion au système', 'qjglpgsr4cu3bo5prr6i4ij5ce', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '08/07/2026 08:47 - Redirect: license_valid'),
(126, 'Isabelle', 'Connexion au système', 'ul05agrp858uj3h2pd0pmnlsv9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '08/07/2026 09:05 - Redirect: license_valid'),
(127, 'Isabelle', 'Connexion au système', 'oc5kht70i2ekg9vrq85emre9dn', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '08/07/2026 14:36 - Redirect: license_valid'),
(128, 'Isabelle', 'Connexion au système', 'p96pof11k4qcoha7imo0q2r1b3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '08/07/2026 16:57 - Redirect: license_valid'),
(129, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '08/07/2026 18:53'),
(130, 'Isabelle', 'Connexion au système', 'ligclr9gu5hr77fomo4kfeng28', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '09/07/2026 10:20 - Redirect: license_valid'),
(131, 'Isabelle', 'Connexion au système', 'kapq4ajf7ma2uq8iht1ji1ipcv', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '09/07/2026 11:34 - Redirect: license_valid'),
(132, 'Cynthia', 'Connexion au système', 'sbuj4bgbri1tv0k49l88pqj13g', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '09/07/2026 15:39 - Redirect: license_valid'),
(133, 'Isabelle', 'Connexion au système', '36un7no1nq9ehgl5b3qou44f08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '09/07/2026 16:07 - Redirect: license_valid'),
(134, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '09/07/2026 18:31'),
(135, 'Isabelle', 'Connexion au système', 'pqfmab21e0alhboh67k37csed2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '10/07/2026 10:22 - Redirect: license_valid'),
(136, 'Cynthia', 'Connexion au système', 'dcdbosogccbeisjfv1572f98eu', '192.168.88.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '10/07/2026 14:18 - Redirect: license_valid'),
(137, 'Isabelle', 'Connexion au système', 'e3lpua5sm3j1m15n0bi3q6hh2s', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '10/07/2026 16:28 - Redirect: license_valid'),
(138, 'Isabelle', 'Connexion au système', 'oarl1thsit13ec44c2s2e1k699', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '10/07/2026 16:55 - Redirect: license_valid'),
(139, 'Isabelle', 'Connexion au système', '01bfksn7vili2577ardfm1ue7q', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '10/07/2026 17:18 - Redirect: license_valid'),
(140, 'Isabelle', 'Connexion au système', 'b71s96e6pt1nvuph8s0uvsqk01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '13/07/2026 08:48 - Redirect: license_valid'),
(141, 'Cynthia', 'Connexion au système', 'v5qhqhs71skv1gmbh6sannl2j4', '192.168.88.33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '13/07/2026 08:51 - Redirect: license_valid'),
(142, 'Isabelle', 'Connexion au système', 'inc8k39j1rddqmkb5sqrrif0ju', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '13/07/2026 08:52 - Redirect: license_valid'),
(143, 'Isabelle', 'Connexion au système', '9va053u111qp5llpjmel8br7jp', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '13/07/2026 10:26 - Redirect: license_valid'),
(144, 'Isabelle', 'Connexion au système', '9osg01dsil185h7u718e6cfo0q', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '13/07/2026 11:05 - Redirect: license_valid'),
(145, 'Cynthia', 'Connexion au système', 'k3d35p1rt9fl350e6noot2f8nc', '192.168.88.33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '13/07/2026 12:24 - Redirect: license_valid'),
(146, 'Isabelle', 'Connexion au système', 'v9ipmll2u3j5mgd6dqvv6r06kn', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '13/07/2026 15:21 - Redirect: license_valid'),
(147, 'Isabelle', 'Connexion au système', 'fra2qcbusnl1obr287hbm438ts', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '13/07/2026 15:44 - Redirect: license_valid'),
(148, 'Isabelle', 'Connexion au système', 'apme19lbihemq2e2vta33u1pdv', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '13/07/2026 16:25 - Redirect: license_valid'),
(149, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '13/07/2026 19:01'),
(150, 'Isabelle', 'Connexion au système', 'd235lrfqp723sf6t24nlheg7sh', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '14/07/2026 08:47 - Redirect: license_valid'),
(151, 'Cynthia', 'Connexion au système', 'vnqriqctvaquod38ojgq08ecun', '192.168.88.33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '14/07/2026 09:28 - Redirect: license_valid'),
(152, 'Isabelle', 'Connexion au système', '6ua74gd5223onqm2kqn7b0ngkf', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '14/07/2026 10:20 - Redirect: license_valid'),
(153, 'Isabelle', 'Connexion au système', 'bvkf88if7ra9a1i3n1q7fo4aut', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '14/07/2026 11:53 - Redirect: license_valid'),
(154, 'Isabelle', 'Connexion au système', 'eqa2m247tlfvehlu35ircjdbc2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '14/07/2026 13:11 - Redirect: license_valid'),
(155, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '14/07/2026 18:07'),
(156, 'Cynthia', 'Connexion au système', 't0qmngaksj9h96hti5ge2g7pcr', '192.168.88.33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '15/07/2026 09:04 - Redirect: license_valid'),
(157, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '15/07/2026 20:00'),
(158, 'Isabelle', 'Connexion au système', 'r3p644a446tncu0ec7inkvmtu2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '16/07/2026 09:12 - Redirect: license_valid'),
(159, 'Cynthia', 'Connexion au système', 'n4sr7puqlbudmro4ru6ptg1036', '192.168.88.33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '16/07/2026 11:01 - Redirect: license_valid'),
(160, 'Isabelle', 'Connexion au système', 'ovabi9uc47agqa1ri7mdj5nuuq', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '16/07/2026 12:51 - Redirect: license_valid'),
(161, 'Isabelle', 'Connexion au système', 'av1frhbk4cl16rvo016ibi49kc', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '16/07/2026 13:41 - Redirect: license_valid'),
(162, 'Isabelle', 'Connexion au système', 'm8r5ufg8tcuhhpfippvdsl6hcb', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '16/07/2026 15:20 - Redirect: license_valid'),
(163, 'Isabelle', 'Connexion au système', 's7g1o247nb494810n9pbq1oan3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '16/07/2026 17:20 - Redirect: license_valid'),
(164, 'Isabelle', 'Connexion au système', 'pkjp1dc3nieklcopg3g0mm6g6g', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '17/07/2026 10:02 - Redirect: license_valid'),
(165, 'Cynthia', 'Connexion au système', 'ac031eh29ifm6mgn9c6kp9rng7', '192.168.88.33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '17/07/2026 10:49 - Redirect: license_valid'),
(166, 'Isabelle', 'Connexion au système', '0g81uhp75jhgag6h50c4qak4h7', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '17/07/2026 13:53 - Redirect: license_valid'),
(167, 'Isabelle', 'Connexion au système', 'jhqsjl3q59paoss3mugsvdn9bj', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '17/07/2026 15:26 - Redirect: license_valid'),
(168, 'Isabelle', 'Connexion au système', '996dfm3ku7vl0lfe1blie14jrc', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '17/07/2026 16:27 - Redirect: license_valid'),
(169, 'Isabelle', 'Connexion au système', 'smufiinggascnd9krkfljops1j', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '17/07/2026 17:24 - Redirect: license_valid'),
(170, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '17/07/2026 19:58'),
(171, 'Cynthia', 'Connexion au système', '3d6c6c5kec73ub1ssnib8mgkjk', '192.168.88.33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '20/07/2026 08:53 - Redirect: license_valid'),
(172, 'Isabelle', 'Connexion au système', 'anifahmocorvv13v4l3nconb0i', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '20/07/2026 08:59 - Redirect: license_valid'),
(173, 'Isabelle', 'Connexion au système', '6qmnagtase1tl5l40ufqi8jcc4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '20/07/2026 11:40 - Redirect: license_valid'),
(174, 'Isabelle', 'Connexion au système', 'ems18c74cibhfjng5aoqo3q4qi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '20/07/2026 12:36 - Redirect: license_valid'),
(175, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '20/07/2026 18:13'),
(176, 'Cynthia', 'Connexion au système', '0t5cs8icfuct8uo1p1t1jfb9iq', '192.168.88.33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '21/07/2026 09:18 - Redirect: license_valid'),
(177, 'Isabelle', 'Connexion au système', '08qo204tid1kind1fc98jab3bg', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '21/07/2026 10:45 - Redirect: license_valid'),
(178, 'Isabelle', 'Connexion au système', '1hpmq3hhh885qeik4d1vvtnl2v', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '21/07/2026 12:25 - Redirect: license_valid'),
(179, 'Isabelle', 'Connexion au système', 'pg0adml8efsolv3ccsgb6barvf', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '21/07/2026 12:56 - Redirect: license_valid'),
(180, 'Isabelle', 'Connexion au système', 'ous0gl4pooq83f2a1ns6ccvn85', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '21/07/2026 15:23 - Redirect: license_valid'),
(181, 'Isabelle', 'Connexion au système', 'uf7rjca8lj8ar07mbe1ch5osus', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '21/07/2026 16:29 - Redirect: license_valid'),
(182, 'Cynthia', 'Connexion au système', '0t5cs8icfuct8uo1p1t1jfb9iq', '192.168.88.33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '21/07/2026 17:22 - Redirect: license_valid'),
(183, 'Isabelle', 'Connexion au système', 'hn5u8ponmdsj9gat8sdlslcjoq', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '21/07/2026 17:30 - Redirect: license_valid'),
(184, 'Isabelle', 'Connexion au système', '135fr7a1fu8v7u1s1u604thh6q', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '22/07/2026 09:14 - Redirect: license_valid'),
(185, 'Isabelle', 'Connexion au système', 'd378f02cnkqripflm800d2ni0l', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '22/07/2026 09:30 - Redirect: license_valid'),
(186, 'Isabelle', 'Connexion au système', '64qaq8eptnkbh8pbcrq8utbuef', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '22/07/2026 10:32 - Redirect: license_valid'),
(187, 'Cynthia', 'Connexion au système', 'k9enmb9sg1dv7q8sge97go63eb', '192.168.88.33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '22/07/2026 12:41 - Redirect: license_valid'),
(188, 'Isabelle', 'Connexion au système', '0vn29ucejb85fas12nlbv9qovr', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '22/07/2026 13:01 - Redirect: license_valid'),
(189, 'Isabelle', 'Connexion au système', 'dog1olojhfehbm6ib6qsopnqi0', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '22/07/2026 14:38 - Redirect: license_valid'),
(190, 'Isabelle', 'Connexion au système', 'obhnbblva9e2irbvjojfmil92u', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '22/07/2026 15:01 - Redirect: license_valid'),
(191, 'Isabelle', 'Connexion au système', 'cm193fd2qqh6jt6fj6jl046h9p', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '22/07/2026 15:59 - Redirect: license_valid'),
(192, 'Isabelle', 'Connexion au système', 'gj0l798o18h5r510gq00s30ec5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '22/07/2026 16:53 - Redirect: license_valid'),
(193, 'Isabelle', 'Connexion au système', 'idrsqk4k1cne1isph30h98fihl', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '23/07/2026 09:44 - Redirect: license_valid'),
(194, 'Isabelle', 'Connexion au système', '2lbpbjcflhbg6o5ni291gmd06i', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '23/07/2026 09:54 - Redirect: license_valid'),
(195, 'Cynthia', 'Connexion au système', 'nv4hqtmc0n5rdqfi82t1n120b9', '192.168.88.33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '23/07/2026 09:56 - Redirect: license_valid'),
(196, 'Isabelle', 'Connexion au système', 'ccoeuqu4o06desmo8qq0iqg3i4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '23/07/2026 12:44 - Redirect: license_valid'),
(197, 'Isabelle', 'Connexion au système', '4081mn0imvtk7hejt6rkp91r1r', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '23/07/2026 15:03 - Redirect: license_valid'),
(198, 'Isabelle', 'Connexion au système', 'efgkj902scpcqle1gaur9415nb', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '23/07/2026 15:54 - Redirect: license_valid'),
(199, 'Isabelle', 'Connexion au système', 'uieo6q6lbhfllngmp1c03d3ibh', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '23/07/2026 16:18 - Redirect: license_valid'),
(200, 'Isabelle', 'Connexion au système', '3p97ccc0tuukv4p9lhk8ufvfs9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '23/07/2026 16:56 - Redirect: license_valid'),
(201, 'Isabelle', 'Connexion au système', '6s5f4mb7gqn4n1huf8elnruqoo', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '23/07/2026 18:11 - Redirect: license_valid'),
(202, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '23/07/2026 18:22'),
(203, 'Isabelle', 'Connexion au système', '6pigd2tgfdevj9ldcgv1cjt86k', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '24/07/2026 10:53 - Redirect: license_valid'),
(204, 'Cynthia', 'Connexion au système', 't52lqog8022k1hprifflcg62li', '192.168.88.33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '24/07/2026 11:05 - Redirect: license_valid'),
(205, 'Isabelle', 'Connexion au système', 'losl4ehrnl93rgjbmfr9jeh8rj', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '24/07/2026 11:14 - Redirect: license_valid'),
(206, 'Isabelle', 'Connexion au système', 'trrhsuhfhblj6q762nrphmh1e5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '24/07/2026 14:16 - Redirect: license_valid'),
(207, 'Cynthia', 'Connexion au système', '2pu5leba5ip2dd66r1o44sjav3', '192.168.88.33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '25/07/2026 14:49 - Redirect: license_valid'),
(208, 'Isabelle', 'Connexion au système', '4ep4jlivn0kfvc1iuu5j1726uq', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '27/07/2026 08:46 - Redirect: license_valid'),
(209, 'Cynthia', 'Connexion au système', '5th3q2klcvkf50tdlplqc5ubv0', '192.168.88.33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '27/07/2026 10:32 - Redirect: license_valid'),
(210, 'Isabelle', 'Connexion au système', 'fhg777kdndu857qkgm4pcfrsse', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '27/07/2026 12:53 - Redirect: license_valid'),
(211, 'Isabelle', 'Connexion au système', 'opk5dvnsb8o271n7jee1dk8b7n', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '27/07/2026 14:53 - Redirect: license_valid'),
(212, 'Isabelle', 'Connexion au système', 'o7ddmoketfju323h2t1262iggm', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '27/07/2026 17:11 - Redirect: license_valid'),
(213, 'Cynthia', 'Connexion au système', 'mccpfnv3qr399p6c7nin89mk4f', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '27/07/2026 19:25 - Redirect: license_valid'),
(214, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '27/07/2026 19:31'),
(215, 'Isabelle', 'Connexion au système', '6k84npqeqq860vjsc5np3smntm', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '28/07/2026 11:05 - Redirect: license_valid'),
(216, 'Isabelle', 'Connexion au système', '2iis7dglh5qm26tiij7cjjg1lk', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '28/07/2026 12:02 - Redirect: license_valid'),
(217, 'Cynthia', 'Connexion au système', 'vekgc4dkhudo78jvtt6m0unmrs', '192.168.88.33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '28/07/2026 12:39 - Redirect: license_valid'),
(218, 'Isabelle', 'Connexion au système', 'lejrletegh81l3brmtjcd86od9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '28/07/2026 13:19 - Redirect: license_valid'),
(219, 'Isabelle', 'Connexion au système', '7q2dd0tusq6gbrm7f4upj8j544', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '28/07/2026 18:02 - Redirect: license_valid'),
(220, 'Cynthia', 'Connexion au système', 'f991v6t4eh7ieeij67ult9votb', '192.168.88.33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '29/07/2026 08:33 - Redirect: license_valid'),
(221, 'Isabelle', 'Connexion au système', 'm7tq5ahocg0j9g8iqra2acjfi8', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '29/07/2026 10:17 - Redirect: license_valid'),
(222, 'Isabelle', 'Connexion au système', '04jshuasoubpohbqgc69mgnnlk', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '29/07/2026 11:31 - Redirect: license_valid'),
(223, 'Isabelle', 'Connexion au système', '0sgcsmtb2n0qv1vucrh63vg086', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '29/07/2026 16:38 - Redirect: license_valid'),
(224, 'Cynthia', 'Connexion au système', 'f991v6t4eh7ieeij67ult9votb', '192.168.88.33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '29/07/2026 18:33 - Redirect: license_valid'),
(225, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '29/07/2026 19:47'),
(226, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '29/07/2026 19:47'),
(227, 'Isabelle', 'Connexion au système', '1n3p06u3jfe77d6hvqes2e944b', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '30/07/2026 08:34 - Redirect: license_valid'),
(228, 'Isabelle', 'Connexion au système', 'qutjqvbths5l88up2totcq9ebu', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '30/07/2026 12:13 - Redirect: license_valid'),
(229, 'Isabelle', 'Connexion au système', 'qrnp9emjlvkcdjm2e6fuug1v4f', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '30/07/2026 12:18 - Redirect: license_valid'),
(230, 'Cynthia', 'Connexion au système', 'g408osi1mmhmtgblh1lkegjsam', '192.168.88.33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '30/07/2026 12:20 - Redirect: license_valid'),
(231, 'Isabelle', 'Connexion au système', '0kv1a24bkfikif24eu9kf8858c', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '30/07/2026 15:02 - Redirect: license_valid'),
(232, 'Isabelle', 'Connexion au système', '4rf2dqm1ud7h4s4n2calk29tq9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '30/07/2026 15:52 - Redirect: license_valid');
INSERT INTO `logintos` (`id`, `user_id`, `action_type`, `session_id`, `ip_adress`, `user_agent`, `descrip`) VALUES
(233, 'Isabelle', 'Connexion au système', 'qhje00e258ca0bgtr1r6df10j7', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '31/07/2026 09:15 - Redirect: license_valid'),
(234, 'Cynthia', 'Connexion au système', 't0j0ls50lasp49js7uvkke2r8k', '192.168.88.33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '31/07/2026 09:40 - Redirect: license_valid'),
(235, 'Isabelle', 'Connexion au système', '2st90a13okh2b08qem3g520knu', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '31/07/2026 11:26 - Redirect: license_valid'),
(236, 'Isabelle', 'Connexion au système', 'bpk86c6osqok5daia3n78jgkhl', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '31/07/2026 17:14 - Redirect: license_valid'),
(237, 'Cynthia', 'Connexion au système', '01hatvtumfvsuigbdioa71ka1t', '192.168.88.33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '31/07/2026 17:16 - Redirect: license_valid'),
(238, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '31/07/2026 19:08'),
(239, 'Isabelle', 'Connexion au système', 'dpaaoc65r64mfj24tut8ecrq7m', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '03/08/2026 08:44 - Redirect: license_valid'),
(240, 'Isabelle', 'Connexion au système', '003i88qdbscga68nvmd7k0g4n7', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '03/08/2026 14:46 - Redirect: license_valid'),
(241, 'Isabelle', 'Connexion au système', '71j1cpntkdc46otlpbck1o0u4n', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '03/08/2026 18:41 - Redirect: license_valid'),
(242, 'Isabelle', 'Connexion au système', 'tc2h76d3pblnp9ia9ocueueeup', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '04/08/2026 15:56 - Redirect: license_valid'),
(243, 'Isabelle', 'Connexion au système', '9mf228voaflmrjbilg5mqf5sa3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '04/08/2026 16:41 - Redirect: license_valid'),
(244, 'Cynthia', 'Connexion au système', 't8irvl2dcssup04bgr68devr3c', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '05/08/2026 10:28 - Redirect: license_valid'),
(245, 'Isabelle', 'Connexion au système', '0bqngp1hve2ijbne4eteq6baiv', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '05/08/2026 15:31 - Redirect: license_valid'),
(246, 'Isabelle', 'Connexion au système', 'h3879tacbrn8b2m6qnanojmdt8', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '05/08/2026 16:38 - Redirect: license_valid'),
(247, 'Isabelle', 'Connexion au système', 'v3kdccef1lt8itmcmf3qadk5eh', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '06/08/2026 08:46 - Redirect: license_valid'),
(248, 'Isabelle', 'Connexion au système', '1qjqsrvqm24kl1bqgdlp838k77', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '06/08/2026 09:00 - Redirect: license_valid'),
(249, 'Isabelle', 'Connexion au système', '6j5f3o1qlv2is1qcubsdbal4rd', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '06/08/2026 15:36 - Redirect: license_valid'),
(250, 'Cynthia', 'Connexion au système', 'k0a266ek9ufjv6m8jp59p53ds1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '06/08/2026 18:24 - Redirect: license_valid'),
(251, 'Isabelle', 'Connexion au système', 'vjpics8boui2gogp1mpv4rtdcs', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '06/08/2026 18:58 - Redirect: license_valid'),
(252, 'Isabelle', 'Connexion au système', 'gmlnq141edot6rl3nr9lov081e', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '07/08/2026 11:53 - Redirect: license_valid'),
(253, 'Isabelle', 'Connexion au système', '8jggbico2apptqn68smq7rnll8', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '07/08/2026 14:29 - Redirect: license_valid'),
(254, 'Isabelle', 'Connexion au système', 'vk8oortkf9olps8vrjrfjg7vj4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '07/08/2026 17:54 - Redirect: license_valid'),
(255, 'Isabelle', 'Connexion au système', 'vumj0smekrk8hu371q93r5pei6', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '07/08/2026 18:19 - Redirect: license_valid'),
(256, 'Isabelle', 'Connexion au système', 'q1u54shav608nq4gv0h7lef5m0', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '10/08/2026 09:50 - Redirect: license_valid'),
(257, 'Cynthia', 'Connexion au système', '2bke5qupphdbvm6t0hkdjb8h9t', '192.168.88.33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '10/08/2026 10:13 - Redirect: license_valid'),
(258, 'Isabelle', 'Connexion au système', '0k0oi3i8bsnf8mtobnk313sdns', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '10/08/2026 10:23 - Redirect: license_valid'),
(259, 'Isabelle', 'Connexion au système', 'fcgj66jms69aqhn93p9he3rnef', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '10/08/2026 11:24 - Redirect: license_valid'),
(260, 'Isabelle', 'Connexion au système', 'sa04khmn99huqgl41soqqnqtk2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '10/08/2026 14:49 - Redirect: license_valid'),
(261, 'Isabelle', 'Connexion au système', '8i440h2r1p9f72hu60s44aq0r5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '11/08/2026 09:13 - Redirect: license_valid'),
(262, 'Isabelle', 'Connexion au système', 'gji4rmegj9150qs71lts3crd37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '11/08/2026 11:17 - Redirect: license_valid'),
(263, 'Isabelle', 'Connexion au système', 'bmv0r02mhi4pv169c2m7lt0t81', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '11/08/2026 15:28 - Redirect: license_valid'),
(264, 'Cynthia', 'Connexion au système', 'v373j6j28nrttrrfb07prehfrq', '192.168.88.33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '11/08/2026 17:25 - Redirect: license_valid'),
(265, 'Isabelle', 'Connexion au système', 'gf6q168k8dfklspmdqa078cn9c', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '12/08/2026 16:11 - Redirect: license_valid'),
(266, 'Isabelle', 'Connexion au système', 'o2nsrnunkl68s7iqg2m6j9q8gl', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '13/08/2026 12:05 - Redirect: license_valid'),
(267, 'Isabelle', 'Connexion au système', 'tvldalaht0k1c6otqp8c5ce1fa', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '13/08/2026 17:36 - Redirect: license_valid'),
(268, 'Isabelle', 'Connexion au système', '3e4qulls5o0latmdpqfjiinpru', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '17/08/2026 09:16 - Redirect: license_valid'),
(269, 'Cynthia', 'Connexion au système', 'a3bqf3ob5jdld7rf7t6efua0ct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '18/08/2026 13:03 - Redirect: license_valid'),
(270, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '18/08/2026 13:28'),
(271, 'Isabelle', 'Connexion au système', 'a3bqf3ob5jdld7rf7t6efua0ct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '18/08/2026 13:28 - Redirect: license_valid'),
(272, 'Isabelle', 'Déconnexion', NULL, NULL, NULL, '18/08/2026 13:30'),
(273, 'Cynthia', 'Connexion au système', 'a3bqf3ob5jdld7rf7t6efua0ct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '18/08/2026 13:31 - Redirect: license_valid'),
(274, 'Cynthia', 'Déconnexion', NULL, NULL, NULL, '18/08/2026 15:44'),
(275, 'Isabelle', 'Connexion au système', 'a3bqf3ob5jdld7rf7t6efua0ct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '19/08/2026 09:03 - Redirect: license_valid'),
(276, 'Isabelle', 'Connexion au système', 'im6qnrnm2hfivbvml6sa13fjsh', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '19/08/2026 09:53 - Redirect: license_valid'),
(277, 'Isabelle', 'Connexion au système', 'n19sm20rkssv8k31a32pg9ep3a', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '19/08/2026 18:13 - Redirect: license_valid'),
(278, 'Isabelle', 'Connexion au système', 'euvr275jdjqqb65seo5crgchj8', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '20/08/2026 12:13 - Redirect: license_valid'),
(279, 'Isabelle', 'Connexion au système', 'pqb84pq3i9gaor1eo154jbuck6', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '20/08/2026 12:21 - Redirect: license_valid'),
(280, 'Isabelle', 'Connexion au système', 'ltrapf5mvg0d8leptb5l7123nk', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '20/08/2026 12:48 - Redirect: license_valid'),
(281, 'Isabelle', 'Connexion au système', '3seom0rdi8iejaulled1bv9d2k', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '20/08/2026 18:11 - Redirect: license_valid'),
(282, 'Isabelle', 'Connexion au système', 'smdo1qgmj052ifvhac0ob77v5q', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '20/08/2026 18:23 - Redirect: license_valid'),
(283, 'Isabelle', 'Connexion au système', 's1a7r08rrk9rmtm8rrml2lk8vu', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '21/08/2026 11:23 - Redirect: license_valid'),
(284, 'Isabelle', 'Connexion au système', '3bdolv5p6v1k11r96pregck609', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '24/08/2026 10:04 - Redirect: license_valid'),
(285, 'Isabelle', 'Connexion au système', 'skpeg427aan3r85hp8a0d0svts', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '25/08/2026 12:50 - Redirect: license_valid'),
(286, 'Isabelle', 'Connexion au système', 'n77lu7ekf3gbufe02ov369004h', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '26/08/2026 12:36 - Redirect: license_valid'),
(287, 'Isabelle', 'Connexion au système', '2jmrv5emq7cusunu5hebdmmnup', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '27/08/2026 10:06 - Redirect: license_valid'),
(288, 'Isabelle', 'Connexion au système', '2l1bto9m5qtu78tkh099lmimdh', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '28/08/2026 08:00 - Redirect: license_valid'),
(289, 'Isabelle', 'Connexion au système', '0fm8n4d0p6fua47gmao2urkkst', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '01/09/2026 15:43 - Redirect: license_valid'),
(290, 'Isabelle', 'Connexion au système', '4p3l1p4397hfvh4kh72r2vs2ul', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '08/09/2026 10:02 - Redirect: license_valid'),
(291, 'Isabelle', 'Connexion au système', 'pe88qok6rg4o6t8l0u4ujejvj1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', '14/09/2026 09:31 - Redirect: license_valid'),
(292, 'Isabelle', 'Connexion au système', '794ridmmhms9ue5b095og02pt4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36 Edg/154.0.0.0', '02/10/2026 09:54 - Redirect: license_valid');

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
  `viewed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `logs`
--

INSERT INTO `logs` (`id`, `user_id`, `action_type`, `facture`, `montant`, `descrip`, `date`, `type`, `status`, `viewed`) VALUES
(11, 'Isabelle', 'Suppression', NULL, NULL, 'Impression Livre (-1.00 pièce)', '2026-06-24 15:29:21', NULL, 'Active', 0),
(12, 'Isabelle', 'AJOUT_CATEGORIE', NULL, NULL, 'Ajout de la catégorie : Service', '2026-06-24 15:36:14', NULL, 'Active', 0),
(13, 'Isabelle', 'AJOUT_CATEGORIE', NULL, NULL, 'Ajout de la catégorie : Produit', '2026-06-24 15:36:28', NULL, 'Active', 0),
(14, 'Isabelle', 'AJOUT_FORME', NULL, NULL, 'Ajout de la forme : m', '2026-06-24 15:36:45', NULL, 'Active', 0),
(15, 'Isabelle', 'AJOUT_FORME', NULL, NULL, 'Ajout de la forme : piece', '2026-06-24 15:36:53', NULL, 'Active', 0),
(16, 'Isabelle', 'AJOUT_FORME', NULL, NULL, 'Ajout de la forme : rouleau', '2026-06-24 15:37:01', NULL, 'Active', 0),
(17, 'Isabelle', 'Mis-à-jour-produit', NULL, NULL, 'Design ( pièce)', '2026-06-24 15:38:33', NULL, 'Active', 0),
(18, 'Cynthia', 'Encaissement libre', NULL, '508969', 'Encaissement apres migration db', '2026-06-24 15:41:50', 'entrée', 'Active', 0),
(19, 'Cynthia', 'Vente', '0001', '8000', 'Vente de :\n- 2.00 piece Impression sur corde', '2026-06-25 12:02:00', 'entrée', 'Active', 0),
(20, 'Cynthia', 'Consommation: 25000', 'BS00001', '25000', 'transport soir', '2026-06-25 12:02:27', 'sortie', 'Active', 0),
(21, 'Isabelle', 'Vente', '0002', '10000', 'Vente de :\n- 1.00 pièce DtfA4', '2026-06-25 12:16:57', 'entrée', 'Active', 0),
(22, 'Isabelle', 'Vente', '0003', '2000', 'Vente de :\n- 1.00 pièce Impression N/B', '2026-06-25 12:17:46', 'entrée', 'Active', 0),
(23, 'Isabelle', 'Consommation: 5000', 'BS00002', '5000', 'ajout sablimation', '2026-06-25 12:18:17', 'sortie', 'Active', 0),
(24, 'Isabelle', 'Consommation: 50000', 'BS00003', '50000', 'cash power', '2026-06-25 12:18:39', 'sortie', 'Active', 0),
(25, 'Isabelle', 'Consommation: 31500', 'BS00004', '31500', 'ratio', '2026-06-25 12:19:03', 'sortie', 'Active', 0),
(26, 'Isabelle', 'Consommation: 1500', 'BS00005', '1500', 'mega', '2026-06-25 12:19:20', 'sortie', 'Active', 0),
(27, 'Isabelle', 'Consommation: 35000', 'BS00006', '35000', 'sikouchi', '2026-06-25 12:19:40', 'sortie', 'Active', 0),
(28, 'Isabelle', 'Consommation: 10000', 'BS00007', '10000', 'frais pour terrain ', '2026-06-25 12:20:05', 'sortie', 'Active', 0),
(29, 'Isabelle', 'Consommation: 9000', 'BS00008', '9000', 'water', '2026-06-25 12:20:21', 'sortie', 'Active', 0),
(30, 'Isabelle', 'Consommation: 30000', 'BS00009', '30000', 'tear drop bubet', '2026-06-25 12:21:07', 'sortie', 'Active', 0),
(31, 'Isabelle', 'Consommation: 82500', 'BS00010', '82500', 'representation dg', '2026-06-25 12:28:12', 'sortie', 'Active', 0),
(32, 'Isabelle', 'AJOUT_FORME', NULL, NULL, 'Ajout de la forme : Forfait', '2026-06-25 12:34:58', NULL, 'Active', 0),
(33, 'Cynthia', 'AJOUT_FORME', NULL, NULL, 'Ajout de la forme : Neutre', '2026-06-25 12:43:30', NULL, 'Active', 0),
(34, 'Cynthia', 'AJOUT_FORME', NULL, NULL, 'Ajout de la forme : Another', '2026-06-25 12:44:52', NULL, 'Active', 0),
(35, 'Cynthia', 'SUPPRESSION_FORME', NULL, NULL, 'Suppression de la forme : Another', '2026-06-25 12:45:02', NULL, 'Active', 0),
(36, 'Cynthia', 'SUPPRESSION_FORME', NULL, NULL, 'Suppression de la forme : Neutre', '2026-06-25 12:45:11', NULL, 'Active', 0),
(37, 'Cynthia', 'AJOUT_CATEGORIE', NULL, NULL, 'Ajout de la catégorie : nez', '2026-06-25 12:46:00', NULL, 'Active', 0),
(38, 'Cynthia', 'SUPPRESSION_CATEGORIE', NULL, NULL, 'Suppression de la catégorie : nez', '2026-06-25 12:46:08', NULL, 'Active', 0),
(39, 'Isabelle', 'Consommation: 10000', 'BS00011', '10000', 'AJOUT TEAR DROP BUBET', '2026-06-25 15:48:24', 'sortie', 'Active', 0),
(40, 'Isabelle', 'Consommation: 5000', 'BS00012', '5000', 'POMPE MOTO', '2026-06-25 15:48:45', 'sortie', 'Active', 0),
(41, 'Isabelle', 'Consommation: 22000', 'BS00013', '22000', 'PROFORMA MANUEL', '2026-06-25 15:49:09', 'sortie', 'Active', 0),
(42, 'Isabelle', 'Consommation: 50000', 'BS00014', '50000', 'REPRESENTATION DG', '2026-06-25 15:49:52', 'sortie', 'Active', 0),
(43, 'Isabelle', 'Consommation: 1000', 'BS00015', '1000', 'UNITE', '2026-06-25 15:50:09', 'sortie', 'Active', 0),
(44, 'Isabelle', 'Vente', '0004', '23000', 'Vente de :\n- 23.00 pièce Impression N/B', '2026-06-25 15:52:14', 'entrée', 'Active', 0),
(45, 'Isabelle', 'Vente', '0005', '10000', 'Vente de :\n- 1.00 pièce DtfA4', '2026-06-25 15:53:14', 'entrée', 'Active', 0),
(46, 'Cynthia', 'Consommation: 3500', 'BS00016', '3500', 'ratio', '2026-06-25 16:16:53', 'sortie', 'Active', 0),
(47, 'Cynthia', 'Consommation: 10000', 'BS00017', '10000', 'ratio', '2026-06-26 07:13:16', 'sortie', 'Active', 0),
(48, 'Cynthia', 'Consommation: 5000', 'BS00018', '5000', 'deplacement matin', '2026-06-26 08:30:12', 'sortie', 'Active', 0),
(49, 'Cynthia', 'Consommation: 2500', 'BS00019', '2500', 'representation dg', '2026-06-26 08:31:15', 'sortie', 'Active', 0),
(50, 'Cynthia', 'Consommation: 20000', 'BS00020', '20000', 'representation finance', '2026-06-26 08:31:49', 'sortie', 'Active', 0),
(51, 'Cynthia', 'Consommation: 100000', 'BS00021', '100000', 'autocollant PNUD', '2026-06-26 09:49:13', 'sortie', 'Active', 0),
(52, 'Cynthia', 'Consommation: 14000', 'BS00022', '14000', 'FRAIS DE REPRESENTATION DG', '2026-06-26 09:50:14', 'sortie', 'Active', 0),
(53, 'Cynthia', 'Consommation: 21800', 'BS00023', '21800', 'RATIO', '2026-06-26 09:50:46', 'sortie', 'Active', 0),
(54, 'Cynthia', 'Vente', '0006', '10000', 'Vente de :\n- 1.00 pièce DtfA4', '2026-06-26 09:54:11', 'entrée', 'Active', 0),
(55, 'Cynthia', 'Vente', '0007', '10000', 'Vente de :\n- 1.00 pièce DtfA4', '2026-06-26 09:56:48', 'entrée', 'Active', 0),
(56, 'Cynthia', 'Paiement Dette', '0194', '25000', 'HAM - Paiement Dette - Facture #0194', '2026-06-26 10:29:33', 'entrée', 'Active', 0),
(57, 'Cynthia', 'Vente', '0008', '10000', 'Vente de :\n- 1.00 pièce DtfA4', '2026-06-26 11:48:37', 'entrée', 'Active', 0),
(58, 'Cynthia', 'Vente', '0009', '15000', 'Vente de :\n- 1.00 m BACHE', '2026-06-26 11:49:26', 'entrée', 'Active', 0),
(59, 'Cynthia', 'Vente', '0010', '20000', 'Vente de :\n- 1.00 m AUTOCOLLANT', '2026-06-26 15:10:30', 'entrée', 'Active', 0),
(60, 'Cynthia', 'Vente', '0011', '15000', 'Vente de :\n- 1.00 m BACHE', '2026-06-26 15:35:21', 'entrée', 'Active', 0),
(61, 'Cynthia', 'Vente', '0012', '36000', 'Vente de :\n- 2.40 m BACHE', '2026-06-26 15:36:04', 'entrée', 'Active', 0),
(62, 'Cynthia', 'Vente', '0013', '6500', 'Vente de :\n- 13.00 pièce Photocopie', '2026-06-26 15:38:00', 'entrée', 'Active', 0),
(63, 'Cynthia', 'Encaissement libre', NULL, '500000', 'alimentation caisse', '2026-06-26 15:38:35', 'entrée', 'Active', 0),
(64, 'Cynthia', 'Vente', '0014', '71700', 'Vente de :\n- 2.50 m BACHE\n- 1.90 m AUTOCOLLANT', '2026-06-26 15:46:51', 'entrée', 'Active', 0),
(65, 'Cynthia', 'Vente', '0015', '15000', 'Vente de :\n- 1.00 m BACHE', '2026-06-26 15:49:12', 'entrée', 'Active', 0),
(66, 'Cynthia', 'Consommation: 45000', 'BS00024', '45000', 'representation dg', '2026-06-26 15:50:28', 'sortie', 'Active', 0),
(67, 'Cynthia', 'Consommation: 30000', 'BS00025', '30000', 'representation finance', '2026-06-26 15:55:02', 'sortie', 'Active', 0),
(68, 'Cynthia', 'Consommation: 10000', 'BS00026', '10000', 'aide', '2026-06-26 15:55:24', 'sortie', 'Active', 0),
(69, 'Cynthia', 'Consommation: 10000', 'BS00027', '10000', 'cadre whh', '2026-06-26 15:55:53', 'sortie', 'Active', 0),
(70, 'Cynthia', 'Consommation: 100000', 'BS00028', '100000', 'autocollant PNUD', '2026-06-26 15:56:26', 'sortie', 'Active', 0),
(71, 'Cynthia', 'Consommation: 5000', 'BS00029', '5000', 'CITY', '2026-06-26 15:56:48', 'sortie', 'Active', 0),
(72, 'Cynthia', 'Consommation: 20000', 'BS00030', '20000', 'aide', '2026-06-26 16:49:53', 'sortie', 'Active', 0),
(73, 'Cynthia', 'Consommation: 10000', 'BS00031', '10000', 'terrain ', '2026-06-26 16:50:13', 'sortie', 'Active', 0),
(74, 'Cynthia', 'Consommation: 150000', 'BS00032', '150000', 'frs cash power unite', '2026-06-26 16:50:53', 'sortie', 'Active', 0),
(75, 'Cynthia', 'Consommation: 10000', 'BS00033', '10000', 'branding', '2026-06-26 16:51:10', 'sortie', 'Active', 0),
(76, 'Cynthia', 'Consommation: 20000', 'BS00034', '20000', 'frais de representation dg', '2026-06-26 17:40:44', 'sortie', 'Active', 0),
(77, 'Cynthia', 'Suppression Dépense No.BS00021', 'BS00021', '100000', 'Une dépense de 100000 enregistrée le 2026-06-26 a été supprimée et la caisse a été réajustée', '2026-06-26 17:59:42', 'entrée', 'Active', 0),
(78, 'Cynthia', 'Consommation: 8000', 'BS00035', '8000', 'aide', '2026-06-29 10:14:13', 'sortie', 'Active', 0),
(79, 'Cynthia', 'Consommation: 30000', 'BS00036', '30000', 'terrain bbci', '2026-06-29 10:14:36', 'sortie', 'Active', 0),
(80, 'Cynthia', 'Consommation: 5000', 'BS00037', '5000', 'representation dg', '2026-06-29 10:14:58', 'sortie', 'Active', 0),
(81, 'Cynthia', 'Consommation: 25000', 'BS00038', '25000', 'transport vendredi', '2026-06-29 10:15:22', 'sortie', 'Active', 0),
(82, 'Cynthia', 'Consommation: 15000', 'BS00039', '15000', 'ratio', '2026-06-29 10:15:37', 'sortie', 'Active', 0),
(83, 'Cynthia', 'Vente', '0016', '1375000', 'Vente de :\n- 250.00 m INVITATIONS', '2026-06-29 10:16:52', 'entrée', 'Active', 0),
(84, 'Cynthia', 'Consommation: 500000', 'BS00040', '500000', 'achat invitations', '2026-06-29 10:17:24', 'sortie', 'Active', 0),
(85, 'Cynthia', 'Vente', '0017', '10000', 'Vente de :\n- 1.00 pièce DtfA4', '2026-06-29 10:20:36', 'entrée', 'Active', 0),
(86, 'Cynthia', 'Vente', '0018', '2000', 'Vente de :\n- 2.00 piece Heating', '2026-06-29 10:22:59', 'entrée', 'Active', 0),
(87, 'Cynthia', 'Vente', '0019', '15000', 'Vente de :\n- 1.00 m BACHE', '2026-06-29 10:23:49', 'entrée', 'Active', 0),
(88, 'Cynthia', 'Consommation: 6000', 'BS00041', '6000', 'achat sachet', '2026-06-29 10:29:46', 'sortie', 'Active', 0),
(89, 'Cynthia', 'Consommation: 50000', 'BS00042', '50000', 'transport samedi', '2026-06-29 10:30:11', 'sortie', 'Active', 0),
(90, 'Cynthia', 'Consommation: 35000', 'BS00043', '35000', 'ratio', '2026-06-29 10:39:46', 'sortie', 'Active', 0),
(91, 'Cynthia', 'Consommation: 47700', 'BS00044', '47700', 'representation dg', '2026-06-29 10:41:10', 'sortie', 'Active', 0),
(92, 'Cynthia', 'Consommation: 7000', 'BS00045', '7000', 'reparation pneu', '2026-06-29 10:42:30', 'sortie', 'Active', 0),
(93, 'Cynthia', 'Consommation: 10000', 'BS00046', '10000', 'finition calendrier', '2026-06-29 10:43:00', 'sortie', 'Active', 0),
(94, 'Cynthia', 'Consommation: 20000', 'BS00047', '20000', 'aide', '2026-06-29 10:43:18', 'sortie', 'Active', 0),
(95, 'Cynthia', 'Approvisionnement: 288000', 'BS00048', '288000', 'achat lacoste 9', '2026-06-29 10:57:12', 'sortie', 'Active', 0),
(96, 'Isabelle', 'Ajout Nouveau Utilisateur', NULL, NULL, 'Venuste', '2026-06-29 12:18:15', NULL, 'Active', 0),
(97, 'Isabelle', 'Modification-utilisateur', NULL, NULL, '', '2026-06-29 12:18:27', NULL, 'Active', 0),
(98, 'Isabelle', 'Ajout Nouveau Utilisateur', NULL, NULL, 'Dev', '2026-06-29 12:19:23', NULL, 'Active', 0),
(99, 'Isabelle', 'Modification-utilisateur', NULL, NULL, '', '2026-06-29 12:19:55', NULL, 'Active', 0),
(100, 'Isabelle', 'Modification Détails d\'utilisateur', NULL, NULL, 'Modification du mot de passe de Dev par l\'administrateur', '2026-06-29 12:20:19', NULL, 'Active', 0),
(101, 'Cynthia', 'Consommation: 3500', 'BS00049', '3500', 'bassin', '2026-06-29 13:26:05', 'sortie', 'Active', 0),
(102, 'Cynthia', 'Approvisionnement: 6000', 'BS00050', '6000', 'achat stylos', '2026-06-29 13:26:40', 'sortie', 'Active', 0),
(103, 'Cynthia', 'Vente', '0020', '24000', 'Vente de :\n- 3.00 pièce Stylo personnalise', '2026-06-29 13:28:19', 'entrée', 'Active', 0),
(104, 'Cynthia', 'Vente', '0021', '1000000', 'Vente de :\n- 28.00 pièce Lacoste', '2026-06-29 14:01:33', 'entrée', 'Active', 0),
(105, 'Cynthia', 'Dette: 260000', '0021', '-', 'séraphine', '2026-06-29 14:01:33', '', 'Active', 0),
(106, 'Cynthia', 'Approvisionnement: 924000', 'BS00051', '924000', 'achat 28lacoste', '2026-06-29 14:02:08', 'sortie', 'Active', 0),
(107, 'Cynthia', 'Vente', '0022', '20000', 'Vente de :\n- 2.00 pièce Impression sur tshirt', '2026-06-29 14:23:22', 'entrée', 'Active', 0),
(108, 'Cynthia', 'Consommation: 10000', 'BS00052', '10000', 'representation dg', '2026-06-29 14:58:54', 'sortie', 'Active', 0),
(109, 'Cynthia', 'Consommation: 2000', 'BS00053', '2000', 'sylo', '2026-06-29 14:59:16', 'sortie', 'Active', 0),
(110, 'Cynthia', 'Consommation: 4000', 'BS00054', '4000', 'aide;unite', '2026-06-29 14:59:40', 'sortie', 'Active', 0),
(111, 'Cynthia', 'Vente', '0023', '9000', 'Vente de :\n- 0.60 m BACHE', '2026-06-29 15:37:21', 'entrée', 'Supprimée', 0),
(112, 'Cynthia', 'Consommation: 4000', 'BS00055', '4000', 'water', '2026-06-29 15:49:25', 'sortie', 'Active', 0),
(113, 'Cynthia', 'Consommation: 3000', 'BS00056', '3000', 'city', '2026-06-29 15:49:37', 'sortie', 'Active', 0),
(114, 'Cynthia', 'Consommation: 150000', 'BS00057', '150000', 'representation dg', '2026-06-29 16:01:32', 'sortie', 'Active', 0),
(115, 'Cynthia', 'Annulation Vente', '0023', NULL, '9000', '2026-06-29 16:02:22', NULL, 'Active', 0),
(116, 'Cynthia', 'Vente', '0024', '9000', 'Vente de :\n- 0.60 m BACHE', '2026-06-29 16:03:16', 'entrée', 'Active', 0),
(117, 'Cynthia', 'Consommation: 2000', 'BS00058', '2000', 'water', '2026-06-29 16:17:35', 'sortie', 'Active', 0),
(118, 'Cynthia', 'Consommation: 45000', 'BS00059', '45000', 'aide', '2026-06-29 16:22:00', 'sortie', 'Active', 0),
(119, 'Cynthia', 'Vente', '0025', '20000', 'Vente de :\n- 1.00 pièce Design', '2026-06-29 16:43:34', 'entrée', 'Active', 0),
(120, 'Cynthia', 'Vente', '0026', '10000', 'Vente de :\n- 1.00 pièce Impression sur tshirt', '2026-06-29 16:52:19', 'entrée', 'Active', 0),
(121, 'Cynthia', 'Vente', '0027', '15000', 'Vente de :\n- 1.00 m BACHE', '2026-06-29 16:57:56', 'entrée', 'Active', 0),
(122, 'Cynthia', 'Vente', '0028', '2000', 'Vente de :\n- 4.00 pièce Anneau', '2026-06-29 16:58:35', 'entrée', 'Active', 0),
(123, 'Cynthia', 'Vente', '0029', '8000', 'Vente de :\n- 1.00 pièce Pvc', '2026-06-30 13:10:08', 'entrée', 'Active', 0),
(124, 'Cynthia', 'Consommation: 20000', 'BS00060', '20000', 'transport soir', '2026-06-30 13:17:08', 'sortie', 'Active', 0),
(125, 'Cynthia', 'Paiement Dette', '0021', '260000', 'séraphine - Paiement Dette - Facture #0021', '2026-06-30 13:17:31', 'entrée', 'Active', 0),
(126, 'Cynthia', 'Consommation: 3000', 'BS00061', '3000', 'cutting', '2026-06-30 13:18:54', 'sortie', 'Active', 0),
(127, 'Cynthia', 'Consommation: 10000', 'BS00062', '10000', 'terrain bubet', '2026-06-30 13:19:20', 'sortie', 'Active', 0),
(128, 'Cynthia', 'Consommation: 31000', 'BS00063', '31000', 'ratio', '2026-06-30 13:21:53', 'sortie', 'Active', 0),
(129, 'Cynthia', 'Consommation: 15000', 'BS00064', '15000', 'superglue', '2026-06-30 13:22:21', 'sortie', 'Active', 0),
(130, 'Cynthia', 'Consommation: 1500', 'BS00065', '1500', 'sachet ', '2026-06-30 13:24:01', 'sortie', 'Active', 0),
(131, 'Cynthia', 'Consommation: 100000', 'BS00066', '100000', 'relieure calendrier', '2026-06-30 13:24:37', 'sortie', 'Active', 0),
(132, 'Cynthia', 'Consommation: 50000', 'BS00067', '50000', 'cash power', '2026-06-30 13:25:04', 'sortie', 'Active', 0),
(133, 'Cynthia', 'Consommation: 200000', 'BS00068', '200000', 'frs materiels', '2026-06-30 13:25:26', 'sortie', 'Active', 0),
(134, 'Cynthia', 'Consommation: 6000', 'BS00069', '6000', 'superglue', '2026-06-30 13:25:47', 'sortie', 'Active', 0),
(135, 'Cynthia', 'Consommation: 5000', 'BS00070', '5000', 'serviette', '2026-06-30 13:26:04', 'sortie', 'Active', 0),
(136, 'Cynthia', 'Approvisionnement: 300000', 'BS00071', '300000', 'versement bcb', '2026-06-30 13:46:02', 'sortie', 'Active', 0),
(137, 'Cynthia', 'Approvisionnement: 300000', 'BS00071', '300000', 'versement bcb', '2026-06-30 13:46:02', 'sortie', 'Active', 0),
(138, 'Cynthia', 'Approvisionnement: 300000', 'BS00071', '300000', 'versement bcb', '2026-06-30 13:46:02', 'sortie', 'Active', 0),
(139, 'Cynthia', 'Approvisionnement: 300000', 'BS00071', '300000', 'versement bcb', '2026-06-30 13:46:02', 'sortie', 'Active', 0),
(140, 'Cynthia', 'Consommation: 300000', 'BS00071', '300000', 'versement bcb', '2026-06-30 13:46:02', 'sortie', 'Active', 0),
(141, 'Cynthia', 'Suppression Dépense No.BS00071', 'BS00071', '300000', 'Une dépense de 300000 enregistrée le 2026-06-30 a été supprimée et la caisse a été réajustée', '2026-06-30 13:46:10', 'entrée', 'Active', 0),
(142, 'Cynthia', 'Suppression Dépense No.BS00071', 'BS00071', '300000', 'Une dépense de 300000 enregistrée le 2026-06-30 a été supprimée et la caisse a été réajustée', '2026-06-30 13:46:19', 'entrée', 'Active', 0),
(143, 'Cynthia', 'Suppression Dépense No.BS00071', 'BS00071', '300000', 'Une dépense de 300000 enregistrée le 2026-06-30 a été supprimée et la caisse a été réajustée', '2026-06-30 13:46:57', 'entrée', 'Active', 0),
(144, 'Cynthia', 'Suppression Dépense No.BS00071', 'BS00071', '300000', 'Une dépense de 300000 enregistrée le 2026-06-30 a été supprimée et la caisse a été réajustée', '2026-06-30 13:46:57', 'entrée', 'Active', 0),
(145, 'Cynthia', 'Vente', '0030', '50220', 'Vente de :\n- 2.79 m AUTOCOLLANT', '2026-06-30 14:42:03', 'entrée', 'Supprimée', 0),
(146, 'Cynthia', 'Annulation Vente', '0030', NULL, '50220', '2026-06-30 16:09:26', NULL, 'Active', 0),
(147, 'Cynthia', 'Vente', '0031', '50400', 'Vente de :\n- 2.80 m AUTOCOLLANT', '2026-06-30 16:10:18', 'entrée', 'Active', 0),
(148, 'Cynthia', 'Consommation: 10000', 'BS00072', '10000', 'aide', '2026-06-30 16:19:22', 'sortie', 'Active', 0),
(149, 'Cynthia', 'Encaissement libre', NULL, '1090000', 'retrait de la banque le 17/6/2026', '2026-06-30 16:23:53', 'entrée', 'Active', 0),
(150, 'Cynthia', 'Consommation: 400000', 'BS00073', '400000', 'representation bbci', '2026-06-30 16:25:12', 'sortie', 'Active', 0),
(151, 'Cynthia', 'Consommation: 400000', 'BS00074', '400000', 'representation boss', '2026-06-30 16:25:37', 'sortie', 'Active', 0),
(152, 'Cynthia', 'Consommation: 290000', 'BS00075', '290000', 'carburent', '2026-06-30 16:25:59', 'sortie', 'Active', 0),
(153, 'Cynthia', 'Encaissement libre', NULL, '2010000', 'retrait de la banque le24/6/2026', '2026-06-30 16:27:38', 'entrée', 'Active', 0),
(154, 'Cynthia', 'Consommation: 209000', 'BS00076', '209000', 'facture fixe', '2026-06-30 16:28:03', 'sortie', 'Active', 0),
(155, 'Cynthia', 'Consommation: 240000', 'BS00077', '240000', 'frs materiels', '2026-06-30 16:28:40', 'sortie', 'Active', 0),
(156, 'Cynthia', 'Consommation: 850000', 'BS00078', '850000', 'encre bizhub', '2026-06-30 16:29:07', 'sortie', 'Active', 0),
(157, 'Cynthia', 'Consommation: 711000', 'BS00079', '711000', 'representation bic et fle microfinance', '2026-06-30 16:29:55', 'sortie', 'Active', 0),
(158, 'Cynthia', 'Encaissement libre', NULL, '3320000', 'retrait de la banque', '2026-06-30 16:30:44', 'entrée', 'Active', 0),
(159, 'Cynthia', 'Consommation: 1092500', 'BS00080', '1092500', 'auto pnud', '2026-06-30 16:59:14', 'sortie', 'Active', 0),
(160, 'Cynthia', 'Consommation: 450000', 'BS00081', '450000', 'representation boss', '2026-06-30 16:59:37', 'sortie', 'Active', 0),
(161, 'Cynthia', 'Consommation: 780000', 'BS00082', '780000', 'representation dg fle', '2026-06-30 17:00:22', 'sortie', 'Active', 0),
(162, 'Cynthia', 'Consommation: 160000', 'BS00083', '160000', 'cadrement whh', '2026-06-30 17:00:49', 'sortie', 'Active', 0),
(163, 'Cynthia', 'Consommation: 315000', 'BS00084', '315000', 'representation pafen', '2026-06-30 17:02:40', 'sortie', 'Active', 0),
(164, 'Cynthia', 'Consommation: 20000', 'BS00085', '20000', 'representation bcb', '2026-06-30 17:03:02', 'sortie', 'Active', 0),
(165, 'Cynthia', 'Consommation: 500000', 'BS00086', '500000', 'alimentation caisse', '2026-06-30 17:03:38', 'sortie', 'Active', 0),
(166, 'Cynthia', 'Consommation: 2500', 'BS00087', '2500', 'representation dg', '2026-06-30 17:05:13', 'sortie', 'Active', 0),
(167, 'Cynthia', 'Vente', '0032', '401250', 'Vente de :\n- 26.75 m BACHE', '2026-06-30 17:25:22', 'entrée', 'Active', 0),
(168, 'Cynthia', 'Consommation: 10000', 'BS00088', '10000', 'aide', '2026-06-30 17:27:39', 'sortie', 'Active', 0),
(169, 'Cynthia', 'Vente', '0033', '18000', 'Vente de :\n- 1.00 m AUTOCOLLANT', '2026-07-02 09:52:41', 'entrée', 'Active', 0),
(170, 'Cynthia', 'Vente', '0034', '10000', 'Vente de :\n- 1.00 pièce DtfA4', '2026-07-02 09:54:11', 'entrée', 'Active', 0),
(171, 'Cynthia', 'Vente', '0035', '2000', 'Vente de :\n- 2.00 piece Heating', '2026-07-02 09:54:47', 'entrée', 'Active', 0),
(172, 'Cynthia', 'Consommation: 20000', 'BS00089', '20000', 'transport soir', '2026-07-02 09:55:20', 'sortie', 'Active', 0),
(173, 'Cynthia', 'Consommation: 20000', 'BS00089', '20000', 'transport soir', '2026-07-02 09:55:20', 'sortie', 'Active', 0),
(174, 'Cynthia', 'Consommation: 20000', 'BS00089', '20000', 'transport soir', '2026-07-02 09:55:20', 'sortie', 'Active', 0),
(175, 'Cynthia', 'Consommation: 10000', 'BS00090', '10000', 'pret destin', '2026-07-02 09:55:42', 'sortie', 'Active', 0),
(176, 'Cynthia', 'Consommation: 10500', 'BS00091', '10500', 'ratio', '2026-07-02 09:55:59', 'sortie', 'Active', 0),
(177, 'Cynthia', 'Suppression Dépense No.BS00089', 'BS00089', '20000', 'Une dépense de 20000 enregistrée le 2026-07-02 a été supprimée et la caisse a été réajustée', '2026-07-02 10:02:18', 'entrée', 'Active', 0),
(178, 'Cynthia', 'Suppression Dépense No.BS00089', 'BS00089', '20000', 'Une dépense de 20000 enregistrée le 2026-07-02 a été supprimée et la caisse a été réajustée', '2026-07-02 10:02:23', 'entrée', 'Active', 0),
(179, 'Cynthia', 'Consommation: 32000', 'BS00092', '32000', 'ratio', '2026-07-02 10:03:06', 'sortie', 'Active', 0),
(180, 'Cynthia', 'Consommation: 5000', 'BS00093', '5000', 'representation dg', '2026-07-02 10:03:29', 'sortie', 'Active', 0),
(181, 'Cynthia', 'Consommation: 85000', 'BS00094', '85000', 'representation febabu', '2026-07-02 10:03:54', 'sortie', 'Active', 0),
(182, 'Cynthia', 'Consommation: 10000', 'BS00095', '10000', 'pret destin', '2026-07-02 10:04:25', 'sortie', 'Active', 0),
(183, 'Cynthia', 'Vente', '0036', '54000', 'Vente de :\n- 3.60 m BACHE', '2026-07-02 16:17:15', 'entrée', 'Active', 0),
(184, 'Cynthia', 'Vente', '0037', '2000', 'Vente de :\n- 4.00 pièce Anneau', '2026-07-02 16:20:18', 'entrée', 'Active', 0),
(185, 'Cynthia', 'Vente', '0038', '14000', 'Vente de :\n- 1.00 piece Design 1', '2026-07-02 16:42:08', 'entrée', 'Active', 0),
(186, 'Cynthia', 'Consommation: 30000', 'BS00096', '30000', 'vidange MOTO', '2026-07-02 16:44:21', 'sortie', 'Active', 0),
(187, 'Cynthia', 'Consommation: 11000', 'BS00097', '11000', 'terrain unite', '2026-07-02 16:46:46', 'sortie', 'Active', 0),
(188, 'Cynthia', 'Consommation: 11000', 'BS00097', '11000', 'terrain unite', '2026-07-02 16:46:46', 'sortie', 'Active', 0),
(189, 'Cynthia', 'Consommation: 11000', 'BS00097', '11000', 'terrain unite', '2026-07-02 16:46:46', 'sortie', 'Active', 0),
(190, 'Cynthia', 'Suppression Dépense No.BS00097', 'BS00097', '11000', 'Une dépense de 11000 enregistrée le 2026-07-02 a été supprimée et la caisse a été réajustée', '2026-07-02 16:47:13', 'entrée', 'Active', 0),
(191, 'Cynthia', 'Suppression Dépense No.BS00097', 'BS00097', '11000', 'Une dépense de 11000 enregistrée le 2026-07-02 a été supprimée et la caisse a été réajustée', '2026-07-02 16:47:16', 'entrée', 'Active', 0),
(192, 'Cynthia', 'Consommation: 5000', 'BS00098', '5000', 'aide', '2026-07-02 16:47:55', 'sortie', 'Active', 0),
(193, 'Cynthia', 'Consommation: 10000', 'BS00099', '10000', 'water', '2026-07-02 16:48:43', 'sortie', 'Active', 0),
(194, 'Cynthia', 'Consommation: 69100', 'BS00100', '69100', 'assurance automobile', '2026-07-02 16:49:43', 'sortie', 'Active', 0),
(195, 'Cynthia', 'Consommation: 170000', 'BS00101', '170000', 'finition calendrier', '2026-07-02 16:50:20', 'sortie', 'Active', 0),
(196, 'Cynthia', 'Consommation: 106000', 'BS00102', '106000', 'representation dg', '2026-07-02 17:04:59', 'sortie', 'Active', 0),
(197, 'Cynthia', 'Vente', '0039', '10000', 'Vente de :\n- 1.00 pièce DtfA4', '2026-07-02 17:17:45', 'entrée', 'Active', 0),
(198, 'Cynthia', 'Vente', '0040', '10000', 'Vente de :\n- 20.00 pièce Carte de visite', '2026-07-02 17:18:21', 'entrée', 'Active', 0),
(199, 'Cynthia', 'Vente', '0041', '47200', 'Vente de :\n- 20.00 pièce Depliant', '2026-07-02 17:30:26', 'entrée', 'Active', 0),
(200, 'Cynthia', 'Vente', '0042', '32000', 'Vente de :\n- 1.00 pièce Backlight1m', '2026-07-06 11:07:37', 'entrée', 'Active', 0),
(201, 'Cynthia', 'Vente', '0043', '129600', 'Vente de :\n- 8.64 m BACHE', '2026-07-06 11:08:23', 'entrée', 'Active', 0),
(202, 'Cynthia', 'Vente', '0044', '162000', 'Vente de :\n- 10.80 m BACHE', '2026-07-06 11:09:55', 'entrée', 'Active', 0),
(203, 'Cynthia', 'Vente', '0045', '27430', 'Vente de :\n- 1.00 m AUTOCOLLANT', '2026-07-06 11:13:11', 'entrée', 'Active', 0),
(204, 'Cynthia', 'Dette: 2', '0045', '-', 'Client de passage', '2026-07-06 11:13:11', '', 'Active', 0),
(205, 'Cynthia', 'Vente', '0046', '20520', 'Vente de :\n- 1.14 m AUTOCOLLANT', '2026-07-06 11:13:57', 'entrée', 'Active', 0),
(206, 'Cynthia', 'Vente', '0047', '48600', 'Vente de :\n- 3.24 m BACHE', '2026-07-06 11:23:52', 'entrée', 'Active', 0),
(207, 'Cynthia', 'Vente', '0048', '30000', 'Vente de :\n- 30.00 pièce Impression N/B', '2026-07-06 11:24:27', 'entrée', 'Active', 0),
(208, 'Cynthia', 'Vente', '0049', '950000', 'Vente de :\n- 1.00 m Tshirt 23lacoste15chapeau6 impression9', '2026-07-06 11:31:12', 'entrée', 'Active', 0),
(209, 'Cynthia', 'Dette: 758000', '0049', '-', 'Client de passage', '2026-07-06 11:31:12', '', 'Active', 0),
(210, 'Cynthia', 'Vente', '0050', '202500', 'Vente de :\n- 1.00 piece Back light 1.2et 1 +design', '2026-07-06 11:34:40', 'entrée', 'Active', 0),
(211, 'Cynthia', 'Vente', '0051', '150000', 'Vente de :\n- 1.00 piece Xstand', '2026-07-06 11:38:10', 'entrée', 'Active', 0),
(212, 'Cynthia', 'Vente', '0052', '47000', 'Vente de :\n- 1.00 pièce Lacoste', '2026-07-06 11:39:09', 'entrée', 'Active', 0),
(213, 'Cynthia', 'Consommation: 20000', 'BS00103', '20000', 'transport soir', '2026-07-06 11:39:56', 'sortie', 'Active', 0),
(214, 'Cynthia', 'Consommation: 48800', 'BS00104', '48800', 'bbci fumee', '2026-07-06 11:40:32', 'sortie', 'Active', 0),
(215, 'Cynthia', 'Consommation: 20000', 'BS00105', '20000', 'representation dg', '2026-07-06 11:46:18', 'sortie', 'Active', 0),
(216, 'Cynthia', 'Consommation: 230000', 'BS00106', '230000', 'bbci fumee', '2026-07-06 11:46:38', 'sortie', 'Active', 0),
(217, 'Cynthia', 'Consommation: 15000', 'BS00107', '15000', 'savon clear', '2026-07-06 11:48:55', 'sortie', 'Active', 0),
(218, 'Cynthia', 'Consommation: 10000', 'BS00108', '10000', 'frais pour terrain', '2026-07-06 11:49:16', 'sortie', 'Active', 0),
(219, 'Cynthia', 'Consommation: 33000', 'BS00109', '33000', 'ratio', '2026-07-06 11:49:29', 'sortie', 'Active', 0),
(220, 'Cynthia', 'Consommation: 6800', 'BS00110', '6800', 'representation dg', '2026-07-06 11:49:48', 'sortie', 'Active', 0),
(221, 'Cynthia', 'Consommation: 760000', 'BS00111', '760000', 'achat tshir13et lacoste10', '2026-07-06 11:50:30', 'sortie', 'Active', 0),
(222, 'Cynthia', 'Vente', '0053', '100000', 'Vente de :\n- 15.00 pièce Impression sur tshirt', '2026-07-06 11:56:43', 'entrée', 'Active', 0),
(223, 'Cynthia', 'Dette: 50000', '0053', '-', 'Client de passage', '2026-07-06 11:56:43', '', 'Active', 0),
(224, 'Cynthia', 'Vente', '0054', '140000', 'Vente de :\n- 14.00 pièce DtfA4', '2026-07-06 11:57:31', 'entrée', 'Active', 0),
(225, 'Cynthia', 'Vente', '0055', '60000', 'Vente de :\n- 4.00 m BACHE', '2026-07-06 12:36:43', 'entrée', 'Supprimée', 0),
(226, 'Cynthia', 'Annulation Vente', '0055', NULL, '60000', '2026-07-06 12:37:57', NULL, 'Active', 0),
(227, 'Cynthia', 'Vente', '0056', '40000', 'Vente de :\n- 4.00 m BACHE', '2026-07-06 12:39:30', 'entrée', 'Active', 0),
(228, 'Cynthia', 'Dette: 20000', '0056', '-', 'sammy', '2026-07-06 12:39:30', '', 'Active', 0),
(229, 'Cynthia', 'Vente', '0057', '2000', 'Vente de :\n- 2.00 piece Heating', '2026-07-06 12:57:58', 'entrée', 'Active', 0),
(230, 'Cynthia', 'Vente', '0058', '15000', 'Vente de :\n- 1.00 m BACHE', '2026-07-06 12:58:30', 'entrée', 'Active', 0),
(231, 'Cynthia', 'Consommation: 399000', 'BS00112', '399000', 'achat 14tshirtet lacoste2', '2026-07-06 12:59:21', 'sortie', 'Active', 0),
(232, 'Cynthia', 'Consommation: 80000', 'BS00113', '80000', 'achat 6chapeau et aide', '2026-07-06 13:01:16', 'sortie', 'Active', 0),
(233, 'Cynthia', 'Consommation: 1000', 'BS00114', '1000', 'allumette', '2026-07-06 13:12:49', 'sortie', 'Active', 0),
(234, 'Cynthia', 'Consommation: 153000', 'BS00115', '153000', 'representation dg', '2026-07-06 13:13:09', 'sortie', 'Active', 0),
(235, 'Cynthia', 'Consommation: 8000', 'BS00116', '8000', 'ratio', '2026-07-06 13:17:57', 'sortie', 'Active', 0),
(236, 'Cynthia', 'Consommation: 10000', 'BS00117', '10000', 'transport soir', '2026-07-06 13:18:19', 'sortie', 'Active', 0),
(237, 'Cynthia', 'Consommation: 97200', 'BS00118', '97200', 'frs unite cash power', '2026-07-06 13:18:41', 'sortie', 'Active', 0),
(238, 'Cynthia', 'Consommation: 60000', 'BS00119', '60000', 'branding probox', '2026-07-06 13:19:00', 'sortie', 'Active', 0),
(239, 'Cynthia', 'Consommation: 40000', 'BS00120', '40000', 'transport', '2026-07-06 13:19:21', 'sortie', 'Active', 0),
(240, 'Cynthia', 'Consommation: 3000', 'BS00121', '3000', 'sachet', '2026-07-06 13:19:35', 'sortie', 'Active', 0),
(241, 'Cynthia', 'Encaissement libre', NULL, '56', 'autre frais disponibles', '2026-07-06 13:21:03', 'entrée', 'Active', 0),
(242, 'Cynthia', 'Encaissement libre', NULL, '56', 'autre frais disponibles', '2026-07-06 13:21:03', 'entrée', 'Active', 0),
(243, 'Cynthia', 'Consommation: 56', 'BS00122', '56', 'autres charges', '2026-07-06 13:21:50', 'sortie', 'Active', 0),
(244, 'Cynthia', 'Vente', '0059', '18000', 'Vente de :\n- 1.20 m BACHE', '2026-07-06 13:31:15', 'entrée', 'Active', 0),
(245, 'Cynthia', 'Vente', '0060', '10000', 'Vente de :\n- 1.00 pièce DtfA4', '2026-07-06 13:46:31', 'entrée', 'Active', 0),
(246, 'Cynthia', 'Paiement Dette', '0053', '50000', 'Client de passage - Paiement Dette - Facture #0053', '2026-07-06 13:56:44', 'entrée', 'Active', 0),
(247, 'Cynthia', 'Consommation: 30000', 'BS00123', '30000', 'ratio', '2026-07-06 13:57:22', 'sortie', 'Active', 0),
(248, 'Cynthia', 'Consommation: 120000', 'BS00124', '120000', 'superglue', '2026-07-06 14:01:38', 'sortie', 'Active', 0),
(249, 'Cynthia', 'Consommation: 14000', 'BS00125', '14000', 'cutteur', '2026-07-06 14:01:51', 'sortie', 'Active', 0),
(250, 'Cynthia', 'Consommation: 23000', 'BS00126', '23000', 'aide', '2026-07-06 14:02:06', 'sortie', 'Active', 0),
(251, 'Cynthia', 'Consommation: 12000', 'BS00127', '12000', 'representation dg', '2026-07-06 14:03:56', 'sortie', 'Active', 0),
(252, 'Cynthia', 'Consommation: 90000', 'BS00128', '90000', 'depense du 5/7/2026 dimanche', '2026-07-06 14:06:12', 'sortie', 'Active', 0),
(253, 'Cynthia', 'Vente', '0061', '60000', 'Vente de :\n- 4.00 m BACHE', '2026-07-06 14:09:17', 'entrée', 'Active', 0),
(254, 'Cynthia', 'Consommation: 50000', 'BS00129', '50000', 'journal', '2026-07-06 14:34:26', 'sortie', 'Active', 0),
(255, 'Cynthia', 'Vente', '0062', '1000', 'Vente de :\n- 2.00 pièce Photocopie', '2026-07-06 15:16:47', 'entrée', 'Active', 0),
(256, 'Cynthia', 'Consommation: 4500', 'BS00130', '4500', 'representationn dg', '2026-07-06 15:46:42', 'sortie', 'Active', 0),
(257, 'Cynthia', 'Vente', '0063', '4000', 'Vente de :\n- 8.00 pièce Anneau', '2026-07-06 15:51:46', 'entrée', 'Active', 0),
(258, 'Cynthia', 'Consommation: 30000', 'BS00131', '30000', 'representation dg', '2026-07-06 15:55:47', 'sortie', 'Active', 0),
(259, 'Cynthia', 'Consommation: 30000', 'BS00132', '30000', 'boulette', '2026-07-06 16:32:33', 'sortie', 'Active', 0),
(260, 'Cynthia', 'Vente', '0064', '200000', 'Vente de :\n- 21.60 m BACHE', '2026-07-06 16:58:39', 'entrée', 'Active', 0),
(261, 'Cynthia', 'Dette: 124000', '0064', '-', 'Client de passage', '2026-07-06 16:58:39', '', 'Active', 0),
(262, 'Cynthia', 'Consommation: 30000', 'BS00133', '30000', 'pret destin', '2026-07-06 17:27:51', 'sortie', 'Active', 0),
(263, 'Cynthia', 'Consommation: 4000', 'BS00134', '4000', 'superglue', '2026-07-07 08:21:53', 'sortie', 'Active', 0),
(264, 'Cynthia', 'Consommation: 37000', 'BS00135', '37000', 'lacoste obr', '2026-07-07 08:22:46', 'sortie', 'Active', 0),
(265, 'Cynthia', 'Consommation: 9000', 'BS00136', '9000', 'water', '2026-07-07 08:23:04', 'sortie', 'Active', 0),
(266, 'Cynthia', 'Consommation: 31000', 'BS00137', '31000', 'ratio', '2026-07-07 08:23:17', 'sortie', 'Active', 0),
(267, 'Cynthia', 'Consommation: 13500', 'BS00138', '13500', 'representation dg', '2026-07-07 08:23:52', 'sortie', 'Active', 0),
(268, 'Cynthia', 'Suppression Dépense No.BS00135', 'BS00135', '37000', 'Une dépense de 37000 enregistrée le 2026-07-07 a été supprimée et la caisse a été réajustée', '2026-07-07 08:23:58', 'entrée', 'Active', 0),
(269, 'Cynthia', 'Consommation: 10000', 'BS00139', '10000', 'transport soir', '2026-07-07 09:49:25', 'sortie', 'Active', 0),
(270, 'Cynthia', 'Approvisionnement: 37000', 'BS00140', '37000', 'lacoste 1', '2026-07-07 09:49:59', 'sortie', 'Active', 0),
(271, 'Cynthia', 'Consommation: 20000', 'BS00141', '20000', 'avance broderie obr', '2026-07-07 09:50:37', 'sortie', 'Active', 0),
(272, 'Cynthia', 'Vente', '0065', '98100', 'Vente de :\n- 6.54 m BACHE', '2026-07-07 10:30:46', 'entrée', 'Active', 0),
(273, 'Cynthia', 'Vente', '0066', '3000', 'Vente de :\n- 2.00 pièce IMPRESSION EN COULEUR', '2026-07-07 10:31:23', 'entrée', 'Active', 0),
(274, 'Cynthia', 'Vente', '0067', '1000', 'Vente de :\n- 1.00 pièce Enveloppe A4', '2026-07-07 10:31:55', 'entrée', 'Active', 0),
(275, 'Cynthia', 'Vente', '0068', '1000', 'Vente de :\n- 1.00 pièce Impression N/B', '2026-07-07 10:33:04', 'entrée', 'Active', 0),
(276, 'Cynthia', 'Vente', '0069', '63720', 'Vente de :\n- 3.00 m BACHE', '2026-07-07 10:34:25', 'entrée', 'Active', 0),
(277, 'Cynthia', 'Approvisionnement: 27000', 'BS00142', '27000', 'tshirt simple', '2026-07-07 10:39:46', 'sortie', 'Active', 0),
(278, 'Cynthia', 'Consommation: 74000', 'BS00143', '74000', 'obr broderie', '2026-07-08 07:34:34', 'sortie', 'Active', 0),
(279, 'Cynthia', 'Vente', '0070', '20000', 'Vente de :\n- 10.00 pièce Impression N/B', '2026-07-08 07:36:38', 'entrée', 'Active', 0),
(280, 'Cynthia', 'Paiement Dette', '0064', '124000', 'Client de passage - Paiement Dette - Facture #0064', '2026-07-08 07:37:09', 'entrée', 'Active', 0),
(281, 'Cynthia', 'Consommation: 20000', 'BS00144', '20000', 'representation dg', '2026-07-08 07:43:51', 'sortie', 'Active', 0),
(282, 'Cynthia', 'Consommation: 9000', 'BS00145', '9000', 'water', '2026-07-08 07:44:27', 'sortie', 'Active', 0),
(283, 'Cynthia', 'Consommation: 20000', 'BS00146', '20000', 'cordes', '2026-07-08 07:45:11', 'sortie', 'Active', 0),
(284, 'Cynthia', 'Consommation: 30000', 'BS00147', '30000', 'aide', '2026-07-08 07:45:45', 'sortie', 'Active', 0),
(285, 'Cynthia', 'Consommation: 5000', 'BS00148', '5000', 'cutting', '2026-07-08 07:46:23', 'sortie', 'Active', 0),
(286, 'Cynthia', 'Consommation: 25000', 'BS00149', '25000', 'transport soir', '2026-07-08 07:47:38', 'sortie', 'Active', 0),
(287, 'Cynthia', 'Vente', '0071', '6000', 'Vente de :\n- 12.00 pièce Anneau', '2026-07-08 07:52:09', 'entrée', 'Active', 0),
(288, 'Cynthia', 'Encaissement libre', NULL, '30', 'autes frais disponible', '2026-07-08 07:54:13', 'entrée', 'Active', 0),
(289, 'Cynthia', 'Consommation: 10000', 'BS00150', '10000', 'terrain bubet', '2026-07-08 07:59:20', 'sortie', 'Active', 0),
(290, 'Cynthia', 'Consommation: 10000', 'BS00151', '10000', 'representation dg', '2026-07-08 07:59:55', 'sortie', 'Active', 0),
(291, 'Cynthia', 'Consommation: 1500', 'BS00152', '1500', 'sachet', '2026-07-08 08:00:11', 'sortie', 'Active', 0),
(292, 'Cynthia', 'Paiement Dette', '0049', '758000', 'Client de passage - Paiement Dette - Facture #0049', '2026-07-08 10:07:14', 'entrée', 'Active', 0),
(293, 'Cynthia', 'Vente', '0072', '200000', 'Vente de :\n- 5.00 pièce DTF 1m', '2026-07-08 10:07:43', 'entrée', 'Active', 0),
(294, 'Cynthia', 'Vente', '0073', '5000', 'Vente de :\n- 1.00 pièce Pvc', '2026-07-08 13:07:27', 'entrée', 'Active', 0),
(295, 'Cynthia', 'Vente', '0074', '4000', 'Vente de :\n- 1.00 piece Porte badge', '2026-07-08 13:09:05', 'entrée', 'Active', 0),
(296, 'Cynthia', 'Vente', '0075', '50000', 'Vente de :\n- 5.00 pièce DtfA4', '2026-07-08 13:09:50', 'entrée', 'Active', 0),
(297, 'Cynthia', 'Vente', '0076', '20000', 'Vente de :\n- 10.00 piece Heating', '2026-07-08 13:11:04', 'entrée', 'Active', 0),
(298, 'Cynthia', 'Vente', '0077', '15000', 'Vente de :\n- 1.00 m BACHE', '2026-07-08 13:11:52', 'entrée', 'Active', 0),
(299, 'Cynthia', 'Vente', '0078', '18000', 'Vente de :\n- 1.20 m BACHE', '2026-07-08 13:12:24', 'entrée', 'Active', 0),
(300, 'Cynthia', 'Vente', '0079', '30000', 'Vente de :\n- 3.00 pièce DtfA4', '2026-07-08 13:12:51', 'entrée', 'Active', 0),
(301, 'Cynthia', 'Vente', '0080', '15000', 'Vente de :\n- 10.00 piece Heating', '2026-07-08 13:13:39', 'entrée', 'Active', 0),
(302, 'Cynthia', 'Vente', '0081', '40000', 'Vente de :\n- 2.00 pièce IMPRESSION SUR GILET', '2026-07-08 13:14:16', 'entrée', 'Active', 0),
(303, 'Cynthia', 'Vente', '0082', '10000', 'Vente de :\n- 1.00 pièce DtfA4', '2026-07-08 14:14:18', 'entrée', 'Active', 0),
(304, 'Cynthia', 'Vente', '0083', '45000', 'Vente de :\n- 3.00 m BACHE', '2026-07-08 14:14:51', 'entrée', 'Active', 0),
(305, 'Cynthia', 'Vente', '0084', '17280', 'Vente de :\n- 0.96 m AUTOCOLLANT', '2026-07-08 14:16:07', 'entrée', 'Active', 0),
(306, 'Cynthia', 'Encaissement libre', NULL, '120', 'autre frais dispnible', '2026-07-08 14:17:17', 'entrée', 'Active', 0),
(307, 'Cynthia', 'Consommation: 1000', 'BS00153', '1000', 'unite', '2026-07-08 14:18:00', 'sortie', 'Active', 0),
(308, 'Cynthia', 'Consommation: 2000', 'BS00154', '2000', 'water', '2026-07-08 14:18:49', 'sortie', 'Active', 0),
(309, 'Cynthia', 'Consommation: 377200', 'BS00155', '377200', 'representation dg', '2026-07-08 14:19:40', 'sortie', 'Active', 0),
(310, 'Cynthia', 'Consommation: 78300', 'BS00156', '78300', 'cash power unite', '2026-07-08 14:20:11', 'sortie', 'Active', 0),
(311, 'Cynthia', 'Consommation: 50000', 'BS00157', '50000', 'assistance sociale', '2026-07-08 14:20:55', 'sortie', 'Active', 0),
(312, 'Cynthia', 'Consommation: 49000', 'BS00158', '49000', 'frais de representation dg', '2026-07-08 14:32:58', 'sortie', 'Active', 0),
(313, 'Cynthia', 'Consommation: 20000', 'BS00159', '20000', 'lavage voiture', '2026-07-08 14:33:22', 'sortie', 'Active', 0),
(314, 'Cynthia', 'Consommation: 34000', 'BS00160', '34000', 'ratio', '2026-07-08 14:33:45', 'sortie', 'Active', 0),
(315, 'Cynthia', 'Consommation: 1500', 'BS00161', '1500', 'sachet', '2026-07-08 14:36:16', 'sortie', 'Active', 0),
(316, 'Cynthia', 'Vente', '0085', '2000', 'Vente de :\n- 2.00 pièce Impression N/B', '2026-07-08 15:08:14', 'entrée', 'Active', 0),
(317, 'Cynthia', 'Consommation: 20000', 'BS00162', '20000', 'representation dg', '2026-07-08 15:10:46', 'sortie', 'Active', 0),
(318, 'Cynthia', 'Vente', '0086', '10000', 'Vente de :\n- 1.00 pièce DtfA4', '2026-07-09 13:40:41', 'entrée', 'Supprimée', 0),
(319, 'Cynthia', 'Vente', '0087', '20000', 'Vente de :\n- 1.00 pièce Impression sur tshirt', '2026-07-09 13:41:45', 'entrée', 'Active', 0),
(320, 'Cynthia', 'Vente', '0088', '30000', 'Vente de :\n- 2.00 m BACHE', '2026-07-09 13:42:47', 'entrée', 'Active', 0),
(321, 'Cynthia', 'Vente', '0089', '100000', 'Vente de :\n- 1.00 piece Branding pancarte de 2m sur 2m', '2026-07-09 14:09:21', 'entrée', 'Active', 0),
(322, 'Cynthia', 'Vente', '0090', '500', 'Vente de :\n- 1.00 pièce Photocopie', '2026-07-09 14:11:04', 'entrée', 'Active', 0),
(323, 'Cynthia', 'Vente', '0091', '15000', 'Vente de :\n- 30.00 pièce Carte de visite', '2026-07-09 14:12:35', 'entrée', 'Active', 0),
(324, 'Cynthia', 'Vente', '0092', '10000', 'Vente de :\n- 1.00 pièce DtfA4', '2026-07-09 14:13:08', 'entrée', 'Active', 0),
(325, 'Cynthia', 'Vente', '0093', '60000', 'Vente de :\n- 4.00 m BACHE', '2026-07-09 14:13:43', 'entrée', 'Active', 0),
(326, 'Cynthia', 'Vente', '0094', '4000', 'Vente de :\n- 8.00 pièce Anneau', '2026-07-09 14:14:10', 'entrée', 'Active', 0),
(327, 'Cynthia', 'Consommation: 3000', 'BS00163', '3000', 'city', '2026-07-09 14:23:03', 'sortie', 'Active', 0),
(328, 'Cynthia', 'Consommation: 10000', 'BS00164', '10000', 'aide life', '2026-07-09 14:23:34', 'sortie', 'Active', 0),
(329, 'Cynthia', 'Consommation: 20000', 'BS00165', '20000', 'transport soir', '2026-07-09 14:24:27', 'sortie', 'Active', 0),
(330, 'Cynthia', 'Consommation: 20000', 'BS00166', '20000', 'superglue', '2026-07-09 14:57:39', 'sortie', 'Active', 0),
(331, 'Cynthia', 'Consommation: 5000', 'BS00167', '5000', 'terrain bubet', '2026-07-09 14:58:30', 'sortie', 'Active', 0),
(332, 'Cynthia', 'Consommation: 5000', 'BS00167', '5000', 'terrain bubet', '2026-07-09 14:58:30', 'sortie', 'Active', 0),
(333, 'Cynthia', 'Consommation: 5000', 'BS00167', '5000', 'terrain bubet', '2026-07-09 14:58:30', 'sortie', 'Active', 0),
(334, 'Cynthia', 'Suppression Dépense No.BS00167', 'BS00167', '5000', 'Une dépense de 5000 enregistrée le 2026-07-09 a été supprimée et la caisse a été réajustée', '2026-07-09 14:58:39', 'entrée', 'Active', 0),
(335, 'Cynthia', 'Suppression Dépense No.BS00167', 'BS00167', '5000', 'Une dépense de 5000 enregistrée le 2026-07-09 a été supprimée et la caisse a été réajustée', '2026-07-09 14:58:42', 'entrée', 'Active', 0),
(336, 'Cynthia', 'Consommation: 14000', 'BS00168', '14000', 'cutteur', '2026-07-09 14:59:21', 'sortie', 'Active', 0),
(337, 'Cynthia', 'Consommation: 11000', 'BS00169', '11000', 'water', '2026-07-09 14:59:50', 'sortie', 'Active', 0),
(338, 'Cynthia', 'Consommation: 20000', 'BS00170', '20000', 'porte cle', '2026-07-09 15:00:10', 'sortie', 'Active', 0),
(339, 'Cynthia', 'Consommation: 1500', 'BS00171', '1500', 'MEGA', '2026-07-09 15:00:34', 'sortie', 'Active', 0),
(340, 'Cynthia', 'Vente', '0095', '10000', 'Vente de :\n- 1.00 pièce DtfA4', '2026-07-09 15:05:57', 'entrée', 'Active', 0),
(341, 'Cynthia', 'Consommation: 10000', 'BS00172', '10000', 'couche 130g', '2026-07-09 15:06:41', 'sortie', 'Active', 0),
(342, 'Cynthia', 'Consommation: 32500', 'BS00173', '32500', 'assurance moto', '2026-07-09 15:07:12', 'sortie', 'Active', 0),
(343, 'Cynthia', 'Consommation: 41000', 'BS00174', '41000', 'ratio', '2026-07-09 15:07:27', 'sortie', 'Active', 0),
(344, 'Cynthia', 'Consommation: 22000', 'BS00175', '22000', 'pret niyo', '2026-07-09 15:10:45', 'sortie', 'Active', 0),
(345, 'Cynthia', 'Consommation: 32400', 'BS00176', '32400', 'lavage noah', '2026-07-09 15:11:28', 'sortie', 'Active', 0),
(346, 'Cynthia', 'Consommation: 100000', 'BS00177', '100000', 'M O soudeur', '2026-07-09 15:12:09', 'sortie', 'Active', 0),
(347, 'Cynthia', 'Consommation: 46500', 'BS00178', '46500', 'representation dg', '2026-07-09 15:13:09', 'sortie', 'Active', 0),
(348, 'Cynthia', 'Vente', '0096', '500', 'Vente de :\n- 1.00 pièce Photocopie', '2026-07-09 15:15:38', 'entrée', 'Active', 0),
(349, 'Cynthia', 'Consommation: 15000', 'BS00179', '15000', 'moto', '2026-07-09 15:16:59', 'sortie', 'Active', 0),
(350, 'Cynthia', 'Vente', '0097', '20000', 'Vente de :\n- 2.00 pièce DtfA4', '2026-07-09 15:26:11', 'entrée', 'Active', 0),
(351, 'Cynthia', 'Consommation: 5000', 'BS00180', '5000', 'representation dg', '2026-07-09 16:04:16', 'sortie', 'Active', 0),
(352, 'Cynthia', 'Consommation: 3000', 'BS00181', '3000', 'ratio', '2026-07-09 16:05:05', 'sortie', 'Active', 0),
(353, 'Cynthia', 'Consommation: 10000', 'BS00182', '10000', 'representation dg', '2026-07-09 16:09:01', 'sortie', 'Active', 0),
(354, 'Cynthia', 'Annulation Vente', '0086', NULL, '10000', '2026-07-09 16:20:53', NULL, 'Active', 0),
(355, 'Cynthia', 'Vente', '0098', '224000', 'Vente de :\n- 280.00 pièce Impression N/B', '2026-07-10 12:18:54', 'entrée', 'Active', 0),
(356, 'Cynthia', 'Vente', '0099', '20000', 'Vente de :\n- 1.00 piece Finition Avec spiral', '2026-07-10 12:20:42', 'entrée', 'Active', 0),
(357, 'Cynthia', 'Vente', '0100', '6000', 'Vente de :\n- 6.00 pièce Impression N/B', '2026-07-10 12:21:20', 'entrée', 'Active', 0),
(358, 'Cynthia', 'Consommation: 40000', 'BS00183', '40000', 'transport soir', '2026-07-10 12:22:00', 'sortie', 'Active', 0),
(359, 'Cynthia', 'Consommation: 140000', 'BS00184', '140000', 'achat 70stylos', '2026-07-10 12:22:29', 'sortie', 'Active', 0),
(360, 'Cynthia', 'Consommation: 8000', 'BS00185', '8000', 'superglue', '2026-07-10 12:22:52', 'sortie', 'Active', 0),
(361, 'Cynthia', 'Approvisionnement: 37000', 'BS00186', '37000', 'achat lacoste', '2026-07-10 12:23:25', 'sortie', 'Active', 0),
(362, 'Cynthia', 'Consommation: 10000', 'BS00187', '10000', 'noah symonise', '2026-07-10 12:25:37', 'sortie', 'Active', 0),
(363, 'Cynthia', 'Consommation: 25000', 'BS00188', '25000', 'reliure', '2026-07-10 12:26:31', 'sortie', 'Active', 0),
(364, 'Cynthia', 'Consommation: 23000', 'BS00189', '23000', 'representation dg', '2026-07-10 12:26:59', 'sortie', 'Active', 0),
(365, 'Cynthia', 'Consommation: 32000', 'BS00190', '32000', 'ratio', '2026-07-10 12:27:19', 'sortie', 'Active', 0),
(366, 'Cynthia', 'Vente', '0101', '1000', 'Vente de :\n- 1.00 pièce Impression N/B', '2026-07-10 12:28:47', 'entrée', 'Active', 0),
(367, 'Cynthia', 'Vente', '0102', '45000', 'Vente de :\n- 1.00 pièce Lacoste', '2026-07-10 13:08:31', 'entrée', 'Active', 0),
(368, 'Cynthia', 'Vente', '0103', '60000', 'Vente de :\n- 1.50 m BACHE', '2026-07-10 15:36:13', 'entrée', 'Active', 0),
(369, 'Cynthia', 'Vente', '0104', '12000', 'Vente de :\n- 0.64 m AUTOCOLLANT', '2026-07-10 15:41:59', 'entrée', 'Active', 0),
(370, 'Cynthia', 'Consommation: 45500', 'BS00191', '45500', 'representation dg', '2026-07-10 15:44:42', 'sortie', 'Active', 0),
(371, 'Cynthia', 'Approvisionnement: 220000', 'BS00192', '220000', 'cordes febabu badge', '2026-07-10 15:45:13', 'sortie', 'Active', 0),
(372, 'Cynthia', 'Consommation: 5000', 'BS00193', '5000', 'ratio', '2026-07-10 15:45:30', 'sortie', 'Active', 0),
(373, 'Cynthia', 'Consommation: 10000', 'BS00194', '10000', 'terrain bubet', '2026-07-10 15:46:36', 'sortie', 'Active', 0),
(374, 'Cynthia', 'Consommation: 15000', 'BS00195', '15000', 'representation dg', '2026-07-10 15:48:32', 'sortie', 'Active', 0),
(375, 'Cynthia', 'Approvisionnement: 208000', 'BS00196', '208000', 'fixation pancarte bubet', '2026-07-10 16:05:00', 'sortie', 'Active', 0),
(376, 'Cynthia', 'Vente', '0105', '115000', 'Vente de :\n- 65.00 piece Stylo personnalise vip', '2026-07-10 16:07:19', 'entrée', 'Active', 0),
(377, 'Cynthia', 'Dette: 275000', '0105', '-', 'Client de passage', '2026-07-10 16:07:19', '', 'Active', 0),
(378, 'Cynthia', 'Vente', '0106', '42000', 'Vente de :\n- 2.80 m BACHE', '2026-07-10 16:45:06', 'entrée', 'Active', 0),
(379, 'Cynthia', 'Vente', '0107', '20000', 'Vente de :\n- 1.00 pièce Design', '2026-07-10 16:45:40', 'entrée', 'Active', 0),
(380, 'Cynthia', 'Vente', '0108', '2000', 'Vente de :\n- 4.00 pièce Anneau', '2026-07-10 16:46:07', 'entrée', 'Active', 0),
(381, 'Cynthia', 'Vente', '0109', '1000', 'Vente de :\n- 1.00 pièce Impression N/B', '2026-07-10 17:10:04', 'entrée', 'Active', 0),
(382, 'Cynthia', 'Consommation: 70000', 'BS00197', '70000', 'transport soir', '2026-07-13 06:52:33', 'sortie', 'Active', 0),
(383, 'Cynthia', 'Approvisionnement: 3000', 'BS00198', '3000', 'frais de retrait', '2026-07-13 06:52:57', 'sortie', 'Active', 0),
(384, 'Cynthia', 'Paiement Dette', '0105', '275000', 'Client de passage - Paiement Dette - Facture #0105', '2026-07-13 06:53:18', 'entrée', 'Active', 0),
(385, 'Cynthia', 'Consommation: 250000', 'BS00199', '250000', 'encourragement', '2026-07-13 06:54:36', 'sortie', 'Active', 0),
(386, 'Cynthia', 'Vente', '0110', '10000', 'Vente de :\n- 1.00 pièce DtfA4', '2026-07-13 06:55:32', 'entrée', 'Active', 0),
(387, 'Cynthia', 'Vente', '0111', '49500', 'Vente de :\n- 3.30 m BACHE', '2026-07-13 07:00:15', 'entrée', 'Active', 0),
(388, 'Cynthia', 'Vente', '0112', '500', 'Vente de :\n- 1.00 pièce Anneau', '2026-07-13 07:00:39', 'entrée', 'Active', 0),
(389, 'Cynthia', 'Consommation: 5000', 'BS00200', '5000', 'serviette', '2026-07-13 07:01:39', 'sortie', 'Active', 0),
(390, 'Cynthia', 'Approvisionnement: 9000', 'BS00201', '9000', 'cordes', '2026-07-13 07:01:57', 'sortie', 'Active', 0),
(391, 'Cynthia', 'Consommation: 20000', 'BS00202', '20000', 'transport', '2026-07-13 07:02:18', 'sortie', 'Active', 0),
(392, 'Cynthia', 'Consommation: 1000', 'BS00203', '1000', 'unite', '2026-07-13 07:02:32', 'sortie', 'Active', 0),
(393, 'Cynthia', 'Consommation: 30000', 'BS00204', '30000', 'controle technique', '2026-07-13 07:35:06', 'sortie', 'Active', 0),
(394, 'Cynthia', 'Consommation: 17000', 'BS00205', '17000', 'representation dg', '2026-07-13 07:35:29', 'sortie', 'Active', 0),
(395, 'Cynthia', 'Consommation: 2000', 'BS00206', '2000', 'unite', '2026-07-13 07:35:41', 'sortie', 'Active', 0),
(396, 'Cynthia', 'Consommation: 2000', 'BS00207', '2000', 'representation dg', '2026-07-13 07:56:51', 'sortie', 'Active', 0),
(397, 'Cynthia', 'Consommation: 33000', 'BS00208', '33000', 'ratio', '2026-07-13 07:57:04', 'sortie', 'Active', 0),
(398, 'Isabelle', 'Vente', '0113', '90000', 'Vente de :\n- 2.00 pièce Lacoste', '2026-07-13 08:45:00', 'entrée', 'Supprimée', 0),
(399, 'Isabelle', 'Annulation Vente', '0113', NULL, '90000', '2026-07-13 08:45:33', NULL, 'Active', 0),
(400, 'Cynthia', 'Vente', '0114', '5000', 'Vente de :\n- 1.00 pièce Pvc', '2026-07-13 10:24:37', 'entrée', 'Active', 0),
(401, 'Cynthia', 'Vente', '0115', '5000', 'Vente de :\n- 1.00 piece Porte badge', '2026-07-13 10:25:12', 'entrée', 'Active', 0),
(402, 'Cynthia', 'Consommation: 2000', 'BS00209', '2000', 'deplacement', '2026-07-13 10:31:20', 'sortie', 'Active', 0),
(403, 'Cynthia', 'Approvisionnement: 150000', 'BS00210', '150000', 'avance conception 30 carnet utema', '2026-07-13 10:39:18', 'sortie', 'Active', 0),
(404, 'Cynthia', 'Approvisionnement: 5000', 'BS00211', '5000', 'representation dg', '2026-07-13 12:08:45', 'sortie', 'Active', 0),
(405, 'Cynthia', 'Vente', '0116', '9000', 'Vente de :\n- 18.00 pièce Photocopie', '2026-07-13 12:09:31', 'entrée', 'Active', 0),
(406, 'Cynthia', 'Vente', '0117', '1000', 'Vente de :\n- 1.00 pièce Enveloppe A4', '2026-07-13 12:09:51', 'entrée', 'Active', 0),
(407, 'Cynthia', 'Consommation: 5000', 'BS00212', '5000', 'ratio', '2026-07-13 13:58:08', 'sortie', 'Active', 0),
(408, 'Cynthia', 'Vente', '0118', '21240', 'Vente de :\n- 30.00 piece Papier auto A4 ETIQUETTE', '2026-07-13 16:00:33', 'entrée', 'Active', 0);
INSERT INTO `logs` (`id`, `user_id`, `action_type`, `facture`, `montant`, `descrip`, `date`, `type`, `status`, `viewed`) VALUES
(409, 'Cynthia', 'Vente', '0119', '50000', 'Vente de :\n- 5.00 pièce DtfA4', '2026-07-13 16:01:04', 'entrée', 'Active', 0),
(410, 'Cynthia', 'Consommation: 10000', 'BS00213', '10000', 'FRAIS DE REPRESENTATION DG', '2026-07-13 16:01:52', 'sortie', 'Active', 0),
(411, 'Cynthia', 'Consommation: 3000', 'BS00214', '3000', 'CITY', '2026-07-13 16:02:06', 'sortie', 'Active', 0),
(412, 'Cynthia', 'Consommation: 10000', 'BS00215', '10000', 'RATIO DU 14/7/2026', '2026-07-13 16:02:31', 'sortie', 'Active', 0),
(413, 'Cynthia', 'Consommation: 20000', 'BS00216', '20000', 'CONTROLE TECHNIQUE', '2026-07-13 16:05:05', 'sortie', 'Active', 0),
(414, 'Cynthia', 'Consommation: 3000', 'BS00217', '3000', 'SABLIMATION', '2026-07-13 16:06:36', 'sortie', 'Active', 0),
(415, 'Cynthia', 'Consommation: 2000', 'BS00218', '2000', 'FRAIS DE REPRESENTANT DG', '2026-07-13 16:13:50', 'sortie', 'Active', 0),
(416, 'Cynthia', 'Suppression Dépense No.BS00218', 'BS00218', '2000', 'Une dépense de 2000 enregistrée le 2026-07-13 a été supprimée et la caisse a été réajustée', '2026-07-13 16:52:20', 'entrée', 'Active', 0),
(417, 'Cynthia', 'Encaissement libre', NULL, '10', 'autre frais', '2026-07-13 16:52:56', 'entrée', 'Active', 0),
(418, 'Cynthia', 'Vente', '0120', '3000', 'Vente de :\n- 3.00 pièce Enveloppe A4', '2026-07-14 08:13:35', 'entrée', 'Active', 0),
(419, 'Cynthia', 'Consommation: 20000', 'BS00218', '20000', 'transport soir', '2026-07-14 08:14:11', 'sortie', 'Active', 0),
(420, 'Cynthia', 'Consommation: 15000', 'BS00219', '15000', 'frais de representation dg', '2026-07-14 08:14:51', 'sortie', 'Active', 0),
(421, 'Cynthia', 'Consommation: 23000', 'BS00220', '23000', 'ratio', '2026-07-14 08:15:02', 'sortie', 'Active', 0),
(422, 'Cynthia', 'Vente', '0121', '600000', 'Vente de :\n- 3.00 piece Xstand', '2026-07-14 08:48:57', 'entrée', 'Active', 0),
(423, 'Cynthia', 'Vente', '0122', '22500', 'Vente de :\n- 1.50 m BACHE', '2026-07-14 08:50:28', 'entrée', 'Active', 0),
(424, 'Cynthia', 'Vente', '0123', '100000', 'Vente de :\n- 10.00 m Autocollant non imprimé', '2026-07-14 08:52:56', 'entrée', 'Active', 0),
(425, 'Cynthia', 'Consommation: 15000', 'BS00221', '15000', 'colle', '2026-07-14 08:56:04', 'sortie', 'Active', 0),
(426, 'Cynthia', 'Consommation: 2000', 'BS00222', '2000', 'superlue', '2026-07-14 08:56:46', 'sortie', 'Active', 0),
(427, 'Cynthia', 'Consommation: 2000', 'BS00222', '2000', 'superlue', '2026-07-14 08:56:47', 'sortie', 'Active', 0),
(428, 'Cynthia', 'Suppression Dépense No.BS00222', 'BS00222', '2000', 'Une dépense de 2000 enregistrée le 2026-07-14 a été supprimée et la caisse a été réajustée', '2026-07-14 08:56:52', 'entrée', 'Active', 0),
(429, 'Cynthia', 'Consommation: 3000', 'BS00223', '3000', 'brosse', '2026-07-14 09:01:13', 'sortie', 'Active', 0),
(430, 'Cynthia', 'Consommation: 70000', 'BS00224', '70000', 'reparation chargeur moto', '2026-07-14 09:03:20', 'sortie', 'Active', 0),
(431, 'Cynthia', 'Consommation: 100000', 'BS00225', '100000', 'representation bubet', '2026-07-14 11:15:51', 'sortie', 'Active', 0),
(432, 'Cynthia', 'Consommation: 35000', 'BS00226', '35000', 'cadenat', '2026-07-14 11:17:00', 'sortie', 'Active', 0),
(433, 'Cynthia', 'Consommation: 1000', 'BS00227', '1000', 'clé', '2026-07-14 11:18:18', 'sortie', 'Active', 0),
(434, 'Cynthia', 'Consommation: 50000', 'BS00228', '50000', 'represenation dg', '2026-07-14 12:28:49', 'sortie', 'Active', 0),
(435, 'Cynthia', 'Approvisionnement: 50000', 'BS00229', '50000', 'bordereaux utema', '2026-07-14 12:29:19', 'sortie', 'Active', 0),
(436, 'Cynthia', 'Consommation: 5000', 'BS00230', '5000', 'ratio', '2026-07-14 12:29:46', 'sortie', 'Active', 0),
(437, 'Cynthia', 'Encaissement libre', NULL, '15000', 'paiement dette mahoro', '2026-07-14 12:33:36', 'entrée', 'Active', 0),
(438, 'Cynthia', 'Vente', '0124', '22500', 'Vente de :\n- 1.50 m BACHE', '2026-07-14 14:22:03', 'entrée', 'Active', 0),
(439, 'Cynthia', 'Vente', '0125', '3000', 'Vente de :\n- 6.00 pièce Anneau', '2026-07-14 14:22:28', 'entrée', 'Active', 0),
(440, 'Cynthia', 'Consommation: 40000', 'BS00231', '40000', 'representationdg', '2026-07-14 14:28:35', 'sortie', 'Active', 0),
(441, 'Cynthia', 'Consommation: 10000', 'BS00232', '10000', 'water', '2026-07-14 14:28:48', 'sortie', 'Active', 0),
(442, 'Cynthia', 'Consommation: 15000', 'BS00233', '15000', 'gilet', '2026-07-14 14:29:12', 'sortie', 'Active', 0),
(443, 'Cynthia', 'Consommation: 20000', 'BS00234', '20000', 'porte menteau', '2026-07-14 14:29:38', 'sortie', 'Active', 0),
(444, 'Cynthia', 'Consommation: 5000', 'BS00235', '5000', 'ratio', '2026-07-14 14:31:35', 'sortie', 'Active', 0),
(445, 'Cynthia', 'Consommation: 10000', 'BS00236', '10000', 'ratio du 15/6', '2026-07-14 14:31:53', 'sortie', 'Active', 0),
(446, 'Cynthia', 'Vente', '0126', '127440', 'Vente de :\n- 108.00 pièce Impression N/B', '2026-07-14 15:00:53', 'entrée', 'Active', 0),
(447, 'Cynthia', 'Vente', '0127', '20000', 'Vente de :\n- 2.00 pièce DtfA4', '2026-07-14 15:06:10', 'entrée', 'Active', 0),
(448, 'Cynthia', 'Vente', '0128', '5000', 'Vente de :\n- 1.00 pièce DTF A5', '2026-07-14 15:06:31', 'entrée', 'Active', 0),
(449, 'Cynthia', 'Consommation: 17000', 'BS00237', '17000', 'representaton dg', '2026-07-14 15:16:14', 'sortie', 'Active', 0),
(450, 'Cynthia', 'Consommation: 5000', 'BS00238', '5000', 'autres charges', '2026-07-14 15:16:40', 'sortie', 'Active', 0),
(451, 'Cynthia', 'Consommation: 7000', 'BS00239', '7000', 'representation dg', '2026-07-14 15:21:09', 'sortie', 'Active', 0),
(452, 'Cynthia', 'Vente', '0129', '10000', 'Vente de :\n- 1.00 pièce DtfA4', '2026-07-14 15:31:06', 'entrée', 'Active', 0),
(453, 'Cynthia', 'Vente', '0130', '20000', 'Vente de :\n- 1.00 pièce Badge vip', '2026-07-14 15:32:28', 'entrée', 'Active', 0),
(454, 'Cynthia', 'Consommation: 10000', 'BS00240', '10000', 'superglue', '2026-07-14 15:34:55', 'sortie', 'Active', 0),
(455, 'Cynthia', 'Suppression Dépense No.BS00232', 'BS00232', '10000', 'Une dépense de 10000 enregistrée le 2026-07-14 a été supprimée et la caisse a été réajustée', '2026-07-14 15:38:11', 'entrée', 'Active', 0),
(456, 'Cynthia', 'Consommation: 9000', 'BS00241', '9000', 'water', '2026-07-14 15:38:23', 'sortie', 'Active', 0),
(457, 'Cynthia', 'Vente', '0131', '10000', 'Vente de :\n- 1.00 pièce DtfA4', '2026-07-14 15:44:46', 'entrée', 'Active', 0),
(458, 'Cynthia', 'Vente', '0132', '2000', 'Vente de :\n- 1.00 pièce Cutting', '2026-07-15 07:37:08', 'entrée', 'Active', 0),
(459, 'Cynthia', 'Vente', '0133', '55800', 'Vente de :\n- 3.10 m AUTOCOLLANT', '2026-07-15 07:37:44', 'entrée', 'Active', 0),
(460, 'Cynthia', 'Vente', '0134', '455400', 'Vente de :\n- 30.36 m BACHE', '2026-07-15 07:38:50', 'entrée', 'Active', 0),
(461, 'Cynthia', 'Consommation: 409500', 'BS00242', '409500', 'inss', '2026-07-15 07:40:00', 'sortie', 'Active', 0),
(462, 'Cynthia', 'Consommation: 82200', 'BS00243', '82200', 'frs cash power', '2026-07-15 07:40:23', 'sortie', 'Active', 0),
(463, 'Cynthia', 'Consommation: 23000', 'BS00244', '23000', 'ratio', '2026-07-15 13:04:51', 'sortie', 'Active', 0),
(464, 'Cynthia', 'Consommation: 1000', 'BS00245', '1000', 'enveloppe', '2026-07-15 13:05:13', 'sortie', 'Active', 0),
(465, 'Cynthia', 'Consommation: 10000', 'BS00246', '10000', 'terrain bubet', '2026-07-15 13:05:44', 'sortie', 'Active', 0),
(466, 'Cynthia', 'Consommation: 3000', 'BS00247', '3000', 'entretien', '2026-07-15 13:06:05', 'sortie', 'Active', 0),
(467, 'Cynthia', 'Consommation: 6000', 'BS00248', '6000', 'sablimation', '2026-07-15 13:06:23', 'sortie', 'Active', 0),
(468, 'Cynthia', 'Consommation: 1000', 'BS00249', '1000', 'unite', '2026-07-15 13:06:35', 'sortie', 'Active', 0),
(469, 'Cynthia', 'Consommation: 13000', 'BS00250', '13000', 'achat casquette', '2026-07-15 13:06:54', 'sortie', 'Active', 0),
(470, 'Cynthia', 'Consommation: 10000', 'BS00251', '10000', 'representation dg', '2026-07-15 13:07:34', 'sortie', 'Active', 0),
(471, 'Cynthia', 'Vente', '0135', '36000', 'Vente de :\n- 2.00 m AUTOCOLLANT', '2026-07-15 13:08:29', 'entrée', 'Active', 0),
(472, 'Cynthia', 'Vente', '0136', '18000', 'Vente de :\n- 1.20 m BACHE', '2026-07-15 13:08:55', 'entrée', 'Active', 0),
(473, 'Cynthia', 'Vente', '0137', '2000', 'Vente de :\n- 4.00 pièce Anneau', '2026-07-15 13:09:30', 'entrée', 'Active', 0),
(474, 'Cynthia', 'Encaissement libre', NULL, '1000000', 'dette payé Mamert', '2026-07-15 13:10:37', 'entrée', 'Active', 0),
(475, 'Cynthia', 'Approvisionnement: 1092000', 'BS00252', '1092000', 'achat casquette et gilet', '2026-07-15 13:12:24', 'sortie', 'Active', 0),
(476, 'Cynthia', 'Consommation: 114000', 'BS00253', '114000', 'sablimation', '2026-07-15 13:21:45', 'sortie', 'Active', 0),
(477, 'Cynthia', 'Suppression Dépense No.BS00253', 'BS00253', '114000', 'Une dépense de 114000 enregistrée le 2026-07-15 a été supprimée et la caisse a été réajustée', '2026-07-15 15:36:04', 'entrée', 'Active', 0),
(478, 'Cynthia', 'Consommation: 60000', 'BS00253', '60000', 'sublimation', '2026-07-15 15:36:38', 'sortie', 'Active', 0),
(479, 'Cynthia', 'Consommation: 14200', 'BS00254', '14200', 'achat chapeau,', '2026-07-15 15:37:58', 'sortie', 'Active', 0),
(480, 'Cynthia', 'Consommation: 202000', 'BS00255', '202000', 'encre dtf', '2026-07-15 15:40:23', 'sortie', 'Active', 0),
(481, 'Cynthia', 'Consommation: 5000', 'BS00256', '5000', 'ratio', '2026-07-15 15:40:41', 'sortie', 'Active', 0),
(482, 'Cynthia', 'Consommation: 80000', 'BS00257', '80000', 'representation dg', '2026-07-15 15:41:48', 'sortie', 'Active', 0),
(483, 'Cynthia', 'Consommation: 40000', 'BS00258', '40000', 'enveloppe bic', '2026-07-15 15:42:42', 'sortie', 'Active', 0),
(484, 'Cynthia', 'Consommation: 40000', 'BS00258', '40000', 'enveloppe bic', '2026-07-15 15:42:42', 'sortie', 'Active', 0),
(485, 'Cynthia', 'Consommation: 40000', 'BS00258', '40000', 'enveloppe bic', '2026-07-15 15:42:43', 'sortie', 'Active', 0),
(486, 'Cynthia', 'Suppression Dépense No.BS00258', 'BS00258', '40000', 'Une dépense de 40000 enregistrée le 2026-07-15 a été supprimée et la caisse a été réajustée', '2026-07-15 15:42:49', 'entrée', 'Active', 0),
(487, 'Cynthia', 'Suppression Dépense No.BS00258', 'BS00258', '40000', 'Une dépense de 40000 enregistrée le 2026-07-15 a été supprimée et la caisse a été réajustée', '2026-07-15 15:42:54', 'entrée', 'Active', 0),
(488, 'Cynthia', 'Consommation: 16000', 'BS00259', '16000', 'superglue', '2026-07-15 15:43:12', 'sortie', 'Active', 0),
(489, 'Cynthia', 'Consommation: 4000', 'BS00260', '4000', 'superglue', '2026-07-15 15:43:35', 'sortie', 'Active', 0),
(490, 'Cynthia', 'Approvisionnement: 45000', 'BS00261', '45000', 'transport soir', '2026-07-16 09:01:46', 'sortie', 'Active', 0),
(491, 'Cynthia', 'Consommation: 15500', 'BS00262', '15500', 'représentation dg', '2026-07-16 09:02:37', 'sortie', 'Active', 0),
(492, 'Cynthia', 'Consommation: 20000', 'BS00263', '20000', 'bat utema', '2026-07-16 09:03:04', 'sortie', 'Active', 0),
(493, 'Cynthia', 'Consommation: 25000', 'BS00264', '25000', 'representation dg', '2026-07-16 09:03:25', 'sortie', 'Active', 0),
(494, 'Cynthia', 'Consommation: 31000', 'BS00265', '31000', 'ratio', '2026-07-16 09:03:35', 'sortie', 'Active', 0),
(495, 'Cynthia', 'Vente', '0138', '1000', 'Vente de :\n- 1.00 pièce Enveloppe A4', '2026-07-16 09:04:01', 'entrée', 'Active', 0),
(496, 'Cynthia', 'Vente', '0139', '10000', 'Vente de :\n- 1.00 piece Cashet simple', '2026-07-16 09:06:59', 'entrée', 'Active', 0),
(497, 'Cynthia', 'Dette: 20000', '0139', '-', 'Client de passage', '2026-07-16 09:06:59', '', 'Active', 0),
(498, 'Cynthia', 'Vente', '0140', '50000', 'Vente de :\n- 5.00 pièce DtfA4', '2026-07-16 10:07:02', 'entrée', 'Active', 0),
(499, 'Cynthia', 'Vente', '0141', '60000', 'Vente de :\n- 60.00 piece Heating', '2026-07-16 10:08:21', 'entrée', 'Active', 0),
(500, 'Cynthia', 'Vente', '0142', '9000', 'Vente de :\n- 9.00 pièce Impression N/B', '2026-07-16 16:17:34', 'entrée', 'Active', 0),
(501, 'Cynthia', 'Encaissement libre', NULL, '1100000', 'dette paye', '2026-07-16 16:19:19', 'entrée', 'Active', 0),
(502, 'Cynthia', 'Vente', '0143', '400000', 'Vente de :\n- 7.00 piece Livre plus finition', '2026-07-16 16:20:27', 'entrée', 'Active', 0),
(503, 'Cynthia', 'Dette: 370000', '0143', '-', 'Client de passage', '2026-07-16 16:20:27', '', 'Active', 0),
(504, 'Cynthia', 'Consommation: 80000', 'BS00266', '80000', 'representation dg', '2026-07-16 16:22:52', 'sortie', 'Active', 0),
(505, 'Cynthia', 'Consommation: 5000', 'BS00267', '5000', 'cutting', '2026-07-16 16:23:31', 'sortie', 'Active', 0),
(506, 'Cynthia', 'Consommation: 18000', 'BS00268', '18000', 'transport materiels', '2026-07-16 16:24:18', 'sortie', 'Active', 0),
(507, 'Cynthia', 'Approvisionnement: 45000', 'BS00269', '45000', 'porte badge', '2026-07-16 16:24:47', 'sortie', 'Active', 0),
(508, 'Cynthia', 'Consommation: 20000', 'BS00270', '20000', 'conception cashet', '2026-07-16 16:25:08', 'sortie', 'Active', 0),
(509, 'Cynthia', 'Consommation: 300000', 'BS00271', '300000', 'frais de representaton finance', '2026-07-16 16:25:52', 'sortie', 'Active', 0),
(510, 'Cynthia', 'Consommation: 30000', 'BS00272', '30000', 'latte', '2026-07-16 16:26:37', 'sortie', 'Active', 0),
(511, 'Cynthia', 'Consommation: 3000', 'BS00273', '3000', 'city', '2026-07-16 16:26:53', 'sortie', 'Active', 0),
(512, 'Cynthia', 'Consommation: 5000', 'BS00274', '5000', 'sac', '2026-07-16 16:27:05', 'sortie', 'Active', 0),
(513, 'Cynthia', 'Consommation: 4000', 'BS00275', '4000', 'frais de retrait', '2026-07-16 16:27:25', 'sortie', 'Active', 0),
(514, 'Cynthia', 'Consommation: 16200', 'BS00276', '16200', 'tube', '2026-07-16 16:27:43', 'sortie', 'Active', 0),
(515, 'Cynthia', 'Consommation: 114000', 'BS00277', '114000', 'representatin dg', '2026-07-16 16:32:16', 'sortie', 'Active', 0),
(516, 'Cynthia', 'Consommation: 10000', 'BS00278', '10000', 'finition livre', '2026-07-17 09:08:51', 'sortie', 'Active', 0),
(517, 'Cynthia', 'Consommation: 20000', 'BS00279', '20000', 'impression pvc10', '2026-07-17 09:10:45', 'sortie', 'Active', 0),
(518, 'Cynthia', 'Consommation: 20000', 'BS00279', '20000', 'impression pvc10', '2026-07-17 09:10:45', 'sortie', 'Active', 0),
(519, 'Cynthia', 'Approvisionnement: 45000', 'BS00280', '45000', 'porte badge', '2026-07-17 09:13:46', 'sortie', 'Active', 0),
(520, 'Cynthia', 'Consommation: 30000', 'BS00281', '30000', 'representqtin dg', '2026-07-17 09:22:09', 'sortie', 'Active', 0),
(521, 'Cynthia', 'Consommation: 10000', 'BS00282', '10000', 'colle', '2026-07-17 09:22:50', 'sortie', 'Active', 0),
(522, 'Cynthia', 'Consommation: 100000', 'BS00283', '100000', 'cartouche', '2026-07-17 09:23:52', 'sortie', 'Active', 0),
(523, 'Cynthia', 'Consommation: 50000', 'BS00284', '50000', 'representqtion brb', '2026-07-17 09:24:14', 'sortie', 'Active', 0),
(524, 'Cynthia', 'Consommation: 32000', 'BS00285', '32000', 'ratio', '2026-07-17 09:24:30', 'sortie', 'Active', 0),
(525, 'Cynthia', 'Consommation: 5000', 'BS00286', '5000', 'transport matériels', '2026-07-17 09:25:10', 'sortie', 'Active', 0),
(526, 'Cynthia', 'Vente', '0144', '20000', 'Vente de :\n- 2.00 pièce DtfA4', '2026-07-17 09:31:11', 'entrée', 'Active', 0),
(527, 'Cynthia', 'Vente', '0145', '120000', 'Vente de :\n- 100.00 m INVITATIONS', '2026-07-17 09:39:46', 'entrée', 'Active', 0),
(528, 'Cynthia', 'Vente', '0146', '32400', 'Vente de :\n- 2.16 m BACHE', '2026-07-17 09:46:24', 'entrée', 'Active', 0),
(529, 'Cynthia', 'Vente', '0147', '4500', 'Vente de :\n- 9.00 pièce Anneau', '2026-07-17 09:46:56', 'entrée', 'Active', 0),
(530, 'Cynthia', 'Suppression Dépense No.BS00279', 'BS00279', '20000', 'Une dépense de 20000 enregistrée le 2026-07-17 a été supprimée et la caisse a été réajustée', '2026-07-17 09:53:16', 'entrée', 'Active', 0),
(531, 'Cynthia', 'Consommation: 1000', 'BS00287', '1000', 'unite', '2026-07-17 10:11:57', 'sortie', 'Active', 0),
(532, 'Cynthia', 'Approvisionnement: 25000', 'BS00288', '25000', 'tshirt', '2026-07-17 10:12:08', 'sortie', 'Active', 0),
(533, 'Cynthia', 'Suppression Dépense No.BS00280', 'BS00280', '45000', 'Une dépense de 45000 enregistrée le 2026-07-17 a été supprimée et la caisse a été réajustée', '2026-07-17 10:14:09', 'entrée', 'Active', 0),
(534, 'Cynthia', 'Vente', '0148', '27360', 'Vente de :\n- 1.52 m AUTOCOLLANT', '2026-07-17 10:31:40', 'entrée', 'Active', 0),
(535, 'Cynthia', 'Encaissement libre', NULL, '140', 'autres frais dispnible', '2026-07-17 10:32:43', 'entrée', 'Active', 0),
(536, 'Cynthia', 'Consommation: 2500', 'BS00289', '2500', 'representqtion dg', '2026-07-17 10:35:58', 'sortie', 'Active', 0),
(537, 'Cynthia', 'Suppression Dépense No.BS00289', 'BS00289', '2500', 'Une dépense de 2500 enregistrée le 2026-07-17 a été supprimée et la caisse a été réajustée', '2026-07-17 14:40:58', 'entrée', 'Active', 0),
(538, 'Cynthia', 'Consommation: 2500', 'BS00289', '2500', 'autres charges', '2026-07-17 14:41:13', 'sortie', 'Active', 0),
(539, 'Cynthia', 'Consommation: 46500', 'BS00290', '46500', 'representation dg', '2026-07-17 14:42:17', 'sortie', 'Active', 0),
(540, 'Cynthia', 'Consommation: 35000', 'BS00291', '35000', 'aqllonge', '2026-07-17 14:43:17', 'sortie', 'Active', 0),
(541, 'Cynthia', 'Approvisionnement: 54000', 'BS00292', '54000', 'tshirt 2', '2026-07-17 14:43:45', 'sortie', 'Active', 0),
(542, 'Cynthia', 'Consommation: 3000', 'BS00293', '3000', 'cutting', '2026-07-17 14:48:29', 'sortie', 'Active', 0),
(543, 'Cynthia', 'Paiement Dette', '0139', '20000', 'Client de passage - Paiement Dette - Facture #0139', '2026-07-17 15:36:01', 'entrée', 'Active', 0),
(544, 'Cynthia', 'Paiement Dette', '0143', '370000', 'Client de passage - Paiement Dette - Facture #0143', '2026-07-17 15:36:16', 'entrée', 'Active', 0),
(545, 'Cynthia', 'Consommation: 2000', 'BS00294', '2000', 'cutting', '2026-07-17 15:40:24', 'sortie', 'Active', 0),
(546, 'Cynthia', 'Consommation: 85000', 'BS00295', '85000', 'representation dg', '2026-07-17 15:41:02', 'sortie', 'Active', 0),
(547, 'Cynthia', 'Consommation: 8000', 'BS00296', '8000', 'autres charges', '2026-07-17 15:41:22', 'sortie', 'Active', 0),
(548, 'Cynthia', 'Encaissement libre', NULL, '460000', 'alimentation caisse', '2026-07-17 15:53:37', 'entrée', 'Active', 0),
(549, 'Cynthia', 'Consommation: 2500', 'BS00297', '2500', 'frais de representation dg', '2026-07-17 16:05:37', 'sortie', 'Active', 0),
(550, 'Cynthia', 'Consommation: 50000', 'BS00298', '50000', 'pret cynthia', '2026-07-17 16:06:04', 'sortie', 'Active', 0),
(551, 'Cynthia', 'Consommation: 40000', 'BS00299', '40000', 'pret alix', '2026-07-17 16:06:18', 'sortie', 'Active', 0),
(552, 'Cynthia', 'Consommation: 40000', 'BS00299', '40000', 'pret alix', '2026-07-17 16:06:18', 'sortie', 'Active', 0),
(553, 'Cynthia', 'Suppression Dépense No.BS00299', 'BS00299', '40000', 'Une dépense de 40000 enregistrée le 2026-07-17 a été supprimée et la caisse a été réajustée', '2026-07-17 16:08:28', 'entrée', 'Active', 0),
(554, 'Cynthia', 'Consommation: 10000', 'BS00300', '10000', 'autres charges aide', '2026-07-17 16:26:22', 'sortie', 'Active', 0),
(555, 'Cynthia', 'Consommation: 5000', 'BS00301', '5000', 'installation tube', '2026-07-17 16:28:17', 'sortie', 'Active', 0),
(556, 'Cynthia', 'Consommation: 252000', 'BS00302', '252000', 'representation dg', '2026-07-17 16:35:25', 'sortie', 'Active', 0),
(557, 'Cynthia', 'Consommation: 10000', 'BS00303', '10000', 'transport soir', '2026-07-17 16:52:29', 'sortie', 'Active', 0),
(558, 'Cynthia', 'Vente', '0149', '30000', 'Vente de :\n- 3.00 m BACHE', '2026-07-17 16:53:04', 'entrée', 'Active', 0),
(559, 'Cynthia', 'Vente', '0150', '350000', 'Vente de :\n- 100.00 m Certificat', '2026-07-17 16:55:12', 'entrée', 'Active', 0),
(560, 'Cynthia', 'Vente', '0151', '10000', 'Vente de :\n- 2.00 pièce IMPRESSION EN COULEUR', '2026-07-17 17:37:25', 'entrée', 'Active', 0),
(561, 'Cynthia', 'Vente', '0152', '2000', 'Vente de :\n- 2.00 piece Heating', '2026-07-17 17:37:52', 'entrée', 'Active', 0),
(562, 'Cynthia', 'Vente', '0153', '22500', 'Vente de :\n- 1.50 m BACHE', '2026-07-20 06:55:55', 'entrée', 'Active', 0),
(563, 'Cynthia', 'Vente', '0154', '2000', 'Vente de :\n- 4.00 pièce Anneau', '2026-07-20 06:56:21', 'entrée', 'Active', 0),
(564, 'Cynthia', 'Vente', '0155', '59400', 'Vente de :\n- 3.30 m AUTOCOLLANT', '2026-07-20 06:57:20', 'entrée', 'Active', 0),
(565, 'Cynthia', 'Encaissement libre', NULL, '36', 'FRAIS DISPONIBLE', '2026-07-20 06:57:57', 'entrée', 'Active', 0),
(566, 'Cynthia', 'Vente', '0156', '15000', 'Vente de :\n- 1.00 m BACHE', '2026-07-20 06:58:49', 'entrée', 'Active', 0),
(567, 'Cynthia', 'Vente', '0157', '15000', 'Vente de :\n- 1.00 m BACHE', '2026-07-20 06:59:20', 'entrée', 'Active', 0),
(568, 'Cynthia', 'Vente', '0158', '2000', 'Vente de :\n- 4.00 pièce Anneau', '2026-07-20 06:59:43', 'entrée', 'Active', 0),
(569, 'Cynthia', 'Vente', '0159', '150000', 'Vente de :\n- 10.00 pièce Badge vip', '2026-07-20 07:00:19', 'entrée', 'Active', 0),
(570, 'Cynthia', 'Vente', '0160', '45000', 'Vente de :\n- 3.00 pièce Impression sur tshirt', '2026-07-20 07:09:15', 'entrée', 'Active', 0),
(571, 'Cynthia', 'Vente', '0161', '600000', 'Vente de :\n- 1.00 pièce Tear drop', '2026-07-20 07:11:22', 'entrée', 'Active', 0),
(572, 'Cynthia', 'Consommation: 115000', 'BS00304', '115000', 'transport', '2026-07-20 07:11:52', 'sortie', 'Active', 0),
(573, 'Cynthia', 'Approvisionnement: 75000', 'BS00305', '75000', 'achat 3 tshirt', '2026-07-20 07:12:10', 'sortie', 'Active', 0),
(574, 'Cynthia', 'Consommation: 643600', 'BS00306', '643600', 'frais de representation dg', '2026-07-20 07:14:31', 'sortie', 'Active', 0),
(575, 'Cynthia', 'Consommation: 2000', 'BS00307', '2000', 'aide', '2026-07-20 07:15:32', 'sortie', 'Active', 0),
(576, 'Cynthia', 'Consommation: 40000', 'BS00308', '40000', 'broderie ', '2026-07-20 07:15:54', 'sortie', 'Active', 0),
(577, 'Cynthia', 'Consommation: 30000', 'BS00309', '30000', 'transport soir', '2026-07-20 07:16:43', 'sortie', 'Active', 0),
(578, 'Cynthia', 'Consommation: 20000', 'BS00310', '20000', 'tear drop', '2026-07-20 07:17:04', 'sortie', 'Active', 0),
(579, 'Cynthia', 'Consommation: 10000', 'BS00311', '10000', 'ratio', '2026-07-20 07:18:48', 'sortie', 'Active', 0),
(580, 'Cynthia', 'Consommation: 10000', 'BS00311', '10000', 'ratio', '2026-07-20 07:18:49', 'sortie', 'Active', 0),
(581, 'Cynthia', 'Suppression Dépense No.BS00311', 'BS00311', '10000', 'Une dépense de 10000 enregistrée le 2026-07-20 a été supprimée et la caisse a été réajustée', '2026-07-20 07:20:05', 'entrée', 'Active', 0),
(582, 'Cynthia', 'Approvisionnement: 26000', 'BS00312', '26000', 'achat t-shirt Bic', '2026-07-20 07:20:37', 'sortie', 'Active', 0),
(583, 'Cynthia', 'Consommation: 30300', 'BS00313', '30300', 'ratio', '2026-07-20 07:56:27', 'sortie', 'Active', 0),
(584, 'Cynthia', 'Consommation: 1700', 'BS00314', '1700', 'représentation dg', '2026-07-20 09:56:54', 'sortie', 'Active', 0),
(585, 'Cynthia', 'Approvisionnement: 135000', 'BS00315', '135000', 'achat 5 tshirt', '2026-07-20 09:57:14', 'sortie', 'Active', 0),
(586, 'Cynthia', 'Consommation: 16000', 'BS00316', '16000', 'coll plus tournevis Bic', '2026-07-20 09:57:41', 'sortie', 'Active', 0),
(587, 'Cynthia', 'Vente', '0162', '11800', 'Vente de :\n- 0.40 m BACHE', '2026-07-20 14:39:19', 'entrée', 'Active', 0),
(588, 'Cynthia', 'Approvisionnement: 270000', 'BS00317', '270000', 'tshirt 10bic', '2026-07-20 14:46:27', 'sortie', 'Active', 0),
(589, 'Cynthia', 'Consommation: 2000', 'BS00318', '2000', 'aIDE', '2026-07-20 14:46:53', 'sortie', 'Active', 0),
(590, 'Cynthia', 'Consommation: 36000', 'BS00319', '36000', 'REPRESENTATION DG', '2026-07-20 14:47:20', 'sortie', 'Active', 0),
(591, 'Cynthia', 'Consommation: 12000', 'BS00320', '12000', 'REPRESENTATION DG', '2026-07-20 14:47:39', 'sortie', 'Active', 0),
(592, 'Cynthia', 'Consommation: 31000', 'BS00321', '31000', 'TUBE PLUS INSTALLATION', '2026-07-20 14:48:10', 'sortie', 'Active', 0),
(593, 'Cynthia', 'Consommation: 15000', 'BS00322', '15000', 'COLLE', '2026-07-20 14:48:31', 'sortie', 'Active', 0),
(594, 'Cynthia', 'Consommation: 250000', 'BS00323', '250000', 'CARNET UTEMA', '2026-07-20 14:49:07', 'sortie', 'Active', 0),
(595, 'Cynthia', 'Consommation: 10000', 'BS00324', '10000', 'TERRAN BUBET', '2026-07-20 14:49:29', 'sortie', 'Active', 0),
(596, 'Cynthia', 'Consommation: 20000', 'BS00325', '20000', 'AGENDA', '2026-07-20 14:49:59', 'sortie', 'Active', 0),
(597, 'Cynthia', 'Consommation: 3000', 'BS00326', '3000', 'RATIO', '2026-07-20 14:52:01', 'sortie', 'Active', 0),
(598, 'Cynthia', 'Approvisionnement: 5000', 'BS00327', '5000', 'RATIO', '2026-07-20 14:52:24', 'sortie', 'Active', 0),
(599, 'Cynthia', 'Consommation: 10000', 'BS00328', '10000', 'RATIO DU 21/6/2026', '2026-07-20 14:52:41', 'sortie', 'Active', 0),
(600, 'Cynthia', 'Consommation: 20500', 'BS00329', '20500', 'REPRESENTATION DG', '2026-07-20 15:23:59', 'sortie', 'Active', 0),
(601, 'Cynthia', 'Consommation: 20500', 'BS00329', '20500', 'REPRESENTATION DG', '2026-07-20 15:23:59', 'sortie', 'Active', 0),
(602, 'Cynthia', 'Suppression Dépense No.BS00329', 'BS00329', '20500', 'Une dépense de 20500 enregistrée le 2026-07-20 a été supprimée et la caisse a été réajustée', '2026-07-20 15:55:43', 'entrée', 'Active', 0),
(603, 'Cynthia', 'Vente', '0163', '8000', 'Vente de :\n- 2.00 piece Impression sur corde', '2026-07-20 16:09:18', 'entrée', 'Active', 0),
(604, 'Cynthia', 'Vente', '0164', '1000', 'Vente de :\n- 2.00 pièce Anneau', '2026-07-20 16:09:42', 'entrée', 'Active', 0),
(605, 'Cynthia', 'Vente', '0165', '42000', 'Vente de :\n- 2.80 m BACHE', '2026-07-20 16:10:19', 'entrée', 'Active', 0),
(606, 'Cynthia', 'Vente', '0166', '20000', 'Vente de :\n- 2.00 pièce DtfA4', '2026-07-21 09:11:33', 'entrée', 'Active', 0),
(607, 'Cynthia', 'Vente', '0167', '5000', 'Vente de :\n- 1.00 pièce DTF A5', '2026-07-21 10:39:05', 'entrée', 'Active', 0),
(608, 'Cynthia', 'Consommation: 23000', 'BS00330', '23000', 'ratio', '2026-07-21 10:39:29', 'sortie', 'Active', 0),
(609, 'Cynthia', 'Consommation: 14000', 'BS00331', '14000', 'transport soir', '2026-07-21 10:50:57', 'sortie', 'Active', 0),
(610, 'Cynthia', 'Approvisionnement: 15000', 'BS00332', '15000', 'corde bubet', '2026-07-21 10:51:17', 'sortie', 'Active', 0),
(611, 'Cynthia', 'Approvisionnement: 60000', 'BS00333', '60000', 'pvc jrs', '2026-07-21 10:51:37', 'sortie', 'Active', 0),
(612, 'Cynthia', 'Consommation: 2000', 'BS00334', '2000', 'represntaton dg', '2026-07-21 10:51:55', 'sortie', 'Active', 0),
(613, 'Cynthia', 'Vente', '0168', '36000', 'Vente de :\n- 2.40 m BACHE', '2026-07-21 14:02:18', 'entrée', 'Active', 0),
(614, 'Cynthia', 'Vente', '0169', '9000', 'Vente de :\n- 1.00 pièce Porte cle', '2026-07-21 14:05:52', 'entrée', 'Active', 0),
(615, 'Cynthia', 'Dette: 26000', '0169', '-', 'Client de passage', '2026-07-21 14:05:53', '', 'Active', 0),
(616, 'Cynthia', 'Consommation: 5000', 'BS00335', '5000', 'représentation dg', '2026-07-21 14:07:02', 'sortie', 'Active', 0),
(617, 'Cynthia', 'Consommation: 95000', 'BS00336', '95000', 'carnet utema et job card', '2026-07-21 14:08:16', 'sortie', 'Active', 0),
(618, 'Cynthia', 'Consommation: 34300', 'BS00337', '34300', 'impression pvc', '2026-07-21 15:05:43', 'sortie', 'Active', 0),
(619, 'Cynthia', 'Approvisionnement: 27000', 'BS00338', '27000', 'tshirt', '2026-07-21 15:05:57', 'sortie', 'Active', 0),
(620, 'Cynthia', 'Vente', '0170', '55000', 'Vente de :\n- 1.00 pièce Tshirt simple', '2026-07-21 15:25:09', 'entrée', 'Active', 0),
(621, 'Cynthia', 'Consommation: 41000', 'BS00339', '41000', 'représentation dg', '2026-07-21 15:27:06', 'sortie', 'Active', 0),
(622, 'Cynthia', 'Consommation: 40000', 'BS00340', '40000', 'representation dg', '2026-07-21 16:22:12', 'sortie', 'Active', 0),
(623, 'Cynthia', 'Consommation: 5000', 'BS00341', '5000', 'city', '2026-07-21 16:22:23', 'sortie', 'Active', 0),
(624, 'Cynthia', 'Consommation: 27000', 'BS00342', '27000', 'ajout achat tshirt fnuap', '2026-07-21 16:22:54', 'sortie', 'Active', 0),
(625, 'Cynthia', 'Suppression Dépense No.BS00342', 'BS00342', '27000', 'Une dépense de 27000 enregistrée le 2026-07-21 a été supprimée et la caisse a été réajustée', '2026-07-21 16:23:27', 'entrée', 'Active', 0),
(626, 'Cynthia', 'Approvisionnement: 27000', 'BS00342', '27000', 'ajout achat tshirt fnuap', '2026-07-21 16:23:48', 'sortie', 'Active', 0),
(627, 'Cynthia', 'Consommation: 20000', 'BS00343', '20000', 'Superglue ,déplacement', '2026-07-21 16:24:30', 'sortie', 'Active', 0),
(628, 'Cynthia', 'Consommation: 2000', 'BS00344', '2000', 'unité', '2026-07-21 16:24:47', 'sortie', 'Active', 0),
(629, 'Cynthia', 'Consommation: 5000', 'BS00345', '5000', 'representation dg', '2026-07-22 10:41:34', 'sortie', 'Active', 0),
(630, 'Cynthia', 'Consommation: 12000', 'BS00346', '12000', 'porte badge bubet', '2026-07-22 10:53:27', 'sortie', 'Active', 0),
(631, 'Cynthia', 'Vente', '0171', '20000', 'Vente de :\n- 2.00 pièce DtfA4', '2026-07-22 13:10:18', 'entrée', 'Active', 0),
(632, 'Cynthia', 'Vente', '0172', '50000', 'Vente de :\n- 1.00 piece Parapluie vip', '2026-07-22 13:16:21', 'entrée', 'Active', 0),
(633, 'Cynthia', 'Consommation: 3000', 'BS00347', '3000', 'unite', '2026-07-22 13:16:50', 'sortie', 'Active', 0),
(634, 'Cynthia', 'Consommation: 5000', 'BS00348', '5000', 'serviette', '2026-07-22 13:17:06', 'sortie', 'Active', 0),
(635, 'Cynthia', 'Consommation: 7000', 'BS00349', '7000', 'vice', '2026-07-22 13:18:00', 'sortie', 'Active', 0),
(636, 'Cynthia', 'Consommation: 33000', 'BS00350', '33000', 'ratio', '2026-07-22 13:18:15', 'sortie', 'Active', 0),
(637, 'Cynthia', 'Consommation: 10000', 'BS00351', '10000', 'metre', '2026-07-22 13:18:29', 'sortie', 'Active', 0),
(638, 'Cynthia', 'Approvisionnement: 25000', 'BS00352', '25000', 't-shirt 1', '2026-07-22 13:18:51', 'sortie', 'Active', 0),
(639, 'Cynthia', 'Consommation: 6000', 'BS00353', '6000', 'moto', '2026-07-22 13:27:25', 'sortie', 'Active', 0),
(640, 'Cynthia', 'Consommation: 20000', 'BS00354', '20000', 'inss', '2026-07-22 13:27:45', 'sortie', 'Active', 0),
(641, 'Cynthia', 'Consommation: 240000', 'BS00355', '240000', 'carburent', '2026-07-22 13:28:04', 'sortie', 'Active', 0),
(642, 'Cynthia', 'Approvisionnement: 40200', 'BS00356', '40200', 'tasse4', '2026-07-22 13:28:23', 'sortie', 'Active', 0),
(643, 'Cynthia', 'Vente', '0173', '100000', 'Vente de :\n- 4.00 rouleau Tasse', '2026-07-22 13:30:30', 'entrée', 'Active', 0),
(644, 'Cynthia', 'Consommation: 20000', 'BS00357', '20000', 'representation dg', '2026-07-22 13:32:10', 'sortie', 'Active', 0),
(645, 'Cynthia', 'Consommation: 10000', 'BS00358', '10000', 'ndaha pret', '2026-07-22 13:47:00', 'sortie', 'Active', 0),
(646, 'Cynthia', 'Vente', '0174', '80000', 'Vente de :\n- 2.00 pièce DTF 1m', '2026-07-22 14:07:33', 'entrée', 'Active', 0),
(647, 'Cynthia', 'Vente', '0175', '100000', 'Vente de :\n- 100.00 piece Heating', '2026-07-22 14:08:11', 'entrée', 'Active', 0),
(648, 'Cynthia', 'Consommation: 10000', 'BS00359', '10000', 'water', '2026-07-22 14:27:57', 'sortie', 'Active', 0),
(649, 'Cynthia', 'Consommation: 1000', 'BS00360', '1000', 'représentation dg', '2026-07-22 14:28:25', 'sortie', 'Active', 0),
(650, 'Cynthia', 'Consommation: 7000', 'BS00361', '7000', 'représentation dg', '2026-07-22 14:29:22', 'sortie', 'Active', 0),
(651, 'Cynthia', 'Consommation: 25000', 'BS00362', '25000', 'parapluie', '2026-07-22 15:00:23', 'sortie', 'Active', 0),
(652, 'Cynthia', 'Vente', '0176', '37500', 'Vente de :\n- 2.50 m BACHE', '2026-07-22 15:16:15', 'entrée', 'Active', 0),
(653, 'Cynthia', 'Vente', '0177', '30000', 'Vente de :\n- 3.00 pièce DtfA4', '2026-07-22 15:16:36', 'entrée', 'Active', 0),
(654, 'Cynthia', 'Vente', '0178', '5000', 'Vente de :\n- 1.00 pièce DTF A5', '2026-07-22 15:17:00', 'entrée', 'Active', 0),
(655, 'Cynthia', 'Consommation: 3000', 'BS00363', '3000', 'sabliamation', '2026-07-22 15:36:51', 'sortie', 'Active', 0),
(656, 'Cynthia', 'Consommation: 3000', 'BS00364', '3000', 'representation dg', '2026-07-22 15:37:17', 'sortie', 'Active', 0),
(657, 'Cynthia', 'Consommation: 20000', 'BS00365', '20000', 'bat carnet  Bic ', '2026-07-22 15:44:24', 'sortie', 'Active', 0),
(658, 'Cynthia', 'Vente', '0179', '10000', 'Vente de :\n- 1.00 pièce DtfA4', '2026-07-22 15:47:18', 'entrée', 'Active', 0),
(659, 'Cynthia', 'Vente', '0180', '5000', 'Vente de :\n- 1.00 pièce DTF A5', '2026-07-22 15:47:43', 'entrée', 'Active', 0),
(660, 'Cynthia', 'Consommation: 10000', 'BS00366', '10000', 'sablimation', '2026-07-22 15:50:23', 'sortie', 'Active', 0),
(661, 'Cynthia', 'Approvisionnement: 10000', 'BS00367', '10000', 'autres charges', '2026-07-22 16:17:47', 'sortie', 'Active', 0),
(662, 'Cynthia', 'Consommation: 30000', 'BS00368', '30000', 'assistance sociale', '2026-07-23 08:01:54', 'sortie', 'Active', 0),
(663, 'Cynthia', 'Consommation: 25000', 'BS00369', '25000', 'porte clé soutraitance ', '2026-07-23 08:02:31', 'sortie', 'Active', 0),
(664, 'Cynthia', 'Consommation: 3000', 'BS00370', '3000', 'city', '2026-07-23 08:02:42', 'sortie', 'Active', 0),
(665, 'Cynthia', 'Consommation: 5000', 'BS00371', '5000', 'ratio', '2026-07-23 08:03:02', 'sortie', 'Active', 0),
(666, 'Cynthia', 'Consommation: 2000', 'BS00372', '2000', 'sablimation', '2026-07-23 14:04:43', 'sortie', 'Active', 0),
(667, 'Cynthia', 'Consommation: 35500', 'BS00373', '35500', 'ratio', '2026-07-23 14:16:01', 'sortie', 'Active', 0),
(668, 'Cynthia', 'Consommation: 99000', 'BS00374', '99000', 'représentation dg', '2026-07-23 14:17:15', 'sortie', 'Active', 0),
(669, 'Cynthia', 'Consommation: 35000', 'BS00375', '35000', 'reparation machine', '2026-07-23 14:17:45', 'sortie', 'Active', 0),
(670, 'Cynthia', 'Vente', '0181', '15000', 'Vente de :\n- 1.00 m AUTOCOLLANT', '2026-07-23 14:20:00', 'entrée', 'Active', 0),
(671, 'Cynthia', 'Vente', '0182', '30000', 'Vente de :\n- 1.00 pièce AGENDA A5', '2026-07-23 14:20:26', 'entrée', 'Active', 0),
(672, 'Cynthia', 'Vente', '0183', '10000', 'Vente de :\n- 1.00 pièce DtfA4', '2026-07-23 14:20:52', 'entrée', 'Active', 0),
(673, 'Cynthia', 'Vente', '0184', '134100', 'Vente de :\n- 8.94 m BACHE', '2026-07-23 14:22:40', 'entrée', 'Active', 0),
(674, 'Cynthia', 'Vente', '0185', '6000', 'Vente de :\n- 4.00 pièce Photocopie', '2026-07-23 14:25:52', 'entrée', 'Active', 0),
(675, 'Cynthia', 'Consommation: 22500', 'BS00376', '22500', 'représentation dg', '2026-07-23 14:38:12', 'sortie', 'Active', 0),
(676, 'Cynthia', 'Vente', '0186', '20000', 'Vente de :\n- 1.00 pièce Impression sur tshirt', '2026-07-23 15:20:30', 'entrée', 'Active', 0),
(677, 'Cynthia', 'Consommation: 2000', 'BS00377', '2000', 'representation dg', '2026-07-23 16:07:00', 'sortie', 'Active', 0),
(678, 'Cynthia', 'Encaissement libre', NULL, '9800000', 'retrait de la banque le ', '2026-07-24 09:14:09', 'entrée', 'Active', 0),
(679, 'Cynthia', 'Consommation: 5000000', 'BS00378', '5000000', 'représentantion ,msp', '2026-07-24 09:15:52', 'sortie', 'Active', 0),
(680, 'Cynthia', 'Consommation: 350000', 'BS00379', '350000', 'batterie vitz', '2026-07-24 09:16:24', 'sortie', 'Active', 0),
(681, 'Cynthia', 'Consommation: 250000', 'BS00380', '250000', 'frais de representation canal', '2026-07-24 09:16:57', 'sortie', 'Active', 0),
(682, 'Cynthia', 'Consommation: 50000', 'BS00381', '50000', 'frais de represntation toursime', '2026-07-24 09:17:33', 'sortie', 'Active', 0),
(683, 'Cynthia', 'Consommation: 1315000', 'BS00382', '1315000', 'encouragement staff', '2026-07-24 09:18:32', 'sortie', 'Active', 0),
(684, 'Cynthia', 'Approvisionnement: 2835000', 'BS00383', '2835000', 'achat t-shirt 105', '2026-07-24 09:19:48', 'sortie', 'Active', 0),
(685, 'Cynthia', 'Approvisionnement: 2835000', 'BS00383', '2835000', 'achat t-shirt 105', '2026-07-24 09:19:49', 'sortie', 'Active', 0),
(686, 'Cynthia', 'Suppression Dépense No.BS00383', 'BS00383', '2835000', 'Une dépense de 2835000 enregistrée le 2026-07-24 a été supprimée et la caisse a été réajustée', '2026-07-24 09:19:57', 'entrée', 'Active', 0),
(687, 'Cynthia', 'Encaissement libre', NULL, '5250000', 'retrait de la banque du 23/7/2026', '2026-07-24 09:20:54', 'entrée', 'Active', 0),
(688, 'Cynthia', 'Consommation: 3500000', 'BS00384', '3500000', '400livres bic', '2026-07-24 09:21:27', 'sortie', 'Active', 0),
(689, 'Cynthia', 'Consommation: 300000', 'BS00385', '300000', 'représentation dg', '2026-07-24 09:24:08', 'sortie', 'Active', 0),
(690, 'Cynthia', 'Consommation: 600000', 'BS00386', '600000', 'representation bbci', '2026-07-24 09:25:35', 'sortie', 'Active', 0),
(691, 'Cynthia', 'Consommation: 443000', 'BS00387', '443000', 'mecanicien noah', '2026-07-24 09:26:07', 'sortie', 'Active', 0),
(692, 'Cynthia', 'Consommation: 125000', 'BS00388', '125000', 'frais de mission tamba bic', '2026-07-24 09:27:01', 'sortie', 'Active', 0),
(693, 'Cynthia', 'Consommation: 12000', 'BS00389', '12000', 'banquier', '2026-07-24 09:27:26', 'sortie', 'Active', 0),
(694, 'Cynthia', 'Consommation: 270000', 'BS00390', '270000', 'batterie', '2026-07-24 09:27:52', 'sortie', 'Active', 0),
(695, 'Cynthia', 'Consommation: 15000', 'BS00391', '15000', 'transport soir', '2026-07-24 09:32:38', 'sortie', 'Active', 0),
(696, 'Cynthia', 'Consommation: 40000', 'BS00392', '40000', 'représentation finance', '2026-07-24 09:33:16', 'sortie', 'Active', 0),
(697, 'Cynthia', 'Consommation: 4000', 'BS00393', '4000', 'représentation dg', '2026-07-24 09:33:45', 'sortie', 'Active', 0),
(698, 'Cynthia', 'Vente', '0187', '365900', 'Vente de :\n- 114.44 m BACHE', '2026-07-24 09:52:46', 'entrée', 'Active', 0),
(699, 'Cynthia', 'Dette: 1350700', '0187', '-', 'patient', '2026-07-24 09:52:46', '', 'Active', 0),
(700, 'Cynthia', 'Vente', '0188', '200000', 'Vente de :\n- 6.00 pièce Tshirt simple', '2026-07-24 12:08:27', 'entrée', 'Active', 0),
(701, 'Cynthia', 'Dette: 154000', '0188', '-', 'Victory', '2026-07-24 12:08:27', '', 'Active', 0),
(702, 'Cynthia', 'Consommation: 17000', 'BS00394', '17000', 'ratio', '2026-07-24 12:10:21', 'sortie', 'Active', 0),
(703, 'Cynthia', 'Consommation: 40000', 'BS00395', '40000', 'tear drop', '2026-07-24 12:10:54', 'sortie', 'Active', 0),
(704, 'Cynthia', 'Consommation: 5000', 'BS00396', '5000', 'cutting', '2026-07-24 12:11:09', 'sortie', 'Active', 0),
(705, 'Cynthia', 'Consommation: 40000', 'BS00397', '40000', 'representation dg', '2026-07-24 12:11:36', 'sortie', 'Active', 0),
(706, 'Cynthia', 'Consommation: 10000', 'BS00398', '10000', 'deplacement', '2026-07-24 12:12:00', 'sortie', 'Active', 0),
(707, 'Cynthia', 'Consommation: 131000', 'BS00399', '131000', 'representation dg', '2026-07-24 15:10:56', 'sortie', 'Active', 0),
(708, 'Cynthia', 'Consommation: 25000', 'BS00400', '25000', 'reparation noah', '2026-07-24 15:11:21', 'sortie', 'Active', 0),
(709, 'Cynthia', 'Consommation: 20000', 'BS00401', '20000', 'superglue', '2026-07-24 15:11:46', 'sortie', 'Active', 0),
(710, 'Cynthia', 'Consommation: 3000', 'BS00402', '3000', 'ratio', '2026-07-24 15:12:33', 'sortie', 'Active', 0),
(711, 'Cynthia', 'Consommation: 2500', 'BS00403', '2500', 'unite', '2026-07-24 15:12:47', 'sortie', 'Active', 0),
(712, 'Cynthia', 'Approvisionnement: 165000', 'BS00404', '165000', 'achat 6tshirt', '2026-07-24 15:13:18', 'sortie', 'Active', 0),
(713, 'Cynthia', 'Consommation: 10000', 'BS00405', '10000', 'pret don', '2026-07-24 15:13:37', 'sortie', 'Active', 0),
(714, 'Cynthia', 'Consommation: 2000', 'BS00406', '2000', 'water', '2026-07-24 15:13:54', 'sortie', 'Active', 0),
(715, 'Cynthia', 'Consommation: 24000', 'BS00407', '24000', 'representation dg', '2026-07-24 15:14:19', 'sortie', 'Active', 0),
(716, 'Cynthia', 'Consommation: 100000', 'BS00408', '100000', 'frs cash power', '2026-07-24 15:14:44', 'sortie', 'Active', 0),
(717, 'Cynthia', 'Consommation: 35000', 'BS00409', '35000', 'transport banner autocollant couche', '2026-07-24 15:16:18', 'sortie', 'Active', 0),
(718, 'Cynthia', 'Consommation: 10000', 'BS00410', '10000', 'terrain bbci', '2026-07-24 15:16:37', 'sortie', 'Active', 0),
(719, 'Cynthia', 'Consommation: 25000', 'BS00411', '25000', 'representation dg', '2026-07-24 15:16:56', 'sortie', 'Active', 0),
(720, 'Cynthia', 'Consommation: 7000', 'BS00412', '7000', 'water', '2026-07-24 16:30:25', 'sortie', 'Active', 0),
(721, 'Cynthia', 'Suppression Dépense No.BS00396', 'BS00396', '5000', 'Une dépense de 5000 enregistrée le 2026-07-24 a été supprimée et la caisse a été réajustée', '2026-07-24 16:31:13', 'entrée', 'Active', 0),
(722, 'Cynthia', 'Consommation: 3000', 'BS00413', '3000', 'cutting', '2026-07-24 16:31:26', 'sortie', 'Active', 0),
(723, 'Cynthia', 'Vente', '0189', '10000', 'Vente de :\n- 2.00 pièce IMPRESSION EN COULEUR', '2026-07-24 16:34:48', 'entrée', 'Active', 0),
(724, 'Cynthia', 'Vente', '0190', '80000', 'Vente de :\n- 8.00 pièce DtfA4', '2026-07-25 12:51:11', 'entrée', 'Active', 0),
(725, 'Cynthia', 'Paiement Dette', '0188', '154000', 'Victory - Paiement Dette - Facture #0188', '2026-07-25 12:52:28', 'entrée', 'Active', 0),
(726, 'Cynthia', 'Vente', '0191', '37800', 'Vente de :\n- 2.52 m BACHE', '2026-07-25 12:53:35', 'entrée', 'Active', 0),
(727, 'Cynthia', 'Vente', '0192', '1000', 'Vente de :\n- 2.00 pièce Anneau', '2026-07-25 12:54:13', 'entrée', 'Active', 0),
(728, 'Cynthia', 'Vente', '0193', '39000', 'Vente de :\n- 2.60 m BACHE', '2026-07-25 12:57:18', 'entrée', 'Active', 0),
(729, 'Cynthia', 'Consommation: 60000', 'BS00414', '60000', 'superglue', '2026-07-25 12:58:14', 'sortie', 'Active', 0),
(730, 'Cynthia', 'Consommation: 90000', 'BS00415', '90000', 'ratio soir plus deplacement', '2026-07-25 12:58:57', 'sortie', 'Active', 0),
(731, 'Cynthia', 'Consommation: 21000', 'BS00416', '21000', 'representation dg', '2026-07-25 12:59:18', 'sortie', 'Active', 0),
(732, 'Cynthia', 'Consommation: 10000', 'BS00417', '10000', 'bbci terrain', '2026-07-25 12:59:39', 'sortie', 'Active', 0),
(733, 'Cynthia', 'Vente', '0194', '10000', 'Vente de :\n- 1.00 pièce DtfA4', '2026-07-25 14:40:35', 'entrée', 'Active', 0),
(734, 'Cynthia', 'Vente', '0195', '5000', 'Vente de :\n- 1.00 pièce DTF A5', '2026-07-25 14:41:04', 'entrée', 'Active', 0),
(735, 'Cynthia', 'Consommation: 10000', 'BS00418', '10000', 'deplacement', '2026-07-25 14:44:55', 'sortie', 'Active', 0),
(736, 'Cynthia', 'Consommation: 1000', 'BS00419', '1000', 'marqeur', '2026-07-27 08:37:13', 'sortie', 'Active', 0),
(737, 'Cynthia', 'Consommation: 10000', 'BS00420', '10000', 'ratio', '2026-07-27 08:37:34', 'sortie', 'Active', 0),
(738, 'Cynthia', 'Consommation: 10000', 'BS00421', '10000', 'représentation dg', '2026-07-27 08:38:47', 'sortie', 'Active', 0),
(739, 'Cynthia', 'Consommation: 5000', 'BS00422', '5000', 'moto', '2026-07-27 08:39:04', 'sortie', 'Active', 0),
(740, 'Cynthia', 'Vente', '0196', '70000', 'Vente de :\n- 2.00 pièce Tshirt simple', '2026-07-27 09:00:47', 'entrée', 'Active', 0),
(741, 'Cynthia', 'Vente', '0197', '30000', 'Vente de :\n- 1.00 pièce Porte cle', '2026-07-27 09:01:59', 'entrée', 'Active', 0),
(742, 'Cynthia', 'Vente', '0198', '15000', 'Vente de :\n- 1.00 pièce Porte cle', '2026-07-27 09:02:30', 'entrée', 'Active', 0),
(743, 'Cynthia', 'Vente', '0199', '400000', 'Vente de :\n- 200.00 m INVITATIONS', '2026-07-27 09:03:55', 'entrée', 'Active', 0),
(744, 'Cynthia', 'Consommation: 515000', 'BS00423', '515000', 'représentation dg', '2026-07-27 09:04:40', 'sortie', 'Active', 0),
(745, 'Cynthia', 'Vente', '0200', '24000', 'Vente de :\n- 1.60 m BACHE', '2026-07-27 09:51:17', 'entrée', 'Active', 0),
(746, 'Cynthia', 'Vente', '0201', '1000', 'Vente de :\n- 2.00 pièce Anneau', '2026-07-27 09:51:46', 'entrée', 'Active', 0),
(747, 'Cynthia', 'Consommation: 21500', 'BS00424', '21500', 'ratio', '2026-07-27 09:52:12', 'sortie', 'Active', 0),
(748, 'Cynthia', 'Consommation: 2000', 'BS00425', '2000', 'représentation dg', '2026-07-27 09:52:44', 'sortie', 'Active', 0),
(749, 'Cynthia', 'Vente', '0202', '10000', 'Vente de :\n- 1.00 pièce Impression sur tshirt', '2026-07-27 15:27:52', 'entrée', 'Active', 0),
(750, 'Cynthia', 'Vente', '0203', '3000', 'Vente de :\n- 1.00 pièce Reliure', '2026-07-27 15:29:01', 'entrée', 'Active', 0),
(751, 'Cynthia', 'Consommation: 2000', 'BS00426', '2000', 'unite', '2026-07-27 15:29:39', 'sortie', 'Active', 0),
(752, 'Cynthia', 'Consommation: 12000', 'BS00427', '12000', 'representation dg', '2026-07-27 15:30:21', 'sortie', 'Active', 0),
(753, 'Cynthia', 'Consommation: 25000', 'BS00428', '25000', 'scoth', '2026-07-27 15:30:37', 'sortie', 'Active', 0),
(754, 'Cynthia', 'Consommation: 6000', 'BS00429', '6000', 'unite et entretien', '2026-07-27 15:31:57', 'sortie', 'Active', 0),
(755, 'Cynthia', 'Approvisionnement: 83000', 'BS00430', '83000', 'représentation dg', '2026-07-27 17:29:46', 'sortie', 'Active', 0),
(756, 'Cynthia', 'Consommation: 5000', 'BS00431', '5000', 'representation dg', '2026-07-27 17:30:05', 'sortie', 'Active', 0),
(757, 'Cynthia', 'Paiement Dette', '0187', '1350700', 'patient - Paiement Dette - Facture #0187', '2026-07-28 10:40:36', 'entrée', 'Active', 0),
(758, 'Cynthia', 'Consommation: 25000', 'BS00432', '25000', 'transport soir', '2026-07-28 10:45:14', 'sortie', 'Active', 0),
(759, 'Cynthia', 'Consommation: 30000', 'BS00433', '30000', 'ratio', '2026-07-28 10:45:32', 'sortie', 'Active', 0),
(760, 'Cynthia', 'Consommation: 8000', 'BS00434', '8000', 'representation dg', '2026-07-28 10:46:25', 'sortie', 'Active', 0),
(761, 'Cynthia', 'Consommation: 8000', 'BS00434', '8000', 'representation dg', '2026-07-28 10:46:25', 'sortie', 'Active', 0),
(762, 'Cynthia', 'Consommation: 9000', 'BS00435', '9000', 'water', '2026-07-28 10:46:41', 'sortie', 'Active', 0),
(763, 'Cynthia', 'Consommation: 9000', 'BS00435', '9000', 'water', '2026-07-28 10:46:41', 'sortie', 'Active', 0),
(764, 'Cynthia', 'Consommation: 35000', 'BS00436', '35000', 'porte clé jrs', '2026-07-28 10:47:12', 'sortie', 'Active', 0),
(765, 'Cynthia', 'Consommation: 16000', 'BS00437', '16000', 'cutteur', '2026-07-28 10:47:27', 'sortie', 'Active', 0),
(766, 'Cynthia', 'Consommation: 16000', 'BS00437', '16000', 'cutteur', '2026-07-28 10:47:27', 'sortie', 'Active', 0),
(767, 'Cynthia', 'Consommation: 5000', 'BS00438', '5000', 'terrain bbci', '2026-07-28 10:48:10', 'sortie', 'Active', 0),
(768, 'Cynthia', 'Approvisionnement: 26000', 'BS00439', '26000', 'tshirt', '2026-07-28 11:00:26', 'sortie', 'Active', 0),
(769, 'Cynthia', 'Vente', '0204', '60000', 'Vente de :\n- 2.00 piece Facturier 2', '2026-07-28 15:48:25', 'entrée', 'Active', 0),
(770, 'Cynthia', 'Consommation: 40000', 'BS00440', '40000', 'porte cle', '2026-07-28 15:59:00', 'sortie', 'Active', 0),
(771, 'Cynthia', 'Consommation: 14000', 'BS00441', '14000', 'cutteur', '2026-07-28 15:59:35', 'sortie', 'Active', 0),
(772, 'Cynthia', 'Consommation: 9000', 'BS00442', '9000', 'water', '2026-07-28 15:59:51', 'sortie', 'Active', 0),
(773, 'Cynthia', 'Consommation: 3000', 'BS00443', '3000', 'city', '2026-07-28 16:00:08', 'sortie', 'Active', 0),
(774, 'Cynthia', 'Consommation: 8500', 'BS00444', '8500', 'representation dg', '2026-07-28 16:00:46', 'sortie', 'Active', 0),
(775, 'Cynthia', 'Approvisionnement: 10000', 'BS00445', '10000', 'aide', '2026-07-28 16:01:23', 'sortie', 'Active', 0),
(776, 'Cynthia', 'Consommation: 10000', 'BS00446', '10000', 'transport materiel', '2026-07-28 16:01:55', 'sortie', 'Active', 0),
(777, 'Cynthia', 'Suppression Dépense No.BS00437', 'BS00437', '16000', 'Une dépense de 16000 enregistrée le 2026-07-28 a été supprimée et la caisse a été réajustée', '2026-07-28 16:10:37', 'entrée', 'Active', 0),
(778, 'Cynthia', 'Suppression Dépense No.BS00434', 'BS00434', '8000', 'Une dépense de 8000 enregistrée le 2026-07-28 a été supprimée et la caisse a été réajustée', '2026-07-28 16:10:45', 'entrée', 'Active', 0),
(779, 'Cynthia', 'Suppression Dépense No.BS00435', 'BS00435', '9000', 'Une dépense de 9000 enregistrée le 2026-07-28 a été supprimée et la caisse a été réajustée', '2026-07-28 16:10:57', 'entrée', 'Active', 0),
(780, 'Cynthia', 'Consommation: 172000', 'BS00447', '172000', 'cash power unite', '2026-07-28 16:14:24', 'sortie', 'Active', 0),
(781, 'Cynthia', 'Consommation: 4500', 'BS00448', '4500', 'cordes 3', '2026-07-29 06:34:32', 'sortie', 'Active', 0),
(782, 'Cynthia', 'Consommation: 7500', 'BS00449', '7500', 'porte badges', '2026-07-29 06:34:52', 'sortie', 'Active', 0),
(783, 'Cynthia', 'Consommation: 350000', 'BS00450', '350000', 'pvc', '2026-07-29 06:35:15', 'sortie', 'Active', 0),
(784, 'Cynthia', 'Consommation: 350000', 'BS00450', '350000', 'pvc', '2026-07-29 06:35:15', 'sortie', 'Active', 0),
(785, 'Cynthia', 'Consommation: 25000', 'BS00451', '25000', 'papier photo autocollant', '2026-07-29 06:36:12', 'sortie', 'Active', 0),
(786, 'Cynthia', 'Consommation: 25000', 'BS00451', '25000', 'papier photo autocollant', '2026-07-29 06:36:12', 'sortie', 'Active', 0),
(787, 'Cynthia', 'Approvisionnement: 164000', 'BS00452', '164000', 't-shirt 6', '2026-07-29 06:36:53', 'sortie', 'Active', 0),
(788, 'Cynthia', 'Suppression Dépense No.BS00451', 'BS00451', '25000', 'Une dépense de 25000 enregistrée le 2026-07-29 a été supprimée et la caisse a été réajustée', '2026-07-29 06:37:26', 'entrée', 'Active', 0),
(789, 'Cynthia', 'Suppression Dépense No.BS00450', 'BS00450', '350000', 'Une dépense de 350000 enregistrée le 2026-07-29 a été supprimée et la caisse a été réajustée', '2026-07-29 06:37:31', 'entrée', 'Active', 0),
(790, 'Cynthia', 'Consommation: 20000', 'BS00453', '20000', 'transport soir', '2026-07-29 06:41:12', 'sortie', 'Active', 0),
(791, 'Cynthia', 'Vente', '0205', '18000', 'Vente de :\n- 1.20 m BACHE', '2026-07-29 06:43:34', 'entrée', 'Active', 0),
(792, 'Cynthia', 'Consommation: 5000', 'BS00454', '5000', 'terrain bbci', '2026-07-29 06:58:34', 'sortie', 'Active', 0),
(793, 'Cynthia', 'Consommation: 15000', 'BS00455', '15000', 'bbci terrain', '2026-07-29 07:30:29', 'sortie', 'Active', 0),
(794, 'Cynthia', 'Consommation: 100000', 'BS00456', '100000', 'achat DAO', '2026-07-29 09:34:56', 'sortie', 'Active', 0),
(795, 'Cynthia', 'Consommation: 33000', 'BS00457', '33000', 'RATIO', '2026-07-29 09:35:10', 'sortie', 'Active', 0),
(796, 'Cynthia', 'Consommation: 16000', 'BS00458', '16000', 'colle', '2026-07-29 14:44:07', 'sortie', 'Active', 0),
(797, 'Cynthia', 'Consommation: 5000', 'BS00459', '5000', 'ratio', '2026-07-29 14:45:08', 'sortie', 'Active', 0),
(798, 'Cynthia', 'Consommation: 9000', 'BS00460', '9000', 'representation dg', '2026-07-29 14:45:42', 'sortie', 'Active', 0),
(799, 'Cynthia', 'Consommation: 45000', 'BS00461', '45000', 'transport matériels', '2026-07-29 15:28:39', 'sortie', 'Active', 0),
(800, 'Cynthia', 'Vente', '0206', '5000', 'Vente de :\n- 10.00 pièce Photocopie', '2026-07-29 15:31:15', 'entrée', 'Active', 0),
(801, 'Cynthia', 'Consommation: 33500', 'BS00462', '33500', 'Assurance', '2026-07-29 16:14:42', 'sortie', 'Active', 0);
INSERT INTO `logs` (`id`, `user_id`, `action_type`, `facture`, `montant`, `descrip`, `date`, `type`, `status`, `viewed`) VALUES
(802, 'Cynthia', 'Vente', '0207', '250000', 'Vente de :\n- 96.00 m BACHE', '2026-07-29 16:20:47', 'entrée', 'Active', 0),
(803, 'Cynthia', 'Dette: 1190000', '0207', '-', 'Client de passage', '2026-07-29 16:20:47', '', 'Active', 0),
(804, 'Cynthia', 'Consommation: 5000', 'BS00463', '5000', 'bbci', '2026-07-29 17:05:07', 'sortie', 'Active', 0),
(805, 'Cynthia', 'Consommation: 266000', 'BS00464', '266000', 'dtf', '2026-07-29 17:08:28', 'sortie', 'Active', 0),
(806, 'Cynthia', 'Vente', '0208', '2000', 'Vente de :\n- 2.00 pièce Impression N/B', '2026-07-30 10:27:11', 'entrée', 'Active', 0),
(807, 'Cynthia', 'Vente', '0209', '1000', 'Vente de :\n- 1.00 pièce Enveloppe A4', '2026-07-30 10:27:39', 'entrée', 'Active', 0),
(808, 'Cynthia', 'Vente', '0210', '90000', 'Vente de :\n- 6.00 m BACHE', '2026-07-30 10:28:24', 'entrée', 'Active', 0),
(809, 'Cynthia', 'Vente', '0211', '150000', 'Vente de :\n- 3.00 pièce Lacoste', '2026-07-30 10:32:19', 'entrée', 'Active', 0),
(810, 'Cynthia', 'Vente', '0212', '15000', 'Vente de :\n- 1.00 pièce Impression sur tshirt', '2026-07-30 10:33:47', 'entrée', 'Active', 0),
(811, 'Cynthia', 'Vente', '0213', '20000', 'Vente de :\n- 1.00 rouleau Tasse', '2026-07-30 10:35:47', 'entrée', 'Active', 0),
(812, 'Cynthia', 'Dette: 10000', '0213', '-', '79032914', '2026-07-30 10:35:47', '', 'Active', 0),
(813, 'Cynthia', 'Consommation: 70000', 'BS00465', '70000', 'transport ', '2026-07-30 10:36:52', 'sortie', 'Active', 0),
(814, 'Cynthia', 'Consommation: 10000', 'BS00466', '10000', 'superglue', '2026-07-30 10:37:56', 'sortie', 'Active', 0),
(815, 'Cynthia', 'Consommation: 5000', 'BS00467', '5000', 'serviette', '2026-07-30 10:40:55', 'sortie', 'Active', 0),
(816, 'Cynthia', 'Consommation: 100000', 'BS00468', '100000', 'commission de réception onpr ', '2026-07-30 10:43:40', 'sortie', 'Active', 0),
(817, 'Cynthia', 'Consommation: 31000', 'BS00469', '31000', 'ratio', '2026-07-30 10:43:59', 'sortie', 'Active', 0),
(818, 'Cynthia', 'Consommation: 10000', 'BS00470', '10000', 'mètre', '2026-07-30 10:44:24', 'sortie', 'Active', 0),
(819, 'Cynthia', 'Consommation: 2000', 'BS00471', '2000', 'stylos', '2026-07-30 10:44:48', 'sortie', 'Active', 0),
(820, 'Cynthia', 'Consommation: 10500', 'BS00472', '10500', 'représentation dg', '2026-07-30 10:46:12', 'sortie', 'Active', 0),
(821, 'Cynthia', 'Consommation: 15500', 'BS00473', '15500', 'tasse et sublimation', '2026-07-30 10:47:21', 'sortie', 'Active', 0),
(822, 'Cynthia', 'Consommation: 15500', 'BS00473', '15500', 'tasse et sublimation', '2026-07-30 10:47:21', 'sortie', 'Active', 0),
(823, 'Cynthia', 'Consommation: 10000', 'BS00474', '10000', 'banque', '2026-07-30 11:51:59', 'sortie', 'Active', 0),
(824, 'Cynthia', 'Consommation: 111000', 'BS00475', '111000', 'lacoste3', '2026-07-30 11:52:21', 'sortie', 'Active', 0),
(825, 'Cynthia', 'Vente', '0214', '10000', 'Vente de :\n- 1.00 pièce DtfA4', '2026-07-30 14:32:04', 'entrée', 'Active', 0),
(826, 'Cynthia', 'Consommation: 20000', 'BS00476', '20000', 'superglue', '2026-07-30 14:38:45', 'sortie', 'Active', 0),
(827, 'Cynthia', 'Consommation: 2000', 'BS00477', '2000', 'autres charges', '2026-07-30 14:39:12', 'sortie', 'Active', 0),
(828, 'Cynthia', 'Consommation: 13500', 'BS00478', '13500', 'représentation dg', '2026-07-30 14:39:35', 'sortie', 'Active', 0),
(829, 'Cynthia', 'Consommation: 10500', 'BS00479', '10500', 'representation dg', '2026-07-30 14:58:33', 'sortie', 'Active', 0),
(830, 'Cynthia', 'Consommation: 10000', 'BS00480', '10000', 'moto', '2026-07-30 14:58:55', 'sortie', 'Active', 0),
(831, 'Cynthia', 'Consommation: 9000', 'BS00481', '9000', 'représentation dg', '2026-07-30 14:59:51', 'sortie', 'Active', 0),
(832, 'Cynthia', 'Paiement Dette', '0213', '10000', '79032914 - Paiement Dette - Facture #0213', '2026-07-30 15:03:17', 'entrée', 'Active', 0),
(833, 'Cynthia', 'Vente', '0215', '45000', 'Vente de :\n- 3.00 m BACHE', '2026-07-30 15:04:00', 'entrée', 'Active', 0),
(834, 'Cynthia', 'Suppression Dépense No.BS00481', 'BS00481', '9000', 'Une dépense de 9000 enregistrée le 2026-07-30 a été supprimée et la caisse a été réajustée', '2026-07-30 17:04:26', 'entrée', 'Active', 0),
(835, 'Cynthia', 'Consommation: 9000', 'BS00481', '9000', 'water', '2026-07-30 17:04:39', 'sortie', 'Active', 0),
(836, 'Cynthia', 'Consommation: 15000', 'BS00482', '15000', 'aide', '2026-07-30 17:05:34', 'sortie', 'Active', 0),
(837, 'Cynthia', 'Consommation: 24000', 'BS00483', '24000', 'representationdg', '2026-07-30 17:06:19', 'sortie', 'Active', 0),
(838, 'Cynthia', 'Consommation: 20000', 'BS00484', '20000', 'autres charges', '2026-07-30 17:06:52', 'sortie', 'Active', 0),
(839, 'Cynthia', 'Consommation: 40000', 'BS00485', '40000', 'facturier', '2026-07-30 17:07:18', 'sortie', 'Active', 0),
(840, 'Cynthia', 'Suppression Dépense No.BS00478', 'BS00478', '13500', 'Une dépense de 13500 enregistrée le 2026-07-30 a été supprimée et la caisse a été réajustée', '2026-07-30 17:10:24', 'entrée', 'Active', 0),
(841, 'Cynthia', 'Suppression Dépense No.BS00473', 'BS00473', '15500', 'Une dépense de 15500 enregistrée le 2026-07-30 a été supprimée et la caisse a été réajustée', '2026-07-30 17:11:36', 'entrée', 'Active', 0),
(842, 'Cynthia', 'Suppression Dépense No.BS00472', 'BS00472', '10500', 'Une dépense de 10500 enregistrée le 2026-07-30 a été supprimée et la caisse a été réajustée', '2026-07-31 07:41:40', 'entrée', 'Active', 0),
(843, 'Cynthia', 'Consommation: 1500', 'BS00486', '1500', 'unite', '2026-07-31 09:14:15', 'sortie', 'Active', 0),
(844, 'Cynthia', 'Consommation: 31000', 'BS00487', '31000', 'ratio', '2026-07-31 09:14:28', 'sortie', 'Active', 0),
(845, 'Cynthia', 'Vente', '0216', '150000', 'Vente de :\n- 150.00 m INVITATIONS', '2026-07-31 15:37:39', 'entrée', 'Active', 0),
(846, 'Cynthia', 'Vente', '0217', '1000', 'Vente de :\n- 1.00 pièce Enveloppe A4', '2026-07-31 15:38:14', 'entrée', 'Active', 0),
(847, 'Cynthia', 'Vente', '0218', '3000', 'Vente de :\n- 1.00 pièce Reliure', '2026-07-31 15:38:46', 'entrée', 'Active', 0),
(848, 'Cynthia', 'Vente', '0219', '50000', 'Vente de :\n- 1.00 pièce Lacoste', '2026-07-31 16:15:15', 'entrée', 'Active', 0),
(849, 'Cynthia', 'Vente', '0220', '1000', 'Vente de :\n- 2.00 pièce Photocopie', '2026-07-31 16:15:54', 'entrée', 'Active', 0),
(850, 'Cynthia', 'Consommation: 10000', 'BS00488', '10000', 'échafaudage bbci', '2026-07-31 16:16:38', 'sortie', 'Active', 0),
(851, 'Cynthia', 'Consommation: 71000', 'BS00489', '71000', 'représentation dg', '2026-07-31 16:17:38', 'sortie', 'Active', 0),
(852, 'Cynthia', 'Consommation: 8000', 'BS00490', '8000', 'transport materiel', '2026-07-31 16:18:10', 'sortie', 'Active', 0),
(853, 'Cynthia', 'Consommation: 1000', 'BS00491', '1000', 'unite', '2026-07-31 16:18:26', 'sortie', 'Active', 0),
(854, 'Cynthia', 'Suppression Dépense No.BS00488', 'BS00488', '10000', 'Une dépense de 10000 enregistrée le 2026-07-31 a été supprimée et la caisse a été réajustée', '2026-07-31 17:07:34', 'entrée', 'Active', 0),
(855, 'Cynthia', 'Consommation: 10000', 'BS00492', '10000', 'échelle bbci', '2026-07-31 17:07:58', 'sortie', 'Active', 0),
(856, 'Cynthia', 'Vente', '0221', '30000', 'Vente de :\n- 30.00 pièce Impression N/B', '2026-08-05 08:38:12', 'entrée', 'Active', 0),
(857, 'Cynthia', 'Vente', '0222', '20000', 'Vente de :\n- 2.00 pièce DtfA4', '2026-08-05 08:38:51', 'entrée', 'Active', 0),
(858, 'Cynthia', 'Vente', '0223', '18000', 'Vente de :\n- 1.00 m AUTOCOLLANT', '2026-08-05 08:39:25', 'entrée', 'Active', 0),
(859, 'Cynthia', 'Consommation: 30000', 'BS00493', '30000', 'transport soir', '2026-08-05 08:40:30', 'sortie', 'Active', 0),
(860, 'Cynthia', 'Consommation: 15000', 'BS00494', '15000', 'paccy', '2026-08-05 08:40:57', 'sortie', 'Active', 0),
(861, 'Cynthia', 'Consommation: 15000', 'BS00495', '15000', 'lavage vitz', '2026-08-05 08:41:18', 'sortie', 'Active', 0),
(862, 'Cynthia', 'Consommation: 10000', 'BS00496', '10000', 'representation dg', '2026-08-05 08:41:50', 'sortie', 'Active', 0),
(863, 'Cynthia', 'Consommation: 2500', 'BS00497', '2500', 'mega', '2026-08-05 08:42:04', 'sortie', 'Active', 0),
(864, 'Cynthia', 'Consommation: 10000', 'BS00498', '10000', 'autres charges', '2026-08-05 08:42:23', 'sortie', 'Active', 0),
(865, 'Cynthia', 'Vente', '0224', '29000', 'Vente de :\n- 29.00 pièce Impression N/B', '2026-08-05 08:46:54', 'entrée', 'Active', 0),
(866, 'Cynthia', 'Vente', '0225', '3000', 'Vente de :\n- 1.00 pièce Reliure', '2026-08-05 08:47:32', 'entrée', 'Active', 0),
(867, 'Cynthia', 'Vente', '0226', '36000', 'Vente de :\n- 2.40 m BACHE', '2026-08-05 08:48:53', 'entrée', 'Active', 0),
(868, 'Cynthia', 'Consommation: 10500', 'BS00499', '10500', 'water', '2026-08-05 08:50:22', 'sortie', 'Active', 0),
(869, 'Cynthia', 'Consommation: 33000', 'BS00500', '33000', 'branding', '2026-08-05 08:51:22', 'sortie', 'Active', 0),
(870, 'Cynthia', 'Consommation: 10000', 'BS00501', '10000', 'mairie', '2026-08-05 08:51:39', 'sortie', 'Active', 0),
(871, 'Cynthia', 'Consommation: 38000', 'BS00502', '38000', 'ratio', '2026-08-05 08:51:54', 'sortie', 'Active', 0),
(872, 'Cynthia', 'Consommation: 23000', 'BS00503', '23000', 'cutteur', '2026-08-05 08:52:07', 'sortie', 'Active', 0),
(873, 'Cynthia', 'Consommation: 20000', 'BS00504', '20000', 'desinfecta', '2026-08-05 08:52:23', 'sortie', 'Active', 0),
(874, 'Cynthia', 'Consommation: 42500', 'BS00505', '42500', 'assurance', '2026-08-05 08:52:37', 'sortie', 'Active', 0),
(875, 'Cynthia', 'Consommation: 23700', 'BS00506', '23700', 'representationn dg', '2026-08-05 08:53:02', 'sortie', 'Active', 0),
(876, 'Cynthia', 'Vente', '0227', '80000', 'Vente de :\n- 1.00 piece Impression sur roll up', '2026-08-05 08:57:09', 'entrée', 'Active', 0),
(877, 'Cynthia', 'Consommation: 50000', 'BS00507', '50000', 'encre cashet bic', '2026-08-05 08:57:36', 'sortie', 'Active', 0),
(878, 'Cynthia', 'Vente', '0228', '138000', 'Vente de :\n- 138.00 pièce Impression N/B', '2026-08-05 09:07:08', 'entrée', 'Active', 0),
(879, 'Cynthia', 'Vente', '0229', '3000', 'Vente de :\n- 1.00 pièce Reliure', '2026-08-05 09:08:23', 'entrée', 'Active', 0),
(880, 'Cynthia', 'Vente', '0230', '15000', 'Vente de :\n- 1.00 pièce Impression sur tshirt', '2026-08-05 09:09:11', 'entrée', 'Active', 0),
(881, 'Cynthia', 'Vente', '0231', '4000', 'Vente de :\n- 4.00 pièce Impression N/B', '2026-08-05 09:10:26', 'entrée', 'Active', 0),
(882, 'Cynthia', 'Vente', '0232', '100000', 'Vente de :\n- 10.00 m AUTOCOLLANT', '2026-08-05 09:16:47', 'entrée', 'Active', 0),
(883, 'Cynthia', 'Dette: 80000', '0232', '-', 'cartas', '2026-08-05 09:16:47', '', 'Active', 0),
(884, 'Cynthia', 'Consommation: 35000', 'BS00508', '35000', 'transport soir', '2026-08-05 10:18:27', 'sortie', 'Active', 0),
(885, 'Cynthia', 'Consommation: 20000', 'BS00509', '20000', 'ratio aide', '2026-08-05 10:19:09', 'sortie', 'Active', 0),
(886, 'Cynthia', 'Consommation: 1500', 'BS00510', '1500', 'mega', '2026-08-05 10:19:22', 'sortie', 'Active', 0),
(887, 'Cynthia', 'Consommation: 3000', 'BS00511', '3000', 'cutting', '2026-08-05 10:19:35', 'sortie', 'Active', 0),
(888, 'Cynthia', 'Consommation: 9000', 'BS00512', '9000', 'water', '2026-08-05 10:19:49', 'sortie', 'Active', 0),
(889, 'Cynthia', 'Consommation: 20000', 'BS00513', '20000', 'reluire', '2026-08-05 10:20:11', 'sortie', 'Active', 0),
(890, 'Cynthia', 'Consommation: 5000', 'BS00514', '5000', 'prêt destin', '2026-08-05 10:20:43', 'sortie', 'Active', 0),
(891, 'Cynthia', 'Consommation: 6000', 'BS00515', '6000', 'pret don', '2026-08-05 10:21:07', 'sortie', 'Active', 0),
(892, 'Cynthia', 'Consommation: 30000', 'BS00516', '30000', 'représentation dg', '2026-08-05 10:21:38', 'sortie', 'Active', 0),
(893, 'Cynthia', 'Consommation: 53000', 'BS00517', '53000', 'frs materiels', '2026-08-05 10:21:54', 'sortie', 'Active', 0),
(894, 'Cynthia', 'Consommation: 31000', 'BS00518', '31000', 'représentation dg', '2026-08-05 10:22:37', 'sortie', 'Active', 0),
(895, 'Cynthia', 'Vente', '0233', '5000', 'Vente de :\n- 1.00 pièce DTF A5', '2026-08-06 16:26:03', 'entrée', 'Active', 0),
(896, 'Cynthia', 'Paiement Dette', '0169', '26000', 'Client de passage - Paiement Dette - Facture #0169', '2026-08-06 16:26:22', 'entrée', 'Active', 0),
(897, 'Cynthia', 'Consommation: 15000', 'BS00519', '15000', 'transport soir', '2026-08-06 16:34:14', 'sortie', 'Active', 0),
(898, 'Cynthia', 'Consommation: 31000', 'BS00520', '31000', 'ratio', '2026-08-06 16:34:31', 'sortie', 'Active', 0),
(899, 'Cynthia', 'Consommation: 25000', 'BS00521', '25000', 'collé', '2026-08-06 16:34:48', 'sortie', 'Active', 0),
(900, 'Cynthia', 'Consommation: 2000', 'BS00522', '2000', 'savon', '2026-08-06 16:35:00', 'sortie', 'Active', 0),
(901, 'Cynthia', 'Consommation: 5000', 'BS00523', '5000', 'serviette', '2026-08-06 16:35:14', 'sortie', 'Active', 0),
(902, 'Cynthia', 'Consommation: 5000', 'BS00524', '5000', 'water', '2026-08-06 16:35:28', 'sortie', 'Active', 0),
(903, 'Cynthia', 'Consommation: 5000', 'BS00525', '5000', 'porte badge', '2026-08-06 16:35:44', 'sortie', 'Active', 0),
(904, 'Cynthia', 'Suppression Dépense No.BS00525', 'BS00525', '5000', 'Une dépense de 5000 enregistrée le 2026-08-06 a été supprimée et la caisse a été réajustée', '2026-08-06 16:35:56', 'entrée', 'Active', 0),
(905, 'Cynthia', 'Consommation: 23000', 'BS00525', '23000', 'porte badge', '2026-08-06 16:36:19', 'sortie', 'Active', 0),
(906, 'Cynthia', 'Consommation: 15000', 'BS00526', '15000', 'moto', '2026-08-06 16:36:33', 'sortie', 'Active', 0),
(907, 'Cynthia', 'Consommation: 6000', 'BS00527', '6000', 'representation dg', '2026-08-06 16:36:59', 'sortie', 'Active', 0),
(908, 'Cynthia', 'Encaissement libre', NULL, '160000', 'alimentation caisse', '2026-08-06 16:37:59', 'entrée', 'Active', 0),
(909, 'Cynthia', 'Approvisionnement: 160000', 'BS00528', '160000', 'achat 5lacostes', '2026-08-06 16:38:23', 'sortie', 'Active', 0),
(910, 'Cynthia', 'Vente', '0234', '22500', 'Vente de :\n- 1.50 m BACHE', '2026-08-10 08:17:17', 'entrée', 'Active', 0),
(911, 'Cynthia', 'Encaissement libre', NULL, '50000', 'prêt ndaha', '2026-08-10 08:18:05', 'entrée', 'Active', 0),
(912, 'Cynthia', 'Vente', '0235', '45000', 'Vente de :\n- 1.00 pièce Tshirt simple', '2026-08-10 08:19:36', 'entrée', 'Active', 0),
(913, 'Cynthia', 'Paiement Dette', '0207', '745000', 'Client de passage - Paiement Dette - Facture #0207', '2026-08-10 08:20:17', 'entrée', 'Active', 0),
(914, 'Cynthia', 'Consommation: 7500', 'BS00529', '7500', 'cordes ', '2026-08-10 08:21:03', 'sortie', 'Active', 0),
(915, 'Cynthia', 'Consommation: 42000', 'BS00530', '42000', 'ratio', '2026-08-10 08:21:21', 'sortie', 'Active', 0),
(916, 'Cynthia', 'Approvisionnement: 20000', 'BS00531', '20000', 'couche 350g', '2026-08-10 08:22:11', 'sortie', 'Active', 0),
(917, 'Cynthia', 'Consommation: 46000', 'BS00532', '46000', 'colle', '2026-08-10 08:22:27', 'sortie', 'Active', 0),
(918, 'Cynthia', 'Consommation: 135000', 'BS00533', '135000', 'autocollant soutraitance', '2026-08-10 08:23:02', 'sortie', 'Active', 0),
(919, 'Cynthia', 'Consommation: 135000', 'BS00533', '135000', 'autocollant soutraitance', '2026-08-10 08:23:02', 'sortie', 'Active', 0),
(920, 'Cynthia', 'Suppression Dépense No.BS00533', 'BS00533', '135000', 'Une dépense de 135000 enregistrée le 2026-08-10 a été supprimée et la caisse a été réajustée', '2026-08-10 08:23:20', 'entrée', 'Active', 0),
(921, 'Cynthia', 'Consommation: 97000', 'BS00534', '97000', 'contrôle technique', '2026-08-10 08:24:36', 'sortie', 'Active', 0),
(922, 'Cynthia', 'Consommation: 15000', 'BS00535', '15000', 'cordes', '2026-08-10 08:24:50', 'sortie', 'Active', 0),
(923, 'Cynthia', 'Consommation: 10000', 'BS00536', '10000', 'frais de terrain', '2026-08-10 08:25:14', 'sortie', 'Active', 0),
(924, 'Cynthia', 'Consommation: 3000', 'BS00537', '3000', 'city', '2026-08-10 08:25:28', 'sortie', 'Active', 0),
(925, 'Cynthia', 'Consommation: 40000', 'BS00538', '40000', 'remboursement ndaha', '2026-08-10 08:25:58', 'sortie', 'Active', 0),
(926, 'Cynthia', 'Consommation: 12000', 'BS00539', '12000', 'transport materiels et entrertien', '2026-08-10 08:26:31', 'sortie', 'Active', 0),
(927, 'Cynthia', 'Suppression Dépense No.BS00538', 'BS00538', '40000', 'Une dépense de 40000 enregistrée le 2026-08-10 a été supprimée et la caisse a été réajustée', '2026-08-10 08:29:34', 'entrée', 'Active', 0),
(928, 'Cynthia', 'Consommation: 50000', 'BS00540', '50000', 'rembourssement ndaha', '2026-08-10 08:29:58', 'sortie', 'Active', 0),
(929, 'Cynthia', 'Consommation: 16000', 'BS00541', '16000', 'brosse', '2026-08-10 08:32:28', 'sortie', 'Active', 0),
(930, 'Cynthia', 'Consommation: 20500', 'BS00542', '20500', 'representation dg', '2026-08-10 08:32:50', 'sortie', 'Active', 0),
(931, 'Cynthia', 'Consommation: 3000', 'BS00543', '3000', 'unité ', '2026-08-10 08:33:15', 'sortie', 'Active', 0),
(932, 'Cynthia', 'Consommation: 2000', 'BS00544', '2000', 'water', '2026-08-10 08:33:32', 'sortie', 'Active', 0),
(933, 'Cynthia', 'Consommation: 10000', 'BS00545', '10000', 'aide', '2026-08-10 08:34:02', 'sortie', 'Active', 0),
(934, 'Cynthia', 'Consommation: 60000', 'BS00546', '60000', 'transport soir', '2026-08-10 08:34:35', 'sortie', 'Active', 0),
(935, 'Cynthia', 'Consommation: 5000', 'BS00547', '5000', 'city', '2026-08-10 08:34:49', 'sortie', 'Active', 0),
(936, 'Cynthia', 'Vente', '0236', '108000', 'Vente de :\n- 7.20 m BACHE', '2026-08-10 08:36:58', 'entrée', 'Active', 0),
(937, 'Cynthia', 'Vente', '0237', '10000', 'Vente de :\n- 1.00 pièce DtfA4', '2026-08-10 08:39:05', 'entrée', 'Active', 0),
(938, 'Cynthia', 'Vente', '0238', '50000', 'Vente de :\n- 1.00 pièce Lacoste', '2026-08-10 08:39:49', 'entrée', 'Active', 0),
(939, 'Cynthia', 'Vente', '0239', '260460', 'Vente de :\n- 14.47 m AUTOCOLLANT', '2026-08-10 08:46:27', 'entrée', 'Active', 0),
(940, 'Cynthia', 'Vente', '0240', '135000', 'Vente de :\n- 7.50 m AUTOCOLLANT', '2026-08-10 08:47:36', 'entrée', 'Active', 0),
(941, 'Cynthia', 'Vente', '0241', '826000', 'Vente de :\n- 14.00 pièce Tshirt simple', '2026-08-10 09:20:01', 'entrée', 'Active', 0),
(942, 'Isabelle', 'Mis-à-jour Stock', '', NULL, 'Autocollant18*10 (60 m)', '2026-08-10 09:25:04', NULL, 'Active', 0),
(943, 'Cynthia', 'Vente', '0242', '49560', 'Vente de :\n- 60.00 m Autocollant18*10', '2026-08-10 09:29:19', 'entrée', 'Active', 0),
(944, 'Cynthia', 'Vente', '0243', '10000', 'Vente de :\n- 1.00 pièce DtfA4', '2026-08-10 09:29:55', 'entrée', 'Active', 0),
(945, 'Cynthia', 'Vente', '0244', '108000', 'Vente de :\n- 27.00 m Certificat', '2026-08-10 09:30:36', 'entrée', 'Active', 0),
(946, 'Cynthia', 'Vente', '0245', '10000', 'Vente de :\n- 1.00 pièce Autocollant A4', '2026-08-10 09:31:11', 'entrée', 'Active', 0),
(947, 'Cynthia', 'Paiement Dette', '0232', '80000', 'cartas - Paiement Dette - Facture #0232', '2026-08-10 09:31:45', 'entrée', 'Active', 0),
(948, 'Cynthia', 'Vente', '0246', '50000', 'Vente de :\n- 10.00 m Fixation contravision', '2026-08-10 09:34:57', 'entrée', 'Active', 0),
(949, 'Cynthia', 'Vente', '0247', '50000', 'Vente de :\n- 1.00 pièce Design', '2026-08-10 09:35:32', 'entrée', 'Active', 0),
(950, 'Cynthia', 'Encaissement libre', NULL, '400', 'tva pour10m ', '2026-08-10 09:38:06', 'entrée', 'Active', 0),
(951, 'Cynthia', 'Consommation: 50000', 'BS00548', '50000', 'renouveau', '2026-08-10 09:38:31', 'sortie', 'Active', 0),
(952, 'Cynthia', 'Consommation: 31000', 'BS00549', '31000', 'ratio', '2026-08-10 09:38:44', 'sortie', 'Active', 0),
(953, 'Cynthia', 'Consommation: 10000', 'BS00550', '10000', 'water', '2026-08-10 09:38:59', 'sortie', 'Active', 0),
(954, 'Cynthia', 'Consommation: 37000', 'BS00551', '37000', 'lacoste', '2026-08-10 09:39:14', 'sortie', 'Active', 0),
(955, 'Cynthia', 'Consommation: 25000', 'BS00552', '25000', 'tshirt simple', '2026-08-10 09:39:48', 'sortie', 'Active', 0),
(956, 'Cynthia', 'Consommation: 25000', 'BS00553', '25000', 'porte clé ', '2026-08-10 09:40:29', 'sortie', 'Active', 0),
(957, 'Cynthia', 'Consommation: 25000', 'BS00553', '25000', 'porte clé ', '2026-08-10 09:40:29', 'sortie', 'Active', 0),
(958, 'Cynthia', 'Suppression Dépense No.BS00553', 'BS00553', '25000', 'Une dépense de 25000 enregistrée le 2026-08-10 a été supprimée et la caisse a été réajustée', '2026-08-10 09:40:39', 'entrée', 'Active', 0),
(959, 'Cynthia', 'Consommation: 40000', 'BS00554', '40000', 'finition livre 4', '2026-08-10 09:44:35', 'sortie', 'Active', 0),
(960, 'Cynthia', 'Consommation: 42000', 'BS00555', '42000', 'moto', '2026-08-10 09:44:56', 'sortie', 'Active', 0),
(961, 'Cynthia', 'Consommation: 15000', 'BS00556', '15000', 'superglue', '2026-08-10 09:45:31', 'sortie', 'Active', 0),
(962, 'Cynthia', 'Approvisionnement: 175000', 'BS00557', '175000', 'achat 7 tshirt', '2026-08-10 09:46:00', 'sortie', 'Active', 0),
(963, 'Cynthia', 'Consommation: 435000', 'BS00558', '435000', 'frs matériels ', '2026-08-10 09:46:27', 'sortie', 'Active', 0),
(964, 'Cynthia', 'Consommation: 100000', 'BS00559', '100000', 'représentation bic', '2026-08-10 09:47:39', 'sortie', 'Active', 0),
(965, 'Cynthia', 'Consommation: 100000', 'BS00560', '100000', 'cash power', '2026-08-10 09:48:01', 'sortie', 'Active', 0),
(966, 'Cynthia', 'Consommation: 8000', 'BS00561', '8000', 'adaptateur', '2026-08-10 09:48:24', 'sortie', 'Active', 0),
(967, 'Cynthia', 'Consommation: 72500', 'BS00562', '72500', 'representation dg', '2026-08-10 09:48:45', 'sortie', 'Active', 0),
(968, 'Cynthia', 'Consommation: 2000', 'BS00563', '2000', 'unité ', '2026-08-10 09:48:59', 'sortie', 'Active', 0),
(969, 'Cynthia', 'Consommation: 2000', 'BS00563', '2000', 'unité ', '2026-08-10 09:48:59', 'sortie', 'Active', 0),
(970, 'Cynthia', 'Suppression Dépense No.BS00563', 'BS00563', '2000', 'Une dépense de 2000 enregistrée le 2026-08-10 a été supprimée et la caisse a été réajustée', '2026-08-10 09:49:16', 'entrée', 'Active', 0),
(971, 'Cynthia', 'Vente', '0248', '15000', 'Vente de :\n- 15.00 pièce Impression N/B', '2026-08-10 09:51:07', 'entrée', 'Active', 0),
(972, 'Cynthia', 'Vente', '0249', '360000', 'Vente de :\n- 3.00 piece Livre plus finition', '2026-08-10 09:52:46', 'entrée', 'Active', 0),
(973, 'Cynthia', 'Vente', '0250', '30000', 'Vente de :\n- 2.00 m BACHE', '2026-08-10 09:53:52', 'entrée', 'Active', 0),
(974, 'Cynthia', 'Vente', '0251', '30000', 'Vente de :\n- 30.00 pièce Impression N/B', '2026-08-10 09:54:47', 'entrée', 'Supprimée', 0),
(975, 'Cynthia', 'Annulation Vente', '0251', NULL, '30000', '2026-08-10 09:55:34', NULL, 'Active', 0),
(976, 'Cynthia', 'Vente', '0252', '20000', 'Vente de :\n- 20.00 pièce Impression N/B', '2026-08-10 09:56:05', 'entrée', 'Active', 0),
(977, 'Cynthia', 'Consommation: 30000', 'BS00564', '30000', 'transport soir', '2026-08-10 09:56:37', 'sortie', 'Active', 0),
(978, 'Cynthia', 'Approvisionnement: 189000', 'BS00565', '189000', 'tshirt 7', '2026-08-10 09:57:58', 'sortie', 'Active', 0),
(979, 'Cynthia', 'Consommation: 250000', 'BS00566', '250000', 'salaire', '2026-08-10 09:58:15', 'sortie', 'Active', 0),
(980, 'Cynthia', 'Consommation: 100000', 'BS00567', '100000', 'assistance sociale', '2026-08-10 09:58:50', 'sortie', 'Active', 0),
(981, 'Cynthia', 'Consommation: 5000', 'BS00568', '5000', 'impression', '2026-08-10 09:59:07', 'sortie', 'Active', 0),
(982, 'Cynthia', 'Consommation: 20000', 'BS00569', '20000', 'aide', '2026-08-10 09:59:20', 'sortie', 'Active', 0),
(983, 'Cynthia', 'Consommation: 5000', 'BS00570', '5000', 'isolant', '2026-08-10 10:00:24', 'sortie', 'Active', 0),
(984, 'Cynthia', 'Suppression Dépense No.BS00570', 'BS00570', '5000', 'Une dépense de 5000 enregistrée le 2026-08-10 a été supprimée et la caisse a été réajustée', '2026-08-10 10:00:52', 'entrée', 'Active', 0),
(985, 'Cynthia', 'Consommation: 10000', 'BS00570', '10000', 'isolant', '2026-08-10 10:01:10', 'sortie', 'Active', 0),
(986, 'Cynthia', 'Consommation: 10000', 'BS00571', '10000', 'représentation dg', '2026-08-10 10:01:30', 'sortie', 'Active', 0),
(987, 'Cynthia', 'Consommation: 15000', 'BS00572', '15000', 'surcone ', '2026-08-10 10:04:04', 'sortie', 'Active', 0),
(988, 'Cynthia', 'Consommation: 5000', 'BS00573', '5000', 'déplacement', '2026-08-10 10:05:36', 'sortie', 'Active', 0),
(989, 'Cynthia', 'Consommation: 31000', 'BS00574', '31000', 'ratio', '2026-08-10 10:06:18', 'sortie', 'Active', 0),
(990, 'Cynthia', 'Consommation: 15000', 'BS00575', '15000', 'colle', '2026-08-10 10:06:34', 'sortie', 'Active', 0),
(991, 'Cynthia', 'Vente', '0253', '10000', 'Vente de :\n- 1.00 pièce DtfA4', '2026-08-10 10:07:15', 'entrée', 'Active', 0),
(992, 'Cynthia', 'Suppression Dépense No.BS00572', 'BS00572', '15000', 'Une dépense de 15000 enregistrée le 2026-08-10 a été supprimée et la caisse a été réajustée', '2026-08-10 10:07:53', 'entrée', 'Active', 0),
(993, 'Cynthia', 'Consommation: 15000', 'BS00576', '15000', 'sylicone', '2026-08-10 10:08:39', 'sortie', 'Active', 0),
(994, 'Cynthia', 'Vente', '0254', '106400', 'Vente de :\n- 3.04 pièce Contravision1m', '2026-08-10 11:39:49', 'entrée', 'Active', 0),
(995, 'Cynthia', 'Encaissement libre', NULL, '280', 'contravision de 3.048', '2026-08-10 11:40:45', 'entrée', 'Active', 0),
(996, 'Cynthia', 'Consommation: 5000', 'BS00577', '5000', 'representation dg', '2026-08-10 13:08:49', 'sortie', 'Active', 0),
(997, 'Cynthia', 'Vente', '0255', '10000', 'Vente de :\n- 1.00 pièce DtfA4', '2026-08-11 15:29:37', 'entrée', 'Active', 0),
(998, 'Cynthia', 'Vente', '0256', '65000', 'Vente de :\n- 5.00 pièce IMPRESSION SUR GILET', '2026-08-11 15:30:23', 'entrée', 'Active', 0),
(999, 'Cynthia', 'Vente', '0257', '396000', 'Vente de :\n- 26.40 m BACHE', '2026-08-11 15:31:15', 'entrée', 'Active', 0),
(1000, 'Cynthia', 'Vente', '0258', '78000', 'Vente de :\n- 78.00 pièce Impression N/B', '2026-08-11 15:32:18', 'entrée', 'Active', 0),
(1001, 'Cynthia', 'Consommation: 56000', 'BS00578', '56000', 'représentation dg', '2026-08-11 15:35:45', 'sortie', 'Active', 0),
(1002, 'Cynthia', 'Consommation: 207500', 'BS00579', '207500', 'frs cash power', '2026-08-11 15:36:12', 'sortie', 'Active', 0),
(1003, 'Cynthia', 'Consommation: 327840', 'BS00580', '327840', 'frs matériels ', '2026-08-11 15:36:37', 'sortie', 'Active', 0),
(1004, 'Cynthia', 'Consommation: 6000', 'BS00581', '6000', 'autres charges', '2026-08-11 15:36:56', 'sortie', 'Active', 0),
(1005, 'Cynthia', 'Consommation: 10000', 'BS00582', '10000', 'ratio', '2026-08-11 15:37:09', 'sortie', 'Active', 0),
(1006, 'Cynthia', 'Vente', '0259', '50000', 'Vente de :\n- 1.00 pièce Design', '2026-08-11 15:39:06', 'entrée', 'Active', 0),
(1007, 'Cynthia', 'Vente', '0260', '45000', 'Vente de :\n- 1.00 pièce Lacoste', '2026-08-11 15:39:39', 'entrée', 'Active', 0),
(1008, 'Cynthia', 'Consommation: 40000', 'BS00583', '40000', 'transport soir', '2026-08-11 15:40:19', 'sortie', 'Active', 0),
(1009, 'Cynthia', 'Approvisionnement: 222000', 'BS00584', '222000', 'achat 6tshirt', '2026-08-11 15:42:03', 'sortie', 'Active', 0),
(1010, 'Cynthia', 'Consommation: 33000', 'BS00585', '33000', 'ratio', '2026-08-11 15:42:24', 'sortie', 'Active', 0),
(1011, 'Cynthia', 'Consommation: 5000', 'BS00586', '5000', 'serviette', '2026-08-11 15:42:44', 'sortie', 'Active', 0),
(1012, 'Cynthia', 'Consommation: 1000', 'BS00587', '1000', 'pille crayon', '2026-08-11 15:43:03', 'sortie', 'Active', 0),
(1013, 'Cynthia', 'Consommation: 109000', 'BS00588', '109000', 'poudre dtf', '2026-08-11 15:43:23', 'sortie', 'Active', 0),
(1014, 'Cynthia', 'Consommation: 50000', 'BS00589', '50000', 'sérigraphie ', '2026-08-11 15:43:47', 'sortie', 'Active', 0),
(1015, 'Cynthia', 'Consommation: 50000', 'BS00590', '50000', 'transport matériels', '2026-08-11 15:44:23', 'sortie', 'Active', 0),
(1016, 'Cynthia', 'Suppression Dépense No.BS00590', 'BS00590', '50000', 'Une dépense de 50000 enregistrée le 2026-08-11 a été supprimée et la caisse a été réajustée', '2026-08-11 15:44:36', 'entrée', 'Active', 0),
(1017, 'Cynthia', 'Consommation: 22500', 'BS00590', '22500', 'transport materiel', '2026-08-11 15:45:28', 'sortie', 'Active', 0),
(1018, 'Cynthia', 'Consommation: 20000', 'BS00591', '20000', 'représentation dg', '2026-08-11 15:46:00', 'sortie', 'Active', 0),
(1019, 'Cynthia', 'Approvisionnement: 10500', 'BS00592', '10500', 'autres charges', '2026-08-11 15:46:25', 'sortie', 'Active', 0),
(1020, 'Cynthia', 'Approvisionnement: 27000', 'BS00593', '27000', 't-shirt ', '2026-08-11 15:47:17', 'sortie', 'Active', 0),
(1021, 'Cynthia', 'Consommation: 60000', 'BS00594', '60000', 'représentation dg', '2026-08-11 15:48:16', 'sortie', 'Active', 0),
(1022, 'Cynthia', 'Consommation: 5500', 'BS00595', '5500', 'représentation dg', '2026-08-11 15:49:58', 'sortie', 'Active', 0),
(1023, 'Cynthia', 'Consommation: 3000', 'BS00596', '3000', 'autre charges', '2026-08-18 11:20:13', 'sortie', 'Active', 0),
(1024, 'Cynthia', 'Vente', '0261', '65000', 'Vente de :\n- 65.00 pièce Impression N/B', '2026-08-18 11:21:30', 'entrée', 'Active', 0),
(1025, 'Cynthia', 'Vente', '0262', '2000', 'Vente de :\n- 4.00 pièce Anneau', '2026-08-18 11:22:00', 'entrée', 'Active', 0),
(1026, 'Cynthia', 'Vente', '0263', '360000', 'Vente de :\n- 12.00 pièce Tshirt simple', '2026-08-18 11:26:22', 'entrée', 'Active', 0),
(1027, 'Cynthia', 'Vente', '0264', '1000', 'Vente de :\n- 1.00 pièce Enveloppe A4', '2026-08-18 11:26:59', 'entrée', 'Active', 0),
(1028, 'Isabelle', 'Mis-à-jour Stock', '', NULL, 'Autocollant18*10 (75 m)', '2026-08-18 11:30:20', NULL, 'Active', 0),
(1029, 'Cynthia', 'Vente', '0265', '59470', 'Vente de :\n- 72.00 m Autocollant18*10', '2026-08-18 11:33:02', 'entrée', 'Active', 0),
(1030, 'Cynthia', 'Dette: 2', '0265', '-', 'Client de passage', '2026-08-18 11:33:02', '', 'Active', 0),
(1031, 'Cynthia', 'Paiement Dette', '0265', '2', 'Client de passage - Paiement Dette - Facture #0265', '2026-08-18 11:33:23', 'entrée', 'Active', 0),
(1032, 'Cynthia', 'Encaissement libre', NULL, '28', 'frais disponible', '2026-08-18 11:34:07', 'entrée', 'Active', 0),
(1033, 'Cynthia', 'Vente', '0266', '18000', 'Vente de :\n- 1.20 m BACHE', '2026-08-18 11:34:42', 'entrée', 'Active', 0),
(1034, 'Cynthia', 'Vente', '0267', '2000', 'Vente de :\n- 4.00 pièce Anneau', '2026-08-18 11:35:07', 'entrée', 'Active', 0),
(1035, 'Cynthia', 'Consommation: 20000', 'BS00597', '20000', 'transport soir', '2026-08-18 11:36:31', 'sortie', 'Active', 0),
(1036, 'Cynthia', 'Consommation: 65000', 'BS00598', '65000', 'transport', '2026-08-18 11:36:53', 'sortie', 'Active', 0),
(1037, 'Cynthia', 'Consommation: 37000', 'BS00599', '37000', 'ratio', '2026-08-18 11:37:39', 'sortie', 'Active', 0),
(1038, 'Cynthia', 'Consommation: 13000', 'BS00600', '13000', 'autres charges', '2026-08-18 11:38:16', 'sortie', 'Active', 0),
(1039, 'Cynthia', 'Consommation: 12000', 'BS00601', '12000', 'transport materiel', '2026-08-18 11:38:47', 'sortie', 'Active', 0),
(1040, 'Cynthia', 'Consommation: 7000', 'BS00602', '7000', 'aide', '2026-08-18 11:38:59', 'sortie', 'Active', 0),
(1041, 'Cynthia', 'Consommation: 10000', 'BS00603', '10000', 'serigraphie', '2026-08-18 11:39:23', 'sortie', 'Active', 0),
(1042, 'Cynthia', 'Approvisionnement: 280000', 'BS00604', '280000', 'achat t-shirt 12', '2026-08-18 11:40:12', 'sortie', 'Active', 0),
(1043, 'Cynthia', 'Consommation: 62500', 'BS00605', '62500', 'représentation dg', '2026-08-18 11:40:37', 'sortie', 'Active', 0),
(1044, 'Cynthia', 'Consommation: 3200', 'BS00606', '3200', 'frais de retrait', '2026-08-18 11:59:29', 'sortie', 'Active', 0),
(1045, 'Cynthia', 'Vente', '0268', '75900', 'Vente de :\n- 5.06 m BACHE', '2026-08-18 12:03:20', 'entrée', 'Active', 0),
(1046, 'Cynthia', 'Encaissement libre', NULL, '100', 'autres frais disponibles', '2026-08-18 12:04:08', 'entrée', 'Active', 0),
(1047, 'Cynthia', 'Vente', '0269', '2000', 'Vente de :\n- 2.00 pièce Impression N/B', '2026-08-18 12:05:50', 'entrée', 'Active', 0),
(1048, 'Cynthia', 'Vente', '0270', '9000', 'Vente de :\n- 3.00 pièce Reliure', '2026-08-18 12:06:41', 'entrée', 'Active', 0),
(1049, 'Cynthia', 'Vente', '0271', '50000', 'Vente de :\n- 11.20 m BACHE', '2026-08-18 12:10:44', 'entrée', 'Active', 0),
(1050, 'Cynthia', 'Dette: 118000', '0271', '-', 'hamza amababa', '2026-08-18 12:10:44', '', 'Active', 0),
(1051, 'Cynthia', 'Consommation: 35500', 'BS00607', '35500', 'gullote fiche mol', '2026-08-18 12:11:30', 'sortie', 'Active', 0),
(1052, 'Cynthia', 'Suppression Dépense No.BS00607', 'BS00607', '35500', 'Une dépense de 35500 enregistrée le 2026-08-18 a été supprimée et la caisse a été réajustée', '2026-08-18 12:11:40', 'entrée', 'Active', 0),
(1053, 'Cynthia', 'Consommation: 35500', 'BS00607', '35500', 'rario', '2026-08-18 12:11:56', 'sortie', 'Active', 0),
(1054, 'Cynthia', 'Consommation: 24000', 'BS00608', '24000', 'gullotte fiche mol', '2026-08-18 12:12:33', 'sortie', 'Active', 0),
(1055, 'Cynthia', 'Consommation: 20000', 'BS00609', '20000', 'serigraphie', '2026-08-18 12:13:05', 'sortie', 'Active', 0),
(1056, 'Cynthia', 'Consommation: 5000', 'BS00610', '5000', 'sac', '2026-08-18 12:13:16', 'sortie', 'Active', 0),
(1057, 'Cynthia', 'Consommation: 6000', 'BS00611', '6000', 'matériel ,entretien', '2026-08-18 12:13:53', 'sortie', 'Active', 0),
(1058, 'Cynthia', 'Consommation: 5000', 'BS00612', '5000', 'transport matin', '2026-08-18 12:14:32', 'sortie', 'Active', 0),
(1059, 'Cynthia', 'Consommation: 121000', 'BS00613', '121000', 'representation dg', '2026-08-18 12:14:50', 'sortie', 'Active', 0),
(1060, 'Cynthia', 'Consommation: 31000', 'BS00614', '31000', 'ratio', '2026-08-18 13:11:08', 'sortie', 'Active', 0),
(1061, 'Cynthia', 'Consommation: 70000', 'BS00615', '70000', 'gourde', '2026-08-18 13:11:22', 'sortie', 'Active', 0),
(1062, 'Cynthia', 'Approvisionnement: 2500', 'BS00616', '2500', 'représentation dg', '2026-08-18 13:11:43', 'sortie', 'Active', 0),
(1063, 'Cynthia', 'Vente', '0272', '10000', 'Vente de :\n- 1.00 pièce DtfA4', '2026-08-18 13:12:43', 'entrée', 'Active', 0),
(1064, 'Cynthia', 'Vente', '0273', '22500', 'Vente de :\n- 1.25 m BACHE', '2026-08-18 13:13:33', 'entrée', 'Active', 0),
(1065, 'Cynthia', 'Vente', '0274', '2000', 'Vente de :\n- 4.00 pièce Anneau', '2026-08-18 13:13:55', 'entrée', 'Active', 0),
(1066, 'Cynthia', 'Vente', '0275', '100000', 'Vente de :\n- 1.00 pièce Gouldet', '2026-08-18 13:14:27', 'entrée', 'Active', 0),
(1067, 'Cynthia', 'Vente', '0276', '36000', 'Vente de :\n- 18.00 pièce Photocopie', '2026-08-18 13:15:12', 'entrée', 'Active', 0),
(1068, 'Cynthia', 'Vente', '0277', '100000', 'Vente de :\n- 1.00 pièce FOAMBODY A4', '2026-08-18 13:15:59', 'entrée', 'Active', 0),
(1069, 'Cynthia', 'Vente', '0278', '20000', 'Vente de :\n- 1.00 pièce Impression sur tshirt', '2026-08-18 13:20:04', 'entrée', 'Active', 0),
(1070, 'Cynthia', 'Consommation: 50000', 'BS00617', '50000', 'finition livre enabel', '2026-08-18 13:20:42', 'sortie', 'Active', 0),
(1071, 'Cynthia', 'Consommation: 20000', 'BS00618', '20000', 'entretien', '2026-08-18 13:21:00', 'sortie', 'Active', 0),
(1072, 'Cynthia', 'Consommation: 100000', 'BS00619', '100000', 'frais des honoraires', '2026-08-18 13:21:23', 'sortie', 'Active', 0),
(1073, 'Cynthia', 'Vente', '0279', '25000', 'Vente de :\n- 1.00 rouleau Tasse', '2026-08-18 13:25:52', 'entrée', 'Active', 0),
(1074, 'Cynthia', 'Vente', '0280', '15000', 'Vente de :\n- 1.00 m BACHE', '2026-08-18 13:26:18', 'entrée', 'Active', 0),
(1075, 'Cynthia', 'Vente', '0281', '27000', 'Vente de :\n- 1.80 m BACHE', '2026-08-18 13:26:56', 'entrée', 'Active', 0),
(1076, 'Cynthia', 'Vente', '0282', '48000', 'Vente de :\n- 3.20 m BACHE', '2026-08-18 13:27:40', 'entrée', 'Active', 0),
(1077, 'Cynthia', 'Vente', '0283', '90000', 'Vente de :\n- 5.00 m BACHE', '2026-08-18 13:29:31', 'entrée', 'Active', 0),
(1078, 'Cynthia', 'Vente', '0284', '10000', 'Vente de :\n- 1.00 pièce Design', '2026-08-18 13:30:10', 'entrée', 'Active', 0),
(1079, 'Cynthia', 'Vente', '0285', '40000', 'Vente de :\n- 4.00 pièce Lacoste', '2026-08-18 13:31:10', 'entrée', 'Active', 0),
(1080, 'Cynthia', 'Dette: 160000', '0285', '-', 'Client de passage', '2026-08-18 13:31:10', '', 'Active', 0),
(1081, 'Cynthia', 'Consommation: 35000', 'BS00620', '35000', 'transport', '2026-08-18 13:31:50', 'sortie', 'Active', 0),
(1082, 'Cynthia', 'Consommation: 100000', 'BS00621', '100000', 'cash power', '2026-08-18 13:32:31', 'sortie', 'Active', 0),
(1083, 'Cynthia', 'Paiement Dette', '0285', '160000', 'Client de passage - Paiement Dette - Facture #0285', '2026-08-18 13:33:55', 'entrée', 'Active', 0),
(1084, 'Cynthia', 'Vente', '0286', '10000', 'Vente de :\n- 1.00 pièce Design', '2026-08-18 13:35:26', 'entrée', 'Active', 0),
(1085, 'Cynthia', 'Vente', '0287', '120000', 'Vente de :\n- 4.00 pièce Tshirt simple', '2026-08-18 13:38:13', 'entrée', 'Active', 0),
(1086, 'Cynthia', 'Vente', '0288', '10000', 'Vente de :\n- 1.00 m BACHE', '2026-08-18 13:38:44', 'entrée', 'Active', 0),
(1087, 'Cynthia', 'Vente', '0289', '50000', 'Vente de :\n- 2.00 rouleau Tasse', '2026-08-18 13:39:09', 'entrée', 'Active', 0),
(1088, 'Cynthia', 'Vente', '0290', '200000', 'Vente de :\n- 2.00 pièce FOAMBODY A4', '2026-08-18 13:39:50', 'entrée', 'Active', 0),
(1089, 'Cynthia', 'Vente', '0291', '30000', 'Vente de :\n- 1.00 piece Cashet simple', '2026-08-18 13:40:27', 'entrée', 'Active', 0),
(1090, 'Cynthia', 'Consommation: 10000', 'BS00622', '10000', 'transport', '2026-08-18 13:41:13', 'sortie', 'Active', 0),
(1091, 'Cynthia', 'Consommation: 41000', 'BS00623', '41000', 'ratio', '2026-08-18 13:41:31', 'sortie', 'Active', 0),
(1092, 'Cynthia', 'Consommation: 20000', 'BS00624', '20000', 'clous', '2026-08-18 13:41:47', 'sortie', 'Active', 0),
(1093, 'Cynthia', 'Consommation: 25000', 'BS00625', '25000', 'facturier', '2026-08-18 13:42:06', 'sortie', 'Active', 0),
(1094, 'Cynthia', 'Consommation: 10000', 'BS00626', '10000', 'autres charges', '2026-08-18 13:42:26', 'sortie', 'Active', 0),
(1095, 'Cynthia', 'Consommation: 92000', 'BS00627', '92000', 'achat t-shirt ', '2026-08-18 13:42:52', 'sortie', 'Active', 0),
(1096, 'Cynthia', 'Consommation: 2000', 'BS00628', '2000', 'transport materiel', '2026-08-18 13:43:17', 'sortie', 'Active', 0),
(1097, 'Cynthia', 'Consommation: 20000', 'BS00629', '20000', 'cashet', '2026-08-18 13:43:32', 'sortie', 'Active', 0),
(1098, 'Cynthia', 'Consommation: 62000', 'BS00630', '62000', 'representation dg', '2026-08-18 13:43:55', 'sortie', 'Active', 0);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `on_exp`
--

CREATE TABLE `on_exp` (
  `id` int(11) NOT NULL,
  `doc_number` varchar(100) NOT NULL,
  `medicine_name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `expire_date` varchar(255) NOT NULL,
  `qty` bigint(11) NOT NULL,
  `type` varchar(10) NOT NULL,
  `cost` bigint(11) NOT NULL,
  `amount` bigint(11) NOT NULL,
  `profit_amount` bigint(11) NOT NULL,
  `date` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `on_hold`
--

CREATE TABLE `on_hold` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(100) NOT NULL,
  `medicine_name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `expire_date` date NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  `type` varchar(10) NOT NULL,
  `actual_price` decimal(10,0) NOT NULL,
  `cost` decimal(10,0) NOT NULL,
  `amount` decimal(10,0) NOT NULL,
  `totcost` decimal(10,0) GENERATED ALWAYS AS (`actual_price` * `qty`) STORED,
  `totprice` decimal(10,0) GENERATED ALWAYS AS (`cost` * `qty`) STORED,
  `profit_amount` decimal(10,0) GENERATED ALWAYS AS (`totprice` - `totcost`) STORED,
  `discount` decimal(10,0) NOT NULL,
  `tot_after_discount` decimal(10,0) NOT NULL,
  `guard_total` decimal(10,0) NOT NULL,
  `origin` tinyint(1) NOT NULL DEFAULT 0,
  `date` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `on_order`
--

CREATE TABLE `on_order` (
  `id` int(11) NOT NULL,
  `doc_number` varchar(100) NOT NULL,
  `medicine_name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `expire_date` varchar(255) NOT NULL,
  `qty` bigint(11) NOT NULL,
  `type` varchar(10) NOT NULL,
  `cost` bigint(11) NOT NULL,
  `amount` bigint(11) NOT NULL,
  `profit_amount` bigint(11) NOT NULL,
  `date` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `on_quot`
--

CREATE TABLE `on_quot` (
  `id` int(11) NOT NULL,
  `doc_number` varchar(100) NOT NULL,
  `medicine_name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `expire_date` date NOT NULL,
  `qty` bigint(11) NOT NULL,
  `type` varchar(10) NOT NULL,
  `cost` bigint(11) NOT NULL,
  `amount` bigint(11) NOT NULL,
  `profit_amount` bigint(11) NOT NULL,
  `date` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `partners`
--

CREATE TABLE `partners` (
  `id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `abbr` varchar(25) DEFAULT NULL,
  `category` enum('client','fournisseur') DEFAULT NULL,
  `address` varchar(1000) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `RC` varchar(100) NOT NULL,
  `NIF` varchar(255) NOT NULL,
  `profil` varchar(100) DEFAULT NULL,
  `debt_limit` bigint(10) NOT NULL DEFAULT 0,
  `debt` bigint(10) NOT NULL DEFAULT 0,
  `annif` varchar(10) DEFAULT NULL,
  `status_client` varchar(25) DEFAULT 'Nouveau',
  `total_achats` bigint(15) DEFAULT 0,
  `frequence_achats` int(10) DEFAULT 0,
  `dernier_achat` date NOT NULL DEFAULT current_timestamp(),
  `rating` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `partners`
--

INSERT INTO `partners` (`id`, `name`, `abbr`, `category`, `address`, `phone`, `email`, `RC`, `NIF`, `profil`, `debt_limit`, `debt`, `annif`, `status_client`, `total_achats`, `frequence_achats`, `dernier_achat`, `rating`) VALUES
(123, 'Walk-in-customer', NULL, 'client', 'Bujumbura', '69xxxxxx', 'nxxx@gmail.com', '00xxxx/xx', '4000xxxxxxxx', 'admin-icn.png', 0, -86750, '01-03', 'VIP', 229374652, 1308, '2026-06-09', NULL),
(130, 'Alexis', NULL, 'client', 'Isare', '69788400', 'NLTS', '005655/24', '4002645879', 'prod_6a2d7ec8cbd16.jpg', 0, 272210, '14-06', 'Nouveau', 3391270, 7, '2026-06-17', NULL),
(131, 'WHH', NULL, 'client', 'BUJA ', '-', '-', '-', '-', NULL, 0, 0, NULL, 'Nouveau', 0, 0, '2026-05-07', NULL),
(132, 'cfr don', NULL, NULL, 'Non fourni', '68436351', '', '', '', NULL, 0, 17500, NULL, 'Nouveau', 70000, 1, '2026-05-11', NULL),
(133, 'JUSTIN', NULL, NULL, 'Non fourni', '62924265', '', '', '', NULL, 0, 5000, NULL, 'Nouveau', 1104000, 2, '2026-05-25', NULL),
(134, 'dg', NULL, NULL, 'Non fourni', '-', '', '', '', NULL, 0, 35000, NULL, 'Nouveau', 100000, 1, '2026-05-13', NULL),
(135, 'fao', NULL, NULL, 'Non fourni', '-', '', '-', '', NULL, 0, 50, NULL, 'Nouveau', 66700, 2, '2026-05-15', NULL),
(136, '61246433', NULL, NULL, '-', '61246433', '-', '-', '-', NULL, 0, 0, NULL, 'Nouveau', 50000, 1, '2026-05-18', NULL),
(137, 'fatiri', NULL, NULL, '', '68714057', '', '', '', NULL, 0, 0, NULL, 'Nouveau', 30000, 1, '2026-05-19', NULL),
(138, 'FLEURY', NULL, NULL, '', '79461059', '', '', '', NULL, 0, 0, NULL, 'Nouveau', 20000, 1, '2026-05-20', NULL),
(139, 'KINGS PRINTING', NULL, NULL, '', '', '', '', '', NULL, 0, 0, NULL, 'Nouveau', 66000, 2, '2026-06-17', NULL),
(140, 'fariti', NULL, NULL, '', '', '', '', '', NULL, 0, 0, NULL, 'Nouveau', 8000, 1, '2026-05-21', NULL),
(141, 'farit', NULL, NULL, '', '', '', '', '', NULL, 0, 0, NULL, 'Nouveau', 20000, 1, '2026-05-21', NULL),
(142, 'kings', NULL, NULL, '', '', '', '', '', NULL, 0, 38500, NULL, 'Nouveau', 119000, 4, '2026-06-03', NULL),
(143, 'fanny lavine', NULL, NULL, '', '', '', '', '', NULL, 0, 0, NULL, 'Nouveau', 200000, 1, '2026-05-22', NULL),
(144, 'CEDRICK', NULL, NULL, '', '', '', '', '', NULL, 0, 0, NULL, 'Nouveau', 195000, 1, '2026-05-25', NULL),
(145, 'horizon', NULL, NULL, '', '68703120', '', '', '', NULL, 0, 10000, NULL, 'Nouveau', 30000, 1, '2026-05-29', NULL),
(146, 'CFR DON FLEURY', NULL, NULL, '', '79461059', '', '', '', NULL, 0, 0, NULL, 'Nouveau', 30000, 1, '2026-06-01', NULL),
(147, 'edd', NULL, 'client', 'GIHOSHA', '71355319', 'ELoheeKA', '0003/786', '400045788555', NULL, 0, 284280, '25-12', 'Nouveau', 0, 1, '2026-06-01', NULL),
(148, 'STAR', NULL, NULL, '', '', '', '', '', NULL, 0, 0, NULL, 'Nouveau', 7000, 1, '2026-06-01', NULL),
(149, 'METALUSA', NULL, NULL, '', '', '', '', '', NULL, 0, 0, NULL, 'Nouveau', 0, 1, '2026-06-02', NULL),
(150, 'FANNY', NULL, NULL, '', '', '', '', '', NULL, 0, 0, NULL, 'Nouveau', 35000, 1, '2026-06-03', NULL),
(151, 'victory beverages', NULL, NULL, '', '', '', '', '', NULL, 0, 0, NULL, 'Nouveau', 165200, 1, '2026-06-04', NULL),
(152, 'Client de passage', NULL, 'client', '-', '-', '-', '-', '-', 'prod_6a2e0d8b1db23.jpg', 0, 445002, '23-04', 'VIP', 17974050, 287, '2026-08-18', NULL),
(153, 'séraphine', NULL, NULL, '', '', '', '', '', NULL, 0, 0, NULL, 'Nouveau', 1000000, 1, '2026-06-29', NULL),
(154, 'sammy ggc', NULL, NULL, '', '61086933', '', '', '', NULL, 0, 0, NULL, 'Nouveau', 60000, 1, '2026-07-06', NULL),
(155, 'sammy', NULL, NULL, '', '', '', '', '', NULL, 0, 20000, NULL, 'Nouveau', 40000, 1, '2026-07-06', NULL),
(156, 'patient', NULL, NULL, '', '68469227', '', '', '', NULL, 0, 0, NULL, 'Nouveau', 365900, 1, '2026-07-24', NULL),
(157, 'Victory', NULL, NULL, '', '', '', '', '', NULL, 0, 0, NULL, 'Nouveau', 200000, 1, '2026-07-24', NULL),
(158, '79032914', NULL, NULL, '', '', '', '', '', NULL, 0, 0, NULL, 'Nouveau', 20000, 1, '2026-07-30', NULL),
(159, 'cartas', NULL, NULL, '', '79428564', '', '', '', NULL, 0, 0, NULL, 'Nouveau', 100000, 1, '2026-08-05', NULL),
(160, 'hamza amababa', NULL, NULL, '', '61292657', '', '', '', NULL, 0, 118000, NULL, 'Nouveau', 50000, 1, '2026-08-18', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(100) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `quantity_corrections`
--

INSERT INTO `quantity_corrections` (`id`, `product_id`, `medicine_name`, `old_entry_quantity`, `new_entry_quantity`, `old_stock_quantity`, `new_stock_quantity`, `difference`, `reason`, `invoice_ref`, `corrected_by`, `correction_date`, `correction_time`, `created_at`) VALUES
(1, '62', 'Cadre photo A1', '5.00', '4.00', '5.00', '4.00', '-1.00', 'Erreur de vitesse', '-', 'Cynthia', '09-06-2026', '18:32:30', '2026-06-09 15:32:30'),
(2, '776', 'Ixine sp Cefixime 100mg/5ml', '1.00', '2.00', '1.00', '2.00', '1.00', 'erreur', '-', 'Cynthia', '14-06-2026', '20:52:17', '2026-06-14 17:52:17'),
(3, '168', 'Cefixime sp 100mg', '3.00', '4.00', '3.00', '4.00', '1.00', 'erreur', '-', 'Cynthia', '14-06-2026', '20:59:18', '2026-06-14 17:59:18'),
(4, '10', 'J', '50.00', '50.00', '51.00', '51.00', '0.00', '-', '-', 'Clovis', '17-06-2026', '00:54:49', '2026-06-17 21:54:49'),
(5, '10', 'J', '50.00', '49.00', '51.00', '50.00', '-1.00', '-', '-', 'Clovis', '17-06-2026', '00:55:05', '2026-06-17 21:55:05');

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
  `period_end` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `operator` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `agent` varchar(255) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `medicines` varchar(255) NOT NULL,
  `quantity` varchar(255) NOT NULL,
  `unit_cost` varchar(100) NOT NULL,
  `actual_price` varchar(255) NOT NULL,
  `tot_htva` bigint(20) NOT NULL,
  `tva` bigint(20) NOT NULL,
  `tva_paid` bigint(20) NOT NULL,
  `tot_tvac` bigint(20) NOT NULL,
  `debt` bigint(11) NOT NULL,
  `total_amount` bigint(11) NOT NULL,
  `total_profit` bigint(11) NOT NULL,
  `discount` bigint(11) NOT NULL,
  `commission` bigint(11) NOT NULL DEFAULT 0,
  `date` date NOT NULL DEFAULT current_timestamp(),
  `status` enum('Active','Supprimée') DEFAULT 'Active',
  `origin` varchar(50) NOT NULL,
  `guard` varchar(500) NOT NULL,
  `netcash` decimal(10,0) NOT NULL,
  `payment_method` varchar(200) NOT NULL DEFAULT 'cash',
  `ext_paid_amount` decimal(10,0) DEFAULT NULL,
  `ext_paid_status` varchar(50) DEFAULT NULL,
  `cashier` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `sales`
--

INSERT INTO `sales` (`id`, `invoice_number`, `operator`, `agent`, `client_name`, `phone`, `medicines`, `quantity`, `unit_cost`, `actual_price`, `tot_htva`, `tva`, `tva_paid`, `tot_tvac`, `debt`, `total_amount`, `total_profit`, `discount`, `commission`, `date`, `status`, `origin`, `guard`, `netcash`, `payment_method`, `ext_paid_amount`, `ext_paid_status`, `cashier`) VALUES
(1, '0001', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression sur corde', '2.00', '4000', '0', 8000, 0, 0, 8000, 0, 8000, 8000, 0, 0, '2026-06-25', 'Active', 'Interne', '0', '8000', 'Espèces', NULL, NULL, ''),
(2, '0002', 'Isabelle', 'Cynthia', 'Client de passage', '-', 'DtfA4', '1.00', '10000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, 0, 0, '2026-06-25', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(3, '0003', 'Isabelle', 'Cynthia', 'Client de passage', '-', 'Impression N/B', '1.00', '2000', '0', 2000, 0, 0, 2000, 0, 2000, 2000, -1000, 0, '2026-06-25', 'Active', 'Interne', '0', '2000', 'Espèces', NULL, NULL, ''),
(4, '0004', 'Isabelle', 'Cynthia', 'Client de passage', '-', 'Impression N/B', '23.00', '1000', '0', 23000, 0, 0, 23000, 0, 23000, 23000, 0, 0, '2026-06-25', 'Active', 'Interne', '0', '23000', 'Espèces', NULL, NULL, ''),
(5, '0005', 'Isabelle', 'Cynthia', 'Client de passage', '-', 'DtfA4', '1.00', '10000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, 0, 0, '2026-06-25', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(6, '0006', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '1.00', '10000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, 0, 0, '2026-06-26', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(7, '0007', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '1.00', '10000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, 0, 0, '2026-06-26', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(8, '0008', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '1.00', '10000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, 0, 0, '2026-06-26', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(9, '0009', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '1.00', '15000', '10000', 15000, 0, 0, 15000, 0, 15000, 5000, 0, 0, '2026-06-26', 'Active', 'Interne', '0', '15000', 'Espèces', NULL, NULL, ''),
(10, '0010', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'AUTOCOLLANT', '1.00', '20000', '12000', 20000, 0, 0, 20000, 0, 20000, 8000, -2000, 0, '2026-06-26', 'Active', 'Interne', '0', '20000', 'Espèces', NULL, NULL, ''),
(11, '0011', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '1.00', '15000', '10000', 15000, 0, 0, 15000, 0, 15000, 5000, 0, 0, '2026-06-26', 'Active', 'Interne', '0', '15000', 'Espèces', NULL, NULL, ''),
(12, '0012', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '2.40', '15000', '10000', 36000, 0, 0, 36000, 0, 36000, 12000, 0, 0, '2026-06-26', 'Active', 'Interne', '0', '36000', 'Espèces', NULL, NULL, ''),
(13, '0013', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Photocopie', '13.00', '500', '0', 6500, 0, 0, 6500, 0, 6500, 6500, 0, 0, '2026-06-26', 'Active', 'Interne', '0', '6500', 'Espèces', NULL, NULL, ''),
(14, '0014', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ,AUTOCOLLANT', '2.50,1.90', '15000,18000', '10000,12000', 71700, 0, 0, 71700, 0, 71700, 23900, 0, 0, '2026-06-26', 'Active', 'Interne,Interne', '0,0', '71700', 'Espèces', NULL, NULL, ''),
(15, '0015', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '1.00', '15000', '10000', 15000, 0, 0, 15000, 0, 15000, 5000, 0, 0, '2026-06-26', 'Active', 'Interne', '0', '15000', 'Espèces', NULL, NULL, ''),
(16, '0016', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'INVITATIONS ', '250.00', '5500', '0', 1375000, 0, 0, 1375000, 0, 1375000, 1375000, -125000, 0, '2026-06-29', 'Active', 'Interne', '0', '1375000', 'Espèces', NULL, NULL, ''),
(17, '0017', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '1.00', '10000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, 0, 0, '2026-06-29', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(18, '0018', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Heating', '2.00', '1000', '0', 2000, 0, 0, 2000, 0, 2000, 2000, 0, 0, '2026-06-29', 'Active', 'Interne', '0', '2000', 'Espèces', NULL, NULL, ''),
(19, '0019', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '1.00', '15000', '10000', 15000, 0, 0, 15000, 0, 15000, 5000, 0, 0, '2026-06-29', 'Active', 'Interne', '0', '15000', 'Espèces', NULL, NULL, ''),
(20, '0020', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Stylo personnalise', '3.00', '8000', '3000', 24000, 0, 0, 24000, 0, 24000, 15000, 0, 0, '2026-06-29', 'Active', 'Interne', '0', '24000', 'Espèces', NULL, NULL, ''),
(21, '0021', 'Cynthia', 'Cynthia', 'séraphine', '', 'Lacoste', '28.00', '45000', '37000', 1260000, 0, 0, 1260000, 260000, 1000000, 224000, 0, 0, '2026-06-29', 'Active', 'Interne', '0', '1000000', 'Espèces', NULL, NULL, ''),
(22, '0022', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression sur tshirt', '2.00', '10000', '0', 20000, 0, 0, 20000, 0, 20000, 20000, 0, 0, '2026-06-29', 'Active', 'Interne', '0', '20000', 'Espèces', NULL, NULL, ''),
(23, '0023', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '0.60', '15000', '10000', 9000, 0, 0, 9000, 0, 9000, 3000, 0, 0, '2026-06-29', 'Supprimée', 'Interne', '0', '9000', 'Espèces', NULL, NULL, ''),
(24, '0024', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '0.60', '15000', '10000', 9000, 0, 0, 9000, 0, 9000, 3000, 0, 0, '2026-06-29', 'Active', 'Interne', '0', '9000', 'Espèces', NULL, NULL, ''),
(25, '0025', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Design', '1.00', '20000', '0', 20000, 0, 0, 20000, 0, 20000, 20000, 80000, 0, '2026-06-29', 'Active', 'Interne', '0', '20000', 'Espèces', NULL, NULL, ''),
(26, '0026', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression sur tshirt', '1.00', '10000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, 0, 0, '2026-06-29', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(27, '0027', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '1.00', '15000', '10000', 15000, 0, 0, 15000, 0, 15000, 5000, 0, 0, '2026-06-29', 'Active', 'Interne', '0', '15000', 'Espèces', NULL, NULL, ''),
(28, '0028', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Anneau', '4.00', '500', '0', 2000, 0, 0, 2000, 0, 2000, 2000, 0, 0, '2026-06-29', 'Active', 'Interne', '0', '2000', 'Espèces', NULL, NULL, ''),
(29, '0029', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Pvc', '1.00', '8000', '0', 8000, 0, 0, 8000, 0, 8000, 8000, -3000, 0, '2026-06-30', 'Active', 'Interne', '0', '8000', 'Espèces', NULL, NULL, ''),
(30, '0030', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'AUTOCOLLANT', '2.79', '18000', '12000', 50220, 0, 0, 50220, 0, 50220, 16740, 0, 0, '2026-06-30', 'Supprimée', 'Interne', '0', '50220', 'Espèces', NULL, NULL, ''),
(31, '0031', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'AUTOCOLLANT', '2.80', '18000', '12000', 50400, 0, 0, 50400, 0, 50400, 16800, 0, 0, '2026-06-30', 'Active', 'Interne', '0', '50400', 'Espèces', NULL, NULL, ''),
(32, '0032', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '26.75', '15000', '10000', 401250, 0, 0, 401250, 0, 401250, 133750, 0, 0, '2026-06-30', 'Active', 'Interne', '0', '401250', 'Espèces', NULL, NULL, ''),
(33, '0033', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'AUTOCOLLANT', '1.00', '18000', '12000', 18000, 0, 0, 18000, 0, 18000, 6000, 0, 0, '2026-07-02', 'Active', 'Interne', '0', '18000', 'Espèces', NULL, NULL, ''),
(34, '0034', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '1.00', '10000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, 0, 0, '2026-07-02', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(35, '0035', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Heating', '2.00', '1000', '0', 2000, 0, 0, 2000, 0, 2000, 2000, 0, 0, '2026-07-02', 'Active', 'Interne', '0', '2000', 'Espèces', NULL, NULL, ''),
(36, '0036', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '3.60', '15000', '10000', 54000, 0, 0, 54000, 0, 54000, 18000, 0, 0, '2026-07-02', 'Active', 'Interne', '0', '54000', 'Espèces', NULL, NULL, ''),
(37, '0037', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Anneau', '4.00', '500', '0', 2000, 0, 0, 2000, 0, 2000, 2000, 0, 0, '2026-07-02', 'Active', 'Interne', '0', '2000', 'Espèces', NULL, NULL, ''),
(38, '0038', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Design 1', '1.00', '14000', '0', 14000, 0, 0, 14000, 0, 14000, 14000, 0, 0, '2026-07-02', 'Active', 'Interne', '0', '14000', 'Espèces', NULL, NULL, ''),
(39, '0039', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '1.00', '10000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, 0, 0, '2026-07-02', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(40, '0040', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Carte de visite ', '20.00', '500', '0', 10000, 0, 0, 10000, 0, 10000, 10000, 0, 0, '2026-07-02', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(41, '0041', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Depliant', '20.00', '2000', '0', 40000, 18, 7200, 47200, 0, 47200, 40000, 0, 0, '2026-07-02', 'Active', 'Interne', '0', '47200', 'Espèces', NULL, NULL, ''),
(42, '0042', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Backlight1m', '1.00', '32000', '15000', 32000, 0, 0, 32000, 0, 32000, 17000, 3000, 0, '2026-07-06', 'Active', 'Interne', '0', '32000', 'Espèces', NULL, NULL, ''),
(43, '0043', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '8.64', '15000', '10000', 129600, 0, 0, 129600, 0, 129600, 43200, 0, 0, '2026-07-06', 'Active', 'Interne', '0', '129600', 'Espèces', NULL, NULL, ''),
(44, '0044', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '10.80', '15000', '10000', 162000, 0, 0, 162000, 0, 162000, 54000, 0, 0, '2026-07-06', 'Active', 'Interne', '0', '162000', 'Espèces', NULL, NULL, ''),
(45, '0045', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'AUTOCOLLANT', '1.00', '27432', '12000', 27432, 0, 0, 27432, 2, 27430, 15432, -9432, 0, '2026-07-06', 'Active', 'Interne', '0', '27430', 'Espèces', NULL, NULL, ''),
(46, '0046', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'AUTOCOLLANT', '1.14', '18000', '12000', 20520, 0, 0, 20520, 0, 20520, 6840, 0, 0, '2026-07-06', 'Active', 'Interne', '0', '20520', 'Espèces', NULL, NULL, ''),
(47, '0047', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '3.24', '15000', '10000', 48600, 0, 0, 48600, 0, 48600, 16200, 0, 0, '2026-07-06', 'Active', 'Interne', '0', '48600', 'Espèces', NULL, NULL, ''),
(48, '0048', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression N/B', '30.00', '1000', '0', 30000, 0, 0, 30000, 0, 30000, 30000, 0, 0, '2026-07-06', 'Active', 'Interne', '0', '30000', 'Espèces', NULL, NULL, ''),
(49, '0049', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Tshirt 23lacoste15chapeau6 impression9', '1.00', '1708000', '0', 1708000, 0, 0, 1708000, 758000, 950000, 1708000, 0, 0, '2026-07-06', 'Active', 'Interne', '0', '950000', 'Espèces', NULL, NULL, ''),
(50, '0050', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Back light 1.2et 1 +design', '1.00', '202500', '0', 202500, 0, 0, 202500, 0, 202500, 202500, 0, 0, '2026-07-06', 'Active', 'Interne', '0', '202500', 'Espèces', NULL, NULL, ''),
(51, '0051', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Xstand', '1.00', '150000', '0', 150000, 0, 0, 150000, 0, 150000, 150000, 0, 0, '2026-07-06', 'Active', 'Interne', '0', '150000', 'Espèces', NULL, NULL, ''),
(52, '0052', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Lacoste', '1.00', '47000', '37000', 47000, 0, 0, 47000, 0, 47000, 10000, -2000, 0, '2026-07-06', 'Active', 'Interne', '0', '47000', 'Espèces', NULL, NULL, ''),
(53, '0053', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression sur tshirt', '15.00', '10000', '0', 150000, 0, 0, 150000, 50000, 100000, 150000, 0, 0, '2026-07-06', 'Active', 'Interne', '0', '100000', 'Espèces', NULL, NULL, ''),
(54, '0054', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '14.00', '10000', '0', 140000, 0, 0, 140000, 0, 140000, 140000, 0, 0, '2026-07-06', 'Active', 'Interne', '0', '140000', 'Espèces', NULL, NULL, ''),
(55, '0055', 'Cynthia', 'Cynthia', 'sammy ggc', '61086933', 'BACHE ', '4.00', '15000', '10000', 60000, 0, 0, 60000, 0, 60000, 20000, 0, 0, '2026-07-06', 'Supprimée', 'Interne', '0', '60000', 'Espèces', NULL, NULL, ''),
(56, '0056', 'Cynthia', 'Cynthia', 'sammy', '', 'BACHE ', '4.00', '15000', '10000', 60000, 0, 0, 60000, 20000, 40000, 20000, 0, 0, '2026-07-06', 'Active', 'Interne', '0', '40000', 'Espèces', NULL, NULL, ''),
(57, '0057', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Heating', '2.00', '1000', '0', 2000, 0, 0, 2000, 0, 2000, 2000, 0, 0, '2026-07-06', 'Active', 'Interne', '0', '2000', 'Espèces', NULL, NULL, ''),
(58, '0058', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '1.00', '15000', '10000', 15000, 0, 0, 15000, 0, 15000, 5000, 0, 0, '2026-07-06', 'Active', 'Interne', '0', '15000', 'Espèces', NULL, NULL, ''),
(59, '0059', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '1.20', '15000', '10000', 18000, 0, 0, 18000, 0, 18000, 6000, 0, 0, '2026-07-06', 'Active', 'Interne', '0', '18000', 'Espèces', NULL, NULL, ''),
(60, '0060', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '1.00', '10000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, 0, 0, '2026-07-06', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(61, '0061', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '4.00', '15000', '10000', 60000, 0, 0, 60000, 0, 60000, 20000, 0, 0, '2026-07-06', 'Active', 'Interne', '0', '60000', 'Espèces', NULL, NULL, ''),
(62, '0062', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Photocopie', '2.00', '500', '0', 1000, 0, 0, 1000, 0, 1000, 1000, 0, 0, '2026-07-06', 'Active', 'Interne', '0', '1000', 'Espèces', NULL, NULL, ''),
(63, '0063', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Anneau', '8.00', '500', '0', 4000, 0, 0, 4000, 0, 4000, 4000, 0, 0, '2026-07-06', 'Active', 'Interne', '0', '4000', 'Espèces', NULL, NULL, ''),
(64, '0064', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '21.60', '15000', '10000', 324000, 0, 0, 324000, 124000, 200000, 108000, 0, 0, '2026-07-06', 'Active', 'Interne', '0', '200000', 'Espèces', NULL, NULL, ''),
(65, '0065', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '6.54', '15000', '10000', 98100, 0, 0, 98100, 0, 98100, 32700, 0, 0, '2026-07-07', 'Active', 'Interne', '0', '98100', 'Espèces', NULL, NULL, ''),
(66, '0066', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'IMPRESSION EN COULEUR', '2.00', '1500', '0', 3000, 0, 0, 3000, 0, 3000, 3000, 0, 0, '2026-07-07', 'Active', 'Interne', '0', '3000', 'Espèces', NULL, NULL, ''),
(67, '0067', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Enveloppe A4', '1.00', '1000', '500', 1000, 0, 0, 1000, 0, 1000, 500, 0, 0, '2026-07-07', 'Active', 'Interne', '0', '1000', 'Espèces', NULL, NULL, ''),
(68, '0068', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression N/B', '1.00', '1000', '0', 1000, 0, 0, 1000, 0, 1000, 1000, 0, 0, '2026-07-07', 'Active', 'Interne', '0', '1000', 'Espèces', NULL, NULL, ''),
(69, '0069', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '3.00', '18000', '10000', 54000, 18, 9720, 63720, 0, 63720, 24000, -9000, 0, '2026-07-07', 'Active', 'Interne', '0', '63720', 'Espèces', NULL, NULL, ''),
(70, '0070', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression N/B', '10.00', '2000', '0', 20000, 0, 0, 20000, 0, 20000, 20000, -10000, 0, '2026-07-08', 'Active', 'Interne', '0', '20000', 'Espèces', NULL, NULL, ''),
(71, '0071', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Anneau', '12.00', '500', '0', 6000, 0, 0, 6000, 0, 6000, 6000, 0, 0, '2026-07-08', 'Active', 'Interne', '0', '6000', 'Espèces', NULL, NULL, ''),
(72, '0072', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DTF 1m', '5.00', '40000', '0', 200000, 0, 0, 200000, 0, 200000, 200000, 0, 0, '2026-07-08', 'Active', 'Interne', '0', '200000', 'Espèces', NULL, NULL, ''),
(73, '0073', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Pvc', '1.00', '5000', '0', 5000, 0, 0, 5000, 0, 5000, 5000, 0, 0, '2026-07-08', 'Active', 'Interne', '0', '5000', 'Espèces', NULL, NULL, ''),
(74, '0074', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Porte badge', '1.00', '4000', '0', 4000, 0, 0, 4000, 0, 4000, 4000, 0, 0, '2026-07-08', 'Active', 'Interne', '0', '4000', 'Espèces', NULL, NULL, ''),
(75, '0075', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '5.00', '10000', '0', 50000, 0, 0, 50000, 0, 50000, 50000, 0, 0, '2026-07-08', 'Active', 'Interne', '0', '50000', 'Espèces', NULL, NULL, ''),
(76, '0076', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Heating', '10.00', '2000', '0', 20000, 0, 0, 20000, 0, 20000, 20000, -10000, 0, '2026-07-08', 'Active', 'Interne', '0', '20000', 'Espèces', NULL, NULL, ''),
(77, '0077', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '1.00', '15000', '10000', 15000, 0, 0, 15000, 0, 15000, 5000, 0, 0, '2026-07-08', 'Active', 'Interne', '0', '15000', 'Espèces', NULL, NULL, ''),
(78, '0078', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '1.20', '15000', '10000', 18000, 0, 0, 18000, 0, 18000, 6000, 0, 0, '2026-07-08', 'Active', 'Interne', '0', '18000', 'Espèces', NULL, NULL, ''),
(79, '0079', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '3.00', '10000', '0', 30000, 0, 0, 30000, 0, 30000, 30000, 0, 0, '2026-07-08', 'Active', 'Interne', '0', '30000', 'Espèces', NULL, NULL, ''),
(80, '0080', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Heating', '10.00', '1500', '0', 15000, 0, 0, 15000, 0, 15000, 15000, -5000, 0, '2026-07-08', 'Active', 'Interne', '0', '15000', 'Espèces', NULL, NULL, ''),
(81, '0081', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'IMPRESSION SUR GILET', '2.00', '20000', '0', 40000, 0, 0, 40000, 0, 40000, 40000, 0, 0, '2026-07-08', 'Active', 'Interne', '0', '40000', 'Espèces', NULL, NULL, ''),
(82, '0082', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '1.00', '10000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, 0, 0, '2026-07-08', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(83, '0083', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '3.00', '15000', '10000', 45000, 0, 0, 45000, 0, 45000, 15000, 0, 0, '2026-07-08', 'Active', 'Interne', '0', '45000', 'Espèces', NULL, NULL, ''),
(84, '0084', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'AUTOCOLLANT', '0.96', '18000', '12000', 17280, 0, 0, 17280, 0, 17280, 5760, 0, 0, '2026-07-08', 'Active', 'Interne', '0', '17280', 'Espèces', NULL, NULL, ''),
(85, '0085', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression N/B', '2.00', '1000', '0', 2000, 0, 0, 2000, 0, 2000, 2000, 0, 0, '2026-07-08', 'Active', 'Interne', '0', '2000', 'Espèces', NULL, NULL, ''),
(86, '0086', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '1.00', '10000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, 0, 0, '2026-07-09', 'Supprimée', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(87, '0087', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression sur tshirt', '1.00', '20000', '0', 20000, 0, 0, 20000, 0, 20000, 20000, -10000, 0, '2026-07-09', 'Active', 'Interne', '0', '20000', 'Espèces', NULL, NULL, ''),
(88, '0088', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '2.00', '15000', '10000', 30000, 0, 0, 30000, 0, 30000, 10000, 0, 0, '2026-07-09', 'Active', 'Interne', '0', '30000', 'Espèces', NULL, NULL, ''),
(89, '0089', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Branding pancarte de 2m sur 2m', '1.00', '100000', '0', 100000, 0, 0, 100000, 0, 100000, 100000, 0, 0, '2026-07-09', 'Active', 'Interne', '0', '100000', 'Espèces', NULL, NULL, ''),
(90, '0090', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Photocopie', '1.00', '500', '0', 500, 0, 0, 500, 0, 500, 500, 0, 0, '2026-07-09', 'Active', 'Interne', '0', '500', 'Espèces', NULL, NULL, ''),
(91, '0091', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Carte de visite ', '30.00', '500', '0', 15000, 0, 0, 15000, 0, 15000, 15000, 0, 0, '2026-07-09', 'Active', 'Interne', '0', '15000', 'Espèces', NULL, NULL, ''),
(92, '0092', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '1.00', '10000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, 0, 0, '2026-07-09', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(93, '0093', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '4.00', '15000', '10000', 60000, 0, 0, 60000, 0, 60000, 20000, 0, 0, '2026-07-09', 'Active', 'Interne', '0', '60000', 'Espèces', NULL, NULL, ''),
(94, '0094', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Anneau', '8.00', '500', '0', 4000, 0, 0, 4000, 0, 4000, 4000, 0, 0, '2026-07-09', 'Active', 'Interne', '0', '4000', 'Espèces', NULL, NULL, ''),
(95, '0095', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '1.00', '10000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, 0, 0, '2026-07-09', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(96, '0096', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Photocopie', '1.00', '500', '0', 500, 0, 0, 500, 0, 500, 500, 0, 0, '2026-07-09', 'Active', 'Interne', '0', '500', 'Espèces', NULL, NULL, ''),
(97, '0097', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '2.00', '10000', '0', 20000, 0, 0, 20000, 0, 20000, 20000, 0, 0, '2026-07-09', 'Active', 'Interne', '0', '20000', 'Espèces', NULL, NULL, ''),
(98, '0098', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression N/B', '280.00', '800', '0', 224000, 0, 0, 224000, 0, 224000, 224000, 56000, 0, '2026-07-10', 'Active', 'Interne', '0', '224000', 'Espèces', NULL, NULL, ''),
(99, '0099', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Finition Avec spiral', '1.00', '20000', '0', 20000, 0, 0, 20000, 0, 20000, 20000, 0, 0, '2026-07-10', 'Active', 'Interne', '0', '20000', 'Espèces', NULL, NULL, ''),
(100, '0100', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression N/B', '6.00', '1000', '0', 6000, 0, 0, 6000, 0, 6000, 6000, 0, 0, '2026-07-10', 'Active', 'Interne', '0', '6000', 'Espèces', NULL, NULL, ''),
(101, '0101', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression N/B', '1.00', '1000', '0', 1000, 0, 0, 1000, 0, 1000, 1000, 0, 0, '2026-07-10', 'Active', 'Interne', '0', '1000', 'Espèces', NULL, NULL, ''),
(102, '0102', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Lacoste', '1.00', '45000', '37000', 45000, 0, 0, 45000, 0, 45000, 8000, 0, 0, '2026-07-10', 'Active', 'Interne', '0', '45000', 'Espèces', NULL, NULL, ''),
(103, '0103', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '1.50', '40000', '10000', 60000, 0, 0, 60000, 0, 60000, 45000, -37500, 0, '2026-07-10', 'Active', 'Interne', '0', '60000', 'Espèces', NULL, NULL, ''),
(104, '0104', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'AUTOCOLLANT', '0.64', '18750', '12000', 12000, 0, 0, 12000, 0, 12000, 4320, -480, 0, '2026-07-10', 'Active', 'Interne', '0', '12000', 'Espèces', NULL, NULL, ''),
(105, '0105', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Stylo personnalise vip', '65.00', '6000', '0', 390000, 0, 0, 390000, 275000, 115000, 390000, 0, 0, '2026-07-10', 'Active', 'Interne', '0', '115000', 'Espèces', NULL, NULL, ''),
(106, '0106', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '2.80', '15000', '10000', 42000, 0, 0, 42000, 0, 42000, 14000, 0, 0, '2026-07-10', 'Active', 'Interne', '0', '42000', 'Espèces', NULL, NULL, ''),
(107, '0107', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Design', '1.00', '20000', '0', 20000, 0, 0, 20000, 0, 20000, 20000, 80000, 0, '2026-07-10', 'Active', 'Interne', '0', '20000', 'Espèces', NULL, NULL, ''),
(108, '0108', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Anneau', '4.00', '500', '0', 2000, 0, 0, 2000, 0, 2000, 2000, 0, 0, '2026-07-10', 'Active', 'Interne', '0', '2000', 'Espèces', NULL, NULL, ''),
(109, '0109', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression N/B', '1.00', '1000', '0', 1000, 0, 0, 1000, 0, 1000, 1000, 0, 0, '2026-07-10', 'Active', 'Interne', '0', '1000', 'Espèces', NULL, NULL, ''),
(110, '0110', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '1.00', '10000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, 0, 0, '2026-07-13', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(111, '0111', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '3.30', '15000', '10000', 49500, 0, 0, 49500, 0, 49500, 16500, 0, 0, '2026-07-13', 'Active', 'Interne', '0', '49500', 'Espèces', NULL, NULL, ''),
(112, '0112', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Anneau', '1.00', '500', '0', 500, 0, 0, 500, 0, 500, 500, 0, 0, '2026-07-13', 'Active', 'Interne', '0', '500', 'Espèces', NULL, NULL, ''),
(113, '0113', 'Isabelle', 'Isabelle', 'Client de passage', '-', 'Lacoste', '2.00', '45000', '37000', 90000, 0, 0, 90000, 0, 90000, 16000, 0, 0, '2026-07-13', 'Supprimée', 'Interne', '0', '90000', 'Espèces', NULL, NULL, ''),
(114, '0114', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Pvc', '1.00', '5000', '0', 5000, 0, 0, 5000, 0, 5000, 5000, 0, 0, '2026-07-13', 'Active', 'Interne', '0', '5000', 'Espèces', NULL, NULL, ''),
(115, '0115', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Porte badge', '1.00', '5000', '0', 5000, 0, 0, 5000, 0, 5000, 5000, -1000, 0, '2026-07-13', 'Active', 'Interne', '0', '5000', 'Espèces', NULL, NULL, ''),
(116, '0116', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Photocopie', '18.00', '500', '0', 9000, 0, 0, 9000, 0, 9000, 9000, 0, 0, '2026-07-13', 'Active', 'Interne', '0', '9000', 'Espèces', NULL, NULL, ''),
(117, '0117', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Enveloppe A4', '1.00', '1000', '500', 1000, 0, 0, 1000, 0, 1000, 500, 0, 0, '2026-07-13', 'Active', 'Interne', '0', '1000', 'Espèces', NULL, NULL, ''),
(118, '0118', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Papier auto A4 ETIQUETTE', '30.00', '600', '0', 18000, 18, 3240, 21240, 0, 21240, 18000, 0, 0, '2026-07-13', 'Active', 'Interne', '0', '21240', 'Espèces', NULL, NULL, ''),
(119, '0119', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '5.00', '10000', '0', 50000, 0, 0, 50000, 0, 50000, 50000, 0, 0, '2026-07-13', 'Active', 'Interne', '0', '50000', 'Espèces', NULL, NULL, ''),
(120, '0120', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Enveloppe A4', '3.00', '1000', '500', 3000, 0, 0, 3000, 0, 3000, 1500, 0, 0, '2026-07-14', 'Active', 'Interne', '0', '3000', 'Espèces', NULL, NULL, ''),
(121, '0121', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Xstand', '3.00', '200000', '0', 600000, 0, 0, 600000, 0, 600000, 600000, -150000, 0, '2026-07-14', 'Active', 'Interne', '0', '600000', 'Espèces', NULL, NULL, ''),
(122, '0122', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '1.50', '15000', '10000', 22500, 0, 0, 22500, 0, 22500, 7500, 0, 0, '2026-07-14', 'Active', 'Interne', '0', '22500', 'Espèces', NULL, NULL, ''),
(123, '0123', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Autocollant non imprimé', '10.00', '10000', '0', 100000, 0, 0, 100000, 0, 100000, 100000, 0, 0, '2026-07-14', 'Active', 'Interne', '0', '100000', 'Espèces', NULL, NULL, ''),
(124, '0124', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '1.50', '15000', '10000', 22500, 0, 0, 22500, 0, 22500, 7500, 0, 0, '2026-07-14', 'Active', 'Interne', '0', '22500', 'Espèces', NULL, NULL, ''),
(125, '0125', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Anneau', '6.00', '500', '0', 3000, 0, 0, 3000, 0, 3000, 3000, 0, 0, '2026-07-14', 'Active', 'Interne', '0', '3000', 'Espèces', NULL, NULL, ''),
(126, '0126', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression N/B', '108.00', '1000', '0', 108000, 18, 19440, 127440, 0, 127440, 108000, 0, 0, '2026-07-14', 'Active', 'Interne', '0', '127440', 'Espèces', NULL, NULL, ''),
(127, '0127', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '2.00', '10000', '0', 20000, 0, 0, 20000, 0, 20000, 20000, 0, 0, '2026-07-14', 'Active', 'Interne', '0', '20000', 'Espèces', NULL, NULL, ''),
(128, '0128', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DTF A5', '1.00', '5000', '0', 5000, 0, 0, 5000, 0, 5000, 5000, 0, 0, '2026-07-14', 'Active', 'Interne', '0', '5000', 'Espèces', NULL, NULL, ''),
(129, '0129', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '1.00', '10000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, 0, 0, '2026-07-14', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(130, '0130', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Badge vip', '1.00', '20000', '0', 20000, 0, 0, 20000, 0, 20000, 20000, 0, 0, '2026-07-14', 'Active', 'Interne', '0', '20000', 'Espèces', NULL, NULL, ''),
(131, '0131', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '1.00', '10000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, 0, 0, '2026-07-14', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(132, '0132', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Cutting', '1.00', '2000', '0', 2000, 0, 0, 2000, 0, 2000, 2000, 1000, 0, '2026-07-15', 'Active', 'Interne', '0', '2000', 'Espèces', NULL, NULL, ''),
(133, '0133', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'AUTOCOLLANT', '3.10', '18000', '12000', 55800, 0, 0, 55800, 0, 55800, 18600, 0, 0, '2026-07-15', 'Active', 'Interne', '0', '55800', 'Espèces', NULL, NULL, ''),
(134, '0134', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '30.36', '15000', '0', 455400, 0, 0, 455400, 0, 455400, 455400, 0, 0, '2026-07-15', 'Active', 'Interne', '0', '455400', 'Espèces', NULL, NULL, ''),
(135, '0135', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'AUTOCOLLANT', '2.00', '18000', '12000', 36000, 0, 0, 36000, 0, 36000, 12000, 0, 0, '2026-07-15', 'Active', 'Interne', '0', '36000', 'Espèces', NULL, NULL, ''),
(136, '0136', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '1.20', '15000', '10000', 18000, 0, 0, 18000, 0, 18000, 6000, 0, 0, '2026-07-15', 'Active', 'Interne', '0', '18000', 'Espèces', NULL, NULL, ''),
(137, '0137', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Anneau', '4.00', '500', '0', 2000, 0, 0, 2000, 0, 2000, 2000, 0, 0, '2026-07-15', 'Active', 'Interne', '0', '2000', 'Espèces', NULL, NULL, ''),
(138, '0138', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Enveloppe A4', '1.00', '1000', '500', 1000, 0, 0, 1000, 0, 1000, 500, 0, 0, '2026-07-16', 'Active', 'Interne', '0', '1000', 'Espèces', NULL, NULL, ''),
(139, '0139', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Cashet simple', '1.00', '30000', '0', 30000, 0, 0, 30000, 20000, 10000, 30000, 0, 0, '2026-07-16', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(140, '0140', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '5.00', '10000', '0', 50000, 0, 0, 50000, 0, 50000, 50000, 0, 0, '2026-07-16', 'Active', 'Interne', '0', '50000', 'Espèces', NULL, NULL, ''),
(141, '0141', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Heating', '60.00', '1000', '0', 60000, 0, 0, 60000, 0, 60000, 60000, 0, 0, '2026-07-16', 'Active', 'Interne', '0', '60000', 'Espèces', NULL, NULL, ''),
(142, '0142', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression N/B', '9.00', '1000', '0', 9000, 0, 0, 9000, 0, 9000, 9000, 0, 0, '2026-07-16', 'Active', 'Interne', '0', '9000', 'Espèces', NULL, NULL, ''),
(143, '0143', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Livre plus finition', '7.00', '110000', '0', 770000, 0, 0, 770000, 370000, 400000, 770000, 0, 0, '2026-07-16', 'Active', 'Interne', '0', '400000', 'Espèces', NULL, NULL, ''),
(144, '0144', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '2.00', '10000', '0', 20000, 0, 0, 20000, 0, 20000, 20000, 0, 0, '2026-07-17', 'Active', 'Interne', '0', '20000', 'Espèces', NULL, NULL, ''),
(145, '0145', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'INVITATIONS ', '100.00', '1200', '0', 120000, 0, 0, 120000, 0, 120000, 120000, 380000, 0, '2026-07-17', 'Active', 'Interne', '0', '120000', 'Espèces', NULL, NULL, ''),
(146, '0146', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '2.16', '15000', '10000', 32400, 0, 0, 32400, 0, 32400, 10800, 0, 0, '2026-07-17', 'Active', 'Interne', '0', '32400', 'Espèces', NULL, NULL, ''),
(147, '0147', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Anneau', '9.00', '500', '0', 4500, 0, 0, 4500, 0, 4500, 4500, 0, 0, '2026-07-17', 'Active', 'Interne', '0', '4500', 'Espèces', NULL, NULL, ''),
(148, '0148', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'AUTOCOLLANT', '1.52', '18000', '12000', 27360, 0, 0, 27360, 0, 27360, 9120, 0, 0, '2026-07-17', 'Active', 'Interne', '0', '27360', 'Espèces', NULL, NULL, ''),
(149, '0149', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '3.00', '10000', '10000', 30000, 0, 0, 30000, 0, 30000, 0, 15000, 0, '2026-07-17', 'Active', 'Interne', '0', '30000', 'Espèces', NULL, NULL, ''),
(150, '0150', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Certificat', '100.00', '3500', '0', 350000, 0, 0, 350000, 0, 350000, 350000, 0, 0, '2026-07-17', 'Active', 'Interne', '0', '350000', 'Espèces', NULL, NULL, ''),
(151, '0151', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'IMPRESSION EN COULEUR', '2.00', '5000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, -7000, 0, '2026-07-17', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(152, '0152', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Heating', '2.00', '1000', '0', 2000, 0, 0, 2000, 0, 2000, 2000, 0, 0, '2026-07-17', 'Active', 'Interne', '0', '2000', 'Espèces', NULL, NULL, ''),
(153, '0153', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '1.50', '15000', '10000', 22500, 0, 0, 22500, 0, 22500, 7500, 0, 0, '2026-07-20', 'Active', 'Interne', '0', '22500', 'Espèces', NULL, NULL, ''),
(154, '0154', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Anneau', '4.00', '500', '0', 2000, 0, 0, 2000, 0, 2000, 2000, 0, 0, '2026-07-20', 'Active', 'Interne', '0', '2000', 'Espèces', NULL, NULL, ''),
(155, '0155', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'AUTOCOLLANT', '3.30', '18000', '12000', 59400, 0, 0, 59400, 0, 59400, 19800, 0, 0, '2026-07-20', 'Active', 'Interne', '0', '59400', 'Espèces', NULL, NULL, ''),
(156, '0156', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '1.00', '15000', '10000', 15000, 0, 0, 15000, 0, 15000, 5000, 0, 0, '2026-07-20', 'Active', 'Interne', '0', '15000', 'Espèces', NULL, NULL, ''),
(157, '0157', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '1.00', '15000', '10000', 15000, 0, 0, 15000, 0, 15000, 5000, 0, 0, '2026-07-20', 'Active', 'Interne', '0', '15000', 'Espèces', NULL, NULL, ''),
(158, '0158', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Anneau', '4.00', '500', '0', 2000, 0, 0, 2000, 0, 2000, 2000, 0, 0, '2026-07-20', 'Active', 'Interne', '0', '2000', 'Espèces', NULL, NULL, ''),
(159, '0159', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Badge vip', '10.00', '15000', '0', 150000, 0, 0, 150000, 0, 150000, 150000, 50000, 0, '2026-07-20', 'Active', 'Interne', '0', '150000', 'Espèces', NULL, NULL, ''),
(160, '0160', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression sur tshirt', '3.00', '15000', '0', 45000, 0, 0, 45000, 0, 45000, 45000, -15000, 0, '2026-07-20', 'Active', 'Interne', '0', '45000', 'Espèces', NULL, NULL, ''),
(161, '0161', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Tear drop', '1.00', '600000', '0', 600000, 0, 0, 600000, 0, 600000, 600000, 50000, 0, '2026-07-20', 'Active', 'Interne', '0', '600000', 'Espèces', NULL, NULL, ''),
(162, '0162', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '0.40', '25000', '10000', 10000, 18, 1800, 11800, 0, 11800, 6000, -4000, 0, '2026-07-20', 'Active', 'Interne', '0', '11800', 'Espèces', NULL, NULL, ''),
(163, '0163', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression sur corde', '2.00', '4000', '0', 8000, 0, 0, 8000, 0, 8000, 8000, 0, 0, '2026-07-20', 'Active', 'Interne', '0', '8000', 'Espèces', NULL, NULL, ''),
(164, '0164', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Anneau', '2.00', '500', '0', 1000, 0, 0, 1000, 0, 1000, 1000, 0, 0, '2026-07-20', 'Active', 'Interne', '0', '1000', 'Espèces', NULL, NULL, ''),
(165, '0165', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '2.80', '15000', '10000', 42000, 0, 0, 42000, 0, 42000, 14000, 0, 0, '2026-07-20', 'Active', 'Interne', '0', '42000', 'Espèces', NULL, NULL, ''),
(166, '0166', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '2.00', '10000', '0', 20000, 0, 0, 20000, 0, 20000, 20000, 0, 0, '2026-07-21', 'Active', 'Interne', '0', '20000', 'Espèces', NULL, NULL, ''),
(167, '0167', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DTF A5', '1.00', '5000', '0', 5000, 0, 0, 5000, 0, 5000, 5000, 0, 0, '2026-07-21', 'Active', 'Interne', '0', '5000', 'Espèces', NULL, NULL, ''),
(168, '0168', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '2.40', '15000', '10000', 36000, 0, 0, 36000, 0, 36000, 12000, 0, 0, '2026-07-21', 'Active', 'Interne', '0', '36000', 'Espèces', NULL, NULL, ''),
(169, '0169', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Porte cle', '1.00', '35000', '0', 35000, 0, 0, 35000, 26000, 9000, 35000, -15000, 0, '2026-07-21', 'Active', 'Interne', '0', '9000', 'Espèces', NULL, NULL, ''),
(170, '0170', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Tshirt simple', '1.00', '55000', '27000', 55000, 0, 0, 55000, 0, 55000, 28000, -20000, 0, '2026-07-21', 'Active', 'Interne', '0', '55000', 'Espèces', NULL, NULL, ''),
(171, '0171', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '2.00', '10000', '0', 20000, 0, 0, 20000, 0, 20000, 20000, 0, 0, '2026-07-22', 'Active', 'Interne', '0', '20000', 'Espèces', NULL, NULL, ''),
(172, '0172', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Parapluie vip', '1.00', '50000', '0', 50000, 0, 0, 50000, 0, 50000, 50000, 0, 0, '2026-07-22', 'Active', 'Interne', '0', '50000', 'Espèces', NULL, NULL, ''),
(173, '0173', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Tasse', '4.00', '25000', '12500', 100000, 0, 0, 100000, 0, 100000, 50000, 0, 0, '2026-07-22', 'Active', 'Interne', '0', '100000', 'Espèces', NULL, NULL, ''),
(174, '0174', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DTF 1m', '2.00', '40000', '0', 80000, 0, 0, 80000, 0, 80000, 80000, 0, 0, '2026-07-22', 'Active', 'Interne', '0', '80000', 'Espèces', NULL, NULL, ''),
(175, '0175', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Heating', '100.00', '1000', '0', 100000, 0, 0, 100000, 0, 100000, 100000, 0, 0, '2026-07-22', 'Active', 'Interne', '0', '100000', 'Espèces', NULL, NULL, ''),
(176, '0176', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '2.50', '15000', '10000', 37500, 0, 0, 37500, 0, 37500, 12500, 0, 0, '2026-07-22', 'Active', 'Interne', '0', '37500', 'Espèces', NULL, NULL, ''),
(177, '0177', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '3.00', '10000', '0', 30000, 0, 0, 30000, 0, 30000, 30000, 0, 0, '2026-07-22', 'Active', 'Interne', '0', '30000', 'Espèces', NULL, NULL, ''),
(178, '0178', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DTF A5', '1.00', '5000', '0', 5000, 0, 0, 5000, 0, 5000, 5000, 0, 0, '2026-07-22', 'Active', 'Interne', '0', '5000', 'Espèces', NULL, NULL, ''),
(179, '0179', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '1.00', '10000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, 0, 0, '2026-07-22', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(180, '0180', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DTF A5', '1.00', '5000', '0', 5000, 0, 0, 5000, 0, 5000, 5000, 0, 0, '2026-07-22', 'Active', 'Interne', '0', '5000', 'Espèces', NULL, NULL, ''),
(181, '0181', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'AUTOCOLLANT', '1.00', '15000', '12000', 15000, 0, 0, 15000, 0, 15000, 3000, 3000, 0, '2026-07-23', 'Active', 'Interne', '0', '15000', 'Espèces', NULL, NULL, ''),
(182, '0182', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'AGENDA A5', '1.00', '30000', '18000', 30000, 0, 0, 30000, 0, 30000, 12000, 0, 0, '2026-07-23', 'Active', 'Interne', '0', '30000', 'Espèces', NULL, NULL, ''),
(183, '0183', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '1.00', '10000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, 0, 0, '2026-07-23', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(184, '0184', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '8.94', '15000', '10000', 134100, 0, 0, 134100, 0, 134100, 44700, 0, 0, '2026-07-23', 'Active', 'Interne', '0', '134100', 'Espèces', NULL, NULL, ''),
(185, '0185', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Photocopie', '4.00', '1500', '0', 6000, 0, 0, 6000, 0, 6000, 6000, -4000, 0, '2026-07-23', 'Active', 'Interne', '0', '6000', 'Espèces', NULL, NULL, ''),
(186, '0186', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression sur tshirt', '1.00', '20000', '0', 20000, 0, 0, 20000, 0, 20000, 20000, -10000, 0, '2026-07-23', 'Active', 'Interne', '0', '20000', 'Espèces', NULL, NULL, ''),
(187, '0187', 'Cynthia', 'Cynthia', 'patient', '68469227', 'BACHE ', '114.44', '15000', '10000', 1716600, 0, 0, 1716600, 1350700, 365900, 572200, 0, 0, '2026-07-24', 'Active', 'Interne', '0', '365900', 'Espèces', NULL, NULL, ''),
(188, '0188', 'Cynthia', 'Cynthia', 'Victory', '', 'Tshirt simple', '6.00', '50000', '27000', 300000, 18, 54000, 354000, 154000, 200000, 138000, -90000, 0, '2026-07-24', 'Active', 'Interne', '0', '200000', 'Espèces', NULL, NULL, ''),
(189, '0189', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'IMPRESSION EN COULEUR', '2.00', '5000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, -7000, 0, '2026-07-24', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(190, '0190', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '8.00', '10000', '0', 80000, 0, 0, 80000, 0, 80000, 80000, 0, 0, '2026-07-25', 'Active', 'Interne', '0', '80000', 'Espèces', NULL, NULL, ''),
(191, '0191', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '2.52', '15000', '10000', 37800, 0, 0, 37800, 0, 37800, 12600, 0, 0, '2026-07-25', 'Active', 'Interne', '0', '37800', 'Espèces', NULL, NULL, ''),
(192, '0192', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Anneau', '2.00', '500', '0', 1000, 0, 0, 1000, 0, 1000, 1000, 0, 0, '2026-07-25', 'Active', 'Interne', '0', '1000', 'Espèces', NULL, NULL, ''),
(193, '0193', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '2.60', '15000', '10000', 39000, 0, 0, 39000, 0, 39000, 13000, 0, 0, '2026-07-25', 'Active', 'Interne', '0', '39000', 'Espèces', NULL, NULL, ''),
(194, '0194', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '1.00', '10000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, 0, 0, '2026-07-25', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(195, '0195', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DTF A5', '1.00', '5000', '0', 5000, 0, 0, 5000, 0, 5000, 5000, 0, 0, '2026-07-25', 'Active', 'Interne', '0', '5000', 'Espèces', NULL, NULL, ''),
(196, '0196', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Tshirt simple', '2.00', '35000', '27000', 70000, 0, 0, 70000, 0, 70000, 16000, 0, 0, '2026-07-27', 'Active', 'Interne', '0', '70000', 'Espèces', NULL, NULL, ''),
(197, '0197', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Porte cle', '1.00', '30000', '0', 30000, 0, 0, 30000, 0, 30000, 30000, -10000, 0, '2026-07-27', 'Active', 'Interne', '0', '30000', 'Espèces', NULL, NULL, ''),
(198, '0198', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Porte cle', '1.00', '15000', '0', 15000, 0, 0, 15000, 0, 15000, 15000, 5000, 0, '2026-07-27', 'Active', 'Interne', '0', '15000', 'Espèces', NULL, NULL, ''),
(199, '0199', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'INVITATIONS ', '200.00', '2000', '0', 400000, 0, 0, 400000, 0, 400000, 400000, 600000, 0, '2026-07-27', 'Active', 'Interne', '0', '400000', 'Espèces', NULL, NULL, ''),
(200, '0200', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '1.60', '15000', '10000', 24000, 0, 0, 24000, 0, 24000, 8000, 0, 0, '2026-07-27', 'Active', 'Interne', '0', '24000', 'Espèces', NULL, NULL, ''),
(201, '0201', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Anneau', '2.00', '500', '0', 1000, 0, 0, 1000, 0, 1000, 1000, 0, 0, '2026-07-27', 'Active', 'Interne', '0', '1000', 'Espèces', NULL, NULL, ''),
(202, '0202', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression sur tshirt', '1.00', '10000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, 0, 0, '2026-07-27', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(203, '0203', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Reliure', '1.00', '3000', '0', 3000, 0, 0, 3000, 0, 3000, 3000, 1000, 0, '2026-07-27', 'Active', 'Interne', '0', '3000', 'Espèces', NULL, NULL, ''),
(204, '0204', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Facturier 2 ', '2.00', '30000', '0', 60000, 0, 0, 60000, 0, 60000, 60000, 0, 0, '2026-07-28', 'Active', 'Interne', '0', '60000', 'Espèces', NULL, NULL, ''),
(205, '0205', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '1.20', '15000', '10000', 18000, 0, 0, 18000, 0, 18000, 6000, 0, 0, '2026-07-29', 'Active', 'Interne', '0', '18000', 'Espèces', NULL, NULL, ''),
(206, '0206', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Photocopie', '10.00', '500', '0', 5000, 0, 0, 5000, 0, 5000, 5000, 0, 0, '2026-07-29', 'Active', 'Interne', '0', '5000', 'Espèces', NULL, NULL, ''),
(207, '0207', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '96.00', '15000', '10000', 1440000, 0, 0, 1440000, 1190000, 250000, 480000, 0, 0, '2026-07-29', 'Active', 'Interne', '0', '250000', 'Espèces', NULL, NULL, ''),
(208, '0208', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression N/B', '2.00', '1000', '0', 2000, 0, 0, 2000, 0, 2000, 2000, 0, 0, '2026-07-30', 'Active', 'Interne', '0', '2000', 'Espèces', NULL, NULL, ''),
(209, '0209', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Enveloppe A4', '1.00', '1000', '500', 1000, 0, 0, 1000, 0, 1000, 500, 0, 0, '2026-07-30', 'Active', 'Interne', '0', '1000', 'Espèces', NULL, NULL, ''),
(210, '0210', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '6.00', '15000', '10000', 90000, 0, 0, 90000, 0, 90000, 30000, 0, 0, '2026-07-30', 'Active', 'Interne', '0', '90000', 'Espèces', NULL, NULL, ''),
(211, '0211', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Lacoste', '3.00', '50000', '37000', 150000, 0, 0, 150000, 0, 150000, 39000, -15000, 0, '2026-07-30', 'Active', 'Interne', '0', '150000', 'Espèces', NULL, NULL, ''),
(212, '0212', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression sur tshirt', '1.00', '15000', '0', 15000, 0, 0, 15000, 0, 15000, 15000, -5000, 0, '2026-07-30', 'Active', 'Interne', '0', '15000', 'Espèces', NULL, NULL, ''),
(213, '0213', 'Cynthia', 'Cynthia', '79032914', '', 'Tasse', '1.00', '30000', '12500', 30000, 0, 0, 30000, 10000, 20000, 17500, -5000, 0, '2026-07-30', 'Active', 'Interne', '0', '20000', 'Espèces', NULL, NULL, ''),
(214, '0214', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '1.00', '10000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, 0, 0, '2026-07-30', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(215, '0215', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '3.00', '15000', '10000', 45000, 0, 0, 45000, 0, 45000, 15000, 0, 0, '2026-07-30', 'Active', 'Interne', '0', '45000', 'Espèces', NULL, NULL, ''),
(216, '0216', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'INVITATIONS ', '150.00', '1000', '0', 150000, 0, 0, 150000, 0, 150000, 150000, 600000, 0, '2026-07-31', 'Active', 'Interne', '0', '150000', 'Espèces', NULL, NULL, ''),
(217, '0217', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Enveloppe A4', '1.00', '1000', '500', 1000, 0, 0, 1000, 0, 1000, 500, 0, 0, '2026-07-31', 'Active', 'Interne', '0', '1000', 'Espèces', NULL, NULL, ''),
(218, '0218', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Reliure', '1.00', '3000', '0', 3000, 0, 0, 3000, 0, 3000, 3000, 1000, 0, '2026-07-31', 'Active', 'Interne', '0', '3000', 'Espèces', NULL, NULL, ''),
(219, '0219', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Lacoste', '1.00', '50000', '37000', 50000, 0, 0, 50000, 0, 50000, 13000, -5000, 0, '2026-07-31', 'Active', 'Interne', '0', '50000', 'Espèces', NULL, NULL, ''),
(220, '0220', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Photocopie', '2.00', '500', '0', 1000, 0, 0, 1000, 0, 1000, 1000, 0, 0, '2026-07-31', 'Active', 'Interne', '0', '1000', 'Espèces', NULL, NULL, ''),
(221, '0221', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression N/B', '30.00', '1000', '0', 30000, 0, 0, 30000, 0, 30000, 30000, 0, 0, '2026-08-05', 'Active', 'Interne', '0', '30000', 'Espèces', NULL, NULL, ''),
(222, '0222', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '2.00', '10000', '0', 20000, 0, 0, 20000, 0, 20000, 20000, 0, 0, '2026-08-05', 'Active', 'Interne', '0', '20000', 'Espèces', NULL, NULL, ''),
(223, '0223', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'AUTOCOLLANT', '1.00', '18000', '12000', 18000, 0, 0, 18000, 0, 18000, 6000, 0, 0, '2026-08-05', 'Active', 'Interne', '0', '18000', 'Espèces', NULL, NULL, ''),
(224, '0224', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression N/B', '29.00', '1000', '0', 29000, 0, 0, 29000, 0, 29000, 29000, 0, 0, '2026-08-05', 'Active', 'Interne', '0', '29000', 'Espèces', NULL, NULL, ''),
(225, '0225', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Reliure', '1.00', '3000', '0', 3000, 0, 0, 3000, 0, 3000, 3000, 1000, 0, '2026-08-05', 'Active', 'Interne', '0', '3000', 'Espèces', NULL, NULL, ''),
(226, '0226', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '2.40', '15000', '10000', 36000, 0, 0, 36000, 0, 36000, 12000, 0, 0, '2026-08-05', 'Active', 'Interne', '0', '36000', 'Espèces', NULL, NULL, ''),
(227, '0227', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression sur roll up', '1.00', '80000', '0', 80000, 0, 0, 80000, 0, 80000, 80000, 0, 0, '2026-08-05', 'Active', 'Interne', '0', '80000', 'Espèces', NULL, NULL, ''),
(228, '0228', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression N/B', '138.00', '1000', '0', 138000, 0, 0, 138000, 0, 138000, 138000, 0, 0, '2026-08-05', 'Active', 'Interne', '0', '138000', 'Espèces', NULL, NULL, ''),
(229, '0229', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Reliure', '1.00', '3000', '0', 3000, 0, 0, 3000, 0, 3000, 3000, 1000, 0, '2026-08-05', 'Active', 'Interne', '0', '3000', 'Espèces', NULL, NULL, '');
INSERT INTO `sales` (`id`, `invoice_number`, `operator`, `agent`, `client_name`, `phone`, `medicines`, `quantity`, `unit_cost`, `actual_price`, `tot_htva`, `tva`, `tva_paid`, `tot_tvac`, `debt`, `total_amount`, `total_profit`, `discount`, `commission`, `date`, `status`, `origin`, `guard`, `netcash`, `payment_method`, `ext_paid_amount`, `ext_paid_status`, `cashier`) VALUES
(230, '0230', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression sur tshirt', '1.00', '15000', '0', 15000, 0, 0, 15000, 0, 15000, 15000, -5000, 0, '2026-08-05', 'Active', 'Interne', '0', '15000', 'Espèces', NULL, NULL, ''),
(231, '0231', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression N/B', '4.00', '1000', '0', 4000, 0, 0, 4000, 0, 4000, 4000, 0, 0, '2026-08-05', 'Active', 'Interne', '0', '4000', 'Espèces', NULL, NULL, ''),
(232, '0232', 'Cynthia', 'Cynthia', 'cartas', '79428564', 'AUTOCOLLANT', '10.00', '18000', '12000', 180000, 0, 0, 180000, 80000, 100000, 60000, 0, 0, '2026-08-05', 'Active', 'Interne', '0', '100000', 'Espèces', NULL, NULL, ''),
(233, '0233', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DTF A5', '1.00', '5000', '0', 5000, 0, 0, 5000, 0, 5000, 5000, 0, 0, '2026-08-06', 'Active', 'Interne', '0', '5000', 'Espèces', NULL, NULL, ''),
(234, '0234', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '1.50', '15000', '10000', 22500, 0, 0, 22500, 0, 22500, 7500, 0, 0, '2026-08-10', 'Active', 'Interne', '0', '22500', 'Espèces', NULL, NULL, ''),
(235, '0235', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Tshirt simple', '1.00', '45000', '27000', 45000, 0, 0, 45000, 0, 45000, 18000, -10000, 0, '2026-08-10', 'Active', 'Interne', '0', '45000', 'Espèces', NULL, NULL, ''),
(236, '0236', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '7.20', '15000', '10000', 108000, 0, 0, 108000, 0, 108000, 36000, 0, 0, '2026-08-10', 'Active', 'Interne', '0', '108000', 'Espèces', NULL, NULL, ''),
(237, '0237', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '1.00', '10000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, 0, 0, '2026-08-10', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(238, '0238', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Lacoste', '1.00', '50000', '37000', 50000, 0, 0, 50000, 0, 50000, 13000, -5000, 0, '2026-08-10', 'Active', 'Interne', '0', '50000', 'Espèces', NULL, NULL, ''),
(239, '0239', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'AUTOCOLLANT', '14.47', '18000', '12000', 260460, 0, 0, 260460, 0, 260460, 86820, 0, 0, '2026-08-10', 'Active', 'Interne', '0', '260460', 'Espèces', NULL, NULL, ''),
(240, '0240', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'AUTOCOLLANT', '7.50', '18000', '12000', 135000, 0, 0, 135000, 0, 135000, 45000, 0, 0, '2026-08-10', 'Active', 'Interne', '0', '135000', 'Espèces', NULL, NULL, ''),
(241, '0241', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Tshirt simple', '14.00', '50000', '27000', 700000, 18, 126000, 826000, 0, 826000, 322000, -210000, 0, '2026-08-10', 'Active', 'Interne', '0', '826000', 'Espèces', NULL, NULL, ''),
(242, '0242', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Autocollant18*10', '60.00', '700', '0', 42000, 18, 7560, 49560, 0, 49560, 42000, 0, 0, '2026-08-10', 'Active', 'Interne', '0', '49560', 'Espèces', NULL, NULL, ''),
(243, '0243', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '1.00', '10000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, 0, 0, '2026-08-10', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(244, '0244', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Certificat', '27.00', '4000', '0', 108000, 0, 0, 108000, 0, 108000, 108000, -13500, 0, '2026-08-10', 'Active', 'Interne', '0', '108000', 'Espèces', NULL, NULL, ''),
(245, '0245', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Autocollant A4', '1.00', '10000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, -8000, 0, '2026-08-10', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(246, '0246', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Fixation contravision', '10.00', '5000', '0', 50000, 0, 0, 50000, 0, 50000, 50000, 0, 0, '2026-08-10', 'Active', 'Interne', '0', '50000', 'Espèces', NULL, NULL, ''),
(247, '0247', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Design', '1.00', '50000', '0', 50000, 0, 0, 50000, 0, 50000, 50000, 50000, 0, '2026-08-10', 'Active', 'Interne', '0', '50000', 'Espèces', NULL, NULL, ''),
(248, '0248', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression N/B', '15.00', '1000', '0', 15000, 0, 0, 15000, 0, 15000, 15000, 0, 0, '2026-08-10', 'Active', 'Interne', '0', '15000', 'Espèces', NULL, NULL, ''),
(249, '0249', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Livre plus finition', '3.00', '120000', '0', 360000, 0, 0, 360000, 0, 360000, 360000, -30000, 0, '2026-08-10', 'Active', 'Interne', '0', '360000', 'Espèces', NULL, NULL, ''),
(250, '0250', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '2.00', '15000', '10000', 30000, 0, 0, 30000, 0, 30000, 10000, 0, 0, '2026-08-10', 'Active', 'Interne', '0', '30000', 'Espèces', NULL, NULL, ''),
(251, '0251', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression N/B', '30.00', '1000', '0', 30000, 0, 0, 30000, 0, 30000, 30000, 0, 0, '2026-08-10', 'Supprimée', 'Interne', '0', '30000', 'Espèces', NULL, NULL, ''),
(252, '0252', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression N/B', '20.00', '1000', '0', 20000, 0, 0, 20000, 0, 20000, 20000, 0, 0, '2026-08-10', 'Active', 'Interne', '0', '20000', 'Espèces', NULL, NULL, ''),
(253, '0253', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '1.00', '10000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, 0, 0, '2026-08-10', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(254, '0254', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Contravision1m', '3.04', '35000', '0', 106400, 0, 0, 106400, 0, 106400, 106400, 0, 0, '2026-08-10', 'Active', 'Interne', '0', '106400', 'Espèces', NULL, NULL, ''),
(255, '0255', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '1.00', '10000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, 0, 0, '2026-08-11', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(256, '0256', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'IMPRESSION SUR GILET', '5.00', '13000', '0', 65000, 0, 0, 65000, 0, 65000, 65000, 35000, 0, '2026-08-11', 'Active', 'Interne', '0', '65000', 'Espèces', NULL, NULL, ''),
(257, '0257', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '26.40', '15000', '10000', 396000, 0, 0, 396000, 0, 396000, 132000, 0, 0, '2026-08-11', 'Active', 'Interne', '0', '396000', 'Espèces', NULL, NULL, ''),
(258, '0258', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression N/B', '78.00', '1000', '0', 78000, 0, 0, 78000, 0, 78000, 78000, 0, 0, '2026-08-11', 'Active', 'Interne', '0', '78000', 'Espèces', NULL, NULL, ''),
(259, '0259', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Design', '1.00', '50000', '0', 50000, 0, 0, 50000, 0, 50000, 50000, 50000, 0, '2026-08-11', 'Active', 'Interne', '0', '50000', 'Espèces', NULL, NULL, ''),
(260, '0260', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Lacoste', '1.00', '45000', '37000', 45000, 0, 0, 45000, 0, 45000, 8000, 0, 0, '2026-08-11', 'Active', 'Interne', '0', '45000', 'Espèces', NULL, NULL, ''),
(261, '0261', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression N/B', '65.00', '1000', '0', 65000, 0, 0, 65000, 0, 65000, 65000, 0, 0, '2026-08-18', 'Active', 'Interne', '0', '65000', 'Espèces', NULL, NULL, ''),
(262, '0262', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Anneau', '4.00', '500', '0', 2000, 0, 0, 2000, 0, 2000, 2000, 0, 0, '2026-08-18', 'Active', 'Interne', '0', '2000', 'Espèces', NULL, NULL, ''),
(263, '0263', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Tshirt simple', '12.00', '30000', '27000', 360000, 0, 0, 360000, 0, 360000, 36000, 60000, 0, '2026-08-18', 'Active', 'Interne', '0', '360000', 'Espèces', NULL, NULL, ''),
(264, '0264', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Enveloppe A4', '1.00', '1000', '0', 1000, 0, 0, 1000, 0, 1000, 1000, 0, 0, '2026-08-18', 'Active', 'Interne', '0', '1000', 'Espèces', NULL, NULL, ''),
(265, '0265', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Autocollant18*10', '72.00', '700', '0', 50400, 18, 9072, 59472, 2, 59470, 50400, 0, 0, '2026-08-18', 'Active', 'Interne', '0', '59470', 'Espèces', NULL, NULL, ''),
(266, '0266', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '1.20', '15000', '10000', 18000, 0, 0, 18000, 0, 18000, 6000, 0, 0, '2026-08-18', 'Active', 'Interne', '0', '18000', 'Espèces', NULL, NULL, ''),
(267, '0267', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Anneau', '4.00', '500', '0', 2000, 0, 0, 2000, 0, 2000, 2000, 0, 0, '2026-08-18', 'Active', 'Interne', '0', '2000', 'Espèces', NULL, NULL, ''),
(268, '0268', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '5.06', '15000', '10000', 75900, 0, 0, 75900, 0, 75900, 25300, 0, 0, '2026-08-18', 'Active', 'Interne', '0', '75900', 'Espèces', NULL, NULL, ''),
(269, '0269', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression N/B', '2.00', '1000', '0', 2000, 0, 0, 2000, 0, 2000, 2000, 0, 0, '2026-08-18', 'Active', 'Interne', '0', '2000', 'Espèces', NULL, NULL, ''),
(270, '0270', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Reliure', '3.00', '3000', '0', 9000, 0, 0, 9000, 0, 9000, 9000, 3000, 0, '2026-08-18', 'Active', 'Interne', '0', '9000', 'Espèces', NULL, NULL, ''),
(271, '0271', 'Cynthia', 'Cynthia', 'hamza amababa', '61292657', 'BACHE ', '11.20', '15000', '10000', 168000, 0, 0, 168000, 118000, 50000, 56000, 0, 0, '2026-08-18', 'Active', 'Interne', '0', '50000', 'Espèces', NULL, NULL, ''),
(272, '0272', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'DtfA4', '1.00', '10000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, 0, 0, '2026-08-18', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(273, '0273', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '1.25', '18000', '10000', 22500, 0, 0, 22500, 0, 22500, 10000, -3750, 0, '2026-08-18', 'Active', 'Interne', '0', '22500', 'Espèces', NULL, NULL, ''),
(274, '0274', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Anneau', '4.00', '500', '0', 2000, 0, 0, 2000, 0, 2000, 2000, 0, 0, '2026-08-18', 'Active', 'Interne', '0', '2000', 'Espèces', NULL, NULL, ''),
(275, '0275', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Gouldet', '1.00', '100000', '45000', 100000, 0, 0, 100000, 0, 100000, 55000, -20000, 0, '2026-08-18', 'Active', 'Interne', '0', '100000', 'Espèces', NULL, NULL, ''),
(276, '0276', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Photocopie', '18.00', '2000', '0', 36000, 0, 0, 36000, 0, 36000, 36000, -27000, 0, '2026-08-18', 'Active', 'Interne', '0', '36000', 'Espèces', NULL, NULL, ''),
(277, '0277', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'FOAMBODY A4', '1.00', '100000', '0', 100000, 0, 0, 100000, 0, 100000, 100000, -70000, 0, '2026-08-18', 'Active', 'Interne', '0', '100000', 'Espèces', NULL, NULL, ''),
(278, '0278', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Impression sur tshirt', '1.00', '20000', '0', 20000, 0, 0, 20000, 0, 20000, 20000, -10000, 0, '2026-08-18', 'Active', 'Interne', '0', '20000', 'Espèces', NULL, NULL, ''),
(279, '0279', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Tasse', '1.00', '25000', '12500', 25000, 0, 0, 25000, 0, 25000, 12500, 0, 0, '2026-08-18', 'Active', 'Interne', '0', '25000', 'Espèces', NULL, NULL, ''),
(280, '0280', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '1.00', '15000', '10000', 15000, 0, 0, 15000, 0, 15000, 5000, 0, 0, '2026-08-18', 'Active', 'Interne', '0', '15000', 'Espèces', NULL, NULL, ''),
(281, '0281', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '1.80', '15000', '10000', 27000, 0, 0, 27000, 0, 27000, 9000, 0, 0, '2026-08-18', 'Active', 'Interne', '0', '27000', 'Espèces', NULL, NULL, ''),
(282, '0282', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '3.20', '15000', '10000', 48000, 0, 0, 48000, 0, 48000, 16000, 0, 0, '2026-08-18', 'Active', 'Interne', '0', '48000', 'Espèces', NULL, NULL, ''),
(283, '0283', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '5.00', '18000', '10000', 90000, 0, 0, 90000, 0, 90000, 40000, -15000, 0, '2026-08-18', 'Active', 'Interne', '0', '90000', 'Espèces', NULL, NULL, ''),
(284, '0284', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Design', '1.00', '10000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, 90000, 0, '2026-08-18', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(285, '0285', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Lacoste', '4.00', '50000', '37000', 200000, 0, 0, 200000, 160000, 40000, 52000, -20000, 0, '2026-08-18', 'Active', 'Interne', '0', '40000', 'Espèces', NULL, NULL, ''),
(286, '0286', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Design', '1.00', '10000', '0', 10000, 0, 0, 10000, 0, 10000, 10000, 90000, 0, '2026-08-18', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(287, '0287', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Tshirt simple', '4.00', '30000', '27000', 120000, 0, 0, 120000, 0, 120000, 12000, 20000, 0, '2026-08-18', 'Active', 'Interne', '0', '120000', 'Espèces', NULL, NULL, ''),
(288, '0288', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'BACHE ', '1.00', '10000', '10000', 10000, 0, 0, 10000, 0, 10000, 0, 5000, 0, '2026-08-18', 'Active', 'Interne', '0', '10000', 'Espèces', NULL, NULL, ''),
(289, '0289', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Tasse', '2.00', '25000', '12500', 50000, 0, 0, 50000, 0, 50000, 25000, 0, 0, '2026-08-18', 'Active', 'Interne', '0', '50000', 'Espèces', NULL, NULL, ''),
(290, '0290', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'FOAMBODY A4', '2.00', '100000', '0', 200000, 0, 0, 200000, 0, 200000, 200000, -140000, 0, '2026-08-18', 'Active', 'Interne', '0', '200000', 'Espèces', NULL, NULL, ''),
(291, '0291', 'Cynthia', 'Cynthia', 'Client de passage', '-', 'Cashet simple', '1.00', '30000', '0', 30000, 0, 0, 30000, 0, 30000, 30000, 0, 0, '2026-08-18', 'Active', 'Interne', '0', '30000', 'Espèces', NULL, NULL, '');

-- --------------------------------------------------------

--
-- Structure de la table `sell_type`
--

CREATE TABLE `sell_type` (
  `id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `adder` varchar(100) NOT NULL,
  `cat_date` date NOT NULL DEFAULT current_timestamp(),
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `sell_type`
--

INSERT INTO `sell_type` (`id`, `category`, `adder`, `cat_date`, `status`) VALUES
(34, 'm', 'Isabelle', '2026-06-24', 1),
(35, 'piece', 'Isabelle', '2026-06-24', 1),
(36, 'rouleau', 'Isabelle', '2026-06-24', 1),
(37, 'Forfait', 'Isabelle', '2026-06-25', 1);

-- --------------------------------------------------------

--
-- Structure de la table `sett`
--

CREATE TABLE `sett` (
  `id` int(11) NOT NULL,
  `delivery_adress` varchar(255) NOT NULL,
  `delivery_time` varchar(255) NOT NULL,
  `delivery_cost` varchar(255) NOT NULL,
  `payment_time` varchar(255) NOT NULL,
  `payment_mode` varchar(1000) NOT NULL,
  `payment_conditions` varchar(1000) NOT NULL,
  `property_mention` varchar(1000) NOT NULL,
  `legal_mentions` varchar(1000) NOT NULL,
  `conformity_mention` varchar(1000) NOT NULL,
  `guard_amount` varchar(25) NOT NULL,
  `authorizer` varchar(50) DEFAULT NULL,
  `filer` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `sett`
--

INSERT INTO `sett` (`id`, `delivery_adress`, `delivery_time`, `delivery_cost`, `payment_time`, `payment_mode`, `payment_conditions`, `property_mention`, `legal_mentions`, `conformity_mention`, `guard_amount`, `authorizer`, `filer`) VALUES
(1, 'Bujumbura', '7 jours', '10.000FBU', '30 jours', 'Virement bancaire / BANCOBU', 'Livraison partielle ou complete; Poids:....', 'Camion, moto, velo, pied', 'Nouvelles mentions speciales', 'Marchandises conformes à la celles commandées.', '5000', 'Admin.13456', '$2y$10$17iAw7Z5d2oK8Gxpen3tG.MB8/Y3Yg0vd/JZ9vi/CAjFaToI6EhX2');

-- --------------------------------------------------------

--
-- Structure de la table `stock`
--

CREATE TABLE `stock` (
  `id` int(100) NOT NULL,
  `bar_code` int(11) NOT NULL,
  `medicine_name` varchar(100) NOT NULL,
  `color` varchar(10) NOT NULL DEFAULT '#000000',
  `category` varchar(255) NOT NULL,
  `shelf` text DEFAULT NULL,
  `registered_quantity` decimal(10,2) NOT NULL,
  `used_quantity` decimal(10,2) NOT NULL,
  `actual_quantity` decimal(10,2) NOT NULL,
  `register_date` varchar(100) NOT NULL,
  `expire_date` varchar(100) NOT NULL,
  `sell_type` varchar(100) NOT NULL,
  `origin` varchar(50) NOT NULL,
  `quality` varchar(50) NOT NULL,
  `actual_price` decimal(10,0) NOT NULL,
  `selling_price` decimal(10,0) NOT NULL,
  `profit_price` decimal(10,0) NOT NULL,
  `status` varchar(100) NOT NULL,
  `inventory` decimal(10,2) NOT NULL,
  `storage_conditions` varchar(255) NOT NULL,
  `profil` varchar(100) NOT NULL,
  `totalPrice` decimal(10,0) GENERATED ALWAYS AS (`actual_quantity` * `actual_price`) STORED,
  `guard` decimal(10,0) NOT NULL DEFAULT 0,
  `requisition` decimal(10,2) DEFAULT NULL,
  `seuil` decimal(10,0) NOT NULL DEFAULT 3
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `stock`
--

INSERT INTO `stock` (`id`, `bar_code`, `medicine_name`, `color`, `category`, `shelf`, `registered_quantity`, `used_quantity`, `actual_quantity`, `register_date`, `expire_date`, `sell_type`, `origin`, `quality`, `actual_price`, `selling_price`, `profit_price`, `status`, `inventory`, `storage_conditions`, `profil`, `guard`, `requisition`, `seuil`) VALUES
(1, 1, 'Lacoste', '#000000', 'Produit', '-', '2000.00', '64.00', '1936.00', '13-05-2026', '2026-05-06', 'pièce', '-', 'Standard', '37000', '45000', '8000', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(2, 2, 'Tshirt simple', '#000000', 'Produit', '-', '500.00', '90.00', '410.00', '06-05-2026', '2026-05-06', 'pièce', '', 'Standard', '27000', '35000', '8000', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(3, 3, 'Chapeau', '#000000', 'Produit', '-', '100.00', '1.00', '99.00', '06-05-2026', '2026-05-06', 'pièce', '', 'Standard', '15000', '25000', '10000', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(4, 4, 'Impression sur tshirt', '#000000', 'Service', '-', '1000.00', '36.00', '964.00', '06-05-2026', '2026-05-06', 'pièce', '', 'Standard', '0', '10000', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(5, 5, 'Impression N/B', '#000000', 'Service', '-', '5200.00', '884.00', '4316.00', '06-05-2026', '2026-05-06', 'pièce', '', 'Standard', '0', '1000', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(6, 6, 'IMPRESSION EN COULEUR', '#000000', 'Service', '-', '800.00', '106.00', '694.00', '06-05-2026', '2026-05-06', 'pièce', '', 'Standard', '0', '1500', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(7, 7, 'BACHE ', '#000000', 'Produit', '-', '15000.00', '701.82', '14298.18', '06-05-2026', '2026-05-06', 'm', '', 'Standard', '10000', '15000', '5000', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(8, 8, 'AUTOCOLLANT', '#000000', 'Produit', '-', '6000.00', '98.00', '5902.00', '06-05-2026', '2026-05-06', 'm', '', 'Standard', '12000', '18000', '6000', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(9, 9, 'Carte de visite ', '#000000', 'Produit', '-', '700.00', '180.00', '520.00', '06-05-2026', '2026-05-06', 'pièce', '', 'Standard', '0', '500', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(10, 10, 'Carte de visite avec lamination', '#000000', 'Produit', '-', '2000.00', '0.00', '2000.00', '06-05-2026', '2026-05-06', 'pièce', '', 'Standard', '0', '1000', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(11, 11, 'Depliant', '#000000', 'Produit', '-', '1000.00', '95.00', '905.00', '06-05-2026', '2026-05-06', 'pièce', '', 'Standard', '0', '2000', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(12, 12, 'Roll up complet', '#000000', 'Produit', '-', '100.00', '0.00', '100.00', '06-05-2026', '2026-05-06', 'pièce', '', 'Standard', '245000', '450000', '205000', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(13, 13, 'Tear drop', '#000000', 'Produit', '-', '300.00', '1.00', '299.00', '06-05-2026', '2026-05-06', 'pièce', '', 'Standard', '0', '650000', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(14, 14, 'Gilet original', '#000000', 'Produit', '-', '50.00', '0.00', '50.00', '06-05-2026', '2026-05-06', 'pièce', '', 'Standard', '90000', '150000', '60000', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(15, 15, 'Gilet simple', '#000000', 'Produit', '-', '200.00', '1.00', '199.00', '06-05-2026', '2026-05-06', 'pièce', '', 'Standard', '22500', '35000', '12500', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(16, 16, 'Block note ', '#000000', 'Produit', '-', '780.00', '0.00', '780.00', '06-05-2026', '2026-05-06', 'pièce', '', 'Standard', '0', '45000', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(17, 17, 'Fardes A3', '#000000', 'Produit', '-', '200.00', '0.00', '200.00', '06-05-2026', '2026-05-06', 'pièce', '', 'Standard', '0', '8000', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(18, 18, 'ABS A4', '#000000', 'Produit', '-', '500.00', '0.00', '500.00', '06-05-2026', '2026-05-06', 'pièce', '', 'Standard', '0', '30000', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(19, 19, 'FOAMBODY A4', '#000000', 'Produit', '-', '300.00', '5.00', '295.00', '06-05-2026', '2026-05-06', 'pièce', '', 'Standard', '0', '30000', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(20, 20, 'PLAQUE A4', '#000000', 'Produit', '-', '0.00', '0.00', '0.00', '2026-05-06', '2026-05-06', 'pièce', '', 'Standard', '0', '60000', '0', 'Indisponible', '0.00', '', '', '0', NULL, '3'),
(21, 21, 'AGENDA A4', '#000000', 'Produit', '-', '150.00', '0.00', '150.00', '06-05-2026', '2026-05-06', 'm', '', 'Standard', '30000', '50000', '20000', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(22, 22, 'AGENDA A5', '#000000', 'Produit', '-', '160.00', '1.00', '159.00', '06-05-2026', '2026-05-06', 'pièce', '', 'Standard', '18000', '30000', '12000', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(23, 23, 'INVITATIONS ', '#000000', 'Produit', '-', '1000.00', '700.00', '300.00', '06-05-2026', '2026-05-06', 'm', '', 'Standard', '0', '5000', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(24, 24, 'Calendrier de table ', '#000000', 'Produit', '-', '800.00', '0.00', '800.00', '06-05-2026', '2026-05-06', 'pièce', '', 'Standard', '0', '45000', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(25, 25, 'Tasse', '#000000', 'Produit', '-', '50.00', '9.00', '41.00', '06-05-2026', '2026-05-06', 'rouleau', '', 'Standard', '12500', '25000', '12500', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(26, 26, 'Gouldet', '#000000', 'Produit', '-', '10.00', '2.00', '8.00', '06-05-2026', '2026-05-06', 'pièce', '', 'Standard', '45000', '80000', '35000', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(27, 27, 'Badge vip', '#000000', 'Produit', '-', '1000.00', '14.00', '986.00', '06-05-2026', '2026-05-06', 'pièce', '', 'Standard', '0', '20000', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(28, 28, 'Pvc', '#000000', 'Produit', '-', '40.00', '3.00', '37.00', '06-05-2026', '2026-05-06', 'pièce', '', 'Standard', '0', '5000', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(29, 29, 'Photocopie', '#000000', 'Produit', '-', '500.00', '443.00', '57.00', '02-06-2026', '2026-05-06', 'pièce', '', 'Standard', '0', '500', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(30, 30, 'Porte cle', '#000000', 'Produit', '-', '100.00', '5.00', '95.00', '07-05-2026', '2026-05-07', 'pièce', '', 'Standard', '0', '20000', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(31, 31, 'Stylo personnalise', '#000000', 'Produit', '-', '50.00', '3.00', '47.00', '07-05-2026', '2026-05-07', 'pièce', '', 'Standard', '3000', '8000', '5000', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(32, 32, 'DtfA4', '#000000', 'Produit', '-', '500.00', '129.00', '371.00', '07-05-2026', '2026-05-07', 'pièce', '', 'Standard', '0', '10000', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(33, 33, 'DTF 1m', '#000000', 'Produit', '-', '200.00', '11.00', '189.00', '07-05-2026', '2026-05-07', 'pièce', '', 'Standard', '0', '40000', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(34, 34, 'Backlight1m', '#000000', 'Produit', '-', '300.00', '6.86', '293.14', '07-05-2026', '2026-05-07', 'pièce', '', 'Standard', '15000', '35000', '20000', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(35, 35, 'Foambody 1msur 50', '#000000', 'Produit', '-', '1000.00', '4.00', '996.00', '11-05-2026', '2026-05-11', 'pièce', '', 'Standard', '0', '90000', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(36, 36, 'Contravision1m', '#000000', 'Produit', '-', '2000.00', '29.80', '1970.20', '11-05-2026', '2026-05-11', 'pièce', '', 'Standard', '0', '35000', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(37, 37, 'Fixation contravision 3.6', '#000000', 'Service', '-', '20.00', '3.60', '16.40', '11-05-2026', '2026-05-11', 'pièce', '', 'Standard', '0', '7000', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(38, 38, 'RESTE LACOSTE 45', '#000000', 'Produit', '-', '50.00', '1.00', '49.00', '11-05-2026', '2026-05-11', 'pièce', '', 'Standard', '0', '725000', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(39, 39, 'Cutting', '#000000', 'Service', '-', '2000.00', '3.00', '1997.00', '11-05-2026', '2026-05-11', 'pièce', '', 'Standard', '0', '3000', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(40, 40, 'Reste back ligh 4m du6/5/2026', '#000000', 'Produit', '-', '1.00', '1.00', '0.00', '13-05-2026', '2026-05-13', 'm', '', 'Standard', '0', '80000', '0', 'Indisponible', '0.00', '', '', '0', '0.00', '3'),
(41, 41, 'Fixation autocollant 1.87', '#000000', 'Service', '-', '10.00', '1.00', '9.00', '15-05-2026', '2026-05-15', 'm', '', 'Standard', '0', '20000', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(42, 42, 'Anneau', '#000000', 'Produit', '-', '1010.00', '196.00', '814.00', '25-05-2026', '2026-05-15', 'pièce', '', 'Standard', '0', '500', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(43, 43, 'Cadre A4', '#000000', 'Service', '-', '50.00', '4.00', '46.00', '15-05-2026', '2026-05-15', 'pièce', '', 'Standard', '13000', '40000', '27000', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(44, 44, 'Reste chris', '#000000', 'Produit', '-', '20.00', '1.00', '19.00', '15-05-2026', '2026-05-15', 'pièce', '', 'Standard', '0', '105000', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(45, 45, 'Facturier', '#000000', 'Produit', '-', '3.00', '6.00', '0.00', '18-05-2026', '2026-05-18', 'pièce', '-', 'Standard', '20000', '30000', '10000', 'Indisponible', '0.00', '', '', '0', '0.00', '3'),
(47, 47, 'Autocollant A4', '#000000', 'Produit', '-', '1000.00', '421.00', '579.00', '19-05-2026', '2026-05-19', 'pièce', '', 'Standard', '0', '2000', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(48, 48, 'IMPRESSION SUR GILET', '#000000', 'Produit', '-', '1000.00', '62.00', '938.00', '19-05-2026', '2026-05-19', 'pièce', '', 'Standard', '0', '20000', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(49, 49, 'BRANDING', '#000000', 'Produit', '-', '1.00', '1.00', '0.00', '19-05-2026', '2026-05-19', 'pièce', '', 'Standard', '0', '10000', '0', 'Indisponible', '0.00', '', '', '0', '0.00', '3'),
(50, 50, 'DTF A5', '#000000', 'Produit', '-', '101.00', '9.00', '92.00', '25-05-2026', '2026-05-20', 'pièce', '', 'Standard', '0', '5000', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(51, 51, 'Reliure', '#000000', 'Service', '-', '3.00', '9.00', '-6.00', '21-05-2026', '2026-05-21', 'pièce', '', 'Standard', '0', '4000', '0', 'Indisponible', '0.00', '', '', '0', '0.00', '3'),
(52, 52, 'Commande fanny avec reduction', '#000000', 'Produit', '-', '1.00', '1.00', '0.00', '22-05-2026', '2026-05-22', 'pièce', '', 'Standard', '0', '400000', '0', 'Indisponible', '0.00', '', '', '0', '0.00', '3'),
(53, 53, 'Scan', '#000000', 'Produit', '-', '200.00', '36.00', '164.00', '25-05-2026', '2026-05-25', 'pièce', '', 'Standard', '0', '500', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(54, 54, 'Bache1.5', '#000000', 'Produit', '-', '100.00', '1.00', '99.00', '25-05-2026', '2026-05-25', 'm', '', 'Standard', '10000', '41000', '31000', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(55, 55, 'Bache3m sur1m avec 6anneau cnc', '#000000', 'Produit', '-', '13.15', '1.00', '12.15', '24-06-2026', '2026-05-26', 'pièce', '', 'Standard', '0', '67000', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(56, 56, 'IMPRESSION ROLL UP', '#000000', 'Produit', '-', '1.00', '1.00', '0.00', '26-05-2026', '2026-05-26', 'pièce', '', 'Standard', '0', '100000', '0', 'Indisponible', '0.00', '', '', '0', '0.00', '3'),
(57, 57, 'PARAPLUIE', '#000000', 'Produit', '-', '30.00', '30.00', '0.00', '28-05-2026', '2026-05-28', 'm', '', 'Standard', '23000', '45000', '22000', 'Indisponible', '0.00', '', '', '0', '0.00', '3'),
(59, 59, 'Impression sur foulard', '#000000', 'Service', '-', '55.00', '56.00', '-1.00', '30-05-2026', '2026-05-30', 'm', '', 'Standard', '0', '5000', '0', 'Indisponible', '0.00', '', '', '0', '0.00', '3'),
(60, 60, 'Cadre A2', '#000000', 'Produit', '-', '2.00', '1.00', '1.00', '02-06-2026', '2026-06-02', 'pièce', '', 'Standard', '130000', '250000', '120000', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(61, 61, 'Autocollant18*10', '#000000', 'Produit', '-', '335.00', '332.00', '3.00', '18-08-2026', '2026-06-04', 'm', '', 'Standard', '0', '700', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(62, 62, 'Cadre photo A1', '#000000', 'Produit', '-', '5.00', '1.00', '4.00', '09-06-2026', '2026-06-05', 'pièce', '', 'Standard', '150000', '250000', '100000', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(63, 63, 'Heating chapeau12,lacoste 12', '#000000', 'Produit', '-', '1.00', '1.00', '0.00', '08-06-2026', '2026-06-08', 'm', '', 'Standard', '0', '30000', '0', 'Indisponible', '0.00', '', '', '0', '0.00', '3'),
(64, 64, 'Enveloppe A4', '#000000', 'Service', '-', '10.00', '11.00', '-1.00', '09-06-2026', '2026-06-09', 'pièce', '', 'Standard', '500', '1000', '500', 'Indisponible', '0.00', '', '', '0', '0.00', '3'),
(65, 65, 'Plastification A4', '#000000', 'Produit', '-', '1.00', '1.00', '0.00', '12-06-2026', '2026-06-12', 'pièce', '', 'Standard', '1000', '2000', '1000', 'Indisponible', '0.00', '', '', '0', '0.00', '3'),
(66, 66, 'AJOUT AUTOCOLLANT A4 400PIECE', '#000000', 'Produit', '-', '1.00', '1.00', '0.00', '12-06-2026', '2026-06-12', 'pièce', '', 'Standard', '0', '50000', '0', 'Indisponible', '0.00', '', '', '0', '0.00', '3'),
(67, 67, 'Fixation contravision', '#000000', 'Produit', '-', '32.00', '26.30', '5.70', '17-06-2026', '2026-06-17', 'm', '', 'Standard', '0', '5000', '0', 'Disponible', '0.00', '', '', '0', '0.00', '3'),
(68, 68, 'Design', '#000000', 'Service', '-', '8.21', '13.21', '-5.00', '2026-06-24', '2026-06-17', 'pièce', '', 'Standard', '0', '100000', '0', 'Indisponible', '0.00', '', '', '0', '0.00', '3'),
(69, 69, 'Carnet de recu', '#000000', 'Produit', '-', '12.00', '12.00', '0.00', '24-06-2026', '2026-06-24', 'pièce', '', 'Standard', '10000', '12000', '2000', 'Indisponible', '0.00', '', '', '0', '0.00', '3'),
(70, 70, 'Pin', '#000000', 'Produit', '-', '2.00', '2.00', '0.00', '24-06-2026', '2026-06-24', 'pièce', '', 'Standard', '3000', '10000', '7000', 'Indisponible', '0.00', '', '', '0', '0.00', '3'),
(71, 71, 'IMPRESSION SUR CASQUE', '#000000', 'Produit', '-', '10.00', '10.00', '0.00', '24-06-2026', '2026-06-24', 'pièce', '', 'Standard', '0', '5000', '0', 'Indisponible', '0.00', '', '', '0', '0.00', '3'),
(72, 72, 'Impression sur corde', '#000000', 'Service', '-', '10000.00', '4.00', '9996.00', '2026-06-25', '2026-06-25', 'piece', '', 'Standard', '0', '4000', '0', 'Indisponible', '0.00', '', '', '0', NULL, '3'),
(73, 73, 'New prod', '#ff0000', 'Produit', '-', '0.00', '0.00', '0.00', '2026-06-25', '2026-06-25', 'm', '&', 'Standard', '1000', '2000', '1000', 'Indisponible', '0.00', '', '', '0', NULL, '3'),
(74, 74, 'New service', '#ffff00', 'Service', '-', '10000.00', '0.00', '10000.00', '2026-06-25', '2026-06-25', 'piece', '-', 'Standard', '0', '50000', '0', 'Indisponible', '0.00', '', '', '0', NULL, '3'),
(75, 75, 'Heating', '#000000', 'Service', '-', '10000.00', '188.00', '9812.00', '2026-06-29', '2026-06-29', 'piece', '', 'Standard', '0', '1000', '0', 'Indisponible', '0.00', '', '', '0', NULL, '3'),
(76, 76, 'Design 1', '#000000', 'Service', '-', '10000.00', '1.00', '9999.00', '2026-07-02', '2026-07-02', 'piece', '', 'Standard', '0', '14000', '0', 'Indisponible', '0.00', '', '', '0', NULL, '3'),
(77, 77, 'Tshirt 23lacoste15chapeau6 impression9', '#000000', 'Service', '-', '10000.00', '1.00', '9999.00', '2026-07-06', '2026-07-06', 'm', '', 'Standard', '0', '1708000', '0', 'Indisponible', '0.00', '', '', '0', NULL, '3'),
(78, 78, 'Back light 1.2et 1 +design', '#000000', 'Service', '-', '10000.00', '1.00', '9999.00', '2026-07-06', '2026-07-06', 'piece', '', 'Standard', '0', '202500', '0', 'Indisponible', '0.00', '', '', '0', NULL, '3'),
(79, 79, 'Xstand', '#000000', 'Service', '-', '10000.00', '4.00', '9996.00', '2026-07-06', '2026-07-06', 'piece', '', 'Standard', '0', '150000', '0', 'Indisponible', '0.00', '', '', '0', NULL, '3'),
(80, 80, 'Porte badge', '#000000', 'Service', '-', '10000.00', '2.00', '9998.00', '2026-07-08', '2026-07-08', 'piece', '', 'Standard', '2500', '4000', '1500', 'Indisponible', '0.00', '', '', '0', NULL, '3'),
(81, 81, 'Branding pancarte de 2m sur 2m', '#000000', 'Service', '-', '10000.00', '1.00', '9999.00', '2026-07-09', '2026-07-09', 'piece', '', 'Standard', '0', '100000', '0', 'Indisponible', '0.00', '', '', '0', NULL, '3'),
(82, 82, 'Finition Avec spiral', '#000000', 'Service', '-', '10000.00', '1.00', '9999.00', '2026-07-10', '2026-07-10', 'piece', '', 'Standard', '0', '20000', '0', 'Indisponible', '0.00', '', '', '0', NULL, '3'),
(83, 83, 'Stylo personnalise vip', '#000000', 'Service', '-', '10000.00', '65.00', '9935.00', '2026-07-10', '2026-07-10', 'piece', '', 'Standard', '2000', '6000', '4000', 'Indisponible', '0.00', '', '', '0', NULL, '3'),
(84, 84, 'Papier auto A4 ETIQUETTE', '#000000', 'Service', '-', '10000.00', '30.00', '9970.00', '2026-07-13', '2026-07-13', 'piece', '', 'Standard', '0', '600', '0', 'Indisponible', '0.00', '', '', '0', NULL, '3'),
(85, 85, 'Autocollant non imprimé', '#000000', 'Service', '-', '10000.00', '10.00', '9990.00', '2026-07-14', '2026-07-14', 'm', '', 'Standard', '0', '10000', '0', 'Indisponible', '0.00', '', '', '0', NULL, '3'),
(86, 86, 'Cashet simple', '#000000', 'Service', '-', '10000.00', '2.00', '9998.00', '2026-07-16', '2026-07-16', 'piece', '', 'Standard', '20000', '30000', '10000', 'Indisponible', '0.00', '', '', '0', NULL, '3'),
(87, 87, 'Livre plus finition', '#000000', 'Service', '-', '10000.00', '10.00', '9990.00', '2026-07-16', '2026-07-16', 'piece', '', 'Standard', '0', '110000', '0', 'Indisponible', '0.00', '', '', '0', NULL, '3'),
(88, 88, 'Certificat', '#000000', 'Service', '-', '10000.00', '127.00', '9873.00', '2026-07-17', '2026-07-17', 'm', '', 'Standard', '1000', '3500', '2500', 'Indisponible', '0.00', '', '', '0', NULL, '3'),
(89, 89, 'Parapluie vip', '#000000', 'Service', '-', '10000.00', '1.00', '9999.00', '2026-07-22', '2026-07-22', 'piece', '', 'Standard', '0', '50000', '0', 'Indisponible', '0.00', '', '', '0', NULL, '3'),
(90, 90, 'Facturier 2 ', '#000000', 'Service', '-', '10000.00', '2.00', '9998.00', '2026-07-28', '2026-07-28', 'piece', '', 'Standard', '0', '30000', '0', 'Indisponible', '0.00', '', '', '0', NULL, '3'),
(91, 91, 'Impression sur roll up', '#000000', 'Service', '-', '10000.00', '1.00', '9999.00', '2026-08-05', '2026-08-05', 'piece', '', 'Standard', '0', '80000', '0', 'Indisponible', '0.00', '', '', '0', NULL, '3');

-- --------------------------------------------------------

--
-- Structure de la table `sys_control`
--

CREATE TABLE `sys_control` (
  `id` int(11) NOT NULL,
  `last_time` datetime DEFAULT NULL,
  `last_check_ts` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `sys_control`
--

INSERT INTO `sys_control` (`id`, `last_time`, `last_check_ts`) VALUES
(1, '2026-10-02 10:16:19', 1790928979);

-- --------------------------------------------------------

--
-- Structure de la table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `ticket_no` varchar(50) NOT NULL,
  `client` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `priority` varchar(25) NOT NULL DEFAULT 'Medium',
  `created_by` varchar(25) NOT NULL,
  `assigned_to` varchar(25) NOT NULL,
  `contributor` varchar(25) DEFAULT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp(),
  `updated_at` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `due_date` varchar(25) DEFAULT NULL,
  `status` enum('Nouveau','Consulté','En cours','Résolu','Annulé') NOT NULL DEFAULT 'Nouveau',
  `sound_status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `temp`
--

CREATE TABLE `temp` (
  `id` int(11) NOT NULL,
  `doc_number` varchar(100) NOT NULL,
  `doc_type` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `temp`
--

INSERT INTO `temp` (`id`, `doc_number`, `doc_type`) VALUES
(115, 'PROF-0006', 'Proforma');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(100) NOT NULL,
  `profil` varchar(255) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `password` varchar(100) NOT NULL,
  `secret_code` varchar(100) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `f_name` varchar(100) DEFAULT NULL,
  `l_name` varchar(100) DEFAULT NULL,
  `telephone` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `adress` varchar(100) NOT NULL,
  `level` varchar(100) NOT NULL,
  `salary` decimal(10,0) NOT NULL DEFAULT 0,
  `status` varchar(100) NOT NULL,
  `session_id` varchar(1000) NOT NULL,
  `admission` date NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(25) DEFAULT NULL,
  `token_expiry` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `profil`, `user_name`, `password`, `secret_code`, `category`, `f_name`, `l_name`, `telephone`, `email`, `adress`, `level`, `salary`, `status`, `session_id`, `admission`, `reset_token`, `token_expiry`) VALUES
(92, 'prod_6a3be731b137f.jpg', 'Isabelle', '$2y$10$iGbrn6K0K2mAa9/WcSdeveiMM.ob6maga12BFgRgULvVKFf7WZd7.', '$2y$10$eQa73JyahWI1Z7ljVnD90.fkjvp0.HpyZQlnGjnn2IIkYxm1NGf9e', 'Admin', 'NIMUBONA', 'Alexis', '69788400', 'n.alexisonline@gmail.com', 'Gihosha', 'Dev', '0', 'En ligne', '794ridmmhms9ue5b095og02pt4', '2025-04-06', '4ca02e179a546e460aa5f7530', '2026-03-01 20:20:46'),
(145, '', 'Cynthia', '$2y$10$ARzzKLpp2q.O44FVDr9CVu3j6w86l.WkGGaniQLI27idx4pIOZc/.', '$2y$10$CD1oT1q9G.mi7ugqy.2ip./KbE953qczSsF.rZNiMK2Jxr/mJ/5Ny', 'Op', 'N', 'Nelly', '67052914', 'user@gmail.com', '', '', '0', '', '', '2026-06-15', NULL, '2026-06-15 07:22:00'),
(146, '', 'Venuste', '$2y$10$ZPhiXmfyh5tOa4D/ePADO.aLfMqqhfatIuC.FPkfv3CgSjV2cFr/S', '$2y$10$RRBSTYxvxlfa7SYjTCKTEe0XbOfn3Vk67MPh5ocRJowcdBdiswmjS', 'Admin', '', '', '', '', '', '', '0', 'Disponible', '', '2026-06-29', NULL, '2026-06-29 14:18:15'),
(147, '', 'Dev', '$2y$10$wpz0w9id14tjVyEAfFqBQu4t61Qzg6Rh8WzQ1T3SbCpLGvr6xjFZ2', '$2y$10$PtyO0KdzWml5gom7OdnEAePwq/Lmzohq1KpZjAQ/./ycj8S99JWiu', 'Admin', '', '', '69788400', 'nimubona71@gmail.com', 'Rushubi', 'BAC+V', '0', '', '', '2026-06-29', NULL, '2026-06-29 14:19:23');

-- --------------------------------------------------------

--
-- Structure de la table `user_sound_settings`
--

CREATE TABLE `user_sound_settings` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `sound_status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Index pour les tables déchargées
--

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
  ADD PRIMARY KEY (`id`);

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
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `cash_out`
--
ALTER TABLE `cash_out`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `cat_departments`
--
ALTER TABLE `cat_departments`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `cat_expens`
--
ALTER TABLE `cat_expens`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `cat_partners`
--
ALTER TABLE `cat_partners`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `cat_prod`
--
ALTER TABLE `cat_prod`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `cat_task`
--
ALTER TABLE `cat_task`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `cat_users`
--
ALTER TABLE `cat_users`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `debts`
--
ALTER TABLE `debts`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `debts_paid`
--
ALTER TABLE `debts_paid`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `deleted_sales`
--
ALTER TABLE `deleted_sales`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `discounts`
--
ALTER TABLE `discounts`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `entries`
--
ALTER TABLE `entries`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `external_payments`
--
ALTER TABLE `external_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `agent` (`agent`);

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
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `logintos`
--
ALTER TABLE `logintos`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `mutuelle_details`
--
ALTER TABLE `mutuelle_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_number` (`invoice_number`),
  ADD KEY `partner_id` (`partner_id`);

--
-- Index pour la table `on_exp`
--
ALTER TABLE `on_exp`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `on_hold`
--
ALTER TABLE `on_hold`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `on_order`
--
ALTER TABLE `on_order`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `on_quot`
--
ALTER TABLE `on_quot`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `partners`
--
ALTER TABLE `partners`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `quantity_corrections`
--
ALTER TABLE `quantity_corrections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_medicine_name` (`medicine_name`),
  ADD KEY `idx_corrected_by` (`corrected_by`),
  ADD KEY `idx_correction_date` (`correction_date`);

--
-- Index pour la table `report_comments`
--
ALTER TABLE `report_comments`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`);

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
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `sys_control`
--
ALTER TABLE `sys_control`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `temp`
--
ALTER TABLE `temp`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `user_sound_settings`
--
ALTER TABLE `user_sound_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `arch_types`
--
ALTER TABLE `arch_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `banks`
--
ALTER TABLE `banks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT pour la table `caisse`
--
ALTER TABLE `caisse`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT pour la table `cash_in`
--
ALTER TABLE `cash_in`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT pour la table `cash_out`
--
ALTER TABLE `cash_out`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `cat_prod`
--
ALTER TABLE `cat_prod`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=138;

--
-- AUTO_INCREMENT pour la table `cat_task`
--
ALTER TABLE `cat_task`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `cat_users`
--
ALTER TABLE `cat_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `chats`
--
ALTER TABLE `chats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `debts`
--
ALTER TABLE `debts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT pour la table `debts_paid`
--
ALTER TABLE `debts_paid`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `entries`
--
ALTER TABLE `entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=667;

--
-- AUTO_INCREMENT pour la table `external_payments`
--
ALTER TABLE `external_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `licenses`
--
ALTER TABLE `licenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `logintos`
--
ALTER TABLE `logintos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=293;

--
-- AUTO_INCREMENT pour la table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1099;

--
-- AUTO_INCREMENT pour la table `mutuelle_details`
--
ALTER TABLE `mutuelle_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `on_exp`
--
ALTER TABLE `on_exp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

--
-- AUTO_INCREMENT pour la table `on_hold`
--
ALTER TABLE `on_hold`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3142;

--
-- AUTO_INCREMENT pour la table `on_order`
--
ALTER TABLE `on_order`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;

--
-- AUTO_INCREMENT pour la table `on_quot`
--
ALTER TABLE `on_quot`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=158;

--
-- AUTO_INCREMENT pour la table `partners`
--
ALTER TABLE `partners`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=161;

--
-- AUTO_INCREMENT pour la table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `quantity_corrections`
--
ALTER TABLE `quantity_corrections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `report_comments`
--
ALTER TABLE `report_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=292;

--
-- AUTO_INCREMENT pour la table `sell_type`
--
ALTER TABLE `sell_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT pour la table `sett`
--
ALTER TABLE `sett`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT pour la table `sys_control`
--
ALTER TABLE `sys_control`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `temp`
--
ALTER TABLE `temp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT pour la table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1499;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT pour la table `user_sound_settings`
--
ALTER TABLE `user_sound_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
