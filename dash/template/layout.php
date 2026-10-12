<?php
/**
 * template/layout.php
 * Layout complet avec sidebar filtrée selon le rôle utilisateur
 * Compatible avec router.php (URLs propres : /accueil, /stock, etc.)
 *
 * ⚠️ Ce fichier est inclus depuis router.php situé à la racine du projet.
 *    Tous les require utilisent __DIR__ pour éviter les problèmes de chemin relatif.
 */

// ============================================================
// DÉBOGAGE (retirer en production)
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================================
// INCLUSIONS (chemins absolus via __DIR__)
// ============================================================
require_once __DIR__ . "/permissions.php";
require_once __DIR__ . "/sidebar_config.php";

// ============================================================
// DÉTERMINATION DE LA ROUTE ACTIVE
// ============================================================
if (!isset($route)) {
    $route = $_GET['route'] ?? 'accueil';
}
$route = trim(strtolower($route), '/');
if ($route === '' || $route === 'index.php') {
    $route = 'accueil';
}

// ============================================================
// UTILISATEUR (déjà défini par router.php normalement)
// ============================================================
if (!isset($user) || !is_array($user)) {
    // Fallback de test si layout.php est ouvert seul
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
$profileUrl = app_url("profile");

// ============================================================
// FONCTION DE RENDU DE LA SIDEBAR (avec permissions + route propre)
// ============================================================
if (!function_exists('renderSidebarItem')) {
    function renderSidebarItem(array $item, string $currentRoute, array $user): void
    {
        // Vérification de permission
        if (!empty($item['permission']) && !userCan($user, $item['permission'])) {
            return;
        }

        // Comparaison : href="/stock" vs route="stock"
        $itemHref = $item['href'] ?? '';
        $itemRoute = ltrim($itemHref, '/');
        $isActive = ($itemRoute === $currentRoute);

        if ($item['type'] === 'link') {
            $activeClass = $isActive
                ? 'bg-[#C0EBA6] text-slate-900 font-semibold'
                : 'text-white hover:bg-[#C0EBA6] hover:text-slate-900';
            ?>
            <li>
                <a href="<?= htmlspecialchars(app_url($itemHref), ENT_QUOTES, 'UTF-8') ?>"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition <?= $activeClass ?>">
                    <i data-feather="<?= htmlspecialchars($item['icon']) ?>" class="w-4 h-4 flex-shrink-0"></i>
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
            // Filtrer les sous-items selon permissions
            $visibleChildren = array_filter($item['items'] ?? [], function ($sub) use ($user) {
                if (empty($sub['permission'])) return true;
                return userCan($user, $sub['permission']);
            });

            if (empty($visibleChildren)) return;

            // Détecter si un enfant est actif
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
                    <i data-feather="<?= htmlspecialchars($item['icon']) ?>" class="w-4 h-4 flex-shrink-0"></i>
                    <span class="menu-text text-left flex-1"><?= htmlspecialchars($item['label']) ?></span>
                    <i data-feather="chevron-down" class="w-3.5 h-3.5 transition-transform"></i>
                </a>
                <ul class="sidebar-submenu mt-1 ml-2 <?= $hasActiveChild ? '' : 'hidden' ?> text-sm space-y-1 py-1 border-l border-white/20">
                    <?php foreach ($visibleChildren as $sub): ?>
                        <?php
                        $subHref = $sub['href'] ?? '';
                        $subRoute = ltrim($subHref, '/');
                        $subActive = ($subRoute === $currentRoute);
                        ?>
                        <li>
                            <a href="<?= htmlspecialchars(app_url($subHref), ENT_QUOTES, 'UTF-8') ?>"
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
    <script src="https://unpkg.com/feather-icons"></script>

    <style>
        .table-row-hover tbody tr:hover { background-color: rgba(22,163,74,0.03); }
        .scrollbar-thin::-webkit-scrollbar { height: 8px; width: 8px; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 8px; }
        .anim-smooth { transition: all 180ms ease-in-out; }
        .sidebar-dropdown-btn[aria-expanded="true"] .feather-chevron-down { transform: rotate(180deg); }
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

        <!-- Logo + Entreprise -->
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
                <div class="font-bold text-sm text-[#FCCD2A] leading-tight">
                    DH TELECOM
                </div>
                <div class="text-[11px] text-white/80 truncate">
                    AND ADVERTISING HOUSE
                </div>
            </div>
        </div>

        <!-- Badge rôle utilisateur -->
        <div class="mt-3 px-2">
            <div class="sidebar-labels flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/10">
                <span class="w-2 h-2 rounded-full bg-<?= $roleColor ?>-400"></span>
                <span class="text-[11px] text-white/90 font-medium"><?= $escRole ?></span>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-auto scrollbar-thin mt-3 -mr-2 pr-2">
            <ul class="space-y-1 text-sm">
                <?php if (!empty($SIDEBAR_CONFIG)): ?>
                    <?php foreach ($SIDEBAR_CONFIG as $sectionKey => $section): ?>
                        <?php
                        // Vérifier si la section a au moins un item visible
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

                        <?php foreach ($visibleItems as $item) renderSidebarItem($item, $route, $user); ?>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Déconnexion -->
                <li class="mt-4 pt-3 border-t border-white/10">
                    <a href="#" id="logoutBtn"
                       class="flex items-center gap-3 px-3 py-2 rounded text-white hover:bg-red-500/80 transition">
                        <i data-feather="log-out" class="w-4 h-4"></i>
                        <span class="menu-text">Déconnexion</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="mt-3 px-2">
            <button id="collapseBtn"
                    class="w-full py-2 text-sm rounded-lg text-white bg-muco hover:bg-muco-dark transition flex items-center justify-center gap-2">
                <i data-feather="chevrons-left" class="w-4 h-4"></i>
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
                    <i data-feather="menu" class="w-5 h-5"></i>
                </button>

                <!-- Breadcrumb simple -->
                <div class="hidden sm:flex items-center gap-2 text-sm text-slate-500">
                    <i data-feather="map-pin" class="w-4 h-4 text-muco"></i>
                    <span class="font-medium text-slate-700"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></span>
                </div>
            </div>

            <div class="flex items-center gap-3">
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
                        <i data-feather="chevron-down" class="w-4 h-4"></i>
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
                        <a href="<?= htmlspecialchars(app_url('profile'), ENT_QUOTES, 'UTF-8') ?>" class="flex items-center px-4 py-2 text-sm hover:bg-slate-50">
                            <i data-feather="user" class="w-4 h-4 mr-2"></i> Mon profil
                        </a>
                        <a href="<?= htmlspecialchars(app_url('messagerie'), ENT_QUOTES, 'UTF-8') ?>" class="flex items-center px-4 py-2 text-sm hover:bg-slate-50">
                            <i data-feather="message-circle" class="w-4 h-4 mr-2"></i> Messagerie
                        </a>
                        <a href="<?= htmlspecialchars(app_url('rappels'), ENT_QUOTES, 'UTF-8') ?>" class="flex items-center px-4 py-2 text-sm hover:bg-slate-50">
                            <i data-feather="bell" class="w-4 h-4 mr-2"></i> Rappels & alertes
                        </a>
                        <?php if (userCan($user, 'utilisateurs.roles')): ?>
                            <a href="<?= htmlspecialchars(app_url('parametres'), ENT_QUOTES, 'UTF-8') ?>" class="flex items-center px-4 py-2 text-sm hover:bg-slate-50">
                                <i data-feather="settings" class="w-4 h-4 mr-2"></i> Paramètres
                            </a>
                        <?php endif; ?>
                        <div class="border-t"></div>
                        <a href="#" id="logoutBtn2" class="flex items-center px-4 py-2 text-sm hover:bg-slate-50 text-rose-600">
                            <i data-feather="power" class="w-4 h-4 mr-2"></i> Se déconnecter
                        </a>
                    </div>
                </div>

                <!-- Notifications -->
                <button id="notificationBell" class="p-2 rounded hover:bg-slate-100 relative">
                    <i data-feather="bell"></i>
                    <span id="notificationBadge" class="absolute top-0 right-0 h-2 w-2 bg-red-500 rounded-full"></span>
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

        <!-- FOOTER -->
        <footer class="px-4 py-3 border-t bg-white text-sm text-slate-500">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
                <div>© <span id="year"></span> <strong>DH TELECOM AND ADVERTISING HOUSE</strong> — Tous droits réservés</div>
                <div class="text-xs text-slate-400">Version 1.0 — <?= $escRole ?></div>
            </div>
        </footer>
    </div>
</div>

<!-- TOAST -->
<div id="toast" class="fixed right-4 bottom-4 z-50 hidden">
    <div class="px-4 py-3 bg-muco text-white rounded shadow-lg">Action réalisée</div>
</div>

<script>
    feather.replace();

    const $ = (s) => document.querySelector(s);
    const $$ = (s) => Array.from(document.querySelectorAll(s));

    const sidebar = $('#sidebar');
    const mainArea = $('#mainArea');
    const mobileOverlayEl = $('#mobileOverlay');

    // Mobile toggle
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

    // Collapse desktop
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
        feather.replace();
    });

    if (window.innerWidth < 1024) sidebar.classList.add('-translate-x-72');

    // Sidebar dropdowns (un seul ouvert à la fois)
    $$('.sidebar-dropdown-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const submenu = btn.parentElement.querySelector('.sidebar-submenu');
            const expanded = btn.getAttribute('aria-expanded') === 'true';

            // Fermer les autres dropdowns
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

    // User dropdown
    (function() {
        const btn = document.getElementById('userBtn');
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

    // Toast
    function toast(msg = 'Ok') {
        const t = $('#toast');
        t.querySelector('div').textContent = msg;
        t.classList.remove('hidden');
        setTimeout(() => t.classList.add('hidden'), 2600);
    }

    // Notification
    $('#notificationBell')?.addEventListener('click', () => {
        playNotificationAudio();
        toast('Vous avez 3 nouvelles notifications');
    });

    // Audio
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

    // Logout
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
                // En production : window.location.href = "/logout";
                toast('Déconnexion simulée (test)');
            }
        });
    }
    document.getElementById('logoutBtn')?.addEventListener('click', handleLogout);
    document.getElementById('logoutBtn2')?.addEventListener('click', handleLogout);

    // Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (window.innerWidth < 1024) {
                sidebar.classList.add('-translate-x-72');
                mobileOverlayEl.classList.add('hidden');
            }
        }
    });

    // Resize
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
        feather.replace();
    });
</script>

</body>
</html>