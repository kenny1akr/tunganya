<?php
if (!defined('ROUTER_LOADED')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

if (!userCan($user, 'caisse.view')) {
    http_response_code(403);
    echo '<p class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</p>';
    return;
}

require_once __DIR__ . '/../../api/database.php';
require_once __DIR__ . '/_sales.php';

$db     = Database::getInstance();
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$role   = $user['role_system'];

receptionEnsureCsrf();

// =========================================================================
// POST — marquer payée / impayée / paiement partiel
// =========================================================================
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $csrf   = (string) ($_POST['csrf_token'] ?? '');
    $action = (string) ($_POST['action']     ?? '');
    $saleId = (int)    ($_POST['sale_id']    ?? 0);

    if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
        $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'La demande a expiré. Rechargez la page.'];
        header('Location: ' . app_route_url('caisse/transactions'));
        exit;
    }

    $invoice = $db->fetch(
        "SELECT id, invoice_number, client_name, client_id, total_amount, netcash, debt, payment_method
         FROM sales
         WHERE id = ? AND document_type = 'Facture' AND status = 'Active'
         LIMIT 1",
        [$saleId]
    );

    if (!$invoice) {
        $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Facture introuvable.'];
        header('Location: ' . app_route_url('caisse/transactions'));
        exit;
    }

    $total = (float) $invoice['total_amount'];
    $paid  = (float) $invoice['netcash'];
    $debt  = (float) $invoice['debt'];

    try {
        // -----------------------------------------------------------------
        // Marquer entièrement payée
        // -----------------------------------------------------------------
        if ($action === 'mark_paid') {
            $remaining = max(0, $total - $paid);

            $db->execute(
                "UPDATE sales SET netcash = total_amount, debt = 0 WHERE id = ?",
                [$saleId]
            );

            $db->execute(
                "UPDATE debts SET paid_amount = amount, status = 1 WHERE invoice_number = ?",
                [$invoice['invoice_number']]
            );

            if ($remaining > 0) {
                if ($invoice['payment_method'] === 'Virement bancaire') {
                    receptionRecordFinancialTransaction(
                        $db, $user['nom_utilisateur'],
                        'Règlement facture (virement)',
                        $invoice['invoice_number'], (int) $remaining, 'entrée'
                    );
                } else {
                    receptionEnsureOpenCashRegister($db, $user['nom_complet']);
                    $db->execute(
                        'INSERT INTO cash_in (trans_motive, amount, operator, date_created, reference)
                         VALUES (?, ?, ?, CURDATE(), ?)',
                        ['Règlement facture', (int) $remaining, $user['nom_complet'], $invoice['invoice_number']]
                    );
                    $db->execute(
                        'UPDATE caisse SET montant_in = montant_in + ? WHERE date = CURDATE()',
                        [(int) $remaining]
                    );
                    receptionRecordFinancialTransaction(
                        $db, $user['nom_utilisateur'],
                        'Règlement facture',
                        $invoice['invoice_number'], (int) $remaining, 'entrée'
                    );
                }
            }

            $_SESSION['flash_message'] = [
                'type' => 'success',
                'text' => 'Facture ' . $invoice['invoice_number'] . ' marquée comme PAYÉE.',
            ];
        }

        // -----------------------------------------------------------------
        // Marquer impayée
        // -----------------------------------------------------------------
        elseif ($action === 'mark_unpaid') {
            $db->execute(
                "UPDATE sales SET netcash = 0, debt = total_amount WHERE id = ?",
                [$saleId]
            );

            $db->execute(
                "UPDATE debts SET paid_amount = 0, status = 0 WHERE invoice_number = ?",
                [$invoice['invoice_number']]
            );

            $_SESSION['flash_message'] = [
                'type' => 'success',
                'text' => 'Facture ' . $invoice['invoice_number'] . ' marquée comme IMPAYÉE.',
            ];
        }

        // -----------------------------------------------------------------
        // Paiement partiel
        // -----------------------------------------------------------------
        elseif ($action === 'partial_payment') {
            $amount = (int) round((float) ($_POST['amount'] ?? 0));

            if ($amount <= 0) {
                $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Le montant doit être supérieur à 0.'];
            } elseif ($amount > $debt) {
                $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Le montant dépasse le reste à payer (' . number_format($debt, 0, ',', ' ') . ' FBu).'];
            } else {
                $newPaid = $paid + $amount;
                $newDebt = $total - $newPaid;

                $db->execute(
                    "UPDATE sales SET netcash = ?, debt = ? WHERE id = ?",
                    [(int) $newPaid, (int) $newDebt, $saleId]
                );

                $db->execute(
                    "UPDATE debts SET paid_amount = paid_amount + ?, status = ? WHERE invoice_number = ?",
                    [$amount, $newDebt <= 0 ? 1 : 2, $invoice['invoice_number']]
                );

                $debtRow = $db->fetch(
                    "SELECT id FROM debts WHERE invoice_number = ? LIMIT 1",
                    [$invoice['invoice_number']]
                );
                if ($debtRow) {
                    $db->execute(
                        "INSERT INTO debts_paid (debt_id, amount, client_name, operator, payment_method, invoice_number, date)
                         VALUES (?, ?, ?, ?, ?, ?, CURDATE())",
                        [
                            (int) $debtRow['id'],
                            $amount,
                            mb_substr($invoice['client_name'], 0, 255),
                            $user['nom_complet'],
                            $invoice['payment_method'],
                            $invoice['invoice_number'],
                        ]
                    );
                }

                if ($invoice['payment_method'] === 'Virement bancaire') {
                    receptionRecordFinancialTransaction(
                        $db, $user['nom_utilisateur'],
                        'Paiement partiel facture (virement)',
                        $invoice['invoice_number'], $amount, 'entrée'
                    );
                } else {
                    receptionEnsureOpenCashRegister($db, $user['nom_complet']);
                    $db->execute(
                        'INSERT INTO cash_in (trans_motive, amount, operator, date_created, reference)
                         VALUES (?, ?, ?, CURDATE(), ?)',
                        ['Paiement partiel facture', $amount, $user['nom_complet'], $invoice['invoice_number']]
                    );
                    $db->execute(
                        'UPDATE caisse SET montant_in = montant_in + ? WHERE date = CURDATE()',
                        [$amount]
                    );
                    receptionRecordFinancialTransaction(
                        $db, $user['nom_utilisateur'],
                        'Paiement partiel facture',
                        $invoice['invoice_number'], $amount, 'entrée'
                    );
                }

                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'text' => 'Paiement de ' . number_format($amount, 0, ',', ' ') . ' FBu enregistré sur ' . $invoice['invoice_number'] . '.',
                ];
            }
        }
    } catch (Throwable $e) {
        error_log('Payment update failed: ' . $e->getMessage());
        $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Impossible de mettre à jour le paiement.'];
    }

    header('Location: ' . app_route_url('caisse/transactions'));
    exit;
}

