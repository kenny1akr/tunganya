<?php
if (!defined('ROUTER_LOADED')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

if (!userCan($user, 'commandes.view')) {
    http_response_code(403);
    echo '<p class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</p>';
    return;
}

require_once __DIR__ . '/../../api/database.php';
$db     = Database::getInstance();
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$role   = $user['role_system'];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// =========================================================================
// POST : changer état / changer livraison / annuler
// =========================================================================
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $csrf     = (string) ($_POST['csrf_token'] ?? '');
    $action   = (string) ($_POST['action']     ?? '');
    $ticketNo = trim((string) ($_POST['ticket_no'] ?? ''));

    if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
        $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'La demande a expiré. Rechargez la page.'];
        header('Location: ' . app_route_url('commandes/clients'));
        exit;
    }

    $task = $db->fetch(
        "SELECT id, ticket_no, status FROM tasks WHERE ticket_no = ? AND category = 'Commande' LIMIT 1",
        [$ticketNo]
    );

    if (!$task) {
        $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Commande introuvable.'];
        header('Location: ' . app_route_url('commandes/clients'));
        exit;
    }

    if ($action === 'change_status') {
        $newStatus = (string) ($_POST['new_status'] ?? '');
        $allowed = ['Nouveau','Consulté','En cours','Résolu','Annulé'];
        if (!in_array($newStatus, $allowed, true)) {
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Statut invalide.'];
        } else {
            try {
                $db->execute(
                    "UPDATE tasks SET status = ?, updated_at = NOW() WHERE id = ?",
                    [$newStatus, (int) $task['id']]
                );
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Statut de ' . $task['ticket_no'] . ' mis à jour : ' . $newStatus . '.'];
            } catch (Throwable $e) {
                error_log('Task status update failed: ' . $e->getMessage());
                $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Impossible de changer le statut.'];
            }
        }
    } elseif ($action === 'change_delivery') {
        $newDelivery = (string) ($_POST['new_delivery'] ?? '');
        $allowed = ['En préparation','Expédiée','Livrée','Annulée'];
        if (!in_array($newDelivery, $allowed, true)) {
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Statut livraison invalide.'];
        } else {
            try {
                $db->execute(
                    "UPDATE tasks SET delivery_status = ?, updated_at = NOW() WHERE id = ?",
                    [$newDelivery, (int) $task['id']]
                );
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Livraison de ' . $task['ticket_no'] . ' : ' . $newDelivery . '.'];
            } catch (Throwable $e) {
                error_log('Delivery status update failed: ' . $e->getMessage());
                $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Impossible de changer le statut livraison.'];
            }
        }
    } elseif ($action === 'cancel') {
        try {
            $db->execute(
                "UPDATE tasks SET status = 'Annulé', delivery_status = 'Annulée', updated_at = NOW() WHERE id = ?",
                [(int) $task['id']]
            );
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Commande ' . $task['ticket_no'] . ' annulée.'];
        } catch (Throwable $e) {
            error_log('Cancel failed: ' . $e->getMessage());
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Impossible d’annuler la commande.'];
        }
    }

    header('Location: ' . app_route_url('commandes/clients'));
    exit;
}

// =========================================================================
// Filtres
// =========================================================================
$search   = trim((string) ($_GET['q']        ?? ''));
$status   = (string) ($_GET['status']         ?? '');
$delivery = (string) ($_GET['delivery']       ?? '');
$assignee = (string) ($_GET['assigned_to']    ?? '');
$priority = (string) ($_GET['priority']       ?? '');
$fromDate = (string) ($_GET['du']             ?? '');
$toDate   = (string) ($_GET['au']             ?? '');
$page     = max(1, (int) ($_GET['page'] ?? 1));
$perPage  = 15;

$allowedStatuses   = ['Nouveau','Consulté','En cours','Résolu','Annulé'];
$allowedDeliveries = ['En préparation','Expédiée','Livrée','Annulée'];
$allowedPriorities = ['Basse','Moyenne','Haute','Critique'];

