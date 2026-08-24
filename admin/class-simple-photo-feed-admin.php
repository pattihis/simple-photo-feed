<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://gp-web.dev/
 * @since      1.0.0
 *
 * @package    Simple_Photo_Feed
 * @subpackage Simple_Photo_Feed/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Setup the admin part of the plugin
 *
 * Enqueue assets, register menu, settings, options and Ajax callbacks
 *
 * @since      1.0.0
 * @package    Simple_Photo_Feed
 * @subpackage Simple_Photo_Feed/includes
 * @author     George Pattichis <info@gp-web.dev>
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
	 * Loaded only on the plugin's own settings screen.
	 *
	 * @since    1.0.0
	 * @param    string $hook_suffix Current admin page hook.
	 */
	public function enqueue_styles( $hook_suffix ) {

		if ( 'toplevel_page_' . $this->plugin_name !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/simple-photo-feed-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * Loaded only on the plugin's own settings screen, so the AJAX nonce is
	 * not exposed on every wp-admin page.
	 *
	 * @since    1.0.0
	 * @param    string $hook_suffix Current admin page hook.
	 */
	public function enqueue_scripts( $hook_suffix ) {

		if ( 'toplevel_page_' . $this->plugin_name !== $hook_suffix ) {
			return;
		}

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/simple-photo-feed-admin.js', array(), $this->version, false );
		wp_localize_script(
			$this->plugin_name,
			'spf',
			array(
				'ajax_url'  => admin_url( 'admin-ajax.php' ),
				'theme_uri' => get_stylesheet_directory_uri(),
				'nonce'     => wp_create_nonce( 'simple-photo-feed-nonce' ),
			)
		);
	}

	/**
	 * Get the required capability for accessing the plugin
	 *
	 * @since  1.4.3
	 * @return string The required capability
	 */
	public function get_required_capability() {
		$options    = get_option( 'spf_main_settings', array() );
		$capability = isset( $options['required_capability'] ) ? $options['required_capability'] : 'manage_options';

		// Old 1.4.3 value was labeled "Editors" but edit_posts includes Contributors.
		if ( 'edit_posts' === $capability ) {
			$capability = 'edit_others_posts';
		}

		$valid_capabilities = $this->allowed_capabilities();
		if ( ! in_array( $capability, $valid_capabilities, true ) ) {
			$capability = 'manage_options';
		}

		/**
		 * Filter the required capability for accessing the plugin
		 *
		 * @since 1.4.3
		 * @param string $capability The required capability
		 */
		return apply_filters( 'spf_required_capability', $capability );
	}

	/**
	 * Register the admin menu
	 *
	 * @since    1.0.0
	 */
	public function simple_photo_feed_admin_menu() {

		add_menu_page(
			__( 'Simple Photo Feed Settings', 'simple-photo-feed' ),
			__( 'Photo Feed', 'simple-photo-feed' ),
			$this->get_required_capability(),
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
		// Handle custom form submission for non-administrators.
		if (
			! current_user_can( 'manage_options' )
			&& current_user_can( $this->get_required_capability() )
			&& isset( $_POST['spf_nonce'] )
			&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['spf_nonce'] ) ), 'spf_save_settings' )
		) {
			$this->handle_custom_form_submission();
		}

		include_once 'partials/simple-photo-feed-admin-display.php';
	}

	/**
	 * Capabilities that may be stored for access control.
	 *
	 * @since 1.4.4
	 * @return array
	 */
	public function allowed_capabilities() {
		return array( 'manage_options', 'edit_others_posts', 'publish_posts' );
	}

	/**
	 * Allowed feed refresh intervals.
	 *
	 * Derived from the same (filterable) list that builds the dropdown,
	 * so values added via the spf_cron_times filter remain saveable.
	 *
	 * @since 1.4.4
	 * @return array
	 */
	public function allowed_cron_times() {
		return array_map( 'strval', array_keys( $this->simple_photo_feed_cron_times() ) );
	}

	/**
	 * Handle form submission for non-administrators
	 *
	 * @since  1.4.3
	 */
	private function handle_custom_form_submission() {
		if ( ! current_user_can( $this->get_required_capability() ) ) {
			return;
		}

		$input   = isset( $_POST['spf_main_settings'] ) && is_array( $_POST['spf_main_settings'] ) ? wp_unslash( $_POST['spf_main_settings'] ) : array();
		$stored  = get_option( 'spf_main_settings', array() );
		$options = $this->merge_settings( is_array( $stored ) ? $stored : array(), $input, false );

		update_option( 'spf_main_settings', $options );
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

		register_setting( 'spf_main_settings', 'spf_main_settings', array( $this, 'sanitize_settings' ) );
	}

	/**
	 * Sanitize and validate settings before saving
	 *
	 * @since  1.4.3
	 * @param  array $input The input array from the form.
	 * @return array The sanitized input array.
	 */
	public function sanitize_settings( $input ) {
		$stored = get_option( 'spf_main_settings', array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}
		if ( ! is_array( $input ) ) {
			$input = array();
		}

		return $this->merge_settings( $stored, $input, current_user_can( 'manage_options' ) );
	}

	/**
	 * Merge submitted settings into stored options.
	 *
	 * @since 1.4.4
	 * @param array $stored  Existing options.
	 * @param array $input   Submitted values.
	 * @param bool  $is_admin Whether the current user may change access control.
	 * @return array
	 */
	private function merge_settings( $stored, $input, $is_admin ) {
		$output = $stored;
		unset( $output['app_secret'] );

		if ( isset( $input['cron_time'] ) && in_array( (string) $input['cron_time'], $this->allowed_cron_times(), true ) ) {
			$output['cron_time'] = (string) $input['cron_time'];
		}

		if ( isset( $input['token'] ) ) {
			$token = sanitize_text_field( $input['token'] );
			if ( '' !== $token || empty( $stored['token'] ) ) {
				$output['token'] = $token;
			}
		}

		if ( isset( $input['user_id'] ) ) {
			$output['user_id'] = sanitize_text_field( $input['user_id'] );
		}

		if ( isset( $input['auth'] ) ) {
			$auth           = sanitize_text_field( $input['auth'] );
			$output['auth'] = ( ! empty( $output['token'] ) && '' !== $auth ) ? '1' : '';
		} elseif ( empty( $output['token'] ) ) {
			$output['auth'] = '';
		}

		if ( $is_admin && isset( $input['required_capability'] ) ) {
			$cap = sanitize_text_field( $input['required_capability'] );
			if ( 'edit_posts' === $cap ) {
				$cap = 'edit_others_posts';
			}
			if ( in_array( $cap, $this->allowed_capabilities(), true ) ) {
				$output['required_capability'] = $cap;
			}
		}

		return $output;
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
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! current_user_can( $this->get_required_capability() ) || ! wp_verify_nonce( $nonce, 'simple-photo-feed-nonce' ) ) {
			wp_send_json_error( esc_html__( 'Unauthorized!', 'simple-photo-feed' ), 403 );
			return;
		}

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
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! current_user_can( $this->get_required_capability() ) || ! wp_verify_nonce( $nonce, 'simple-photo-feed-nonce' ) ) {
			wp_send_json_error( esc_html__( 'Unauthorized!', 'simple-photo-feed' ), 403 );
			return;
		}

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
