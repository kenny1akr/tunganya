<?php
if (!defined('ROUTER_LOADED')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

if (!userCan($user, 'utilisateurs.create')) {
    http_response_code(403);
    echo '<div class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</div>';
    return;
}

require_once __DIR__ . '/../../api/database.php';
$db = Database::getInstance();
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$errors = [];
$values = ['user_name' => '', 'f_name' => '', 'l_name' => '', 'email' => '', 'telephone' => '', 'category' => '', 'status' => 'Disponible'];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $field => $default) {
        $values[$field] = trim((string) ($_POST[$field] ?? $default));
    }
    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');
    $csrfToken = (string) ($_POST['csrf_token'] ?? '');

    if (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $errors[] = 'Le formulaire a expiré. Rechargez la page puis réessayez.';
    }
    if ($values['user_name'] === '' || !preg_match('/^[A-Za-z0-9._-]{3,50}$/', $values['user_name'])) {
        $errors[] = 'L’identifiant doit contenir entre 3 et 50 caractères (lettres, chiffres, point, tiret ou souligné).';
    }
    if ($values['f_name'] === '' || $values['l_name'] === '') {
        $errors[] = 'Le prénom et le nom sont obligatoires.';
    }
    if ($values['email'] !== '' && !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Adresse email invalide.';
    }
    if (!isset($ROLES[$values['category']])) {
        $errors[] = 'Sélectionnez un rôle valide.';
    }
    if (!in_array($values['status'], ['Disponible', 'Désactivé'], true)) {
        $errors[] = 'Sélectionnez un statut valide.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
    }
    if ($password !== $passwordConfirm) {
        $errors[] = 'Les deux mots de passe ne correspondent pas.';
    }

    if (!$errors) {
        $existingUser = $db->fetch(
            'SELECT id FROM users WHERE user_name = ? OR (? <> "" AND email = ?) LIMIT 1',
            [$values['user_name'], $values['email'], $values['email']]
        );
        if ($existingUser) {
            $errors[] = 'Cet identifiant ou cette adresse email est déjà utilisé.';
        }
    }

    if (!$errors) {
        $db->execute(
            'INSERT INTO users (user_name, password, category, f_name, l_name, email, telephone, status, level)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $values['user_name'],
                password_hash($password, PASSWORD_DEFAULT),
                $values['category'],
                $values['f_name'],
                $values['l_name'],
                $values['email'] !== '' ? $values['email'] : null,
                $values['telephone'] !== '' ? $values['telephone'] : null,
                $values['status'],
                $ROLES[$values['category']]['label'],
            ]
        );

        $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Le compte utilisateur a été créé.'];
        header('Location: ' . app_route_url('utilisateurs/liste'));
        exit;
    }
}
?>
<section class="mx-auto max-w-3xl space-y-5">
    <header>
        <a href="<?= $escape(app_route_url('utilisateurs/liste')) ?>" class="inline-flex items-center gap-2 text-sm font-medium text-green-800 hover:underline"><i data-feather="arrow-left" class="h-4 w-4"></i> Retour aux utilisateurs</a>
        <h1 class="mt-3 text-2xl font-bold text-slate-900">Ajouter un utilisateur</h1>
        <p class="mt-1 text-sm text-slate-600">Créez un compte et attribuez-lui un rôle d’accès.</p>
    </header>

    <?php if ($errors): ?>
        <div role="alert" class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><ul class="list-disc space-y-1 pl-5"><?php foreach ($errors as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form method="post" class="space-y-5 rounded-md border border-slate-200 bg-white p-5 sm:p-6">
        <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_token']) ?>">
        <div class="grid gap-4 sm:grid-cols-2">
            <label class="space-y-1.5 text-sm font-medium text-slate-700">Prénom <span class="text-red-600">*</span><input name="f_name" required maxlength="100" value="<?= $escape($values['f_name']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20"></label>
            <label class="space-y-1.5 text-sm font-medium text-slate-700">Nom <span class="text-red-600">*</span><input name="l_name" required maxlength="100" value="<?= $escape($values['l_name']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20"></label>
            <label class="space-y-1.5 text-sm font-medium text-slate-700">Identifiant <span class="text-red-600">*</span><input name="user_name" required minlength="3" maxlength="50" autocomplete="username" value="<?= $escape($values['user_name']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20"></label>
            <label class="space-y-1.5 text-sm font-medium text-slate-700">Rôle <span class="text-red-600">*</span><select name="category" required class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 font-normal focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20"><option value="">Choisir un rôle</option><?php foreach ($ROLES as $roleName => $roleDefinition): ?><option value="<?= $escape($roleName) ?>" <?= $values['category'] === $roleName ? 'selected' : '' ?>><?= $escape($roleDefinition['label']) ?></option><?php endforeach; ?></select></label>
            <label class="space-y-1.5 text-sm font-medium text-slate-700">Adresse email<input type="email" name="email" maxlength="100" autocomplete="email" value="<?= $escape($values['email']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20"></label>
            <label class="space-y-1.5 text-sm font-medium text-slate-700">Téléphone<input name="telephone" maxlength="100" autocomplete="tel" value="<?= $escape($values['telephone']) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20"></label>
            <label class="space-y-1.5 text-sm font-medium text-slate-700">Mot de passe <span class="text-red-600">*</span><input type="password" name="password" required minlength="8" autocomplete="new-password" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20"></label>
            <label class="space-y-1.5 text-sm font-medium text-slate-700">Confirmer le mot de passe <span class="text-red-600">*</span><input type="password" name="password_confirm" required minlength="8" autocomplete="new-password" class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20"></label>
            <label class="space-y-1.5 text-sm font-medium text-slate-700 sm:col-span-2">Statut<select name="status" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 font-normal focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20"><option value="Disponible" <?= $values['status'] === 'Disponible' ? 'selected' : '' ?>>Actif</option><option value="Désactivé" <?= $values['status'] === 'Désactivé' ? 'selected' : '' ?>>Désactivé</option></select></label>
        </div>
        <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:justify-end">
            <a href="<?= $escape(app_route_url('utilisateurs/liste')) ?>" class="inline-flex justify-center rounded-md border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Annuler</a>
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-md bg-green-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-800"><i data-feather="user-plus" class="h-4 w-4"></i> Créer le compte</button>
        </div>
    </form>
</section>