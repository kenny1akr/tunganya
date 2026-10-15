<?php
if (!defined('ROUTER_LOADED')) { http_response_code(403); exit('Accès direct interdit.'); }
if (!userCan($user, 'factures.view')) { http_response_code(403); echo '<div class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</div>'; return; }
require_once __DIR__ . '/../../api/database.php';
require_once __DIR__ . '/_sales.php';
$db = Database::getInstance();
$search = trim((string) ($_GET['q'] ?? ''));
$conditions = ["document_type = 'Commande'", "origin = 'Sortie client magasin'", "status = 'Active'"];
$params = [];
if ($search !== '') {
    $conditions[] = '(invoice_number LIKE ? OR client_name LIKE ? OR phone LIKE ?)';
    $pattern = '%' . $search . '%';
    array_push($params, $pattern, $pattern, $pattern);
}
$where = 'WHERE ' . implode(' AND ', $conditions);
$total = (int) $db->fetchColumn("SELECT COUNT(*) FROM sales $where", $params);
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 20;
$pages = max(1, (int) ceil($total / $perPage));
$page = min($page, $pages);
$offset = ($page - 1) * $perPage;
$issues = $db->fetchAll("SELECT id, invoice_number, client_name, phone, date, total_amount, products_json FROM sales $where ORDER BY date, id LIMIT $perPage OFFSET $offset", $params);
$pageUrl = static fn(int $target): string => app_route_url('ventes/factures-attente') . '?' . http_build_query(['q' => $search, 'page' => $target]);
?>
<section class="space-y-5">
    <header><p class="text-sm font-semibold text-amber-800">Transmission magasin</p><h1 class="mt-1 text-2xl font-bold text-slate-900">Factures à établir</h1><p class="mt-1 text-sm text-slate-600"><?= number_format($total, 0, ',', ' ') ?> sortie(s) client en attente</p></header>
    <form method="get" action="<?= receptionEscape(app_url('router.php')) ?>" class="grid gap-3 rounded-md border border-slate-200 bg-white p-4 sm:grid-cols-[1fr_auto_auto] sm:items-end"><input type="hidden" name="route" value="<?= receptionEscape($role . '/ventes/factures-attente') ?>"><label class="space-y-1 text-sm font-medium">Recherche<input name="q" value="<?= receptionEscape($search) ?>" placeholder="Référence, client, téléphone" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label><button class="rounded-md border border-slate-300 px-4 py-2.5 text-sm font-medium">Filtrer</button><?php if ($search !== ''): ?><a href="<?= receptionEscape(app_route_url('ventes/factures-attente')) ?>" class="px-3 py-2 text-center text-sm text-slate-600">Effacer</a><?php endif; ?></form>
    <div class="overflow-x-auto rounded-md border border-slate-200 bg-white"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-4 py-3">Référence de sortie</th><th class="px-4 py-3">Client</th><th class="px-4 py-3">Articles</th><th class="px-4 py-3">Date</th><th class="px-4 py-3 text-right">Montant indicatif</th><th class="px-4 py-3">Action</th></tr></thead><tbody class="divide-y divide-slate-100">
        <?php if (!$issues): ?><tr><td colspan="6" class="px-4 py-12 text-center text-slate-500">Aucune sortie client n’attend de facturation.</td></tr><?php else: foreach ($issues as $issue): $items = json_decode((string) $issue['products_json'], true); ?>
            <tr><td class="whitespace-nowrap px-4 py-3 font-mono text-xs font-semibold"><?= receptionEscape($issue['invoice_number']) ?></td><td class="px-4 py-3"><div class="font-medium"><?= receptionEscape($issue['client_name']) ?></div><div class="text-xs text-slate-500"><?= receptionEscape($issue['phone'] ?: '—') ?></div></td><td class="min-w-64 px-4 py-3"><?php if (is_array($items) && $items): ?><div class="space-y-2"><?php foreach ($items as $item): ?><div class="border-b border-slate-100 pb-2 last:border-0 last:pb-0"><div class="font-medium"><?= receptionEscape($item['name'] ?? 'Article') ?></div><div class="text-xs text-slate-600"><?= number_format((float) ($item['quantity'] ?? 0), 2, ',', ' ') ?> <?= receptionEscape($item['unit'] ?? '') ?> × <?= number_format((float) ($item['unit_price'] ?? 0), 0, ',', ' ') ?> FBu = <?= number_format((float) ($item['line_total'] ?? 0), 0, ',', ' ') ?> FBu</div></div><?php endforeach; ?></div><?php else: ?>—<?php endif; ?></td><td class="px-4 py-3"><?= receptionEscape(date('d/m/Y', strtotime($issue['date']))) ?></td><td class="px-4 py-3 text-right font-medium"><?= number_format((float) $issue['total_amount'], 0, ',', ' ') ?> FBu</td><td class="px-4 py-3"><a href="<?= receptionEscape(app_route_url('ventes/nouvelle')) ?>?issue_id=<?= (int) $issue['id'] ?>" class="font-semibold text-green-800 hover:underline">Établir la facture</a></td></tr>
        <?php endforeach; endif; ?>
    </tbody></table></div>
    <?php if ($pages > 1): ?><nav class="flex items-center justify-between"><span class="text-sm text-slate-600">Page <?= $page ?> sur <?= $pages ?></span><div class="flex gap-2"><?php if ($page > 1): ?><a class="rounded border px-3 py-2 text-sm" href="<?= receptionEscape($pageUrl($page - 1)) ?>">Précédent</a><?php endif; ?><?php if ($page < $pages): ?><a class="rounded border px-3 py-2 text-sm" href="<?= receptionEscape($pageUrl($page + 1)) ?>">Suivant</a><?php endif; ?></div></nav><?php endif; ?>
</section>