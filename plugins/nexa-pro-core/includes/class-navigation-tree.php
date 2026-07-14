<?php
/**
 * Generated navigation tree helpers.
 *
 * @package Nexa_Pro_Core
 */

namespace Nexa_Pro_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds normalized navigation trees from pages and component metadata.
 */
final class Navigation_Tree {
	/**
	 * Get generated navigation.
	 *
	 * @param array $settings Optional settings override.
	 * @return array
	 */
	public static function get_generated_navigation( $settings = array() ) {
		$settings = Navigation_Settings::sanitize_settings(
			is_array( $settings ) && $settings ? $settings : Navigation_Settings::get_settings(),
			Navigation_Settings::defaults()
		);

		$page_ids = $settings['generated_page_ids'];

		if ( empty( $page_ids ) && $settings['generated_primary_page_id'] ) {
			$page_ids = array( absint( $settings['generated_primary_page_id'] ) );
		}

		$page_ids = self::filter_public_page_ids( $page_ids, '1' === $settings['include_disabled_pages'] );

		if ( empty( $page_ids ) ) {
			return array();
		}

		$include_sections = '1' === $settings['include_page_sections'];
		$is_single_page   = 1 === count( $page_ids ) && absint( $settings['generated_primary_page_id'] ) === absint( $page_ids[0] );
		$items            = array();

		if ( $is_single_page && $include_sections ) {
			$items = self::get_page_navigation_items(
				$page_ids[0],
				array(
					'same_page' => true,
				)
			);

			if ( $items ) {
				return $items;
			}
		}

		foreach ( $page_ids as $page_id ) {
			$page = \get_post( $page_id );

			if ( ! $page ) {
				continue;
			}

			$url      = \get_permalink( $page_id );
			$children = $include_sections ? self::get_page_navigation_items( $page_id ) : array();

			$items[] = array(
				'id'                => 'page-' . absint( $page_id ),
				'type'              => 'page',
				'label'             => \get_the_title( $page_id ),
				'url'               => $url ? $url : '',
				'page_id'           => absint( $page_id ),
				'instance_id'       => '',
				'anchor_id'         => '',
				'children'          => $children,
				'highlight_as_cta'  => false,
				'mobile_visibility' => 'all',
			);
		}

		return $items;
	}

	/**
	 * Get generated navigation items for one page.
	 *
	 * @param int   $page_id Page ID.
	 * @param array $args    Args.
	 * @return array
	 */
	public static function get_page_navigation_items( $page_id, $args = array() ) {
		$args = array_merge(
			array(
				'same_page' => false,
			),
			is_array( $args ) ? $args : array()
		);

		$page_id    = absint( $page_id );
		$components = Render_API::get_renderable_page_components( $page_id );

		if ( \is_wp_error( $components ) || empty( $components ) ) {
			return array();
		}

		$items         = array();
		$by_instance   = array();
		$registry      = self::get_registry();
		$page_permalink = \get_permalink( $page_id );

		$sequence = 0;

		foreach ( $components as $component ) {
			$navigation = isset( $component['navigation'] ) && is_array( $component['navigation'] ) ? $component['navigation'] : array();

			if ( empty( $navigation['show_in_navigation'] ) ) {
				continue;
			}

			$anchor_id = ! empty( $navigation['anchor_id'] ) ? Sanitizer::sanitize_anchor_id( $navigation['anchor_id'] ) : '';

			if ( '' === $anchor_id ) {
				$anchor_id = self::default_anchor_for_component( $component );
			}

			if ( '' === $anchor_id ) {
				continue;
			}

			$label = ! empty( $navigation['navigation_label'] ) ? \sanitize_text_field( $navigation['navigation_label'] ) : '';

			if ( '' === $label ) {
				$label = self::default_label_for_component( $component, $registry );
			}

			$url = $args['same_page'] ? '#' . $anchor_id : \trailingslashit( $page_permalink ) . '#' . $anchor_id;

			$item = array(
				'id'                => 'component-' . $page_id . '-' . $anchor_id,
				'type'              => 'component',
				'label'             => $label,
				'url'               => $url,
				'page_id'           => $page_id,
				'instance_id'       => isset( $component['instance_id'] ) ? $component['instance_id'] : '',
				'anchor_id'         => $anchor_id,
				'parent_instance_id' => ! empty( $navigation['parent_instance_id'] ) ? $navigation['parent_instance_id'] : '',
				'order'             => isset( $component['order'] ) ? absint( $component['order'] ) : 0,
				'order_override'    => isset( $navigation['order_override'] ) && null !== $navigation['order_override'] ? absint( $navigation['order_override'] ) : null,
				'sequence'          => $sequence,
				'children'          => array(),
				'highlight_as_cta'  => ! empty( $navigation['highlight_as_cta'] ),
				'mobile_visibility' => ! empty( $navigation['mobile_visibility'] ) ? \sanitize_key( $navigation['mobile_visibility'] ) : 'all',
			);

			$items[]                              = $item;
			$by_instance[ $item['instance_id'] ] = $item;
			$sequence++;
		}

		if ( empty( $items ) ) {
			return array();
		}

		usort( $items, array( __CLASS__, 'sort_items' ) );

		return self::build_component_hierarchy( $items, $by_instance );
	}

