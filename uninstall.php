<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://gp-web.dev/
 * @since      1.0.0
 *
 * @package    Simple_Photo_Feed
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// If we are on a multisite installation clean up all subsites.
if ( is_multisite() ) {

	foreach ( get_sites( array( 'fields' => 'ids' ) ) as $blogid ) {
		switch_to_blog( $blogid );
		simple_photo_feed_cleanup();
		restore_current_blog();
	}
} else {
	simple_photo_feed_cleanup();
}

/**
 * Delete plugin options and any DB tables.
 *
 * @since  1.0.0
 * @return void
 */
function simple_photo_feed_cleanup() {

	// Plugin options.
	$options = array(
		'spf_main_settings',
		'spf_version_no',
	);

	// Loop through each option.
	foreach ( $options as $option ) {
		delete_option( $option );
	}

	// Delete our transients.
	global $wpdb;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$trans = $wpdb->get_results( "SELECT option_name AS name FROM $wpdb->options WHERE option_name LIKE '\_transient\_spf\_get\_media\_%'" );
	foreach ( $trans as $t ) {
		// option_name is "_transient_spf_get_media_X"; delete_transient() needs "spf_get_media_X".
		delete_transient( substr( $t->name, strlen( '_transient_' ) ) );
	}

	// Clear our cron jobs.
	wp_clear_scheduled_hook( 'simple_photo_update_feed' );
	wp_clear_scheduled_hook( 'simple_photo_refresh_token' );
}
