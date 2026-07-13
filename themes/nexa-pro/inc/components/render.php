<?php
/**
 * Component rendering helpers.
 *
 * @package Nexa_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize component class names.
 *
 * @param mixed $classes Raw classes.
 * @return array
 */
function nexa_pro_sanitize_component_classes( $classes ) {
	if ( ! is_array( $classes ) ) {
		$classes = preg_split( '/\s+/', (string) $classes );
	}

	$sanitized = array();

	foreach ( $classes as $class ) {
		$class = sanitize_html_class( $class );

		if ( '' === $class || in_array( $class, $sanitized, true ) ) {
			continue;
		}

		$sanitized[] = $class;
	}

	return $sanitized;
}

/**
 * Normalize custom properties supplied to component templates.
 *
 * @param mixed $properties Raw style properties.
 * @return array
 */
function nexa_pro_normalize_component_style_properties( $properties ) {
	if ( ! is_array( $properties ) ) {
		return array();
	}

	$normalized = array();

	foreach ( $properties as $property => $value ) {
		if ( ! is_string( $property ) || 0 !== strpos( $property, '--nexa-pro-' ) || ! is_scalar( $value ) ) {
			continue;
		}

		$value = trim( (string) $value );

		if ( '' === $value || false !== strpos( $value, ';' ) || false !== strpos( $value, '{' ) || false !== strpos( $value, '}' ) ) {
			continue;
		}

		$normalized[ $property ] = $value;
	}

	return $normalized;
}

/**
 * Convert a validated component template path to a get_template_part slug.
 *
 * @param string $template Component template path.
 * @return string
 */
function nexa_pro_get_component_template_slug( $template ) {
	$template = nexa_pro_normalize_component_template( $template );

	if ( '' === $template ) {
		return '';
	}

	return preg_replace( '/\.php$/', '', $template );
}

/**
 * Render a registered presentation component.
 *
 * @param string $type    Component type.
 * @param array  $data    Component render data.
 * @param array  $context Component render context.
 * @return bool
 */
function nexa_pro_render_component( $type, array $data = array(), array $context = array() ) {
	$type       = sanitize_key( $type );
	$definition = nexa_pro_get_component_definition( $type );

	if ( empty( $definition ) ) {
		return false;
	}

	$template_slug = nexa_pro_get_component_template_slug( $definition['template'] );

	if ( '' === $template_slug ) {
		return false;
	}

	$context = wp_parse_args(
		$context,
		array(
			'source'             => 'component',
			'legacy_section_key' => $definition['legacy_section_key'],
		)
	);

	/**
	 * Filter component data before template rendering.
	 *
	 * @param array  $data       Component render data.
	 * @param string $type       Component type.
	 * @param array  $context    Render context.
	 * @param array  $definition Component definition.
	 */
	$data = apply_filters( 'nexa_pro_component_render_data', $data, $type, $context, $definition );

	if ( ! is_array( $data ) ) {
		return false;
	}

	$classes = array(
		'nexa-pro-component',
		'nexa-pro-component--' . $type,
	);

	/**
	 * Filter advisory component classes passed to templates.
	 *
	 * These classes are not wrapped by the registry renderer by default. They
	 * are provided as presentation metadata for future template integrations.
	 *
	 * @param array  $classes    Component classes.
	 * @param string $type       Component type.
	 * @param array  $data       Component render data.
	 * @param array  $context    Render context.
	 * @param array  $definition Component definition.
	 */
	$classes = apply_filters( 'nexa_pro_component_classes', $classes, $type, $data, $context, $definition );
	$classes = nexa_pro_sanitize_component_classes( $classes );

	/**
	 * Filter advisory component custom properties passed to templates.
	 *
	 * @param array  $style_properties Component custom properties.
	 * @param string $type             Component type.
	 * @param array  $data             Component render data.
	 * @param array  $context          Render context.
	 * @param array  $definition       Component definition.
	 */
	$style_properties = apply_filters( 'nexa_pro_component_style_properties', array(), $type, $data, $context, $definition );
	$style_properties = nexa_pro_normalize_component_style_properties( $style_properties );

	$template_args = array(
		'section'   => $data,
		'component' => array(
			'type'             => $type,
			'definition'       => $definition,
			'context'          => $context,
			'classes'          => $classes,
			'style_properties' => $style_properties,
		),
	);

	/**
	 * Fires immediately before a registered component template renders.
	 *
	 * @param string $type       Component type.
	 * @param array  $data       Component render data.
	 * @param array  $context    Render context.
	 * @param array  $definition Component definition.
	 */
	do_action( 'nexa_pro_before_component', $type, $data, $context, $definition );

	get_template_part( $template_slug, null, $template_args );

	/**
	 * Fires immediately after a registered component template renders.
	 *
	 * @param string $type       Component type.
	 * @param array  $data       Component render data.
	 * @param array  $context    Render context.
	 * @param array  $definition Component definition.
	 */
	do_action( 'nexa_pro_after_component', $type, $data, $context, $definition );

	return true;
}
