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

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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

if (!function_exists('app_route_url')) {
    function app_route_url(string $path, ?string $role = null): string
    {
        $role = $role ?? ($_SESSION['role'] ?? '');
        $path = trim($path, '/');
        if ($role !== '' && $path !== $role && strpos($path, $role . '/') !== 0) {
            $path = $role . '/' . $path;
        }

        return app_url($path);
    }
}

// ============================================================
// CATALOGUE DES ROUTES
// Format : route => [fichier, permission, titre]
// Les groupes par rôle sont générés depuis permissions.php.
// ============================================================
$ROUTE_DEFINITIONS = [ 
    "accueil" => ["./dash/admin/index.php", "dashboard.view", "Tableau de bord"],
    "dashboard" => ["./dash/admin/index.php", "dashboard.view", "Tableau de bord"],
    "stock" => ["./dash/magasinier/stocks.php", "stock.view", "Gestion de stock"],
    "stock/mouvements" => ["./dash/magasinier/stock_mouvements.php", "stock.mouvements", "Mouvements produits"],
    "stock/ajouter" => ["./dash/magasinier/produit_ajouter.php", "stock.create", "Ajouter un produit"],
    "stock/entrees" => ["./dash/magasinier/stock_entree.php", "flux.entrees", "Entrée de stock"],
    "stock/sorties" => ["./dash/magasinier/stock_sortie.php", "flux.sorties", "Sortie de stock"],
    "stock/alertes" => ["./dash/magasinier/stock_alertes.php", "stock.alertes", "Alertes seuils"],
    "stock/reconciliation" => ["./dash/magasinier/stock_reconciliation.php", "stock.reconciliation", "Réconciliation stock"],
    "flux" => ["./dash/magasinier/stock_mouvements.php", "flux.view", "Flux matériels"],
    "flux/materiels" => ["./dash/magasinier/stock_mouvements.php", "flux.view", "Flux matériels"],
    "flux/financiers" => ["./dash/admin/flux_financiers.php", "flux.view", "Flux financiers"],

    "caisse" => ["./dash/comptable/caisse_transactions.php", "caisse.view", "Caisse"],
    "caisse/transactions" => ["./dash/comptable/caisse_transactions.php", "caisse.view", "Transactions caisse"],
    "caisse/rapports" => ["./dash/comptable/caisse_rapports.php", "caisse.rapport", "Rapports financiers"],
    "caisse/etats" => ["./dash/comptable/etats_comptables.php", "caisse.rapport", "États comptables"],
    "banque" => ["./dash/admin/banque_comptes.php", "banque.view", "Comptes bancaires"],
    "banque/comptes" => ["./dash/admin/banque_comptes.php", "banque.view", "Comptes bancaires"],
    "banque/virements" => ["./dash/admin/banque_virements.php", "banque.create", "Virements"],
    "banque/rapprochement" => ["./dash/admin/banque_rapprochement.php", "banque.rapprochement", "Rapprochement"],

    "factures" => ["./dash/admin/factures_liste.php", "factures.view", "Factures"],
    "factures/liste" => ["./dash/admin/factures_liste.php", "factures.view", "Liste des factures"],
    "factures/creer" => ["./dash/admin/factures_create.php", "factures.create", "Créer une facture"],
    "factures/envoyer" => ["./dash/admin/factures_envoyer.php", "factures.create", "Envoyer une facture"],
    "factures/archives" => ["./dash/admin/factures_archives.php", "factures.archiver", "Archives factures"],
    "commandes" => ["./dash/receptionist/commandes_clients.php", "commandes.view", "Commandes"],
    "commandes/clients" => ["./dash/receptionist/commandes_clients.php", "commandes.clients", "Commandes clients"],
    "commandes/fournisseurs" => ["./dash/admin/commandes_fournisseurs.php", "commandes.fournisseurs", "Commandes fournisseurs"],
    "commandes/nouvelle" => ["./dash/receptionist/commande_nouvelle.php", "commandes.create", "Nouvelle commande"],
    "livraisons" => ["./dash/admin/livraisons_liste.php", "livraisons.view", "Livraisons"],
    "livraisons/liste" => ["./dash/admin/livraisons_liste.php", "livraisons.view", "Liste bordereaux"],
    "livraisons/creer" => ["./dash/admin/livraison_create.php", "livraisons.create", "Créer un bordereau"],
    "livraisons/suivi" => ["./dash/admin/livraison_suivi.php", "livraisons.view", "Suivi livraisons"],
    "livraisons/impression" => ["./dash/admin/livraison_print.php", "livraisons.print", "Impression"],

    "offres" => ["./dash/admin/offres_liste.php", "offres.view", "Offres commerciales"],
    "offres/liste" => ["./dash/admin/offres_liste.php", "offres.view", "Liste des offres"],
    "offres/creer" => ["./dash/admin/offre_create.php", "offres.create", "Créer une offre"],
    "offres/suivi" => ["./dash/admin/offres_suivi.php", "offres.edit", "Suivi propositions"],
    "clients" => ["./dash/receptionist/clients_liste.php", "clients.view", "Clients"],
    "clients/liste" => ["./dash/receptionist/clients_liste.php", "clients.view", "Liste clients"],
    "clients/ajouter" => ["./dash/receptionist/client_ajouter.php", "clients.create", "Ajouter client"],
    "clients/modifier" => ["./dash/receptionist/client_modifier.php", "clients.edit", "Modifier client"],
    "clients/vip" => ["./dash/receptionist/clients_vip.php", "clients.view", "Clients VIP"],
    "fournisseurs" => ["./dash/admin/fournisseurs_liste.php", "fournisseurs.view", "Fournisseurs"],
    "fournisseurs/liste" => ["./dash/admin/fournisseurs_liste.php", "fournisseurs.view", "Liste fournisseurs"],
    "fournisseurs/ajouter" => ["./dash/admin/fournisseur_add.php", "fournisseurs.create", "Ajouter fournisseur"],
    "fournisseurs/modifier" => ["./dash/admin/fournisseur_edit.php", "fournisseurs.edit", "Modifier fournisseur"],
    "promotions" => ["./dash/admin/promotions.php", "promotions.view", "Promotions VIP"],
    "anniversaires" => ["./dash/admin/anniversaires.php", "anniversaires.view", "Anniversaires clients"],

    "operations" => ["./dash/admin/operations.php", "operations.view", "Suivi des opérations"],
    "rapports" => ["./dash/admin/rapports.php", "rapports.view", "Rapports"],
    "rapports/financier" => ["./dash/admin/rapports_financier.php", "rapports.financier", "Rapports financiers"],
    "rapports/stock" => ["./dash/admin/rapports_stock.php", "rapports.stock", "Rapports stock"],
    "rapports/commercial" => ["./dash/admin/rapports_commercial.php", "rapports.commercial", "Rapports commerciaux"],
    "rapports/rh" => ["./dash/admin/rapports_rh.php", "rapports.rh", "Rapports RH"],
    "messagerie" => ["./dash/admin/messagerie.php", "messagerie.view", "Messagerie interne"],
    "rappels" => ["./dash/admin/rappels.php", "rappels.view", "Rappels & alertes"],

    "utilisateurs" => ["./dash/admin/utilisateurs_liste.php", "utilisateurs.view", "Utilisateurs"],
    "utilisateurs/liste" => ["./dash/admin/utilisateurs_liste.php", "utilisateurs.view", "Liste utilisateurs"],
    "utilisateurs/ajouter" => ["./dash/admin/utilisateur_add.php", "utilisateurs.create", "Ajouter utilisateur"],
    "roles" => ["./dash/admin/roles.php", "utilisateurs.roles", "Rôles & Permissions"],
    "parametres" => ["./dash/admin/company_info.php", "utilisateurs.roles", "Paramètres"],
    "profile" => ["./dash/admin/profile.php", "dashboard.view", "Mon profil"],




    // pour le receprionniste ici

    
];

