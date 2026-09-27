<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function format_whatsapp($phone) {
    $cleaned = preg_replace('/\D/', '', $phone);
    if (strpos($cleaned, '225') !== 0) {
        $cleaned = '225' . $cleaned;
    }
    return $cleaned;
}

function format_whatsapp_display($phone) {
    return '+' . format_whatsapp($phone);
}

function get_expiry_badge($end_date) {
    $today = new DateTime('today');
    $end = new DateTime($end_date);
    $days = (int) $today->diff($end)->format('%r%a');

    if ($days < 0) {
        return ['class' => 'badge-danger', 'label' => 'Expiré', 'icon' => '🔴'];
    }
    if ($days === 0) {
        return ['class' => 'badge-danger', 'label' => "Expire aujourd'hui", 'icon' => '🔴'];
    }
    if ($days <= 3) {
        return ['class' => 'badge-warning', 'label' => "J-{$days}", 'icon' => '🟠'];
    }
    if ($days <= 7) {
        return ['class' => 'badge-info', 'label' => "J-{$days}", 'icon' => '🟡'];
    }
    return ['class' => 'badge-success', 'label' => 'Actif', 'icon' => ''];
}

function whatsapp_renewal_url($name, $phone, $end_date, $formula = 'Premium') {
    $dateFormatted = date('d/m/Y', strtotime($end_date));
    $msg = "Bonjour {$name}, votre abonnement TrustFlix (formule {$formula}) expire le {$dateFormatted}. "
         . "Merci de renouveler pour éviter toute interruption de service. Contactez-nous pour votre réabonnement !";
    return 'https://api.whatsapp.com/send?phone=' . urlencode(format_whatsapp($phone))
         . '&text=' . urlencode($msg);
}

function app_url($path = 'index.php') {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $nested = ['clients', 'accounts', 'expenses', 'cron'];
    $base = in_array(basename($scriptDir), $nested, true) ? dirname($scriptDir) : $scriptDir;

    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

function ensure_expenses_table(PDO $pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `expenses` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `label` VARCHAR(255) NOT NULL,
        `amount` INT NOT NULL,
        `expense_date` DATE NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function sync_expired_subscriptions(PDO $pdo) {
    $pdo->exec("UPDATE clients SET status = 'Expiré' WHERE end_date < CURDATE() AND status = 'Actif'");
    $pdo->exec("UPDATE profiles p
        INNER JOIN clients c ON c.profile_id = p.id
        SET p.status = 'Libre'
        WHERE c.end_date < CURDATE() AND p.status = 'Occupé'");
}

function check_auth() {
    global $pdo;

    if (empty($_SESSION['admin_logged'])) {
        header('Location: ' . app_url('login.php'));
        exit;
    }

    if ($pdo instanceof PDO) {
        ensure_expenses_table($pdo);
        sync_expired_subscriptions($pdo);
    }
}
