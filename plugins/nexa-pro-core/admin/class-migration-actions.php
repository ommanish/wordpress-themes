<?php
/**
 * Migration admin request handlers.
 *
 * @package Nexa_Pro_Core
 */

namespace Nexa_Pro_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Migration action service.
 */
final class Migration_Actions {
	const NOTICE_QUERY = 'nexa_core_migration_notice';
	const ERROR_QUERY  = 'nexa_core_migration_error';

	/**
	 * Register authenticated handlers.
	 *
	 * @return void
	 */
	public static function register() {
		\add_action( 'admin_post_nexa_pro_core_migration_preview', array( __CLASS__, 'handle_preview' ) );
		\add_action( 'admin_post_nexa_pro_core_migration_apply', array( __CLASS__, 'handle_apply' ) );
		\add_action( 'admin_post_nexa_pro_core_migration_rollback', array( __CLASS__, 'handle_rollback' ) );
		\add_action( 'admin_post_nexa_pro_core_compatibility_mode_save', array( __CLASS__, 'handle_mode_save' ) );
		\add_action( 'admin_post_nexa_pro_core_migration_report_download', array( __CLASS__, 'handle_report_download' ) );
	}

	/**
	 * Get a nonce action.
	 *
	 * @param string $operation Operation.
	 * @return string
	 */
	public static function nonce_action( $operation ) {
		return 'nexa_pro_core_migration_' . \sanitize_key( $operation );
	}

	/**
	 * Render notices for migration actions.
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
	 * Preview migration.
	 *
	 * @return void|string
	 */
	public static function handle_preview() {
		$target_page_id = self::posted_absint( 'target_page_id' );
		$mode           = self::posted_text( 'migration_mode' );
		$verified       = self::verify_request( 'preview' );

		if ( \is_wp_error( $verified ) ) {
			return self::redirect_error( $verified, array( 'target_page_id' => $target_page_id ) );
		}

		$preview = Migration::preview( $target_page_id, $mode );

		if ( ! empty( $preview['errors'] ) ) {
			return self::redirect_error(
				new \WP_Error( 'nexa_pro_core_migration_preview_failed', implode( ' ', $preview['errors'] ) ),
				array(
					'target_page_id' => $target_page_id,
					'migration_mode' => Migration::sanitize_mode( $mode ),
				)
			);
		}

		return self::redirect_notice(
			'previewed',
			array(
				'target_page_id'     => $target_page_id,
				'migration_mode'     => Migration::sanitize_mode( $mode ),
				'migration_preview'  => 1,
			)
		);
	}

	/**
	 * Apply migration.
	 *
	 * @return void|string
	 */
	public static function handle_apply() {
		$target_page_id = self::posted_absint( 'target_page_id' );
		$mode           = self::posted_text( 'migration_mode' );
		$switch_mode    = '1' === self::posted_text( 'switch_builder_mode' );
		$verified       = self::verify_request( 'apply' );

		if ( \is_wp_error( $verified ) ) {
			return self::redirect_error( $verified, array( 'target_page_id' => $target_page_id ) );
		}

		if ( '1' !== self::posted_text( 'confirm_migration' ) ) {
			return self::redirect_error(
				new \WP_Error( 'nexa_pro_core_migration_confirmation_required', __( 'Confirm that you understand migration will write builder data before applying.', 'nexa-pro-core' ) ),
				array( 'target_page_id' => $target_page_id )
			);
		}

		$result = Migration::apply( $target_page_id, $mode, $switch_mode );

		if ( \is_wp_error( $result ) ) {
			return self::redirect_error( $result, array( 'target_page_id' => $target_page_id ) );
		}

		return self::redirect_notice(
			'applied',
			array(
				'target_page_id' => $target_page_id,
				'migration_mode' => Migration::sanitize_mode( $mode ),
			)
		);
	}

	/**
	 * Roll back migration.
	 *
	 * @return void|string
	 */
	public static function handle_rollback() {
		$verified = self::verify_request( 'rollback' );

		if ( \is_wp_error( $verified ) ) {
			return self::redirect_error( $verified );
		}

		if ( '1' !== self::posted_text( 'confirm_rollback' ) ) {
			return self::redirect_error(
				new \WP_Error( 'nexa_pro_core_migration_rollback_confirmation_required', __( 'Confirm rollback before restoring the migration backup.', 'nexa-pro-core' ) )
			);
		}

		$result = Migration::rollback();

		if ( \is_wp_error( $result ) ) {
			return self::redirect_error( $result );
		}

		return self::redirect_notice( 'rolled_back', array( 'target_page_id' => absint( $result['target_page_id'] ) ) );
	}

