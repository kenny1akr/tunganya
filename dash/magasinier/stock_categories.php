<?php
if (!defined('ROUTER_LOADED')) { http_response_code(403); exit('Accès direct interdit.'); }
if (!userCan($user, 'stock.categories')) { http_response_code(403); echo '<div class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</div>'; return; }
require_once __DIR__ . '/../../api/database.php';
require_once __DIR__ . '/_inventory.php';
$db = Database::getInstance();
$errors = [];
$flash = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);
$editId = (int) ($_GET['edit'] ?? 0);
$values = ['category' => '', 'description' => '', 'family' => '', 'default_unit_id' => ''];
$units = $db->fetchAll("SELECT id, code, label, symbol FROM units WHERE status = 1 ORDER BY label");
inventoryEnsureCsrf();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], (string) ($_POST['csrf_token'] ?? ''))) {
        $errors[] = 'Le formulaire a expiré. Rechargez puis réessayez.';
    } else {
        $action = (string) ($_POST['action'] ?? 'save');
        $categoryId = (int) ($_POST['category_id'] ?? 0);

        if ($action === 'toggle' && $categoryId > 0) {
            $db->execute('UPDATE cat_prod SET status = IF(status = 1, 0, 1) WHERE id = ?', [$categoryId]);
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Le statut de la catégorie a été mis à jour.'];
            header('Location: ' . app_route_url('stock/categories'));
            exit;
        }

        foreach ($values as $field => $default) {
            $values[$field] = trim((string) ($_POST[$field] ?? $default));
        }
        $editId = $categoryId;

        if ($values['category'] === '' || mb_strlen($values['category']) > 100) $errors[] = 'Le nom est obligatoire (100 caractères maximum).';
        if (mb_strlen($values['family']) > 100) $errors[] = 'La famille ne peut pas dépasser 100 caractères.';
        if (mb_strlen($values['description']) > 5000) $errors[] = 'La description ne peut pas dépasser 5 000 caractères.';
        $unitId = $values['default_unit_id'] === '' ? null : (int) $values['default_unit_id'];
        if ($values['default_unit_id'] !== '' && !$db->fetch('SELECT id FROM units WHERE id = ? AND status = 1', [$unitId])) $errors[] = 'Choisissez une unité active.';
        if ($editId > 0 && !$db->fetch('SELECT id FROM cat_prod WHERE id = ?', [$editId])) $errors[] = 'La catégorie à modifier est introuvable.';
        if ($db->fetch('SELECT id FROM cat_prod WHERE category = ? AND id <> ?', [$values['category'], $editId])) $errors[] = 'Une catégorie porte déjà ce nom.';

        if (!$errors) {
            try {
                if ($editId > 0) {
                    $pdo = $db->getPdo();
                    $pdo->beginTransaction();
                    $current = $db->query('SELECT category FROM cat_prod WHERE id = ? FOR UPDATE', [$editId])->fetch();
                    if (!$current) throw new RuntimeException('La catégorie à modifier est introuvable.');
                    if ($current['category'] !== $values['category']) {
                        $db->execute('UPDATE stock SET category = ? WHERE category = ?', [$values['category'], $current['category']]);
                    }
                    $db->execute('UPDATE cat_prod SET category = ?, description = ?, family = ?, default_unit_id = ? WHERE id = ?', [$values['category'], $values['description'] ?: null, $values['family'] ?: null, $unitId, $editId]);
                    $pdo->commit();
                    $message = 'La catégorie a été modifiée.';
                } else {
                    $db->execute('INSERT INTO cat_prod (category, description, family, default_unit_id, adder, cat_date, status) VALUES (?, ?, ?, ?, ?, CURDATE(), 1)', [$values['category'], $values['description'] ?: null, $values['family'] ?: null, $unitId, mb_substr($user['nom_utilisateur'], 0, 100)]);
                    $message = 'La catégorie a été créée.';
                }
            } catch (Throwable $error) {
                if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
                error_log('Stock category save failed: ' . $error->getMessage());
                $errors[] = $error instanceof RuntimeException ? $error->getMessage() : 'La catégorie n’a pas pu être enregistrée. Vérifiez son nom et les autres valeurs saisies.';
            }
            if (!$errors) {
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => $message];
                header('Location: ' . app_route_url('stock/categories'));
                exit;
            }
        }
    }
}

