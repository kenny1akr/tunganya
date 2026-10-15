<?php
if (!defined('ROUTER_LOADED')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

if (!userCan($user, 'dashboard.view')) {
    http_response_code(403);
    echo '<p class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</p>';
    return;
}

require_once __DIR__ . '/../../api/database.php';
require_once __DIR__ . '/_sales.php';

$db     = Database::getInstance();
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$role   = $user['role_system'];
$today  = date('Y-m-d');

// =========================================================================
// KPI 1 — CA du jour
// =========================================================================
$caToday = 0.0;
try {
    $caToday = (float) $db->fetchColumn(
        "SELECT COALESCE(SUM(total_amount), 0)
         FROM sales
         WHERE date = CURDATE() AND status = 'Active'"
    );
} catch (Throwable $e) { error_log('kpi caToday: ' . $e->getMessage()); }

// =========================================================================
// KPI 2 — Encaissé du jour (caisse)
// =========================================================================
$cashToday = 0.0;
try {
    $cashToday = (float) $db->fetchColumn(
        "SELECT COALESCE(SUM(amount), 0)
         FROM cash_in
         WHERE date_created = CURDATE()"
    );
} catch (Throwable $e) { error_log('kpi cashToday: ' . $e->getMessage()); }

// =========================================================================
// KPI 3 — Impayés (reste dû total)
// =========================================================================
$unpaidTotal = 0.0;
$unpaidCount = 0;
try {
    $row = $db->fetch(
        "SELECT COUNT(*) AS nb, COALESCE(SUM(debt), 0) AS total
         FROM sales
         WHERE document_type = 'Facture' AND status = 'Active' AND debt > 0"
    );
    $unpaidCount = (int) ($row['nb'] ?? 0);
    $unpaidTotal = (float) ($row['total'] ?? 0);
} catch (Throwable $e) { error_log('kpi unpaid: ' . $e->getMessage()); }

// =========================================================================
// KPI 4 — Commandes en cours (En préparation + Expédiée)
// =========================================================================
$ordersInProgress = 0;
try {
    $ordersInProgress = (int) $db->fetchColumn(
        "SELECT COUNT(*) FROM tasks
         WHERE category = 'Commande'
           AND delivery_status IN ('En préparation','Expédiée')"
    );
} catch (Throwable $e) { error_log('kpi inProgress: ' . $e->getMessage()); }

// =========================================================================
// ALERTES — compteurs
// =========================================================================
$alerts = [
    'birthdaysToday'  => 0,
    'lateOrders'      => 0,
    'deliveriesToday' => 0,
    'deliveredNotBilled' => 0,
];

// Anniversaires aujourd'hui
try {
    $alerts['birthdaysToday'] = (int) $db->fetchColumn(
        "SELECT COUNT(*) FROM partners
         WHERE category = 'client'
           AND annif IS NOT NULL AND annif <> ''
           AND CAST(SUBSTRING(annif, 1, 2) AS UNSIGNED) = DAY(CURDATE())
           AND CAST(SUBSTRING(annif, 4, 2) AS UNSIGNED) = MONTH(CURDATE())"
    );
} catch (Throwable $e) { error_log('kpi birthdaysToday: ' . $e->getMessage()); }

// Commandes en retard
try {
    $alerts['lateOrders'] = (int) $db->fetchColumn(
        "SELECT COUNT(*) FROM tasks
         WHERE category = 'Commande'
           AND delivery_status IN ('En préparation','Expédiée')
           AND due_date IS NOT NULL AND due_date < CURDATE()"
    );
} catch (Throwable $e) { error_log('kpi lateOrders: ' . $e->getMessage()); }

// Livraisons du jour
try {
    $alerts['deliveriesToday'] = (int) $db->fetchColumn(
        "SELECT COUNT(*) FROM tasks
         WHERE category = 'Commande'
           AND delivery_status IN ('En préparation','Expédiée')
           AND due_date = CURDATE()"
    );
} catch (Throwable $e) { error_log('kpi deliveriesToday: ' . $e->getMessage()); }

// Livrées non facturées
try {
    $alerts['deliveredNotBilled'] = (int) $db->fetchColumn(
        "SELECT COUNT(*) FROM tasks
         WHERE category = 'Commande'
           AND delivery_status = 'Livrée'
           AND (invoice_number IS NULL OR invoice_number = '')"
    );
} catch (Throwable $e) { error_log('kpi deliveredNotBilled: ' . $e->getMessage()); }

// =========================================================================
// GRAPHIQUE — CA des 7 derniers jours
// =========================================================================
$chartLabels = [];
$chartData   = [];
$chartMax    = 0;

for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-{$i} days"));
    $chartLabels[] = date('d/m', strtotime($d));
    $chartData[]   = 0;
}

