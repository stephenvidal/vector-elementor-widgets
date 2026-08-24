<?php
/**
 * Widget scaffold tool.
 *
 * Generates a complete, gate-clean new widget for the Vector Elementor
 * Widgets plugin. Automates the manual 6-step process in
 * `docs/creating-a-widget.md`:
 *
 *   1. src/Elementor/Widget/XWidget.php
 *   2. src/Elementor/Control/XContentControls.php + XStyleControls.php
 *   3. widgets/X/x.css (+ widgets/X/x.js when --js)
 *   4. src/Plugin.php            (use + register + register_assets)
 *   5. src/Elementor/WidgetCatalog.php (use + catalog entry)
 *
 * Usage (from the plugin root):
 *   php tools/scaffold-widget.php Process
 *   php tools/scaffold-widget.php Process --slug=vew-process --js
 *
 * The generated files follow the exact conventions of the existing widgets
 * (FeatureGrid, Testimonials, Faq) so they pass PHPCS / PHPStan / PHPUnit
 * out of the box. Run `composer run all` after scaffolding.
 *
 * @package Vector\ElementorWidgets\Tools
 */

declare( strict_types=1 );

if ( PHP_SAPI !== 'cli' ) {
	exit( 1 );
}

// --- Argument parsing -------------------------------------------------------

$args    = array_slice( $argv, 1 );
$name    = '';
$slug    = '';
$with_js = false;

foreach ( $args as $arg ) {
	if ( str_starts_with( $arg, '--slug=' ) ) {
		$slug = substr( $arg, strlen( '--slug=' ) );
	} elseif ( '--js' === $arg ) {
		$with_js = true;
	} elseif ( '' === $name ) {
		$name = $arg;
	}
}

if ( '' === $name ) {
	fwrite( STDERR, "Usage: php tools/scaffold-widget.php <Name> [--slug=vew-x] [--js]\n" );
	exit( 1 );
}

// --- Derive identifiers -----------------------------------------------------

$class = ucfirst( $name ); // e.g. Process
if ( '' === $slug ) {
	$slug = 'vew-' . strtolower( $name );
}
$ns_class   = 'Vector\\ElementorWidgets\\Elementor\\Widget\\' . $class;
$content    = $class . 'ContentControls';
$style      = $class . 'StyleControls';
$css_handle = $slug;
$js_handle  = $slug;
$css_path   = "widgets/{$class}/{$slug}.css";
$js_path    = "widgets/{$class}/{$slug}.js";
$title      = ucwords( str_replace( array( '-', '_' ), ' ', $name ) );

$root = dirname( __DIR__ );

// --- Idempotency guard ------------------------------------------------------

$widget_abs = $root . '/src/Elementor/Widget/' . $class . '.php';
if ( file_exists( $widget_abs ) ) {
	fwrite( STDERR, "Widget '{$class}' already exists at src/Elementor/Widget/{$class}.php — aborting (scaffold is not idempotent).\n" );
	exit( 1 );
}

// --- File templates ---------------------------------------------------------

$widget_tpl = <<<'PHP'
<?php
/**
 * {Title} widget.
 *
 * Phase 5 scaffold — a {Title} component. Replace this docblock with the
 * component's purpose and the Derby/vidal-studio source it ports.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\{Content};
use Vector\ElementorWidgets\Elementor\Control\{Style};

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * {Title} widget.
 */
final class {Class} extends BaseWidget {

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return '{Slug}';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( '{Title}', 'vector-elementor-widgets' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-code';
	}

	/**
	 * Widget category.
	 *
	 * @return array<int, string>
	 */
	public function get_categories(): array {
		return array( 'vector-widgets' );
	}

	/**
	 * Widget keywords.
	 *
	 * @return array<int, string>
	 */
	public function get_keywords(): array {
		return array( '{Keyword}' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new {Content}() )->register( $this );
		( new {Style}() )->register( $this );
	}

