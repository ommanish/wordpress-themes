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
 * Get schemas for homepage repeater options.
 *
 * @return array
 */
function nexa_pro_get_repeater_schemas() {
	return array(
		'services_items' => array(
			'section' => 'services',
			'fields'  => array( 'id', 'title', 'text', 'link_text', 'link_url' ),
		),
		'features_items' => array(
			'section' => 'features',
			'fields'  => array( 'id', 'title', 'text' ),
		),
		'process_items'  => array(
			'section' => 'process',
			'fields'  => array( 'id', 'title', 'text' ),
		),
		'why_items'      => array(
			'section' => 'why',
			'fields'  => array( 'id', 'title', 'text' ),
		),
	);
}

/**
 * Build a stable, deterministic repeater item ID.
 *
 * @param string $section Section key.
 * @param string $title Item title.
 * @param array  $used_ids IDs already used in this repeater.
 * @param string $submitted_id Submitted ID.
 * @return string
 */
function nexa_pro_sanitize_repeater_item_id( $section, $title, $used_ids, $submitted_id = '' ) {
	$id = sanitize_key( $submitted_id );

	if ( '' === $id || isset( $used_ids[ $id ] ) ) {
		$title_base = sanitize_key( $title );
		$base       = sanitize_key( $section . ( $title_base ? '-' . $title_base : '' ) );

		if ( '' === $base ) {
			$base = sanitize_key( $section . '-item' );
		}

		$id     = $base;
		$suffix = 2;

		while ( isset( $used_ids[ $id ] ) ) {
			$id = $base . '-' . $suffix;
			$suffix++;
		}
	}

	return $id;
}

/**
 * Sanitize a homepage repeater option.
 *
 * @param mixed  $items Raw submitted items.
 * @param array  $schema Repeater schema.
 * @param string $key Option key.
 * @return array
 */
function nexa_pro_sanitize_repeater_items( $items, $schema, $key ) {
	if ( ! is_array( $items ) ) {
		return array();
	}

	$output   = array();
	$used_ids = array();
	$count    = 0;

	foreach ( $items as $item ) {
		if ( 20 <= $count ) {
			break;
		}

		$count++;

		if ( ! is_array( $item ) || ! empty( $item['_remove'] ) ) {
			continue;
		}

		$title = isset( $item['title'] ) ? sanitize_text_field( $item['title'] ) : '';

		if ( '' === $title ) {
			continue;
		}

		$id  = nexa_pro_sanitize_repeater_item_id(
			$schema['section'],
			$title,
			$used_ids,
			isset( $item['id'] ) ? $item['id'] : ''
		);
		$row = array(
			'id'    => $id,
			'title' => $title,
			'text'  => isset( $item['text'] ) ? sanitize_textarea_field( $item['text'] ) : '',
		);

		if ( 'services_items' === $key ) {
			$url              = isset( $item['link_url'] ) ? nexa_pro_sanitize_url_or_fragment( $item['link_url'] ) : '';
			$row['link_text'] = isset( $item['link_text'] ) ? sanitize_text_field( $item['link_text'] ) : '';
			$row['link_url']  = null === $url ? '' : $url;
		}

		$output[]        = $row;
		$used_ids[ $id ] = true;
	}

	return $output;
}

/**
 * Sanitize an image attachment ID for storage.
 *
 * @param mixed $attachment_id Attachment ID.
 * @return int
 */
function nexa_pro_sanitize_image_attachment_id( $attachment_id ) {
	if ( ! is_scalar( $attachment_id ) ) {
		return 0;
	}

	$attachment_id = absint( $attachment_id );

	return nexa_pro_is_valid_image_attachment_id( $attachment_id ) ? $attachment_id : 0;
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
	$schemas  = nexa_pro_get_repeater_schemas();

	if ( ! is_array( $existing ) ) {
		$existing = array();
	}

	$output = wp_parse_args( array_intersect_key( $existing, $defaults ), $defaults );

	if ( ! is_array( $input ) ) {
		return $output;
	}

	$submitted_repeaters = array();
	$reset_homepage_order = isset( $input['homepage_section_order_reset'] ) && '1' === (string) $input['homepage_section_order_reset'];

	foreach ( $schemas as $repeater_key => $schema ) {
		$marker_key = $repeater_key . '_submitted';

		if ( isset( $input[ $marker_key ] ) && '1' === (string) $input[ $marker_key ] ) {
			$submitted_repeaters[ $repeater_key ] = $schema;
		}
	}

	if ( $reset_homepage_order ) {
		$output['homepage_section_order'] = nexa_pro_get_default_homepage_section_order();
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

			case 'about_image_id':
			case 'services_background_image_id':
			case 'features_background_image_id':
			case 'process_background_image_id':
			case 'why_image_id':
			case 'portfolio_image_id':
			case 'testimonials_background_image_id':
			case 'team_background_image_id':
			case 'cta_background_image_id':
			case 'contact_background_image_id':
				$output[ $key ] = nexa_pro_sanitize_image_attachment_id( $value );
				break;

			case 'display_brand_text':
			case 'sticky_header':
			case 'transparent_header':
			case 'header_cta_enabled':
			case 'mobile_cta_enabled':
			case 'about_show':
			case 'services_show':
			case 'features_show':
			case 'process_show':
			case 'why_show':
			case 'cta_show':
				$output[ $key ] = '1' === (string) $value ? '1' : '0';
				break;

			case 'header_layout':
				$layout = sanitize_key( $value );

				if ( in_array( $layout, array( 'standard', 'centered' ), true ) ) {
					$output[ $key ] = $layout;
				}
				break;

			case 'hero_text':
			case 'about_text':
			case 'services_text':
			case 'features_text':
			case 'process_text':
			case 'why_text':
			case 'cta_text':
				$output[ $key ] = sanitize_textarea_field( $value );
				break;

			case 'about_label':
			case 'services_label':
			case 'features_label':
			case 'process_label':
			case 'why_label':
			case 'cta_button_text':
				$output[ $key ] = sanitize_text_field( $value );
				break;

			case 'about_heading':
			case 'services_heading':
			case 'features_heading':
			case 'process_heading':
			case 'why_heading':
			case 'cta_heading':
				$text = sanitize_text_field( $value );

				if ( '' !== $text ) {
					$output[ $key ] = $text;
				} elseif ( empty( $output[ $key ] ) ) {
					$output[ $key ] = $defaults[ $key ];
				}
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
			case 'cta_button_url':
				$url = nexa_pro_sanitize_url_or_fragment( $value );

				if ( null !== $url ) {
					$output[ $key ] = $url;
				}
				break;

			case 'services_items':
			case 'features_items':
			case 'process_items':
			case 'why_items':
				if ( isset( $submitted_repeaters[ $key ] ) ) {
					$output[ $key ] = nexa_pro_sanitize_repeater_items( $value, $submitted_repeaters[ $key ], $key );
				}
				break;

			case 'homepage_section_order':
				if ( ! $reset_homepage_order && is_array( $value ) ) {
					$output[ $key ] = nexa_pro_normalize_homepage_section_order( $value );
				}
				break;
		}
	}

	foreach ( $submitted_repeaters as $key => $schema ) {
		if ( ! array_key_exists( $key, $input ) ) {
			$output[ $key ] = array();
		}
	}

	return $output;
}
