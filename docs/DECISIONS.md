# Vector Elementor Widgets — Decisions

Owner: Stephen Vidal
Last updated: 2026-08-15

Each decision is dated and records context, the decision, rationale, and impact.
Append new decisions; never rewrite history.

---

## D-001: Elementor is the composition engine; the plugin is the component library

Date: 2026-08-15
Context: The plugin could either build its own page-building editor or augment
Elementor. A prior project (`page-builder-plugin`) built its own editor.
Decision: Elementor is the visual composition engine (drag-drop, sections,
responsive, history, templates, global site settings). This plugin is a
reusable production UI component library that augments Elementor — it never
builds a custom editor.
Rationale: Reuses Elementor's mature, battle-tested editing surface instead of
reimplementing it. Keeps the plugin focused on art-directed components.
Impact: All widgets use Elementor's native controls, categories, per-widget
asset dependencies, and global color/font kits.

## D-002: Widgets are direct ports of the component registry, not generic components

Date: 2026-08-15
Context: The raw material is 10 finished sites + `WEBSITE-COMPONENTS.md`.
Decision: Widgets are direct, art-directed ports of the existing component
registry (hero, feature grid, testimonials, FAQ, CTA banner, ...), not generic
reusable primitives.
Rationale: Gives production-quality, opinionated components out of the box.
Impact: Each widget maps 1:1 to a real component with real content patterns.

## D-003: Per-site theming via Elementor global kits

Date: 2026-08-15
Context: Derby uses named design tokens (`--brand`, `--brand-dark`, `--accent`,
`--ink`, `--muted`, `--paper`, `--surface`, `--soft`, `--line`, `--hero-overlay`).
Decision: Named Elementor global colors map to the Derby tokens. Widgets
reference `globals/colors?id=...` via `__globals__`, never hardcoded colors.
The plugin ships a "create site kit" helper.
Rationale: Keeps theming centralized and per-site; widgets stay theme-agnostic.
Impact: A site's look is defined once in its global kit, not per-widget.

## D-004: Elementor default breakpoints; component CSS owns the layout collapse

Date: 2026-08-15
Context: Derby collapses layout at 820px. Elementor defaults are tablet 1024 /
mobile 767.
Decision: Use Elementor's default breakpoints (1024/767). The component CSS
owns the structural layout collapse at ≤820px via its own media query.
Elementor's responsive controls are for value tweaks only (font size, spacing,
alignment), never structural layout.
Rationale: Avoids custom-breakpoint friction while preserving the Derby
no-overflow guarantee.
Impact: Each widget's stylesheet bakes in the ≤820px collapse.

## D-005: Plugin identity — Vector family

Date: 2026-08-15
Context: The plugin needs a stable identity and namespace.
Decision: Name "Vector Elementor Widgets", slug `vector-elementor-widgets`,
namespace `Vector\ElementorWidgets`, category "Vector Widgets", text domain
`vector-elementor-widgets`, GPL-2.0-or-later, Requires WP 6.x / PHP 8.3+ /
Elementor 4.x (free). Sibling of `VectorYT\Gallery`.
Rationale: Consistent with the existing Vector plugin family.
Impact: Stable slugs/namespaces that never change.

## D-006: Control-id uniqueness is scoped per-widget, not global

Date: 2026-08-15
Context: An early test flagged `eyebrow`/`title` as duplicates across widgets.
Decision: Elementor keys controls in one flat stack PER WIDGET, so control ids
must be unique within a widget's own control set — not globally across widgets.
The `ControlSectionIdsTest` groups control files by widget and checks
uniqueness within each group.
Rationale: Cross-widget reuse of common ids (eyebrow, title) is intentional and
correct; the real invariant is per-widget uniqueness.
Impact: The test documents and enforces the correct convention.

## D-007: Page gutter lives in Elementor's layout layer, not baked into widgets

Date: 2026-08-15
Context: The comparison page rendered content flush to the screen edge because
widgets were dropped without proper section composition.
Decision: The horizontal page gutter comes from Elementor's Section/Container
layer (Boxed content width ~1240px) + global kit, NOT baked into the widgets.
Widgets stay layout-agnostic. The hero is the exception (full-bleed with its
own inner container). Mobile gutter (17px each side) is set on the container's
mobile padding.
Rationale: Matches Elementor's native mental model; avoids double-padding when
a widget sits in a Boxed section; consistent with D-004 (layout owned by the
composition layer).
Impact: Pages must be composed with proper Boxed containers. Widgets remain
drop-in safe and theme-agnostic.

## D-008: vidal-studio is the canonical visual reference; Derby is the structural source

Date: 2026-08-15
Context: Reviewing the comparison page, the Derby mosaic treatment (large first
card in the feature grid) was not the desired look. Stephen confirmed
vidal-studio is the better visual reference.
Decision: **vidal-studio is the canonical visual reference for the plugin
widgets' art direction.** Derby is used only for the component/control
architecture (data-hook → control mapping, responsive collapse rules). When a
Derby component and a vidal-studio component differ visually, the vidal-studio
treatment wins.
Rationale: vidal-studio is cleaner, more consistent, and more modern — the look
Stephen actually wants. Derby's value is structural, not visual.
Impact: The Feature Grid was changed from a mosaic (large first card) to a
uniform `repeat(3, 1fr)` grid of equal cards. Future widget ports should
reference vidal-studio for visual treatment and Derby for control mapping.

