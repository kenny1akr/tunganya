<?php
if (!defined('ROUTER_LOADED')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

if (!userCan($user, 'caisse.view')) {
    http_response_code(403);
    echo '<div class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</div>';
    return;
}

require_once __DIR__ . '/../../api/database.php';

$db     = Database::getInstance();
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$role   = $user['role_system'];

$flashMessage = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);

// -----------------------------------------------------------------
// Filtres
// -----------------------------------------------------------------
$search   = trim((string) ($_GET['q']    ?? ''));
$fromDate = (string) ($_GET['du']        ?? '');
$toDate   = (string) ($_GET['au']        ?? '');
$type     = (string) ($_GET['type']      ?? '');
$page     = max(1, (int) ($_GET['page'] ?? 1));
$perPage  = 20;

$validDate = static function (string $v): bool {
    if ($v === '') return false;
    $d = DateTime::createFromFormat('!Y-m-d', $v);
    return $d !== false && $d->format('Y-m-d') === $v;
};
if (!$validDate($fromDate)) $fromDate = '';
if (!$validDate($toDate))   $toDate   = '';
if (!in_array($type, ['entree', 'sortie'], true)) $type = '';

// -----------------------------------------------------------------
// WHERE dynamique
// -----------------------------------------------------------------
$cashInWhere  = ['1 = 1'];
$cashOutWhere = ['1 = 1'];
$params       = [];

if ($fromDate !== '') {
    $cashInWhere[]  = 'date_created >= ?';
    $cashOutWhere[] = 'created_at >= ?';
    $params[] = $fromDate;
}
if ($toDate !== '') {
    $cashInWhere[]  = 'date_created <= ?';
    $cashOutWhere[] = 'created_at <= ?';
    $params[] = $toDate;
}
if ($search !== '') {
    $cashInWhere[]  = '(reference LIKE ? OR trans_motive LIKE ? OR operator LIKE ?)';
    $cashOutWhere[] = '(CAST(id AS CHAR) LIKE ? OR reason LIKE ? OR cashout_type LIKE ? OR user_id LIKE ?)';
    $p = '%' . $search . '%';
    array_push($params, $p, $p, $p);
    array_push($params, $p, $p, $p, $p);
}

$cashInWhereStr  = implode(' AND ', $cashInWhere);
$cashOutWhereStr = implode(' AND ', $cashOutWhere);

// -----------------------------------------------------------------
// Collation-safe UNION branches
// -----------------------------------------------------------------
$cashInSelect = "
    SELECT
        id,
        date_created COLLATE utf8mb4_general_ci AS op_date,
        'entree' COLLATE utf8mb4_general_ci     AS op_type,
        reference COLLATE utf8mb4_general_ci    AS reference,
        trans_motive COLLATE utf8mb4_general_ci AS description,
        operator COLLATE utf8mb4_general_ci     AS who,
        amount
    FROM cash_in
    WHERE $cashInWhereStr
";

$cashOutSelect = "
    SELECT
        id,
        created_at COLLATE utf8mb4_general_ci                     AS op_date,
        'sortie' COLLATE utf8mb4_general_ci                       AS op_type,
        CAST(id AS CHAR) COLLATE utf8mb4_general_ci               AS reference,
        COALESCE(reason, cashout_type) COLLATE utf8mb4_general_ci AS description,
        user_id COLLATE utf8mb4_general_ci                        AS who,
        amount
    FROM cash_out
    WHERE $cashOutWhereStr
";

// -----------------------------------------------------------------
// Sélection selon le filtre type
// -----------------------------------------------------------------
if ($type === 'entree') {
    $countSql = "SELECT COUNT(*) FROM ($cashInSelect) AS t";
    $listSql  = "$cashInSelect ORDER BY op_date DESC, id DESC LIMIT $perPage OFFSET ";
} elseif ($type === 'sortie') {
    $countSql = "SELECT COUNT(*) FROM ($cashOutSelect) AS t";
    $listSql  = "$cashOutSelect ORDER BY op_date DESC, id DESC LIMIT $perPage OFFSET ";
} else {
    $unionSql = "$cashInSelect UNION ALL $cashOutSelect";
    $countSql = "SELECT COUNT(*) FROM ($unionSql) AS t";
    $listSql  = "$unionSql ORDER BY op_date DESC, id DESC LIMIT $perPage OFFSET ";
}

// -----------------------------------------------------------------
// Pagination
// -----------------------------------------------------------------
$total = (int) $db->fetchColumn($countSql, $params);

$totalPages = max(1, (int) ceil($total / $perPage));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * $perPage;

$transactions = $db->fetchAll($listSql . $offset, $params);

// -----------------------------------------------------------------
// Totaux (montants)
// -----------------------------------------------------------------
$paramsIn  = $params;
$paramsOut = $params;

// Quand il y a recherche, les params entrée/sortie sont dans le même tableau.
// On les sépare proprement ici.
if ($search !== '') {
    $searchParams = array_slice($params, -7);   // 3 pour IN, 4 pour OUT
    $dateParams   = array_slice($params, 0, count($params) - 7);

    $paramsIn  = array_merge($dateParams, array_slice($searchParams, 0, 3));
    $paramsOut = array_merge($dateParams, array_slice($searchParams, 3, 4));
}

$sumIn  = (float) ($db->fetch(
    "SELECT COALESCE(SUM(amount), 0) AS total FROM cash_in WHERE $cashInWhereStr",
    $paramsIn
)['total'] ?? 0);

