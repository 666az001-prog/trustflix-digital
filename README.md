# TrustFlix Digital

SaaS de gestion des reventes d’abonnements streaming, construit avec Next.js 14, TypeScript, Tailwind CSS, Prisma et PostgreSQL/Supabase.

## Démarrage

1. Copiez `.env.example` en `.env` et définissez une URL PostgreSQL Supabase, une clé AES-256-GCM et un `CRON_SECRET`. Pour générer la clé : `node -e "console.log(require('crypto').randomBytes(32).toString('base64'))"`.
2. Installez les dépendances avec `npm install`.
3. Créez les tables : `npm run db:push`.
4. Ajoutez Netflix, Spotify, Apple Music et des comptes de démonstration : `npm run db:seed`.
5. Lancez `npm run dev`.

## Déploiement Vercel

Ajoutez ces variables dans Vercel, pour les environnements Preview et Production :

```text
DATABASE_URL=postgresql://postgres:[YOUR-PASSWORD]@db.qrugbbynubipoacvewdo.supabase.co:5432/postgres?sslmode=require&schema=public
NEXT_PUBLIC_SUPABASE_URL=https://qrugbbynubipoacvewdo.supabase.co
NEXT_PUBLIC_SUPABASE_ANON_KEY=sb_publishable_YcjFzcsfoGaboo4nZvQOrQ__zHBsI2y
ACCOUNT_CREDENTIALS_KEY=[GENERATE_A_32_BYTE_BASE64_KEY]
CRON_SECRET=[GENERATE_A_RANDOM_SECRET]
```

Remplacez `[YOUR-PASSWORD]` par le mot de passe PostgreSQL Supabase, puis redéployez le dernier commit. Créez les tables dans Supabase avec `npx prisma db push` depuis une machine qui possède cette `DATABASE_URL`, puis lancez `npm run db:seed`. Le cron Vercel quotidien libère automatiquement les slots dont les clients ont expiré.

## Sécurité

Les identifiants des comptes et PIN ne sont jamais stockés en clair. La clé de chiffrement doit être gardée exclusivement dans les variables d’environnement. N’exposez jamais `DATABASE_URL` ni la clé de chiffrement au navigateur.
