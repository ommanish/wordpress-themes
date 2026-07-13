<?php
/**
 * Navigation admin request handlers.
 *
 * @package Nexa_Pro_Core
 */

namespace Nexa_Pro_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Navigation action service.
 */
final class Navigation_Actions {
	const NOTICE_QUERY = 'nexa_core_nav_notice';
	const ERROR_QUERY  = 'nexa_core_nav_error';

	/**
	 * Register admin-post handlers.
	 *
	 * @return void
	 */
	public static function register() {
		\add_action( 'admin_post_nexa_pro_core_navigation_save', array( __CLASS__, 'handle_save' ) );
		\add_action( 'admin_post_nexa_pro_core_navigation_reset', array( __CLASS__, 'handle_reset' ) );
	}

	/**
	 * Nonce action.
	 *
	 * @param string $operation Operation key.
	 * @return string
	 */
	public static function nonce_action( $operation ) {
		return 'nexa_pro_core_navigation_' . \sanitize_key( $operation );
	}

	/**
	 * Handle settings save.
	 *
	 * @return void|string
	 */
	public static function handle_save() {
		$verified = self::verify_request( 'save' );

		if ( \is_wp_error( $verified ) ) {
			return self::redirect_error( $verified );
		}

		$input = isset( $_POST['nexa_pro_core_navigation'] ) && is_array( $_POST['nexa_pro_core_navigation'] )
			? \wp_unslash( $_POST['nexa_pro_core_navigation'] )
			: array();

		Navigation_Settings::save_settings( $input );

		return self::redirect_notice( 'saved' );
	}

	/**
	 * Handle settings reset.
	 *
	 * @return void|string
	 */
	public static function handle_reset() {
		$verified = self::verify_request( 'reset' );

		if ( \is_wp_error( $verified ) ) {
			return self::redirect_error( $verified );
		}

		Navigation_Settings::reset_settings();

		return self::redirect_notice( 'reset' );
	}

	/**
	 * Verify capability and nonce.
	 *
	 * @param string $operation Operation key.
	 * @return true|\WP_Error
	 */
	private static function verify_request( $operation ) {
		if ( ! Capabilities::current_user_can_manage_components() ) {
			return new \WP_Error( 'nexa_pro_core_forbidden', \__( 'You do not have permission to manage generated navigation.', 'nexa-pro-core' ) );
		}

		$nonce = isset( $_POST['_wpnonce'] ) && is_scalar( $_POST['_wpnonce'] ) ? \sanitize_text_field( \wp_unslash( $_POST['_wpnonce'] ) ) : '';

		if ( '' === $nonce || ! \wp_verify_nonce( $nonce, self::nonce_action( $operation ) ) ) {
			return new \WP_Error( 'nexa_pro_core_invalid_nonce', \__( 'The navigation settings request could not be verified.', 'nexa-pro-core' ) );
		}

		return true;
	}

	/**
	 * Redirect with notice.
	 *
	 * @param string $notice Notice key.
	 * @return void|string
	 */
	private static function redirect_notice( $notice ) {
		return self::redirect(
			array(
				self::NOTICE_QUERY => \sanitize_key( $notice ),
			)
		);
	}

	/**
	 * Redirect with error.
	 *
	 * @param \WP_Error $error Error.
	 * @return void|string
	 */
	private static function redirect_error( \WP_Error $error ) {
		return self::redirect(
			array(
				self::ERROR_QUERY => $error->get_error_code(),
			)
		);
	}

	/**
	 * Redirect to navigation view.
	 *
	 * @param array $args Args.
	 * @return void|string
	 */
	private static function redirect( array $args = array() ) {
		$url = \add_query_arg(
			array_merge(
				array(
					'page'         => Builder_Admin::PAGE_SLUG,
					'builder_view' => 'navigation',
				),
				$args
			),
			\admin_url( 'themes.php' )
		);

		if ( defined( 'NEXA_PRO_CORE_TESTING' ) && NEXA_PRO_CORE_TESTING ) {
			return $url;
		}

		\wp_safe_redirect( $url );
		exit;
	}
}
