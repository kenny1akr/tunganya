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
$db     = Database::getInstance();
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$taskId = (int) ($_GET['id'] ?? 0);

$order = $db->fetch(
    "SELECT t.*, u.f_name, u.l_name
     FROM tasks t
     LEFT JOIN users u ON u.user_name = t.assigned_to
     WHERE t.id = ? AND t.category = 'Commande'
     LIMIT 1",
    [$taskId]
);

if (!$order) {
    http_response_code(404);
    echo '<div class="rounded-md border border-amber-200 bg-amber-50 p-4 text-amber-900">Commande introuvable.</div>';
    return;
}

// === Déjà convertie → afficher une carte avec lien vers la facture ===
if (!empty($order['invoice_number'])) {
    $sale = $db->fetch(
        "SELECT id FROM sales WHERE invoice_number = ? AND document_type = 'Facture' LIMIT 1",
        [$order['invoice_number']]
    );
    ?>
    <section class="mx-auto max-w-2xl py-10">
        <div class="rounded-lg border border-blue-200 bg-blue-50 p-8 text-center">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-blue-100">
                <i data-feather="check-circle" class="h-7 w-7 text-blue-700"></i>
            </div>
            <h2 class="text-xl font-semibold text-blue-900">Commande déjà facturée</h2>
            <p class="mt-2 text-sm text-blue-800">
                La commande <strong><?= $escape($order['ticket_no']) ?></strong> a déjà été convertie en facture.
            </p>
            <p class="mt-3 font-mono text-base font-semibold text-blue-900"><?= $escape($order['invoice_number']) ?></p>
            <?php if (!empty($order['converted_at'])): ?>
                <p class="mt-1 text-xs text-blue-700">
                    Convertie le <?= $escape(date('d/m/Y à H:i', strtotime($order['converted_at']))) ?>
                </p>
            <?php endif; ?>

            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <?php if ($sale): ?>
                    <a href="<?= $escape(app_route_url('ventes/facture')) . '?id=' . (int) $sale['id'] ?>"
                       class="inline-flex items-center gap-2 rounded-md bg-green-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-800">
                        <i data-feather="file-text" class="h-4 w-4"></i> Voir la facture
                    </a>
                <?php endif; ?>
                <a href="<?= $escape(app_route_url('factures/creer')) ?>"
                   class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    <i data-feather="arrow-left" class="h-4 w-4"></i> Retour à la liste
                </a>
            </div>
        </div>
    </section>
    <?php
    return;
}

$items = json_decode((string) $order['items_json'], true);
if (!is_array($items)) $items = [];

$company = $db->fetch("SELECT company_name, company_logo FROM app_settings WHERE id = 1 LIMIT 1") ?: [];
$companyName = $company['company_name'] ?? 'DH TELECOM AND ADVERTISING HOUSE';
$companyLogo = $company['company_logo'] ?? '';

$flashMessage = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);

