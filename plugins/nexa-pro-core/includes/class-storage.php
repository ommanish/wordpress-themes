<?php
/**
 * Page component storage helpers.
 *
 * @package Nexa_Pro_Core
 */

namespace Nexa_Pro_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Page component storage service.
 */
final class Storage {
	/**
	 * Validate a WordPress page ID.
	 *
	 * @param int $page_id Page ID.
	 * @return int|\WP_Error
	 */
	public static function validate_page_id( $page_id ) {
		$page_id = absint( $page_id );

		if ( ! $page_id ) {
			return new \WP_Error( 'nexa_pro_core_invalid_page_id', \__( 'A valid page ID is required.', 'nexa-pro-core' ) );
		}

		$post = \get_post( $page_id );

		if ( ! $post || 'page' !== $post->post_type ) {
			return new \WP_Error( 'nexa_pro_core_invalid_page', \__( 'Component storage is limited to WordPress pages.', 'nexa-pro-core' ) );
		}

		return $page_id;
	}

	/**
	 * Get normalized page components.
	 *
	 * @param int $page_id Page ID.
	 * @return array|\WP_Error
	 */
	public static function get_page_components( $page_id ) {
		$page_id = self::validate_page_id( $page_id );

		if ( \is_wp_error( $page_id ) ) {
			return $page_id;
		}

		$components = \get_post_meta( $page_id, NEXA_PRO_CORE_PAGE_META_KEY, true );

		if ( empty( $components ) ) {
			return array();
		}

		if ( ! is_array( $components ) ) {
			return array();
		}

		return Sanitizer::sanitize_component_collection( $components );
	}

	/**
	 * Save page components.
	 *
	 * @param int   $page_id    Page ID.
	 * @param array $components Components.
	 * @param array $args       Operation args.
	 * @return array|\WP_Error
	 */
	public static function save_page_components( $page_id, array $components, $args = array() ) {
		$page_id = self::validate_page_id( $page_id );

		if ( \is_wp_error( $page_id ) ) {
			return $page_id;
		}

		$verified = Capabilities::verify_page_write( $page_id, $args );

		if ( \is_wp_error( $verified ) ) {
			return $verified;
		}

		$components = Sanitizer::sanitize_component_collection( $components );

		if ( \is_wp_error( $components ) ) {
			return $components;
		}

		$written = self::write_components( $page_id, $components );

		if ( \is_wp_error( $written ) ) {
			return $written;
		}

		return $components;
	}

	/**
	 * Add a page component.
	 *
	 * @param int   $page_id   Page ID.
	 * @param array $component Component data.
	 * @param array $args      Operation args.
	 * @return array|\WP_Error
	 */
	public static function add_page_component( $page_id, array $component, $args = array() ) {
		$components = self::get_page_components( $page_id );

		if ( \is_wp_error( $components ) ) {
			return $components;
		}

		$component['order'] = count( $components );
		$component          = Sanitizer::sanitize_component_instance( $component );

		if ( \is_wp_error( $component ) ) {
			return $component;
		}

		$existing_ids                = wp_list_pluck( $components, 'instance_id' );
		if ( in_array( $component['instance_id'], $existing_ids, true ) ) {
			$component['instance_id'] = Sanitizer::generate_unique_instance_id( $component['component_type'], $existing_ids );
		}

		$components[]                = $component;
		$saved                       = self::save_page_components( $page_id, $components, $args );

		if ( \is_wp_error( $saved ) ) {
			return $saved;
		}

		return $component;
	}

	/**
	 * Update a page component.
	 *
	 * @param int    $page_id     Page ID.
	 * @param string $instance_id Instance ID.
	 * @param array  $changes     Changes.
	 * @param array  $args        Operation args.
	 * @return array|\WP_Error
	 */
	public static function update_page_component( $page_id, $instance_id, array $changes, $args = array() ) {
		$components = self::get_page_components( $page_id );

		if ( \is_wp_error( $components ) ) {
			return $components;
		}

		foreach ( $components as $index => $component ) {
			if ( $instance_id !== $component['instance_id'] ) {
				continue;
			}

			$changes['instance_id']    = $component['instance_id'];
			$changes['component_type'] = $component['component_type'];

			foreach ( array( 'content', 'design', 'navigation', 'advanced' ) as $group ) {
				if ( isset( $changes[ $group ] ) && is_array( $changes[ $group ] ) && isset( $component[ $group ] ) && is_array( $component[ $group ] ) ) {
					$changes[ $group ] = array_merge( $component[ $group ], $changes[ $group ] );
				}
			}

			$updated                   = Sanitizer::sanitize_component_instance( array_merge( $component, $changes ), $component, true );

			if ( \is_wp_error( $updated ) ) {
				return $updated;
			}

			$components[ $index ] = $updated;
			$saved                = self::save_page_components( $page_id, $components, $args );

			if ( \is_wp_error( $saved ) ) {
				return $saved;
			}

			return $updated;
		}

		return new \WP_Error( 'nexa_pro_core_missing_component', \__( 'The requested component could not be found.', 'nexa-pro-core' ) );
	}

