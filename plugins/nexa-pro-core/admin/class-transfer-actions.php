<?php
/**
 * Import/export admin request handlers.
 *
 * @package Nexa_Pro_Core
 */

namespace Nexa_Pro_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Transfer action service.
 */
final class Transfer_Actions {
	const NOTICE_QUERY = 'nexa_core_transfer_notice';
	const ERROR_QUERY  = 'nexa_core_transfer_error';

	/**
	 * Register authenticated handlers.
	 *
	 * @return void
	 */
	public static function register() {
		\add_action( 'admin_post_nexa_pro_core_export', array( __CLASS__, 'handle_export' ) );
		\add_action( 'admin_post_nexa_pro_core_import_preview', array( __CLASS__, 'handle_import_preview' ) );
		\add_action( 'admin_post_nexa_pro_core_import_apply', array( __CLASS__, 'handle_import_apply' ) );
	}

	/**
	 * Get nonce action.
	 *
	 * @param string $operation Operation.
	 * @return string
	 */
	public static function nonce_action( $operation ) {
		return 'nexa_pro_core_transfer_' . \sanitize_key( $operation );
	}

	/**
	 * Render action notices.
	 *
	 * @return void
	 */
	public static function render_notices() {
		$notice = isset( $_GET[ self::NOTICE_QUERY ] ) ? \sanitize_key( \wp_unslash( $_GET[ self::NOTICE_QUERY ] ) ) : '';
		$error  = isset( $_GET[ self::ERROR_QUERY ] ) ? \sanitize_key( \wp_unslash( $_GET[ self::ERROR_QUERY ] ) ) : '';

		if ( $notice ) {
			printf(
				'<div class="notice notice-success is-dismissible" role="status"><p>%s</p></div>',
				esc_html( self::notice_message( $notice ) )
			);
		}

		if ( $error ) {
			$message = \get_transient( self::error_transient_key() );

			if ( ! is_string( $message ) || '' === $message ) {
				$message = self::error_message( $error );
			}

			\delete_transient( self::error_transient_key() );

			printf(
				'<div class="notice notice-error" role="alert"><p>%s</p></div>',
				esc_html( $message )
			);
		}
	}

	/**
	 * Download an export payload.
	 *
	 * @return void|string
	 */
	public static function handle_export() {
		$verified = self::verify_request( 'export', Capabilities::EXPORT_COMPONENTS );

		if ( \is_wp_error( $verified ) ) {
			return self::redirect_error( $verified );
		}

		$scope   = self::posted_text( 'export_scope' );
		$page_id = self::posted_absint( 'export_page_id' );
		$args    = array(
			'page_id'  => $page_id,
			'page_ids' => self::posted_absint_array( 'export_page_ids' ),
		);
		$payload = Transfer::build_export( $scope, $args );
		$scope   = isset( $payload['scope'] ) ? \sanitize_key( $payload['scope'] ) : 'full-site';
		$date    = gmdate( 'Ymd-His' );

		if ( ! headers_sent() ) {
			\nocache_headers();
			header( 'Content-Type: application/json; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename="nexa-pro-' . $scope . '-' . $date . '.json"' );
		}

		echo wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		exit;
	}

	/**
	 * Preview a JSON import.
	 *
	 * @return void|string
	 */
	public static function handle_import_preview() {
		$verified = self::verify_request( 'import_preview', Capabilities::IMPORT_COMPONENTS );

		if ( \is_wp_error( $verified ) ) {
			return self::redirect_error( $verified );
		}

		$raw = self::import_json_from_request();

		if ( \is_wp_error( $raw ) ) {
			return self::redirect_error( $raw );
		}

		$result = Transfer::preview_import( $raw );

		if ( \is_wp_error( $result ) ) {
			return self::redirect_error( $result );
		}

		return self::redirect_notice(
			'import_previewed',
			array(
				'import_token'  => isset( $result['token'] ) ? $result['token'] : '',
				'conflict_mode' => self::posted_text( 'conflict_mode' ),
			)
		);
	}

