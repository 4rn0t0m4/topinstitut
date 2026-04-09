# CLAUDE.md

Ce fichier fournit des instructions à Claude Code pour travailler sur ce projet.

## Commandes

```bash
# Serveur dev
php artisan serve              # HTTP sur :8000
npm run dev                    # Vite HMR pour les assets

# Build
npm run build                  # Assets production

# Base de données
php artisan migrate            # Lancer les migrations
php artisan migrate:fresh      # Reset + re-migrate
php artisan tinker             # REPL interactif

# Tests
php artisan test               # Tous les tests

# Cache
php artisan cache:clear && php artisan view:clear && php artisan route:clear

# Code formatting
./vendor/bin/pint

# Migration données legacy (dans l'ordre)
php artisan migrate:legacy-departements
php artisan migrate:legacy-villes
php artisan migrate:legacy-users
php artisan migrate:legacy-categories
php artisan migrate:legacy-etablissements   # inclut slugs, catégories, admins
php artisan migrate:legacy-photos
php artisan migrate:legacy-avis             # inclut avis_utile
php artisan migrate:legacy-actualites
php artisan migrate:legacy-horaires
php artisan migrate:recalculate-ratings
```

## Architecture

**Stack** : Laravel 11 / PHP 8.2+ / MySQL / Tailwind CSS v4 / Alpine.js / Vite

**Auth** : Custom `AuthController` (pas Breeze/Fortify). Session-based. Admin via `is_admin` boolean sur User + `AdminMiddleware`. `LegacyUserProvider` gère la compatibilité MD5 (tente bcrypt puis MD5, re-hash en bcrypt automatiquement).

### Groupes de routes

| Groupe | Fichier | Préfixe | Middleware | Usage |
|--------|---------|---------|------------|-------|
| Web | `routes/web.php` | `/` | `web` | Pages publiques, auth |
| Admin | `routes/admin.php` | `/admin` | `web, auth, admin` | Back-office CRUD |
| Client | `routes/client.php` | `/espace-client` | `web, auth` | Espace propriétaire |

### Répertoires clés

- `app/Http/Controllers/` — Controllers publics (Home, Etablissement, Departement, Ville, Recherche, Avis, Contact)
- `app/Http/Controllers/Admin/` — Controllers admin (Dashboard, Etablissement, Avis, Categorie)
- `app/Http/Controllers/Client/` — Controllers espace client (Dashboard, Profil, Etablissement, Photo, Actualite, Avis)
- `app/Models/` — Eloquent models : User, Etablissement, Avis, Ville, Departement, Categorie, Photo, Horaire, Actualite, Message, EtablissementSlug, AvisUtile, UserTier
- `app/Services/` — GeoSearchService (Haversine), RatingService, SlugService
- `app/Http/Middleware/AdminMiddleware.php` — Vérifie `$user->is_admin`

### Vite entry points

```
resources/css/app.css    → Frontend styles
resources/js/app.js      → Frontend JS (Alpine)
resources/css/admin.css  → Admin styles
resources/js/admin.js    → Admin JS (Alpine)
```

### Connexions DB

- **mysql** (défaut) : `topinstitut_laravel`
- **legacy** : `einstitutdb` (charset `latin1` pour migration données)

### Types d'établissement

```
0 = Institut de beauté    → /institut-de-beaute/{slug}.html
1 = Esthéticienne à domicile → /estheticienne-a-domicile/{slug}.html
2 = Spa                   → /spa/{slug}.html
3 = Thalasso              → /thalasso/{slug}.html
```

### Modèles clés

**Etablissement** : `TYPE_SLUGS` et `TYPE_LABELS` constantes, scope `valide()`, scope `nearby($lat, $lng, $km)` (Haversine SQL), accesseurs `type_label`, `type_slug`, `url`.

**Avis** : 6 critères de notation (accueil, qualite, choix, prix, cadre, proprete) de 1 à 5. Accesseur `moyenne`. Scope `approved()`. Les notes `avis` + `avis_note` du legacy sont fusionnées dans une seule table.

**User** : Cast `password => hashed` (ne jamais utiliser `bcrypt()` manuellement). Relation `etablissements()` belongsToMany via pivot `etablissement_user`.

### Services

**GeoSearchService** : Recherche par proximité avec formule Haversine en SQL brut.

**RatingService** : Recalcule `moyenne` et `nb_avis` d'un établissement à partir de ses avis approuvés.

**SlugService** : Génère des slugs URL-safe depuis du texte français (accents, caractères spéciaux).

## Conventions

- Tout le texte UI est en **français**
- Routes avec slugs français : `/connexion`, `/inscription`, `/espace-client`, `/recherche_institut.html`
- URLs SEO avec suffixe `.html` pour compatibilité legacy
- Table `etablissement_slugs` pour 301 redirects d'anciens slugs
- Prix affichés avec `number_format($prix, 2, ',', ' ') €`
- Layouts frontend : `<x-layouts.app>` (composant), admin : `@extends('admin.layouts.app')` avec `@section('content')`
- Composants Blade réutilisables : `<x-star-rating>`, `<x-etablissement-card>`
- Les établissements ont un flag `valide` — seuls les validés sont visibles publiquement
- Les avis nécessitent modération admin (valide=false par défaut)
- Vérification propriété établissement dans les controllers client via pivot `etablissement_user`

## Origine

Refonte d'un site PHP legacy (`/Users/arnaud/Sites/TopInstitut`) — annuaire d'instituts de beauté avec avis, géolocalisation, et modération. Le legacy utilise des fonctions `mysql_*`, MD5 pour les mots de passe, encodage windows-1252.