if ($editId > 0 && !$values['category']) {
    $category = $db->fetch('SELECT id, category, description, family, default_unit_id FROM cat_prod WHERE id = ?', [$editId]);
    if ($category) {
        foreach ($values as $field => $default) $values[$field] = (string) ($category[$field] ?? '');
    } else {
        $errors[] = 'La catégorie demandée est introuvable.';
    }
}

$search = trim((string) ($_GET['q'] ?? ''));
$statusFilter = (string) ($_GET['status'] ?? '1');
$conditions = [];
$params = [];
if ($search !== '') {
    $conditions[] = '(c.category LIKE ? OR c.family LIKE ?)';
    $term = '%' . $search . '%';
    array_push($params, $term, $term);
}
if (in_array($statusFilter, ['0', '1'], true)) {
    $conditions[] = 'c.status = ?';
    $params[] = (int) $statusFilter;
}
$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
$categories = $db->fetchAll(
    "SELECT c.id, c.category, c.description, c.family, c.status, c.cat_date, c.adder, u.label AS unit_label, u.symbol,
            (SELECT COUNT(*) FROM stock s WHERE s.category = c.category) AS product_count
     FROM cat_prod c LEFT JOIN units u ON u.id = c.default_unit_id $where
     ORDER BY c.status DESC, c.category",
    $params
);
?>
<section class="space-y-5">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-sm font-semibold text-green-800">Catalogue</p><h1 class="mt-1 text-2xl font-bold text-slate-900">Catégories de stock</h1><p class="mt-1 text-sm text-slate-600">Gérez les catégories disponibles dans le catalogue d’articles.</p></div>
        <a href="<?= inventoryEscape(app_route_url('stock/ajouter')) ?>" class="inline-flex items-center gap-2 rounded-md bg-green-700 px-4 py-2.5 text-sm font-semibold text-white"><i data-feather="plus" class="h-4 w-4"></i> Ajouter un article</a>
    </header>
    <?php if ($flash): ?><div role="status" class="rounded-md border border-green-200 bg-green-50 p-4 text-sm text-green-800"><?= inventoryEscape($flash['text'] ?? '') ?></div><?php endif; ?>
    <?php if ($errors): ?><div role="alert" class="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800"><ul class="list-disc pl-5"><?php foreach (array_unique($errors) as $error): ?><li><?= inventoryEscape($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <section class="rounded-md border border-slate-200 bg-white p-5">
        <h2 class="font-semibold text-slate-900"><?= $editId > 0 ? 'Modifier la catégorie' : 'Créer une catégorie' ?></h2>
        <form method="post" class="mt-4 grid gap-4 sm:grid-cols-2">
            <input type="hidden" name="csrf_token" value="<?= inventoryEscape($_SESSION['csrf_token']) ?>"><input type="hidden" name="category_id" value="<?= $editId ?>">
            <label class="space-y-1 text-sm font-medium text-slate-700">Nom <span class="text-red-600">*</span><input name="category" required maxlength="100" value="<?= inventoryEscape($values['category']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label>
            <label class="space-y-1 text-sm font-medium text-slate-700">Famille<input name="family" maxlength="100" value="<?= inventoryEscape($values['family']) ?>" placeholder="Ex. Matières d’impression" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label>
            <label class="space-y-1 text-sm font-medium text-slate-700">Unité par défaut<select name="default_unit_id" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 font-normal"><option value="">Aucune</option><?php foreach ($units as $unit): ?><option value="<?= (int) $unit['id'] ?>" <?= $values['default_unit_id'] === (string) $unit['id'] ? 'selected' : '' ?>><?= inventoryEscape($unit['label']) ?><?= $unit['symbol'] ? ' (' . inventoryEscape($unit['symbol']) . ')' : '' ?></option><?php endforeach; ?></select></label>
            <label class="space-y-1 text-sm font-medium text-slate-700 sm:col-span-2">Description<textarea name="description" maxlength="5000" rows="3" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"><?= inventoryEscape($values['description']) ?></textarea></label>
            <div class="flex justify-end gap-2 sm:col-span-2"><button class="rounded-md bg-green-700 px-4 py-2.5 text-sm font-semibold text-white">Enregistrer</button></div>
        </form>
    </section>
    <form method="get" action="<?= inventoryEscape(app_url('router.php')) ?>" class="grid gap-3 rounded-md border border-slate-200 bg-white p-4 sm:grid-cols-[minmax(12rem,1fr)_12rem_auto_auto] sm:items-end">
        <input type="hidden" name="route" value="<?= inventoryEscape($role . '/stock/categories') ?>">
        <label class="space-y-1 text-sm font-medium">Recherche<input name="q" value="<?= inventoryEscape($search) ?>" placeholder="Nom, famille" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label>
        <label class="space-y-1 text-sm font-medium">Statut<select name="status" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 font-normal"><option value="1" <?= $statusFilter === '1' ? 'selected' : '' ?>>Actives</option><option value="0" <?= $statusFilter === '0' ? 'selected' : '' ?>>Inactives</option><option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>Toutes</option></select></label>
        <button class="rounded-md bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white">Filtrer</button><a href="<?= inventoryEscape(app_route_url('stock/categories')) ?>" class="rounded-md border border-slate-300 px-4 py-2.5 text-center text-sm font-medium text-slate-700">Effacer</a>
    </form>
    <div class="overflow-x-auto rounded-md border border-slate-200 bg-white"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-4 py-3">Catégorie</th><th class="px-4 py-3">Famille / unité</th><th class="px-4 py-3 text-right">Articles</th><th class="px-4 py-3">Statut</th><th class="px-4 py-3">Actions</th></tr></thead><tbody class="divide-y divide-slate-100">
        <?php if (!$categories): ?><tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">Aucune catégorie ne correspond aux filtres.</td></tr><?php else: foreach ($categories as $category): ?>
            <tr><td class="px-4 py-3"><div class="font-medium text-slate-900"><?= inventoryEscape($category['category']) ?></div><?php if ($category['description']): ?><div class="mt-1 max-w-md text-xs text-slate-500"><?= inventoryEscape($category['description']) ?></div><?php endif; ?></td><td class="px-4 py-3 text-slate-600"><?= inventoryEscape($category['family'] ?: '—') ?> · <?= inventoryEscape($category['unit_label'] ?: 'Unité non définie') ?></td><td class="px-4 py-3 text-right"><?= number_format((int) $category['product_count'], 0, ',', ' ') ?></td><td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-medium <?= (int) $category['status'] === 1 ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-600' ?>"><?= (int) $category['status'] === 1 ? 'Active' : 'Inactive' ?></span></td><td class="px-4 py-3"><div class="flex items-center gap-3"><a href="<?= inventoryEscape(app_route_url('stock/categories')) ?>?edit=<?= (int) $category['id'] ?>" class="font-medium text-green-800 hover:underline">Modifier</a><form method="post" onsubmit="return confirm('<?= (int) $category['status'] === 1 ? 'Désactiver' : 'Réactiver' ?> cette catégorie ?')"><input type="hidden" name="csrf_token" value="<?= inventoryEscape($_SESSION['csrf_token']) ?>"><input type="hidden" name="category_id" value="<?= (int) $category['id'] ?>"><input type="hidden" name="action" value="toggle"><button class="font-medium text-slate-600 hover:text-slate-900"><?= (int) $category['status'] === 1 ? 'Désactiver' : 'Réactiver' ?></button></form></div></td></tr>
        <?php endforeach; endif; ?>
    </tbody></table></div>
</section>
