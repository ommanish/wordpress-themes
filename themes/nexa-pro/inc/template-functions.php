<?php
/**
 * Template helper functions.
 *
 * @package Nexa_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add contextual classes to the body element.
 *
 * @param array $classes Body classes.
 * @return array
 */
function nexa_pro_body_classes( $classes ) {
	if ( ! is_singular() ) {
		$classes[] = 'nexa-pro-list-view';
	}

	if ( is_singular() && ! has_post_thumbnail() ) {
		$classes[] = 'nexa-pro-no-featured-image';
	}

	if ( is_front_page() ) {
		$classes[] = 'nexa-pro-front-page';
	}

	return $classes;
}
add_filter( 'body_class', 'nexa_pro_body_classes' );

/**
 * Determine whether comments should be displayed for the current post.
 *
 * @return bool
 */
function nexa_pro_should_show_comments() {
	return comments_open() || get_comments_number();
}

