<?php
if (!defined('ROUTER_LOADED')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

if (!userCan($user, 'factures.create')) {
    http_response_code(403);
    echo '<p class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</p>';
    return;
}

require_once __DIR__ . '/../../api/database.php';
require_once __DIR__ . '/_sales.php';

$db = Database::getInstance();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Méthode non autorisée.');
}

if (!hash_equals($_SESSION['csrf_token'] ?? '', (string) ($_POST['csrf_token'] ?? ''))) {
    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'La demande a expiré. Rechargez la page.'];
    header('Location: ' . app_route_url('factures/creer'));
    exit;
}

$taskId = (int) ($_POST['task_id'] ?? 0);

$order = $db->fetch(
    "SELECT * FROM tasks WHERE id = ? AND category = 'Commande' LIMIT 1",
    [$taskId]
);

if (!$order) {
    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Commande introuvable.'];
    header('Location: ' . app_route_url('factures/creer'));
    exit;
}

if (!empty($order['invoice_number'])) {
    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Cette commande a déjà été facturée (' . $order['invoice_number'] . ').'];
    header('Location: ' . app_route_url('factures/creer'));
    exit;
}

$items = json_decode((string) $order['items_json'], true);
if (!is_array($items) || !$items) {
    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Cette commande ne contient aucun article.'];
    header('Location: ' . app_route_url('factures/creer'));
    exit;
}

