<?php
/**
 * Plugin Name:       WP Pause - Maintenance & Coming Soon
 * Plugin URI:        https://example.com/pausewp
 * Description:       Lightweight Maintenance Mode & Coming Soon page. Zero-JS frontend, countdown timer, proper 503 headers, developer-friendly.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            PauseWP Team
 * Author URI:        https://example.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       pausewp
 * Domain Path:       /languages
 *
 * @package PauseWP
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

/**
 * Plugin constants.
 */
define( 'PAUSEWP_VERSION', '1.0.0' );
define( 'PAUSEWP_FILE', __FILE__ );
define( 'PAUSEWP_PATH', plugin_dir_path( __FILE__ ) );
define( 'PAUSEWP_URL', plugin_dir_url( __FILE__ ) );
define( 'PAUSEWP_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Autoloader.
 */
if ( file_exists( PAUSEWP_PATH . 'vendor/autoload.php' ) ) {
	require_once PAUSEWP_PATH . 'vendor/autoload.php';
}

/**
 * Plugin activation hook.
 */
register_activation_hook( __FILE__, [ \PauseWP\Core\Activator::class, 'activate' ] );

/**
 * Plugin deactivation hook.
 */
register_deactivation_hook( __FILE__, [ \PauseWP\Core\Deactivator::class, 'deactivate' ] );

/**
 * Initialize the plugin on plugins_loaded.
 *
 * @return void
 */
function pausewp_init(): void {
	\PauseWP\Core\Plugin::get_instance()->run();
}
add_action( 'plugins_loaded', 'pausewp_init' );
