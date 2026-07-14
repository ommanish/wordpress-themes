<?php
/**
 * Frontend compatibility mode storage.
 *
 * @package Nexa_Pro_Core
 */

namespace Nexa_Pro_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stores whether legacy theme rendering or builder rendering is active.
 */
final class Compatibility_Mode {
	const OPTION_NAME = 'nexa_pro_core_compatibility_mode';

	/**
	 * Get allowed compatibility modes.
	 *
	 * @return array
	 */
	public static function allowed_modes() {
		return array( 'legacy', 'builder' );
	}

	/**
	 * Get the current mode.
	 *
	 * @return string
	 */
	public static function get_mode() {
		$mode = \get_option( self::OPTION_NAME, 'legacy' );
		$mode = \sanitize_key( is_scalar( $mode ) ? $mode : 'legacy' );

		return in_array( $mode, self::allowed_modes(), true ) ? $mode : 'legacy';
	}

	/**
	 * Save the current mode.
	 *
	 * @param string $mode Mode.
	 * @return string
	 */
	public static function set_mode( $mode ) {
		$mode = \sanitize_key( $mode );

		if ( ! in_array( $mode, self::allowed_modes(), true ) ) {
			$mode = 'legacy';
		}

		\update_option( self::OPTION_NAME, $mode, false );

		return $mode;
	}

	/**
	 * Determine whether builder rendering should be attempted for a page.
	 *
	 * @param int $page_id Page ID.
	 * @return bool
	 */
	public static function should_render_builder_page( $page_id ) {
		if ( 'builder' !== self::get_mode() ) {
			return false;
		}

		$page_id = absint( $page_id );

		if ( ! $page_id || ! Render_API::has_builder_components( $page_id ) ) {
			return false;
		}

		return true;
	}
}
