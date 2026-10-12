<?php
/**
 * template/sidebar_config.php
 * Sidebar avec URLs propres (compatibles avec router.php)
 */

$SIDEBAR_CONFIG = [
    "main" => [
        "label" => null,
        "items" => [
            [
                "type"       => "link",
                "label"      => "Tableau de bord",
                "icon"       => "home",
                "href"       => "/accueil",
                "permission" => "dashboard.view",
                "badge"      => ["text" => "Live", "color" => "muco"],
            ],
        ],
    ],

    "stock" => [
        "label" => "Gestion de stock",
        "items" => [
            [
                "type"       => "dropdown",
                "label"      => "Stocks",
                "icon"       => "database",
                "permission" => "stock.view",
                "items"      => [
                    ["label" => "Vue d'ensemble",      "href" => "/stock",                 "permission" => "stock.view"],
                    ["label" => "Mouvements produits", "href" => "/stock/mouvements",      "permission" => "stock.mouvements"],
                    ["label" => "Alertes seuils",      "href" => "/stock/alertes",         "permission" => "stock.alertes"],
                    ["label" => "Réconciliation",      "href" => "/stock/reconciliation",  "permission" => "stock.reconciliation"],
                ],
            ],
            [
                "type"       => "link",
                "label"      => "Entrées / Sorties",
                "icon"       => "repeat",
                "href"       => "/flux",
                "permission" => "flux.view",
            ],
        ],
    ],

    "caisse" => [
        "label" => "Caisse & Finance",
        "items" => [
            [
                "type"       => "dropdown",
                "label"      => "Caisse",
                "icon"       => "dollar-sign",
                "permission" => "caisse.view",
                "items"      => [
                    ["label" => "Transactions caisse", "href" => "/caisse/transactions", "permission" => "caisse.view"],
                    ["label" => "Rapports financiers", "href" => "/caisse/rapports",     "permission" => "caisse.rapport"],
                    ["label" => "États comptables",    "href" => "/caisse/etats",        "permission" => "caisse.rapport"],
                ],
            ],
            [
                "type"       => "dropdown",
                "label"      => "Transactions bancaires",
                "icon"       => "credit-card",
                "permission" => "banque.view",
                "items"      => [
                    ["label" => "Comptes bancaires", "href" => "/banque/comptes",       "permission" => "banque.view"],
                    ["label" => "Virements",         "href" => "/banque/virements",     "permission" => "banque.create"],
                    ["label" => "Rapprochement",     "href" => "/banque/rapprochement", "permission" => "banque.rapprochement"],
                ],
            ],
        ],
    ],

    "flux" => [
        "label" => "Flux",
        "items" => [
            [
                "type"       => "link",
                "label"      => "Flux matériels",
                "icon"       => "truck",
                "href"       => "/flux/materiels",
                "permission" => "flux.view",
            ],
            [
                "type"       => "link",
                "label"      => "Flux financiers",
                "icon"       => "trending-up",
                "href"       => "/flux/financiers",
                "permission" => "flux.view",
            ],
        ],
    ],

    "factures" => [
        "label" => "Facturation",
        "items" => [
            [
                "type"       => "dropdown",
                "label"      => "Factures",
                "icon"       => "file-text",
                "permission" => "factures.view",
                "items"      => [
                    ["label" => "Liste des factures",  "href" => "/factures/liste",    "permission" => "factures.view"],
                    ["label" => "Créer une facture",   "href" => "/factures/creer",    "permission" => "factures.create"],
                    ["label" => "Envoyer une facture", "href" => "/factures/envoyer",  "permission" => "factures.create"],
                    ["label" => "Archives",            "href" => "/factures/archives", "permission" => "factures.archiver"],
                ],
            ],
        ],
    ],

    "commandes" => [
        "label" => "Commandes",
        "items" => [
            [
                "type"       => "dropdown",
                "label"      => "Books de commande",
                "icon"       => "book-open",
                "permission" => "commandes.view",
                "items"      => [
                    ["label" => "Commandes clients",      "href" => "/commandes/clients",      "permission" => "commandes.clients"],
                    ["label" => "Commandes fournisseurs", "href" => "/commandes/fournisseurs", "permission" => "commandes.fournisseurs"],
                    ["label" => "Nouvelle commande",      "href" => "/commandes/nouvelle",     "permission" => "commandes.create"],
                ],
            ],
        ],
    ],

    "livraisons" => [
        "label" => "Livraisons",
        "items" => [
            [
                "type"       => "dropdown",
                "label"      => "Bordereaux",
                "icon"       => "clipboard",
                "permission" => "livraisons.view",
                "items"      => [
                    ["label" => "Liste bordereaux",     "href" => "/livraisons/liste",      "permission" => "livraisons.view"],
                    ["label" => "Créer un bordereau",   "href" => "/livraisons/creer",      "permission" => "livraisons.create"],
                    ["label" => "Suivi des livraisons", "href" => "/livraisons/suivi",      "permission" => "livraisons.view"],
                    ["label" => "Impression",           "href" => "/livraisons/impression", "permission" => "livraisons.print"],
                ],
            ],
        ],
    ],

    "offres" => [
        "label" => "Commercial",
        "items" => [
            [
                "type"       => "dropdown",
                "label"      => "Offres commerciales",
                "icon"       => "briefcase",
                "permission" => "offres.view",
                "items"      => [
                    ["label" => "Liste des offres",   "href" => "/offres/liste", "permission" => "offres.view"],
                    ["label" => "Créer une offre",    "href" => "/offres/creer", "permission" => "offres.create"],
                    ["label" => "Suivi propositions", "href" => "/offres/suivi", "permission" => "offres.edit"],
                ],
            ],
            [
                "type"       => "dropdown",
                "label"      => "Clients",
                "icon"       => "users",
                "permission" => "clients.view",
                "items"      => [
                    ["label" => "Liste clients",  "href" => "/clients/liste",   "permission" => "clients.view"],
                    ["label" => "Ajouter client", "href" => "/clients/ajouter", "permission" => "clients.create"],
                    ["label" => "Clients VIP",    "href" => "/clients/vip",     "permission" => "clients.view"],
                ],
            ],
            [
                "type"       => "dropdown",
                "label"      => "Fournisseurs",
                "icon"       => "package",
                "permission" => "fournisseurs.view",
                "items"      => [
                    ["label" => "Liste fournisseurs", "href" => "/fournisseurs/liste",   "permission" => "fournisseurs.view"],
                    ["label" => "Ajouter",            "href" => "/fournisseurs/ajouter", "permission" => "fournisseurs.create"],
                ],
            ],
        ],
    ],

    "fidelisation" => [
        "label" => "Fidélisation",
        "items" => [
            [
                "type"       => "link",
                "label"      => "Promotions VIP",
                "icon"       => "gift",
                "href"       => "/promotions",
                "permission" => "promotions.view",
            ],
            [
                "type"       => "link",
                "label"      => "Anniversaires clients",
                "icon"       => "cake",
                "href"       => "/anniversaires",
                "permission" => "anniversaires.view",
            ],
        ],
    ],

    "operations" => [
        "label" => "Pilotage",
        "items" => [
            [
                "type"       => "link",
                "label"      => "Suivi des opérations",
                "icon"       => "activity",
                "href"       => "/operations",
                "permission" => "operations.view",
            ],
            [
                "type"       => "dropdown",
                "label"      => "Rapports",
                "icon"       => "bar-chart-2",
                "permission" => "rapports.view",
                "items"      => [
                    ["label" => "Rapports financiers",  "href" => "/rapports/financier",  "permission" => "rapports.financier"],
                    ["label" => "Rapports stock",       "href" => "/rapports/stock",      "permission" => "rapports.stock"],
                    ["label" => "Rapports commerciaux", "href" => "/rapports/commercial", "permission" => "rapports.commercial"],
                    ["label" => "Rapports RH",          "href" => "/rapports/rh",         "permission" => "rapports.rh"],
                ],
            ],
        ],
    ],

    "communication" => [
        "label" => "Communication",
        "items" => [
            [
                "type"       => "link",
                "label"      => "Messagerie interne",
                "icon"       => "message-circle",
                "href"       => "/messagerie",
                "permission" => "messagerie.view",
                "badge"      => ["text" => "3", "color" => "red"],
            ],
            [
                "type"       => "link",
                "label"      => "Rappels & Alertes",
                "icon"       => "bell",
                "href"       => "/rappels",
                "permission" => "rappels.view",
            ],
        ],
    ],

    "admin" => [
        "label" => "Administration",
        "items" => [
            [
                "type"       => "dropdown",
                "label"      => "Utilisateurs",
                "icon"       => "shield",
                "permission" => "utilisateurs.view",
                "items"      => [
                    ["label" => "Liste des utilisateurs", "href" => "/utilisateurs/liste",   "permission" => "utilisateurs.view"],
                    ["label" => "Ajouter un utilisateur", "href" => "/utilisateurs/ajouter", "permission" => "utilisateurs.create"],
                    ["label" => "Rôles & Permissions",    "href" => "/roles",                "permission" => "utilisateurs.roles"],
                ],
            ],
            [
                "type"       => "link",
                "label"      => "Paramètres",
                "icon"       => "settings",
                "href"       => "/parametres",
                "permission" => "utilisateurs.roles",
            ],
            [
                "type"       => "link",
                "label"      => "Mon profil",
                "icon"       => "user",
                "href"       => "/profile",
                "permission" => "dashboard.view",
            ],
        ],
    ],
];