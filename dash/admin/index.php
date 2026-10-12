<?php
/**
 * index.php
 * Page de contenu : Tableau de bord DH TELECOM AND ADVERTISING HOUSE
 *
 * ⚠️ Ce fichier est inclus par router.php dans un ob_start()/ob_get_clean().
 *    Il ne doit PAS redéfinir le layout, ni faire de require_once de layout.php.
 *    Les variables $user, $route, $pageTitle, $company sont déjà définies par le routeur.
 */

// ============================================================
// SÉCURITÉ : ce fichier ne doit pas être accessible directement
// ============================================================
if (!defined('ROUTER_LOADED')) {
    // Si quelqu'un accède directement à /index.php, rediriger vers le routeur
    header("Location: /accueil");
    exit;
}

// ============================================================
// SÉCURITÉ : vérification de permission (défense en profondeur)
// ============================================================
if (!userCan($user, 'dashboard.view')) {
    http_response_code(403);
    echo '<div class="p-6 bg-red-50 text-red-700 rounded">Accès refusé au tableau de bord.</div>';
    return;
}

// ============================================================
// DONNÉES DE TEST (à remplacer par des requêtes SQL en production)
// ============================================================
$stats = [
    "chiffre_affaires"  => 45_850_000,
    "commandes_jour"    => 28,
    "produits_stock"    => 1_250,
    "alertes_stock"     => 7,
    "factures_impayees" => 12,
    "clients_vip"       => 45,
    "devise"            => "BIF",
];

$recentCommandes = [
    ["ref" => "CMD-2025-001", "client" => "Burundi Télécom",      "total" => 2_500_000, "date" => "2025-01-15", "statut" => "Livré"],
    ["ref" => "CMD-2025-002", "client" => "Radio Isanganiro",     "total" => 850_000,   "date" => "2025-01-15", "statut" => "En cours"],
    ["ref" => "CMD-2025-003", "client" => "Agence Africaine",     "total" => 1_200_000, "date" => "2025-01-14", "statut" => "Livré"],
    ["ref" => "CMD-2025-004", "client" => "Client VIP — M. Eric", "total" => 4_800_000, "date" => "2025-01-14", "statut" => "Payé"],
];

$alertesStock = [
    ["produit" => "Câbles fibre optique 100m",  "stock" => 5, "seuil" => 20, "depot" => "Dépôt principal"],
    ["produit" => "Routeurs Cisco RV340",       "stock" => 3, "seuil" => 10, "depot" => "Dépôt principal"],
    ["produit" => "Antennes paraboliques 90cm", "stock" => 8, "seuil" => 15, "depot" => "Boutique Nord"],
];

$anniversaires = [
    ["client" => "M. Eric Niyongabo", "date" => "15 Jan", "type" => "VIP", "remise" => 15],
    ["client" => "Mme Alice Uwimana", "date" => "18 Jan", "type" => "VIP", "remise" => 10],
    ["client" => "Sté Bujumbura Net", "date" => "22 Jan", "type" => "Pro", "remise" => 5],
];

$topProduits = [
    ["nom" => "Routeurs Cisco RV340",       "ventes" => 85,  "ca" => 12_750_000],
    ["nom" => "Câbles fibre optique 100m",  "ventes" => 220, "ca" => 8_800_000],
    ["nom" => "Antennes paraboliques 90cm", "ventes" => 65,  "ca" => 5_200_000],
    ["nom" => "Décodeurs TV HD",            "ventes" => 140, "ca" => 4_200_000],
];

// Rôle courant (pour personnaliser l'affichage)
$currentRole = $user["role_system"] ?? "invite";
$currentRoleLabel = getRoleLabel($currentRole);
?>

