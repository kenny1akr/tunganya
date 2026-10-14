<?php
if (!defined('ROUTER_LOADED')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

if (!userCan($user, 'livraisons.view')) {
    http_response_code(403);
    echo '<p class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</p>';
    return;
}

require_once __DIR__ . '/../../api/database.php';
require_once __DIR__ . '/_sales.php';

$db     = Database::getInstance();
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$role   = $user['role_system'];

$flashMessage = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);

// =========================================================================
// FILTRES
// =========================================================================
$search   = trim((string) ($_GET['q']    ?? ''));
$delivery = (string) ($_GET['delivery']  ?? '');
$fromDate = (string) ($_GET['du']        ?? '');
$toDate   = (string) ($_GET['au']        ?? '');
$page     = max(1, (int) ($_GET['page'] ?? 1));
$perPage  = 20;

$allowedDeliveries = ['En préparation','Expédiée','Livrée','Annulée'];
if (!in_array($delivery, $allowedDeliveries, true)) $delivery = '';

$validDate = static function (string $v): bool {
    if ($v === '') return false;
    $d = DateTime::createFromFormat('!Y-m-d', $v);
    return $d !== false && $d->format('Y-m-d') === $v;
};
if (!$validDate($fromDate)) $fromDate = '';
if (!$validDate($toDate))   $toDate   = '';

$conditions = ["t.category = 'Commande'"];
$params     = [];

if ($search !== '') {
    $conditions[] = "(t.ticket_no LIKE ? OR t.client LIKE ? OR t.client_reference LIKE ? OR t.delivery_address LIKE ?)";
    $p = '%' . $search . '%';
    array_push($params, $p, $p, $p, $p);
}
if ($delivery !== '') {
    $conditions[] = 't.delivery_status = ?';
    $params[] = $delivery;
}
if ($fromDate !== '') {
    $conditions[] = 'COALESCE(t.order_date, t.created_at) >= ?';
    $params[] = $fromDate;
}
if ($toDate !== '') {
    $conditions[] = 'COALESCE(t.order_date, t.created_at) <= ?';
    $params[] = $toDate;
}

$where = 'WHERE ' . implode(' AND ', $conditions);

// =========================================================================
// LISTE PAGINÉE
// =========================================================================
$total      = (int) $db->fetchColumn("SELECT COUNT(*) FROM tasks t $where", $params);
$totalPages = max(1, (int) ceil($total / $perPage));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * $perPage;

$deliveries = $db->fetchAll(
    "SELECT
        t.id, t.ticket_no, t.client, t.client_id, t.walk_in, t.client_reference,
        t.priority, t.assigned_to, t.order_date, t.due_date,
        t.delivery_address, t.status, t.delivery_status,
        t.invoice_number, t.converted_at, t.total_amount,
        u.f_name, u.l_name, u.category AS assignee_category
     FROM tasks t
     LEFT JOIN users u ON u.user_name = t.assigned_to
     $where
     ORDER BY t.due_date ASC, t.id DESC
     LIMIT $perPage OFFSET $offset",
    $params
);

// =========================================================================
// STATS — Compteurs globaux
// =========================================================================
$counts = $db->fetch(
    "SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN delivery_status = 'En préparation' THEN 1 ELSE 0 END) AS prep,
        SUM(CASE WHEN delivery_status = 'Expédiée'       THEN 1 ELSE 0 END) AS ship,
        SUM(CASE WHEN delivery_status = 'Livrée'         THEN 1 ELSE 0 END) AS done,
        SUM(CASE WHEN delivery_status = 'Annulée'        THEN 1 ELSE 0 END) AS cancel,
        SUM(CASE WHEN delivery_status = 'Livrée' AND (invoice_number IS NULL OR invoice_number = '') THEN 1 ELSE 0 END) AS done_not_billed,
        SUM(CASE WHEN invoice_number IS NOT NULL AND invoice_number <> '' THEN 1 ELSE 0 END) AS billed
     FROM tasks WHERE category = 'Commande'"
) ?: [
    'total' => 0, 'prep' => 0, 'ship' => 0, 'done' => 0, 'cancel' => 0,
    'done_not_billed' => 0, 'billed' => 0,
];

// =========================================================================
// STATS — Priorité
// =========================================================================
$priorityOrder  = ['Critique', 'Haute', 'Moyenne', 'Basse'];
$priorityValues = [0, 0, 0, 0];

