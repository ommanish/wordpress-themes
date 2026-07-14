<?php
/**
 * Builder admin request handlers.
 *
 * @package Nexa_Pro_Core
 */

namespace Nexa_Pro_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builder action service.
 */
final class Builder_Actions {
	const NOTICE_QUERY = 'nexa_core_notice';
	const ERROR_QUERY  = 'nexa_core_error';

	/**
	 * Register authenticated admin-post handlers.
	 *
	 * @return void
	 */
	public static function register() {
		\add_action( 'admin_post_nexa_pro_core_builder_save_component', array( __CLASS__, 'handle_save_component' ) );
		\add_action( 'admin_post_nexa_pro_core_builder_duplicate_component', array( __CLASS__, 'handle_duplicate_component' ) );
		\add_action( 'admin_post_nexa_pro_core_builder_toggle_component', array( __CLASS__, 'handle_toggle_component' ) );
		\add_action( 'admin_post_nexa_pro_core_builder_move_order', array( __CLASS__, 'handle_move_order' ) );
		\add_action( 'admin_post_nexa_pro_core_builder_save_order', array( __CLASS__, 'handle_save_order' ) );
		\add_action( 'admin_post_nexa_pro_core_builder_move_page', array( __CLASS__, 'handle_move_page' ) );
		\add_action( 'admin_post_nexa_pro_core_builder_delete_component', array( __CLASS__, 'handle_delete_component' ) );
		\add_action( 'admin_post_nexa_pro_core_builder_undo_delete', array( __CLASS__, 'handle_undo_delete' ) );
	}

	/**
	 * Get a nonce action for a builder operation.
	 *
	 * @param string $operation Operation key.
	 * @param int    $page_id   Page ID.
	 * @return string
	 */
	public static function nonce_action( $operation, $page_id ) {
		return 'nexa_pro_core_builder_' . \sanitize_key( $operation ) . '_' . absint( $page_id );
	}

	/**
	 * Get the current user's delete undo payload.
	 *
	 * @return array
	 */
	public static function get_recent_deleted_component() {
		$payload = \get_transient( self::deleted_transient_key() );

		return is_array( $payload ) ? $payload : array();
	}

	/**
	 * Get a retained invalid editor submission for the current user.
	 *
	 * @return array
	 */
	public static function get_error_draft() {
		$payload = \get_transient( self::draft_transient_key() );

		return is_array( $payload ) ? $payload : array();
	}

	/**
	 * Clear the retained invalid editor submission for the current user.
	 *
	 * @return void
	 */
	public static function clear_error_draft() {
		\delete_transient( self::draft_transient_key() );
	}

	/**
	 * Build raw component data from a posted editor payload.
	 *
	 * @param array $posted Posted component values.
	 * @return array
	 */
	public static function build_component_from_posted_values( array $posted ) {
		$content    = isset( $posted['content'] ) && is_array( $posted['content'] ) ? $posted['content'] : array();
		$design     = isset( $posted['design'] ) && is_array( $posted['design'] ) ? $posted['design'] : array();
		$navigation = isset( $posted['navigation'] ) && is_array( $posted['navigation'] ) ? $posted['navigation'] : array();
		$advanced   = isset( $posted['advanced'] ) && is_array( $posted['advanced'] ) ? $posted['advanced'] : array();

		$component = array(
			'instance_id'           => isset( $posted['instance_id'] ) ? \sanitize_text_field( $posted['instance_id'] ) : '',
			'component_type'        => isset( $posted['component_type'] ) ? \sanitize_key( $posted['component_type'] ) : '',
			'admin_title'           => isset( $posted['admin_title'] ) ? \sanitize_text_field( $posted['admin_title'] ) : '',
			'enabled'               => isset( $posted['enabled'] ) ? $posted['enabled'] : '0',
			'layout'                => isset( $posted['layout'] ) ? \sanitize_key( $posted['layout'] ) : '',
			'content'               => self::prepare_posted_group( $content ),
			'design'                => self::prepare_posted_group( $design ),
			'navigation'            => self::prepare_posted_group( $navigation ),
			'advanced'              => self::prepare_posted_group( $advanced ),
			'reusable_component_id' => isset( $posted['reusable_component_id'] ) ? absint( $posted['reusable_component_id'] ) : 0,
			'inheritance_mode'      => isset( $posted['inheritance_mode'] ) ? \sanitize_key( $posted['inheritance_mode'] ) : 'local',
		);

		if ( '' === $component['admin_title'] ) {
			$component['admin_title'] = Sanitizer::default_admin_title( $component['component_type'] );
		}

		return $component;
	}

