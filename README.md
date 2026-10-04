# TrustFlix Digital

SaaS de gestion des reventes d’abonnements streaming, construit avec Next.js 14, TypeScript, Tailwind CSS, Prisma et PostgreSQL/Supabase.

## Démarrage

1. Copiez `.env.example` en `.env`, remplacez les deux URL par les chaînes de connexion Supabase indiquées ci-dessous, puis définissez une clé AES-256-GCM et un `CRON_SECRET`. Pour générer la clé : `node -e "console.log(require('crypto').randomBytes(32).toString('base64'))"`.
2. Installez les dépendances avec `npm install`.
3. Créez les tables : `npm run db:push`.
4. Ajoutez Netflix, Spotify, Apple Music et des comptes de démonstration : `npm run db:seed`.
5. Lancez `npm run dev`.

## Déploiement Vercel

Dans Supabase, ouvrez **Connect** et copiez l’URI **Transaction pooler** (port `6543`) dans `DATABASE_URL`, puis l’URI **Session pooler** (port `5432`) dans `DIRECT_URL`. Utilisez exactement l’hôte et l’utilisateur fournis par Supabase, car ils dépendent du projet. Le pooler partagé accepte IPv4 ; l’endpoint direct `db.<project-ref>.supabase.co` est IPv6 uniquement sans option IPv4. Encodez les caractères spéciaux du mot de passe dans les URI.

Ajoutez ces variables dans Vercel, pour les environnements Preview et Production :

```text
DATABASE_URL=postgresql://postgres.[PROJECT-REF]:[YOUR-PASSWORD]@[POOLER-HOST]:6543/postgres?sslmode=require&pgbouncer=true&connection_limit=1&schema=public
DIRECT_URL=postgresql://postgres.[PROJECT-REF]:[YOUR-PASSWORD]@[POOLER-HOST]:5432/postgres?sslmode=require&schema=public
NEXT_PUBLIC_SUPABASE_URL=https://scrzyrqaapxhzbyefszc.supabase.co
NEXT_PUBLIC_SUPABASE_ANON_KEY=[COPY_THE_NEW_PROJECT_PUBLISHABLE_KEY]
ACCOUNT_CREDENTIALS_KEY=[GENERATE_A_32_BYTE_BASE64_KEY]
CRON_SECRET=[GENERATE_A_RANDOM_SECRET]
```

Remplacez les valeurs d’exemple par les URI copiées dans Supabase, puis redéployez. `npx prisma db push` utilise `DIRECT_URL`; lancez-le depuis une machine pouvant joindre le pooler, puis exécutez `npm run db:seed`. Le cron Vercel quotidien libère automatiquement les slots dont les clients ont expiré.

## Sécurité

Les identifiants des comptes et PIN ne sont jamais stockés en clair. La clé de chiffrement doit être gardée exclusivement dans les variables d’environnement. N’exposez jamais `DATABASE_URL` ni la clé de chiffrement au navigateur.
