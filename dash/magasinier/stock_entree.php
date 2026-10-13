<?php
if (!defined('ROUTER_LOADED')) { http_response_code(403); exit('Accès direct interdit.'); }
if (!userCan($user, 'flux.entrees')) { http_response_code(403); echo '<div class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</div>'; return; }
require_once __DIR__ . '/../../api/database.php';
require_once __DIR__ . '/_inventory.php';
$db = Database::getInstance();
$products = $db->fetchAll('SELECT id, medicine_name, actual_quantity, actual_price, bar_code, category, sell_type FROM stock ORDER BY medicine_name');
$productId = (int)($_GET['product_id'] ?? $_POST['product_id'] ?? 0);
$values = ['quantity'=>'1', 'actual_price'=>'0', 'invoice_in'=>'', 'expire_date'=>'', 'reason'=>'Réception fournisseur'];
$errors = [];
inventoryEnsureCsrf();
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    foreach ($values as $field=>$default) $values[$field] = trim((string)($_POST[$field] ?? $default));
    $productId = (int)($_POST['product_id'] ?? 0);
    if (!hash_equals($_SESSION['csrf_token'], (string)($_POST['csrf_token'] ?? ''))) $errors[] = 'Le formulaire a expiré. Rechargez puis réessayez.';
    if (!is_numeric($values['quantity']) || !is_finite((float)$values['quantity']) || (float)$values['quantity'] <= 0 || round((float)$values['quantity'], 1) !== (float)$values['quantity']) $errors[] = 'La quantité doit être positive avec au plus une décimale.';
    if (!is_numeric($values['actual_price']) || (float)$values['actual_price'] < 0) $errors[] = 'Le coût unitaire doit être positif ou nul.';
    if (mb_strlen($values['invoice_in']) > 100 || mb_strlen($values['reason']) > 500) $errors[] = 'La référence ou le motif est trop long.';
    if ($values['expire_date'] !== '' && !inventoryValidDate($values['expire_date'])) $errors[] = 'La date d’expiration est invalide.';
    if (!$errors) {
        $pdo=$db->getPdo();
        try {
            $pdo->beginTransaction();
            $product=$db->query('SELECT * FROM stock WHERE id = ? FOR UPDATE', [$productId])->fetch();
            if (!$product) throw new RuntimeException('Produit introuvable.');
            $quantity=(float)$values['quantity']; $before=(float)$product['actual_quantity']; $after=$before+$quantity;
            $unitCost=(float)$values['actual_price'];
            $newAverage=$after > 0 ? round(($before*(float)$product['actual_price'] + $quantity*$unitCost)/$after) : $unitCost;
            $expiry=$values['expire_date'] ?: ($product['expire_date'] ?: null);
            $db->execute("UPDATE stock SET registered_quantity = registered_quantity + ?, actual_quantity = ?, actual_price = ?, profit_price = selling_price - ?, expire_date = ?, status = 'Disponible' WHERE id = ?", [$quantity,$after,$newAverage,$newAverage,$expiry,$productId]);
            $db->execute('INSERT INTO entries (operator, invoice_in, bar_code, medicine_name, category, quantity, expire_date, sell_type, actual_price, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [mb_substr($user['nom_utilisateur'],0,25),$values['invoice_in'] ?: null,(int)$product['bar_code'],$product['medicine_name'],$product['category'],$quantity,$expiry,$product['sell_type'],$unitCost,$quantity*$unitCost]);
            inventoryLogMovement($db,$product,'entree',$quantity,$before,$after,$unitCost,$values['invoice_in'] ?: null,$values['reason'],$user['nom_complet']);
            $pdo->commit();
            $_SESSION['flash_message']=['type'=>'success','text'=>'Entrée enregistrée : +'.number_format($quantity,2,',',' ').' '.$product['sell_type'].' de « '.$product['medicine_name'].' ».'];
            header('Location: '.app_route_url('stock/mouvements')); exit;
        } catch (Throwable $error) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('Stock entry failed: '.$error->getMessage());
            $errors[]=$error instanceof RuntimeException ? $error->getMessage() : 'L’entrée n’a pas pu être enregistrée.';
        }
    }
}
?>
<section class="mx-auto max-w-3xl space-y-5"><header><a href="<?= inventoryEscape(app_route_url('stock')) ?>" class="inline-flex items-center gap-2 text-sm font-medium text-green-800"><i data-feather="arrow-left" class="h-4 w-4"></i> État du stock</a><div class="mt-3"><p class="text-sm font-semibold text-green-800">Réception</p><h1 class="mt-1 text-2xl font-bold text-slate-900">Entrée de stock</h1><p class="mt-1 text-sm text-slate-600">La quantité disponible et le coût moyen seront recalculés.</p></div></header>
<?php if ($errors): ?><div role="alert" class="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800"><ul class="list-disc pl-5"><?php foreach (array_unique($errors) as $error): ?><li><?= inventoryEscape($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php if (!$products): ?><div class="rounded-md border border-amber-200 bg-amber-50 p-4 text-amber-900">Aucun produit au catalogue. Créez d’abord un produit.</div><?php endif; ?>
<form method="post" class="space-y-4 rounded-md border border-slate-200 bg-white p-5"><input type="hidden" name="csrf_token" value="<?= inventoryEscape($_SESSION['csrf_token']) ?>"><label class="block space-y-1 text-sm font-medium">Produit<select name="product_id" required class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5" <?= !$products?'disabled':'' ?>><option value="">Choisir un produit</option><?php foreach($products as $product): ?><option value="<?= (int)$product['id'] ?>" <?= $productId===(int)$product['id']?'selected':'' ?>><?= inventoryEscape($product['medicine_name']) ?> · stock <?= number_format((float)$product['actual_quantity'],2,',',' ') ?> <?= inventoryEscape($product['sell_type']) ?></option><?php endforeach; ?></select></label>
<div class="grid gap-4 sm:grid-cols-2"><label class="space-y-1 text-sm font-medium">Quantité reçue<input name="quantity" type="number" min="0.1" step="0.1" required value="<?= inventoryEscape($values['quantity']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label><label class="space-y-1 text-sm font-medium">Coût unitaire (FBu)<input name="actual_price" type="number" min="0" step="1" required value="<?= inventoryEscape($values['actual_price']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label><label class="space-y-1 text-sm font-medium">Référence fournisseur<input name="invoice_in" maxlength="100" value="<?= inventoryEscape($values['invoice_in']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label><label class="space-y-1 text-sm font-medium">Expiration (facultatif)<input name="expire_date" type="date" value="<?= inventoryEscape($values['expire_date']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label><label class="space-y-1 text-sm font-medium sm:col-span-2">Motif<input name="reason" maxlength="500" required value="<?= inventoryEscape($values['reason']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label></div>
<div class="flex justify-end border-t pt-4"><button class="rounded-md bg-green-700 px-4 py-2.5 text-sm font-semibold text-white" <?= !$products?'disabled':'' ?>>Enregistrer l’entrée</button></div></form></section>