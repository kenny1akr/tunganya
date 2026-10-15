<?php
if (!defined('ROUTER_LOADED')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}
if (!userCan($user, 'dashboard.view')) {
    http_response_code(403);
    echo '<div class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</div>';
    return;
}

require_once __DIR__ . '/../../api/database.php';
require_once __DIR__ . '/_inventory.php';
$db = Database::getInstance();
$totalProducts = (int) $db->fetchColumn('SELECT COUNT(*) FROM stock');
$unitGroups = $db->fetchAll("SELECT sell_type, COALESCE(SUM(actual_quantity), 0) AS quantity FROM stock GROUP BY sell_type ORDER BY sell_type");
$lowStockCount = (int) $db->fetchColumn('SELECT COUNT(*) FROM stock WHERE actual_quantity > 0 AND actual_quantity <= seuil');
$outOfStockCount = (int) $db->fetchColumn('SELECT COUNT(*) FROM stock WHERE actual_quantity <= 0');
$todayEntries = (int) $db->fetchColumn("SELECT COUNT(*) FROM stock_movements WHERE movement_type = 'entree' AND created_at >= CURDATE() AND created_at < CURDATE() + INTERVAL 1 DAY");
$todayExits = (int) $db->fetchColumn("SELECT COUNT(*) FROM stock_movements WHERE movement_type = 'sortie' AND created_at >= CURDATE() AND created_at < CURDATE() + INTERVAL 1 DAY");
$stockAlerts = $db->fetchAll('SELECT id, medicine_name, actual_quantity, seuil, sell_type FROM stock WHERE actual_quantity <= seuil ORDER BY actual_quantity ASC, medicine_name LIMIT 6');
$recentMovements = $db->fetchAll(
    'SELECT m.product_name, m.movement_type, m.quantity, m.quantity_before, m.quantity_after, m.operator, m.created_at, s.sell_type
     FROM stock_movements m LEFT JOIN stock s ON s.id = m.product_id ORDER BY m.id DESC LIMIT 8'
);
?>
<section class="w-full min-w-0 space-y-6">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-blue-800">Gestion des stocks</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Bonjour <?= inventoryEscape($user['nom_complet'] ?? '') ?></h1>
            <p class="mt-1 text-sm text-slate-600">Vue opérationnelle des produits, des entrées et des sorties.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <?php if (userCan($user, 'stock.create')): ?><a href="<?= inventoryEscape(app_route_url('stock/ajouter')) ?>" class="inline-flex items-center gap-2 rounded-md bg-green-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-800"><i data-feather="plus" class="h-4 w-4"></i> Nouveau produit</a><?php endif; ?>
            <?php if (userCan($user, 'stock.categories')): ?><a href="<?= inventoryEscape(app_route_url('stock/categories')) ?>" class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"><i data-feather="tag" class="h-4 w-4"></i> Catégories</a><?php endif; ?>
            <?php if (userCan($user, 'flux.entrees')): ?><a href="<?= inventoryEscape(app_route_url('stock/entrees')) ?>" class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"><i data-feather="download" class="h-4 w-4"></i> Réceptionner</a><?php endif; ?>
        </div>
    </header>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <?php foreach ([
            ['Références produits', number_format($totalProducts, 0, ',', ' '), 'package', 'blue'],
            ['Unités de mesure', number_format(count($unitGroups), 0, ',', ' '), 'layers', 'green'],
            ['Sous le seuil', number_format($lowStockCount, 0, ',', ' '), 'alert-triangle', 'amber'],
            ['Ruptures', number_format($outOfStockCount, 0, ',', ' '), 'slash', 'rose'],
        ] as [$label, $value, $icon, $tone]): ?>
            <article class="flex min-w-0 items-start justify-between gap-3 rounded-md border border-slate-200 bg-white p-4 sm:p-5"><div class="min-w-0"><p class="break-words text-sm text-slate-500"><?= inventoryEscape($label) ?></p><p class="mt-2 text-2xl font-bold text-slate-900"><?= inventoryEscape($value) ?></p></div><span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-<?= inventoryEscape($tone) ?>-50 text-<?= inventoryEscape($tone) ?>-700"><i data-feather="<?= inventoryEscape($icon) ?>" class="h-5 w-5"></i></span></article>
        <?php endforeach; ?>
    </div>

    <form method="get" action="<?= inventoryEscape(app_url('router.php')) ?>" class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white p-4 sm:flex-row sm:items-end">
        <input type="hidden" name="route" value="<?= inventoryEscape($user['role_system'] . '/stock') ?>">
        <label class="flex-1 space-y-1 text-sm font-medium text-slate-700">Recherche rapide d’article<input name="q" placeholder="Nom, référence ou code-barres" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label>
        <button class="inline-flex items-center justify-center gap-2 rounded-md bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white"><i data-feather="search" class="h-4 w-4"></i> Rechercher</button>
    </form>

    <div class="grid min-w-0 gap-4 lg:grid-cols-2">
        <section class="rounded-md border border-slate-200 bg-white p-5"><h2 class="font-semibold text-slate-900">Quantités disponibles par unité</h2><ul class="mt-3 divide-y divide-slate-100 text-sm">
            <?php if (!$unitGroups): ?><li class="py-4 text-slate-500">Aucun article au catalogue.</li><?php else: foreach ($unitGroups as $unitGroup): ?><li class="flex justify-between gap-4 py-2"><span class="text-slate-600"><?= inventoryEscape($unitGroup['sell_type'] ?: 'Unité non définie') ?></span><span class="font-semibold text-slate-900"><?= number_format((float) $unitGroup['quantity'], 2, ',', ' ') ?></span></li><?php endforeach; endif; ?>
        </ul></section>
        <div class="grid gap-4 sm:grid-cols-2"><article class="rounded-md border border-slate-200 bg-white p-5"><p class="text-sm text-slate-500">Entrées aujourd’hui</p><p class="mt-2 text-2xl font-bold text-green-800"><?= number_format($todayEntries, 0, ',', ' ') ?></p><p class="mt-1 text-xs text-slate-500">mouvement(s)</p></article><article class="rounded-md border border-slate-200 bg-white p-5"><p class="text-sm text-slate-500">Sorties aujourd’hui</p><p class="mt-2 text-2xl font-bold text-rose-700"><?= number_format($todayExits, 0, ',', ' ') ?></p><p class="mt-1 text-xs text-slate-500">mouvement(s)</p></article></div>
    </div>

    <section class="overflow-hidden rounded-md border border-amber-200 bg-white">
        <div class="flex items-center justify-between gap-3 border-b border-amber-100 px-5 py-4"><div><h2 class="font-semibold text-slate-900">Alertes de stock</h2><p class="mt-1 text-sm text-slate-500">Articles au seuil minimum ou en dessous</p></div><a href="<?= inventoryEscape(app_route_url('stock/alertes')) ?>" class="text-sm font-medium text-green-800 hover:underline">Toutes les alertes</a></div>
        <div class="divide-y divide-slate-100"><?php if (!$stockAlerts): ?><p class="px-5 py-6 text-sm text-slate-500">Aucune alerte active.</p><?php else: foreach ($stockAlerts as $alert): ?><a href="<?= inventoryEscape(app_route_url('stock')) ?>?q=<?= rawurlencode($alert['medicine_name']) ?>" class="flex items-center justify-between gap-4 px-5 py-3 hover:bg-slate-50"><span class="font-medium text-slate-900"><?= inventoryEscape($alert['medicine_name']) ?></span><span class="text-sm text-amber-800"><?= number_format((float) $alert['actual_quantity'], 2, ',', ' ') ?> / <?= number_format((float) $alert['seuil'], 2, ',', ' ') ?> <?= inventoryEscape($alert['sell_type']) ?></span></a><?php endforeach; endif; ?></div>
    </section>
    <section class="overflow-hidden rounded-md border border-slate-200 bg-white">
        <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-5 py-4"><div><h2 class="font-semibold text-slate-900">Derniers mouvements</h2><p class="mt-1 text-sm text-slate-500">Entrées, sorties et corrections récentes</p></div><a href="<?= inventoryEscape(app_route_url('stock/mouvements')) ?>" class="text-sm font-medium text-green-800 hover:underline">Voir l’historique</a></div>
        <div class="max-w-full overflow-x-auto"><table class="w-full min-w-[680px] table-fixed divide-y divide-slate-200 text-sm"><thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="w-[22%] px-3 py-3">Produit</th><th class="w-[14%] px-3 py-3">Mouvement</th><th class="w-[14%] px-3 py-3 text-right">Qté</th><th class="w-[18%] px-3 py-3">Avant → après</th><th class="w-[16%] px-3 py-3">Opérateur</th><th class="w-[16%] px-3 py-3">Date</th></tr></thead><tbody class="divide-y divide-slate-100">
            <?php if (!$recentMovements): ?><tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">Aucun mouvement enregistré.</td></tr><?php else: foreach ($recentMovements as $movement): ?>
                <?php $typeLabel = ['entree' => 'Entrée', 'sortie' => 'Sortie', 'correction' => 'Correction'][$movement['movement_type']] ?? $movement['movement_type']; ?>
                <tr><td class="break-words px-3 py-3 font-medium text-slate-900"><?= inventoryEscape($movement['product_name']) ?></td><td class="break-words px-3 py-3"><?= inventoryEscape($typeLabel) ?></td><td class="break-words px-3 py-3 text-right"><?= number_format((float) $movement['quantity'], 2, ',', ' ') ?> <?= inventoryEscape($movement['sell_type'] ?? '') ?></td><td class="break-words px-3 py-3 text-slate-600"><?= number_format((float) $movement['quantity_before'], 2, ',', ' ') ?> → <?= number_format((float) $movement['quantity_after'], 2, ',', ' ') ?></td><td class="break-words px-3 py-3 text-slate-600"><?= inventoryEscape($movement['operator']) ?></td><td class="break-words px-3 py-3 text-slate-600"><?= inventoryEscape(date('d/m/Y H:i', strtotime($movement['created_at']))) ?></td></tr>
            <?php endforeach; endif; ?>
        </tbody></table></div>
    </section>
</section>