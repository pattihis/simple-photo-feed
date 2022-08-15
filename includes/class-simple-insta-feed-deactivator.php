<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://gp-web.dev/
 * @since      1.0.0
 *
 * @package    Simple_Insta_Feed
 * @subpackage Simple_Insta_Feed/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Simple_Insta_Feed
 * @subpackage Simple_Insta_Feed/includes
 * @author     George Pattihis <info@gp-web.dev>
 */
class Simple_Insta_Feed_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		wp_clear_scheduled_hook( 'simple_instagram_refresh_token' );

	}

}
