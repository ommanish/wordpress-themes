<?php
/**
 * Component instance sanitizer.
 *
 * @package Nexa_Pro_Core
 */

namespace Nexa_Pro_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitization service.
 */
final class Sanitizer {
	/**
	 * Get fallback component types when the theme registry is unavailable.
	 *
	 * @return array
	 */
	public static function fallback_component_types() {
		return array(
			'hero',
			'about',
			'services',
			'features',
			'process',
			'why',
			'portfolio',
			'testimonials',
			'team',
			'faq',
			'cta',
			'contact',
		);
	}

	/**
	 * Get allowed component types.
	 *
	 * @return array
	 */
	public static function allowed_component_types() {
		if ( function_exists( 'nexa_pro_get_component_types' ) ) {
			$types = \nexa_pro_get_component_types();
		} else {
			$types = self::fallback_component_types();
		}

		$types = is_array( $types ) ? $types : array();

		/**
		 * Filter allowed component types for storage.
		 *
		 * @param array $types Component types.
		 */
		$types = \apply_filters( 'nexa_pro_core_allowed_component_types', $types );

		$allowed = array();

		foreach ( $types as $type ) {
			$type = \sanitize_key( $type );

			if ( '' !== $type && ! in_array( $type, $allowed, true ) ) {
				$allowed[] = $type;
			}
		}

		return $allowed;
	}

	/**
	 * Determine whether a component type is allowed.
	 *
	 * @param string $type Component type.
	 * @return bool
	 */
	public static function component_type_is_allowed( $type ) {
		$type = \sanitize_key( $type );

		if ( '' === $type ) {
			return false;
		}

		if ( function_exists( 'nexa_pro_component_type_exists' ) && \nexa_pro_component_type_exists( $type ) ) {
			return true;
		}

		return in_array( $type, self::allowed_component_types(), true );
	}

	/**
	 * Get layouts allowed for a component type.
	 *
	 * @param string $type Component type.
	 * @return array
	 */
	public static function allowed_layouts_for_type( $type ) {
		$type    = \sanitize_key( $type );
		$layouts = array( 'default' );

		if ( function_exists( 'nexa_pro_get_component_supported_layouts' ) ) {
			$theme_layouts = \nexa_pro_get_component_supported_layouts( $type );

			if ( is_array( $theme_layouts ) && $theme_layouts ) {
				$layouts = $theme_layouts;
			}
		}

		$layouts = array_merge(
			$layouts,
			array( 'content-only', 'image-left', 'image-right', 'background-image', 'card-grid', 'list', 'centered' )
		);

		return array_values( array_unique( array_map( 'sanitize_key', $layouts ) ) );
	}

