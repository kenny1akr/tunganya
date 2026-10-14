<?php
if (!defined('ROUTER_LOADED')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

if (!userCan($user, 'dashboard.view')) {
    http_response_code(403);
    echo '<p class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</p>';
    return;
}

require_once __DIR__ . '/../../api/database.php';
require_once __DIR__ . '/_sales.php';

$db     = Database::getInstance();
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$errors = [];

receptionEnsureCsrf();

$userId = (int) ($user['utilisateur_id'] ?? 0);

// =========================================================================
// Traitement POST
// =========================================================================
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    $csrf   = (string) ($_POST['csrf_token'] ?? '');

    if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
        $errors[] = 'La demande a expiré. Rechargez la page.';
    }

    // ---------------------------------------------------------------------
    // Mise à jour du profil
    // ---------------------------------------------------------------------
    if (!$errors && $action === 'update_profile') {
        $fName     = trim((string) ($_POST['f_name'] ?? ''));
        $lName     = trim((string) ($_POST['l_name'] ?? ''));
        $telephone = trim((string) ($_POST['telephone'] ?? ''));
        $email     = trim((string) ($_POST['email'] ?? ''));
        $adresse   = trim((string) ($_POST['adress'] ?? ''));

        if ($fName === '' && $lName === '') {
            $errors[] = 'Indiquez au moins votre prénom ou votre nom.';
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L’adresse email est invalide.';
        }
        if (mb_strlen($telephone) > 100) $errors[] = 'Téléphone trop long.';
        if (mb_strlen($adresse) > 100)   $errors[] = 'Adresse trop longue.';

        if (!$errors) {
            try {
                $db->execute(
                    "UPDATE users SET f_name = ?, l_name = ?, telephone = ?, email = ?, adress = ?, updated_at = NOW() WHERE id = ?",
                    [
                        mb_substr($fName, 0, 100),
                        mb_substr($lName, 0, 100),
                        mb_substr($telephone, 0, 100),
                        mb_substr($email, 0, 100),
                        mb_substr($adresse, 0, 100),
                        $userId,
                    ]
                );

                // Mettre à jour la session
                $_SESSION['user_name_full'] = trim($fName . ' ' . $lName);

                $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Profil mis à jour.'];
                header('Location: ' . app_route_url('profile'));
                exit;
            } catch (Throwable $e) {
                error_log('Profile update failed: ' . $e->getMessage());
                $errors[] = 'Impossible de mettre à jour le profil.';
            }
        }
    }

    // ---------------------------------------------------------------------
    // Changement de mot de passe
    // ---------------------------------------------------------------------
    if (!$errors && $action === 'change_password') {
        $current = (string) ($_POST['current_password'] ?? '');
        $new     = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        if ($current === '')  $errors[] = 'Saisissez votre mot de passe actuel.';
        if (strlen($new) < 8) $errors[] = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
        if ($new !== $confirm) $errors[] = 'Les deux mots de passe ne correspondent pas.';

        if (!$errors) {
            $record = $db->fetch("SELECT password FROM users WHERE id = ? LIMIT 1", [$userId]);
            $hash   = $record['password'] ?? '';

            if (!password_verify($current, $hash)) {
                $errors[] = 'Le mot de passe actuel est incorrect.';
            } else {
                try {
                    $db->execute(
                        "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?",
                        [password_hash($new, PASSWORD_DEFAULT), $userId]
                    );
                    $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Mot de passe modifié.'];
                    header('Location: ' . app_route_url('profile'));
                    exit;
                } catch (Throwable $e) {
                    error_log('Password change failed: ' . $e->getMessage());
                    $errors[] = 'Impossible de changer le mot de passe.';
                }
            }
        }
    }

    // ---------------------------------------------------------------------
    // Mise à jour de l'avatar (upload)
    // ---------------------------------------------------------------------
    if (!$errors && $action === 'update_avatar') {
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE) {
            $errors[] = 'Aucun fichier sélectionné.';
        } elseif ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Erreur lors du téléchargement (code ' . $_FILES['avatar']['error'] . ').';
        } else {
            $tmp = $_FILES['avatar']['tmp_name'];
            $size = (int) $_FILES['avatar']['size'];
            $mime = mime_content_type($tmp);

            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
            if (!isset($allowed[$mime])) {
                $errors[] = 'Format non autorisé. Utilisez JPG, PNG, WEBP ou GIF.';
            } elseif ($size > 2 * 1024 * 1024) {
                $errors[] = 'Image trop lourde (max 2 Mo).';
            } else {
                $ext      = $allowed[$mime];
                $filename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
                $uploadDir = __DIR__ . '/../../uploads/avatars';

                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0775, true);
                }

                $target = $uploadDir . '/' . $filename;

                if (move_uploaded_file($tmp, $target)) {
                    $webPath = app_url('uploads/avatars/' . $filename);
                    try {
                        $db->execute(
                            "UPDATE users SET profil = ?, updated_at = NOW() WHERE id = ?",
                            [$webPath, $userId]
                        );
                        $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Photo de profil mise à jour.'];
                        header('Location: ' . app_route_url('profile'));
                        exit;
                    } catch (Throwable $e) {
                        error_log('Avatar DB update failed: ' . $e->getMessage());
                        @unlink($target);
                        $errors[] = 'Impossible d’enregistrer la photo.';
                    }
                } else {
                    $errors[] = 'Impossible de déplacer le fichier. Vérifiez les permissions du dossier uploads/.';
                }
            }
        }
    }
}

