<?php
/**
 * template/permissions.php
 * Définition centralisée des rôles et permissions
 * Adapté à l'organisation : DH TELECOM AND ADVERTISING HOUSE
 */

$ROLES = [
    // ============================================================
    // SUPER ADMINISTRATEUR : contrôle total du système
    // ============================================================
    "super_admin" => [
        "label"       => "Super Administrateur",
        "description" => "Contrôle total du système",
        "color"       => "red",
        "permissions" => ["*"],
    ],

    // ============================================================
    // DIRECTEUR GÉNÉRAL : vision globale + gestion
    // ============================================================
    "directeur_general" => [
        "label"       => "Directeur Général",
        "description" => "Vue globale et validation des opérations",
        "color"       => "purple",
        "permissions" => [
            "dashboard.view",
            "stock.view", "stock.alertes",
            "caisse.view", "caisse.rapport",
            "flux.view",
            "factures.view",
            "commandes.view",
            "livraisons.view",
            "offres.view", "offres.edit",
            "operations.view",
            "messagerie.*",
            "rappels.*",
            "promotions.view", "promotions.appliquer",
            "anniversaires.view",
            "banque.view",
            "clients.view", "fournisseurs.view", "fournisseurs.create", "fournisseurs.edit", "fournisseurs.delete",
            "rapports.*",
            "utilisateurs.view",
        ],
    ],

    // ============================================================
    // COMPTABLE : finances, banque, rapports
    // ============================================================
    "comptable" => [
        "label"       => "Comptable",
        "description" => "Comptabilité, banque et rapports financiers",
        "color"       => "yellow",
        "permissions" => [
            "dashboard.view",
            "caisse.view", "caisse.rapport",
            "banque.view", "banque.create", "banque.edit", "banque.rapprochement",
            "flux.view", "flux.rapports",
            "factures.view", "factures.archiver",
            "commandes.view",
            "rapports.view", "rapports.financier", "rapports.comptable",
            "messagerie.view", "messagerie.send",
            "rappels.view",
        ],
    ],

    // ============================================================
    // RÉCEPTIONNISTE : accueil clients, commandes, facturation
    // ============================================================
    "receptionniste" => [
        "label"       => "Réceptionniste",
        "description" => "Accueil clients, commandes et facturation",
        "color"       => "green",
        "permissions" => [
            "dashboard.view",
            "caisse.view", "caisse.operations",
            "factures.view", "factures.create", "factures.print",
            "commandes.view", "commandes.clients", "commandes.create",
            "livraisons.view",
            "offres.view",
            "clients.view", "clients.create", "clients.edit", "clients.delete",
            "promotions.view", "promotions.appliquer",
            "anniversaires.view",
            "messagerie.view", "messagerie.send",
            "rappels.view",
        ],
    ],

    // ============================================================
    // MAGASINIER : gestion du stock et livraisons
    // ============================================================
    "magasinier" => [
        "label"       => "Magasinier",
        "description" => "Gestion du stock et des livraisons",
        "color"       => "blue",
        "permissions" => [
            "dashboard.view",
            "stock.view", "stock.create", "stock.edit", "stock.mouvements", "stock.alertes", "stock.reconciliation",
            "flux.entrees", "flux.sorties",
            "livraisons.view", "livraisons.create", "livraisons.print",
            "commandes.view", "commandes.fournisseurs",
            "fournisseurs.view",
            "rapports.stock",
            "messagerie.view", "messagerie.send",
            "rappels.view",
        ],
    ],

    // ============================================================
    // TRAVAILLEUR : accès limité aux opérations courantes
    // ============================================================
    "travailleur" => [
        "label"       => "Travailleur",
        "description" => "Accès aux opérations courantes",
        "color"       => "teal",
        "permissions" => [
            "dashboard.view",
            "stock.view",
            "flux.entrees", "flux.sorties",
            "commandes.view",
            "livraisons.view",
            "messagerie.view", "messagerie.send",
            "rappels.view",
        ],
    ],
];

// ============================================================
// FONCTIONS DE VÉRIFICATION
// ============================================================
function userCan(array $user, string $permission): bool
{
    global $ROLES;
    $role = $user["role_system"] ?? "";
    if (!isset($ROLES[$role])) return false;

    $permissions = $ROLES[$role]["permissions"] ?? [];
    if (in_array("*", $permissions, true)) return true;
    if (in_array($permission, $permissions, true)) return true;

    $module = explode(".", $permission)[0] ?? "";
    if ($module && in_array("$module.*", $permissions, true)) return true;

    return false;
}

function userHasAny(array $user, array $permissions): bool
{
    foreach ($permissions as $p) {
        if (userCan($user, $p)) return true;
    }
    return false;
}

function getRoleLabel(string $role): string
{
    global $ROLES;
    return $ROLES[$role]["label"] ?? ucfirst(str_replace("_", " ", $role));
}

function getRoleColor(string $role): string
{
    global $ROLES;
    return $ROLES[$role]["color"] ?? "slate";
}