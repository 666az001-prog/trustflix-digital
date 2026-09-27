-- TrustFlix Digital V2 - Données de Test
-- Insérez ces données après avoir exécuté schema.sql pour tester l'application

-- ATTENTION : À utiliser UNIQUEMENT en développement
-- Supprimez ces données avant la production

-- Ajouter des clients de test
INSERT INTO clients (name, whatsapp, plan_type, start_date, end_date, price, notes) VALUES
('Jean Dupont', '0750123456', 'Premium', '2026-05-01', '2026-07-01', 25000, 'Client VIP - Paiement mensuel'),
('Marie Martin', '0701234567', 'Professionnel', '2026-05-15', '2026-06-10', 15000, 'À relancer bientôt'),
('Pierre Bernard', '0787654321', 'Starter', '2026-04-01', '2026-06-03', 5000, 'Contrat annuel - Expiré'),
('Sophie Durand', '0740000000', 'Professionnel', '2026-06-01', '2026-06-08', 18000, 'En alerte - Moins de 3 jours'),
('Marc Lefevre', '0750999999', 'Premium', '2026-06-01', '2026-07-15', 35000, 'Nouvelle inscription'),
('Julie Rousseau', '0741111111', 'Premium', '2026-05-01', '2026-08-01', 28000, 'Contrat trimestriel'),
('Thomas Girard', '0742222222', 'Starter', '2026-06-03', '2026-06-20', 8000, 'En période d\'essai'),
('Anne Moreau', '0743333333', 'Professionnel', '2026-05-10', '2026-06-10', 16000, 'À réactiver');

-- Ajouter des paiements de test
INSERT INTO payments (client_id, amount, type, description, payment_date) VALUES
-- Client 1
(1, 25000, 'Entrée', 'Paiement initial - Premium', '2026-05-01'),
(1, 25000, 'Entrée', 'Renouvellement Premium', '2026-06-01'),

-- Client 2
(2, 15000, 'Entrée', 'Paiement initial - Professionnel', '2026-05-15'),
(2, 2000, 'Sortie', 'Frais de support', '2026-05-20'),

-- Client 3
(3, 5000, 'Entrée', 'Paiement initial - Starter', '2026-04-01'),
(3, 300, 'Sortie', 'Stockage supplémentaire', '2026-05-01'),

-- Client 4
(4, 18000, 'Entrée', 'Paiement initial - Professionnel', '2026-06-01'),

-- Client 5
(5, 35000, 'Entrée', 'Paiement initial - Premium', '2026-06-01'),

-- Client 6
(6, 28000, 'Entrée', 'Paiement initial - Premium', '2026-05-01'),
(6, 1500, 'Sortie', 'Consultation technique', '2026-05-15'),

-- Client 7
(7, 8000, 'Entrée', 'Paiement initial - Starter', '2026-06-03'),

-- Client 8
(8, 16000, 'Entrée', 'Paiement initial - Professionnel', '2026-05-10'),
(8, 5000, 'Sortie', 'Formation utilisateurs', '2026-05-25');

-- Vérifier les données
SELECT 'Clients créés' as status, COUNT(*) as total FROM clients;
SELECT 'Paiements créés' as status, COUNT(*) as total FROM payments;

-- Voir le résumé financier
SELECT
    'Revenus' as type,
    SUM(amount) as total
FROM payments
WHERE type = 'Entrée'
UNION ALL
SELECT
    'Dépenses' as type,
    SUM(amount) as total
FROM payments
WHERE type = 'Sortie'
UNION ALL
SELECT
    'Solde net' as type,
    SUM(CASE WHEN type = 'Entrée' THEN amount ELSE -amount END) as total
FROM payments;

-- Afficher les clients et leurs statuts
SELECT
    c.id,
    c.name,
    c.plan_type,
    c.end_date,
    DATEDIFF(c.end_date, CURDATE()) as days_left,
    CASE
        WHEN c.end_date < CURDATE() THEN 'EXPIRÉ 🔴'
        WHEN DATEDIFF(c.end_date, CURDATE()) <= 3 THEN 'ALERTE 🟠'
        ELSE 'ACTIF 🟢'
    END as statut,
    c.price
FROM clients c
ORDER BY c.end_date ASC;
