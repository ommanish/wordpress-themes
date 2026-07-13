<?php
/**
 * Reusable component admin handlers.
 *
 * @package Nexa_Pro_Core
 */

namespace Nexa_Pro_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reusable admin action service.
 */
final class Reusable_Actions {
	const NOTICE_QUERY = 'nexa_core_reusable_notice';
	const ERROR_QUERY  = 'nexa_core_reusable_error';

	/**
	 * Register handlers.
	 *
	 * @return void
	 */
	public static function register() {
		\add_action( 'admin_post_nexa_pro_core_reusable_save', array( __CLASS__, 'handle_save' ) );
		\add_action( 'admin_post_nexa_pro_core_reusable_duplicate', array( __CLASS__, 'handle_duplicate' ) );
		\add_action( 'admin_post_nexa_pro_core_reusable_archive', array( __CLASS__, 'handle_archive' ) );
		\add_action( 'admin_post_nexa_pro_core_reusable_restore', array( __CLASS__, 'handle_restore' ) );
		\add_action( 'admin_post_nexa_pro_core_reusable_delete', array( __CLASS__, 'handle_delete' ) );
		\add_action( 'admin_post_nexa_pro_core_reusable_insert', array( __CLASS__, 'handle_insert' ) );
		\add_action( 'admin_post_nexa_pro_core_reusable_save_from_instance', array( __CLASS__, 'handle_save_from_instance' ) );
		\add_action( 'admin_post_nexa_pro_core_reusable_detach_instance', array( __CLASS__, 'handle_detach_instance' ) );
	}

	/**
	 * Nonce action.
	 *
	 * @param string $operation Operation.
	 * @param int    $id        Optional ID.
	 * @return string
	 */
	public static function nonce_action( $operation, $id = 0 ) {
		return 'nexa_pro_core_reusable_' . \sanitize_key( $operation ) . '_' . absint( $id );
	}

	/**
	 * Save a reusable component.
	 *
	 * @return void|string
	 */
	public static function handle_save() {
		$post_id  = self::posted_absint( 'reusable_id' );
		$verified = self::verify_reusable_request( 'save', $post_id );

		if ( \is_wp_error( $verified ) ) {
			return self::redirect_error( $verified );
		}

		$posted    = self::posted_array( 'component' );
		$component = Builder_Actions::build_component_from_posted_values( $posted );

		$is_new = ! $post_id;

		if ( $post_id ) {
			$result = Reusable_Components::update_reusable_component( $post_id, $component, $verified );
		} else {
			$result  = Reusable_Components::create_reusable_component( $component, $verified );
			$post_id = \is_wp_error( $result ) ? 0 : absint( $result );
		}

		if ( \is_wp_error( $result ) ) {
			return self::redirect_error( $result, array( 'reusable_mode' => $post_id ? 'edit' : 'new', 'reusable_id' => $post_id ) );
		}

		return self::redirect_notice( $is_new ? 'created' : 'saved', array( 'reusable_id' => $post_id ) );
	}

	/**
	 * Duplicate a reusable component.
	 *
	 * @return void|string
	 */
	public static function handle_duplicate() {
		$post_id  = self::posted_absint( 'reusable_id' );
		$verified = self::verify_reusable_request( 'duplicate', $post_id );

		if ( \is_wp_error( $verified ) ) {
			return self::redirect_error( $verified );
		}

		$result = Reusable_Components::duplicate_reusable_component( $post_id, $verified );

		if ( \is_wp_error( $result ) ) {
			return self::redirect_error( $result );
		}

		return self::redirect_notice( 'duplicated', array( 'reusable_id' => absint( $result ) ) );
	}

	/**
	 * Archive a reusable component.
	 *
	 * @return void|string
	 */
	public static function handle_archive() {
		return self::handle_status_change( 'archive', 'archived', array( Reusable_Components::class, 'archive_reusable_component' ) );
	}

