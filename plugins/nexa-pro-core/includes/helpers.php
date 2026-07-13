<?php
/**
 * Public helper functions for Nexa Pro Core.
 *
 * @package Nexa_Pro_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Nexa_Pro_Core\Capabilities;
use Nexa_Pro_Core\Navigation_Settings;
use Nexa_Pro_Core\Navigation_Tree;
use Nexa_Pro_Core\Render_API;
use Nexa_Pro_Core\Reusable_Components;
use Nexa_Pro_Core\Schema;
use Nexa_Pro_Core\Sanitizer;
use Nexa_Pro_Core\Storage;

/**
 * Get page components.
 *
 * @param int $page_id Page ID.
 * @return array|WP_Error
 */
function nexa_pro_core_get_page_components( $page_id ) {
	return Storage::get_page_components( $page_id );
}

/**
 * Save page components.
 *
 * @param int   $page_id    Page ID.
 * @param array $components Components.
 * @param array $args       Operation args.
 * @return array|WP_Error
 */
function nexa_pro_core_save_page_components( $page_id, array $components, $args = array() ) {
	return Storage::save_page_components( $page_id, $components, $args );
}

/**
 * Add a page component.
 *
 * @param int   $page_id   Page ID.
 * @param array $component Component.
 * @param array $args      Operation args.
 * @return array|WP_Error
 */
function nexa_pro_core_add_page_component( $page_id, array $component, $args = array() ) {
	return Storage::add_page_component( $page_id, $component, $args );
}

/**
 * Update a page component.
 *
 * @param int    $page_id     Page ID.
 * @param string $instance_id Instance ID.
 * @param array  $changes     Changes.
 * @param array  $args        Operation args.
 * @return array|WP_Error
 */
function nexa_pro_core_update_page_component( $page_id, $instance_id, array $changes, $args = array() ) {
	return Storage::update_page_component( $page_id, $instance_id, $changes, $args );
}

/**
 * Duplicate a page component.
 *
 * @param int    $page_id     Page ID.
 * @param string $instance_id Instance ID.
 * @param array  $args        Operation args.
 * @return array|WP_Error
 */
function nexa_pro_core_duplicate_page_component( $page_id, $instance_id, $args = array() ) {
	return Storage::duplicate_page_component( $page_id, $instance_id, $args );
}

/**
 * Remove a page component.
 *
 * @param int    $page_id     Page ID.
 * @param string $instance_id Instance ID.
 * @param array  $args        Operation args.
 * @return array|WP_Error
 */
function nexa_pro_core_remove_page_component( $page_id, $instance_id, $args = array() ) {
	return Storage::remove_page_component( $page_id, $instance_id, $args );
}

/**
 * Reorder page components.
 *
 * @param int   $page_id     Page ID.
 * @param array $ordered_ids Ordered IDs.
 * @param array $args        Operation args.
 * @return array|WP_Error
 */
function nexa_pro_core_reorder_page_components( $page_id, array $ordered_ids, $args = array() ) {
	return Storage::reorder_page_components( $page_id, $ordered_ids, $args );
}

/**
 * Move a page component.
 *
 * @param int    $source_page_id Source page ID.
 * @param int    $target_page_id Target page ID.
 * @param string $instance_id    Instance ID.
 * @param array  $args           Operation args.
 * @return array|WP_Error
 */
function nexa_pro_core_move_page_component( $source_page_id, $target_page_id, $instance_id, $args = array() ) {
	return Storage::move_page_component( $source_page_id, $target_page_id, $instance_id, $args );
}

/**
 * Count page components.
 *
 * @param int $page_id Page ID.
 * @return int|WP_Error
 */
function nexa_pro_core_count_page_components( $page_id ) {
	return Storage::count_page_components( $page_id );
}

/**
 * Get enabled, resolved page components for future rendering integration.
 *
 * @param int $page_id Page ID.
 * @return array|WP_Error
 */
function nexa_pro_core_get_renderable_page_components( $page_id ) {
	return Render_API::get_renderable_page_components( $page_id );
}

/**
 * Determine whether a page has builder components.
 *
 * @param int $page_id Page ID.
 * @return bool
 */
function nexa_pro_core_has_builder_components( $page_id ) {
	return Render_API::has_builder_components( $page_id );
}

/**
 * Create a reusable component.
 *
 * @param array $component Component.
 * @param array $args      Operation args.
 * @return int|WP_Error
 */
function nexa_pro_core_create_reusable_component( array $component, $args = array() ) {
	return Reusable_Components::create_reusable_component( $component, $args );
}

/**
 * Get a reusable component.
 *
 * @param int $post_id Reusable component ID.
 * @return array|WP_Error
 */
function nexa_pro_core_get_reusable_component( $post_id ) {
	return Reusable_Components::get_reusable_component( $post_id );
}

/**
 * Update a reusable component.
 *
 * @param int   $post_id Reusable component ID.
 * @param array $changes Changes.
 * @param array $args    Operation args.
 * @return array|WP_Error
 */
