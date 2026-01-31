<?php
/**
 * Access Control - Bypass Logic.
 *
 * Determines if a user can bypass maintenance mode.
 *
 * @package PauseWP\Frontend
 */

declare(strict_types=1);

namespace PauseWP\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Class Access_Control
 *
 * Handles permission checks for bypassing maintenance mode.
 */
final class Access_Control {

	/**
	 * Plugin settings.
	 *
	 * @var array<string, mixed>
	 */
	private array $settings;

	/**
	 * Constructor.
	 *
	 * @param array<string, mixed> $settings Plugin settings.
	 */
	public function __construct( array $settings = [] ) {
		$this->settings = $settings ?: $this->get_settings();
	}

	/**
	 * Check if the current user/request can access the site.
	 *
	 * @return bool True if user can bypass maintenance mode.
	 */
	public function can_access(): bool {
		// Check role-based bypass.
		if ( $this->is_allowed_role() ) {
			return true;
		}

		// Check IP whitelist.
		if ( $this->is_whitelisted_ip() ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if current user has an allowed role.
	 *
	 * @return bool
	 */
	private function is_allowed_role(): bool {
		// User must be logged in.
		if ( ! is_user_logged_in() ) {
			return false;
		}

		$allowed_roles = $this->settings['bypass_roles'] ?? [ 'administrator' ];

		// Get current user.
		$user = wp_get_current_user();

		// Check if user has any of the allowed roles.
		foreach ( $allowed_roles as $role ) {
			if ( in_array( $role, (array) $user->roles, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if current IP is whitelisted.
	 *
	 * @return bool
	 */
	private function is_whitelisted_ip(): bool {
		$whitelisted_ips = $this->settings['whitelisted_ips'] ?? [];

		if ( empty( $whitelisted_ips ) ) {
			return false;
		}

		$current_ip = $this->get_client_ip();

		if ( empty( $current_ip ) ) {
			return false;
		}

		return in_array( $current_ip, $whitelisted_ips, true );
	}

	/**
	 * Get the client IP address.
	 *
	 * Accounts for proxies like Cloudflare.
	 *
	 * @return string
	 */
	private function get_client_ip(): string {
		// Priority order for IP detection.
		$headers = [
			'HTTP_CF_CONNECTING_IP', // Cloudflare.
			'HTTP_X_FORWARDED_FOR',  // Proxy.
			'HTTP_X_REAL_IP',        // Nginx proxy.
			'REMOTE_ADDR',           // Standard.
		];

		foreach ( $headers as $header ) {
			if ( ! empty( $_SERVER[ $header ] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) );

				// X-Forwarded-For can contain multiple IPs, take the first.
				if ( str_contains( $ip, ',' ) ) {
					$ips = explode( ',', $ip );
					$ip  = trim( $ips[0] );
				}

				// Validate IP format.
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}

		return '';
	}

	/**
	 * Get plugin settings from database.
	 *
	 * @return array<string, mixed>
	 */
	private function get_settings(): array {
		return get_option( 'pausewp_settings', [] );
	}
}