## D-009: Full-page section widgets own a safe internal content shell

Date: 2026-08-16
Context: The full Vidal Studio rebuild proved D-007 incomplete. Elementor
container padding existed only at the mobile breakpoint, leaving tablet content
flush to the viewport; full-width Process and Footer surfaces had no internal
horizontal protection; and a widget dropped into a differently configured
container could regress again.
Decision: **Every full-page section widget owns a full-width surface plus an
internal max-width content shell with a 24px desktop gutter and 20px gutter at
820px and below.** Elementor containers may still own vertical section rhythm
and page composition, but they are no longer the sole safety boundary for
horizontal content. Header and Hero retain their existing internal shells;
Services, Portfolio, Process, Pricing, FAQ, Contact, and Footer enforce the same
invariant. Interactive widget controls must also reset theme button states for
normal, hover, focus, and active states. The Elementor top-level header
container owns sticky positioning because a sticky child is constrained by its
header-height parent.
Rationale: A reusable widget must remain acceptable when dropped into full-width,
boxed, tablet, or mobile compositions. Component safety cannot depend on a
specific page builder script having remembered every responsive padding field.
Impact: D-007 is superseded where it assigned horizontal safety exclusively to
the layout layer. New section widgets require an internal shell and responsive
regression tests before acceptance. Phone-first review at 390px is mandatory,
followed by tablet (768px) and desktop (1280px) verification.

## D-010: Structured headings are an internal reusable capability

Date: 2026-08-16
Context: Six unrelated Vidal sections repeated eyebrow/title/intro rendering
but could not express the source's partially italic, deliberately broken
headings without raw HTML. Hero's WYSIWYG proved the visual need but was too
fragile to copy into every widget.
Decision: Add `Elementor\Component\SectionHeading` as an internal shared
control/renderer/style contract. Existing persisted IDs remain unchanged; new
accent/after/placement/style controls are additive. The capability is not a
standalone or nested widget because it composes semantic text pieces rather
than arbitrary children.
Rationale: One narrow abstraction solves six discrepancies, improves editor
safety, and avoids a universal widget or one-site selectors.
Impact: Future agents must inspect `Elementor/Component` before recreating
heading logic. New components require evidence across unrelated widgets and a
legacy rendering strategy.

*End of DECISIONS.md. Any future architectural change appends a new entry.*

## D-011: Per-site kits via `_vew_site_kit` post meta, not a single global kit

Date: 2026-08-17
Context: Recreating the portfolio fleet required each site to load its own
palette. A single global Vidal kit could not express 7 distinct palettes.
Decision: Each composed page stores a `_vew_site_kit` post meta; `SiteKit`
resolves the per-site kit CSS with the Vidal kit as fallback. The composer sets
it from the spec's `slug` (or `spec.kit` for shared multi-page kits).
Rationale: Each page loads exactly its own tokens; multi-page sites share one kit
via the `kit` override.
Impact: New site compositions set `_vew_site_kit`; multi-page sites add
`"kit": "<parent>"` to the spec.

## D-012: `_elementor_data` is a plain top-level array of containers

Date: 2026-08-17
Context: The composer initially wrapped containers in `{version, elements}`.
Elementor 4.2.2 iterates `_elementor_data` directly and fatals in
`controls-stack.php` when it hits the version string.
Decision: `_elementor_data` is a plain array of containers; no object wrapper.
Also, do NOT store `_elementor_page_settings` (an empty array stored as `[]` is
read as a string → fatal in `CSS_Manager`).
Rationale: Matches Elementor 4.x persisted schema exactly.
Impact: The composer emits the plain array and deletes the page-settings meta.

## D-013: Repeater MEDIA values are nested `{url, id}` objects

Date: 2026-08-17
Context: Gallery images stored as plain string URLs rendered an empty grid;
Portfolio (nested arrays) worked.
Decision: MEDIA controls in repeaters store `{url, id}` objects, matching
Elementor's `get_settings_for_display()` contract.
Rationale: Elementor transforms MEDIA repeater values to nested arrays.
Impact: Gallery specs use `"url": { "url": "...", "id": N }`.

## D-014: PageHero widget + FeatureGrid link support for multi-page sites

Date: 2026-08-17
Context: GSLG is a multi-page site needing a page-hero (banner + breadcrumb) and
practice cards that navigate to subpages.
Decision: Added `PageHero` widget (full-width surface, breadcrumb, eyebrow/title/
lead). FeatureGrid gained an optional per-card `link` URL rendering the detail as
a link.
Rationale: Multi-page navigation requires real page links; PageHero is a
recurring subpage header across 8 GSLG pages.
Impact: New `vew-page-hero` widget + catalog entry + tests; FeatureGrid link
rendering is backward-compatible.

## D-015: Pretty permalinks require the Apache rewrite block

Date: 2026-08-17
Context: Switching permalink structure to `/%postname%/` made `/slug/` URLs 404
— the WordPress `.htaccess` block was empty.
Decision: Regenerated the standard WordPress rewrite rules in `.htaccess`.
Rationale: Apache needs mod_rewrite rules to route `/slug/` to index.php.
Impact: All 15 composed pages are reachable via clean `/slug/` URLs.

