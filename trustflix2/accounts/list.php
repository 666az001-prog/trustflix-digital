<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
check_auth();

$stmt = $pdo->query("SELECT * FROM netflix_accounts ORDER BY id DESC");
$accounts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Comptes Netflix - TrustFlix Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-[#09090b] text-white p-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
            <h2 class="text-2xl font-bold"><a href="../index.php" class="text-gray-400 hover:text-white mr-2"><i class="fas fa-arrow-left"></i></a> Comptes Meres Netflix</h2>
            <a href="add.php" class="p-3 bg-gradient-to-r from-amber-500 to-orange-500 rounded font-semibold text-center"><i class="fas fa-plus mr-2"></i> Ajouter un Compte</a>
        </div>

        <div class="space-y-4">
            <?php foreach ($accounts as $acc): ?>
                <?php
                $p_stmt = $pdo->prepare("SELECT * FROM profiles WHERE account_id = ? ORDER BY id ASC");
                $p_stmt->execute([$acc['id']]);
                $profiles = $p_stmt->fetchAll();
                ?>
                <div class="p-6 bg-[#18181b] border border-[#27272a] rounded-xl">
                    <div class="flex justify-between items-start gap-4 mb-4 border-b border-[#27272a] pb-3">
                        <div>
                            <h3 class="text-lg font-bold text-amber-500 break-all"><?= e($acc['email']) ?></h3>
                            <p class="text-sm text-gray-400">Mot de passe : <?= e($acc['password']) ?> | Renouvellement : <?= e($acc['renewal_date']) ?></p>
                        </div>
                        <a href="delete.php?id=<?= e($acc['id']) ?>" class="text-red-400 hover:text-red-600 text-sm" onclick="return confirm('Supprimer ce compte supprimera definitivement ses profils ?')"><i class="fas fa-trash"></i></a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                        <?php foreach ($profiles as $prof): ?>
                            <div class="p-3 bg-[#27272a] border border-[#3f3f46] rounded">
                                <form method="POST" action="update_profile.php" class="space-y-2">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="profile_id" value="<?= e($prof['id']) ?>">

                                    <div>
                                        <label class="block text-[10px] uppercase tracking-wide text-gray-500 mb-1">Nom</label>
                                        <input type="text" name="profile_name" value="<?= e($prof['profile_name']) ?>" maxlength="50" required class="w-full px-2 py-1.5 bg-[#18181b] border border-[#3f3f46] rounded text-xs text-white focus:outline-none">
                                    </div>

                                    <div>
                                        <label class="block text-[10px] uppercase tracking-wide text-gray-500 mb-1">PIN</label>
                                        <input type="text" name="pin_code" value="<?= e($prof['pin_code']) ?>" maxlength="4" pattern="[0-9]{4}" inputmode="numeric" required class="w-full px-2 py-1.5 bg-[#18181b] border border-[#3f3f46] rounded text-xs text-white font-mono focus:outline-none">
                                    </div>

                                    <button type="submit" class="w-full px-2 py-1.5 bg-amber-500/20 border border-amber-500/40 text-amber-300 rounded text-xs font-semibold hover:bg-amber-500 hover:text-white transition">Enregistrer</button>
                                </form>

                                <div class="mt-3 text-center">
                                <span class="px-2 py-0.5 text-xs rounded font-medium <?= $prof['status'] === 'Libre' ? 'bg-green-500/20 text-green-400 border border-green-500/40' : 'bg-red-500/20 text-red-400 border border-red-500/40' ?>">
                                    <?= e($prof['status']) ?>
                                </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($accounts)): ?>
                <div class="p-6 bg-[#18181b] border border-[#27272a] rounded-xl text-gray-400">Aucun compte Netflix enregistre.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
