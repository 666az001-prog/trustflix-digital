<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
check_auth();

$id = intval($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = $pdo->prepare("DELETE FROM netflix_accounts WHERE id = ?");
    $stmt->execute([$id]);
}
header('Location: list.php');
exit;
