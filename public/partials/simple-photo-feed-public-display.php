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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$options  = get_option( 'spf_main_settings', array() );
$limit    = (int) $atts['view'];
$text     = $atts['text'];
$size     = $atts['size'];
$lightbox = $atts['lightbox'];
$api      = new Simple_Photo_Feed_Api();
$media    = $api->spf_get_media();

if ( ! empty( $options['token'] ) && is_array( $media ) ) :

	echo '<div class="spf_container spf_size_' . esc_attr( $size ) . '">';

	foreach ( $media as $i => $p ) :
		if ( $i === $limit ) {
			break;
		}
		if ( ! is_object( $p ) ) {
			continue;
		}

		$url = ( isset( $p->media_type ) && 'VIDEO' === $p->media_type && ! empty( $p->thumbnail_url ) )
			? esc_url( $p->thumbnail_url )
			: ( ! empty( $p->media_url ) ? esc_url( $p->media_url ) : '' );
		$caption = ( isset( $p->caption ) && '' !== $p->caption ) ? $p->caption : esc_html__( 'No caption.', 'simple-photo-feed' );

		if ( 'on' === $text ) {
			echo '<div class="spf_item_wrap">';
		}

		echo '<div class="spf_item">';
		if ( 'off' === $lightbox ) {
			echo '<a href="' . esc_url( isset( $p->permalink ) ? $p->permalink : '' ) . '" target="_blank" rel="noopener noreferrer" title="' . esc_attr( $caption ) . '"><img src="' . esc_url( $url ) . '" alt="" /></a>';
		} else {
			echo '<a href="' . esc_url( isset( $p->permalink ) ? $p->permalink : '' ) . '" target="_blank" rel="noopener noreferrer" class="spf_lightbox" data-i="' . esc_attr( $i ) . '" data-count="' . esc_attr( count( $media ) ) . '" data-src="' . esc_url( $url ) . '" data-url="' . esc_url( isset( $p->permalink ) ? $p->permalink : '' ) . '" title="' . esc_attr( $caption ) . '">
				<img src="' . esc_url( $url ) . '" alt="" ' . ( 'on' === $text ? 'aria-labelledby="spf_' . esc_attr( isset( $p->id ) ? $p->id : $i ) . '"' : 'aria-label="' . esc_attr( $caption ) . '"' ) . ' />
			</a>';
		}
		echo '</div>';

		if ( 'on' === $text ) {
			echo '<div class="spf_caption" id="spf_' . esc_attr( isset( $p->id ) ? $p->id : $i ) . '">' . esc_html( $caption ) . '</div>';
			echo '</div>';
		}
	endforeach;

	echo '</div>';

else :

	echo '<h6>' . esc_html__( 'Please authorize Simple Photo Feed plugin.', 'simple-photo-feed' ) . '</h6>';

endif;