	/**
	 * Restore a reusable component.
	 *
	 * @return void|string
	 */
	public static function handle_restore() {
		return self::handle_status_change( 'restore', 'restored', array( Reusable_Components::class, 'restore_reusable_component' ) );
	}

	/**
	 * Delete a reusable component.
	 *
	 * @return void|string
	 */
	public static function handle_delete() {
		$post_id  = self::posted_absint( 'reusable_id' );
		$mode     = self::posted_text( 'delete_mode' );
		$verified = self::verify_reusable_request( 'delete', $post_id );

		if ( \is_wp_error( $verified ) ) {
			return self::redirect_error( $verified );
		}

		if ( '1' !== self::posted_text( 'confirm_delete' ) ) {
			return self::redirect_error( new \WP_Error( 'nexa_pro_core_delete_unconfirmed', \__( 'Confirm deletion before deleting a reusable component.', 'nexa-pro-core' ) ) );
		}

		if ( 'detach-all' === $mode ) {
			$detached = Reusable_Components::detach_all_linked_instances( $post_id, $verified );

			if ( \is_wp_error( $detached ) ) {
				return self::redirect_error( $detached );
			}
		}

		$result = Reusable_Components::delete_reusable_component( $post_id, $verified );

		if ( \is_wp_error( $result ) ) {
			return self::redirect_error( $result );
		}

		return self::redirect_notice( 'deleted' );
	}

	/**
	 * Insert reusable into a page.
	 *
	 * @return void|string
	 */
	public static function handle_insert() {
		$page_id     = self::posted_absint( 'builder_page_id' );
		$post_id     = self::posted_absint( 'reusable_id' );
		$insert_mode = 'local' === self::posted_text( 'insert_mode' ) ? 'local' : 'linked';
		$verified    = self::verify_page_and_reusable_request( 'insert', $page_id, $post_id );

		if ( \is_wp_error( $verified ) ) {
			return self::redirect_error( $verified, array( 'builder_page_id' => $page_id ) );
		}

		$payload = Reusable_Components::get_reusable_component( $post_id );

		if ( \is_wp_error( $payload ) ) {
			return self::redirect_error( $payload, array( 'builder_page_id' => $page_id ) );
		}

		$component                           = $payload;
		$component['instance_id']            = Sanitizer::generate_instance_id( $payload['component_type'] );
		$component['reusable_component_id']  = 'linked' === $insert_mode ? $post_id : 0;
		$component['inheritance_mode']       = $insert_mode;
		$component['created_at']             = Sanitizer::current_timestamp();
		$component['updated_at']             = $component['created_at'];
		$component['navigation']['anchor_id'] = ! empty( $component['navigation']['anchor_id'] ) ? $component['navigation']['anchor_id'] : $payload['component_type'];

		$result = Storage::add_page_component( $page_id, $component, $verified );

		if ( \is_wp_error( $result ) ) {
			return self::redirect_error( $result, array( 'builder_page_id' => $page_id ) );
		}

		return self::redirect_to_builder( $page_id, array( Builder_Actions::NOTICE_QUERY => 'component_added', 'component_id' => $result['instance_id'] ) );
	}

