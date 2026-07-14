<?php
/**
 * Navigation settings storage.
 *
 * @package Nexa_Pro_Core
 */

namespace Nexa_Pro_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin-owned navigation settings.
 */
final class Navigation_Settings {
	const OPTION_NAME = 'nexa_pro_core_navigation';

	/**
	 * Get default navigation settings.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'navigation_source'          => 'wordpress',
			'generated_primary_page_id' => 0,
			'include_page_sections'     => '1',
			'generated_page_ids'        => array(),
			'hybrid_insertion_position' => 'after',
			'hybrid_placeholder_url'    => '#nexa-generated-navigation',
			'active_section_enabled'    => '1',
			'smooth_scroll_enabled'     => '1',
			'scroll_offset'             => 0,
			'include_disabled_pages'    => '0',
			'generated_mobile_visibility' => 'visible',
		);
	}

	/**
	 * Get normalized navigation settings.
	 *
	 * @return array
	 */
	public static function get_settings() {
		$stored = \get_option( self::OPTION_NAME, array() );

		return self::sanitize_settings( is_array( $stored ) ? $stored : array(), self::defaults() );
	}

	/**
	 * Save navigation settings.
	 *
	 * @param array $input Raw input.
	 * @return array
	 */
	public static function save_settings( array $input ) {
		$settings = self::sanitize_settings( $input, self::get_settings() );

		\update_option( self::OPTION_NAME, $settings, false );

		return $settings;
	}

	/**
	 * Reset navigation settings.
	 *
	 * @return array
	 */
	public static function reset_settings() {
		$defaults = self::defaults();

		\update_option( self::OPTION_NAME, $defaults, false );

		return $defaults;
	}

	/**
	 * Sanitize settings with an existing/default fallback.
	 *
	 * @param array $input    Raw settings.
	 * @param array $fallback Existing or default settings.
	 * @return array
	 */
	public static function sanitize_settings( array $input, array $fallback = array() ) {
		$defaults = self::defaults();
		$fallback = array_merge( $defaults, array_intersect_key( $fallback, $defaults ) );

		$settings = $defaults;

		$source = isset( $input['navigation_source'] ) ? \sanitize_key( $input['navigation_source'] ) : $fallback['navigation_source'];
		if ( ! in_array( $source, array( 'wordpress', 'generated', 'hybrid' ), true ) ) {
			$source = $fallback['navigation_source'];
		}
		$settings['navigation_source'] = $source;

		$settings['generated_primary_page_id'] = isset( $input['generated_primary_page_id'] ) ? self::valid_page_id_or_zero( $input['generated_primary_page_id'] ) : absint( $fallback['generated_primary_page_id'] );
		$settings['include_page_sections']     = self::strict_toggle( isset( $input['include_page_sections'] ) ? $input['include_page_sections'] : $fallback['include_page_sections'] );
		if ( ! empty( $input['generated_page_ids_submitted'] ) ) {
			$settings['generated_page_ids'] = isset( $input['generated_page_ids'] ) && is_array( $input['generated_page_ids'] ) ? self::sanitize_page_ids( $input['generated_page_ids'] ) : array();

			if ( ! empty( $input['generated_page_order'] ) && is_array( $input['generated_page_order'] ) ) {
				$settings['generated_page_ids'] = self::sort_page_ids_by_submitted_order( $settings['generated_page_ids'], $input['generated_page_order'] );
			}
		} else {
			$settings['generated_page_ids'] = isset( $input['generated_page_ids'] ) && is_array( $input['generated_page_ids'] ) ? self::sanitize_page_ids( $input['generated_page_ids'] ) : self::sanitize_page_ids( $fallback['generated_page_ids'] );
		}

		$position = isset( $input['hybrid_insertion_position'] ) ? \sanitize_key( $input['hybrid_insertion_position'] ) : $fallback['hybrid_insertion_position'];
		if ( ! in_array( $position, array( 'before', 'after', 'replace-placeholder' ), true ) ) {
			$position = $fallback['hybrid_insertion_position'];
		}
		$settings['hybrid_insertion_position'] = $position;

		$placeholder = isset( $input['hybrid_placeholder_url'] ) ? trim( (string) \wp_unslash( $input['hybrid_placeholder_url'] ) ) : $fallback['hybrid_placeholder_url'];
		if ( '' === $placeholder || '#' !== $placeholder[0] ) {
			$placeholder = $defaults['hybrid_placeholder_url'];
		}
		$settings['hybrid_placeholder_url'] = Sanitizer::sanitize_anchor_id( substr( $placeholder, 1 ) );
		$settings['hybrid_placeholder_url'] = '#' . ( '' !== $settings['hybrid_placeholder_url'] ? $settings['hybrid_placeholder_url'] : 'nexa-generated-navigation' );

		$settings['active_section_enabled']     = self::strict_toggle( isset( $input['active_section_enabled'] ) ? $input['active_section_enabled'] : $fallback['active_section_enabled'] );
		$settings['smooth_scroll_enabled']      = self::strict_toggle( isset( $input['smooth_scroll_enabled'] ) ? $input['smooth_scroll_enabled'] : $fallback['smooth_scroll_enabled'] );
		$settings['include_disabled_pages']     = self::strict_toggle( isset( $input['include_disabled_pages'] ) ? $input['include_disabled_pages'] : $fallback['include_disabled_pages'] );
		$settings['scroll_offset']              = max( 0, min( 240, absint( isset( $input['scroll_offset'] ) ? $input['scroll_offset'] : $fallback['scroll_offset'] ) ) );
		$mobile_visibility                      = isset( $input['generated_mobile_visibility'] ) ? \sanitize_key( $input['generated_mobile_visibility'] ) : $fallback['generated_mobile_visibility'];
		$settings['generated_mobile_visibility'] = in_array( $mobile_visibility, array( 'visible', 'hidden' ), true ) ? $mobile_visibility : $defaults['generated_mobile_visibility'];

		return $settings;
	}

