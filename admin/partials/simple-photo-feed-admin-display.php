<?php
/**
 * The main admin page of the plugin
 *
 * This file handles authorization callback, token exchange and main settings.
 *
 * @link       https://gp-web.dev/
 * @since      1.0.0
 *
 * @package    Simple_Photo_Feed
 * @subpackage Simple_Photo_Feed/admin/partials
 */

$admin = new Simple_Photo_Feed_Admin( 'simple-photo-feed', SPF_VERSION );
$times = $admin->simple_photo_feed_cron_times();
$api   = new Simple_Photo_Feed_Api();
$auth  = $api->spf_get_auth_url_personal();
$uri   = $api->api['redirect_uri'];

$code = isset( $_GET['code'], $_GET['nonce'] ) && wp_verify_nonce( sanitize_key( $_GET['nonce'] ), 'spf_nonce' )
		? sanitize_text_field( wp_unslash( $_GET['code'] ) )
		: false;

if ( $code ) :
	$response = $api->spf_get_short_lived_token( $code );
	$short    = is_object( $response ) ? $response->access_token : '';
	$user_id  = is_object( $response ) ? $response->user_id : '';
	$long     = $api->spf_get_long_lived_token( $short );
	$token    = is_object( $long ) ? $long->access_token : '';
	echo $api->spf_connect_user( $user_id, '1', $token ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
endif;

$options = get_option( 'spf_main_settings', array() );

if ( empty( $options['auth'] ) && ! empty( $options['token'] ) ) :
	$profile = $api->spf_get_account();
	echo $api->spf_connect_user( $profile->id, '1', $options['token'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
endif;

$auth_error = isset( $_GET['auth_error'], $_GET['reason'], $_GET['nonce'] ) && wp_verify_nonce( sanitize_key( $_GET['nonce'] ), 'spf_nonce' )
			? sanitize_text_field( wp_unslash( $_GET['reason'] ) )
			: false;

$auth     = empty( $options['app_id'] ) || empty( $options['app_secret'] ) ? '' : $auth;
$disabled = empty( $options['app_id'] ) || empty( $options['app_secret'] ) ? '' : 'disabled';

?>

<h1 class="spf_main_title"><span class="dashicons dashicons-instagram" style="font-size: 24px;"></span>&nbsp;<?php esc_html_e( 'Simple Photo Feed', 'simple-photo-feed' ); ?></h1>
<h4><?php esc_html_e( 'Connect an Instagram account and display its feed in your WordPress site.', 'simple-photo-feed' ); ?></h4>
<div class="spf_main_wrap">

	<div class="spf_main_left">
		<form method="post" action="options.php">
			<?php settings_fields( 'spf_main_settings' ); ?>
			<?php echo (bool) $options['auth'] ? '' : '<p>' . esc_html__( 'You need an access token for the official Instagram API. Please add your App\'s ID & Secret Key to enable the authorize button below or visit our ', 'simple-photo-feed' ) . '<a href="' . esc_url( $uri ) . '" target="_blank">Token Generator</a></p>'; ?>
			<div class="spf-dual-ring hidden" id="spf-loader"></div>
			<table class="form-table">
				<tbody>
				<?php
				if ( (bool) $options['auth'] ) :
					$profile = $api->spf_get_account();

					if ( is_null( $profile->media_count ) ) {
						$notice = __( 'You\'ve approved access to your profile info but not to your media', 'simple-photo-feed' );
						echo '<tr><th>' . esc_html__( 'Limited Access', 'simple-photo-feed' ) . '</th><td><div class="notice notice-warning">' . esc_html( $notice ) . '</div><p>' . esc_html__( 'To get access to all plugin\'s functionalities, please click the button below to grant additional permission.', 'simple-photo-feed' ) . '</p><p><a class="button button-secondary" href="' . esc_url( $auth ) . '">' . esc_html__( 'Grant missing permission', 'simple-photo-feed' ) . '</a></p></td></tr>';

					}
					?>
					<tr class="spf_profile_row">
						<th><?php esc_html_e( 'Connected', 'simple-photo-feed' ); ?></th>
						<td>
							<a href="https://instagram.com/<?php echo esc_html( $profile->username ); ?>" target="_blank" class="spf_profile_link button button-primary button-small">
								<span class="dashicons dashicons-instagram"></span><?php echo esc_html( $profile->username ); ?>
							</a>
							<table class="spf_profile">
								<tr>
									<th>Posts</th>
									<th>Account Type</th>
									<th>Account ID</th>
								</tr>
								<tr>
									<td><?php echo is_null( $profile->media_count ) ? esc_html__( 'No Access!', 'simple-photo-feed' ) : esc_html( $profile->media_count ); ?></td>
									<td><?php echo esc_html( $profile->account_type ); ?></td>
									<td><?php echo esc_html( $profile->id ); ?></td>
								</tr>
							</table>
							<a class="button button-secondary" id="spf-admin-deauthorize" href="#">
								<?php esc_html_e( 'Disconnect Account', 'simple-photo-feed' ); ?>
							</a>
							<input type="hidden" name="spf_main_settings[token]" id="spf_token" value="<?php echo esc_html( $options['token'] ); ?>">
						</td>
					</tr>
				<?php else : ?>
					<?php
					if ( $auth_error ) {
						$notice = 'user_denied' === $_GET['reason'] ? __( 'Access denied by user. Please try again below.', 'simple-photo-feed' ) : $auth_error;
						echo '<tr><th>Error</th><td><div class="notice notice-error">' . esc_html( $notice ) . '</div></td></tr>';
					}
					?>
					<tr>
						<th><?php esc_html_e( 'App ID', 'simple-photo-feed' ); ?></th>
						<td>
							<input type="text" name='spf_main_settings[app_id]' id='spf_app_id' value="<?php echo esc_attr( $options['app_id'] ); ?>" autocomplete="off">
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'App Secret', 'simple-photo-feed' ); ?></th>
						<td>
							<input type="text" name='spf_main_settings[app_secret]' id='spf_app_secret' value="<?php echo esc_attr( $options['app_secret'] ); ?>" autocomplete="off">
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Authorize Access', 'simple-photo-feed' ); ?></th>
						<td>
							<a class="button button-primary" id="spf-admin-authorize" href="<?php echo esc_url( $auth ); ?>" <?php echo esc_attr( $disabled ); ?>>
								<?php esc_html_e( 'Connect Account', 'simple-photo-feed' ); ?>
							</a>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Access Token', 'simple-photo-feed' ); ?></th>
						<td>
							<input type="text" name='spf_main_settings[token]' id='spf_token' value="" autocomplete="off">
							<?php echo esc_html__( 'You can also get a token at our ', 'simple-photo-feed' ) . '<a href="' . esc_url( $uri ) . '" target="_blank">Token Generator</a></p>'; ?>
						</td>
					</tr>
				<?php endif; ?>
					<tr>
						<th><?php esc_html_e( 'Update Feed', 'simple-photo-feed' ); ?></th>
						<td>
							<select name='spf_main_settings[cron_time]' id='spf_cron_time'>
								<?php foreach ( $times as $k => $v ) : ?>
									<option value='<?php echo esc_attr( $k ); ?>' <?php selected( esc_attr( $options['cron_time'] ), esc_attr( $k ) ); ?>><?php echo esc_html( $v ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Feed Cache', 'simple-photo-feed' ); ?></th>
						<td>
							<button class="button button-secondary" id="spf-admin-clear-cache">Clear Feed Cache</button>
							<div class="spf-dual-ring hidden" id="spf-loader-small"></div>
						</td>
					</tr>
				</tbody>
			</table>
			<input type="hidden" name="spf_main_settings[user_id]" id="spf_user_id" value="<?php echo esc_html( $options['user_id'] ); ?>">
			<input type="hidden" name="spf_main_settings[auth]" id="spf_auth" value="<?php echo esc_html( $options['auth'] ); ?>">
			<?php submit_button( __( 'Save options', 'simple-photo-feed' ), 'button button-primary button-large' ); ?>
		</form><!-- /.form -->
	</div>

	<div class="spf_main_right">
		<h3><span class="dashicons dashicons-shortcode"></span>&nbsp;<?php esc_html_e( 'How to use', 'simple-photo-feed' ); ?></h3>
		<div>
			<?php esc_html_e( 'You can use the shortcode below in your Posts/Pages:', 'simple-photo-feed' ); ?>
			<pre>[simple-photo-feed]</pre>
		</div>
		<div>
			<?php esc_html_e( 'Choose how many images to show (1-100):', 'simple-photo-feed' ); ?>
			<pre>[simple-photo-feed view="12"]</pre>
		</div>
		<div>
			<?php esc_html_e( 'Show your captions (on/off):', 'simple-photo-feed' ); ?>
			<pre>[simple-photo-feed view="12" text="on"]</pre>
		</div>
		<div>
			<?php esc_html_e( 'Smaller thumbnails, more columns:', 'simple-photo-feed' ); ?>
			<pre>[simple-photo-feed view="12" text="on" size="small"]</pre>
		</div>
	</div>

	<div class="spf_main_bottom">
		<p>	<?php esc_html_e( 'If you like this free plugin then please', 'simple-photo-feed' ); ?> <a target="_blank" href="https://wordpress.org/support/plugin/simple-photo-feed/reviews/?rate=5#new-post" title="Rate the plugin"><?php esc_html_e( 'give us a review ', 'simple-photo-feed' ); ?> ❤</a></p>
	</div>

</div>
