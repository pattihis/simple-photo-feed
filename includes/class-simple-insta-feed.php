<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * frontend side of the site and the admin area.
 *
 * @link       https://gp-web.dev/
 * @since      1.0.0
 *
 * @package    Simple_Insta_Feed
 * @subpackage Simple_Insta_Feed/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * frontend site hooks.Also maintains the unique identifier of this plugin
 * as well as the current version of the plugin.
 *
 * @since      1.0.0
 * @package    Simple_Insta_Feed
 * @subpackage Simple_Insta_Feed/includes
 * @author     George Pattihis <info@gp-web.dev>
 */
class Simple_Insta_Feed {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Simple_Insta_Feed_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the frontend side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'SIF_VERSION' ) ) {
			$this->version = SIF_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'simple-insta-feed';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		$options = get_option( 'sif_main_settings', array() );

		$options['token']      = $options['token'] ?: ''; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
		$options['cron_time']  = $options['cron_time'] ?: ''; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
		$options['app_id']     = SIF_APP_ID;
		$options['app_secret'] = SIF_APP_SECRET;

		update_option( 'sif_main_settings', $options );

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-simple-insta-feed-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-simple-insta-feed-i18n.php';

		/**
		 * The class responsible to fetch and cache the Instagram Feed
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-simple-insta-feed-api.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-simple-insta-feed-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the frontend
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-simple-insta-feed-public.php';

		$this->loader = new Simple_Insta_Feed_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Simple_Insta_Feed_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Simple_Insta_Feed_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Simple_Insta_Feed_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_init', $plugin_admin, 'simple_insta_feed_register_settings' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'simple_insta_feed_admin_menu' );
		$this->loader->add_filter( 'cron_schedules', $plugin_admin, 'simple_insta_feed_cron_schedule' );
		$this->loader->add_filter( 'plugin_action_links', $plugin_admin, 'simple_insta_feed_plugin_links', 10, 2 );

		$this->loader->add_action( 'wp_ajax_sif_disconnect_user', $plugin_admin, 'sif_disconnect_user' );
		$this->loader->add_action( 'wp_ajax_sif_clear_insta_cache', $plugin_admin, 'sif_clear_insta_cache' );

		$plugin_api = new Simple_Insta_Feed_Api();

		$this->loader->add_action( 'simple_instagram_refresh_token', $plugin_api, 'sif_refresh_long_lived_token' );
		$this->loader->add_action( 'update_option_sif_main_settings', $plugin_api, 'sif_setup_cron_job', 10, 2 );
		$this->loader->add_action( 'simple_instagram_update_feed', $plugin_api, 'sif_refresh_feed' );

	}

	/**
	 * Register all of the hooks related to the frontend functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Simple_Insta_Feed_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );

		$this->loader->add_shortcode( 'simple-insta-feed', $plugin_public, 'display_simple_insta_feed' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Simple_Insta_Feed_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
