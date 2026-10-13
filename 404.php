<?php
$missingRoute = trim((string) ($route ?? $_GET['route'] ?? ''), '/');
$basePath = defined('APP_BASE_PATH')
    ? APP_BASE_PATH
    : rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($basePath === '.') {
    $basePath = '';
}
$role = $_SESSION['role'] ?? '';
$homeUrl = $basePath . '/' . ($role !== '' ? rawurlencode($role) . '/accueil' : 'auth/');
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Page introuvable</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 flex items-center justify-center p-6">
    <main class="w-full max-w-lg text-center">
        <p class="text-7xl font-bold text-green-700">404</p>
        <h1 class="mt-4 text-2xl font-bold">Page introuvable</h1>
        <p class="mt-3 text-slate-600">
            La route <code class="rounded bg-slate-200 px-2 py-1"><?= htmlspecialchars('/' . $missingRoute, ENT_QUOTES, 'UTF-8') ?></code> n'existe pas.
        </p>
        <a href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') ?>"
           class="mt-6 inline-flex rounded-md bg-green-700 px-5 py-3 font-medium text-white hover:bg-green-800">
            Retour au tableau de bord
        </a>
    </main>
</body>
</html>