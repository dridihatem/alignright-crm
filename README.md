# AlignRight CRM

CRM dentaire basé sur **Laravel 11** pour la gestion des cas orthodontiques entre plusieurs rôles : **Admin**, **Médecin (Doctor)**, **Technicien** et **Laboratoire**.

## Fonctionnalités

- Gestion des cas patients (création, suivi de statut, modification)
- Plans de traitement (3D / IPR) et workflow de **finition**
- Téléversement de fichiers (STL, photos cliniques, radiographies) avec intégration Google Drive
- Messagerie / chat par cas et notifications en temps quasi réel
- Facturation et gestion des prix
- Multilingue (Français par défaut, Anglais)
- Tableaux de bord par rôle, recherche globale par cas/patient

## Stack technique

- PHP `^8.2`, Laravel `^11.31`
- MySQL
- Vite + Tailwind CSS, Bootstrap (thème admin)
- DomPDF (factures), Intervention Image, Hashids, Laravel Socialite (Google), Yajra DataTables

## Prérequis

- PHP 8.2+
- Composer
- Node.js 18+ et npm
- MySQL 5.7+ / 8+

## Installation (local)

```bash
# 1. Cloner le dépôt
git clone https://github.com/dridihatem/alignright-crm.git
cd alignright-crm

# 2. Dépendances PHP et JS
composer install
npm install

# 3. Configuration de l'environnement
cp .env.example .env
php artisan key:generate

# 4. Renseigner .env (base de données, mail, Google OAuth, HASHIDS_SALT...)

# 5. Base de données
php artisan migrate --seed

# 6. Lien de stockage public
php artisan storage:link

# 7. Build des assets
npm run dev      # développement
# ou
npm run build    # production

# 8. Lancer le serveur de développement
php artisan serve
```

## Variables d'environnement importantes

| Clé | Description |
| --- | --- |
| `APP_KEY` | Générée via `php artisan key:generate` |
| `DB_*` | Connexion MySQL |
| `MAIL_*` | Configuration SMTP pour les e-mails |
| `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` / `GOOGLE_REDIRECT_URI` | OAuth & Google Drive |
| `HASHIDS_SALT` | Chaîne aléatoire longue pour l'obfuscation des IDs |
| `UPLOAD_MAX_FILE_SIZE` | Taille max des téléversements |

> ⚠️ Ne jamais committer le fichier `.env`, les sauvegardes SQL (`storage/backups`) ni aucun secret.

## Déploiement (production)

```bash
git pull origin main

composer install --no-dev --optimize-autoloader
npm install && npm run build

php artisan migrate --force
php artisan storage:link

# Optimisations
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Pensez à définir `APP_ENV=production` et `APP_DEBUG=false` dans le `.env` du serveur.

## Tests

```bash
php artisan test
```

## Licence

Propriétaire — tous droits réservés.
