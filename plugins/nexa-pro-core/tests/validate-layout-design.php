#!/usr/bin/env php
<?php
/**
 * Validate Phase 6E layout and design behavior.
 *
 * @package Nexa_Pro_Core
 */

define( 'ABSPATH', __DIR__ . '/../../../' );
define( 'NEXA_PRO_DIR', realpath( __DIR__ . '/../../../themes/nexa-pro' ) );
define( 'NEXA_PRO_CORE_SCHEMA_VERSION', 1 );

$plugin_dir = realpath( __DIR__ . '/..' );
$failures   = array();

if ( ! function_exists( '__' ) ) {
	function __( $text ) {
		return $text;
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( $hook, $value ) {
		return $value;
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
	function sanitize_key( $key ) {
		return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) );
	}
}

if ( ! function_exists( 'sanitize_html_class' ) ) {
	function sanitize_html_class( $class ) {
		return preg_replace( '/[^A-Za-z0-9_\-]/', '', (string) $class );
	}
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $text ) {
		return trim( preg_replace( '/[\r\n\t ]+/', ' ', wp_strip_all_tags( (string) $text ) ) );
	}
}

if ( ! function_exists( 'wp_strip_all_tags' ) ) {
	function wp_strip_all_tags( $text ) {
		return strip_tags( (string) $text );
	}
}

if ( ! function_exists( 'wp_kses_post' ) ) {
	function wp_kses_post( $text ) {
		return strip_tags( (string) $text, '<a><br><em><strong><p><ul><ol><li>' );
	}
}

if ( ! function_exists( 'esc_url_raw' ) ) {
	function esc_url_raw( $url ) {
		$url = trim( (string) $url );

		if ( '' === $url || 1 === preg_match( '/^\s*javascript:/i', $url ) ) {
			return '';
		}

		return filter_var( $url, FILTER_VALIDATE_URL ) || 0 === strpos( $url, '#' ) ? $url : '';
	}
}

if ( ! function_exists( 'absint' ) ) {
	function absint( $value ) {
		return abs( (int) $value );
	}
}

if ( ! function_exists( 'sanitize_title' ) ) {
	function sanitize_title( $title ) {
		$title = strtolower( preg_replace( '/[^a-zA-Z0-9]+/', '-', (string) $title ) );

		return trim( $title, '-' );
	}
}

