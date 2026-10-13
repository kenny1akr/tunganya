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
$db = Database::getInstance();
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$search = trim((string) ($_GET['q'] ?? ''));
$fromDate = (string) ($_GET['du'] ?? '');
$toDate = (string) ($_GET['au'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 15;
$conditions = ["document_type = 'Commande'"];
$params = [];

if ($search !== '') {
    $conditions[] = '(invoice_number LIKE ? OR client_name LIKE ? OR phone LIKE ?)';
    $pattern = '%' . $search . '%';
    array_push($params, $pattern, $pattern, $pattern);
}
foreach (['du' => $fromDate, 'au' => $toDate] as $field => $dateValue) {
    $parsedDate = DateTime::createFromFormat('!Y-m-d', $dateValue);
    if ($dateValue !== '' && $parsedDate && $parsedDate->format('Y-m-d') === $dateValue) {
        $conditions[] = $field === 'du' ? 'date >= ?' : 'date <= ?';
        $params[] = $dateValue;
    } else {
        ${$field === 'du' ? 'fromDate' : 'toDate'} = '';
    }
}

$where = 'WHERE ' . implode(' AND ', $conditions);
$totalOrders = (int) $db->fetchColumn("SELECT COUNT(*) FROM sales $where", $params);
$totalPages = max(1, (int) ceil($totalOrders / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;
$orders = $db->fetchAll(
    "SELECT id, invoice_number, client_name, phone, medicines, quantity, date, total_amount, status
     FROM sales $where
     ORDER BY created_at DESC, id DESC
     LIMIT $perPage OFFSET $offset",
    $params
);
$listRoute = $role . '/commandes/clients';
$flashMessage = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);
?>
<section class="space-y-5">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div><p class="text-sm font-semibold text-green-800">Commandes clients</p><h1 class="mt-1 text-2xl font-bold text-slate-900">Suivi des commandes</h1><p class="mt-1 text-sm text-slate-600"><?= number_format($totalOrders, 0, ',', ' ') ?> commande(s)</p></div>
        <?php if (userCan($user, 'commandes.create')): ?>
            <a href="<?= $escape(app_route_url('commandes/nouvelle')) ?>" class="inline-flex items-center justify-center gap-2 rounded-md bg-green-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-800"><i data-feather="plus" class="h-4 w-4"></i> Nouvelle commande</a>
        <?php endif; ?>
    </header>

    <?php if ($flashMessage): ?>
        <div role="status" class="rounded-md border px-4 py-3 text-sm <?= ($flashMessage['type'] ?? '') === 'success' ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-800' ?>"><?= $escape($flashMessage['text'] ?? '') ?></div>
    <?php endif; ?>

    <form method="get" action="<?= $escape(app_url('router.php')) ?>" class="grid gap-3 rounded-md border border-slate-200 bg-white p-4 sm:grid-cols-2 xl:grid-cols-[minmax(14rem,1fr)_auto_auto_auto_auto] xl:items-end">
        <input type="hidden" name="route" value="<?= $escape($listRoute) ?>">
        <label class="space-y-1 text-sm font-medium text-slate-700">Recherche<input name="q" value="<?= $escape($search) ?>" placeholder="Référence, client, téléphone" class="w-full rounded-md border border-slate-300 px-3 py-2 font-normal focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20"></label>
        <label class="space-y-1 text-sm font-medium text-slate-700">Du<input type="date" name="du" value="<?= $escape($fromDate) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2 font-normal"></label>
        <label class="space-y-1 text-sm font-medium text-slate-700">Au<input type="date" name="au" value="<?= $escape($toDate) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2 font-normal"></label>
        <button class="inline-flex items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium hover:bg-slate-50"><i data-feather="search" class="h-4 w-4"></i> Filtrer</button>
        <?php if ($search !== '' || $fromDate !== '' || $toDate !== ''): ?><a href="<?= $escape(app_route_url('commandes/clients')) ?>" class="inline-flex items-center justify-center rounded-md px-3 py-2 text-sm text-slate-600 hover:bg-slate-100">Effacer</a><?php endif; ?>
    </form>

    <div class="overflow-x-auto rounded-md border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-4 py-3">Référence</th><th class="px-4 py-3">Client</th><th class="px-4 py-3">Articles</th><th class="px-4 py-3">Date</th><th class="px-4 py-3 text-right">Total</th><th class="px-4 py-3">Statut</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (!$orders): ?>
                    <tr><td colspan="6" class="px-4 py-12 text-center"><p class="font-medium text-slate-700">Aucune commande trouvée</p><p class="mt-1 text-sm text-slate-500">Modifiez les filtres ou créez une commande client.</p></td></tr>
                <?php else: foreach ($orders as $order): ?>
                    <?php
                    $lineItems = json_decode((string) $order['medicines'], true);
                    if (!is_array($lineItems)) {
                        $lineItems = array_filter(array_map('trim', explode(',', (string) $order['medicines'])));
                    }
                    $itemCount = count($lineItems);
                    ?>
                    <tr class="hover:bg-slate-50">
                        <td class="whitespace-nowrap px-4 py-3 font-semibold text-slate-900"><?= $escape($order['invoice_number']) ?></td>
                        <td class="px-4 py-3"><div class="font-medium text-slate-800"><?= $escape($order['client_name']) ?></div><div class="text-xs text-slate-500"><?= $escape($order['phone'] ?: '—') ?></div></td>
                        <td class="px-4 py-3 text-slate-600"><?= number_format($itemCount, 0, ',', ' ') ?> article(s)</td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate-600"><?= $escape(date('d/m/Y', strtotime($order['date']))) ?></td>
                        <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-slate-900"><?= number_format((float) $order['total_amount'], 0, ',', ' ') ?> FBu</td>
                        <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-medium <?= $order['status'] === 'Active' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-700' ?>"><?= $escape($order['status']) ?></span></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <nav class="flex items-center justify-between gap-3" aria-label="Pagination"><span class="text-sm text-slate-600">Page <?= $page ?> sur <?= $totalPages ?></span><div class="flex gap-2"><?php foreach (['Précédent' => max(1, $page - 1), 'Suivant' => min($totalPages, $page + 1)] as $label => $targetPage): ?><a href="<?= $escape(app_url('router.php') . '?' . http_build_query(['route' => $listRoute, 'q' => $search, 'du' => $fromDate, 'au' => $toDate, 'page' => $targetPage])) ?>" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-50"><?= $label ?></a><?php endforeach; ?></div></nav>
    <?php endif; ?>
</section>