<div class="space-y-6">

    <!-- ==================== EN-TÊTE ==================== -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-[#0f5132]">Tableau de bord</h1>
            <p class="text-sm text-slate-600">
                Bienvenue <strong><?= htmlspecialchars($user["nom_complet"] ?? "Utilisateur") ?></strong>
                <span class="text-slate-400">·</span>
                <span class="text-<?= getRoleColor($currentRole) ?>-600 font-medium">
                    <?= htmlspecialchars($currentRoleLabel) ?>
                </span>
                — Vue d'ensemble de l'activité
            </p>
        </div>
        <div class="flex gap-2">
            <button onclick="toast('Rafraîchissement des données...')"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-muco text-white hover:bg-muco-dark transition text-sm">
                <i data-feather="refresh-cw" class="w-4 h-4"></i> Actualiser
            </button>
        </div>
    </div>

    <!-- ==================== KPIs ==================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- CA -->
        <?php if (userCan($user, 'caisse.view') || userCan($user, 'rapports.financier')): ?>
        <div class="bg-white rounded-xl shadow-card p-5 border-l-4 border-muco">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs uppercase text-slate-500 tracking-wide">Chiffre d'affaires</p>
                    <p class="text-2xl font-bold text-[#0f5132] mt-1">
                        <?= number_format($stats["chiffre_affaires"], 0, ',', ' ') ?>
                        <span class="text-sm font-normal text-slate-500"><?= $stats["devise"] ?></span>
                    </p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-muco-light/60 flex items-center justify-center">
                    <i data-feather="trending-up" class="w-5 h-5 text-muco-dark"></i>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Commandes du jour -->
        <?php if (userCan($user, 'commandes.view')): ?>
        <div class="bg-white rounded-xl shadow-card p-5 border-l-4 border-mucoYellow">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs uppercase text-slate-500 tracking-wide">Commandes du jour</p>
                    <p class="text-2xl font-bold text-[#0f5132] mt-1"><?= $stats["commandes_jour"] ?></p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-mucoYellow-light flex items-center justify-center">
                    <i data-feather="shopping-bag" class="w-5 h-5 text-mucoYellow"></i>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Produits en stock -->
        <?php if (userCan($user, 'stock.view')): ?>
        <div class="bg-white rounded-xl shadow-card p-5 border-l-4 border-blue-400">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs uppercase text-slate-500 tracking-wide">Produits en stock</p>
                    <p class="text-2xl font-bold text-[#0f5132] mt-1"><?= number_format($stats["produits_stock"]) ?></p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                    <i data-feather="package" class="w-5 h-5 text-blue-600"></i>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Alertes stock -->
        <?php if (userCan($user, 'stock.alertes')): ?>
        <div class="bg-white rounded-xl shadow-card p-5 border-l-4 border-red-400">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs uppercase text-slate-500 tracking-wide">Alertes stock</p>
                    <p class="text-2xl font-bold text-red-600 mt-1"><?= $stats["alertes_stock"] ?></p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                    <i data-feather="alert-triangle" class="w-5 h-5 text-red-600"></i>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- ==================== COMMANDES + ALERTES ==================== -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Commandes récentes -->
        <?php if (userCan($user, 'commandes.view')): ?>
        <div class="lg:col-span-2 bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-5 py-4 border-b flex items-center justify-between">
                <h2 class="font-semibold text-[#0f5132]">Commandes récentes</h2>
                <a href="<?= htmlspecialchars(app_url('commandes/clients'), ENT_QUOTES, 'UTF-8') ?>" class="text-sm text-muco hover:underline">Voir tout →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="table-row-hover w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                        <tr>
                            <th class="text-left px-5 py-3">Réf.</th>
                            <th class="text-left px-5 py-3">Client</th>
                            <th class="text-right px-5 py-3">Total</th>
                            <th class="text-left px-5 py-3">Date</th>
                            <th class="text-left px-5 py-3">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php foreach ($recentCommandes as $c): ?>
                            <tr>
                                <td class="px-5 py-3 font-mono text-xs"><?= htmlspecialchars($c["ref"]) ?></td>
                                <td class="px-5 py-3"><?= htmlspecialchars($c["client"]) ?></td>
                                <td class="px-5 py-3 text-right font-semibold text-[#0f5132]">
                                    <?= number_format($c["total"], 0, ',', ' ') ?> BIF
                                </td>
                                <td class="px-5 py-3 text-slate-500 text-xs"><?= htmlspecialchars($c["date"]) ?></td>
                                <td class="px-5 py-3">
                                    <?php
                                    $colors = [
                                        "Livré"    => "green",
                                        "En cours" => "yellow",
                                        "Payé"     => "blue",
                                    ];
                                    $col = $colors[$c["statut"]] ?? "slate";
                                    ?>
                                    <span class="inline-block px-2 py-0.5 rounded-full bg-<?= $col ?>-100 text-<?= $col ?>-700 text-xs">
                                        <?= htmlspecialchars($c["statut"]) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Alertes stock -->
        <?php if (userCan($user, 'stock.alertes')): ?>
        <div class="bg-white rounded-xl shadow-card overflow-hidden <?= !userCan($user, 'commandes.view') ? 'lg:col-span-3' : '' ?>">
            <div class="px-5 py-4 border-b flex items-center justify-between">
                <h2 class="font-semibold text-[#0f5132]">Alertes stock critiques</h2>
                <a href="<?= htmlspecialchars(app_url('stock/alertes'), ENT_QUOTES, 'UTF-8') ?>" class="text-sm text-muco hover:underline">Détails →</a>
            </div>
            <ul class="divide-y">
                <?php foreach ($alertesStock as $a): ?>
                    <li class="px-5 py-3 flex items-center gap-3">
                        <span class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                            <i data-feather="alert-triangle" class="w-4 h-4 text-red-600"></i>
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium truncate"><?= htmlspecialchars($a["produit"]) ?></p>
                            <p class="text-xs text-slate-500"><?= htmlspecialchars($a["depot"]) ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-red-600"><?= $a["stock"] ?></p>
                            <p class="text-xs text-slate-400">seuil <?= $a["seuil"] ?></p>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>

    <!-- ==================== TOP PRODUITS + ANNIVERSAIRES ==================== -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Top produits -->
        <?php if (userCan($user, 'rapports.stock') || userCan($user, 'stock.view')): ?>
        <div class="lg:col-span-2 bg-white rounded-xl shadow-card p-5">
            <h2 class="font-semibold text-[#0f5132] mb-4">Top produits vendus</h2>
            <div class="space-y-3">
                <?php
                $maxCA = max(array_column($topProduits, "ca"));
                foreach ($topProduits as $p):
                    $pct = $maxCA > 0 ? round(($p["ca"] / $maxCA) * 100) : 0;
                ?>
                    <div>
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span class="font-medium"><?= htmlspecialchars($p["nom"]) ?></span>
                            <span class="text-slate-500"><?= number_format($p["ca"], 0, ',', ' ') ?> BIF</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="bg-muco h-2 rounded-full transition-all" style="width: <?= $pct ?>%"></div>
                        </div>
                        <p class="text-xs text-slate-400 mt-1"><?= $p["ventes"] ?> ventes</p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Anniversaires (VIP) -->
        <?php if (userCan($user, 'anniversaires.view')): ?>
        <div class="bg-white rounded-xl shadow-card p-5 <?= !userCan($user, 'rapports.stock') && !userCan($user, 'stock.view') ? 'lg:col-span-3' : '' ?>">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-[#0f5132]">🎂 Anniversaires à venir</h2>
                <a href="<?= htmlspecialchars(app_url('anniversaires'), ENT_QUOTES, 'UTF-8') ?>" class="text-xs text-muco hover:underline">Voir tout</a>
            </div>
            <ul class="space-y-3">
                <?php foreach ($anniversaires as $a): ?>
                    <li class="flex items-center gap-3 p-2 rounded hover:bg-slate-50">
                        <span class="w-9 h-9 rounded-full bg-mucoYellow-light flex items-center justify-center">
                            <i data-feather="gift" class="w-4 h-4 text-mucoYellow"></i>
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium truncate"><?= htmlspecialchars($a["client"]) ?></p>
                            <p class="text-xs text-slate-500"><?= htmlspecialchars($a["date"]) ?> · <?= $a["type"] ?></p>
                        </div>
                        <span class="text-xs font-bold text-muco">-<?= $a["remise"] ?>%</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?> 
    </div>

    <!-- ==================== RACCOURCIS RAPIDES ==================== -->
    <?php
    // Construire la liste des raccourcis visibles selon permissions
    $shortcuts = [];

    if (userCan($user, 'factures.create')) {
        $shortcuts[] = ["href" => "/factures/creer",   "icon" => "file-plus",      "label" => "Nouvelle facture"];
    }
    if (userCan($user, 'commandes.create')) {
        $shortcuts[] = ["href" => "/commandes/nouvelle", "icon" => "shopping-cart", "label" => "Nouvelle commande"];
    }
    if (userCan($user, 'livraisons.create')) {
        $shortcuts[] = ["href" => "/livraisons/creer",  "icon" => "truck",         "label" => "Bordereau livraison"];
    }
    if (userCan($user, 'offres.create')) {
        $shortcuts[] = ["href" => "/offres/creer",      "icon" => "briefcase",     "label" => "Nouvelle offre"];
    }
    if (userCan($user, 'clients.create')) {
        $shortcuts[] = ["href" => "/clients/ajouter",   "icon" => "user-plus",     "label" => "Nouveau client"];
    }
    if (userCan($user, 'rapports.view')) {
        $shortcuts[] = ["href" => "/rapports",          "icon" => "bar-chart-2",   "label" => "Rapports"];
    }
    if (userCan($user, 'stock.view')) {
        $shortcuts[] = ["href" => "/stock",             "icon" => "database",      "label" => "Stock"];
    }
    if (userCan($user, 'messagerie.view')) {
        $shortcuts[] = ["href" => "/messagerie",        "icon" => "message-circle","label" => "Messagerie"];
    }
    ?>

    <?php if (!empty($shortcuts)): ?>
    <div class="bg-white rounded-xl shadow-card p-5">
        <h2 class="font-semibold text-[#0f5132] mb-4">Raccourcis rapides</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-<?= min(count($shortcuts), 6) ?> gap-3">
            <?php foreach ($shortcuts as $s): ?>
                <a href="<?= htmlspecialchars($s["href"]) ?>"
                   class="flex flex-col items-center gap-2 p-3 rounded-lg border hover:border-muco hover:bg-muco-light/20 transition">
                    <i data-feather="<?= htmlspecialchars($s["icon"]) ?>" class="w-5 h-5 text-muco"></i>
                    <span class="text-xs font-medium text-center"><?= htmlspecialchars($s["label"]) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>