<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
check_auth();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Token de sécurité invalide.';
    } else {
        $label = trim($_POST['label'] ?? '');
        $amount = (int) ($_POST['amount'] ?? 0);
        $expense_date = trim($_POST['expense_date'] ?? '');

        if ($label === '' || $amount <= 0 || !strtotime($expense_date)) {
            $error = 'Veuillez remplir tous les champs correctement.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO expenses (label, amount, expense_date) VALUES (?, ?, ?)');
            $stmt->execute([$label, $amount, $expense_date]);
            $success = 'Dépense enregistrée avec succès.';
        }
    }
}

$stmtEntrees = $pdo->query('SELECT COALESCE(SUM(amount), 0) as total FROM payments');
$totalEntrees = (int) ($stmtEntrees->fetch()['total'] ?? 0);
$stmtSorties = $pdo->query('SELECT COALESCE(SUM(amount), 0) as total FROM expenses');
$totalSorties = (int) ($stmtSorties->fetch()['total'] ?? 0);
$beneficeNet = $totalEntrees - $totalSorties;

$expenses = $pdo->query('SELECT * FROM expenses ORDER BY expense_date DESC, id DESC LIMIT 20')->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Trésorerie - TrustFlix</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-[#09090b] text-white p-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">
                <a href="../index.php" class="text-gray-400 hover:text-white mr-2"><i class="fas fa-arrow-left"></i></a>
                Trésorerie
            </h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <div class="p-4 bg-[#18181b] border border-[#27272a] rounded-xl">
                <div class="text-gray-400 text-xs">Entrées</div>
                <div class="text-xl font-bold text-green-500"><?= number_format($totalEntrees, 0, ',', ' ') ?> F CFA</div>
            </div>
            <div class="p-4 bg-[#18181b] border border-[#27272a] rounded-xl">
                <div class="text-gray-400 text-xs">Sorties</div>
                <div class="text-xl font-bold text-red-500"><?= number_format($totalSorties, 0, ',', ' ') ?> F CFA</div>
            </div>
            <div class="p-4 bg-[#18181b] border border-amber-500/30 rounded-xl">
                <div class="text-gray-400 text-xs">Bénéfice Net</div>
                <div class="text-xl font-bold text-amber-500"><?= number_format($beneficeNet, 0, ',', ' ') ?> F CFA</div>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-red-500/20 border border-red-500/40 text-red-400 rounded text-sm"><?= e($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="mb-4 p-3 bg-green-500/20 border border-green-500/40 text-green-400 rounded text-sm"><?= e($success) ?></div>
        <?php endif; ?>

        <div class="p-6 bg-[#18181b] border border-[#27272a] rounded-xl mb-8">
            <h3 class="text-lg font-semibold mb-4">Nouvelle sortie (dépense)</h3>
            <form method="POST" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Libellé</label>
                    <input type="text" name="label" placeholder="Ex: Achat compte Netflix" required
                           class="w-full p-2 bg-[#27272a] border border-[#3f3f46] rounded text-sm text-white">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Montant (F CFA)</label>
                    <input type="number" name="amount" min="1" required
                           class="w-full p-2 bg-[#27272a] border border-[#3f3f46] rounded text-sm text-white">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Date</label>
                    <input type="date" name="expense_date" value="<?= date('Y-m-d') ?>" required
                           class="w-full p-2 bg-[#27272a] border border-[#3f3f46] rounded text-sm text-white">
                </div>
                <div class="sm:col-span-3">
                    <button type="submit" class="p-2 px-4 bg-gradient-to-r from-amber-500 to-orange-500 rounded font-semibold text-sm">
                        <i class="fas fa-plus mr-1"></i> Enregistrer la dépense
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-[#18181b] border border-[#27272a] rounded-xl overflow-hidden">
            <h3 class="p-4 text-lg font-semibold border-b border-[#27272a]">Dernières sorties</h3>
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-[#27272a] text-gray-400">
                        <th class="p-3">Libellé</th>
                        <th class="p-3">Montant</th>
                        <th class="p-3">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#27272a]">
                    <?php if (empty($expenses)): ?>
                        <tr><td colspan="3" class="p-4 text-center text-gray-500">Aucune dépense enregistrée</td></tr>
                    <?php else: ?>
                        <?php foreach ($expenses as $exp): ?>
                            <tr>
                                <td class="p-3"><?= e($exp['label']) ?></td>
                                <td class="p-3 text-red-400">-<?= number_format($exp['amount'], 0, ',', ' ') ?> F CFA</td>
                                <td class="p-3 text-gray-400"><?= e(date('d/m/Y', strtotime($exp['expense_date']))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
