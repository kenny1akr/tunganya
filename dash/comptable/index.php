<?php
if (!defined('ROUTER_LOADED')) { http_response_code(403); exit('Accès direct interdit.'); }
if (!userCan($user, 'dashboard.view')) { http_response_code(403); echo '<div class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</div>'; return; }
require_once __DIR__ . '/../../api/database.php';
require_once __DIR__ . '/_finance.php';
$db = Database::getInstance();
$todaySales = (float) $db->fetchColumn("SELECT COALESCE(SUM(netcash), 0) FROM sales WHERE status = 'Active' AND date = CURDATE()");
$monthSales = (float) $db->fetchColumn("SELECT COALESCE(SUM(total_amount), 0) FROM sales WHERE status = 'Active' AND YEAR(date) = YEAR(CURDATE()) AND MONTH(date) = MONTH(CURDATE())");
$monthExpenses = (float) $db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE status = 'Active' AND YEAR(date) = YEAR(CURDATE()) AND MONTH(date) = MONTH(CURDATE())");
$unpaidDebt = (float) $db->fetchColumn('SELECT COALESCE(SUM(GREATEST(COALESCE(amount,0) - COALESCE(paid_amount,0),0)),0) FROM debts WHERE status IN (0,2)');
$cashRegister = $db->fetch('SELECT date, initial, montant_in, montant_out, balance, status FROM caisse ORDER BY date DESC LIMIT 1');
$recentOperations = $db->fetchAll("SELECT invoice_number AS reference, client_name AS description, total_amount AS amount, date AS operation_date, 'Vente' AS operation_type FROM sales WHERE status = 'Active' ORDER BY date DESC, id DESC LIMIT 6");
?>
<section class="space-y-6">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div><p class="text-sm font-semibold text-amber-800">Espace comptabilité</p><h1 class="mt-1 text-2xl font-bold text-slate-900">Bonjour <?= financeEscape($user['nom_complet'] ?? '') ?></h1><p class="mt-1 text-sm text-slate-600">Suivi de la caisse, des recettes, des dépenses et des créances.</p></div>
        <a href="<?= financeEscape(app_route_url('caisse/transactions')) ?>" class="inline-flex items-center gap-2 rounded-md bg-green-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-800"><i data-feather="list" class="h-4 w-4"></i> Transactions caisse</a>
    </header>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <?php foreach ([
            ['Encaissements du jour', number_format($todaySales, 0, ',', ' ') . ' FBu', 'trending-up', 'green'],
            ['Ventes du mois', number_format($monthSales, 0, ',', ' ') . ' FBu', 'bar-chart-2', 'blue'],
            ['Dépenses du mois', number_format($monthExpenses, 0, ',', ' ') . ' FBu', 'credit-card', 'rose'],
            ['Créances restantes', number_format($unpaidDebt, 0, ',', ' ') . ' FBu', 'alert-circle', 'amber'],
        ] as [$label, $value, $icon, $color]): ?>
            <article class="flex items-start justify-between rounded-md border border-slate-200 bg-white p-5"><div><p class="text-sm text-slate-500"><?= financeEscape($label) ?></p><p class="mt-2 text-xl font-bold text-slate-900"><?= financeEscape($value) ?></p></div><span class="inline-flex h-10 w-10 items-center justify-center rounded-md bg-<?= financeEscape($color) ?>-50 text-<?= financeEscape($color) ?>-700"><i data-feather="<?= financeEscape($icon) ?>" class="h-5 w-5"></i></span></article>
        <?php endforeach; ?>
    </div>

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1.5fr)_minmax(18rem,1fr)]">
        <section class="overflow-hidden rounded-md border border-slate-200 bg-white">
            <div class="flex items-center justify-between border-b px-5 py-4"><div><h2 class="font-semibold text-slate-900">Activité récente</h2><p class="mt-1 text-sm text-slate-500">Dernières ventes enregistrées</p></div><a href="<?= financeEscape(app_route_url('caisse/transactions')) ?>" class="text-sm font-medium text-green-800 hover:underline">Tout voir</a></div>
            <div class="overflow-x-auto"><table class="min-w-full divide-y text-sm"><thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Référence</th><th class="px-5 py-3">Client</th><th class="px-5 py-3">Date</th><th class="px-5 py-3 text-right">Montant</th></tr></thead><tbody class="divide-y divide-slate-100">
                <?php if (!$recentOperations): ?><tr><td colspan="4" class="px-5 py-10 text-center text-slate-500">Aucune vente enregistrée.</td></tr><?php else: foreach ($recentOperations as $operation): ?><tr><td class="px-5 py-3 font-medium text-slate-900"><?= financeEscape($operation['reference']) ?></td><td class="px-5 py-3"><?= financeEscape($operation['description']) ?></td><td class="px-5 py-3 text-slate-600"><?= financeEscape(date('d/m/Y', strtotime($operation['operation_date']))) ?></td><td class="px-5 py-3 text-right font-semibold"><?= number_format((float)$operation['amount'], 0, ',', ' ') ?> FBu</td></tr><?php endforeach; endif; ?>
            </tbody></table></div>
        </section>
        <section class="rounded-md border border-slate-200 bg-white p-5"><div class="flex items-center justify-between"><div><h2 class="font-semibold text-slate-900">Dernière caisse</h2><p class="mt-1 text-sm text-slate-500"><?= $cashRegister ? financeEscape(date('d/m/Y', strtotime($cashRegister['date']))) : 'Aucune journée ouverte' ?></p></div><i data-feather="dollar-sign" class="h-5 w-5 text-green-700"></i></div><?php if ($cashRegister): ?><dl class="mt-5 space-y-3 text-sm"><div class="flex justify-between"><dt class="text-slate-500">Solde de départ</dt><dd class="font-medium"><?= number_format((float)$cashRegister['initial'],0,',',' ') ?> FBu</dd></div><div class="flex justify-between"><dt class="text-slate-500">Entrées</dt><dd class="font-medium text-green-700">+<?= number_format((float)$cashRegister['montant_in'],0,',',' ') ?> FBu</dd></div><div class="flex justify-between"><dt class="text-slate-500">Sorties</dt><dd class="font-medium text-rose-700">−<?= number_format((float)$cashRegister['montant_out'],0,',',' ') ?> FBu</dd></div><div class="flex justify-between border-t pt-3"><dt class="font-semibold">Solde</dt><dd class="font-bold text-slate-900"><?= number_format((float)$cashRegister['balance'],0,',',' ') ?> FBu</dd></div></dl><?php else: ?><p class="mt-5 text-sm text-slate-500">Les données apparaîtront après l’ouverture de la caisse.</p><?php endif; ?><div class="mt-5 flex flex-col gap-2 border-t pt-4"><a href="<?= financeEscape(app_route_url('caisse/rapports')) ?>" class="text-sm font-medium text-green-800 hover:underline">Consulter les rapports financiers</a><a href="<?= financeEscape(app_route_url('caisse/etats')) ?>" class="text-sm font-medium text-green-800 hover:underline">Voir les états comptables</a></div></section>
    </div>
</section>