	/**
	 * Validate navigation tree integrity.
	 *
	 * @param array $items Tree items.
	 * @return array
	 */
	public static function validate_navigation_tree( array $items ) {
		$seen_ids  = array();
		$seen_urls = array();
		$warnings  = array();

		self::walk_items(
			$items,
			function ( $item ) use ( &$seen_ids, &$seen_urls, &$warnings ) {
				$id  = isset( $item['id'] ) ? (string) $item['id'] : '';
				$url = isset( $item['url'] ) ? (string) $item['url'] : '';

				if ( '' === $id ) {
					$warnings[] = \__( 'A generated navigation item is missing an ID.', 'nexa-pro-core' );
				} elseif ( in_array( $id, $seen_ids, true ) ) {
					$warnings[] = \__( 'Generated navigation contains a duplicate item ID.', 'nexa-pro-core' );
				}

				if ( '' !== $url && in_array( $url, $seen_urls, true ) ) {
					$warnings[] = \__( 'Generated navigation contains a duplicate URL.', 'nexa-pro-core' );
				}

				$seen_ids[]  = $id;
				$seen_urls[] = $url;
			}
		);

		return array_values( array_unique( $warnings ) );
	}

	/**
	 * Filter IDs to public pages.
	 *
	 * @param array $page_ids                 Page IDs.
	 * @param bool  $include_unpublished_pages Include unpublished pages.
	 * @return array
	 */
	private static function filter_public_page_ids( array $page_ids, $include_unpublished_pages = false ) {
		$normalized = array();

		foreach ( $page_ids as $page_id ) {
			$page_id = absint( $page_id );
			$page    = $page_id ? \get_post( $page_id ) : null;

			if ( ! $page || 'page' !== $page->post_type ) {
				continue;
			}

			if ( ! $include_unpublished_pages && 'publish' !== $page->post_status ) {
				continue;
			}

			if ( ! in_array( $page_id, $normalized, true ) ) {
				$normalized[] = $page_id;
			}
		}

		return $normalized;
	}

	/**
	 * Build component hierarchy.
	 *
	 * @param array $items       Items.
	 * @param array $by_instance Items by instance ID.
	 * @return array
	 */
	private static function build_component_hierarchy( array $items, array $by_instance ) {
		$children = array();
		$roots    = array();

		foreach ( $items as $item ) {
			$parent = ! empty( $item['parent_instance_id'] ) ? $item['parent_instance_id'] : '';

			if ( '' === $parent || empty( $by_instance[ $parent ] ) || $parent === $item['instance_id'] || self::would_cycle( $item['instance_id'], $parent, $items ) ) {
				$roots[] = $item;
				continue;
			}

			if ( empty( $children[ $parent ] ) ) {
				$children[ $parent ] = array();
			}

			$children[ $parent ][] = $item;
		}

		foreach ( $roots as $index => $root ) {
			$roots[ $index ] = self::attach_children( $root, $children );
		}

		return $roots;
	}

