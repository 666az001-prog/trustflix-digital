# 🚀 Démarrage Rapide - XAMPP Local

## 1️⃣ Préparation

Assurez-vous que **XAMPP est démarré** :
- ✅ Apache (port 80)
- ✅ MySQL (port 3306)

## 2️⃣ Créer la Base de Données

```bash
# Option A : PhpMyAdmin (GUI)
1. Ouvrez http://localhost/phpmyadmin
2. Créez une base de données : trustflix_v2
3. Allez à l'onglet SQL
4. Copiez le contenu de database/schema.sql
5. Exécutez
```

```bash
# Option B : Ligne de commande (MySQL)
mysql -u root < database/schema.sql
# Ou avec password:
mysql -u root -p < database/schema.sql
```

## 3️⃣ Vérifier la Configuration

Ouvrez votre navigateur :

```
http://localhost/trustflix/health-check.php
```

✅ Si tout est vert, vous pouvez continuer.
❌ Si il y a une erreur, consultez le message et ajustez `config/database.php`

## 4️⃣ Lancer l'Application

```
http://localhost/trustflix/
```

Vous êtes redirigé vers `/login.php`

## 5️⃣ Se Connecter

```
👤 Identifiant : admin
🔐 Mot de passe : admin123
```

## 6️⃣ Premières Actions

### Créer un Client Test
```
✨ Cliquez sur : Clients → Ajouter un client
- Nom : John Doe
- WhatsApp : 0750123456 (ou +2250750123456)
- Plan : Premium
- Expiration : +15 jours
- Prix : 15000
✅ Créer le client
```

### Observer le Paiement Auto
```
📊 Tableau de bord → Dernières transactions
✅ Vous verrez la ligne de paiement créée automatiquement
```

### Tester WhatsApp
```
👥 Clients → Cliquez sur le 💬 WhatsApp
✅ Un lien wa.me s'ouvre (testé en local)
✅ En production, envoie le message au client
```

### Ajouter un Paiement Manuel
```
💰 Paiements → Ajouter un paiement
- Client : John Doe
- Type : Entrée (revenu)
- Montant : 15000
- Description : Paiement mensuel
- Date : Aujourd'hui
✅ Enregistrer
```

## 📁 Structure des Fichiers

```
trustflix/
├── config/database.php              ← Éditer ici les identifiants MySQL
├── database/
│   ├── schema.sql                   ← Importer ici dans PhpMyAdmin
│   └── maintenance.sql              ← Utilitaires SQL
├── login.php                        ← Point d'entrée
├── dashboard/index.php              ← Vue principale
├── clients/
│   ├── list.php                     ← Voir tous les clients
│   ├── add.php                      ← Créer un client
│   ├── edit.php                     ← Modifier un client
│   └── delete.php                   ← Supprimer un client
└── payments/add.php                 ← Enregistrer un paiement
```

## ⚙️ Configuration MySQL

Si PhpMyAdmin ou MySQL ne fonctionne pas :

1. **Vérifiez que MySQL est démarré** (panneau de contrôle XAMPP)
2. **Vérifiez les identifiants par défaut** :
   - Utilisateur : `root`
   - Mot de passe : (vide)
   - Host : `localhost`
3. **Si vos identifiants sont différents** :
   - Éditez `config/database.php` lignes 7-10
   - Mettez vos vrais identifiants

## 🔧 Troubleshooting

### "Erreur de connexion à la base de données"
```
→ Vérifiez MySQL est démarré
→ Vérifiez les identifiants dans config/database.php
→ Vérifiez que la base de données "trustflix_v2" existe
```

### "Table not found"
```
→ Réexécutez schema.sql dans PhpMyAdmin
→ Ou : mysql -u root < database/schema.sql
```

### "Connection timeout"
```
→ Le serveur MySQL s'est arrêté
→ Redémarrez-le depuis le panneau XAMPP
```

### "Erreur CSRF"
```
→ Actualiser la page
→ Videz les cookies du navigateur
```

## 📊 Afficher les Logs

Les erreurs sont stockées dans la table `logs` :

```sql
-- PhpMyAdmin SQL
SELECT * FROM logs ORDER BY created_at DESC LIMIT 20;
```

Vous verrez :
- Les tentatives de connexion échouées
- Les erreurs d'accès à la base de données
- Les actions créées/modifiées/supprimées

## ✅ Checklist de Vérification

- [ ] XAMPP Apache démarré
- [ ] XAMPP MySQL démarré
- [ ] Base de données `trustflix_v2` créée
- [ ] Fichiers du projet dans `c:/xampp/htdocs/trustflix/`
- [ ] `config/database.php` configuré avec les bonnes identifiants
- [ ] http://localhost/trustflix/health-check.php affiche tout vert
- [ ] Connexion avec admin / admin123 fonctionne
- [ ] Tableau de bord affiche des statistiques

## 🎬 Démarrage du Serveur (optionnel)

Si vous voulez lancer XAMPP depuis le terminal :

```bash
# Windows
"C:\xampp\apache_start.bat"
"C:\xampp\mysql_start.bat"

# Ou utiliser le contrôle XAMPP GUI
```

## 📝 Notes

- ✅ L'application n'a **aucune dépendance externe** (sauf CDN)
- ✅ Le code est **100% fonctionnel et testable localement**
- ✅ Vous pouvez **exporter la base de données** à tout moment
- ✅ Tous les fichiers sont **prêts pour InfinityFree**

## 🚀 Prêt pour la Production ?

Consultez `DEPLOYMENT.md` pour les instructions InfinityFree compètes.

---

**Bon développement ! 🎉**