	/**
	 * Sanitize a component instance.
	 *
	 * @param array $component     Raw component instance.
	 * @param array $existing      Existing component instance.
	 * @param bool  $touch_updated Whether to refresh the updated timestamp.
	 * @return array|\WP_Error
	 */
	public static function sanitize_component_instance( $component, $existing = array(), $touch_updated = false ) {
		if ( ! is_array( $component ) ) {
			return new \WP_Error(
				'nexa_pro_core_invalid_component',
				\__( 'Component data must be an array.', 'nexa-pro-core' )
			);
		}

		$component_type = isset( $component['component_type'] ) ? \sanitize_key( $component['component_type'] ) : '';

		if ( '' === $component_type && ! empty( $existing['component_type'] ) ) {
			$component_type = \sanitize_key( $existing['component_type'] );
		}

		if ( ! self::component_type_is_allowed( $component_type ) ) {
			return new \WP_Error(
				'nexa_pro_core_invalid_component_type',
				\__( 'The component type is not supported.', 'nexa-pro-core' )
			);
		}

		$input_id    = isset( $component['instance_id'] ) ? (string) $component['instance_id'] : '';
		$existing_id = ! empty( $existing['instance_id'] ) ? (string) $existing['instance_id'] : '';
		$instance_id = self::is_valid_instance_id( $input_id ) ? $input_id : $existing_id;

		if ( ! self::is_valid_instance_id( $instance_id ) ) {
			$instance_id = self::generate_instance_id( $component_type );
		}

		$now        = self::current_timestamp();
		$created_at = isset( $component['created_at'] ) ? self::sanitize_timestamp( $component['created_at'], '' ) : '';

		if ( '' === $created_at && ! empty( $existing['created_at'] ) ) {
			$created_at = self::sanitize_timestamp( $existing['created_at'], '' );
		}

		if ( '' === $created_at ) {
			$created_at = $now;
		}

		$updated_at = isset( $component['updated_at'] ) ? self::sanitize_timestamp( $component['updated_at'], '' ) : '';

		if ( '' === $updated_at && ! empty( $existing['updated_at'] ) ) {
			$updated_at = self::sanitize_timestamp( $existing['updated_at'], '' );
		}

		if ( $touch_updated || '' === $updated_at ) {
			$updated_at = $now;
		}

		$layout = isset( $component['layout'] ) ? \sanitize_key( $component['layout'] ) : '';

		if ( '' === $layout && ! empty( $existing['layout'] ) ) {
			$layout = \sanitize_key( $existing['layout'] );
		}

		$allowed_layouts = self::allowed_layouts_for_type( $component_type );

		if ( ! in_array( $layout, $allowed_layouts, true ) ) {
			$layout = $allowed_layouts ? $allowed_layouts[0] : 'default';
		}

		$inheritance_mode = isset( $component['inheritance_mode'] ) ? \sanitize_key( $component['inheritance_mode'] ) : '';

		if ( ! in_array( $inheritance_mode, array( 'local', 'linked' ), true ) ) {
			$inheritance_mode = ! empty( $existing['inheritance_mode'] ) && 'linked' === $existing['inheritance_mode'] ? 'linked' : 'local';
		}

		$reusable_component_id = isset( $component['reusable_component_id'] ) ? absint( $component['reusable_component_id'] ) : 0;

		if ( 'local' === $inheritance_mode ) {
			$reusable_component_id = 0;
		}

		return array(
			'instance_id'           => $instance_id,
			'component_type'        => $component_type,
			'admin_title'           => isset( $component['admin_title'] ) ? \sanitize_text_field( $component['admin_title'] ) : self::default_admin_title( $component_type ),
			'enabled'               => self::sanitize_bool( isset( $component['enabled'] ) ? $component['enabled'] : true ),
			'order'                 => isset( $component['order'] ) ? absint( $component['order'] ) : 0,
			'layout'                => $layout,
			'content'               => isset( $component['content'] ) ? self::sanitize_content( $component['content'] ) : array(),
			'design'                => isset( $component['design'] ) ? self::sanitize_design( $component['design'] ) : array(),
			'navigation'            => isset( $component['navigation'] ) ? self::sanitize_navigation( $component['navigation'] ) : array(),
			'advanced'              => isset( $component['advanced'] ) ? self::sanitize_advanced( $component['advanced'] ) : array(),
			'reusable_component_id' => $reusable_component_id,
			'inheritance_mode'      => $inheritance_mode,
			'schema_version'        => (int) NEXA_PRO_CORE_SCHEMA_VERSION,
			'created_at'            => $created_at,
			'updated_at'            => $updated_at,
		);
	}

	/**
	 * Sanitize a component collection and normalize IDs, anchors, and order.
	 *
	 * @param array $components Raw components.
	 * @return array|\WP_Error
	 */
	public static function sanitize_component_collection( $components ) {
		if ( ! is_array( $components ) ) {
			return new \WP_Error(
				'nexa_pro_core_invalid_component_collection',
				\__( 'Component collection must be an array.', 'nexa-pro-core' )
			);
		}

		$normalized = array();
		$ids        = array();
		$anchors    = array();

		foreach ( $components as $component ) {
			$component = self::sanitize_component_instance( $component );

			if ( \is_wp_error( $component ) ) {
				return $component;
			}

			if ( in_array( $component['instance_id'], $ids, true ) ) {
				$component['instance_id'] = self::generate_unique_instance_id( $component['component_type'], $ids );
			}

			$anchor_id = isset( $component['navigation']['anchor_id'] ) ? $component['navigation']['anchor_id'] : '';

			if ( '' !== $anchor_id ) {
				if ( in_array( $anchor_id, $anchors, true ) ) {
					return new \WP_Error(
						'nexa_pro_core_duplicate_anchor',
						\__( 'Component navigation anchors must be unique on a page.', 'nexa-pro-core' )
					);
				}

				$anchors[] = $anchor_id;
			}

			$ids[]        = $component['instance_id'];
			$normalized[] = $component;
		}

		return self::normalize_order( $normalized );
	}