	/**
	 * Save a page instance as reusable.
	 *
	 * @return void|string
	 */
	public static function handle_save_from_instance() {
		$page_id     = self::posted_absint( 'builder_page_id' );
		$instance_id = self::posted_text( 'component_id' );
		$mode        = 'linked' === self::posted_text( 'save_mode' ) ? 'linked' : 'local';
		$verified    = self::verify_page_request( 'save_from_instance', $page_id );

		if ( \is_wp_error( $verified ) ) {
			return self::redirect_error( $verified, array( 'builder_page_id' => $page_id ) );
		}

		$components = Storage::get_page_components( $page_id );

		if ( \is_wp_error( $components ) ) {
			return self::redirect_error( $components, array( 'builder_page_id' => $page_id ) );
		}

		$component = Builder_Actions::get_component_by_id( $components, $instance_id );

		if ( empty( $component ) ) {
			return self::redirect_error( new \WP_Error( 'nexa_pro_core_missing_component', \__( 'The requested component could not be found.', 'nexa-pro-core' ) ), array( 'builder_page_id' => $page_id ) );
		}

		unset( $component['_index'] );
		$source = Reusable_Components::resolve_component_instance( $component );

		if ( \is_wp_error( $source ) ) {
			$source = $component;
		}

		$source['inheritance_mode']      = 'local';
		$source['reusable_component_id'] = 0;

		$post_id = Reusable_Components::create_reusable_component( $source, $verified );

		if ( \is_wp_error( $post_id ) ) {
			return self::redirect_error( $post_id, array( 'builder_page_id' => $page_id ) );
		}

		if ( 'linked' === $mode ) {
			$component['inheritance_mode']      = 'linked';
			$component['reusable_component_id'] = absint( $post_id );
			$result                            = Storage::update_page_component( $page_id, $instance_id, $component, $verified );

			if ( \is_wp_error( $result ) ) {
				return self::redirect_error( $result, array( 'builder_page_id' => $page_id ) );
			}
		}

		return self::redirect_notice( 'created', array( 'reusable_id' => absint( $post_id ) ) );
	}

	/**
	 * Detach one linked instance.
	 *
	 * @return void|string
	 */
	public static function handle_detach_instance() {
		$page_id     = self::posted_absint( 'builder_page_id' );
		$instance_id = self::posted_text( 'component_id' );
		$verified    = self::verify_page_request( 'detach_instance', $page_id );

		if ( \is_wp_error( $verified ) ) {
			return self::redirect_error( $verified, array( 'builder_page_id' => $page_id ) );
		}

		$result = Reusable_Components::detach_reusable_component( $page_id, $instance_id, $verified );

		if ( \is_wp_error( $result ) ) {
			return self::redirect_error( $result, array( 'builder_page_id' => $page_id ) );
		}

		return self::redirect_to_builder( $page_id, array( Builder_Actions::NOTICE_QUERY => 'component_updated', 'component_id' => $instance_id ) );
	}

	/**
	 * Handle archive/restore.
	 *
	 * @param string   $operation Operation key.
	 * @param string   $notice    Notice key.
	 * @param callable $callback  Callback.
	 * @return void|string
	 */
	private static function handle_status_change( $operation, $notice, $callback ) {
		$post_id  = self::posted_absint( 'reusable_id' );
		$verified = self::verify_reusable_request( $operation, $post_id );

		if ( \is_wp_error( $verified ) ) {
			return self::redirect_error( $verified );
		}

		$result = call_user_func( $callback, $post_id, $verified );

		if ( \is_wp_error( $result ) ) {
			return self::redirect_error( $result );
		}

		return self::redirect_notice( $notice, array( 'reusable_id' => $post_id ) );
	}

	/**
	 * Verify reusable request.
	 *
	 * @param string $operation Operation key.
	 * @param int    $post_id   Reusable ID.
	 * @return array|\WP_Error
	 */
	private static function verify_reusable_request( $operation, $post_id = 0 ) {
		if ( ! Capabilities::current_user_can_manage_reusable_components() ) {
			return new \WP_Error( 'nexa_pro_core_forbidden', \__( 'You do not have permission to manage reusable components.', 'nexa-pro-core' ) );
		}

		$nonce        = self::posted_text( '_wpnonce' );
		$nonce_action = self::nonce_action( $operation, $post_id );

		if ( '' === $nonce || ! \wp_verify_nonce( $nonce, $nonce_action ) ) {
			return new \WP_Error( 'nexa_pro_core_invalid_nonce', \__( 'The reusable component request could not be verified.', 'nexa-pro-core' ) );
		}

		return array(
			'nonce'        => $nonce,
			'nonce_action' => $nonce_action,
		);
	}

