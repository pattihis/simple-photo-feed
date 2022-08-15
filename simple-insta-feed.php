<?php
/**
 * Simple Insta Feed
 *
 * @package           Simple_Insta_Feed
 * @author            George Pattihis
 * @copyright         2021 George Pattihis
 * @license           GPL-2.0-or-later
 * @link              https://profiles.wordpress.org/pattihis/
 * @since             1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       Simple Insta Feed
 * Plugin URI:        https://wordpress.org/plugins/simple-insta-feed/
 * Description:       Simple Insta Feed provides an easy way to connect to your Instagram account and display your photos in your WordPress site.
 * Version:           1.0.0
 * Requires at least: 5.0
 * Tested up to:      6.0
 * Requires PHP:      5.6
 * Author:            George Pattihis
 * Author URI:        https://profiles.wordpress.org/pattihis/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       simple-insta-feed
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version
 */
define( 'SIF_VERSION', '1.0.0' );

/**
 * Plugin's basename
 */
define( 'SIF_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Our Instagram App ID
 */
define( 'SIF_APP_ID', '441876417835861' );

/**
 * Our Instagram App Secret
 */
define( 'SIF_APP_SECRET', 'a9bbb3ad41c0a797f5e5c880f7edc360' );

/**
 * The code that runs during plugin activation
 */
function activate_simple_insta_feed() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-simple-insta-feed-activator.php';
	Simple_Insta_Feed_Activator::activate();
}

/**
 * The code that runs during plugin deactivation
 */
function deactivate_simple_insta_feed() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-simple-insta-feed-deactivator.php';
	Simple_Insta_Feed_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_simple_insta_feed' );
register_deactivation_hook( __FILE__, 'deactivate_simple_insta_feed' );

/**
 * The core plugin class
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-simple-insta-feed.php';

/**
 * Begins execution of the plugin
 *
 * @since    1.0.0
 */
function run_simple_insta_feed() {

	$plugin = new Simple_Insta_Feed();
	$plugin->run();

}
run_simple_insta_feed();
