<?php
/**
 * Frontend Engine - Content Blocking.
 *
 * Intercepts requests and displays the maintenance page.
 *
 * @package PauseWP\Frontend
 */

declare(strict_types=1);

namespace PauseWP\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Class Engine
 *
 * The heart of the plugin - blocks frontend access when maintenance is active.
 */
final class Engine {

	/**
	 * Handle the incoming request.
	 *
	 * Hooked to `template_redirect` with priority 1.
	 *
	 * @return void
	 */
	public function handle_request(): void {
		// Bail early for admin, AJAX, cron, and REST API requests.
		if ( $this->should_bail_early() ) {
			return;
		}

		// Check if maintenance mode is enabled.
		if ( ! $this->is_maintenance_enabled() ) {
			return;
		}

		// Check if user can bypass maintenance mode.
		$access_control = new Access_Control();
		if ( $access_control->can_access() ) {
			return;
		}

		// Block access and show maintenance page.
		$this->render_maintenance_page();
	}

	/**
	 * Check if we should bail early (admin, ajax, cron, etc.).
	 *
	 * @return bool
	 */
	private function should_bail_early(): bool {
		// Allow WordPress admin.
		if ( is_admin() ) {
			return true;
		}

		// Allow AJAX requests.
		if ( wp_doing_ajax() ) {
			return true;
		}

		// Allow Cron requests.
		if ( wp_doing_cron() ) {
			return true;
		}

		// Allow login page.
		if ( $this->is_login_page() ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if we're on the login page.
	 *
	 * @return bool
	 */
	private function is_login_page(): bool {
		$pagenow = $GLOBALS['pagenow'] ?? '';
		return 'wp-login.php' === $pagenow || 'wp-register.php' === $pagenow;
	}

	/**
	 * Check if maintenance mode is enabled.
	 *
	 * @return bool
	 */
	private function is_maintenance_enabled(): bool {
		$settings = get_option( 'pausewp_settings', [] );
		return ! empty( $settings['is_enabled'] );
	}

	/**
	 * Render the maintenance page and exit.
	 *
	 * @return void
	 */
	private function render_maintenance_page(): void {
		// Set HTTP 503 status.
		status_header( 503 );

		// Prevent caching.
		nocache_headers();

		// Set Retry-After header (1 hour).
		header( 'Retry-After: 3600' );

		// Get settings to pass to template.
		$settings = get_option( 'pausewp_settings', [] );

		// Load the maintenance template.
		$template_path = PAUSEWP_PATH . 'templates/maintenance.php';

		if ( file_exists( $template_path ) ) {
			include $template_path;
		} else {
			// Fallback if template is missing.
			wp_die(
				esc_html__( 'Maintenance Mode Active - Site is temporarily unavailable.', 'pausewp' ),
				esc_html__( 'Maintenance', 'pausewp' ),
				[
					'response'  => 503,
					'back_link' => false,
				]
			);
		}

		exit;
	}
}
