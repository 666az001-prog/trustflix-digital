<?php
require_once 'includes/functions.php';
require_once 'config/database.php';
check_auth();

$total_clients = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
$active_clients = $pdo->query("SELECT COUNT(*) FROM clients WHERE status = 'Actif'")->fetchColumn();
$expired_clients = $pdo->query("SELECT COUNT(*) FROM clients WHERE status = 'Expire'")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(amount) FROM payments")->fetchColumn() ?? 0;
$free_profiles = $pdo->query("SELECT COUNT(*) FROM profiles WHERE status = 'Libre'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TrustFlix Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-[#09090b] text-white font-sans min-h-screen flex flex-col md:flex-row">
    <aside class="w-full md:w-64 bg-[#18181b] border-r border-[#27272a] p-6 flex flex-col justify-between">
        <div>
            <h1 class="text-xl font-bold bg-gradient-to-r from-amber-500 to-orange-500 bg-clip-text text-transparent mb-8">TrustFlix Digital</h1>
            <nav class="space-y-2">
                <a href="index.php" class="block p-3 bg-gradient-to-r from-amber-500 to-orange-500 rounded font-medium text-white"><i class="fas fa-chart-pie mr-2"></i> Dashboard</a>
                <a href="accounts/list.php" class="block p-3 hover:bg-[#27272a] rounded text-gray-400 hover:text-white"><i class="fas fa-tv mr-2"></i> Comptes Netflix</a>
                <a href="clients/list.php" class="block p-3 hover:bg-[#27272a] rounded text-gray-400 hover:text-white"><i class="fas fa-users mr-2"></i> Clients</a>
            </nav>
        </div>

        <div class="mt-8">
            <div class="text-sm text-gray-400 mb-2">Utilisateur : <?= e($_SESSION['username']) ?></div>
            <a href="logout.php" class="block p-2 text-center bg-red-600/20 border border-red-600/40 text-red-400 rounded hover:bg-red-600 hover:text-white transition"><i class="fas fa-sign-out-alt mr-2"></i> Deconnexion</a>
        </div>
    </aside>

    <main class="flex-1 p-8">
        <h2 class="text-2xl font-bold mb-6">Vue Synoptique & Tresorerie</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="p-6 bg-[#18181b] border border-[#27272a] rounded-xl">
                <div class="text-gray-400 text-sm">Chiffre d'Affaires</div>
                <div class="text-3xl font-bold text-green-500 mt-2"><?= number_format((float) $total_revenue, 0, ',', ' ') ?> F CFA</div>
            </div>
            <div class="p-6 bg-[#18181b] border border-[#27272a] rounded-xl">
                <div class="text-gray-400 text-sm">Clients Actifs</div>
                <div class="text-3xl font-bold text-amber-500 mt-2"><?= e($active_clients) ?> / <?= e($total_clients) ?></div>
            </div>
            <div class="p-6 bg-[#18181b] border border-[#27272a] rounded-xl">
                <div class="text-gray-400 text-sm">Clients Expires</div>
                <div class="text-3xl font-bold text-red-500 mt-2"><?= e($expired_clients) ?></div>
            </div>
            <div class="p-6 bg-[#18181b] border border-[#27272a] rounded-xl">
                <div class="text-gray-400 text-sm">Ecrans Disponibles</div>
                <div class="text-3xl font-bold text-blue-500 mt-2"><?= e($free_profiles) ?> Libres</div>
            </div>
        </div>

        <div class="p-6 bg-[#18181b] border border-[#27272a] rounded-xl">
            <h3 class="text-lg font-semibold mb-4">Bienvenue sur votre espace de gestion</h3>
            <p class="text-gray-400 text-sm">Utilisez le menu de gauche pour piloter vos comptes meres et distribuer vos ecrans.</p>
        </div>
    </main>
</body>
</html>
