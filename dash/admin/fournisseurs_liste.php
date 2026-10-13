<?php
if (!defined('ROUTER_LOADED')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

if (!userCan($user, 'fournisseurs.view')) {
    http_response_code(403);
    echo '<div class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</div>';
    return;
}

require_once __DIR__ . '/../../api/database.php';
$db = Database::getInstance();
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$role = $user['role_system'];
$canEditSuppliers = userCan($user, 'fournisseurs.edit');
$canDeleteSuppliers = userCan($user, 'fournisseurs.delete');
$flashMessage = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'delete_supplier') {
    $supplierId = (int) ($_POST['supplier_id'] ?? 0);
    $csrfToken = (string) ($_POST['csrf_token'] ?? '');

    if (!$canDeleteSuppliers) {
        $flashMessage = ['type' => 'error', 'text' => 'Vous n’êtes pas autorisé à supprimer des fournisseurs.'];
    } elseif (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $flashMessage = ['type' => 'error', 'text' => 'La demande a expiré. Rechargez la page puis réessayez.'];
    } else {
        $supplier = $db->fetch("SELECT id, name FROM partners WHERE id = ? AND category = 'fournisseur' LIMIT 1", [$supplierId]);
        if (!$supplier) {
            $flashMessage = ['type' => 'error', 'text' => 'Ce fournisseur n’existe plus.'];
        } else {
            $relatedTables = [
                ['sales', 'client_id', 'commandes ou ventes'],
                ['debts', 'client_id', 'dettes'],
                ['documents', 'client_id', 'documents'],
                ['tasks', 'client_id', 'tâches'],
                ['mutuelle_details', 'partner_id', 'détails de mutuelle'],
            ];
            $references = [];
            foreach ($relatedTables as [$table, $column, $label]) {
                if ((int) $db->fetchColumn("SELECT COUNT(*) FROM `$table` WHERE `$column` = ?", [$supplierId]) > 0) {
                    $references[] = $label;
                }
            }

            if ($references) {
                $flashMessage = [
                    'type' => 'error',
                    'text' => 'Suppression impossible : ce fournisseur est lié à ' . implode(', ', $references) . '. Conservez sa fiche.',
                ];
            } else {
                try {
                    $db->execute("DELETE FROM partners WHERE id = ? AND category = 'fournisseur'", [$supplierId]);
                    $flashMessage = ['type' => 'success', 'text' => 'Le fournisseur « ' . $supplier['name'] . ' » a été supprimé.'];
                } catch (Throwable $error) {
                    error_log('Supplier deletion failed: ' . $error->getMessage());
                    $flashMessage = ['type' => 'error', 'text' => 'Le fournisseur n’a pas pu être supprimé.'];
                }
            }
        }
    }

    $_SESSION['flash_message'] = $flashMessage;
    header('Location: ' . app_route_url('fournisseurs/liste'));
    exit;
}

