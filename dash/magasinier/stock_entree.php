<?php
if (!defined('ROUTER_LOADED')) { http_response_code(403); exit('Accès direct interdit.'); }
if (!userCan($user, 'flux.entrees')) { http_response_code(403); echo '<div class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</div>'; return; }
require_once __DIR__ . '/../../api/database.php';
require_once __DIR__ . '/_inventory.php';
$db = Database::getInstance();
$products = $db->fetchAll('SELECT id, medicine_name, actual_quantity, actual_price, bar_code, category, sell_type FROM stock ORDER BY medicine_name');
$suppliers = $db->fetchAll("SELECT id, name FROM partners WHERE category = 'fournisseur' ORDER BY name");
$productIds = array_values((array) ($_POST['product_id'] ?? [$_GET['product_id'] ?? '']));
$quantities = array_values((array) ($_POST['quantity'] ?? ['1']));
$costs = array_values((array) ($_POST['actual_price'] ?? ['0']));
$expiries = array_values((array) ($_POST['expire_date'] ?? ['']));
$values = ['entry_type'=>'Réception fournisseur', 'received_date'=>date('Y-m-d'), 'supplier_id'=>'', 'invoice_in'=>'', 'reason'=>'Réception fournisseur'];
$errors = [];
inventoryEnsureCsrf();
if (empty($_SESSION['stock_entry_token'])) $_SESSION['stock_entry_token'] = bin2hex(random_bytes(32));
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    foreach ($values as $field=>$default) $values[$field] = trim((string)($_POST[$field] ?? $default));
    $productIds = array_values((array) ($_POST['product_id'] ?? []));
    $quantities = array_values((array) ($_POST['quantity'] ?? []));
    $costs = array_values((array) ($_POST['actual_price'] ?? []));
    $expiries = array_values((array) ($_POST['expire_date'] ?? []));
    if (!hash_equals($_SESSION['csrf_token'], (string)($_POST['csrf_token'] ?? ''))) $errors[] = 'Le formulaire a expiré. Rechargez puis réessayez.';
    if (!hash_equals($_SESSION['stock_entry_token'], (string)($_POST['entry_token'] ?? ''))) $errors[] = 'Cette réception a déjà été traitée ou le formulaire a expiré. Rechargez la page.';
    if (!in_array($values['entry_type'], ['Réception fournisseur', 'Retour client', 'Autre entrée'], true)) $errors[] = 'Choisissez un type d’entrée valide.';
    if ($values['entry_type'] === 'Réception fournisseur' && $values['supplier_id'] === '') $errors[] = 'Sélectionnez le fournisseur de cette réception.';
    if ($values['supplier_id'] !== '' && !$db->fetch("SELECT id FROM partners WHERE id = ? AND category = 'fournisseur'", [(int)$values['supplier_id']])) $errors[] = 'Le fournisseur sélectionné est invalide.';
    if (!inventoryValidDate($values['received_date']) || $values['received_date'] > date('Y-m-d')) $errors[] = 'La date de réception est invalide.';
    if (mb_strlen($values['invoice_in']) > 100 || mb_strlen($values['reason']) > 200) $errors[] = 'La référence ou le motif est trop long.';
    $lineInputs = []; $rowCount = max(count($productIds), count($quantities), count($costs), count($expiries));
    if ($rowCount > 50) $errors[] = 'Une réception ne peut pas contenir plus de 50 articles.';
    for ($index = 0; $index < min($rowCount, 50); $index++) {
        $lineProductId = (int)($productIds[$index] ?? 0);
        $quantityText = str_replace(',', '.', trim((string)($quantities[$index] ?? '')));
        $costText = str_replace(',', '.', trim((string)($costs[$index] ?? '')));
        $expiry = trim((string)($expiries[$index] ?? ''));
        if ($lineProductId <= 0 && $quantityText === '' && $costText === '') continue;
        if ($lineProductId <= 0 || !is_numeric($quantityText) || !is_finite((float)$quantityText) || (float)$quantityText <= 0 || !is_numeric($costText) || !is_finite((float)$costText) || (float)$costText < 0 || (float)$costText > 999999999) {
            $errors[] = 'Chaque ligne doit avoir un article, une quantité positive et un coût valide.';
            continue;
        }
        if ($expiry !== '' && !inventoryValidDate($expiry)) $errors[] = 'Une date d’expiration est invalide.';
        $lineInputs[] = ['id'=>$lineProductId, 'quantity'=>round((float)$quantityText, 2), 'cost'=>(float)$costText, 'expiry'=>$expiry];
    }
    if (!$lineInputs) $errors[] = 'Ajoutez au moins un article à la réception.';
    if (!$errors) {
        $pdo = $db->getPdo();
        try {
            $pdo->beginTransaction();
            $idsToLock = array_values(array_unique(array_map('intval', array_column($lineInputs, 'id'))));
            sort($idsToLock, SORT_NUMERIC);
            $productsById = [];
            foreach ($idsToLock as $idToLock) {
                $product = $db->query('SELECT * FROM stock WHERE id = ? FOR UPDATE', [$idToLock])->fetch();
                if (!$product) throw new RuntimeException('Un des articles sélectionnés est introuvable.');
                $productsById[(int)$idToLock] = $product;
            }
            $entryNumber = 'REC-' . str_replace('-', '', $values['received_date']) . '-' . strtoupper(bin2hex(random_bytes(4)));
            $supplierId = $values['supplier_id'] === '' ? null : (int)$values['supplier_id'];
            $supplierName = '';
            if ($supplierId) $supplierName = (string)$db->fetchColumn('SELECT name FROM partners WHERE id = ?', [$supplierId]);
            $reason = $values['entry_type'] . '; ' . $values['reason'] . ($supplierName !== '' ? '; Fournisseur: ' . $supplierName : '');
            foreach ($lineInputs as $line) {
                $product = $productsById[$line['id']];
                $quantity = $line['quantity']; $unitCost = $line['cost'];
                $before = (float)$product['actual_quantity']; $after = round($before + $quantity, 2);
                $newAverage = round(($before * (float)$product['actual_price'] + $quantity * $unitCost) / $after);
                $expiry = $line['expiry'] ?: ($product['expire_date'] ?: null);
                $db->execute("UPDATE stock SET registered_quantity = registered_quantity + ?, actual_quantity = ?, actual_price = ?, profit_price = selling_price - ?, expire_date = ?, status = 'Disponible' WHERE id = ?", [$quantity, $after, $newAverage, $newAverage, $expiry, $line['id']]);
                $db->execute('INSERT INTO entries (entry_number, product_id, supplier_id, entry_type, operator, received_by, invoice_in, bar_code, medicine_name, category, quantity, register_date, expire_date, sell_type, actual_price, total_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [$entryNumber, $line['id'], $supplierId, $values['entry_type'], mb_substr($user['nom_utilisateur'], 0, 25), mb_substr($user['nom_complet'], 0, 100), $values['invoice_in'] ?: null, (int)$product['bar_code'], $product['medicine_name'], $product['category'], $quantity, $values['received_date'], $expiry, $product['sell_type'], $unitCost, $quantity * $unitCost, 'Disponible']);
                inventoryLogMovement($db, $product, 'entree', $quantity, $before, $after, $unitCost, $entryNumber, $reason . ($values['invoice_in'] !== '' ? '; BL: ' . $values['invoice_in'] : ''), $user['nom_complet']);
                $productsById[$line['id']]['actual_quantity'] = $after;
                $productsById[$line['id']]['actual_price'] = $newAverage;
            }
            $pdo->commit();
            $_SESSION['stock_entry_token'] = bin2hex(random_bytes(32));
            $_SESSION['flash_message']=['type'=>'success','text'=>'Réception '.$entryNumber.' validée pour '.count($lineInputs).' ligne(s).'];
            header('Location: '.app_route_url('stock/mouvements')); exit;
        } catch (Throwable $error) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('Stock entry failed: '.$error->getMessage());
            $errors[]=$error instanceof RuntimeException ? $error->getMessage() : 'L’entrée n’a pas pu être enregistrée.';
        }
    }
}
?>
<section class="mx-auto max-w-5xl space-y-5"><header><a href="<?= inventoryEscape(app_route_url('stock')) ?>" class="inline-flex items-center gap-2 text-sm font-medium text-green-800"><i data-feather="arrow-left" class="h-4 w-4"></i> État du stock</a><div class="mt-3"><p class="text-sm font-semibold text-green-800">Réception</p><h1 class="mt-1 text-2xl font-bold text-slate-900">Entrée de stock</h1><p class="mt-1 text-sm text-slate-600">La validation augmente le stock et recalcule le coût moyen dans une transaction unique.</p></div></header>
<?php if ($errors): ?><div role="alert" class="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800"><ul class="list-disc pl-5"><?php foreach (array_unique($errors) as $error): ?><li><?= inventoryEscape($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php if (!$products): ?><div class="rounded-md border border-amber-200 bg-amber-50 p-4 text-amber-900">Aucun produit au catalogue. Créez d’abord un produit.</div><?php endif; ?>
<form method="post" class="space-y-5 rounded-md border border-slate-200 bg-white p-5"><input type="hidden" name="csrf_token" value="<?= inventoryEscape($_SESSION['csrf_token']) ?>"><input type="hidden" name="entry_token" value="<?= inventoryEscape($_SESSION['stock_entry_token']) ?>">
<div class="grid gap-4 border-b border-slate-100 pb-5 sm:grid-cols-2"><label class="space-y-1 text-sm font-medium">Type d’entrée<select name="entry_type" id="entryType" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 font-normal"><?php foreach(['Réception fournisseur','Retour client','Autre entrée'] as $entryType): ?><option value="<?= inventoryEscape($entryType) ?>" <?= $values['entry_type']===$entryType?'selected':'' ?>><?= inventoryEscape($entryType) ?></option><?php endforeach; ?></select></label><label class="space-y-1 text-sm font-medium">Date de réception<input name="received_date" type="date" required value="<?= inventoryEscape($values['received_date']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label><label id="supplierField" class="space-y-1 text-sm font-medium">Fournisseur<select name="supplier_id" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 font-normal"><option value="">Choisir un fournisseur</option><?php foreach($suppliers as $supplier): ?><option value="<?= (int)$supplier['id'] ?>" <?= $values['supplier_id']===(string)$supplier['id']?'selected':'' ?>><?= inventoryEscape($supplier['name']) ?></option><?php endforeach; ?></select></label><label class="space-y-1 text-sm font-medium">Référence du bon de livraison<input name="invoice_in" maxlength="100" value="<?= inventoryEscape($values['invoice_in']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label><label class="space-y-1 text-sm font-medium sm:col-span-2">Observations<textarea name="reason" maxlength="200" rows="2" required class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"><?= inventoryEscape($values['reason']) ?></textarea></label></div>
<fieldset class="space-y-3"><div class="flex items-center justify-between gap-3"><legend class="text-sm font-semibold">Articles reçus</legend><button type="button" id="addEntryLine" class="inline-flex items-center gap-2 rounded-md border border-slate-300 px-3 py-2 text-sm font-medium" <?= !$products?'disabled':'' ?>><i data-feather="plus" class="h-4 w-4"></i> Ajouter un article</button></div><div id="entryLines" class="space-y-3">
<?php foreach($productIds as $lineIndex=>$selectedId): ?><div class="entry-line grid gap-3 rounded-md border border-slate-200 p-3 sm:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_8rem_9rem_10rem_10rem_auto] xl:items-end"><label class="space-y-1 text-sm font-medium">Article<select name="product_id[]" required class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 font-normal"><option value="">Choisir un article</option><?php foreach($products as $product): ?><option value="<?= (int)$product['id'] ?>" <?= (string)$selectedId===(string)$product['id']?'selected':'' ?>><?= inventoryEscape($product['medicine_name']) ?> · <?= inventoryEscape($product['sell_type']) ?></option><?php endforeach; ?></select></label><label class="space-y-1 text-sm font-medium">Quantité reçue<input name="quantity[]" type="number" min="0.01" step="0.01" required value="<?= inventoryEscape($quantities[$lineIndex]??'1') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label><label class="space-y-1 text-sm font-medium">Coût unitaire<input name="actual_price[]" type="number" min="0" step="1" required value="<?= inventoryEscape($costs[$lineIndex]??'0') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label><label class="space-y-1 text-sm font-medium">Expiration<input name="expire_date[]" type="date" value="<?= inventoryEscape($expiries[$lineIndex]??'') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label><button type="button" class="remove-entry-line rounded-md border border-slate-300 p-2 text-rose-700" aria-label="Retirer l’article" title="Retirer l’article"><i data-feather="trash-2" class="h-4 w-4"></i></button></div><?php endforeach; ?>
</div></fieldset><div class="flex justify-end border-t pt-4"><button class="rounded-md bg-green-700 px-4 py-2.5 text-sm font-semibold text-white" <?= !$products?'disabled':'' ?>>Valider la réception</button></div></form></section>
<template id="entryLineTemplate"><div class="entry-line grid gap-3 rounded-md border border-slate-200 p-3 sm:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_8rem_9rem_10rem_10rem_auto] xl:items-end"><label class="space-y-1 text-sm font-medium">Article<select name="product_id[]" required class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 font-normal"><option value="">Choisir un article</option><?php foreach($products as $product): ?><option value="<?= (int)$product['id'] ?>"><?= inventoryEscape($product['medicine_name']) ?> · <?= inventoryEscape($product['sell_type']) ?></option><?php endforeach; ?></select></label><label class="space-y-1 text-sm font-medium">Quantité reçue<input name="quantity[]" type="number" min="0.01" step="0.01" required value="1" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label><label class="space-y-1 text-sm font-medium">Coût unitaire<input name="actual_price[]" type="number" min="0" step="1" required value="0" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label><label class="space-y-1 text-sm font-medium">Expiration<input name="expire_date[]" type="date" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label><button type="button" class="remove-entry-line rounded-md border border-slate-300 p-2 text-rose-700" aria-label="Retirer l’article" title="Retirer l’article"><i data-feather="trash-2" class="h-4 w-4"></i></button></div></template>
<script>(()=>{const lines=document.getElementById('entryLines');const add=document.getElementById('addEntryLine');const template=document.getElementById('entryLineTemplate');const type=document.getElementById('entryType');const supplier=document.querySelector('#supplierField select');function toggleSupplier(){supplier.required=type.value==='Réception fournisseur';}type.addEventListener('change',toggleSupplier);toggleSupplier();add.addEventListener('click',()=>{if(lines.children.length>=50)return;lines.append(template.content.cloneNode(true));if(window.feather)feather.replace();});lines.addEventListener('click',event=>{const button=event.target.closest('.remove-entry-line');if(!button)return;const rows=lines.querySelectorAll('.entry-line');if(rows.length>1)button.closest('.entry-line').remove();else{rows[0].querySelector('select').value='';rows[0].querySelectorAll('input').forEach(input=>input.value=input.name==='quantity[]'?'1':'0');}});})();</script>