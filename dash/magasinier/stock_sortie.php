<?php
if (!defined('ROUTER_LOADED')) { http_response_code(403); exit('Accès direct interdit.'); }
if (!userCan($user, 'flux.sorties')) { http_response_code(403); echo '<div class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</div>'; return; }
require_once __DIR__ . '/../../api/database.php';
require_once __DIR__ . '/_inventory.php';
$db=Database::getInstance();
$products=$db->fetchAll('SELECT id, medicine_name, actual_quantity, sell_type FROM stock WHERE actual_quantity > 0 ORDER BY medicine_name');
$productId=(int)($_GET['product_id'] ?? $_POST['product_id'] ?? 0);
$quantityValue='1'; $reference=''; $reason=''; $errors=[]; inventoryEnsureCsrf();
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $productId=(int)($_POST['product_id']??0); $quantityValue=trim((string)($_POST['quantity']??'')); $reference=trim((string)($_POST['reference']??'')); $reason=trim((string)($_POST['reason']??''));
    if (!hash_equals($_SESSION['csrf_token'],(string)($_POST['csrf_token']??''))) $errors[]='Le formulaire a expiré. Rechargez puis réessayez.';
    if (!is_numeric($quantityValue)||!is_finite((float)$quantityValue)||(float)$quantityValue<=0) $errors[]='La quantité doit être un nombre supérieur à zéro.';
    if ($reason===''||mb_strlen($reason)>500||mb_strlen($reference)>100) $errors[]='Indiquez un motif valide (500 caractères maximum).';
    if (!$errors) {
        $pdo=$db->getPdo();
        try {
            $pdo->beginTransaction();
            $product=$db->query('SELECT * FROM stock WHERE id = ? FOR UPDATE',[$productId])->fetch();
            if (!$product) throw new RuntimeException('Produit introuvable.');
            $quantity=round((float)$quantityValue,2); $before=(float)$product['actual_quantity'];
            if ($quantity>$before) throw new RuntimeException('Stock insuffisant : disponible '.number_format($before,2,',',' ').' '.$product['sell_type'].'.');
            $after=round($before-$quantity,2); $status=$after<=0?'Rupture':'Disponible';
            $db->execute('UPDATE stock SET actual_quantity = ?, used_quantity = used_quantity + ?, status = ? WHERE id = ?',[$after,$quantity,$status,$productId]);
            inventoryLogMovement($db,$product,'sortie',$quantity,$before,$after,(float)$product['actual_price'],$reference ?: null,$reason,$user['nom_complet']);
            $pdo->commit();
            $_SESSION['flash_message']=['type'=>'success','text'=>'Sortie enregistrée : −'.number_format($quantity,2,',',' ').' '.$product['sell_type'].' de « '.$product['medicine_name'].' ».'];
            header('Location: '.app_route_url('stock/mouvements')); exit;
        } catch (Throwable $error) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('Stock exit failed: '.$error->getMessage());
            $errors[]=$error instanceof RuntimeException?$error->getMessage():'La sortie n’a pas pu être enregistrée.';
        }
    }
}
?>
<section class="mx-auto max-w-3xl space-y-5"><header><a href="<?= inventoryEscape(app_route_url('stock')) ?>" class="inline-flex items-center gap-2 text-sm font-medium text-green-800"><i data-feather="arrow-left" class="h-4 w-4"></i> État du stock</a><div class="mt-3"><p class="text-sm font-semibold text-rose-700">Déstockage</p><h1 class="mt-1 text-2xl font-bold text-slate-900">Sortie de stock</h1><p class="mt-1 text-sm text-slate-600">Chaque sortie est enregistrée avec son motif et son opérateur.</p></div></header>
<?php if ($errors): ?><div role="alert" class="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800"><ul class="list-disc pl-5"><?php foreach(array_unique($errors) as $error): ?><li><?= inventoryEscape($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php if (!$products): ?><div class="rounded-md border border-amber-200 bg-amber-50 p-4 text-amber-900">Aucun produit n’a de quantité disponible à sortir.</div><?php endif; ?>
<form method="post" class="space-y-4 rounded-md border border-slate-200 bg-white p-5"><input type="hidden" name="csrf_token" value="<?= inventoryEscape($_SESSION['csrf_token']) ?>"><label class="block space-y-1 text-sm font-medium">Produit<select name="product_id" required class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5" <?= !$products?'disabled':'' ?>><option value="">Choisir un produit</option><?php foreach($products as $product): ?><option value="<?= (int)$product['id'] ?>" <?= $productId===(int)$product['id']?'selected':'' ?>><?= inventoryEscape($product['medicine_name']) ?> · disponible <?= number_format((float)$product['actual_quantity'],2,',',' ') ?> <?= inventoryEscape($product['sell_type']) ?></option><?php endforeach; ?></select></label>
<div class="grid gap-4 sm:grid-cols-2"><label class="space-y-1 text-sm font-medium">Quantité à sortir<input name="quantity" type="number" min="0.01" step="0.01" required value="<?= inventoryEscape($quantityValue) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label><label class="space-y-1 text-sm font-medium">Référence (facultatif)<input name="reference" maxlength="100" value="<?= inventoryEscape($reference) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label><label class="space-y-1 text-sm font-medium sm:col-span-2">Motif de la sortie <span class="text-red-600">*</span><textarea name="reason" maxlength="500" rows="3" required class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"><?= inventoryEscape($reason) ?></textarea></label></div>
<div class="flex justify-end border-t pt-4"><button class="rounded-md bg-rose-700 px-4 py-2.5 text-sm font-semibold text-white" <?= !$products?'disabled':'' ?>>Confirmer la sortie</button></div></form></section>