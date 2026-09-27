# TrustFlix Digital — version PHP (XAMPP)

Application de gestion des reventes Netflix (comptes mères, profils, clients, trésorerie).

## Installation rapide

1. Copiez le dossier dans `C:\xampp\htdocs\trustflix`.
2. Créez la base MySQL `trustflix_db` dans phpMyAdmin (ou laissez l’install le faire si les droits le permettent).
3. Ouvrez `http://localhost/trustflix/install.php` une fois.
4. Connectez-vous : **admin** / **admin123**.
5. Supprimez ou renommez `install.php` en production.

Configuration PDO : `config/database.php`.

## Fonctionnalités

- **Dashboard** : entrées, sorties, bénéfice net, alertes J-7 avec WhatsApp
- **Clients** : recherche instantanée (`.client-row`), badges d’échéance, relance WhatsApp (+225)
- **Comptes** : recherche instantanée (`.account-row`), profils libres/occupés
- **Trésorerie** : enregistrement des dépenses (`expenses`)
- **Expiration auto** : à chaque connexion + script `cron/expire.php`

## Cron (optionnel)

Planifiez l’exécution quotidienne :

```bat
php C:\xampp\htdocs\trustflix\cron\expire.php
```

## Dossier `trustflix2/`

Variante avec édition inline des profils (`accounts/update_profile.php`). Même stack et mêmes helpers que la racine.
