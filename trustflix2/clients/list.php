<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
check_auth();

$stmt = $pdo->query("SELECT c.*, p.profile_name, p.pin_code, a.email FROM clients c LEFT JOIN profiles p ON c.profile_id = p.id LEFT JOIN netflix_accounts a ON p.account_id = a.id ORDER BY c.id DESC");
$clients = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Clients - TrustFlix Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-[#09090b] text-white p-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 mb-6">
            <h2 class="text-2xl font-bold"><a href="../index.php" class="text-gray-400 hover:text-white mr-2"><i class="fas fa-arrow-left"></i></a> Ventes & Ecrans</h2>
            <div class="flex flex-col sm:flex-row gap-4">
                <input type="text" id="searchClient" placeholder="Rechercher un client..." class="p-2 bg-[#18181b] border border-[#27272a] rounded text-sm text-white focus:outline-none">
                <a href="add.php" class="p-3 bg-gradient-to-r from-amber-500 to-orange-500 rounded font-semibold text-sm text-center"><i class="fas fa-user-plus mr-2"></i> Nouveau Client</a>
            </div>
        </div>

        <div class="bg-[#18181b] border border-[#27272a] rounded-xl overflow-x-auto">
            <table class="w-full min-w-[880px] text-left border-collapse">
                <thead>
                    <tr class="bg-[#27272a] text-gray-400 text-sm">
                        <th class="p-4">Nom</th>
                        <th class="p-4">WhatsApp</th>
                        <th class="p-4">Formule</th>
                        <th class="p-4">Ecran assigne</th>
                        <th class="p-4">Echeance</th>
                        <th class="p-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#27272a] text-sm">
                    <?php foreach ($clients as $c): ?>
                        <?php
                        $msg = "Bonjour " . $c['name'] . ", votre abonnement TrustFlix Digital " . $c['formula'] . " expire bientot le " . $c['end_date'] . ". Merci de faire votre reabonnement pour eviter toute coupure !";
                        $whatsapp_url = "https://api.whatsapp.com/send?phone=" . urlencode(format_whatsapp($c['whatsapp'])) . "&text=" . urlencode($msg);
                        ?>
                        <tr class="hover:bg-[#27272a]/40">
                            <td class="p-4 font-semibold"><?= e($c['name']) ?></td>
                            <td class="p-4 text-gray-300"><?= e($c['whatsapp']) ?></td>
                            <td class="p-4"><span class="px-2 py-0.5 rounded text-xs bg-orange-500/20 text-orange-400 border border-orange-500/40"><?= e($c['formula']) ?></span></td>
                            <td class="p-4 text-gray-400">
                                <?php if ($c['profile_id']): ?>
                                    <div class="text-white font-medium text-xs break-all"><?= e($c['email']) ?></div>
                                    <div class="text-[11px] text-amber-500 font-mono"><?= e($c['profile_name']) ?> (PIN: <?= e($c['pin_code']) ?>)</div>
                                <?php else: ?>
                                    <span class="text-red-400 text-xs">Aucun ecran lie</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4">
                                <div class="font-medium text-xs"><?= e($c['end_date']) ?></div>
                                <span class="text-[10px] px-2 py-0.5 rounded <?= $c['status'] === 'Actif' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' ?>"><?= e($c['status']) ?></span>
                            </td>
                            <td class="p-4 space-x-3">
                                <a href="<?= e($whatsapp_url) ?>" target="_blank" rel="noopener" class="text-green-400 hover:text-green-500 text-base"><i class="fab fa-whatsapp"></i></a>
                                <a href="delete.php?id=<?= e($c['id']) ?>" class="text-red-400 hover:text-red-600 text-base" onclick="return confirm('Supprimer ce client et liberer le profil immediatement ?')"><i class="fas fa-user-minus"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($clients)): ?>
                        <tr>
                            <td colspan="6" class="p-6 text-center text-gray-400">Aucun client enregistre.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>