// =========================================================================
// Recharger les données utilisateur
// =========================================================================
$me = $db->fetch(
    "SELECT id, user_name, category, f_name, l_name, telephone, email, adress,
            level, salary, status, profil, admission, last_login, created_at
     FROM users
     WHERE id = ?
     LIMIT 1",
    [$userId]
);

if (!$me) {
    echo '<div class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Utilisateur introuvable.</div>';
    return;
}

// =========================================================================
// Statistiques personnelles
// =========================================================================

// Nombre de ventes effectuées par cet utilisateur
$stats = [
    'ventes_count'    => 0,
    'ventes_total'    => 0,
    'commandes_count' => 0,
    'factures_count'  => 0,
];

try {
    $stats['ventes_count'] = (int) $db->fetchColumn(
        "SELECT COUNT(*) FROM sales WHERE operator = ? AND status = 'Active'",
        [$me['user_name']]
    );
    $stats['ventes_total'] = (float) $db->fetchColumn(
        "SELECT COALESCE(SUM(total_amount), 0) FROM sales WHERE operator = ? AND status = 'Active'",
        [$me['user_name']]
    );
    $stats['commandes_count'] = (int) $db->fetchColumn(
        "SELECT COUNT(*) FROM tasks WHERE created_by = ? AND category = 'Commande'",
        [$me['user_name']]
    );
    $stats['factures_count'] = (int) $db->fetchColumn(
        "SELECT COUNT(*) FROM sales WHERE operator = ? AND document_type = 'Facture' AND status = 'Active'",
        [$me['user_name']]
    );
} catch (Throwable $e) {
    error_log('Profile stats failed: ' . $e->getMessage());
}

// Dernières activités
$recentActivity = [];
try {
    $recentActivity = $db->fetchAll(
        "SELECT action_type, descrip, created_at, type
         FROM logs
         WHERE user_id = ?
         ORDER BY created_at DESC
         LIMIT 10",
        [$me['user_name']]
    );
} catch (Throwable $e) { error_log('Profile activity failed: ' . $e->getMessage()); }

$flashMessage = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);

$roleLabel = function_exists('getRoleLabel') ? getRoleLabel($user['role_system']) : $user['role_system'];
$roleColor = function_exists('getRoleColor') ? getRoleColor($user['role_system']) : 'slate';

$fullName = trim(($me['f_name'] ?? '') . ' ' . ($me['l_name'] ?? '')) ?: $me['user_name'];
$initial  = strtoupper(mb_substr($fullName, 0, 1));

