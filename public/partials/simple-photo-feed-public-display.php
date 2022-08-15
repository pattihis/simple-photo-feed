<?php
/**
 * Provide a frontend view for the plugin
 *
 * This file is used to markup the frontend aspects of the plugin.
 *
 * @link       https://gp-web.dev/
 * @since      1.0.0
 *
 * @package    Simple_Photo_Feed
 * @subpackage Simple_Photo_Feed/public/partials
 */

$options = get_option( 'spf_main_settings', array() );
$limit   = (int) $atts['view'];
$text    = $atts['text'];
$size    = $atts['size'];
$api     = new Simple_Photo_Feed_Api();
$media   = $api->spf_get_media();
$profile = $api->spf_get_account();

?>

<div class="spf_container spf_size_<?php echo esc_attr( $size ); ?>">
	<?php
	if ( ! isset( $options['[auth]'] ) && ! empty( $options['token'] ) ) :
		foreach ( $media as $i => $p ) :
			if ( $i === $limit ) {
				break;
			}
			?>
			<?php if ( 'on' === $text ) : ?>
			<div class="spf_item_wrap">
			<?php endif; ?>
				<div class="spf_item">
					<a href="<?php echo esc_url( $p->permalink ); ?>" target="_blank" title="<?php echo esc_attr( $p->caption ); ?>">
						<img src="<?php echo 'VIDEO' !== $p->media_type ? esc_url( $p->media_url ) : esc_url( $p->thumbnail_url ); ?>" alt="" />
					</a>
				</div>
			<?php if ( 'on' === $text ) : ?>
				<div class="spf_caption"><?php echo esc_html( $p->caption ); ?></div>
			</div>
			<?php endif; ?>
			<?php
		endforeach;
	else :
		echo '<h5>' . esc_html__( 'Please authorize Simple Photo Feed plugin.', 'simple-photo-feed' ) . '</h5>';
	endif;
	?>
</div>
