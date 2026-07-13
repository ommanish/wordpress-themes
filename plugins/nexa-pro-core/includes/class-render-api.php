<?php
/**
 * Read-side helpers for future component rendering integration.
 *
 * @package Nexa_Pro_Core
 */

namespace Nexa_Pro_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render API service.
 */
final class Render_API {
	/**
	 * Get enabled, resolved components for a page.
	 *
	 * The read API resolves enabled local and linked instances without mutating
	 * page storage.
	 *
	 * @param int $page_id Page ID.
	 * @return array|\WP_Error
	 */
	public static function get_renderable_page_components( $page_id ) {
		$components = Storage::get_page_components( $page_id );

		if ( \is_wp_error( $components ) ) {
			return $components;
		}

		$renderable = array();

		foreach ( $components as $component ) {
			if ( empty( $component['enabled'] ) ) {
				continue;
			}

			$resolved = Reusable_Components::resolve_component_instance( $component );

			if ( \is_wp_error( $resolved ) ) {
				continue;
			}

			$renderable[] = $resolved;
		}

		return Sanitizer::normalize_order( $renderable );
	}

	/**
	 * Determine whether a page has stored builder components.
	 *
	 * @param int $page_id Page ID.
	 * @return bool
	 */
	public static function has_builder_components( $page_id ) {
		$components = Storage::get_page_components( $page_id );

		if ( \is_wp_error( $components ) ) {
			return false;
		}

		return ! empty( $components );
	}
}