	/**
	 * Render the widget.
	 *
	 * The ONLY render path. No `_content_template()` override.
	 *
	 * @return void
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$safe     = $this->sanitize_settings(
			$settings,
			array(
				'heading' => 'string',
			)
		);

		$heading = $safe['heading'] ?? '';
		?>
		<section class="{Slug}" aria-label="{Title}">
			<?php if ( '' !== $heading ) : ?>
				<h2 class="{Slug}__title"><?php echo esc_html( $heading ); ?></h2>
			<?php endif; ?>
		</section>
		<?php
	}

	/**
	 * Declare the stylesheet dependency.
	 *
	 * @return array<int, string>
	 */
	public function get_style_depends(): array {
		return array( '{CssHandle}' );
	}
{ScriptDepends}
PHP;

$content_tpl = <<<'PHP'
<?php
/**
 * {Title} content controls.
 *
 * @package Vector\ElementorWidgets\Elementor\Control
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Control;

use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content controls for the {Title} widget.
 */
final class {Content} {

	/**
	 * Register the content section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'{SectionId}',
			array(
				'label' => __( 'Content', 'vector-elementor-widgets' ),
			)
		);

		$widget->add_control(
			'heading',
			array(
				'label'       => __( 'Heading', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( '{Title}', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->end_controls_section();
	}
}
PHP;

$style_tpl = <<<'PHP'
<?php
/**
 * {Title} style controls.
 *
 * @package Vector\ElementorWidgets\Elementor\Control
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Control;

use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Style controls for the {Title} widget.
 */
final class {Style} {