$sumOut = (float) ($db->fetch(
    "SELECT COALESCE(SUM(amount), 0) AS total FROM cash_out WHERE $cashOutWhereStr",
    $paramsOut
)['total'] ?? 0);

$balance = $sumIn - $sumOut;

$queryUrl = static function (int $targetPage) use ($search, $fromDate, $toDate, $type, $role): string {
    return app_route_url('caisse/transactions', $role) . '?' . http_build_query([
        'q'    => $search,
        'du'   => $fromDate,
        'au'   => $toDate,
        'type' => $type,
        'page' => $targetPage,
    ]);
};
?>
<section class="space-y-5">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-green-800">Caisse</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Transactions de caisse</h1>
            <p class="mt-1 text-sm text-slate-600">
                <?= number_format($total, 0, ',', ' ') ?> mouvement(s) —
                Entrées : <strong class="text-green-800">+<?= number_format($sumIn, 0, ',', ' ') ?> FBu</strong>,
                Sorties : <strong class="text-rose-700">−<?= number_format($sumOut, 0, ',', ' ') ?> FBu</strong>,
                Solde : <strong class="<?= $balance < 0 ? 'text-rose-700' : 'text-slate-900' ?>"><?= number_format($balance, 0, ',', ' ') ?> FBu</strong>
            </p>
        </div>
        <?php if (userCan($user, 'caisse.operations')): ?>
            <a href="<?= $escape(app_route_url('caisse/operations')) ?>"
               class="inline-flex items-center gap-2 rounded-md bg-green-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-800">
                <i data-feather="plus" class="h-4 w-4"></i> Nouveau mouvement
            </a>
        <?php endif; ?>
    </header>

    <?php if ($flashMessage): ?>
        <div role="status" class="rounded-md border px-4 py-3 text-sm <?= ($flashMessage['type'] ?? '') === 'success' ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-800' ?>">
            <?= $escape($flashMessage['text'] ?? '') ?>
        </div>
    <?php endif; ?>

    <!-- Filtres -->
    <form method="get" action="<?= $escape(app_url('router.php')) ?>"
          class="grid gap-3 rounded-md border border-slate-200 bg-white p-4
                 sm:grid-cols-2 lg:grid-cols-4">
        <input type="hidden" name="route" value="<?= $escape($role . '/caisse/transactions') ?>">

        <label class="space-y-1 text-sm font-medium text-slate-700 lg:col-span-2">
            Recherche
            <input name="q" value="<?= $escape($search) ?>"
                   placeholder="Référence, motif, opérateur…"
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

        <label class="space-y-1 text-sm font-medium text-slate-700">
            Type
            <select name="type" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 font-normal">
                <option value="">Tous</option>
                <option value="entree" <?= $type === 'entree' ? 'selected' : '' ?>>Entrées uniquement</option>
                <option value="sortie" <?= $type === 'sortie' ? 'selected' : '' ?>>Sorties uniquement</option>
            </select>
        </label>

        <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-3">
            <button class="inline-flex items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium hover:bg-slate-50">
                <i data-feather="search" class="h-4 w-4"></i> Filtrer
            </button>
            <?php if ($search !== '' || $fromDate !== '' || $toDate !== '' || $type !== ''): ?>
                <a href="<?= $escape(app_route_url('caisse/transactions')) ?>"
                   class="inline-flex items-center justify-center rounded-md px-3 py-2 text-sm text-slate-600 hover:bg-slate-100">
                    Effacer
                </a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Table -->
    <div class="overflow-x-auto rounded-md border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Référence</th>
                    <th class="px-4 py-3">Motif</th>
                    <th class="px-4 py-3">Opérateur</th>
                    <th class="px-4 py-3 text-right">Montant</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (!$transactions): ?>
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <p class="font-medium text-slate-700">Aucune transaction trouvée</p>
                            <p class="mt-1 text-sm text-slate-500">Modifiez les filtres ou créez un mouvement de caisse.</p>
                        </td>
                    </tr>
                <?php else: foreach ($transactions as $tx):
                    $isIn = $tx['op_type'] === 'entree';
                ?>
                    <tr class="hover:bg-slate-50">
                        <td class="whitespace-nowrap px-4 py-3 text-slate-600">
                            <?= $escape(date('d/m/Y', strtotime($tx['op_date']))) ?>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium <?= $isIn ? 'bg-green-100 text-green-800' : 'bg-rose-100 text-rose-800' ?>">
                                <?= $isIn ? 'Entrée' : 'Sortie' ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-slate-700">
                            <?= $escape($tx['reference'] ?: '—') ?>
                        </td>
                        <td class="px-4 py-3 text-slate-800">
                            <?= $escape($tx['description'] ?: '—') ?>
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            <?= $escape($tx['who'] ?: '—') ?>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right font-semibold <?= $isIn ? 'text-green-800' : 'text-rose-700' ?>">
                            <?= $isIn ? '+' : '−' ?><?= number_format((float) $tx['amount'], 0, ',', ' ') ?> FBu
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav class="flex items-center justify-between gap-3" aria-label="Pagination">
            <span class="text-sm text-slate-600">Page <?= $page ?> sur <?= $totalPages ?></span>
            <div class="flex gap-2">
                <?php if ($page > 1): ?>
                    <a href="<?= $escape($queryUrl($page - 1)) ?>"
                       class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-50">Précédent</a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="<?= $escape($queryUrl($page + 1)) ?>"
                       class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-50">Suivant</a>
                <?php endif; ?>
            </div>
        </nav>
    <?php endif; ?>
</section>