$byPriority = $db->fetchAll(
    "SELECT priority, COUNT(*) AS nb
     FROM tasks
     WHERE category = 'Commande'
     GROUP BY priority"
);

foreach ($byPriority as $row) {
    $idx = array_search($row['priority'], $priorityOrder, true);
    if ($idx !== false) $priorityValues[$idx] = (int) $row['nb'];
}

// =========================================================================
// STATS — Série 14 jours
// =========================================================================
$daysBack   = 14;
$dayLabels  = [];
$dayPrep    = [];
$dayShip    = [];
$dayDone    = [];
$dayCancel  = [];
$dayOverdue = [];

for ($i = $daysBack - 1; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-{$i} days"));
    $dayLabels[]  = date('d/m', strtotime($d));
    $dayPrep[]    = 0;
    $dayShip[]    = 0;
    $dayDone[]    = 0;
    $dayCancel[]  = 0;
    $dayOverdue[] = 0;
}

$startDate = date('Y-m-d', strtotime('-' . ($daysBack - 1) . ' days'));

$byDay = $db->fetchAll(
    "SELECT COALESCE(order_date, created_at) AS d, delivery_status, COUNT(*) AS nb
     FROM tasks
     WHERE category = 'Commande'
       AND COALESCE(order_date, created_at) >= ?
     GROUP BY d, delivery_status
     ORDER BY d ASC",
    [$startDate]
);

foreach ($byDay as $row) {
    $idx = array_search(date('d/m', strtotime($row['d'])), $dayLabels, true);
    if ($idx === false) continue;
    $n = (int) $row['nb'];
    switch ($row['delivery_status']) {
        case 'En préparation': $dayPrep[$idx]   = $n; break;
        case 'Expédiée':       $dayShip[$idx]   = $n; break;
        case 'Livrée':         $dayDone[$idx]   = $n; break;
        case 'Annulée':        $dayCancel[$idx] = $n; break;
    }
}

$overdueByDay = $db->fetchAll(
    "SELECT due_date AS d, COUNT(*) AS nb
     FROM tasks
     WHERE category = 'Commande'
       AND delivery_status IN ('En préparation','Expédiée')
       AND due_date IS NOT NULL
       AND due_date >= ?
       AND due_date < CURDATE()
     GROUP BY due_date
     ORDER BY due_date ASC",
    [$startDate]
);

foreach ($overdueByDay as $row) {
    $idx = array_search(date('d/m', strtotime($row['d'])), $dayLabels, true);
    if ($idx === false) continue;
    $dayOverdue[$idx] = (int) $row['nb'];
}

// =========================================================================
// STATS — Charge par assigné
// =========================================================================
$topAssignees = $db->fetchAll(
    "SELECT
        t.assigned_to,
        COALESCE(CONCAT(u.f_name, ' ', u.l_name), t.assigned_to) AS name,
        COUNT(*) AS nb,
        SUM(CASE WHEN t.delivery_status = 'Livrée' THEN 1 ELSE 0 END) AS done
     FROM tasks t
     LEFT JOIN users u ON u.user_name = t.assigned_to
     WHERE t.category = 'Commande' AND t.assigned_to <> ''
     GROUP BY t.assigned_to, name
     ORDER BY nb DESC
     LIMIT 6"
);

$assigneeLabels = [];
$assigneeNb     = [];
$assigneeDone   = [];
foreach ($topAssignees as $row) {
    $assigneeLabels[] = $row['name'] ?: $row['assigned_to'];
    $assigneeNb[]     = (int) $row['nb'];
    $assigneeDone[]   = (int) $row['done'];
}

// =========================================================================
// ALERTES
// =========================================================================
$today = date('Y-m-d');

$overdueRow = $db->fetch(
    "SELECT COUNT(*) AS nb
     FROM tasks
     WHERE category = 'Commande'
       AND delivery_status IN ('En préparation','Expédiée')
       AND due_date IS NOT NULL
       AND due_date < CURDATE()"
);
$overdueCount = (int) ($overdueRow['nb'] ?? 0);

$dueSoonRow = $db->fetch(
    "SELECT COUNT(*) AS nb
     FROM tasks
     WHERE category = 'Commande'
       AND delivery_status IN ('En préparation','Expédiée')
       AND due_date = CURDATE()"
);
$dueTodayCount = (int) ($dueSoonRow['nb'] ?? 0);

