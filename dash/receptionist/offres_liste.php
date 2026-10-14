<?php
if (!defined('ROUTER_LOADED')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

if (!userCan($user, 'offres.view')) {
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

// Filtres
$search   = trim((string) ($_GET['q']      ?? ''));
$status   = (string) ($_GET['statut']      ?? '');
$fromDate = (string) ($_GET['du']          ?? '');
$toDate   = (string) ($_GET['au']          ?? '');
$page     = max(1, (int) ($_GET['page'] ?? 1));
$perPage  = 20;

$allowedStatuses = ['Brouillon','Envoyée','Acceptée','Refusée','Expirée'];
if (!in_array($status, $allowedStatuses, true)) $status = '';

$validDate = static function (string $v): bool {
    if ($v === '') return false;
    $d = DateTime::createFromFormat('!Y-m-d', $v);
    return $d !== false && $d->format('Y-m-d') === $v;
};
if (!$validDate($fromDate)) $fromDate = '';
if (!$validDate($toDate))   $toDate   = '';

// Conditions
$conditions = ["document_type = 'Devis'", "status = 'Active'"];
$params     = [];

if ($search !== '') {
    $conditions[] = '(invoice_number LIKE ? OR client_name LIKE ? OR phone LIKE ?)';
    $p = '%' . $search . '%';
    array_push($params, $p, $p, $p);
}
if ($status !== '') {
    $conditions[] = 'offer_status = ?';
    $params[] = $status;
}
if ($fromDate !== '') { $conditions[] = 'date >= ?'; $params[] = $fromDate; }
if ($toDate !== '')   { $conditions[] = 'date <= ?'; $params[] = $toDate; }

$where = 'WHERE ' . implode(' AND ', $conditions);

$total = (int) $db->fetchColumn("SELECT COUNT(*) FROM sales $where", $params);
$totalPages = max(1, (int) ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$offers = $db->fetchAll(
    "SELECT id, invoice_number, client_name, phone, date, valid_until,
            tot_htva, tva, total_amount, offer_status, discount, operator
     FROM sales
     $where
     ORDER BY date DESC, id DESC
     LIMIT $perPage OFFSET $offset",
    $params
);

// Compteurs pour les onglets
$counts = $db->fetch(
    "SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN offer_status = 'Brouillon' THEN 1 ELSE 0 END) AS brouillon,
        SUM(CASE WHEN offer_status = 'Envoyée'   THEN 1 ELSE 0 END) AS envoyee,
        SUM(CASE WHEN offer_status = 'Acceptée'  THEN 1 ELSE 0 END) AS acceptee,
        SUM(CASE WHEN offer_status = 'Refusée'   THEN 1 ELSE 0 END) AS refusee,
        SUM(CASE WHEN offer_status = 'Expirée'   THEN 1 ELSE 0 END) AS expiree
     FROM sales WHERE document_type = 'Devis' AND status = 'Active'"
) ?: ['total'=>0,'brouillon'=>0,'envoyee'=>0,'acceptee'=>0,'refusee'=>0,'expiree'=>0];

$queryUrl = static function (int $targetPage) use ($search, $status, $fromDate, $toDate, $role): string {
    return app_route_url('offres/liste', $role) . '?' . http_build_query([
        'q' => $search, 'statut' => $status, 'du' => $fromDate, 'au' => $toDate, 'page' => $targetPage,
    ]);
};

$tabUrl = static function (string $newStatus) use ($search, $fromDate, $toDate, $role): string {
    return app_route_url('offres/liste', $role) . '?' . http_build_query([
        'q' => $search, 'statut' => $newStatus, 'du' => $fromDate, 'au' => $toDate, 'page' => 1,
    ]);
};

$statusColor = static function (string $s): string {
    return match ($s) {
        'Brouillon' => 'bg-slate-100 text-slate-700',
        'Envoyée'   => 'bg-blue-100 text-blue-800',
        'Acceptée'  => 'bg-green-100 text-green-800',
        'Refusée'   => 'bg-rose-100 text-rose-800',
        'Expirée'   => 'bg-amber-100 text-amber-800',
        default     => 'bg-slate-100 text-slate-600',
    };
};
?>
<section class="space-y-5">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-green-800">Commercial</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Offres commerciales</h1>
            <p class="mt-1 text-sm text-slate-600">
                <?= number_format((int) $counts['total'], 0, ',', ' ') ?> offre(s) au total —
                Acceptées : <strong class="text-green-700"><?= (int) $counts['acceptee'] ?></strong>,
                Envoyées : <strong class="text-blue-700"><?= (int) $counts['envoyee'] ?></strong>,
                Brouillons : <strong><?= (int) $counts['brouillon'] ?></strong>
            </p>
        </div>
        <?php if (userCan($user, 'offres.create')): ?>
            <a href="<?= $escape(app_route_url('offres/creer')) ?>"
               class="inline-flex items-center gap-2 rounded-md bg-green-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-800">
                <i data-feather="plus" class="h-4 w-4"></i> Nouvelle offre
            </a>
        <?php endif; ?>
    </header>

    <?php if ($flashMessage): ?>
        <div role="status" class="rounded-md border px-4 py-3 text-sm <?= ($flashMessage['type'] ?? '') === 'success' ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-800' ?>">
            <?= $escape($flashMessage['text'] ?? '') ?>
        </div>
    <?php endif; ?>

    <!-- Onglets statut -->
    <nav class="flex flex-wrap gap-2 rounded-md border border-slate-200 bg-white p-2">
        <?php
        $tabs = [
            ''          => ['Toutes',    (int) $counts['total'],     'bg-slate-100 text-slate-700'],
            'Brouillon' => ['Brouillons',(int) $counts['brouillon'], 'bg-slate-100 text-slate-700'],
            'Envoyée'   => ['Envoyées',  (int) $counts['envoyee'],   'bg-blue-100 text-blue-800'],
            'Acceptée'  => ['Acceptées', (int) $counts['acceptee'],  'bg-green-100 text-green-800'],
            'Refusée'   => ['Refusées',  (int) $counts['refusee'],   'bg-rose-100 text-rose-800'],
            'Expirée'   => ['Expirées',  (int) $counts['expiree'],   'bg-amber-100 text-amber-800'],
        ];
        foreach ($tabs as $key => [$label, $count, $activeClass]):
            $isActive = $status === $key;
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
        <input type="hidden" name="route" value="<?= $escape($role . '/offres/liste') ?>">
        <input type="hidden" name="statut" value="<?= $escape($status) ?>">

        <label class="space-y-1 text-sm font-medium text-slate-700 lg:col-span-2">
            Recherche
            <input name="q" value="<?= $escape($search) ?>" placeholder="N° offre, client, téléphone…"
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
                <a href="<?= $escape(app_route_url('offres/liste')) ?>"
                   class="inline-flex items-center justify-center rounded-md px-3 py-2 text-sm text-slate-600 hover:bg-slate-100">Effacer</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Table -->
    <div class="overflow-x-auto rounded-md border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">N° Offre</th>
                    <th class="px-4 py-3">Client</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Valable jusqu'au</th>
                    <th class="px-4 py-3">Statut</th>
                    <th class="px-4 py-3 text-right">Total TTC</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (!$offers): ?>
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <p class="font-medium text-slate-700">Aucune offre trouvée</p>
                            <p class="mt-1 text-sm text-slate-500">Modifiez les filtres ou créez une nouvelle offre.</p>
                        </td>
                    </tr>
                <?php else: foreach ($offers as $o):
                    $st = $o['offer_status'] ?: 'Brouillon';
                    $expired = !empty($o['valid_until']) && $o['valid_until'] < date('Y-m-d') && $st === 'Envoyée';
                ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <a href="<?= $escape(app_route_url('offres/apercu')) . '?id=' . (int) $o['id'] ?>"
                               class="font-mono text-xs font-semibold text-green-800 hover:underline">
                                <?= $escape($o['invoice_number']) ?>
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-800"><?= $escape($o['client_name']) ?></div>
                            <?php if (!empty($o['phone'])): ?>
                                <div class="text-xs text-slate-500"><?= $escape($o['phone']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate-600">
                            <?= $escape(date('d/m/Y', strtotime($o['date']))) ?>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate-600">
                            <?php if (!empty($o['valid_until'])): ?>
                                <?= $escape(date('d/m/Y', strtotime($o['valid_until']))) ?>
                                <?php if ($expired): ?>
                                    <span class="ml-1 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-medium text-amber-800">Expirée</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-slate-400">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium <?= $statusColor($st) ?>">
                                <?= $escape($st) ?>
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-slate-900">
                            <?= number_format((float) $o['total_amount'], 0, ',', ' ') ?> FBu
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-1">
                                <a href="<?= $escape(app_route_url('offres/apercu')) . '?id=' . (int) $o['id'] ?>"
                                   class="inline-flex items-center justify-center rounded border border-slate-300 p-2 text-slate-700 hover:bg-slate-100"
                                   title="Voir l'offre">
                                    <i data-feather="eye" class="h-4 w-4"></i>
                                </a>
                                <?php if (userCan($user, 'offres.edit')): ?>
                                    <a href="<?= $escape(app_route_url('offres/modifier')) . '?id=' . (int) $o['id'] ?>"
                                       class="inline-flex items-center justify-center rounded border border-slate-300 p-2 text-slate-700 hover:bg-slate-100"
                                       title="Modifier">
                                        <i data-feather="edit-2" class="h-4 w-4"></i>
                                    </a>
                                    <form method="post" action="<?= $escape(app_route_url('offres/supprimer')) ?>"
                                          class="inline-block"
                                          onsubmit="return confirm('Supprimer définitivement l\'offre <?= $escape($o['invoice_number']) ?> ?');">
                                        <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="sale_id" value="<?= (int) $o['id'] ?>">
                                        <button type="submit"
                                                class="inline-flex items-center justify-center rounded border border-rose-200 p-2 text-rose-700 hover:bg-rose-50"
                                                title="Supprimer">
                                            <i data-feather="trash-2" class="h-4 w-4"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
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