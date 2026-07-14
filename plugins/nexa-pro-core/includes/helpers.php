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
use Nexa_Pro_Core\Compatibility_Mode;
use Nexa_Pro_Core\Migration;
use Nexa_Pro_Core\Navigation_Settings;
use Nexa_Pro_Core\Navigation_Tree;
use Nexa_Pro_Core\Render_API;
use Nexa_Pro_Core\Reusable_Components;
use Nexa_Pro_Core\Schema;
use Nexa_Pro_Core\Sanitizer;
use Nexa_Pro_Core\Storage;
use Nexa_Pro_Core\Transfer;

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
 * Get the current compatibility mode.
 *
 * @return string
 */
function nexa_pro_core_get_compatibility_mode() {
	return Compatibility_Mode::get_mode();
}

/**
 * Save the current compatibility mode.
 *
 * @param string $mode Mode.
 * @return string
 */
function nexa_pro_core_set_compatibility_mode( $mode ) {
	return Compatibility_Mode::set_mode( $mode );
}

/**
 * Determine whether the theme should render builder components for a page.
 *
 * @param int $page_id Page ID.
 * @return bool
 */
function nexa_pro_core_should_render_builder_page( $page_id ) {
	return Compatibility_Mode::should_render_builder_page( $page_id );
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

/**
 * Get migration state.
 *
 * @return array
 */
function nexa_pro_core_get_migration_state() {
	return Migration::get_state();
}

/**
 * Detect available migration source data.
 *
 * @param int $target_page_id Target page.
 * @return array
 */
function nexa_pro_core_detect_migration( $target_page_id = 0 ) {
	return Migration::detect( $target_page_id );
}

/**
 * Preview migration.
 *
 * @param int    $target_page_id Target page.
 * @param string $mode           Mode.
 * @return array
 */
function nexa_pro_core_preview_migration( $target_page_id = 0, $mode = 'merge' ) {
	return Migration::preview( $target_page_id, $mode );
}

/**
 * Apply migration.
 *
 * @param int    $target_page_id Target page.
 * @param string $mode           Mode.
 * @param bool   $switch_mode    Switch to builder mode.
 * @param array  $args           Args.
 * @return array|WP_Error
 */
function nexa_pro_core_apply_migration( $target_page_id, $mode = 'merge', $switch_mode = false, $args = array() ) {
	return Migration::apply( $target_page_id, $mode, $switch_mode, $args );
}

/**
 * Roll back migration.
 *
 * @param array $args Args.
 * @return array|WP_Error
 */
function nexa_pro_core_rollback_migration( $args = array() ) {
	return Migration::rollback( $args );
}

/**
 * Build a component platform export.
 *
 * @param string $scope Scope.
 * @param array  $args  Args.
 * @return array
 */
function nexa_pro_core_build_export( $scope = 'full-site', $args = array() ) {
	return Transfer::build_export( $scope, is_array( $args ) ? $args : array() );
}

/**
 * Preview a component platform import.
 *
 * @param string|array $payload Payload.
 * @return array|WP_Error
 */
function nexa_pro_core_preview_import( $payload ) {
	return Transfer::preview_import( $payload );
}

/**
 * Apply a component platform import.
 *
 * @param array  $payload       Payload.
 * @param string $conflict_mode Conflict mode.
 * @param array  $page_map      Page map.
 * @param array  $args          Args.
 * @return array|WP_Error
 */
function nexa_pro_core_apply_import( array $payload, $conflict_mode = 'skip', array $page_map = array(), $args = array() ) {
	return Transfer::apply_import( $payload, $conflict_mode, $page_map, $args );
}
