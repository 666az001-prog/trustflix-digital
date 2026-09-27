<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
check_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: list.php');
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    header('Location: list.php');
    exit;
}

$profile_id = intval($_POST['profile_id'] ?? 0);
$profile_name = trim($_POST['profile_name'] ?? '');
$pin_code = preg_replace('/\D/', '', $_POST['pin_code'] ?? '');

if ($profile_id > 0 && $profile_name !== '' && preg_match('/^\d{4}$/', $pin_code)) {
    $stmt = $pdo->prepare("UPDATE profiles SET profile_name = ?, pin_code = ? WHERE id = ?");
    $stmt->execute([$profile_name, $pin_code, $profile_id]);
}

header('Location: list.php');
exit;
