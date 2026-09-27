<?php
/**
 * Tâche planifiée : libère les profils expirés.
 * Exemple Windows (Planificateur) : php C:\xampp\htdocs\trustflix\cron\expire.php
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

ensure_expenses_table($pdo);
sync_expired_subscriptions($pdo);

$expired = (int) $pdo->query("SELECT COUNT(*) FROM clients WHERE status = 'Expiré' AND end_date < CURDATE()")->fetchColumn();
echo date('Y-m-d H:i:s') . " — synchronisation OK (clients expirés cumulés : {$expired})\n";
