# ✅ Checklist Post-Installation

## 🎯 Avant le Lancement (Local XAMPP)

- [ ] **Apache démarré** → Panneau XAMPP
- [ ] **MySQL démarré** → Panneau XAMPP
- [ ] **Base de données créée** `trustflix_v2` → PhpMyAdmin
- [ ] **schema.sql importé** → PhpMyAdmin → SQL
- [ ] **config/database.php configuré** → Vérifier identifiants MySQL (lignes 7-10)
- [ ] **Tous les fichiers uploadés** → Dossier c:/xampp/htdocs/trustflix/
- [ ] **health-check.php vert** → http://localhost/trustflix/health-check.php
- [ ] **Login fonctionne** → Identifiant: admin / Password: admin123
- [ ] **Dashboard chargé** → Statistiques visibles
- [ ] **Créer un client test** → Vérifier paiement auto-créé
- [ ] **WhatsApp link fonctionnel** → Clique sur 💬 dans la liste clients

## 🚀 Avant la Production InfinityFree

- [ ] **Changé mot de passe admin**
  ```sql
  -- Générer nouveau hash bcrypt
  echo password_hash('VotreNouveauMotDePasse123', PASSWORD_BCRYPT, ['cost' => 10]);
  
  -- Exécuter dans PhpMyAdmin
  UPDATE users SET password_hash = 'NOUVEAU_HASH' WHERE username = 'admin';
  ```

- [ ] **Supprimé health-check.php** ⚠️ CRITIQUE
  
- [ ] **Supprimé .env-example** (optionnel)
  
- [ ] **Configuré config/database.php**
  ```php
  define('DB_HOST', 'votre_host_infinityfree');
  define('DB_NAME', 'votre_base_infinityfree');
  define('DB_USER', 'votre_user_infinityfree');
  define('DB_PASS', 'votre_password_infinityfree');
  ```

- [ ] **Uploadé TOUS les fichiers via FTP**
  
- [ ] **Dossier logs/ a les permissions 755**
  
- [ ] **Testé login** → https://votredomaine.infinityfree.app/login.php
  
- [ ] **Testé CRUD complet**
  - [ ] Créer client
  - [ ] Éditer client
  - [ ] Ajouter paiement
  - [ ] Voir statuts
  - [ ] Tester WhatsApp

## 📊 Vérifications de Sécurité

- [ ] **PDO préparé utilisé** → Grep `db()->prepare` dans le code
- [ ] **CSRF token présent** → Sur tous les formulaires
- [ ] **Authentification obligatoire** → check_auth() sur pages protégées
- [ ] **Erreurs loggées** → Consultables dans table `logs`
- [ ] **Fichiers sensibles protégés** → .htaccess en place
- [ ] **Sessions timeout actif** → 3600s (1 heure)
- [ ] **Hash bcrypt utilisé** → password_verify() au login

## 🎨 Vérifications UI/UX

- [ ] **Design responsive testé**
  - [ ] Desktop (1920px)
  - [ ] Tablette (768px)
  - [ ] Mobile (375px)

- [ ] **Navigateur testé**
  - [ ] Chrome
  - [ ] Firefox
  - [ ] Safari (Mac/iOS)
  - [ ] Edge

- [ ] **Navigation fluide**
  - [ ] Sidebar visible sur desktop
  - [ ] Menu hamburger fonctionne sur mobile
  - [ ] Lien WhatsApp ouvre wa.me
  - [ ] Boutons Supprimer demandent confirmation

- [ ] **Statuts visuels corrects**
  - [ ] 🟢 Actif (vert)
  - [ ] 🟠 Alerte (jaune)
  - [ ] 🔴 Expiré (rouge)

## 🗄️ Vérifications Base de Données

- [ ] **Tables créées**
  ```sql
  SHOW TABLES;
  -- Doit afficher: users, clients, payments, logs
  ```

- [ ] **Admin utilisateur existe**
  ```sql
  SELECT * FROM users WHERE username = 'admin';
  ```

- [ ] **Index sur dates**
  ```sql
  SHOW INDEX FROM clients;
  -- Doit avoir index sur end_date
  ```

- [ ] **Clés étrangères OK**
  ```sql
  SELECT * FROM payments LIMIT 1;
  -- Doit avoir client_id valide
  ```

## 📝 Documentation

- [ ] **README.md lu** → Comprendre l'architecture
- [ ] **QUICKSTART.md lu** → Pour configurations futures
- [ ] **DEPLOYMENT.md lu** → Instructions complètes InfinityFree
- [ ] **package.json consulté** → Vue d'ensemble du projet

## 🔧 Configuration Recommandée

PHP.ini sur InfinityFree (généralement OK par défaut) :
```ini
memory_limit = 128M+
upload_max_filesize = 64M+
post_max_size = 64M+
max_execution_time = 300+
date.timezone = "Africa/Abidjan"
```

MySQL (généralement OK par défaut) :
```sql
-- Vérifier
SHOW VARIABLES LIKE '%version%';
SHOW VARIABLES LIKE '%collation%';
-- Résultat : utf8mb4_unicode_ci
```

## 📞 En Cas de Problème

### Erreur "Erreur de connexion à la base de données"
1. Vérifier identifiants dans config/database.php
2. Vérifier base de données existe (PhpMyAdmin)
3. Vérifier utilisateur MySQL a permissions

### Erreur "Table not found"
1. Vérifier schema.sql exécuté
2. Réexécuter schema.sql si besoin
3. Vérifier CREATE TABLE syntaxe

### Erreur CSRF "Token invalide"
1. Actualiser la page
2. Vider les cookies navigateur
3. Tester en navigation privée

### WhatsApp ne fonctionne pas
1. Vérifier numéro formaté (ex: 0750123456)
2. Tester manuellement: https://wa.me/225750123456
3. Vérifier indicatif +225 correct

### Statuts incorrects
1. Vérifier la date de serveur: `SELECT NOW();`
2. Vérifier format date client: YYYY-MM-DD
3. Recalculer manuellement: `DATEDIFF(end_date, CURDATE())`

## 📊 Monitoring Post-Lancement

**Chaque semaine :**
- [ ] Consulter table `logs` pour erreurs
- [ ] Vérifier les clients en alerte
- [ ] Vérifier balance revenu/dépense
- [ ] Test de performance (chargement < 2s)

**Chaque mois :**
- [ ] Sauvegarde base de données
- [ ] Vérifier mises à jour PHP/MySQL
- [ ] Analyser l'utilisation (nombre clients, transactions)
- [ ] Nettoyer les logs (> 30 jours)

## 🎯 Prochaines Étapes (Niveau 2)

- [ ] Ajouter API REST endpoints
- [ ] Générer PDF factures
- [ ] Intégrer notifications email
- [ ] Ajouter multi-utilisateurs
- [ ] Créer tableau de bord analytics

## ✅ Validation Finale

Cochez cette case quand TOUT est OK :

- [ ] **✅ APPLICATION PRÊTE POUR PRODUCTION**

Date : ________________
Validé par : ________________

---

**Note :** Gardez cette checklist pour chaque déploiement futur.
