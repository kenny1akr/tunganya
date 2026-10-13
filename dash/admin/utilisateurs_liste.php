<?php
if (!defined('ROUTER_LOADED')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

if (!userCan($user, 'utilisateurs.view')) {
    http_response_code(403);
    echo '<div class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</div>';
    return;
}

require_once __DIR__ . '/../../api/database.php';
$db = Database::getInstance();
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$search = trim((string) ($_GET['q'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

if (!empty($_SESSION['flash_message'])) {
    $flashMessage = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['new_status'])) {
    $csrfToken = (string) ($_POST['csrf_token'] ?? '');
    $targetUserId = (int) $_POST['user_id'];
    $newStatus = (string) $_POST['new_status'];
    $validStatuses = ['Disponible', 'Désactivé'];

    if (!userCan($user, 'utilisateurs.create')) {
        http_response_code(403);
        $flashMessage = ['type' => 'error', 'text' => 'Vous n’êtes pas autorisé à modifier les comptes.'];
    } elseif (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
        $flashMessage = ['type' => 'error', 'text' => 'La demande a expiré. Rechargez la page puis réessayez.'];
    } elseif ($targetUserId === (int) $user['utilisateur_id']) {
        $flashMessage = ['type' => 'error', 'text' => 'Vous ne pouvez pas désactiver votre propre compte.'];
    } elseif (!in_array($newStatus, $validStatuses, true)) {
        $flashMessage = ['type' => 'error', 'text' => 'Statut invalide.'];
    } else {
        $db->execute('UPDATE users SET status = ? WHERE id = ?', [$newStatus, $targetUserId]);
        $flashMessage = ['type' => 'success', 'text' => 'Statut du compte mis à jour.'];
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$where = '';
$params = [];
if ($search !== '') {
    $where = 'WHERE user_name LIKE ? OR f_name LIKE ? OR l_name LIKE ? OR email LIKE ? OR category LIKE ?';
    $pattern = '%' . $search . '%';
    $params = array_fill(0, 5, $pattern);
}

$totalUsers = (int) $db->fetchColumn("SELECT COUNT(*) FROM users $where", $params);
$totalPages = max(1, (int) ceil($totalUsers / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;
$users = $db->fetchAll(
    "SELECT id, user_name, category, f_name, l_name, email, telephone, status, last_login
     FROM users $where
     ORDER BY created_at DESC, id DESC
     LIMIT $perPage OFFSET $offset",
    $params
);
?>
<section class="space-y-5">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Liste des utilisateurs</h1>
            <p class="mt-1 text-sm text-slate-600"><?= number_format($totalUsers, 0, ',', ' ') ?> compte(s)</p>
        </div>
        <?php if (userCan($user, 'utilisateurs.create')): ?>
            <a href="<?= $escape(app_route_url('utilisateurs/ajouter')) ?>" class="inline-flex items-center justify-center gap-2 rounded-md bg-green-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-800">
                <i data-feather="user-plus" class="h-4 w-4"></i> Ajouter un utilisateur
            </a>
        <?php endif; ?>
    </header>

    <?php if (isset($flashMessage)): ?>
        <div role="status" class="rounded-md border px-4 py-3 text-sm <?= $flashMessage['type'] === 'success' ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-800' ?>"><?= $escape($flashMessage['text']) ?></div>
    <?php endif; ?>

    <form method="get" action="<?= $escape(app_url('router.php')) ?>" class="flex flex-col gap-3 sm:flex-row">
        <input type="hidden" name="route" value="<?= $escape($role . '/utilisateurs/liste') ?>">
        <label class="sr-only" for="user-search">Rechercher un utilisateur</label>
        <input id="user-search" name="q" value="<?= $escape($search) ?>" placeholder="Nom, identifiant, email ou rôle" class="min-w-0 flex-1 rounded-md border border-slate-300 bg-white px-3 py-2 text-sm focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20">
        <button class="inline-flex items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium hover:bg-slate-50"><i data-feather="search" class="h-4 w-4"></i> Rechercher</button>
        <?php if ($search !== ''): ?><a href="<?= $escape(app_route_url('utilisateurs/liste')) ?>" class="inline-flex items-center justify-center rounded-md px-3 py-2 text-sm text-slate-600 hover:bg-slate-100">Effacer</a><?php endif; ?>
    </form>

    <div class="overflow-x-auto rounded-md border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr><th class="px-4 py-3 font-semibold">Utilisateur</th><th class="px-4 py-3 font-semibold">Contact</th><th class="px-4 py-3 font-semibold">Rôle</th><th class="px-4 py-3 font-semibold">Statut</th><th class="px-4 py-3 font-semibold">Dernière connexion</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (!$users): ?>
                    <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">Aucun utilisateur ne correspond à cette recherche.</td></tr>
                <?php else: foreach ($users as $listedUser): ?>
                    <?php $isActive = strtolower((string) $listedUser['status']) !== 'désactivé'; ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3"><div class="font-medium text-slate-900"><?= $escape(trim(($listedUser['f_name'] ?? '') . ' ' . ($listedUser['l_name'] ?? '')) ?: $listedUser['user_name']) ?></div><div class="text-xs text-slate-500">@<?= $escape($listedUser['user_name']) ?></div></td>
                        <td class="px-4 py-3 text-slate-600"><div><?= $escape($listedUser['email'] ?: '—') ?></div><div class="text-xs text-slate-500"><?= $escape($listedUser['telephone'] ?: '') ?></div></td>
                        <td class="px-4 py-3 text-slate-700"><?= $escape(getRoleLabel($listedUser['category'] ?? '')) ?></td>
                        <td class="px-4 py-3"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium <?= $isActive ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>"><?= $isActive ? 'Actif' : 'Désactivé' ?></span>
                            <?php if (userCan($user, 'utilisateurs.create') && (int) $listedUser['id'] !== (int) $user['utilisateur_id']): ?>
                                <form method="post" class="mt-1">
                                    <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="user_id" value="<?= (int) $listedUser['id'] ?>">
                                    <input type="hidden" name="new_status" value="<?= $isActive ? 'Désactivé' : 'Disponible' ?>">
                                    <button class="text-xs font-medium text-slate-600 underline decoration-slate-300 underline-offset-2 hover:text-green-800"><?= $isActive ? 'Désactiver' : 'Réactiver' ?></button>
                                </form>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-slate-600"><?= $listedUser['last_login'] ? $escape(date('d/m/Y H:i', strtotime($listedUser['last_login']))) : 'Jamais' ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <nav class="flex items-center justify-between" aria-label="Pagination">
            <span class="text-sm text-slate-600">Page <?= $page ?> sur <?= $totalPages ?></span>
            <div class="flex gap-2">
                <?php foreach (['Précédent' => max(1, $page - 1), 'Suivant' => min($totalPages, $page + 1)] as $label => $targetPage): ?>
                    <a href="<?= $escape(app_url('router.php') . '?' . http_build_query(['route' => $role . '/utilisateurs/liste', 'q' => $search, 'page' => $targetPage])) ?>" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-50"><?= $label ?></a>
                <?php endforeach; ?>
            </div>
        </nav>
    <?php endif; ?>
</section>