$search = trim((string) ($_GET['q'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 20;
$conditions = ["category = 'fournisseur'"];
$params = [];

if ($search !== '') {
    $conditions[] = '(name LIKE ? OR phone LIKE ? OR email LIKE ? OR RC LIKE ? OR NIF LIKE ?)';
    $pattern = '%' . $search . '%';
    array_push($params, $pattern, $pattern, $pattern, $pattern, $pattern);
}

$where = 'WHERE ' . implode(' AND ', $conditions);
$totalSuppliers = (int) $db->fetchColumn("SELECT COUNT(*) FROM partners $where", $params);
$totalPages = max(1, (int) ceil($totalSuppliers / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;
$suppliers = $db->fetchAll(
    "SELECT id, name, phone, email, address, RC, NIF, created_at
     FROM partners $where
     ORDER BY name ASC, id DESC
     LIMIT $perPage OFFSET $offset",
    $params
);
$pageUrl = static fn(int $targetPage): string => app_route_url('fournisseurs/liste', $role) . '?' . http_build_query([
    'q' => $search,
    'page' => $targetPage,
]);
?>
<section class="space-y-5">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-green-800">Achats & approvisionnement</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Liste des fournisseurs</h1>
            <p class="mt-1 text-sm text-slate-600"><?= number_format($totalSuppliers, 0, ',', ' ') ?> fournisseur(s)</p>
        </div>
        <?php if (userCan($user, 'fournisseurs.create')): ?>
            <a href="<?= $escape(app_route_url('fournisseurs/ajouter')) ?>" class="inline-flex items-center justify-center gap-2 rounded-md bg-green-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-800"><i data-feather="user-plus" class="h-4 w-4"></i> Ajouter un fournisseur</a>
        <?php endif; ?>
    </header>

    <?php if ($flashMessage): ?>
        <div role="status" class="rounded-md border px-4 py-3 text-sm <?= ($flashMessage['type'] ?? '') === 'success' ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-800' ?>"><?= $escape($flashMessage['text'] ?? '') ?></div>
    <?php endif; ?>

    <form method="get" action="<?= $escape(app_url('router.php')) ?>" class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white p-4 sm:flex-row sm:items-end">
        <input type="hidden" name="route" value="<?= $escape($role . '/fournisseurs/liste') ?>">
        <label class="flex-1 space-y-1 text-sm font-medium text-slate-700">Recherche<input name="q" value="<?= $escape($search) ?>" placeholder="Nom, téléphone, email, RC ou NIF" class="w-full rounded-md border border-slate-300 px-3 py-2 font-normal focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20"></label>
        <button class="inline-flex items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium hover:bg-slate-50"><i data-feather="search" class="h-4 w-4"></i> Rechercher</button>
        <?php if ($search !== ''): ?><a href="<?= $escape(app_route_url('fournisseurs/liste')) ?>" class="inline-flex items-center justify-center rounded-md px-3 py-2 text-sm text-slate-600 hover:bg-slate-100">Effacer</a><?php endif; ?>
    </form>

    <div class="overflow-x-auto rounded-md border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-4 py-3">Fournisseur</th><th class="px-4 py-3">Coordonnées</th><th class="px-4 py-3">RC</th><th class="px-4 py-3">NIF</th><th class="px-4 py-3">Adresse</th><th class="px-4 py-3">Ajouté le</th><?php if ($canEditSuppliers || $canDeleteSuppliers): ?><th class="px-4 py-3 text-right">Actions</th><?php endif; ?></tr></thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (!$suppliers): ?>
                    <tr><td colspan="<?= $canEditSuppliers || $canDeleteSuppliers ? '7' : '6' ?>" class="px-4 py-12 text-center"><p class="font-medium text-slate-700">Aucun fournisseur trouvé</p><p class="mt-1 text-sm text-slate-500"><?= $search !== '' ? 'Modifiez votre recherche.' : 'Ajoutez un fournisseur pour le retrouver dans ce répertoire.' ?></p></td></tr>
                <?php else: foreach ($suppliers as $supplier): ?>
                    <tr class="align-top hover:bg-slate-50">
                        <td class="px-4 py-3"><div class="font-semibold text-slate-900"><?= $escape($supplier['name']) ?></div><div class="text-xs text-slate-500">Fournisseur #<?= (int) $supplier['id'] ?></div></td>
                        <td class="px-4 py-3 text-slate-600"><div><?= $escape($supplier['phone'] ?: 'Téléphone non renseigné') ?></div><div class="text-xs"><?= $escape($supplier['email'] ?: 'Email non renseigné') ?></div></td>
                        <td class="px-4 py-3 text-slate-600"><?= $escape($supplier['RC'] ?: '—') ?></td>
                        <td class="px-4 py-3 text-slate-600"><?= $escape($supplier['NIF'] ?: '—') ?></td>
                        <td class="max-w-xs px-4 py-3 text-slate-600"><?= $escape($supplier['address'] ?: '—') ?></td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate-600"><?= $escape(date('d/m/Y', strtotime($supplier['created_at']))) ?></td>
                        <?php if ($canEditSuppliers || $canDeleteSuppliers): ?>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <div class="inline-flex items-center justify-end gap-2">
                                    <?php if ($canEditSuppliers): ?>
                                        <a href="<?= $escape(app_route_url('fournisseurs/modifier')) . '?id=' . (int) $supplier['id'] ?>" class="inline-flex items-center justify-center rounded border border-slate-300 p-2 text-slate-700 hover:bg-slate-100" aria-label="Modifier <?= $escape($supplier['name']) ?>" title="Modifier"><i data-feather="edit-2" class="h-4 w-4"></i></a>
                                    <?php endif; ?>
                                    <?php if ($canDeleteSuppliers): ?>
                                        <form method="post" onsubmit="return confirm('Supprimer le fournisseur ' + <?= json_encode((string) $supplier['name'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?> + ' ? Cette action est définitive.');">
                                            <input type="hidden" name="action" value="delete_supplier">
                                            <input type="hidden" name="supplier_id" value="<?= (int) $supplier['id'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_token']) ?>">
                                            <button type="submit" class="inline-flex items-center justify-center rounded border border-red-200 p-2 text-red-700 hover:bg-red-50" aria-label="Supprimer <?= $escape($supplier['name']) ?>" title="Supprimer"><i data-feather="trash-2" class="h-4 w-4"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <nav class="flex items-center justify-between gap-3" aria-label="Pagination"><span class="text-sm text-slate-600">Page <?= $page ?> sur <?= $totalPages ?></span><div class="flex gap-2"><?php if ($page > 1): ?><a href="<?= $escape($pageUrl($page - 1)) ?>" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-50">Précédent</a><?php endif; ?><?php if ($page < $totalPages): ?><a href="<?= $escape($pageUrl($page + 1)) ?>" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-50">Suivant</a><?php endif; ?></div></nav>
    <?php endif; ?>
</section>