$avatarUrl = !empty($me['profil']) ? $me['profil'] : null;
?>
<section class="mx-auto max-w-5xl space-y-5">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-green-800">Mon compte</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Mon profil</h1>
            <p class="mt-1 text-sm text-slate-600">Gérez vos informations personnelles et votre sécurité.</p>
        </div>
    </header>

    <?php if ($flashMessage): ?>
        <div role="status" class="rounded-md border px-4 py-3 text-sm <?= ($flashMessage['type'] ?? '') === 'success' ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-800' ?>">
            <?= $escape($flashMessage['text'] ?? '') ?>
        </div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div role="alert" class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc space-y-1 pl-5">
                <?php foreach (array_unique($errors) as $e): ?><li><?= $escape($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Carte identité -->
    <article class="overflow-hidden rounded-md border border-slate-200 bg-white">
        <div class="bg-gradient-to-r from-green-700 to-green-500 px-6 py-8">
            <div class="flex flex-col items-center gap-4 text-center sm:flex-row sm:text-left">
                <div class="relative">
                    <?php if ($avatarUrl): ?>
                        <img src="<?= $escape($avatarUrl) ?>" alt="<?= $escape($fullName) ?>"
                             class="h-24 w-24 rounded-full object-cover ring-4 ring-white/40">
                    <?php else: ?>
                        <span class="flex h-24 w-24 items-center justify-center rounded-full bg-white/20 text-4xl font-bold text-white ring-4 ring-white/40">
                            <?= $escape($initial) ?>
                        </span>
                    <?php endif; ?>
                    <label for="avatarInput"
                           class="absolute bottom-0 right-0 flex h-8 w-8 cursor-pointer items-center justify-center rounded-full bg-white text-green-700 shadow hover:bg-green-50"
                           title="Changer la photo">
                        <i class="fa-solid fa-camera text-xs"></i>
                    </label>
                </div>
                <div class="text-white">
                    <h2 class="text-2xl font-bold"><?= $escape($fullName) ?></h2>
                    <p class="text-sm text-white/80">
                        <i class="fa-solid fa-at mr-1"></i><?= $escape($me['user_name']) ?>
                    </p>
                    <span class="mt-2 inline-flex items-center gap-2 rounded-full bg-white/20 px-3 py-1 text-xs font-medium">
                        <i class="fa-solid fa-user-shield"></i>
                        <?= $escape($roleLabel) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Stats sous la carte -->
        <div class="grid gap-3 border-t border-slate-200 bg-slate-50 p-4 sm:grid-cols-4">
            <div class="text-center">
                <p class="text-xs uppercase text-slate-500">Ventes</p>
                <p class="mt-1 text-2xl font-bold text-slate-900"><?= number_format($stats['ventes_count'], 0, ',', ' ') ?></p>
            </div>
            <div class="text-center">
                <p class="text-xs uppercase text-slate-500">Factures</p>
                <p class="mt-1 text-2xl font-bold text-slate-900"><?= number_format($stats['factures_count'], 0, ',', ' ') ?></p>
            </div>
            <div class="text-center">
                <p class="text-xs uppercase text-slate-500">Commandes créées</p>
                <p class="mt-1 text-2xl font-bold text-slate-900"><?= number_format($stats['commandes_count'], 0, ',', ' ') ?></p>
            </div>
            <div class="text-center">
                <p class="text-xs uppercase text-slate-500">Chiffre d’affaires</p>
                <p class="mt-1 text-lg font-bold text-green-700">
                    <?= number_format($stats['ventes_total'], 0, ',', ' ') ?> FBu
                </p>
            </div>
        </div>

        <!-- Formulaire avatar (caché, déclenché par le bouton camera) -->
        <form method="post" enctype="multipart/form-data" id="avatarForm" class="hidden">
            <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="action" value="update_avatar">
            <input type="file" name="avatar" id="avatarInput" accept="image/jpeg,image/png,image/webp,image/gif">
        </form>
    </article>

    <div class="grid gap-5 lg:grid-cols-3">

        <!-- Colonne principale -->
        <div class="space-y-5 lg:col-span-2">

            <!-- Informations personnelles -->
            <section class="rounded-md border border-slate-200 bg-white p-5">
                <header class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-slate-900">
                            <i class="fa-solid fa-id-card text-green-700 mr-2"></i>Informations personnelles
                        </h3>
                    </div>
                </header>

                <form method="post" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="space-y-1 text-sm font-medium text-slate-700">
                            Prénom
                            <input name="f_name" maxlength="100" value="<?= $escape($me['f_name'] ?? '') ?>"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20">
                        </label>
                        <label class="space-y-1 text-sm font-medium text-slate-700">
                            Nom
                            <input name="l_name" maxlength="100" value="<?= $escape($me['l_name'] ?? '') ?>"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20">
                        </label>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="space-y-1 text-sm font-medium text-slate-700">
                            Téléphone
                            <input name="telephone" maxlength="100" value="<?= $escape($me['telephone'] ?? '') ?>"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20">
                        </label>
                        <label class="space-y-1 text-sm font-medium text-slate-700">
                            Email
                            <input name="email" type="email" maxlength="100" value="<?= $escape($me['email'] ?? '') ?>"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20">
                        </label>
                    </div>

                    <label class="block space-y-1 text-sm font-medium text-slate-700">
                        Adresse
                        <input name="adress" maxlength="100" value="<?= $escape($me['adress'] ?? '') ?>"
                               class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20">
                    </label>

                    <div class="flex justify-end border-t border-slate-100 pt-4">
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-md bg-green-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-800">
                            <i class="fa-solid fa-floppy-disk"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </section>

            <!-- Changer mot de passe -->
            <section class="rounded-md border border-slate-200 bg-white p-5">
                <header class="mb-4">
                    <h3 class="font-semibold text-slate-900">
                        <i class="fa-solid fa-lock text-green-700 mr-2"></i>Changer le mot de passe
                    </h3>
                    <p class="mt-1 text-xs text-slate-500">
                        Utilisez un mot de passe d’au moins 8 caractères. Mélangez lettres, chiffres et symboles.
                    </p>
                </header>

                <form method="post" class="space-y-4" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="action" value="change_password">

                    <label class="block space-y-1 text-sm font-medium text-slate-700">
                        Mot de passe actuel
                        <input name="current_password" type="password" required autocomplete="current-password"
                               class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20">
                    </label>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="space-y-1 text-sm font-medium text-slate-700">
                            Nouveau mot de passe
                            <input name="new_password" type="password" minlength="8" required autocomplete="new-password"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20">
                        </label>
                        <label class="space-y-1 text-sm font-medium text-slate-700">
                            Confirmer
                            <input name="confirm_password" type="password" minlength="8" required autocomplete="new-password"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2.5 font-normal focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20">
                        </label>
                    </div>

                    <div class="flex justify-end border-t border-slate-100 pt-4">
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-md bg-green-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-800">
                            <i class="fa-solid fa-key"></i> Modifier le mot de passe
                        </button>
                    </div>
                </form>
            </section>

        </div>

        <!-- Colonne latérale -->
        <aside class="space-y-5">

            <!-- Infos système -->
            <section class="rounded-md border border-slate-200 bg-white p-5">
                <h3 class="mb-3 font-semibold text-slate-900">
                    <i class="fa-solid fa-circle-info text-slate-500 mr-2"></i>Informations
                </h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Identifiant</dt>
                        <dd class="font-mono text-xs text-slate-800"><?= $escape($me['user_name']) ?></dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Rôle</dt>
                        <dd>
                            <span class="rounded-full bg-<?= $roleColor ?>-100 px-2.5 py-0.5 text-xs font-medium text-<?= $roleColor ?>-800">
                                <?= $escape($roleLabel) ?>
                            </span>
                        </dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Statut</dt>
                        <dd class="font-medium text-green-700"><?= $escape($me['status']) ?></dd>
                    </div>
                    <?php if (!empty($me['admission'])): ?>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Depuis</dt>
                            <dd class="text-slate-800"><?= $escape(date('d/m/Y', strtotime($me['admission']))) ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($me['last_login'])): ?>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Dernière connexion</dt>
                            <dd class="text-slate-800"><?= $escape(date('d/m/Y H:i', strtotime($me['last_login']))) ?></dd>
                        </div>
                    <?php endif; ?>
                </dl>
            </section>

            <!-- Activité récente -->
            <section class="rounded-md border border-slate-200 bg-white p-5">
                <h3 class="mb-3 font-semibold text-slate-900">
                    <i class="fa-solid fa-clock-rotate-left text-slate-500 mr-2"></i>Activité récente
                </h3>
                <?php if (!$recentActivity): ?>
                    <p class="text-sm text-slate-500">Aucune activité enregistrée.</p>
                <?php else: ?>
                    <ul class="space-y-3">
                        <?php foreach ($recentActivity as $a):
                            $isIn = ($a['type'] ?? '') === 'entrée';
                        ?>
                            <li class="flex gap-3 text-sm">
                                <span class="mt-1 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full <?= $isIn ? 'bg-green-100 text-green-700' : 'bg-rose-100 text-rose-700' ?>">
                                    <i class="fa-solid <?= $isIn ? 'fa-arrow-down' : 'fa-arrow-up' ?> text-[10px]"></i>
                                </span>
                                <div class="min-w-0">
                                    <p class="font-medium text-slate-800"><?= $escape($a['action_type']) ?></p>
                                    <?php if (!empty($a['descrip'])): ?>
                                        <p class="truncate text-xs text-slate-500"><?= $escape($a['descrip']) ?></p>
                                    <?php endif; ?>
                                    <p class="text-[11px] text-slate-400"><?= $escape(date('d/m/Y H:i', strtotime($a['created_at']))) ?></p>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
        </aside>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('avatarInput');
    var form  = document.getElementById('avatarForm');
    if (input && form) {
        input.addEventListener('change', function () {
            if (input.files.length > 0) form.submit();
        });
    }
});
</script>