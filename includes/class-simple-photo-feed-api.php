<?php
/**
 * Class Simple_Photo_Feed_Api
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
 * Connect to Instagram's API.
 *
 * Token exchange happens on gp-web.dev. This class never ships or uses the app secret.
 *
 * @since      1.0.0
 * @package    Simple_Photo_Feed
 * @subpackage Simple_Photo_Feed/includes
 * @author     George Pattichis <info@gp-web.dev>
 */
class Simple_Photo_Feed_Api {

	/**
	 * Public Meta app ID used for the shared easy-connect flow.
	 *
	 * @since 1.4.4
	 */
	const APP_ID = '3669671916678176';

	/**
	 * API endpoints.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $api
	 */
	public $api;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 */
	public function __construct() {

		$this->api = array(
			'authorize_url' => 'https://www.instagram.com/oauth/authorize',
			'response_type' => 'code',
			'scope'         => 'instagram_business_basic',
			'root'          => 'https://graph.instagram.com/',
			'redirect_uri'  => 'https://gp-web.dev/instagram-authorize/',
			'redeem_url'    => 'https://gp-web.dev/wp-json/sic/v1/redeem',
			'refresh_token' => 'https://graph.instagram.com/refresh_access_token/',
		);
	}

	/**
	 * Public app ID for the authorize URL.
	 *
	 * @since 1.4.4
	 * @return string
	 */
	public function get_app_id() {
		$options = get_option( 'spf_main_settings', array() );
		if ( ! empty( $options['app_id'] ) ) {
			return $options['app_id'];
		}

		return self::APP_ID;
	}

	/**
	 * Create the authorization URL.
	 *
	 * @since   1.0.0
	 */
	public function spf_get_auth_url_personal() {

		$spf_nonce = wp_create_nonce( 'spf_nonce' );

		// http_build_query() RFC 3986-encodes every value; add_query_arg() would not.
		$args = array(
			'client_id'     => $this->get_app_id(),
			'redirect_uri'  => $this->api['redirect_uri'],
			'scope'         => $this->api['scope'],
			'response_type' => $this->api['response_type'],
			'state'         => admin_url( 'admin.php?page=simple-photo-feed&' ) . 'spf_flow=ticket&spf_nonce=' . $spf_nonce,
		);

		return $this->api['authorize_url'] . '?' . http_build_query( $args, '', '&', PHP_QUERY_RFC3986 );
	}