// =========================================================================
// Filtres
// =========================================================================
$search   = trim((string) ($_GET['q']    ?? ''));
$status   = (string) ($_GET['statut']    ?? 'all');   // all | unpaid | partial | paid
$fromDate = (string) ($_GET['du']        ?? '');
$toDate   = (string) ($_GET['au']        ?? '');
$page     = max(1, (int) ($_GET['page'] ?? 1));
$perPage  = 20;

$validDate = static function (string $v): bool {
    if ($v === '') return false;
    $d = DateTime::createFromFormat('!Y-m-d', $v);
    return $d !== false && $d->format('Y-m-d') === $v;
};
if (!$validDate($fromDate)) $fromDate = '';
if (!$validDate($toDate))   $toDate   = '';
if (!in_array($status, ['all', 'unpaid', 'partial', 'paid'], true)) $status = 'all';

// =========================================================================
// Compteurs pour les onglets (toujours calculés, sans filtre d'état)
// =========================================================================
$baseConditions = ["document_type = 'Facture'", "status = 'Active'"];
$baseParams     = [];

if ($search !== '') {
    $baseConditions[] = '(invoice_number LIKE ? OR client_name LIKE ? OR phone LIKE ?)';
    $p = '%' . $search . '%';
    array_push($baseParams, $p, $p, $p);
}
if ($fromDate !== '') { $baseConditions[] = 'date >= ?'; $baseParams[] = $fromDate; }
if ($toDate !== '')   { $baseConditions[] = 'date <= ?'; $baseParams[] = $toDate; }