	/**
	 * Normalize component order.
	 *
	 * @param array $components Components.
	 * @return array
	 */
	public static function normalize_order( $components ) {
		usort(
			$components,
			function ( $a, $b ) {
				$a_order = isset( $a['order'] ) ? absint( $a['order'] ) : 0;
				$b_order = isset( $b['order'] ) ? absint( $b['order'] ) : 0;

				if ( $a_order === $b_order ) {
					return 0;
				}

				return $a_order < $b_order ? -1 : 1;
			}
		);

		foreach ( $components as $index => $component ) {
			$components[ $index ]['order'] = $index;
		}

		return $components;
	}

	/**
	 * Determine whether an instance ID is valid.
	 *
	 * @param string $instance_id Instance ID.
	 * @return bool
	 */
	public static function is_valid_instance_id( $instance_id ) {
		return is_string( $instance_id ) && 1 === preg_match( '/^nexa_[a-z0-9_-]+_[a-z0-9]{8,16}$/', $instance_id );
	}

	/**
	 * Generate an instance ID.
	 *
	 * @param string $component_type Component type.
	 * @return string
	 */
	public static function generate_instance_id( $component_type ) {
		$component_type = \sanitize_key( $component_type );
		$random         = function_exists( 'wp_generate_password' ) ? \wp_generate_password( 16, false, false ) : bin2hex( random_bytes( 8 ) );
		$random         = preg_replace( '/[^a-z0-9]/', '', strtolower( $random ) );

		if ( strlen( $random ) < 10 ) {
			$seed    = function_exists( 'wp_rand' ) ? \wp_rand() : random_int( 0, PHP_INT_MAX );
			$random .= substr( md5( $component_type . microtime( true ) . $seed ), 0, 10 );
		}

		return 'nexa_' . $component_type . '_' . substr( $random, 0, 10 );
	}

	/**
	 * Generate a unique instance ID.
	 *
	 * @param string $component_type Component type.
	 * @param array  $existing_ids   Existing IDs.
	 * @return string
	 */
	public static function generate_unique_instance_id( $component_type, $existing_ids = array() ) {
		for ( $attempt = 0; $attempt < 50; $attempt++ ) {
			$instance_id = self::generate_instance_id( $component_type );

			if ( ! in_array( $instance_id, $existing_ids, true ) ) {
				return $instance_id;
			}
		}

		return 'nexa_' . \sanitize_key( $component_type ) . '_' . substr( md5( wp_json_encode( $existing_ids ) . microtime( true ) ), 0, 10 );
	}

	/**
	 * Sanitize structured component content.
	 *
	 * @param mixed $value Raw value.
	 * @return mixed
	 */
	public static function sanitize_content( $value, $key_context = '' ) {
		if ( is_array( $value ) ) {
			$sanitized = array();

			foreach ( $value as $key => $item ) {
				$key = is_string( $key ) ? \sanitize_key( $key ) : absint( $key );

				if ( '' === (string) $key ) {
					continue;
				}

				$sanitized[ $key ] = self::sanitize_content( $item, (string) $key );
			}

			return $sanitized;
		}

		if ( is_bool( $value ) || is_int( $value ) || is_float( $value ) ) {
			return $value;
		}

		if ( ! is_scalar( $value ) ) {
			return '';
		}

		$value = (string) $value;

		if ( self::content_key_is_url( $key_context ) ) {
			return \esc_url_raw( $value );
		}

		if ( self::contains_disallowed_markup( $value ) ) {
			$value = \wp_kses_post( $value );
		}

		return \wp_kses_post( $value );
	}

