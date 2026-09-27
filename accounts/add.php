<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
check_auth();

$error = '';
$success = '';

$servicesStmt = $pdo->query('SELECT id, name, code, default_slots FROM services ORDER BY name ASC');
$services = $servicesStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Jeton de sécurité invalide.';
    } else {
        $serviceId = (int) ($_POST['service_id'] ?? 0);
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $renewalDate = trim($_POST['renewal_date'] ?? '');
        $status = ($_POST['status'] ?? 'Actif') === 'Inactif' ? 'Inactif' : 'Actif';

        if ($serviceId <= 0 || $email === '' || $password === '' || $renewalDate === '') {
            $error = 'Tous les champs obligatoires doivent être renseignés.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Adresse e-mail invalide.';
        } else {
            $serviceStmt = $pdo->prepare('SELECT id, name, code, default_slots FROM services WHERE id = ? LIMIT 1');
            $serviceStmt->execute([$serviceId]);
            $service = $serviceStmt->fetch();

            if (!$service) {
                $error = 'Service introuvable.';
            } else {
                try {
                    $pdo->beginTransaction();

                    $insertAccount = $pdo->prepare(
                        'INSERT INTO accounts (service_id, email, password, renewal_date, status)
                         VALUES (?, ?, ?, ?, ?)'
                    );
                    $insertAccount->execute([$serviceId, $email, $password, $renewalDate, $status]);
                    $accountId = (int) $pdo->lastInsertId();

                    create_slots_for_account($pdo, $accountId, $service);

                    $pdo->commit();

                    header('Location: ' . app_url('accounts/list.php'));
                    exit;
                } catch (Throwable $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    $error = 'Impossible de créer le compte. Vérifiez que le schéma SQL est importé.';
                }
            }
        }
    }
}

$pageTitle = 'Ajouter un compte — TrustFlix Digital';
$activeNav = 'accounts_add';
require_once __DIR__ . '/../includes/header.php';
?>

<h1 style="margin-top:0;">Ajouter un compte fournisseur</h1>
<p class="muted" style="margin-bottom: 1.25rem;">
    Les slots sont générés automatiquement selon le service :
    Netflix (5 écrans), Spotify (6 places), Apple Music (5 accès), PIN par défaut <strong>0000</strong>.
</p>

<?php if ($error !== ''): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<div class="card" style="max-width: 640px;">
    <form method="post" class="grid" style="gap: 1rem;">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div>
            <label class="label" for="service_id">Service</label>
            <select class="select" name="service_id" id="service_id" required>
                <option value="">— Choisir —</option>
                <?php foreach ($services as $service): ?>
                    <option value="<?= (int) $service['id'] ?>" <?= (int) ($_POST['service_id'] ?? 0) === (int) $service['id'] ? 'selected' : '' ?>>
                        <?= e($service['name']) ?> (<?= (int) $service['default_slots'] ?> slots)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="label" for="email">E-mail du compte</label>
            <input class="input" type="email" id="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
        </div>

        <div>
            <label class="label" for="password">Mot de passe</label>
            <input class="input" type="text" id="password" name="password" required value="<?= e($_POST['password'] ?? '') ?>">
        </div>

        <div>
            <label class="label" for="renewal_date">Date de renouvellement</label>
            <input class="input" type="date" id="renewal_date" name="renewal_date" required value="<?= e($_POST['renewal_date'] ?? date('Y-m-d', strtotime('+30 days'))) ?>">
        </div>

        <div>
            <label class="label" for="status">Statut</label>
            <select class="select" name="status" id="status">
                <option value="Actif" selected>Actif</option>
                <option value="Inactif">Inactif</option>
            </select>
        </div>

        <div style="display:flex; gap:0.75rem;">
            <button class="btn" type="submit">Créer le compte + slots</button>
            <a class="btn btn-secondary" href="<?= e(app_url('accounts/list.php')) ?>">Annuler</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
