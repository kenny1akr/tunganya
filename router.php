<?php
/**
 * router.php
 * Point d'entrée unique — toutes les URLs passent ici.
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
// ============================================================
$ROUTE_DEFINITIONS = [

    // --- Accueil ---
    "accueil"   => ["./dash/admin/index.php", "dashboard.view", "Tableau de bord"],
    "dashboard" => ["./dash/admin/index.php", "dashboard.view", "Tableau de bord"],

    // --- Stock ---
    "stock"                => ["./dash/magasinier/stocks.php",               "stock.view",           "Gestion de stock"],
    "stock/mouvements"     => ["./dash/magasinier/stock_mouvements.php",     "stock.mouvements",     "Mouvements produits"],
    "stock/ajouter"        => ["./dash/magasinier/produit_ajouter.php",      "stock.create",         "Ajouter un produit"],
    "stock/modifier"       => ["./dash/magasinier/produit_modifier.php",    "stock.edit",           "Modifier un produit"],
    "stock/entrees"        => ["./dash/magasinier/stock_entree.php",         "flux.entrees",         "Entrée de stock"],
    "stock/sorties"        => ["./dash/magasinier/stock_sortie.php",         "flux.sorties",         "Sortie de stock"],
    "stock/alertes"        => ["./dash/magasinier/stock_alertes.php",        "stock.alertes",        "Alertes seuils"],
    "stock/categories" => ["./dash/magasinier/stock_categories.php", "stock.categories", "Catégories de stock"],
    "stock/sorties-clients" => ["./dash/magasinier/stock_sortie_client.php", "flux.sorties.client", "Sortie client"],
    "stock/reconciliation" => ["./dash/magasinier/stock_reconciliation.php", "stock.reconciliation", "Réconciliation stock"],

    // --- Flux ---
    "flux"            => ["./dash/magasinier/stock_mouvements.php", "flux.view", "Flux matériels"],
    "flux/materiels"  => ["./dash/magasinier/stock_mouvements.php", "flux.view", "Flux matériels"],
    "flux/financiers" => ["./dash/admin/flux_financiers.php",       "flux.view", "Flux financiers"],

    // --- Caisse ---
    "caisse"              => ["./dash/receptionist/caisse_transactions.php", "caisse.view",       "Caisse"],
    "caisse/transactions" => ["./dash/receptionist/caisse_transactions.php", "caisse.view",       "Transactions caisse"],
    "caisse/operations"   => ["./dash/receptionist/caisse_operations.php",   "caisse.operations", "Opérations de caisse"],
    "caisse/rapports"     => ["./dash/comptable/caisse_rapports.php",        "caisse.rapport",    "Rapports financiers"],
    "caisse/etats"        => ["./dash/comptable/etats_comptables.php",       "caisse.rapport",    "États comptables"],

    // --- Ventes ---
    "ventes/nouvelle" => ["./dash/receptionist/vente_nouvelle.php", "ventes.create", "Vente comptoir"],
    "ventes/factures" => ["./dash/receptionist/ventes_liste.php",   "factures.view", "Factures de vente"],
    "ventes/facture"  => ["./dash/receptionist/facture.php",        "factures.view", "Facture de vente"],
    "ventes/factures-attente" => ["./dash/receptionist/factures_attente.php", "factures.view", "Factures en attente"],

    // --- Banque ---
    "banque"                => ["./dash/admin/banque_comptes.php",       "banque.view",          "Comptes bancaires"],
    "banque/comptes"        => ["./dash/admin/banque_comptes.php",       "banque.view",          "Comptes bancaires"],
    "banque/virements"      => ["./dash/admin/banque_virements.php",     "banque.create",        "Virements"],
    "banque/rapprochement"  => ["./dash/admin/banque_rapprochement.php", "banque.rapprochement", "Rapprochement"],

    // --- Factures ---
    "factures"           => ["./dash/receptionist/factures_liste.php",    "factures.view",     "Factures"],
    "factures/liste"     => ["./dash/receptionist/factures_liste.php",    "factures.view",     "Liste des factures"],
    "factures/creer"     => ["./dash/receptionist/factures_create.php",   "factures.create",   "Factures à créer"],
    "factures/apercu"    => ["./dash/receptionist/facture_apercu.php",    "factures.create",   "Aperçu facture"],
    "factures/convertir" => ["./dash/receptionist/facture_convertir.php", "factures.create",   "Convertir en facture"],
    "factures/envoyer"   => ["./dash/receptionist/factures_envoyer.php",  "factures.create",   "Envoyer une facture"],
    "factures/archives"  => ["./dash/receptionist/factures_archives.php", "factures.archiver", "Archives factures"],

    // --- Commandes ---
    "commandes"              => ["./dash/receptionist/commandes_clients.php",  "commandes.view",         "Commandes"],
    "commandes/clients"      => ["./dash/receptionist/commandes_clients.php",  "commandes.clients",      "Commandes clients"],
    "commandes/fournisseurs" => ["./dash/admin/commandes_fournisseurs.php",    "commandes.fournisseurs", "Commandes fournisseurs"],
    "commandes/nouvelle"     => ["./dash/receptionist/commande_nouvelle.php",  "commandes.create",       "Nouvelle commande"],

    // --- Livraisons ---
    "livraisons"            => ["./dash/receptionist/livraisons_liste.php", "livraisons.view",  "Livraisons"],
    "livraisons/liste"      => ["./dash/receptionist/livraisons_liste.php", "livraisons.view",  "Liste bordereaux"],
    "livraisons/creer"      => ["./dash/admin/livraison_create.php",        "livraisons.create","Créer un bordereau"],
    "livraisons/suivi"      => ["./dash/receptionist/livraison_suivi.php",  "livraisons.view",  "Suivi livraisons"],
    "livraisons/impression" => ["./dash/admin/livraison_print.php",         "livraisons.print", "Impression"],

    // --- Commercial ---
    "offres"           => ["./dash/receptionist/offres_liste.php",    "offres.view",   "Offres commerciales"],
    "offres/liste"     => ["./dash/receptionist/offres_liste.php",    "offres.view",   "Liste des offres"],
    "offres/creer"     => ["./dash/receptionist/offre_form.php",      "offres.create", "Créer une offre"],
    "offres/modifier"  => ["./dash/receptionist/offre_form.php",      "offres.edit",   "Modifier l'offre"],
    "offres/apercu"    => ["./dash/receptionist/offre_apercu.php",    "offres.view",   "Aperçu de l'offre"],
    "offres/supprimer" => ["./dash/receptionist/offre_supprimer.php", "offres.edit",   "Supprimer l'offre"],
    "offres/suivi"     => ["./dash/receptionist/offres_liste.php",    "offres.edit",   "Suivi propositions"],

    // --- Clients ---
    "clients"          => ["./dash/receptionist/clients_liste.php",  "clients.view",   "Clients"],
    "clients/liste"    => ["./dash/receptionist/clients_liste.php",  "clients.view",   "Liste clients"],
    "clients/ajouter"  => ["./dash/receptionist/client_ajouter.php", "clients.create", "Ajouter client"],
    "clients/modifier" => ["./dash/receptionist/client_modifier.php","clients.edit",   "Modifier client"],
    "clients/vip"      => ["./dash/receptionist/clients_vip.php",    "clients.view",   "Clients VIP"],

    // --- Fournisseurs ---
    "fournisseurs"          => ["./dash/admin/fournisseurs_liste.php", "fournisseurs.view",   "Fournisseurs"],
    "fournisseurs/liste"    => ["./dash/admin/fournisseurs_liste.php", "fournisseurs.view",   "Liste fournisseurs"],
    "fournisseurs/ajouter"  => ["./dash/admin/fournisseur_add.php",    "fournisseurs.create", "Ajouter fournisseur"],
    "fournisseurs/modifier" => ["./dash/admin/fournisseur_edit.php",   "fournisseurs.edit",   "Modifier fournisseur"],

    // --- Fidélisation ---
    "promotions"    => ["./dash/admin/promotions.php",           "promotions.view",    "Promotions VIP"],
    "anniversaires" => ["./dash/receptionist/anniversaires.php", "anniversaires.view", "Anniversaires clients"],

    // --- Pilotage ---
    "operations"          => ["./dash/admin/operations.php",          "operations.view",     "Suivi des opérations"],
    "rapports"            => ["./dash/admin/rapports.php",            "rapports.view",       "Rapports"],
    "rapports/financier"  => ["./dash/admin/rapports_financier.php",  "rapports.financier",  "Rapports financiers"],
    "rapports/stock"      => ["./dash/admin/rapports_stock.php",      "rapports.stock",      "Rapports stock"],
    "rapports/commercial" => ["./dash/admin/rapports_commercial.php", "rapports.commercial", "Rapports commerciaux"],
    "rapports/rh"         => ["./dash/admin/rapports_rh.php",         "rapports.rh",         "Rapports RH"],

    // --- Communication ---
    "messagerie" => ["./dash/admin/messagerie.php", "messagerie.view", "Messagerie interne"],
    "rappels"    => ["./dash/receptionist/rappels.php",    "rappels.view",    "Rappels & alertes"],

    // --- Administration ---
    "utilisateurs"         => ["./dash/admin/utilisateurs_liste.php", "utilisateurs.view",   "Utilisateurs"],
    "utilisateurs/liste"   => ["./dash/admin/utilisateurs_liste.php", "utilisateurs.view",   "Liste utilisateurs"],
    "utilisateurs/ajouter" => ["./dash/admin/utilisateur_add.php",    "utilisateurs.create", "Ajouter utilisateur"],
    "roles"                => ["./dash/admin/roles.php",              "utilisateurs.roles",  "Rôles & Permissions"],
    "parametres"           => ["./dash/admin/company_info.php",       "utilisateurs.roles",  "Paramètres"],
    "profile"              => ["./dash/receptionist/profile.php",            "dashboard.view",      "Mon profil"],
];

// ============================================================
// CONSTRUCTION DES ROUTES PAR RÔLE
// ============================================================
$ROUTES_BY_ROLE  = [];
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

// ============================================================
// ALIAS DE RÔLES
// ============================================================
$ROLE_ALIASES = [
    "Super Admin" => "super_admin",
    "Admin"       => "directeur_general",
    "caissier"    => "comptable",
    "agent"       => "magasinier",
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
    "utilisateur_id"  => (int) $userRecord["id"],
    "nom_complet"     => trim(($userRecord["f_name"] ?? "") . " " . ($userRecord["l_name"] ?? "")),
    "nom_utilisateur" => $userRecord["user_name"],
    "role_system"     => $role,
    "statut"          => $userRecord["status"],
    "avatar"          => $userRecord["profil"] ?? "",
];
$_SESSION["role"] = $role;
$ROUTES = $ROUTES_BY_ROLE[$role];

if (($_GET['ajax'] ?? '') === 'client-search') {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, private');
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée.']);
        exit;
    }
    if (!userCan($user, 'flux.sorties.client') || ($_GET['route'] ?? '') !== $role . '/stock/sorties-clients') {
        http_response_code(403);
        echo json_encode(['error' => 'Accès refusé.']);
        exit;
    }
    $clientSearch = trim((string) ($_GET['q'] ?? ''));
    if ($clientSearch === '') {
        echo json_encode(['clients' => []], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if (mb_strlen($clientSearch) > 100) {
        http_response_code(400);
        echo json_encode(['error' => 'La recherche est trop longue.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    try {
        $pattern = '%' . $clientSearch . '%';
        $clients = $db->fetchAll(
            "SELECT id, name, phone FROM partners WHERE category = 'client' AND (LOWER(name) LIKE LOWER(?) OR phone LIKE ?) ORDER BY name LIMIT 20",
            [$pattern, $pattern]
        );
        echo json_encode(['clients' => $clients], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    } catch (Throwable $error) {
        error_log('Client search failed: ' . $error->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'La recherche des clients est momentanément indisponible.'], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// ============================================================
// BIRTHDAYS — clients fêtant leur anniversaire dans les 14 jours
// ============================================================
$birthdayClients = [];
$birthdayToday   = 0;
$birthdaySoon    = 0;

if (userCan($user, 'anniversaires.view')) {
    try {
        $todayTs  = strtotime(date('Y-m-d'));
        $thisYear = (int) date('Y');
        $isLeap   = (bool) date('L');

        $candidates = $db->fetchAll(
            "SELECT id, name, phone, email, status_client, annif
             FROM partners
             WHERE category = 'client'
               AND annif IS NOT NULL
               AND annif <> ''
               AND CHAR_LENGTH(annif) >= 5"
        );

        $upcoming = [];

        foreach ($candidates as $c) {
            $parts = explode('-', $c['annif']);
            if (count($parts) !== 2) continue;
            $d = (int) $parts[0];
            $m = (int) $parts[1];
            if ($d < 1 || $d > 31 || $m < 1 || $m > 12) continue;

            if ($m === 2 && $d === 29 && !$isLeap) {
                $d = 1; $m = 3;
            }

            $annifTs = mktime(0, 0, 0, $m, $d, $thisYear);
            if ($annifTs < $todayTs) {
                $annifTs = mktime(0, 0, 0, $m, $d, $thisYear + 1);
            }

            $daysUntil = (int) round(($annifTs - $todayTs) / 86400);
            if ($daysUntil > 14) continue;

            $upcoming[] = [
                'id'         => (int) $c['id'],
                'name'       => $c['name'],
                'phone'      => $c['phone'] ?? '',
                'email'      => $c['email'] ?? '',
                'status'     => $c['status_client'] ?? 'Nouveau',
                'day'        => $d,
                'month'      => $m,
                'date_ts'    => $annifTs,
                'days_until' => $daysUntil,
            ];

            if ($daysUntil === 0) $birthdayToday++;
        }

        usort($upcoming, function ($a, $b) {
            if ($a['days_until'] !== $b['days_until']) {
                return $a['days_until'] <=> $b['days_until'];
            }
            return strcmp($a['name'], $b['name']);
        });

        $birthdayClients = $upcoming;
        $birthdaySoon    = count($upcoming) - $birthdayToday;
    } catch (Throwable $e) {
        error_log('Birthday check failed: ' . $e->getMessage());
        $birthdayClients = [];
        $birthdayToday   = 0;
        $birthdaySoon    = 0;
    }
}

// ============================================================
// ROUTE ACTUELLE
// ============================================================
$route = $_GET['route'] ?? ($role . '/accueil');
$route = trim($route, '/');
$route = strtolower($route);

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
            <i class="fa-solid fa-arrow-left w-4 h-4"></i> Retour au tableau de bord
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
            <i class="fa-solid fa-arrow-left w-4 h-4"></i> Retour au tableau de bord
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
    $pageTitle = "Fichier manquant";
    ob_start();
    ?>
    <div class="mx-auto max-w-2xl mt-16 rounded-md border border-amber-200 bg-amber-50 p-6 text-amber-900">
        <h1 class="text-xl font-bold mb-2">Fichier introuvable</h1>
        <p class="text-sm">La route existe mais le fichier correspondant est introuvable sur le disque :</p>
        <pre class="mt-3 overflow-x-auto rounded bg-white p-3 text-xs"><?= htmlspecialchars($file) ?></pre>
        <p class="mt-3 text-xs text-amber-800">Chemin résolu : <code><?= htmlspecialchars($pagePath) ?></code></p>
        <a href="<?= htmlspecialchars(app_route_url('accueil'), ENT_QUOTES, 'UTF-8') ?>" class="mt-4 inline-flex items-center gap-2 rounded-md bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700">
            <i class="fa-solid fa-arrow-left w-4 h-4"></i> Retour au tableau de bord
        </a>
    </div>
    <?php
    $content = ob_get_clean();
    require_once __DIR__ . "/dash/template/layout.php";
    exit;
}

ob_start();
require $pagePath;
$content = ob_get_clean();

// ============================================================
// AFFICHAGE DANS LE LAYOUT
// ============================================================
require_once __DIR__ . "/dash/template/layout.php";