try {
    $rows = $db->fetchAll(
        "SELECT date, COALESCE(SUM(total_amount), 0) AS total
         FROM sales
         WHERE status = 'Active'
           AND date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
         GROUP BY date
         ORDER BY date ASC"
    );
    foreach ($rows as $r) {
        $idx = array_search(date('d/m', strtotime($r['date'])), $chartLabels, true);
        if ($idx !== false) {
            $chartData[$idx] = (int) $r['total'];
        }
    }
    $chartMax = max($chartData ?: [0]);
} catch (Throwable $e) { error_log('chart daily: ' . $e->getMessage()); }

// =========================================================================
// DERNIÈRES COMMANDES
// =========================================================================
$recentOrders = [];
try {
    $recentOrders = $db->fetchAll(
        "SELECT t.id, t.ticket_no, t.client, t.total_amount, t.delivery_status,
                t.priority, t.created_at
         FROM tasks t
         WHERE t.category = 'Commande'
         ORDER BY t.id DESC
         LIMIT 5"
    );
} catch (Throwable $e) { error_log('recentOrders: ' . $e->getMessage()); }

// =========================================================================
// DERNIÈRES FACTURES
// =========================================================================
$recentInvoices = [];
try {
    $recentInvoices = $db->fetchAll(
        "SELECT id, invoice_number, client_name, total_amount, netcash, debt, date, payment_method
         FROM sales
         WHERE document_type = 'Facture' AND status = 'Active'
         ORDER BY id DESC
         LIMIT 5"
    );
} catch (Throwable $e) { error_log('recentInvoices: ' . $e->getMessage()); }

// =========================================================================
// PROCHAINES LIVRAISONS (aujourd'hui + à venir)
// =========================================================================
$upcomingDeliveries = [];
try {
    $upcomingDeliveries = $db->fetchAll(
        "SELECT t.id, t.ticket_no, t.client, t.due_date, t.delivery_status, t.priority,
                u.f_name, u.l_name
         FROM tasks t
         LEFT JOIN users u ON u.user_name = t.assigned_to
         WHERE t.category = 'Commande'
           AND t.delivery_status IN ('En préparation','Expédiée')
           AND t.due_date IS NOT NULL
           AND t.due_date >= CURDATE()
         ORDER BY t.due_date ASC
         LIMIT 5"
    );
} catch (Throwable $e) { error_log('upcomingDeliveries: ' . $e->getMessage()); }

// Helpers couleur
$deliveryColor = static function (string $d): string {
    return match ($d) {
        'En préparation' => 'bg-orange-100 text-orange-800',
        'Expédiée'       => 'bg-blue-100 text-blue-800',
        'Livrée'         => 'bg-green-100 text-green-800',
        'Annulée'        => 'bg-slate-200 text-slate-600',
        default          => 'bg-slate-100 text-slate-700',
    };
};

$priorityColor = static function (string $p): string {
    return match ($p) {
        'Critique' => 'bg-rose-100 text-rose-800',
        'Haute'    => 'bg-amber-100 text-amber-800',
        'Moyenne'  => 'bg-blue-100 text-blue-800',
        'Basse'    => 'bg-slate-100 text-slate-700',
        default    => 'bg-slate-100 text-slate-700',
    };
};

$firstName = trim(explode(' ', trim($user['nom_complet'] ?? ''))[0] ?? '');
if ($firstName === '') $firstName = 'collègue';

