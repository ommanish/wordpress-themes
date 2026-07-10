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
 * Get a known theme option without treating empty saved values as missing.
 *
 * @param string $key Option key.
 * @param mixed  $fallback Optional fallback for unknown or unsaved keys.
 * @return mixed
 */
function nexa_pro_get_raw_option( $key, $fallback = null ) {
	$defaults = nexa_pro_get_default_options();

	if ( ! array_key_exists( $key, $defaults ) ) {
		return $fallback;
	}

	$saved = get_option( 'nexa_pro_options', array() );

	if ( ! is_array( $saved ) ) {
		$saved = array();
	}

	$known_saved = array_intersect_key( $saved, $defaults );

	if ( array_key_exists( $key, $known_saved ) ) {
		return $known_saved[ $key ];
	}

	return null !== $fallback ? $fallback : $defaults[ $key ];
}

/**
 * Normalize a homepage section order array.
 *
 * @param mixed $order Section order.
 * @return array
 */
function nexa_pro_normalize_homepage_section_order( $order ) {
	$default_order = nexa_pro_get_default_homepage_section_order();
	$allowed       = array_fill_keys( $default_order, true );
	$normalized    = array();
	$used          = array();

	if ( ! is_array( $order ) ) {
		$order = $default_order;
	}

	foreach ( $order as $section ) {
		if ( ! is_scalar( $section ) ) {
			continue;
		}

		$section_key = sanitize_key( $section );

		if ( ! isset( $allowed[ $section_key ] ) || isset( $used[ $section_key ] ) ) {
			continue;
		}

		$normalized[]         = $section_key;
		$used[ $section_key ] = true;
	}

	foreach ( $default_order as $section_key ) {
		if ( ! isset( $used[ $section_key ] ) ) {
			$normalized[] = $section_key;
		}
	}

	return $normalized;
}

/**
 * Get the normalized movable homepage section order.
 *
 * @return array
 */
function nexa_pro_get_homepage_section_order() {
	return nexa_pro_normalize_homepage_section_order(
		nexa_pro_get_raw_option( 'homepage_section_order', nexa_pro_get_default_homepage_section_order() )
	);
}

/**
 * Determine whether an attachment ID is a valid image.
 *
 * @param mixed $attachment_id Attachment ID.
 * @return bool
 */
function nexa_pro_is_valid_image_attachment_id( $attachment_id ) {
	$attachment_id = absint( $attachment_id );

	return $attachment_id > 0 && wp_attachment_is_image( $attachment_id );
}

/**
 * Get a validated image attachment ID option.
 *
 * @param string $key Option key.
 * @return int
 */
function nexa_pro_get_image_attachment_id( $key ) {
	$attachment_id = absint( nexa_pro_get_raw_option( $key, 0 ) );

	return nexa_pro_is_valid_image_attachment_id( $attachment_id ) ? $attachment_id : 0;
}

/**
 * Normalize saved repeater items for read-only use.
 *
 * This defensively filters malformed rows, but does not generate IDs or repair
 * duplicates. Stable ID repair belongs to save-time sanitization.
 *
 * @param string $key Repeater option key.
 * @param array  $fields Allowed schema fields.
 * @return array
 */
function nexa_pro_get_repeater_items( $key, $fields ) {
	$items = nexa_pro_get_raw_option( $key, array() );

	if ( ! is_array( $items ) ) {
		return array();
	}

	$output = array();

	foreach ( $items as $item ) {
		if ( ! is_array( $item ) || empty( $item['title'] ) ) {
			continue;
		}

		$row = array();

		foreach ( $fields as $field ) {
			if ( 'id' === $field ) {
				$row['id'] = isset( $item['id'] ) ? sanitize_key( $item['id'] ) : '';
				continue;
			}

			$row[ $field ] = isset( $item[ $field ] ) ? (string) $item[ $field ] : '';
		}

		$output[] = $row;
	}

	return $output;
}

/**
 * Get service repeater items.
 *
 * @return array
 */
function nexa_pro_get_services_items() {
	return nexa_pro_get_repeater_items( 'services_items', array( 'id', 'title', 'text', 'link_text', 'link_url' ) );
}

/**
 * Get feature repeater items.
 *
 * @return array
 */
function nexa_pro_get_features_items() {
	return nexa_pro_get_repeater_items( 'features_items', array( 'id', 'title', 'text' ) );
}

/**
 * Get process repeater items.
 *
 * @return array
 */
function nexa_pro_get_process_items() {
	return nexa_pro_get_repeater_items( 'process_items', array( 'id', 'title', 'text' ) );
}

/**
 * Get why repeater items.
 *
 * @return array
 */
function nexa_pro_get_why_items() {
	return nexa_pro_get_repeater_items( 'why_items', array( 'id', 'title', 'text' ) );
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
