<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
check_auth();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . app_url('clients/list.php'));
    exit;
}

try {
    $pdo->beginTransaction();

    $slotStmt = $pdo->prepare('SELECT slot_id FROM clients WHERE id = ? LIMIT 1');
    $slotStmt->execute([$id]);
    $slot = $slotStmt->fetch();

    if ($slot && !empty($slot['slot_id'])) {
        $freeStmt = $pdo->prepare('UPDATE slots SET status = "Libre" WHERE id = ?');
        $freeStmt->execute([$slot['slot_id']]);
    }

    $deleteStmt = $pdo->prepare('DELETE FROM clients WHERE id = ?');
    $deleteStmt->execute([$id]);

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['flash_error'] = 'Impossible de supprimer ce client.';
}

header('Location: ' . app_url('clients/list.php'));
exit;
