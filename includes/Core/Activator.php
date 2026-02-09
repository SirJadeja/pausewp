<?php
/**
 * Plugin Activator.
 *
 * Fired during plugin activation.
 *
 * @package PauseWP\Core
 */

declare(strict_types=1);

namespace PauseWP\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Class Activator
 *
 * Handles plugin activation logic.
 */
final class Activator {

	/**
	 * Default plugin settings.
	 *
	 * @var array<string, mixed>
	 */
	private const DEFAULTS = [
		'is_enabled'           => false,
		'heading'              => 'We\'ll Be Right Back',
		'subheading'           => 'Our site is currently undergoing scheduled maintenance. Please check back soon.',
		'logo_id'              => 0,
		'logo_alt'             => '',
		'seo_title'            => 'Site Under Maintenance',
		'meta_description'     => 'We are currently performing scheduled maintenance. We will be back online shortly.',
		'bypass_roles'         => [ 'administrator' ],
		'whitelisted_ips'      => [],
		'cta_buttons'          => [],
		'countdown_enabled'    => false,
		'countdown_datetime'   => '',
		'auto_disable_enabled' => false,
	];

	/**
	 * Run activation logic.
	 *
	 * @return void
	 */
	public static function activate(): void {
		self::set_default_options();
		self::schedule_cleanup();

		// Flush rewrite rules for future custom endpoints.
		flush_rewrite_rules();
	}

	/**
	 * Set default plugin options if they don't exist.
	 *
	 * @return void
	 */
	private static function set_default_options(): void {
		$existing = get_option( 'pausewp_settings' );

		if ( false === $existing ) {
			// Option doesn't exist, create it with defaults.
			add_option( 'pausewp_settings', self::DEFAULTS, '', 'yes' );
		} else {
			// Merge existing with defaults (in case new settings added in updates).
			$merged = wp_parse_args( $existing, self::DEFAULTS );
			update_option( 'pausewp_settings', $merged, 'yes' );
		}
	}

	/**
	 * Schedule any cleanup tasks (placeholder for future use).
	 *
	 * @return void
	 */
	private static function schedule_cleanup(): void {
		// Future: Schedule cron jobs if needed.
	}

	/**
	 * Get default settings.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_defaults(): array {
		return self::DEFAULTS;
	}
}
