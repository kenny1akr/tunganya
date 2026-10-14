<?php
if (!defined('ROUTER_LOADED')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

if (!userCan($user, 'anniversaires.view')) {
    http_response_code(403);
    echo '<p class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">Accès refusé.</p>';
    return;
}

require_once __DIR__ . '/../../api/database.php';
$db     = Database::getInstance();
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$role   = $user['role_system'];

$flashMessage = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);

// -----------------------------------------------------------------
// Filtres
// -----------------------------------------------------------------
$search = trim((string) ($_GET['q']      ?? ''));
$month  = (int)    ($_GET['mois']        ?? 0);
$onlyVip = !empty($_GET['vip']);

$conditions = [
    "category = 'client'",
    "annif IS NOT NULL",
    "annif <> ''",
];
$params = [];

if ($search !== '') {
    $conditions[] = '(name LIKE ? OR phone LIKE ? OR email LIKE ?)';
    $p = '%' . $search . '%';
    array_push($params, $p, $p, $p);
}
if ($month >= 1 && $month <= 12) {
    $conditions[] = "SUBSTRING(annif, 4, 2) = LPAD(?, 2, '0')";
    $params[] = $month;
}
if ($onlyVip) {
    $conditions[] = "status_client = 'VIP'";
}

$where = 'WHERE ' . implode(' AND ', $conditions);

$clients = $db->fetchAll(
    "SELECT id, name, phone, email, annif, status_client,
            total_achats, frequence_achats, dernier_achat
     FROM partners
     $where
     ORDER BY
        -- ce mois-ci en premier, puis mois croissant, puis jour
        CASE WHEN SUBSTRING(annif, 4, 2) = LPAD(MONTH(CURDATE()), 2, '0') THEN 0 ELSE 1 END,
        SUBSTRING(annif, 4, 2),
        SUBSTRING(annif, 1, 2)",
    $params
);

// -----------------------------------------------------------------
// Classement : aujourd'hui / ce mois / à venir
// -----------------------------------------------------------------
$todayDay   = (int) date('d');
$todayMonth = (int) date('n');
$todayYear  = (int) date('Y');
$isLeap     = (bool) date('L');

$todayList   = [];
$thisWeek    = [];
$thisMonth   = [];
$laterList   = [];

foreach ($clients as $c) {
    $parts = explode('-', $c['annif']);
    if (count($parts) !== 2) continue;
    $d = (int) $parts[0];
    $m = (int) $parts[1];
    if ($d < 1 || $d > 31 || $m < 1 || $m > 12) continue;

    // Gérer le 29 février en année non bissextile → considérer le 1er mars
    if ($m === 2 && $d === 29 && !$isLeap) {
        $d = 1; $m = 3;
    }

    // Calcul du prochain anniversaire (à partir d'aujourd'hui)
    $thisYearAnnif = mktime(0, 0, 0, $m, $d, $todayYear);
    $todayTs       = mktime(0, 0, 0, $todayMonth, $todayDay, $todayYear);
    if ($thisYearAnnif < $todayTs) {
        $thisYearAnnif = mktime(0, 0, 0, $m, $d, $todayYear + 1);
    }

    $daysUntil = (int) round(($thisYearAnnif - $todayTs) / 86400);

    $entry = [
        'client'     => $c,
        'd'          => $d,
        'm'          => $m,
        'date_annif' => $thisYearAnnif,
        'days'       => $daysUntil,
    ];

    if ($d === $todayDay && $m === $todayMonth) {
        $todayList[] = $entry;
    } elseif ($daysUntil <= 7) {
        $thisWeek[] = $entry;
    } elseif ($daysUntil <= 30) {
        $thisMonth[] = $entry;
    } else {
        $laterList[] = $entry;
    }
}

// Tri par date croissante pour les 3 derniers groupes
usort($thisWeek,  fn($a, $b) => $a['date_annif'] <=> $b['date_annif']);
usort($thisMonth, fn($a, $b) => $a['date_annif'] <=> $b['date_annif']);
usort($laterList, fn($a, $b) => $a['date_annif'] <=> $b['date_annif']);

