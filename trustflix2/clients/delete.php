<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
check_auth();

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT profile_id FROM clients WHERE id = ?");
        $stmt->execute([$id]);
        $client = $stmt->fetch();

        if ($client && $client['profile_id']) {
            $up_p = $pdo->prepare("UPDATE profiles SET status = 'Libre' WHERE id = ?");
            $up_p->execute([$client['profile_id']]);
        }

        $del = $pdo->prepare("DELETE FROM clients WHERE id = ?");
        $del->execute([$id]);

        $pdo->commit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
    }
}

header('Location: list.php');
exit;