// =========================================================================
// URLs
// =========================================================================
$queryUrl = static function (int $targetPage) use ($search, $delivery, $fromDate, $toDate, $role): string {
    return app_route_url('livraisons/suivi', $role) . '?' . http_build_query([
        'q' => $search, 'delivery' => $delivery, 'du' => $fromDate, 'au' => $toDate, 'page' => $targetPage,
    ]);
};

$tabUrl = static function (string $newDelivery) use ($search, $fromDate, $toDate, $role): string {
    return app_route_url('livraisons/suivi', $role) . '?' . http_build_query([
        'q' => $search, 'delivery' => $newDelivery, 'du' => $fromDate, 'au' => $toDate, 'page' => 1,
    ]);
};

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

$dueBadge = static function (?string $due, string $status, string $today): array {
    if (empty($due) || $status === 'Livrée' || $status === 'Annulée') {
        return ['', ''];
    }
    if ($due < $today) {
        return ['En retard', 'bg-rose-100 text-rose-800'];
    }
    if ($due === $today) {
        return ['Aujourd’hui', 'bg-amber-100 text-amber-800'];
    }
    return ['', ''];
};
?>
<section class="space-y-5">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-green-800">Logistique</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Suivi des livraisons</h1>
            <p class="mt-1 text-sm text-slate-600">Vue opérationnelle avec statistiques et graphiques.</p>
        </div>
        <a href="<?= $escape(app_route_url('livraisons/liste')) ?>"
           class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
            <i data-feather="list" class="h-4 w-4"></i> Liste bordereaux
        </a>
    </header>

    <?php if ($flashMessage): ?>
        <div role="status" class="rounded-md border px-4 py-3 text-sm <?= ($flashMessage['type'] ?? '') === 'success' ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-800' ?>">
            <?= $escape($flashMessage['text'] ?? '') ?>
        </div>
    <?php endif; ?>

    <!-- KPI CARDS -->
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <article class="rounded-md border border-orange-200 bg-orange-50 p-4">
            <div class="flex items-start justify-between">
                <p class="text-xs font-semibold uppercase text-orange-700">En préparation</p>
                <i data-feather="package" class="h-4 w-4 text-orange-600"></i>
            </div>
            <p class="mt-1 text-2xl font-bold text-orange-800"><?= (int) $counts['prep'] ?></p>
        </article>
        <article class="rounded-md border border-blue-200 bg-blue-50 p-4">
            <div class="flex items-start justify-between">
                <p class="text-xs font-semibold uppercase text-blue-700">Expédiées</p>
                <i data-feather="truck" class="h-4 w-4 text-blue-600"></i>
            </div>
            <p class="mt-1 text-2xl font-bold text-blue-800"><?= (int) $counts['ship'] ?></p>
        </article>
        <article class="rounded-md border border-green-200 bg-green-50 p-4">
            <div class="flex items-start justify-between">
                <p class="text-xs font-semibold uppercase text-green-700">Livrées</p>
                <i data-feather="check-circle" class="h-4 w-4 text-green-600"></i>
            </div>
            <p class="mt-1 text-2xl font-bold text-green-800"><?= (int) $counts['done'] ?></p>
        </article>
        <article class="rounded-md border border-slate-200 bg-white p-4">
            <div class="flex items-start justify-between">
                <p class="text-xs font-semibold uppercase text-slate-600">Livrées · à facturer</p>
                <i data-feather="file-plus" class="h-4 w-4 text-slate-500"></i>
            </div>
            <p class="mt-1 text-2xl font-bold <?= (int) $counts['done_not_billed'] > 0 ? 'text-amber-700' : 'text-slate-400' ?>"><?= (int) $counts['done_not_billed'] ?></p>
        </article>
    </div>

    <!-- ALERTES -->
    <?php if ($overdueCount > 0 || $dueTodayCount > 0): ?>
        <div class="grid gap-3 sm:grid-cols-2">
            <?php if ($overdueCount > 0): ?>
                <div class="flex items-center gap-3 rounded-md border border-rose-200 bg-rose-50 p-4">
                    <i data-feather="alert-triangle" class="h-6 w-6 text-rose-600"></i>
                    <div>
                        <p class="text-sm font-semibold text-rose-800"><?= $overdueCount ?> commande(s) en retard</p>
                        <p class="text-xs text-rose-700">La date de livraison prévue est dépassée.</p>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($dueTodayCount > 0): ?>
                <div class="flex items-center gap-3 rounded-md border border-amber-200 bg-amber-50 p-4">
                    <i data-feather="clock" class="h-6 w-6 text-amber-600"></i>
                    <div>
                        <p class="text-sm font-semibold text-amber-800"><?= $dueTodayCount ?> commande(s) à livrer aujourd’hui</p>
                        <p class="text-xs text-amber-700">Prévue pour le <?= date('d/m/Y') ?>.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- GRAPHIQUES — BARS & LINES -->
    <div class="grid gap-4 lg:grid-cols-2">
        <article class="rounded-md border border-slate-200 bg-white p-5">
            <h3 class="text-sm font-semibold text-slate-800">Commandes par statut</h3>
            <p class="mt-1 text-xs text-slate-500">Comparaison actuelle des 4 états.</p>
            <div class="mt-4 h-64">
                <canvas id="chartStatus"></canvas>
            </div>
        </article>

        <article class="rounded-md border border-slate-200 bg-white p-5">
            <h3 class="text-sm font-semibold text-slate-800">Commandes par priorité</h3>
            <p class="mt-1 text-xs text-slate-500">Répartition selon l’urgence.</p>
            <div class="mt-4 h-64">
                <canvas id="chartPriority"></canvas>
            </div>
        </article>

        <article class="rounded-md border border-slate-200 bg-white p-5 lg:col-span-2">
            <h3 class="text-sm font-semibold text-slate-800">Commandes créées par jour (14 derniers jours)</h3>
            <p class="mt-1 text-xs text-slate-500">Empilées par statut de livraison.</p>
            <div class="mt-4 h-72">
                <canvas id="chartDaily"></canvas>
            </div>
        </article>

        <article class="rounded-md border border-slate-200 bg-white p-5 lg:col-span-2">
            <h3 class="text-sm font-semibold text-slate-800">Évolution des retards (14 derniers jours)</h3>
            <p class="mt-1 text-xs text-slate-500">Commandes dont la date de livraison prévue est dépassée.</p>
            <div class="mt-4 h-72">
                <canvas id="chartOverdue"></canvas>
            </div>
        </article>

        <article class="rounded-md border border-slate-200 bg-white p-5 lg:col-span-2">
            <h3 class="text-sm font-semibold text-slate-800">Charge par magasinier / travailleur</h3>
            <p class="mt-1 text-xs text-slate-500">Top 6 — commandes assignées vs. livrées.</p>
            <div class="mt-4 h-72">
                <canvas id="chartAssignees"></canvas>
            </div>
        </article>
    </div>

    <!-- ONGLETS -->
    <nav class="flex flex-wrap gap-2 rounded-md border border-slate-200 bg-white p-2">
        <?php
        $tabs = [
            ''               => ['Toutes',          (int) $counts['total'],  'bg-slate-100 text-slate-700'],
            'En préparation' => ['En préparation',  (int) $counts['prep'],   'bg-orange-100 text-orange-800'],
            'Expédiée'       => ['Expédiées',       (int) $counts['ship'],   'bg-blue-100 text-blue-800'],
            'Livrée'         => ['Livrées',         (int) $counts['done'],   'bg-green-100 text-green-800'],
            'Annulée'        => ['Annulées',        (int) $counts['cancel'], 'bg-slate-200 text-slate-600'],
        ];
        foreach ($tabs as $key => [$label, $count, $activeClass]):
            $isActive = $delivery === $key;
        ?>
            <a href="<?= $escape($tabUrl($key)) ?>"
               class="inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition <?= $isActive ? $activeClass . ' ring-2 ring-offset-1 ring-slate-300' : 'text-slate-600 hover:bg-slate-50' ?>">
                <?= $escape($label) ?>
                <span class="rounded-full bg-white/70 px-2 py-0.5 text-xs font-semibold"><?= number_format($count, 0, ',', ' ') ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- FILTRES -->
    <form method="get" action="<?= $escape(app_url('router.php')) ?>"
          class="grid gap-3 rounded-md border border-slate-200 bg-white p-4 sm:grid-cols-2 lg:grid-cols-4">
        <input type="hidden" name="route" value="<?= $escape($role . '/livraisons/suivi') ?>">
        <input type="hidden" name="delivery" value="<?= $escape($delivery) ?>">

        <label class="space-y-1 text-sm font-medium text-slate-700 lg:col-span-2">
            Recherche
            <input name="q" value="<?= $escape($search) ?>" placeholder="Ticket, client, adresse…"
                   class="w-full rounded-md border border-slate-300 px-3 py-2 font-normal">
        </label>

        <label class="space-y-1 text-sm font-medium text-slate-700">
            Du
            <input type="date" name="du" value="<?= $escape($fromDate) ?>"
                   class="w-full rounded-md border border-slate-300 px-3 py-2 font-normal">
        </label>

        <label class="space-y-1 text-sm font-medium text-slate-700">
            Au
            <input type="date" name="au" value="<?= $escape($toDate) ?>"
                   class="w-full rounded-md border border-slate-300 px-3 py-2 font-normal">
        </label>

        <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-4">
            <button class="inline-flex items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium hover:bg-slate-50">
                <i data-feather="search" class="h-4 w-4"></i> Filtrer
            </button>
            <?php if ($search !== '' || $fromDate !== '' || $toDate !== ''): ?>
                <a href="<?= $escape(app_route_url('livraisons/suivi')) ?>"
                   class="inline-flex items-center justify-center rounded-md px-3 py-2 text-sm text-slate-600 hover:bg-slate-100">Effacer</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- TABLEAU -->
    <div class="overflow-x-auto rounded-md border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">Ticket</th>
                    <th class="px-4 py-3">Client</th>
                    <th class="px-4 py-3">Priorité</th>
                    <th class="px-4 py-3">Assigné</th>
                    <th class="px-4 py-3">Prévue le</th>
                    <th class="px-4 py-3">Statut</th>
                    <th class="px-4 py-3">Facture</th>
                    <th class="px-4 py-3 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (!$deliveries): ?>
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center">
                            <p class="font-medium text-slate-700">Aucune commande à suivre</p>
                            <p class="mt-1 text-sm text-slate-500">Modifiez les filtres ou créez une nouvelle commande.</p>
                        </td>
                    </tr>
                <?php else: foreach ($deliveries as $d):
                    $assigneeName = trim(($d['f_name'] ?? '') . ' ' . ($d['l_name'] ?? '')) ?: ($d['assigned_to'] ?? '—');
                    [$dueLabel, $dueClass] = $dueBadge($d['due_date'] ?: null, $d['delivery_status'], $today);
                    $isBilled = !empty($d['invoice_number']);
                ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <a href="<?= $escape(app_route_url('commandes/clients')) . '?q=' . urlencode($d['ticket_no']) ?>"
                               class="font-mono text-xs font-semibold text-green-800 hover:underline">
                                <?= $escape($d['ticket_no']) ?>
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-800">
                                <?= $escape($d['client']) ?>
                                <?php if ((int) $d['walk_in'] === 1): ?>
                                    <span class="ml-1 rounded bg-slate-200 px-1.5 py-0.5 text-[10px] font-medium uppercase text-slate-700">Passage</span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($d['delivery_address'])): ?>
                                <div class="text-xs text-slate-500"><?= $escape($d['delivery_address']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium <?= $priorityColor($d['priority']) ?>">
                                <?= $escape($d['priority']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-700"><?= $escape($assigneeName) ?></td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate-600">
                            <?php if (!empty($d['due_date'])): ?>
                                <div><?= $escape(date('d/m/Y', strtotime($d['due_date']))) ?></div>
                                <?php if ($dueLabel): ?>
                                    <span class="mt-1 inline-block rounded-full px-2 py-0.5 text-[10px] font-medium <?= $dueClass ?>">
                                        <?= $escape($dueLabel) ?>
                                    </span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-slate-400">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium <?= $deliveryColor($d['delivery_status']) ?>">
                                <?= $escape($d['delivery_status']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <?php if ($isBilled): ?>
                                <a href="<?= $escape(app_route_url('factures/liste')) . '?q=' . urlencode($d['invoice_number']) ?>"
                                   class="inline-flex items-center gap-1 text-xs font-medium text-green-800 hover:underline">
                                    <i data-feather="file-text" class="h-3 w-3"></i>
                                    <?= $escape($d['invoice_number']) ?>
                                </a>
                            <?php else: ?>
                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-medium text-amber-800">
                                    À facturer
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-slate-900">
                            <?= number_format((float) $d['total_amount'], 0, ',', ' ') ?> FBu
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <nav class="flex items-center justify-between gap-3">
            <span class="text-sm text-slate-600">Page <?= $page ?> sur <?= $totalPages ?></span>
            <div class="flex gap-2">
                <?php if ($page > 1): ?>
                    <a href="<?= $escape($queryUrl($page - 1)) ?>" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-50">Précédent</a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="<?= $escape($queryUrl($page + 1)) ?>" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-50">Suivant</a>
                <?php endif; ?>
            </div>
        </nav>
    <?php endif; ?>
</section>

<!-- CHART.JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    if (typeof Chart === 'undefined') {
        console.warn('[livraison_suivi] Chart.js non chargé — les graphiques ne s’affichent pas.');
        return;
    }

    var COLORS = {
        prep:   '#fb923c',
        ship:   '#60a5fa',
        done:   '#4ade80',
        cancel: '#cbd5e1',
        critic: '#fb7185',
        high:   '#fbbf24',
        medium: '#818cf8',
        low:    '#cbd5e1',
        red:    '#ef4444'
    };

    Chart.defaults.font.family = 'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif';
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#475569';

    // 1. BAR — Statut
    new Chart(document.getElementById('chartStatus'), {
        type: 'bar',
        data: {
            labels: ['En préparation', 'Expédiée', 'Livrée', 'Annulée'],
            datasets: [{
                label: 'Commandes',
                data: [
                    <?= (int) $counts['prep'] ?>,
                    <?= (int) $counts['ship'] ?>,
                    <?= (int) $counts['done'] ?>,
                    <?= (int) $counts['cancel'] ?>
                ],
                backgroundColor: [COLORS.prep, COLORS.ship, COLORS.done, COLORS.cancel],
                borderRadius: 6,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0 } }
            },
            plugins: { legend: { display: false } }
        }
    });

    // 2. BAR — Priorité
    new Chart(document.getElementById('chartPriority'), {
        type: 'bar',
        data: {
            labels: ['Critique', 'Haute', 'Moyenne', 'Basse'],
            datasets: [{
                label: 'Commandes',
                data: [
                    <?= (int) $priorityValues[0] ?>,
                    <?= (int) $priorityValues[1] ?>,
                    <?= (int) $priorityValues[2] ?>,
                    <?= (int) $priorityValues[3] ?>
                ],
                backgroundColor: [COLORS.critic, COLORS.high, COLORS.medium, COLORS.low],
                borderRadius: 6,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0 } }
            },
            plugins: { legend: { display: false } }
        }
    });

    // 3. STACKED BAR — Commandes par jour
    new Chart(document.getElementById('chartDaily'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($dayLabels, JSON_UNESCAPED_UNICODE) ?>,
            datasets: [
                { label: 'En préparation', data: <?= json_encode($dayPrep,   JSON_UNESCAPED_UNICODE) ?>, backgroundColor: COLORS.prep },
                { label: 'Expédiée',       data: <?= json_encode($dayShip,   JSON_UNESCAPED_UNICODE) ?>, backgroundColor: COLORS.ship },
                { label: 'Livrée',         data: <?= json_encode($dayDone,   JSON_UNESCAPED_UNICODE) ?>, backgroundColor: COLORS.done },
                { label: 'Annulée',        data: <?= json_encode($dayCancel, JSON_UNESCAPED_UNICODE) ?>, backgroundColor: COLORS.cancel }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { stacked: true, grid: { display: false } },
                y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } }
            },
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10 } } }
        }
    });

    // 4. LINE — Évolution des retards
    new Chart(document.getElementById('chartOverdue'), {
        type: 'line',
        data: {
            labels: <?= json_encode($dayLabels, JSON_UNESCAPED_UNICODE) ?>,
            datasets: [{
                label: 'Commandes en retard',
                data: <?= json_encode($dayOverdue, JSON_UNESCAPED_UNICODE) ?>,
                borderColor: COLORS.red,
                backgroundColor: 'rgba(239, 68, 68, 0.15)',
                fill: true,
                tension: 0.35,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#fff',
                pointBorderColor: COLORS.red,
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0 } }
            },
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10 } } }
        }
    });

    // 5. HORIZONTAL BAR — Charge par assigné
    new Chart(document.getElementById('chartAssignees'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($assigneeLabels, JSON_UNESCAPED_UNICODE) ?>,
            datasets: [
                {
                    label: 'Total assignées',
                    data: <?= json_encode($assigneeNb,   JSON_UNESCAPED_UNICODE) ?>,
                    backgroundColor: '#93c5fd',
                    borderRadius: 6,
                    borderSkipped: false
                },
                {
                    label: 'Livrées',
                    data: <?= json_encode($assigneeDone, JSON_UNESCAPED_UNICODE) ?>,
                    backgroundColor: '#4ade80',
                    borderRadius: 6,
                    borderSkipped: false
                }
            ]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { beginAtZero: true, ticks: { precision: 0 } },
                y: { grid: { display: false } }
            },
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10 } } }
        }
    });
});
</script>