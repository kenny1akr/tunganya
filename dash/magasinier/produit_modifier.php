<?php
if (!defined('ROUTER_LOADED')) { http_response_code(403); exit('Accès direct interdit.'); }
if (!userCan($user, 'stock.edit')) { http_response_code(403); echo '<div class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</div>'; return; }
require_once __DIR__ . '/../../api/database.php';
require_once __DIR__ . '/_inventory.php';
$db = Database::getInstance();
$role = $user['role_system'];
$productId = (int) ($_GET['id'] ?? $_POST['product_id'] ?? 0);
$errors = [];
$flash = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);
inventoryEnsureCsrf();
$product = $productId > 0 ? $db->fetch(
    'SELECT s.*, c.category AS linked_category FROM stock s LEFT JOIN cat_prod c ON c.id = s.category_id WHERE s.id = ?',
    [$productId]
) : null;
if (!$product) {
    http_response_code(404);
    echo '<div class="rounded-md border border-amber-200 bg-amber-50 p-4 text-amber-900">Produit introuvable.</div>';
    return;
}

$values = [
    'medicine_name' => (string) $product['medicine_name'],
    'bar_code' => (string) $product['bar_code'],
    'category_id' => (string) ($product['category_id'] ?? ''),
    'shelf' => (string) ($product['shelf'] ?? ''),
    'sell_type' => (string) $product['sell_type'],
    'selling_price' => (string) $product['selling_price'],
    'seuil' => (string) $product['seuil'],
    'expire_date' => (string) ($product['expire_date'] ?? ''),
];
$archiveReason = '';
$units = $db->fetchAll('SELECT category FROM sell_type WHERE status = 1 ORDER BY category');
$categories = $db->fetchAll(
    'SELECT id, category FROM cat_prod WHERE status = 1 OR id = ? OR category = ? ORDER BY category',
    [(int) ($product['category_id'] ?? 0), (string) $product['category']]
);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], (string) ($_POST['csrf_token'] ?? ''))) {
        $errors[] = 'Le formulaire a expiré. Rechargez puis réessayez.';
    } else {
        $action = (string) ($_POST['action'] ?? 'save');
        if ($action === 'archive' || $action === 'restore') {
            $archiveReason = trim((string) ($_POST['archive_reason'] ?? ''));
            try {
                if ($action === 'archive') {
                    if ($archiveReason === '' || mb_strlen($archiveReason) > 255) throw new RuntimeException('Indiquez un motif d’archivage (255 caractères maximum).');
                    $pdo = $db->getPdo();
                    $pdo->beginTransaction();
                    $locked = $db->query('SELECT actual_quantity, is_archived FROM stock WHERE id = ? FOR UPDATE', [$productId])->fetch();
                    if (!$locked || (int) $locked['is_archived'] === 1) throw new RuntimeException('Ce produit est introuvable ou déjà archivé.');
                    if ((float) $locked['actual_quantity'] > 0) throw new RuntimeException('Un produit avec un stock disponible ne peut pas être archivé. Écoulez ou réconciliez le stock avant de continuer.');
                    $db->execute('UPDATE stock SET is_archived = 1, archived_at = NOW(), archived_by = ?, archive_reason = ? WHERE id = ?', [mb_substr($user['nom_complet'], 0, 100), $archiveReason, $productId]);
                    $pdo->commit();
                    $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Le produit a été archivé. Son historique est conservé.'];
                } else {
                    $db->execute('UPDATE stock SET is_archived = 0, archived_at = NULL, archived_by = NULL, archive_reason = NULL WHERE id = ? AND is_archived = 1', [$productId]);
                    $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Le produit a été réactivé.'];
                }
                header('Location: ' . app_route_url('stock/modifier') . '?id=' . $productId);
                exit;
            } catch (Throwable $error) {
                if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
                $errors[] = $error instanceof RuntimeException ? $error->getMessage() : 'Le statut du produit n’a pas pu être modifié.';
            }
        } else {
            foreach ($values as $field => $default) $values[$field] = trim((string) ($_POST[$field] ?? $default));
            if ($values['medicine_name'] === '' || mb_strlen($values['medicine_name']) > 100) $errors[] = 'Le nom est obligatoire (100 caractères maximum).';
            if (!ctype_digit($values['bar_code']) || (int) $values['bar_code'] < 0) $errors[] = 'Le code-barres doit être un entier positif ou nul.';
            if (mb_strlen($values['shelf']) > 255) $errors[] = 'L’emplacement ne peut pas dépasser 255 caractères.';
            $categoryId = (int) $values['category_id'];
            if (!$db->fetch('SELECT id FROM cat_prod WHERE id = ?', [$categoryId])) $errors[] = 'Choisissez une catégorie existante.';
            if (!in_array($values['sell_type'], array_column($units, 'category'), true)) $errors[] = 'Choisissez une unité active.';
            foreach (['selling_price' => 'prix de vente', 'seuil' => 'seuil d’alerte'] as $field => $label) {
                if (!is_numeric($values[$field]) || !is_finite((float) $values[$field]) || (float) $values[$field] < 0 || (float) $values[$field] > 999999999 || floor((float) $values[$field]) !== (float) $values[$field]) $errors[] = 'Le ' . $label . ' doit être un entier positif ou nul.';
            }
            if ($values['expire_date'] !== '' && !inventoryValidDate($values['expire_date'])) $errors[] = 'La date d’expiration est invalide.';

            if (!$errors) {
                try {
                    $category = $db->fetch('SELECT category FROM cat_prod WHERE id = ?', [$categoryId]);
                    $db->getPdo()->beginTransaction();
                    $db->execute(
                        'UPDATE stock SET medicine_name = ?, bar_code = ?, category = ?, category_id = ?, shelf = ?, sell_type = ?, selling_price = ?, profit_price = ? - actual_price, seuil = ?, expire_date = ? WHERE id = ?',
                        [$values['medicine_name'], (int) $values['bar_code'], $category['category'], $categoryId, $values['shelf'] ?: null, $values['sell_type'], (int) $values['selling_price'], (int) $values['selling_price'], (int) $values['seuil'], $values['expire_date'] ?: null, $productId]
                    );
                    $db->getPdo()->commit();
                    $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'La fiche produit a été mise à jour. Le coût moyen reste géré par les réceptions.'];
                    header('Location: ' . app_route_url('stock/modifier') . '?id=' . $productId);
                    exit;
                } catch (Throwable $error) {
                    if ($db->getPdo()->inTransaction()) $db->getPdo()->rollBack();
                    error_log('Stock product update failed: ' . $error->getMessage());
                    $errors[] = 'La fiche produit n’a pas pu être enregistrée. Vérifiez les champs puis réessayez.';
                }
            }
        }
    }
}

