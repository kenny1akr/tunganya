<?php
if (!defined('ROUTER_LOADED')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

$offerId = (int) ($_GET['id'] ?? 0);
$isEdit  = $offerId > 0;

if ($isEdit && !userCan($user, 'offres.edit')) {
    http_response_code(403);
    echo '<p class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</p>';
    return;
}
if (!$isEdit && !userCan($user, 'offres.create')) {
    http_response_code(403);
    echo '<p class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</p>';
    return;
}

require_once __DIR__ . '/../../api/database.php';
require_once __DIR__ . '/_sales.php';

$db     = Database::getInstance();
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$errors = [];

receptionEnsureCsrf();

$validDate = static function (string $v): bool {
    if ($v === '') return false;
    $d = DateTime::createFromFormat('!Y-m-d', $v);
    return $d !== false && $d->format('Y-m-d') === $v;
};

$settings = $db->fetch("SELECT tva_default FROM app_settings WHERE id = 1 LIMIT 1") ?: [];
$taxRate  = (float) ($settings['tva_default'] ?? 18.00);
$allowedStatuses = ['Brouillon','Envoyée','Acceptée','Refusée','Expirée'];

$clients = $db->fetchAll(
    "SELECT id, name, phone, email, status_client, annif
     FROM partners WHERE category = 'client' ORDER BY name"
);

$products = $db->fetchAll(
    "SELECT id, medicine_name, actual_quantity, sell_type, selling_price
     FROM stock WHERE status = 'Disponible' AND actual_quantity > 0 ORDER BY medicine_name"
);

// Valeurs du formulaire
$form = [
    'client_id'      => 0,
    'walk_in'        => 0,
    'client_name'    => '',
    'client_phone'   => '',
    'offer_date'     => date('Y-m-d'),
    'valid_until'    => date('Y-m-d', strtotime('+30 days')),
    'offer_status'   => 'Brouillon',
    'offer_notes'    => '',
    'discount_type'  => 'Aucune',
    'discount_value' => '',
];
$postedProductIds = [''];
$postedQuantities = ['1'];
$postedUnitPrices = [''];
$postedLineNotes  = [''];

// Si édition → charger
if ($isEdit && ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    $sale = $db->fetch(
        "SELECT * FROM sales WHERE id = ? AND document_type = 'Devis' AND status = 'Active' LIMIT 1",
        [$offerId]
    );
    if (!$sale) {
        http_response_code(404);
        echo '<div class="rounded-md border border-amber-200 bg-amber-50 p-4 text-amber-900">Offre introuvable.</div>';
        return;
    }

    $form['client_id']      = (int) ($sale['client_id'] ?? 0);
    $form['client_name']    = (string) $sale['client_name'];
    $form['client_phone']   = (string) $sale['phone'];
    $form['offer_date']     = (string) $sale['date'];
    $form['valid_until']    = (string) ($sale['valid_until'] ?? '');
    $form['offer_status']   = (string) ($sale['offer_status'] ?? 'Brouillon');
    $form['offer_notes']    = (string) ($sale['offer_notes'] ?? '');
    $form['discount_type']  = (string) ($sale['discount_type'] ?? 'Aucune');
    $form['discount_value'] = (string) ($sale['discount_value'] ?? '');
    if ($form['client_id'] === 0) {
        $form['walk_in'] = 1;
    }

    $items = json_decode((string) ($sale['products_json'] ?? $sale['medicines']), true);
    if (is_array($items) && $items) {
        $postedProductIds = [];
        $postedQuantities = [];
        $postedUnitPrices = [];
        $postedLineNotes  = [];
        foreach ($items as $it) {
            $postedProductIds[] = (string) ($it['stock_id'] ?? '');
            $postedQuantities[] = (string) ($it['quantity'] ?? '1');
            $postedUnitPrices[] = (string) ($it['unit_price'] ?? '');
            $postedLineNotes[]  = (string) ($it['note'] ?? '');
        }
    }
}

// Traitement POST
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {

    foreach ($form as $key => $default) {
        if ($key === 'walk_in') {
            $form[$key] = !empty($_POST['walk_in']) ? 1 : 0;
        } else {
            $form[$key] = trim((string) ($_POST[$key] ?? $default));
        }
    }
    $form['client_id']      = (int) $form['client_id'];
    $form['discount_value'] = str_replace(',', '.', $form['discount_value']);

    $postedProductIds = array_values((array) ($_POST['product_id'] ?? ['']));
    $postedQuantities = array_values((array) ($_POST['quantity']     ?? ['1']));
    $postedUnitPrices = array_values((array) ($_POST['unit_price']   ?? ['']));
    $postedLineNotes  = array_values((array) ($_POST['line_note']    ?? ['']));

    if (!hash_equals($_SESSION['csrf_token'], (string) ($_POST['csrf_token'] ?? ''))) {
        $errors[] = 'Le formulaire a expiré. Rechargez la page.';
    }

    // Client
    $client = null;
    if ($form['walk_in'] === 1) {
        if ($form['client_name'] === '' || mb_strlen($form['client_name']) > 255) {
            $errors[] = 'Indiquez le nom du client (255 caractères max).';
        }
    } else {
        $client = $db->fetch(
            "SELECT id, name, phone FROM partners WHERE id = ? AND category = 'client' LIMIT 1",
            [$form['client_id']]
        );
        if (!$client) {
            $errors[] = 'Sélectionnez un client ou cochez « Client de passage ».';
        }
    }

    // En-tête
    if (!$validDate($form['offer_date'])) $errors[] = 'La date de l’offre est invalide.';
    if ($form['valid_until'] !== '' && !$validDate($form['valid_until'])) $errors[] = 'La date de validité est invalide.';
    if (!in_array($form['offer_status'], $allowedStatuses, true)) $errors[] = 'Statut invalide.';
    if (mb_strlen($form['offer_notes']) > 2000) $errors[] = 'Les notes ne peuvent pas dépasser 2000 caractères.';

    // Lignes
    $lineItems = [];
    $seenProductIds = [];
    $subtotal = 0.0;
    $rowCount = max(count($postedProductIds), count($postedQuantities));

    for ($i = 0; $i < $rowCount; $i++) {
        $productId  = (int) ($postedProductIds[$i] ?? 0);
        $qtyInput   = str_replace(',', '.', trim((string) ($postedQuantities[$i] ?? '')));
        $priceInput = str_replace(',', '.', trim((string) ($postedUnitPrices[$i] ?? '')));
        $lineNote   = trim((string) ($postedLineNotes[$i] ?? ''));

        if ($productId <= 0 && $qtyInput === '' && $priceInput === '' && $lineNote === '') continue;
        if ($productId <= 0 || !is_numeric($qtyInput)) {
            $errors[] = 'Chaque ligne doit contenir un produit et une quantité valide.';
            continue;
        }
        $qty = (float) $qtyInput;
        if (!is_finite($qty) || $qty <= 0) { $errors[] = 'Les quantités doivent être supérieures à zéro.'; continue; }
        if (isset($seenProductIds[$productId])) { $errors[] = 'Un produit ne peut apparaître qu’une fois.'; continue; }
        $seenProductIds[$productId] = true;

        $product = $db->fetch(
            "SELECT id, medicine_name, sell_type, selling_price FROM stock WHERE id = ? LIMIT 1",
            [$productId]
        );
        if (!$product) { $errors[] = 'Produit introuvable.'; continue; }

        $defaultPrice = (float) $product['selling_price'];
        $unitPrice    = ($priceInput !== '' && is_numeric($priceInput) && (float) $priceInput >= 0)
                        ? (float) $priceInput : $defaultPrice;

        $lineTotal = round($qty * $unitPrice, 2);
        $subtotal += $lineTotal;

        $lineItems[] = [
            'stock_id'   => (int) $product['id'],
            'name'       => $product['medicine_name'],
            'quantity'   => $qty,
            'unit'       => $product['sell_type'],
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
            'note'       => $lineNote,
        ];
    }
    if (!$lineItems) $errors[] = 'Ajoutez au moins un produit à l’offre.';

    // Remise
    $discountType = $form['discount_type'];
    if (!in_array($discountType, ['Aucune','Pourcentage','Montant fixe'], true)) $discountType = 'Aucune';
    $discountAmount = 0.0;
    if ($discountType === 'Pourcentage' && is_numeric($form['discount_value'])) {
        $pct = (float) $form['discount_value'];
        if ($pct >= 0 && $pct <= 100) $discountAmount = round($subtotal * $pct / 100, 2);
    } elseif ($discountType === 'Montant fixe' && is_numeric($form['discount_value'])) {
        $fixed = (float) $form['discount_value'];
        if ($fixed >= 0 && $fixed <= $subtotal) $discountAmount = round($fixed, 2);
    }

    $subtotalAfter = max(0, $subtotal - $discountAmount);
    $taxAmount     = (int) round($subtotalAfter * $taxRate / 100);
    $grandTotal    = (int) round($subtotalAfter) + $taxAmount;

    // Save
    if (!$errors) {
        $clientName  = $form['walk_in'] === 1 ? $form['client_name']  : $client['name'];
        $clientPhone = $form['walk_in'] === 1 ? $form['client_phone'] : ($client['phone'] ?? '');
        $clientId    = $form['walk_in'] === 1 ? null                 : (int) $client['id'];
        $productNames = implode(', ', array_column($lineItems, 'name'));

        try {
            if ($isEdit) {
                $db->execute(
                    "UPDATE sales SET
                        client_name = ?, client_id = ?, phone = ?,
                        medicines = ?, products_json = ?, quantity = ?, units_json = ?, actual_price = ?,
                        tot_htva = ?, tva = ?, tot_tvac = ?, total_amount = ?,
                        discount = ?, offer_status = ?, valid_until = ?, offer_notes = ?, date = ?
                     WHERE id = ? AND document_type = 'Devis'",
                    [
                        mb_substr($clientName, 0, 255), $clientId, mb_substr($clientPhone, 0, 15),
                        mb_substr($productNames, 0, 65000),
                        json_encode($lineItems, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                        json_encode(array_column($lineItems, 'quantity'), JSON_THROW_ON_ERROR),
                        json_encode(array_column($lineItems, 'unit'), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                        json_encode(array_column($lineItems, 'unit_price'), JSON_THROW_ON_ERROR),
                        (int) round($subtotalAfter), $taxAmount, $grandTotal, $grandTotal,
                        (int) round($discountAmount), $form['offer_status'],
                        $form['valid_until'] ?: null, $form['offer_notes'] ?: null, $form['offer_date'],
                        $offerId,
                    ]
                );
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Offre mise à jour.'];
            } else {
                $offerNumber = 'OFF-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
                $db->execute(
                    "INSERT INTO sales (
                        invoice_number, operator, agent, client_name, client_id, phone,
                        medicines, products_json, quantity, units_json, actual_price,
                        tot_htva, tva, tva_paid, tot_tvac, debt, total_amount, total_profit,
                        discount, commission, date, status, origin, guard, netcash,
                        payment_method, document_type, offer_status, valid_until, offer_notes
                     ) VALUES (
                        ?, ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?,
                        ?, ?, 0, ?, 0, ?, 0,
                        ?, 0, ?, 'Active', 'Interne', '', 0,
                        'À crédit', 'Devis', ?, ?, ?
                     )",
                    [
                        $offerNumber, $user['nom_utilisateur'], $user['nom_complet'],
                        mb_substr($clientName, 0, 255), $clientId, mb_substr($clientPhone, 0, 15),
                        mb_substr($productNames, 0, 65000),
                        json_encode($lineItems, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                        json_encode(array_column($lineItems, 'quantity'), JSON_THROW_ON_ERROR),
                        json_encode(array_column($lineItems, 'unit'), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                        json_encode(array_column($lineItems, 'unit_price'), JSON_THROW_ON_ERROR),
                        (int) round($subtotalAfter), $taxAmount, $grandTotal, $grandTotal,
                        (int) round($discountAmount), $form['offer_date'],
                        $form['offer_status'], $form['valid_until'] ?: null, $form['offer_notes'] ?: null,
                    ]
                );
                $offerId = (int) $db->lastInsertId();
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Offre ' . $offerNumber . ' créée.'];
            }

            header('Location: ' . app_route_url('offres/apercu') . '?id=' . $offerId);
            exit;

        } catch (Throwable $e) {
            error_log('Offer save failed: ' . $e->getMessage());
            $errors[] = 'L’offre n’a pas pu être enregistrée.';
        }
    }
}

$pageTitle = $isEdit ? 'Modifier l’offre' : 'Nouvelle offre';
?>
<section class="mx-auto max-w-5xl space-y-5">
    <header>
        <a href="<?= $escape(app_route_url('offres/liste')) ?>" class="inline-flex items-center gap-2 text-sm font-medium text-green-800 hover:underline">
            <i data-feather="arrow-left" class="h-4 w-4"></i> Liste des offres
        </a>
        <h1 class="mt-3 text-2xl font-bold text-slate-900"><?= $escape($pageTitle) ?></h1>
        <p class="mt-1 text-sm text-slate-600">
            Une offre commerciale est une proposition de prix envoyée à un client ou prospect. Elle ne modifie ni le stock ni la caisse.
        </p>
    </header>

    <?php if ($errors): ?>
        <div role="alert" class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc space-y-1 pl-5">
                <?php foreach (array_unique($errors) as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" id="offerForm" class="space-y-6 rounded-md border border-slate-200 bg-white p-5 sm:p-6">
        <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_token']) ?>">

        <!-- Client -->
        <fieldset class="space-y-4 border-b border-slate-100 pb-5">
            <legend class="text-sm font-semibold text-slate-800">Client</legend>

            <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                <input type="checkbox" name="walk_in" id="walkInToggle" value="1"
                       class="h-4 w-4 accent-green-700" <?= $form['walk_in'] === 1 ? 'checked' : '' ?>>
                Client de passage / prospect non enregistré
            </label>

            <div id="registeredClientBlock" class="grid gap-4 sm:grid-cols-2">
                <div class="relative space-y-1 text-sm font-medium text-slate-700 sm:col-span-2">
                    <label for="clientSearch">Client enregistré</label>
                    <input type="hidden" name="client_id" id="clientIdInput" value="<?= (int) $form['client_id'] ?>">
                    <input type="text" id="clientSearch" autocomplete="off"
                           placeholder="Tapez le nom, le téléphone ou l’email…"
                           value="<?php
                               $sel = '';
                               foreach ($clients as $c) {
                                   if ((int) $c['id'] === (int) $form['client_id']) {
                                       $sel = $c['name'] . ($c['phone'] ? ' · ' . $c['phone'] : '');
                                       break;
                                   }
                               }
                               echo $escape($sel);
                           ?>"
                           class="w-full rounded-md border border-slate-300 px-3 py-2.5 pr-10 font-normal focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20">
                    <button type="button" id="clientClear"
                            class="absolute right-2 top-[1.85rem] hidden rounded p-1 text-slate-400 hover:bg-slate-100">
                        <i data-feather="x" class="h-4 w-4"></i>
                    </button>
                    <ul id="clientResults" class="absolute z-30 mt-1 hidden max-h-72 w-full overflow-y-auto rounded-md border border-slate-200 bg-white shadow-lg"></ul>
                    <p id="clientHint" class="mt-1 text-xs font-normal text-slate-500"></p>
                </div>
            </div>

            <div id="walkInBlock" class="grid gap-4 sm:grid-cols-2" <?= $form['walk_in'] === 1 ? '' : 'hidden' ?>>
                <label class="space-y-1 text-sm font-medium text-slate-700">
                    Nom
                    <input name="client_name" maxlength="255" value="<?= $escape($form['client_name']) ?>"
                           class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal">
                </label>
                <label class="space-y-1 text-sm font-medium text-slate-700">
                    Téléphone
                    <input name="client_phone" maxlength="15" value="<?= $escape($form['client_phone']) ?>"
                           class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal">
                </label>
            </div>
        </fieldset>

        <!-- En-tête offre -->
        <fieldset class="space-y-4 border-b border-slate-100 pb-5">
            <legend class="text-sm font-semibold text-slate-800">Détails de l’offre</legend>

            <div class="grid gap-4 sm:grid-cols-3">
                <label class="space-y-1 text-sm font-medium text-slate-700">
                    Date de l’offre <span class="text-red-600">*</span>
                    <input type="date" name="offer_date" required value="<?= $escape($form['offer_date']) ?>"
                           class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal">
                </label>
                <label class="space-y-1 text-sm font-medium text-slate-700">
                    Valable jusqu’au
                    <input type="date" name="valid_until" value="<?= $escape($form['valid_until']) ?>"
                           class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal">
                </label>
                <label class="space-y-1 text-sm font-medium text-slate-700">
                    Statut
                    <select name="offer_status" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 font-normal">
                        <?php foreach ($allowedStatuses as $s): ?>
                            <option value="<?= $escape($s) ?>" <?= $form['offer_status'] === $s ? 'selected' : '' ?>><?= $escape($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>

            <label class="block space-y-1 text-sm font-medium text-slate-700">
                Notes / conditions (apparaîtront sur l’offre)
                <textarea name="offer_notes" rows="3" maxlength="2000"
                          placeholder="Ex. acompte 50% à la commande, solde à la livraison. Délai de production 7 jours ouvrables."
                          class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"><?= $escape($form['offer_notes']) ?></textarea>
            </label>
        </fieldset>

        <!-- Articles -->
        <fieldset class="space-y-3">
            <div class="flex items-center justify-between gap-3">
                <legend class="text-sm font-semibold text-slate-800">Articles proposés</legend>
                <button type="button" id="addLine"
                        class="inline-flex items-center gap-2 rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    <i data-feather="plus" class="h-4 w-4"></i> Ajouter un article
                </button>
            </div>

            <div id="orderLines" class="space-y-3">
                <?php foreach ($postedProductIds as $index => $postedId): ?>
                    <div class="order-line grid gap-3 rounded-md border border-slate-200 p-3 sm:grid-cols-[minmax(0,1fr)_7rem_8rem_auto] sm:items-end">
                        <label class="space-y-1 text-sm font-medium text-slate-700">
                            Produit
                            <select name="product_id[]" required class="product-select w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 font-normal">
                                <option value="">Choisir un produit</option>
                                <?php foreach ($products as $p): ?>
                                    <option value="<?= (int) $p['id'] ?>"
                                            data-price="<?= (float) $p['selling_price'] ?>"
                                            data-stock="<?= (float) $p['actual_quantity'] ?>"
                                            <?= (string) $postedId === (string) $p['id'] ? 'selected' : '' ?>>
                                        <?= $escape($p['medicine_name']) ?> · <?= number_format((float) $p['selling_price'], 0, ',', ' ') ?> FBu
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label class="space-y-1 text-sm font-medium text-slate-700">
                            Quantité
                            <input name="quantity[]" type="number" min="0.01" step="0.01" required
                                   value="<?= $escape($postedQuantities[$index] ?? '1') ?>"
                                   class="quantity-input w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal">
                        </label>
                        <label class="space-y-1 text-sm font-medium text-slate-700">
                            Prix unitaire
                            <input name="unit_price[]" type="number" min="0" step="1"
                                   value="<?= $escape($postedUnitPrices[$index] ?? '') ?>"
                                   placeholder="auto"
                                   class="unit-price w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal">
                        </label>
                        <button type="button" class="remove-line inline-flex h-10 items-center justify-center rounded-md border border-slate-300 px-3 text-slate-600 hover:border-red-300 hover:text-red-700">
                            <i data-feather="trash-2" class="h-4 w-4"></i>
                        </button>
                        <label class="space-y-1 text-sm font-medium text-slate-700 sm:col-span-4">
                            Note
                            <input name="line_note[]" maxlength="255" value="<?= $escape($postedLineNotes[$index] ?? '') ?>"
                                   placeholder="Ex. découpe A3, couleur Pantone 186C"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal">
                        </label>
                        <div class="text-sm sm:col-span-4">
                            <span class="block font-medium text-slate-700">Sous-total ligne</span>
                            <output class="line-total block rounded-md bg-slate-50 px-3 py-2.5 text-right font-semibold text-slate-800">—</output>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <!-- Remise -->
        <fieldset class="grid gap-4 border-t border-slate-100 pt-5 sm:grid-cols-2">
            <legend class="text-sm font-semibold text-slate-800">Remise</legend>
            <label class="space-y-1 text-sm font-medium text-slate-700">
                Type
                <select name="discount_type" id="discountType" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 font-normal">
                    <?php foreach (['Aucune','Pourcentage','Montant fixe'] as $d): ?>
                        <option value="<?= $escape($d) ?>" <?= $form['discount_type'] === $d ? 'selected' : '' ?>><?= $escape($d) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="space-y-1 text-sm font-medium text-slate-700" id="discountValueBlock">
                Valeur
                <input name="discount_value" id="discountValue" type="number" min="0" step="0.01"
                       value="<?= $escape($form['discount_value']) ?>"
                       class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal">
            </label>
        </fieldset>

        <div class="ml-auto max-w-sm space-y-2 border-t border-slate-200 pt-4 text-sm">
            <div class="flex justify-between text-slate-600"><span>Sous-total HT</span><span id="subtotalDisplay">0 FBu</span></div>
            <div class="flex justify-between text-slate-600"><span>Remise</span><span id="discountDisplay">− 0 FBu</span></div>
            <div class="flex justify-between text-slate-600"><span>TVA (<?= number_format($taxRate, 0) ?>%)</span><span id="taxDisplay">0 FBu</span></div>
            <div class="flex justify-between border-t border-slate-200 pt-2 text-base font-bold text-slate-900"><span>Total TTC</span><span id="grandTotalDisplay">0 FBu</span></div>
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:justify-end">
            <a href="<?= $escape(app_route_url('offres/liste')) ?>"
               class="inline-flex justify-center rounded-md border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Annuler</a>
            <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-md bg-green-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-800">
                <i data-feather="save" class="h-4 w-4"></i> <?= $isEdit ? 'Enregistrer les modifications' : 'Créer l’offre' ?>
            </button>
        </div>
    </form>
</section>

<template id="orderLineTemplate">
    <div class="order-line grid gap-3 rounded-md border border-slate-200 p-3 sm:grid-cols-[minmax(0,1fr)_7rem_8rem_auto] sm:items-end">
        <label class="space-y-1 text-sm font-medium text-slate-700">
            Produit
            <select name="product_id[]" required class="product-select w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 font-normal">
                <option value="">Choisir un produit</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= (int) $p['id'] ?>" data-price="<?= (float) $p['selling_price'] ?>">
                        <?= $escape($p['medicine_name']) ?> · <?= number_format((float) $p['selling_price'], 0, ',', ' ') ?> FBu
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="space-y-1 text-sm font-medium text-slate-700">
            Quantité
            <input name="quantity[]" type="number" min="0.01" step="0.01" required value="1"
                   class="quantity-input w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal">
        </label>
        <label class="space-y-1 text-sm font-medium text-slate-700">
            Prix unitaire
            <input name="unit_price[]" type="number" min="0" step="1" placeholder="auto"
                   class="unit-price w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal">
        </label>
        <button type="button" class="remove-line inline-flex h-10 items-center justify-center rounded-md border border-slate-300 px-3 text-slate-600 hover:border-red-300 hover:text-red-700">
            <i data-feather="trash-2" class="h-4 w-4"></i>
        </button>
        <label class="space-y-1 text-sm font-medium text-slate-700 sm:col-span-4">
            Note
            <input name="line_note[]" maxlength="255" placeholder="Ex. découpe A3, couleur Pantone 186C"
                   class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal">
        </label>
        <div class="text-sm sm:col-span-4">
            <span class="block font-medium text-slate-700">Sous-total ligne</span>
            <output class="line-total block rounded-md bg-slate-50 px-3 py-2.5 text-right font-semibold text-slate-800">—</output>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    var form     = document.getElementById('offerForm');
    var lines    = document.getElementById('orderLines');
    var template = document.getElementById('orderLineTemplate');
    var addBtn   = document.getElementById('addLine');

    if (!form || !lines || !template) return;

    var taxRate = <?= json_encode($taxRate, JSON_THROW_ON_ERROR) ?>;

    function formatMoney(v) {
        return new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(v) + ' FBu';
    }
    function feath() { if (window.feather) try { feather.replace(); } catch (e) {} }
    function escapeHtml(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
        });
    }

    // Autocomplete client
    var clientList = <?= json_encode(array_map(static function ($c) {
        return ['id' => (int) $c['id'], 'name' => $c['name'], 'phone' => $c['phone'] ?? '', 'email' => $c['email'] ?? '', 'status' => $c['status_client'] ?? ''];
    }, $clients), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    var clientSearch   = document.getElementById('clientSearch');
    var clientIdInput  = document.getElementById('clientIdInput');
    var clientResults  = document.getElementById('clientResults');
    var clientClearBtn = document.getElementById('clientClear');

    function clientMatches(q) {
        q = q.toLowerCase().trim();
        if (!q) return [];
        return clientList.filter(function (c) {
            return (c.name||'').toLowerCase().indexOf(q) !== -1 || (c.phone||'').toLowerCase().indexOf(q) !== -1 || (c.email||'').toLowerCase().indexOf(q) !== -1;
        }).slice(0, 20);
    }
    function renderClientResults(matches) {
        clientResults.innerHTML = '';
        if (!matches.length) {
            var empty = document.createElement('li');
            empty.className = 'px-3 py-3 text-sm text-slate-500';
            empty.textContent = 'Aucun client trouvé.';
            clientResults.appendChild(empty);
        } else {
            matches.forEach(function (c) {
                var li = document.createElement('li');
                li.className = 'cursor-pointer px-3 py-2 text-sm hover:bg-green-50';
                li.innerHTML = '<div class="flex justify-between gap-2"><span class="font-medium text-slate-800">' + escapeHtml(c.name) + '</span>' + (c.phone ? '<span class="text-xs text-slate-500">' + escapeHtml(c.phone) + '</span>' : '') + '</div>';
                li.addEventListener('mousedown', function (ev) { ev.preventDefault(); selectClient(c); });
                clientResults.appendChild(li);
            });
        }
        clientResults.classList.remove('hidden');
    }
    function selectClient(c) {
        clientIdInput.value = c.id;
        clientSearch.value  = c.name + (c.phone ? ' · ' + c.phone : '');
        clientResults.classList.add('hidden');
        if (clientClearBtn) clientClearBtn.classList.remove('hidden');
    }
    function clearClient() {
        clientIdInput.value = '0'; clientSearch.value = ''; clientResults.classList.add('hidden');
        if (clientClearBtn) clientClearBtn.classList.add('hidden');
        clientSearch.focus();
    }
    if (clientSearch) {
        clientSearch.addEventListener('input', function () {
            clientIdInput.value = '0';
            if (clientClearBtn) clientClearBtn.classList.add('hidden');
            if (clientSearch.value.trim() === '') { clientResults.classList.add('hidden'); return; }
            renderClientResults(clientMatches(clientSearch.value));
        });
        clientSearch.addEventListener('blur', function () {
            setTimeout(function () { clientResults.classList.add('hidden'); }, 150);
        });
        if (clientClearBtn) clientClearBtn.addEventListener('click', clearClient);
        if (parseInt(clientIdInput.value, 10) > 0) {
            var found = clientList.find(function (c) { return c.id === parseInt(clientIdInput.value, 10); });
            if (found) selectClient(found);
        }
    }

    // Walk-in
    var walkIn = document.getElementById('walkInToggle');
    var walkInBlock = document.getElementById('walkInBlock');
    var regBlock = document.getElementById('registeredClientBlock');
    function syncClientBlocks() {
        if (walkIn.checked) { walkInBlock.removeAttribute('hidden'); regBlock.setAttribute('hidden', ''); }
        else                { walkInBlock.setAttribute('hidden', ''); regBlock.removeAttribute('hidden'); }
    }
    if (walkIn) { walkIn.addEventListener('change', syncClientBlocks); syncClientBlocks(); }

    // Discount
    var discountType = document.getElementById('discountType');
    var discountBlock = document.getElementById('discountValueBlock');
    var discountValue = document.getElementById('discountValue');
    function toggleDiscount() {
        if (discountType.value === 'Aucune') discountBlock.setAttribute('hidden', '');
        else discountBlock.removeAttribute('hidden');
    }
    if (discountType) discountType.addEventListener('change', function () { toggleDiscount(); recalculate(); });
    if (discountValue) discountValue.addEventListener('input', recalculate);
    toggleDiscount();

    // Recalc
    function recalculate() {
        var subtotal = 0;
        lines.querySelectorAll('.order-line').forEach(function (line) {
            var select = line.querySelector('.product-select');
            var qtyInput = line.querySelector('.quantity-input');
            var priceIn = line.querySelector('.unit-price');
            if (!select || !qtyInput) return;
            var qty = parseFloat(qtyInput.value) || 0;
            var opt = select.options[select.selectedIndex];
            var dataPrice = opt ? parseFloat(opt.dataset.price || '0') : 0;
            var unitPrice = (priceIn && priceIn.value !== '') ? parseFloat(priceIn.value) || 0 : dataPrice;
            var lineTotal = qty * unitPrice;
            subtotal += lineTotal;
            var out = line.querySelector('.line-total');
            if (out) out.textContent = select.value ? formatMoney(lineTotal) : '—';
        });
        var dtype = discountType ? discountType.value : 'Aucune';
        var dAmt = 0;
        if (discountValue) {
            if (dtype === 'Pourcentage' && discountValue.value !== '') {
                var pct = parseFloat(discountValue.value);
                if (pct >= 0 && pct <= 100) dAmt = subtotal * pct / 100;
            } else if (dtype === 'Montant fixe' && discountValue.value !== '') {
                var f = parseFloat(discountValue.value);
                if (f >= 0 && f <= subtotal) dAmt = f;
            }
        }
        var after = Math.max(0, subtotal - dAmt);
        var tax = Math.round(after * taxRate / 100);
        document.getElementById('subtotalDisplay').textContent = formatMoney(subtotal);
        document.getElementById('discountDisplay').textContent = '− ' + formatMoney(dAmt);
        document.getElementById('taxDisplay').textContent = formatMoney(tax);
        document.getElementById('grandTotalDisplay').textContent = formatMoney(after + tax);
    }

    function addLine() {
        lines.appendChild(template.content.cloneNode(true));
        feath(); recalculate();
    }
    if (addBtn) addBtn.addEventListener('click', function (e) { e.preventDefault(); addLine(); });
    lines.addEventListener('input', recalculate);
    lines.addEventListener('change', recalculate);
    lines.addEventListener('click', function (e) {
        var btn = e.target.closest('.remove-line');
        if (!btn) return;
        var all = lines.querySelectorAll('.order-line');
        if (all.length > 1) btn.closest('.order-line').remove();
        else {
            var o = all[0];
            o.querySelector('.product-select').value = '';
            o.querySelector('.quantity-input').value = '1';
            o.querySelector('.unit-price').value = '';
            o.querySelector('.line-total').textContent = '—';
        }
        recalculate();
    });

    recalculate();
});
</script>