<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
check_auth();

$stats = fetch_dashboard_stats($pdo);

$pageTitle = 'Dashboard — TrustFlix Digital';
$activeNav = 'dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<h1 style="margin-top: 0;">Dashboard principal</h1>
<p class="muted" style="margin-bottom: 1.5rem;">Vue Niveau 1 — comptes fournisseurs et slots (Netflix, Spotify, Apple Music).</p>

<div class="grid grid-4" style="margin-bottom: 1rem;">
    <div class="card">
        <div class="muted">Comptes totaux</div>
        <div class="stat-value"><?= (int) $stats['totals']['accounts'] ?></div>
    </div>
    <div class="card">
        <div class="muted">Slots totaux</div>
        <div class="stat-value"><?= (int) $stats['totals']['slots'] ?></div>
    </div>
    <div class="card">
        <div class="muted">Slots libres</div>
        <div class="stat-value" style="color: var(--success);"><?= (int) $stats['totals']['free'] ?></div>
    </div>
    <div class="card">
        <div class="muted">Slots occupés</div>
        <div class="stat-value" style="color: var(--danger);"><?= (int) $stats['totals']['occupied'] ?></div>
    </div>
</div>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 1rem;">
        <h2 style="margin:0; font-size:1.125rem;">Répartition par service</h2>
        <a class="btn" href="<?= e(app_url('accounts/add.php')) ?>">+ Nouveau compte</a>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Service</th>
                <th>Comptes</th>
                <th>Slots</th>
                <th>Libres</th>
                <th>Occupés</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stats['services'] as $row): ?>
                <tr>
                    <td><strong><?= e($row['name']) ?></strong> <span class="muted">(<?= e($row['code']) ?>)</span></td>
                    <td><?= (int) $row['account_count'] ?></td>
                    <td><?= (int) $row['slot_count'] ?></td>
                    <td style="color: var(--success);"><?= (int) $row['free_slots'] ?></td>
                    <td style="color: var(--danger);"><?= (int) $row['occupied_slots'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
