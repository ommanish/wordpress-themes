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
 * Sanitize an absolute URL, site-relative path, or same-page fragment URL.
 *
 * @param string $url URL value.
 * @return string|null
 */
function nexa_pro_sanitize_url_or_path_or_fragment( $url ) {
	$url = trim( (string) $url );

	if ( '' === $url ) {
		return '';
	}

	if ( 1 === preg_match( '/^#[A-Za-z][A-Za-z0-9_-]*$/', $url ) ) {
		return $url;
	}

	if ( 0 === strpos( $url, '/' ) && 0 !== strpos( $url, '//' ) ) {
		$sanitized_path = esc_url_raw( $url );

		return '' === $sanitized_path ? null : $sanitized_path;
	}

	$sanitized_url = esc_url_raw( $url, array( 'http', 'https' ) );

	return '' === $sanitized_url ? null : $sanitized_url;
}

/**
 * Sanitize an absolute HTTP/HTTPS URL.
 *
 * @param string $url URL value.
 * @return string
 */
function nexa_pro_sanitize_absolute_http_url( $url ) {
	$url = trim( (string) $url );

	if ( '' === $url ) {
		return '';
	}

	$sanitized_url = esc_url_raw( $url, array( 'http', 'https' ) );

	if ( '' === $sanitized_url ) {
		return '';
	}

	$parts = wp_parse_url( $sanitized_url );

	if ( empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
		return '';
	}

	return in_array( strtolower( $parts['scheme'] ), array( 'http', 'https' ), true ) ? $sanitized_url : '';
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
 * Sanitize a whitelisted choice.
 *
 * @param mixed $value Raw value.
 * @param array $allowed Allowed values.
 * @return string|null
 */
function nexa_pro_sanitize_choice( $value, $allowed ) {
	if ( ! is_scalar( $value ) ) {
		return null;
	}

	$value = sanitize_key( $value );

	return in_array( $value, $allowed, true ) ? $value : null;
}

/**
 * Sanitize a gradient direction token.
 *
 * @param mixed $value Raw value.
 * @return string|null
 */
function nexa_pro_sanitize_gradient_direction( $value ) {
	if ( ! is_scalar( $value ) ) {
		return null;
	}

	$value   = strtolower( trim( (string) $value ) );
	$allowed = array(
		'to-top',
		'to-right',
		'to-bottom',
		'to-left',
		'to-top-right',
		'to-bottom-right',
		'to-bottom-left',
		'to-top-left',
	);

	return in_array( $value, $allowed, true ) ? $value : null;
}

/**
 * Sanitize a section overlay opacity.
 *
 * @param mixed $value Raw value.
 * @return int|null
 */
function nexa_pro_sanitize_overlay_opacity( $value ) {
	if ( ! is_numeric( $value ) ) {
		return null;
	}

	return max( 0, min( 90, absint( $value ) ) );
}

/**
 * Sanitize a scroll offset in pixels.
 *
 * @param mixed $value Raw value.
 * @return int|null
 */
function nexa_pro_sanitize_scroll_offset( $value ) {
	if ( ! is_numeric( $value ) ) {
		return null;
	}

	return max( 0, min( 240, absint( $value ) ) );
}

/**
 * Sanitize limited legal modal content.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function nexa_pro_sanitize_legal_modal_content( $value ) {
	if ( ! is_scalar( $value ) ) {
		return '';
	}

	return wp_kses_post( $value );
}

/**
 * Sanitize dynamic reusable section design options.
 *
 * @param string $key Option key.
 * @param mixed  $value Raw value.
 * @param array  $output Current sanitized output.
 * @return array|null Updated output when handled; null otherwise.
 */
function nexa_pro_sanitize_section_design_option( $key, $value, $output ) {
	foreach ( nexa_pro_get_section_design_sections() as $section ) {
		$prefix = $section . '_';

		if ( $key === $prefix . 'background_type' ) {
			$choice = nexa_pro_sanitize_choice( $value, array( 'default', 'solid', 'gradient', 'image' ) );

			if ( null !== $choice ) {
				$output[ $key ] = $choice;
			}

			return $output;
		}

		if ( in_array( $key, array( $prefix . 'background_color', $prefix . 'gradient_start', $prefix . 'gradient_end', $prefix . 'overlay_color' ), true ) ) {
			$color = nexa_pro_sanitize_design_color( $value );

			if ( null !== $color ) {
				$output[ $key ] = $color;
			}

			return $output;
		}

		if ( $key === $prefix . 'gradient_direction' ) {
			$direction = nexa_pro_sanitize_gradient_direction( $value );

			if ( null !== $direction ) {
				$output[ $key ] = $direction;
			}

			return $output;
		}

		if ( $key === $prefix . 'overlay_enabled' ) {
			$output[ $key ] = '1' === (string) $value ? '1' : '0';

			return $output;
		}

		if ( $key === $prefix . 'overlay_opacity' ) {
			$opacity = nexa_pro_sanitize_overlay_opacity( $value );

			if ( null !== $opacity ) {
				$output[ $key ] = $opacity;
			}

			return $output;
		}

		if ( $key === $prefix . 'text_theme' ) {
			$theme = nexa_pro_sanitize_choice( $value, array( 'automatic', 'light', 'dark' ) );

			if ( null !== $theme ) {
				$output[ $key ] = $theme;
			}

			return $output;
		}
	}

	return null;
}

/**
 * Sanitize a design color value.
 *
 * @param mixed $value Color value.
 * @return string|null
 */
function nexa_pro_sanitize_design_color( $value ) {
	if ( ! is_scalar( $value ) ) {
		return null;
	}

	$color = sanitize_hex_color( $value );

	return $color ? strtolower( $color ) : null;
}

/**
 * Sanitize a font family choice.
 *
 * @param mixed $value Font choice value.
 * @return string|null
 */
function nexa_pro_sanitize_font_choice( $value ) {
	if ( ! is_scalar( $value ) ) {
		return null;
	}

	$value  = sanitize_key( $value );
	$stacks = nexa_pro_get_font_stack_map();

	return isset( $stacks[ $value ] ) ? $value : null;
}

/**
 * Sanitize a numeric design value.
 *
 * @param string $key Option key.
 * @param mixed  $value Raw value.
 * @return int|float|null
 */
function nexa_pro_sanitize_design_number( $key, $value ) {
	if ( ! is_numeric( $value ) ) {
		return null;
	}

	$number   = nexa_pro_clamp_design_number( $key, $value );
	$defaults = nexa_pro_get_default_global_design_options();

	if ( $number === $defaults[ $key ] && (float) $value !== (float) $defaults[ $key ] ) {
		return null;
	}

	return $number;
}

/**
 * Sanitize a design font weight.
 *
 * @param string $key Option key.
 * @param mixed  $value Raw value.
 * @return string|null
 */
function nexa_pro_sanitize_design_font_weight( $key, $value ) {
	if ( ! is_scalar( $value ) ) {
		return null;
	}

	$value = (string) absint( $value );

	return in_array( $value, nexa_pro_get_allowed_font_weights( $key ), true ) ? $value : null;
}

/**
 * Get option keys that store image attachment IDs.
 *
 * @return array
 */
function nexa_pro_get_media_option_keys() {
	return array_values(
		array_unique(
			array_merge(
				array(
					'logo_attachment_id',
					'mobile_logo_attachment_id',
					'footer_logo_id',
					'about_image_id',
					'why_image_id',
					'portfolio_image_id',
					'hero_mobile_image_id',
				),
				array_values( nexa_pro_get_section_background_image_option_keys() )
			)
		)
	);
}

/**
 * Sanitize theme options against a caller-provided base option array.
 *
 * Missing or invalid submitted values preserve the existing saved value, falling
 * back to defaults when no saved value exists. Full replacements rebuild from
 * defaults and process known submitted repeaters without tab markers.
 *
 * @param mixed      $input Raw submitted option value.
 * @param array      $base_options Existing option base.
 * @param bool       $full_replacement Whether this is a full settings replacement.
 * @param array|null $report Optional normalization report.
 * @return array
 */
function nexa_pro_sanitize_options_with_base( $input, $base_options = array(), $full_replacement = false, &$report = null ) {
	$defaults = nexa_pro_get_default_options();
	$existing = is_array( $base_options ) ? $base_options : array();
	$schemas  = nexa_pro_get_repeater_schemas();

	if ( $full_replacement ) {
		$report = array(
			'media_references' => 0,
			'media_cleared'    => 0,
		);
	}

	$output = wp_parse_args( array_intersect_key( $existing, $defaults ), $defaults );

	if ( ! is_array( $input ) ) {
		return $output;
	}

	$submitted_repeaters  = array();
	$design_keys          = nexa_pro_get_global_design_option_keys();
	$reset_homepage_order = isset( $input['homepage_section_order_reset'] ) && '1' === (string) $input['homepage_section_order_reset'];
	$reset_global_design  = isset( $input['global_design_reset'] ) && '1' === (string) $input['global_design_reset'];

	foreach ( $schemas as $repeater_key => $schema ) {
		$marker_key = $repeater_key . '_submitted';

		if ( isset( $input[ $marker_key ] ) && '1' === (string) $input[ $marker_key ] ) {
			$submitted_repeaters[ $repeater_key ] = $schema;
		} elseif ( $full_replacement && array_key_exists( $repeater_key, $input ) ) {
			$submitted_repeaters[ $repeater_key ] = $schema;
		}
	}

	if ( $reset_homepage_order ) {
		$output['homepage_section_order'] = nexa_pro_get_default_homepage_section_order();
	}

	if ( $reset_global_design ) {
		$design_defaults = nexa_pro_get_default_global_design_options();

		foreach ( $design_keys as $design_key ) {
			$output[ $design_key ] = $design_defaults[ $design_key ];
		}
	}

	$input = array_intersect_key( $input, $defaults );

	foreach ( $input as $key => $value ) {
		if ( $reset_global_design && in_array( $key, $design_keys, true ) ) {
			continue;
		}

		$section_design_output = nexa_pro_sanitize_section_design_option( $key, $value, $output );

		if ( null !== $section_design_output ) {
			$output = $section_design_output;
			continue;
		}

		switch ( $key ) {
			case 'brand_name':
			case 'brand_tagline':
			case 'header_cta_text':
			case 'hero_eyebrow':
			case 'hero_heading':
			case 'hero_primary_cta_text':
			case 'hero_secondary_cta_text':
			case 'footer_brand_text':
			case 'footer_privacy_label':
			case 'footer_terms_label':
			case 'contact_phone':
			case 'schedule_modal_title':
			case 'schedule_email_label':
			case 'schedule_calendar_label':
			case 'schedule_email_subject':
			case 'footer_privacy_modal_title':
			case 'footer_terms_modal_title':
				$output[ $key ] = sanitize_text_field( $value );
				break;

			case 'logo_attachment_id':
			case 'mobile_logo_attachment_id':
				$output[ $key ] = absint( $value );
				break;

			case 'footer_logo_id':
				$output[ $key ] = nexa_pro_sanitize_image_attachment_id( $value );
				break;

			case 'about_image_id':
			case 'why_image_id':
			case 'portfolio_image_id':
			case 'hero_image_id':
			case 'hero_mobile_image_id':
			case 'about_background_image_id':
			case 'services_background_image_id':
			case 'features_background_image_id':
			case 'process_background_image_id':
			case 'why_background_image_id':
			case 'portfolio_background_image_id':
			case 'testimonials_background_image_id':
			case 'team_background_image_id':
			case 'faq_background_image_id':
			case 'cta_background_image_id':
			case 'contact_background_image_id':
				$output[ $key ] = nexa_pro_sanitize_image_attachment_id( $value );
				break;

			case 'display_brand_text':
			case 'sticky_header':
			case 'transparent_header':
			case 'header_cta_enabled':
			case 'mobile_cta_enabled':
			case 'footer_show_brand_text':
			case 'footer_menu_enabled':
			case 'schedule_modal_enabled':
			case 'hero_show_image_mobile':
			case 'single_page_smooth_scroll':
			case 'single_page_active_state':
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

			case 'hero_layout':
				$layout = nexa_pro_sanitize_choice( $value, array( 'content-only', 'image-left', 'image-right', 'background-image' ) );

				if ( null !== $layout ) {
					$output[ $key ] = $layout;
				}
				break;

			case 'hero_image_position':
				$position = nexa_pro_sanitize_choice( $value, array( 'left', 'center', 'right' ) );

				if ( null !== $position ) {
					$output[ $key ] = $position;
				}
				break;

			case 'hero_image_object_position':
				$object_position = trim( strtolower( sanitize_text_field( $value ) ) );
				$allowed         = array( 'center center', 'top center', 'bottom center', 'left center', 'right center' );

				if ( in_array( $object_position, $allowed, true ) ) {
					$output[ $key ] = $object_position;
				}
				break;

			case 'hero_content_alignment':
				$alignment = nexa_pro_sanitize_choice( $value, array( 'left', 'center', 'right' ) );

				if ( null !== $alignment ) {
					$output[ $key ] = $alignment;
				}
				break;

			case 'hero_content_width':
				$width = nexa_pro_sanitize_choice( $value, array( 'narrow', 'standard', 'wide' ) );

				if ( null !== $width ) {
					$output[ $key ] = $width;
				}
				break;

			case 'navigation_mode':
				$mode = nexa_pro_sanitize_choice( $value, array( 'multipage', 'single-page' ) );

				if ( null !== $mode ) {
					$output[ $key ] = $mode;
				}
				break;

			case 'single_page_scroll_offset':
				$offset = nexa_pro_sanitize_scroll_offset( $value );

				if ( null !== $offset ) {
					$output[ $key ] = $offset;
				}
				break;

			case 'hero_text':
			case 'about_text':
			case 'services_text':
			case 'features_text':
			case 'process_text':
			case 'why_text':
			case 'cta_text':
			case 'footer_description':
			case 'footer_copyright':
			case 'contact_address':
			case 'contact_business_hours':
			case 'schedule_modal_text':
			case 'schedule_email_body':
				$output[ $key ] = sanitize_textarea_field( $value );
				break;

			case 'footer_privacy_modal_content':
			case 'footer_terms_modal_content':
				$output[ $key ] = nexa_pro_sanitize_legal_modal_content( $value );
				break;

			case 'contact_email':
				$email          = sanitize_email( $value );
				$output[ $key ] = is_email( $email ) ? $email : '';
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

			case 'color_primary':
			case 'color_secondary':
			case 'color_accent':
			case 'color_background':
			case 'color_surface':
			case 'color_text':
			case 'color_text_muted':
			case 'color_heading':
			case 'color_border':
			case 'color_button_primary_background':
			case 'color_button_primary_text':
			case 'color_button_primary_hover':
			case 'color_button_secondary_background':
			case 'color_button_secondary_text':
			case 'color_button_secondary_border':
			case 'color_link':
			case 'color_link_hover':
				$color = nexa_pro_sanitize_design_color( $value );

				if ( null !== $color ) {
					$output[ $key ] = $color;
				}
				break;

			case 'font_body':
			case 'font_heading':
				$font = nexa_pro_sanitize_font_choice( $value );

				if ( null !== $font ) {
					$output[ $key ] = $font;
				}
				break;

			case 'font_size_base':
			case 'line_height_body':
			case 'line_height_heading':
				$number = nexa_pro_sanitize_design_number( $key, $value );

				if ( null !== $number ) {
					$output[ $key ] = $number;
				}
				break;

			case 'font_weight_heading':
			case 'font_weight_button':
				$weight = nexa_pro_sanitize_design_font_weight( $key, $value );

				if ( null !== $weight ) {
					$output[ $key ] = $weight;
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

			case 'footer_privacy_url':
			case 'footer_terms_url':
				$url            = nexa_pro_sanitize_url_or_path_or_fragment( $value );
				$output[ $key ] = null === $url ? '' : $url;
				break;

			case 'footer_privacy_behavior':
			case 'footer_terms_behavior':
				$behavior = nexa_pro_sanitize_choice( $value, array( 'link', 'modal', 'hidden' ) );

				if ( null !== $behavior ) {
					$output[ $key ] = $behavior;
				}
				break;

			case 'social_linkedin_url':
			case 'social_github_url':
			case 'social_x_url':
			case 'social_facebook_url':
			case 'social_instagram_url':
			case 'schedule_calendar_url':
				$output[ $key ] = nexa_pro_sanitize_absolute_http_url( $value );
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

	if ( $full_replacement ) {
		foreach ( nexa_pro_get_media_option_keys() as $media_key ) {
			$raw_media_value = isset( $input[ $media_key ] ) && is_scalar( $input[ $media_key ] ) ? absint( $input[ $media_key ] ) : 0;

			if ( $raw_media_value ) {
				$report['media_references']++;
			}

			$stored_media_value = absint( isset( $output[ $media_key ] ) ? $output[ $media_key ] : 0 );
			$output[ $media_key ] = nexa_pro_sanitize_image_attachment_id( $stored_media_value );

			if ( $raw_media_value && ! $output[ $media_key ] ) {
				$report['media_cleared']++;
			}
		}
	}

	return $output;
}

/**
 * Sanitize theme options for normal Settings API storage.
 *
 * @param mixed $input Raw submitted option value.
 * @return array
 */
function nexa_pro_sanitize_options( $input ) {
	$existing = get_option( 'nexa_pro_options', array() );
	$report   = null;

	if ( ! is_array( $existing ) ) {
		$existing = array();
	}

	return nexa_pro_sanitize_options_with_base( $input, $existing, false, $report );
}

/**
 * Normalize a full settings payload for import, presets, reset, and rollback.
 *
 * @param mixed      $settings Raw settings payload.
 * @param array|null $report Optional normalization report.
 * @return array
 */
function nexa_pro_normalize_full_settings( $settings, &$report = null ) {
	$defaults = nexa_pro_get_default_options();

	if ( ! is_array( $settings ) ) {
		$settings = array();
	}

	return nexa_pro_sanitize_options_with_base(
		array_intersect_key( $settings, $defaults ),
		$defaults,
		true,
		$report
	);
}
