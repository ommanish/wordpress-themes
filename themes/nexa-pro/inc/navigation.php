<?php
/**
 * Nexa Pro Core navigation integration.
 *
 * @package Nexa_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the primary navigation menu.
 *
 * @return void
 */
function nexa_pro_render_primary_navigation_menu() {
	$source = nexa_pro_get_primary_navigation_source();

	if ( 'wordpress' === $source || ! function_exists( 'nexa_pro_core_get_generated_navigation' ) ) {
		nexa_pro_render_wordpress_primary_menu();
		return;
	}

	$generated = nexa_pro_core_get_generated_navigation();

	if ( empty( $generated ) ) {
		nexa_pro_render_wordpress_primary_menu();
		return;
	}

	if ( 'generated' === $source ) {
		nexa_pro_render_navigation_tree( $generated, nexa_pro_generated_menu_classes() );
		return;
	}

	nexa_pro_render_hybrid_primary_menu( $generated );
}

/**
 * Render the original WordPress Primary Menu.
 *
 * @return void
 */
function nexa_pro_render_wordpress_primary_menu() {
	wp_nav_menu(
		array(
			'theme_location' => 'primary',
			'menu_id'        => 'primary-menu',
			'menu_class'     => 'primary-menu',
			'container'      => false,
			'fallback_cb'    => 'nexa_pro_primary_menu_fallback',
		)
	);
}

/**
 * Get the effective primary navigation source.
 *
 * @return string
 */
function nexa_pro_get_primary_navigation_source() {
	if ( ! function_exists( 'nexa_pro_core_get_navigation_settings' ) ) {
		return 'wordpress';
	}

	$settings = nexa_pro_core_get_navigation_settings();
	$source   = isset( $settings['navigation_source'] ) ? sanitize_key( $settings['navigation_source'] ) : 'wordpress';

	return in_array( $source, array( 'wordpress', 'generated', 'hybrid' ), true ) ? $source : 'wordpress';
}

/**
 * Get generated menu classes.
 *
 * @return array
 */
function nexa_pro_generated_menu_classes() {
	$classes = array( 'primary-menu', 'nexa-pro-generated-menu' );

	if ( function_exists( 'nexa_pro_core_get_navigation_settings' ) ) {
		$settings = nexa_pro_core_get_navigation_settings();

		if ( ! empty( $settings['generated_mobile_visibility'] ) && 'hidden' === $settings['generated_mobile_visibility'] ) {
			$classes[] = 'nexa-pro-generated-menu--mobile-hidden';
		}
	}

	return $classes;
}

/**
 * Render hybrid menu.
 *
 * @param array $generated Generated items.
 * @return void
 */
function nexa_pro_render_hybrid_primary_menu( array $generated ) {
	$wp_items = nexa_pro_get_wordpress_primary_menu_tree();

	if ( empty( $wp_items ) ) {
		nexa_pro_render_navigation_tree( $generated, nexa_pro_generated_menu_classes() );
		return;
	}

	$settings    = function_exists( 'nexa_pro_core_get_navigation_settings' ) ? nexa_pro_core_get_navigation_settings() : array();
	$position    = ! empty( $settings['hybrid_insertion_position'] ) ? sanitize_key( $settings['hybrid_insertion_position'] ) : 'after';
	$placeholder = ! empty( $settings['hybrid_placeholder_url'] ) ? (string) $settings['hybrid_placeholder_url'] : '#nexa-generated-navigation';
	$generated   = nexa_pro_filter_duplicate_navigation_urls( $generated, $wp_items, $placeholder );
	$items       = array();

	if ( 'before' === $position ) {
		$items = array_merge( $generated, $wp_items );
	} elseif ( 'replace-placeholder' === $position ) {
		$replaced = false;

		foreach ( $wp_items as $item ) {
			if ( isset( $item['url'] ) && $placeholder === $item['url'] ) {
				$items    = array_merge( $items, $generated );
				$replaced = true;
				continue;
			}

			$items[] = $item;
		}

		if ( ! $replaced ) {
			$items = array_merge( $items, $generated );
		}
	} else {
		$items = array_merge( $wp_items, $generated );
	}

	nexa_pro_render_navigation_tree( $items, array( 'primary-menu', 'nexa-pro-hybrid-menu' ) );
}

/**
 * Get assigned WordPress primary menu as a normalized tree.
 *
 * @return array
 */
