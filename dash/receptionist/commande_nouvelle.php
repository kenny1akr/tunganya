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

$db     = Database::getInstance();
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$errors = [];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// -----------------------------------------------------------------
// Helpers locaux
// -----------------------------------------------------------------
$validDate = static function (string $value): bool {
    if ($value === '') return false;
    $d = DateTime::createFromFormat('!Y-m-d', $value);
    return $d !== false && $d->format('Y-m-d') === $value;
};

$settings        = $db->fetch("SELECT tva_default FROM app_settings WHERE id = 1 LIMIT 1") ?: [];
$taxRate         = (float) ($settings['tva_default'] ?? 18.00);
$allowedPriority = ['Basse', 'Moyenne', 'Haute', 'Critique'];
$allowedTerms    = ['À la commande', 'À la livraison', '30 jours', 'À crédit'];
$allowedDeposit  = ['Espèces', 'Virement bancaire'];

// -----------------------------------------------------------------
// Listes pour le formulaire
// -----------------------------------------------------------------
$clients = $db->fetchAll(
    "SELECT id, name, phone, email, status_client, annif
     FROM partners
     WHERE category = 'client'
     ORDER BY name"
);

$products = $db->fetchAll(
    "SELECT id, medicine_name, actual_quantity, sell_type, selling_price
     FROM stock
     WHERE status = 'Disponible' AND actual_quantity > 0
     ORDER BY medicine_name"
);

$banks = $db->fetchAll(
    "SELECT id, bank_name, account_number
     FROM banks
     WHERE currency = 'FBu'
     ORDER BY bank_name"
);

$assignees = $db->fetchAll(
    "SELECT user_name, f_name, l_name, category
     FROM users
     WHERE category IN ('magasinier', 'travailleur')
       AND status = 'Disponible'
     ORDER BY category ASC, f_name ASC, l_name ASC"
);

// -----------------------------------------------------------------
// Valeurs du formulaire
// -----------------------------------------------------------------
$form = [
    'client_id'              => 0,
    'walk_in'                => 0,
    'client_name'            => '',
    'client_phone'           => '',
    'client_reference'       => '',
    'order_date'             => date('Y-m-d'),
    'expected_delivery_date' => '',
    'delivery_address'       => '',
    'priority'               => 'Moyenne',
    'assigned_to'            => '',
    'notes'                  => '',
    'discount_type'          => 'Aucune',
    'discount_value'         => '',
    'payment_terms'          => 'À la livraison',
    'deposit_amount'         => '',
    'deposit_method'         => 'Espèces',
    'bank_id'                => 0,
];
$postedProductIds = [''];
$postedQuantities = ['1'];
$postedUnitPrices = [''];
$postedLineNotes  = [''];