	/**
	 * Find one component in a collection.
	 *
	 * @param array  $components  Components.
	 * @param string $instance_id Instance ID.
	 * @return array
	 */
	public static function get_component_by_id( array $components, $instance_id ) {
		foreach ( $components as $index => $component ) {
			if ( isset( $component['instance_id'] ) && $instance_id === $component['instance_id'] ) {
				$component['_index'] = $index;
				return $component;
			}
		}

		return array();
	}

	/**
	 * Handle add/edit saves.
	 *
	 * @return string|void
	 */
	public static function handle_save_component() {
		$page_id  = self::posted_absint( 'builder_page_id' );
		$verified = self::verify_page_request( $page_id, 'save_component' );

		if ( \is_wp_error( $verified ) ) {
			return self::redirect_error( $page_id, $verified );
		}

		$posted    = self::posted_array( 'component' );
		$component = self::build_component_from_posted_values( $posted );
		$is_edit   = ! empty( $component['instance_id'] );
		$layout    = isset( $component['layout'] ) ? \sanitize_key( $component['layout'] ) : '';
		$layouts   = Sanitizer::allowed_layouts_for_type( $component['component_type'] );

		if ( '' === $layout || ! in_array( $layout, $layouts, true ) ) {
			$error = new \WP_Error( 'nexa_pro_core_invalid_layout', \__( 'Choose a supported layout for this component.', 'nexa-pro-core' ) );
			self::store_error_draft( $page_id, $component, $error, $is_edit ? 'edit' : 'add' );

			return self::redirect_error(
				$page_id,
				$error,
				array(
					'builder_mode' => $is_edit ? 'edit' : 'add',
					'component_id' => $is_edit ? $component['instance_id'] : '',
					'add_type'     => $is_edit ? '' : $component['component_type'],
				)
			);
		}

		if ( ! $is_edit && empty( $component['navigation']['anchor_id'] ) && function_exists( 'nexa_pro_get_component_default_anchor' ) ) {
			$component['navigation']['anchor_id'] = \nexa_pro_get_component_default_anchor( $component['component_type'] );
		}

		$result = $is_edit
			? Storage::update_page_component( $page_id, $component['instance_id'], $component, $verified )
			: Storage::add_page_component( $page_id, $component, $verified );

		if ( \is_wp_error( $result ) ) {
			self::store_error_draft( $page_id, $component, $result, $is_edit ? 'edit' : 'add' );
			return self::redirect_error(
				$page_id,
				$result,
				array(
					'builder_mode' => $is_edit ? 'edit' : 'add',
					'component_id' => $is_edit ? $component['instance_id'] : '',
					'add_type'     => $is_edit ? '' : $component['component_type'],
				)
			);
		}

		self::clear_error_draft();

		return self::redirect_notice(
			$page_id,
			$is_edit ? 'component_updated' : 'component_added',
			array( 'component_id' => $result['instance_id'] )
		);
	}

	/**
	 * Handle duplication.
	 *
	 * @return string|void
	 */
	public static function handle_duplicate_component() {
		$page_id     = self::posted_absint( 'builder_page_id' );
		$instance_id = self::posted_text( 'component_id' );
		$verified    = self::verify_page_request( $page_id, 'duplicate_component' );

		if ( \is_wp_error( $verified ) ) {
			return self::redirect_error( $page_id, $verified );
		}

		$result = Storage::duplicate_page_component( $page_id, $instance_id, $verified );

		if ( \is_wp_error( $result ) ) {
			return self::redirect_error( $page_id, $result );
		}

		return self::redirect_notice( $page_id, 'component_duplicated', array( 'component_id' => $result['instance_id'] ) );
	}

	/**
	 * Handle enable/disable.
	 *
	 * @return string|void
	 */
	public static function handle_toggle_component() {
		$page_id     = self::posted_absint( 'builder_page_id' );
		$instance_id = self::posted_text( 'component_id' );
		$enabled     = self::posted_text( 'enabled' );
		$verified    = self::verify_page_request( $page_id, 'toggle_component' );

		if ( \is_wp_error( $verified ) ) {
			return self::redirect_error( $page_id, $verified );
		}

		$result = Storage::update_page_component( $page_id, $instance_id, array( 'enabled' => '1' === $enabled ? '1' : '0' ), $verified );

		if ( \is_wp_error( $result ) ) {
			return self::redirect_error( $page_id, $result );
		}

		return self::redirect_notice( $page_id, 'component_toggled', array( 'component_id' => $instance_id ) );
	}