	/**
	 * Register the style section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'{StyleSectionId}',
			array(
				'label' => __( 'Style', 'vector-elementor-widgets' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$widget->add_control(
			'{StyleId}',
			array(
				'label'     => __( 'Heading Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .{Slug}__title' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->end_controls_section();
	}
}
PHP;

$css_tpl = <<<'CSS'
/* {Title} widget — scoped to .{Slug}. Never use generic selectors. */
.{Slug} {
	/* component root */
}

.{Slug}__title {
	/* heading */
}

/* Responsive collapse at 820px (component CSS owns the layout collapse). */
@media ( max-width: 820px ) {
	.{Slug} {
		/* mobile layout */
	}
}
CSS;

$js_tpl = <<<'JS'
/**
 * {Title} widget frontend behaviour.
 * Vanilla JS, no dependencies. Scope all selectors to .{Slug}.
 */
( function () {
	'use strict';
	document.querySelectorAll( '.{Slug}' ).forEach( function ( root ) {
		// behaviour
	} );
} )();
JS;

// --- Render templates -------------------------------------------------------

$script_depends = "}\n";
if ( $with_js ) {
	$script_depends = "\n\n	/**\n	 * Declare the script dependency.\n	 *\n	 * @return array<int, string>\n	 */\n	public function get_script_depends(): array {\n		return array( '{JsHandle}' );\n	}\n}\n";
}

$replace = array(
	'{Title}'          => $title,
	'{Class}'          => $class,
	'{Slug}'           => $slug,
	'{Keyword}'        => strtolower( $name ),
	'{Content}'        => $content,
	'{Style}'          => $style,
	'{CssHandle}'      => $css_handle,
	'{JsHandle}'       => $js_handle,
	'{SectionId}'      => 'vew_' . strtolower( $name ) . '_content',
	'{StyleSectionId}' => 'vew_' . strtolower( $name ) . '_style',
	'{StyleId}'        => strtolower( $name ) . '_heading_color',
	'{ScriptDepends}'  => $script_depends,
);

$files = array(
	"src/Elementor/Widget/{$class}.php" => strtr( $widget_tpl, $replace ),
	"src/Elementor/Control/{$content}.php" => strtr( $content_tpl, $replace ),
	"src/Elementor/Control/{$style}.php" => strtr( $style_tpl, $replace ),
	$css_path => strtr( $css_tpl, $replace ),
);
if ( $with_js ) {
	$files[ $js_path ] = strtr( $js_tpl, $replace );
}

// --- Write files ------------------------------------------------------------

$written = array();
foreach ( $files as $rel => $body ) {
	$abs = $root . '/' . $rel;
	$dir = dirname( $abs );
	if ( ! is_dir( $dir ) ) {
		mkdir( $dir, 0755, true );
	}
	// Ensure a single trailing newline (WPCS requires it).
	$body = rtrim( $body, "\n" ) . "\n";
	if ( file_put_contents( $abs, $body ) === false ) {
		fwrite( STDERR, "FAILED to write {$rel}\n" );
		exit( 1 );
	}
	$written[] = $rel;
}

// --- Patch Plugin.php -------------------------------------------------------

$plugin = $root . '/src/Plugin.php';
$plugin_src = file_get_contents( $plugin );
if ( false === $plugin_src ) {
	fwrite( STDERR, "Cannot read src/Plugin.php\n" );
	exit( 1 );
}

// 1. Add use statement after the last widget use.
$use_anchor = "use Vector\\ElementorWidgets\\Elementor\\Widget\\Testimonials;\n";
if ( false !== strpos( $plugin_src, $use_anchor ) ) {
	$plugin_src = str_replace(
		$use_anchor,
		$use_anchor . "use Vector\\ElementorWidgets\\Elementor\\Widget\\{$class};\n",
		$plugin_src
	);
}

// 2. Add register call after the last register.
$reg_anchor = "\$this->container->get( 'widget_registry' )->register( Faq::class );\n";
if ( false !== strpos( $plugin_src, $reg_anchor ) ) {
	$plugin_src = str_replace(
		$reg_anchor,
		$reg_anchor . "\t\t\$this->container->get( 'widget_registry' )->register( {$class}::class );\n",
		$plugin_src
	);
}

// 3. Add asset registration after the last style/script.
$asset_anchor = "		\$assets->register_script( 'vew-faq', 'widgets/Faq/faq.js' );\n";
if ( false !== strpos( $plugin_src, $asset_anchor ) ) {
	$new_asset = "		\$assets->register_style( '{$css_handle}', '{$css_path}' );\n";
	if ( $with_js ) {
		$new_asset .= "		\$assets->register_script( '{$js_handle}', '{$js_path}' );\n";
	}
	$plugin_src = str_replace( $asset_anchor, $asset_anchor . $new_asset, $plugin_src );
}

file_put_contents( $plugin, $plugin_src );
$written[] = 'src/Plugin.php (patched)';

// --- Patch WidgetCatalog.php ------------------------------------------------

$catalog = $root . '/src/Elementor/WidgetCatalog.php';
$catalog_src = file_get_contents( $catalog );
if ( false === $catalog_src ) {
	fwrite( STDERR, "Cannot read src/Elementor/WidgetCatalog.php\n" );
	exit( 1 );
}

// 1. Add use statement.
$cat_use_anchor = "use Vector\\ElementorWidgets\\Elementor\\Widget\\Testimonials;\n";
if ( false !== strpos( $catalog_src, $cat_use_anchor ) ) {
	$catalog_src = str_replace(
		$cat_use_anchor,
		$cat_use_anchor . "use Vector\\ElementorWidgets\\Elementor\\Widget\\{$class};\n",
		$catalog_src
	);
}

// 2. Add catalog entry before the closing of the `all()` array. Anchor on the
//    unique end-of-method marker (the array's `);` + the method's closing
//    brace + the next docblock) so it works no matter how many entries exist.
$cat_entry = "			array(\n				'slug'        => '{$slug}',\n				'class'       => {$class}::class,\n				'title'       => __( '{$title}', 'vector-elementor-widgets' ),\n				'description' => __( 'A {$title} component.', 'vector-elementor-widgets' ),\n				'icon'        => 'eicon-code',\n			),\n";
$cat_anchor = "		);\n	}\n\n	/**\n	 * Find a widget definition by its class name.";
if ( false !== strpos( $catalog_src, $cat_anchor ) ) {
	$catalog_src = str_replace( $cat_anchor, $cat_entry . $cat_anchor, $catalog_src );
}

file_put_contents( $catalog, $catalog_src );
$written[] = 'src/Elementor/WidgetCatalog.php (patched)';

// --- Report -----------------------------------------------------------------

echo "Scaffolded widget '{$title}' ({$slug})\n";
echo "  class: {$ns_class}\n";
echo "  files written:\n";
foreach ( $written as $w ) {
	echo "    - {$w}\n";
}
echo "\nNext steps:\n";
echo "  1. Fill in the real controls + render() + CSS for the component.\n";
echo "  2. composer run all   (lint + stan + test)\n";
echo "  3. Verify live in Elementor (see docs/quality-gate.md).\n";
