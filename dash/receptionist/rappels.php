<?php
if (!defined('ROUTER_LOADED')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

if (!userCan($user, 'rappels.view')) {
    http_response_code(403);
    echo '<p class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</p>';
    return;
}

require_once __DIR__ . '/../../api/database.php';
$db     = Database::getInstance();
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$role   = $user['role_system'];

$today = date('Y-m-d');

// =========================================================================
// 1. DETTES EN RETARD
// =========================================================================
$overdueDebts = [];
try {
    $overdueDebts = $db->fetchAll(
        "SELECT d.id, d.invoice_number, d.client_indebted, d.client_id,
                d.amount, d.paid_amount, (d.amount - d.paid_amount) AS remaining,
                d.due_date, DATEDIFF(CURDATE(), d.due_date) AS days_late
         FROM debts d
         WHERE d.status <> 1
           AND d.due_date IS NOT NULL
           AND d.due_date < CURDATE()
         ORDER BY days_late DESC, remaining DESC
         LIMIT 20"
    );
} catch (Throwable $e) { error_log('overdueDebts: ' . $e->getMessage()); }

// =========================================================================
// 2. FACTURES IMPAYÉES > 15 JOURS
// =========================================================================
$unpaidInvoices = [];
try {
    $unpaidInvoices = $db->fetchAll(
        "SELECT id, invoice_number, client_name, phone, date,
                total_amount, netcash, debt,
                DATEDIFF(CURDATE(), date) AS age
         FROM sales
         WHERE document_type = 'Facture'
           AND status = 'Active'
           AND debt > 0
           AND date <= DATE_SUB(CURDATE(), INTERVAL 15 DAY)
         ORDER BY age DESC, debt DESC
         LIMIT 20"
    );
} catch (Throwable $e) { error_log('unpaidInvoices: ' . $e->getMessage()); }

// =========================================================================
// 3. COMMANDES EN RETARD
// =========================================================================
$lateOrders = [];
try {
    $lateOrders = $db->fetchAll(
        "SELECT t.id, t.ticket_no, t.client, t.priority, t.due_date,
                t.delivery_status, t.assigned_to,
                DATEDIFF(CURDATE(), t.due_date) AS days_late,
                u.f_name, u.l_name
         FROM tasks t
         LEFT JOIN users u ON u.user_name = t.assigned_to
         WHERE t.category = 'Commande'
           AND t.delivery_status IN ('En préparation','Expédiée')
           AND t.due_date IS NOT NULL
           AND t.due_date < CURDATE()
         ORDER BY days_late DESC, t.priority DESC
         LIMIT 20"
    );
} catch (Throwable $e) { error_log('lateOrders: ' . $e->getMessage()); }

// =========================================================================
// 4. COMMANDES À LIVRER AUJOURD'HUI
// =========================================================================
$todayDeliveries = [];
try {
    $todayDeliveries = $db->fetchAll(
        "SELECT t.id, t.ticket_no, t.client, t.priority, t.due_date,
                t.delivery_status, t.assigned_to, t.delivery_address,
                u.f_name, u.l_name
         FROM tasks t
         LEFT JOIN users u ON u.user_name = t.assigned_to
         WHERE t.category = 'Commande'
           AND t.delivery_status IN ('En préparation','Expédiée')
           AND t.due_date = CURDATE()
         ORDER BY t.priority DESC, t.id DESC
         LIMIT 20"
    );
} catch (Throwable $e) { error_log('todayDeliveries: ' . $e->getMessage()); }

// =========================================================================
// 5. COMMANDES LIVRÉES NON FACTURÉES
// =========================================================================
$deliveredNotBilled = [];
try {
    $deliveredNotBilled = $db->fetchAll(
        "SELECT t.id, t.ticket_no, t.client, t.due_date, t.total_amount,
                t.assigned_to, t.updated_at,
                u.f_name, u.l_name
         FROM tasks t
         LEFT JOIN users u ON u.user_name = t.assigned_to
         WHERE t.category = 'Commande'
           AND t.delivery_status = 'Livrée'
           AND (t.invoice_number IS NULL OR t.invoice_number = '')
         ORDER BY t.updated_at ASC, t.id DESC
         LIMIT 20"
    );
} catch (Throwable $e) { error_log('deliveredNotBilled: ' . $e->getMessage()); }

// =========================================================================
// 6. ANNIVERSAIRES À VENIR (14 jours)
// =========================================================================
$birthdays = [];
try {
    $candidates = $db->fetchAll(
        "SELECT id, name, phone, annif, status_client
         FROM partners
         WHERE category = 'client'
           AND annif IS NOT NULL AND annif <> ''"
    );
    $todayTs  = strtotime(date('Y-m-d'));
    $thisYear = (int) date('Y');
    $isLeap   = (bool) date('L');
    $upcoming = [];
    foreach ($candidates as $c) {
        $parts = explode('-', $c['annif']);
        if (count($parts) !== 2) continue;
        $d = (int) $parts[0]; $m = (int) $parts[1];
        if ($d < 1 || $d > 31 || $m < 1 || $m > 12) continue;
        if ($m === 2 && $d === 29 && !$isLeap) { $d = 1; $m = 3; }
        $ts = mktime(0, 0, 0, $m, $d, $thisYear);
        if ($ts < $todayTs) $ts = mktime(0, 0, 0, $m, $d, $thisYear + 1);
        $days = (int) round(($ts - $todayTs) / 86400);
        if ($days > 14) continue;
        $upcoming[] = [
            'id' => (int) $c['id'], 'name' => $c['name'], 'phone' => $c['phone'],
            'status_client' => $c['status_client'], 'days_until' => $days,
            'day' => $d, 'month' => $m,
        ];
    }
    usort($upcoming, fn($a, $b) => $a['days_until'] <=> $b['days_until']);
    $birthdays = $upcoming;
} catch (Throwable $e) { error_log('birthdays: ' . $e->getMessage()); }

$totals = [
    'overdueDebts'       => count($overdueDebts),
    'unpaidInvoices'     => count($unpaidInvoices),
    'lateOrders'         => count($lateOrders),
    'todayDeliveries'    => count($todayDeliveries),
    'deliveredNotBilled' => count($deliveredNotBilled),
    'birthdays'          => count($birthdays),
];

$totalAlerts = $totals['overdueDebts'] + $totals['unpaidInvoices'] + $totals['lateOrders']
             + $totals['todayDeliveries'] + $totals['deliveredNotBilled'];

$priorityColor = static function (string $p): string {
    return match ($p) {
        'Critique' => 'bg-rose-100 text-rose-800',
        'Haute'    => 'bg-amber-100 text-amber-800',
        'Moyenne'  => 'bg-blue-100 text-blue-800',
        'Basse'    => 'bg-slate-100 text-slate-700',
        default    => 'bg-slate-100 text-slate-700',
    };
};
?>
<section class="space-y-5">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-green-800">Communication</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Rappels &amp; alertes</h1>
            <p class="mt-1 text-sm text-slate-600">
                <?php if ($totalAlerts > 0): ?>
                    <strong class="text-rose-700"><?= $totalAlerts ?></strong> alerte(s) active(s) à traiter aujourd’hui.
                <?php else: ?>
                    Aucune alerte active. Tout est à jour ✅
                <?php endif; ?>
            </p>
        </div>
        <div class="text-sm text-slate-500">
            <i class="fa-regular fa-clock mr-1"></i>
            <?= date('d/m/Y H:i') ?>
        </div>
    </header>

    <!-- KPI cards -->
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        <a href="#late-debts" class="rounded-md border border-rose-200 bg-rose-50 p-4 hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <p class="text-xs font-semibold uppercase text-rose-700">Dettes en retard</p>
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
            </div>
            <p class="mt-1 text-2xl font-bold text-rose-800"><?= $totals['overdueDebts'] ?></p>
        </a>
        <a href="#unpaid-invoices" class="rounded-md border border-orange-200 bg-orange-50 p-4 hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <p class="text-xs font-semibold uppercase text-orange-700">Factures impayées</p>
                <i class="fa-solid fa-file-invoice-dollar text-orange-600"></i>
            </div>
            <p class="mt-1 text-2xl font-bold text-orange-800"><?= $totals['unpaidInvoices'] ?></p>
        </a>
        <a href="#late-orders" class="rounded-md border border-amber-200 bg-amber-50 p-4 hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <p class="text-xs font-semibold uppercase text-amber-700">Commandes en retard</p>
                <i class="fa-solid fa-clock-rotate-left text-amber-600"></i>
            </div>
            <p class="mt-1 text-2xl font-bold text-amber-800"><?= $totals['lateOrders'] ?></p>
        </a>
        <a href="#today-deliveries" class="rounded-md border border-blue-200 bg-blue-50 p-4 hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <p class="text-xs font-semibold uppercase text-blue-700">À livrer auj.</p>
                <i class="fa-solid fa-truck-fast text-blue-600"></i>
            </div>
            <p class="mt-1 text-2xl font-bold text-blue-800"><?= $totals['todayDeliveries'] ?></p>
        </a>
        <a href="#not-billed" class="rounded-md border border-slate-200 bg-white p-4 hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <p class="text-xs font-semibold uppercase text-slate-600">Livrées non facturées</p>
                <i class="fa-solid fa-file-circle-plus text-slate-500"></i>
            </div>
            <p class="mt-1 text-2xl font-bold <?= $totals['deliveredNotBilled'] > 0 ? 'text-amber-700' : 'text-slate-400' ?>"><?= $totals['deliveredNotBilled'] ?></p>
        </a>
        <a href="<?= $escape(app_route_url('anniversaires')) ?>" class="rounded-md border border-pink-200 bg-pink-50 p-4 hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <p class="text-xs font-semibold uppercase text-pink-700">Anniversaires</p>
                <i class="fa-solid fa-cake-candles text-pink-600"></i>
            </div>
            <p class="mt-1 text-2xl font-bold text-pink-800"><?= $totals['birthdays'] ?></p>
        </a>
    </div>

    <!-- 1. DETTES EN RETARD -->
    <section id="late-debts" class="rounded-md border border-rose-200 bg-white">
        <header class="flex items-center justify-between gap-3 border-b border-rose-200 bg-rose-50/60 px-5 py-3">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-triangle-exclamation text-rose-600 text-lg"></i>
                <div>
                    <h2 class="font-semibold text-rose-900">Dettes en retard</h2>
                    <p class="text-xs text-rose-700">Échéance dépassée, non soldées</p>
                </div>
            </div>
            <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-800"><?= $totals['overdueDebts'] ?></span>
        </header>
        <?php if (!$overdueDebts): ?>
            <p class="px-5 py-6 text-sm text-slate-500">Aucune dette en retard ✅</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-2">Facture</th>
                            <th class="px-4 py-2">Client</th>
                            <th class="px-4 py-2">Échéance</th>
                            <th class="px-4 py-2">Retard</th>
                            <th class="px-4 py-2 text-right">Reste dû</th>
                            <th class="px-4 py-2 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($overdueDebts as $d): ?>
                            <tr class="hover:bg-rose-50/30">
                                <td class="px-4 py-2 font-mono text-xs font-semibold text-slate-800"><?= $escape($d['invoice_number']) ?></td>
                                <td class="px-4 py-2 text-slate-800"><?= $escape($d['client_indebted']) ?></td>
                                <td class="px-4 py-2 text-slate-600"><?= $escape(date('d/m/Y', strtotime($d['due_date']))) ?></td>
                                <td class="px-4 py-2">
                                    <span class="rounded-full bg-rose-100 px-2 py-0.5 text-xs font-medium text-rose-800">
                                        +<?= (int) $d['days_late'] ?> j
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-right font-semibold text-rose-700">
                                    <?= number_format((float) $d['remaining'], 0, ',', ' ') ?> FBu
                                </td>
                                <td class="px-4 py-2 text-right">
                                    <a href="<?= $escape(app_route_url('caisse/transactions')) ?>?q=<?= urlencode($d['invoice_number']) ?>"
                                       class="inline-flex items-center gap-1 rounded border border-slate-300 px-2 py-1 text-xs hover:bg-slate-100">
                                        <i class="fa-solid fa-eye"></i> Voir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <!-- 2. FACTURES IMPAYÉES > 15 JOURS -->
    <section id="unpaid-invoices" class="rounded-md border border-orange-200 bg-white">
        <header class="flex items-center justify-between gap-3 border-b border-orange-200 bg-orange-50/60 px-5 py-3">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-file-invoice-dollar text-orange-600 text-lg"></i>
                <div>
                    <h2 class="font-semibold text-orange-900">Factures impayées depuis plus de 15 jours</h2>
                    <p class="text-xs text-orange-700">Relancer les clients</p>
                </div>
            </div>
            <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-semibold text-orange-800"><?= $totals['unpaidInvoices'] ?></span>
        </header>
        <?php if (!$unpaidInvoices): ?>
            <p class="px-5 py-6 text-sm text-slate-500">Aucune facture impayée ancienne ✅</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-2">Facture</th>
                            <th class="px-4 py-2">Client</th>
                            <th class="px-4 py-2">Date</th>
                            <th class="px-4 py-2">Âge</th>
                            <th class="px-4 py-2 text-right">Reste dû</th>
                            <th class="px-4 py-2 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($unpaidInvoices as $inv): ?>
                            <tr class="hover:bg-orange-50/30">
                                <td class="px-4 py-2 font-mono text-xs font-semibold text-slate-800"><?= $escape($inv['invoice_number']) ?></td>
                                <td class="px-4 py-2">
                                    <div class="font-medium text-slate-800"><?= $escape($inv['client_name']) ?></div>
                                    <?php if (!empty($inv['phone'])): ?>
                                        <div class="text-xs text-slate-500"><?= $escape($inv['phone']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-2 text-slate-600"><?= $escape(date('d/m/Y', strtotime($inv['date']))) ?></td>
                                <td class="px-4 py-2">
                                    <span class="rounded-full bg-orange-100 px-2 py-0.5 text-xs font-medium text-orange-800">
                                        <?= (int) $inv['age'] ?> j
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-right font-semibold text-rose-700">
                                    <?= number_format((float) $inv['debt'], 0, ',', ' ') ?> FBu
                                </td>
                                <td class="px-4 py-2 text-right">
                                    <a href="<?= $escape(app_route_url('caisse/transactions')) ?>?q=<?= urlencode($inv['invoice_number']) ?>"
                                       class="inline-flex items-center gap-1 rounded border border-slate-300 px-2 py-1 text-xs hover:bg-slate-100">
                                        <i class="fa-solid fa-eye"></i> Voir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <!-- 3. COMMANDES EN RETARD -->
    <section id="late-orders" class="rounded-md border border-amber-200 bg-white">
        <header class="flex items-center justify-between gap-3 border-b border-amber-200 bg-amber-50/60 px-5 py-3">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-clock-rotate-left text-amber-600 text-lg"></i>
                <div>
                    <h2 class="font-semibold text-amber-900">Commandes en retard</h2>
                    <p class="text-xs text-amber-700">Livraison prévue dépassée</p>
                </div>
            </div>
            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800"><?= $totals['lateOrders'] ?></span>
        </header>
        <?php if (!$lateOrders): ?>
            <p class="px-5 py-6 text-sm text-slate-500">Aucune commande en retard ✅</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-2">Ticket</th>
                            <th class="px-4 py-2">Client</th>
                            <th class="px-4 py-2">Priorité</th>
                            <th class="px-4 py-2">Assigné</th>
                            <th class="px-4 py-2">Prévue le</th>
                            <th class="px-4 py-2">Retard</th>
                            <th class="px-4 py-2 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($lateOrders as $o):
                            $who = trim(($o['f_name'] ?? '') . ' ' . ($o['l_name'] ?? '')) ?: ($o['assigned_to'] ?? '—');
                        ?>
                            <tr class="hover:bg-amber-50/30">
                                <td class="px-4 py-2 font-mono text-xs font-semibold text-slate-800"><?= $escape($o['ticket_no']) ?></td>
                                <td class="px-4 py-2 text-slate-800"><?= $escape($o['client']) ?></td>
                                <td class="px-4 py-2">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium <?= $priorityColor($o['priority']) ?>">
                                        <?= $escape($o['priority']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-slate-700"><?= $escape($who) ?></td>
                                <td class="px-4 py-2 text-slate-600"><?= $escape(date('d/m/Y', strtotime($o['due_date']))) ?></td>
                                <td class="px-4 py-2">
                                    <span class="rounded-full bg-rose-100 px-2 py-0.5 text-xs font-medium text-rose-800">
                                        +<?= (int) $o['days_late'] ?> j
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-right">
                                    <a href="<?= $escape(app_route_url('commandes/clients')) ?>?q=<?= urlencode($o['ticket_no']) ?>"
                                       class="inline-flex items-center gap-1 rounded border border-slate-300 px-2 py-1 text-xs hover:bg-slate-100">
                                        <i class="fa-solid fa-eye"></i> Voir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <!-- 4. À LIVRER AUJOURD'HUI -->
    <section id="today-deliveries" class="rounded-md border border-blue-200 bg-white">
        <header class="flex items-center justify-between gap-3 border-b border-blue-200 bg-blue-50/60 px-5 py-3">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-truck-fast text-blue-600 text-lg"></i>
                <div>
                    <h2 class="font-semibold text-blue-900">À livrer aujourd’hui</h2>
                    <p class="text-xs text-blue-700"><?= date('d/m/Y') ?></p>
                </div>
            </div>
            <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800"><?= $totals['todayDeliveries'] ?></span>
        </header>
        <?php if (!$todayDeliveries): ?>
            <p class="px-5 py-6 text-sm text-slate-500">Aucune livraison prévue aujourd’hui.</p>
        <?php else: ?>
            <ul class="divide-y divide-slate-100">
                <?php foreach ($todayDeliveries as $o):
                    $who = trim(($o['f_name'] ?? '') . ' ' . ($o['l_name'] ?? '')) ?: ($o['assigned_to'] ?? '—');
                ?>
                    <li class="flex flex-col gap-2 px-5 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs font-semibold text-slate-800"><?= $escape($o['ticket_no']) ?></span>
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium <?= $priorityColor($o['priority']) ?>">
                                    <?= $escape($o['priority']) ?>
                                </span>
                            </div>
                            <div class="mt-1 text-sm text-slate-800"><?= $escape($o['client']) ?></div>
                            <?php if (!empty($o['delivery_address'])): ?>
                                <div class="text-xs text-slate-500">
                                    <i class="fa-solid fa-location-dot mr-1"></i><?= $escape($o['delivery_address']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-500"><?= $escape($who) ?></span>
                            <a href="<?= $escape(app_route_url('commandes/clients')) ?>?q=<?= urlencode($o['ticket_no']) ?>"
                               class="inline-flex items-center gap-1 rounded border border-slate-300 px-2 py-1 text-xs hover:bg-slate-100">
                                <i class="fa-solid fa-eye"></i> Voir
                            </a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <!-- 5. LIVRÉES NON FACTURÉES -->
    <section id="not-billed" class="rounded-md border border-slate-200 bg-white">
        <header class="flex items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 px-5 py-3">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-file-circle-plus text-slate-600 text-lg"></i>
                <div>
                    <h2 class="font-semibold text-slate-900">Livrées mais pas encore facturées</h2>
                    <p class="text-xs text-slate-600">À convertir en facture rapidement</p>
                </div>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700"><?= $totals['deliveredNotBilled'] ?></span>
        </header>
        <?php if (!$deliveredNotBilled): ?>
            <p class="px-5 py-6 text-sm text-slate-500">Tout est facturé ✅</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-2">Ticket</th>
                            <th class="px-4 py-2">Client</th>
                            <th class="px-4 py-2">Livré le</th>
                            <th class="px-4 py-2">Assigné</th>
                            <th class="px-4 py-2 text-right">Total</th>
                            <th class="px-4 py-2 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($deliveredNotBilled as $o):
                            $who = trim(($o['f_name'] ?? '') . ' ' . ($o['l_name'] ?? '')) ?: ($o['assigned_to'] ?? '—');
                        ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-2 font-mono text-xs font-semibold text-slate-800"><?= $escape($o['ticket_no']) ?></td>
                                <td class="px-4 py-2 text-slate-800"><?= $escape($o['client']) ?></td>
                                <td class="px-4 py-2 text-slate-600">
                                    <?= !empty($o['updated_at']) ? $escape(date('d/m/Y', strtotime($o['updated_at']))) : '—' ?>
                                </td>
                                <td class="px-4 py-2 text-slate-700"><?= $escape($who) ?></td>
                                <td class="px-4 py-2 text-right font-semibold text-slate-800">
                                    <?= number_format((float) $o['total_amount'], 0, ',', ' ') ?> FBu
                                </td>
                                <td class="px-4 py-2 text-right">
                                    <a href="<?= $escape(app_route_url('factures/apercu')) ?>?id=<?= (int) $o['id'] ?>"
                                       class="inline-flex items-center gap-1 rounded bg-green-700 px-2 py-1 text-xs font-semibold text-white hover:bg-green-800">
                                        <i class="fa-solid fa-file-circle-plus"></i> Facturer
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <!-- 6. ANNIVERSAIRES -->
    <section class="rounded-md border border-pink-200 bg-white">
        <header class="flex items-center justify-between gap-3 border-b border-pink-200 bg-pink-50/60 px-5 py-3">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-cake-candles text-pink-600 text-lg"></i>
                <div>
                    <h2 class="font-semibold text-pink-900">Anniversaires dans les 14 prochains jours</h2>
                    <p class="text-xs text-pink-700">Préparer un message ou une promo</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="rounded-full bg-pink-100 px-3 py-1 text-xs font-semibold text-pink-800"><?= $totals['birthdays'] ?></span>
                <a href="<?= $escape(app_route_url('anniversaires')) ?>"
                   class="text-xs font-medium text-pink-800 hover:underline">Voir tout →</a>
            </div>
        </header>
        <?php if (!$birthdays): ?>
            <p class="px-5 py-6 text-sm text-slate-500">Aucun anniversaire dans les 14 prochains jours.</p>
        <?php else: ?>
            <ul class="divide-y divide-slate-100">
                <?php foreach ($birthdays as $b): ?>
                    <li class="flex flex-col gap-2 px-5 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="font-medium text-slate-800">
                                <i class="fa-solid <?= $b['days_until'] === 0 ? 'fa-gift text-pink-600' : 'fa-cake-candles text-pink-500' ?> mr-2"></i>
                                <?= $escape($b['name']) ?>
                            </div>
                            <div class="text-xs text-slate-500">
                                <i class="fa-solid fa-phone mr-1"></i><?= $escape($b['phone'] ?: '—') ?>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium <?= $b['status_client'] === 'VIP' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600' ?>">
                                <?= $escape($b['status_client']) ?>
                            </span>
                            <span class="rounded-full bg-pink-100 px-2 py-0.5 text-xs font-medium text-pink-800">
                                <?= sprintf('%02d/%02d', $b['day'], $b['month']) ?>
                            </span>
                            <span class="text-xs font-semibold <?= $b['days_until'] === 0 ? 'text-pink-700' : 'text-slate-600' ?>">
                                <?php if ($b['days_until'] === 0): ?>
                                    Aujourd’hui
                                <?php elseif ($b['days_until'] === 1): ?>
                                    Demain
                                <?php else: ?>
                                    dans <?= $b['days_until'] ?> j
                                <?php endif; ?>
                            </span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</section>