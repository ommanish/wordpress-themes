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
	static $options = null;

	if ( null !== $options ) {
		return $options;
	}

	$defaults = nexa_pro_get_default_options();
	$saved    = get_option( 'nexa_pro_options', array() );

	if ( ! is_array( $saved ) ) {
		$saved = array();
	}

	$known_saved = array_intersect_key( $saved, $defaults );

	$options = wp_parse_args( $known_saved, $defaults );

	return $options;
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
 * Get approved font family choices.
 *
 * @return array
 */
function nexa_pro_get_font_family_choices() {
	return array(
		'system-ui'       => __( 'System UI', 'nexa-pro' ),
		'arial'           => __( 'Arial', 'nexa-pro' ),
		'helvetica'       => __( 'Helvetica', 'nexa-pro' ),
		'georgia'         => __( 'Georgia', 'nexa-pro' ),
		'times-new-roman' => __( 'Times New Roman', 'nexa-pro' ),
		'verdana'         => __( 'Verdana', 'nexa-pro' ),
		'trebuchet-ms'    => __( 'Trebuchet MS', 'nexa-pro' ),
		'tahoma'          => __( 'Tahoma', 'nexa-pro' ),
		'courier-new'     => __( 'Courier New', 'nexa-pro' ),
	);
}

/**
 * Get approved font family stacks.
 *
 * @return array
 */
function nexa_pro_get_font_stack_map() {
	return array(
		'system-ui'       => '-apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
		'arial'           => 'Arial, Helvetica, sans-serif',
		'helvetica'       => 'Helvetica, Arial, sans-serif',
		'georgia'         => 'Georgia, "Times New Roman", serif',
		'times-new-roman' => '"Times New Roman", Times, serif',
		'verdana'         => 'Verdana, Geneva, sans-serif',
		'trebuchet-ms'    => '"Trebuchet MS", "Lucida Grande", sans-serif',
		'tahoma'          => 'Tahoma, Geneva, sans-serif',
		'courier-new'     => '"Courier New", Courier, monospace',
	);
}

/**
 * Get allowed font weights for a typography option.
 *
 * @param string $key Option key.
 * @return array
 */
function nexa_pro_get_allowed_font_weights( $key ) {
	if ( 'font_weight_button' === $key ) {
		return array( '400', '500', '600', '700' );
	}

	return array( '400', '500', '600', '700', '800' );
}

/**
 * Get a strictly validated design color option.
 *
 * @param string $key Color option key.
 * @return string
 */
function nexa_pro_get_design_color( $key ) {
	$defaults = nexa_pro_get_default_global_design_options();

	if ( ! in_array( $key, nexa_pro_get_design_color_option_keys(), true ) ) {
		return '';
	}

	$value = sanitize_hex_color( nexa_pro_get_raw_option( $key, $defaults[ $key ] ) );

	if ( ! $value ) {
		$value = $defaults[ $key ];
	}

	return strtolower( $value );
}

/**
 * Get a validated font choice key.
 *
 * @param string $key Font option key.
 * @return string
 */
function nexa_pro_get_font_choice( $key ) {
	$defaults = nexa_pro_get_default_global_design_options();
	$choices  = nexa_pro_get_font_stack_map();
	$default  = isset( $defaults[ $key ] ) ? $defaults[ $key ] : 'system-ui';
	$value    = (string) nexa_pro_get_raw_option( $key, $default );

	return isset( $choices[ $value ] ) ? $value : $default;
}

/**
 * Get an approved font stack for a font option.
 *
 * @param string $key Font option key.
 * @return string
 */
function nexa_pro_get_font_stack( $key ) {
	$stacks = nexa_pro_get_font_stack_map();
	$choice = nexa_pro_get_font_choice( $key );

	return isset( $stacks[ $choice ] ) ? $stacks[ $choice ] : $stacks['system-ui'];
}

/**
 * Clamp a numeric design option.
 *
 * @param string $key Option key.
 * @param mixed  $value Raw value.
 * @return int|float
 */
function nexa_pro_clamp_design_number( $key, $value ) {
	$defaults = nexa_pro_get_default_global_design_options();

	switch ( $key ) {
		case 'font_size_base':
			$value = absint( $value );

			if ( 14 <= $value && 22 >= $value ) {
				return $value;
			}

			return $defaults[ $key ];

		case 'line_height_body':
			$value = is_numeric( $value ) ? round( (float) $value, 2 ) : 0;

			if ( 1.2 <= $value && 2.0 >= $value ) {
				return $value;
			}

			return $defaults[ $key ];

		case 'line_height_heading':
			$value = is_numeric( $value ) ? round( (float) $value, 2 ) : 0;

			if ( 1.0 <= $value && 1.6 >= $value ) {
				return $value;
			}

			return $defaults[ $key ];
	}

	return isset( $defaults[ $key ] ) ? $defaults[ $key ] : 0;
}

/**
 * Get a validated numeric typography option.
 *
 * @param string $key Option key.
 * @return int|float
 */
