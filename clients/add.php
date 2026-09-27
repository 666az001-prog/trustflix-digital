<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
check_auth();

$error = '';
$services = $pdo->query('SELECT id, name, code FROM services ORDER BY name ASC')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceId = (int) ($_POST['service_id'] ?? 0);
    $name = trim((string) ($_POST['name'] ?? ''));
    $whatsapp = trim((string) ($_POST['whatsapp'] ?? ''));
    $price = (float) ($_POST['price'] ?? 0);
    $startDate = trim((string) ($_POST['start_date'] ?? ''));
    $notes = trim((string) ($_POST['notes'] ?? ''));

    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Jeton de sécurité invalide.';
    } elseif ($serviceId <= 0 || $name === '' || $whatsapp === '' || $price <= 0 || $startDate === '') {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } else {
        $service = $pdo->prepare('SELECT id, name, code FROM services WHERE id = ? LIMIT 1');
        $service->execute([$serviceId]);
        $serviceRow = $service->fetch();

        if (!$serviceRow) {
            $error = 'Service introuvable.';
        } else {
            try {
                $pdo->beginTransaction();

                $slotStmt = $pdo->prepare(
                    'SELECT s.id, s.slot_name
                     FROM slots s
                     INNER JOIN accounts a ON a.id = s.account_id
                     WHERE a.service_id = ? AND s.status = "Libre"
                     ORDER BY s.id ASC
                     LIMIT 1
                     FOR UPDATE'
                );
                $slotStmt->execute([$serviceId]);
                $slot = $slotStmt->fetch();

                if (!$slot) {
                    $pdo->rollBack();
                    $error = 'Aucun écran/place disponible pour ce service.';
                } else {
                    $endDate = date('Y-m-d', strtotime($startDate . ' + 30 days'));

                    $insertClient = $pdo->prepare(
                        'INSERT INTO clients (name, whatsapp, service_id, slot_id, price, start_date, end_date, status, notes, created_at)
                         VALUES (?, ?, ?, ?, ?, ?, ?, "Actif", ?, NOW())'
                    );
                    $insertClient->execute([
                        $name,
                        $whatsapp,
                        $serviceId,
                        $slot['id'],
                        $price,
                        $startDate,
                        $endDate,
                        $notes,
                    ]);

                    $slotUpdate = $pdo->prepare('UPDATE slots SET status = "Occupé" WHERE id = ?');
                    $slotUpdate->execute([$slot['id']]);

                    $pdo->commit();
                    header('Location: ' . app_url('clients/list.php'));
                    exit;
                }
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $error = 'Impossible d’enregistrer le client. Vérifiez la base de données.';
            }
        }
    }
}

$pageTitle = 'Ajouter un client — TrustFlix Digital';
$activeNav = 'clients_add';
require_once __DIR__ . '/../includes/header.php';
?>

<h1 style="margin-top:0;">Ajouter un client</h1>
<p class="muted" style="margin-bottom: 1.25rem;">
    Le premier slot libre du service sélectionné est attribué automatiquement. La durée par défaut est de 30 jours.
</p>

<?php if ($error !== ''): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<div class="card" style="max-width: 720px;">
    <form method="post" class="grid" style="gap: 1rem;">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div>
            <label class="label" for="service_id">Service</label>
            <select class="select" name="service_id" id="service_id" required>
                <option value="">— Sélectionner —</option>
                <?php foreach ($services as $service): ?>
                    <option value="<?= (int) $service['id'] ?>" <?= (int) ($_POST['service_id'] ?? 0) === (int) $service['id'] ? 'selected' : '' ?>>
                        <?= e($service['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="label" for="name">Nom du client</label>
            <input class="input" type="text" id="name" name="name" value="<?= e($_POST['name'] ?? '') ?>" required>
        </div>

        <div>
            <label class="label" for="whatsapp">WhatsApp</label>
            <input class="input" type="text" id="whatsapp" name="whatsapp" value="<?= e($_POST['whatsapp'] ?? '') ?>" required placeholder="0550000000 / +2250550000000">
        </div>

        <div class="grid grid-2">
            <div>
                <label class="label" for="price">Prix (F CFA)</label>
                <input class="input" type="number" id="price" name="price" min="1" step="1" value="<?= e((string) ($_POST['price'] ?? 2000)) ?>" required>
            </div>

            <div>
                <label class="label" for="start_date">Date de début</label>
                <input class="input" type="date" id="start_date" name="start_date" value="<?= e($_POST['start_date'] ?? date('Y-m-d')) ?>" required>
            </div>
        </div>

        <div>
            <label class="label" for="notes">Notes</label>
            <textarea class="input" id="notes" name="notes" rows="3" placeholder="Informations complémentaires..."><?= e($_POST['notes'] ?? '') ?></textarea>
        </div>

        <div style="display:flex; gap:0.75rem;">
            <button class="btn" type="submit">Créer le client</button>
            <a class="btn btn-secondary" href="<?= e(app_url('clients/list.php')) ?>">Annuler</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