// Chaque rôle reçoit ses routes sous la forme /{role}/{route}.
$ROUTES_BY_ROLE = [];
$ALL_ROLE_ROUTES = [];
foreach ($ROLES as $roleName => $roleDefinition) {
    $roleUser = ["role_system" => $roleName];
    $ROUTES_BY_ROLE[$roleName] = [];
    foreach ($ROUTE_DEFINITIONS as $routeName => $routeDefinition) {
        $roleRoute = $roleName . '/' . $routeName;
        $ALL_ROLE_ROUTES[$roleRoute] = true;
        if (userCan($roleUser, $routeDefinition[1])) {
            if ($roleName === "receptionniste" && $routeName === "accueil") {
                $routeDefinition[0] = "./dash/receptionist/index.php";
            }
            if ($roleName === "comptable" && $routeName === "accueil") {
                $routeDefinition[0] = "./dash/comptable/index.php";
            }
            if ($roleName === "magasinier" && $routeName === "accueil") {
                $routeDefinition[0] = "./dash/magasinier/index.php";
            }
            $ROUTES_BY_ROLE[$roleName][$roleRoute] = $routeDefinition;
        }
    }
}

// Alias des anciens rôles utilisés dans certaines bases existantes.
$ROLE_ALIASES = [
    "Super Admin" => "super_admin",
    "Admin" => "directeur_general",
    "caissier" => "comptable",
    "agent" => "magasinier",
    "Utilisateur" => "travailleur",
];

