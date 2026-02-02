<?php
/**
 * Admin Settings Page.
 *
 * Registers the admin menu and enqueues the React app.
 *
 * @package PauseWP\Admin
 */

declare(strict_types=1);

namespace PauseWP\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Settings_Page
 *
 * Handles the admin settings page.
 */
final class Settings_Page {

	/**
	 * Menu slug.
	 *
	 * @var string
	 */
	public const MENU_SLUG = 'pausewp';

	/**
	 * Script handle.
	 *
	 * @var string
	 */
	private const SCRIPT_HANDLE = 'pausewp-admin-app';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Add the admin menu page.
	 *
	 * @return void
	 */
	public function add_menu_page(): void {
		add_menu_page(
			__( 'Maintenance Mode', 'pausewp' ),
			__( 'Maintenance Mode', 'pausewp' ),
			'manage_options',
			self::MENU_SLUG,
			[ $this, 'render_page' ],
			'dashicons-lock',
			80
		);
	}

	/**
	 * Render the admin page (React root).
	 *
	 * @return void
	 */
	public function render_page(): void {
		?>
		<div class="wrap pausewp-wrap">
			<div id="pausewp-admin-app"></div>
		</div>
		<?php
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_assets( string $hook_suffix ): void {
		// Only load on our settings page.
		if ( 'toplevel_page_' . self::MENU_SLUG !== $hook_suffix ) {
			return;
		}

		$asset_file = PAUSEWP_PATH . 'assets/build/index.asset.php';

		// Check if build exists.
		if ( ! file_exists( $asset_file ) ) {
			$this->show_build_notice();
			return;
		}

		$asset = require $asset_file;

		// Enqueue the main script.
		wp_enqueue_script(
			self::SCRIPT_HANDLE,
			PAUSEWP_URL . 'assets/build/index.js',
			$asset['dependencies'] ?? [ 'wp-element', 'wp-components', 'wp-api-fetch', 'wp-i18n' ],
			$asset['version'] ?? PAUSEWP_VERSION,
			true
		);

		// Enqueue styles.
		$style_file = PAUSEWP_PATH . 'assets/build/index.css';
		if ( file_exists( $style_file ) ) {
			wp_enqueue_style(
				self::SCRIPT_HANDLE,
				PAUSEWP_URL . 'assets/build/index.css',
				[ 'wp-components' ],
				$asset['version'] ?? PAUSEWP_VERSION
			);
		}

		// Enqueue WordPress components styles.
		wp_enqueue_style( 'wp-components' );

		// Enqueue media library for logo upload.
		wp_enqueue_media();

		// Localize script with data.
		wp_localize_script(
			self::SCRIPT_HANDLE,
			'pausewpAdmin',
			[
				'restUrl'   => rest_url( 'pausewp/v1/' ),
				'nonce'     => wp_create_nonce( 'wp_rest' ),
				'version'   => PAUSEWP_VERSION,
				'adminUrl'  => admin_url(),
				'siteUrl'   => home_url(),
				'allRoles'  => $this->get_all_roles(),
			]
		);

		// Set script translations.
		wp_set_script_translations( self::SCRIPT_HANDLE, 'pausewp', PAUSEWP_PATH . 'languages' );
	}

	/**
	 * Show notice when build files are missing.
	 *
	 * @return void
	 */
	private function show_build_notice(): void {
		add_action(
			'admin_notices',
			function () {
				?>
				<div class="notice notice-warning">
					<p>
						<strong><?php esc_html_e( 'WP Pause:', 'pausewp' ); ?></strong>
						<?php esc_html_e( 'Admin assets not found. Please run', 'pausewp' ); ?>
						<code>npm run build</code>
						<?php esc_html_e( 'from the plugin directory.', 'pausewp' ); ?>
					</p>
				</div>
				<?php
			}
		);
	}

	/**
	 * Get all available user roles.
	 *
	 * @return array<string, string>
	 */
	private function get_all_roles(): array {
		$roles     = wp_roles()->roles;
		$role_list = [];

		foreach ( $roles as $slug => $role ) {
			$role_list[ $slug ] = translate_user_role( $role['name'] );
		}

		return $role_list;
	}
}
