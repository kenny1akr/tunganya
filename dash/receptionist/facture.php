<?php
if (!defined('ROUTER_LOADED')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

if (!userCan($user, 'factures.view')) {
    http_response_code(403);
    echo '<div class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</div>';
    return;
}

require_once __DIR__ . '/../../api/database.php';
require_once __DIR__ . '/_sales.php';

$db        = Database::getInstance();
$invoiceId = (int) ($_GET['id'] ?? 0);

// NOTE: origin filter removed — accepts Factures from any source (Vente comptoir, Commande…)
$invoice = $db->fetch(
    "SELECT * FROM sales
     WHERE id = ? AND document_type = 'Facture'
     LIMIT 1",
    [$invoiceId]
);

if (!$invoice) {
    http_response_code(404);
    echo '<div class="rounded-md border border-amber-200 bg-amber-50 p-4 text-amber-900">Facture introuvable.</div>';
    return;
}

$items = json_decode((string) ($invoice['products_json'] ?: $invoice['medicines']), true);
if (!is_array($items)) $items = [];

$details = $db->fetch(
    "SELECT doc_fields FROM documents
     WHERE doc_number = ? AND doc_type = 'Facture'
     ORDER BY id DESC LIMIT 1",
    [$invoice['invoice_number']]
);
$invoiceDetails = $details ? json_decode((string) $details['doc_fields'], true) : [];
if (!is_array($invoiceDetails)) $invoiceDetails = [];
?>
<section class="mx-auto max-w-4xl space-y-5">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between print:hidden">
        <a href="<?= receptionEscape(app_route_url('ventes/factures')) ?>"
           class="inline-flex items-center gap-2 text-sm font-medium text-green-800">
            <i data-feather="arrow-left" class="h-4 w-4"></i> Factures de vente
        </a>
        <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-2 rounded-md bg-green-700 px-4 py-2.5 text-sm font-semibold text-white">
            <i data-feather="printer" class="h-4 w-4"></i> Imprimer
        </button>
    </header>

    <article class="rounded-md border border-slate-200 bg-white p-6 sm:p-10 print:border-0 print:p-0">
        <!-- EN-TÊTE -->
        <div class="flex flex-col gap-5 border-b border-slate-200 pb-6 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-widest text-green-800">DH TELECOM AND ADVERTISING HOUSE</p>
                <h1 class="mt-3 text-3xl font-bold text-slate-900">FACTURE</h1>
                <p class="mt-2 font-mono text-sm text-slate-600"><?= receptionEscape($invoice['invoice_number']) ?></p>
            </div>
            <dl class="space-y-1 text-sm sm:text-right">
                <div>
                    <dt class="inline text-slate-500">Date : </dt>
                    <dd class="inline font-medium"><?= receptionEscape(date('d/m/Y', strtotime($invoice['date']))) ?></dd>
                </div>
                <div>
                    <dt class="inline text-slate-500">Paiement : </dt>
                    <dd class="inline font-medium"><?= receptionEscape($invoice['payment_method']) ?></dd>
                </div>
                <div>
                    <dt class="inline text-slate-500">Émis par : </dt>
                    <dd class="inline font-medium"><?= receptionEscape($invoice['agent'] ?: $invoice['operator']) ?></dd>
                </div>
                <?php if (!empty($invoice['origin']) && $invoice['origin'] !== 'Vente comptoir'): ?>
                    <div>
                        <dt class="inline text-slate-500">Origine : </dt>
                        <dd class="inline font-medium"><?= receptionEscape($invoice['origin']) ?></dd>
                    </div>
                <?php endif; ?>
                <?php if (!empty($invoiceDetails['payment_terms'])): ?>
                    <div>
                        <dt class="inline text-slate-500">Conditions : </dt>
                        <dd class="inline font-medium"><?= receptionEscape($invoiceDetails['payment_terms']) ?></dd>
                    </div>
                <?php endif; ?>
                <?php if (!empty($invoiceDetails['due_date'])): ?>
                    <div>
                        <dt class="inline text-slate-500">Échéance : </dt>
                        <dd class="inline font-medium"><?= receptionEscape(date('d/m/Y', strtotime($invoiceDetails['due_date']))) ?></dd>
                    </div>
                <?php endif; ?>
            </dl>
        </div>

        <!-- CLIENT -->
        <section class="border-b border-slate-200 py-6">
            <p class="text-xs font-semibold uppercase text-slate-500">Facturé à</p>
            <p class="mt-2 text-lg font-semibold text-slate-900"><?= receptionEscape($invoice['client_name']) ?></p>
            <?php if (!empty($invoice['phone'])): ?>
                <p class="mt-1 text-sm text-slate-600"><?= receptionEscape($invoice['phone']) ?></p>
            <?php endif; ?>
            <?php if (!empty($invoiceDetails['from_task'])): ?>
                <p class="mt-1 text-xs text-slate-500">Réf. commande : <?= receptionEscape($invoiceDetails['from_task']) ?></p>
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
                                <div class="font-medium"><?= receptionEscape($item['name'] ?? 'Produit') ?></div>
                                <?php if (!empty($item['note'])): ?>
                                    <div class="text-xs text-slate-500"><?= receptionEscape($item['note']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-3 text-right"><?= number_format((float) ($item['quantity'] ?? 0), 2, ',', ' ') ?></td>
                            <td class="px-3 py-3"><?= receptionEscape($item['unit'] ?? '') ?></td>
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
                <dd><?= number_format((float) $invoice['tot_htva'], 0, ',', ' ') ?> FBu</dd>
            </div>
            <?php if (!empty($invoiceDetails['discount']) && (float) $invoiceDetails['discount'] > 0): ?>
                <div class="flex justify-between text-rose-700">
                    <dt>Remise</dt>
                    <dd>− <?= number_format((float) $invoiceDetails['discount'], 0, ',', ' ') ?> FBu</dd>
                </div>
            <?php endif; ?>
            <div class="flex justify-between">
                <dt class="text-slate-600">TVA</dt>
                <dd><?= number_format((float) $invoice['tva'], 0, ',', ' ') ?> FBu</dd>
            </div>
            <div class="flex justify-between border-t pt-3 text-base font-bold">
                <dt>Total TTC</dt>
                <dd><?= number_format((float) $invoice['total_amount'], 0, ',', ' ') ?> FBu</dd>
            </div>
            <div class="flex justify-between text-green-800">
                <dt>Montant reçu</dt>
                <dd><?= number_format((float) $invoice['netcash'], 0, ',', ' ') ?> FBu</dd>
            </div>
            <div class="flex justify-between font-semibold <?= (float) $invoice['debt'] > 0 ? 'text-rose-700' : '' ?>">
                <dt>Reste à payer</dt>
                <dd><?= number_format((float) $invoice['debt'], 0, ',', ' ') ?> FBu</dd>
            </div>
            <?php if (!empty($invoiceDetails['bank'])): ?>
                <div class="pt-1 text-right text-xs text-slate-500">Compte : <?= receptionEscape($invoiceDetails['bank']) ?></div>
            <?php endif; ?>
        </dl>

        <!-- NOTES -->
        <?php if (!empty($invoice['guard'])): ?>
            <div class="mt-6 rounded border border-slate-200 bg-slate-50 p-4 text-sm">
                <p class="text-xs font-semibold uppercase text-slate-500">Notes</p>
                <p class="mt-1 text-slate-700"><?= nl2br(receptionEscape($invoice['guard'])) ?></p>
            </div>
        <?php endif; ?>

        <footer class="mt-10 border-t border-slate-200 pt-4 text-center text-xs text-slate-500">
            Merci pour votre confiance.
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