$baseWhere = 'WHERE ' . implode(' AND ', $baseConditions);

$counts = $db->fetch(
    "SELECT
        COUNT(*) AS total_count,
        SUM(CASE WHEN debt <= 0 THEN 1 ELSE 0 END) AS paid_count,
        SUM(CASE WHEN debt > 0 AND netcash > 0 THEN 1 ELSE 0 END) AS partial_count,
        SUM(CASE WHEN debt > 0 AND netcash = 0 THEN 1 ELSE 0 END) AS unpaid_count,
        COALESCE(SUM(total_amount), 0) AS total_ttc,
        COALESCE(SUM(netcash), 0) AS total_paid,
        COALESCE(SUM(debt), 0) AS total_due
     FROM sales $baseWhere",
    $baseParams
) ?: [
    'total_count' => 0, 'paid_count' => 0, 'partial_count' => 0, 'unpaid_count' => 0,
    'total_ttc' => 0, 'total_paid' => 0, 'total_due' => 0,
];

// =========================================================================
// Requête principale (filtrée par état)
// =========================================================================
$conditions = $baseConditions;
$params     = $baseParams;

if ($status === 'unpaid') {
    $conditions[] = 'debt > 0 AND netcash = 0';
} elseif ($status === 'partial') {
    $conditions[] = 'debt > 0 AND netcash > 0';
} elseif ($status === 'paid') {
    $conditions[] = 'debt = 0';
}

$where = 'WHERE ' . implode(' AND ', $conditions);

$total = (int) $db->fetchColumn("SELECT COUNT(*) FROM sales $where", $params);

$totalPages = max(1, (int) ceil($total / $perPage));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * $perPage;

$invoices = $db->fetchAll(
    "SELECT id, invoice_number, client_name, phone, date,
            total_amount, netcash, debt, payment_method, origin
     FROM sales
     $where
     ORDER BY
        CASE
            WHEN debt > 0 AND netcash = 0 THEN 1   -- impayées en haut
            WHEN debt > 0 AND netcash > 0 THEN 2   -- partielles ensuite
            ELSE 3                                  -- payées en bas
        END,
        date DESC, id DESC
     LIMIT $perPage OFFSET $offset",
    $params
);

$flashMessage = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);

$tabUrl = static function (string $newStatus) use ($search, $fromDate, $toDate, $role): string {
    return app_route_url('caisse/transactions', $role) . '?' . http_build_query([
        'q' => $search, 'statut' => $newStatus, 'du' => $fromDate, 'au' => $toDate, 'page' => 1,
    ]);
};

