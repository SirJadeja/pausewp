<?php
/**
 * REST API Settings Controller.
 *
 * Handles reading and writing plugin settings via REST API.
 *
 * @package PauseWP\Api
 */

declare(strict_types=1);

namespace PauseWP\Api;

defined( 'ABSPATH' ) || exit;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Class Settings_Controller
 *
 * REST API controller for plugin settings.
 */
final class Settings_Controller extends WP_REST_Controller {

	/**
	 * REST API namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'pausewp/v1';

	/**
	 * REST API route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'settings';

	/**
	 * Option name in database.
	 *
	 * @var string
	 */
	private const OPTION_NAME = 'pausewp_settings';

	/**
	 * Register REST API routes.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_settings' ],
					'permission_callback' => [ $this, 'get_settings_permissions_check' ],
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'update_settings' ],
					'permission_callback' => [ $this, 'update_settings_permissions_check' ],
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);
	}

	/**
	 * Check if user can read settings.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error
	 */
	public function get_settings_permissions_check( WP_REST_Request $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to view settings.', 'pausewp' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}
		return true;
	}

	/**
	 * Check if user can update settings.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error
	 */
	public function update_settings_permissions_check( WP_REST_Request $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to update settings.', 'pausewp' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}
		return true;
	}

	/**
	 * Get plugin settings.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response
	 */
	public function get_settings( WP_REST_Request $request ): WP_REST_Response {
		$settings = get_option( self::OPTION_NAME, [] );
		$defaults = \PauseWP\Core\Activator::get_defaults();

		// Merge with defaults to ensure all keys exist.
		$settings = wp_parse_args( $settings, $defaults );

		return new WP_REST_Response( $settings, 200 );
	}

	/**
	 * Update plugin settings.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_settings( WP_REST_Request $request ) {
		$params   = $request->get_json_params();
		$current  = get_option( self::OPTION_NAME, [] );
		$defaults = \PauseWP\Core\Activator::get_defaults();

		// Sanitize incoming data.
		$sanitized = $this->sanitize_settings( $params, $defaults );

		// Merge with current settings.
		$updated = wp_parse_args( $sanitized, $current );

		// Save to database.
		$result = update_option( self::OPTION_NAME, $updated, 'yes' );

		if ( false === $result ) {
			// Check if values are the same (not an error).
			$existing = get_option( self::OPTION_NAME );
			if ( $existing === $updated ) {
				return new WP_REST_Response( $updated, 200 );
			}

			return new WP_Error(
				'rest_cannot_update',
				__( 'Failed to update settings.', 'pausewp' ),
				[ 'status' => 500 ]
			);
		}

		return new WP_REST_Response( $updated, 200 );
	}

	/**
	 * Sanitize settings input.
	 *
	 * @param array<string, mixed> $input   Raw input from request.
	 * @param array<string, mixed> $defaults Default values.
	 * @return array<string, mixed>
	 */
	private function sanitize_settings( array $input, array $defaults ): array {
		$sanitized = [];

		// Boolean: is_enabled.
		if ( isset( $input['is_enabled'] ) ) {
			$sanitized['is_enabled'] = (bool) $input['is_enabled'];
		}

		// Text: heading.
		if ( isset( $input['heading'] ) ) {
			$sanitized['heading'] = sanitize_text_field( $input['heading'] );
		}

		// HTML: subheading (allow limited tags).
		if ( isset( $input['subheading'] ) ) {
			$sanitized['subheading'] = wp_kses(
				$input['subheading'],
				[
					'a'      => [
						'href'   => [],
						'target' => [],
						'rel'    => [],
					],
					'br'     => [],
					'strong' => [],
					'b'      => [],
					'em'     => [],
					'i'      => [],
				]
			);
		}

		// Integer: logo_id.
		if ( isset( $input['logo_id'] ) ) {
			$sanitized['logo_id'] = absint( $input['logo_id'] );
		}

		// Text: logo_alt.
		if ( isset( $input['logo_alt'] ) ) {
			$sanitized['logo_alt'] = sanitize_text_field( $input['logo_alt'] );
		}

		// Text: seo_title.
		if ( isset( $input['seo_title'] ) ) {
			$sanitized['seo_title'] = sanitize_text_field( $input['seo_title'] );
		}

		// Text: meta_description.
		if ( isset( $input['meta_description'] ) ) {
			$sanitized['meta_description'] = sanitize_text_field( $input['meta_description'] );
		}

		// Array: bypass_roles.
		if ( isset( $input['bypass_roles'] ) && is_array( $input['bypass_roles'] ) ) {
			$sanitized['bypass_roles'] = array_map( 'sanitize_text_field', $input['bypass_roles'] );
		}

		// Array: whitelisted_ips.
		if ( isset( $input['whitelisted_ips'] ) && is_array( $input['whitelisted_ips'] ) ) {
			$sanitized['whitelisted_ips'] = array_filter(
				array_map(
					function ( $ip ) {
						$ip = sanitize_text_field( $ip );
						return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : null;
					},
					$input['whitelisted_ips']
				)
			);
		}

		// Array: cta_buttons.
		if ( isset( $input['cta_buttons'] ) && is_array( $input['cta_buttons'] ) ) {
			$sanitized['cta_buttons'] = array_map(
				function ( $button ) {
					return [
						'label' => sanitize_text_field( $button['label'] ?? '' ),
						'url'   => esc_url_raw( $button['url'] ?? '' ),
					];
				},
				$input['cta_buttons']
			);
		}

		return $sanitized;
	}

	/**
	 * Get item schema for settings.
	 *
	 * @return array
	 */
	public function get_item_schema(): array {
		return [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'pausewp-settings',
			'type'       => 'object',
			'properties' => [
				'is_enabled'       => [
					'type'        => 'boolean',
					'description' => __( 'Whether maintenance mode is enabled.', 'pausewp' ),
				],
				'heading'          => [
					'type'        => 'string',
					'description' => __( 'Main heading text.', 'pausewp' ),
				],
				'subheading'       => [
					'type'        => 'string',
					'description' => __( 'Subheading/description text (HTML allowed).', 'pausewp' ),
				],
				'logo_id'          => [
					'type'        => 'integer',
					'description' => __( 'Attachment ID for logo.', 'pausewp' ),
				],
				'logo_alt'         => [
					'type'        => 'string',
					'description' => __( 'Alt text for logo.', 'pausewp' ),
				],
				'seo_title'        => [
					'type'        => 'string',
					'description' => __( 'HTML title tag content.', 'pausewp' ),
				],
				'meta_description' => [
					'type'        => 'string',
					'description' => __( 'Meta description content.', 'pausewp' ),
				],
				'bypass_roles'     => [
					'type'        => 'array',
					'items'       => [ 'type' => 'string' ],
					'description' => __( 'User roles that can bypass maintenance.', 'pausewp' ),
				],
				'whitelisted_ips'  => [
					'type'        => 'array',
					'items'       => [ 'type' => 'string' ],
					'description' => __( 'IP addresses that can bypass maintenance.', 'pausewp' ),
				],
				'cta_buttons'      => [
					'type'        => 'array',
					'items'       => [
						'type'       => 'object',
						'properties' => [
							'label' => [ 'type' => 'string' ],
							'url'   => [ 'type' => 'string' ],
						],
					],
					'description' => __( 'Call-to-action buttons.', 'pausewp' ),
				],
			],
		];
	}
}
