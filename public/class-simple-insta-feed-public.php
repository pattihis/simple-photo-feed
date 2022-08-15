<?php
/**
 * The frontend functionality of the plugin.
 *
 * @link       https://gp-web.dev/
 * @since      1.0.0
 *
 * @package    Simple_Insta_Feed
 * @subpackage Simple_Insta_Feed/public
 */

/**
 * The frontend functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the frontend stylesheet and JavaScript.
 *
 * @package    Simple_Insta_Feed
 * @subpackage Simple_Insta_Feed/public
 * @author     George Pattihis <info@gp-web.dev>
 */
class Simple_Insta_Feed_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the frontend side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		$css_ver = gmdate( 'ymd-Gis', filemtime( plugin_dir_path( __FILE__ ) . './css/simple-insta-feed-public.css' ) );

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/simple-insta-feed-public.css', array(), $css_ver, 'all' );

	}

	/**
	 * Render the shortcode [simple-insta-feed].
	 *
	 * @param array $atts Shortcode attributes.
	 * @since    1.0.0
	 */
	public function display_simple_insta_feed( $atts ) {

		if ( ! is_array( $atts ) ) {
			$atts = array();
		}

		if ( ! isset( $atts['view'] ) || empty( $atts['view'] ) ) {
			$atts['view'] = '12';
		}

		if ( ! isset( $atts['text'] ) || empty( $atts['text'] ) ) {
			$atts['text'] = 'off';
		}

		if ( ! isset( $atts['size'] ) || empty( $atts['size'] ) ) {
			$atts['size'] = 'large';
		}

		ob_start();

		if ( ! is_admin() ) {
			include 'partials/simple-insta-feed-public-display.php';
		}

		return ob_get_clean();
	}

}