function nexa_pro_get_design_number( $key ) {
	$defaults = nexa_pro_get_default_global_design_options();

	return nexa_pro_clamp_design_number(
		$key,
		nexa_pro_get_raw_option( $key, isset( $defaults[ $key ] ) ? $defaults[ $key ] : 0 )
	);
}

/**
 * Get a validated font weight option.
 *
 * @param string $key Option key.
 * @return string
 */
function nexa_pro_get_design_font_weight( $key ) {
	$defaults = nexa_pro_get_default_global_design_options();
	$value    = (string) nexa_pro_get_raw_option( $key, isset( $defaults[ $key ] ) ? $defaults[ $key ] : '700' );

	return in_array( $value, nexa_pro_get_allowed_font_weights( $key ), true ) ? $value : $defaults[ $key ];
}

/**
 * Get generated CSS custom properties for global design settings.
 *
 * Only values that differ from the stylesheet defaults are emitted, preserving
 * the static CSS fallback values for an unsaved installation.
 *
 * @param string $selector CSS selector to scope variables.
 * @return string
 */
function nexa_pro_get_global_design_css( $selector = ':root' ) {
	$defaults     = nexa_pro_get_default_global_design_options();
	$declarations = array();
	$selector     = trim( (string) $selector );

	if ( '' === $selector ) {
		return '';
	}

	$color_map = array(
		'color_primary'                     => array( '--nexa-pro-color-primary' ),
		'color_secondary'                   => array( '--nexa-pro-color-secondary' ),
		'color_accent'                      => array( '--nexa-pro-color-accent', '--nexa-pro-color-focus' ),
		'color_background'                  => array( '--nexa-pro-color-canvas', '--nexa-pro-color-background' ),
		'color_surface'                     => array( '--nexa-pro-color-surface-raised' ),
		'color_text'                        => array( '--nexa-pro-color-ink', '--nexa-pro-color-text' ),
		'color_text_muted'                  => array( '--nexa-pro-color-muted' ),
		'color_heading'                     => array( '--nexa-pro-color-heading' ),
		'color_border'                      => array( '--nexa-pro-color-border' ),
		'color_button_primary_background'   => array( '--nexa-pro-color-button-primary-background' ),
		'color_button_primary_text'         => array( '--nexa-pro-color-button-primary-text' ),
		'color_button_primary_hover'        => array( '--nexa-pro-color-button-primary-hover' ),
		'color_button_secondary_background' => array( '--nexa-pro-color-button-secondary-background' ),
		'color_button_secondary_text'       => array( '--nexa-pro-color-button-secondary-text' ),
		'color_button_secondary_border'     => array( '--nexa-pro-color-button-secondary-border' ),
		'color_link'                        => array( '--nexa-pro-color-link' ),
		'color_link_hover'                  => array( '--nexa-pro-color-link-hover' ),
	);

	foreach ( $color_map as $option_key => $css_vars ) {
		$value = nexa_pro_get_design_color( $option_key );

		if ( $value === $defaults[ $option_key ] ) {
			continue;
		}

		foreach ( $css_vars as $css_var ) {
			$declarations[] = $css_var . ':' . $value;
		}
	}

	$font_map = array(
		'font_body'    => array( '--nexa-pro-font-body', '--nexa-pro-font-sans' ),
		'font_heading' => array( '--nexa-pro-font-heading' ),
	);

	foreach ( $font_map as $option_key => $css_vars ) {
		$value = nexa_pro_get_font_choice( $option_key );

		if ( $value === $defaults[ $option_key ] ) {
			continue;
		}

		foreach ( $css_vars as $css_var ) {
			$declarations[] = $css_var . ':' . nexa_pro_get_font_stack( $option_key );
		}
	}

	$font_size_base = nexa_pro_get_design_number( 'font_size_base' );

	if ( $font_size_base !== $defaults['font_size_base'] ) {
		$declarations[] = '--nexa-pro-font-size-base:' . absint( $font_size_base ) . 'px';
	}

	$line_height_body = nexa_pro_get_design_number( 'line_height_body' );

	if ( (float) $line_height_body !== (float) $defaults['line_height_body'] ) {
		$declarations[] = '--nexa-pro-line-height-base:' . rtrim( rtrim( sprintf( '%.2F', $line_height_body ), '0' ), '.' );
	}

	$line_height_heading = nexa_pro_get_design_number( 'line_height_heading' );

	if ( (float) $line_height_heading !== (float) $defaults['line_height_heading'] ) {
		$declarations[] = '--nexa-pro-line-height-heading:' . rtrim( rtrim( sprintf( '%.2F', $line_height_heading ), '0' ), '.' );
	}

	$heading_weight = nexa_pro_get_design_font_weight( 'font_weight_heading' );

	if ( $heading_weight !== $defaults['font_weight_heading'] ) {
		$declarations[] = '--nexa-pro-font-weight-heading:' . $heading_weight;
	}

	$button_weight = nexa_pro_get_design_font_weight( 'font_weight_button' );

	if ( $button_weight !== $defaults['font_weight_button'] ) {
		$declarations[] = '--nexa-pro-font-weight-button:' . $button_weight;
	}

	if ( empty( $declarations ) ) {
		return '';
	}

	return $selector . '{' . implode( ';', $declarations ) . ';}';
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