	/**
	 * Verify a page write request.
	 *
	 * @param string $operation Operation key.
	 * @param int    $page_id   Page ID.
	 * @return array|\WP_Error
	 */
	private static function verify_page_request( $operation, $page_id ) {
		$verified = self::verify_reusable_request( $operation, $page_id );

		if ( \is_wp_error( $verified ) ) {
			return $verified;
		}

		$page_id = Storage::validate_page_id( $page_id );

		if ( \is_wp_error( $page_id ) ) {
			return $page_id;
		}

		if ( ! Capabilities::current_user_can_manage_components() || ! \current_user_can( 'edit_post', $page_id ) ) {
			return new \WP_Error( 'nexa_pro_core_page_forbidden', \__( 'You do not have permission to edit this page.', 'nexa-pro-core' ) );
		}

		return $verified;
	}

	/**
	 * Verify page and reusable IDs.
	 */
	private static function verify_page_and_reusable_request( $operation, $page_id, $post_id ) {
		$verified = self::verify_reusable_request( $operation, 0 );

		if ( \is_wp_error( $verified ) ) {
			return $verified;
		}

		$page_id = Storage::validate_page_id( $page_id );

		if ( \is_wp_error( $page_id ) ) {
			return $page_id;
		}

		if ( ! Capabilities::current_user_can_manage_components() || ! \current_user_can( 'edit_post', $page_id ) ) {
			return new \WP_Error( 'nexa_pro_core_page_forbidden', \__( 'You do not have permission to edit this page.', 'nexa-pro-core' ) );
		}

		$payload = Reusable_Components::get_reusable_component( $post_id );

		if ( \is_wp_error( $payload ) ) {
			return $payload;
		}

		return $verified;
	}

	/**
	 * Redirect to reusable screen with notice.
	 */
	private static function redirect_notice( $notice, $extra = array() ) {
		$extra[ self::NOTICE_QUERY ] = \sanitize_key( $notice );

		return self::redirect( $extra );
	}

	/**
	 * Redirect to reusable screen with error.
	 */
	private static function redirect_error( \WP_Error $error, $extra = array() ) {
		$extra[ self::ERROR_QUERY ] = $error->get_error_code();

		return self::redirect( $extra );
	}

	/**
	 * Redirect to reusable screen.
	 */
	private static function redirect( $extra = array() ) {
		$url = \add_query_arg(
			array_merge(
				array(
					'page'         => Builder_Admin::PAGE_SLUG,
					'builder_view' => 'reusable',
				),
				$extra
			),
			\admin_url( 'themes.php' )
		);

		if ( defined( 'NEXA_PRO_CORE_TESTING' ) && NEXA_PRO_CORE_TESTING ) {
			return $url;
		}

		\wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Redirect to builder page.
	 */
	private static function redirect_to_builder( $page_id, $extra = array() ) {
		$url = \add_query_arg(
			array_merge(
				array(
					'page'            => Builder_Admin::PAGE_SLUG,
					'builder_page_id' => absint( $page_id ),
				),
				$extra
			),
			\admin_url( 'themes.php' )
		);

		if ( defined( 'NEXA_PRO_CORE_TESTING' ) && NEXA_PRO_CORE_TESTING ) {
			return $url;
		}

		\wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Posted text.
	 */
	private static function posted_text( $key ) {
		return isset( $_POST[ $key ] ) && is_scalar( $_POST[ $key ] ) ? \sanitize_text_field( \wp_unslash( $_POST[ $key ] ) ) : '';
	}

	/**
	 * Posted absint.
	 */
	private static function posted_absint( $key ) {
		return isset( $_POST[ $key ] ) ? absint( \wp_unslash( $_POST[ $key ] ) ) : 0;
	}

	/**
	 * Posted array.
	 */
	private static function posted_array( $key ) {
		if ( empty( $_POST[ $key ] ) || ! is_array( $_POST[ $key ] ) ) {
			return array();
		}

		return \wp_unslash( $_POST[ $key ] );
	}
}
