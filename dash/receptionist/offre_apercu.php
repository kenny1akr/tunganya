<?php
if (!defined('ROUTER_LOADED')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}
if (!userCan($user, 'offres.view')) {
    http_response_code(403);
    echo '<p class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</p>';
    return;
}

require_once __DIR__ . '/../../api/database.php';
require_once __DIR__ . '/_sales.php';

$db     = Database::getInstance();
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$offerId = (int) ($_GET['id'] ?? 0);
$offer = $db->fetch(
    "SELECT * FROM sales WHERE id = ? AND document_type = 'Devis' AND status = 'Active' LIMIT 1",
    [$offerId]
);
if (!$offer) {
    http_response_code(404);
    echo '<div class="rounded-md border border-amber-200 bg-amber-50 p-4 text-amber-900">Offre introuvable.</div>';
    return;
}

$items = json_decode((string) ($offer['products_json'] ?? $offer['medicines']), true);
if (!is_array($items)) $items = [];

$company = $db->fetch("SELECT company_name, company_logo FROM app_settings WHERE id = 1 LIMIT 1") ?: [];
$companyName = $company['company_name'] ?? 'DH TELECOM AND ADVERTISING HOUSE';
$companyLogo = $company['company_logo'] ?? '';

$st = $offer['offer_status'] ?: 'Brouillon';
$statusColor = match ($st) {
    'Brouillon' => 'bg-slate-100 text-slate-700',
    'Envoyée'   => 'bg-blue-100 text-blue-800',
    'Acceptée'  => 'bg-green-100 text-green-800',
    'Refusée'   => 'bg-rose-100 text-rose-800',
    'Expirée'   => 'bg-amber-100 text-amber-800',
    default     => 'bg-slate-100 text-slate-600',
};
?>
<section class="mx-auto max-w-4xl space-y-5">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between print:hidden">
        <a href="<?= $escape(app_route_url('offres/liste')) ?>" class="inline-flex items-center gap-2 text-sm font-medium text-green-800 hover:underline">
            <i data-feather="arrow-left" class="h-4 w-4"></i> Liste des offres
        </a>
        <div class="flex gap-2">
            <button type="button" onclick="window.print()"
                    class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                <i data-feather="printer" class="h-4 w-4"></i> Imprimer
            </button>
            <?php if (userCan($user, 'offres.edit')): ?>
                <a href="<?= $escape(app_route_url('offres/modifier')) . '?id=' . (int) $offer['id'] ?>"
                   class="inline-flex items-center gap-2 rounded-md bg-green-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-800">
                    <i data-feather="edit-2" class="h-4 w-4"></i> Modifier
                </a>
            <?php endif; ?>
        </div>
    </header>

    <article class="rounded-md border border-slate-200 bg-white p-6 sm:p-10 print:border-0 print:p-0">
        <div class="flex flex-col gap-5 border-b border-slate-200 pb-6 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-start gap-3">
                <?php if ($companyLogo): ?>
                    <img src="<?= $escape($companyLogo) ?>" alt="" class="h-12 w-12 object-contain">
                <?php else: ?>
                    <div class="flex h-12 w-12 items-center justify-center rounded-md bg-green-700 text-white font-bold">DH</div>
                <?php endif; ?>
                <div>
                    <p class="text-sm font-bold uppercase tracking-widest text-green-800"><?= $escape($companyName) ?></p>
                    <h1 class="mt-3 text-3xl font-bold text-slate-900">OFFRE COMMERCIALE</h1>
                    <p class="mt-2 font-mono text-sm text-slate-600"><?= $escape($offer['invoice_number']) ?></p>
                    <span class="mt-2 inline-block rounded-full px-2.5 py-1 text-xs font-medium <?= $statusColor ?>"><?= $escape($st) ?></span>
                </div>
            </div>
            <dl class="space-y-1 text-sm sm:text-right">
                <div><dt class="inline text-slate-500">Date : </dt>
                    <dd class="inline font-medium"><?= $escape(date('d/m/Y', strtotime($offer['date']))) ?></dd></div>
                <?php if (!empty($offer['valid_until'])): ?>
                    <div><dt class="inline text-slate-500">Valable jusqu’au : </dt>
                        <dd class="inline font-medium"><?= $escape(date('d/m/Y', strtotime($offer['valid_until']))) ?></dd></div>
                <?php endif; ?>
                <div><dt class="inline text-slate-500">Émise par : </dt>
                    <dd class="inline font-medium"><?= $escape($offer['agent'] ?: $offer['operator']) ?></dd></div>
            </dl>
        </div>

        <section class="border-b border-slate-200 py-6">
            <p class="text-xs font-semibold uppercase text-slate-500">Destinataire</p>
            <p class="mt-2 text-lg font-semibold text-slate-900"><?= $escape($offer['client_name']) ?></p>
            <?php if (!empty($offer['phone'])): ?>
                <p class="mt-1 text-sm text-slate-600"><?= $escape($offer['phone']) ?></p>
            <?php endif; ?>
        </section>

        <div class="overflow-x-auto py-6">
            <table class="min-w-full divide-y text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-3 py-3">Désignation</th>
                        <th class="px-3 py-3 text-right">Qté</th>
                        <th class="px-3 py-3">Unité</th>
                        <th class="px-3 py-3 text-right">Prix unitaire</th>
                        <th class="px-3 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td class="px-3 py-3">
                                <div class="font-medium"><?= $escape($item['name'] ?? '') ?></div>
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

        <dl class="ml-auto max-w-sm space-y-2 border-t border-slate-200 pt-5 text-sm">
            <div class="flex justify-between"><dt class="text-slate-600">Sous-total HT</dt>
                <dd><?= number_format((float) $offer['tot_htva'], 0, ',', ' ') ?> FBu</dd></div>
            <?php if ((float) $offer['discount'] > 0): ?>
                <div class="flex justify-between text-rose-700"><dt>Remise</dt>
                    <dd>− <?= number_format((float) $offer['discount'], 0, ',', ' ') ?> FBu</dd></div>
            <?php endif; ?>
            <div class="flex justify-between"><dt class="text-slate-600">TVA</dt>
                <dd><?= number_format((float) $offer['tva'], 0, ',', ' ') ?> FBu</dd></div>
            <div class="flex justify-between border-t pt-3 text-base font-bold"><dt>Total TTC</dt>
                <dd><?= number_format((float) $offer['total_amount'], 0, ',', ' ') ?> FBu</dd></div>
        </dl>

        <?php if (!empty($offer['offer_notes'])): ?>
            <div class="mt-6 rounded border border-slate-200 bg-slate-50 p-4 text-sm">
                <p class="text-xs font-semibold uppercase text-slate-500">Notes / conditions</p>
                <p class="mt-1 text-slate-700"><?= nl2br($escape($offer['offer_notes'])) ?></p>
            </div>
        <?php endif; ?>

        <footer class="mt-10 border-t border-slate-200 pt-4 text-center text-xs text-slate-500">
            Offre valable selon les conditions ci-dessus. Merci pour votre confiance.
        </footer>
    </article>
</section>

<style>
@media print {
    body { background: #fff !important; }
    aside, header.print\:hidden, footer, nav, .print\:hidden { display: none !important; }
    main { padding: 0 !important; }
}
</style>