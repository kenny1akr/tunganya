<?php
if (!defined('ROUTER_LOADED')) { http_response_code(403); exit('Accès direct interdit.'); }
if (!userCan($user, 'ventes.create')) { http_response_code(403); echo '<div class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</div>'; return; }

require_once __DIR__ . '/../../api/database.php';
require_once __DIR__ . '/_sales.php';
$db = Database::getInstance();
$role = $user['role_system'];
$errors = [];
$clientIssueId = (int) ($_GET['issue_id'] ?? $_POST['issue_id'] ?? 0);
$clientIssue = $clientIssueId > 0 ? $db->fetch("SELECT id, invoice_number, client_id, client_name, phone, products_json, tot_htva, tva, total_amount FROM sales WHERE id = ? AND document_type = 'Commande' AND origin = 'Sortie client magasin' AND status = 'Active' LIMIT 1", [$clientIssueId]) : null;
if ($clientIssueId > 0 && !$clientIssue) $errors[] = 'Cette sortie client est introuvable ou a déjà été facturée.';
$clientIssueItems = $clientIssue ? json_decode((string) $clientIssue['products_json'], true) : [];
if ($clientIssue && (!is_array($clientIssueItems) || !$clientIssueItems)) $errors[] = 'Les articles de cette sortie client sont illisibles.';
$clientId = $clientIssue ? (int) $clientIssue['client_id'] : (int) ($_POST['client_id'] ?? 0);
$clientName = $clientIssue ? (string) $clientIssue['client_name'] : trim((string) ($_POST['client_name'] ?? ''));
$clientPhone = $clientIssue ? (string) ($clientIssue['phone'] ?? '') : trim((string) ($_POST['client_phone'] ?? ''));
$postedProductIds = $clientIssue ? array_column($clientIssueItems, 'stock_id') : array_values((array) ($_POST['product_id'] ?? ['']));
$postedQuantities = $clientIssue ? array_column($clientIssueItems, 'quantity') : array_values((array) ($_POST['quantity'] ?? ['1']));
$paymentMethod = (string) ($_POST['payment_method'] ?? 'Espèces');
$bankId = (int) ($_POST['bank_id'] ?? 0);
$paidInput = trim((string) ($_POST['paid_amount'] ?? ''));
$allowedPaymentMethods = ['Espèces', 'Virement bancaire', 'À crédit'];
$clients = $db->fetchAll("SELECT id, name, phone FROM partners WHERE category = 'client' ORDER BY name");
$products = $db->fetchAll("SELECT id, medicine_name, actual_quantity, sell_type, selling_price FROM stock WHERE status = 'Disponible' AND actual_quantity > 0 ORDER BY medicine_name");
$banks = $db->fetchAll("SELECT id, bank_name, account_number, balance, currency FROM banks WHERE currency = 'FBu' ORDER BY bank_name");
receptionEnsureCsrf();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], (string) ($_POST['csrf_token'] ?? ''))) {
        $errors[] = 'Le formulaire a expiré. Rechargez la page puis réessayez.';
    }
    if (!in_array($paymentMethod, $allowedPaymentMethods, true)) {
        $errors[] = 'Choisissez un moyen de paiement valide.';
    }
    if (mb_strlen($clientName) > 255 || mb_strlen($clientPhone) > 15) {
        $errors[] = 'Les informations du client dépassent la longueur autorisée.';
    }
    if ($paymentMethod === 'Virement bancaire' && $bankId <= 0) {
        $errors[] = 'Choisissez le compte bancaire qui reçoit le virement.';
    }

    $customer = null;
    if ($clientId > 0) {
        $customer = $db->fetch("SELECT id, name, phone FROM partners WHERE id = ? AND category = 'client' LIMIT 1", [$clientId]);
        if (!$customer) {
            $errors[] = 'Le client sélectionné est introuvable.';
        } else {
            $clientName = $customer['name'];
            $clientPhone = (string) ($customer['phone'] ?? '');
        }
    } elseif ($clientName === '') {
        $clientName = 'Client comptoir';
    }

    $lineInputs = [];
    if ($clientIssueId > 0) {
        foreach ($clientIssueItems as $issueItem) {
            $productId = (int) ($issueItem['stock_id'] ?? 0);
            $quantity = $issueItem['quantity'] ?? null;
            if ($productId <= 0 || !is_numeric($quantity) || !is_finite((float) $quantity) || (float) $quantity <= 0) {
                $errors[] = 'Les articles de cette sortie client contiennent une quantité invalide.';
                continue;
            }
            $lineInputs[] = ['id' => $productId, 'quantity' => (float) $quantity];
        }
        if (!$lineInputs) $errors[] = 'Cette sortie client ne contient aucun article facturable.';
    } else {
        $seen = [];
        $rowCount = max(count($postedProductIds), count($postedQuantities));
        for ($index = 0; $index < $rowCount; $index++) {
            $productId = (int) ($postedProductIds[$index] ?? 0);
            $quantityText = str_replace(',', '.', trim((string) ($postedQuantities[$index] ?? '')));
            if ($productId <= 0 && $quantityText === '') continue;
            if ($productId <= 0 || !is_numeric($quantityText) || !is_finite((float) $quantityText) || (float) $quantityText <= 0) {
                $errors[] = 'Chaque ligne doit avoir un produit et une quantité supérieure à zéro.';
                continue;
            }
            if (isset($seen[$productId])) {
                $errors[] = 'Un produit ne peut apparaître qu’une fois. Augmentez sa quantité sur la même ligne.';
                continue;
            }
            $seen[$productId] = true;
            $lineInputs[] = ['id' => $productId, 'quantity' => (float) $quantityText];
        }
        if (!$lineInputs) $errors[] = 'Ajoutez au moins un produit à la vente.';
    }

    if (!$errors) {
        $pdo = $db->getPdo();
        try {
            $pdo->beginTransaction();
            $productsById = [];
            if ($clientIssueId > 0) {
                $lockedIssue = $db->query("SELECT id, invoice_number, client_id, client_name, phone, products_json FROM sales WHERE id = ? AND document_type = 'Commande' AND origin = 'Sortie client magasin' AND status = 'Active' FOR UPDATE", [$clientIssueId])->fetch();
                if (!$lockedIssue) throw new RuntimeException('Cette sortie client a déjà été facturée ou n’est plus disponible.');
                $lockedItems = json_decode((string) $lockedIssue['products_json'], true);
                if (!is_array($lockedItems) || !$lockedItems) throw new RuntimeException('Les articles de cette sortie client sont illisibles.');
                $lineInputs = [];
                foreach ($lockedItems as $lockedItem) {
                    $productId = (int) ($lockedItem['stock_id'] ?? 0);
                    $quantity = $lockedItem['quantity'] ?? null;
                    $unitPrice = $lockedItem['unit_price'] ?? null;
                    if ($productId <= 0 || !is_numeric($quantity) || !is_finite((float) $quantity) || (float) $quantity <= 0 || isset($productsById[$productId])) {
                        throw new RuntimeException('Les articles de cette sortie client sont invalides.');
                    }
                    $productsById[$productId] = [
                        'id' => $productId,
                        'medicine_name' => (string) ($lockedItem['name'] ?? 'Article'),
                        'category' => '',
                        'actual_quantity' => (float) $quantity,
                        'used_quantity' => 0,
                        'actual_price' => (float) ($lockedItem['unit_cost'] ?? 0),
                        'selling_price' => (float) $unitPrice,
                        'sell_type' => (string) ($lockedItem['unit'] ?? ''),
                        'status' => 'Sortie déjà effectuée',
                    ];
                    $lineInputs[] = ['id' => $productId, 'quantity' => (float) $quantity];
                }
                if ((int) $lockedIssue['client_id'] !== $clientId) throw new RuntimeException('Le client de la sortie a changé. Rechargez la page.');
                $clientName = (string) $lockedIssue['client_name'];
                $clientPhone = (string) ($lockedIssue['phone'] ?? '');
            } else {
                $idsToLock = array_column($lineInputs, 'id');
                sort($idsToLock, SORT_NUMERIC);
                foreach ($idsToLock as $idToLock) {
                    $lockedProduct = $db->query("SELECT id, medicine_name, category, actual_quantity, used_quantity, actual_price, selling_price, sell_type, status FROM stock WHERE id = ? FOR UPDATE", [$idToLock])->fetch();
                    if (!$lockedProduct || $lockedProduct['status'] !== 'Disponible') {
                        throw new RuntimeException('Un des produits n’est plus disponible. Actualisez la page puis recommencez.');
                    }
                    $productsById[(int) $idToLock] = $lockedProduct;
                }
            }

            $items = [];
            $subtotal = 0.0;
            $costTotal = 0.0;
            foreach ($lineInputs as $line) {
                $product = $productsById[$line['id']];
                $quantity = $line['quantity'];
                if ($clientIssueId === 0 && $quantity > (float) $product['actual_quantity']) {
                    throw new RuntimeException('Stock insuffisant pour « ' . $product['medicine_name'] . ' » : disponible ' . $product['actual_quantity'] . ' ' . $product['sell_type'] . '.');
                }
                $lineTotal = $clientIssueId > 0 ? (int) round($quantity * (float) $product['selling_price']) : round($quantity * (float) $product['selling_price'], 2);
                $unitCost = (float) $product['actual_price'];
                $subtotal += $lineTotal;
                $costTotal += $quantity * $unitCost;
                $items[] = [
                    'stock_id' => (int) $product['id'],
                    'name' => $product['medicine_name'],
                    'quantity' => $quantity,
                    'unit' => $product['sell_type'],
                    'unit_cost' => $unitCost,
                    'unit_price' => (float) $product['selling_price'],
                    'line_total' => $lineTotal,
                ];
            }

            $taxRate = defined('TVA_DEFAULT') ? (float) TVA_DEFAULT : 18.0;
            $taxAmount = (int) round($subtotal * $taxRate / 100);
            $grandTotal = (int) round($subtotal) + $taxAmount;
            if ($paymentMethod === 'À crédit') {
                $paidAmount = 0;
            } else {
                $paidAmount = $paidInput === '' ? $grandTotal : (is_numeric($paidInput) ? (int) round((float) $paidInput) : -1);
                if ($paidAmount < 0 || $paidAmount > $grandTotal) {
                    throw new RuntimeException('Le montant reçu doit être entre 0 et le total de la facture.');
                }
            }
            if ($paymentMethod === 'Virement bancaire' && $paidAmount > 0) {
                $bank = $db->query('SELECT id, bank_name, balance FROM banks WHERE id = ? AND currency = ? FOR UPDATE', [$bankId, 'FBu'])->fetch();
                if (!$bank) throw new RuntimeException('Le compte bancaire choisi est introuvable ou n’est pas en FBu.');
            } else {
                $bank = null;
            }

            $invoiceNumber = 'FCT-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
            $remaining = $grandTotal - $paidAmount;
            $paidVat = $grandTotal > 0 ? min($taxAmount, (int) round($taxAmount * $paidAmount / $grandTotal)) : 0;
            $productNames = implode(', ', array_column($items, 'name'));
            if ($clientIssueId > 0) {
                $updated = $db->execute(
                    "UPDATE sales SET source_reference = COALESCE(source_reference, ?), invoice_number = ?, operator = ?, agent = ?, client_name = ?, client_id = ?, phone = ?, medicines = ?, products_json = ?, quantity = ?, units_json = ?, unit_cost = ?, actual_price = ?, tot_htva = ?, tva = ?, tva_paid = ?, tot_tvac = ?, debt = ?, total_amount = ?, total_profit = ?, discount = 0, commission = 0, date = CURDATE(), status = 'Active', netcash = ?, payment_method = ?, document_type = 'Facture' WHERE id = ? AND document_type = 'Commande' AND origin = 'Sortie client magasin' AND status = 'Active'",
                    [$lockedIssue['invoice_number'], $invoiceNumber, $user['nom_utilisateur'], $user['nom_complet'], $clientName, $clientId, $clientPhone, mb_substr($productNames, 0, 65000), json_encode($items, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), json_encode(array_column($items, 'quantity'), JSON_THROW_ON_ERROR), json_encode(array_column($items, 'unit'), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), json_encode(array_column($items, 'unit_cost'), JSON_THROW_ON_ERROR), json_encode(array_column($items, 'unit_price'), JSON_THROW_ON_ERROR), (int) round($subtotal), $taxAmount, $paidVat, $grandTotal, $remaining, $grandTotal, (int) round($subtotal - $costTotal), $paidAmount, $paymentMethod, $clientIssueId]
                );
                if ($updated !== 1) throw new RuntimeException('La sortie client a déjà été finalisée.');
                $saleId = $clientIssueId;
            } else {
                $saleId = $db->insert(
                    "INSERT INTO sales (invoice_number,operator,agent,client_name,client_id,phone,medicines,products_json,quantity,units_json,unit_cost,actual_price,tot_htva,tva,tva_paid,tot_tvac,debt,total_amount,total_profit,discount,commission,date,status,origin,guard,netcash,payment_method,document_type)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,CURDATE(),'Active','Vente comptoir','',?,?, 'Facture')",
                    [
                        $invoiceNumber, $user['nom_utilisateur'], $user['nom_complet'], $clientName, $clientId ?: null, $clientPhone,
                        mb_substr($productNames, 0, 65000), json_encode($items, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                        json_encode(array_column($items, 'quantity'), JSON_THROW_ON_ERROR), json_encode(array_column($items, 'unit'), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                        json_encode(array_column($items, 'unit_cost'), JSON_THROW_ON_ERROR), json_encode(array_column($items, 'unit_price'), JSON_THROW_ON_ERROR),
                        (int) round($subtotal), $taxAmount, $paidVat, $grandTotal, $remaining, $grandTotal, (int) round($subtotal - $costTotal), 0, 0,
                        $paidAmount, $paymentMethod,
                    ]
                );
            }

            if ($clientIssueId === 0) {
                foreach ($items as $item) {
                    $product = $productsById[$item['stock_id']];
                    $before = (float) $product['actual_quantity'];
                    $after = round($before - (float) $item['quantity'], 2);
                    $db->execute("UPDATE stock SET actual_quantity = ?, used_quantity = used_quantity + ?, status = ? WHERE id = ?", [$after, $item['quantity'], $after <= 0 ? 'Rupture' : 'Disponible', $item['stock_id']]);
                    inventoryLogMovement($db, $product, 'sortie', (float) $item['quantity'], $before, $after, (float) $item['unit_cost'], $invoiceNumber, 'Vente comptoir', $user['nom_complet']);
                }
            }

            $financialOrigin = $clientIssueId > 0 ? 'Facturation sortie client' : 'Vente comptoir';
            if ($paidAmount > 0 && $paymentMethod === 'Espèces') {
                receptionEnsureOpenCashRegister($db, $user['nom_complet']);
                $db->execute('INSERT INTO cash_in (trans_motive,amount,operator,date_created,reference) VALUES (?,?,?,?,?)', [$financialOrigin, $paidAmount, $user['nom_complet'], date('Y-m-d'), $invoiceNumber]);
                $db->execute('UPDATE caisse SET montant_in = montant_in + ? WHERE date = CURDATE()', [$paidAmount]);
                receptionRecordFinancialTransaction($db, $user['nom_utilisateur'], $financialOrigin, $invoiceNumber, $paidAmount, 'entrée');
            } elseif ($paidAmount > 0 && $paymentMethod === 'Virement bancaire' && $bank) {
                $db->execute('UPDATE banks SET balance = balance + ? WHERE id = ?', [$paidAmount, $bank['id']]);
                receptionRecordFinancialTransaction($db, $user['nom_utilisateur'], $financialOrigin . ' par virement - ' . $bank['bank_name'], $invoiceNumber, $paidAmount, 'entrée');
            }

            if ($remaining > 0) {
                $db->execute("INSERT INTO debts (amount,paid_amount,client_indebted,client_id,operator,date_reported,invoice_number,status) VALUES (?,?,?,?,?,CURDATE(),?,?)", [$grandTotal, $paidAmount, $clientName, $clientId ?: null, $user['nom_complet'], $invoiceNumber, $paidAmount > 0 ? 2 : 0]);
            }

            if ($clientId > 0) {
                $db->execute("UPDATE partners SET total_achats = COALESCE(total_achats,0) + ?, frequence_achats = COALESCE(frequence_achats,0) + 1, dernier_achat = CURDATE() WHERE id = ? AND category = 'client'", [$grandTotal, $clientId]);
            }

            $documentFields = json_encode(['sale_id'=>$saleId,'items'=>$items,'subtotal'=>$subtotal,'tax_rate'=>$taxRate,'tax_amount'=>$taxAmount,'total'=>$grandTotal,'paid'=>$paidAmount,'remaining'=>$remaining,'payment_method'=>$paymentMethod,'bank'=>$bank['bank_name']??null], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            $db->execute("INSERT INTO documents (doc_type,doc_number,operator,client_name,client_id,phone,medicines,quantity,tot_htva,tva,tva_paid,tot_tvac,total_amount,date,doc_fields,category,status,contact_person) VALUES ('Facture',?,?,?,?,?,?,?,?,?,?,?, ?,CURDATE(),?,'Archive',?,?)", [$invoiceNumber,$user['nom_complet'],$clientName,$clientId?:null,$clientPhone,mb_substr($productNames,0,255),mb_substr(json_encode(array_column($items,'quantity')),0,255),(int)round($subtotal),$taxAmount,$paidVat,$grandTotal,$grandTotal,$documentFields,$remaining>0?($paidAmount>0?'Partielle':'Impayée'):'Payée',$user['nom_complet']]);

            $pdo->commit();
            $_SESSION['flash_message'] = ['type'=>'success','text'=>'Vente enregistrée. Facture '.$invoiceNumber.'.'];
            header('Location: ' . app_route_url('ventes/facture') . '?id=' . $saleId);
            exit;
        } catch (Throwable $error) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('Counter sale failed: ' . $error->getMessage());
            $errors[] = $error instanceof RuntimeException ? $error->getMessage() : 'La vente n’a pas été enregistrée. Aucune quantité ni écriture financière n’a été conservée.';
        }
    }
}
?>
<section class="mx-auto max-w-5xl space-y-5">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"><div><p class="text-sm font-semibold text-green-800"><?= $clientIssueId > 0 ? 'Sortie client transmise par le magasin' : 'Vente avec facture' ?></p><h1 class="mt-1 text-2xl font-bold text-slate-900"><?= $clientIssueId > 0 ? 'Établir la facture' : 'Vente comptoir' ?></h1><p class="mt-1 text-sm text-slate-600"><?= $clientIssueId > 0 ? 'Les articles sont déjà sortis du stock. Cette étape enregistre le règlement sans modifier les quantités.' : 'Une vente décrémente le stock, génère une facture et inscrit le paiement en caisse/banque.' ?></p></div><a href="<?=receptionEscape(app_route_url('ventes/factures'))?>" class="rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold">Factures récentes</a></header>
    <?php if($errors):?><div role="alert" class="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800"><ul class="list-disc space-y-1 pl-5"><?php foreach(array_unique($errors) as $error):?><li><?=receptionEscape($error)?></li><?php endforeach;?></ul></div><?php endif;?>
    <?php if(!$products && $clientIssueId === 0):?><div class="rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">Aucun produit disponible. Une sortie simple d’inventaire ne crée pas une vente; ajoutez d’abord du stock ou consultez le menu Stock.</div><?php endif;?>
    <form method="post" id="saleForm" class="space-y-5 rounded-md border border-slate-200 bg-white p-5 sm:p-6"><input type="hidden" name="csrf_token" value="<?=receptionEscape($_SESSION['csrf_token'])?>"><?php if ($clientIssueId > 0): ?><input type="hidden" name="issue_id" value="<?=$clientIssueId?>"><section class="border-b border-slate-100 pb-5"><p class="text-sm font-semibold text-slate-800">Client enregistré</p><p class="mt-2 font-medium"><?=receptionEscape($clientName)?></p><p class="text-sm text-slate-600"><?=receptionEscape($clientPhone?:'Téléphone non renseigné')?></p></section><?php else: ?>
        <fieldset class="grid gap-4 border-b border-slate-100 pb-5 sm:grid-cols-2"><legend class="mb-3 text-sm font-semibold text-slate-800">Client</legend><label class="space-y-1 text-sm font-medium">Client enregistré (facultatif)<select name="client_id" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 font-normal"><option value="0">Client comptoir</option><?php foreach($clients as $client):?><option value="<?=(int)$client['id']?>" <?= $clientId===(int)$client['id']?'selected':''?>><?=receptionEscape($client['name'])?><?= $client['phone']?' · '.receptionEscape($client['phone']):''?></option><?php endforeach;?></select></label><label class="space-y-1 text-sm font-medium">Nom client comptoir<input name="client_name" maxlength="255" value="<?=receptionEscape($clientName)?>" placeholder="Client comptoir" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label><label class="space-y-1 text-sm font-medium">Téléphone comptoir<input name="client_phone" maxlength="15" value="<?=receptionEscape($clientPhone)?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label></fieldset><?php endif; ?>
        <?php if ($clientIssueId > 0): ?><section class="space-y-3"><h2 class="text-sm font-semibold">Articles remis par le magasin</h2><div class="overflow-x-auto border-y border-slate-200"><table class="min-w-full text-sm"><thead class="text-left text-xs uppercase text-slate-500"><tr><th class="py-2">Article</th><th class="py-2">Unité</th><th class="py-2 text-right">Quantité</th><th class="py-2 text-right">Prix unitaire</th><th class="py-2 text-right">Total</th></tr></thead><tbody class="divide-y"><?php foreach ($clientIssueItems as $item): ?><tr><td class="py-2 font-medium"><?=receptionEscape($item['name']??'Article')?></td><td class="py-2"><?=receptionEscape($item['unit']??'')?></td><td class="py-2 text-right"><?=number_format((float)($item['quantity']??0),2,',',' ')?></td><td class="py-2 text-right"><?=number_format((float)($item['unit_price']??0),0,',',' ')?></td><td class="py-2 text-right"><?=number_format((float)($item['line_total']??0),0,',',' ')?></td></tr><?php endforeach; ?></tbody></table></div></section><?php else: ?>
        <fieldset class="space-y-3"><div class="flex items-center justify-between"><legend class="text-sm font-semibold">Produits vendus</legend><button type="button" id="addSaleLine" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium" <?=!$products?'disabled':''?>><i data-feather="plus" class="mr-1 inline h-4 w-4"></i>Ajouter un produit</button></div><div id="saleLines" class="space-y-3">
            <?php foreach($postedProductIds as $index=>$selectedId):?><div class="sale-line grid gap-3 rounded-md border border-slate-200 p-3 sm:grid-cols-[minmax(0,1fr)_8rem_9rem_auto] sm:items-end"><label class="space-y-1 text-sm font-medium">Produit<select name="product_id[]" required class="sale-product w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 font-normal"><option value="">Choisir un produit</option><?php foreach($products as $product):?><option value="<?=(int)$product['id']?>" data-price="<?= (float)$product['selling_price']?>" data-available="<?= (float)$product['actual_quantity']?>" <?= (string)$selectedId===(string)$product['id']?'selected':''?>><?=receptionEscape($product['medicine_name'])?> · <?=number_format((float)$product['selling_price'],0,',',' ')?> FBu · stock <?=number_format((float)$product['actual_quantity'],2,',',' ')?> <?=receptionEscape($product['sell_type'])?></option><?php endforeach;?></select></label><label class="space-y-1 text-sm font-medium">Quantité<input name="quantity[]" type="number" min="0.01" step="0.01" required value="<?=receptionEscape($postedQuantities[$index]??'1')?>" class="sale-quantity w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label><div class="text-sm"><span class="block font-medium">Sous-total</span><output class="sale-line-total block rounded-md bg-slate-50 px-3 py-2.5 text-right">—</output></div><button type="button" class="remove-sale-line rounded-md border border-slate-300 p-2 text-rose-700" title="Retirer" aria-label="Retirer la ligne"><i data-feather="trash-2" class="h-4 w-4"></i></button></div><?php endforeach;?>
        </div></fieldset><?php endif; ?>
        <fieldset class="grid gap-4 border-t border-slate-100 pt-5 sm:grid-cols-3"><legend class="text-sm font-semibold text-slate-800">Règlement</legend><label class="space-y-1 text-sm font-medium">Mode<select name="payment_method" id="paymentMethod" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 font-normal"><?php foreach($allowedPaymentMethods as $method):?><option value="<?=receptionEscape($method)?>" <?=$paymentMethod===$method?'selected':''?>><?=receptionEscape($method)?></option><?php endforeach;?></select></label><label id="bankField" class="space-y-1 text-sm font-medium">Compte bancaire<select name="bank_id" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 font-normal"><option value="">Choisir</option><?php foreach($banks as $bank):?><option value="<?=(int)$bank['id']?>" <?= $bankId===(int)$bank['id']?'selected':''?>><?=receptionEscape($bank['bank_name'])?><?= $bank['account_number']?' · '.receptionEscape($bank['account_number']):''?></option><?php endforeach;?></select><?php if(!$banks):?><span class="text-xs font-normal text-amber-700">Aucun compte FBu n’est configuré.</span><?php endif;?></label><label class="space-y-1 text-sm font-medium">Montant reçu (FBu)<input name="paid_amount" id="paidAmount" type="number" min="0" step="1" value="<?=receptionEscape($paidInput)?>" placeholder="Vide = total à payer" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label></fieldset>
        <div class="ml-auto max-w-sm space-y-2 border-t border-slate-200 pt-4 text-sm"><div class="flex justify-between text-slate-600"><span>Sous-total HT</span><span id="saleSubtotal"><?= $clientIssue ? number_format((float) $clientIssue['tot_htva'], 0, ',', ' ') . ' FBu' : '0 FBu' ?></span></div><div class="flex justify-between text-slate-600"><span>TVA (<?=defined('TVA_DEFAULT')?(float)TVA_DEFAULT:18?>%)</span><span id="saleTax"><?= $clientIssue ? number_format((float) $clientIssue['tva'], 0, ',', ' ') . ' FBu' : '0 FBu' ?></span></div><div class="flex justify-between border-t pt-2 text-base font-bold"><span>Total facture</span><span id="saleTotal"><?= $clientIssue ? number_format((float) $clientIssue['total_amount'], 0, ',', ' ') . ' FBu' : '0 FBu' ?></span></div><div class="text-xs text-slate-500">Le paiement partiel crée automatiquement un solde client. Une sortie d’inventaire simple ne génère pas de facture.</div></div>
        <div class="flex justify-end border-t border-slate-100 pt-4"><button class="rounded-md bg-green-700 px-5 py-2.5 text-sm font-semibold text-white" <?=!$products?'disabled':''?>>Valider la vente et générer la facture</button></div>
    </form>
</section>
<template id="saleLineTemplate"><div class="sale-line grid gap-3 rounded-md border border-slate-200 p-3 sm:grid-cols-[minmax(0,1fr)_8rem_9rem_auto] sm:items-end"><label class="space-y-1 text-sm font-medium">Produit<select name="product_id[]" required class="sale-product w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 font-normal"><option value="">Choisir un produit</option><?php foreach($products as $product):?><option value="<?=(int)$product['id']?>" data-price="<?= (float)$product['selling_price']?>" data-available="<?= (float)$product['actual_quantity']?>"><?=receptionEscape($product['medicine_name'])?> · <?=number_format((float)$product['selling_price'],0,',',' ')?> FBu · stock <?=number_format((float)$product['actual_quantity'],2,',',' ')?> <?=receptionEscape($product['sell_type'])?></option><?php endforeach;?></select></label><label class="space-y-1 text-sm font-medium">Quantité<input name="quantity[]" type="number" min="0.01" step="0.01" required value="1" class="sale-quantity w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label><div class="text-sm"><span class="block font-medium">Sous-total</span><output class="sale-line-total block rounded-md bg-slate-50 px-3 py-2.5 text-right">—</output></div><button type="button" class="remove-sale-line rounded-md border border-slate-300 p-2 text-rose-700" title="Retirer" aria-label="Retirer la ligne"><i data-feather="trash-2" class="h-4 w-4"></i></button></div></template>
<script>
(() => {
    const lines = document.getElementById('saleLines');
    const taxRate = <?=defined('TVA_DEFAULT')?(float)TVA_DEFAULT:18?>;
    const paymentMethod = document.getElementById('paymentMethod');
    const bankField = document.getElementById('bankField');
    const paidAmount = document.getElementById('paidAmount');
    const formatMoney = value => `${new Intl.NumberFormat('fr-FR', {maximumFractionDigits: 0}).format(value)} FBu`;
    function recalculate() {
        let subtotal = 0;
        lines.querySelectorAll('.sale-line').forEach(line => {
            const select = line.querySelector('.sale-product');
            const qty = Number(line.querySelector('.sale-quantity').value) || 0;
            const price = Number(select.selectedOptions[0]?.dataset.price) || 0;
            const lineTotal = qty * price;
            subtotal += lineTotal;
            line.querySelector('.sale-line-total').textContent = select.value ? formatMoney(lineTotal) : '—';
        });
        const tax = Math.round(subtotal * taxRate / 100);
        document.getElementById('saleSubtotal').textContent = formatMoney(subtotal);
        document.getElementById('saleTax').textContent = formatMoney(tax);
        document.getElementById('saleTotal').textContent = formatMoney(subtotal + tax);
    }
    function toggleBank() { bankField.hidden = paymentMethod.value !== 'Virement bancaire'; paidAmount.disabled = paymentMethod.value === 'À crédit'; if (paymentMethod.value === 'À crédit') paidAmount.value = '0'; else if (paidAmount.value === '0') paidAmount.value = ''; }
    const addSaleLine = document.getElementById('addSaleLine');
    if (lines && addSaleLine) {
        addSaleLine.addEventListener('click', () => { lines.append(document.getElementById('saleLineTemplate').content.cloneNode(true)); if (window.feather) feather.replace(); recalculate(); });
        lines.addEventListener('input', recalculate);
        lines.addEventListener('change', recalculate);
        lines.addEventListener('click', event => { const button=event.target.closest('.remove-sale-line'); if(!button)return; const all=lines.querySelectorAll('.sale-line'); if(all.length>1)button.closest('.sale-line').remove(); else {all[0].querySelector('.sale-product').value='';all[0].querySelector('.sale-quantity').value='1';} recalculate(); });
    }
    paymentMethod.addEventListener('change',toggleBank); toggleBank(); if (lines) recalculate();
})();
</script>