	/**
	 * Duplicate a page component.
	 *
	 * @param int    $page_id     Page ID.
	 * @param string $instance_id Instance ID.
	 * @param array  $args        Operation args.
	 * @return array|\WP_Error
	 */
	public static function duplicate_page_component( $page_id, $instance_id, $args = array() ) {
		$components = self::get_page_components( $page_id );

		if ( \is_wp_error( $components ) ) {
			return $components;
		}

		$existing_ids = wp_list_pluck( $components, 'instance_id' );

		foreach ( $components as $index => $component ) {
			if ( $instance_id !== $component['instance_id'] ) {
				continue;
			}

			$duplicate                = $component;
			$duplicate['instance_id'] = Sanitizer::generate_unique_instance_id( $component['component_type'], $existing_ids );
			$duplicate['admin_title'] = trim( $component['admin_title'] . ' ' . \__( 'Copy', 'nexa-pro-core' ) );
			$duplicate['created_at']  = Sanitizer::current_timestamp();
			$duplicate['updated_at']  = $duplicate['created_at'];
			$duplicate['order']       = $component['order'] + 1;

			if ( ! empty( $duplicate['navigation']['anchor_id'] ) ) {
				$duplicate['navigation']['anchor_id'] = self::get_unique_anchor_id( $duplicate['navigation']['anchor_id'], $components );
			}

			array_splice( $components, $index + 1, 0, array( $duplicate ) );

			$saved = self::save_page_components( $page_id, $components, $args );

			if ( \is_wp_error( $saved ) ) {
				return $saved;
			}

			return $duplicate;
		}

		return new \WP_Error( 'nexa_pro_core_missing_component', \__( 'The requested component could not be found.', 'nexa-pro-core' ) );
	}

	/**
	 * Remove a page component.
	 *
	 * @param int    $page_id     Page ID.
	 * @param string $instance_id Instance ID.
	 * @param array  $args        Operation args.
	 * @return array|\WP_Error
	 */
	public static function remove_page_component( $page_id, $instance_id, $args = array() ) {
		$components = self::get_page_components( $page_id );

		if ( \is_wp_error( $components ) ) {
			return $components;
		}

		$remaining = array();
		$removed   = false;

		foreach ( $components as $component ) {
			if ( $instance_id === $component['instance_id'] ) {
				$removed = true;
				continue;
			}

			$remaining[] = $component;
		}

		if ( ! $removed ) {
			return new \WP_Error( 'nexa_pro_core_missing_component', \__( 'The requested component could not be found.', 'nexa-pro-core' ) );
		}

		return self::save_page_components( $page_id, $remaining, $args );
	}

	/**
	 * Reorder page components.
	 *
	 * @param int   $page_id     Page ID.
	 * @param array $ordered_ids Ordered instance IDs.
	 * @param array $args        Operation args.
	 * @return array|\WP_Error
	 */
	public static function reorder_page_components( $page_id, array $ordered_ids, $args = array() ) {
		$components = self::get_page_components( $page_id );

		if ( \is_wp_error( $components ) ) {
			return $components;
		}

		$current_ids = wp_list_pluck( $components, 'instance_id' );
		$ordered_ids = array_values( array_map( 'strval', $ordered_ids ) );

		sort( $current_ids );
		$sorted_ordered = $ordered_ids;
		sort( $sorted_ordered );

		if ( $current_ids !== $sorted_ordered ) {
			return new \WP_Error( 'nexa_pro_core_invalid_reorder', \__( 'Reorder requests must include each component exactly once.', 'nexa-pro-core' ) );
		}

		$by_id = array();

		foreach ( $components as $component ) {
			$by_id[ $component['instance_id'] ] = $component;
		}

		$reordered = array();

		foreach ( $ordered_ids as $index => $instance_id ) {
			$component          = $by_id[ $instance_id ];
			$component['order'] = $index;
			$reordered[]        = $component;
		}

		return self::save_page_components( $page_id, $reordered, $args );
	}