	/**
	 * Handle keyboard/no-JavaScript move up/down.
	 *
	 * @return string|void
	 */
	public static function handle_move_order() {
		$page_id     = self::posted_absint( 'builder_page_id' );
		$instance_id = self::posted_text( 'component_id' );
		$direction   = self::posted_text( 'direction' );
		$verified    = self::verify_page_request( $page_id, 'move_order' );

		if ( \is_wp_error( $verified ) ) {
			return self::redirect_error( $page_id, $verified );
		}

		$components = Storage::get_page_components( $page_id );

		if ( \is_wp_error( $components ) ) {
			return self::redirect_error( $page_id, $components );
		}

		$ids   = wp_list_pluck( $components, 'instance_id' );
		$index = array_search( $instance_id, $ids, true );

		if ( false === $index ) {
			return self::redirect_error( $page_id, new \WP_Error( 'nexa_pro_core_missing_component', \__( 'The requested component could not be found.', 'nexa-pro-core' ) ) );
		}

		$target = 'down' === $direction ? $index + 1 : $index - 1;

		if ( $target < 0 || $target >= count( $ids ) ) {
			return self::redirect_notice( $page_id, 'component_order_unchanged', array( 'component_id' => $instance_id ) );
		}

		$swap          = $ids[ $target ];
		$ids[ $target ] = $ids[ $index ];
		$ids[ $index ]  = $swap;
		$result        = Storage::reorder_page_components( $page_id, $ids, $verified );

		if ( \is_wp_error( $result ) ) {
			return self::redirect_error( $page_id, $result );
		}

		return self::redirect_notice( $page_id, 'component_reordered', array( 'component_id' => $instance_id ) );
	}

	/**
	 * Handle explicit save order.
	 *
	 * @return string|void
	 */
	public static function handle_save_order() {
		$page_id  = self::posted_absint( 'builder_page_id' );
		$verified = self::verify_page_request( $page_id, 'save_order' );

		if ( \is_wp_error( $verified ) ) {
			return self::redirect_error( $page_id, $verified );
		}

		$ordered_ids = self::posted_array( 'ordered_ids' );
		$ordered_ids = array_values( array_map( 'sanitize_text_field', $ordered_ids ) );
		$result      = Storage::reorder_page_components( $page_id, $ordered_ids, $verified );

		if ( \is_wp_error( $result ) ) {
			return self::redirect_error( $page_id, $result );
		}

		return self::redirect_notice( $page_id, 'component_order_saved' );
	}

	/**
	 * Handle move to page.
	 *
	 * @return string|void
	 */
	public static function handle_move_page() {
		$page_id        = self::posted_absint( 'builder_page_id' );
		$target_page_id = self::posted_absint( 'target_page_id' );
		$instance_id    = self::posted_text( 'component_id' );
		$verified       = self::verify_page_request( $page_id, 'move_page' );

		if ( \is_wp_error( $verified ) ) {
			return self::redirect_error( $page_id, $verified );
		}

		if ( ! \current_user_can( 'edit_post', $target_page_id ) ) {
			return self::redirect_error( $page_id, new \WP_Error( 'nexa_pro_core_target_forbidden', \__( 'You do not have permission to edit the target page.', 'nexa-pro-core' ) ) );
		}

		$result = Storage::move_page_component( $page_id, $target_page_id, $instance_id, $verified );

		if ( \is_wp_error( $result ) ) {
			return self::redirect_error( $page_id, $result );
		}

		return self::redirect_notice( $target_page_id, 'component_moved', array( 'component_id' => $result['instance_id'] ) );
	}

