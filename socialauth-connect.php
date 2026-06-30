<?php
/**
 * Plugin Name:       SocialAuth Connect
 * Plugin URI:        https://yoursite.com/socialauth-connect
 * Description:       Modular social authentication with Google OAuth2/OIDC. Extensible to Facebook, X, Email, and more.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.1
 * Author:            Your Name
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       socialauth-connect
 * Domain Path:       /languages
 *
 * @package SocialAuthConnect
 */

defined( 'ABSPATH' ) || exit;

// Plugin constants
define( 'SOCIALAUTH_VERSION', '1.0.0' );
define( 'SOCIALAUTH_PLUGIN_FILE', __FILE__ );
define( 'SOCIALAUTH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SOCIALAUTH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SOCIALAUTH_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Autoloader
require_once SOCIALAUTH_PLUGIN_DIR . 'vendor/autoload.php';

// Activation / Deactivation hooks
register_activation_hook( __FILE__, [ 'SocialAuth\Activator', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'SocialAuth\Deactivator', 'deactivate' ] );

// Bootstrap
require_once SOCIALAUTH_PLUGIN_DIR . 'includes/class-socialauth-core.php';

function socialauth_run(): void {
    $plugin = new \SocialAuth\Core();
    $plugin->run();
}

socialauth_run();