	/**
	 * Sanitize design data.
	 *
	 * @param mixed $design Raw design data.
	 * @return array
	 */
	public static function sanitize_design( $design ) {
		if ( ! is_array( $design ) ) {
			return array();
		}

		$sanitized = array();

		if ( isset( $design['preset'] ) ) {
			$sanitized['preset'] = \sanitize_key( $design['preset'] );
		}

		if ( isset( $design['background_type'] ) ) {
			$background_type = \sanitize_key( $design['background_type'] );

			if ( in_array( $background_type, array( 'default', 'solid', 'gradient', 'image' ), true ) ) {
				$sanitized['background_type'] = $background_type;
			}
		}

		foreach ( array( 'background_color', 'gradient_start', 'gradient_end', 'overlay_color' ) as $color_key ) {
			if ( isset( $design[ $color_key ] ) ) {
				$color = \sanitize_hex_color( $design[ $color_key ] );

				if ( $color ) {
					$sanitized[ $color_key ] = $color;
				}
			}
		}

		if ( isset( $design['background_image_id'] ) ) {
			$sanitized['background_image_id'] = absint( $design['background_image_id'] );
		}

		if ( isset( $design['overlay_opacity'] ) ) {
			$sanitized['overlay_opacity'] = self::bounded_float( $design['overlay_opacity'], 0, 100 );
		}

		if ( isset( $design['text_theme'] ) ) {
			$text_theme = \sanitize_key( $design['text_theme'] );

			if ( in_array( $text_theme, array( 'automatic', 'light', 'dark' ), true ) ) {
				$sanitized['text_theme'] = $text_theme;
			}
		}

		foreach ( array( 'spacing', 'radius', 'shadow' ) as $token_key ) {
			if ( isset( $design[ $token_key ] ) ) {
				$sanitized[ $token_key ] = \sanitize_key( $design[ $token_key ] );
			}
		}

		return $sanitized;
	}

	/**
	 * Sanitize navigation data.
	 *
	 * @param mixed $navigation Raw navigation data.
	 * @return array
	 */
	public static function sanitize_navigation( $navigation ) {
		if ( ! is_array( $navigation ) ) {
			return array();
		}

		$sanitized = array();

		$sanitized['show_in_navigation'] = self::sanitize_bool( isset( $navigation['show_in_navigation'] ) ? $navigation['show_in_navigation'] : false );
		$sanitized['navigation_label']   = isset( $navigation['navigation_label'] ) ? \sanitize_text_field( $navigation['navigation_label'] ) : '';
		$sanitized['anchor_id']          = isset( $navigation['anchor_id'] ) ? self::sanitize_anchor_id( $navigation['anchor_id'] ) : '';
		$sanitized['parent_instance_id'] = isset( $navigation['parent_instance_id'] ) && self::is_valid_instance_id( $navigation['parent_instance_id'] ) ? $navigation['parent_instance_id'] : '';
		$sanitized['order_override']     = isset( $navigation['order_override'] ) && '' !== $navigation['order_override'] ? absint( $navigation['order_override'] ) : null;
		$sanitized['highlight_as_cta']   = self::sanitize_bool( isset( $navigation['highlight_as_cta'] ) ? $navigation['highlight_as_cta'] : false );

		$mobile_visibility = isset( $navigation['mobile_visibility'] ) ? \sanitize_key( $navigation['mobile_visibility'] ) : 'all';

		if ( ! in_array( $mobile_visibility, array( 'all', 'mobile', 'desktop', 'hidden' ), true ) ) {
			$mobile_visibility = 'all';
		}

		$sanitized['mobile_visibility'] = $mobile_visibility;

		return $sanitized;
	}

