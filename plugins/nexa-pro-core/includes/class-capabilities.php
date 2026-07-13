<?php
/**
 * Capability helpers.
 *
 * @package Nexa_Pro_Core
 */

namespace Nexa_Pro_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Capability service.
 */
final class Capabilities {
	const MANAGE_COMPONENTS          = 'manage_nexa_pro_components';
	const MANAGE_REUSABLE_COMPONENTS = 'manage_nexa_pro_reusable_components';
	const IMPORT_COMPONENTS          = 'import_nexa_pro_components';
	const EXPORT_COMPONENTS          = 'export_nexa_pro_components';

	/**
	 * Get plugin capabilities.
	 *
	 * @return array
	 */
	public static function get_capabilities() {
		return array(
			self::MANAGE_COMPONENTS,
			self::MANAGE_REUSABLE_COMPONENTS,
			self::IMPORT_COMPONENTS,
			self::EXPORT_COMPONENTS,
		);
	}

	/**
	 * Grant plugin capabilities to administrators.
	 *
	 * @return void
	 */
	public static function grant_administrator_capabilities() {
		$role = \get_role( 'administrator' );

		if ( ! $role ) {
			return;
		}

		foreach ( self::get_capabilities() as $capability ) {
			$role->add_cap( $capability );
		}
	}

	/**
	 * Determine whether a testing bypass is explicitly allowed.
	 *
	 * @param array $args Operation args.
	 * @return bool
	 */
	public static function testing_bypass_allowed( $args ) {
		return defined( 'NEXA_PRO_CORE_TESTING' ) && NEXA_PRO_CORE_TESTING && ! empty( $args['bypass_capability_check'] );
	}

	/**
	 * Verify a write operation capability and optional nonce.
	 *
	 * @param string $capability Required plugin capability.
	 * @param array  $args       Operation args.
	 * @return true|\WP_Error
	 */
	public static function verify_operation( $capability, $args = array() ) {
		if ( self::testing_bypass_allowed( $args ) ) {
			return true;
		}

		if ( ! \current_user_can( $capability ) ) {
			return new \WP_Error(
				'nexa_pro_core_forbidden',
				\__( 'You do not have permission to manage Nexa Pro components.', 'nexa-pro-core' )
			);
		}

		if ( ! empty( $args['nonce_action'] ) ) {
			$nonce = isset( $args['nonce'] ) ? (string) $args['nonce'] : '';

			if ( '' === $nonce || ! \wp_verify_nonce( $nonce, $args['nonce_action'] ) ) {
				return new \WP_Error(
					'nexa_pro_core_invalid_nonce',
					\__( 'The component storage request could not be verified.', 'nexa-pro-core' )
				);
			}
		}

		return true;
	}

	/**
	 * Verify access to write page-level components.
	 *
	 * @param int   $page_id Page ID.
	 * @param array $args    Operation args.
	 * @return true|\WP_Error
	 */
	public static function verify_page_write( $page_id, $args = array() ) {
		$verified = self::verify_operation( self::MANAGE_COMPONENTS, $args );

		if ( \is_wp_error( $verified ) ) {
			return $verified;
		}

		if ( self::testing_bypass_allowed( $args ) ) {
			return true;
		}

		if ( ! \current_user_can( 'edit_post', $page_id ) ) {
			return new \WP_Error(
				'nexa_pro_core_page_forbidden',
				\__( 'You do not have permission to edit this page.', 'nexa-pro-core' )
			);
		}

		return true;
	}

	/**
	 * Verify access to write reusable components.
	 *
	 * @param array $args Operation args.
	 * @return true|\WP_Error
	 */
	public static function verify_reusable_write( $args = array() ) {
		return self::verify_operation( self::MANAGE_REUSABLE_COMPONENTS, $args );
	}
}
