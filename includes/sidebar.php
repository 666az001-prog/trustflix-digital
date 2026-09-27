<?php
declare(strict_types=1);

$navItems = [
    'dashboard' => ['label' => 'Dashboard', 'href' => app_url('index.php')],
    'accounts_list' => ['label' => 'Comptes fournisseurs', 'href' => app_url('accounts/list.php')],
    'accounts_add' => ['label' => 'Ajouter un compte', 'href' => app_url('accounts/add.php')],
    'clients_list' => ['label' => 'Clients', 'href' => app_url('clients/list.php')],
    'clients_add' => ['label' => 'Ajouter un client', 'href' => app_url('clients/add.php')],
    'payments_list' => ['label' => 'Paiements', 'href' => app_url('payments/list.php')],
    'reports_index' => ['label' => 'Rapports', 'href' => app_url('reports/index.php')],
];
?>
<aside class="sidebar">
    <div class="brand">TrustFlix Digital</div>
    <nav>
        <?php foreach ($navItems as $key => $item): ?>
            <a class="nav-link <?= $activeNav === $key ? 'active' : '' ?>" href="<?= e($item['href']) ?>">
                <?= e($item['label']) ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <div style="margin-top: 2rem;">
        <p class="muted" style="margin-bottom: 0.5rem;">Connecté : <?= e($_SESSION['username'] ?? 'admin') ?></p>
        <a class="nav-link" href="<?= e(app_url('logout.php')) ?>">Déconnexion</a>
    </div>
</aside>