function nexa_pro_get_wordpress_primary_menu_tree() {
	$locations = get_nav_menu_locations();
	$menu_id   = isset( $locations['primary'] ) ? absint( $locations['primary'] ) : 0;

	if ( ! $menu_id ) {
		return array();
	}

	$items = wp_get_nav_menu_items( $menu_id );

	if ( empty( $items ) || ! is_array( $items ) ) {
		return array();
	}

	$normalized = array();
	$children   = array();

	foreach ( $items as $item ) {
		$menu_item = array(
			'id'                => 'menu-item-' . absint( $item->ID ),
			'type'              => 'wordpress',
			'label'             => $item->title,
			'url'               => $item->url,
			'page_id'           => 'post_type' === $item->type && 'page' === $item->object ? absint( $item->object_id ) : 0,
			'anchor_id'         => '',
			'children'          => array(),
			'highlight_as_cta'  => false,
			'mobile_visibility' => 'all',
		);

		$parent_id = absint( $item->menu_item_parent );

		if ( $parent_id ) {
			if ( empty( $children[ $parent_id ] ) ) {
				$children[ $parent_id ] = array();
			}

			$children[ $parent_id ][] = $menu_item;
		} else {
			$normalized[ absint( $item->ID ) ] = $menu_item;
		}
	}

	foreach ( $normalized as $item_id => $item ) {
		if ( ! empty( $children[ $item_id ] ) ) {
			$normalized[ $item_id ]['children'] = $children[ $item_id ];
		}
	}

	return array_values( $normalized );
}

/**
 * Remove generated URLs already covered by WordPress menu items.
 *
 * @param array  $generated   Generated items.
 * @param array  $wp_items    WordPress items.
 * @param string $placeholder Placeholder URL.
 * @return array
 */
function nexa_pro_filter_duplicate_navigation_urls( array $generated, array $wp_items, $placeholder ) {
	$urls = nexa_pro_collect_navigation_urls( $wp_items, $placeholder );

	return array_values(
		array_filter(
			$generated,
			function ( $item ) use ( $urls ) {
				$url = isset( $item['url'] ) ? (string) $item['url'] : '';

				return '' === $url || ! in_array( $url, $urls, true );
			}
		)
	);
}

/**
 * Collect navigation URLs.
 *
 * @param array  $items       Items.
 * @param string $placeholder Placeholder URL.
 * @return array
 */
function nexa_pro_collect_navigation_urls( array $items, $placeholder ) {
	$urls = array();

	foreach ( $items as $item ) {
		$url = isset( $item['url'] ) ? (string) $item['url'] : '';

		if ( '' !== $url && $placeholder !== $url ) {
			$urls[] = $url;
		}

		if ( ! empty( $item['children'] ) && is_array( $item['children'] ) ) {
			$urls = array_merge( $urls, nexa_pro_collect_navigation_urls( $item['children'], $placeholder ) );
		}
	}

	return array_values( array_unique( $urls ) );
}

/**
 * Render normalized navigation tree.
 *
 * @param array $items   Items.
 * @param array $classes Menu classes.
 * @return void
 */
function nexa_pro_render_navigation_tree( array $items, array $classes ) {
	$classes = array_map( 'sanitize_html_class', $classes );

	printf(
		'<ul id="nexa-pro-generated-primary-menu" class="%s">',
		esc_attr( implode( ' ', array_filter( $classes ) ) )
	);

	foreach ( $items as $item ) {
		nexa_pro_render_navigation_tree_item( $item );
	}

	echo '</ul>';
}

/**
 * Render one navigation item.
 *
 * @param array $item Item.
 * @return void
 */
function nexa_pro_render_navigation_tree_item( array $item ) {
	$label = isset( $item['label'] ) ? (string) $item['label'] : '';
	$url   = isset( $item['url'] ) ? (string) $item['url'] : '';

	if ( '' === $label || '' === $url || '' === esc_url( $url ) ) {
		return;
	}

	$classes = array( 'menu-item' );
	$visibility = ! empty( $item['mobile_visibility'] ) ? sanitize_key( $item['mobile_visibility'] ) : 'all';

	if ( 'hidden' === $visibility ) {
		return;
	}

	if ( ! empty( $item['highlight_as_cta'] ) ) {
		$classes[] = 'menu-item--cta';
	}

	if ( 'desktop' === $visibility ) {
		$classes[] = 'menu-item--desktop-only';
	} elseif ( 'mobile' === $visibility ) {
		$classes[] = 'menu-item--mobile-only';
	}

	if ( ! empty( $item['children'] ) ) {
		$classes[] = 'menu-item-has-children';
	}

	echo '<li class="' . esc_attr( implode( ' ', array_map( 'sanitize_html_class', $classes ) ) ) . '">';
	echo '<a href="' . esc_url( $url ) . '"' . nexa_pro_get_navigation_current_attribute( $item ) . '>' . esc_html( $label ) . '</a>';

	if ( ! empty( $item['children'] ) && is_array( $item['children'] ) ) {
		echo '<ul class="sub-menu">';

		foreach ( $item['children'] as $child ) {
			nexa_pro_render_navigation_tree_item( $child );
		}

		echo '</ul>';
	}

	echo '</li>';
}

/**
 * Get aria-current attribute for a generated item.
 *
 * @param array $item Item.
 * @return string
 */
function nexa_pro_get_navigation_current_attribute( array $item ) {
	$page_id = isset( $item['page_id'] ) ? absint( $item['page_id'] ) : 0;

	if ( $page_id && is_page( $page_id ) && empty( $item['anchor_id'] ) ) {
		return ' aria-current="page"';
	}

	return '';
}
