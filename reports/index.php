<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
check_auth();

$stats = $pdo->query(
    'SELECT
        COUNT(DISTINCT c.id) AS clients,
        COALESCE(SUM(CASE WHEN c.status = "Actif" THEN 1 ELSE 0 END), 0) AS active_clients,
        COALESCE(SUM(CASE WHEN c.status = "Expiré" THEN 1 ELSE 0 END), 0) AS expired_clients,
        COALESCE(SUM(CASE WHEN s.status = "Libre" THEN 1 ELSE 0 END), 0) AS free_slots,
        COALESCE(SUM(CASE WHEN s.status = "Occupé" THEN 1 ELSE 0 END), 0) AS occupied_slots,
        COALESCE(SUM(CASE WHEN p.payment_date >= DATE_FORMAT(CURDATE(), "%Y-%m-01") THEN p.amount ELSE 0 END), 0) AS month_revenue,
        COALESCE(SUM(p.amount), 0) AS total_revenue
     FROM clients c
     LEFT JOIN slots s ON s.id = c.slot_id
     LEFT JOIN payments p ON p.client_id = c.id'
)->fetch();

$services = $pdo->query(
    'SELECT s.name, COUNT(c.id) AS clients, COALESCE(SUM(p.amount), 0) AS revenue
     FROM services s
     LEFT JOIN clients c ON c.service_id = s.id
     LEFT JOIN payments p ON p.client_id = c.id
     GROUP BY s.id, s.name
     ORDER BY revenue DESC, s.name ASC'
)->fetchAll();

$pageTitle = 'Rapports financiers — TrustFlix Digital';
$activeNav = 'reports_index';
require_once __DIR__ . '/../includes/header.php';
?>

<h1 style="margin-top:0;">Rapports financiers</h1>
<p class="muted" style="margin-bottom: 1.25rem;">Vue d’ensemble de la performance et du revenu.</p>

<div class="grid grid-4" style="margin-bottom: 1rem;">
    <div class="card">
        <div class="muted">Revenu du mois</div>
        <div class="stat-value" style="color:#10b981;">
            <?= number_format((float) ($stats['month_revenue'] ?? 0), 0, ',', ' ') ?> F CFA
        </div>
    </div>
    <div class="card">
        <div class="muted">Revenu total</div>
        <div class="stat-value" style="color:#f59e0b;">
            <?= number_format((float) ($stats['total_revenue'] ?? 0), 0, ',', ' ') ?> F CFA
        </div>
    </div>
    <div class="card">
        <div class="muted">Clients actifs</div>
        <div class="stat-value"><?= (int) ($stats['active_clients'] ?? 0) ?></div>
    </div>
    <div class="card">
        <div class="muted">Taux d’occupation</div>
        <div class="stat-value">
            <?php
            $totalSlots = (int) (($stats['free_slots'] ?? 0) + ($stats['occupied_slots'] ?? 0));
            $occupation = $totalSlots > 0 ? round(((int) ($stats['occupied_slots'] ?? 0) / $totalSlots) * 100, 1) : 0;
            echo $occupation . '%';
            ?>
        </div>
    </div>
</div>

<div class="card">
    <h2 style="margin-top:0; margin-bottom:1rem; font-size:1.125rem;">Performance par service</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Service</th>
                <th>Clients</th>
                <th>Revenu</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($services as $service): ?>
                <tr>
                    <td><?= e($service['name']) ?></td>
                    <td><?= (int) $service['clients'] ?></td>
                    <td style="font-weight:700; color:#10b981;">
                        <?= number_format((float) $service['revenue'], 0, ',', ' ') ?> F CFA
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