if (!in_array($status, $allowedStatuses, true))     $status = '';
if (!in_array($delivery, $allowedDeliveries, true)) $delivery = '';
if (!in_array($priority, $allowedPriorities, true)) $priority = '';

$validDate = static function (string $v): bool {
    if ($v === '') return false;
    $d = DateTime::createFromFormat('!Y-m-d', $v);
    return $d !== false && $d->format('Y-m-d') === $v;
};
if (!$validDate($fromDate)) $fromDate = '';
if (!$validDate($toDate))   $toDate   = '';

// =========================================================================
// Requête
// =========================================================================
$conditions = ["t.category = 'Commande'"];
$params     = [];

if ($search !== '') {
    $conditions[] = "(t.ticket_no LIKE ? OR t.client LIKE ? OR t.client_reference LIKE ? OR t.delivery_address LIKE ?)";
    $p = '%' . $search . '%';
    array_push($params, $p, $p, $p, $p);
}
if ($status !== '')   { $conditions[] = 't.status = ?';          $params[] = $status; }
if ($delivery !== '') { $conditions[] = 't.delivery_status = ?'; $params[] = $delivery; }
if ($assignee !== '') { $conditions[] = 't.assigned_to = ?';     $params[] = $assignee; }
if ($priority !== '') { $conditions[] = 't.priority = ?';        $params[] = $priority; }
if ($fromDate !== '') { $conditions[] = 'COALESCE(t.order_date, t.created_at) >= ?'; $params[] = $fromDate; }
if ($toDate !== '')   { $conditions[] = 'COALESCE(t.order_date, t.created_at) <= ?'; $params[] = $toDate; }

$where = 'WHERE ' . implode(' AND ', $conditions);

$total = (int) $db->fetchColumn("SELECT COUNT(*) FROM tasks t $where", $params);

$totalPages = max(1, (int) ceil($total / $perPage));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * $perPage;

$orders = $db->fetchAll(
    "SELECT
        t.id, t.ticket_no, t.client, t.client_id, t.walk_in, t.client_reference,
        t.priority, t.assigned_to, t.created_by, t.created_at, t.order_date,
        t.due_date, t.delivery_address, t.status, t.delivery_status,
        t.invoice_number, t.converted_at,
        t.items_json, t.subtotal, t.tax_amount, t.total_amount,
        t.deposit_amount, t.payment_terms,
        u.f_name, u.l_name, u.category AS assignee_category,
        (SELECT s.id FROM sales s
          WHERE s.invoice_number = t.invoice_number
            AND s.document_type = 'Facture'
          LIMIT 1) AS invoice_sale_id
     FROM tasks t
     LEFT JOIN users u ON u.user_name = t.assigned_to
     $where
     ORDER BY t.id DESC
     LIMIT $perPage OFFSET $offset",
    $params
);

$assignees = $db->fetchAll(
    "SELECT user_name, f_name, l_name, category
     FROM users
     WHERE category IN ('magasinier', 'travailleur')
     ORDER BY f_name, l_name"
);

$flashMessage = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);

