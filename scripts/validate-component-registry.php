#!/usr/bin/env php
<?php
/**
 * Validate the Nexa Pro component presentation registry without WordPress.
 *
 * @package Nexa_Pro
 */

define( 'ABSPATH', __DIR__ . '/../' );
define( 'NEXA_PRO_DIR', realpath( __DIR__ . '/../themes/nexa-pro' ) );

$GLOBALS['nexa_pro_test_filters']       = array();
$GLOBALS['nexa_pro_test_template_calls'] = array();
$failures                              = array();

if ( ! function_exists( '__' ) ) {
	/**
	 * Translation shim.
	 *
	 * @param string $text Text.
	 * @return string
	 */
	function __( $text ) {
		return $text;
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
	/**
	 * sanitize_key shim.
	 *
	 * @param mixed $key Raw key.
	 * @return string
	 */
	function sanitize_key( $key ) {
		$key = strtolower( (string) $key );

		return preg_replace( '/[^a-z0-9_\-]/', '', $key );
	}
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	/**
	 * sanitize_text_field shim.
	 *
	 * @param mixed $text Raw text.
	 * @return string
	 */
	function sanitize_text_field( $text ) {
		$text = wp_strip_all_tags( (string) $text );

		return trim( preg_replace( '/[\r\n\t ]+/', ' ', $text ) );
	}
}

if ( ! function_exists( 'wp_strip_all_tags' ) ) {
	/**
	 * wp_strip_all_tags shim.
	 *
	 * @param string $text Text.
	 * @return string
	 */
	function wp_strip_all_tags( $text ) {
		return strip_tags( $text );
	}
}

if ( ! function_exists( 'sanitize_html_class' ) ) {
	/**
	 * sanitize_html_class shim.
	 *
	 * @param mixed $class Raw class.
	 * @return string
	 */
	function sanitize_html_class( $class ) {
		return preg_replace( '/[^A-Za-z0-9_\-]/', '', (string) $class );
	}
}

if ( ! function_exists( 'wp_parse_args' ) ) {
	/**
	 * wp_parse_args shim.
	 *
	 * @param mixed $args     Args.
	 * @param array $defaults Defaults.
	 * @return array
	 */
	function wp_parse_args( $args, $defaults = array() ) {
		if ( ! is_array( $args ) ) {
			$args = array();
		}

		return array_merge( $defaults, $args );
	}
}

if ( ! function_exists( 'add_filter' ) ) {
	/**
	 * add_filter shim.
	 *
	 * @param string   $hook          Hook name.
	 * @param callable $callback      Callback.
	 * @param int      $priority      Priority.
	 * @param int      $accepted_args Accepted args.
	 * @return true
	 */
	function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		$GLOBALS['nexa_pro_test_filters'][ $hook ][ $priority ][] = array(
			'callback'      => $callback,
			'accepted_args' => $accepted_args,
		);

		return true;
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	/**
	 * apply_filters shim.
	 *
	 * @param string $hook  Hook name.
	 * @param mixed  $value Value.
	 * @param mixed  ...$args Extra args.
	 * @return mixed
	 */
	function apply_filters( $hook, $value, ...$args ) {
		if ( empty( $GLOBALS['nexa_pro_test_filters'][ $hook ] ) ) {
			return $value;
		}

		ksort( $GLOBALS['nexa_pro_test_filters'][ $hook ] );

		foreach ( $GLOBALS['nexa_pro_test_filters'][ $hook ] as $callbacks ) {
			foreach ( $callbacks as $registered ) {
				$callback_args = array_slice( array_merge( array( $value ), $args ), 0, $registered['accepted_args'] );
				$value         = call_user_func_array( $registered['callback'], $callback_args );
			}
		}

		return $value;
	}
}

if ( ! function_exists( 'do_action' ) ) {
	/**
	 * do_action shim.
	 */
	function do_action() {
		return null;
	}
}

if ( ! function_exists( 'get_template_part' ) ) {
	/**
	 * get_template_part shim.
	 *
	 * @param string $slug Template slug.
	 * @param string $name Optional name.
	 * @param array  $args Template args.
	 * @return bool
	 */
	function get_template_part( $slug, $name = null, $args = array() ) {
		$GLOBALS['nexa_pro_test_template_calls'][] = array(
			'slug' => $slug,
			'name' => $name,
			'args' => $args,
		);

		return true;
	}
}

/**
 * Add a validation failure when a condition is false.
 *
 * @param bool   $condition Condition.
 * @param string $message   Failure message.
 * @return void
 */
function nexa_pro_component_registry_assert( $condition, $message ) {
	global $failures;

	if ( ! $condition ) {
		$failures[] = $message;
	}
}

require NEXA_PRO_DIR . '/inc/components/registry.php';
require NEXA_PRO_DIR . '/inc/components/render.php';
require NEXA_PRO_DIR . '/inc/components/compatibility.php';

$required_types = array(
	'hero',
	'about',
	'services',
	'features',
	'process',
	'why',
	'portfolio',
	'testimonials',
	'team',
	'faq',
	'cta',
	'contact',
);

$registry = nexa_pro_get_component_registry();

nexa_pro_component_registry_assert( array_keys( $registry ) === nexa_pro_get_component_types(), 'Component type helper should match registry keys.' );

foreach ( $required_types as $type ) {
	nexa_pro_component_registry_assert( isset( $registry[ $type ] ), "Missing component type: {$type}" );
	nexa_pro_component_registry_assert( nexa_pro_component_type_exists( $type ), "Component type should exist: {$type}" );

	$template = nexa_pro_get_component_template( $type );
	nexa_pro_component_registry_assert( '' !== $template, "Component template missing: {$type}" );
	nexa_pro_component_registry_assert( file_exists( NEXA_PRO_DIR . '/' . $template ), "Component template file missing: {$type}" );
	nexa_pro_component_registry_assert( 0 === strpos( $template, 'template-parts/sections/' ), "Component template must stay in sections directory: {$type}" );
}

$unique_types = array_unique( nexa_pro_get_component_types() );
nexa_pro_component_registry_assert( count( $unique_types ) === count( nexa_pro_get_component_types() ), 'Component types must be unique.' );

$anchors = array();
foreach ( $required_types as $type ) {
	$anchor = nexa_pro_get_component_default_anchor( $type );
	nexa_pro_component_registry_assert( '' !== $anchor, "Default anchor missing: {$type}" );
	nexa_pro_component_registry_assert( ! in_array( $anchor, $anchors, true ), "Duplicate default anchor: {$anchor}" );
	$anchors[] = $anchor;
}

$legacy_map = nexa_pro_get_legacy_component_map();

foreach ( $required_types as $type ) {
	nexa_pro_component_registry_assert( isset( $legacy_map[ $type ] ), "Legacy map missing section: {$type}" );
	nexa_pro_component_registry_assert( $type === $legacy_map[ $type ], "Legacy map should point {$type} to {$type}." );
	nexa_pro_component_registry_assert( $type === nexa_pro_get_component_type_for_legacy_section( $type ), "Legacy lookup failed for: {$type}" );
	nexa_pro_component_registry_assert( $type === nexa_pro_get_legacy_section_for_component_type( $type ), "Component inverse lookup failed for: {$type}" );
}

add_filter(
	'nexa_pro_component_registry',
	function ( $registry ) {
		$registry['smoke-test'] = array(
			'type'                      => 'smoke-test',
			'label'                     => 'Smoke Test',
			'description'               => 'Validation-only component.',
			'template'                  => 'template-parts/sections/cta.php',
			'legacy_section_key'        => 'smoke-test',
			'default_anchor'            => 'smoke-test',
			'supported_layouts'         => array( 'default' ),
			'supported_design_features' => array( 'solid-background' ),
			'navigation'                => array(
				'supported'     => true,
				'default_label' => 'Smoke Test',
			),
			'repeatable'                => array(
				'supported' => true,
			),
			'reusable'                  => array(
				'eligible' => true,
			),
		);

		return $registry;
	}
);

nexa_pro_component_registry_assert( nexa_pro_component_type_exists( 'smoke-test' ), 'Registry filter should allow a valid extension component.' );

nexa_pro_component_registry_assert( ! nexa_pro_component_type_exists( 'missing-component' ), 'Unknown component types should fail safely.' );
nexa_pro_component_registry_assert( false === nexa_pro_render_component( 'missing-component' ), 'Unknown component render should return false.' );

add_filter(
	'nexa_pro_component_registry',
	function ( $registry ) {
		$registry['unsafe-template'] = array(
			'type'                      => 'unsafe-template',
			'label'                     => 'Unsafe Template',
			'description'               => 'Validation-only unsafe template component.',
			'template'                  => '../../wp-config.php',
			'legacy_section_key'        => 'unsafe-template',
			'default_anchor'            => 'unsafe-template',
			'supported_layouts'         => array( 'default' ),
			'supported_design_features' => array(),
			'navigation'                => array(
				'supported' => false,
			),
			'repeatable'                => false,
			'reusable'                  => false,
		);

		return $registry;
	}
);

$GLOBALS['nexa_pro_test_template_calls'] = array();
nexa_pro_component_registry_assert( ! nexa_pro_component_type_exists( 'unsafe-template' ), 'Unsafe template component should not be registered.' );
nexa_pro_component_registry_assert( false === nexa_pro_render_component( 'unsafe-template' ), 'Unsafe template component should not render.' );
nexa_pro_component_registry_assert( empty( $GLOBALS['nexa_pro_test_template_calls'] ), 'Unsafe template component should not call get_template_part().' );

if ( $failures ) {
	foreach ( $failures as $failure ) {
		fwrite( STDERR, "FAIL: {$failure}\n" );
	}

	exit( 1 );
}

echo "PASS: Nexa Pro component registry validation completed successfully.\n";