	/**
	 * Attach children recursively.
	 *
	 * @param array $item     Item.
	 * @param array $children Children by parent.
	 * @return array
	 */
	private static function attach_children( array $item, array $children ) {
		$instance_id = isset( $item['instance_id'] ) ? $item['instance_id'] : '';

		if ( $instance_id && ! empty( $children[ $instance_id ] ) ) {
			$item['children'] = array();

			foreach ( $children[ $instance_id ] as $child ) {
				$item['children'][] = self::attach_children( $child, $children );
			}
		}

		return $item;
	}

	/**
	 * Detect a parent cycle.
	 *
	 * @param string $instance_id Instance ID.
	 * @param string $parent_id   Parent ID.
	 * @param array  $items       Items.
	 * @return bool
	 */
	private static function would_cycle( $instance_id, $parent_id, array $items ) {
		$parents = array();

		foreach ( $items as $item ) {
			if ( ! empty( $item['instance_id'] ) ) {
				$parents[ $item['instance_id'] ] = ! empty( $item['parent_instance_id'] ) ? $item['parent_instance_id'] : '';
			}
		}

		$seen = array();

		while ( $parent_id ) {
			if ( $parent_id === $instance_id || in_array( $parent_id, $seen, true ) ) {
				return true;
			}

			$seen[]    = $parent_id;
			$parent_id = isset( $parents[ $parent_id ] ) ? $parents[ $parent_id ] : '';
		}

		return false;
	}

	/**
	 * Sort items by override, then stored order.
	 *
	 * @param array $a First item.
	 * @param array $b Second item.
	 * @return int
	 */
	private static function sort_items( array $a, array $b ) {
		$a_has_override = null !== $a['order_override'];
		$b_has_override = null !== $b['order_override'];
		$a_order        = $a_has_override ? $a['order_override'] : $a['order'];
		$b_order        = $b_has_override ? $b['order_override'] : $b['order'];

		if ( $a_order === $b_order ) {
			if ( $a_has_override !== $b_has_override ) {
				return $a_has_override ? -1 : 1;
			}

			$a_sequence = isset( $a['sequence'] ) ? absint( $a['sequence'] ) : 0;
			$b_sequence = isset( $b['sequence'] ) ? absint( $b['sequence'] ) : 0;

			if ( $a_sequence === $b_sequence ) {
				return 0;
			}

			return $a_sequence < $b_sequence ? -1 : 1;
		}

		return $a_order < $b_order ? -1 : 1;
	}

	/**
	 * Walk tree items.
	 *
	 * @param array    $items    Items.
	 * @param callable $callback Callback.
	 * @return void
	 */
	private static function walk_items( array $items, $callback ) {
		foreach ( $items as $item ) {
			call_user_func( $callback, $item );

			if ( ! empty( $item['children'] ) && is_array( $item['children'] ) ) {
				self::walk_items( $item['children'], $callback );
			}
		}
	}

	/**
	 * Get registry definitions.
	 *
	 * @return array
	 */
	private static function get_registry() {
		if ( function_exists( 'nexa_pro_get_component_registry' ) ) {
			$registry = \nexa_pro_get_component_registry();

			return is_array( $registry ) ? $registry : array();
		}

		return array();
	}

	/**
	 * Get a component label fallback.
	 *
	 * @param array $component Component.
	 * @param array $registry  Registry.
	 * @return string
	 */
	private static function default_label_for_component( array $component, array $registry ) {
		$type = isset( $component['component_type'] ) ? \sanitize_key( $component['component_type'] ) : '';

		if ( $type && ! empty( $registry[ $type ]['navigation']['default_label'] ) ) {
			return \sanitize_text_field( $registry[ $type ]['navigation']['default_label'] );
		}

		if ( ! empty( $component['admin_title'] ) ) {
			return \sanitize_text_field( $component['admin_title'] );
		}

		return Sanitizer::default_admin_title( $type );
	}

	/**
	 * Get a component anchor fallback.
	 *
	 * @param array $component Component.
	 * @return string
	 */
	private static function default_anchor_for_component( array $component ) {
		$type = isset( $component['component_type'] ) ? \sanitize_key( $component['component_type'] ) : '';

		if ( function_exists( 'nexa_pro_get_component_default_anchor' ) && $type ) {
			return Sanitizer::sanitize_anchor_id( \nexa_pro_get_component_default_anchor( $type ) );
		}

		return $type;
	}
}