$pendingStockInvoices = 0;
try {
    $pendingStockInvoices = (int) $db->fetchColumn(
        "SELECT COUNT(*) FROM sales
         WHERE document_type = 'Commande'
           AND origin = 'Sortie client magasin'
           AND status = 'Active'"
    );
} catch (Throwable $e) {
    error_log('kpi pendingStockInvoices: ' . $e->getMessage());
}
?>
<section class="space-y-5">

    <!-- HEADER -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-green-800">Espace réception</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">
                Bonjour <?= $escape($firstName) ?> 👋
            </h1>
            <p class="mt-1 text-sm text-slate-600">
                <?= date('l d F Y') ?> — voici un aperçu de votre activité.
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <?php if (userCan($user, 'commandes.create')): ?>
                <a href="<?= $escape(app_route_url('commandes/nouvelle')) ?>"
                   class="inline-flex items-center gap-2 rounded-md bg-green-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-800">
                    <i class="fa-solid fa-plus"></i> Nouvelle commande
                </a>
            <?php endif; ?>
            <?php if (userCan($user, 'ventes.create')): ?>
                <a href="<?= $escape(app_route_url('ventes/nouvelle')) ?>"
                   class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    <i class="fa-solid fa-cart-shopping"></i> Vente comptoir
                </a>
            <?php endif; ?>
            <?php if (userCan($user, 'factures.view')): ?>
                <a href="<?= $escape(app_route_url('ventes/factures-attente')) ?>"
                   class="inline-flex items-center gap-2 rounded-md border border-amber-300 bg-amber-50 px-4 py-2.5 text-sm font-semibold text-amber-900 hover:bg-amber-100">
                    <i class="fa-solid fa-clock"></i> Factures à établir (<?= number_format($pendingStockInvoices, 0, ',', ' ') ?>)
                </a>
            <?php endif; ?>
        </div>
    </header>

    <!-- KPI CARDS -->
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <article class="rounded-md border border-green-200 bg-green-50 p-4">
            <div class="flex items-start justify-between">
                <p class="text-xs font-semibold uppercase text-green-700">CA du jour</p>
                <i class="fa-solid fa-coins text-green-600"></i>
            </div>
            <p class="mt-1 text-2xl font-bold text-green-800">
                <?= number_format($caToday, 0, ',', ' ') ?> <span class="text-sm font-medium">FBu</span>
            </p>
        </article>

        <article class="rounded-md border border-blue-200 bg-blue-50 p-4">
            <div class="flex items-start justify-between">
                <p class="text-xs font-semibold uppercase text-blue-700">Encaissé aujourd’hui</p>
                <i class="fa-solid fa-cash-register text-blue-600"></i>
            </div>
            <p class="mt-1 text-2xl font-bold text-blue-800">
                <?= number_format($cashToday, 0, ',', ' ') ?> <span class="text-sm font-medium">FBu</span>
            </p>
        </article>

        <article class="rounded-md border border-rose-200 bg-rose-50 p-4">
            <div class="flex items-start justify-between">
                <p class="text-xs font-semibold uppercase text-rose-700">Impayés</p>
                <i class="fa-solid fa-file-invoice-dollar text-rose-600"></i>
            </div>
            <p class="mt-1 text-2xl font-bold text-rose-800">
                <?= number_format($unpaidTotal, 0, ',', ' ') ?> <span class="text-sm font-medium">FBu</span>
            </p>
            <p class="mt-1 text-xs text-rose-700"><?= $unpaidCount ?> facture(s)</p>
        </article>

        <article class="rounded-md border border-amber-200 bg-amber-50 p-4">
            <div class="flex items-start justify-between">
                <p class="text-xs font-semibold uppercase text-amber-700">Commandes en cours</p>
                <i class="fa-solid fa-clipboard-list text-amber-600"></i>
            </div>
            <p class="mt-1 text-2xl font-bold text-amber-800"><?= $ordersInProgress ?></p>
            <p class="mt-1 text-xs text-amber-700">En préparation ou expédiées</p>
        </article>
    </div>

    <!-- ALERTES -->
    <?php
    $totalAlerts = $alerts['birthdaysToday'] + $alerts['lateOrders']
                 + $alerts['deliveriesToday'] + $alerts['deliveredNotBilled'];
    ?>
    <?php if ($totalAlerts > 0): ?>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <?php if ($alerts['birthdaysToday'] > 0): ?>
                <a href="<?= $escape(app_route_url('anniversaires')) ?>"
                   class="flex items-center gap-3 rounded-md border border-pink-200 bg-pink-50 p-3 hover:bg-pink-100 transition">
                    <i class="fa-solid fa-cake-candles text-pink-600 text-lg"></i>
                    <div>
                        <p class="text-sm font-semibold text-pink-800"><?= $alerts['birthdaysToday'] ?> anniversaire(s)</p>
                        <p class="text-xs text-pink-700">Aujourd’hui</p>
                    </div>
                </a>
            <?php endif; ?>

            <?php if ($alerts['lateOrders'] > 0): ?>
                <a href="<?= $escape(app_route_url('rappels')) ?>#late-orders"
                   class="flex items-center gap-3 rounded-md border border-rose-200 bg-rose-50 p-3 hover:bg-rose-100 transition">
                    <i class="fa-solid fa-clock-rotate-left text-rose-600 text-lg"></i>
                    <div>
                        <p class="text-sm font-semibold text-rose-800"><?= $alerts['lateOrders'] ?> commande(s) en retard</p>
                        <p class="text-xs text-rose-700">À relancer</p>
                    </div>
                </a>
            <?php endif; ?>

            <?php if ($alerts['deliveriesToday'] > 0): ?>
                <a href="<?= $escape(app_route_url('livraisons/suivi')) ?>"
                   class="flex items-center gap-3 rounded-md border border-blue-200 bg-blue-50 p-3 hover:bg-blue-100 transition">
                    <i class="fa-solid fa-truck-fast text-blue-600 text-lg"></i>
                    <div>
                        <p class="text-sm font-semibold text-blue-800"><?= $alerts['deliveriesToday'] ?> à livrer auj.</p>
                        <p class="text-xs text-blue-700">Voir le suivi</p>
                    </div>
                </a>
            <?php endif; ?>

            <?php if ($alerts['deliveredNotBilled'] > 0): ?>
                <a href="<?= $escape(app_route_url('factures/creer')) ?>"
                   class="flex items-center gap-3 rounded-md border border-amber-200 bg-amber-50 p-3 hover:bg-amber-100 transition">
                    <i class="fa-solid fa-file-circle-plus text-amber-600 text-lg"></i>
                    <div>
                        <p class="text-sm font-semibold text-amber-800"><?= $alerts['deliveredNotBilled'] ?> à facturer</p>
                        <p class="text-xs text-amber-700">Livrées non facturées</p>
                    </div>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- CHART + PROCHAINES LIVRAISONS -->
    <div class="grid gap-5 lg:grid-cols-3">
        <!-- Chart : CA 7 jours -->
        <article class="rounded-md border border-slate-200 bg-white p-5 lg:col-span-2">
            <h3 class="text-sm font-semibold text-slate-800">
                <i class="fa-solid fa-chart-line text-green-700 mr-2"></i>Chiffre d’affaires — 7 derniers jours
            </h3>
            <div class="mt-4 h-64">
                <canvas id="chartCA"></canvas>
            </div>
        </article>

        <!-- Prochaines livraisons -->
        <article class="rounded-md border border-slate-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-800">
                    <i class="fa-solid fa-truck text-blue-700 mr-2"></i>Prochaines livraisons
                </h3>
                <a href="<?= $escape(app_route_url('livraisons/suivi')) ?>"
                   class="text-xs font-medium text-green-800 hover:underline">Voir tout</a>
            </div>
            <?php if (!$upcomingDeliveries): ?>
                <p class="mt-3 text-sm text-slate-500">Aucune livraison prévue.</p>
            <?php else: ?>
                <ul class="mt-3 space-y-2">
                    <?php foreach ($upcomingDeliveries as $d):
                        $isToday = ($d['due_date'] === $today);
                    ?>
                        <li class="rounded border border-slate-100 p-2 text-sm hover:bg-slate-50">
                            <div class="flex items-center justify-between gap-2">
                                <a href="<?= $escape(app_route_url('commandes/clients')) ?>?q=<?= urlencode($d['ticket_no']) ?>"
                                   class="font-mono text-xs font-semibold text-green-800 hover:underline">
                                    <?= $escape($d['ticket_no']) ?>
                                </a>
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-medium <?= $isToday ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700' ?>">
                                    <?= $isToday ? 'Aujourd’hui' : date('d/m', strtotime($d['due_date'])) ?>
                                </span>
                            </div>
                            <div class="mt-1 truncate text-xs text-slate-700"><?= $escape($d['client']) ?></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </article>
    </div>

    <!-- DERNIÈRES COMMANDES + DERNIÈRES FACTURES -->
    <div class="grid gap-5 lg:grid-cols-2">
        <!-- Dernières commandes -->
        <article class="rounded-md border border-slate-200 bg-white">
            <header class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                <h3 class="text-sm font-semibold text-slate-800">
                    <i class="fa-solid fa-clipboard-list text-amber-700 mr-2"></i>Dernières commandes
                </h3>
                <a href="<?= $escape(app_route_url('commandes/clients')) ?>"
                   class="text-xs font-medium text-green-800 hover:underline">Voir tout</a>
            </header>
            <?php if (!$recentOrders): ?>
                <p class="px-5 py-6 text-sm text-slate-500">Aucune commande.</p>
            <?php else: ?>
                <ul class="divide-y divide-slate-100">
                    <?php foreach ($recentOrders as $o): ?>
                        <li class="flex items-center justify-between gap-3 px-5 py-3 text-sm hover:bg-slate-50">
                            <div class="min-w-0">
                                <a href="<?= $escape(app_route_url('commandes/clients')) ?>?q=<?= urlencode($o['ticket_no']) ?>"
                                   class="font-mono text-xs font-semibold text-green-800 hover:underline">
                                    <?= $escape($o['ticket_no']) ?>
                                </a>
                                <div class="mt-0.5 truncate text-slate-800"><?= $escape($o['client']) ?></div>
                            </div>
                            <div class="flex-shrink-0 text-right">
                                <div class="font-semibold text-slate-800"><?= number_format((float) $o['total_amount'], 0, ',', ' ') ?> FBu</div>
                                <span class="mt-0.5 inline-block rounded-full px-2 py-0.5 text-[10px] font-medium <?= $deliveryColor($o['delivery_status']) ?>">
                                    <?= $escape($o['delivery_status']) ?>
                                </span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </article>

        <!-- Dernières factures -->
        <article class="rounded-md border border-slate-200 bg-white">
            <header class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                <h3 class="text-sm font-semibold text-slate-800">
                    <i class="fa-solid fa-file-invoice text-green-700 mr-2"></i>Dernières factures
                </h3>
                <a href="<?= $escape(app_route_url('factures/liste')) ?>"
                   class="text-xs font-medium text-green-800 hover:underline">Voir tout</a>
            </header>
            <?php if (!$recentInvoices): ?>
                <p class="px-5 py-6 text-sm text-slate-500">Aucune facture.</p>
            <?php else: ?>
                <ul class="divide-y divide-slate-100">
                    <?php foreach ($recentInvoices as $inv):
                        $isPaid = (float) $inv['debt'] <= 0;
                    ?>
                        <li class="flex items-center justify-between gap-3 px-5 py-3 text-sm hover:bg-slate-50">
                            <div class="min-w-0">
                                <a href="<?= $escape(app_route_url('ventes/facture')) . '?id=' . (int) $inv['id'] ?>"
                                   class="font-mono text-xs font-semibold text-green-800 hover:underline">
                                    <?= $escape($inv['invoice_number']) ?>
                                </a>
                                <div class="mt-0.5 truncate text-slate-800"><?= $escape($inv['client_name']) ?></div>
                            </div>
                            <div class="flex-shrink-0 text-right">
                                <div class="font-semibold text-slate-800"><?= number_format((float) $inv['total_amount'], 0, ',', ' ') ?> FBu</div>
                                <span class="mt-0.5 inline-block rounded-full px-2 py-0.5 text-[10px] font-medium <?= $isPaid ? 'bg-green-100 text-green-800' : 'bg-rose-100 text-rose-800' ?>">
                                    <?= $isPaid ? 'Payée' : 'Impayée' ?>
                                </span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </article>
    </div>
</section>

<!-- ========================================================= -->
<!-- CHART.JS                                                  -->
<!-- ========================================================= -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    if (typeof Chart === 'undefined') return;

    Chart.defaults.font.family = 'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif';
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#475569';

    new Chart(document.getElementById('chartCA'), {
        type: 'line',
        data: {
            labels: <?= json_encode($chartLabels, JSON_UNESCAPED_UNICODE) ?>,
            datasets: [{
                label: 'CA (FBu)',
                data: <?= json_encode($chartData, JSON_UNESCAPED_UNICODE) ?>,
                borderColor: '#16a34a',
                backgroundColor: 'rgba(22, 163, 74, 0.15)',
                fill: true,
                tension: 0.35,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#16a34a',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { grid: { display: false } },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(value);
                        }
                    }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (ctx) {
                            return new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(ctx.parsed.y) + ' FBu';
                        }
                    }
                }
            }
        }
    });
});
</script>