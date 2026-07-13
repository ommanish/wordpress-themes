<?php
/**
 * Legacy homepage section compatibility helpers.
 *
 * @package Nexa_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the mapping between legacy homepage sections and component types.
 *
 * @return array
 */
function nexa_pro_get_legacy_component_map() {
	$map = array(
		'hero'         => 'hero',
		'about'        => 'about',
		'services'     => 'services',
		'features'     => 'features',
		'process'      => 'process',
		'why'          => 'why',
		'portfolio'    => 'portfolio',
		'testimonials' => 'testimonials',
		'team'         => 'team',
		'faq'          => 'faq',
		'cta'          => 'cta',
		'contact'      => 'contact',
	);

	/**
	 * Filter the legacy homepage section to component type map.
	 *
	 * @param array $map Legacy section keys mapped to component types.
	 */
	$map = apply_filters( 'nexa_pro_legacy_component_map', $map );

	if ( ! is_array( $map ) ) {
		return array();
	}

	$normalized = array();

	foreach ( $map as $legacy_section => $component_type ) {
		$legacy_section = sanitize_key( $legacy_section );
		$component_type = sanitize_key( $component_type );

		if ( '' === $legacy_section || '' === $component_type || ! nexa_pro_component_type_exists( $component_type ) ) {
			continue;
		}

		$normalized[ $legacy_section ] = $component_type;
	}

	return $normalized;
}

/**
 * Get the registered component type for a legacy homepage section.
 *
 * @param string $legacy_section Legacy section key.
 * @return string
 */
function nexa_pro_get_component_type_for_legacy_section( $legacy_section ) {
	$legacy_section = sanitize_key( $legacy_section );

	if ( '' === $legacy_section ) {
		return '';
	}

	$map = nexa_pro_get_legacy_component_map();

	return isset( $map[ $legacy_section ] ) ? $map[ $legacy_section ] : '';
}

/**
 * Get the legacy homepage section key for a registered component type.
 *
 * @param string $component_type Component type.
 * @return string
 */
function nexa_pro_get_legacy_section_for_component_type( $component_type ) {
	$component_type = sanitize_key( $component_type );

	if ( '' === $component_type ) {
		return '';
	}

	foreach ( nexa_pro_get_legacy_component_map() as $legacy_section => $mapped_type ) {
		if ( $component_type === $mapped_type ) {
			return $legacy_section;
		}
	}

	return '';
}
