<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
check_auth();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $renewal_date = trim($_POST['renewal_date'] ?? '');

        if (!empty($email) && !empty($password) && !empty($renewal_date)) {
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("INSERT INTO netflix_accounts (email, password, renewal_date) VALUES (?, ?, ?)");
                $stmt->execute([$email, $password, $renewal_date]);
                $account_id = $pdo->lastInsertId();

                for ($i = 1; $i <= 6; $i++) {
                    $profile_name = "Profil " . $i;
                    $pin_code = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
                    $p_stmt = $pdo->prepare("INSERT INTO profiles (account_id, profile_name, pin_code, status) VALUES (?, ?, ?, 'Libre')");
                    $p_stmt->execute([$account_id, $profile_name, $pin_code]);
                }

                $pdo->commit();
                header('Location: list.php');
                exit;
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $error = "Erreur reseau lors du deploiement.";
            }
        } else {
            $error = "Tous les champs sont requis.";
        }
    } else {
        $error = "Jeton CSRF invalide.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Compte - TrustFlix Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-[#09090b] text-white p-8">
    <div class="max-w-md mx-auto bg-[#18181b] border border-[#27272a] p-6 rounded-xl shadow-xl">
        <h2 class="text-xl font-bold mb-4">Creer un Compte & Deployer 6 Ecrans</h2>

        <?php if ($error): ?>
            <div class="p-3 mb-4 bg-red-500/20 border border-red-500 text-red-400 rounded text-sm"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

            <div>
                <label class="block text-sm text-gray-400 mb-1">Email Netflix</label>
                <input type="email" name="email" required class="w-full p-3 bg-[#27272a] border border-[#3f3f46] rounded text-white focus:outline-none">
            </div>

            <div>
                <label class="block text-sm text-gray-400 mb-1">Mot de passe</label>
                <input type="text" name="password" required class="w-full p-3 bg-[#27272a] border border-[#3f3f46] rounded text-white focus:outline-none">
            </div>

            <div>
                <label class="block text-sm text-gray-400 mb-1">Date de Renouvellement</label>
                <input type="date" name="renewal_date" required class="w-full p-3 bg-[#27272a] border border-[#3f3f46] rounded text-white focus:outline-none">
            </div>

            <div class="flex justify-between items-center pt-2">
                <a href="list.php" class="text-gray-400 hover:text-white text-sm">Annuler</a>
                <button type="submit" class="p-3 bg-gradient-to-r from-amber-500 to-orange-500 rounded font-semibold">Generer le Compte</button>
            </div>
        </form>
    </div>
</body>
</html>