	/**
	 * Sanitize advanced component settings.
	 *
	 * @param mixed $advanced Raw advanced settings.
	 * @return array
	 */
	public static function sanitize_advanced( $advanced ) {
		if ( ! is_array( $advanced ) ) {
			return array();
		}

		$sanitized = array();

		if ( isset( $advanced['custom_css_class'] ) ) {
			$classes = preg_split( '/\s+/', (string) $advanced['custom_css_class'] );
			$classes = array_filter( array_map( 'sanitize_html_class', $classes ) );

			$sanitized['custom_css_class'] = implode( ' ', array_unique( $classes ) );
		}

		if ( isset( $advanced['aria_label'] ) ) {
			$sanitized['aria_label'] = \sanitize_text_field( $advanced['aria_label'] );
		}

		if ( isset( $advanced['semantic_element'] ) ) {
			$semantic_element = \sanitize_key( $advanced['semantic_element'] );

			if ( in_array( $semantic_element, array( 'section', 'div', 'article', 'aside', 'header', 'footer' ), true ) ) {
				$sanitized['semantic_element'] = $semantic_element;
			}
		}

		if ( isset( $advanced['device_visibility'] ) ) {
			$device_visibility = \sanitize_key( $advanced['device_visibility'] );

			if ( in_array( $device_visibility, array( 'all', 'mobile', 'desktop', 'hidden' ), true ) ) {
				$sanitized['device_visibility'] = $device_visibility;
			}
		}

		if ( isset( $advanced['animation_preset'] ) ) {
			$animation_preset = \sanitize_key( $advanced['animation_preset'] );

			if ( in_array( $animation_preset, array( 'none', 'fade', 'slide-up' ), true ) ) {
				$sanitized['animation_preset'] = $animation_preset;
			}
		}

		return $sanitized;
	}

	/**
	 * Sanitize an HTML ID/anchor token.
	 *
	 * @param mixed $anchor Raw anchor.
	 * @return string
	 */
	public static function sanitize_anchor_id( $anchor ) {
		$anchor = strtolower( trim( (string) $anchor ) );
		$anchor = preg_replace( '/[^a-z0-9_-]+/', '-', $anchor );
		$anchor = trim( $anchor, '-' );

		return $anchor;
	}

	/**
	 * Sanitize a timestamp and return ISO-8601 UTC.
	 *
	 * @param mixed  $timestamp Raw timestamp.
	 * @param string $fallback  Fallback.
	 * @return string
	 */
	public static function sanitize_timestamp( $timestamp, $fallback = '' ) {
		if ( is_numeric( $timestamp ) ) {
			$time = absint( $timestamp );
		} else {
			$time = strtotime( (string) $timestamp );
		}

		if ( ! $time ) {
			return $fallback;
		}

		return gmdate( 'c', $time );
	}

	/**
	 * Get the current timestamp.
	 *
	 * @return string
	 */
	public static function current_timestamp() {
		return gmdate( 'c' );
	}

	/**
	 * Sanitize a boolean-like value.
	 *
	 * @param mixed $value Raw value.
	 * @return bool
	 */
	public static function sanitize_bool( $value ) {
		return true === $value || '1' === (string) $value || 1 === $value;
	}

	/**
	 * Bound a float.
	 *
	 * @param mixed $value Raw value.
	 * @param float $min   Minimum.
	 * @param float $max   Maximum.
	 * @return float
	 */
	public static function bounded_float( $value, $min, $max ) {
		$value = is_numeric( $value ) ? (float) $value : 0.0;

		return max( $min, min( $max, $value ) );
	}

	/**
	 * Get a default admin title.
	 *
	 * @param string $component_type Component type.
	 * @return string
	 */
	public static function default_admin_title( $component_type ) {
		return ucwords( str_replace( array( '-', '_' ), ' ', \sanitize_key( $component_type ) ) );
	}

	/**
	 * Detect disallowed markup.
	 *
	 * @param string $value Raw value.
	 * @return bool
	 */
	public static function contains_disallowed_markup( $value ) {
		return 1 === preg_match( '/<(script|style)\b|on[a-z]+\s*=|javascript\s*:/i', $value );
	}

	/**
	 * Determine whether a content key should be treated as a URL.
	 *
	 * @param string $key_context Content key.
	 * @return bool
	 */
	public static function content_key_is_url( $key_context ) {
		return 1 === preg_match( '/(^|_)(url|href|src)$/', (string) $key_context );
	}
}
