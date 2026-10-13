<?php
if (!defined('ROUTER_LOADED')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

if (!userCan($user, 'dashboard.view')) {
    http_response_code(403);
    echo '<p class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</p>';
    return;
}

require_once __DIR__ . '/../../api/database.php';
$db = Database::getInstance();
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$todayOrders = (int) $db->fetchColumn(
    "SELECT COUNT(*) FROM sales WHERE document_type = 'Commande' AND status = 'Active' AND date = CURDATE()"
);
$monthOrders = (int) $db->fetchColumn(
    "SELECT COUNT(*) FROM sales WHERE document_type = 'Commande' AND status = 'Active' AND YEAR(date) = YEAR(CURDATE()) AND MONTH(date) = MONTH(CURDATE())"
);
$clientCount = (int) $db->fetchColumn("SELECT COUNT(*) FROM partners WHERE category = 'client'");
$monthTotal = (float) $db->fetchColumn(
    "SELECT COALESCE(SUM(total_amount), 0) FROM sales WHERE document_type = 'Commande' AND status = 'Active' AND YEAR(date) = YEAR(CURDATE()) AND MONTH(date) = MONTH(CURDATE())"
);
$recentOrders = $db->fetchAll(
    "SELECT invoice_number, client_name, date, total_amount, status
     FROM sales
     WHERE document_type = 'Commande'
     ORDER BY created_at DESC, id DESC
     LIMIT 8"
);
?>
<section class="space-y-6">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-green-800">Espace réception</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Bonjour <?= $escape($user['nom_complet'] ?? ''); ?></h1>
            <p class="mt-1 text-sm text-slate-600">Suivez les commandes clients et préparez-en de nouvelles.</p>
        </div>
        <a href="<?= $escape(app_route_url('commandes/nouvelle')) ?>" class="inline-flex items-center justify-center gap-2 rounded-md bg-green-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-800">
            <i data-feather="plus" class="h-4 w-4"></i> Nouvelle commande
        </a>
    </header>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <?php foreach ([
            ['Commandes aujourd’hui', $todayOrders, 'shopping-bag', 'text-green-700', 'bg-green-50'],
            ['Commandes ce mois', $monthOrders, 'calendar', 'text-blue-700', 'bg-blue-50'],
            ['Clients enregistrés', $clientCount, 'users', 'text-amber-700', 'bg-amber-50'],
            ['Valeur des commandes du mois', number_format($monthTotal, 0, ',', ' ') . ' FBu', 'trending-up', 'text-rose-700', 'bg-rose-50'],
        ] as [$label, $value, $icon, $color, $background]): ?>
            <article class="flex items-start justify-between rounded-md border border-slate-200 bg-white p-5">
                <div><p class="text-sm text-slate-500"><?= $escape($label) ?></p><p class="mt-2 text-2xl font-bold text-slate-900"><?= $escape($value) ?></p></div>
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-md <?= $background ?> <?= $color ?>"><i data-feather="<?= $icon ?>" class="h-5 w-5"></i></span>
            </article>
        <?php endforeach; ?>
    </div>

    <section class="overflow-hidden rounded-md border border-slate-200 bg-white">
        <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
            <div><h2 class="font-semibold text-slate-900">Commandes récentes</h2><p class="mt-1 text-sm text-slate-500">Dernières commandes enregistrées</p></div>
            <a href="<?= $escape(app_route_url('commandes/clients')) ?>" class="text-sm font-medium text-green-800 hover:underline">Voir la liste</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Référence</th><th class="px-5 py-3">Client</th><th class="px-5 py-3">Date</th><th class="px-5 py-3 text-right">Total</th><th class="px-5 py-3">État</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!$recentOrders): ?>
                        <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">Aucune commande pour le moment.</td></tr>
                    <?php else: foreach ($recentOrders as $order): ?>
                        <tr class="hover:bg-slate-50"><td class="px-5 py-3 font-medium text-slate-900"><?= $escape($order['invoice_number']) ?></td><td class="px-5 py-3"><?= $escape($order['client_name']) ?></td><td class="px-5 py-3 text-slate-600"><?= $escape(date('d/m/Y', strtotime($order['date']))) ?></td><td class="px-5 py-3 text-right font-medium"><?= number_format((float) $order['total_amount'], 0, ',', ' ') ?> FBu</td><td class="px-5 py-3"><span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-800"><?= $escape($order['status']) ?></span></td></tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</section>