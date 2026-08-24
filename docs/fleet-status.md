# Fleet Recreation — Status & Pipeline Recipe

Autonomous recreation of the portfolio fleet in the WordPress + Elementor instance
using the `vector-elementor-widgets` plugin. **All 15 pages are composed and verified.**

## Live URLs (pretty permalinks)

| Site | Slug | Page ID | Status |
|------|------|---------|--------|
| Blackstone & Hale | `/blackstone-law/` | 225 | ✅ |
| Ember & Oak | `/ember-oak/` | 227 | ✅ |
| Hearth & Crumb | `/hearth-crumb/` | 229 | ✅ |
| Iron & Oak | `/ironoak/` | 230 | ✅ |
| Realms of Revelation Ecclesia | `/rore/` | 231 | ✅ |
| Rore Ecclesia (Myrtle Beach) | `/rorecclesia/` | 232 | ✅ |
| Grand Strand Law Group — Home | `/gslg/` | 244 | ✅ |
| — About | `/gslg-about/` | 251 | ✅ |
| — Real Estate | `/gslg-real-estate/` | 246 | ✅ |
| — Estate Planning | `/gslg-estate-planning/` | 248 | ✅ |
| — Probate | `/gslg-probate/` | 249 | ✅ |
| — Business Law | `/gslg-business/` | 250 | ✅ |
| — Contact | `/gslg-contact/` | 252 | ✅ |
| — Privacy | `/gslg-privacy/` | 253 | ✅ |
| — Terms | `/gslg-terms/` | 254 | ✅ |

All pages verified at **390×844, 768×1024, 1280×900**: header + footer present,
zero horizontal overflow, zero JS errors. Plugin gate green — **130 tests / 1009
assertions**.

## Widget coverage

19 custom widgets (12 core + 7 new: Stats, Split, Gallery, Menu, Watch, Give,
Visit) + **PageHero** (added for GSLG subpages). FeatureGrid gained optional
`link` URL support so practice cards navigate to subpages.

## The 3-step pipeline recipe

From the dev-env dir (`.../vector-youtube-gallery/plugin/tools/dev-env`):

### 1. Import a site's images
```bash
docker compose run --rm -T \
  -v /home/msn0624c/agent-lab/websites:/var/www/html/sites:ro \
  wp-cli -c "wp eval-file /var/www/html/wp-content/plugins/vector-elementor-widgets/tools/import-site.php <slug> --allow-root"
```
Reads the site's `assets/` dir, copies each file to a writable temp dir, and
uploads it to the WP media library. Prints a source-path → attachment-ID map.
Allows SVG mime.

### 2. Compose a page
```bash
docker compose run --rm -T wp-cli -c \
  "wp eval-file /var/www/html/wp-content/plugins/vector-elementor-widgets/tools/compose-site.php <slug> --allow-root"
```
Reads `docs/sites/<slug>.json` → builds Elementor 4.x `_elementor_data`
(**plain top-level array of containers**), creates/updates the page, sets
`_vew_site_kit` (per-site kit, `spec.kit` overrides), clears stale caches.

### 3. Verify
```bash
python3 /tmp/final-fleet-verify.py   # all pages × 3 viewports
curl -s -o /dev/null -w "%{http_code}\n" http://127.0.0.1:8020/<slug>/
curl -s -o /dev/null -w "%{http_code}\n" http://127.0.0.1:8020/wp-content/uploads/elementor/css/post-<ID>.css
```

## Pitfalls encountered (learned)

- **`_elementor_data` must be a plain array of containers** — wrapping it in
  `{version, elements}` causes a 500 in `controls-stack.php`.
- **Do NOT store `_elementor_page_settings`** — an empty array stored as `[]`
  is read as a string → fatal in `CSS_Manager`.
- **Gallery/MEDIA repeater values must be nested `{url, id}`** — plain string
  URLs render an empty grid.
- **`wp eval-file` quirks:** omit `declare(strict_types=1)`; args arrive via
  `$args` (not `$argv`).
- **Pretty permalinks need the Apache rewrite rules** in `.htaccess` (WordPress
  block was empty; rewrite mod was enabled but rules absent).
- **Multi-page sites share one kit** via `"kit": "<parent>"` in the spec.
- The CTA widget slug is **`vew-cta-banner`** (not `vew-cta`).
