<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
check_auth();

$paymentsStmt = $pdo->query(
    'SELECT p.id, p.amount, p.description, p.payment_date, c.name AS client_name, s.name AS service_name
     FROM payments p
     INNER JOIN clients c ON c.id = p.client_id
     INNER JOIN services s ON s.id = c.service_id
     ORDER BY p.payment_date DESC, p.id DESC'
);
$payments = $paymentsStmt->fetchAll();

$pageTitle = 'Historique des paiements — TrustFlix Digital';
$activeNav = 'payments_list';
require_once __DIR__ . '/../includes/header.php';
?>

<div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom: 1rem;">
    <div>
        <h1 style="margin:0;">Historique des encaissements</h1>
        <p class="muted">Mouvements financiers enregistrés</p>
    </div>
</div>

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Client</th>
                <th>Service</th>
                <th>Description</th>
                <th>Date</th>
                <th>Montant</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($payments === []): ?>
                <tr>
                    <td colspan="5" class="muted" style="text-align:center; padding:1rem;">Aucun paiement enregistré.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?= e($payment['client_name']) ?></td>
                        <td><?= e($payment['service_name']) ?></td>
                        <td><?= e($payment['description']) ?></td>
                        <td><?= e(date('d/m/Y', strtotime($payment['payment_date']))) ?></td>
                        <td style="font-weight:700; color:#10b981;">
                            <?= number_format((float) $payment['amount'], 0, ',', ' ') ?> F CFA
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