// ============================================================
// UTILISATEUR AUTHENTIFIÉ
// ============================================================
if (empty($_SESSION["logged_in"]) || empty($_SESSION["user_id"])) {
    header("Location: " . app_url("auth/"));
    exit;
}

require_once __DIR__ . "/api/database.php";
$db = Database::getInstance();
$userRecord = $db->fetch(
    "SELECT id, user_name, category, f_name, l_name, status, profil
     FROM users
     WHERE id = ?
     LIMIT 1",
    [$_SESSION["user_id"]]
);

if (!$userRecord || strtolower($userRecord["status"] ?? "") === "désactivé") {
    $_SESSION = [];
    session_destroy();
    header("Location: " . app_url("auth/"));
    exit;
}

$role = $ROLE_ALIASES[$userRecord["category"] ?? ""] ?? ($userRecord["category"] ?? "");
if (!isset($ROLES[$role])) {
    $_SESSION = [];
    session_destroy();
    header("Location: " . app_url("auth/"));
    exit;
}

$user = [
    "utilisateur_id" => (int) $userRecord["id"],
    "nom_complet" => trim(($userRecord["f_name"] ?? "") . " " . ($userRecord["l_name"] ?? "")),
    "nom_utilisateur" => $userRecord["user_name"],
    "role_system" => $role,
    "statut" => $userRecord["status"],
    "avatar" => $userRecord["profil"] ?? "",
];
$_SESSION["role"] = $role;
$ROUTES = $ROUTES_BY_ROLE[$role];

// ============================================================
// ROUTE ACTUELLE
// ============================================================
$route = $_GET['route'] ?? ($role . '/accueil');
$route = trim($route, '/');
$route = strtolower($route);

// Route vide → accueil
if ($route === '' || $route === 'index.php') {
    $route = $role . '/accueil';
}

// ============================================================
// RECHERCHE DE LA ROUTE
// ============================================================
if (!isset($ALL_ROLE_ROUTES[$route])) {
    http_response_code(404);
    require __DIR__ . "/404.php";
    exit;
}

if (!isset($ROUTES[$route])) {
    http_response_code(403);
    $pageTitle = "Accès refusé";
    ob_start();
    ?>
    <div class="max-w-lg mx-auto mt-16 text-center">
        <h1 class="text-2xl font-bold text-red-600 mb-2">Accès refusé</h1>
        <p class="text-slate-600 mb-6">
            Le rôle <strong><?= htmlspecialchars(getRoleLabel($user["role_system"])) ?></strong>
            ne possède pas la permission nécessaire pour cette route.
        </p>
        <a href="<?= htmlspecialchars(app_route_url('accueil'), ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-muco text-white hover:bg-muco-dark transition">
            <i data-feather="arrow-left" class="w-4 h-4"></i> Retour au tableau de bord
        </a>
    </div>
    <?php
    $content = ob_get_clean();
    require_once __DIR__ . "/dash/template/layout.php";
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
        <a href="<?= htmlspecialchars(app_route_url('accueil'), ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-muco text-white hover:bg-muco-dark transition">
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