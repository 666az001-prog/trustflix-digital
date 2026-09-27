<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
check_auth();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $name = trim($_POST['name'] ?? '');
        $whatsapp = trim($_POST['whatsapp'] ?? '');
        $formula = $_POST['formula'] ?? 'Premium';
        $price = intval($_POST['price'] ?? 2000);
        $start_date = trim($_POST['start_date'] ?? date('Y-m-d'));
        $end_date = date('Y-m-d', strtotime($start_date . ' + 30 days'));

        if (!empty($name) && !empty($whatsapp)) {
            try {
                $pdo->beginTransaction();

                $p_stmt = $pdo->prepare("SELECT id FROM profiles WHERE status = 'Libre' LIMIT 1 FOR UPDATE");
                $p_stmt->execute();
                $profile = $p_stmt->fetch();

                if ($profile) {
                    $profile_id = $profile['id'];

                    $up_p = $pdo->prepare("UPDATE profiles SET status = 'Occupe' WHERE id = ?");
                    $up_p->execute([$profile_id]);

                    $c_stmt = $pdo->prepare("INSERT INTO clients (profile_id, name, whatsapp, formula, price, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Actif')");
                    $c_stmt->execute([$profile_id, $name, $whatsapp, $formula, $price, $start_date, $end_date]);
                    $client_id = $pdo->lastInsertId();

                    $pay_stmt = $pdo->prepare("INSERT INTO payments (client_id, amount, payment_date) VALUES (?, ?, ?)");
                    $pay_stmt->execute([$client_id, $price, $start_date]);

                    $pdo->commit();
                    header('Location: list.php');
                    exit;
                }

                $pdo->rollBack();
                $error = "Plus aucun profil libre disponible sur vos comptes meres.";
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $error = "Erreur lors de la reservation.";
            }
        } else {
            $error = "Veuillez remplir le nom et le numero WhatsApp.";
        }
    } else {
        $error = "Jeton CSRF invalide.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Client - TrustFlix Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-[#09090b] text-white p-8">
    <div class="max-w-md mx-auto bg-[#18181b] border border-[#27272a] p-6 rounded-xl shadow-xl">
        <h2 class="text-xl font-bold mb-4 text-amber-500">Attribuer une Vente a un Ecran</h2>

        <?php if ($error): ?>
            <div class="p-3 mb-4 bg-red-500/20 border border-red-500 text-red-400 rounded text-sm"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

            <div>
                <label class="block text-sm text-gray-400 mb-1">Nom / Pseudo du Client</label>
                <input type="text" name="name" required class="w-full p-3 bg-[#27272a] border border-[#3f3f46] rounded text-white focus:outline-none">
            </div>

            <div>
                <label class="block text-sm text-gray-400 mb-1">Numero WhatsApp</label>
                <input type="text" name="whatsapp" required class="w-full p-3 bg-[#27272a] border border-[#3f3f46] rounded text-white focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Formule</label>
                    <select name="formula" class="w-full p-3 bg-[#27272a] border border-[#3f3f46] rounded text-white focus:outline-none">
                        <option value="Premium">Premium</option>
                        <option value="Standard">Standard</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-1">Prix Paye (F CFA)</label>
                    <input type="number" name="price" value="2000" min="0" class="w-full p-3 bg-[#27272a] border border-[#3f3f46] rounded text-white focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-sm text-gray-400 mb-1">Date de debut</label>
                <input type="date" name="start_date" value="<?= date('Y-m-d') ?>" class="w-full p-3 bg-[#27272a] border border-[#3f3f46] rounded text-white focus:outline-none">
            </div>

            <div class="flex justify-between items-center pt-2">
                <a href="list.php" class="text-gray-400 hover:text-white text-sm">Annuler</a>
                <button type="submit" class="p-3 bg-gradient-to-r from-amber-500 to-orange-500 rounded font-semibold">Associer Ecran</button>
            </div>
        </form>
    </div>
</body>
</html>
