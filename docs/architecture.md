# Vector Elementor Widgets — Architecture

A WordPress plugin that is a **reusable production UI component library for
Elementor**. Elementor is the visual composition engine; this plugin provides
the opinionated, art-directed components. We build the component system —
Elementor remains the page builder.

## Positioning

- **Elementor = visual composition engine** (drag-drop, sections, responsive,
  history, templates, global site settings).
- **This plugin = reusable production UI component library.** Components are
  substantial and art-directed (not tiny primitives). Elementor exposes the
  meaningful knobs.
- We **augment Elementor, never fight it.** We use Elementor's native controls,
  categories, per-widget asset dependencies, and global color/font kits.

## Visual reference

**vidal-studio is the canonical visual reference** for widget art direction
(clean, consistent, modern). **Derby is the structural source** — its component
system provides the data-hook → control mapping and the responsive collapse
rules. When a Derby component and a vidal-studio component differ visually, the
vidal-studio treatment wins. See `DECISIONS.md` D-008.

## Bootstrap flow

1. `vector-elementor-widgets.php` — header, constants, optional Composer
   autoload, `vew_plugin()` helper, activation/deactivation shims,
   `plugins_loaded` → `Plugin::boot()`.
2. `Plugin::boot()` (idempotent) wires services into the `ServiceContainer`,
   then registers on `plugins_loaded` (priority 20):
   - the Elementor **Detector** (admin notice when Elementor missing/old),
   - the Elementor **integration** (category + widget registry) only when
     Elementor meets the minimum version.
3. `register_assets()` on `init` registers all widget CSS/JS centrally, loaded
   only when a widget is on the page.

## Service container

`src/Support/ServiceContainer.php` — a lightweight PSR-style container.
Services registered in `Plugin::register_services()`:

| id | class | notes |
|----|-------|-------|
| `compatibility` | `Support\Compatibility` | stateless version/Elementor check |
| `asset_manager` | `Elementor\AssetManager` | central asset registration |
| `widget_registry` | `Elementor\WidgetRegistry` | single widget registration point |
| `elementor_plugin` | `Elementor\Plugin` | category + widget registration |
| `elementor_detector` | `Elementor\Detector` | admin notice surface |

## Widget registration

Runtime widget classes are registered in `Plugin::register_services()`:

```php
$this->container->get( 'widget_registry' )->register( CtaBanner::class );
```

`WidgetRegistry::register_all()` iterates enabled widgets and calls
`$widgets_manager->register( new $class() )`. No constructor args — Elementor
owns the constructor (`__construct( array $data, ?array $args )`); services are
resolved lazily inside `render()`.

Adding a widget is a coordinated three-part change: runtime class registration,
`WidgetCatalog` metadata, and asset registration. Use
`tools/scaffold-widget.php` to patch these locations consistently. The catalog
is authoritative for admin metadata, but it does not replace the runtime or
asset lists.

## Widget contract

Every widget extends `Elementor\Widget\BaseWidget` (extends `Elementor\Widget_Base`):

- `get_name()` — stable slug, permanent.
- `__construct( array $data = [], ?array $args = null )` — matches
  `Widget_Base`; no constructor injection.
- `register_controls()` — delegates to per-tab control classes.
- `render()` — the ONLY render path. **No `_content_template()` override**
  (it fatals in the editor's print-template context).
- `get_style_depends()` / `get_script_depends()` — per-widget assets, loaded
  only when the widget is on the page.
- Settings are **whitelisted + type-coerced** via `BaseWidget::sanitize_settings()`
  before use.

## CSS architecture

All frontend selectors are scoped to the component:

```
.vew-cta
.vew-cta__heading
.vew-cta__actions
.vew-cta__button--primary
```

Never use generic selectors (`.card`, `.title`). No `!important`, no theme
dependencies, no leaking typography/spacing into surrounding content.

## Responsive

Elementor's **default breakpoints** (tablet 1024 / mobile 767) are used.
The component CSS owns the **layout collapse** at ≤820px (the Derby Rule 6
convention) via its own media query. Elementor's responsive controls are for
**value overrides** (font size, spacing, alignment), never the structural
collapse.

## Theming (global kits)

Widgets reference the site's Elementor **global kit** via `__globals__`
(`globals/colors?id=brand`, etc.), never hardcoded per-site colors. See
`theming.md`.

## File layout

```
vector-elementor-widgets.php   bootstrap
src/
  Plugin.php                   kernel (service wiring + boot)
  Support/                     ServiceContainer, Compatibility, CompatibilityReport
  Elementor/
    Plugin.php                 category + widget registration
    Detector.php               admin notice when Elementor missing
    AssetManager.php           central asset registration
    WidgetRegistry.php         single registration point
    Component/                 reusable sub-widget design capabilities
    Widget/                    BaseWidget, CtaBanner, ...
    Control/                   CtaContentControls, CtaStyleControls, ...
assets/components/             conditionally loaded shared component CSS
widgets/
  CtaBanner/cta-banner.css     per-widget scoped CSS/JS
admin/                          Widget Manager (Phase 4)
docs/                           this + creating-a-widget + control-mapping + theming + quality-gate + DECISIONS
tests/                          PHPUnit (Brain Monkey) + PHPStan stubs
```

## Adding a widget (summary)

1. Create `src/Elementor/Widget/XWidget.php` extending `BaseWidget`.
2. Create `src/Elementor/Control/XContentControls.php` + `XStyleControls.php`.
3. Create `widgets/X/x.css` (+ `x.js` if interactive).
4. Register the widget: `->register( XWidget::class )` in `Plugin::register_services()`.
5. Register the asset: `$assets->register_style('vew-x', 'widgets/X/x.css')` in `register_assets()`.
6. Run `composer run all` (lint + stan + test).

See `creating-a-widget.md` for the full process.

## Reuse below the section-widget level

Before duplicating a visual contract across section widgets, inspect
`src/Elementor/Component/`. `SectionHeading` is the first shared capability: it
adds structured emphasis controls and renders escaped eyebrow/title/intro
markup while preserving each widget's BEM classes. Components are internal
helpers, not automatically standalone or nested Elementor widgets. Introduce a
new component only after recurrence is demonstrated across unrelated widgets.
See `framework-refinement.md` for the evidence and abstraction threshold.
