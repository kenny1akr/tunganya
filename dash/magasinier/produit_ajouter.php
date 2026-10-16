<?php
if (!defined('ROUTER_LOADED')) { http_response_code(403); exit('Accès direct interdit.'); }
if (!userCan($user, 'stock.create')) { http_response_code(403); echo '<div class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</div>'; return; }
require_once __DIR__ . '/../../api/database.php';
require_once __DIR__ . '/_inventory.php';
$db = Database::getInstance();
$role = $user['role_system'];
$errors = [];
$values = ['medicine_name'=>'','bar_code'=>'0','category'=>'','shelf'=>'','sell_type'=>'','quantity'=>'0','actual_price'=>'0','selling_price'=>'0','seuil'=>'3','expire_date'=>''];
$hasCategoryIcon = inventoryCategoryIconColumnExists($db);
$categoryOptions = $db->fetchAll('SELECT c.id AS category_id, c.category, ' . ($hasCategoryIcon ? 'c.icon' : "'tag' AS icon") . ', u.code AS unit_code, u.label AS unit_label, u.symbol AS unit_symbol FROM cat_prod c LEFT JOIN units u ON u.id = c.default_unit_id WHERE c.status = 1 ORDER BY c.category');
$categories = array_column($categoryOptions, 'category');
$units = array_column($db->fetchAll('SELECT category FROM sell_type WHERE status = 1 ORDER BY category'), 'category');
if (!$units) $units = ['piece', 'm', 'rouleau', 'Forfait'];
inventoryEnsureCsrf();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    foreach ($values as $field => $default) $values[$field] = trim((string) ($_POST[$field] ?? $default));
    if (!hash_equals($_SESSION['csrf_token'], (string) ($_POST['csrf_token'] ?? ''))) $errors[] = 'Le formulaire a expiré. Rechargez la page puis réessayez.';
    if ($values['medicine_name'] === '' || mb_strlen($values['medicine_name']) > 100) $errors[] = 'Le nom du produit est obligatoire (100 caractères maximum).';
    if (!in_array($values['category'], $categories, true)) $errors[] = 'Sélectionnez une catégorie valide.';
    if (!in_array($values['sell_type'], $units, true)) $errors[] = 'Sélectionnez une unité valide.';
    if (!ctype_digit($values['bar_code']) || (int) $values['bar_code'] < 0) $errors[] = 'Le code-barres doit être un entier positif.';
    foreach (['quantity'=>'stock initial', 'actual_price'=>'coût unitaire', 'selling_price'=>'prix de vente', 'seuil'=>'seuil d’alerte'] as $field=>$label) {
        if (!is_numeric($values[$field]) || !is_finite((float)$values[$field]) || (float)$values[$field] < 0) $errors[] = 'Le ' . $label . ' doit être un nombre positif ou nul.';
    }
    if (is_numeric($values['quantity']) && round((float)$values['quantity'], 1) !== (float)$values['quantity']) $errors[] = 'Le stock initial accepte au maximum une décimale.';
    if ((float)$values['quantity'] > 99999999.99 || (float)$values['actual_price'] > 999999999 || (float)$values['selling_price'] > 999999999) $errors[] = 'Une valeur dépasse la capacité autorisée.';
    if ($values['expire_date'] !== '' && !inventoryValidDate($values['expire_date'])) $errors[] = 'La date d’expiration est invalide.';

    if (!$errors) {
        $quantity = round((float)$values['quantity'], 2);
        $cost = (float)$values['actual_price'];
        $selling = (float)$values['selling_price'];
        $db->getPdo()->beginTransaction();
        try {
            $db->execute(
                "INSERT INTO stock (bar_code, medicine_name, category, category_id, shelf, registered_quantity, used_quantity, actual_quantity, register_date, expire_date, sell_type, origin, actual_price, selling_price, profit_price, status, is_archived, inventory, seuil)
                 VALUES (?, ?, ?, ?, ?, ?, 0, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, 0, ?, ?)",
                [(int)$values['bar_code'], $values['medicine_name'], $values['category'], (int) array_column($categoryOptions, 'category_id', 'category')[$values['category']], $values['shelf'], $quantity, $quantity, $values['expire_date'] ?: null, $values['sell_type'], 'Ajout manuel', $cost, $selling, $selling-$cost, $quantity > 0 ? 'Disponible' : 'Rupture', $quantity, (float)$values['seuil']]
            );
            $productId = (int)$db->getPdo()->lastInsertId();
            if ($quantity > 0) {
                $db->execute(
                    "INSERT INTO entries (operator, invoice_in, bar_code, medicine_name, category, quantity, expire_date, sell_type, actual_price, total_amount)
                     VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [mb_substr($user['nom_utilisateur'], 0, 25), (int)$values['bar_code'], $values['medicine_name'], $values['category'], round($quantity, 1), $values['expire_date'] ?: null, $values['sell_type'], $cost, $quantity*$cost]
                );
                inventoryLogMovement($db, ['id'=>$productId, 'medicine_name'=>$values['medicine_name']], 'entree', $quantity, 0, $quantity, $cost, null, 'Stock initial à la création du produit', $user['nom_complet']);
            }
            $db->getPdo()->commit();
            $_SESSION['flash_message'] = ['type'=>'success', 'text'=>'Le produit « ' . $values['medicine_name'] . ' » a été créé.'];
            header('Location: ' . app_route_url('stock'));
            exit;
        } catch (Throwable $error) {
            if ($db->getPdo()->inTransaction()) $db->getPdo()->rollBack();
            error_log('Product creation failed: ' . $error->getMessage());
            $errors[] = 'Le produit n’a pas pu être créé. Vérifiez les données puis réessayez.';
        }
    }
}
?>
<section class="mx-auto max-w-4xl space-y-5">
    <header><a href="<?= inventoryEscape(app_route_url('stock')) ?>" class="inline-flex items-center gap-2 text-sm font-medium text-green-800 hover:underline"><i data-feather="arrow-left" class="h-4 w-4"></i> État du stock</a><div class="mt-3"><p class="text-sm font-semibold text-blue-800">Catalogue</p><h1 class="mt-1 text-2xl font-bold text-slate-900">Ajouter un produit</h1><p class="mt-1 text-sm text-slate-600">La quantité initiale est ajoutée au stock et journalisée comme entrée.</p></div></header>
    <?php if (!$categories): ?><div role="alert" class="rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">Aucune catégorie active. <a class="font-semibold underline" href="<?= inventoryEscape(app_route_url('stock/categories')) ?>">Créer une catégorie</a> avant d’ajouter un article.</div><?php endif; ?>
    <?php if ($errors): ?><div role="alert" class="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800"><ul class="list-disc space-y-1 pl-5"><?php foreach (array_unique($errors) as $error): ?><li><?= inventoryEscape($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form method="post" class="space-y-5 rounded-md border border-slate-200 bg-white p-5 sm:p-6"><input type="hidden" name="csrf_token" value="<?= inventoryEscape($_SESSION['csrf_token']) ?>">
        <div class="grid gap-4 sm:grid-cols-2">
            <label class="space-y-1 text-sm font-medium text-slate-700 sm:col-span-2">Nom du produit <span class="text-red-600">*</span><input name="medicine_name" maxlength="100" required value="<?= inventoryEscape($values['medicine_name']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label>
            <label class="space-y-1 text-sm font-medium text-slate-700">Catégorie <select name="category" id="productCategory" required class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 font-normal"><option value="">Choisir</option><?php foreach ($categoryOptions as $option): $defaultUnits = array_values(array_filter([$option['unit_label'], $option['unit_symbol'], $option['unit_code']])); ?><option value="<?= inventoryEscape($option['category']) ?>" data-default-units="<?= inventoryEscape(json_encode($defaultUnits, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>" <?= $values['category']===$option['category']?'selected':'' ?>><?= ['printer'=>'&#x25A3;','image'=>'&#x25A7;','roll'=>'&#x25A4;','tool'=>'&#x2699;','ink'=>'&#x25C9;','truck'=>'&#x25B0;','box'=>'&#x25A1;','tag'=>'&#x25C6;'][$option['icon'] ?? 'tag'] ?? '&#x25C6;' ?> <?= inventoryEscape($option['category']) ?></option><?php endforeach; ?></select></label>
            <label class="space-y-1 text-sm font-medium text-slate-700">Unité <select name="sell_type" required class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 font-normal"><option value="">Choisir</option><?php foreach ($units as $option): ?><option value="<?= inventoryEscape($option) ?>" <?= $values['sell_type']===$option?'selected':'' ?>><?= inventoryEscape($option) ?></option><?php endforeach; ?></select></label>
            <label class="space-y-1 text-sm font-medium text-slate-700">Code-barres<input name="bar_code" type="number" min="0" step="1" value="<?= inventoryEscape($values['bar_code']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label>
            <label class="space-y-1 text-sm font-medium text-slate-700">Emplacement<input name="shelf" maxlength="255" value="<?= inventoryEscape($values['shelf']) ?>" placeholder="Rayon, étagère…" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label>
            <label class="space-y-1 text-sm font-medium text-slate-700">Quantité initiale<input name="quantity" type="number" min="0" step="0.1" required value="<?= inventoryEscape($values['quantity']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label>
            <label class="space-y-1 text-sm font-medium text-slate-700">Seuil d’alerte<input name="seuil" type="number" min="0" step="1" required value="<?= inventoryEscape($values['seuil']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label>
            <label class="space-y-1 text-sm font-medium text-slate-700">Coût unitaire (FBu)<input name="actual_price" type="number" min="0" step="1" required value="<?= inventoryEscape($values['actual_price']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label>
            <label class="space-y-1 text-sm font-medium text-slate-700">Prix de vente (FBu)<input name="selling_price" type="number" min="0" step="1" required value="<?= inventoryEscape($values['selling_price']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label>
            <label class="space-y-1 text-sm font-medium text-slate-700">Date d’expiration<input name="expire_date" type="date" value="<?= inventoryEscape($values['expire_date']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label>
        </div>
        <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:justify-end"><a href="<?= inventoryEscape(app_route_url('stock')) ?>" class="rounded-md border border-slate-300 px-4 py-2.5 text-center text-sm font-medium text-slate-700">Annuler</a><button class="inline-flex items-center justify-center gap-2 rounded-md bg-green-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-800" <?= !$categories ? 'disabled' : '' ?>><i data-feather="save" class="h-4 w-4"></i> Créer le produit</button></div>
    </form>
</section>
<script>(()=>{const category=document.getElementById('productCategory'),unit=document.querySelector('select[name="sell_type"]');if(!category||!unit)return;const applyDefault=()=>{const candidates=JSON.parse(category.selectedOptions[0]?.dataset.defaultUnits||'[]').map(value=>value.trim().toLocaleLowerCase());if(!candidates.length)return;const match=[...unit.options].find(option=>candidates.includes(option.value.trim().toLocaleLowerCase()));if(match)unit.value=match.value;};category.addEventListener('change',applyDefault);if(!unit.value)applyDefault();})();</script>
