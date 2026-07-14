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
	 * Repair administrator capabilities after plugin updates.
	 *
	 * Existing beta installs may already be active when new capabilities are
	 * introduced, so activation alone is not enough.
	 *
	 * @return void
	 */
	public static function maybe_grant_administrator_capabilities() {
		$state = \get_option( NEXA_PRO_CORE_MIGRATION_STATE_OPTION, array() );

		if ( ! is_array( $state ) ) {
			$state = array();
		}

		if ( ! empty( $state['capabilities_version'] ) && NEXA_PRO_CORE_VERSION === $state['capabilities_version'] ) {
			return;
		}

		self::grant_administrator_capabilities();

		$state['capabilities_version']    = NEXA_PRO_CORE_VERSION;
		$state['capabilities_updated_at'] = Sanitizer::current_timestamp();

		\update_option( NEXA_PRO_CORE_MIGRATION_STATE_OPTION, $state, false );
	}

	/**
	 * Map plugin capabilities to a core administrator capability fallback.
	 *
	 * @param array  $caps    Primitive capabilities required.
	 * @param string $cap     Requested capability.
	 * @param int    $user_id User ID.
	 * @param array  $args    Capability args.
	 * @return array
	 */
	public static function map_meta_cap( $caps, $cap, $user_id, $args ) {
		if ( ! in_array( $cap, self::get_capabilities(), true ) ) {
			return $caps;
		}

		$user    = \get_userdata( $user_id );
		$allcaps = $user ? (array) $user->allcaps : array();

		if ( ! empty( $allcaps[ $cap ] ) ) {
			return array( $cap );
		}

		if ( self::MANAGE_REUSABLE_COMPONENTS === $cap && ! empty( $allcaps[ self::MANAGE_COMPONENTS ] ) ) {
			return array( self::MANAGE_COMPONENTS );
		}

		return array( 'manage_options' );
	}

	/**
	 * Check whether the current user can manage page components.
	 *
	 * @return bool
	 */
	public static function current_user_can_manage_components() {
		return \current_user_can( self::MANAGE_COMPONENTS ) || \current_user_can( 'manage_options' );
	}

	/**
	 * Check whether the current user can manage reusable components.
	 *
	 * Existing component managers can manage reusable components because the
	 * reusable library is an extension of the page-component builder.
	 *
	 * @return bool
	 */
	public static function current_user_can_manage_reusable_components() {
		return \current_user_can( self::MANAGE_REUSABLE_COMPONENTS ) || self::current_user_can_manage_components();
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

		$allowed = self::MANAGE_REUSABLE_COMPONENTS === $capability
			? self::current_user_can_manage_reusable_components()
			: \current_user_can( $capability );

		if ( ! $allowed ) {
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
