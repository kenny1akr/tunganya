<?php
if (!defined('ROUTER_LOADED')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

if (!userCan($user, 'clients.view')) {
    http_response_code(403);
    echo '<p class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</p>';
    return;
}

require_once __DIR__ . '/../../api/database.php';
$db = Database::getInstance();
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$role = $user['role_system'];
$search = trim((string) ($_GET['q'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 20;
$conditions = ["category = 'client'", "status_client = 'VIP'"];
$params = [];
if ($search !== '') {
    $conditions[] = '(name LIKE ? OR phone LIKE ? OR email LIKE ? OR NIF LIKE ?)';
    $pattern = '%' . $search . '%';
    array_push($params, $pattern, $pattern, $pattern, $pattern);
}
$where = 'WHERE ' . implode(' AND ', $conditions);
$vipCount = (int) $db->fetchColumn("SELECT COUNT(*) FROM partners $where", $params);
$vipTotals = $db->fetch(
    "SELECT COALESCE(SUM(total_achats), 0) AS total_achats, COALESCE(SUM(frequence_achats), 0) AS total_visits
     FROM partners $where",
    $params
);
$totalPages = max(1, (int) ceil($vipCount / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;
$clients = $db->fetchAll(
    "SELECT id, name, phone, email, total_achats, frequence_achats, dernier_achat, rating
     FROM partners $where
     ORDER BY total_achats DESC, name ASC
     LIMIT $perPage OFFSET $offset",
    $params
);
$queryUrl = static fn(int $targetPage): string => app_route_url('clients/vip', $role) . '?' . http_build_query(['q' => $search, 'page' => $targetPage]);
?>
<section class="space-y-5">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-sm font-semibold text-amber-700">Fidélisation</p><h1 class="mt-1 text-2xl font-bold text-slate-900">Clients VIP</h1><p class="mt-1 text-sm text-slate-600">Clients classés VIP dans leur fiche.</p></div>
        <a href="<?= $escape(app_route_url('clients/liste')) ?>" class="inline-flex items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"><i data-feather="users" class="h-4 w-4"></i> Tous les clients</a>
    </header>

    <div class="grid gap-3 sm:grid-cols-2">
        <div class="border-l-4 border-amber-400 bg-white p-4 shadow-sm"><p class="text-xs font-semibold uppercase text-slate-500">Clients VIP</p><p class="mt-1 text-2xl font-bold text-slate-900"><?= number_format($vipCount, 0, ',', ' ') ?></p></div>
        <div class="border-l-4 border-green-600 bg-white p-4 shadow-sm"><p class="text-xs font-semibold uppercase text-slate-500">Achats cumulés</p><p class="mt-1 text-2xl font-bold text-slate-900"><?= number_format((float) ($vipTotals['total_achats'] ?? 0), 0, ',', ' ') ?> <span class="text-sm font-medium text-slate-500">FBu</span></p></div>
    </div>

    <form method="get" action="<?= $escape(app_url('router.php')) ?>" class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white p-4 sm:flex-row sm:items-end">
        <input type="hidden" name="route" value="<?= $escape($role . '/clients/vip') ?>">
        <label class="flex-1 space-y-1 text-sm font-medium text-slate-700">Recherche<input name="q" value="<?= $escape($search) ?>" placeholder="Nom, téléphone, email, NIF" class="w-full rounded-md border border-slate-300 px-3 py-2 font-normal focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20"></label>
        <button class="inline-flex items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium hover:bg-slate-50"><i data-feather="search" class="h-4 w-4"></i> Rechercher</button>
        <?php if ($search !== ''): ?><a href="<?= $escape(app_route_url('clients/vip')) ?>" class="inline-flex justify-center rounded-md px-3 py-2 text-sm text-slate-600 hover:bg-slate-100">Effacer</a><?php endif; ?>
    </form>

    <div class="overflow-x-auto rounded-md border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-4 py-3">Client VIP</th><th class="px-4 py-3">Coordonnées</th><th class="px-4 py-3 text-right">Achats cumulés</th><th class="px-4 py-3 text-right">Fréquence</th><th class="px-4 py-3">Dernier achat</th><th class="px-4 py-3 text-right">Note</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (!$clients): ?>
                    <tr><td colspan="6" class="px-4 py-12 text-center"><p class="font-medium text-slate-700">Aucun client VIP trouvé</p><p class="mt-1 text-sm text-slate-500"><?= $search !== '' ? 'Essayez une autre recherche.' : 'Les clients apparaîtront ici lorsque leur statut sera passé à VIP.' ?></p></td></tr>
                <?php else: foreach ($clients as $client): ?>
                    <tr class="hover:bg-amber-50/40">
                        <td class="px-4 py-3"><div class="flex items-center gap-2 font-semibold text-slate-900"><i data-feather="award" class="h-4 w-4 text-amber-500"></i><?= $escape($client['name']) ?></div><div class="pl-6 text-xs text-slate-500">Client #<?= (int) $client['id'] ?></div></td>
                        <td class="px-4 py-3 text-slate-600"><div><?= $escape($client['phone'] ?: 'Téléphone non renseigné') ?></div><div class="text-xs"><?= $escape($client['email'] ?: 'Email non renseigné') ?></div></td>
                        <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-slate-900"><?= number_format((float) ($client['total_achats'] ?? 0), 0, ',', ' ') ?> FBu</td>
                        <td class="px-4 py-3 text-right text-slate-600"><?= number_format((int) ($client['frequence_achats'] ?? 0), 0, ',', ' ') ?></td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate-600"><?= !empty($client['dernier_achat']) ? $escape(date('d/m/Y', strtotime($client['dernier_achat']))) : '—' ?></td>
                        <td class="px-4 py-3 text-right text-slate-600"><?= $client['rating'] !== null ? number_format((float) $client['rating'], 1, ',', ' ') . '/5' : '—' ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?><nav class="flex items-center justify-between gap-3" aria-label="Pagination"><span class="text-sm text-slate-600">Page <?= $page ?> sur <?= $totalPages ?></span><div class="flex gap-2"><?php if ($page > 1): ?><a href="<?= $escape($queryUrl($page - 1)) ?>" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-50">Précédent</a><?php endif; ?><?php if ($page < $totalPages): ?><a href="<?= $escape($queryUrl($page + 1)) ?>" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-50">Suivant</a><?php endif; ?></div></nav><?php endif; ?>
</section>