$product = $db->fetch('SELECT s.*, c.category AS linked_category FROM stock s LEFT JOIN cat_prod c ON c.id = s.category_id WHERE s.id = ?', [$productId]);
if ($values['category_id'] === '') {
    foreach ($categories as $categoryOption) {
        if (strcasecmp(trim($categoryOption['category']), trim((string) $product['category'])) === 0) {
            $values['category_id'] = (string) $categoryOption['id'];
            break;
        }
    }
}
?>
<section class="mx-auto max-w-5xl space-y-5">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div><a href="<?= inventoryEscape(app_route_url('stock')) ?>" class="text-sm font-medium text-green-800 hover:underline">← Catalogue des produits</a><p class="mt-3 text-sm font-semibold text-blue-800">Fiche produit</p><h1 class="mt-1 text-2xl font-bold text-slate-900"><?= inventoryEscape($product['medicine_name']) ?></h1><p class="mt-1 text-sm text-slate-600">Modifiez les informations du catalogue. Les quantités et le coût moyen restent pilotés par les mouvements de stock.</p></div>
        <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold <?= (int) $product['is_archived'] === 1 ? 'bg-slate-100 text-slate-700' : ((float) $product['actual_quantity'] > 0 ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800') ?>"><?= (int) $product['is_archived'] === 1 ? 'Archivé' : ((float) $product['actual_quantity'] > 0 ? 'Actif · disponible' : 'Actif · rupture') ?></span>
    </header>
    <?php if ($flash): ?><div role="status" class="rounded-md border border-green-200 bg-green-50 p-4 text-sm text-green-800"><?= inventoryEscape($flash['text'] ?? '') ?></div><?php endif; ?>
    <?php if ($errors): ?><div role="alert" class="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800"><ul class="list-disc space-y-1 pl-5"><?php foreach (array_unique($errors) as $error): ?><li><?= inventoryEscape($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

    <div class="grid gap-4 sm:grid-cols-3"><article class="rounded-md border border-slate-200 bg-white p-4"><p class="text-sm text-slate-500">Stock disponible</p><p class="mt-1 text-xl font-bold text-slate-900"><?= number_format((float) $product['actual_quantity'], 2, ',', ' ') ?> <?= inventoryEscape($product['sell_type']) ?></p></article><article class="rounded-md border border-slate-200 bg-white p-4"><p class="text-sm text-slate-500">Coût moyen unitaire</p><p class="mt-1 text-xl font-bold text-slate-900"><?= number_format((float) $product['actual_price'], 0, ',', ' ') ?> FBu</p><p class="mt-1 text-xs text-slate-500">Recalculé à chaque réception.</p></article><article class="rounded-md border border-slate-200 bg-white p-4"><p class="text-sm text-slate-500">Valeur au coût moyen</p><p class="mt-1 text-xl font-bold text-slate-900"><?= number_format((float) $product['actual_quantity'] * (float) $product['actual_price'], 0, ',', ' ') ?> FBu</p></article></div>

    <form method="post" class="space-y-5 rounded-md border border-slate-200 bg-white p-5 sm:p-6">
        <input type="hidden" name="csrf_token" value="<?= inventoryEscape($_SESSION['csrf_token']) ?>"><input type="hidden" name="product_id" value="<?= $productId ?>"><input type="hidden" name="action" value="save">
        <div class="grid gap-4 sm:grid-cols-2">
            <label class="space-y-1 text-sm font-medium text-slate-700 sm:col-span-2">Nom du produit <span class="text-red-600">*</span><input name="medicine_name" required maxlength="100" value="<?= inventoryEscape($values['medicine_name']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label>
            <label class="space-y-1 text-sm font-medium text-slate-700">Catégorie <select name="category_id" required class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 font-normal"><?php foreach ($categories as $item): ?><option value="<?= (int) $item['id'] ?>" <?= $values['category_id'] === (string) $item['id'] ? 'selected' : '' ?>><?= inventoryEscape($item['category']) ?></option><?php endforeach; ?></select></label>
            <label class="space-y-1 text-sm font-medium text-slate-700">Unité de gestion <select name="sell_type" required class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 font-normal"><?php foreach ($units as $unit): ?><option value="<?= inventoryEscape($unit['category']) ?>" <?= $values['sell_type'] === $unit['category'] ? 'selected' : '' ?>><?= inventoryEscape($unit['category']) ?></option><?php endforeach; ?></select></label>
            <label class="space-y-1 text-sm font-medium text-slate-700">Code-barres<input name="bar_code" type="number" min="0" step="1" required value="<?= inventoryEscape($values['bar_code']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label>
            <label class="space-y-1 text-sm font-medium text-slate-700">Emplacement<input name="shelf" maxlength="255" value="<?= inventoryEscape($values['shelf']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label>
            <label class="space-y-1 text-sm font-medium text-slate-700">Prix de vente (FBu)<input name="selling_price" type="number" min="0" step="1" required value="<?= inventoryEscape($values['selling_price']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label>
            <label class="space-y-1 text-sm font-medium text-slate-700">Seuil minimum d’alerte<input name="seuil" type="number" min="0" step="1" required value="<?= inventoryEscape($values['seuil']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"><span class="block text-xs font-normal text-slate-500">Une alerte apparaît lorsque le stock disponible atteint ce seuil.</span></label>
            <label class="space-y-1 text-sm font-medium text-slate-700">Date d’expiration<input name="expire_date" type="date" value="<?= inventoryEscape($values['expire_date']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label>
        </div>
        <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:justify-between"><a href="<?= inventoryEscape(app_route_url('stock')) ?>" class="rounded-md border border-slate-300 px-4 py-2.5 text-center text-sm font-medium text-slate-700">Retour au catalogue</a><button class="rounded-md bg-green-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-800">Enregistrer les modifications</button></div>
    </form>

    <section class="rounded-md border border-slate-200 bg-white p-5"><div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"><div><h2 class="font-semibold text-slate-900">Statut du produit</h2><p class="mt-1 max-w-2xl text-sm text-slate-600">L’archivage retire le produit des nouveaux choix de stock et de vente, sans supprimer ses mouvements ni ses factures. Un produit doit être à zéro pour être archivé.</p><?php if ((int) $product['is_archived'] === 1): ?><p class="mt-2 text-xs text-slate-500">Archivé le <?= inventoryEscape($product['archived_at'] ?? '') ?> par <?= inventoryEscape($product['archived_by'] ?? '') ?> · <?= inventoryEscape($product['archive_reason'] ?? '') ?></p><?php endif; ?></div>
        <?php if ((int) $product['is_archived'] === 1): ?><form method="post"><input type="hidden" name="csrf_token" value="<?= inventoryEscape($_SESSION['csrf_token']) ?>"><input type="hidden" name="product_id" value="<?= $productId ?>"><input type="hidden" name="action" value="restore"><button class="rounded-md border border-green-200 bg-green-50 px-4 py-2.5 text-sm font-semibold text-green-800 hover:bg-green-100">Réactiver le produit</button></form>
        <?php else: ?><form method="post" class="flex w-full flex-col gap-2 sm:max-w-xl sm:flex-row sm:items-end"><input type="hidden" name="csrf_token" value="<?= inventoryEscape($_SESSION['csrf_token']) ?>"><input type="hidden" name="product_id" value="<?= $productId ?>"><input type="hidden" name="action" value="archive"><label class="flex-1 space-y-1 text-sm font-medium text-slate-700">Motif d’archivage<input name="archive_reason" required maxlength="255" placeholder="Produit remplacé, référence obsolète…" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label><button class="rounded-md border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-800 hover:bg-rose-100" <?= (float) $product['actual_quantity'] > 0 ? 'disabled title="Le stock doit être épuisé avant l’archivage."' : '' ?>>Archiver</button></form><?php endif; ?></div></section>
</section>
