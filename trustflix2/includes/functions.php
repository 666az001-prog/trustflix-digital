<?php
if (session_status() === PHP_SESSION_NONE) {
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

    if (strpos($cleaned, '225') === 0) {
        return '+' . $cleaned;
    }

    return '+225' . $cleaned;
}

function check_auth() {
    if (empty($_SESSION['admin_logged'])) {
        header('Location: /login.php');
        exit;
    }
}
