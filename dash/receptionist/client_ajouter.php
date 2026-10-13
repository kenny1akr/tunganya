<?php
if (!defined('ROUTER_LOADED')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

if (!userCan($user, 'clients.create')) {
    http_response_code(403);
    echo '<p class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</p>';
    return;
}

require_once __DIR__ . '/../../api/database.php';
$db = Database::getInstance();
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$role = $user['role_system'];
$errors = [];
$values = [
    'name' => '', 'phone' => '', 'email' => '', 'address' => '',
    'RC' => '', 'NIF' => '', 'annif' => '', 'portal_username' => '',
];
$portalPassword = '';
$portalPasswordConfirm = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    foreach ($values as $field => $default) {
        $values[$field] = trim((string) ($_POST[$field] ?? $default));
    }
    $values['portal_username'] = strtolower($values['portal_username']);
    $portalPassword = (string) ($_POST['portal_password'] ?? '');
    $portalPasswordConfirm = (string) ($_POST['portal_password_confirm'] ?? '');

    $csrfToken = (string) ($_POST['csrf_token'] ?? '');
    if (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $errors[] = 'Le formulaire a expiré. Rechargez la page puis réessayez.';
    }
    if ($values['name'] === '' || mb_strlen($values['name']) > 100) {
        $errors[] = 'Le nom du client est obligatoire et limité à 100 caractères.';
    }
    if ($values['email'] !== '' && (!filter_var($values['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($values['email']) > 255)) {
        $errors[] = 'Saisissez une adresse email valide (255 caractères maximum).';
    }
    if ($values['phone'] !== '' && mb_strlen($values['phone']) > 255) {
        $errors[] = 'Le numéro de téléphone est trop long.';
    }
    if (!preg_match('/^[a-z0-9._-]{3,100}$/', $values['portal_username'])) {
        $errors[] = 'Le nom d’utilisateur doit contenir 3 à 100 caractères (lettres, chiffres, point, tiret ou tiret bas).';
    }
    if (mb_strlen($portalPassword) < 8) {
        $errors[] = 'Le mot de passe du portail doit contenir au moins 8 caractères.';
    }
    if (strlen($portalPassword) > 72) {
        $errors[] = 'Le mot de passe ne peut pas dépasser 72 octets.';
    }
    if ($portalPassword !== $portalPasswordConfirm) {
        $errors[] = 'Les deux mots de passe ne correspondent pas.';
    }
    if ($values['annif'] !== '') {
        $birthday = DateTime::createFromFormat('!d-m', $values['annif']);
        if (!$birthday || $birthday->format('d-m') !== $values['annif']) {
            $errors[] = 'L’anniversaire doit respecter le format JJ-MM.';
        }
    }

    if (!$errors) {
        $existingUsername = $db->fetch(
            "SELECT id FROM partners WHERE portal_username = ? LIMIT 1",
            [$values['portal_username']]
        );
        if ($existingUsername) {
            $errors[] = 'Ce nom d’utilisateur est déjà utilisé.';
        }
        if ($values['email'] !== '') {
            $existingClient = $db->fetch("SELECT id FROM partners WHERE category = 'client' AND email = ? LIMIT 1", [$values['email']]);
            if ($existingClient) {
                $errors[] = 'Un client utilise déjà cette adresse email.';
            }
        }
    }

    if (!$errors) {
        try {
            $db->execute(
                "INSERT INTO partners (name, category, address, phone, email, RC, NIF, status_client, total_achats, frequence_achats, annif, portal_username, portal_password)
                 VALUES (?, 'client', ?, ?, ?, ?, ?, 'Nouveau', 0, 0, ?, ?, ?)",
                [
                    $values['name'], $values['address'], $values['phone'], $values['email'],
                    $values['RC'], $values['NIF'], $values['annif'] ?: null,
                    $values['portal_username'], password_hash($portalPassword, PASSWORD_DEFAULT),
                ]
            );
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Le client « ' . $values['name'] . ' » a été ajouté. Identifiant portail : ' . $values['portal_username'] . '.'];
            header('Location: ' . app_route_url('clients/liste'));
            exit;
        } catch (Throwable $error) {
            error_log('Client creation failed: ' . $error->getMessage());
            $errors[] = 'Le client n’a pas pu être enregistré. Vérifiez les informations puis réessayez.';
        }
    }
}
?>
<section class="mx-auto max-w-3xl space-y-5">
    <header>
        <a href="<?= $escape(app_route_url('clients/liste')) ?>" class="inline-flex items-center gap-2 text-sm font-medium text-green-800 hover:underline"><i data-feather="arrow-left" class="h-4 w-4"></i> Liste des clients</a>
        <div class="mt-3"><p class="text-sm font-semibold text-green-800">Répertoire clients</p><h1 class="mt-1 text-2xl font-bold text-slate-900">Ajouter un client</h1><p class="mt-1 text-sm text-slate-600">Le nouveau client sera créé avec le statut « Nouveau ».</p></div>
    </header>

    <?php if ($errors): ?>
        <div role="alert" class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><ul class="list-disc space-y-1 pl-5"><?php foreach (array_unique($errors) as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form method="post" class="space-y-5 rounded-md border border-slate-200 bg-white p-5 sm:p-6">
        <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_token']) ?>">
        <div class="grid gap-4 sm:grid-cols-2">
            <label class="space-y-1 text-sm font-medium text-slate-700 sm:col-span-2">Nom du client <span class="text-red-600">*</span><input name="name" maxlength="100" required value="<?= $escape($values['name']) ?>" autocomplete="organization" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20"></label>
            <label class="space-y-1 text-sm font-medium text-slate-700">Téléphone<input name="phone" maxlength="255" value="<?= $escape($values['phone']) ?>" autocomplete="tel" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label>
            <label class="space-y-1 text-sm font-medium text-slate-700">Email<input name="email" type="email" maxlength="255" value="<?= $escape($values['email']) ?>" autocomplete="email" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label>
            <label class="space-y-1 text-sm font-medium text-slate-700 sm:col-span-2">Adresse<textarea name="address" maxlength="1000" rows="3" autocomplete="street-address" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"><?= $escape($values['address']) ?></textarea></label>
            <label class="space-y-1 text-sm font-medium text-slate-700">Registre de commerce (RC)<input name="RC" maxlength="100" value="<?= $escape($values['RC']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label>
            <label class="space-y-1 text-sm font-medium text-slate-700">NIF<input name="NIF" maxlength="255" value="<?= $escape($values['NIF']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label>
            <label class="space-y-1 text-sm font-medium text-slate-700">Anniversaire (JJ-MM)<input name="annif" maxlength="5" placeholder="ex. 24-08" pattern="[0-3][0-9]-[0-1][0-9]" value="<?= $escape($values['annif']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label>
            <div class="space-y-3 border-t border-slate-100 pt-4 sm:col-span-2">
                <div><h2 class="font-semibold text-slate-900">Accès au portail client</h2><p class="mt-1 text-xs text-slate-500">Le client utilisera ces identifiants pour consulter ses commandes.</p></div>
                <label class="block space-y-1 text-sm font-medium text-slate-700">Nom d’utilisateur <span class="text-red-600">*</span><input name="portal_username" maxlength="100" minlength="3" pattern="[A-Za-z0-9._-]{3,100}" required autocomplete="username" value="<?= $escape($values['portal_username']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20"><span class="block text-xs font-normal text-slate-500">3 à 100 caractères : lettres, chiffres, point, tiret ou tiret bas.</span></label>
                <label class="block space-y-1 text-sm font-medium text-slate-700">Mot de passe <span class="text-red-600">*</span><input name="portal_password" type="password" minlength="8" maxlength="72" required autocomplete="new-password" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20"><span class="block text-xs font-normal text-slate-500">8 caractères minimum, 72 octets maximum. Le mot de passe sera stocké sous forme de hash.</span></label>
                <label class="block space-y-1 text-sm font-medium text-slate-700">Confirmer le mot de passe <span class="text-red-600">*</span><input name="portal_password_confirm" type="password" minlength="8" maxlength="72" required autocomplete="new-password" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal"></label>
            </div>
        </div>
        <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:justify-end">
            <a href="<?= $escape(app_route_url('clients/liste')) ?>" class="inline-flex justify-center rounded-md border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Annuler</a>
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-md bg-green-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-800"><i data-feather="user-plus" class="h-4 w-4"></i> Enregistrer le client</button>
        </div>
    </form>
</section>