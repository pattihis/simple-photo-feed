<?php
/**
 * The main admin page of the plugin
 *
 * This file handles authorization callback, token exchange and main settings.
 *
 * @link       https://gp-web.dev/
 * @since      1.0.0
 *
 * @package    Simple_Insta_Feed
 * @subpackage Simple_Insta_Feed/admin/partials
 */

$admin = new Simple_Insta_Feed_Admin( 'simple-insta-feed', SIF_VERSION );
$times = $admin->simple_insta_feed_cron_times();
$api   = new Simple_Insta_Feed_Api();
$auth  = $api->sif_get_auth_url_personal();
$uri   = $api->api['redirect_uri'];

$code = isset( $_GET['code'], $_GET['nonce'] ) && wp_verify_nonce( sanitize_key( $_GET['nonce'] ), 'sif_nonce' )
		? sanitize_text_field( wp_unslash( $_GET['code'] ) )
		: false;

if ( $code ) :
	$response = $api->sif_get_short_lived_token( $code );
	$short    = is_object( $response ) ? $response->access_token : '';
	$user_id  = is_object( $response ) ? $response->user_id : '';
	$long     = $api->sif_get_long_lived_token( $short );
	$token    = is_object( $long ) ? $long->access_token : '';
	echo $api->sif_connect_user( $user_id, '1', $token ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
endif;

$options = get_option( 'sif_main_settings', array() );

if ( empty( $options['auth'] ) && ! empty( $options['token'] ) ) :
	$profile = $api->sif_get_account();
	echo $api->sif_connect_user( $profile->id, '1', $options['token'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
endif;

$auth_error = isset( $_GET['auth_error'], $_GET['reason'], $_GET['nonce'] ) && wp_verify_nonce( sanitize_key( $_GET['nonce'] ), 'sif_nonce' )
			? sanitize_text_field( wp_unslash( $_GET['reason'] ) )
			: false;

?>

<h1 class="sif_main_title"><span class="dashicons dashicons-instagram" style="font-size: 24px;"></span>&nbsp;<?php esc_html_e( 'Simple Instagram Feed', 'simple-insta-feed' ); ?></h1>
<h4><?php esc_html_e( 'Connect an Instagram account and display its feed in your WordPress site.', 'simple-insta-feed' ); ?></h4>
<div class="sif_main_wrap">

	<div class="sif_main_left">
		<form method="post" action="options.php">
			<?php settings_fields( 'sif_main_settings' ); ?>
			<?php echo (bool) $options['auth'] ? '' : '<p>' . esc_html__( 'You need an access token for the official Instagram API. Please click the authorize button below to get one or visit our ', 'simple-insta-feed' ) . '<a href="' . esc_url( $uri ) . '" target="_blank">Token Generator</a></p>'; ?>
			<div class="sif-dual-ring hidden" id="sif-loader"></div>
			<table class="form-table">
				<tbody>
				<?php
				if ( (bool) $options['auth'] ) :
					$profile = $api->sif_get_account();

					if ( is_null( $profile->media_count ) ) {
						$notice = __( 'You\'ve approved access to your profile info but not to your media', 'simple-insta-feed' );
						echo '<tr><th>' . esc_html__( 'Limited Access', 'simple-insta-feed' ) . '</th><td><div class="notice notice-warning">' . esc_html( $notice ) . '</div><p>' . esc_html__( 'To get access to all plugin\'s functionalities, please click the button below to grant additional permission.', 'simple-insta-feed' ) . '</p><p><a class="button button-secondary" href="' . esc_url( $auth ) . '">' . esc_html__( 'Grant missing permission', 'simple-insta-feed' ) . '</a></p></td></tr>';

					}
					?>
					<tr class="sif_profile_row">
						<th><?php esc_html_e( 'Connected', 'simple-insta-feed' ); ?></th>
						<td>
							<a href="https://instagram.com/<?php echo esc_html( $profile->username ); ?>" target="_blank" class="sif_profile_link button button-primary button-small">
								<span class="dashicons dashicons-instagram"></span><?php echo esc_html( $profile->username ); ?>
							</a>
							<table class="sif_profile">
								<tr>
									<th>Posts</th>
									<th>Account Type</th>
									<th>Account ID</th>
								</tr>
								<tr>
									<td><?php echo is_null( $profile->media_count ) ? esc_html__( 'No Access!', 'simple-insta-feed' ) : esc_html( $profile->media_count ); ?></td>
									<td><?php echo esc_html( $profile->account_type ); ?></td>
									<td><?php echo esc_html( $profile->id ); ?></td>
								</tr>
							</table>
							<a class="button button-secondary" id="sif-admin-deauthorize" href="#">
								<?php esc_html_e( 'Disconnect Account', 'simple-insta-feed' ); ?>
							</a>
							<input type="hidden" name="sif_main_settings[token]" id="sif_token" value="<?php echo esc_html( $options['token'] ); ?>">
						</td>
					</tr>
				<?php else : ?>
					<?php
					if ( $auth_error ) {
						$notice = 'user_denied' === $_GET['reason'] ? __( 'Access denied by user. Please try again below.', 'simple-insta-feed' ) : $auth_error;
						echo '<tr><th>Error</th><td><div class="notice notice-error">' . esc_html( $notice ) . '</div></td></tr>';
					}
					?>
					<tr>
						<th><?php esc_html_e( 'Authorize Access', 'simple-insta-feed' ); ?></th>
						<td>
							<a class="button button-primary" id="sif-admin-authorize" href="<?php echo esc_url( $auth ); ?>">
								<?php esc_html_e( 'Connect Account', 'simple-insta-feed' ); ?>
							</a>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Access Token', 'simple-insta-feed' ); ?></th>
						<td>
							<input type="text" name='sif_main_settings[token]' id='sif_token' value="" autocomplete="off">
							<?php echo esc_html__( 'You can also get a token at our ', 'simple-insta-feed' ) . '<a href="' . esc_url( $uri ) . '" target="_blank">Token Generator</a></p>'; ?>
						</td>
					</tr>
				<?php endif; ?>
					<tr>
						<th><?php esc_html_e( 'Update Feed', 'simple-insta-feed' ); ?></th>
						<td>
							<select name='sif_main_settings[cron_time]' id='sif_cron_time'>
								<?php foreach ( $times as $k => $v ) : ?>
									<option value='<?php echo esc_attr( $k ); ?>' <?php selected( esc_attr( $options['cron_time'] ), esc_attr( $k ) ); ?>><?php echo esc_html( $v ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Feed Cache', 'simple-insta-feed' ); ?></th>
						<td>
							<button class="button button-secondary" id="sif-admin-clear-cache">Clear Instagram Cache</button>
							<div class="sif-dual-ring hidden" id="sif-loader-small"></div>
						</td>
					</tr>
				</tbody>
			</table>
			<input type="hidden" name="sif_main_settings[user_id]" id="sif_user_id" value="<?php echo esc_html( $options['user_id'] ); ?>">
			<input type="hidden" name="sif_main_settings[auth]" id="sif_auth" value="<?php echo esc_html( $options['auth'] ); ?>">
			<?php submit_button( __( 'Save options', 'simple-insta-feed' ), 'button button-primary button-large' ); ?>
		</form><!-- /.form -->
	</div>

	<div class="sif_main_right">
		<h3><span class="dashicons dashicons-shortcode"></span>&nbsp;<?php esc_html_e( 'How to use', 'simple-insta-feed' ); ?></h3>
		<div>
			<?php esc_html_e( 'You can use the shortcode below in your Posts/Pages:', 'simple-insta-feed' ); ?>
			<pre>[simple-insta-feed]</pre>
		</div>
		<div>
			<?php esc_html_e( 'Choose how many images to show (1-100):', 'simple-insta-feed' ); ?>
			<pre>[simple-insta-feed view="12"]</pre>
		</div>
		<div>
			<?php esc_html_e( 'Show your captions (on/off):', 'simple-insta-feed' ); ?>
			<pre>[simple-insta-feed view="12" text="on"]</pre>
		</div>
		<div>
			<?php esc_html_e( 'Smaller thumbnails, more columns:', 'simple-insta-feed' ); ?>
			<pre>[simple-insta-feed view="12" text="on" size="small"]</pre>
		</div>
	</div>

	<div class="sif_main_bottom">
		<p>	<?php esc_html_e( 'If you like this free plugin then please', 'simple-insta-feed' ); ?> <a target="_blank" href="https://wordpress.org/support/plugin/simple-insta-feed/reviews/?rate=5#new-post" title="Rate the plugin"><?php esc_html_e( 'give us a review ', 'simple-insta-feed' ); ?> ❤</a></p>
	</div>

</div>
