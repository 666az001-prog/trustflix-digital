<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
check_auth();

function normalize_whatsapp(string $phone): string
{
    $digits = preg_replace('/\D+/', '', $phone ?? '');
    if ($digits === '') {
        return '';
    }

    if (str_starts_with($digits, '225')) {
        return $digits;
    }

    return '225' . $digits;
}

function expiry_label(string $endDate): array
{
    $today = new DateTime('today');
    $end = new DateTime($endDate);
    $days = (int) $today->diff($end)->format('%r%a');

    if ($days < 0) {
        return ['label' => '🔴 Expiré', 'class' => 'badge badge-danger'];
    }

    if ($days === 0) {
        return ['label' => '🔴 Expire aujourd\'hui', 'class' => 'badge badge-danger'];
    }

    if ($days <= 3) {
        return ['label' => '🟠 J-' . $days, 'class' => 'badge badge-warning'];
    }

    if ($days <= 7) {
        return ['label' => '🟡 J-' . $days, 'class' => 'badge badge-info'];
    }

    return ['label' => '✅ Actif', 'class' => 'badge badge-success'];
}

function whatsapp_link(string $name, string $phone, string $serviceName, string $endDate): string
{
    $clean = normalize_whatsapp($phone);
    if ($clean === '') {
        return '#';
    }

    $message = sprintf(
        'Bonjour %s, votre abonnement %s expire le %s. Merci de renouveler pour éviter toute interruption. Nous restons à votre disposition.',
        $name,
        $serviceName,
        date('d/m/Y', strtotime($endDate))
    );

    return 'https://wa.me/' . $clean . '?text=' . rawurlencode($message);
}

$clientsStmt = $pdo->query(
    'SELECT c.id, c.name, c.whatsapp, c.price, c.start_date, c.end_date, c.status, c.notes,
            s.name AS service_name, s.code AS service_code,
            sl.slot_name, sl.pin_code
     FROM clients c
     LEFT JOIN services s ON s.id = c.service_id
     LEFT JOIN slots sl ON sl.id = c.slot_id
     ORDER BY c.end_date ASC, c.id DESC'
);
$clients = $clientsStmt->fetchAll();

$pageTitle = 'Clients — TrustFlix Digital';
$activeNav = 'clients_list';
require_once __DIR__ . '/../includes/header.php';
?>

<div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom: 1rem;">
    <div>
        <h1 style="margin:0;">Clients</h1>
        <p class="muted">Suivi des abonnements et relances WhatsApp</p>
    </div>
    <a class="btn" href="<?= e(app_url('clients/add.php')) ?>">+ Nouveau client</a>
</div>

<?php if ($clients === []): ?>
    <div class="card">
        <p class="muted" style="margin:0;">Aucun client enregistré.</p>
    </div>
<?php else: ?>
    <?php foreach ($clients as $client): ?>
        <?php $badge = expiry_label($client['end_date']); ?>
        <article class="card" style="margin-bottom: 1rem;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; flex-wrap:wrap;">
                <div>
                    <h2 style="margin:0 0 0.35rem 0; font-size:1.08rem;">
                        <?= e($client['name']) ?>
                        <span class="muted">— <?= e($client['service_name'] ?? 'Service') ?></span>
                    </h2>
                    <p class="muted" style="margin:0;">
                        WhatsApp: <?= e('+' . normalize_whatsapp($client['whatsapp'])) ?>
                        · Prix: <?= number_format((float) $client['price'], 0, ',', ' ') ?> F CFA
                    </p>
                </div>

                <div style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
                    <span class="<?= e($badge['class']) ?>"><?= e($badge['label']) ?></span>
                    <a class="btn btn-success" href="<?= e(whatsapp_link($client['name'], $client['whatsapp'], $client['service_name'] ?? 'service', $client['end_date'])) ?>" target="_blank" rel="noopener">Relancer sur WhatsApp</a>
                    <a class="btn btn-secondary" href="<?= e(app_url('clients/edit.php?id=' . (int) $client['id'])) ?>">Modifier</a>
                    <a class="btn btn-danger" href="<?= e(app_url('clients/delete.php?id=' . (int) $client['id'])) ?>" onclick="return confirm('Libérer le slot associé et supprimer ce client ?');">Supprimer</a>
                </div>
            </div>

            <div class="grid grid-3" style="margin-top: 1rem;">
                <div class="card" style="padding:0.85rem;">
                    <div class="muted" style="font-size:0.75rem;">Slot assigné</div>
                    <div style="font-weight:600; margin-top:0.35rem;">
                        <?= e($client['slot_name'] ?? 'Aucun slot') ?>
                    </div>
                </div>
                <div class="card" style="padding:0.85rem;">
                    <div class="muted" style="font-size:0.75rem;">PIN</div>
                    <div style="font-weight:600; margin-top:0.35rem;">
                        <?= e($client['pin_code'] ?? '—') ?>
                    </div>
                </div>
                <div class="card" style="padding:0.85rem;">
                    <div class="muted" style="font-size:0.75rem;">Échéance</div>
                    <div style="font-weight:600; margin-top:0.35rem;">
                        <?= e(date('d/m/Y', strtotime($client['end_date']))) ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($client['notes'])): ?>
                <div class="muted" style="margin-top: 0.9rem;">
                    <strong>Notes :</strong> <?= e($client['notes']) ?>
                </div>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
