<?php
/**
 * Front page template.
 *
 * @package Nexa_Pro
 */

get_header();

$nexa_pro_rendered_builder_components = function_exists( 'nexa_pro_render_builder_page_components' ) && nexa_pro_render_builder_page_components( get_queried_object_id() );

if ( ! $nexa_pro_rendered_builder_components ) {
	nexa_pro_render_homepage_sections();
}

get_footer();