	/**
	 * Handle delete with accessible confirmation.
	 *
	 * @return string|void
	 */
	public static function handle_delete_component() {
		$page_id     = self::posted_absint( 'builder_page_id' );
		$instance_id = self::posted_text( 'component_id' );
		$verified    = self::verify_page_request( $page_id, 'delete_component' );

		if ( \is_wp_error( $verified ) ) {
			return self::redirect_error( $page_id, $verified );
		}

		if ( '1' !== self::posted_text( 'confirm_delete' ) ) {
			return self::redirect_error( $page_id, new \WP_Error( 'nexa_pro_core_delete_unconfirmed', \__( 'Confirm deletion before removing a component.', 'nexa-pro-core' ) ) );
		}

		$components = Storage::get_page_components( $page_id );

		if ( \is_wp_error( $components ) ) {
			return self::redirect_error( $page_id, $components );
		}

		$component = self::get_component_by_id( $components, $instance_id );

		if ( empty( $component ) ) {
			return self::redirect_error( $page_id, new \WP_Error( 'nexa_pro_core_missing_component', \__( 'The requested component could not be found.', 'nexa-pro-core' ) ) );
		}

		\set_transient(
			self::deleted_transient_key(),
			array(
				'page_id'   => $page_id,
				'component' => $component,
				'index'     => absint( $component['_index'] ),
			),
			10 * MINUTE_IN_SECONDS
		);

		$result = Storage::remove_page_component( $page_id, $instance_id, $verified );

		if ( \is_wp_error( $result ) ) {
			return self::redirect_error( $page_id, $result );
		}

		return self::redirect_notice( $page_id, 'component_deleted' );
	}

	/**
	 * Restore the current user's most recent deletion.
	 *
	 * @return string|void
	 */
	public static function handle_undo_delete() {
		$page_id  = self::posted_absint( 'builder_page_id' );
		$verified = self::verify_page_request( $page_id, 'undo_delete' );

		if ( \is_wp_error( $verified ) ) {
			return self::redirect_error( $page_id, $verified );
		}

		$deleted = self::get_recent_deleted_component();

		if ( empty( $deleted['component'] ) || empty( $deleted['page_id'] ) || absint( $deleted['page_id'] ) !== $page_id ) {
			return self::redirect_error( $page_id, new \WP_Error( 'nexa_pro_core_undo_expired', \__( 'The deleted component can no longer be restored.', 'nexa-pro-core' ) ) );
		}

		$components = Storage::get_page_components( $page_id );

		if ( \is_wp_error( $components ) ) {
			return self::redirect_error( $page_id, $components );
		}

		$component = $deleted['component'];
		unset( $component['_index'] );

		$index = isset( $deleted['index'] ) ? absint( $deleted['index'] ) : count( $components );
		$index = min( $index, count( $components ) );

		array_splice( $components, $index, 0, array( $component ) );

		$result = Storage::save_page_components( $page_id, $components, $verified );

		if ( \is_wp_error( $result ) ) {
			return self::redirect_error( $page_id, $result );
		}

		\delete_transient( self::deleted_transient_key() );

		return self::redirect_notice( $page_id, 'component_restored', array( 'component_id' => $component['instance_id'] ) );
	}

	/**
	 * Prepare a posted group for the storage sanitizer.
	 *
	 * @param array $group Posted values.
	 * @return array
	 */
	private static function prepare_posted_group( array $group ) {
		$prepared = array();

		foreach ( $group as $key => $value ) {
			$key = \sanitize_key( $key );

			if ( '' === $key ) {
				continue;
			}

			if ( is_array( $value ) ) {
				$prepared[ $key ] = self::prepare_posted_group( $value );
				continue;
			}

			$prepared[ $key ] = is_scalar( $value ) ? (string) $value : '';
		}

		return $prepared;
	}

	/**
	 * Verify a page action.
	 *
	 * @param int    $page_id   Page ID.
	 * @param string $operation Operation key.
	 * @return array|\WP_Error
	 */
	private static function verify_page_request( $page_id, $operation ) {
		$page_id = Storage::validate_page_id( $page_id );

		if ( \is_wp_error( $page_id ) ) {
			return $page_id;
		}

		if ( ! \current_user_can( Capabilities::MANAGE_COMPONENTS ) ) {
			return new \WP_Error( 'nexa_pro_core_forbidden', \__( 'You do not have permission to manage Nexa Pro components.', 'nexa-pro-core' ) );
		}

		if ( ! \current_user_can( 'edit_post', $page_id ) ) {
			return new \WP_Error( 'nexa_pro_core_page_forbidden', \__( 'You do not have permission to edit this page.', 'nexa-pro-core' ) );
		}

		$page = \get_post( $page_id );

		if ( $page && 'trash' === $page->post_status ) {
			return new \WP_Error( 'nexa_pro_core_invalid_page', \__( 'Trashed pages cannot be edited in the component builder.', 'nexa-pro-core' ) );
		}

		$nonce        = self::posted_text( '_wpnonce' );
		$nonce_action = self::nonce_action( $operation, $page_id );

		if ( '' === $nonce || ! \wp_verify_nonce( $nonce, $nonce_action ) ) {
			return new \WP_Error( 'nexa_pro_core_invalid_nonce', \__( 'The component storage request could not be verified.', 'nexa-pro-core' ) );
		}

		return array(
			'nonce'        => $nonce,
			'nonce_action' => $nonce_action,
		);
	}

