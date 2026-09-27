<?php
/**
 * Installation one-shot : importe schema.sql dans MySQL (XAMPP).
 * Supprimez ou renommez ce fichier après installation en production.
 */
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

$schemaPath = __DIR__ . '/schema.sql';
if (!is_readable($schemaPath)) {
    die('schema.sql introuvable.');
}

$sql = file_get_contents($schemaPath);
$statements = array_filter(array_map('trim', preg_split('/;\s*\n/', $sql)));

$done = 0;
foreach ($statements as $statement) {
    if ($statement === '' || stripos($statement, 'CREATE DATABASE') === 0) {
        continue;
    }
    $pdo->exec($statement);
    $done++;
}

ensure_expenses_table($pdo);

$hash = password_hash('admin123', PASSWORD_DEFAULT);
$pdo->prepare('INSERT INTO users (username, password) VALUES (?, ?) ON DUPLICATE KEY UPDATE password = VALUES(password)')
    ->execute(['admin', $hash]);

echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Installation TrustFlix</title>';
echo '<script src="https://cdn.tailwindcss.com"></script></head>';
echo '<body class="bg-[#09090b] text-white flex items-center justify-center min-h-screen">';
echo '<div class="max-w-md p-8 bg-[#18181b] border border-[#27272a] rounded-xl text-center">';
echo '<h1 class="text-xl font-bold text-amber-500 mb-4">Installation terminée</h1>';
echo '<p class="text-gray-400 text-sm mb-6">' . (int) $done . ' requêtes SQL exécutées.<br>Identifiants : <strong>admin</strong> / <strong>admin123</strong></p>';
echo '<a href="login.php" class="inline-block p-3 bg-gradient-to-r from-amber-500 to-orange-500 rounded font-semibold">Se connecter</a>';
echo '</div></body></html>';
