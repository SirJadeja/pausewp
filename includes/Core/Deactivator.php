<?php
/**
 * Plugin Deactivator.
 *
 * Fired during plugin deactivation.
 *
 * @package PauseWP\Core
 */

declare(strict_types=1);

namespace PauseWP\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Class Deactivator
 *
 * Handles plugin deactivation logic.
 */
final class Deactivator {

	/**
	 * Run deactivation logic.
	 *
	 * Note: We intentionally do NOT delete options here.
	 * Options are only deleted on uninstall (uninstall.php).
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		self::clear_transients();
		self::unschedule_cron();

		// Flush rewrite rules.
		flush_rewrite_rules();
	}

	/**
	 * Clear any plugin transients.
	 *
	 * @return void
	 */
	private static function clear_transients(): void {
		delete_transient( 'pausewp_cache' );
	}

	/**
	 * Unschedule any cron jobs.
	 *
	 * @return void
	 */
	private static function unschedule_cron(): void {
		$timestamp = wp_next_scheduled( 'pausewp_cleanup_cron' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'pausewp_cleanup_cron' );
		}
	}
}
