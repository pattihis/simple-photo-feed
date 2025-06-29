<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://gp-web.dev/
 * @since      1.0.0
 *
 * @package    Simple_Photo_Feed
 * @subpackage Simple_Photo_Feed/public
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * The frontend functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the frontend stylesheet and JavaScript.
 *
 * @package    Simple_Photo_Feed
 * @subpackage Simple_Photo_Feed/public
 * @author     George Pattichis <info@gp-web.dev>
 */
class Simple_Photo_Feed_Public {

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

		$css_ver = gmdate( 'ymd-Gis', filemtime( plugin_dir_path( __FILE__ ) . './css/simple-photo-feed-public.css' ) );

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/simple-photo-feed-public.css', array(), $css_ver, 'all' );

		$js_ver = gmdate( 'ymd-Gis', filemtime( plugin_dir_path( __FILE__ ) . './js/simple-photo-feed-public.js' ) );

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/simple-photo-feed-public.js', array( 'jquery' ), $js_ver, true );
	}

	/**
	 * Render the shortcode [simple-photo-feed].
	 *
	 * @param array $atts Shortcode attributes.
	 * @since    1.0.0
	 */
	public function display_simple_photo_feed( $atts ) {

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

		if ( ! isset( $atts['lightbox'] ) || empty( $atts['lightbox'] ) ) {
			$atts['lightbox'] = 'off';
		}

		ob_start();

		if ( ! is_admin() ) {
			include 'partials/simple-photo-feed-public-display.php';
		}

		return ob_get_clean();
	}
}
