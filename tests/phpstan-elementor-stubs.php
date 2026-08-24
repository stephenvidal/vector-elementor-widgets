<?php
/**
 * PHPStan stubs for Elementor.
 *
 * The plugin only uses a small surface area of the Elementor API in
 * Phase 1 (category + widget registration, control types, and the
 * Widget_Base render/content_template hooks). The real Elementor
 * class files are not on disk in the dev environment (the plugin
 * runs against the live Elementor), so PHPStan cannot resolve the
 * `Elementor\…` symbols without a stub. We define just enough types
 * to satisfy static analysis. The shapes here match the official
 * Elementor developer documentation; no runtime code is imported
 * from this file.
 *
 * @phpstan-file-stub
 */

namespace Elementor {

	if ( ! class_exists( __NAMESPACE__ . '\\Elements_Manager' ) ) {
		/**
		 * Elementor Elements Manager.
		 *
		 * @phpstan-type CategoryDef array{ title: string, icon?: string }
		 */
		class Elements_Manager {
			/**
			 * @param string      $name Category slug.
			 * @param CategoryDef $args Category definition.
			 * @return void
			 */
			public function add_category( string $name, array $args ): void {}
		}
	}

	if ( ! class_exists( __NAMESPACE__ . '\\Widgets_Manager' ) ) {
		/**
		 * Elementor Widgets Manager.
		 */
		class Widgets_Manager {
			/**
			 * @param \Elementor\Widget_Base $widget Widget instance.
			 * @return void
			 */
			public function register( Widget_Base $widget ): void {}
		}
	}

	if ( ! class_exists( __NAMESPACE__ . '\\Widget_Base' ) ) {
		/**
		 * Elementor Widget base class.
		 */
		abstract class Widget_Base {
			/**
			 * @param array<string, mixed> $data Data.
			 * @param array<string, mixed> $args Optional args.
			 */
			public function __construct( array $data = array(), array $args = array() ) {}

			/**
			 * @return string
			 */
			abstract public function get_name(): string;

			/**
			 * @return string
			 */
			abstract public function get_title(): string;

			/**
			 * @return string
			 */
			abstract public function get_icon(): string;

			/**
			 * @return array<int, string>
			 */
			abstract public function get_categories(): array;

			/**
			 * @return array<int, string>
			 */
			public function get_keywords(): array { return array(); }

			/**
			 * Register controls.
			 * @return void
			 */
			protected function register_controls(): void {}

			/**
			 * @return void
			 */
			protected function render(): void {}

			// Note: we intentionally do NOT declare `content_template()` or
			// `_content_template()` in the stub. Elementor 4.x's actual API
			// for "save as template" is the read-only HTML of the live render,
			// not a separately-overridable PHP method. Per D-500, the
			// widget's `_content_template()` override (if any) runs in the
			// editor's print-template context where settings are not yet
			// bound, so accessing them is a fatal. The widget deliberately
			// does not override it, and we do not model it here.

			/**
			 * @param string               $id    Section id.
			 * @param array<string, mixed> $args Section args.
			 * @return void
			 */
			public function start_controls_section( string $id, array $args ): void {}

			/**
			 * @return void
			 */
			public function end_controls_section(): void {}

			/**
			 * @param string               $id    Control id.
			 * @param array<string, mixed> $args Control args.
			 * @return void
			 */
			public function add_control( string $id, array $args ): void {}

			/**
			 * Register a grouped control.
			 *
			 * @param string               $type Group control type.
			 * @param array<string, mixed> $args Group control arguments.
			 * @return void
			 */
			public function add_group_control( string $type, array $args ): void {}

			/**
			 * Responsive variant of add_control.
			 * @param string               $id    Control id.
			 * @param array<string, mixed> $args Control args.
			 * @return void
			 */
			public function add_responsive_control( string $id, array $args ): void {}

			/**
			 * Elementor's helper for fetching sanitized settings in render methods.
			 * @return array<string, mixed>
			 */
			public function get_settings_for_display(): array { return array(); }

			/**
			 * Declare stylesheet dependencies.
			 * @return array<int, string>
			 */
			public function get_style_depends(): array { return array(); }
		}
	}

	if ( ! class_exists( __NAMESPACE__ . '\\Group_Control_Typography' ) ) {
		/**
		 * Elementor typography group control.
		 */
		final class Group_Control_Typography {
			public static function get_type(): string {
				return 'typography';
			}
		}
	}

	if ( ! class_exists( __NAMESPACE__ . '\\Controls_Manager' ) ) {
		/**
		 * Elementor control type registry.
		 */
		final class Controls_Manager {
			public const TEXT             = 'text';
			public const TEXTAREA         = 'textarea';
			public const WYSIWYG          = 'wysiwyg';
			public const NUMBER           = 'number';
			public const SWITCHER         = 'switcher';
			public const SELECT           = 'select';
			public const SELECT2          = 'select2';
			public const CHOOSE           = 'choose';
			public const SLIDER           = 'slider';
			public const DATE_TIME        = 'date_time';
			public const HIDDEN           = 'hidden';
			public const URL              = 'url';
			public const COLOR            = 'color';
			public const MEDIA            = 'media';
			public const REPEATER         = 'repeater';
			public const TAB_CONTENT      = 'content';
			public const TAB_STYLE        = 'style';
			public const TAB_LAYOUT       = 'layout';
			public const TAB_ADVANCED     = 'advanced';
		}
	}

	if ( ! class_exists( __NAMESPACE__ . '\\Plugin' ) ) {
		/**
		 * Elementor main plugin singleton.
		 */
		final class Plugin {
			/**
			 * @var \Elementor\Plugin|null
			 */
			public static $instance = null;

			/**
			 * @var \Elementor\Editor
			 */
			public $editor;
		}
	}

	if ( ! class_exists( __NAMESPACE__ . '\\Editor' ) ) {
		/**
		 * Elementor editor.
		 */
		class Editor {
			/**
			 * @return bool
			 */
			public function is_edit_mode(): bool { return false; }
		}
	}
}
