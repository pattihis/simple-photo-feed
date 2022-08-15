<?php
/**
 * Provide a frontend view for the plugin
 *
 * This file is used to markup the frontend aspects of the plugin.
 *
 * @link       https://gp-web.dev/
 * @since      1.0.0
 *
 * @package    Simple_Insta_Feed
 * @subpackage Simple_Insta_Feed/public/partials
 */

$options = get_option( 'sif_main_settings', array() );
$limit   = (int) $atts['view'];
$text    = $atts['text'];
$size    = $atts['size'];
$api     = new Simple_Insta_Feed_Api();
$media   = $api->sif_get_media();
$profile = $api->sif_get_account();

?>

<div class="sif_container sif_size_<?php echo esc_attr( $size ); ?>">
	<?php
	if ( ! isset( $options['[auth]'] ) && ! empty( $options['token'] ) ) :
		foreach ( $media as $i => $p ) :
			if ( $i === $limit ) {
				break;
			}
			?>
			<?php if ( 'on' === $text ) : ?>
			<div class="sif_item_wrap">
			<?php endif; ?>
				<div class="sif_item">
					<a href="<?php echo esc_url( $p->permalink ); ?>" target="_blank" title="<?php echo esc_attr( $p->caption ); ?>">
						<img src="<?php echo 'VIDEO' !== $p->media_type ? esc_url( $p->media_url ) : esc_url( $p->thumbnail_url ); ?>" alt="" />
					</a>
				</div>
			<?php if ( 'on' === $text ) : ?>
				<div class="sif_caption"><?php echo esc_html( $p->caption ); ?></div>
			</div>
			<?php endif; ?>
			<?php
		endforeach;
	else :
		echo '<h5>' . esc_html__( 'Please authorize instagram plugin.', 'simple-insta-feed' ) . '</h5>';
	endif;
	?>
</div>
