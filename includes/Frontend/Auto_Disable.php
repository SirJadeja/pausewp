<?php
/**
 * Auto Disable Handler.
 *
 * Handles automatic disabling of maintenance mode.
 *
 * @package PauseWP\Frontend
 */

declare(strict_types=1);

namespace PauseWP\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Class Auto_Disable
 *
 * Handles scheduled auto-disable of maintenance mode.
 */
final class Auto_Disable {

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'pausewp_auto_disable', [ $this, 'disable_maintenance_mode' ] );
	}

	/**
	 * Disable maintenance mode.
	 *
	 * This is triggered by WP Cron at the scheduled time.
	 *
	 * @return void
	 */
	public function disable_maintenance_mode(): void {
		$settings = get_option( 'pausewp_settings', [] );

		if ( ! empty( $settings['is_enabled'] ) ) {
			// Disable maintenance mode.
			$settings['is_enabled'] = false;
			update_option( 'pausewp_settings', $settings );

			// Optional: Send notification or log
			do_action( 'pausewp_auto_disabled', $settings );
		}
	}
}
