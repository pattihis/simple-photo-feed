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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Simple_Photo_Feed
 * @subpackage Simple_Photo_Feed/includes
 * @author     George Pattichis <info@gp-web.dev>
 */
class Simple_Photo_Feed_Activator {

	/**
	 * Initialize plugin settings.
	 *
	 * Merge defaults into existing options. Do not wipe a connected account.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		$defaults = array(
			'token'               => '',
			'user_id'             => '',
			'auth'                => '',
			'cron_time'           => '3',
			'app_id'              => '',
			'required_capability' => 'manage_options',
		);

		$existing = get_option( 'spf_main_settings' );
		if ( is_array( $existing ) ) {
			foreach ( $defaults as $key => $value ) {
				if ( ! array_key_exists( $key, $existing ) ) {
					$existing[ $key ] = $value;
				}
			}
			if ( isset( $existing['required_capability'] ) && 'edit_posts' === $existing['required_capability'] ) {
				$existing['required_capability'] = 'edit_others_posts';
			}
			unset( $existing['app_secret'] );
			update_option( 'spf_main_settings', $existing );
		} else {
			update_option( 'spf_main_settings', $defaults );
		}

		if ( ! wp_next_scheduled( 'simple_photo_refresh_token' ) ) {
			wp_schedule_event( time(), 'weekly', 'simple_photo_refresh_token' );
		}

		// Restore the feed-refresh cron for already-connected sites (deactivation clears it).
		$options = is_array( $existing ) ? $existing : $defaults;
		if ( ! empty( $options['auth'] ) && ! wp_next_scheduled( 'simple_photo_update_feed' ) ) {
			$map      = array(
				1  => 'hourly',
				3  => '3h',
				6  => '6h',
				12 => 'twicedaily',
				24 => 'daily',
			);
			$interval = isset( $map[ (int) $options['cron_time'] ] ) ? $map[ (int) $options['cron_time'] ] : '3h';
			wp_schedule_event( time(), $interval, 'simple_photo_update_feed' );
		}
	}
}