// -----------------------------------------------------------------
// Stats pour le bandeau du haut
// -----------------------------------------------------------------
$monthNames = [
    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
];

$totalClients = count($clients);
$totalToday   = count($todayList);
$totalWeek    = count($thisWeek);
$totalMonth   = count($thisMonth);

$statusColor = static function (string $s): string {
    return match ($s) {
        'VIP'      => 'bg-amber-100 text-amber-800',
        'Régulier' => 'bg-blue-100 text-blue-800',
        default    => 'bg-slate-100 text-slate-700',
    };
};

$monthUrl = static function (int $newMonth) use ($search, $onlyVip, $role): string {
    return app_route_url('anniversaires', $role) . '?' . http_build_query([
        'q'    => $search,
        'mois' => $newMonth,
        'vip'  => $onlyVip ? 1 : null,
    ]);
};
?>
<section class="space-y-5">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-green-800">Fidélisation</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Anniversaires clients</h1>
            <p class="mt-1 text-sm text-slate-600">
                <?= number_format($totalClients, 0, ',', ' ') ?> client(s) avec date d’anniversaire —
                <strong class="text-green-800"><?= $totalToday ?></strong> aujourd’hui,
                <strong class="text-blue-800"><?= $totalWeek ?></strong> cette semaine,
                <strong class="text-amber-800"><?= $totalMonth ?></strong> ce mois-ci.
            </p>
        </div>
        <?php if (userCan($user, 'clients.view')): ?>
            <a href="<?= $escape(app_route_url('clients/liste')) ?>"
               class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                <i data-feather="users" class="h-4 w-4"></i> Liste clients
            </a>
        <?php endif; ?>
    </header>

    <?php if ($flashMessage): ?>
        <div role="status" class="rounded-md border px-4 py-3 text-sm <?= ($flashMessage['type'] ?? '') === 'success' ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-800' ?>">
            <?= $escape($flashMessage['text'] ?? '') ?>
        </div>
    <?php endif; ?>

    <!-- KPI cards -->
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <article class="rounded-md border border-rose-200 bg-rose-50 p-4">
            <div class="flex items-start justify-between">
                <p class="text-xs font-semibold uppercase text-rose-700">Aujourd’hui</p>
                <i data-feather="cake" class="h-4 w-4 text-rose-600"></i>
            </div>
            <p class="mt-1 text-2xl font-bold text-rose-800"><?= $totalToday ?></p>
            <p class="mt-1 text-xs text-rose-700">Fêter leur anniversaire</p>
        </article>
        <article class="rounded-md border border-blue-200 bg-blue-50 p-4">
            <div class="flex items-start justify-between">
                <p class="text-xs font-semibold uppercase text-blue-700">Cette semaine</p>
                <i data-feather="calendar" class="h-4 w-4 text-blue-600"></i>
            </div>
            <p class="mt-1 text-2xl font-bold text-blue-800"><?= $totalWeek ?></p>
            <p class="mt-1 text-xs text-blue-700">7 prochains jours</p>
        </article>
        <article class="rounded-md border border-amber-200 bg-amber-50 p-4">
            <div class="flex items-start justify-between">
                <p class="text-xs font-semibold uppercase text-amber-700">Ce mois-ci</p>
                <i data-feather="star" class="h-4 w-4 text-amber-600"></i>
            </div>
            <p class="mt-1 text-2xl font-bold text-amber-800"><?= $totalMonth ?></p>
            <p class="mt-1 text-xs text-amber-700">30 prochains jours</p>
        </article>
        <article class="rounded-md border border-slate-200 bg-white p-4">
            <div class="flex items-start justify-between">
                <p class="text-xs font-semibold uppercase text-slate-600">Total enregistré</p>
                <i data-feather="gift" class="h-4 w-4 text-slate-500"></i>
            </div>
            <p class="mt-1 text-2xl font-bold text-slate-800"><?= $totalClients ?></p>
            <p class="mt-1 text-xs text-slate-500">Clients avec anniversaire</p>
        </article>
    </div>

    <!-- Filtres -->
    <form method="get" action="<?= $escape(app_url('router.php')) ?>"
          class="grid gap-3 rounded-md border border-slate-200 bg-white p-4 sm:grid-cols-2 lg:grid-cols-4">
        <input type="hidden" name="route" value="<?= $escape($role . '/anniversaires') ?>">

        <label class="space-y-1 text-sm font-medium text-slate-700 lg:col-span-2">
            Recherche
            <input name="q" value="<?= $escape($search) ?>" placeholder="Nom, téléphone, email…"
                   class="w-full rounded-md border border-slate-300 px-3 py-2 font-normal">
        </label>

        <label class="space-y-1 text-sm font-medium text-slate-700">
            Mois
            <select name="mois" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 font-normal">
                <option value="0">Tous les mois</option>
                <?php foreach ($monthNames as $num => $label): ?>
                    <option value="<?= $num ?>" <?= $month === $num ? 'selected' : '' ?>><?= $escape($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="flex items-end gap-2 pb-2 text-sm font-medium text-slate-700">
            <input type="checkbox" name="vip" value="1" <?= $onlyVip ? 'checked' : '' ?>
                   class="h-4 w-4 accent-green-700">
            Clients VIP uniquement
        </label>

        <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-4">
            <button class="inline-flex items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium hover:bg-slate-50">
                <i data-feather="search" class="h-4 w-4"></i> Filtrer
            </button>
            <?php if ($search !== '' || $month > 0 || $onlyVip): ?>
                <a href="<?= $escape(app_route_url('anniversaires')) ?>"
                   class="inline-flex items-center justify-center rounded-md px-3 py-2 text-sm text-slate-600 hover:bg-slate-100">Effacer</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Aujourd'hui -->
    <?php if ($todayList): ?>
        <section class="rounded-md border-2 border-rose-200 bg-rose-50/40">
            <header class="flex items-center gap-3 border-b border-rose-200 px-5 py-3">
                <i data-feather="cake" class="h-5 w-5 text-rose-600"></i>
                <div>
                    <h2 class="font-semibold text-rose-900">🎉 Anniversaire aujourd’hui (<?= count($todayList) ?>)</h2>
                    <p class="text-xs text-rose-700"><?= date('d/m/Y') ?></p>
                </div>
            </header>
            <ul class="divide-y divide-rose-100">
                <?php foreach ($todayList as $e): $c = $e['client']; ?>
                    <li class="flex flex-col gap-3 px-5 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="font-semibold text-slate-900"><?= $escape($c['name']) ?></div>
                            <div class="text-xs text-slate-600">
                                <?= $escape($c['phone'] ?: '—') ?>
                                <?php if (!empty($c['email'])): ?> · <?= $escape($c['email']) ?><?php endif; ?>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium <?= $statusColor($c['status_client']) ?>">
                                <?= $escape($c['status_client']) ?>
                            </span>
                            <span class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-medium text-rose-800">
                                <?= sprintf('%02d/%02d', $e['d'], $e['m']) ?>
                            </span>
                            <?php if (!empty($c['phone'])): ?>
                                <a href="tel:<?= $escape($c['phone']) ?>"
                                   class="inline-flex items-center gap-1 rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                    <i data-feather="phone" class="h-3 w-3"></i> Appeler
                                </a>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <!-- Cette semaine -->
    <?php if ($thisWeek): ?>
        <section class="rounded-md border border-blue-200 bg-white">
            <header class="flex items-center gap-3 border-b border-blue-200 bg-blue-50/60 px-5 py-3">
                <i data-feather="calendar" class="h-5 w-5 text-blue-600"></i>
                <div>
                    <h2 class="font-semibold text-blue-900">Cette semaine (<?= count($thisWeek) ?>)</h2>
                    <p class="text-xs text-blue-700">Dans les 7 prochains jours</p>
                </div>
            </header>
            <ul class="divide-y divide-slate-100">
                <?php foreach ($thisWeek as $e): $c = $e['client']; ?>
                    <li class="flex flex-col gap-3 px-5 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="font-medium text-slate-800"><?= $escape($c['name']) ?></div>
                            <div class="text-xs text-slate-500">
                                <?= $escape($c['phone'] ?: '—') ?>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium <?= $statusColor($c['status_client']) ?>">
                                <?= $escape($c['status_client']) ?>
                            </span>
                            <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-800">
                                <?= sprintf('%02d/%02d', $e['d'], $e['m']) ?>
                            </span>
                            <span class="text-xs text-slate-500">
                                <?= $e['days'] === 1 ? 'demain' : 'dans ' . $e['days'] . ' j' ?>
                            </span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <!-- Ce mois -->
    <?php if ($thisMonth): ?>
        <section class="rounded-md border border-amber-200 bg-white">
            <header class="flex items-center gap-3 border-b border-amber-200 bg-amber-50/60 px-5 py-3">
                <i data-feather="star" class="h-5 w-5 text-amber-600"></i>
                <div>
                    <h2 class="font-semibold text-amber-900">Ce mois-ci (<?= count($thisMonth) ?>)</h2>
                    <p class="text-xs text-amber-700">Dans les 30 prochains jours</p>
                </div>
            </header>
            <ul class="divide-y divide-slate-100">
                <?php foreach ($thisMonth as $e): $c = $e['client']; ?>
                    <li class="flex flex-col gap-3 px-5 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="font-medium text-slate-800"><?= $escape($c['name']) ?></div>
                            <div class="text-xs text-slate-500">
                                <?= $escape($c['phone'] ?: '—') ?>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium <?= $statusColor($c['status_client']) ?>">
                                <?= $escape($c['status_client']) ?>
                            </span>
                            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800">
                                <?= sprintf('%02d/%02d', $e['d'], $e['m']) ?>
                            </span>
                            <span class="text-xs text-slate-500">dans <?= $e['days'] ?> j</span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <!-- Plus tard -->
    <?php if ($laterList): ?>
        <section class="rounded-md border border-slate-200 bg-white">
            <header class="flex items-center gap-3 border-b border-slate-200 px-5 py-3">
                <i data-feather="clock" class="h-5 w-5 text-slate-500"></i>
                <div>
                    <h2 class="font-semibold text-slate-800">Plus tard (<?= count($laterList) ?>)</h2>
                    <p class="text-xs text-slate-500">Dans plus de 30 jours</p>
                </div>
            </header>
            <ul class="divide-y divide-slate-100 max-h-96 overflow-y-auto">
                <?php foreach ($laterList as $e): $c = $e['client']; ?>
                    <li class="flex flex-col gap-3 px-5 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="font-medium text-slate-800"><?= $escape($c['name']) ?></div>
                            <div class="text-xs text-slate-500"><?= $escape($c['phone'] ?: '—') ?></div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium <?= $statusColor($c['status_client']) ?>">
                                <?= $escape($c['status_client']) ?>
                            </span>
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                                <?= sprintf('%02d/%02d', $e['d'], $e['m']) ?>
                            </span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <!-- Aucun résultat -->
    <?php if (!$todayList && !$thisWeek && !$thisMonth && !$laterList): ?>
        <div class="rounded-md border border-amber-200 bg-amber-50 p-8 text-center">
            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-amber-100">
                <i data-feather="cake" class="h-6 w-6 text-amber-700"></i>
            </div>
            <p class="font-semibold text-amber-900">Aucun anniversaire à afficher</p>
            <p class="mt-2 text-sm text-amber-800">
                Ajoutez la date d’anniversaire (JJ-MM) dans la fiche client
                <?php if (userCan($user, 'clients.edit')): ?>
                    — <a href="<?= $escape(app_route_url('clients/liste')) ?>" class="font-medium underline">voir la liste des clients</a>.
                <?php else: ?>.
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>

    <!-- Bandeau des mois (raccourci) -->
    <section class="rounded-md border border-slate-200 bg-white p-4">
        <p class="mb-2 text-xs font-semibold uppercase text-slate-500">Filtrer par mois</p>
        <div class="flex flex-wrap gap-2">
            <a href="<?= $escape(app_route_url('anniversaires')) ?>"
               class="inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium <?= $month === 0 ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
                Tous
            </a>
            <?php foreach ($monthNames as $num => $label): ?>
                <a href="<?= $escape($monthUrl($num)) ?>"
                   class="inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium <?= $month === $num ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
                    <?= $escape(substr($label, 0, 3)) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
</section>