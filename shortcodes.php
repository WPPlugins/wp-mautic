<?php
/**
 * Shortcode definition
 *
 * @package wpmautic
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	echo 'This file should not be accessed directly!';
	exit; // Exit if accessed directly.
}

add_shortcode( 'mautic', 'wpmautic_shortcode' );
add_shortcode( 'mauticcontent', 'wpmautic_dwc_shortcode' );
add_shortcode( 'mauticvideo', 'wpmautic_video_shortcode' );
add_shortcode( 'mauticform', 'wpmautic_form_shortcode' );
add_shortcode( 'mautictags', 'wpmautic_tags_shortcode' );
add_shortcode( 'mauticfocus', 'wpmautic_focus_shortcode' );

/**
 * Handle mautic shortcode. Must include a type attribute.
 *
 * @param array       $atts    Shortcode attributes.
 * @param string|null $content Default content to be displayed.
 *
 * @return string
 */
function wpmautic_shortcode( $atts, $content = null ) {
	$default = shortcode_atts(array(
		'type' => null,
	), $atts);

	switch ( $default['type'] ) {
		case 'form':
			return wpmautic_form_shortcode( $atts );
		case 'content':
			return wpmautic_dwc_shortcode( $atts, $content );
		case 'video':
			return wpmautic_video_shortcode( $atts );
		case 'tags':
			return wpmautic_tags_shortcode( $atts );
		case 'focus':
			return wpmautic_focus_shortcode( $atts );
	}

	return false;
}

/**
 * Handle mauticform shortcode
 * example: [mauticform id="1"]
 * example: [mautic type="form" id="1"]
 *
 * @param  array $atts Shortcode attributes.
 *
 * @return string
 */
function wpmautic_form_shortcode( $atts ) {
	$base_url = wpmautic_option( 'base_url', '' );
	if ( '' === $base_url ) {
		return false;
	}

	$atts = shortcode_atts( array(
		'id' => '',
	), $atts );

	if ( empty( $atts['id'] ) ) {
		return false;
	}

	return '<script type="text/javascript" ' . sprintf(
		'src="%s/form/generate.js?id=%s"',
		esc_url( $base_url ),
		esc_attr( $atts['id'] )
	) . '></script>';
}

/**
 * Dynamic content shortcode handling
 * example: [mautic type="content" slot="slot_name"]Default Content[/mautic]
 * example: [mauticcontent slot="slot_name"]Default Content[/mauticcontent]
 *
 * @param  array       $atts    Shortcode attributes.
 * @param  string|null $content Default content to be displayed.
 *
 * @return string
 */
function wpmautic_dwc_shortcode( $atts, $content = null ) {
	$atts     = shortcode_atts( array(
		'slot' => '',
	), $atts, 'mautic' );

	return sprintf(
		'<div class="mautic-slot" data-slot-name="%s">%s</div>',
		esc_attr( $atts['slot'] ),
		esc_textarea( $content )
	);
}

/**
 * Video shortcode handling
 * example: [mautic type="video" gate-time="15" form-id="1" src="https://www.youtube.com/watch?v=QT6169rdMdk"]
 * example: [mauticvideo gate-time="15" form-id="1" src="https://www.youtube.com/watch?v=QT6169rdMdk"]
 *
 * @param  array $atts Shortcode attributes.
 *
 * @return string
 */
function wpmautic_video_shortcode( $atts ) {
	$video_type = '';
	$atts = shortcode_atts(array(
		'gate-time' => 15,
		'form-id' => '',
		'src' => '',
		'video-type' => '',
		'width' => 640,
		'height' => 360,
	), $atts);

	if ( empty( $atts['src'] ) ) {
		return 'You must provide a video source. Add a src="URL" attribute to your shortcode. Replace URL with the source url for your video.';
	}

	if ( empty( $atts['form-id'] ) ) {
		return 'You must provide a mautic form id. Add a form-id="#" attribute to your shortcode. Replace # with the id of the form you want to use.';
	}

	if ( preg_match( '/^.*((youtu.be)|(youtube.com))\/((v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))?\??v?=?([^#\&\?]*).*/', $atts['src'] ) ) {
		$atts['video-type'] = 'youtube';
	}
	if ( preg_match( '/^.*(vimeo\.com\/)((channels\/[A-z]+\/)|(groups\/[A-z]+\/videos\/))?([0-9]+)/', $atts['src'] ) ) {
		$atts['video-type'] = 'vimeo';
	}
	if ( strtolower( substr( $atts['src'], -3 ) ) === 'mp4' ) {
		$atts['video-type'] = 'mp4';
	}

	if ( empty( $atts['video-type'] ) ) {
		return 'Please define a valid video type with video-type="#".';
	}

	return sprintf(
		'<video height="%s" width="%s" data-form-id="%s" data-gate-time="%s">' .
			'<source type="video/%s" src="%s" />' .
		'</video>',
		esc_attr( $atts['height'] ),
		esc_attr( $atts['width'] ),
		esc_attr( $atts['form-id'] ),
		esc_attr( $atts['gate-time'] ),
		esc_attr( $atts['video-type'] ),
		esc_attr( $atts['src'] )
	);
}

/**
 * Handle mautic tags by Wordpress shortcodes
 * example: [mautic type="tags" values="addtag,-removetag"]
 * example: [mautictags values="addtag,-removetag"]
 *
 * @param  array $atts Shortcode attributes.
 *
 * @return string
 */
function wpmautic_tags_shortcode( $atts ) {
	$base_url = wpmautic_option( 'base_url', '' );
	if ( '' === $base_url ) {
		return false;
	}

	$atts = shortcode_atts( array(
		'values' => '',
	), $atts );

	if ( empty( $atts['values'] ) ) {
		return false;
	}

	return sprintf(
		'<img src="%s/mtracking.gif?tags=%s" alt="Mautic Tags" />',
		esc_url( $base_url ),
		esc_attr( $atts['values'] )
	);
}

/**
 * Handle mautic focus itens on Wordpress Page
 * example: [mautic type="focus" id="1"]
 *
 * @param  array $atts Shortcode attributes.
 *
 * @return string
 */
function wpmautic_focus_shortcode( $atts ) {
	$base_url = wpmautic_option( 'base_url', '' );
	if ( '' === $base_url ) {
		return false;
	}

	$atts = shortcode_atts( array(
		'id' => '',
	), $atts );

	if ( empty( $atts['id'] ) ) {
		return false;
	}

	return '<script type="text/javascript" ' . sprintf(
		'src="%s/focus/%s.js"',
		esc_url( $base_url ),
		esc_attr( $atts['id'] )
	) . ' async="async"></script>';
}
