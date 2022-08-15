<?php
/**
 * Class Simple_Photo_Feed_Admin
 *
 * @link       https://gp-web.dev/
 * @since      1.0.0
 *
 * @package    Simple_Photo_Feed
 * @subpackage Simple_Photo_Feed/includes
 */

/**
 * Setup the admin part of the plugin
 *
 * Enqueue assets, register menu, settings, options and Ajax callbacks
 *
 * @since      1.0.0
 * @package    Simple_Photo_Feed
 * @subpackage Simple_Photo_Feed/includes
 * @author     George Pattihis <info@gp-web.dev>
 */
class Simple_Photo_Feed_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 * @param   string $plugin_name The name of this plugin.
	 * @param   string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/simple-photo-feed-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/simple-photo-feed-admin.js', array(), $this->version, false );
		wp_localize_script(
			$this->plugin_name,
			'spf',
			array(
				'ajax_url'  => admin_url( 'admin-ajax.php' ),
				'theme_uri' => get_stylesheet_directory_uri(),
			)
		);

	}

	/**
	 * Register the admin menu
	 *
	 * @since    1.0.0
	 */
	public function simple_photo_feed_admin_menu() {

		add_menu_page(
			__( 'Simple Photo Feed Settings', 'simple-photo-feed' ),
			__( 'Simple Photo Feed', 'simple-photo-feed' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'simple_photo_feed_admin_display' ),
			'dashicons-instagram',
			25
		);

	}

	/**
	 * Render the admin menu page content
	 *
	 * @since  1.0.0
	 */
	public function simple_photo_feed_admin_display() {
		include_once 'partials/simple-photo-feed-admin-display.php';
	}

	/**
	 * Registering our settings options using WordPress settings API.
	 *
	 * @since  1.0.0
	 * @access public
	 * @uses   hooks  register_setting Hook to register options in db.
	 *
	 * @return void
	 */
	public function simple_photo_feed_register_settings() {

		register_setting( 'spf_main_settings', 'spf_main_settings' );

	}

	/**
	 * Show custom links in Plugins Page
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  array $links Default Links.
	 * @param  array $file Plugin's root filepath.
	 * @return array Links list to display in plugins page.
	 */
	public function simple_photo_feed_plugin_links( $links, $file ) {

		if ( SPF_BASENAME === $file ) {
			$spf_links = '<a href="' . get_admin_url() . 'admin.php?page=simple-photo-feed" title="Plugin Options">' . __( 'Settings', 'simple-photo-feed' ) . '</a>';
			$spf_visit = '<a href="https://gp-web.dev/" title="Contact" target="_blank" >' . __( 'Contact', 'simple-photo-feed' ) . '</a>';
			array_unshift( $links, $spf_visit );
			array_unshift( $links, $spf_links );
		}

		return $links;

	}

	/**
	 * Registering our custom cron recurrences
	 *
	 * Supported by default: "hourly", "twicedaily", "daily", "weekly"
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  array $schedules Default cron recurrences.
	 * @return array Custom cron recurrences.
	 */
	public function simple_photo_feed_cron_schedule( $schedules ) {

		if ( ! isset( $schedules['3h'] ) ) {
			$schedules['3h'] = array(
				'interval' => 3 * HOUR_IN_SECONDS,
				'display'  => __( 'Once every 3 hours' ),
			);
		}
		if ( ! isset( $schedules['6h'] ) ) {
			$schedules['6h'] = array(
				'interval' => 6 * HOUR_IN_SECONDS,
				'display'  => __( 'Once every 6 hours' ),
			);
		}
		return $schedules;

	}

	/**
	 * Return the allowed cron recurrences
	 *
	 * @since  1.0.0
	 * @return array Allowed cron recurrences
	 */
	public function simple_photo_feed_cron_times() {

		$times = array(
			1  => __( 'Every 1h', 'simple-photo-feed' ),
			3  => __( 'Every 3h', 'simple-photo-feed' ),
			6  => __( 'Every 6h', 'simple-photo-feed' ),
			12 => __( 'Every 12h', 'simple-photo-feed' ),
			24 => __( 'Every 24h', 'simple-photo-feed' ),
		);

		return (array) apply_filters( 'spf_cron_times', $times );

	}

	/**
	 * Ajax callback to disconnect a user
	 *
	 * @since   1.0.0
	 */
	public function spf_disconnect_user() {
		$options = get_option( 'spf_main_settings', array() );

		$options['token']   = '';
		$options['user_id'] = '';
		$options['auth']    = '';

		update_option( 'spf_main_settings', $options );

		if ( $this->spf_delete_transients() ) {
			$message = esc_html__( 'User Disconnected. Redirecting...', 'simple-photo-feed' );
		} else {
			$message = esc_html__( 'Error! Something went wrong...', 'simple-photo-feed' );
		}
		echo wp_json_encode( $message );
		die();
	}

	/**
	 * Ajax callback to clear cached queries
	 *
	 * @since   1.0.0
	 */
	public function spf_clear_feed_cache() {

		if ( $this->spf_delete_transients() ) {
			$success = esc_html__( 'Cache Cleared!', 'simple-photo-feed' );
			echo wp_json_encode( $success );
		} else {
			echo 'Error!';
		}
		die();
	}

	/**
	 * Deleted all our media transients
	 *
	 * @since   1.0.0
	 */
	public function spf_delete_transients() {

		$times = $this->simple_photo_feed_cron_times();
		foreach ( $times as $k => $v ) {
			delete_transient( 'spf_get_media_' . $k );
		}
		return true;
	}

}