	/**
	 * Apply a previously previewed import.
	 *
	 * @return void|string
	 */
	public static function handle_import_apply() {
		$verified = self::verify_request( 'import_apply', Capabilities::IMPORT_COMPONENTS );

		if ( \is_wp_error( $verified ) ) {
			return self::redirect_error( $verified );
		}

		if ( '1' !== self::posted_text( 'confirm_import' ) ) {
			return self::redirect_error( new \WP_Error( 'nexa_pro_core_import_confirmation_required', __( 'Confirm the import before applying it.', 'nexa-pro-core' ) ) );
		}

		$token   = self::posted_text( 'import_token' );
		$payload = Transfer::get_preview_payload( $token );

		if ( empty( $payload ) ) {
			return self::redirect_error( new \WP_Error( 'nexa_pro_core_import_preview_missing', __( 'The import preview expired or could not be found.', 'nexa-pro-core' ) ) );
		}

		$page_map = self::posted_page_map();
		$result   = Transfer::apply_import( $payload, self::posted_text( 'conflict_mode' ), $page_map );

		if ( \is_wp_error( $result ) ) {
			return self::redirect_error( $result );
		}

		return self::redirect_notice( 'import_applied' );
	}

	/**
	 * Verify capability and nonce.
	 *
	 * @param string $operation  Operation.
	 * @param string $capability Capability.
	 * @return true|\WP_Error
	 */
	private static function verify_request( $operation, $capability ) {
		if ( ! \current_user_can( $capability ) && ! \current_user_can( 'manage_options' ) ) {
			return new \WP_Error( 'nexa_pro_core_forbidden', __( 'You do not have permission to import or export Nexa Pro components.', 'nexa-pro-core' ) );
		}

		$nonce = isset( $_POST['_wpnonce'] ) ? (string) \wp_unslash( $_POST['_wpnonce'] ) : '';

		if ( '' === $nonce || ! \wp_verify_nonce( $nonce, self::nonce_action( $operation ) ) ) {
			return new \WP_Error( 'nexa_pro_core_invalid_nonce', __( 'The import/export request could not be verified.', 'nexa-pro-core' ) );
		}

		return true;
	}

	/**
	 * Read import JSON from textarea or upload.
	 *
	 * @return string|\WP_Error
	 */
	private static function import_json_from_request() {
		if ( ! empty( $_POST['import_json'] ) && is_scalar( $_POST['import_json'] ) ) {
			$json = (string) \wp_unslash( $_POST['import_json'] );

			if ( strlen( $json ) > Transfer::MAX_JSON_BYTES ) {
				return new \WP_Error( 'nexa_pro_core_import_too_large', __( 'Import JSON exceeds the maximum supported size.', 'nexa-pro-core' ) );
			}

			return $json;
		}

		if ( empty( $_FILES['import_file'] ) || ! is_array( $_FILES['import_file'] ) ) {
			return new \WP_Error( 'nexa_pro_core_import_missing', __( 'Choose a JSON file or paste JSON before previewing import.', 'nexa-pro-core' ) );
		}

		$file = $_FILES['import_file'];
		$size = isset( $file['size'] ) ? absint( $file['size'] ) : 0;
		$tmp  = isset( $file['tmp_name'] ) ? (string) $file['tmp_name'] : '';
		$name = isset( $file['name'] ) ? \sanitize_file_name( $file['name'] ) : '';

		if ( ! $tmp || ! file_exists( $tmp ) ) {
			return new \WP_Error( 'nexa_pro_core_import_upload_failed', __( 'The uploaded import file could not be read.', 'nexa-pro-core' ) );
		}

		if ( $size > Transfer::MAX_JSON_BYTES ) {
			return new \WP_Error( 'nexa_pro_core_import_too_large', __( 'Import JSON exceeds the maximum supported size.', 'nexa-pro-core' ) );
		}

		if ( '.json' !== strtolower( substr( $name, -5 ) ) ) {
			return new \WP_Error( 'nexa_pro_core_import_invalid_file_type', __( 'Import files must use the .json extension.', 'nexa-pro-core' ) );
		}

		$contents = file_get_contents( $tmp );

		return is_string( $contents ) ? $contents : new \WP_Error( 'nexa_pro_core_import_read_failed', __( 'The import file could not be read.', 'nexa-pro-core' ) );
	}

