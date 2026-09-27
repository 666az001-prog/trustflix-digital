<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_NAME', 'trustflix_db');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Connexion à la base de données impossible. Importez database/schema.sql puis vérifiez config/database.php.');
}

/**
 * Échappe une chaîne pour l'affichage HTML.
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Génère ou retourne le jeton CSRF de session.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Vérifie le jeton CSRF soumis.
 */
function verify_csrf(?string $token): bool
{
    return isset($_SESSION['csrf_token'])
        && is_string($token)
        && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Construit une URL relative à la racine de l'application.
 */
function app_url(string $path = 'index.php'): string
{
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $nestedDirs = ['accounts', 'clients', 'expenses', 'database', 'config', 'includes'];

    $base = in_array(basename($scriptDir), $nestedDirs, true)
        ? dirname($scriptDir)
        : $scriptDir;

    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

/**
 * URL vers un asset public.
 */
function asset_url(string $path): string
{
    return app_url('assets/' . ltrim($path, '/'));
}

/**
 * Redirige vers la page de connexion si l'utilisateur n'est pas authentifié.
 */
function check_auth(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . app_url('login.php'));
        exit;
    }
}

/**
 * Définition des slots à créer selon le service (logique métier Niveau 1).
 *
 * @return array{count: int, prefix: string}
 */
function slot_plan_for_service(array $service): array
{
    $code = $service['code'] ?? '';

    return match ($code) {
        'netflix' => ['count' => 5, 'prefix' => 'Écran'],
        'spotify' => ['count' => 6, 'prefix' => 'Place'],
        'apple_music' => ['count' => 5, 'prefix' => 'Accès'],
        default => [
            'count' => max(1, (int) ($service['default_slots'] ?? 1)),
            'prefix' => 'Slot',
        ],
    };
}

/**
 * Crée les slots d'un compte fournisseur selon le service.
 */
function create_slots_for_account(PDO $pdo, int $accountId, array $service): void
{
    $plan = slot_plan_for_service($service);
    $stmt = $pdo->prepare(
        'INSERT INTO slots (account_id, slot_name, pin_code, status) VALUES (?, ?, ?, ?)'
    );

    for ($i = 1; $i <= $plan['count']; $i++) {
        $slotName = $plan['prefix'] . ' ' . $i;
        $stmt->execute([$accountId, $slotName, '0000', 'Libre']);
    }
}

/**
 * Statistiques dashboard : comptes par service + slots libres/occupés.
 *
 * @return array{
 *   services: list<array<string, mixed>>,
 *   totals: array{accounts:int, slots:int, free:int, occupied:int}
 * }
 */
function fetch_dashboard_stats(PDO $pdo): array
{
    $servicesStmt = $pdo->query(
        'SELECT s.id, s.name, s.code, s.default_slots,
                COUNT(DISTINCT a.id) AS account_count,
                COUNT(sl.id) AS slot_count,
                SUM(CASE WHEN sl.status = "Libre" THEN 1 ELSE 0 END) AS free_slots,
                SUM(CASE WHEN sl.status = "Occupé" THEN 1 ELSE 0 END) AS occupied_slots
         FROM services s
         LEFT JOIN accounts a ON a.service_id = s.id
         LEFT JOIN slots sl ON sl.account_id = a.id
         GROUP BY s.id, s.name, s.code, s.default_slots
         ORDER BY s.name ASC'
    );

    $services = $servicesStmt->fetchAll();

    $totalsStmt = $pdo->query(
        'SELECT
            (SELECT COUNT(*) FROM accounts) AS accounts,
            (SELECT COUNT(*) FROM slots) AS slots,
            (SELECT COUNT(*) FROM slots WHERE status = "Libre") AS free,
            (SELECT COUNT(*) FROM slots WHERE status = "Occupé") AS occupied'
    );
    $totals = $totalsStmt->fetch() ?: ['accounts' => 0, 'slots' => 0, 'free' => 0, 'occupied' => 0];

    return [
        'services' => $services,
        'totals' => [
            'accounts' => (int) $totals['accounts'],
            'slots' => (int) $totals['slots'],
            'free' => (int) $totals['free'],
            'occupied' => (int) $totals['occupied'],
        ],
    ];
}