$pdo = $db->getPdo();
try {
    $pdo->beginTransaction();

    // Lock + vérif stock
    $productsById = [];
    foreach ($items as $item) {
        $stockId = (int) ($item['stock_id'] ?? 0);
        if ($stockId <= 0) {
            throw new RuntimeException('Article sans référence stock dans la commande.');
        }
        if (!isset($productsById[$stockId])) {
            $locked = $db->query(
                "SELECT id, medicine_name, category, actual_quantity, used_quantity, actual_price, selling_price, sell_type, status, is_archived
                 FROM stock WHERE id = ? FOR UPDATE",
                [$stockId]
            )->fetch();
            if (!$locked || $locked['status'] !== 'Disponible' || (int) $locked['is_archived'] === 1) {
                throw new RuntimeException('Produit introuvable ou indisponible : ' . ($item['name'] ?? ''));
            }
            $productsById[$stockId] = $locked;
        }
        $required = (float) ($item['quantity'] ?? 0);
        if ($required > (float) $productsById[$stockId]['actual_quantity']) {
            throw new RuntimeException('Stock insuffisant pour « ' . $productsById[$stockId]['medicine_name'] . ' » (dispo '
                . $productsById[$stockId]['actual_quantity'] . ').');
        }
    }

    $subtotal    = (float) $order['subtotal'];
    $taxAmount   = (int) $order['tax_amount'];
    $total       = (int) $order['total_amount'];
    $deposit     = (int) $order['deposit_amount'];
    $remaining   = $total - $deposit;
    $costTotal   = 0.0;
    foreach ($items as $it) {
        $costTotal += (float) ($it['quantity'] ?? 0) * (float) ($it['unit_cost'] ?? 0);
    }

    $invoiceNumber = 'FCT-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
    $paidVat       = $total > 0 ? min($taxAmount, (int) round($taxAmount * $deposit / $total)) : 0;
    $productNames  = implode(', ', array_column($items, 'name'));
    $paymentMethod = $deposit > 0
        ? ($order['deposit_method'] === 'Virement bancaire' ? 'Virement bancaire' : 'Espèces')
        : 'À crédit';

    $saleId = $db->insert(
        "INSERT INTO sales (
            invoice_number, operator, agent, client_name, client_id, phone,
            medicines, products_json, quantity, units_json, unit_cost, actual_price,
            tot_htva, tva, tva_paid, tot_tvac, debt, total_amount, total_profit,
            discount, commission, date, status, origin, guard, netcash,
            payment_method, document_type, delivery_status
         ) VALUES (
            ?,?,?,?,?,?,
            ?,?,?,?,?,?,
            ?,?,?,?,?,?,?,
            ?,0,CURDATE(),'Active','Commande',?,?,
            ?, 'Facture', 'Livrée'
         )",
        [
            $invoiceNumber,
            $user['nom_utilisateur'],
            $user['nom_complet'],
            mb_substr($order['client'], 0, 255),
            $order['client_id'] ?: null,
            '',
            mb_substr($productNames, 0, 65000),
            json_encode($items, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            json_encode(array_column($items, 'quantity'), JSON_THROW_ON_ERROR),
            json_encode(array_column($items, 'unit'), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            json_encode(array_column($items, 'unit_cost'), JSON_THROW_ON_ERROR),
            json_encode(array_column($items, 'unit_price'), JSON_THROW_ON_ERROR),
            (int) round($subtotal),
            $taxAmount,
            $paidVat,
            $total,
            $remaining,
            $total,
            (int) round($subtotal - $costTotal),
            (int) $order['discount_amount'],
            $order['ticket_no'],
            $deposit,
            $paymentMethod,
        ]
    );

    // Stock
    foreach ($items as $item) {
        $stockId = (int) $item['stock_id'];
        $product = $productsById[$stockId];
        $before  = (float) $product['actual_quantity'];
        $qty     = (float) $item['quantity'];
        $after   = round($before - $qty, 2);
        $db->execute(
            "UPDATE stock SET actual_quantity = ?, used_quantity = used_quantity + ?, status = ? WHERE id = ?",
            [$after, $qty, $after <= 0 ? 'Rupture' : 'Disponible', $stockId]
        );
        if (function_exists('inventoryLogMovement')) {
            inventoryLogMovement($db, $product, 'sortie', $qty, $before, $after, (float) $item['unit_cost'], $invoiceNumber, 'Facture issue commande', $user['nom_complet']);
        }
    }

    // Caisse / banque (acompte)
    if ($deposit > 0) {
        if ($paymentMethod === 'Espèces') {
            receptionEnsureOpenCashRegister($db, $user['nom_complet']);
            $db->execute(
                'INSERT INTO cash_in (trans_motive, amount, operator, date_created, reference) VALUES (?,?,?,?,?)',
                ['Facture (acompte)', $deposit, $user['nom_complet'], date('Y-m-d'), $invoiceNumber]
            );
            $db->execute('UPDATE caisse SET montant_in = montant_in + ? WHERE date = CURDATE()', [$deposit]);
            receptionRecordFinancialTransaction($db, $user['nom_utilisateur'], 'Facture (acompte)', $invoiceNumber, $deposit, 'entrée');
        } elseif ($order['deposit_method'] === 'Virement bancaire' && !empty($order['bank_id'])) {
            $db->execute('UPDATE banks SET balance = balance + ? WHERE id = ?', [$deposit, $order['bank_id']]);
            receptionRecordFinancialTransaction($db, $user['nom_utilisateur'], 'Facture (virement)', $invoiceNumber, $deposit, 'entrée');
        }
    }

    // Dette restante
    if ($remaining > 0) {
        $db->execute(
            "INSERT INTO debts (amount, paid_amount, client_indebted, client_id, operator, date_reported, invoice_number, due_date, status)
             VALUES (?, ?, ?, ?, ?, CURDATE(), ?, ?, ?)",
            [
                $total, $deposit, mb_substr($order['client'], 0, 255), $order['client_id'] ?: null,
                $user['nom_complet'], $invoiceNumber, $order['due_date'] ?: null,
                $deposit > 0 ? 2 : 0,
            ]
        );
    }

    // Mise à jour client
    if ($order['client_id']) {
        $db->execute(
            "UPDATE partners SET total_achats = COALESCE(total_achats,0) + ?, frequence_achats = COALESCE(frequence_achats,0) + 1, dernier_achat = CURDATE() WHERE id = ?",
            [$total, $order['client_id']]
        );
    }

    // Documents archive
    $docFields = json_encode([
        'from_task'      => $order['ticket_no'],
        'items'          => $items,
        'subtotal'       => $subtotal,
        'tax_rate'       => (float) $order['tax_rate'],
        'tax_amount'     => $taxAmount,
        'total'          => $total,
        'paid'           => $deposit,
        'remaining'      => $remaining,
        'payment_method' => $paymentMethod,
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

    $db->execute(
        "INSERT INTO documents (doc_type, doc_number, operator, client_name, client_id, phone,
                                medicines, quantity, tot_htva, tva, tva_paid, tot_tvac, total_amount,
                                date, doc_fields, category, status, contact_person)
         VALUES ('Facture', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), ?, 'Interne', ?, ?)",
        [
            $invoiceNumber, $user['nom_complet'], mb_substr($order['client'], 0, 255),
            $order['client_id'] ?: null, '',
            mb_substr($productNames, 0, 255),
            mb_substr(json_encode(array_column($items, 'quantity')), 0, 255),
            (int) round($subtotal), $taxAmount, $paidVat, $total, $total,
            $docFields,
            $remaining > 0 ? ($deposit > 0 ? 'Partielle' : 'Impayée') : 'Payée',
            $user['nom_complet'],
        ]
    );

    // Mettre à jour la tâche
    $db->execute(
        "UPDATE tasks
            SET invoice_number = ?, converted_at = NOW(),
                status = 'Résolu', delivery_status = 'Livrée',
                updated_at = NOW()
          WHERE id = ?",
        [$invoiceNumber, (int) $order['id']]
    );

    // Log
    $db->execute(
        "INSERT INTO logs (user_id, action_type, facture, montant, descrip, type, status, module)
         VALUES (?, 'Facture (depuis commande)', ?, ?, ?, 'entrée', 'Active', 'FCT-02')",
        [
            $user['nom_utilisateur'], $invoiceNumber, (string) $total,
            'Facture ' . $invoiceNumber . ' créée depuis ' . $order['ticket_no'],
        ]
    );

    $pdo->commit();

    $_SESSION['flash_message'] = [
        'type' => 'success',
        'text' => 'Facture ' . $invoiceNumber . ' créée depuis ' . $order['ticket_no'] . '.',
    ];

    header('Location: ' . app_route_url('ventes/facture') . '?id=' . $saleId);
    exit;

} catch (Throwable $error) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Invoice conversion failed: ' . $error->getMessage());
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'text' => $error instanceof RuntimeException ? $error->getMessage() : 'La conversion a échoué.',
    ];
    header('Location: ' . app_route_url('factures/apercu') . '?id=' . (int) $order['id']);
    exit;
}