	/**
	 * Redeem a one-time OAuth ticket from the callback host.
	 *
	 * @since 1.4.4
	 * @param string $ticket Ticket id from the authorize return URL.
	 * @return array|false { token, user_id } or false.
	 */
	public function spf_redeem_oauth_ticket( $ticket ) {
		$ticket = sanitize_text_field( $ticket );
		if ( ! preg_match( '/^[a-f0-9]{64}$/', $ticket ) ) {
			return false;
		}

		$payload = array(
			'timeout'   => 30,
			'sslverify' => true,
			'headers'   => array(
				'Content-Type' => 'application/json',
			),
			'body'      => wp_json_encode(
				array(
					'ticket' => $ticket,
					'site'   => wp_parse_url( admin_url(), PHP_URL_HOST ),
				)
			),
		);

		$response = wp_remote_post( $this->api['redeem_url'], $payload );
		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			$response = wp_remote_post( 'https://gp-web.dev/?rest_route=/sic/v1/redeem', $payload );
		}

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $body ) || empty( $body['token'] ) ) {
			return false;
		}

		return array(
			'token'   => sanitize_text_field( $body['token'] ),
			'user_id' => isset( $body['user_id'] ) ? sanitize_text_field( (string) $body['user_id'] ) : '',
		);
	}

	/**
	 * Refresh the long-lived access token and persist it.
	 *
	 * Instagram refresh does not need the app secret.
	 *
	 * @since   1.0.0
	 * @return  object|false
	 */
	public function spf_refresh_long_lived_token() {

		$options = get_option( 'spf_main_settings', array() );
		if ( empty( $options['token'] ) ) {
			return false;
		}

		$response = wp_remote_post(
			$this->api['refresh_token'],
			array(
				'timeout'   => 30,
				'sslverify' => true,
				'body'      => array(
					'grant_type'   => 'ig_refresh_token',
					'access_token' => $options['token'],
				),
			)
		);

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			$response = wp_remote_get(
				add_query_arg(
					array(
						'grant_type'   => 'ig_refresh_token',
						'access_token' => $options['token'],
					),
					$this->api['refresh_token']
				),
				array(
					'timeout'   => 30,
					'sslverify' => true,
				)
			);
		}

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ) );
		if ( ! is_object( $body ) || empty( $body->access_token ) ) {
			return false;
		}

		$options['token'] = sanitize_text_field( $body->access_token );
		if ( isset( $body->expires_in ) ) {
			$options['token_expires'] = time() + (int) $body->expires_in;
		}
		update_option( 'spf_main_settings', $options );

		return $body;
	}

	/**
	 * Get account info from Instagram API.
	 *
	 * @since   1.0.0
	 * @return  object|false
	 */
	public function spf_get_account() {

		$options = get_option( 'spf_main_settings', array() );
		if ( empty( $options['token'] ) ) {
			return false;
		}

		$body = $this->get_json(
			$this->api['root'] . 'me',
			array(
				'fields'       => 'account_type,id,username,media_count',
				'access_token' => $options['token'],
			)
		);

		if ( $body ) {
			return $body;
		}

		return $this->get_json(
			$this->api['root'] . 'me',
			array(
				'fields'       => 'account_type,id,username',
				'access_token' => $options['token'],
			)
		);
	}

	/**
	 * Save connection details and refresh the admin screen.
	 *
	 * @param   string $user_id Instagram User ID.
	 * @param   string $auth Whether we've been authorized.
	 * @param   string $token Long-lived access token.
	 * @since   1.0.0
	 * @return  string
	 */
	public function spf_connect_user( $user_id, $auth, $token ) {

		if ( '' === (string) $token ) {
			return '';
		}

		$options = get_option( 'spf_main_settings', array() );

		$options['user_id'] = $user_id;
		$options['auth']    = $auth;
		$options['token']   = $token;
		update_option( 'spf_main_settings', $options );

		$admin = new Simple_Photo_Feed_Admin( 'simple-photo-feed', SPF_VERSION );
		$admin->spf_delete_transients();

		$refresh = '<script type="text/javascript">
			setTimeout(() => {
				window.history.replaceState({}, document.title, window.location.pathname + "?page=simple-photo-feed");
				window.location.reload(true);
			}, "100");
		</script>';

		return $refresh;
	}

	/**
	 * Get Media (max 100) from Instagram API and save to cache.
	 *
	 * @since   1.0.0
	 * @return  array
	 */
	public function spf_get_media() {

		$options = get_option( 'spf_main_settings', array() );
		if ( empty( $options['token'] ) ) {
			return array();
		}

		$cron_time = ! empty( $options['cron_time'] ) ? $options['cron_time'] : '3';
		$data      = get_transient( 'spf_get_media_' . $cron_time );

		if ( false === $data || empty( $data ) || ! is_array( $data ) ) {
			$data = array();
			$body = $this->get_json(
				$this->api['root'] . 'me/media',
				array(
					'fields'       => 'id,caption,media_type,media_url,children{media_url,thumbnail_url},permalink,thumbnail_url,timestamp',
					'access_token' => $options['token'],
					'limit'        => 100,
				)
			);
			if ( $body && isset( $body->data ) && is_array( $body->data ) ) {
				$data = $body->data;
			}

			set_transient( 'spf_get_media_' . $cron_time, $data, (int) $cron_time * HOUR_IN_SECONDS );
		}

		return is_array( $data ) ? $data : array();
	}

	/**
	 * Set-up cron job to update saved feed.
	 * Fires after updating options.
	 *
	 * @param   array $old Our old options value.
	 * @param   array $updated Our new updated options.
	 * @since   1.0.0
	 */
	public function spf_setup_cron_job( $old, $updated ) {

		if ( ! is_array( $updated ) ) {
			return;
		}

		$old       = is_array( $old ) ? $old : array();
		$cron_time = isset( $updated['cron_time'] ) ? (int) $updated['cron_time'] : 3;
		$map       = array(
			1  => 'hourly',
			3  => '3h',
			6  => '6h',
			12 => 'twicedaily',
			24 => 'daily',
		);
		$interval  = isset( $map[ $cron_time ] ) ? $map[ $cron_time ] : '3h';

		if ( ! empty( $updated['auth'] ) ) {

			$old_cron = isset( $old['cron_time'] ) ? $old['cron_time'] : '';
			if ( (string) $old_cron !== (string) $updated['cron_time'] ) {
				wp_clear_scheduled_hook( 'simple_photo_update_feed' );
			}

			if ( ! wp_next_scheduled( 'simple_photo_update_feed' ) ) {
				wp_schedule_event( time(), $interval, 'simple_photo_update_feed' );
			}
		} else {

			wp_clear_scheduled_hook( 'simple_photo_update_feed' );
		}
	}

	/**
	 * Refresh the saved instagram feed
	 *
	 * @since   1.0.0
	 */
	public function spf_refresh_feed() {

		$admin   = new Simple_Photo_Feed_Admin( 'simple-photo-feed', SPF_VERSION );
		$success = $admin->spf_delete_transients();
		$this->spf_get_media();

		return $success;
	}

	/**
	 * GET a JSON object from the Instagram Graph API.
	 *
	 * @param string $url    Endpoint.
	 * @param array  $query  Query args.
	 * @return object|false
	 */
	private function get_json( $url, $query ) {
		$response = wp_remote_get(
			add_query_arg( $query, $url ),
			array(
				'timeout'   => 30,
				'sslverify' => true,
			)
		);

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ) );
		return is_object( $body ) ? $body : false;
	}
}
