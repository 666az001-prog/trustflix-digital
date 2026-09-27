<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$user = check_auth();
$_SESSION['user_data'] = $user;

$error = '';
$success = '';

// Récupère les clients pour le sélecteur
try {
    $clients_stmt = db()->prepare('SELECT id, name, price FROM clients ORDER BY name ASC');
    $clients_stmt->execute();
    $clients = $clients_stmt->fetchAll();
} catch (Exception $e) {
    log_error('Clients fetch error', ['error' => $e->getMessage()]);
    $clients = [];
}

// Traite la soumission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            throw new Exception('Token de sécurité invalide.');
        }

        $client_id = intval($_POST['client_id'] ?? 0);
        $amount = floatval($_POST['amount'] ?? 0);
        $type = trim($_POST['type'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $payment_date = trim($_POST['payment_date'] ?? '');

        // Validations
        if ($client_id <= 0) {
            throw new Exception('Client requis.');
        }

        if ($amount <= 0) {
            throw new Exception('Montant invalide (doit être positif).');
        }

        if (!in_array($type, ['Entrée', 'Sortie'])) {
            throw new Exception('Type de paiement invalide.');
        }

        if (empty($description) || strlen($description) < 2) {
            throw new Exception('Description requise (minimum 2 caractères).');
        }

        if (empty($payment_date) || !strtotime($payment_date)) {
            throw new Exception('Date de paiement invalide.');
        }

        // Vérifie que le client existe
        $client_check = db()->prepare('SELECT id FROM clients WHERE id = ?');
        $client_check->execute([$client_id]);
        if (!$client_check->fetch()) {
            throw new Exception('Client introuvable.');
        }

        // Insère le paiement
        $stmt = db()->prepare('
            INSERT INTO payments (client_id, amount, type, description, payment_date)
            VALUES (?, ?, ?, ?, ?)
        ');
        $stmt->execute([$client_id, $amount, $type, $description, $payment_date]);

        log_info('Payment created', ['client_id' => $client_id, 'amount' => $amount, 'type' => $type]);

        $success = 'Paiement enregistré avec succès.';

        // Réinitialise le formulaire
        $_POST = [];

    } catch (Exception $e) {
        $error = $e->getMessage();
        log_error('Payment creation error', ['error' => $e->getMessage()]);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un paiement - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="authenticated bg-[#09090b]">
    <?php render_navigation(); ?>

    <!-- Main content -->
    <main class="ml-0 md:ml-64 p-4 md:p-8 min-h-screen">
        <!-- Header -->
        <div class="mb-8">
            <a href="/dashboard/" class="text-amber-400 hover:text-amber-300 mb-4 inline-flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                Retour
            </a>
            <h1 class="text-4xl font-bold text-white mb-2">Enregistrer un paiement</h1>
            <p class="text-gray-400">Ajouter une entrée ou une sortie d'argent</p>
        </div>

        <!-- Formulaire -->
        <div class="max-w-2xl">
            <div class="card">
                <?php if ($success): ?>
                    <div class="alert alert-success mb-6">
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo e($success); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger mb-6">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo e($error); ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" data-validate>
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">

                    <!-- Client -->
                    <div class="form-group">
                        <label for="client_id">
                            <i class="fas fa-user text-amber-400 mr-2"></i>Client
                        </label>
                        <select id="client_id" name="client_id" required class="w-full" autofocus>
                            <option value="">-- Sélectionner un client --</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?php echo $client['id']; ?>">
                                    <?php echo e($client['name']); ?> (<?php echo number_format($client['price'], 0, ',', ' '); ?> XOF)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Type de paiement -->
                    <div class="form-group">
                        <label for="type">
                            <i class="fas fa-arrow-right text-amber-400 mr-2"></i>Type
                        </label>
                        <select id="type" name="type" required class="w-full">
                            <option value="">-- Sélectionner un type --</option>
                            <option value="Entrée">Entrée (+ revenu)</option>
                            <option value="Sortie">Sortie (- dépense)</option>
                        </select>
                    </div>

                    <!-- Montant -->
                    <div class="form-group">
                        <label for="amount">
                            <i class="fas fa-money-bill text-amber-400 mr-2"></i>Montant (XOF)
                        </label>
                        <input
                            type="number"
                            id="amount"
                            name="amount"
                            placeholder="10000"
                            min="0"
                            step="100"
                            required
                            class="w-full"
                        >
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label for="description">
                            <i class="fas fa-file-alt text-amber-400 mr-2"></i>Description
                        </label>
                        <input
                            type="text"
                            id="description"
                            name="description"
                            placeholder="Ex: Paiement de l'abonnement mensuel"
                            required
                            class="w-full"
                        >
                    </div>

                    <!-- Date -->
                    <div class="form-group">
                        <label for="payment_date">
                            <i class="fas fa-calendar text-amber-400 mr-2"></i>Date du paiement
                        </label>
                        <input
                            type="date"
                            id="payment_date"
                            name="payment_date"
                            value="<?php echo date('Y-m-d'); ?>"
                            required
                            class="w-full"
                        >
                    </div>

                    <!-- Boutons -->
                    <div class="flex gap-4 mt-8">
                        <button type="submit" class="btn btn-primary flex-1 justify-center">
                            <i class="fas fa-save"></i>
                            Enregistrer le paiement
                        </button>
                        <a href="/dashboard/" class="btn btn-secondary flex-1 justify-center">
                            <i class="fas fa-times"></i>
                            Annuler
                        </a>
                    </div>
                </form>

                <!-- Info box -->
                <div class="mt-6 p-4 bg-[#18181b] border border-[#27272a] rounded-lg">
                    <p class="text-sm text-gray-400">
                        <i class="fas fa-info-circle text-amber-400 mr-2"></i>
                        <strong>Entrée :</strong> Argent reçu (paiement client) | <strong>Sortie :</strong> Argent dépensé (frais, achat)
                    </p>
                </div>
            </div>
        </div>

        <!-- Tableau de suivi rapide -->
        <?php if (!empty($clients)): ?>
            <div class="mt-8">
                <h2 class="text-2xl font-bold text-white mb-4">Derniers paiements</h2>
                <div class="card">
                    <?php
                    try {
                        $recent = db()->prepare('
                            SELECT p.*, c.name
                            FROM payments p
                            JOIN clients c ON p.client_id = c.id
                            ORDER BY p.created_at DESC
                            LIMIT 10
                        ');
                        $recent->execute();
                        $recent_payments = $recent->fetchAll();

                        if (!empty($recent_payments)):
                    ?>
                        <div class="overflow-x-auto">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>Type</th>
                                        <th>Montant</th>
                                        <th>Description</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_payments as $payment): ?>
                                        <tr>
                                            <td class="font-medium text-white"><?php echo e($payment['name']); ?></td>
                                            <td>
                                                <span class="text-xs <?php echo $payment['type'] === 'Entrée' ? 'text-green-400' : 'text-red-400'; ?>">
                                                    <?php echo $payment['type'] === 'Entrée' ? '↑ Entrée' : '↓ Sortie'; ?>
                                                </span>
                                            </td>
                                            <td class="<?php echo $payment['type'] === 'Entrée' ? 'text-green-400' : 'text-red-400'; ?> font-medium">
                                                <?php echo $payment['type'] === 'Entrée' ? '+' : '-'; ?><?php echo number_format($payment['amount'], 0, ',', ' '); ?>
                                            </td>
                                            <td class="text-gray-400 text-sm"><?php echo e($payment['description']); ?></td>
                                            <td class="text-gray-500 text-sm"><?php echo date('d/m/Y', strtotime($payment['payment_date'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-gray-400 py-8">Aucun paiement enregistré</p>
                    <?php endif; ?>
                    <?php
                    } catch (Exception $e) {
                        echo '<p class="text-red-400">Erreur lors du chargement</p>';
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script src="/assets/js/app.js"></script>
</body>
</html>
