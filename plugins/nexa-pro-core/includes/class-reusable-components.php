<?php
/**
 * Reusable component storage foundation.
 *
 * @package Nexa_Pro_Core
 */

namespace Nexa_Pro_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reusable component service.
 */
final class Reusable_Components {
	/**
	 * Request-level reusable payload cache.
	 *
	 * @var array
	 */
	private static $payload_cache = array();

	/**
	 * Register the private reusable component post type.
	 *
	 * @return void
	 */
	public static function register_post_type() {
		\register_post_type(
			NEXA_PRO_CORE_REUSABLE_POST_TYPE,
			array(
				'labels'              => array(
					'name'          => \__( 'Nexa Components', 'nexa-pro-core' ),
					'singular_name' => \__( 'Nexa Component', 'nexa-pro-core' ),
				),
				'public'              => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_ui'             => false,
				'show_in_menu'        => false,
				'show_in_rest'        => false,
				'rewrite'             => false,
				'query_var'           => false,
				'has_archive'         => false,
				'supports'            => array( 'title' ),
				'map_meta_cap'        => true,
				'capabilities'        => array(
					'edit_post'              => Capabilities::MANAGE_REUSABLE_COMPONENTS,
					'read_post'              => Capabilities::MANAGE_REUSABLE_COMPONENTS,
					'delete_post'            => Capabilities::MANAGE_REUSABLE_COMPONENTS,
					'edit_posts'             => Capabilities::MANAGE_REUSABLE_COMPONENTS,
					'edit_others_posts'      => Capabilities::MANAGE_REUSABLE_COMPONENTS,
					'delete_posts'           => Capabilities::MANAGE_REUSABLE_COMPONENTS,
					'delete_others_posts'    => Capabilities::MANAGE_REUSABLE_COMPONENTS,
					'publish_posts'          => Capabilities::MANAGE_REUSABLE_COMPONENTS,
					'read_private_posts'     => Capabilities::MANAGE_REUSABLE_COMPONENTS,
					'create_posts'           => Capabilities::MANAGE_REUSABLE_COMPONENTS,
					'delete_private_posts'   => Capabilities::MANAGE_REUSABLE_COMPONENTS,
					'delete_published_posts' => Capabilities::MANAGE_REUSABLE_COMPONENTS,
					'edit_private_posts'     => Capabilities::MANAGE_REUSABLE_COMPONENTS,
					'edit_published_posts'   => Capabilities::MANAGE_REUSABLE_COMPONENTS,
				),
			)
		);
	}

	/**
	 * Create a reusable component.
	 *
	 * @param array $component Component data.
	 * @param array $args      Operation args.
	 * @return int|\WP_Error
	 */
	public static function create_reusable_component( array $component, $args = array() ) {
		$verified = Capabilities::verify_reusable_write( $args );

		if ( \is_wp_error( $verified ) ) {
			return $verified;
		}

		$component = Sanitizer::sanitize_component_instance( $component );

		if ( \is_wp_error( $component ) ) {
			return $component;
		}

		if ( 'linked' === $component['inheritance_mode'] ) {
			return new \WP_Error( 'nexa_pro_core_invalid_reusable_reference', \__( 'Reusable components cannot be created as linked instances.', 'nexa-pro-core' ) );
		}

		$post_id = \wp_insert_post(
			array(
				'post_type'   => NEXA_PRO_CORE_REUSABLE_POST_TYPE,
				'post_status' => 'private',
				'post_title'  => $component['admin_title'],
			),
			true
		);

		if ( \is_wp_error( $post_id ) ) {
			return $post_id;
		}

		\update_post_meta( $post_id, NEXA_PRO_CORE_REUSABLE_META_KEY, $component );

		return absint( $post_id );
	}

	/**
	 * Get a reusable component payload.
	 *
	 * @param int $post_id Reusable component ID.
	 * @return array|\WP_Error
	 */
	public static function get_reusable_component( $post_id ) {
		$post_id = absint( $post_id );
		$post    = \get_post( $post_id );

		if ( ! $post || NEXA_PRO_CORE_REUSABLE_POST_TYPE !== $post->post_type ) {
			return new \WP_Error( 'nexa_pro_core_invalid_reusable_component', \__( 'Reusable component not found.', 'nexa-pro-core' ) );
		}

		$component = \get_post_meta( $post_id, NEXA_PRO_CORE_REUSABLE_META_KEY, true );

		if ( ! is_array( $component ) ) {
			return new \WP_Error( 'nexa_pro_core_invalid_reusable_payload', \__( 'Reusable component payload is invalid.', 'nexa-pro-core' ) );
		}

		if ( isset( self::$payload_cache[ $post_id ] ) ) {
			return self::$payload_cache[ $post_id ];
		}

		$component = Sanitizer::sanitize_component_instance( $component );

		if ( ! \is_wp_error( $component ) ) {
			self::$payload_cache[ $post_id ] = $component;
		}

		return $component;
	}

