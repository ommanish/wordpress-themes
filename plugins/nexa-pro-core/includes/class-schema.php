<?php
/**
 * Schema version helpers.
 *
 * @package Nexa_Pro_Core
 */

namespace Nexa_Pro_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Schema service.
 */
final class Schema {
	/**
	 * Get the current code schema version.
	 *
	 * @return int
	 */
	public static function current_schema_version() {
		return (int) NEXA_PRO_CORE_SCHEMA_VERSION;
	}

	/**
	 * Get the stored schema version.
	 *
	 * @return int
	 */
	public static function stored_schema_version() {
		$state = \get_option( NEXA_PRO_CORE_MIGRATION_STATE_OPTION, array() );

		if ( ! is_array( $state ) || empty( $state['schema_version'] ) ) {
			return 0;
		}

		return absint( $state['schema_version'] );
	}

	/**
	 * Update the stored schema version.
	 *
	 * @param int $version Schema version.
	 * @return bool
	 */
	public static function update_schema_version( $version ) {
		$state = \get_option( NEXA_PRO_CORE_MIGRATION_STATE_OPTION, array() );

		if ( ! is_array( $state ) ) {
			$state = array();
		}

		$state['schema_version'] = absint( $version );
		$state['updated_at']     = Sanitizer::current_timestamp();

		if ( empty( $state['initialized_at'] ) ) {
			$state['initialized_at'] = $state['updated_at'];
		}

		return (bool) \update_option( NEXA_PRO_CORE_MIGRATION_STATE_OPTION, $state, false );
	}

	/**
	 * Initialize schema metadata.
	 *
	 * @return void
	 */
	public static function initialize() {
		if ( self::stored_schema_version() === self::current_schema_version() ) {
			return;
		}

		self::update_schema_version( self::current_schema_version() );
	}

	/**
	 * Initialize schema metadata when needed.
	 *
	 * @return void
	 */
	public static function maybe_initialize() {
		if ( self::stored_schema_version() < self::current_schema_version() ) {
			self::initialize();
		}
	}
}