	/**
	 * Store a failed editor draft.
	 *
	 * @param int       $page_id   Page ID.
	 * @param array     $component Component.
	 * @param \WP_Error $error     Error.
	 * @param string    $mode      Editor mode.
	 * @return void
	 */
	private static function store_error_draft( $page_id, array $component, \WP_Error $error, $mode ) {
		\set_transient(
			self::draft_transient_key(),
			array(
				'page_id'   => absint( $page_id ),
				'component' => $component,
				'mode'      => \sanitize_key( $mode ),
				'message'   => $error->get_error_message(),
			),
			5 * MINUTE_IN_SECONDS
		);
	}

	/**
	 * Redirect with a success notice.
	 *
	 * @param int    $page_id Page ID.
	 * @param string $notice  Notice key.
	 * @param array  $extra   Extra query args.
	 * @return string|void
	 */
	private static function redirect_notice( $page_id, $notice, $extra = array() ) {
		$extra[ self::NOTICE_QUERY ] = \sanitize_key( $notice );

		return self::redirect_to_builder( $page_id, $extra );
	}

	/**
	 * Redirect with an error notice.
	 *
	 * @param int       $page_id Page ID.
	 * @param \WP_Error $error   Error.
	 * @param array     $extra   Extra query args.
	 * @return string|void
	 */
	private static function redirect_error( $page_id, \WP_Error $error, $extra = array() ) {
		$extra[ self::ERROR_QUERY ] = $error->get_error_code();

		if ( $error->get_error_message() ) {
			\set_transient(
				self::error_transient_key(),
				$error->get_error_message(),
				MINUTE_IN_SECONDS
			);
		}

		return self::redirect_to_builder( $page_id, $extra );
	}

	/**
	 * Redirect to the builder.
	 *
	 * @param int   $page_id Page ID.
	 * @param array $extra   Query args.
	 * @return string|void
	 */
	private static function redirect_to_builder( $page_id, $extra = array() ) {
		$args = array_merge(
			array(
				'page'            => Builder_Admin::PAGE_SLUG,
				'builder_page_id' => absint( $page_id ),
			),
			array_filter(
				$extra,
				function ( $value ) {
					return '' !== (string) $value;
				}
			)
		);

		$url = \add_query_arg( $args, \admin_url( 'themes.php' ) );

		if ( defined( 'NEXA_PRO_CORE_TESTING' ) && NEXA_PRO_CORE_TESTING ) {
			return $url;
		}

		\wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Get the transient key for deleted component undo.
	 *
	 * @return string
	 */
	private static function deleted_transient_key() {
		return 'nexa_pro_core_deleted_' . absint( \get_current_user_id() );
	}

	/**
	 * Get the transient key for failed editor drafts.
	 *
	 * @return string
	 */
	private static function draft_transient_key() {
		return 'nexa_pro_core_draft_' . absint( \get_current_user_id() );
	}

	/**
	 * Get the transient key for the latest builder error.
	 *
	 * @return string
	 */
	public static function error_transient_key() {
		return 'nexa_pro_core_error_' . absint( \get_current_user_id() );
	}

	/**
	 * Get a sanitized text value from POST.
	 *
	 * @param string $key Key.
	 * @return string
	 */
	private static function posted_text( $key ) {
		return isset( $_POST[ $key ] ) && is_scalar( $_POST[ $key ] ) ? \sanitize_text_field( \wp_unslash( $_POST[ $key ] ) ) : '';
	}

	/**
	 * Get an absint value from POST.
	 *
	 * @param string $key Key.
	 * @return int
	 */
	private static function posted_absint( $key ) {
		return isset( $_POST[ $key ] ) ? absint( \wp_unslash( $_POST[ $key ] ) ) : 0;
	}

	/**
	 * Get an array from POST.
	 *
	 * @param string $key Key.
	 * @return array
	 */
	private static function posted_array( $key ) {
		if ( empty( $_POST[ $key ] ) || ! is_array( $_POST[ $key ] ) ) {
			return array();
		}

		return \wp_unslash( $_POST[ $key ] );
	}
}