	/**
	 * Update a reusable component.
	 *
	 * @param int   $post_id Reusable component ID.
	 * @param array $changes Changes.
	 * @param array $args    Operation args.
	 * @return array|\WP_Error
	 */
	public static function update_reusable_component( $post_id, array $changes, $args = array() ) {
		$verified = Capabilities::verify_reusable_write( $args );

		if ( \is_wp_error( $verified ) ) {
			return $verified;
		}

		$existing = self::get_reusable_component( $post_id );

		if ( \is_wp_error( $existing ) ) {
			return $existing;
		}

		if ( ! empty( $changes['reusable_component_id'] ) && self::would_create_recursive_reference( $post_id, absint( $changes['reusable_component_id'] ) ) ) {
			return new \WP_Error( 'nexa_pro_core_recursive_reusable_component', \__( 'Reusable component references cannot be recursive.', 'nexa-pro-core' ) );
		}

		$changes['instance_id']    = $existing['instance_id'];
		$changes['component_type'] = $existing['component_type'];

		foreach ( array( 'content', 'design', 'navigation', 'advanced' ) as $group ) {
			if ( isset( $changes[ $group ] ) && is_array( $changes[ $group ] ) && isset( $existing[ $group ] ) && is_array( $existing[ $group ] ) ) {
				$changes[ $group ] = array_merge( $existing[ $group ], $changes[ $group ] );
			}
		}

		$updated                   = Sanitizer::sanitize_component_instance( array_merge( $existing, $changes ), $existing, true );

		if ( \is_wp_error( $updated ) ) {
			return $updated;
		}

		if ( 'linked' === $updated['inheritance_mode'] && self::would_create_recursive_reference( $post_id, $updated['reusable_component_id'] ) ) {
			return new \WP_Error( 'nexa_pro_core_recursive_reusable_component', \__( 'Reusable component references cannot be recursive.', 'nexa-pro-core' ) );
		}

		\update_post_meta( $post_id, NEXA_PRO_CORE_REUSABLE_META_KEY, $updated );
		unset( self::$payload_cache[ absint( $post_id ) ] );
		\wp_update_post(
			array(
				'ID'         => absint( $post_id ),
				'post_title' => $updated['admin_title'],
			)
		);

		return $updated;
	}

	/**
	 * Duplicate a reusable component.
	 *
	 * @param int   $post_id Reusable component ID.
	 * @param array $args    Operation args.
	 * @return int|\WP_Error
	 */
	public static function duplicate_reusable_component( $post_id, $args = array() ) {
		$component = self::get_reusable_component( $post_id );

		if ( \is_wp_error( $component ) ) {
			return $component;
		}

		$component['instance_id'] = Sanitizer::generate_instance_id( $component['component_type'] );
		$component['admin_title'] = trim( $component['admin_title'] . ' ' . \__( 'Copy', 'nexa-pro-core' ) );
		$component['created_at']  = Sanitizer::current_timestamp();
		$component['updated_at']  = $component['created_at'];

		return self::create_reusable_component( $component, $args );
	}

	/**
	 * Archive a reusable component.
	 *
	 * @param int   $post_id Reusable component ID.
	 * @param array $args    Operation args.
	 * @return true|\WP_Error
	 */
	public static function archive_reusable_component( $post_id, $args = array() ) {
		$verified = Capabilities::verify_reusable_write( $args );

		if ( \is_wp_error( $verified ) ) {
			return $verified;
		}

		$result = \wp_update_post(
			array(
				'ID'          => absint( $post_id ),
				'post_status' => 'draft',
			),
			true
		);

		return \is_wp_error( $result ) ? $result : true;
	}

	/**
	 * Restore an archived reusable component.
	 *
	 * @param int   $post_id Reusable component ID.
	 * @param array $args    Operation args.
	 * @return true|\WP_Error
	 */
	public static function restore_reusable_component( $post_id, $args = array() ) {
		$verified = Capabilities::verify_reusable_write( $args );

		if ( \is_wp_error( $verified ) ) {
			return $verified;
		}

		$result = \wp_update_post(
			array(
				'ID'          => absint( $post_id ),
				'post_status' => 'private',
			),
			true
		);

		return \is_wp_error( $result ) ? $result : true;
	}

	/**
	 * Delete a reusable component only when it is not linked.
	 *
	 * @param int   $post_id Reusable component ID.
	 * @param array $args    Operation args.
	 * @return true|\WP_Error
	 */
	public static function delete_reusable_component( $post_id, $args = array() ) {
		$verified = Capabilities::verify_reusable_write( $args );

		if ( \is_wp_error( $verified ) ) {
			return $verified;
		}

		if ( self::get_reusable_usage_count( $post_id ) > 0 ) {
			return new \WP_Error( 'nexa_pro_core_reusable_in_use', \__( 'Reusable components in use cannot be deleted.', 'nexa-pro-core' ) );
		}

		$deleted = \wp_delete_post( absint( $post_id ), true );

		return $deleted ? true : new \WP_Error( 'nexa_pro_core_reusable_delete_failed', \__( 'Reusable component could not be deleted.', 'nexa-pro-core' ) );
	}