function nexa_pro_core_update_reusable_component( $post_id, array $changes, $args = array() ) {
	return Reusable_Components::update_reusable_component( $post_id, $changes, $args );
}

/**
 * Duplicate a reusable component.
 *
 * @param int   $post_id Reusable component ID.
 * @param array $args    Operation args.
 * @return int|WP_Error
 */
function nexa_pro_core_duplicate_reusable_component( $post_id, $args = array() ) {
	return Reusable_Components::duplicate_reusable_component( $post_id, $args );
}

/**
 * Archive a reusable component.
 *
 * @param int   $post_id Reusable component ID.
 * @param array $args    Operation args.
 * @return true|WP_Error
 */
function nexa_pro_core_archive_reusable_component( $post_id, $args = array() ) {
	return Reusable_Components::archive_reusable_component( $post_id, $args );
}

/**
 * Restore a reusable component.
 *
 * @param int   $post_id Reusable component ID.
 * @param array $args    Operation args.
 * @return true|WP_Error
 */
function nexa_pro_core_restore_reusable_component( $post_id, $args = array() ) {
	return Reusable_Components::restore_reusable_component( $post_id, $args );
}

/**
 * Delete a reusable component.
 *
 * @param int   $post_id Reusable component ID.
 * @param array $args    Operation args.
 * @return true|WP_Error
 */
function nexa_pro_core_delete_reusable_component( $post_id, $args = array() ) {
	return Reusable_Components::delete_reusable_component( $post_id, $args );
}

/**
 * Detach all linked instances for a reusable component.
 *
 * @param int   $post_id Reusable component ID.
 * @param array $args    Operation args.
 * @return int|WP_Error
 */
function nexa_pro_core_detach_all_reusable_instances( $post_id, $args = array() ) {
	return Reusable_Components::detach_all_linked_instances( $post_id, $args );
}

/**
 * Resolve a component instance.
 *
 * @param array $instance Component instance.
 * @return array|WP_Error
 */
function nexa_pro_core_resolve_component_instance( array $instance ) {
	return Reusable_Components::resolve_component_instance( $instance );
}

/**
 * Detach a reusable component instance.
 *
 * @param int    $page_id     Page ID.
 * @param string $instance_id Instance ID.
 * @param array  $args        Operation args.
 * @return array|WP_Error
 */
function nexa_pro_core_detach_reusable_component( $page_id, $instance_id, $args = array() ) {
	return Reusable_Components::detach_reusable_component( $page_id, $instance_id, $args );
}

/**
 * Get reusable usage count.
 *
 * @param int $post_id Reusable component ID.
 * @return int
 */
function nexa_pro_core_get_reusable_usage_count( $post_id ) {
	return Reusable_Components::get_reusable_usage_count( $post_id );
}

/**
 * List linked page instances.
 *
 * @param int $post_id Reusable component ID.
 * @return array
 */
function nexa_pro_core_list_linked_page_instances( $post_id ) {
	return Reusable_Components::list_linked_page_instances( $post_id );
}

/**
 * Get plugin capabilities.
 *
 * @return array
 */
function nexa_pro_core_get_capabilities() {
	return Capabilities::get_capabilities();
}

/**
 * Get current schema version.
 *
 * @return int
 */
function nexa_pro_core_get_current_schema_version() {
	return Schema::current_schema_version();
}

/**
 * Get stored schema version.
 *
 * @return int
 */
function nexa_pro_core_get_stored_schema_version() {
	return Schema::stored_schema_version();
}

/**
 * Validate whether a component type is supported.
 *
 * @param string $type Component type.
 * @return bool
 */
function nexa_pro_core_component_type_is_allowed( $type ) {
	return Sanitizer::component_type_is_allowed( $type );
}

/**
 * Get plugin-owned navigation settings.
 *
 * @return array
 */
function nexa_pro_core_get_navigation_settings() {
	return Navigation_Settings::get_settings();
}

/**
 * Get plugin-owned navigation defaults.
 *
 * @return array
 */
function nexa_pro_core_get_navigation_defaults() {
	return Navigation_Settings::defaults();
}

/**
 * Get generated navigation tree.
 *
 * @param array $settings Optional settings.
 * @return array
 */
function nexa_pro_core_get_generated_navigation( $settings = array() ) {
	return Navigation_Tree::get_generated_navigation( $settings );
}

/**
 * Get generated navigation items for a page.
 *
 * @param int   $page_id Page ID.
 * @param array $args    Arguments.
 * @return array
 */
function nexa_pro_core_get_page_navigation_items( $page_id, $args = array() ) {
	return Navigation_Tree::get_page_navigation_items( $page_id, $args );
}

/**
 * Validate a generated navigation tree.
 *
 * @param array $items Items.
 * @return array
 */
function nexa_pro_core_validate_navigation_tree( array $items ) {
	return Navigation_Tree::validate_navigation_tree( $items );
}
