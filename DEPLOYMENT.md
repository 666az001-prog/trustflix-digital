# TrustFlix Digital V2 - Guide de Déploiement InfinityFree

## 📋 Prérequis

- Compte InfinityFree (gratuit)
- Accès à phpMyAdmin
- Client FTP ou File Manager
- Éditeur de texte

## 🚀 Installation Étape par Étape

### 1. Créer la Base de Données

1. Connectez-vous à votre compte InfinityFree
2. Allez dans **phpMyAdmin** 
3. Créez une nouvelle base de données (ex: `trustflix_v2`)
4. Ouvrez le fichier `database/schema.sql`
5. Copiez tout le contenu
6. Dans phpMyAdmin, allez à l'onglet **SQL**
7. Collez le script complet
8. Cliquez sur **Exécuter**

**Résultat attendu :**
- 4 tables créées : `users`, `clients`, `payments`, `logs`
- Admin initial créé : `admin` / `admin123`

### 2. Configurer les Variables d'Environnement

Si vous avez un accès au fichier `.env` (rare sur InfinityFree), créez/modifiez le fichier :

```
DB_HOST=localhost
DB_NAME=votre_base_de_donnees
DB_USER=votre_utilisateur_bdd
DB_PASS=votre_mot_de_passe_bdd
```

**Sinon**, modifiez directement dans `config/database.php` lignes 7-10 :

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'votre_base_de_donnees');
define('DB_USER', 'votre_utilisateur');
define('DB_PASS', 'votre_mot_de_passe');
```

### 3. Uploader les Fichiers via FTP

1. Connectez-vous à votre espace InfinityFree via FTP
2. Uploadez TOUS les fichiers et dossiers du projet dans le répertoire racine (`public_html/`)

Structure finale :
```
public_html/
├── index.php
├── login.php
├── logout.php
├── config/
│   └── database.php
├── dashboard/
│   └── index.php
├── clients/
│   ├── list.php
│   ├── add.php
│   ├── edit.php
│   └── delete.php
├── payments/
│   └── add.php
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── app.js
├── database/
│   └── schema.sql
└── logs/
    └── README.md
```

### 4. Vérifier les Permissions

Assurez-vous que le dossier `logs/` a les permissions **755** pour permettre l'écriture des fichiers journaux.

## 🔐 Sécurité

- ✅ PDO préparé contre les injections SQL
- ✅ Hash bcrypt pour les mots de passe (coût=10)
- ✅ Token CSRF obligatoire sur tous les formulaires
- ✅ Sessions sécurisées avec timeout 3600s
- ✅ Vérification d'authentification sur toutes les pages protégées
- ✅ Logs centralisés des erreurs et actions

## 🔑 Identifiants par Défaut

**Admin :**
- Identifiant : `admin`
- Mot de passe : `admin123`

⚠️ **À faire après le premier accès :**
1. Changez le mot de passe admin (table `users` dans phpMyAdmin)
2. Hashez le nouveau mot de passe avec bcrypt

Pour générer un hash bcrypt en PHP :
```php
echo password_hash('votre_nouveau_mot_de_passe', PASSWORD_BCRYPT, ['cost' => 10]);
```

## 📱 Fonctionnalités

### Dashboard
- Statistiques globales en temps réel
- Clients actifs, en alerte, expirés
- Revenus vs dépenses
- Derniers clients et transactions

### Gestion Clients
- CRUD complet (Créer, Lire, Mettre à jour, Supprimer)
- Calcul automatique du statut (Actif 🟢 / Alerte 🟠 / Expiré 🔴)
- Bouton WhatsApp intégré pour relance automatique
- Création automatique du premier paiement lors de l'ajout du client

### Gestion Paiements
- Entrée/Sortie d'argent
- Suivi détaillé des transactions
- Historique complet

### Sécurité
- Authentification obligatoire
- Protection CSRF
- Logs des erreurs
- Gestion des timeouts

## 🎨 Design

- **Style :** Sombre cinéma premium
- **Responsive :** 100% mobile-first
- **Couleurs :** Dégradé Amber→Orange avec fond noir
- **Navigation :** Barre latérale PC + Menu hamburger mobile

## 🧪 Tests Rapides

1. **Accédez à l'application :**
   - URL : `https://votredomaine.infinityfree.app/`
   - Vous êtes redirigé automatiquement vers `/login.php`

2. **Connectez-vous :**
   - Identifiant : `admin`
   - Mot de passe : `admin123`

3. **Testez les fonctionnalités :**
   - Créez un client
   - Vérifiez que le paiement initial est créé
   - Testez le bouton WhatsApp
   - Ajoutez un paiement manuel
   - Vérifiez les statuts

## 🐛 Dépannage

### Erreur de connexion BDD
- Vérifiez les identifiants dans `config/database.php`
- Vérifiez que la base de données existe
- Vérifiez que l'utilisateur MySQL a les permissions

### Erreur "Token CSRF invalide"
- Actualisez la page
- Videz les cookies du navigateur
- Relancez la session

### Bouton WhatsApp ne fonctionne pas
- Vérifiez le format du numéro (ex: `0750123456`)
- Testez manuellement : `https://wa.me/225750123456`

### Erreur 500 générale
- Vérifiez les logs dans phpMyAdmin (table `logs`)
- Vérifiez que PHP 8.0+ est installé
- Vérifiez les permissions des dossiers

## 📝 Logs

Toutes les erreurs et actions importantes sont enregistrées dans la table `logs` :
- Erreurs de connexion
- Tentatives de connexion échouées
- Actions utilisateur (création/modification/suppression)
- Erreurs système

Pour consulter : PhpMyAdmin → Table `logs`

## 🔄 Mises à Jour

Pour mettre à jour l'application :
1. Téléchargez les nouveaux fichiers
2. Uploadez-les via FTP (remplacez les anciens)
3. La base de données reste intacte

## 📞 Support

En cas de problème :
1. Consultez les logs (table `logs`)
2. Vérifiez la configuration dans `config/database.php`
3. Testez avec un navigateur moderne (Chrome, Firefox, Safari, Edge)

---

**Version :** 1.0.0  
**PHP :** 8.0+  
**MySQL :** 5.7+  
**Framework :** Native (aucun framework)
