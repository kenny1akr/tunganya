<?php
if (!defined('ROUTER_LOADED')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

if (!userCan($user, 'utilisateurs.roles')) {
    http_response_code(403);
    echo '<div class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</div>';
    return;
}

$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$selectedRole = (string) ($_GET['role'] ?? array_key_first($ROLES));
if (!isset($ROLES[$selectedRole])) {
    $selectedRole = array_key_first($ROLES);
}
$permissions = $ROLES[$selectedRole]['permissions'];
$allPermissions = [];
foreach ($ROLES as $roleDefinition) {
    foreach ($roleDefinition['permissions'] as $permission) {
        if ($permission !== '*') {
            $allPermissions[$permission] = true;
        }
    }
}
ksort($allPermissions);
$permissionModules = [];
foreach (array_keys($allPermissions) as $permission) {
    [$module] = explode('.', $permission, 2);
    $permissionModules[$module][] = $permission;
}
?>
<section class="space-y-5">
    <header>
        <h1 class="text-2xl font-bold text-slate-900">Rôles &amp; permissions</h1>
        <p class="mt-1 text-sm text-slate-600">Consultez les accès attribués à chaque rôle de l’application.</p>
    </header>

    <nav class="flex flex-wrap gap-2 border-b border-slate-200 pb-3" aria-label="Sélection du rôle">
        <?php foreach ($ROLES as $roleName => $roleDefinition): ?>
            <a href="<?= $escape(app_url('router.php') . '?' . http_build_query(['route' => $role . '/roles', 'role' => $roleName])) ?>" aria-current="<?= $selectedRole === $roleName ? 'page' : 'false' ?>" class="rounded-md px-3 py-2 text-sm font-medium <?= $selectedRole === $roleName ? 'bg-green-700 text-white' : 'bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50' ?>"><?= $escape($roleDefinition['label']) ?></a>
        <?php endforeach; ?>
    </nav>

    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-slate-900"><?= $escape($ROLES[$selectedRole]['label']) ?></h2>
            <p class="mt-1 max-w-2xl text-sm text-slate-600"><?= $escape($ROLES[$selectedRole]['description']) ?></p>
        </div>
        <span class="inline-flex shrink-0 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700"><?= $permissions === ['*'] ? 'Accès total' : count($permissions) . ' permission(s) déclarée(s)' ?></span>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <?php foreach ($permissionModules as $module => $modulePermissions): ?>
            <?php $visiblePermissions = array_values(array_filter($modulePermissions, static function ($permission) use ($permissions): bool {
                return $permissions === ['*'] || in_array($permission, $permissions, true) || in_array(explode('.', $permission)[0] . '.*', $permissions, true);
            })); ?>
            <?php if (!$visiblePermissions) continue; ?>
            <section class="rounded-md border border-slate-200 bg-white p-4">
                <h3 class="mb-3 border-b border-slate-100 pb-2 text-sm font-semibold capitalize text-slate-900"><?= $escape(str_replace('_', ' ', $module)) ?></h3>
                <ul class="space-y-2">
                    <?php foreach ($visiblePermissions as $permission): ?>
                        <li class="flex items-center gap-2 text-sm text-slate-700"><i data-feather="check" class="h-4 w-4 shrink-0 text-green-700"></i><code class="text-xs"><?= $escape($permission) ?></code></li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endforeach; ?>
    </div>
    <p class="text-xs text-slate-500">Les permissions sont définies dans la configuration de l’application; cette page est en lecture seule.</p>
</section>