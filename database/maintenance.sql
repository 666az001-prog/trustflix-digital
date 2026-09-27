-- TrustFlix Digital V2 - Maintenance & Utilities

-- Réinitialiser le mot de passe admin à admin123
-- Exécutez cette requête si vous avez oublié le mot de passe
UPDATE users
SET password_hash = '$2y$10$YIjlrBQr2ESkC7oLKp3Q9O6Y5y8QqN5X9mZ1K2w3V4x5Z6Y7A8B9C'
WHERE username = 'admin';

-- Pour définir un nouveau mot de passe :
-- 1. Générez le hash avec : echo password_hash('votre_mot_de_passe', PASSWORD_BCRYPT, ['cost' => 10]);
-- 2. Remplacez le hash ci-dessus
-- Exemple : password_hash('MonNouveauMotDePasse123', PASSWORD_BCRYPT, ['cost' => 10])

-- Supprimer tous les logs (optionnel)
TRUNCATE TABLE logs;

-- Supprimer tous les paiements (ATTENTION : irréversible)
-- TRUNCATE TABLE payments;

-- Supprimer tous les clients (ATTENTION : supprime aussi les paiements automatiquement)
-- TRUNCATE TABLE clients;

-- Afficher les stats des logs
SELECT
    level,
    COUNT(*) as count,
    MAX(created_at) as last_occurrence
FROM logs
GROUP BY level
ORDER BY created_at DESC;

-- Afficher les erreurs des 24 dernières heures
SELECT
    created_at,
    message,
    context
FROM logs
WHERE level = 'ERROR' AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY created_at DESC;

-- Afficher les clients expirés
SELECT
    id,
    name,
    end_date,
    DATEDIFF(end_date, CURDATE()) as days_left,
    price
FROM clients
WHERE end_date < CURDATE()
ORDER BY end_date ASC;

-- Afficher les clients en alerte (expiration dans 3 jours ou moins)
SELECT
    id,
    name,
    end_date,
    DATEDIFF(end_date, CURDATE()) as days_left,
    price
FROM clients
WHERE end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
ORDER BY end_date ASC;

-- Calculer le chiffre d'affaires total
SELECT
    (SELECT SUM(amount) FROM payments WHERE type = 'Entrée') as revenus,
    (SELECT SUM(amount) FROM payments WHERE type = 'Sortie') as depenses,
    (SELECT SUM(amount) FROM payments WHERE type = 'Entrée') -
    (SELECT SUM(amount) FROM payments WHERE type = 'Sortie') as solde_net;

-- Afficher le chiffre d'affaires par client
SELECT
    c.id,
    c.name,
    c.plan_type,
    c.price,
    COUNT(p.id) as transactions,
    SUM(CASE WHEN p.type = 'Entrée' THEN p.amount ELSE -p.amount END) as net
FROM clients c
LEFT JOIN payments p ON c.id = p.client_id
GROUP BY c.id
ORDER BY c.name;

-- Exporter la liste des clients en alerte pour WhatsApp
SELECT
    name as Nom,
    whatsapp as Telephone,
    end_date as DateExpiration,
    DATEDIFF(end_date, CURDATE()) as JoursRestants,
    price as Prix
FROM clients
WHERE end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
ORDER BY end_date ASC;