	/**
	 * Move a component between pages.
	 *
	 * @param int    $source_page_id Source page ID.
	 * @param int    $target_page_id Target page ID.
	 * @param string $instance_id    Instance ID.
	 * @param array  $args           Operation args.
	 * @return array|\WP_Error
	 */
	public static function move_page_component( $source_page_id, $target_page_id, $instance_id, $args = array() ) {
		$source_page_id = self::validate_page_id( $source_page_id );
		$target_page_id = self::validate_page_id( $target_page_id );

		if ( \is_wp_error( $source_page_id ) ) {
			return $source_page_id;
		}

		if ( \is_wp_error( $target_page_id ) ) {
			return $target_page_id;
		}

		if ( $source_page_id === $target_page_id ) {
			return new \WP_Error( 'nexa_pro_core_same_page_move', \__( 'Component move requires two different pages.', 'nexa-pro-core' ) );
		}

		$source_verified = Capabilities::verify_page_write( $source_page_id, $args );
		$target_verified = Capabilities::verify_page_write( $target_page_id, $args );

		if ( \is_wp_error( $source_verified ) ) {
			return $source_verified;
		}

		if ( \is_wp_error( $target_verified ) ) {
			return $target_verified;
		}

		$source_components = self::get_page_components( $source_page_id );
		$target_components = self::get_page_components( $target_page_id );

		if ( \is_wp_error( $source_components ) ) {
			return $source_components;
		}

		if ( \is_wp_error( $target_components ) ) {
			return $target_components;
		}

		$moved_component = null;
		$new_source      = array();

		foreach ( $source_components as $component ) {
			if ( $instance_id === $component['instance_id'] ) {
				$moved_component = $component;
				continue;
			}

			$new_source[] = $component;
		}

		if ( ! $moved_component ) {
			return new \WP_Error( 'nexa_pro_core_missing_component', \__( 'The requested component could not be found.', 'nexa-pro-core' ) );
		}

		$target_ids = wp_list_pluck( $target_components, 'instance_id' );

		if ( in_array( $moved_component['instance_id'], $target_ids, true ) ) {
			$moved_component['instance_id'] = Sanitizer::generate_unique_instance_id( $moved_component['component_type'], $target_ids );
		}

		if ( ! empty( $moved_component['navigation']['anchor_id'] ) ) {
			$moved_component['navigation']['anchor_id'] = self::get_unique_anchor_id( $moved_component['navigation']['anchor_id'], $target_components );
		}

		$moved_component['order'] = count( $target_components );
		$target_components[]      = $moved_component;

		$new_source        = Sanitizer::sanitize_component_collection( $new_source );
		$target_components = Sanitizer::sanitize_component_collection( $target_components );

		if ( \is_wp_error( $new_source ) ) {
			return $new_source;
		}

		if ( \is_wp_error( $target_components ) ) {
			return $target_components;
		}

		$original_target = \get_post_meta( $target_page_id, NEXA_PRO_CORE_PAGE_META_KEY, true );
		$target_written  = self::write_components( $target_page_id, $target_components );

		if ( \is_wp_error( $target_written ) ) {
			return $target_written;
		}

		$source_written = self::write_components( $source_page_id, $new_source );

		if ( \is_wp_error( $source_written ) ) {
			self::write_components( $target_page_id, is_array( $original_target ) ? $original_target : array() );
			return $source_written;
		}

		return $moved_component;
	}

	/**
	 * Count page components.
	 *
	 * @param int $page_id Page ID.
	 * @return int|\WP_Error
	 */
	public static function count_page_components( $page_id ) {
		$components = self::get_page_components( $page_id );

		if ( \is_wp_error( $components ) ) {
			return $components;
		}

		return count( $components );
	}

	/**
	 * Write components to post meta.
	 *
	 * @param int   $page_id    Page ID.
	 * @param array $components Components.
	 * @return true|\WP_Error
	 */
	private static function write_components( $page_id, array $components ) {
		$result = \update_post_meta( $page_id, NEXA_PRO_CORE_PAGE_META_KEY, $components );
		$stored = \get_post_meta( $page_id, NEXA_PRO_CORE_PAGE_META_KEY, true );

		if ( false === $result && $stored != $components ) {
			return new \WP_Error( 'nexa_pro_core_storage_failed', \__( 'Component storage could not be updated.', 'nexa-pro-core' ) );
		}

		return true;
	}

	/**
	 * Generate a unique anchor ID for a page component collection.
	 *
	 * @param string $anchor_id  Requested anchor.
	 * @param array  $components Existing components.
	 * @return string
	 */
	private static function get_unique_anchor_id( $anchor_id, array $components ) {
		$anchor_id = Sanitizer::sanitize_anchor_id( $anchor_id );

		if ( '' === $anchor_id ) {
			return '';
		}

		$existing = array();

		foreach ( $components as $component ) {
			if ( ! empty( $component['navigation']['anchor_id'] ) ) {
				$existing[] = $component['navigation']['anchor_id'];
			}
		}

		if ( ! in_array( $anchor_id, $existing, true ) ) {
			return $anchor_id;
		}

		$base = $anchor_id . '-copy';

		if ( ! in_array( $base, $existing, true ) ) {
			return $base;
		}

		for ( $index = 2; $index <= 50; $index++ ) {
			$candidate = $base . '-' . $index;

			if ( ! in_array( $candidate, $existing, true ) ) {
				return $candidate;
			}
		}

		return $base . '-' . substr( md5( wp_json_encode( $existing ) . microtime( true ) ), 0, 6 );
	}
}
