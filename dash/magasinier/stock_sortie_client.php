<?php
if (!defined('ROUTER_LOADED')) { http_response_code(403); exit('Accès direct interdit.'); }
if (!userCan($user, 'flux.sorties.client')) { http_response_code(403); echo '<div class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</div>'; return; }
require_once __DIR__ . '/../../api/database.php';
require_once __DIR__ . '/_inventory.php';
$db = Database::getInstance();
$role = $user['role_system'];
$errors = [];
$flash = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);
$search = trim((string) ($_GET['q'] ?? ''));
$clientId = (int) ($_GET['client_id'] ?? $_POST['client_id'] ?? 0);
$client = $clientId > 0 ? $db->fetch("SELECT id, name, phone FROM partners WHERE id = ? AND category = 'client'", [$clientId]) : null;
$clients = [];
if (!$client && $search !== '') {
    $term = '%' . $search . '%';
    $clients = $db->fetchAll("SELECT id, name, phone FROM partners WHERE category = 'client' AND (name LIKE ? OR phone LIKE ?) ORDER BY name LIMIT 50", [$term, $term]);
}
$products = $client ? $db->fetchAll('SELECT id, medicine_name, actual_quantity, sell_type FROM stock WHERE actual_quantity > 0 ORDER BY medicine_name') : [];
$productIds = array_values((array) ($_POST['product_id'] ?? ['']));
$quantities = array_values((array) ($_POST['quantity'] ?? ['1']));
$workshop = trim((string) ($_POST['workshop'] ?? ''));
$beneficiary = trim((string) ($_POST['beneficiary'] ?? ''));
$workReference = trim((string) ($_POST['work_reference'] ?? ''));
$reason = trim((string) ($_POST['reason'] ?? ''));
inventoryEnsureCsrf();
if (empty($_SESSION['client_issue_token'])) $_SESSION['client_issue_token'] = bin2hex(random_bytes(32));

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $productIds = array_values((array) ($_POST['product_id'] ?? []));
    $quantities = array_values((array) ($_POST['quantity'] ?? []));
    $workshop = trim((string) ($_POST['workshop'] ?? ''));
    $beneficiary = trim((string) ($_POST['beneficiary'] ?? ''));
    $workReference = trim((string) ($_POST['work_reference'] ?? ''));
    $reason = trim((string) ($_POST['reason'] ?? ''));
    if (!hash_equals($_SESSION['csrf_token'], (string) ($_POST['csrf_token'] ?? ''))) $errors[] = 'Le formulaire a expiré. Rechargez puis réessayez.';
    if (!hash_equals($_SESSION['client_issue_token'], (string) ($_POST['issue_token'] ?? ''))) $errors[] = 'Cette sortie a déjà été traitée ou le formulaire a expiré. Rechargez la page.';
    if (!$client) $errors[] = 'Recherchez et sélectionnez un client avant les articles.';
    if ($workshop === '' || mb_strlen($workshop) > 60) $errors[] = 'Indiquez un atelier destinataire (60 caractères maximum).';
    if (mb_strlen($beneficiary) > 60 || mb_strlen($workReference) > 60 || $reason === '' || mb_strlen($reason) > 200) $errors[] = 'Vérifiez le bénéficiaire, la référence et le motif (200 caractères maximum).';

    $lineInputs = [];
    $seen = [];
    $rowCount = max(count($productIds), count($quantities));
    if ($rowCount > 50) $errors[] = 'Une sortie ne peut pas contenir plus de 50 articles.';
    for ($index = 0; $index < min($rowCount, 50); $index++) {
        $lineProductId = (int) ($productIds[$index] ?? 0);
        $quantityText = str_replace(',', '.', trim((string) ($quantities[$index] ?? '')));
        if ($lineProductId <= 0 && $quantityText === '') continue;
        if ($lineProductId <= 0 || !is_numeric($quantityText) || !is_finite((float) $quantityText) || (float) $quantityText <= 0) {
            $errors[] = 'Chaque ligne doit contenir un article et une quantité supérieure à zéro.';
            continue;
        }
        if (isset($seen[$lineProductId])) {
            $errors[] = 'Un article ne peut apparaître qu’une fois; regroupez sa quantité sur la même ligne.';
            continue;
        }
        $seen[$lineProductId] = true;
        $lineInputs[] = ['id' => $lineProductId, 'quantity' => round((float) $quantityText, 2)];
    }
    if (!$lineInputs) $errors[] = 'Ajoutez au moins un article à la sortie.';

    if (!$errors) {
        $pdo = $db->getPdo();
        try {
            $pdo->beginTransaction();
            $idsToLock = array_column($lineInputs, 'id');
            sort($idsToLock, SORT_NUMERIC);
            $productsById = [];
            foreach ($idsToLock as $idToLock) {
                $product = $db->query('SELECT id, medicine_name, category, actual_quantity, used_quantity, actual_price, selling_price, sell_type, status FROM stock WHERE id = ? FOR UPDATE', [$idToLock])->fetch();
                if (!$product || $product['status'] !== 'Disponible') throw new RuntimeException('Un des articles n’est plus disponible. Actualisez la page puis réessayez.');
                $productsById[(int) $idToLock] = $product;
            }

            $items = [];
            $subtotal = 0.0;
            $costTotal = 0.0;
            foreach ($lineInputs as $line) {
                $product = $productsById[$line['id']];
                $quantity = $line['quantity'];
                if ($quantity > (float) $product['actual_quantity']) throw new RuntimeException('Stock insuffisant pour « ' . $product['medicine_name'] . ' ».');
                $unitPrice = (float) $product['selling_price'];
                $unitCost = (float) $product['actual_price'];
                $lineTotal = round($quantity * $unitPrice, 2);
                $subtotal += $lineTotal;
                $costTotal += $quantity * $unitCost;
                $items[] = ['stock_id' => (int) $product['id'], 'name' => $product['medicine_name'], 'quantity' => $quantity, 'unit' => $product['sell_type'], 'unit_cost' => $unitCost, 'unit_price' => $unitPrice, 'line_total' => $lineTotal];
            }

            $taxRate = defined('TVA_DEFAULT') ? (float) TVA_DEFAULT : 18.0;
            $taxAmount = (int) round($subtotal * $taxRate / 100);
            $grandTotal = (int) round($subtotal) + $taxAmount;
            $issueNumber = 'CLI-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
            $productNames = implode(', ', array_column($items, 'name'));
            $itemsJson = json_encode($items, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            $db->execute(
                "INSERT INTO sales (invoice_number, operator, agent, client_name, client_id, phone, medicines, products_json, quantity, units_json, unit_cost, actual_price, tot_htva, tva, tva_paid, tot_tvac, debt, total_amount, total_profit, discount, commission, date, status, origin, guard, netcash, payment_method, document_type)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, 0, ?, ?, 0, 0, CURDATE(), 'Active', 'Sortie client magasin', '', 0, 'À facturer', 'Commande')",
                [$issueNumber, $user['nom_utilisateur'], $user['nom_complet'], $client['name'], (int) $client['id'], (string) ($client['phone'] ?? ''), mb_substr($productNames, 0, 65000), $itemsJson, json_encode(array_column($items, 'quantity'), JSON_THROW_ON_ERROR), json_encode(array_column($items, 'unit'), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), json_encode(array_column($items, 'unit_cost'), JSON_THROW_ON_ERROR), json_encode(array_column($items, 'unit_price'), JSON_THROW_ON_ERROR), (int) round($subtotal), $taxAmount, $grandTotal, $grandTotal, (int) round($subtotal - $costTotal)]
            );

            $movementReason = 'Sortie client à facturer; Atelier: ' . $workshop . '; Bénéficiaire: ' . ($beneficiary ?: 'Non précisé') . '; Motif: ' . $reason . '; Réf. travail: ' . ($workReference ?: '—');
            foreach ($lineInputs as $line) {
                $product = $productsById[$line['id']];
                $before = (float) $product['actual_quantity'];
                $after = round($before - $line['quantity'], 2);
                $db->execute('UPDATE stock SET actual_quantity = ?, used_quantity = used_quantity + ?, status = ? WHERE id = ?', [$after, $line['quantity'], $after <= 0 ? 'Rupture' : 'Disponible', $line['id']]);
                inventoryLogMovement($db, $product, 'sortie', $line['quantity'], $before, $after, (float) $product['actual_price'], $issueNumber, $movementReason, $user['nom_complet']);
            }
            $pdo->commit();
            $_SESSION['client_issue_token'] = bin2hex(random_bytes(32));
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Sortie ' . $issueNumber . ' enregistrée pour ' . $client['name'] . '. La facturation reste à effectuer par la réception.'];
            header('Location: ' . app_route_url('stock/sorties-clients'));
            exit;
        } catch (Throwable $error) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('Customer stock issue failed: ' . $error->getMessage());
            $errors[] = $error instanceof RuntimeException ? $error->getMessage() : 'La sortie client n’a pas été enregistrée.';
        }
    }
}
?>
<section class="mx-auto max-w-4xl space-y-5">
    <header><a href="<?= inventoryEscape(app_route_url('stock')) ?>" class="text-sm font-medium text-green-800">État du stock</a><p class="mt-3 text-sm font-semibold text-rose-700">Sortie client</p><h1 class="mt-1 text-2xl font-bold text-slate-900">Remise au client</h1><p class="mt-1 text-sm text-slate-600">La sortie diminue le stock. La réception établira ensuite la facture et enregistrera le paiement.</p></header>
    <?php if ($flash): ?><div role="status" class="rounded-md border border-green-200 bg-green-50 p-4 text-sm text-green-800"><?= inventoryEscape($flash['text'] ?? '') ?></div><?php endif; ?>
    <?php if ($errors): ?><div role="alert" class="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800"><ul class="list-disc pl-5"><?php foreach (array_unique($errors) as $error): ?><li><?= inventoryEscape($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

    <?php if (!$client): ?>
        <form method="get" action="<?= inventoryEscape(app_url('router.php')) ?>" class="grid gap-3 rounded-md border border-slate-200 bg-white p-5 sm:grid-cols-[1fr_auto] sm:items-end"><input type="hidden" name="route" value="<?= inventoryEscape($role . '/stock/sorties-clients') ?>"><label class="space-y-1 text-sm font-medium">Rechercher un client par nom ou téléphone<input name="q" value="<?= inventoryEscape($search) ?>" required autofocus class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label><button class="rounded-md bg-green-700 px-4 py-2.5 text-sm font-semibold text-white">Rechercher le client</button></form>
        <?php if ($search !== ''): ?><div class="overflow-x-auto rounded-md border border-slate-200 bg-white"><table class="min-w-full divide-y text-sm"><thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-4 py-3">Client</th><th class="px-4 py-3">Téléphone</th><th class="px-4 py-3">Action</th></tr></thead><tbody class="divide-y"><?php if (!$clients): ?><tr><td colspan="3" class="px-4 py-8 text-center text-slate-500">Aucun client trouvé. La sortie client exige un client enregistré.</td></tr><?php else: foreach ($clients as $candidate): ?><tr><td class="px-4 py-3 font-medium"><?= inventoryEscape($candidate['name']) ?></td><td class="px-4 py-3"><?= inventoryEscape($candidate['phone'] ?: '—') ?></td><td class="px-4 py-3"><a class="font-semibold text-green-800 hover:underline" href="<?= inventoryEscape(app_route_url('stock/sorties-clients')) ?>?client_id=<?= (int) $candidate['id'] ?>">Sélectionner</a></td></tr><?php endforeach; endif; ?></tbody></table></div><?php endif; ?>
    <?php else: ?>
        <div class="flex flex-wrap items-center justify-between gap-3 border-y border-slate-200 py-4"><div><p class="text-xs font-semibold uppercase text-slate-500">Client sélectionné</p><p class="mt-1 font-semibold text-slate-900"><?= inventoryEscape($client['name']) ?></p><p class="text-sm text-slate-600"><?= inventoryEscape($client['phone'] ?: 'Téléphone non renseigné') ?></p></div><a href="<?= inventoryEscape(app_route_url('stock/sorties-clients')) ?>" class="text-sm font-medium text-green-800 hover:underline">Changer de client</a></div>
        <?php if (!$products): ?><div class="rounded-md border border-amber-200 bg-amber-50 p-4 text-amber-900">Aucun article disponible en stock.</div><?php endif; ?>
        <form method="post" class="space-y-5 rounded-md border border-slate-200 bg-white p-5"><input type="hidden" name="csrf_token" value="<?= inventoryEscape($_SESSION['csrf_token']) ?>"><input type="hidden" name="issue_token" value="<?= inventoryEscape($_SESSION['client_issue_token']) ?>"><input type="hidden" name="client_id" value="<?= (int) $client['id'] ?>">
            <fieldset class="space-y-3"><div class="flex items-center justify-between gap-3"><legend class="text-sm font-semibold">Articles remis</legend><button type="button" id="addClientLine" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium" <?= !$products ? 'disabled' : '' ?>>Ajouter un article</button></div><div id="clientLines" class="space-y-3"><?php foreach ($productIds as $lineIndex => $selectedId): ?><div class="client-line grid gap-3 rounded-md border border-slate-200 p-3 sm:grid-cols-[minmax(0,1fr)_9rem_auto] sm:items-end"><label class="space-y-1 text-sm font-medium">Article<select name="product_id[]" required class="client-product w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 font-normal"><option value="">Choisir un article</option><?php foreach ($products as $product): ?><option value="<?= (int) $product['id'] ?>" <?= (string) $selectedId === (string) $product['id'] ? 'selected' : '' ?>><?= inventoryEscape($product['medicine_name']) ?> · <?= number_format((float) $product['actual_quantity'], 2, ',', ' ') ?> <?= inventoryEscape($product['sell_type']) ?></option><?php endforeach; ?></select></label><label class="space-y-1 text-sm font-medium">Quantité<input name="quantity[]" type="number" min="0.01" step="0.01" required value="<?= inventoryEscape($quantities[$lineIndex] ?? '1') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label><button type="button" class="remove-client-line rounded-md border border-slate-300 p-2 text-rose-700" aria-label="Retirer l’article" title="Retirer l’article">Retirer</button></div><?php endforeach; ?></div></fieldset>
            <div class="grid gap-4 border-t border-slate-100 pt-4 sm:grid-cols-2"><label class="space-y-1 text-sm font-medium">Atelier destinataire <span class="text-red-600">*</span><input name="workshop" maxlength="60" required value="<?= inventoryEscape($workshop) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label><label class="space-y-1 text-sm font-medium">Bénéficiaire<input name="beneficiary" maxlength="60" value="<?= inventoryEscape($beneficiary) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label><label class="space-y-1 text-sm font-medium">Référence du travail<input name="work_reference" maxlength="60" value="<?= inventoryEscape($workReference) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label><label class="space-y-1 text-sm font-medium sm:col-span-2">Motif <span class="text-red-600">*</span><textarea name="reason" maxlength="200" rows="3" required class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"><?= inventoryEscape($reason) ?></textarea></label></div>
            <div class="flex justify-end border-t pt-4"><button class="rounded-md bg-rose-700 px-4 py-2.5 text-sm font-semibold text-white" <?= !$products ? 'disabled' : '' ?>>Confirmer la sortie et transmettre à la réception</button></div>
        </form>
        <template id="clientLineTemplate"><div class="client-line grid gap-3 rounded-md border border-slate-200 p-3 sm:grid-cols-[minmax(0,1fr)_9rem_auto] sm:items-end"><label class="space-y-1 text-sm font-medium">Article<select name="product_id[]" required class="client-product w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 font-normal"><option value="">Choisir un article</option><?php foreach ($products as $product): ?><option value="<?= (int) $product['id'] ?>"><?= inventoryEscape($product['medicine_name']) ?> · <?= number_format((float) $product['actual_quantity'], 2, ',', ' ') ?> <?= inventoryEscape($product['sell_type']) ?></option><?php endforeach; ?></select></label><label class="space-y-1 text-sm font-medium">Quantité<input name="quantity[]" type="number" min="0.01" step="0.01" required value="1" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label><button type="button" class="remove-client-line rounded-md border border-slate-300 p-2 text-rose-700" aria-label="Retirer l’article">Retirer</button></div></template>
        <script>(()=>{const lines=document.getElementById('clientLines'),add=document.getElementById('addClientLine'),template=document.getElementById('clientLineTemplate');if(!lines||!add||!template)return;add.addEventListener('click',()=>{if(lines.children.length<50)lines.append(template.content.cloneNode(true));});lines.addEventListener('click',event=>{const button=event.target.closest('.remove-client-line');if(!button)return;const rows=lines.querySelectorAll('.client-line');if(rows.length>1)button.closest('.client-line').remove();else{rows[0].querySelector('select').value='';rows[0].querySelector('input').value='1';}});})();</script>
    <?php endif; ?>
</section>