$queryUrl = static function (int $targetPage) use ($search, $status, $fromDate, $toDate, $role): string {
    return app_route_url('caisse/transactions', $role) . '?' . http_build_query([
        'q' => $search, 'statut' => $status, 'du' => $fromDate, 'au' => $toDate, 'page' => $targetPage,
    ]);
};
?>
<section class="space-y-5">
    <header>
        <p class="text-sm font-semibold text-green-800">Caisse</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-900">État des paiements</h1>
        <p class="mt-1 text-sm text-slate-600">
            Suivez chaque facture : <span class="font-medium text-rose-700">Impayée</span>,
            <span class="font-medium text-amber-700">Partielle</span> ou
            <span class="font-medium text-green-700">Payée</span>.
        </p>
    </header>

    <?php if ($flashMessage): ?>
        <div role="status" class="rounded-md border px-4 py-3 text-sm <?= ($flashMessage['type'] ?? '') === 'success' ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-800' ?>">
            <?= $escape($flashMessage['text'] ?? '') ?>
        </div>
    <?php endif; ?>

    <!-- Résumé 3 états -->
    <div class="grid gap-3 sm:grid-cols-3">
        <article class="rounded-md border border-rose-200 bg-rose-50 p-4">
            <p class="text-xs font-semibold uppercase text-rose-700">Impayées</p>
            <p class="mt-1 text-2xl font-bold text-rose-800"><?= number_format((int) $counts['unpaid_count'], 0, ',', ' ') ?></p>
            <p class="mt-1 text-xs text-rose-700">Clients n'ayant rien payé</p>
        </article>
        <article class="rounded-md border border-amber-200 bg-amber-50 p-4">
            <p class="text-xs font-semibold uppercase text-amber-700">Partielles</p>
            <p class="mt-1 text-2xl font-bold text-amber-800"><?= number_format((int) $counts['partial_count'], 0, ',', ' ') ?></p>
            <p class="mt-1 text-xs text-amber-700">Clients ayant payé une partie</p>
        </article>
        <article class="rounded-md border border-green-200 bg-green-50 p-4">
            <p class="text-xs font-semibold uppercase text-green-700">Payées</p>
            <p class="mt-1 text-2xl font-bold text-green-800"><?= number_format((int) $counts['paid_count'], 0, ',', ' ') ?></p>
            <p class="mt-1 text-xs text-green-700">Totalement réglées</p>
        </article>
    </div>

    <!-- Résumé monétaire -->
    <div class="grid gap-3 sm:grid-cols-3">
        <article class="rounded-md border bg-white p-4">
            <p class="text-xs font-semibold uppercase text-slate-500">Total TTC</p>
            <p class="mt-1 text-lg font-bold text-slate-900"><?= number_format((float) $counts['total_ttc'], 0, ',', ' ') ?> FBu</p>
        </article>
        <article class="rounded-md border bg-white p-4">
            <p class="text-xs font-semibold uppercase text-slate-500">Encaissé</p>
            <p class="mt-1 text-lg font-bold text-green-800">+<?= number_format((float) $counts['total_paid'], 0, ',', ' ') ?> FBu</p>
        </article>
        <article class="rounded-md border bg-white p-4">
            <p class="text-xs font-semibold uppercase text-slate-500">Reste dû</p>
            <p class="mt-1 text-lg font-bold <?= (float) $counts['total_due'] > 0 ? 'text-rose-700' : 'text-slate-400' ?>">
                <?= number_format((float) $counts['total_due'], 0, ',', ' ') ?> FBu
            </p>
        </article>
    </div>

    <!-- Onglets + filtres -->
    <div class="space-y-3">
        <nav class="flex flex-wrap gap-2 rounded-md border border-slate-200 bg-white p-2" aria-label="Filtrer par état">
            <?php
            $tabs = [
                'all'     => ['Toutes', $counts['total_count'], 'bg-slate-100 text-slate-700'],
                'unpaid'  => ['Impayées', $counts['unpaid_count'], 'bg-rose-100 text-rose-800'],
                'partial' => ['Partielles', $counts['partial_count'], 'bg-amber-100 text-amber-800'],
                'paid'    => ['Payées', $counts['paid_count'], 'bg-green-100 text-green-800'],
            ];
            foreach ($tabs as $key => [$label, $count, $activeClass]):
                $isActive = $status === $key;
            ?>
                <a href="<?= $escape($tabUrl($key)) ?>"
                   class="inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition <?= $isActive ? $activeClass . ' ring-2 ring-offset-1 ring-slate-300' : 'text-slate-600 hover:bg-slate-50' ?>">
                    <?= $escape($label) ?>
                    <span class="rounded-full bg-white/70 px-2 py-0.5 text-xs font-semibold"><?= number_format((int) $count, 0, ',', ' ') ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <form method="get" action="<?= $escape(app_url('router.php')) ?>"
              class="grid gap-3 rounded-md border border-slate-200 bg-white p-4 sm:grid-cols-2 lg:grid-cols-4">
            <input type="hidden" name="route" value="<?= $escape($role . '/caisse/transactions') ?>">
            <input type="hidden" name="statut" value="<?= $escape($status) ?>">

            <label class="space-y-1 text-sm font-medium text-slate-700 lg:col-span-2">
                Recherche
                <input name="q" value="<?= $escape($search) ?>" placeholder="Facture, client, téléphone…"
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
                    <a href="<?= $escape($tabUrl($status)) ?>"
                       class="inline-flex items-center justify-center rounded-md px-3 py-2 text-sm text-slate-600 hover:bg-slate-100">Effacer</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tableau -->
    <div class="overflow-x-auto rounded-md border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">Facture</th>
                    <th class="px-4 py-3">Client</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3 text-right">Total TTC</th>
                    <th class="px-4 py-3 text-right">Encaissé</th>
                    <th class="px-4 py-3 text-right">Reste dû</th>
                    <th class="px-4 py-3">Progression</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (!$invoices): ?>
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center">
                            <p class="font-medium text-slate-700">Aucune facture pour ce filtre</p>
                            <p class="mt-1 text-sm text-slate-500">Essayez un autre onglet ou modifiez la recherche.</p>
                        </td>
                    </tr>
                <?php else: foreach ($invoices as $inv):
                    $totalAmt  = (float) $inv['total_amount'];
                    $paidAmt   = (float) $inv['netcash'];
                    $debtAmt   = (float) $inv['debt'];
                    $pct       = $totalAmt > 0 ? min(100, (int) round($paidAmt / $totalAmt * 100)) : 0;
                    $isPaid    = $debtAmt <= 0;
                    $isPartial = !$isPaid && $paidAmt > 0;
                    $isUnpaid  = !$isPaid && $paidAmt <= 0;

                    if ($isPaid) {
                        $badge = ['Payée', 'bg-green-100 text-green-800', 'bg-green-500'];
                    } elseif ($isPartial) {
                        $badge = ['Partielle', 'bg-amber-100 text-amber-800', 'bg-amber-500'];
                    } else {
                        $badge = ['Impayée', 'bg-rose-100 text-rose-800', 'bg-rose-500'];
                    }
                ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <a href="<?= $escape(app_route_url('ventes/facture')) . '?id=' . (int) $inv['id'] ?>"
                               class="font-mono text-xs font-semibold text-green-800 hover:underline">
                                <?= $escape($inv['invoice_number']) ?>
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-800"><?= $escape($inv['client_name']) ?></div>
                            <?php if (!empty($inv['phone'])): ?>
                                <div class="text-xs text-slate-500"><?= $escape($inv['phone']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate-600"><?= $escape(date('d/m/Y', strtotime($inv['date']))) ?></td>
                        <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-slate-900"><?= number_format($totalAmt, 0, ',', ' ') ?> FBu</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-green-800"><?= number_format($paidAmt, 0, ',', ' ') ?> FBu</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right <?= $debtAmt > 0 ? 'font-semibold text-rose-700' : 'text-slate-400' ?>">
                            <?= number_format($debtAmt, 0, ',', ' ') ?> FBu
                        </td>
                        <td class="px-4 py-3">
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="rounded-full px-2 py-0.5 font-medium <?= $badge[1] ?>"><?= $badge[0] ?></span>
                                    <span class="font-semibold text-slate-700"><?= $pct ?>%</span>
                                </div>
                                <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-200">
                                    <div class="h-full <?= $badge[2] ?>" style="width: <?= $pct ?>%"></div>
                                </div>
                                <div class="text-[11px] text-slate-500">
                                    <?= number_format($paidAmt, 0, ',', ' ') ?> / <?= number_format($totalAmt, 0, ',', ' ') ?> FBu
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-1">
                                <?php if (!$isPaid): ?>
                                    <button type="button"
                                            class="partial-pay inline-flex items-center justify-center rounded border border-slate-300 p-2 text-slate-700 hover:bg-slate-100"
                                            title="Enregistrer un paiement"
                                            data-id="<?= (int) $inv['id'] ?>"
                                            data-number="<?= $escape($inv['invoice_number']) ?>"
                                            data-debt="<?= $debtAmt ?>"
                                            data-total="<?= $totalAmt ?>"
                                            data-paid="<?= $paidAmt ?>">
                                        <i data-feather="dollar-sign" class="h-4 w-4"></i>
                                    </button>

                                    <form method="post" class="inline-block"
                                          onsubmit="return confirm('Marquer <?= $escape($inv['invoice_number']) ?> comme PAYÉE ? Le solde de <?= number_format($debtAmt, 0, ',', ' ') ?> FBu sera encaissé.');">
                                        <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="action" value="mark_paid">
                                        <input type="hidden" name="sale_id" value="<?= (int) $inv['id'] ?>">
                                        <button type="submit"
                                                class="inline-flex items-center justify-center rounded border border-green-300 bg-green-50 p-2 text-green-700 hover:bg-green-100"
                                                title="Marquer comme payée">
                                            <i data-feather="check" class="h-4 w-4"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="post" class="inline-block"
                                          onsubmit="return confirm('Marquer <?= $escape($inv['invoice_number']) ?> comme IMPAYÉE ?');">
                                        <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="action" value="mark_unpaid">
                                        <input type="hidden" name="sale_id" value="<?= (int) $inv['id'] ?>">
                                        <button type="submit"
                                                class="inline-flex items-center justify-center rounded border border-rose-300 bg-rose-50 p-2 text-rose-700 hover:bg-rose-100"
                                                title="Marquer comme impayée">
                                            <i data-feather="x" class="h-4 w-4"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <a href="<?= $escape(app_route_url('ventes/facture')) . '?id=' . (int) $inv['id'] ?>"
                                   class="inline-flex items-center justify-center rounded border border-slate-300 p-2 text-slate-700 hover:bg-slate-100"
                                   title="Voir la facture">
                                    <i data-feather="eye" class="h-4 w-4"></i>
                                </a>
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

<!-- MODAL paiement -->
<div id="partialModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" data-close></div>
    <div class="relative mx-auto mt-24 max-w-md rounded-lg bg-white shadow-xl">
        <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Enregistrer un paiement</h2>
                <p id="partialInvoice" class="mt-1 font-mono text-xs text-slate-500">—</p>
            </div>
            <button type="button" data-close class="rounded p-2 text-slate-500 hover:bg-slate-100">
                <i data-feather="x" class="h-5 w-5"></i>
            </button>
        </header>

        <form method="post" class="space-y-4 px-5 py-5">
            <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="action" value="partial_payment">
            <input type="hidden" name="sale_id" id="partialSaleId" value="0">

            <div class="grid grid-cols-2 gap-3 text-sm">
                <div class="rounded-md bg-slate-50 p-3">
                    <p class="text-xs uppercase text-slate-500">Total</p>
                    <p id="partialTotal" class="mt-1 font-semibold text-slate-900">0 FBu</p>
                </div>
                <div class="rounded-md bg-green-50 p-3">
                    <p class="text-xs uppercase text-green-700">Déjà payé</p>
                    <p id="partialPaid" class="mt-1 font-semibold text-green-800">0 FBu</p>
                </div>
            </div>
            <div class="rounded-md bg-amber-50 p-3 text-sm text-amber-900">
                Reste à payer : <strong id="partialDebt">0 FBu</strong>
            </div>

            <label class="block space-y-1 text-sm font-medium text-slate-700">
                Montant reçu (FBu)
                <input name="amount" id="partialAmount" type="number" min="1" step="1" required
                       class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal">
            </label>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" data-close class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Annuler</button>
                <button type="submit" class="rounded-md bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-800">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    var modal       = document.getElementById('partialModal');
    var modalInv    = document.getElementById('partialInvoice');
    var modalId     = document.getElementById('partialSaleId');
    var modalDebt   = document.getElementById('partialDebt');
    var modalTotal  = document.getElementById('partialTotal');
    var modalPaid   = document.getElementById('partialPaid');
    var modalAmount = document.getElementById('partialAmount');

    function formatMoney(v) {
        return new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(v || 0) + ' FBu';
    }

    document.querySelectorAll('.partial-pay').forEach(function (btn) {
        btn.addEventListener('click', function () {
            modalInv.textContent  = btn.dataset.number;
            modalId.value         = btn.dataset.id;
            modalTotal.textContent = formatMoney(btn.dataset.total);
            modalPaid.textContent  = formatMoney(btn.dataset.paid);
            modalDebt.textContent  = formatMoney(btn.dataset.debt);
            modalAmount.max        = btn.dataset.debt;
            modalAmount.value      = '';
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            modalAmount.focus();
        });
    });

    modal.querySelectorAll('[data-close]').forEach(function (el) {
        el.addEventListener('click', function () {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    });
});
</script>