<?php
/**
 * Theme option sanitization.
 *
 * @package Nexa_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize a URL or same-page fragment URL.
 *
 * @param string $url URL value.
 * @return string|null
 */
function nexa_pro_sanitize_url_or_fragment( $url ) {
	$url = trim( (string) $url );

	if ( '' === $url ) {
		return '';
	}

	if ( 1 === preg_match( '/^#[A-Za-z][A-Za-z0-9_-]*$/', $url ) ) {
		return $url;
	}

	$sanitized_url = esc_url_raw( $url );

	return '' === $sanitized_url ? null : $sanitized_url;
}

/**
 * Sanitize theme options for storage.
 *
 * Missing or invalid submitted values preserve the existing saved value, falling
 * back to defaults when no saved value exists.
 *
 * @param mixed $input Raw submitted option value.
 * @return array
 */
function nexa_pro_sanitize_options( $input ) {
	$defaults = nexa_pro_get_default_options();
	$existing = get_option( 'nexa_pro_options', array() );

	if ( ! is_array( $existing ) ) {
		$existing = array();
	}

	$output = wp_parse_args( array_intersect_key( $existing, $defaults ), $defaults );

	if ( ! is_array( $input ) ) {
		return $output;
	}

	$input = array_intersect_key( $input, $defaults );

	foreach ( $input as $key => $value ) {
		switch ( $key ) {
			case 'brand_name':
			case 'brand_tagline':
			case 'header_cta_text':
			case 'hero_eyebrow':
			case 'hero_heading':
			case 'hero_primary_cta_text':
			case 'hero_secondary_cta_text':
				$output[ $key ] = sanitize_text_field( $value );
				break;

			case 'logo_attachment_id':
			case 'mobile_logo_attachment_id':
				$output[ $key ] = absint( $value );
				break;

			case 'display_brand_text':
			case 'sticky_header':
			case 'transparent_header':
			case 'header_cta_enabled':
			case 'mobile_cta_enabled':
				$output[ $key ] = '1' === (string) $value ? '1' : '0';
				break;

			case 'header_layout':
				$layout = sanitize_key( $value );

				if ( in_array( $layout, array( 'standard', 'centered' ), true ) ) {
					$output[ $key ] = $layout;
				}
				break;

			case 'hero_text':
				$output[ $key ] = sanitize_textarea_field( $value );
				break;

			case 'primary_color':
			case 'accent_color':
				$color = sanitize_hex_color( $value );

				if ( $color ) {
					$output[ $key ] = $color;
				}
				break;

			case 'header_cta_url':
			case 'hero_primary_cta_url':
			case 'hero_secondary_cta_url':
				$url = nexa_pro_sanitize_url_or_fragment( $value );

				if ( null !== $url ) {
					$output[ $key ] = $url;
				}
				break;
		}
	}

	return $output;
}
