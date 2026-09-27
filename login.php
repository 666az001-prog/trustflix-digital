<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: ' . app_url('index.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Jeton de sécurité invalide.';
    } elseif ($username === '' || $password === '') {
        $error = 'Veuillez saisir vos identifiants.';
    } else {
        $stmt = $pdo->prepare('SELECT id, username, password_hash FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: ' . app_url('index.php'));
            exit;
        }

        $error = 'Identifiants invalides.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — TrustFlix Digital</title>
    <link rel="stylesheet" href="<?= e(asset_url('css/app.css')) ?>">
</head>
<body>
<div class="login-wrap">
    <div class="card login-card">
        <h1 class="brand" style="text-align:center;">TrustFlix Digital</h1>
        <p class="muted" style="text-align:center; margin-bottom: 1.25rem;">Niveau 1 — Gestion multi-services</p>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" class="grid" style="gap: 1rem;">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <div>
                <label class="label" for="username">Nom d'utilisateur</label>
                <input class="input" type="text" id="username" name="username" required autocomplete="username">
            </div>
            <div>
                <label class="label" for="password">Mot de passe</label>
                <input class="input" type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <button class="btn" type="submit">Se connecter</button>
        </form>
        <p class="muted" style="margin-top: 1rem; font-size: 0.75rem;">Par défaut après import SQL : admin / admin123</p>
    </div>
</div>
</body>
</html>
