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

if ( ! isset( $options['[auth]'] ) && ! empty( $options['token'] ) ) :

	echo '<div class="spf_container spf_size_' . esc_attr( $size ) . '">';

	foreach ( $media as $i => $p ) :
		if ( $i === $limit ) {
			break;
		}

		$url     = 'VIDEO' !== $p->media_type ? $p->media_url : $p->thumbnail_url;
		$caption = property_exists( $p, 'caption' ) ? $p->caption : esc_html__( 'No caption.', 'simple-photo-feed' );

		if ( 'on' === $text ) {
			echo '<div class="spf_item_wrap">';
		}

		echo '<div class="spf_item">';
		echo '<a href="' . esc_url( $p->permalink ) . '" target="_blank" title="' . esc_attr( $caption ) . '"><img src="' . esc_url( $url ) . '" alt="" /></a>';
		echo '</div>';

		if ( 'on' === $text ) {
			echo '<div class="spf_caption">' . esc_html( $caption ) . '</div>';
			echo '</div>';
		}
	endforeach;

	echo '</div>';

else :

	echo '<h6>' . esc_html__( 'Please authorize Simple Photo Feed plugin.', 'simple-photo-feed' ) . '</h6>';

endif;
