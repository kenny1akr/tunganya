<?php
if (!defined('ROUTER_LOADED')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

if (!userCan($user, 'factures.create')) {
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

$search   = trim((string) ($_GET['q'] ?? ''));
$page     = max(1, (int) ($_GET['page'] ?? 1));
$perPage  = 20;

$conditions = [
    "t.category = 'Commande'",
    "t.invoice_number IS NULL",
    "t.status NOT IN ('Annulé')",
    "t.delivery_status IN ('Expédiée','Livrée')",
];
$params = [];

if ($search !== '') {
    $conditions[] = "(t.ticket_no LIKE ? OR t.client LIKE ? OR t.client_reference LIKE ?)";
    $p = '%' . $search . '%';
    array_push($params, $p, $p, $p);
}

$where = 'WHERE ' . implode(' AND ', $conditions);

$total = (int) $db->fetchColumn("SELECT COUNT(*) FROM tasks t $where", $params);
$totalPages = max(1, (int) ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$pending = $db->fetchAll(
    "SELECT t.id, t.ticket_no, t.client, t.client_id, t.walk_in, t.client_reference,
            t.priority, t.assigned_to, t.order_date, t.due_date,
            t.status, t.delivery_status,
            t.items_json, t.subtotal, t.tax_amount, t.total_amount,
            t.deposit_amount, t.payment_terms,
            u.f_name, u.l_name
     FROM tasks t
     LEFT JOIN users u ON u.user_name = t.assigned_to
     $where
     ORDER BY t.due_date ASC, t.id DESC
     LIMIT $perPage OFFSET $offset",
    $params
);

$queryUrl = static function (int $targetPage) use ($search, $role): string {
    return app_route_url('factures/creer', $role) . '?' . http_build_query([
        'q'    => $search,
        'page' => $targetPage,
    ]);
};

$priorityColor = static fn(string $p): string => match ($p) {
    'Critique' => 'bg-rose-100 text-rose-800',
    'Haute'    => 'bg-amber-100 text-amber-800',
    'Moyenne'  => 'bg-blue-100 text-blue-800',
    'Basse'    => 'bg-slate-100 text-slate-700',
    default    => 'bg-slate-100 text-slate-700',
};

$deliveryColor = static fn(string $d): string => match ($d) {
    'En préparation' => 'bg-orange-100 text-orange-800',
    'Expédiée'       => 'bg-blue-100 text-blue-800',
    'Livrée'         => 'bg-green-100 text-green-800',
    'Annulée'        => 'bg-slate-200 text-slate-600',
    default          => 'bg-slate-100 text-slate-700',
};
?>
<section class="space-y-5">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-green-800">Facturation</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Commandes à facturer</h1>
            <p class="mt-1 text-sm text-slate-600">
                <?= number_format($total, 0, ',', ' ') ?> commande(s) prête(s) à convertir en facture.
                Seules les commandes <strong>expédiées</strong> ou <strong>livrées</strong> apparaissent ici.
            </p>
        </div>
        <a href="<?= $escape(app_route_url('factures/liste')) ?>"
           class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
            <i data-feather="file-text" class="h-4 w-4"></i> Liste des factures
        </a>
    </header>

    <?php if ($flashMessage): ?>
        <div role="status" class="rounded-md border px-4 py-3 text-sm <?= ($flashMessage['type'] ?? '') === 'success' ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-800' ?>">
            <?= $escape($flashMessage['text'] ?? '') ?>
        </div>
    <?php endif; ?>

    <form method="get" action="<?= $escape(app_url('router.php')) ?>"
          class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white p-4 sm:flex-row sm:items-end">
        <input type="hidden" name="route" value="<?= $escape($role . '/factures/creer') ?>">
        <label class="flex-1 space-y-1 text-sm font-medium text-slate-700">
            Recherche
            <input name="q" value="<?= $escape($search) ?>"
                   placeholder="Ticket, client, référence…"
                   class="w-full rounded-md border border-slate-300 px-3 py-2 font-normal">
        </label>
        <button class="inline-flex items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium hover:bg-slate-50">
            <i data-feather="search" class="h-4 w-4"></i> Rechercher
        </button>
        <?php if ($search !== ''): ?>
            <a href="<?= $escape(app_route_url('factures/creer')) ?>"
               class="inline-flex justify-center rounded-md px-3 py-2 text-sm text-slate-600 hover:bg-slate-100">Effacer</a>
        <?php endif; ?>
    </form>

    <div class="overflow-x-auto rounded-md border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">Ticket</th>
                    <th class="px-4 py-3">Client</th>
                    <th class="px-4 py-3">Priorité</th>
                    <th class="px-4 py-3">Assignée à</th>
                    <th class="px-4 py-3">Livraison</th>
                    <th class="px-4 py-3">Statut</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (!$pending): ?>
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center">
                            <div class="mx-auto max-w-sm">
                                <div class="mb-3 text-4xl">📦</div>
                                <p class="font-medium text-slate-700">Aucune commande à facturer</p>
                                <p class="mt-1 text-sm text-slate-500">
                                    Les commandes apparaîtront ici une fois qu’elles seront marquées
                                    <strong>Expédiées</strong> ou <strong>Livrées</strong>.
                                </p>
                            </div>
                        </td>
                    </tr>
                <?php else: foreach ($pending as $order):
                    $items = json_decode((string) $order['items_json'], true);
                    $itemCount = is_array($items) ? count($items) : 0;
                    $assigneeName = trim(($order['f_name'] ?? '') . ' ' . ($order['l_name'] ?? '')) ?: ($order['assigned_to'] ?? '—');
                ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <div class="font-mono text-xs font-semibold text-slate-900"><?= $escape($order['ticket_no']) ?></div>
                            <div class="text-xs text-slate-500"><?= $itemCount ?> article(s)</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-800">
                                <?= $escape($order['client']) ?>
                                <?php if ((int) $order['walk_in'] === 1): ?>
                                    <span class="ml-1 rounded bg-slate-200 px-1.5 py-0.5 text-[10px] font-medium uppercase text-slate-700">Passage</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium <?= $priorityColor($order['priority']) ?>">
                                <?= $escape($order['priority']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-600"><?= $escape($assigneeName) ?></td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium <?= $deliveryColor($order['delivery_status']) ?>">
                                <?= $escape($order['delivery_status']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-800">
                                <?= $escape($order['status']) ?>
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-slate-900">
                            <?= number_format((float) $order['total_amount'], 0, ',', ' ') ?> FBu
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            <a href="<?= $escape(app_route_url('factures/apercu')) . '?id=' . (int) $order['id'] ?>"
                               class="inline-flex items-center gap-2 rounded-md bg-green-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-800">
                                <i data-feather="file-plus" class="h-3.5 w-3.5"></i> Créer facture
                            </a>
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