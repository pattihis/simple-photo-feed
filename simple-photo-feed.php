<?php
/**
 * Simple Photo Feed for Instagram
 *
 * @package           Simple_Photo_Feed
 * @author            George Pattichis
 * @copyright         2021 George Pattichis
 * @license           GPL-2.0-or-later
 * @link              https://profiles.wordpress.org/pattihis/
 * @since             1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       Simple Photo Feed for Instagram
 * Plugin URI:        https://wordpress.org/plugins/simple-photo-feed/
 * Description:       Simple Photo Feed for Instagram provides an easy way to connect to your Instagram account and display your photos in your WordPress site.
 * Version:           1.3.0
 * Requires at least: 5.3.0
 * Tested up to:      6.5.5
 * Requires PHP:      7.2
 * Author:            George Pattichis
 * Author URI:        https://profiles.wordpress.org/pattihis/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       simple-photo-feed
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version
 */
define( 'SPF_VERSION', '1.3.0' );

/**
 * Plugin's basename
 */
define( 'SPF_BASENAME', plugin_basename( __FILE__ ) );

/**
 * The code that runs during plugin activation
 */
function activate_simple_photo_feed() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-simple-photo-feed-activator.php';
	Simple_Photo_Feed_Activator::activate();
}

/**
 * The code that runs during plugin deactivation
 */
function deactivate_simple_photo_feed() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-simple-photo-feed-deactivator.php';
	Simple_Photo_Feed_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_simple_photo_feed' );
register_deactivation_hook( __FILE__, 'deactivate_simple_photo_feed' );

/**
 * The core plugin class
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-simple-photo-feed.php';

/**
 * Begins execution of the plugin
 *
 * @since    1.0.0
 */
function run_simple_photo_feed() {

	$plugin = new Simple_Photo_Feed();
	$plugin->run();
}
run_simple_photo_feed();
