<?php
/**
 * router.php
 * Point d'entrée unique — toutes les URLs passent ici.
 * Vérifie l'authentification + les permissions avant d'inclure la page.
 */

// ============================================================
// DÉBOGAGE (retirer en production)
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================================
// SESSION
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================
// CHARGEMENT DES DÉPENDANCES
// ============================================================
require_once __DIR__ . "/dash/template/permissions.php";
require_once __DIR__ . "/dash/template/sidebar_config.php";

if (!defined('ROUTER_LOADED')) {
    define('ROUTER_LOADED', true);
}

if (!defined('APP_BASE_PATH')) {
    $scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    define('APP_BASE_PATH', $scriptDirectory === '/' || $scriptDirectory === '.' ? '' : rtrim($scriptDirectory, '/'));
}

if (!function_exists('app_url')) {
    function app_url(string $path = ''): string
    {
        return APP_BASE_PATH . '/' . ltrim($path, '/');
    }
}

// ============================================================
// SIMULATION UTILISATEUR (à remplacer par la session réelle)
// ============================================================
// En production, remplacez ce bloc par :
//   if (!isset($_SESSION["user_id"])) { header("Location: /login"); exit; }
//   $user = charger_user_depuis_bdd($_SESSION["user_id"]);
//
$TEST_ROLE = $_GET['role'] ?? ($_SESSION['role'] ?? 'directeur_general');

// Sécurité : rôle valide ?
global $ROLES;
if (!isset($ROLES[$TEST_ROLE])) {
    $TEST_ROLE = 'directeur_general';
}

$user = [
    "utilisateur_id" => 1,
    "nom_complet"    => "Jean Ndayishimiye",
    "nom_utilisateur"=> "jndayi",
    "role_system"    => $TEST_ROLE,
    "statut"         => "actif",
    "avatar"         => "",
];

