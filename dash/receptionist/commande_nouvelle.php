<?php
if (!defined('ROUTER_LOADED')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

if (!userCan($user, 'commandes.create')) {
    http_response_code(403);
    echo '<p class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</p>';
    return;
}

require_once __DIR__ . '/../../api/database.php';
$db = Database::getInstance();
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$errors = [];
$clientId = 0;
$postedProductIds = [''];
$postedQuantities = ['1'];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$clients = $db->fetchAll(
    "SELECT id, name, phone FROM partners WHERE category = 'client' ORDER BY name"
);
$products = $db->fetchAll(
    "SELECT id, medicine_name, actual_quantity, sell_type, selling_price
     FROM stock
     WHERE status = 'Disponible' AND actual_quantity > 0
     ORDER BY medicine_name"
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clientId = (int) ($_POST['client_id'] ?? 0);
    $postedProductIds = array_values((array) ($_POST['product_id'] ?? []));
    $postedQuantities = array_values((array) ($_POST['quantity'] ?? []));
    $csrfToken = (string) ($_POST['csrf_token'] ?? '');

    if (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $errors[] = 'Le formulaire a expiré. Rechargez la page puis réessayez.';
    }

    $client = $db->fetch(
        "SELECT id, name, phone FROM partners WHERE id = ? AND category = 'client' LIMIT 1",
        [$clientId]
    );
    if (!$client) {
        $errors[] = 'Sélectionnez un client valide.';
    }

    $lineItems = [];
    $seenProductIds = [];
    $subtotal = 0.0;
    $costTotal = 0.0;
    $rowCount = max(count($postedProductIds), count($postedQuantities));

    for ($index = 0; $index < $rowCount; $index++) {
        $productId = (int) ($postedProductIds[$index] ?? 0);
        $quantityInput = str_replace(',', '.', trim((string) ($postedQuantities[$index] ?? '')));
        if ($productId <= 0 && $quantityInput === '') {
            continue;
        }
        if ($productId <= 0 || !is_numeric($quantityInput)) {
            $errors[] = 'Chaque ligne doit contenir un produit et une quantité valide.';
            continue;
        }

        $quantity = (float) $quantityInput;
        if (!is_finite($quantity) || $quantity <= 0) {
            $errors[] = 'Les quantités doivent être supérieures à zéro.';
            continue;
        }
        if (isset($seenProductIds[$productId])) {
            $errors[] = 'Un même produit ne peut apparaître qu’une fois. Modifiez sa quantité sur sa ligne.';
            continue;
        }
        $seenProductIds[$productId] = true;

        $product = $db->fetch(
            "SELECT id, medicine_name, actual_quantity, sell_type, actual_price, selling_price
             FROM stock
             WHERE id = ? AND status = 'Disponible'
             LIMIT 1",
            [$productId]
        );
        if (!$product) {
            $errors[] = 'Un des produits sélectionnés n’est plus disponible.';
            continue;
        }
        if ($quantity > (float) $product['actual_quantity']) {
            $errors[] = 'La quantité demandée pour « ' . $product['medicine_name'] . ' » dépasse le stock disponible (' . $product['actual_quantity'] . ').';
            continue;
        }

        $unitPrice = (float) $product['selling_price'];
        $unitCost = (float) $product['actual_price'];
        $lineTotal = round($quantity * $unitPrice, 2);
        $subtotal += $lineTotal;
        $costTotal += $quantity * $unitCost;
        $lineItems[] = [
            'stock_id' => (int) $product['id'],
            'name' => $product['medicine_name'],
            'quantity' => $quantity,
            'unit' => $product['sell_type'],
            'unit_cost' => $unitCost,
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
        ];
    }

    if (!$lineItems) {
        $errors[] = 'Ajoutez au moins un produit à la commande.';
    }

    if (!$errors) {
        $taxRate = defined('TVA_DEFAULT') ? (float) TVA_DEFAULT : 0.0;
        $taxAmount = (int) round($subtotal * $taxRate / 100);
        $grandTotal = (int) round($subtotal) + $taxAmount;
        $invoiceNumber = 'CMD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $pdo = $db->getPdo();

        try {
            $pdo->beginTransaction();
            $db->execute(
                "INSERT INTO sales (
                    invoice_number, operator, agent, client_name, client_id, phone,
                    medicines, quantity, unit_cost, actual_price, tot_htva, tva,
                    tva_paid, tot_tvac, debt, total_amount, total_profit, discount,
                    commission, date, status, origin, guard, netcash, payment_method,
                    document_type
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, 0, 0, CURDATE(), 'Active', 'Interne', '', 0, 'À payer', 'Commande')",
                [
                    $invoiceNumber,
                    $user['nom_utilisateur'],
                    $user['nom_complet'],
                    $client['name'],
                    (int) $client['id'],
                    $client['phone'] ?? '',
                    json_encode($lineItems, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                    json_encode(array_column($lineItems, 'quantity'), JSON_THROW_ON_ERROR),
                    json_encode(array_column($lineItems, 'unit_cost'), JSON_THROW_ON_ERROR),
                    json_encode(array_column($lineItems, 'unit_price'), JSON_THROW_ON_ERROR),
                    (int) round($subtotal),
                    $taxAmount,
                    $grandTotal,
                    $grandTotal,
                    $grandTotal,
                    (int) round($subtotal - $costTotal),
                ]
            );
            $pdo->commit();
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'La commande ' . $invoiceNumber . ' a été enregistrée.'];
            header('Location: ' . app_route_url('commandes/clients'));
            exit;
        } catch (Throwable $error) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Order creation failed: ' . $error->getMessage());
            $errors[] = 'La commande n’a pas pu être enregistrée. Vérifiez les données puis réessayez.';
        }
    }
}
?>
<section class="mx-auto max-w-4xl space-y-5">
    <header>
        <a href="<?= $escape(app_route_url('commandes/clients')) ?>" class="inline-flex items-center gap-2 text-sm font-medium text-green-800 hover:underline"><i data-feather="arrow-left" class="h-4 w-4"></i> Commandes clients</a>
        <div class="mt-3"><h1 class="text-2xl font-bold text-slate-900">Nouvelle commande</h1><p class="mt-1 text-sm text-slate-600">Les prix sont repris du catalogue actuel. Le stock ne sera sorti qu’à la livraison.</p></div>
    </header>

    <?php if ($errors): ?>
        <div role="alert" class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><ul class="list-disc space-y-1 pl-5"><?php foreach (array_unique($errors) as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <?php if (!$clients): ?>
        <div class="rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">Aucun client n’est enregistré. Ajoutez d’abord un client pour pouvoir créer une commande.</div>
    <?php endif; ?>
    <?php if (!$products): ?>
        <div class="rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">Aucun produit disponible en stock. Une commande ne peut être créée qu’avec des produits disponibles.</div>
    <?php endif; ?>

    <form method="post" class="space-y-5 rounded-md border border-slate-200 bg-white p-5 sm:p-6" id="orderForm">
        <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_token']) ?>">
        <fieldset class="space-y-2">
            <legend class="text-sm font-semibold text-slate-800">Client</legend>
            <label for="client_id" class="sr-only">Sélectionner un client</label>
            <select id="client_id" name="client_id" required class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20" <?= !$clients ? 'disabled' : '' ?>><option value="">Choisir un client</option><?php foreach ($clients as $clientOption): ?><option value="<?= (int) $clientOption['id'] ?>" <?= $clientId === (int) $clientOption['id'] ? 'selected' : '' ?>><?= $escape($clientOption['name']) ?><?= $clientOption['phone'] ? ' · ' . $escape($clientOption['phone']) : '' ?></option><?php endforeach; ?></select>
        </fieldset>

        <fieldset class="space-y-3">
            <div class="flex items-center justify-between gap-3"><legend class="text-sm font-semibold text-slate-800">Articles</legend><button type="button" id="addLine" class="inline-flex items-center gap-2 rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50" <?= !$products ? 'disabled' : '' ?>><i data-feather="plus" class="h-4 w-4"></i> Ajouter un article</button></div>
            <div id="orderLines" class="space-y-3">
                <?php foreach ($postedProductIds as $index => $postedId): ?>
                    <div class="order-line grid gap-3 rounded-md border border-slate-200 p-3 sm:grid-cols-[minmax(0,1fr)_8rem_8rem_auto] sm:items-end">
                        <label class="space-y-1 text-sm font-medium text-slate-700">Produit<select name="product_id[]" required class="product-select w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 font-normal"><option value="">Choisir un produit</option><?php foreach ($products as $product): ?><option value="<?= (int) $product['id'] ?>" data-price="<?= (float) $product['selling_price'] ?>" data-stock="<?= (float) $product['actual_quantity'] ?>" <?= (string) $postedId === (string) $product['id'] ? 'selected' : '' ?>><?= $escape($product['medicine_name']) ?> · <?= number_format((float) $product['selling_price'], 0, ',', ' ') ?> FBu · stock <?= $escape($product['actual_quantity']) ?> <?= $escape($product['sell_type']) ?></option><?php endforeach; ?></select></label>
                        <label class="space-y-1 text-sm font-medium text-slate-700">Quantité<input name="quantity[]" type="number" min="0.01" step="0.01" required value="<?= $escape($postedQuantities[$index] ?? '1') ?>" class="quantity-input w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label>
                        <div class="space-y-1 text-sm"><span class="block font-medium text-slate-700">Sous-total</span><output class="line-total block rounded-md bg-slate-50 px-3 py-2.5 text-right font-semibold text-slate-800">—</output></div>
                        <button type="button" class="remove-line inline-flex h-10 items-center justify-center rounded-md border border-slate-300 px-3 text-slate-600 hover:border-red-300 hover:text-red-700" aria-label="Retirer cet article" title="Retirer cet article"><i data-feather="trash-2" class="h-4 w-4"></i></button>
                    </div>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <div class="ml-auto max-w-sm space-y-2 border-t border-slate-200 pt-4 text-sm">
            <div class="flex justify-between text-slate-600"><span>Sous-total HT</span><span id="subtotalDisplay">0 FBu</span></div>
            <div class="flex justify-between text-slate-600"><span>TVA (<?= defined('TVA_DEFAULT') ? (float) TVA_DEFAULT : 0 ?>%)</span><span id="taxDisplay">0 FBu</span></div>
            <div class="flex justify-between border-t border-slate-200 pt-2 text-base font-bold text-slate-900"><span>Total TTC</span><span id="grandTotalDisplay">0 FBu</span></div>
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:justify-end">
            <a href="<?= $escape(app_route_url('commandes/clients')) ?>" class="inline-flex justify-center rounded-md border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Annuler</a>
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-md bg-green-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-800 disabled:cursor-not-allowed disabled:opacity-50" <?= (!$clients || !$products) ? 'disabled' : '' ?>><i data-feather="check" class="h-4 w-4"></i> Enregistrer la commande</button>
        </div>
    </form>
</section>

<template id="orderLineTemplate">
    <div class="order-line grid gap-3 rounded-md border border-slate-200 p-3 sm:grid-cols-[minmax(0,1fr)_8rem_8rem_auto] sm:items-end">
        <label class="space-y-1 text-sm font-medium text-slate-700">Produit<select name="product_id[]" required class="product-select w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 font-normal"><option value="">Choisir un produit</option><?php foreach ($products as $product): ?><option value="<?= (int) $product['id'] ?>" data-price="<?= (float) $product['selling_price'] ?>" data-stock="<?= (float) $product['actual_quantity'] ?>"><?= $escape($product['medicine_name']) ?> · <?= number_format((float) $product['selling_price'], 0, ',', ' ') ?> FBu · stock <?= $escape($product['actual_quantity']) ?> <?= $escape($product['sell_type']) ?></option><?php endforeach; ?></select></label>
        <label class="space-y-1 text-sm font-medium text-slate-700">Quantité<input name="quantity[]" type="number" min="0.01" step="0.01" required value="1" class="quantity-input w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label>
        <div class="space-y-1 text-sm"><span class="block font-medium text-slate-700">Sous-total</span><output class="line-total block rounded-md bg-slate-50 px-3 py-2.5 text-right font-semibold text-slate-800">—</output></div>
        <button type="button" class="remove-line inline-flex h-10 items-center justify-center rounded-md border border-slate-300 px-3 text-slate-600 hover:border-red-300 hover:text-red-700" aria-label="Retirer cet article" title="Retirer cet article"><i data-feather="trash-2" class="h-4 w-4"></i></button>
    </div>
</template>

<script>
(() => {
    const lines = document.getElementById('orderLines');
    const taxRate = <?= defined('TVA_DEFAULT') ? (float) TVA_DEFAULT : 0 ?>;
    const formatMoney = value => `${new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(value)} FBu`;

    function recalculate() {
        let subtotal = 0;
        lines.querySelectorAll('.order-line').forEach(line => {
            const select = line.querySelector('.product-select');
            const quantity = Number(line.querySelector('.quantity-input').value) || 0;
            const option = select.options[select.selectedIndex];
            const price = Number(option?.dataset.price) || 0;
            const lineTotal = price * quantity;
            subtotal += lineTotal;
            line.querySelector('.line-total').textContent = select.value ? formatMoney(lineTotal) : '—';
        });
        const tax = Math.round(subtotal * taxRate / 100);
        document.getElementById('subtotalDisplay').textContent = formatMoney(subtotal);
        document.getElementById('taxDisplay').textContent = formatMoney(tax);
        document.getElementById('grandTotalDisplay').textContent = formatMoney(subtotal + tax);
    }

    document.getElementById('addLine').addEventListener('click', () => {
        const template = document.getElementById('orderLineTemplate');
        lines.append(template.content.cloneNode(true));
        if (window.feather) feather.replace();
        recalculate();
    });
    lines.addEventListener('input', recalculate);
    lines.addEventListener('change', recalculate);
    lines.addEventListener('click', event => {
        const removeButton = event.target.closest('.remove-line');
        if (!removeButton) return;
        const orderLines = lines.querySelectorAll('.order-line');
        if (orderLines.length > 1) removeButton.closest('.order-line').remove();
        else {
            orderLines[0].querySelector('.product-select').value = '';
            orderLines[0].querySelector('.quantity-input').value = '1';
        }
        recalculate();
    });
    recalculate();
})();
</script>