<?php
if (!defined('ROUTER_LOADED')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

if (!userCan($user, 'factures.view')) {
    http_response_code(403);
    echo '<p class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</p>';
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
$search      = trim((string) ($_GET['q']          ?? ''));
$fromDate    = (string) ($_GET['du']               ?? '');
$toDate      = (string) ($_GET['au']               ?? '');
$paymentType = (string) ($_GET['payment_status']   ?? '');
$origin      = (string) ($_GET['origin']           ?? '');
$page        = max(1, (int) ($_GET['page'] ?? 1));
$perPage     = 20;

$allowedPaymentStatus = ['Payée', 'Partielle', 'Impayée'];
$allowedOrigins       = ['Vente comptoir', 'Commande', 'Interne'];

if (!in_array($paymentType, $allowedPaymentStatus, true)) $paymentType = '';
if (!in_array($origin, $allowedOrigins, true))            $origin = '';

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
$conditions = ["s.document_type = 'Facture'", "s.status = 'Active'"];
$params     = [];

if ($search !== '') {
    $conditions[] = "(s.invoice_number LIKE ? OR s.client_name LIKE ? OR s.phone LIKE ? OR s.operator LIKE ?)";
    $p = '%' . $search . '%';
    array_push($params, $p, $p, $p, $p);
}
if ($fromDate !== '') { $conditions[] = 's.date >= ?'; $params[] = $fromDate; }
if ($toDate !== '')   { $conditions[] = 's.date <= ?'; $params[] = $toDate; }
if ($origin !== '')   { $conditions[] = 's.origin = ?'; $params[] = $origin; }

// Payment status
if ($paymentType === 'Payée') {
    $conditions[] = 's.debt = 0';
} elseif ($paymentType === 'Impayée') {
    $conditions[] = 's.debt > 0 AND s.netcash = 0';
} elseif ($paymentType === 'Partielle') {
    $conditions[] = 's.debt > 0 AND s.netcash > 0';
}

$where = 'WHERE ' . implode(' AND ', $conditions);

$total = (int) $db->fetchColumn(
    "SELECT COUNT(*) FROM sales s $where",
    $params
);

$totalPages = max(1, (int) ceil($total / $perPage));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * $perPage;

$invoices = $db->fetchAll(
    "SELECT
        s.id, s.invoice_number, s.client_name, s.phone,
        s.date, s.tot_htva, s.tva, s.total_amount, s.netcash, s.debt,
        s.payment_method, s.origin, s.operator, s.agent,
        s.document_type, s.status
     FROM sales s
     $where
     ORDER BY s.date DESC, s.id DESC
     LIMIT $perPage OFFSET $offset",
    $params
);

// Totaux (sur la page filtrée, pas seulement la page courante)
$totals = $db->fetch(
    "SELECT
        COUNT(*) AS nb,
        COALESCE(SUM(s.total_amount), 0) AS total_ttc,
        COALESCE(SUM(s.netcash), 0)      AS total_paid,
        COALESCE(SUM(s.debt), 0)         AS total_due
     FROM sales s $where",
    $params
) ?: ['nb' => 0, 'total_ttc' => 0, 'total_paid' => 0, 'total_due' => 0];

$queryUrl = static function (int $targetPage) use ($search, $fromDate, $toDate, $paymentType, $origin, $role): string {
    return app_route_url('factures/liste', $role) . '?' . http_build_query([
        'q'              => $search,
        'du'             => $fromDate,
        'au'             => $toDate,
        'payment_status' => $paymentType,
        'origin'         => $origin,
        'page'           => $targetPage,
    ]);
};

$paymentBadge = static function (float $total, float $paid, float $debt): array {
    if ($debt <= 0) {
        return ['label' => 'Payée', 'class' => 'bg-green-100 text-green-800'];
    }
    if ($paid > 0) {
        return ['label' => 'Partielle', 'class' => 'bg-amber-100 text-amber-800'];
    }
    return ['label' => 'Impayée', 'class' => 'bg-rose-100 text-rose-800'];
};

$originBadge = static function (string $origin): string {
    return match ($origin) {
        'Commande'       => 'bg-indigo-50 text-indigo-700',
        'Vente comptoir' => 'bg-slate-100 text-slate-700',
        'Interne'        => 'bg-slate-100 text-slate-600',
        default          => 'bg-slate-100 text-slate-700',
    };
};
?>
<section class="space-y-5">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-green-800">Facturation</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Liste des factures</h1>
            <p class="mt-1 text-sm text-slate-600">
                <?= number_format((int) $totals['nb'], 0, ',', ' ') ?> facture(s) —
                Total TTC : <strong><?= number_format((float) $totals['total_ttc'], 0, ',', ' ') ?> FBu</strong>,
                Encaissé : <strong class="text-green-800"><?= number_format((float) $totals['total_paid'], 0, ',', ' ') ?> FBu</strong>,
                Reste dû : <strong class="<?= (float) $totals['total_due'] > 0 ? 'text-rose-700' : '' ?>"><?= number_format((float) $totals['total_due'], 0, ',', ' ') ?> FBu</strong>
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <?php if (userCan($user, 'factures.create')): ?>
                <a href="<?= $escape(app_route_url('factures/creer')) ?>"
                   class="inline-flex items-center justify-center gap-2 rounded-md bg-green-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-800">
                    <i data-feather="file-plus" class="h-4 w-4"></i> Factures à créer
                </a>
            <?php endif; ?>
            <a href="<?= $escape(app_route_url('ventes/nouvelle')) ?>"
               class="inline-flex items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <i data-feather="shopping-cart" class="h-4 w-4"></i> Vente comptoir
            </a>
        </div>
    </header>

    <?php if ($flashMessage): ?>
        <div role="status" class="rounded-md border px-4 py-3 text-sm <?= ($flashMessage['type'] ?? '') === 'success' ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-800' ?>">
            <?= $escape($flashMessage['text'] ?? '') ?>
        </div>
    <?php endif; ?>

    <!-- Filtres -->
    <form method="get" action="<?= $escape(app_url('router.php')) ?>"
          class="grid gap-3 rounded-md border border-slate-200 bg-white p-4
                 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
        <input type="hidden" name="route" value="<?= $escape($role . '/factures/liste') ?>">

        <label class="space-y-1 text-sm font-medium text-slate-700 xl:col-span-2">
            Recherche
            <input name="q" value="<?= $escape($search) ?>"
                   placeholder="Numéro, client, téléphone…"
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
            Statut
            <select name="payment_status" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 font-normal">
                <option value="">Tous</option>
                <?php foreach ($allowedPaymentStatus as $s): ?>
                    <option value="<?= $escape($s) ?>" <?= $paymentType === $s ? 'selected' : '' ?>><?= $escape($s) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="space-y-1 text-sm font-medium text-slate-700">
            Origine
            <select name="origin" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 font-normal">
                <option value="">Toutes</option>
                <?php foreach ($allowedOrigins as $o): ?>
                    <option value="<?= $escape($o) ?>" <?= $origin === $o ? 'selected' : '' ?>><?= $escape($o) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-3 xl:col-span-2">
            <button class="inline-flex items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium hover:bg-slate-50">
                <i data-feather="search" class="h-4 w-4"></i> Filtrer
            </button>
            <?php if ($search !== '' || $fromDate !== '' || $toDate !== '' || $paymentType !== '' || $origin !== ''): ?>
                <a href="<?= $escape(app_route_url('factures/liste')) ?>"
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
                    <th class="px-4 py-3">Facture</th>
                    <th class="px-4 py-3">Client</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Origine</th>
                    <th class="px-4 py-3">Paiement</th>
                    <th class="px-4 py-3 text-right">Total TTC</th>
                    <th class="px-4 py-3 text-right">Encaissé</th>
                    <th class="px-4 py-3 text-right">Reste dû</th>
                    <th class="px-4 py-3">Statut</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (!$invoices): ?>
                    <tr>
                        <td colspan="10" class="px-4 py-12 text-center">
                            <div class="mx-auto max-w-sm">
                                <div class="mb-3 text-4xl">🧾</div>
                                <p class="font-medium text-slate-700">Aucune facture trouvée</p>
                                <p class="mt-1 text-sm text-slate-500">
                                    Modifiez les filtres, ou créez une facture via
                                    <a href="<?= $escape(app_route_url('factures/creer')) ?>" class="font-medium text-green-800 hover:underline">Factures à créer</a>
                                    ou
                                    <a href="<?= $escape(app_route_url('ventes/nouvelle')) ?>" class="font-medium text-green-800 hover:underline">Vente comptoir</a>.
                                </p>
                            </div>
                        </td>
                    </tr>
                <?php else: foreach ($invoices as $invoice):
                    $total = (float) $invoice['total_amount'];
                    $paid  = (float) $invoice['netcash'];
                    $debt  = (float) $invoice['debt'];
                    $badge = $paymentBadge($total, $paid, $debt);
                ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <div class="font-mono text-xs font-semibold text-slate-900"><?= $escape($invoice['invoice_number']) ?></div>
                            <div class="text-[11px] text-slate-500"><?= $escape($invoice['operator']) ?></div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-800"><?= $escape($invoice['client_name']) ?></div>
                            <?php if (!empty($invoice['phone'])): ?>
                                <div class="text-xs text-slate-500"><?= $escape($invoice['phone']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate-600">
                            <?= $escape(date('d/m/Y', strtotime($invoice['date']))) ?>
                        </td>
                        <td class="px-4 py-3">
                            <?php if (!empty($invoice['origin'])): ?>
                                <span class="rounded-full px-2 py-0.5 text-[11px] font-medium <?= $originBadge($invoice['origin']) ?>">
                                    <?= $escape($invoice['origin']) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-slate-400">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            <?= $escape($invoice['payment_method']) ?>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-slate-900">
                            <?= number_format($total, 0, ',', ' ') ?> FBu
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-green-800">
                            <?= number_format($paid, 0, ',', ' ') ?> FBu
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right <?= $debt > 0 ? 'font-semibold text-rose-700' : 'text-slate-500' ?>">
                            <?= number_format($debt, 0, ',', ' ') ?> FBu
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium <?= $badge['class'] ?>">
                                <?= $escape($badge['label']) ?>
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-1">
                                <a href="<?= $escape(app_route_url('ventes/facture')) . '?id=' . (int) $invoice['id'] ?>"
                                   class="inline-flex items-center justify-center rounded border border-slate-300 p-2 text-slate-700 hover:bg-slate-100"
                                   title="Voir la facture">
                                    <i data-feather="eye" class="h-4 w-4"></i>
                                </a>
                                <a href="<?= $escape(app_route_url('ventes/facture')) . '?id=' . (int) $invoice['id'] . '&print=1' ?>"
                                   class="inline-flex items-center justify-center rounded border border-slate-300 p-2 text-slate-700 hover:bg-slate-100"
                                   title="Imprimer la facture"
                                   target="_blank" rel="noopener">
                                    <i data-feather="printer" class="h-4 w-4"></i>
                                </a>
                            </div>
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