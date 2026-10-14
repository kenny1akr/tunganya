<?php
/**
 * template/layout.php
 * Layout complet avec sidebar filtrée selon le rôle utilisateur.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . "/permissions.php";
require_once __DIR__ . "/sidebar_config.php";

// ============================================================
// ROUTE ACTIVE
// ============================================================
if (!isset($route)) {
    $route = $_GET['route'] ?? 'accueil';
}
$route = trim(strtolower($route), '/');
if ($route === '' || $route === 'index.php') {
    $route = 'accueil';
}

// ============================================================
// UTILISATEUR
// ============================================================
if (!isset($user) || !is_array($user)) {
    $TEST_ROLE = $_GET['role'] ?? 'directeur_general';
    global $ROLES;
    if (!isset($ROLES[$TEST_ROLE])) {
        $TEST_ROLE = 'directeur_general';
    }
    $user = [
        "utilisateur_id"  => 1,
        "nom_complet"     => "Jean Ndayishimiye",
        "nom_utilisateur" => "jndayi",
        "role_system"     => $TEST_ROLE,
        "statut"          => "actif",
        "avatar"          => "",
    ];
}

// ============================================================
// ENTREPRISE
// ============================================================
if (!isset($company) || !is_array($company)) {
    $company = [
        "company_name" => "DH TELECOM AND ADVERTISING HOUSE",
        "country"      => "Burundi",
        "logo"         => "",
    ];
}

// ============================================================
// VARIABLES DÉRIVÉES
// ============================================================
$escName   = htmlspecialchars($user["nom_complet"] ?? "Utilisateur", ENT_QUOTES, 'UTF-8');
$escRole   = htmlspecialchars(getRoleLabel($user["role_system"] ?? ""), ENT_QUOTES, 'UTF-8');
$roleColor = getRoleColor($user["role_system"] ?? "");
$escAvatar = !empty($user["avatar"]) ? htmlspecialchars($user["avatar"], ENT_QUOTES, 'UTF-8') : null;
$rolePrefix = !empty($user["role_system"]) ? $user["role_system"] . "/" : "";
$activeRoute = $rolePrefix !== "" && strpos($route, $rolePrefix) === 0
    ? substr($route, strlen($rolePrefix))
    : $route;
$profileUrl = app_route_url("profile", $user["role_system"] ?? null);

// ============================================================
// ICON MAP — Feather names → Font Awesome classes
// ============================================================
if (!function_exists('faIcon')) {
    function faIcon(?string $feather): string
    {
        $map = [
            'home'           => 'fa-solid fa-house',
            'database'       => 'fa-solid fa-database',
            'dollar-sign'    => 'fa-solid fa-dollar-sign',
            'credit-card'    => 'fa-solid fa-credit-card',
            'shopping-cart'  => 'fa-solid fa-cart-shopping',
            'file-text'      => 'fa-solid fa-file-lines',
            'repeat'         => 'fa-solid fa-repeat',
            'truck'          => 'fa-solid fa-truck',
            'trending-up'    => 'fa-solid fa-arrow-trend-up',
            'book-open'      => 'fa-solid fa-book-open',
            'clipboard'      => 'fa-solid fa-clipboard-list',
            'briefcase'      => 'fa-solid fa-briefcase',
            'users'          => 'fa-solid fa-users',
            'package'        => 'fa-solid fa-box',
            'gift'           => 'fa-solid fa-gift',
            'cake'           => 'fa-solid fa-cake-candles',
            'activity'       => 'fa-solid fa-chart-line',
            'bar-chart-2'    => 'fa-solid fa-chart-column',
            'message-circle' => 'fa-solid fa-comments',
            'bell'           => 'fa-solid fa-bell',
            'shield'         => 'fa-solid fa-shield-halved',
            'settings'       => 'fa-solid fa-gear',
            'user'           => 'fa-solid fa-user',
            'log-out'        => 'fa-solid fa-right-from-bracket',
            'chevrons-left'  => 'fa-solid fa-angles-left',
            'chevron-down'   => 'fa-solid fa-chevron-down',
            'menu'           => 'fa-solid fa-bars',
            'map-pin'        => 'fa-solid fa-location-dot',
            'power'          => 'fa-solid fa-power-off',
            'search'         => 'fa-solid fa-magnifying-glass',
            'plus'           => 'fa-solid fa-plus',
            'check'          => 'fa-solid fa-check',
            'x'              => 'fa-solid fa-xmark',
            'eye'            => 'fa-solid fa-eye',
            'printer'        => 'fa-solid fa-print',
            'edit-2'         => 'fa-solid fa-pen-to-square',
            'trash-2'        => 'fa-solid fa-trash-can',
            'save'           => 'fa-solid fa-floppy-disk',
            'file-plus'      => 'fa-solid fa-file-circle-plus',
            'check-circle'   => 'fa-solid fa-circle-check',
            'alert-triangle' => 'fa-solid fa-triangle-exclamation',
            'clock'          => 'fa-solid fa-clock',
            'list'           => 'fa-solid fa-list',
            'award'          => 'fa-solid fa-award',
            'star'           => 'fa-solid fa-star',
            'calendar'       => 'fa-solid fa-calendar',
            'phone'          => 'fa-solid fa-phone',
            'arrow-left'     => 'fa-solid fa-arrow-left',
            'user-plus'      => 'fa-solid fa-user-plus',
            'link'           => 'fa-solid fa-link',
        ];

        return $map[$feather] ?? 'fa-solid fa-circle';
    }
}

// ============================================================
// RENDER SIDEBAR ITEM
// ============================================================
if (!function_exists('renderSidebarItem')) {
    function renderSidebarItem(array $item, string $currentRoute, array $user): void
    {
        if (!empty($item['permission']) && !userCan($user, $item['permission'])) {
            return;
        }

        $itemHref  = $item['href'] ?? '';
        $itemRoute = ltrim($itemHref, '/');
        $isActive  = ($itemRoute === $currentRoute);

        if ($item['type'] === 'link') {
            $activeClass = $isActive
                ? 'bg-[#C0EBA6] text-slate-900 font-semibold'
                : 'text-white hover:bg-[#C0EBA6] hover:text-slate-900';
            ?>
            <li>
                <a href="<?= htmlspecialchars(app_route_url($itemHref, $user['role_system'] ?? null), ENT_QUOTES, 'UTF-8') ?>"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition <?= $activeClass ?>">
                    <i class="<?= htmlspecialchars(faIcon($item['icon'] ?? '')) ?> w-4 text-center flex-shrink-0"></i>
                    <span class="menu-text"><?= htmlspecialchars($item['label']) ?></span>
                    <?php if (!empty($item['badge'])): ?>
                        <span class="ml-auto text-[10px] px-2 py-0.5 rounded-full bg-<?= htmlspecialchars($item['badge']['color']) ?>-500 text-white">
                            <?= htmlspecialchars($item['badge']['text']) ?>
                        </span>
                    <?php endif; ?>
                </a>
            </li>
            <?php
        } elseif ($item['type'] === 'dropdown') {
            $visibleChildren = array_filter($item['items'] ?? [], function ($sub) use ($user) {
                if (empty($sub['permission'])) return true;
                return userCan($user, $sub['permission']);
            });

            if (empty($visibleChildren)) return;

            $hasActiveChild = false;
            foreach ($visibleChildren as $sub) {
                $subRoute = ltrim($sub['href'] ?? '', '/');
                if ($subRoute === $currentRoute) { $hasActiveChild = true; break; }
            }
            ?>
            <li>
                <a href="#"
                   class="sidebar-dropdown-btn flex items-center w-full gap-3 px-3 py-2 rounded text-white hover:bg-[#C0EBA6] hover:text-slate-900 transition"
                   aria-expanded="<?= $hasActiveChild ? 'true' : 'false' ?>">
                    <i class="<?= htmlspecialchars(faIcon($item['icon'] ?? '')) ?> w-4 text-center flex-shrink-0"></i>
                    <span class="menu-text text-left flex-1"><?= htmlspecialchars($item['label']) ?></span>
                    <i class="fa-solid fa-chevron-down w-3 text-xs transition-transform sidebar-chevron"></i>
                </a>
                <ul class="sidebar-submenu mt-1 ml-2 <?= $hasActiveChild ? '' : 'hidden' ?> text-sm space-y-1 py-1 border-l border-white/20">
                    <?php foreach ($visibleChildren as $sub): ?>
                        <?php
                        $subHref   = $sub['href'] ?? '';
                        $subRoute  = ltrim($subHref, '/');
                        $subActive = ($subRoute === $currentRoute);
                        ?>
                        <li>
                            <a href="<?= htmlspecialchars(app_route_url($subHref, $user['role_system'] ?? null), ENT_QUOTES, 'UTF-8') ?>"
                               class="block w-full text-left px-3 py-2 rounded <?= $subActive ? 'bg-[#C0EBA6] text-slate-900 font-semibold' : 'text-white hover:bg-[#C0EBA6] hover:text-slate-900' ?>">
                                <?= htmlspecialchars($sub['label']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </li>
            <?php
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>DH TELECOM — <?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></title>

    <!-- Font Awesome 6 -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
          integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
          crossorigin="anonymous"
          referrerpolicy="no-referrer">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        window.tailwind = {
            config: {
                theme: {
                    extend: {
                        colors: {
                            muco: { dark: '#0f5132', DEFAULT: '#16a34a', light: '#bbf7d0' },
                            mucoYellow: { DEFAULT: '#f59e0b', light: '#ffedd5' }
                        },
                        boxShadow: { card: '0 6px 20px rgba(15,81,50,0.08)' }
                    }
                }
            }
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        .table-row-hover tbody tr:hover { background-color: rgba(22,163,74,0.03); }
        .scrollbar-thin::-webkit-scrollbar { height: 8px; width: 8px; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 8px; }
        .anim-smooth { transition: all 180ms ease-in-out; }
        .sidebar-dropdown-btn[aria-expanded="true"] .sidebar-chevron { transform: rotate(180deg); }
        #sidebar nav::-webkit-scrollbar { width: 5px; }
        #sidebar nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 8px; }
    </style>
</head>

<body class="min-h-screen bg-[#C0EBA6] text-slate-800 antialiased">

<div id="app" class="relative flex h-screen">

    <div id="mobileOverlay" class="fixed inset-0 z-40 bg-black/40 hidden lg:hidden"></div>

    <!-- ==================== SIDEBAR ==================== -->
    <aside id="sidebar"
           class="fixed inset-y-0 left-0 z-50 w-72 transform -translate-x-0 lg:translate-x-0 bg-[#347928] border-r border-slate-200 p-4 flex flex-col anim-smooth">

        <div class="flex items-center gap-3 pb-3 border-b border-white/10">
            <div class="w-10 h-10 rounded-md bg-muco flex items-center justify-center shadow overflow-hidden flex-shrink-0">
                <?php if (!empty($company["logo"])): ?>
                    <img src="<?= htmlspecialchars($company["logo"]) ?>"
                         alt="<?= htmlspecialchars($company["company_name"]) ?>"
                         class="w-9 h-9 object-contain" />
                <?php else: ?>
                    <span class="text-white font-bold">DH</span>
                <?php endif; ?>
            </div>
            <div class="sidebar-labels min-w-0">
                <div class="font-bold text-sm text-[#FCCD2A] leading-tight">DH TELECOM</div>
                <div class="text-[11px] text-white/80 truncate">AND ADVERTISING HOUSE</div>
            </div>
        </div>

        <div class="mt-3 px-2">
            <div class="sidebar-labels flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/10">
                <span class="w-2 h-2 rounded-full bg-<?= $roleColor ?>-400"></span>
                <span class="text-[11px] text-white/90 font-medium"><?= $escRole ?></span>
            </div>
        </div>

        <nav class="flex-1 overflow-auto scrollbar-thin mt-3 -mr-2 pr-2">
            <ul class="space-y-1 text-sm">
                <?php if (!empty($SIDEBAR_CONFIG)): ?>
                    <?php foreach ($SIDEBAR_CONFIG as $sectionKey => $section): ?>
                        <?php
                        $visibleItems = array_filter($section['items'], function ($item) use ($user) {
                            if (empty($item['permission'])) return true;
                            return userCan($user, $item['permission']);
                        });
                        if (empty($visibleItems)) continue;
                        ?>

                        <?php if (!empty($section['label'])): ?>
                            <li class="mt-4 first:mt-0 text-[10px] text-white/50 uppercase px-3 tracking-wider font-semibold">
                                <?= htmlspecialchars($section['label']) ?>
                            </li>
                        <?php endif; ?>

                        <?php foreach ($visibleItems as $item) renderSidebarItem($item, $activeRoute, $user); ?>
                    <?php endforeach; ?>
                <?php endif; ?>

                <li class="mt-4 pt-3 border-t border-white/10">
                    <a href="#" id="logoutBtn"
                       class="flex items-center gap-3 px-3 py-2 rounded text-white hover:bg-red-500/80 transition">
                        <i class="fa-solid fa-right-from-bracket w-4 text-center"></i>
                        <span class="menu-text">Déconnexion</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="mt-3 px-2">
            <button id="collapseBtn"
                    class="w-full py-2 text-sm rounded-lg text-white bg-muco hover:bg-muco-dark transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-angles-left w-4"></i>
                <span class="menu-text">Réduire</span>
            </button>
        </div>
    </aside>

    <!-- ==================== MAIN ==================== -->
    <div id="mainArea" class="flex-1 flex flex-col min-h-0 ml-0 lg:ml-72 anim-smooth">

        <!-- TOP NAV -->
        <header class="flex items-center justify-between gap-4 px-4 py-3 border-b bg-white shadow-sm">
            <div class="flex items-center gap-3">
                <button id="mobileMenuBtn" class="lg:hidden p-2 rounded-md hover:bg-slate-100">
                    <i class="fa-solid fa-bars w-5 text-lg"></i>
                </button>

                <div class="hidden sm:flex items-center gap-2 text-sm text-slate-500">
                    <i class="fa-solid fa-location-dot text-muco"></i>
                    <span class="font-medium text-slate-700"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></span>
                </div>
            </div>

            <div class="flex items-center gap-3">

                <!-- BIRTHDAY QUICK BADGE (top nav) -->
                <?php if (!empty($birthdayClients)): ?>
                    <a href="<?= htmlspecialchars(app_route_url('anniversaires'), ENT_QUOTES, 'UTF-8') ?>"
                       class="relative flex items-center gap-1.5 rounded-full bg-gradient-to-r from-pink-500 to-rose-500 px-3 py-1.5 text-xs font-semibold text-white shadow hover:opacity-90"
                       title="<?= $birthdayToday > 0 ? $birthdayToday . ' anniversaire(s) aujourd’hui' : $birthdaySoon . ' anniversaire(s) dans les 14 jours' ?>">
                        <i class="fa-solid fa-cake-candles"></i>
                        <span><?= $birthdayToday > 0 ? $birthdayToday . ' aujourd’hui' : $birthdaySoon . ' à venir' ?></span>
                    </a>
                <?php endif; ?>

                <!-- User dropdown -->
                <div class="relative">
                    <button id="userBtn" type="button" aria-haspopup="true" aria-expanded="false"
                            class="flex items-center gap-2 p-1 rounded hover:bg-slate-100 focus:outline-none">
                        <?php if ($escAvatar): ?>
                            <img class="h-9 w-9 rounded-full object-cover" src="<?= $escAvatar ?>" alt="<?= $escName ?>">
                        <?php else: ?>
                            <span class="inline-flex h-9 w-9 rounded-full bg-<?= $roleColor ?>-100 text-<?= $roleColor ?>-700 items-center justify-center font-semibold text-sm">
                                <?= strtoupper(substr($escName, 0, 1)) ?>
                            </span>
                        <?php endif; ?>
                        <span class="hidden sm:inline text-sm font-medium"><?= $escName ?></span>
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </button>

                    <div id="userDropdown"
                         class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50 transform transition duration-150 ease-out scale-95 opacity-0 origin-top-right">
                        <div class="px-4 py-3 border-b flex items-center gap-3">
                            <?php if ($escAvatar): ?>
                                <img src="<?= $escAvatar ?>" class="w-10 h-10 rounded-full object-cover">
                            <?php else: ?>
                                <span class="inline-flex w-10 h-10 rounded-full bg-<?= $roleColor ?>-100 text-<?= $roleColor ?>-700 items-center justify-center font-semibold">
                                    <?= strtoupper(substr($escName, 0, 1)) ?>
                                </span>
                            <?php endif; ?>
                            <div class="min-w-0">
                                <div class="text-sm font-medium text-slate-800 truncate"><?= $escName ?></div>
                                <div class="text-xs text-<?= $roleColor ?>-600 font-medium"><?= $escRole ?></div>
                            </div>
                        </div>
                        <a href="<?= htmlspecialchars(app_route_url('profile', $user['role_system'] ?? null), ENT_QUOTES, 'UTF-8') ?>" class="flex items-center px-4 py-2 text-sm hover:bg-slate-50">
                            <i class="fa-solid fa-user w-4 mr-2"></i> Mon profil
                        </a>
                        <a href="<?= htmlspecialchars(app_route_url('messagerie', $user['role_system'] ?? null), ENT_QUOTES, 'UTF-8') ?>" class="flex items-center px-4 py-2 text-sm hover:bg-slate-50">
                            <i class="fa-solid fa-comments w-4 mr-2"></i> Messagerie
                        </a>
                        <a href="<?= htmlspecialchars(app_route_url('rappels', $user['role_system'] ?? null), ENT_QUOTES, 'UTF-8') ?>" class="flex items-center px-4 py-2 text-sm hover:bg-slate-50">
                            <i class="fa-solid fa-bell w-4 mr-2"></i> Rappels & alertes
                        </a>
                        <?php if (userCan($user, 'utilisateurs.roles')): ?>
                            <a href="<?= htmlspecialchars(app_route_url('parametres', $user['role_system'] ?? null), ENT_QUOTES, 'UTF-8') ?>" class="flex items-center px-4 py-2 text-sm hover:bg-slate-50">
                                <i class="fa-solid fa-gear w-4 mr-2"></i> Paramètres
                            </a>
                        <?php endif; ?>
                        <div class="border-t"></div>
                        <a href="#" id="logoutBtn2" class="flex items-center px-4 py-2 text-sm hover:bg-slate-50 text-rose-600">
                            <i class="fa-solid fa-power-off w-4 mr-2"></i> Se déconnecter
                        </a>
                    </div>
                </div>

                <!-- Notifications bell -->
                <button id="notificationBell" class="p-2 rounded hover:bg-slate-100 relative" title="Notifications">
                    <i class="fa-solid fa-bell text-lg"></i>
                    <span id="notificationBadge"
                          class="absolute top-0 right-0 h-2 w-2 bg-red-500 rounded-full hidden"></span>
                </button>
            </div>
        </header>

        <!-- CONTENT -->
        <main id="content" class="flex-1 overflow-auto p-4">
            <?php
            if (isset($content) && $content !== '') {
                echo $content;
            } else {
                echo '<div class="p-4 bg-yellow-50 text-yellow-800 rounded">Aucun contenu à afficher.</div>';
            }
            ?>
        </main>

        <footer class="px-4 py-3 border-t bg-white text-sm text-slate-500">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
                <div>© <span id="year"></span> <strong>DH TELECOM AND ADVERTISING HOUSE</strong> — Tous droits réservés</div>
                <div class="text-xs text-slate-400">Version 1.0 — <?= $escRole ?></div>
            </div>
        </footer>
    </div>
</div>

<!-- ========================================================= -->
<!-- BIRTHDAY TOAST                                            -->
<!-- ========================================================= -->
<?php if (!empty($birthdayClients)): ?>
    <div id="birthdayToast"
         class="fixed right-4 top-20 z-[60] hidden w-96 max-w-[calc(100vw-2rem)]">
        <div class="overflow-hidden rounded-lg bg-gradient-to-br from-pink-500 to-rose-500 text-white shadow-2xl">
            <div class="flex items-start gap-3 p-4">
                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-white/20 text-xl">
                    <i class="fa-solid fa-cake-candles"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold">
                        <?php if ($birthdayToday > 0 && $birthdaySoon > 0): ?>
                            <i class="fa-solid fa-gift mr-1"></i>
                            <?= $birthdayToday ?> aujourd’hui · <?= $birthdaySoon ?> dans les 14 jours
                        <?php elseif ($birthdayToday > 0): ?>
                            <i class="fa-solid fa-gift mr-1"></i>
                            Anniversaire<?= $birthdayToday > 1 ? 's' : '' ?> aujourd’hui !
                        <?php else: ?>
                            <i class="fa-solid fa-cake-candles mr-1"></i>
                            <?= $birthdaySoon ?> anniversaire<?= $birthdaySoon > 1 ? 's' : '' ?> dans les 14 jours
                        <?php endif; ?>
                    </p>
                    <div class="mt-2 max-h-40 space-y-1 overflow-y-auto pr-1 text-sm">
                        <?php foreach (array_slice($birthdayClients, 0, 6) as $bc):
                            $days = (int) $bc['days_until'];
                        ?>
                            <div class="flex items-center justify-between gap-2 rounded bg-white/10 px-2 py-1">
                                <span class="truncate">
                                    <i class="fa-solid <?= $days === 0 ? 'fa-gift' : 'fa-cake-candles' ?> mr-1"></i>
                                    <strong><?= htmlspecialchars($bc['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                </span>
                                <span class="flex-shrink-0 text-xs font-semibold <?= $days === 0 ? 'text-yellow-200' : 'text-white/90' ?>">
                                    <?php if ($days === 0): ?>
                                        Aujourd’hui
                                    <?php elseif ($days === 1): ?>
                                        Demain
                                    <?php else: ?>
                                        dans <?= $days ?> j
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                        <?php if (count($birthdayClients) > 6): ?>
                            <div class="px-2 pt-1 text-xs text-white/80">
                                …et <?= count($birthdayClients) - 6 ?> autre<?= count($birthdayClients) - 6 > 1 ? 's' : '' ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <a href="<?= htmlspecialchars(app_route_url('anniversaires'), ENT_QUOTES, 'UTF-8') ?>"
                           class="inline-flex items-center gap-1 rounded-md bg-white/20 px-3 py-1.5 text-xs font-semibold hover:bg-white/30">
                            <i class="fa-solid fa-list"></i> Voir la liste
                        </a>
                        <button type="button" onclick="document.getElementById('birthdayToast').classList.add('hidden')"
                                class="inline-flex items-center gap-1 rounded-md px-3 py-1.5 text-xs font-medium text-white/80 hover:bg-white/10">
                            Fermer
                        </button>
                    </div>
                </div>
                <button type="button"
                        onclick="document.getElementById('birthdayToast').classList.add('hidden')"
                        class="flex-shrink-0 rounded p-1 text-white/80 hover:bg-white/20"
                        aria-label="Fermer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
    </div>
    <style>
        @keyframes slide-in {
            from { transform: translateX(110%); opacity: 0; }
            to   { transform: translateX(0);    opacity: 1; }
        }
        #birthdayToast:not(.hidden) {
            animation: slide-in 0.4s ease-out;
        }
    </style>
<?php endif; ?>

<!-- TOAST -->
<div id="toast" class="fixed right-4 bottom-4 z-50 hidden">
    <div class="px-4 py-3 bg-muco text-white rounded shadow-lg">Action réalisée</div>
</div>

<form id="logoutForm" method="post" action="<?= htmlspecialchars(app_url('logout.php'), ENT_QUOTES, 'UTF-8') ?>" class="hidden">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
</form>

<script>
    const $  = (s) => document.querySelector(s);
    const $$ = (s) => Array.from(document.querySelectorAll(s));

    const sidebar         = $('#sidebar');
    const mainArea        = $('#mainArea');
    const mobileOverlayEl = $('#mobileOverlay');

    // ---------- Mobile toggle ----------
    $('#mobileMenuBtn')?.addEventListener('click', () => {
        if (sidebar.classList.contains('-translate-x-72')) {
            sidebar.classList.remove('-translate-x-72');
            mobileOverlayEl.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-72');
            mobileOverlayEl.classList.add('hidden');
        }
    });

    mobileOverlayEl?.addEventListener('click', () => {
        sidebar.classList.add('-translate-x-72');
        mobileOverlayEl.classList.add('hidden');
    });

    // ---------- Collapse desktop ----------
    $('#collapseBtn')?.addEventListener('click', () => {
        sidebar.classList.toggle('w-20');
        if (sidebar.classList.contains('w-20')) {
            sidebar.classList.remove('w-72');
            $$('.menu-text, .sidebar-labels').forEach(n => n.classList.add('hidden'));
            mainArea.classList.remove('lg:ml-72');
            mainArea.classList.add('lg:ml-20');
        } else {
            sidebar.classList.remove('w-20');
            sidebar.classList.add('w-72');
            $$('.menu-text, .sidebar-labels').forEach(n => n.classList.remove('hidden'));
            mainArea.classList.remove('lg:ml-20');
            mainArea.classList.add('lg:ml-72');
        }
    });

    if (window.innerWidth < 1024) sidebar.classList.add('-translate-x-72');

    // ---------- Sidebar dropdowns ----------
    $$('.sidebar-dropdown-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const submenu  = btn.parentElement.querySelector('.sidebar-submenu');
            const expanded = btn.getAttribute('aria-expanded') === 'true';

            $$('.sidebar-dropdown-btn').forEach(other => {
                if (other !== btn) {
                    other.setAttribute('aria-expanded', 'false');
                    other.parentElement.querySelector('.sidebar-submenu')?.classList.add('hidden');
                }
            });

            submenu.classList.toggle('hidden', expanded);
            btn.setAttribute('aria-expanded', !expanded);
        });
    });

    // ---------- User dropdown ----------
    (function() {
        const btn  = document.getElementById('userBtn');
        const menu = document.getElementById('userDropdown');
        if (!btn || !menu) return;

        const openMenu = () => {
            menu.classList.remove('hidden', 'scale-95', 'opacity-0');
            menu.classList.add('scale-100', 'opacity-100');
            btn.setAttribute('aria-expanded', 'true');
        };
        const closeMenu = () => {
            menu.classList.add('scale-95', 'opacity-0');
            btn.setAttribute('aria-expanded', 'false');
            setTimeout(() => menu.classList.add('hidden'), 120);
        };

        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            menu.classList.contains('hidden') ? openMenu() : closeMenu();
        });

        document.addEventListener('click', (e) => {
            if (!menu.contains(e.target) && !btn.contains(e.target)) closeMenu();
        });
    })();

    // ---------- Toast ----------
    function toast(msg = 'Ok') {
        const t = $('#toast');
        if (!t) return;
        t.querySelector('div').textContent = msg;
        t.classList.remove('hidden');
        setTimeout(() => t.classList.add('hidden'), 2600);
    }

    // ---------- Birthday toast (auto-show) ----------
    (function () {
        var bt = document.getElementById('birthdayToast');
        if (!bt) return;

        setTimeout(function () {
            bt.classList.remove('hidden');
        }, 600);

        setTimeout(function () {
            if (!bt.classList.contains('hidden')) {
                bt.classList.add('hidden');
            }
        }, 45000);
    })();

    // ---------- Birthday bell badge ----------
    (function () {
        var bell  = document.getElementById('notificationBell');
        var badge = document.getElementById('notificationBadge');
        if (!bell || !badge) return;

        <?php if (!empty($birthdayClients)): ?>
            badge.classList.remove('hidden');

            bell.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                var bt = document.getElementById('birthdayToast');
                if (bt) {
                    bt.classList.remove('hidden');
                    bt.style.animation = 'none';
                    void bt.offsetWidth;
                    bt.style.animation = '';
                }
            }, true);
        <?php endif; ?>
    })();

    // ---------- Notification audio ----------
    let audioContext = null;
    function playNotificationAudio() {
        try {
            if (!audioContext) audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const ctx = audioContext, now = ctx.currentTime;
            const osc = ctx.createOscillator(), gain = ctx.createGain();
            osc.connect(gain); gain.connect(ctx.destination);
            osc.frequency.setValueAtTime(800, now);
            osc.frequency.setValueAtTime(1000, now + 0.1);
            gain.gain.setValueAtTime(0.15, now);
            gain.gain.setValueAtTime(0, now + 0.3);
            osc.start(now); osc.stop(now + 0.3);
        } catch (e) {}
    }

    // ---------- Logout ----------
    function handleLogout(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Se déconnecter ?',
            text: 'Votre session sera fermée.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Oui, se déconnecter',
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#16a34a',
            reverseButtons: true
        }).then((r) => {
            if (r.isConfirmed) {
                localStorage.removeItem('auth_token');
                localStorage.removeItem('auth_user');
                sessionStorage.removeItem('auth_token');
                sessionStorage.removeItem('auth_user');
                document.getElementById('logoutForm')?.requestSubmit();
            }
        });
    }
    document.getElementById('logoutBtn')?.addEventListener('click', handleLogout);
    document.getElementById('logoutBtn2')?.addEventListener('click', handleLogout);

    // ---------- Escape ----------
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (window.innerWidth < 1024) {
                sidebar.classList.add('-translate-x-72');
                mobileOverlayEl.classList.add('hidden');
            }
        }
    });

    // ---------- Resize ----------
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) {
            mobileOverlayEl.classList.add('hidden');
            sidebar.classList.remove('-translate-x-72');
            mainArea.classList.add('lg:ml-72');
        } else {
            sidebar.classList.add('-translate-x-72');
            mainArea.classList.remove('lg:ml-72');
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        $('#year').textContent = new Date().getFullYear();
        if (window.innerWidth >= 1024) mainArea.classList.add('lg:ml-72');
    });
</script>

</body>
</html>