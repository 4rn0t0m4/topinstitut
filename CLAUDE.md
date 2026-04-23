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

# Import données géographiques (geo.api.gouv.fr)
php artisan geo:import         # Import départements + villes (~35k communes)
php artisan geo:import --departments-only
php artisan geo:import --cities-only

# Tests
php artisan test               # Tous les tests

# Cache
php artisan cache:clear && php artisan view:clear && php artisan route:clear

# Code formatting
./vendor/bin/pint
```

## Architecture

**Stack** : Laravel 11 / PHP 8.2+ / MySQL / Tailwind CSS v4 / Alpine.js / Vite

**Auth** : Custom `AuthController` (pas Breeze/Fortify). Session-based. Admin via `is_admin` boolean sur User + `AdminMiddleware`.

### Naming convention

**Tables et colonnes en anglais.** URLs et texte UI en français pour le SEO.

| Table | Model | Anciennes (FR) |
|-------|-------|----------------|
| departments | Department | departements |
| cities | City | villes |
| establishments | Establishment | etablissements |
| establishment_slugs | EstablishmentSlug | — |
| categories | Category | — |
| category_establishment | — | categorie_etablissement |
| photos | Photo | — |
| reviews | Review | avis |
| review_votes | ReviewVote | avis_utiles |
| news | News | actualites |
| messages | Message | — |
| schedules | Schedule | horaires |
| establishment_user | — | etablissement_user |
| user_tiers | UserTier | — |
| claims | Claim | revendications |

### Colonnes clés

| EN | FR (ancien) | Table |
|----|-------------|-------|
| name | titre | establishments |
| is_active | valide | establishments |
| rating | moyenne | establishments |
| review_count | nb_avis | establishments |
| tagline | accroche | establishments |
| pricing | tarifs | establishments |
| city_rank | classement_ville | establishments |
| title / content | titre / contenu | reviews, news |
| is_approved / is_rejected | valide / refus | reviews |
| rating_welcome/quality/variety/price/ambiance/cleanliness | note_accueil/qualite/choix/prix/cadre/proprete | reviews |
| reply / replied_at | reponse / reponse_date | reviews |
| username | pseudo | users |
| last_name / first_name | nom / prenom | users |
| day_of_week | jour | schedules |
| open_am / close_am / open_pm / close_pm | matin_ouverture/fermeture, aprem_* | schedules |
| is_closed | ferme | schedules |
| sort_order | ordre | photos |
| manager_name | nom_gerant | claims |

### Groupes de routes

| Groupe | Fichier | Préfixe | Middleware |
|--------|---------|---------|------------|
| Web | `routes/web.php` | `/` | `web` |
| Admin | `routes/admin.php` | `/admin` | `web, auth, admin` |
| Client | `routes/client.php` | `/espace-client` | `web, auth` |

### Répertoires clés

- `app/Models/` — User, Establishment, Review, City, Department, Category, Photo, Schedule, News, Message, EstablishmentSlug, ReviewVote, UserTier, Claim
- `app/Services/` — GeoSearchService, RatingService, SlugService
- `app/Console/Commands/ImportGeoData.php` — Import geo.api.gouv.fr

### Types d'établissement

```
0 = Institut de beauté    → institut-de-beaute
1 = Esthéticienne         → estheticienne-a-domicile
2 = Spa                   → spa
3 = Thalasso              → thalasso
```

### URL hiérarchiques (SEO longue traîne)

```
/{dept}                                   Page département (ex: /calvados)
/{dept}/{ville}                           Page ville (ex: /calvados/caen)
/{dept}/{ville}/{prestation}              Prestation × ville (ex: /calvados/caen/spa, /calvados/caen/epilation)
/{dept}/{ville}/{type}/{slug}             Fiche établissement (ex: /calvados/caen/spa/kokomo-beauty)
```

- `{prestation}` : soit un slug de type (`spa`, `institut-de-beaute`, `estheticienne-a-domicile`, `thalasso`) soit un slug de catégorie (`epilation`, `massage-bien-etre`, etc.)
- `{type}` : uniquement un slug de type (les 4 valeurs ci-dessus) — contraint par regex sur la route `etablissement.show`
- Slug établissement unique par `(city_id, type)` pour permettre des doublons entre villes/types
- Anciennes URLs `/institut-de-beaute/{slug}` etc. redirigent 301 vers la nouvelle URL hiérarchique
- Routes définies à la fin de `routes/web.php` (catch-all placé après les routes spécifiques)

### Commandes de maintenance

```bash
php artisan slugs:regenerate [--dry-run]   # Régénère slugs courts + sauvegarde anciens pour 301
php artisan seed:category-establishment    # Mapping catégories selon le type
```

### Modèles clés

**Establishment** : scope `active()`, scope `nearby()`, accesseurs `type_label`, `type_slug`, `url`, `opening_status`, `next_opening`.

**Review** : 6 ratings (welcome, quality, variety, price, ambiance, cleanliness). Accesseur `average_rating`. Scope `approved()`. Soumission sans compte possible (author_name + author_email + verification_token).

**User** : Cast `password => hashed`. Relation `establishments()` belongsToMany.

**City** : Peuplée via `php artisan geo:import`. Champ `insee_code` unique.

### SEO

- Canonical URLs, Open Graph, Twitter Cards sur toutes les pages
- Schema.org : LocalBusiness + AggregateRating + Review + OpeningHoursSpecification + BreadcrumbList + WebSite+SearchAction
- `noindex` sur pages admin, client, auth
- Sitemap XML, robots.txt configuré
- Table `establishment_slugs` pour redirections 301

## Conventions

- Texte UI en **français**, code en anglais
- Routes avec slugs français : `/connexion`, `/inscription`, `/espace-client`
- URLs SEO sans suffixe (redirects 301 depuis les anciennes URLs `.html`)
- Layouts : `<x-layouts.app>` (frontend), `@extends('admin.layouts.app')` (admin)
- `is_active` filtre les établissements publics, `is_approved` filtre les avis
