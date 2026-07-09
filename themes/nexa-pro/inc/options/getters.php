<?php
/**
 * Theme option getters.
 *
 * @package Nexa_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get merged theme options without mutating stored values.
 *
 * @return array
 */
function nexa_pro_get_options() {
	$defaults = nexa_pro_get_default_options();
	$saved    = get_option( 'nexa_pro_options', array() );

	if ( ! is_array( $saved ) ) {
		$saved = array();
	}

	$known_saved = array_intersect_key( $saved, $defaults );

	return wp_parse_args( $known_saved, $defaults );
}

/**
 * Get a single known theme option.
 *
 * @param string $key Option key.
 * @param mixed  $fallback Optional fallback for known keys with empty values or unknown keys.
 * @return mixed
 */
function nexa_pro_get_option( $key, $fallback = null ) {
	$defaults = nexa_pro_get_default_options();

	if ( ! array_key_exists( $key, $defaults ) ) {
		return $fallback;
	}

	$options = nexa_pro_get_options();

	if ( ! array_key_exists( $key, $options ) || '' === $options[ $key ] ) {
		return null !== $fallback ? $fallback : $defaults[ $key ];
	}

	return $options[ $key ];
}

/**
 * Get the configured brand name with WordPress site title fallback.
 *
 * @return string
 */
function nexa_pro_get_brand_name() {
	return nexa_pro_get_option( 'brand_name', get_bloginfo( 'name' ) );
}

/**
 * Get the configured brand tagline with WordPress tagline fallback.
 *
 * @return string
 */
function nexa_pro_get_brand_tagline() {
	return nexa_pro_get_option( 'brand_tagline', get_bloginfo( 'description', 'display' ) );
}

