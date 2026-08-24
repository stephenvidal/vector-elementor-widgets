<?php
/**
 * Per-page site kit meta box.
 *
 * Adds a "Site Kit" meta box to the page/post editor so a page can override
 * the global kit. Persists to `_vew_site_kit` meta, which SiteKit::resolve_kit()
 * reads first (page meta → global option → default).
 *
 * @package Vector\ElementorWidgets\Admin
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Admin;

use Vector\ElementorWidgets\Kit\KitStore;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Per-page kit assignment meta box.
 */
final class SiteKitMetaBox {

	/**
	 * Post types that get the meta box.
	 */
	private const POST_TYPES = array( 'page' );

	/**
	 * Kit store.
	 *
	 * @var KitStore
	 */
	private KitStore $store;

	/**
	 * Nonce action.
	 */
	private const NONCE_ACTION = 'vew_site_kit_metabox';

	/**
	 * Constructor.
	 *
	 * @param KitStore $store Kit store.
	 */
	public function __construct( KitStore $store ) {
		$this->store = $store;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( 'add_meta_boxes', $this->add_meta_box( ... ) );
		add_action( 'save_post', $this->save( ... ), 10, 2 );
	}

	/**
	 * Register the meta box on supported post types.
	 *
	 * @return void
	 */
	public function add_meta_box(): void {
		foreach ( self::POST_TYPES as $post_type ) {
			add_meta_box(
				'vew_site_kit',
				__( 'Site Kit', 'vector-elementor-widgets' ),
				$this->render( ... ),
				$post_type,
				'side',
				'default'
			);
		}
	}

	/**
	 * Render the meta box.
	 *
	 * @param \WP_Post $post Post being edited.
	 *
	 * @return void
	 */
	public function render( \WP_Post $post ): void {
		$kits     = $this->store->all();
		$current  = (string) get_post_meta( (int) $post->ID, \Vector\ElementorWidgets\Elementor\SiteKit::META_KEY, true );
		$has_kit  = '' !== $current;
		$selected = $has_kit ? $current : '';

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- nonce_field is WP's own (safe) output.
		echo wp_nonce_field( self::NONCE_ACTION, '_vew_site_kit_meta_nonce', false, false );
		?>
		<p>
			<label for="vew_site_kit_select"><?php echo esc_html__( 'Site kit for this page', 'vector-elementor-widgets' ); ?></label>
		</p>
		<select id="vew_site_kit_select" name="vew_site_kit">
			<option value=""><?php echo esc_html__( '— Use global kit —', 'vector-elementor-widgets' ); ?></option>
			<?php foreach ( $kits as $slug => $kit ) : ?>
				<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $selected, $slug ); ?>><?php echo esc_html( $kit->label() ); ?> (<?php echo esc_html( $slug ); ?>)</option>
			<?php endforeach; ?>
		</select>
		<p class="description"><?php echo esc_html__( 'Leave as "Use global kit" to follow the global theme setting.', 'vector-elementor-widgets' ); ?></p>
		<?php
	}

	/**
	 * Persist the per-page kit on save.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 *
	 * @return void
	 */
	public function save( int $post_id, \WP_Post $post ): void {
		if ( ! in_array( $post->post_type, self::POST_TYPES, true ) ) {
			return;
		}
		if ( ! isset( $_POST['_vew_site_kit_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_vew_site_kit_meta_nonce'] ) ), self::NONCE_ACTION ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$slug = isset( $_POST['vew_site_kit'] ) ? sanitize_key( wp_unslash( $_POST['vew_site_kit'] ) ) : '';

		if ( '' === $slug ) {
			delete_post_meta( $post_id, \Vector\ElementorWidgets\Elementor\SiteKit::META_KEY );
			return;
		}
		if ( ! $this->store->has( $slug ) ) {
			return;
		}

		update_post_meta( $post_id, \Vector\ElementorWidgets\Elementor\SiteKit::META_KEY, $slug );
	}
}
