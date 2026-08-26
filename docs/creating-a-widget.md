# Creating a Widget

The exact process for adding a new widget. Following this makes adding a widget
a predictable operation — no architectural decisions required.

## 0. Scaffold (recommended)

The fastest, gate-clean path is the scaffold tool. It generates the widget
class, both control classes, the CSS asset (and JS with `--js`), and patches
the two registration points (`src/Plugin.php` + `src/Elementor/WidgetCatalog.php`)
for you. Run it from the plugin root:

```bash
php tools/scaffold-widget.php Process            # slug defaults to vew-process
php tools/scaffold-widget.php Process --slug=vew-process --js
```

It writes:

- `src/Elementor/Widget/Process.php`
- `src/Elementor/Control/ProcessContentControls.php` + `ProcessStyleControls.php`
- `widgets/Process/vew-process.css` (+ `vew-process.js` with `--js`)
- patches `src/Plugin.php` (use + register + asset) and
  `src/Elementor/WidgetCatalog.php` (use + catalog entry)

The scaffold is **not idempotent** — it aborts if the widget class file already
exists. After scaffolding, fill in the real controls, `render()`, and CSS for
the component, then run `composer run all`.

The scaffold output is covered by `tests/Unit/Tools/ScaffoldWidgetTest.php`,
which runs the tool against a sandbox copy and asserts the generated files
match the established pattern and the registration points are patched exactly
once.

The manual steps below document what the scaffold does, for reference and for
hand-writing a widget when you prefer full control.

## 1. Create the widget class

`src/Elementor/Widget/XWidget.php`:

```php
<?php
declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\XContentControls;
use Vector\ElementorWidgets\Elementor\Control\XStyleControls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class XWidget extends BaseWidget {

	public function get_name(): string { return 'vew-x'; }   // stable forever
	public function get_title(): string { return __( 'X', 'vector-elementor-widgets' ); }
	public function get_icon(): string { return 'eicon-code'; }
	public function get_categories(): array { return array( 'vector-widgets' ); }
	public function get_keywords(): array { return array( 'x' ); }

	protected function register_controls(): void {
		( new XContentControls() )->register( $this );
		( new XStyleControls() )->register( $this );
	}

	protected function render(): void {   // ONLY render path; no _content_template()
		$settings = $this->get_settings_for_display();
		$safe     = $this->sanitize_settings( $settings, array(
			'heading'     => 'string',
			'description' => 'string',
		) );
		// ... echo scoped, escaped HTML
	}

	public function get_style_depends(): array { return array( 'vew-x' ); }
}
```

## 2. Create the control classes

`src/Elementor/Control/XContentControls.php` and `XStyleControls.php`. Each has
a single `register( Widget_Base $widget ): void` method that pushes one section.

**Section + control ids must be unique** across all control classes (they share
one flat stack). A duplicate is silently dropped by Elementor. The
`ControlSectionIdsTest` enforces this lexically.

Before hand-writing eyebrow/title/intro controls and markup, inspect
`src/Elementor/Component/`. If the widget uses the established section-heading
anatomy, call `SectionHeading::register_content_controls()` inside its Content
section, `register_style_controls()` inside its Style section, whitelist the
additive text fields, and render with `SectionHeading::render()`. Keep the
widget's semantic section structure and BEM layout classes; the component owns
only the repeated heading contract.

> **PITFALL — call order matters (Elementor fatal).** `SectionHeading::register_content_controls()`
> calls `add_control()` immediately, so it MUST be invoked AFTER `start_controls_section()` is open.
> If it is called before the section starts, Elementor throws
> `Cannot add a control outside of a section (use start_controls_section)` and kills the
> editor (the error labels the *calling widget*, e.g. `BeforeAfter::`, but the method
> `handle_control_position` is Elementor core in `includes/base/controls-stack.php`, not ours).
> Affected historically: BeforeAfter, Countdown, GoogleMap, Timeline, VideoEmbed. Fix is to move the
> helper call below the section opener (see `docs/framework-refinement.md`).

## 3. Create the assets

`widgets/X/x.css` (+ `widgets/X/x.js` if interactive). Scope every selector to
the component (`vew-x`). Do not use generic selectors.

## 4. Register the widget

In `src/Plugin.php::register_services()`:

```php
$this->container->get( 'widget_registry' )->register( XWidget::class );
```

## 5. Register the asset

In `src/Plugin.php::register_assets()`:

```php
$assets->register_style( 'vew-x', 'widgets/X/x.css' );
```

## 6. Add the catalog entry

In `src/Elementor/WidgetCatalog.php::all()`, add an entry with the widget's
slug, class, title, description, and icon. The catalog is the single source of
truth for both the Elementor registry and the admin Widget Manager screen — a
widget that isn't in the catalog can't be toggled in the admin.

## 7. Run the gates

```bash
composer run all   # lint + stan + test
```

Must be green before the widget is considered complete.

## Quality gate (definition of done)

A widget is complete only when ALL pass:

- [ ] Appears under "Vector Widgets" category
- [ ] Editor controls function
- [ ] Live preview updates appropriately
- [ ] Frontend rendering matches editor
- [ ] No PHP warnings/notices
- [ ] No console errors
- [ ] Multiple instances work
- [ ] Desktop / tablet / mobile layout works
- [ ] Keyboard behavior works where applicable
- [ ] Output is escaped appropriately
- [ ] CSS is scoped (no generic selectors, no leaks)
- [ ] Assets load only when the widget is on the page
- [ ] Theme conflicts minimized
- [ ] Works after page reload
- [ ] Works outside Elementor editing mode
- [ ] `composer run all` green

See `quality-gate.md` for the full definition and the real-HTTP verification.
