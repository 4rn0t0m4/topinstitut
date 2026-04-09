# TODO — TopInstitut Laravel

## Sprint 1 — Fondations (FAIT)
- [x] Scaffolding Laravel 11, Tailwind v4, Alpine.js, Vite
- [x] 13 migrations (15+ tables)
- [x] 14 models Eloquent
- [x] Auth custom (connexion, inscription, mot de passe oublié, vérification email)
- [x] AdminMiddleware
- [x] 3 services (GeoSearch, Rating, Slug)
- [x] 71 routes (web.php, admin.php, client.php)
- [x] 18 controllers (8 publics, 4 admin, 6 client)
- [x] 35+ vues Blade (3 layouts, 2 composants)
- [x] CLAUDE.md

---

## Sprint 2 — Créer la base MySQL et tester (FAIT)

- [x] Créer la base `topinstitut_laravel` en local
- [x] `php artisan migrate` — 13 migrations passées sans erreur
- [x] `php artisan storage:link` — symlink créé
- [x] `php artisan serve` + `npm run build` — homepage s'affiche (200)
- [x] Créer un user admin via tinker : `admin@topinstitut.fr / admin1234`
- [x] Tester le flow complet : inscription, connexion, ajout établissement, soumission avis, modération admin

### Bugs corrigés pendant le sprint 2

- **Route `verification.verify`** : format `{id}_{vkey}.html` incompatible avec les URLs signées de Laravel → changé en `{id}/{hash}` ([routes/web.php](routes/web.php))
- **`Etablissement::$fillable`** : `moyenne` et `nb_avis` manquants → le `RatingService::recalculate()` échouait silencieusement ([app/Models/Etablissement.php](app/Models/Etablissement.php))

---

## Sprint 3 — Migration des données legacy (FAIT)

### Commandes artisan créées (`app/Console/Commands/Migrate/`)

- [x] `MigrateDepartements` — 102 départements (101 + Outre-Mer)
- [x] `MigrateVilles` — 38 073 villes (doublons URL gérés)
- [x] `MigrateUsers` — 3 567 utilisateurs (26 ignorés : email invalide/doublon)
- [x] `MigrateCategories` — 102 catégories (92 avec parent)
- [x] `MigrateEtablissements` — 22 272 établissements + 588 slugs + 11 963 catégories + 1 409 admins
- [x] `MigratePhotos` — 1 298 photos DB + 1 219 fichiers copiés
- [x] `MigrateAvis` — 1 659 avis + 642 votes utile/inutile
- [x] `MigrateActualites` — 161 actualités
- [x] `MigrateHoraires` — 2 666 horaires (806 ignorés : données invalides)
- [x] `RecalculateRatings` — 22 272 établissements recalculés

### Compatibilité mots de passe MD5

- [x] `app/Auth/LegacyUserProvider.php` — tente bcrypt puis MD5, re-hash en bcrypt si MD5 match
- [x] Provider `legacy` enregistré dans `config/auth.php` + `AppServiceProvider`

### Problèmes résolus pendant la migration

- **Dept 97 (Outre-Mer)** manquant dans le legacy → ajouté automatiquement
- **URLs villes dupliquées** → suffixe `-{id}` pour garantir l'unicité
- **SIRET** avec caractères spéciaux (nbsp) ou > 14 chars → nettoyé (chiffres uniquement, tronqué)
- **Horaires invalides** (`9h90`, etc.) → filtrés avec validation min/max
- **Pseudos dupliqués** après conversion encoding → boucle `while` avec suffixe `_id`
- **ville_id FK** invalides → vérification d'existence avant insertion
- **Téléphone/portable** > 20 chars → tronqués

---

## Sprint 4 — SEO et redirections

- [ ] Vérifier que toutes les anciennes URLs `.html` fonctionnent (200 ou 301)
- [ ] Redirect 301 via `etablissement_slugs` quand un slug a changé
- [ ] Redirect 301 si le type dans l'URL ne correspond pas au type de l'établissement
- [ ] Sitemap XML via `spatie/laravel-sitemap` (`SitemapController`)
- [ ] Meta title/description dynamiques par page
- [ ] Schema.org `LocalBusiness` + `AggregateRating` sur les fiches établissement
- [ ] Schema.org `BreadcrumbList` sur toutes les pages
- [ ] Schema.org `WebSite` + `SearchAction` sur la homepage
- [ ] Page 404 custom avec suggestions d'établissements à proximité
- [ ] `robots.txt` configuré

---

## Sprint 5 — Polish et performances (FAIT)

- [x] Eager loading vérifié partout (aucun N+1 détecté)
- [x] Cache départements homepage (1h)
- [x] Pagination ville (20/page), recherche (20/page déjà en place)
- [x] Validation côté client : étoiles requises avant soumission (Alpine.js)
- [x] Rate limiting : avis/contact (5/min), login (10/min), register (5/min), reset password (3/min)
- [x] CSRF vérifié sur tous les formulaires POST (26/26)
- [x] 22 tests Feature : routes publiques, 301 redirects, 404, sitemap, auth, avis
- [x] `./vendor/bin/pint` — 30 fichiers formatés

---

## Plus tard (V2)

- [ ] Paiement premium (Stripe plutôt que PayPal)
- [ ] Connexion Google/Facebook (Socialite)
- [ ] Géolocalisation navigateur (proximité automatique)
- [ ] Carte Google Maps sur les fiches et pages ville
- [ ] Notifications email (nouveau avis, établissement validé)
- [ ] Export/import données admin
- [ ] Analytics intégrées (visites par établissement)
