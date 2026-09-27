# TrustFlix Digital

SaaS de gestion des reventes d’abonnements streaming, construit avec Next.js 14, TypeScript, Tailwind CSS, Prisma et PostgreSQL/Supabase.

## Démarrage

1. Copiez `.env.example` en `.env` et définissez une URL PostgreSQL Supabase, une clé AES-256-GCM et un `CRON_SECRET`. Pour générer la clé : `node -e "console.log(require('crypto').randomBytes(32).toString('base64'))"`.
2. Installez les dépendances avec `npm install`.
3. Créez les tables : `npm run db:push`.
4. Ajoutez Netflix, Spotify, Apple Music et des comptes de démonstration : `npm run db:seed`.
5. Lancez `npm run dev`.

## Déploiement Vercel

Ajoutez `DATABASE_URL`, `ACCOUNT_CREDENTIALS_KEY` et `CRON_SECRET` dans les variables d’environnement Vercel, puis déployez le dépôt. Le cron Vercel quotidien libère automatiquement les slots dont les clients ont expiré. Le script de build génère automatiquement le client Prisma.

## Sécurité

Les identifiants des comptes et PIN ne sont jamais stockés en clair. La clé de chiffrement doit être gardée exclusivement dans les variables d’environnement. N’exposez jamais `DATABASE_URL` ni la clé de chiffrement au navigateur.
