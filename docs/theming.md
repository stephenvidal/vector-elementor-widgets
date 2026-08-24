# Theming via Elementor Global Kits

This plugin themes widgets through the site's Elementor **global kit**, so a
widget dragged onto any site automatically picks up that site's palette.

## Principle

- **Derby Rule 10** (per-site color theme) becomes a per-site Elementor global
  kit. A new site = a new kit with fresh values.
- Widgets reference the kit's named colors via `__globals__`, never hardcoded
  colors. This preserves the Derby "one rule re-themes everything" property.
- We **augment Elementor, not fight it** — the kit is edited in Elementor's
  native global-settings UI.

## The named color set

The kit must define these named colors (matching the Derby tokens):

`brand`, `brand-dark`, `accent`, `ink`, `muted`, `paper`, `surface`, `soft`,
`line`, `hero-overlay`

## Referencing in a widget

In a style control, bind to the global color id:

```php
$widget->add_control(
	'heading_color',
	array(
		'label' => __( 'Heading Color', 'vector-elementor-widgets' ),
		'type'  => \Elementor\Controls_Manager::COLOR,
		'selectors' => array(
			'{{WRAPPER}} .vew-x__heading' => 'color: {{VALUE}};',
		),
	)
);
```

For full global-kit binding, set `__globals__` so the control's value resolves
to `globals/colors?id=brand` when the user leaves it empty. (Phase 2+ — the
initial CTA banner uses direct defaults that the site's kit/selectors override.)

## Setting up a site kit

1. Elementor → Site Settings → Global Colors.
2. Add the 10 named colors above and set each to the site's palette values.
3. Widgets dropped on the site now theme from the kit.

A `SiteKit` helper (Phase 2+) may automate this; for now the named-color
convention is the contract.

---

# Site Kit Manager (data-driven theming)

> **Status:** Implemented. Supersedes the manual "set up a site kit" steps
> above — kits are now data (editable in the admin), not hand-edited CSS.

## What it is

A site kit is a named set of **design tokens** — colors, font families, and
layout/padding values — stored as a `vew_site_kit` post in the database. Every
Vector widget resolves its `var(--token, fallback)` against these tokens, so
one kit re-themes every widget on the site.

## The token set

| Group  | Keys |
|--------|------|
| Colors | `ink, muted, paper, surface, soft, brand, brand-dark, accent, line, shadow, hero-overlay` |
| Fonts  | `display, sans` |
| Layout | `container_width` (px), `gutter` (px), `section_space_y`, `section_space_y_mobile` (size strings) |

Layout keys are stored underscored (`section_space_y`) and emitted as
hyphenated CSS vars (`--section-space-y`) — matching what the widget
stylesheets reference.

## Resolution precedence

For a given request, the active kit is resolved in this order:

1. **Per-page meta** — the page's `_vew_site_kit` meta (set via the "Site Kit"
   meta box on the page editor).
2. **Global option** — `vew_global_site_kit`, set in the Site Kits admin screen.
3. **Built-in default** — the legacy `assets/site-kit.css` file, used only if
   no DB kit is selected.

The resolved kit's tokens are compiled to `:root`/`body` CSS and enqueued
inline on `wp_enqueue_scripts` (priority 999) by `SiteKit`, so they win the
cascade over theme reset styles.

## Admin usage

**Vector Widgets → Site Kits** (`admin.php?page=vew-site-kits`):

- **List + global selector** — see all kits; pick which is the site-wide
  default.
- **Create / Edit** — a form for label, description, slug, all color tokens,
  font families, and layout values.
- **Import / Export** — each kit can be exported as a JSON file
  (`site-kit-<slug>.json`) and re-imported (with a valid slug). Handy for
  copying a brand theme across sites.
- **Delete** — removes a kit.

**Per-page override:** each page editor shows a "Site Kit" meta box where you
can pin that page to a specific kit, or leave it on "Use global kit".

## Migration

On first admin load, the plugin migrates the legacy `assets/kits/*.css` files
into DB kit posts (`KitMigrator`, run once via the `vew_site_kit_migrated`
option). Existing sites keep their current look — page meta / global option
still point at the same slug, and `SiteKit` resolves from the DB instead of the
file.

## Key classes

| Class | Responsibility |
|-------|----------------|
| `Kit\Kit` | Immutable, validated kit value object |
| `Kit\KitValue` | Whitelisted sanitizers for color/font/layout values |
| `Kit\KitStore` | Persistence (save/get/has/delete/all) via the CPT |
| `Kit\KitPostType` | Registers the `vew_site_kit` CPT |
| `Kit\KitCssCompiler` | Config → `:root`/`body` CSS |
| `Kit\KitImporter` | Validates + hydrates a kit from JSON |
| `Kit\KitMigrator` | Seeds legacy CSS kits into the DB |
| `Elementor\SiteKit` | Resolves + enqueues the active kit CSS |
| `Admin\SiteKitManagerPage` | Admin screen (list/create/edit/global/import/export/delete) |
| `Admin\SiteKitMetaBox` | Per-page kit selector meta box |

