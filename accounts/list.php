<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
check_auth();

$accountsStmt = $pdo->query(
    'SELECT a.id, a.email, a.password, a.renewal_date, a.status, a.created_at,
            s.name AS service_name, s.code AS service_code
     FROM accounts a
     INNER JOIN services s ON s.id = a.service_id
     ORDER BY a.id DESC'
);
$accounts = $accountsStmt->fetchAll();

$slotsByAccount = [];
if ($accounts !== []) {
    $ids = array_column($accounts, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $slotsStmt = $pdo->prepare(
        "SELECT id, account_id, slot_name, pin_code, status
         FROM slots
         WHERE account_id IN ($placeholders)
         ORDER BY account_id DESC, id ASC"
    );
    $slotsStmt->execute($ids);

    foreach ($slotsStmt->fetchAll() as $slot) {
        $slotsByAccount[(int) $slot['account_id']][] = $slot;
    }
}

$pageTitle = 'Comptes fournisseurs — TrustFlix Digital';
$activeNav = 'accounts_list';
require_once __DIR__ . '/../includes/header.php';
?>

<div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom: 1rem;">
    <div>
        <h1 style="margin:0;">Listing des comptes & slots</h1>
        <p class="muted">Netflix, Spotify et Apple Music</p>
    </div>
    <a class="btn" href="<?= e(app_url('accounts/add.php')) ?>">+ Ajouter un compte</a>
</div>

<?php if ($accounts === []): ?>
    <div class="card">
        <p class="muted" style="margin:0;">Aucun compte enregistré. Commencez par ajouter un compte fournisseur.</p>
    </div>
<?php else: ?>
    <?php foreach ($accounts as $account): ?>
        <?php
        $accountId = (int) $account['id'];
        $slots = $slotsByAccount[$accountId] ?? [];
        $freeCount = count(array_filter($slots, static fn ($s) => $s['status'] === 'Libre'));
        $busyCount = count($slots) - $freeCount;
        ?>
        <article class="card" style="margin-bottom: 1rem;">
            <div style="display:flex; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom: 1rem;">
                <div>
                    <h2 style="margin:0 0 0.35rem 0; font-size:1.05rem;">
                        <?= e($account['service_name']) ?>
                        <span class="muted">— <?= e($account['email']) ?></span>
                    </h2>
                    <p class="muted" style="margin:0;">
                        Renouvellement : <?= e(date('d/m/Y', strtotime($account['renewal_date']))) ?>
                        · Mot de passe : <code><?= e($account['password']) ?></code>
                        · Statut : <?= e($account['status']) ?>
                    </p>
                </div>
                <div class="muted" style="font-size:0.875rem;">
                    Slots : <?= count($slots) ?> ·
                    <span style="color:var(--success);">Libres <?= $freeCount ?></span> ·
                    <span style="color:var(--danger);">Occupés <?= $busyCount ?></span>
                </div>
            </div>

            <div class="slot-grid">
                <?php foreach ($slots as $slot): ?>
                    <div class="card" style="padding:0.75rem;">
                        <div style="font-weight:600; font-size:0.875rem;"><?= e($slot['slot_name']) ?></div>
                        <div class="muted" style="font-size:0.75rem; margin:0.35rem 0;">PIN : <?= e($slot['pin_code']) ?></div>
                        <?php if ($slot['status'] === 'Libre'): ?>
                            <span class="badge badge-free">Libre</span>
                        <?php else: ?>
                            <span class="badge badge-busy">Occupé</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
