<?php
/**
 * The main admin page of the plugin
 *
 * This file handles authorization callback, token redeem and main settings.
 *
 * @link       https://gp-web.dev/
 * @since      1.0.0
 *
 * @package    Simple_Photo_Feed
 * @subpackage Simple_Photo_Feed/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$admin = new Simple_Photo_Feed_Admin( 'simple-photo-feed', SPF_VERSION );
$times = $admin->simple_photo_feed_cron_times();
$api   = new Simple_Photo_Feed_Api();
$auth  = $api->spf_get_auth_url_personal();
$uri   = $api->api['redirect_uri'];

$connect_error = '';
$ticket        = isset( $_GET['ticket'], $_GET['nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_GET['nonce'] ) ), 'spf_nonce' )
	? sanitize_text_field( wp_unslash( $_GET['ticket'] ) )
	: '';

if ( $ticket ) {
	$result = $api->spf_redeem_oauth_ticket( $ticket );
	if ( is_array( $result ) && ! empty( $result['token'] ) ) {
		echo $api->spf_connect_user( $result['user_id'], '1', $result['token'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {
		$connect_error = __( 'Could not complete Instagram authorization. Please try again.', 'simple-photo-feed' );
	}
}

$options = get_option( 'spf_main_settings', array() );
if ( ! is_array( $options ) ) {
	$options = array();
}

if ( empty( $options['auth'] ) && ! empty( $options['token'] ) ) {
	$profile = $api->spf_get_account();
	if ( is_object( $profile ) && empty( $profile->error ) && ! empty( $profile->id ) ) {
		echo $api->spf_connect_user( $profile->id, '1', $options['token'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

$auth_error = isset( $_GET['auth_error'], $_GET['reason'], $_GET['nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_GET['nonce'] ) ), 'spf_nonce' )
	? sanitize_text_field( wp_unslash( $_GET['reason'] ) )
	: false;

if ( $connect_error && ! $auth_error ) {
	$auth_error = $connect_error;
}

$auth     = $api->get_app_id() ? $auth : '';
$disabled = $api->get_app_id() ? '' : 'disabled';

$current_cap = isset( $options['required_capability'] ) ? $options['required_capability'] : 'manage_options';
if ( 'edit_posts' === $current_cap ) {
	$current_cap = 'edit_others_posts';
}

$profile = false;
if ( ! empty( $options['auth'] ) ) {
	$profile = $api->spf_get_account();
	if ( ! is_object( $profile ) || ! empty( $profile->error ) ) {
		$profile = false;
	}
}

?>

<h1 class="spf_main_title"><span class="dashicons dashicons-instagram" style="font-size: 24px;"></span>&nbsp;<?php esc_html_e( 'Simple Photo Feed', 'simple-photo-feed' ); ?></h1>
<h4><?php esc_html_e( 'Connect an Instagram account and display its feed in your WordPress site.', 'simple-photo-feed' ); ?></h4>
<div class="spf_main_wrap">

	<div class="spf_main_left">
		<?php if ( current_user_can( 'manage_options' ) ) : ?>
		<form method="post" action="options.php">
			<?php settings_fields( 'spf_main_settings' ); ?>
		<?php else : ?>
		<form method="post" action="">
			<?php wp_nonce_field( 'spf_save_settings', 'spf_nonce' ); ?>
		<?php endif; ?>
			<?php echo ( ! empty( $options['auth'] ) ) ? '' : '<p>' . esc_html__( 'You need an access token for the official Instagram API. Please click the authorize button below to get one or visit our ', 'simple-photo-feed' ) . '<a href="' . esc_url( $uri ) . '" target="_blank" rel="noopener noreferrer">Token Generator</a></p>'; ?>
			<div class="spf-dual-ring hidden" id="spf-loader"></div>
			<table class="form-table">
				<tbody>
					<?php if ( ! empty( $options['auth'] ) ) : ?>
						<tr class="spf_profile_row">
							<th><?php esc_html_e( 'Connected', 'simple-photo-feed' ); ?></th>
							<td>
								<?php if ( $profile ) : ?>
								<a href="<?php echo esc_url( 'https://instagram.com/' . rawurlencode( (string) $profile->username ) ); ?>" target="_blank" rel="noopener noreferrer" class="spf_profile_link button button-primary button-small">
									<span class="dashicons dashicons-instagram"></span><?php echo esc_html( $profile->username ); ?>
								</a>
								<table class="spf_profile">
									<tr>
										<th>Posts</th>
										<th>Account Type</th>
										<th>Account ID</th>
									</tr>
									<tr>
										<td><?php echo ( ! isset( $profile->media_count ) || is_null( $profile->media_count ) ) ? esc_html__( 'No Access!', 'simple-photo-feed' ) : esc_html( $profile->media_count ); ?></td>
										<td><?php echo esc_html( isset( $profile->account_type ) ? $profile->account_type : '' ); ?></td>
										<td><?php echo esc_html( isset( $profile->id ) ? $profile->id : '' ); ?></td>
									</tr>
								</table>
								<?php else : ?>
								<div class="notice notice-warning inline"><p><?php esc_html_e( 'Account is connected but profile data could not be loaded. Disconnect and connect again if this persists.', 'simple-photo-feed' ); ?></p></div>
								<?php endif; ?>
								<a class="button button-secondary" id="spf-admin-deauthorize" href="#">
									<?php esc_html_e( 'Disconnect Account', 'simple-photo-feed' ); ?>
								</a>
								<?php // The stored token is intentionally not printed; saving without a token field keeps it. ?>
							</td>
						</tr>
					<?php else : ?>
						<?php
						if ( $auth_error ) {
							$reason = isset( $_GET['reason'] ) ? sanitize_text_field( wp_unslash( $_GET['reason'] ) ) : '';
							$notice = 'user_denied' === $reason ? __( 'Access denied by user. Please try again below.', 'simple-photo-feed' ) : $auth_error;
							echo '<tr><th>Error</th><td><div class="notice notice-error">' . esc_html( $notice ) . '</div></td></tr>';
						}
						?>
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
								<input type="text" name="spf_main_settings[token]" id="spf_token" value="" autocomplete="off">
								<?php echo esc_html__( 'You can also get a token at our ', 'simple-photo-feed' ) . '<a href="' . esc_url( $uri ) . '" target="_blank" rel="noopener noreferrer">Token Generator</a></p>'; ?>
							</td>
						</tr>
					<?php endif; ?>
					<tr>
						<th><?php esc_html_e( 'Update Feed', 'simple-photo-feed' ); ?></th>
						<td>
							<select name="spf_main_settings[cron_time]" id="spf_cron_time">
								<?php foreach ( $times as $k => $v ) : ?>
									<option value="<?php echo esc_attr( $k ); ?>" <?php selected( (string) ( isset( $options['cron_time'] ) ? $options['cron_time'] : '3' ), (string) $k ); ?>><?php echo esc_html( $v ); ?></option>
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
					<?php if ( current_user_can( 'manage_options' ) ) : ?>
					<tr>
						<th><?php esc_html_e( 'Access Control', 'simple-photo-feed' ); ?></th>
						<td>
							<select name="spf_main_settings[required_capability]" id="spf_required_capability">
								<option value="manage_options" <?php selected( $current_cap, 'manage_options' ); ?>><?php esc_html_e( 'Administrators only', 'simple-photo-feed' ); ?></option>
								<option value="edit_others_posts" <?php selected( $current_cap, 'edit_others_posts' ); ?>><?php esc_html_e( 'Editors and above', 'simple-photo-feed' ); ?></option>
								<option value="publish_posts" <?php selected( $current_cap, 'publish_posts' ); ?>><?php esc_html_e( 'Authors and above', 'simple-photo-feed' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'Choose which user roles can access and configure this plugin.', 'simple-photo-feed' ); ?></p>
						</td>
					</tr>
					<?php endif; ?>
				</tbody>
			</table>
			<input type="hidden" name="spf_main_settings[user_id]" id="spf_user_id" value="<?php echo esc_attr( isset( $options['user_id'] ) ? $options['user_id'] : '' ); ?>">
			<input type="hidden" name="spf_main_settings[auth]" id="spf_auth" value="<?php echo esc_attr( isset( $options['auth'] ) ? $options['auth'] : '' ); ?>">
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
			<pre>[simple-photo-feed view="12" size="small"]</pre>
		</div>
		<div>
			<?php esc_html_e( 'Open images in a popup lightbox:', 'simple-photo-feed' ); ?>
			<pre>[simple-photo-feed view="12" lightbox="on"]</pre>
		</div>
	</div>

	<div class="spf_main_bottom">
		<p> <?php esc_html_e( 'If you like this free plugin then please', 'simple-photo-feed' ); ?> <a target="_blank" rel="noopener noreferrer" href="https://wordpress.org/support/plugin/simple-photo-feed/reviews/?rate=5#new-post" title="Rate the plugin"><?php esc_html_e( 'give us a review ', 'simple-photo-feed' ); ?> ❤</a></p>
	</div>

</div>
