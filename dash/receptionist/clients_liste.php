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
$flashMessage = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'delete_client') {
    $clientIdToDelete = (int) ($_POST['client_id'] ?? 0);
    $csrfToken = (string) ($_POST['csrf_token'] ?? '');

    if (!userCan($user, 'clients.delete')) {
        $flashMessage = ['type' => 'error', 'text' => 'Vous n’êtes pas autorisé à supprimer des clients.'];
    } elseif (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $flashMessage = ['type' => 'error', 'text' => 'La demande a expiré. Rechargez la page puis réessayez.'];
    } else {
        $clientToDelete = $db->fetch("SELECT id, name FROM partners WHERE id = ? AND category = 'client' LIMIT 1", [$clientIdToDelete]);
        if (!$clientToDelete) {
            $flashMessage = ['type' => 'error', 'text' => 'Ce client n’existe plus.'];
        } else {
            $relatedRecords = [
                'commandes ou ventes' => ['sales', 'client_id'],
                'dettes' => ['debts', 'client_id'],
                'documents' => ['documents', 'client_id'],
                'tâches' => ['tasks', 'client_id'],
                'détails de mutuelle' => ['mutuelle_details', 'partner_id'],
            ];
            $references = [];
            foreach ($relatedRecords as $label => [$table, $column]) {
                if ((int) $db->fetchColumn("SELECT COUNT(*) FROM `$table` WHERE `$column` = ?", [$clientIdToDelete]) > 0) {
                    $references[] = $label;
                }
            }

            if ($references) {
                $flashMessage = [
                    'type' => 'error',
                    'text' => 'Suppression impossible : ce client est lié à ' . implode(', ', $references) . '. Désactivez-le ou conservez sa fiche.',
                ];
            } else {
                try {
                    $db->execute("DELETE FROM partners WHERE id = ? AND category = 'client'", [$clientIdToDelete]);
                    $flashMessage = ['type' => 'success', 'text' => 'Le client « ' . $clientToDelete['name'] . ' » a été supprimé.'];
                } catch (Throwable $error) {
                    error_log('Client deletion failed: ' . $error->getMessage());
                    $flashMessage = ['type' => 'error', 'text' => 'Le client n’a pas pu être supprimé.'];
                }
            }
        }
    }

    $_SESSION['flash_message'] = $flashMessage;
    header('Location: ' . app_route_url('clients/liste'));
    exit;
}

$search = trim((string) ($_GET['q'] ?? ''));
$status = (string) ($_GET['statut'] ?? '');
$allowedStatuses = ['Nouveau', 'Régulier', 'VIP'];
if (!in_array($status, $allowedStatuses, true)) {
    $status = '';
}