if ( ! function_exists( 'sanitize_hex_color' ) ) {
	function sanitize_hex_color( $color ) {
		$color = trim( (string) $color );

		return preg_match( '/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $color ) ? strtolower( $color ) : null;
	}
}

if ( ! function_exists( 'wp_attachment_is_image' ) ) {
	function wp_attachment_is_image( $attachment_id ) {
		return in_array( absint( $attachment_id ), array( 101, 202 ), true );
	}
}

if ( ! function_exists( 'nexa_pro_is_valid_image_attachment_id' ) ) {
	function nexa_pro_is_valid_image_attachment_id( $attachment_id ) {
		return wp_attachment_is_image( $attachment_id );
	}
}

if ( ! function_exists( 'wp_json_encode' ) ) {
	function wp_json_encode( $data ) {
		return json_encode( $data );
	}
}

if ( ! function_exists( 'is_wp_error' ) ) {
	function is_wp_error( $thing ) {
		return $thing instanceof WP_Error;
	}
}

if ( ! function_exists( 'wp_generate_password' ) ) {
	function wp_generate_password() {
		return substr( md5( microtime( true ) . random_int( 1, 999999 ) ), 0, 16 );
	}
}

/**
 * Add a failure when a condition is false.
 *
 * @param bool   $condition Condition.
 * @param string $message   Message.
 * @return void
 */
function nexa_pro_core_layout_design_assert( $condition, $message ) {
	global $failures;

	if ( ! $condition ) {
		$failures[] = $message;
	}
}

require NEXA_PRO_DIR . '/inc/components/registry.php';
require NEXA_PRO_DIR . '/inc/components/render.php';
require $plugin_dir . '/includes/class-sanitizer.php';

use Nexa_Pro_Core\Sanitizer;

$expected_layouts = array(
	'hero'         => array( 'centered', 'split-left', 'split-right', 'background-image', 'minimal', 'full-height' ),
	'about'        => array( 'text-only', 'image-left', 'image-right', 'statistics', 'split-content' ),
	'services'     => array( 'card-grid', 'icon-grid', 'alternating-rows', 'image-cards', 'compact-list' ),
	'features'     => array( 'icon-grid', 'bento-grid', 'alternating', 'centered-grid', 'checklist' ),
	'process'      => array( 'horizontal-steps', 'vertical-timeline', 'numbered-cards', 'connected-steps' ),
	'why'          => array( 'benefit-cards', 'icon-list', 'split-media', 'statistics' ),
	'portfolio'    => array( 'grid', 'masonry-grid', 'featured-project', 'case-study-cards' ),
	'testimonials' => array( 'grid', 'featured-quote', 'static-slider', 'logo-and-quote' ),
	'team'         => array( 'profile-grid', 'compact-list', 'leadership-feature', 'image-cards' ),
	'faq'          => array( 'accordion', 'two-column', 'categorized-list' ),
	'cta'          => array( 'centered', 'split', 'banner', 'image-background', 'compact' ),
	'contact'      => array( 'details-only', 'form-and-details', 'split-map-placeholder', 'cards' ),
);

foreach ( $expected_layouts as $type => $layouts ) {
	$definition = nexa_pro_get_component_definition( $type );

	nexa_pro_core_layout_design_assert( $definition, "Component definition missing: {$type}" );
	nexa_pro_core_layout_design_assert( $layouts === $definition['supported_layouts'], "Layout list mismatch for {$type}." );
	nexa_pro_core_layout_design_assert( in_array( $definition['default_layout'], $layouts, true ), "Default layout is not supported for {$type}." );
	nexa_pro_core_layout_design_assert( $definition['default_layout'] === nexa_pro_get_component_layout_or_default( $type, 'unknown-layout' ), "Unknown layout fallback failed for {$type}." );
}

$component = Sanitizer::sanitize_component_instance(
	array(
		'component_type' => 'services',
		'layout'         => 'bad-layout',
		'content'        => array(
			'heading'          => 'Services',
			'desktop_image_id' => '101',
			'mobile_image_id'  => '909',
		),
		'design'         => array(
			'preset'              => 'brand',
			'section_spacing'     => 'compact',
			'card_style'          => 'elevated',
			'radius'              => 'pill',
			'shadow'              => 'strong',
			'image_style'         => 'circle',
			'button_style'        => 'ghost',
			'column_count'        => '9',
			'background_type'     => 'gradient',
			'gradient_direction'  => 'to-right',
			'background_color'    => '#123456',
			'gradient_start'      => '#111111',
			'gradient_end'        => '#eeeeee',
			'background_image_id' => '202',
			'overlay_enabled'     => '1',
			'overlay_color'       => '#000000',
			'overlay_opacity'     => '55',
			'raw_css'             => 'body{display:none}',
		),
		'navigation'     => array(
			'anchor_id' => 'stable-anchor',
		),
	)
);

nexa_pro_core_layout_design_assert( ! is_wp_error( $component ), 'Valid component should sanitize.' );
nexa_pro_core_layout_design_assert( 'card-grid' === $component['layout'], 'Invalid submitted layout should fall back to the component default.' );
nexa_pro_core_layout_design_assert( 101 === $component['content']['desktop_image_id'], 'Valid image attachment IDs should be preserved in content.' );
nexa_pro_core_layout_design_assert( 0 === $component['content']['mobile_image_id'], 'Invalid image attachment IDs should be rejected in content.' );
nexa_pro_core_layout_design_assert( 'brand' === $component['design']['preset'], 'Valid presets should be stored as keys.' );
nexa_pro_core_layout_design_assert( 6 === $component['design']['column_count'], 'Column counts should be capped at six.' );
nexa_pro_core_layout_design_assert( ! isset( $component['design']['raw_css'] ), 'Arbitrary CSS should be discarded.' );
nexa_pro_core_layout_design_assert( 202 === $component['design']['background_image_id'], 'Valid background image IDs should be preserved.' );
nexa_pro_core_layout_design_assert( 'stable-anchor' === $component['navigation']['anchor_id'], 'Layout changes should not rewrite navigation anchors.' );
nexa_pro_core_layout_design_assert( 1 === $component['schema_version'], 'Schema version should remain 1.' );

$invalid_design = Sanitizer::sanitize_design(
	array(
		'preset'              => 'not-real',
		'overlay_opacity'     => '120',
		'background_image_id' => '303',
		'shadow'              => '0 0 20px red',
	)
);

nexa_pro_core_layout_design_assert( array() === $invalid_design, 'Invalid preset, opacity, attachment, and raw shadow values should be rejected.' );

$resolved = nexa_pro_resolve_component_design(
	'services',
	array(
		'preset'          => 'dark',
		'section_spacing' => 'compact',
		'card_style'      => 'flat',
		'overlay_opacity' => 25,
	)
);

nexa_pro_core_layout_design_assert( 'dark' === $resolved['preset'], 'Preset selection should resolve first.' );
nexa_pro_core_layout_design_assert( 'compact' === $resolved['section_spacing'], 'Explicit component override should beat preset spacing.' );
nexa_pro_core_layout_design_assert( 'flat' === $resolved['card_style'], 'Explicit card style should beat preset card style.' );
nexa_pro_core_layout_design_assert( 25.0 === $resolved['overlay_opacity'], 'Explicit opacity should be preserved when valid.' );

$classes = nexa_pro_get_component_render_classes(
	'services',
	'card-grid',
	array(
		'preset'            => 'dark',
		'section_spacing'   => 'compact',
		'content_alignment' => 'center',
		'card_style'        => 'elevated',
		'radius'            => 'large',
		'shadow'            => 'medium',
		'image_style'       => 'rounded',
		'button_style'      => 'outline',
		'column_count'      => 4,
		'background_type'   => 'solid',
		'text_theme'        => 'light',
	),
	array(
		'custom_css_class' => 'local-component safe-extra',
	)
);

foreach ( array( 'nexa-layout--card-grid', 'nexa-preset--dark', 'nexa-section-spacing--compact', 'nexa-card-style--elevated', 'nexa-radius--large', 'nexa-shadow--medium', 'nexa-image-style--rounded', 'nexa-button-style--outline', 'nexa-column-count--4', 'homepage-section--design-solid', 'has-nexa-pro-light-text', 'local-component' ) as $expected_class ) {
	nexa_pro_core_layout_design_assert( in_array( $expected_class, $classes, true ), "Expected render class missing: {$expected_class}" );
}

$faq_template = file_get_contents( NEXA_PRO_DIR . '/template-parts/sections/faq.php' );
nexa_pro_core_layout_design_assert( false !== strpos( $faq_template, 'aria-expanded="false"' ), 'FAQ accordion should expose aria-expanded on button controls.' );
nexa_pro_core_layout_design_assert( false !== strpos( $faq_template, 'aria-controls="' ), 'FAQ accordion should connect controls to panels.' );
nexa_pro_core_layout_design_assert( false !== strpos( $faq_template, '$section_id . \'-faq-panel-\'' ), 'FAQ panel IDs should be stable and section-scoped.' );

$hero_template = file_get_contents( NEXA_PRO_DIR . '/template-parts/sections/hero.php' );
nexa_pro_core_layout_design_assert( false !== strpos( $hero_template, '$heading_level' ), 'Hero template should support renderer-controlled heading levels.' );

$storage_source  = file_get_contents( $plugin_dir . '/includes/class-storage.php' );
$reusable_source = file_get_contents( $plugin_dir . '/includes/class-reusable-components.php' );
nexa_pro_core_layout_design_assert( false !== strpos( $storage_source, "array( 'content', 'design', 'navigation', 'advanced' )" ) && false !== strpos( $storage_source, 'array_merge( $component[ $group ], $changes[ $group ] )' ), 'Page component updates should merge nested groups to preserve hidden layout fields.' );
nexa_pro_core_layout_design_assert( false !== strpos( $reusable_source, "array( 'content', 'design', 'navigation', 'advanced' )" ) && false !== strpos( $reusable_source, 'array_merge( $existing[ $group ], $changes[ $group ] )' ), 'Reusable updates should merge nested groups to preserve hidden layout fields.' );

if ( $failures ) {
	foreach ( $failures as $failure ) {
		fwrite( STDERR, "FAIL: {$failure}\n" );
	}

	exit( 1 );
}

echo "PASS: Nexa Pro Core layout/design validation completed successfully.\n";
