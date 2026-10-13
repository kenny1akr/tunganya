<?php
if (!defined('ROUTER_LOADED')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

if (!userCan($user, 'fournisseurs.create')) {
    http_response_code(403);
    echo '<div class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</div>';
    return;
}

require_once __DIR__ . '/../../api/database.php';
$db = Database::getInstance();
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$role = $user['role_system'];
$errors = [];
$values = ['name' => '', 'phone' => '', 'email' => '', 'address' => '', 'RC' => '', 'NIF' => ''];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    foreach ($values as $field => $default) {
        $values[$field] = trim((string) ($_POST[$field] ?? $default));
    }
    $csrfToken = (string) ($_POST['csrf_token'] ?? '');

    if (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $errors[] = 'Le formulaire a expiré. Rechargez la page puis réessayez.';
    }
    if ($values['name'] === '' || mb_strlen($values['name']) > 100) {
        $errors[] = 'Le nom du fournisseur est obligatoire et limité à 100 caractères.';
    }
    if ($values['email'] !== '' && (!filter_var($values['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($values['email']) > 255)) {
        $errors[] = 'Saisissez une adresse email valide (255 caractères maximum).';
    }
    if (mb_strlen($values['phone']) > 255 || mb_strlen($values['address']) > 1000 || mb_strlen($values['RC']) > 100 || mb_strlen($values['NIF']) > 255) {
        $errors[] = 'Un ou plusieurs champs dépassent la longueur autorisée.';
    }

    if (!$errors && $values['email'] !== '') {
        $existingSupplier = $db->fetch(
            "SELECT id FROM partners WHERE category = 'fournisseur' AND email = ? LIMIT 1",
            [$values['email']]
        );
        if ($existingSupplier) {
            $errors[] = 'Un fournisseur utilise déjà cette adresse email.';
        }
    }

    if (!$errors) {
        try {
            $db->execute(
                "INSERT INTO partners (name, category, address, phone, email, RC, NIF)
                 VALUES (?, 'fournisseur', ?, ?, ?, ?, ?)",
                [
                    $values['name'],
                    $values['address'],
                    $values['phone'],
                    $values['email'],
                    $values['RC'],
                    $values['NIF'],
                ]
            );
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Le fournisseur « ' . $values['name'] . ' » a été ajouté.'];
            header('Location: ' . app_route_url('fournisseurs/liste'));
            exit;
        } catch (Throwable $error) {
            error_log('Supplier creation failed: ' . $error->getMessage());
            $errors[] = 'Le fournisseur n’a pas pu être enregistré. Vérifiez les informations puis réessayez.';
        }
    }
}
?>
<section class="mx-auto max-w-3xl space-y-5">
    <header>
        <a href="<?= $escape(app_route_url('fournisseurs/liste')) ?>" class="inline-flex items-center gap-2 text-sm font-medium text-green-800 hover:underline"><i data-feather="arrow-left" class="h-4 w-4"></i> Liste des fournisseurs</a>
        <div class="mt-3"><p class="text-sm font-semibold text-green-800">Achats & approvisionnement</p><h1 class="mt-1 text-2xl font-bold text-slate-900">Ajouter un fournisseur</h1><p class="mt-1 text-sm text-slate-600">Renseignez les informations de contact et d’identification.</p></div>
    </header>

    <?php if ($errors): ?>
        <div role="alert" class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><ul class="list-disc space-y-1 pl-5"><?php foreach (array_unique($errors) as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form method="post" class="space-y-5 rounded-md border border-slate-200 bg-white p-5 sm:p-6">
        <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_token']) ?>">
        <div class="grid gap-4 sm:grid-cols-2">
            <label class="space-y-1.5 text-sm font-medium text-slate-700 sm:col-span-2">Nom du fournisseur <span class="text-red-600">*</span><input name="name" required maxlength="100" autocomplete="organization" value="<?= $escape($values['name']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20"></label>
            <label class="space-y-1.5 text-sm font-medium text-slate-700">Téléphone<input name="phone" maxlength="255" autocomplete="tel" value="<?= $escape($values['phone']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label>
            <label class="space-y-1.5 text-sm font-medium text-slate-700">Email<input type="email" name="email" maxlength="255" autocomplete="email" value="<?= $escape($values['email']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label>
            <label class="space-y-1.5 text-sm font-medium text-slate-700 sm:col-span-2">Adresse<textarea name="address" maxlength="1000" rows="3" autocomplete="street-address" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"><?= $escape($values['address']) ?></textarea></label>
            <label class="space-y-1.5 text-sm font-medium text-slate-700">Registre de commerce (RC)<input name="RC" maxlength="100" value="<?= $escape($values['RC']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label>
            <label class="space-y-1.5 text-sm font-medium text-slate-700">NIF<input name="NIF" maxlength="255" value="<?= $escape($values['NIF']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label>
        </div>
        <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:justify-end">
            <a href="<?= $escape(app_route_url('fournisseurs/liste')) ?>" class="inline-flex justify-center rounded-md border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Annuler</a>
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-md bg-green-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-800"><i data-feather="user-plus" class="h-4 w-4"></i> Enregistrer le fournisseur</button>
        </div>
    </form>
</section>