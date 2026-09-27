<?php
declare(strict_types=1);

$pageTitle = $pageTitle ?? 'TrustFlix Digital';
$activeNav = $activeNav ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= e(app_url('assets/css/style.css')) ?>">
</head>
<body>
<div class="layout">
    <?php require __DIR__ . '/sidebar.php'; ?>
    <main class="main">
