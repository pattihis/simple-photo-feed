<?php
/**
 * Fired during plugin activation
 *
 * @link       https://gp-web.dev/
 * @since      1.0.0
 *
 * @package    Simple_Photo_Feed
 * @subpackage Simple_Photo_Feed/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Simple_Photo_Feed
 * @subpackage Simple_Photo_Feed/includes
 * @author     George Pattihis <info@gp-web.dev>
 */
class Simple_Photo_Feed_Activator {

	/**
	 * Initialize plugin settings.
	 *
	 * We register default options to WordPress if they do not exist.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		// Default settings for our plugin.
		$options = array(
			'token'      => '',
			'user_id'    => '',
			'auth'       => '',
			'cron_time'  => '3',
			'app_id'     => SPF_APP_ID,
			'app_secret' => SPF_APP_SECRET,
		);

		// Get existing options if exists.
		$existing = get_option( 'spf_main_settings' );
		// Check if valid settings exist.
		if ( $existing && is_array( $existing ) ) {
			foreach ( $options as $key => $value ) {
				if ( array_key_exists( $key, $existing ) ) {
					$options[ $key ] = $existing[ $key ];
				}
			}
		}

		// Update/create our settings.
		update_option( 'spf_main_settings', $options );

		// Setup cron job.
		if ( ! wp_next_scheduled( 'simple_photo_refresh_token' ) ) {
			wp_schedule_event( time(), 'weekly', 'simple_photo_refresh_token' );
		}

	}

}
