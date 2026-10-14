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

// -----------------------------------------------------------------
// Filtres
// -----------------------------------------------------------------
$search    = trim((string) ($_GET['q']       ?? ''));
$delivery  = (string) ($_GET['delivery']     ?? '');
$fromDate  = (string) ($_GET['du']           ?? '');
$toDate    = (string) ($_GET['au']           ?? '');
$page      = max(1, (int) ($_GET['page'] ?? 1));
$perPage   = 20;

$allowedDeliveries = ['En préparation','Expédiée','Livrée','Annulée'];
if (!in_array($delivery, $allowedDeliveries, true)) $delivery = '';

$validDate = static function (string $v): bool {
    if ($v === '') return false;
    $d = DateTime::createFromFormat('!Y-m-d', $v);
    return $d !== false && $d->format('Y-m-d') === $v;
};
if (!$validDate($fromDate)) $fromDate = '';
if (!$validDate($toDate))   $toDate   = '';

// -----------------------------------------------------------------
// Requête
// -----------------------------------------------------------------
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

$total = (int) $db->fetchColumn("SELECT COUNT(*) FROM tasks t $where", $params);
$totalPages = max(1, (int) ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

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
     ORDER BY
        CASE t.delivery_status
            WHEN 'En préparation' THEN 1
            WHEN 'Expédiée'       THEN 2
            WHEN 'Livrée'         THEN 3
            WHEN 'Annulée'        THEN 4
            ELSE 5
        END,
        t.due_date ASC, t.id DESC
     LIMIT $perPage OFFSET $offset",
    $params
);

// Compteurs
$counts = $db->fetch(
    "SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN delivery_status = 'En préparation' THEN 1 ELSE 0 END) AS prep,
        SUM(CASE WHEN delivery_status = 'Expédiée'       THEN 1 ELSE 0 END) AS ship,
        SUM(CASE WHEN delivery_status = 'Livrée'         THEN 1 ELSE 0 END) AS done,
        SUM(CASE WHEN delivery_status = 'Annulée'        THEN 1 ELSE 0 END) AS cancel
     FROM tasks WHERE category = 'Commande'"
) ?: ['total' => 0, 'prep' => 0, 'ship' => 0, 'done' => 0, 'cancel' => 0];

$queryUrl = static function (int $targetPage) use ($search, $delivery, $fromDate, $toDate, $role): string {
    return app_route_url('livraisons/liste', $role) . '?' . http_build_query([
        'q' => $search, 'delivery' => $delivery, 'du' => $fromDate, 'au' => $toDate, 'page' => $targetPage,
    ]);
};

$tabUrl = static function (string $newDelivery) use ($search, $fromDate, $toDate, $role): string {
    return app_route_url('livraisons/liste', $role) . '?' . http_build_query([
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
?>
<section class="space-y-5">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-green-800">Logistique</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Liste des bordereaux</h1>
            <p class="mt-1 text-sm text-slate-600">
                <?= number_format((int) $counts['total'], 0, ',', ' ') ?> livraison(s) —
                En préparation : <strong class="text-orange-700"><?= (int) $counts['prep'] ?></strong>,
                Expédiées : <strong class="text-blue-700"><?= (int) $counts['ship'] ?></strong>,
                Livrées : <strong class="text-green-700"><?= (int) $counts['done'] ?></strong>
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="<?= $escape(app_route_url('livraisons/suivi')) ?>"
               class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                <i data-feather="activity" class="h-4 w-4"></i> Suivi livraisons
            </a>
            <?php if (userCan($user, 'livraisons.create')): ?>
                <a href="<?= $escape(app_route_url('livraisons/creer')) ?>"
                   class="inline-flex items-center gap-2 rounded-md bg-green-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-800">
                    <i data-feather="plus" class="h-4 w-4"></i> Créer un bordereau
                </a>
            <?php endif; ?>
        </div>
    </header>

    <?php if ($flashMessage): ?>
        <div role="status" class="rounded-md border px-4 py-3 text-sm <?= ($flashMessage['type'] ?? '') === 'success' ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-800' ?>">
            <?= $escape($flashMessage['text'] ?? '') ?>
        </div>
    <?php endif; ?>

    <!-- Onglets -->
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

    <!-- Filtres -->
    <form method="get" action="<?= $escape(app_url('router.php')) ?>"
          class="grid gap-3 rounded-md border border-slate-200 bg-white p-4 sm:grid-cols-2 lg:grid-cols-4">
        <input type="hidden" name="route" value="<?= $escape($role . '/livraisons/liste') ?>">
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
                <a href="<?= $escape(app_route_url('livraisons/liste')) ?>"
                   class="inline-flex items-center justify-center rounded-md px-3 py-2 text-sm text-slate-600 hover:bg-slate-100">Effacer</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Table -->
    <div class="overflow-x-auto rounded-md border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">Bordereau</th>
                    <th class="px-4 py-3">Client</th>
                    <th class="px-4 py-3">Adresse</th>
                    <th class="px-4 py-3">Priorité</th>
                    <th class="px-4 py-3">Livreur</th>
                    <th class="px-4 py-3">Prévue le</th>
                    <th class="px-4 py-3">Statut</th>
                    <th class="px-4 py-3 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (!$deliveries): ?>
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center">
                            <p class="font-medium text-slate-700">Aucun bordereau trouvé</p>
                            <p class="mt-1 text-sm text-slate-500">Modifiez les filtres ou créez une nouvelle livraison.</p>
                        </td>
                    </tr>
                <?php else: foreach ($deliveries as $d):
                    $assigneeName = trim(($d['f_name'] ?? '') . ' ' . ($d['l_name'] ?? '')) ?: ($d['assigned_to'] ?? '—');
                ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <a href="<?= $escape(app_route_url('commandes/clients')) . '?q=' . urlencode($d['ticket_no']) ?>"
                               class="font-mono text-xs font-semibold text-green-800 hover:underline">
                                <?= $escape($d['ticket_no']) ?>
                            </a>
                            <?php if (!empty($d['invoice_number'])): ?>
                                <div class="mt-1 inline-flex items-center gap-1 text-[11px] font-medium text-green-800">
                                    <i data-feather="file-text" class="h-3 w-3"></i>
                                    <?= $escape($d['invoice_number']) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-800">
                                <?= $escape($d['client']) ?>
                                <?php if ((int) $d['walk_in'] === 1): ?>
                                    <span class="ml-1 rounded bg-slate-200 px-1.5 py-0.5 text-[10px] font-medium uppercase text-slate-700">Passage</span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($d['client_reference'])): ?>
                                <div class="text-xs text-slate-500">Réf : <?= $escape($d['client_reference']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            <?= $escape($d['delivery_address'] ?: '—') ?>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium <?= $priorityColor($d['priority']) ?>">
                                <?= $escape($d['priority']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-700"><?= $escape($assigneeName) ?></td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate-600">
                            <?= !empty($d['due_date']) ? $escape(date('d/m/Y', strtotime($d['due_date']))) : '—' ?>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium <?= $deliveryColor($d['delivery_status']) ?>">
                                <?= $escape($d['delivery_status']) ?>
                            </span>
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