// ============================================================
// TABLE DE ROUTAGE
// URL propre  →  [fichier, permission requise, titre]
// ============================================================
$ROUTES = [

    // ---------- Dashboard ----------
    "accueil"                     => ["./dash/admin/index.php",              "dashboard.view",         "Tableau de bord"],
    "dashboard"                   => ["./dash/admin/index.php",                  "dashboard.view",         "Tableau de bord"],

    // ---------- Stock ----------
    "stock"                       => ["./dash/admin/stocks.php",                 "stock.view",             "Gestion de stock"],
    "stock/mouvements"            => ["./dash/admin/stock_mouvements.php",       "stock.mouvements",       "Mouvements de stock"],
    "stock/alertes"               => ["./dash/admin/stock_alerts.php",           "stock.alertes",          "Alertes stock"],
    "stock/reconciliation"        => ["./dash/admin/stock_reconciliation.php",   "stock.reconciliation",   "Réconciliation stock"],

    // ---------- Flux ----------
    "flux"                        => ["./dash/admin/flux.php",                   "flux.view",              "Flux"],
    "flux/materiels"              => ["./dash/admin/flux_materiels.php",         "flux.view",              "Flux matériels"],
    "flux/financiers"             => ["./dash/admin/flux_financiers.php",        "flux.view",              "Flux financiers"],

    // ---------- Caisse ----------
    "caisse"                      => ["./dash/admin/caisse_transactions.php",     "caisse.view",            "Caisse"],
    "caisse/transactions"         => ["./dash/admin/caisse_transactions.php",     "caisse.view",            "Transactions caisse"],
    "caisse/rapports"             => ["./dash/admin/caisse_rapports.php",         "caisse.rapport",         "Rapports financiers"],
    "caisse/etats"                => ["./dash/admin/etats_comptables.php",        "caisse.rapport",         "États comptables"],

    // ---------- Banque ----------
    "banque"                      => ["./dash/admin/banque_comptes.php",          "banque.view",            "Comptes bancaires"],
    "banque/comptes"              => ["./dash/admin/banque_comptes.php",          "banque.view",            "Comptes bancaires"],
    "banque/virements"            => ["./dash/admin/banque_virements.php",        "banque.create",          "Virements"],
    "banque/rapprochement"        => ["./dash/admin/banque_rapprochement.php",    "banque.rapprochement",   "Rapprochement"],

    // ---------- Factures ----------
    "factures"                    => ["./dash/admin/factures_liste.php",          "factures.view",          "Factures"],
    "factures/liste"              => ["./dash/admin/factures_liste.php",          "factures.view",          "Liste des factures"],
    "factures/creer"              => ["./dash/admin/factures_create.php",         "factures.create",        "Créer une facture"],
    "factures/envoyer"            => ["./dash/admin/factures_envoyer.php",        "factures.create",        "Envoyer une facture"],
    "factures/archives"           => ["./dash/admin/factures_archives.php",       "factures.archiver",      "Archives factures"],

    // ---------- Commandes ----------
    "commandes"                   => ["./dash/admin/commandes_clients.php",       "commandes.view",         "Commandes"],
    "commandes/clients"           => ["./dash/admin/commandes_clients.php",       "commandes.clients",      "Commandes clients"],
    "commandes/fournisseurs"      => ["./dash/admin/commandes_fournisseurs.php",  "commandes.fournisseurs", "Commandes fournisseurs"],
    "commandes/nouvelle"          => ["./dash/admin/commande_nouvelle.php",       "commandes.create",       "Nouvelle commande"],

    // ---------- Livraisons ----------
    "livraisons"                  => ["./dash/admin/livraisons_liste.php",        "livraisons.view",        "Livraisons"],
    "livraisons/liste"            => ["./dash/admin/livraisons_liste.php",        "livraisons.view",        "Liste bordereaux"],
    "livraisons/creer"            => ["./dash/admin/livraison_create.php",        "livraisons.create",      "Créer un bordereau"],
    "livraisons/suivi"            => ["./dash/admin/livraison_suivi.php",         "livraisons.view",        "Suivi livraisons"],
    "livraisons/impression"       => ["./dash/admin/livraison_print.php",         "livraisons.print",       "Impression"],

    // ---------- Offres ----------
    "offres"                      => ["./dash/admin/offres_liste.php",            "offres.view",            "Offres commerciales"],
    "offres/liste"                => ["./dash/admin/offres_liste.php",            "offres.view",            "Liste des offres"],
    "offres/creer"                => ["./dash/admin/offre_create.php",            "offres.create",          "Créer une offre"],
    "offres/suivi"                => ["./dash/admin/offres_suivi.php",            "offres.edit",            "Suivi propositions"],

    // ---------- Clients ----------
    "clients"                     => ["./dash/admin/clients_liste.php",           "clients.view",           "Clients"],
    "clients/liste"               => ["./dash/admin/clients_liste.php",           "clients.view",           "Liste clients"],
    "clients/ajouter"             => ["./dash/admin/client_add.php",              "clients.create",         "Ajouter client"],
    "clients/vip"                 => ["./dash/admin/clients_vip.php",             "clients.view",           "Clients VIP"],

    // ---------- Fournisseurs ----------
    "fournisseurs"                => ["./dash/admin/fournisseurs_liste.php",      "fournisseurs.view",      "Fournisseurs"],
    "fournisseurs/liste"          => ["./dash/admin/fournisseurs_liste.php",      "fournisseurs.view",      "Liste fournisseurs"],
    "fournisseurs/ajouter"        => ["./dash/admin/fournisseur_add.php",         "fournisseurs.create",    "Ajouter fournisseur"],

    // ---------- Promotions / Fidélisation ----------
    "promotions"                  => ["./dash/admin/promotions.php",              "promotions.view",        "Promotions VIP"],
    "anniversaires"               => ["./dash/admin/anniversaires.php",           "anniversaires.view",     "Anniversaires clients"],

    // ---------- Opérations ----------
    "operations"                  => ["./dash/admin/operations.php",              "operations.view",        "Suivi des opérations"],
    "rapports"                    => ["./dash/admin/rapports.php",                "rapports.view",          "Rapports"],
    "rapports/financier"          => ["./dash/admin/rapports_financier.php",      "rapports.financier",     "Rapports financiers"],
    "rapports/stock"              => ["./dash/admin/rapports_stock.php",          "rapports.stock",         "Rapports stock"],
    "rapports/commercial"         => ["./dash/admin/rapports_commercial.php",     "rapports.commercial",    "Rapports commerciaux"],
    "rapports/rh"                 => ["./dash/admin/rapports_rh.php",             "rapports.rh",            "Rapports RH"],

    // ---------- Communication ----------
    "messagerie"                  => ["./dash/admin/messagerie.php",              "messagerie.view",        "Messagerie interne"],
    "rappels"                     => ["./dash/admin/rappels.php",                 "rappels.view",           "Rappels & alertes"],

    // ---------- Administration ----------
    "utilisateurs"                => ["./dash/admin/utilisateurs_liste.php",      "utilisateurs.view",      "Utilisateurs"],
    "utilisateurs/liste"          => ["./dash/admin/utilisateurs_liste.php",      "utilisateurs.view",      "Liste utilisateurs"],
    "utilisateurs/ajouter"        => ["./dash/admin/utilisateur_add.php",         "utilisateurs.create",    "Ajouter utilisateur"],
    "roles"                       => ["./dash/admin/roles.php",                   "utilisateurs.roles",     "Rôles & Permissions"],
    "parametres"                  => ["./dash/admin/company_info.php",            "utilisateurs.roles",     "Paramètres"],
    "profile"                     => ["./dash/admin/profile.php",                 "dashboard.view",         "Mon profil"],
];