	/**
	 * Build explicit page map from posted values.
	 *
	 * @return array
	 */
	private static function posted_page_map() {
		if ( empty( $_POST['page_map'] ) || ! is_array( $_POST['page_map'] ) ) {
			return array();
		}

		$raw = \wp_unslash( $_POST['page_map'] );
		$map = array();

		foreach ( $raw as $source_id => $target_id ) {
			$source_id = absint( $source_id );
			$target_id = absint( $target_id );

			if ( $source_id && $target_id ) {
				$map[ $source_id ] = $target_id;
			}
		}

		return $map;
	}

	/**
	 * Redirect with notice.
	 *
	 * @param string $notice Notice.
	 * @param array  $extra  Extra args.
	 * @return void|string
	 */
	private static function redirect_notice( $notice, array $extra = array() ) {
		$extra[ self::NOTICE_QUERY ] = \sanitize_key( $notice );

		return self::redirect( $extra );
	}

	/**
	 * Redirect with error.
	 *
	 * @param \WP_Error $error Error.
	 * @param array     $extra Extra args.
	 * @return void|string
	 */
	private static function redirect_error( \WP_Error $error, array $extra = array() ) {
		$extra[ self::ERROR_QUERY ] = $error->get_error_code();

		if ( $error->get_error_message() ) {
			\set_transient( self::error_transient_key(), $error->get_error_message(), MINUTE_IN_SECONDS );
		}

		return self::redirect( $extra );
	}

	/**
	 * Redirect to transfer screen.
	 *
	 * @param array $extra Extra args.
	 * @return void|string
	 */
	private static function redirect( array $extra = array() ) {
		$args = array_merge(
			array(
				'page'         => Builder_Admin::PAGE_SLUG,
				'builder_view' => 'transfer',
			),
			array_filter(
				$extra,
				function ( $value ) {
					return '' !== (string) $value;
				}
			)
		);
		$url  = \add_query_arg( $args, \admin_url( 'themes.php' ) );

		if ( defined( 'NEXA_PRO_CORE_TESTING' ) && NEXA_PRO_CORE_TESTING ) {
			return $url;
		}

		\wp_safe_redirect( $url );
		exit;
	}

	private static function notice_message( $notice ) {
		$messages = array(
			'import_previewed' => __( 'Import preview prepared. Review conflicts and mappings before applying.', 'nexa-pro-core' ),
			'import_applied'   => __( 'Import applied.', 'nexa-pro-core' ),
		);

		return isset( $messages[ $notice ] ) ? $messages[ $notice ] : __( 'Import/export action completed.', 'nexa-pro-core' );
	}

	private static function error_message( $error ) {
		$messages = array(
			'nexa_pro_core_invalid_nonce' => __( 'The import/export request could not be verified.', 'nexa-pro-core' ),
			'nexa_pro_core_forbidden'     => __( 'You do not have permission to import or export Nexa Pro components.', 'nexa-pro-core' ),
		);

		return isset( $messages[ $error ] ) ? $messages[ $error ] : __( 'Import/export action failed.', 'nexa-pro-core' );
	}

	private static function error_transient_key() {
		return 'nexa_pro_core_transfer_error_' . absint( \get_current_user_id() );
	}

	private static function posted_text( $key ) {
		return isset( $_POST[ $key ] ) && is_scalar( $_POST[ $key ] ) ? \sanitize_text_field( \wp_unslash( $_POST[ $key ] ) ) : '';
	}

	private static function posted_absint( $key ) {
		return isset( $_POST[ $key ] ) ? absint( \wp_unslash( $_POST[ $key ] ) ) : 0;
	}

	private static function posted_absint_array( $key ) {
		if ( empty( $_POST[ $key ] ) || ! is_array( $_POST[ $key ] ) ) {
			return array();
		}

		return array_values( array_filter( array_map( 'absint', \wp_unslash( $_POST[ $key ] ) ) ) );
	}
}