$conditions = ["category = 'client'"];
$params = [];
if ($search !== '') {
    $conditions[] = '(name LIKE ? OR phone LIKE ? OR email LIKE ? OR NIF LIKE ?)';
    $pattern = '%' . $search . '%';
    array_push($params, $pattern, $pattern, $pattern, $pattern);
}
if ($status !== '') {
    $conditions[] = 'status_client = ?';
    $params[] = $status;
}
$where = 'WHERE ' . implode(' AND ', $conditions);
$totalClients = (int) $db->fetchColumn("SELECT COUNT(*) FROM partners $where", $params);
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 20;
$totalPages = max(1, (int) ceil($totalClients / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;
$clients = $db->fetchAll(
    "SELECT id, name, phone, email, portal_username, status_client, total_achats, frequence_achats, created_at
     FROM partners $where
     ORDER BY name ASC, id DESC
     LIMIT $perPage OFFSET $offset",
    $params
);
$queryUrl = static function (int $targetPage) use ($search, $status, $role): string {
    return app_route_url('clients/liste', $role) . '?' . http_build_query([
        'q' => $search,
        'statut' => $status,
        'page' => $targetPage,
    ]);
};
?>
<section class="space-y-5">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-green-800">Répertoire clients</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Liste des clients</h1>
            <p class="mt-1 text-sm text-slate-600"><?= number_format($totalClients, 0, ',', ' ') ?> client(s)</p>
        </div>
        <?php if (userCan($user, 'clients.create')): ?>
            <a href="<?= $escape(app_route_url('clients/ajouter')) ?>" class="inline-flex items-center justify-center gap-2 rounded-md bg-green-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-800"><i data-feather="user-plus" class="h-4 w-4"></i> Ajouter un client</a>
        <?php endif; ?>
    </header>

    <?php if ($flashMessage): ?>
        <div role="status" class="rounded-md border px-4 py-3 text-sm <?= ($flashMessage['type'] ?? '') === 'success' ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-800' ?>"><?= $escape($flashMessage['text'] ?? '') ?></div>
    <?php endif; ?>

    <form method="get" action="<?= $escape(app_url('router.php')) ?>" class="grid gap-3 rounded-md border border-slate-200 bg-white p-4 sm:grid-cols-[minmax(12rem,1fr)_12rem_auto_auto] sm:items-end">
        <input type="hidden" name="route" value="<?= $escape($role . '/clients/liste') ?>">
        <label class="space-y-1 text-sm font-medium text-slate-700">Recherche<input name="q" value="<?= $escape($search) ?>" placeholder="Nom, téléphone, email, NIF" class="w-full rounded-md border border-slate-300 px-3 py-2 font-normal focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20"></label>
        <label class="space-y-1 text-sm font-medium text-slate-700">Statut<select name="statut" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 font-normal"><option value="">Tous les statuts</option><?php foreach ($allowedStatuses as $clientStatus): ?><option value="<?= $escape($clientStatus) ?>" <?= $status === $clientStatus ? 'selected' : '' ?>><?= $escape($clientStatus) ?></option><?php endforeach; ?></select></label>
        <button class="inline-flex items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium hover:bg-slate-50"><i data-feather="search" class="h-4 w-4"></i> Filtrer</button>
        <?php if ($search !== '' || $status !== ''): ?><a href="<?= $escape(app_route_url('clients/liste')) ?>" class="inline-flex items-center justify-center rounded-md px-3 py-2 text-sm text-slate-600 hover:bg-slate-100">Effacer</a><?php endif; ?>
    </form>

    <div class="overflow-x-auto rounded-md border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-4 py-3">Client</th><th class="px-4 py-3">Coordonnées</th><th class="px-4 py-3">Identifiant portail</th><th class="px-4 py-3">Statut</th><th class="px-4 py-3 text-right">Achats cumulés</th><th class="px-4 py-3 text-right">Fréquence</th><th class="px-4 py-3">Créé le</th><th class="px-4 py-3 text-right">Actions</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (!$clients): ?>
                    <tr><td colspan="8" class="px-4 py-12 text-center"><p class="font-medium text-slate-700">Aucun client trouvé</p><p class="mt-1 text-sm text-slate-500">Modifiez les filtres ou ajoutez un nouveau client.</p></td></tr>
                <?php else: foreach ($clients as $client): ?>
                    <?php $clientStatus = (string) ($client['status_client'] ?? 'Nouveau'); ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3"><div class="font-semibold text-slate-900"><?= $escape($client['name']) ?></div><div class="text-xs text-slate-500">Client #<?= (int) $client['id'] ?></div></td>
                        <td class="px-4 py-3 text-slate-600"><div><?= $escape($client['phone'] ?: 'Téléphone non renseigné') ?></div><div class="text-xs"><?= $escape($client['email'] ?: 'Email non renseigné') ?></div></td>
                        <td class="px-4 py-3 font-mono text-xs text-slate-700"><?= $escape($client['portal_username'] ?: 'Non configuré') ?></td>
                        <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-medium <?= $clientStatus === 'VIP' ? 'bg-amber-100 text-amber-800' : ($clientStatus === 'Régulier' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700') ?>"><?= $escape($clientStatus) ?></span></td>
                        <td class="whitespace-nowrap px-4 py-3 text-right font-medium text-slate-800"><?= number_format((float) ($client['total_achats'] ?? 0), 0, ',', ' ') ?> FBu</td>
                        <td class="px-4 py-3 text-right text-slate-600"><?= number_format((int) ($client['frequence_achats'] ?? 0), 0, ',', ' ') ?></td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate-600"><?= $escape(date('d/m/Y', strtotime($client['created_at']))) ?></td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            <div class="inline-flex items-center justify-end gap-2">
                                <?php if (userCan($user, 'clients.edit')): ?>
                                    <a href="<?= $escape(app_route_url('clients/modifier')) . '?id=' . (int) $client['id'] ?>" class="inline-flex items-center justify-center rounded border border-slate-300 p-2 text-slate-700 hover:bg-slate-100" aria-label="Modifier <?= $escape($client['name']) ?>" title="Modifier"><i data-feather="edit-2" class="h-4 w-4"></i></a>
                                <?php endif; ?>
                                <?php if (userCan($user, 'clients.delete')): ?>
                                    <form method="post" onsubmit="return confirm('Supprimer le client ' + <?= json_encode((string) $client['name'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?> + ' ? Cette action est définitive.');">
                                        <input type="hidden" name="action" value="delete_client">
                                        <input type="hidden" name="client_id" value="<?= (int) $client['id'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_token']) ?>">
                                        <button type="submit" class="inline-flex items-center justify-center rounded border border-red-200 p-2 text-red-700 hover:bg-red-50" aria-label="Supprimer <?= $escape($client['name']) ?>" title="Supprimer"><i data-feather="trash-2" class="h-4 w-4"></i></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <nav class="flex items-center justify-between gap-3" aria-label="Pagination"><span class="text-sm text-slate-600">Page <?= $page ?> sur <?= $totalPages ?></span><div class="flex gap-2"><?php if ($page > 1): ?><a href="<?= $escape($queryUrl($page - 1)) ?>" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-50">Précédent</a><?php endif; ?><?php if ($page < $totalPages): ?><a href="<?= $escape($queryUrl($page + 1)) ?>" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-50">Suivant</a><?php endif; ?></div></nav>
    <?php endif; ?>
</section>