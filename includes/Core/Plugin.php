<?php
/**
 * Main Plugin bootstrap class.
 *
 * @package PauseWP\Core
 */

declare(strict_types=1);

namespace PauseWP\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Class Plugin
 *
 * Singleton class that bootstraps the entire plugin.
 */
final class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static ?Plugin $instance = null;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private string $version;

	/**
	 * Private constructor to prevent direct instantiation.
	 */
	private function __construct() {
		$this->version = PAUSEWP_VERSION;
	}

	/**
	 * Get singleton instance.
	 *
	 * @return Plugin
	 */
	public static function get_instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Run the plugin - register all hooks.
	 *
	 * @return void
	 */
	public function run(): void {
		$this->load_textdomain();
		$this->init_hooks();
	}

	/**
	 * Load plugin textdomain for translations.
	 *
	 * @return void
	 */
	private function load_textdomain(): void {
		load_plugin_textdomain(
			'pausewp',
			false,
			dirname( PAUSEWP_BASENAME ) . '/languages'
		);
	}

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	private function init_hooks(): void {
		// Frontend: Intercept requests for maintenance mode.
		$engine = new \PauseWP\Frontend\Engine();
		add_action( 'template_redirect', [ $engine, 'handle_request' ], 1 );

		// Frontend: Auto-disable handler.
		$auto_disable = new \PauseWP\Frontend\Auto_Disable();
		$auto_disable->init();

		// REST API: Register settings endpoints.
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );

		// Admin: Register settings page.
		if ( is_admin() ) {
			$settings_page = new \PauseWP\Admin\Settings_Page();
			$settings_page->register();
		}
	}

	/**
	 * Register REST API routes.
	 *
	 * @return void
	 */
	public function register_rest_routes(): void {
		$settings_controller = new \PauseWP\Api\Settings_Controller();
		$settings_controller->register_routes();
	}

	/**
	 * Get plugin version.
	 *
	 * @return string
	 */
	public function get_version(): string {
		return $this->version;
	}

	/**
	 * Prevent cloning.
	 */
	private function __clone() {}

	/**
	 * Prevent unserialization.
	 *
	 * @throws \Exception Always throws to prevent unserialization.
	 */
	public function __wakeup(): void {
		throw new \Exception( 'Cannot unserialize singleton.' );
	}
}
