<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_logged'] = true;
            $_SESSION['username'] = $user['username'];
            header('Location: index.php');
            exit;
        }

        $error = "Identifiants invalides.";
    } else {
        $error = "Jeton de securite invalide.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - TrustFlix Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-[#09090b] text-white flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md p-8 bg-[#18181b] border border-[#27272a] rounded-xl shadow-2xl mx-4">
        <h2 class="text-3xl font-bold mb-6 text-center bg-gradient-to-r from-amber-500 to-orange-500 bg-clip-text text-transparent">TrustFlix Digital</h2>

        <?php if ($error): ?>
            <div class="p-3 mb-4 bg-red-500/20 border border-red-500 text-red-400 rounded text-sm"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">Nom d'utilisateur</label>
                <input type="text" name="username" required class="w-full p-3 bg-[#27272a] border border-[#3f3f46] rounded focus:outline-none focus:border-orange-500 text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">Mot de passe</label>
                <input type="password" name="password" required class="w-full p-3 bg-[#27272a] border border-[#3f3f46] rounded focus:outline-none focus:border-orange-500 text-white">
            </div>

            <button type="submit" class="w-full p-3 mt-4 bg-gradient-to-r from-amber-500 to-orange-500 rounded font-semibold hover:opacity-90 transition">Se connecter</button>
        </form>
    </div>
</body>
</html>