$subtotal    = (float) $order['subtotal'];
$discountAmt = (float) $order['discount_amount'];
$taxRate     = (float) $order['tax_rate'];
$taxAmt      = (float) $order['tax_amount'];
$total       = (float) $order['total_amount'];
$deposit     = (float) $order['deposit_amount'];
$remaining   = max(0, $total - $deposit);
?>
<section class="mx-auto max-w-4xl space-y-5">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between print:hidden">
        <div>
            <a href="<?= $escape(app_route_url('factures/creer')) ?>"
               class="inline-flex items-center gap-2 text-sm font-medium text-green-800 hover:underline">
                <i data-feather="arrow-left" class="h-4 w-4"></i> Retour à la liste
            </a>
            <h1 class="mt-3 text-2xl font-bold text-slate-900">Aperçu facture</h1>
            <p class="mt-1 text-sm text-slate-600">
                Vérifiez le détail puis cliquez sur <strong>Imprimer</strong> ou <strong>Créer la facture</strong>.
            </p>
        </div>
        <div class="flex gap-2">
            <button type="button" onclick="window.print()"
                    class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                <i data-feather="printer" class="h-4 w-4"></i> Imprimer
            </button>
            <form method="post" action="<?= $escape(app_route_url('factures/convertir')) ?>"
                  onsubmit="return confirm('Créer la facture définitive pour <?= $escape($order['ticket_no']) ?> ? Le stock sera décrémenté.');">
                <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="task_id" value="<?= (int) $order['id'] ?>">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-md bg-green-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-800">
                    <i data-feather="check-circle" class="h-4 w-4"></i> Créer la facture
                </button>
            </form>
        </div>
    </header>

    <?php if ($flashMessage): ?>
        <div role="status" class="rounded-md border px-4 py-3 text-sm print:hidden <?= ($flashMessage['type'] ?? '') === 'success' ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-800' ?>">
            <?= $escape($flashMessage['text'] ?? '') ?>
        </div>
    <?php endif; ?>

    <!-- FEUILLE IMPRIMABLE -->
    <article class="rounded-md border border-slate-200 bg-white p-6 sm:p-10 print:border-0 print:p-0">
        <!-- EN-TÊTE -->
        <div class="flex flex-col gap-5 border-b border-slate-200 pb-6 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-start gap-3">
                <?php if ($companyLogo): ?>
                    <img src="<?= $escape($companyLogo) ?>" alt="" class="h-12 w-12 object-contain">
                <?php else: ?>
                    <div class="flex h-12 w-12 items-center justify-center rounded-md bg-green-700 font-bold text-white">DH</div>
                <?php endif; ?>
                <div>
                    <p class="text-sm font-bold uppercase tracking-widest text-green-800"><?= $escape($companyName) ?></p>
                    <h1 class="mt-3 text-3xl font-bold text-slate-900">BON DE COMMANDE</h1>
                    <p class="mt-2 font-mono text-sm text-slate-600"><?= $escape($order['ticket_no']) ?></p>
                </div>
            </div>
            <dl class="space-y-1 text-sm sm:text-right">
                <div>
                    <dt class="inline text-slate-500">Date commande : </dt>
                    <dd class="inline font-medium"><?= $escape(date('d/m/Y', strtotime($order['order_date'] ?? $order['created_at']))) ?></dd>
                </div>
                <?php if (!empty($order['due_date'])): ?>
                    <div>
                        <dt class="inline text-slate-500">Livraison prévue : </dt>
                        <dd class="inline font-medium"><?= $escape(date('d/m/Y', strtotime($order['due_date']))) ?></dd>
                    </div>
                <?php endif; ?>
                <div>
                    <dt class="inline text-slate-500">Assignée à : </dt>
                    <dd class="inline font-medium"><?= $escape(trim(($order['f_name'] ?? '') . ' ' . ($order['l_name'] ?? ''))) ?></dd>
                </div>
                <div>
                    <dt class="inline text-slate-500">Priorité : </dt>
                    <dd class="inline font-medium"><?= $escape($order['priority']) ?></dd>
                </div>
            </dl>
        </div>

        <!-- CLIENT -->
        <section class="grid gap-6 border-b border-slate-200 py-6 sm:grid-cols-2">
            <div>
                <p class="text-xs font-semibold uppercase text-slate-500">Client</p>
                <p class="mt-2 text-lg font-semibold text-slate-900"><?= $escape($order['client']) ?></p>
                <?php if (!empty($order['client_reference'])): ?>
                    <p class="mt-1 text-sm text-slate-600">Réf : <?= $escape($order['client_reference']) ?></p>
                <?php endif; ?>
            </div>
            <?php if (!empty($order['delivery_address'])): ?>
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-500">Adresse de livraison</p>
                    <p class="mt-2 text-sm text-slate-700"><?= nl2br($escape($order['delivery_address'])) ?></p>
                </div>
            <?php endif; ?>
        </section>

        <!-- ARTICLES -->
        <div class="overflow-x-auto py-6">
            <table class="min-w-full divide-y text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-3 py-3">Désignation</th>
                        <th class="px-3 py-3 text-right">Quantité</th>
                        <th class="px-3 py-3">Unité</th>
                        <th class="px-3 py-3 text-right">Prix unitaire</th>
                        <th class="px-3 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td class="px-3 py-3">
                                <div class="font-medium"><?= $escape($item['name'] ?? 'Produit') ?></div>
                                <?php if (!empty($item['note'])): ?>
                                    <div class="text-xs text-slate-500"><?= $escape($item['note']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-3 text-right"><?= number_format((float) ($item['quantity'] ?? 0), 2, ',', ' ') ?></td>
                            <td class="px-3 py-3"><?= $escape($item['unit'] ?? '') ?></td>
                            <td class="px-3 py-3 text-right"><?= number_format((float) ($item['unit_price'] ?? 0), 0, ',', ' ') ?> FBu</td>
                            <td class="px-3 py-3 text-right font-semibold"><?= number_format((float) ($item['line_total'] ?? 0), 0, ',', ' ') ?> FBu</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- TOTAUX -->
        <dl class="ml-auto max-w-sm space-y-2 border-t border-slate-200 pt-5 text-sm">
            <div class="flex justify-between">
                <dt class="text-slate-600">Sous-total HT</dt>
                <dd><?= number_format($subtotal, 0, ',', ' ') ?> FBu</dd>
            </div>
            <?php if ($discountAmt > 0): ?>
                <div class="flex justify-between text-rose-700">
                    <dt>Remise</dt>
                    <dd>− <?= number_format($discountAmt, 0, ',', ' ') ?> FBu</dd>
                </div>
            <?php endif; ?>
            <div class="flex justify-between">
                <dt class="text-slate-600">TVA (<?= number_format($taxRate, 0) ?>%)</dt>
                <dd><?= number_format($taxAmt, 0, ',', ' ') ?> FBu</dd>
            </div>
            <div class="flex justify-between border-t pt-3 text-base font-bold">
                <dt>Total TTC</dt>
                <dd><?= number_format($total, 0, ',', ' ') ?> FBu</dd>
            </div>
            <?php if ($deposit > 0): ?>
                <div class="flex justify-between text-green-800">
                    <dt>Acompte reçu</dt>
                    <dd><?= number_format($deposit, 0, ',', ' ') ?> FBu</dd>
                </div>
                <div class="flex justify-between font-semibold <?= $remaining > 0 ? 'text-rose-700' : '' ?>">
                    <dt>Reste à payer</dt>
                    <dd><?= number_format($remaining, 0, ',', ' ') ?> FBu</dd>
                </div>
            <?php endif; ?>
        </dl>

        <?php if (!empty($order['description'])): ?>
            <div class="mt-6 rounded border border-slate-200 bg-slate-50 p-4 text-sm">
                <p class="text-xs font-semibold uppercase text-slate-500">Notes</p>
                <p class="mt-1 text-slate-700"><?= nl2br($escape($order['description'])) ?></p>
            </div>
        <?php endif; ?>

        <footer class="mt-10 border-t border-slate-200 pt-4 text-center text-xs text-slate-500">
            Document non contractuel — Bon de commande à valider par la réception.
        </footer>
    </article>
</section>

<style>
@media print {
    body { background: #fff !important; }
    aside, header, footer, nav, .print\:hidden { display: none !important; }
    main { padding: 0 !important; }
    article { box-shadow: none !important; }
}
</style>