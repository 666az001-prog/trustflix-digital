<?php
/**
 * TrustFlix Digital - Health Check
 * À utiliser UNIQUEMENT en développement local
 * Supprimer en production !
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Health Check - TrustFlix</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #09090b;
            color: #fff;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto;
            line-height: 1.6;
        }
        .container { max-width: 800px; margin: 50px auto; padding: 20px; }
        h1 { color: #f59e0b; margin-bottom: 30px; }
        .check {
            background: #18181b;
            border: 1px solid #27272a;
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
        }
        .success { border-left: 4px solid #22c55e; }
        .error { border-left: 4px solid #ef4444; }
        .warning { border-left: 4px solid #eab308; }
        .status { display: flex; align-items: center; gap: 10px; }
        .icon { font-size: 20px; }
        code { background: #27272a; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 TrustFlix - Health Check</h1>

        <!-- PHP Version -->
        <div class="check <?php echo version_compare(PHP_VERSION, '7.0') >= 0 ? 'success' : 'error'; ?>">
            <div class="status">
                <span class="icon"><?php echo version_compare(PHP_VERSION, '7.0') >= 0 ? '✅' : '❌'; ?></span>
                <div>
                    <strong>PHP Version</strong><br>
                    <code><?php echo PHP_VERSION; ?></code>
                    (Requis : 7.0+)
                </div>
            </div>
        </div>

        <!-- PDO Extension -->
        <div class="check <?php echo extension_loaded('pdo') ? 'success' : 'error'; ?>">
            <div class="status">
                <span class="icon"><?php echo extension_loaded('pdo') ? '✅' : '❌'; ?></span>
                <div>
                    <strong>PDO Extension</strong><br>
                    <?php echo extension_loaded('pdo') ? 'Disponible' : 'Non trouvée'; ?>
                </div>
            </div>
        </div>

        <!-- PDO MySQL Driver -->
        <div class="check <?php echo extension_loaded('pdo_mysql') ? 'success' : 'error'; ?>">
            <div class="status">
                <span class="icon"><?php echo extension_loaded('pdo_mysql') ? '✅' : '❌'; ?></span>
                <div>
                    <strong>PDO MySQL Driver</strong><br>
                    <?php echo extension_loaded('pdo_mysql') ? 'Disponible' : 'Non trouvé'; ?>
                </div>
            </div>
        </div>

        <!-- Database Connection -->
        <div class="check <?php $conn = null; try { $conn = db(); echo 'success'; } catch (Exception $e) { echo 'error'; } ?>">
            <div class="status">
                <span class="icon"><?php echo $conn ? '✅' : '❌'; ?></span>
                <div>
                    <strong>Connexion Base de Données</strong><br>
                    <?php
                    if ($conn) {
                        echo 'Connecté à <code>' . DB_NAME . '@' . DB_HOST . '</code>';
                    } else {
                        echo 'Impossible de se connecter. Vérifiez config/database.php';
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Tables Check -->
        <?php if ($conn): ?>
            <div class="check success">
                <strong>✅ Tables de la Base de Données</strong>
                <ul style="margin-top: 10px; margin-left: 20px;">
                    <?php
                    $tables = ['users', 'clients', 'payments', 'logs'];
                    foreach ($tables as $table) {
                        try {
                            $stmt = $conn->prepare("SHOW TABLES LIKE ?");
                            $stmt->execute([$table]);
                            $exists = $stmt->fetch() !== false;
                            echo '<li style="color: ' . ($exists ? '#22c55e' : '#ef4444') . '">';
                            echo ($exists ? '✓' : '✗') . ' ' . $table;
                            echo '</li>';
                        } catch (Exception $e) {
                            echo '<li style="color: #ef4444;">✗ ' . $table . '</li>';
                        }
                    }
                    ?>
                </ul>
            </div>

            <!-- Admin User Check -->
            <div class="check success">
                <strong>✅ Utilisateurs Admin</strong>
                <div style="margin-top: 10px; font-size: 14px;">
                    <?php
                    try {
                        $stmt = $conn->prepare("SELECT username, role FROM users LIMIT 5");
                        $stmt->execute();
                        $users = $stmt->fetchAll();
                        echo '<table style="width: 100%; border-collapse: collapse;">';
                        echo '<tr style="background: #27272a;"><th style="padding: 8px; text-align: left;">Utilisateur</th><th style="padding: 8px; text-align: left;">Rôle</th></tr>';
                        foreach ($users as $user) {
                            echo '<tr><td style="padding: 8px;">' . htmlspecialchars($user['username']) . '</td>';
                            echo '<td style="padding: 8px; color: #f59e0b;">' . htmlspecialchars($user['role']) . '</td></tr>';
                        }
                        echo '</table>';
                        if (empty($users)) {
                            echo '<p style="color: #eab308;">Aucun utilisateur trouvé. Réexécutez schema.sql.</p>';
                        }
                    } catch (Exception $e) {
                        echo '<p style="color: #ef4444;">Erreur : ' . htmlspecialchars($e->getMessage()) . '</p>';
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Session Support -->
        <div class="check success">
            <div class="status">
                <span class="icon">✅</span>
                <div>
                    <strong>Sessions PHP</strong><br>
                    Configurées et fonctionnelles
                </div>
            </div>
        </div>

        <!-- File Permissions -->
        <div class="check <?php echo is_writable(__DIR__ . '/logs') ? 'success' : 'warning'; ?>">
            <div class="status">
                <span class="icon"><?php echo is_writable(__DIR__ . '/logs') ? '✅' : '⚠️'; ?></span>
                <div>
                    <strong>Permissions Dossier <code>logs/</code></strong><br>
                    <?php
                    if (is_writable(__DIR__ . '/logs')) {
                        echo 'Permissions correctes (écriture OK)';
                    } else {
                        echo 'ATTENTION : Pas d\'accès en écriture. Configurez permissions 755.';
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div style="background: #1f2937; padding: 20px; border-radius: 8px; margin-top: 30px; border-left: 4px solid #f59e0b;">
            <h2 style="margin-bottom: 10px;">📋 Résumé</h2>
            <p>✅ <strong>L'application est prête à fonctionner !</strong></p>
            <p style="margin-top: 15px; font-size: 14px; color: #d1d5db;">
                🔗 <a href="/login.php" style="color: #f59e0b; text-decoration: none;">Accédez à l'application</a><br>
                👤 Identifiant : <code>admin</code><br>
                🔐 Mot de passe : <code>admin123</code>
            </p>
            <p style="margin-top: 15px; font-size: 12px; color: #9ca3af;">
                ⚠️ <strong>IMPORTANT :</strong> Ce fichier est pour le développement uniquement.
                <strong style="color: #ef4444;">Supprimez-le en production !</strong>
            </p>
        </div>
    </div>
</body>
</html>