	/**
	 * Normalize a checkbox-like value to strict string.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	private static function strict_toggle( $value ) {
		return '1' === (string) $value ? '1' : '0';
	}

	/**
	 * Get a valid page ID or zero.
	 *
	 * @param mixed $page_id Page ID.
	 * @return int
	 */
	private static function valid_page_id_or_zero( $page_id ) {
		$page_id = absint( $page_id );
		$post    = $page_id ? \get_post( $page_id ) : null;

		return $post && 'page' === $post->post_type ? $page_id : 0;
	}

	/**
	 * Sanitize unique page IDs.
	 *
	 * @param array $page_ids Page IDs.
	 * @return array
	 */
	private static function sanitize_page_ids( array $page_ids ) {
		$normalized = array();

		foreach ( $page_ids as $page_id ) {
			$page_id = self::valid_page_id_or_zero( $page_id );

			if ( $page_id && ! in_array( $page_id, $normalized, true ) ) {
				$normalized[] = $page_id;
			}
		}

		return $normalized;
	}

	/**
	 * Sort page IDs by submitted numeric order.
	 *
	 * @param array $page_ids Page IDs.
	 * @param array $order    Order values keyed by page ID.
	 * @return array
	 */
	private static function sort_page_ids_by_submitted_order( array $page_ids, array $order ) {
		$positions = array();

		foreach ( $page_ids as $index => $page_id ) {
			$key                  = (string) absint( $page_id );
			$positions[ $page_id ] = isset( $order[ $key ] ) ? absint( $order[ $key ] ) : $index + 1;
		}

		usort(
			$page_ids,
			function ( $a, $b ) use ( $positions ) {
				if ( $positions[ $a ] === $positions[ $b ] ) {
					return 0;
				}

				return $positions[ $a ] < $positions[ $b ] ? -1 : 1;
			}
		);

		return $page_ids;
	}
}