// ============================================================
// ROUTE ACTUELLE
// ============================================================
$route = $_GET['route'] ?? 'accueil';
$route = trim($route, '/');
$route = strtolower($route);

// Route vide → accueil
if ($route === '' || $route === 'index.php') {
    $route = 'accueil';
}

// ============================================================
// RECHERCHE DE LA ROUTE
// ============================================================
if (!isset($ROUTES[$route])) {
    http_response_code(404);
    require __DIR__ . "/404.php";
    exit;
}

// ============================================================
// EXTRACTION DE LA ROUTE
// ============================================================
list($file, $requiredPermission, $pageTitle) = $ROUTES[$route];

// ============================================================
// VÉRIFICATION DE PERMISSION
// ============================================================
if (!userCan($user, $requiredPermission)) {
    http_response_code(403);
    $pageTitle = "Accès refusé";
    ob_start();
    ?>
    <div class="max-w-lg mx-auto mt-16 text-center">
        <div class="text-6xl mb-4">🔒</div>
        <h1 class="text-2xl font-bold text-red-600 mb-2">Accès refusé</h1>
        <p class="text-slate-600 mb-6">
            Votre rôle <strong><?= htmlspecialchars(getRoleLabel($user["role_system"])) ?></strong>
            ne vous autorise pas à accéder à cette section.
        </p>
        <a href="<?= htmlspecialchars(app_url('accueil'), ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-muco text-white hover:bg-muco-dark transition">
            <i data-feather="arrow-left" class="w-4 h-4"></i> Retour au tableau de bord
        </a>
    </div>
    <?php
    $content = ob_get_clean();
    require_once __DIR__ . "/dash/template/layout.php";
    exit;
}

// ============================================================
// CHARGEMENT DE LA PAGE
// ============================================================
$pagePath = __DIR__ . "/" . $file;

if (!file_exists($pagePath)) {
    http_response_code(404);
    require __DIR__ . "/404.php";
    exit;
}

// Rendre $user disponible dans la page incluse
// et définir $content via ob_start() dans la page
ob_start();
require $pagePath;
$content = ob_get_clean();

// ============================================================
// AFFICHAGE DANS LE LAYOUT
// ============================================================
require_once __DIR__ . "/dash/template/layout.php";