	/**
	 * Save explicit compatibility mode.
	 *
	 * @return void|string
	 */
	public static function handle_mode_save() {
		$verified = self::verify_request( 'mode' );

		if ( \is_wp_error( $verified ) ) {
			return self::redirect_error( $verified );
		}

		Compatibility_Mode::set_mode( self::posted_text( 'compatibility_mode' ) );

		return self::redirect_notice( 'mode_saved' );
	}

	/**
	 * Download the latest migration report.
	 *
	 * @return void|string
	 */
	public static function handle_report_download() {
		$verified = self::verify_request( 'report' );

		if ( \is_wp_error( $verified ) ) {
			return self::redirect_error( $verified );
		}

		$report = Migration::get_report();

		if ( empty( $report ) ) {
			return self::redirect_error( new \WP_Error( 'nexa_pro_core_no_migration_report', __( 'No migration report is available yet.', 'nexa-pro-core' ) ) );
		}

		if ( ! headers_sent() ) {
			\nocache_headers();
			header( 'Content-Type: application/json; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename="nexa-pro-migration-report.json"' );
		}

		echo wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		exit;
	}

	/**
	 * Verify capability and nonce.
	 *
	 * @param string $operation Operation.
	 * @return true|\WP_Error
	 */
	private static function verify_request( $operation ) {
		if ( ! Capabilities::current_user_can_manage_components() ) {
			return new \WP_Error( 'nexa_pro_core_forbidden', __( 'You do not have permission to manage Nexa Pro migration.', 'nexa-pro-core' ) );
		}

		$nonce = isset( $_POST['_wpnonce'] ) ? (string) \wp_unslash( $_POST['_wpnonce'] ) : '';

		if ( '' === $nonce && isset( $_GET['_wpnonce'] ) ) {
			$nonce = (string) \wp_unslash( $_GET['_wpnonce'] );
		}

		if ( '' === $nonce || ! \wp_verify_nonce( $nonce, self::nonce_action( $operation ) ) ) {
			return new \WP_Error( 'nexa_pro_core_invalid_nonce', __( 'The migration request could not be verified.', 'nexa-pro-core' ) );
		}

		return true;
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
	 * Redirect to migration screen.
	 *
	 * @param array $extra Extra args.
	 * @return void|string
	 */
	private static function redirect( array $extra = array() ) {
		$args = array_merge(
			array(
				'page'         => Builder_Admin::PAGE_SLUG,
				'builder_view' => 'migration',
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
			'previewed'   => __( 'Migration preview prepared. Review the mapping before applying.', 'nexa-pro-core' ),
			'applied'     => __( 'Migration applied. Legacy settings were preserved.', 'nexa-pro-core' ),
			'rolled_back' => __( 'Migration rollback completed from the latest backup.', 'nexa-pro-core' ),
			'mode_saved'  => __( 'Compatibility mode saved.', 'nexa-pro-core' ),
		);

		return isset( $messages[ $notice ] ) ? $messages[ $notice ] : __( 'Migration action completed.', 'nexa-pro-core' );
	}

	private static function error_message( $error ) {
		$messages = array(
			'nexa_pro_core_invalid_nonce' => __( 'The migration request could not be verified.', 'nexa-pro-core' ),
			'nexa_pro_core_forbidden'     => __( 'You do not have permission to manage Nexa Pro migration.', 'nexa-pro-core' ),
		);

		return isset( $messages[ $error ] ) ? $messages[ $error ] : __( 'Migration action failed.', 'nexa-pro-core' );
	}

	private static function error_transient_key() {
		return 'nexa_pro_core_migration_error_' . absint( \get_current_user_id() );
	}

	private static function posted_text( $key ) {
		return isset( $_POST[ $key ] ) && is_scalar( $_POST[ $key ] ) ? \sanitize_text_field( \wp_unslash( $_POST[ $key ] ) ) : '';
	}

	private static function posted_absint( $key ) {
		return isset( $_POST[ $key ] ) ? absint( \wp_unslash( $_POST[ $key ] ) ) : 0;
	}
}