$queryUrl = static function (int $targetPage) use ($search, $status, $delivery, $assignee, $priority, $fromDate, $toDate, $role): string {
    return app_route_url('commandes/clients', $role) . '?' . http_build_query([
        'q'           => $search,
        'status'      => $status,
        'delivery'    => $delivery,
        'assigned_to' => $assignee,
        'priority'    => $priority,
        'du'          => $fromDate,
        'au'          => $toDate,
        'page'        => $targetPage,
    ]);
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
$statusColor = static function (string $s): string {
    return match ($s) {
        'Nouveau'  => 'bg-sky-100 text-sky-800',
        'Consulté' => 'bg-indigo-100 text-indigo-800',
        'En cours' => 'bg-amber-100 text-amber-800',
        'Résolu'   => 'bg-green-100 text-green-800',
        'Annulé'   => 'bg-slate-200 text-slate-600',
        default    => 'bg-slate-100 text-slate-700',
    };
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
?>
<section class="space-y-5">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-green-800">Commandes clients</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Suivi des commandes</h1>
            <p class="mt-1 text-sm text-slate-600"><?= number_format($total, 0, ',', ' ') ?> commande(s)</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <?php if (userCan($user, 'factures.create')): ?>
                <a href="<?= $escape(app_route_url('factures/creer')) ?>"
                   class="inline-flex items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    <i data-feather="file-plus" class="h-4 w-4"></i> Factures à créer
                </a>
            <?php endif; ?>
            <?php if (userCan($user, 'commandes.create')): ?>
                <a href="<?= $escape(app_route_url('commandes/nouvelle')) ?>"
                   class="inline-flex items-center justify-center gap-2 rounded-md bg-green-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-800">
                    <i data-feather="plus" class="h-4 w-4"></i> Nouvelle commande
                </a>
            <?php endif; ?>
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
                 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <input type="hidden" name="route" value="<?= $escape($role . '/commandes/clients') ?>">

        <label class="space-y-1 text-sm font-medium text-slate-700 lg:col-span-2">
            Recherche
            <input name="q" value="<?= $escape($search) ?>"
                   placeholder="Ticket, client, référence, adresse"
                   class="w-full rounded-md border border-slate-300 px-3 py-2 font-normal">
        </label>

        <label class="space-y-1 text-sm font-medium text-slate-700">
            Statut
            <select name="status" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 font-normal">
                <option value="">Tous</option>
                <?php foreach ($allowedStatuses as $s): ?>
                    <option value="<?= $escape($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= $escape($s) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="space-y-1 text-sm font-medium text-slate-700">
            Livraison
            <select name="delivery" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 font-normal">
                <option value="">Toutes</option>
                <?php foreach ($allowedDeliveries as $d): ?>
                    <option value="<?= $escape($d) ?>" <?= $delivery === $d ? 'selected' : '' ?>><?= $escape($d) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="space-y-1 text-sm font-medium text-slate-700">
            Assignée à
            <select name="assigned_to" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 font-normal">
                <option value="">Tous</option>
                <?php foreach ($assignees as $a): ?>
                    <?php $label = trim(($a['f_name'] ?? '') . ' ' . ($a['l_name'] ?? '')) ?: $a['user_name']; ?>
                    <option value="<?= $escape($a['user_name']) ?>" <?= $assignee === $a['user_name'] ? 'selected' : '' ?>>
                        <?= $escape($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="space-y-1 text-sm font-medium text-slate-700">
            Priorité
            <select name="priority" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 font-normal">
                <option value="">Toutes</option>
                <?php foreach ($allowedPriorities as $p): ?>
                    <option value="<?= $escape($p) ?>" <?= $priority === $p ? 'selected' : '' ?>><?= $escape($p) ?></option>
                <?php endforeach; ?>
            </select>
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

        <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-1">
            <button class="inline-flex items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium hover:bg-slate-50">
                <i data-feather="search" class="h-4 w-4"></i> Filtrer
            </button>
            <?php if ($search !== '' || $status !== '' || $delivery !== '' || $assignee !== '' || $priority !== '' || $fromDate !== '' || $toDate !== ''): ?>
                <a href="<?= $escape(app_route_url('commandes/clients')) ?>"
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
                    <th class="px-4 py-3">Ticket</th>
                    <th class="px-4 py-3">Client</th>
                    <th class="px-4 py-3">Priorité</th>
                    <th class="px-4 py-3">Assignée à</th>
                    <th class="px-4 py-3">Livraison prévue</th>
                    <th class="px-4 py-3">Statut</th>
                    <th class="px-4 py-3">Livraison</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (!$orders): ?>
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center">
                            <p class="font-medium text-slate-700">Aucune commande trouvée</p>
                            <p class="mt-1 text-sm text-slate-500">Modifiez les filtres ou créez une nouvelle commande.</p>
                        </td>
                    </tr>
                <?php else: foreach ($orders as $order):
                    $items = json_decode((string) $order['items_json'], true);
                    $itemCount = is_array($items) ? count($items) : 0;
                    $assigneeName = trim(($order['f_name'] ?? '') . ' ' . ($order['l_name'] ?? '')) ?: ($order['assigned_to'] ?? '—');
                    $assigneeBadge = match ($order['assignee_category'] ?? '') {
                        'magasinier'  => 'Magasinier',
                        'travailleur' => 'Travailleur',
                        default       => '',
                    };
                    $isConverted = !empty($order['invoice_number']);
                    $isCancelled = $order['status'] === 'Annulé';
                ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <div class="font-mono text-xs font-semibold text-slate-900"><?= $escape($order['ticket_no']) ?></div>
                            <div class="text-xs text-slate-500"><?= $itemCount ?> article(s)</div>
                            <?php if ($isConverted && !empty($order['invoice_sale_id'])): ?>
                                <a href="<?= $escape(app_route_url('ventes/facture')) . '?id=' . (int) $order['invoice_sale_id'] ?>"
                                   class="mt-1 inline-flex items-center gap-1 text-[11px] font-medium text-green-800 hover:underline"
                                   title="Voir la facture générée">
                                    <i data-feather="file-text" class="h-3 w-3"></i> <?= $escape($order['invoice_number']) ?>
                                </a>
                            <?php elseif ($isConverted): ?>
                                <span class="mt-1 inline-flex items-center gap-1 text-[11px] font-medium text-green-800">
                                    <i data-feather="file-text" class="h-3 w-3"></i> <?= $escape($order['invoice_number']) ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-800">
                                <?= $escape($order['client']) ?>
                                <?php if ((int) $order['walk_in'] === 1): ?>
                                    <span class="ml-1 rounded bg-slate-200 px-1.5 py-0.5 text-[10px] font-medium uppercase text-slate-700">Passage</span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($order['client_reference'])): ?>
                                <div class="text-xs text-slate-500">Réf : <?= $escape($order['client_reference']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium <?= $priorityColor($order['priority']) ?>">
                                <?= $escape($order['priority']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-800"><?= $escape($assigneeName) ?></div>
                            <?php if ($assigneeBadge): ?>
                                <div class="text-xs text-slate-500"><?= $escape($assigneeBadge) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            <?= !empty($order['due_date']) ? $escape(date('d/m/Y', strtotime($order['due_date']))) : '—' ?>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium <?= $statusColor($order['status']) ?>">
                                <?= $escape($order['status']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-col gap-1">
                                <span class="rounded-full px-2.5 py-1 text-xs font-medium <?= $deliveryColor($order['delivery_status']) ?>">
                                    <?= $escape($order['delivery_status']) ?>
                                </span>
                                <?php if (!$isConverted && !$isCancelled): ?>
                                    <form method="post" class="inline-block">
                                        <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="action" value="change_delivery">
                                        <input type="hidden" name="ticket_no" value="<?= $escape($order['ticket_no']) ?>">
                                        <select name="new_delivery"
                                                class="rounded border border-slate-300 px-2 py-1 text-[11px]"
                                                onchange="this.form.submit()"
                                                title="Changer le statut de livraison">
                                            <?php foreach ($allowedDeliveries as $d): ?>
                                                <option value="<?= $escape($d) ?>" <?= $order['delivery_status'] === $d ? 'selected' : '' ?>>
                                                    <?= $escape($d) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                <?php else: ?>
                                    <span class="text-[11px] text-slate-400 italic">
                                        <?= $isConverted ? 'Facturée' : 'Annulée' ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-slate-900">
                            <?= number_format((float) $order['total_amount'], 0, ',', ' ') ?> FBu
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-1">
                                <!-- Voir -->
                                <button type="button"
                                        class="view-order inline-flex items-center justify-center rounded border border-slate-300 p-2 text-slate-700 hover:bg-slate-100"
                                        title="Voir le détail"
                                        data-ticket="<?= $escape($order['ticket_no']) ?>"
                                        data-client="<?= $escape($order['client']) ?>"
                                        data-items="<?= $escape($order['items_json']) ?>"
                                        data-subtotal="<?= (int) $order['subtotal'] ?>"
                                        data-tax="<?= (int) $order['tax_amount'] ?>"
                                        data-total="<?= (int) $order['total_amount'] ?>"
                                        data-deposit="<?= (int) $order['deposit_amount'] ?>"
                                        data-terms="<?= $escape($order['payment_terms'] ?? '') ?>"
                                        data-address="<?= $escape($order['delivery_address'] ?? '') ?>">
                                    <i data-feather="eye" class="h-4 w-4"></i>
                                </button>

                                <!-- Imprimer -->
                                <button type="button"
                                        class="print-order inline-flex items-center justify-center rounded border border-slate-300 p-2 text-slate-700 hover:bg-slate-100"
                                        title="Imprimer"
                                        data-ticket="<?= $escape($order['ticket_no']) ?>">
                                    <i data-feather="printer" class="h-4 w-4"></i>
                                </button>

                                <?php if (!$isConverted && !$isCancelled): ?>
                                    <!-- Changer état -->
                                    <form method="post" class="inline-block">
                                        <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="action" value="change_status">
                                        <input type="hidden" name="ticket_no" value="<?= $escape($order['ticket_no']) ?>">
                                        <select name="new_status"
                                                class="status-select rounded border border-slate-300 px-2 py-1 text-xs"
                                                onchange="this.form.submit()">
                                            <?php foreach ($allowedStatuses as $s): ?>
                                                <option value="<?= $escape($s) ?>" <?= $order['status'] === $s ? 'selected' : '' ?>>
                                                    <?= $escape($s) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>

                                    <!-- Annuler -->
                                    <form method="post" class="inline-block"
                                          onsubmit="return confirm('Annuler la commande <?= $escape($order['ticket_no']) ?> ?');">
                                        <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <input type="hidden" name="ticket_no" value="<?= $escape($order['ticket_no']) ?>">
                                        <button type="submit"
                                                class="inline-flex items-center justify-center rounded border border-red-200 p-2 text-red-700 hover:bg-red-50"
                                                title="Annuler la commande">
                                            <i data-feather="x" class="h-4 w-4"></i>
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

<!-- ========================================================= -->
<!-- MODAL DÉTAIL                                              -->
<!-- ========================================================= -->
<div id="orderModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" data-close></div>
    <div class="relative mx-auto mt-16 max-w-2xl rounded-lg bg-white shadow-xl" role="dialog" aria-modal="true">
        <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Détail de la commande</h2>
                <p id="modalTicket" class="mt-1 font-mono text-xs text-slate-500">—</p>
            </div>
            <button type="button" data-close class="rounded p-2 text-slate-500 hover:bg-slate-100">
                <i data-feather="x" class="h-5 w-5"></i>
            </button>
        </header>

        <div class="max-h-[70vh] overflow-y-auto px-5 py-4">
            <div class="mb-4">
                <p class="text-xs font-semibold uppercase text-slate-500">Client</p>
                <p id="modalClient" class="mt-1 text-lg font-semibold text-slate-900">—</p>
            </div>

            <div id="modalItems" class="mb-4 space-y-2"></div>

            <dl class="space-y-2 border-t border-slate-200 pt-4 text-sm">
                <div class="flex justify-between"><dt class="text-slate-600">Sous-total HT</dt><dd id="modalSubtotal">0 FBu</dd></div>
                <div class="flex justify-between"><dt class="text-slate-600">TVA</dt><dd id="modalTax">0 FBu</dd></div>
                <div class="flex justify-between border-t border-slate-200 pt-2 text-base font-bold"><dt>Total TTC</dt><dd id="modalTotal">0 FBu</dd></div>
                <div class="flex justify-between text-slate-600"><dt>Acompte</dt><dd id="modalDeposit">0 FBu</dd></div>
                <div class="flex justify-between font-semibold text-slate-800"><dt>Reste à payer</dt><dd id="modalRemaining">0 FBu</dd></div>
                <div class="flex justify-between text-slate-600"><dt>Conditions</dt><dd id="modalTerms">—</dd></div>
                <div class="flex justify-between text-slate-600"><dt>Adresse livraison</dt><dd id="modalAddress">—</dd></div>
            </dl>
        </div>

        <footer class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
            <button type="button" data-close class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Fermer</button>
        </footer>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    var modal       = document.getElementById('orderModal');
    var modalTicket = document.getElementById('modalTicket');
    var modalClient = document.getElementById('modalClient');
    var modalItems  = document.getElementById('modalItems');
    var modalSub    = document.getElementById('modalSubtotal');
    var modalTax    = document.getElementById('modalTax');
    var modalTotal  = document.getElementById('modalTotal');
    var modalDep    = document.getElementById('modalDeposit');
    var modalRem    = document.getElementById('modalRemaining');
    var modalTerms  = document.getElementById('modalTerms');
    var modalAddr   = document.getElementById('modalAddress');

    function formatMoney(v) {
        return new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(v || 0) + ' FBu';
    }
    function escapeHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function openModal(order) {
        modalTicket.textContent = order.ticket;
        modalClient.textContent = order.client;

        modalItems.innerHTML = '';
        if (Array.isArray(order.items) && order.items.length) {
            order.items.forEach(function (item) {
                var line = document.createElement('div');
                line.className = 'flex justify-between gap-3 rounded border border-slate-200 px-3 py-2 text-sm';
                var left = document.createElement('div');
                left.innerHTML =
                    '<div class="font-medium text-slate-800">' + escapeHtml(item.name || '—') + '</div>' +
                    (item.note ? '<div class="text-xs text-slate-500">' + escapeHtml(item.note) + '</div>' : '');
                var right = document.createElement('div');
                right.className = 'text-right text-slate-700';
                right.innerHTML =
                    '<div>' + (item.quantity || 0) + ' × ' + formatMoney(item.unit_price || 0) + '</div>' +
                    '<div class="font-semibold">' + formatMoney(item.line_total || 0) + '</div>';
                line.appendChild(left);
                line.appendChild(right);
                modalItems.appendChild(line);
            });
        } else {
            modalItems.innerHTML = '<p class="text-sm text-slate-500">Aucun article.</p>';
        }

        modalSub.textContent   = formatMoney(order.subtotal);
        modalTax.textContent   = formatMoney(order.tax);
        modalTotal.textContent = formatMoney(order.total);
        modalDep.textContent   = formatMoney(order.deposit);
        modalRem.textContent   = formatMoney(Math.max(0, order.total - order.deposit));
        modalTerms.textContent = order.terms || '—';
        modalAddr.textContent  = order.address || '—';

        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    document.querySelectorAll('.view-order').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var items = [];
            try { items = JSON.parse(btn.dataset.items || '[]'); } catch (e) { items = []; }
            openModal({
                ticket:   btn.dataset.ticket  || '',
                client:   btn.dataset.client  || '',
                items:    items,
                subtotal: parseInt(btn.dataset.subtotal || '0', 10),
                tax:      parseInt(btn.dataset.tax      || '0', 10),
                total:    parseInt(btn.dataset.total    || '0', 10),
                deposit:  parseInt(btn.dataset.deposit  || '0', 10),
                terms:    btn.dataset.terms   || '',
                address:  btn.dataset.address || '',
            });
        });
    });

    modal.querySelectorAll('[data-close]').forEach(function (el) {
        el.addEventListener('click', closeModal);
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });

    document.querySelectorAll('.print-order').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var ticket = btn.dataset.ticket;
            var w = window.open('', '_blank', 'width=800,height=900');
            if (!w) return;
            var styles = '<style>' +
                'body{font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;padding:30px;color:#0f172a}' +
                'h1{font-size:20px;margin:0 0 4px}' +
                '.muted{color:#64748b;font-size:12px}' +
                '</style>';
            var html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>' + ticket + '</title>' + styles + '</head><body>' +
                '<h1>DH TELECOM AND ADVERTISING HOUSE</h1>' +
                '<div class="muted">Bon de commande — ' + ticket + '</div>' +
                '</body></html>';
            w.document.write(html);
            w.document.close();
            w.focus();
            setTimeout(function () { w.print(); }, 250);
        });
    });
});
</script>