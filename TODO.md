# TODO — TopInstitut Laravel

## Sprints 1-3 (FAIT)

- [x] Scaffolding Laravel 11, Tailwind v4, Alpine.js, Vite
- [x] 13 migrations (15+ tables)
- [x] 14 models Eloquent + services (GeoSearch, Rating, Slug)
- [x] Auth custom + AdminMiddleware + LegacyUserProvider
- [x] 71+ routes, 18+ controllers, 35+ vues Blade
- [x] Migration données legacy (22k établissements, 3.5k users, 1.6k avis)
- [x] Tests Feature (22 tests), rate limiting, Pint

## Sprint 5 — Polish (FAIT)

- [x] Eager loading, cache départements, pagination
- [x] Validation côté client (Alpine.js)
- [x] Rate limiting, CSRF vérifié
- [x] `./vendor/bin/pint`

---

## Sprint 4 — SEO et redirections (À FAIRE)

- [ ] Vérifier que toutes les anciennes URLs `.html` fonctionnent (200 ou 301)
- [ ] Redirect 301 via `etablissement_slugs` quand un slug a changé
- [ ] Redirect 301 si le type dans l'URL ne correspond pas au type de l'établissement
- [ ] Schema.org `LocalBusiness` + `AggregateRating` sur les fiches établissement
- [ ] Schema.org `BreadcrumbList` sur toutes les pages
- [ ] Schema.org `WebSite` + `SearchAction` sur la homepage
- [ ] Page 404 custom avec suggestions d'établissements à proximité
- [ ] `robots.txt` configuré
- [ ] Sitemap XML

---

## Plus tard (V2)

- [ ] Paiement premium (Stripe plutôt que PayPal)
- [ ] Connexion Google/Facebook (Socialite)
- [ ] Géolocalisation navigateur (proximité automatique)
- [ ] Carte Google Maps sur les fiches et pages ville
- [ ] Notifications email (nouveau avis, établissement validé)
- [ ] Export/import données admin
- [ ] Analytics intégrées (visites par établissement)
