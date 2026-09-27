<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$user = check_auth();
$_SESSION['user_data'] = $user;

try {
    // Statistiques générales
    $stats = db()->prepare('
        SELECT
            (SELECT COUNT(*) FROM clients) as total_clients,
            (SELECT COUNT(*) FROM clients WHERE end_date >= CURDATE()) as active_clients,
            (SELECT COUNT(*) FROM clients WHERE end_date < CURDATE()) as expired_clients,
            (SELECT COUNT(*) FROM clients WHERE end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)) as expiring_soon,
            (SELECT SUM(amount) FROM payments WHERE type = "Entrée") as total_revenue,
            (SELECT SUM(amount) FROM payments WHERE type = "Sortie") as total_expenses
    ');
    $stats->execute();
    $stats = $stats->fetch();

    // Derniers clients
    $recent_clients = db()->prepare('
        SELECT id, name, plan_type, end_date, price
        FROM clients
        ORDER BY created_at DESC
        LIMIT 5
    ');
    $recent_clients->execute();
    $recent_clients = $recent_clients->fetchAll();

    // Derniers paiements
    $recent_payments = db()->prepare('
        SELECT p.id, p.amount, p.type, p.description, p.payment_date, c.name
        FROM payments p
        JOIN clients c ON p.client_id = c.id
        ORDER BY p.created_at DESC
        LIMIT 5
    ');
    $recent_payments->execute();
    $recent_payments = $recent_payments->fetchAll();

} catch (Exception $e) {
    log_error('Dashboard statistics error', ['error' => $e->getMessage()]);
    $stats = null;
    $recent_clients = [];
    $recent_payments = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - <?php echo APP_NAME; ?></title>
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
            <h1 class="text-4xl font-bold text-white mb-2">Tableau de bord</h1>
            <p class="text-gray-400">Bienvenue, <?php echo e($user['username']); ?> 👋</p>
        </div>

        <!-- Stats grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Total clients -->
            <div class="card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Total Clients</p>
                        <p class="text-3xl font-bold text-white mt-2">
                            <?php echo $stats['total_clients'] ?? 0; ?>
                        </p>
                    </div>
                    <div class="text-4xl text-amber-400">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>

            <!-- Clients actifs -->
            <div class="card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Actifs</p>
                        <p class="text-3xl font-bold text-green-400 mt-2">
                            <?php echo $stats['active_clients'] ?? 0; ?>
                        </p>
                    </div>
                    <div class="text-4xl text-green-400">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>

            <!-- Expiration imminente -->
            <div class="card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Exp. Imminente</p>
                        <p class="text-3xl font-bold text-yellow-400 mt-2">
                            <?php echo $stats['expiring_soon'] ?? 0; ?>
                        </p>
                    </div>
                    <div class="text-4xl text-yellow-400">
                        <i class="fas fa-bell"></i>
                    </div>
                </div>
            </div>

            <!-- Expiré -->
            <div class="card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Expiré</p>
                        <p class="text-3xl font-bold text-red-400 mt-2">
                            <?php echo $stats['expired_clients'] ?? 0; ?>
                        </p>
                    </div>
                    <div class="text-4xl text-red-400">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>

            <!-- Revenue -->
            <div class="card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Revenus</p>
                        <p class="text-3xl font-bold text-green-400 mt-2">
                            <?php echo number_format($stats['total_revenue'] ?? 0, 0, ',', ' '); ?>
                        </p>
                    </div>
                    <div class="text-4xl text-green-400">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                </div>
            </div>

            <!-- Expenses -->
            <div class="card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Dépenses</p>
                        <p class="text-3xl font-bold text-red-400 mt-2">
                            <?php echo number_format($stats['total_expenses'] ?? 0, 0, ',', ' '); ?>
                        </p>
                    </div>
                    <div class="text-4xl text-red-400">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                </div>
            </div>

            <!-- Solde -->
            <div class="card md:col-span-2 lg:col-span-2">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Solde Net</p>
                        <p class="text-3xl font-bold gradient-accent mt-2">
                            <?php
                            $balance = ($stats['total_revenue'] ?? 0) - ($stats['total_expenses'] ?? 0);
                            echo number_format($balance, 0, ',', ' ');
                            ?>
                        </p>
                    </div>
                    <div class="text-4xl text-amber-400">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent data section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Derniers clients -->
            <div class="card">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-white">Derniers clients</h2>
                    <a href="/clients/list.php" class="text-amber-400 hover:text-amber-300 text-sm font-medium">
                        Voir tous →
                    </a>
                </div>

                <?php if (!empty($recent_clients)): ?>
                    <div class="space-y-3">
                        <?php foreach ($recent_clients as $client):
                            $status = get_client_status($client['end_date']);
                        ?>
                            <div class="flex items-center justify-between p-3 bg-[#18181b] rounded-lg border border-[#27272a]">
                                <div>
                                    <p class="font-medium text-white"><?php echo e($client['name']); ?></p>
                                    <p class="text-xs text-gray-500">
                                        <?php echo e($client['plan_type']); ?> •
                                        <span class="status-badge status-<?php echo strtolower($status['status']); ?>">
                                            <?php echo $status['icon']; ?> <?php echo $status['status']; ?>
                                        </span>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium text-amber-400"><?php echo number_format($client['price'], 0, ',', ' '); ?> XOF</p>
                                    <p class="text-xs text-gray-500"><?php echo date('d M Y', strtotime($client['end_date'])); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-center text-gray-400 py-8">Aucun client pour le moment</p>
                <?php endif; ?>
            </div>

            <!-- Derniers paiements -->
            <div class="card">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-white">Dernières transactions</h2>
                    <a href="/payments/add.php" class="text-amber-400 hover:text-amber-300 text-sm font-medium">
                        Ajouter →
                    </a>
                </div>

                <?php if (!empty($recent_payments)): ?>
                    <div class="space-y-3">
                        <?php foreach ($recent_payments as $payment): ?>
                            <div class="flex items-center justify-between p-3 bg-[#18181b] rounded-lg border border-[#27272a]">
                                <div>
                                    <p class="font-medium text-white"><?php echo e($payment['name']); ?></p>
                                    <p class="text-xs text-gray-500">
                                        <?php echo e($payment['description']); ?> •
                                        <?php echo date('d M Y', strtotime($payment['payment_date'])); ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium <?php echo $payment['type'] === 'Entrée' ? 'text-green-400' : 'text-red-400'; ?>">
                                        <?php echo $payment['type'] === 'Entrée' ? '+' : '-'; ?><?php echo number_format($payment['amount'], 0, ',', ' '); ?> XOF
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-center text-gray-400 py-8">Aucune transaction pour le moment</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="/assets/js/app.js"></script>
</body>
</html>