	/**
	 * Detach all linked page instances for a reusable component.
	 *
	 * @param int   $post_id Reusable component ID.
	 * @param array $args    Operation args.
	 * @return int|\WP_Error
	 */
	public static function detach_all_linked_instances( $post_id, $args = array() ) {
		$verified = Capabilities::verify_reusable_write( $args );

		if ( \is_wp_error( $verified ) ) {
			return $verified;
		}

		$usages   = self::list_linked_page_instances( $post_id );
		$detached = 0;

		foreach ( $usages as $usage ) {
			$page_args = array_merge(
				$args,
				array(
					'bypass_capability_check' => ! empty( $args['bypass_capability_check'] ),
				)
			);

			$result = self::detach_reusable_component( $usage['page_id'], $usage['instance_id'], $page_args );

			if ( \is_wp_error( $result ) ) {
				return $result;
			}

			$detached++;
		}

		return $detached;
	}

	/**
	 * Get linked usage count.
	 *
	 * @param int $post_id Reusable component ID.
	 * @return int
	 */
	public static function get_reusable_usage_count( $post_id ) {
		return count( self::list_linked_page_instances( $post_id ) );
	}

	/**
	 * List linked page instances.
	 *
	 * @param int $post_id Reusable component ID.
	 * @return array
	 */
	public static function list_linked_page_instances( $post_id ) {
		$post_id = absint( $post_id );
		$matches = array();
		$pages   = \get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_key'       => NEXA_PRO_CORE_PAGE_META_KEY,
			)
		);

		foreach ( $pages as $page_id ) {
			$components = Storage::get_page_components( $page_id );

			if ( \is_wp_error( $components ) ) {
				continue;
			}

			foreach ( $components as $component ) {
				if ( 'linked' === $component['inheritance_mode'] && $post_id === absint( $component['reusable_component_id'] ) ) {
					$matches[] = array(
						'page_id'     => absint( $page_id ),
						'instance_id' => $component['instance_id'],
					);
				}
			}
		}

		return $matches;
	}

	/**
	 * Resolve a component instance.
	 *
	 * @param array $instance Component instance.
	 * @return array|\WP_Error
	 */
	public static function resolve_component_instance( array $instance ) {
		$instance = Sanitizer::sanitize_component_instance( $instance );

		if ( \is_wp_error( $instance ) ) {
			return $instance;
		}

		if ( 'linked' !== $instance['inheritance_mode'] || ! $instance['reusable_component_id'] ) {
			return $instance;
		}

		$reusable = self::get_reusable_component( $instance['reusable_component_id'] );

		if ( \is_wp_error( $reusable ) ) {
			return $reusable;
		}

		$resolved                     = $instance;
		$resolved['layout']           = $reusable['layout'];
		$resolved['content']          = $reusable['content'];
		$resolved['design']           = $reusable['design'];
		$resolved['advanced']         = $reusable['advanced'];
		$resolved['resolved_from_id'] = absint( $instance['reusable_component_id'] );

		return $resolved;
	}

	/**
	 * Detach a linked reusable component from a page instance.
	 *
	 * @param int    $page_id     Page ID.
	 * @param string $instance_id Instance ID.
	 * @param array  $args        Operation args.
	 * @return array|\WP_Error
	 */
	public static function detach_reusable_component( $page_id, $instance_id, $args = array() ) {
		$components = Storage::get_page_components( $page_id );

		if ( \is_wp_error( $components ) ) {
			return $components;
		}

		foreach ( $components as $component ) {
			if ( $instance_id === $component['instance_id'] ) {
				$resolved = self::resolve_component_instance( $component );

				if ( \is_wp_error( $resolved ) ) {
					$resolved = $component;
				}

				$resolved['inheritance_mode']      = 'local';
				$resolved['reusable_component_id'] = 0;
				unset( $resolved['resolved_from_id'] );

				return Storage::update_page_component( $page_id, $instance_id, $resolved, $args );
			}
		}

		return new \WP_Error( 'nexa_pro_core_missing_component', \__( 'The requested component could not be found.', 'nexa-pro-core' ) );
	}

	/**
	 * Determine whether a reusable reference would become recursive.
	 *
	 * @param int $component_id        Reusable component ID being edited.
	 * @param int $target_component_id Target reusable component ID.
	 * @return bool
	 */
	public static function would_create_recursive_reference( $component_id, $target_component_id ) {
		$component_id        = absint( $component_id );
		$target_component_id = absint( $target_component_id );

		if ( ! $component_id || ! $target_component_id ) {
			return false;
		}

		if ( $component_id === $target_component_id ) {
			return true;
		}

		$seen = array();

		for ( $depth = 0; $depth < 20; $depth++ ) {
			if ( in_array( $target_component_id, $seen, true ) ) {
				return true;
			}

			$seen[]  = $target_component_id;
			$payload = self::get_reusable_component( $target_component_id );

			if ( \is_wp_error( $payload ) || 'linked' !== $payload['inheritance_mode'] || empty( $payload['reusable_component_id'] ) ) {
				return false;
			}

			$target_component_id = absint( $payload['reusable_component_id'] );

			if ( $component_id === $target_component_id ) {
				return true;
			}
		}

		return true;
	}
}
