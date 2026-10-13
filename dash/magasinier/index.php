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
$unitsInStock = (float) $db->fetchColumn('SELECT COALESCE(SUM(actual_quantity), 0) FROM stock');
$lowStockCount = (int) $db->fetchColumn('SELECT COUNT(*) FROM stock WHERE actual_quantity <= seuil');
$outOfStockCount = (int) $db->fetchColumn('SELECT COUNT(*) FROM stock WHERE actual_quantity <= 0');
$todayEntries = (float) $db->fetchColumn("SELECT COALESCE(SUM(quantity), 0) FROM stock_movements WHERE movement_type = 'entree' AND DATE(created_at) = CURDATE()");
$todayExits = (float) $db->fetchColumn("SELECT COALESCE(SUM(quantity), 0) FROM stock_movements WHERE movement_type = 'sortie' AND DATE(created_at) = CURDATE()");
$recentMovements = $db->fetchAll(
    'SELECT product_name, movement_type, quantity, quantity_before, quantity_after, operator, created_at
     FROM stock_movements ORDER BY id DESC LIMIT 8'
);
?>
<section class="space-y-6">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-blue-800">Gestion des stocks</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Bonjour <?= inventoryEscape($user['nom_complet'] ?? '') ?></h1>
            <p class="mt-1 text-sm text-slate-600">Vue opérationnelle des produits, des entrées et des sorties.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <?php if (userCan($user, 'stock.create')): ?><a href="<?= inventoryEscape(app_route_url('stock/ajouter')) ?>" class="inline-flex items-center gap-2 rounded-md bg-green-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-800"><i data-feather="plus" class="h-4 w-4"></i> Nouveau produit</a><?php endif; ?>
            <?php if (userCan($user, 'flux.entrees')): ?><a href="<?= inventoryEscape(app_route_url('stock/entrees')) ?>" class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"><i data-feather="download" class="h-4 w-4"></i> Réceptionner</a><?php endif; ?>
        </div>
    </header>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <?php foreach ([
            ['Références produits', number_format($totalProducts, 0, ',', ' '), 'package', 'blue'],
            ['Unités disponibles', number_format($unitsInStock, 2, ',', ' '), 'layers', 'green'],
            ['Sous le seuil', number_format($lowStockCount, 0, ',', ' '), 'alert-triangle', 'amber'],
            ['Ruptures', number_format($outOfStockCount, 0, ',', ' '), 'slash', 'rose'],
        ] as [$label, $value, $icon, $tone]): ?>
            <article class="flex items-start justify-between rounded-md border border-slate-200 bg-white p-5"><div><p class="text-sm text-slate-500"><?= inventoryEscape($label) ?></p><p class="mt-2 text-2xl font-bold text-slate-900"><?= inventoryEscape($value) ?></p></div><span class="inline-flex h-10 w-10 items-center justify-center rounded-md bg-<?= inventoryEscape($tone) ?>-50 text-<?= inventoryEscape($tone) ?>-700"><i data-feather="<?= inventoryEscape($icon) ?>" class="h-5 w-5"></i></span></article>
        <?php endforeach; ?>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <article class="rounded-md border border-slate-200 bg-white p-5"><p class="text-sm text-slate-500">Entrées aujourd’hui</p><p class="mt-2 text-2xl font-bold text-green-800">+<?= number_format($todayEntries, 2, ',', ' ') ?></p></article>
        <article class="rounded-md border border-slate-200 bg-white p-5"><p class="text-sm text-slate-500">Sorties aujourd’hui</p><p class="mt-2 text-2xl font-bold text-rose-700">−<?= number_format($todayExits, 2, ',', ' ') ?></p></article>
    </div>

    <section class="overflow-hidden rounded-md border border-slate-200 bg-white">
        <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-5 py-4"><div><h2 class="font-semibold text-slate-900">Derniers mouvements</h2><p class="mt-1 text-sm text-slate-500">Entrées, sorties et corrections récentes</p></div><a href="<?= inventoryEscape(app_route_url('stock/mouvements')) ?>" class="text-sm font-medium text-green-800 hover:underline">Voir l’historique</a></div>
        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Produit</th><th class="px-5 py-3">Mouvement</th><th class="px-5 py-3 text-right">Qté</th><th class="px-5 py-3">Avant → après</th><th class="px-5 py-3">Opérateur</th><th class="px-5 py-3">Date</th></tr></thead><tbody class="divide-y divide-slate-100">
            <?php if (!$recentMovements): ?><tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">Aucun mouvement enregistré.</td></tr><?php else: foreach ($recentMovements as $movement): ?>
                <?php $typeLabel = ['entree' => 'Entrée', 'sortie' => 'Sortie', 'correction' => 'Correction'][$movement['movement_type']] ?? $movement['movement_type']; ?>
                <tr><td class="px-5 py-3 font-medium text-slate-900"><?= inventoryEscape($movement['product_name']) ?></td><td class="px-5 py-3"><?= inventoryEscape($typeLabel) ?></td><td class="px-5 py-3 text-right"><?= number_format((float) $movement['quantity'], 2, ',', ' ') ?></td><td class="px-5 py-3 text-slate-600"><?= number_format((float) $movement['quantity_before'], 2, ',', ' ') ?> → <?= number_format((float) $movement['quantity_after'], 2, ',', ' ') ?></td><td class="px-5 py-3 text-slate-600"><?= inventoryEscape($movement['operator']) ?></td><td class="px-5 py-3 text-slate-600"><?= inventoryEscape(date('d/m/Y H:i', strtotime($movement['created_at']))) ?></td></tr>
            <?php endforeach; endif; ?>
        </tbody></table></div>
    </section>
</section>