// -----------------------------------------------------------------
// TRAITEMENT POST
// -----------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {

    // --- 1. Récupération brute
    foreach ($form as $key => $default) {
        if ($key === 'walk_in') {
            $form[$key] = !empty($_POST['walk_in']) ? 1 : 0;
        } else {
            $form[$key] = trim((string) ($_POST[$key] ?? $default));
        }
    }
    $form['client_id']      = (int) $form['client_id'];
    $form['bank_id']        = (int) $form['bank_id'];
    $form['discount_value'] = str_replace(',', '.', $form['discount_value']);
    $form['deposit_amount'] = str_replace(',', '.', $form['deposit_amount']);

    $postedProductIds = array_values((array) ($_POST['product_id'] ?? ['']));
    $postedQuantities = array_values((array) ($_POST['quantity']     ?? ['1']));
    $postedUnitPrices = array_values((array) ($_POST['unit_price']   ?? ['']));
    $postedLineNotes  = array_values((array) ($_POST['line_note']    ?? ['']));

    // --- 2. CSRF
    if (!hash_equals($_SESSION['csrf_token'], (string) ($_POST['csrf_token'] ?? ''))) {
        $errors[] = 'Le formulaire a expiré. Rechargez la page puis réessayez.';
    }

    // --- 3. Client
    $client = null;
    if ($form['walk_in'] === 1) {
        if ($form['client_name'] === '' || mb_strlen($form['client_name']) > 255) {
            $errors[] = 'Indiquez le nom du client (255 caractères max).';
        }
        if (mb_strlen($form['client_phone']) > 15) {
            $errors[] = 'Le numéro de téléphone est trop long.';
        }
    } else {
        $client = $db->fetch(
            "SELECT id, name, phone, status_client, annif
             FROM partners WHERE id = ? AND category = 'client' LIMIT 1",
            [$form['client_id']]
        );
        if (!$client) {
            $errors[] = 'Sélectionnez un client enregistré ou cochez « Client de passage ».';
        }
    }

    // --- 4. En-tête
    if (!$validDate($form['order_date'])) {
        $errors[] = 'La date de commande est invalide.';
    }
    if ($form['expected_delivery_date'] !== '' && !$validDate($form['expected_delivery_date'])) {
        $errors[] = 'La date de livraison prévue est invalide.';
    }
    if ($form['expected_delivery_date'] !== ''
        && $validDate($form['order_date'])
        && $form['expected_delivery_date'] < $form['order_date']) {
        $errors[] = 'La livraison prévue ne peut pas précéder la date de commande.';
    }
    if (!in_array($form['priority'], $allowedPriority, true)) {
        $errors[] = 'Priorité invalide.';
    }
    if (mb_strlen($form['notes']) > 500) {
        $errors[] = 'Les notes ne peuvent pas dépasser 500 caractères.';
    }
    if (mb_strlen($form['client_reference']) > 100) {
        $errors[] = 'La référence client ne peut pas dépasser 100 caractères.';
    }

    // --- 5. Assignation
    $assignee = null;
    if ($form['assigned_to'] === '') {
        $errors[] = 'Choisissez la personne qui préparera la commande.';
    } else {
        $assignee = $db->fetch(
            "SELECT user_name, f_name, l_name, category
             FROM users
             WHERE user_name = ?
               AND category IN ('magasinier', 'travailleur')
               AND status = 'Disponible'
             LIMIT 1",
            [$form['assigned_to']]
        );
        if (!$assignee) {
            $errors[] = 'L’utilisateur assigné est introuvable ou indisponible.';
        }
    }

    // --- 6. Termes de paiement + acompte
    if (!in_array($form['payment_terms'], $allowedTerms, true)) {
        $errors[] = 'Termes de paiement invalides.';
    }
    if (!in_array($form['deposit_method'], $allowedDeposit, true)) {
        $errors[] = 'Mode d’acompte invalide.';
    }
    $depositAmount = 0.0;
    if ($form['deposit_amount'] !== '') {
        if (!is_numeric($form['deposit_amount']) || (float) $form['deposit_amount'] < 0) {
            $errors[] = 'Le montant de l’acompte doit être un nombre positif.';
        } else {
            $depositAmount = (float) $form['deposit_amount'];
        }
    }

    // --- 7. Remise
    $discountType = $form['discount_type'];
    if (!in_array($discountType, ['Aucune', 'Pourcentage', 'Montant fixe'], true)) {
        $discountType = 'Aucune';
    }

    // --- 8. Lignes
    $lineItems      = [];
    $seenProductIds = [];
    $subtotal       = 0.0;
    $rowCount       = max(count($postedProductIds), count($postedQuantities));

    for ($i = 0; $i < $rowCount; $i++) {
        $productId  = (int) ($postedProductIds[$i] ?? 0);
        $qtyInput   = str_replace(',', '.', trim((string) ($postedQuantities[$i] ?? '')));
        $priceInput = str_replace(',', '.', trim((string) ($postedUnitPrices[$i] ?? '')));
        $lineNote   = trim((string) ($postedLineNotes[$i] ?? ''));

        if ($productId <= 0 && $qtyInput === '' && $priceInput === '' && $lineNote === '') {
            continue;
        }
        if ($productId <= 0 || !is_numeric($qtyInput)) {
            $errors[] = 'Chaque ligne doit contenir un produit et une quantité valide.';
            continue;
        }
        $quantity = (float) $qtyInput;
        if (!is_finite($quantity) || $quantity <= 0) {
            $errors[] = 'Les quantités doivent être supérieures à zéro.';
            continue;
        }
        if (isset($seenProductIds[$productId])) {
            $errors[] = 'Un même produit ne peut apparaître qu’une fois.';
            continue;
        }
        $seenProductIds[$productId] = true;

        $product = $db->fetch(
            "SELECT id, medicine_name, actual_quantity, sell_type, selling_price
             FROM stock WHERE id = ? AND status = 'Disponible' LIMIT 1",
            [$productId]
        );
        if (!$product) {
            $errors[] = 'Un des produits sélectionnés n’est plus disponible.';
            continue;
        }
        if ($quantity > (float) $product['actual_quantity']) {
            $errors[] = 'Quantité demandée pour « ' . $product['medicine_name']
                      . ' » > stock disponible (' . $product['actual_quantity'] . ').';
            continue;
        }

        $defaultPrice = (float) $product['selling_price'];
        $unitPrice    = ($priceInput !== '' && is_numeric($priceInput) && (float) $priceInput >= 0)
                        ? (float) $priceInput
                        : $defaultPrice;

        if (mb_strlen($lineNote) > 255) {
            $errors[] = 'La note sur la ligne « ' . $product['medicine_name'] . ' » est trop longue.';
            continue;
        }

        $lineTotal  = round($quantity * $unitPrice, 2);
        $subtotal  += $lineTotal;

        $lineItems[] = [
            'stock_id'   => (int) $product['id'],
            'name'       => $product['medicine_name'],
            'quantity'   => $quantity,
            'unit'       => $product['sell_type'],
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
            'note'       => $lineNote,
        ];
    }

    if (!$lineItems) {
        $errors[] = 'Ajoutez au moins un produit à la commande.';
    }

    // --- 9. Remise chiffrée
    $discountAmount = 0.0;
    if ($discountType === 'Pourcentage' && is_numeric($form['discount_value'])) {
        $pct = (float) $form['discount_value'];
        if ($pct < 0 || $pct > 100) {
            $errors[] = 'Le pourcentage de remise doit être entre 0 et 100.';
        } else {
            $discountAmount = round($subtotal * $pct / 100, 2);
        }
    } elseif ($discountType === 'Montant fixe' && is_numeric($form['discount_value'])) {
        $fixed = (float) $form['discount_value'];
        if ($fixed < 0) {
            $errors[] = 'La remise fixe doit être positive.';
        } elseif ($fixed > $subtotal) {
            $errors[] = 'La remise ne peut pas dépasser le sous-total.';
        } else {
            $discountAmount = round($fixed, 2);
        }
    }

    $subtotalAfterDiscount = max(0, $subtotal - $discountAmount);
    $taxAmount             = (int) round($subtotalAfterDiscount * $taxRate / 100);
    $grandTotal            = (int) round($subtotalAfterDiscount) + $taxAmount;

    if ($depositAmount > $grandTotal) {
        $errors[] = 'L’acompte ne peut pas dépasser le total de la commande.';
    }
    if ($depositAmount > 0 && $form['deposit_method'] === 'Virement bancaire') {
        if ($form['bank_id'] <= 0) {
            $errors[] = 'Choisissez le compte bancaire qui reçoit l’acompte.';
        } else {
            $bank = $db->fetch("SELECT id, bank_name FROM banks WHERE id = ? AND currency = 'FBu' LIMIT 1", [$form['bank_id']]);
            if (!$bank) {
                $errors[] = 'Le compte bancaire choisi est introuvable.';
            }
        }
    }

    // --- 10. Insertion dans `tasks`
    if (!$errors) {
        $clientName  = $form['walk_in'] === 1 ? $form['client_name']  : $client['name'];
        $clientPhone = $form['walk_in'] === 1 ? $form['client_phone'] : ($client['phone'] ?? '');
        $clientId    = $form['walk_in'] === 1 ? null                 : (int) $client['id'];

        $ticketNo = 'CMD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

        try {
            $taskId = $db->insert(
                "INSERT INTO tasks (
                    ticket_no, client, client_id, walk_in, client_reference,
                    category, priority, assigned_to, contributor,
                    created_by, created_at, order_date, updated_at,
                    due_date, delivery_address,
                    description, items_json,
                    subtotal, discount_type, discount_value, discount_amount,
                    tax_rate, tax_amount, total_amount,
                    payment_terms, deposit_amount, deposit_method, bank_id,
                    status, delivery_status, sound_status
                 ) VALUES (
                    ?, ?, ?, ?, ?,
                    'Commande', ?, ?, NULL,
                    ?, CURDATE(), ?, NOW(),
                    ?, ?,
                    ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?, ?,
                    'En cours', 'En préparation', 1
                 )",
                [
                    $ticketNo,
                    mb_substr($clientName, 0, 255),
                    $clientId,
                    $form['walk_in'],
                    $form['client_reference'] ?: null,

                    $form['priority'],
                    $assignee['user_name'],
                    $user['nom_utilisateur'],

                    $form['order_date'],
                    $form['expected_delivery_date'] ?: null,
                    $form['delivery_address'] ?: null,

                    $form['notes'] ?: null,
                    json_encode($lineItems, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),

                    (int) round($subtotal),
                    $discountType,
                    (float) $form['discount_value'] ?: 0,
                    (int) round($discountAmount),
                    $taxRate,
                    $taxAmount,
                    $grandTotal,

                    $form['payment_terms'],
                    (int) round($depositAmount),
                    $form['deposit_method'] ?: null,
                    $form['bank_id'] ?: null,
                ]
            );

            $_SESSION['flash_message'] = [
                'type' => 'success',
                'text' => 'Commande ' . $ticketNo . ' enregistrée et assignée à '
                        . trim($assignee['f_name'] . ' ' . $assignee['l_name']) . '.',
            ];

            header('Location: ' . app_route_url('commandes/clients'));
            exit;

        } catch (Throwable $error) {
            error_log('Order task creation failed: ' . $error->getMessage());
            $errors[] = 'La commande n’a pas pu être enregistrée. Aucune donnée n’a été conservée.';
        }
    }
}
?>
<section class="mx-auto max-w-5xl space-y-5">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="<?= $escape(app_route_url('commandes/clients')) ?>" class="inline-flex items-center gap-2 text-sm font-medium text-green-800 hover:underline">
                <i data-feather="arrow-left" class="h-4 w-4"></i> Commandes clients
            </a>
            <h1 class="mt-3 text-2xl font-bold text-slate-900">Nouvelle commande</h1>
            <p class="mt-1 text-sm text-slate-600">
                Enregistre une commande à préparer. Le stock n’est pas décrémenté et aucune facture n’est générée. La commande est assignée immédiatement au magasinier ou au travailleur choisi.
            </p>
        </div>
    </header>

    <?php if ($errors): ?>
        <div role="alert" class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc space-y-1 pl-5">
                <?php foreach (array_unique($errors) as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!$products): ?>
        <div class="rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            Aucun produit disponible en stock. Une commande ne peut être créée qu’avec des produits disponibles.
        </div>
    <?php endif; ?>

    <?php if (!$assignees): ?>
        <div class="rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            Aucun magasinier ni travailleur disponible. Ajoutez d’abord un utilisateur avant de créer une commande.
        </div>
    <?php endif; ?>

    <form method="post" id="orderForm" class="space-y-6 rounded-md border border-slate-200 bg-white p-5 sm:p-6">
        <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_token']) ?>">

        <!-- 1. CLIENT -->
        <fieldset class="space-y-4 border-b border-slate-100 pb-5">
            <legend class="text-sm font-semibold text-slate-800">Client</legend>

            <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                <input type="checkbox" name="walk_in" id="walkInToggle" value="1"
                       class="h-4 w-4 accent-green-700"
                       <?= $form['walk_in'] === 1 ? 'checked' : '' ?>>
                Client de passage (non enregistré)
            </label>

            <div id="registeredClientBlock" class="grid gap-4 sm:grid-cols-2">
                <div class="relative space-y-1 text-sm font-medium text-slate-700 sm:col-span-2">
                    <label for="clientSearch">Client enregistré</label>

                    <input type="hidden" name="client_id" id="clientIdInput"
                           value="<?= (int) $form['client_id'] ?>">

                    <input type="text"
                           id="clientSearch"
                           autocomplete="off"
                           placeholder="Tapez le nom, le téléphone ou l’email…"
                           value="<?php
                               $selectedLabel = '';
                               foreach ($clients as $c) {
                                   if ((int) $c['id'] === (int) $form['client_id']) {
                                       $selectedLabel = $c['name'] . ($c['phone'] ? ' · ' . $c['phone'] : '');
                                       break;
                                   }
                               }
                               echo $escape($selectedLabel);
                           ?>"
                           class="w-full rounded-md border border-slate-300 px-3 py-2.5 pr-10 font-normal focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20">

                    <button type="button" id="clientClear"
                            class="absolute right-2 top-[1.85rem] hidden rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                            aria-label="Effacer la sélection">
                        <i data-feather="x" class="h-4 w-4"></i>
                    </button>

                    <ul id="clientResults"
                        class="absolute z-30 mt-1 hidden max-h-72 w-full overflow-y-auto rounded-md border border-slate-200 bg-white shadow-lg"
                        role="listbox"></ul>

                    <p id="clientHint" class="mt-1 text-xs font-normal text-slate-500"></p>
                </div>
            </div>

            <div id="walkInBlock" class="grid gap-4 sm:grid-cols-2" <?= $form['walk_in'] === 1 ? '' : 'hidden' ?>>
                <label class="space-y-1 text-sm font-medium text-slate-700">
                    Nom du client <span class="text-red-600">*</span>
                    <input name="client_name" maxlength="255" value="<?= $escape($form['client_name']) ?>"
                           class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal">
                </label>
                <label class="space-y-1 text-sm font-medium text-slate-700">
                    Téléphone
                    <input name="client_phone" maxlength="15" value="<?= $escape($form['client_phone']) ?>"
                           class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal">
                </label>
            </div>

            <label class="block space-y-1 text-sm font-medium text-slate-700">
                Référence client (facultatif)
                <input name="client_reference" maxlength="100" value="<?= $escape($form['client_reference']) ?>"
                       placeholder="Ex. bon de commande, réf. téléphonique"
                       class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal">
            </label>
        </fieldset>

        <!-- 2. EN-TÊTE -->
        <fieldset class="space-y-4 border-b border-slate-100 pb-5">
            <legend class="text-sm font-semibold text-slate-800">Informations de la commande</legend>

            <div class="grid gap-4 sm:grid-cols-3">
                <label class="space-y-1 text-sm font-medium text-slate-700">
                    Date de commande <span class="text-red-600">*</span>
                    <input type="date" name="order_date" required value="<?= $escape($form['order_date']) ?>"
                           class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal">
                </label>
                <label class="space-y-1 text-sm font-medium text-slate-700">
                    Livraison prévue
                    <input type="date" name="expected_delivery_date" value="<?= $escape($form['expected_delivery_date']) ?>"
                           class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal">
                </label>
                <label class="space-y-1 text-sm font-medium text-slate-700">
                    Priorité
                    <select name="priority" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 font-normal">
                        <?php foreach ($allowedPriority as $p): ?>
                            <option value="<?= $escape($p) ?>" <?= $form['priority'] === $p ? 'selected' : '' ?>><?= $escape($p) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="space-y-1 text-sm font-medium text-slate-700">
                    Assignée à <span class="text-red-600">*</span>
                    <select name="assigned_to" required class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 font-normal">
                        <option value="">— Choisir —</option>
                        <?php foreach ($assignees as $a): ?>
                            <?php
                                $label = trim(($a['f_name'] ?? '') . ' ' . ($a['l_name'] ?? ''));
                                $badge = $a['category'] === 'magasinier' ? 'Magasinier' : 'Travailleur';
                            ?>
                            <option value="<?= $escape($a['user_name']) ?>" <?= $form['assigned_to'] === $a['user_name'] ? 'selected' : '' ?>>
                                <?= $escape($label ?: $a['user_name']) ?> — <?= $escape($badge) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="space-y-1 text-sm font-medium text-slate-700">
                    Adresse de livraison
                    <input name="delivery_address" maxlength="255" value="<?= $escape($form['delivery_address']) ?>"
                           placeholder="Ex. Rohero I, Av. de la Mission, n°12"
                           class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal">
                </label>
            </div>

            <label class="block space-y-1 text-sm font-medium text-slate-700">
                Notes internes
                <textarea name="notes" rows="2" maxlength="500"
                          placeholder="Instructions particulières, exigences client…"
                          class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"><?= $escape($form['notes']) ?></textarea>
            </label>
        </fieldset>

        <!-- 3. ARTICLES -->
        <fieldset class="space-y-3">
            <div class="flex items-center justify-between gap-3">
                <legend class="text-sm font-semibold text-slate-800">Articles</legend>
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
                                        <?= $escape($p['medicine_name']) ?> · <?= number_format((float) $p['selling_price'], 0, ',', ' ') ?> FBu · stock <?= $escape($p['actual_quantity']) ?> <?= $escape($p['sell_type']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="stock-hint mt-1 block text-xs font-normal text-slate-500"></span>
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
                        <button type="button" class="remove-line inline-flex h-10 items-center justify-center rounded-md border border-slate-300 px-3 text-slate-600 hover:border-red-300 hover:text-red-700"
                                aria-label="Retirer cet article" title="Retirer">
                            <i data-feather="trash-2" class="h-4 w-4"></i>
                        </button>
                        <label class="space-y-1 text-sm font-medium text-slate-700 sm:col-span-4">
                            Note sur la ligne
                            <input name="line_note[]" maxlength="255" value="<?= $escape($postedLineNotes[$index] ?? '') ?>"
                                   placeholder="Ex. découpe A3, couleur Pantone 186C, finition brillante"
                                   class="line-note w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal">
                        </label>
                        <div class="text-sm sm:col-span-4">
                            <span class="block font-medium text-slate-700">Sous-total ligne</span>
                            <output class="line-total block rounded-md bg-slate-50 px-3 py-2.5 text-right font-semibold text-slate-800">—</output>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <!-- 4. REMISE -->
        <fieldset class="grid gap-4 border-t border-slate-100 pt-5 sm:grid-cols-2">
            <legend class="text-sm font-semibold text-slate-800">Remise</legend>
            <label class="space-y-1 text-sm font-medium text-slate-700">
                Type de remise
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

        <!-- 5. PAIEMENT -->
        <fieldset class="space-y-4 border-t border-slate-100 pt-5">
            <legend class="text-sm font-semibold text-slate-800">Paiement</legend>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="space-y-1 text-sm font-medium text-slate-700">
                    Conditions
                    <select name="payment_terms" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 font-normal">
                        <?php foreach ($allowedTerms as $t): ?>
                            <option value="<?= $escape($t) ?>" <?= $form['payment_terms'] === $t ? 'selected' : '' ?>><?= $escape($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="space-y-1 text-sm font-medium text-slate-700">
                    Acompte reçu (FBu) — facultatif
                    <input name="deposit_amount" id="depositAmount" type="number" min="0" step="1"
                           value="<?= $escape($form['deposit_amount']) ?>" placeholder="0"
                           class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal">
                </label>
            </div>

            <div id="depositDetails" class="grid gap-4 sm:grid-cols-2" <?= $form['deposit_amount'] !== '' && (float) $form['deposit_amount'] > 0 ? '' : 'hidden' ?>>
                <label class="space-y-1 text-sm font-medium text-slate-700">
                    Mode d’acompte
                    <select name="deposit_method" id="depositMethod" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 font-normal">
                        <?php foreach ($allowedDeposit as $d): ?>
                            <option value="<?= $escape($d) ?>" <?= $form['deposit_method'] === $d ? 'selected' : '' ?>><?= $escape($d) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="space-y-1 text-sm font-medium text-slate-700" id="bankField">
                    Compte bancaire
                    <select name="bank_id" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 font-normal">
                        <option value="0">— Choisir —</option>
                        <?php foreach ($banks as $b): ?>
                            <option value="<?= (int) $b['id'] ?>" <?= $form['bank_id'] === (int) $b['id'] ? 'selected' : '' ?>>
                                <?= $escape($b['bank_name']) ?><?= $b['account_number'] ? ' · ' . $escape($b['account_number']) : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!$banks): ?>
                        <span class="block text-xs font-normal text-amber-700">Aucun compte FBu configuré.</span>
                    <?php endif; ?>
                </label>
            </div>
        </fieldset>

        <!-- 6. ACTION -->
        <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:justify-end">
            <a href="<?= $escape(app_route_url('commandes/clients')) ?>"
               class="inline-flex justify-center rounded-md border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Annuler</a>
            <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-md bg-green-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-800">
                <i data-feather="clipboard" class="h-4 w-4"></i> Enregistrer &amp; préparer
            </button>
        </div>
    </form>
</section>

<!-- ========================================================= -->
<!-- TEMPLATE ligne                                            -->
<!-- ========================================================= -->
<template id="orderLineTemplate">
    <div class="order-line grid gap-3 rounded-md border border-slate-200 p-3 sm:grid-cols-[minmax(0,1fr)_7rem_8rem_auto] sm:items-end">
        <label class="space-y-1 text-sm font-medium text-slate-700">
            Produit
            <select name="product_id[]" required class="product-select w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 font-normal">
                <option value="">Choisir un produit</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= (int) $p['id'] ?>"
                            data-price="<?= (float) $p['selling_price'] ?>"
                            data-stock="<?= (float) $p['actual_quantity'] ?>">
                        <?= $escape($p['medicine_name']) ?> · <?= number_format((float) $p['selling_price'], 0, ',', ' ') ?> FBu · stock <?= $escape($p['actual_quantity']) ?> <?= $escape($p['sell_type']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="stock-hint mt-1 block text-xs font-normal text-slate-500"></span>
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
        <button type="button" class="remove-line inline-flex h-10 items-center justify-center rounded-md border border-slate-300 px-3 text-slate-600 hover:border-red-300 hover:text-red-700"
                aria-label="Retirer cet article" title="Retirer">
            <i data-feather="trash-2" class="h-4 w-4"></i>
        </button>
        <label class="space-y-1 text-sm font-medium text-slate-700 sm:col-span-4">
            Note sur la ligne
            <input name="line_note[]" maxlength="255" placeholder="Ex. découpe A3, couleur Pantone 186C, finition brillante"
                   class="line-note w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal">
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

    // ------------------------------------------------------------------
    // Références
    // ------------------------------------------------------------------
    var form       = document.getElementById('orderForm');
    var lines      = document.getElementById('orderLines');
    var template   = document.getElementById('orderLineTemplate');
    var addBtn     = document.getElementById('addLine');

    if (!form || !lines || !template) {
        console.error('[commande_nouvelle] Elements manquants (#orderForm, #orderLines, #orderLineTemplate).');
        return;
    }

    var taxRate = <?= json_encode($taxRate, JSON_THROW_ON_ERROR) ?>;

    function formatMoney(v) {
        return new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(v) + ' FBu';
    }
    function feath() {
        if (window.feather && typeof window.feather.replace === 'function') {
            try { window.feather.replace(); } catch (e) {}
        }
    }
    function escapeHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    // ------------------------------------------------------------------
    // Autocomplete "Client enregistré"
    // ------------------------------------------------------------------
    var clientList = <?= json_encode(array_map(static function ($c) {
        return [
            'id'     => (int) $c['id'],
            'name'   => $c['name'],
            'phone'  => $c['phone'] ?? '',
            'email'  => $c['email'] ?? '',
            'status' => $c['status_client'] ?? '',
            'annif'  => $c['annif'] ?? '',
        ];
    }, $clients), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    var clientSearch   = document.getElementById('clientSearch');
    var clientIdInput  = document.getElementById('clientIdInput');
    var clientResults  = document.getElementById('clientResults');
    var clientClearBtn = document.getElementById('clientClear');
    var clientHint     = document.getElementById('clientHint');

    function clientLabel(c) {
        return c.name + (c.phone ? ' · ' + c.phone : '');
    }
    function clientMatches(q) {
        q = q.toLowerCase().trim();
        if (!q) return [];
        return clientList.filter(function (c) {
            return (c.name  || '').toLowerCase().indexOf(q) !== -1
                || (c.phone || '').toLowerCase().indexOf(q) !== -1
                || (c.email || '').toLowerCase().indexOf(q) !== -1;
        }).slice(0, 20);
    }
    function renderClientResults(matches) {
        clientResults.innerHTML = '';
        if (!matches.length) {
            var empty = document.createElement('li');
            empty.className = 'px-3 py-3 text-sm text-slate-500';
            empty.textContent = 'Aucun client trouvé. Cochez « Client de passage » ou ajoutez-le d’abord.';
            clientResults.appendChild(empty);
            clientResults.classList.remove('hidden');
            return;
        }
        matches.forEach(function (c) {
            var li = document.createElement('li');
            li.className = 'cursor-pointer px-3 py-2 text-sm hover:bg-green-50';
            li.setAttribute('role', 'option');
            li.dataset.id = c.id;
            li.innerHTML =
                '<div class="flex items-center justify-between gap-2">' +
                    '<span class="font-medium text-slate-800">' + escapeHtml(c.name) +
                        (c.status === 'VIP' ? ' <span class="ml-1 text-amber-500">★</span>' : '') +
                    '</span>' +
                    (c.phone ? '<span class="text-xs text-slate-500">' + escapeHtml(c.phone) + '</span>' : '') +
                '</div>' +
                (c.email ? '<div class="text-xs text-slate-500">' + escapeHtml(c.email) + '</div>' : '');
            li.addEventListener('mousedown', function (ev) {
                ev.preventDefault();
                selectClient(c);
            });
            clientResults.appendChild(li);
        });
        clientResults.classList.remove('hidden');
    }
    function selectClient(c) {
        clientIdInput.value = c.id;
        clientSearch.value  = clientLabel(c);
        clientResults.classList.add('hidden');
        clientHint.textContent = c.status ? ('Statut client : ' + c.status) : '';
        if (clientClearBtn) clientClearBtn.classList.remove('hidden');
    }
    function clearClient() {
        clientIdInput.value = '0';
        clientSearch.value  = '';
        clientHint.textContent = '';
        clientResults.classList.add('hidden');
        if (clientClearBtn) clientClearBtn.classList.add('hidden');
        clientSearch.focus();
    }
    if (clientSearch) {
        clientSearch.addEventListener('input', function () {
            clientIdInput.value = '0';
            if (clientClearBtn) clientClearBtn.classList.add('hidden');
            var q = clientSearch.value;
            if (q.trim().length === 0) {
                clientResults.classList.add('hidden');
                clientHint.textContent = '';
                return;
            }
            renderClientResults(clientMatches(q));
        });
        clientSearch.addEventListener('focus', function () {
            if (clientSearch.value.trim() !== '') {
                renderClientResults(clientMatches(clientSearch.value));
            }
        });
        clientSearch.addEventListener('blur', function () {
            setTimeout(function () { clientResults.classList.add('hidden'); }, 150);
        });
        clientSearch.addEventListener('keydown', function (ev) {
            if (ev.key === 'Escape') {
                clientResults.classList.add('hidden');
                return;
            }
            if (ev.key === 'Enter') {
                var matches = clientMatches(clientSearch.value);
                if (matches.length === 1) {
                    ev.preventDefault();
                    selectClient(matches[0]);
                }
            }
        });
        if (clientClearBtn) {
            clientClearBtn.addEventListener('click', clearClient);
        }
        // Ré-afficher le client déjà sélectionné (retour après POST échoué)
        if (parseInt(clientIdInput.value, 10) > 0) {
            var found = clientList.find(function (c) { return c.id === parseInt(clientIdInput.value, 10); });
            if (found) selectClient(found);
        }
    }

    // ------------------------------------------------------------------
    // Client de passage
    // ------------------------------------------------------------------
    var walkIn      = document.getElementById('walkInToggle');
    var walkInBlock = document.getElementById('walkInBlock');
    var regBlock    = document.getElementById('registeredClientBlock');
    function syncClientBlocks() {
        if (!walkIn || !walkInBlock || !regBlock) return;
        if (walkIn.checked) { walkInBlock.removeAttribute('hidden'); regBlock.setAttribute('hidden', ''); }
        else                { walkInBlock.setAttribute('hidden', ''); regBlock.removeAttribute('hidden'); }
    }
    if (walkIn) { walkIn.addEventListener('change', syncClientBlocks); syncClientBlocks(); }

    // ------------------------------------------------------------------
    // Remise
    // ------------------------------------------------------------------
    var discountType  = document.getElementById('discountType');
    var discountBlock = document.getElementById('discountValueBlock');
    var discountValue = document.getElementById('discountValue');
    function toggleDiscount() {
        if (!discountType || !discountBlock) return;
        if (discountType.value === 'Aucune') discountBlock.setAttribute('hidden', '');
        else                                 discountBlock.removeAttribute('hidden');
    }
    if (discountType) discountType.addEventListener('change', function () { toggleDiscount(); recalculate(); });
    if (discountValue) discountValue.addEventListener('input', recalculate);
    toggleDiscount();

    // ------------------------------------------------------------------
    // Acompte
    // ------------------------------------------------------------------
    var depositAmount  = document.getElementById('depositAmount');
    var depositDetails = document.getElementById('depositDetails');
    var depositMethod  = document.getElementById('depositMethod');
    var bankField      = document.getElementById('bankField');
    function toggleDeposit() {
        if (!depositAmount || !depositDetails) return;
        var v = parseFloat(depositAmount.value || '0');
        if (v > 0) depositDetails.removeAttribute('hidden');
        else       depositDetails.setAttribute('hidden', '');
        if (depositMethod && bankField) {
            if (depositMethod.value === 'Virement bancaire') bankField.removeAttribute('hidden');
            else                                             bankField.setAttribute('hidden', '');
        }
    }
    if (depositAmount) depositAmount.addEventListener('input', toggleDeposit);
    if (depositMethod) depositMethod.addEventListener('change', toggleDeposit);
    toggleDeposit();

    // ------------------------------------------------------------------
    // Recalcul
    // ------------------------------------------------------------------
    function recalculate() {
        var subtotal = 0;
        lines.querySelectorAll('.order-line').forEach(function (line) {
            var select   = line.querySelector('.product-select');
            var qtyInput = line.querySelector('.quantity-input');
            var priceIn  = line.querySelector('.unit-price');
            if (!select || !qtyInput) return;

            var qty = parseFloat(qtyInput.value) || 0;
            var opt = select.options[select.selectedIndex];
            var dataPrice = opt ? parseFloat(opt.dataset.price || '0') : 0;
            var unitPrice = (priceIn && priceIn.value !== '') ? parseFloat(priceIn.value) || 0 : dataPrice;

            var lineTotal = qty * unitPrice;
            subtotal += lineTotal;

            var out = line.querySelector('.line-total');
            if (out) out.textContent = select.value ? formatMoney(lineTotal) : '—';

            var hint = line.querySelector('.stock-hint');
            if (hint) {
                if (select.value && opt && opt.dataset.stock) {
                    var stock = parseFloat(opt.dataset.stock);
                    if (qty > stock) {
                        hint.textContent = '⚠ Stock disponible : ' + stock;
                        hint.className = 'stock-hint mt-1 block text-xs font-normal text-rose-700';
                    } else {
                        hint.textContent = 'Stock disponible : ' + stock;
                        hint.className = 'stock-hint mt-1 block text-xs font-normal text-slate-500';
                    }
                } else { hint.textContent = ''; }
            }
            if (priceIn && priceIn.value === '' && select.value) {
                priceIn.placeholder = dataPrice ? String(dataPrice) : 'auto';
            }
        });

        var dtype = discountType ? discountType.value : 'Aucune';
        var discountAmount = 0;
        if (discountValue) {
            if (dtype === 'Pourcentage' && discountValue.value !== '') {
                var pct = parseFloat(discountValue.value);
                if (pct >= 0 && pct <= 100) discountAmount = subtotal * pct / 100;
            } else if (dtype === 'Montant fixe' && discountValue.value !== '') {
                var fixed = parseFloat(discountValue.value);
                if (fixed >= 0 && fixed <= subtotal) discountAmount = fixed;
            }
        }
        var subtotalAfter = Math.max(0, subtotal - discountAmount);
        var tax = Math.round(subtotalAfter * taxRate / 100);

        var elSub = document.getElementById('subtotalDisplay');
        var elDis = document.getElementById('discountDisplay');
        var elTax = document.getElementById('taxDisplay');
        var elTot = document.getElementById('grandTotalDisplay');
        if (elSub) elSub.textContent = formatMoney(subtotal);
        if (elDis) elDis.textContent = '− ' + formatMoney(discountAmount);
        if (elTax) elTax.textContent = formatMoney(tax);
        if (elTot) elTot.textContent = formatMoney(subtotalAfter + tax);
    }

    // ------------------------------------------------------------------
    // Ajouter / retirer
    // ------------------------------------------------------------------
    function addLine() {
        lines.appendChild(template.content.cloneNode(true));
        feath();
        recalculate();
    }
    if (addBtn) {
        addBtn.addEventListener('click', function (e) {
            e.preventDefault();
            var available = document.querySelectorAll('#orderLineTemplate .product-select option[value]:not([value=""])').length;
            if (available === 0) {
                alert('Aucun produit disponible en stock. Ajoutez d’abord du stock depuis le module Stock.');
                return;
            }
            addLine();
        });
    }
    lines.addEventListener('input', recalculate);
    lines.addEventListener('change', recalculate);
    lines.addEventListener('click', function (e) {
        var btn = e.target.closest('.remove-line');
        if (!btn) return;
        var all = lines.querySelectorAll('.order-line');
        if (all.length > 1) btn.closest('.order-line').remove();
        else {
            var only = all[0];
            var s = only.querySelector('.product-select'); if (s) s.value = '';
            var q = only.querySelector('.quantity-input'); if (q) q.value = '1';
            var p = only.querySelector('.unit-price');     if (p) { p.value = ''; p.placeholder = 'auto'; }
            var n = only.querySelector('.line-note');      if (n) n.value = '';
            var t = only.querySelector('.line-total');     if (t) t.textContent = '—';
        }
        recalculate();
    });

    recalculate();
});
</script>