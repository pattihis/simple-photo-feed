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

/**
 * Connect to Instagram's Basic Display API
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Simple_Photo_Feed
 * @subpackage Simple_Photo_Feed/includes
 * @author     George Pattichis <info@gp-web.dev>
 */
class Simple_Photo_Feed_Api {

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
			'authorize_url' => 'https://api.instagram.com/oauth/authorize/',
			'response_type' => 'code',
			'scope'         => 'user_profile,user_media',
			'root'          => 'https://graph.instagram.com/',
			'redirect_uri'  => 'https://gp-web.dev/instagram-authorize/',
			'short_lived'   => 'https://api.instagram.com/oauth/access_token/',
			'long_lived'    => 'https://graph.instagram.com/access_token',
			'refresh_token' => 'https://graph.instagram.com/refresh_access_token/',
		);
	}

	/**
	 * Create the authorization URL for personal accounts
	 *
	 * @since   1.0.0
	 */
	public function spf_get_auth_url_personal() {

		$spf_nonce = wp_create_nonce( 'spf_nonce' );
		$options   = get_option( 'spf_main_settings', array() );

		$url = add_query_arg(
			array(
				'client_id'     => $options['app_id'],
				'redirect_uri'  => $this->api['redirect_uri'],
				'scope'         => $this->api['scope'],
				'response_type' => $this->api['response_type'],
				'state'         => rawurlencode( admin_url( 'admin.php?page=simple-photo-feed&' ) . 'spf_nonce=' . $spf_nonce ),
			),
			$this->api['authorize_url']
		);

		return $url;
	}

	/**
	 * Get a short-lived access token from authorization code
	 *
	 * @param   string $code Authorization code from Instagram user.
	 * @since   1.0.0
	 */
	public function spf_get_short_lived_token( $code ) {

		$options = get_option( 'spf_main_settings', array() );
		$args    = array(
			'method'    => 'POST',
			'timeout'   => 45,
			'sslverify' => false,
			'body'      => array(
				'client_id'     => $options['app_id'],
				'client_secret' => $options['app_secret'],
				'code'          => $code,
				'grant_type'    => 'authorization_code',
				'redirect_uri'  => $this->api['redirect_uri'],
			),
		);

		$request = wp_remote_post( $this->api['short_lived'], $args );
		if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) !== 200 ) {
			return 'Error!';
		}
		return json_decode( wp_remote_retrieve_body( $request ) );
	}

	/**
	 * Get a long-lived access token from the short-lived one
	 *
	 * @param   string $short Short-Lived Access Token.
	 * @since   1.0.0
	 */
	public function spf_get_long_lived_token( $short ) {

		$options  = get_option( 'spf_main_settings', array() );
		$response = wp_remote_get( $this->api['long_lived'] . '?grant_type=ig_exchange_token&client_secret=' . $options['app_secret'] . '&access_token=' . $short );
		if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
			return json_decode( wp_remote_retrieve_body( $response ) );
		}
		return json_decode( 'Error! Something went wrong.' );
	}

	/**
	 * Refresh the long-lived access token
	 *
	 * @since   1.0.0
	 */
	public function spf_refresh_long_lived_token() {

		$options  = get_option( 'spf_main_settings', array() );
		$response = wp_remote_get( $this->api['refresh_token'] . '?grant_type=ig_refresh_token&access_token=' . $options['token'] );
		if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
			return json_decode( wp_remote_retrieve_body( $response ) );
		}
		return json_decode( 'Error! Something went wrong.' );
	}

	/**
	 * Get account info from Instagram API
	 *
	 * @since   1.0.0
	 */
	public function spf_get_account() {

		$options = get_option( 'spf_main_settings', array() );
		if ( ! empty( $options['token'] ) ) :
			$response = wp_remote_get( $this->api['root'] . 'me?fields=account_type,id,username,media_count&access_token=' . $options['token'] );
			$body     = json_decode( wp_remote_retrieve_body( $response ) );

			if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
				return $body;
			} else {
				if ( 'Application does not have permission for this action' === $body->error->message ) {
					$response = wp_remote_get( $this->api['root'] . 'me?fields=account_type,id,username&access_token=' . $options['token'] );
					return json_decode( wp_remote_retrieve_body( $response ) );
				} else {
					return $body;
				}
			}
		endif;

		return json_decode( 'Error! Something went wrong.' );
	}

	/**
	 * Remove URL parameters and refresh
	 *
	 * @param   string $user_id Instagram User ID.
	 * @param   string $auth Whether we've been authorized.
	 * @param   string $token Long-lived access token.
	 * @since   1.0.0
	 */
	public function spf_connect_user( $user_id, $auth, $token ) {

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
	 */
	public function spf_get_media() {

		$options   = get_option( 'spf_main_settings', array() );
		$data      = get_transient( 'spf_get_media_' . $options['cron_time'] );
		$cron_time = $options['cron_time'] ? $options['cron_time'] : '3';
		if ( false === $data ) {
			$response = wp_remote_get( $this->api['root'] . 'me/media?fields=id,caption,media_type,media_url,children{media_url,thumbnail_url},permalink,thumbnail_url,timestamp&access_token=' . $options['token'] . '&limit=100' );
			if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
				$media = json_decode( wp_remote_retrieve_body( $response ) );
				$data  = $media->data;
			}

			set_transient( 'spf_get_media_' . $cron_time, $data, (int) $cron_time * HOUR_IN_SECONDS );
		}

		return $data;
	}

	/**
	 * Set-up cron job to update saved feed.
	 * Fires after updating options.
	 *
	 * @param   array $old Our old options value.
	 * @param   array $new Our new updated options.
	 * @since   1.0.0
	 */
	public function spf_setup_cron_job( $old, $new ) {

		switch ( $new['cron_time'] ) {
			case 1:
				$interval = 'hourly';
				break;
			case 3:
				$interval = '3h';
				break;
			case 6:
				$interval = '6h';
				break;
			case 12:
				$interval = 'twicedaily';
				break;
			case 24:
				$interval = 'daily';
				break;
		}

		if ( (bool) $new['auth'] ) {

			if ( $old['cron_time'] !== $new['cron_time'] ) {
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
}
