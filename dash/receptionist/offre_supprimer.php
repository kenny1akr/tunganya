<?php
if (!defined('ROUTER_LOADED')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}
if (!userCan($user, 'offres.edit')) {
    http_response_code(403);
    echo '<p class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</p>';
    return;
}

require_once __DIR__ . '/../../api/database.php';
$db = Database::getInstance();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Méthode non autorisée.');
}

if (!hash_equals($_SESSION['csrf_token'] ?? '', (string) ($_POST['csrf_token'] ?? ''))) {
    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Demande invalide.'];
    header('Location: ' . app_route_url('offres/liste'));
    exit;
}

$saleId = (int) ($_POST['sale_id'] ?? 0);

$offer = $db->fetch(
    "SELECT id, invoice_number FROM sales WHERE id = ? AND document_type = 'Devis' LIMIT 1",
    [$saleId]
);

if (!$offer) {
    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Offre introuvable.'];
    header('Location: ' . app_route_url('offres/liste'));
    exit;
}

try {
    $db->execute(
        "UPDATE sales SET status = 'Supprimée', offer_status = 'Refusée' WHERE id = ?",
        [$saleId]
    );
    $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Offre ' . $offer['invoice_number'] . ' supprimée.'];
} catch (Throwable $e) {
    error_log('Offer delete failed: ' . $e->getMessage());
    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Suppression impossible.'];
}

header('Location: ' . app_route_url('offres/liste'));
exit;