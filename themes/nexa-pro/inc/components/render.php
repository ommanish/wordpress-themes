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
 * Sanitize a hex color where WordPress helpers may not be loaded.
 *
 * @param mixed $color Raw color.
 * @return string
 */
function nexa_pro_component_sanitize_hex_color( $color ) {
	if ( function_exists( 'sanitize_hex_color' ) ) {
		$sanitized = sanitize_hex_color( $color );

		return $sanitized ? $sanitized : '';
	}

	$color = trim( (string) $color );

	return preg_match( '/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $color ) ? strtolower( $color ) : '';
}

/**
 * Get safe global component design defaults.
 *
 * @return array
 */
function nexa_pro_get_component_global_design_defaults() {
	return array(
		'preset'             => 'inherit',
		'background_type'    => 'inherit',
		'container_width'    => 'standard',
		'content_width'      => 'standard',
		'content_alignment'  => 'left',
		'media_position'     => 'right',
		'section_spacing'    => 'standard',
		'content_spacing'    => 'standard',
		'item_spacing'       => 'standard',
		'card_density'       => 'comfortable',
		'text_theme'         => 'automatic',
		'card_style'         => 'bordered',
		'radius'             => 'medium',
		'shadow'             => 'none',
		'image_style'        => 'soft',
		'button_style'       => 'primary',
		'column_count'       => 3,
		'gradient_direction' => 'to-bottom',
		'overlay_enabled'    => false,
		'overlay_opacity'    => 45,
	);
}

/**
 * Resolve design tokens for a component.
 *
 * @param string $type   Component type.
 * @param array  $design Stored design values.
 * @return array
 */
function nexa_pro_resolve_component_design( $type, array $design ) {
	$tokens   = function_exists( 'nexa_pro_get_component_design_tokens' ) ? nexa_pro_get_component_design_tokens() : array();
	$presets  = function_exists( 'nexa_pro_get_component_design_presets' ) ? nexa_pro_get_component_design_presets() : array();
	$preset   = ! empty( $design['preset'] ) ? sanitize_key( $design['preset'] ) : 'inherit';
	$resolved = nexa_pro_get_component_global_design_defaults();

	if ( isset( $presets[ $preset ]['design'] ) && is_array( $presets[ $preset ]['design'] ) ) {
		$resolved = array_merge( $resolved, $presets[ $preset ]['design'] );
	}

	$resolved['preset'] = isset( $presets[ $preset ] ) ? $preset : 'inherit';

	foreach ( $design as $key => $value ) {
		$key = sanitize_key( $key );

		if ( in_array( (string) $value, array( '', 'inherit', 'default' ), true ) ) {
			continue;
		}

		if ( isset( $tokens[ $key ] ) && is_scalar( $value ) ) {
			$value = sanitize_key( $value );

			if ( in_array( $value, $tokens[ $key ], true ) ) {
				$resolved[ $key ] = $value;
			}
		}
	}

	foreach ( array( 'background_color', 'gradient_start', 'gradient_end', 'overlay_color' ) as $color_key ) {
		if ( ! empty( $design[ $color_key ] ) ) {
			$color = nexa_pro_component_sanitize_hex_color( $design[ $color_key ] );

			if ( $color ) {
				$resolved[ $color_key ] = $color;
			}
		}
	}

	if ( ! empty( $design['background_image_id'] ) && function_exists( 'nexa_pro_is_valid_image_attachment_id' ) && nexa_pro_is_valid_image_attachment_id( absint( $design['background_image_id'] ) ) ) {
		$resolved['background_image_id'] = absint( $design['background_image_id'] );
	}

	if ( isset( $design['overlay_enabled'] ) ) {
		$resolved['overlay_enabled'] = (bool) $design['overlay_enabled'];
	}

	if ( isset( $design['overlay_opacity'] ) && is_numeric( $design['overlay_opacity'] ) ) {
		$resolved['overlay_opacity'] = max( 0, min( 100, (float) $design['overlay_opacity'] ) );
	}

	if ( isset( $design['column_count'] ) ) {
		$resolved['column_count'] = max( 1, min( 6, absint( $design['column_count'] ) ) );
	}

	/**
	 * Filter resolved component design tokens.
	 *
	 * @param array  $resolved Resolved tokens.
	 * @param string $type     Component type.
	 * @param array  $design   Raw stored design values.
	 */
	return apply_filters( 'nexa_pro_component_resolved_design', $resolved, sanitize_key( $type ), $design );
}

/**
 * Get safe component classes from layout and design tokens.
 *
 * @param string $type     Component type.
 * @param string $layout   Layout token.
 * @param array  $design   Resolved design values.
 * @param array  $advanced Advanced values.
 * @return array
 */
function nexa_pro_get_component_render_classes( $type, $layout, array $design, array $advanced = array() ) {
	$type    = sanitize_key( $type );
	$layout  = sanitize_key( $layout );
	$classes = array(
		'nexa-pro-component',
		'nexa-pro-component--' . $type,
		'nexa-layout--' . $layout,
	);

	foreach ( array( 'preset', 'container_width', 'section_spacing', 'content_alignment', 'content_width', 'content_spacing', 'card_style', 'radius', 'shadow', 'image_style', 'button_style', 'card_density', 'item_spacing', 'column_count' ) as $key ) {
		if ( empty( $design[ $key ] ) || in_array( $design[ $key ], array( 'inherit', 'automatic' ), true ) ) {
			continue;
		}

		$classes[] = 'nexa-' . str_replace( '_', '-', $key ) . '--' . sanitize_html_class( $design[ $key ] );
	}

	if ( ! empty( $design['background_type'] ) && 'inherit' !== $design['background_type'] ) {
		$classes[] = 'homepage-section--design-' . sanitize_html_class( $design['background_type'] );
	}

	if ( ! empty( $design['background_image_id'] ) ) {
		$classes[] = 'homepage-section--has-background-image';
	}

	if ( ! empty( $design['overlay_enabled'] ) ) {
		$classes[] = 'homepage-section--has-overlay';
	}

	if ( ! empty( $design['text_theme'] ) && ! in_array( $design['text_theme'], array( 'inherit', 'automatic' ), true ) ) {
		$classes[] = 'has-nexa-pro-' . sanitize_html_class( $design['text_theme'] ) . '-text';
	}

	if ( ! empty( $advanced['custom_css_class'] ) ) {
		$classes = array_merge( $classes, nexa_pro_sanitize_component_classes( $advanced['custom_css_class'] ) );
	}

	return nexa_pro_sanitize_component_classes( $classes );
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

	if ( ! empty( $data['component_classes'] ) && is_array( $data['component_classes'] ) ) {
		$classes = array_merge( $classes, $data['component_classes'] );
	}

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

/**
 * Determine whether a page has renderable Nexa Pro Core components.
 *
 * @param int $page_id Page ID.
 * @return bool
 */
function nexa_pro_has_builder_page_components( $page_id ) {
	return function_exists( 'nexa_pro_core_get_renderable_page_components' ) && ! empty( nexa_pro_get_builder_page_components( $page_id ) );
}

/**
 * Get renderable builder components for a page.
 *
 * @param int $page_id Page ID.
 * @return array
 */
function nexa_pro_get_builder_page_components( $page_id ) {
	if ( ! function_exists( 'nexa_pro_core_get_renderable_page_components' ) ) {
		return array();
	}

	$components = nexa_pro_core_get_renderable_page_components( absint( $page_id ) );

	return is_array( $components ) ? $components : array();
}

/**
 * Render builder components for a page.
 *
 * @param int $page_id Page ID.
 * @return bool
 */
function nexa_pro_render_builder_page_components( $page_id ) {
	$components = nexa_pro_get_builder_page_components( $page_id );

	if ( empty( $components ) ) {
		return false;
	}

	$has_hero_component   = false;
	$has_primary_heading = false;

	foreach ( $components as $component ) {
		$type = isset( $component['component_type'] ) ? sanitize_key( $component['component_type'] ) : '';

		if ( 'hero' === $type ) {
			$has_hero_component = true;
			break;
		}
	}

	if ( ! $has_hero_component && is_singular() ) {
		?>
		<header class="page-header nexa-pro-builder-page-header">
			<h1 class="page-title"><?php echo esc_html( get_the_title( absint( $page_id ) ) ); ?></h1>
		</header>
		<?php
		$has_primary_heading = true;
	}

	foreach ( $components as $component ) {
		$type = isset( $component['component_type'] ) ? sanitize_key( $component['component_type'] ) : '';

		if ( '' === $type ) {
			continue;
		}

		$data = nexa_pro_map_builder_component_to_section_data( $component );

		if ( 'hero' === $type ) {
			$data['heading_level'] = $has_primary_heading ? 'h2' : 'h1';
			$has_primary_heading   = true;
		}

		nexa_pro_render_component(
			$type,
			$data,
			array(
				'source'  => 'builder',
				'page_id' => absint( $page_id ),
			)
		);
	}

	return true;
}

/**
 * Map a stored component to existing section template data.
 *
 * @param array $component Component.
 * @return array
 */
function nexa_pro_map_builder_component_to_section_data( array $component ) {
	$type       = isset( $component['component_type'] ) ? sanitize_key( $component['component_type'] ) : '';
	$content    = isset( $component['content'] ) && is_array( $component['content'] ) ? $component['content'] : array();
	$raw_design = isset( $component['design'] ) && is_array( $component['design'] ) ? $component['design'] : array();
	$advanced   = isset( $component['advanced'] ) && is_array( $component['advanced'] ) ? $component['advanced'] : array();
	$navigation = isset( $component['navigation'] ) && is_array( $component['navigation'] ) ? $component['navigation'] : array();
	$anchor_id  = ! empty( $navigation['anchor_id'] ) ? sanitize_title( $navigation['anchor_id'] ) : $type;
	$heading    = ! empty( $content['heading'] ) ? wp_strip_all_tags( $content['heading'] ) : '';
	$text       = ! empty( $content['body'] ) ? wp_strip_all_tags( $content['body'] ) : '';
	$layout     = function_exists( 'nexa_pro_get_component_layout_or_default' ) ? nexa_pro_get_component_layout_or_default( $type, isset( $component['layout'] ) ? $component['layout'] : '' ) : sanitize_key( isset( $component['layout'] ) ? $component['layout'] : 'default' );
	$design     = nexa_pro_resolve_component_design( $type, $raw_design );

	if ( '' === $heading && ! empty( $component['admin_title'] ) ) {
		$heading = wp_strip_all_tags( $component['admin_title'] );
	}

	$desktop_image_id = ! empty( $content['desktop_image_id'] ) ? absint( $content['desktop_image_id'] ) : 0;
	$mobile_image_id  = ! empty( $content['mobile_image_id'] ) ? absint( $content['mobile_image_id'] ) : 0;

	if ( $desktop_image_id ) {
		$design['desktop_image_id'] = $desktop_image_id;

		if ( 'hero' === $type && 'background-image' === $layout && empty( $design['background_image_id'] ) ) {
			$design['background_image_id'] = $desktop_image_id;
		}
	}

	if ( $mobile_image_id ) {
		$design['mobile_image_id'] = $mobile_image_id;
	}

	$design['layout']                = $layout;
	$design['show_image_mobile']     = true;
	$design['image_position']        = ! empty( $design['media_position'] ) ? $design['media_position'] : 'right';
	$design['content_width']         = ! empty( $design['content_width'] ) ? $design['content_width'] : 'standard';
	$design['content_alignment']     = ! empty( $design['content_alignment'] ) ? $design['content_alignment'] : 'left';
	$design['image_object_position'] = 'center center';
	$button_style                    = ! empty( $design['button_style'] ) ? $design['button_style'] : 'primary';

	$data = array(
		'id'                => $anchor_id,
		'section_key'       => $type,
		'label'             => ! empty( $content['eyebrow'] ) ? wp_strip_all_tags( $content['eyebrow'] ) : '',
		'eyebrow'           => ! empty( $content['eyebrow'] ) ? wp_strip_all_tags( $content['eyebrow'] ) : '',
		'heading'           => $heading,
		'text'              => $text,
		'layout'            => $layout,
		'design'            => $design,
		'component_classes' => nexa_pro_get_component_render_classes( $type, $layout, $design, $advanced ),
		'items'             => ! empty( $content['items'] ) && is_array( $content['items'] ) ? $content['items'] : array(),
		'points'            => ! empty( $content['points'] ) && is_array( $content['points'] ) ? $content['points'] : array(),
		'action'            => array(
			'label' => ! empty( $content['primary_cta_label'] ) ? wp_strip_all_tags( $content['primary_cta_label'] ) : '',
			'url'   => ! empty( $content['primary_cta_url'] ) ? esc_url_raw( $content['primary_cta_url'] ) : '',
			'style' => in_array( $button_style, array( 'primary', 'secondary', 'outline', 'ghost', 'text' ), true ) ? $button_style : 'primary',
		),
	);

	if ( ! empty( $content['secondary_cta_label'] ) && ! empty( $content['secondary_cta_url'] ) ) {
		$data['actions'] = array(
			array(
				'label' => $data['action']['label'],
				'url'   => $data['action']['url'],
				'style' => $data['action']['style'],
			),
			array(
				'label' => wp_strip_all_tags( $content['secondary_cta_label'] ),
				'url'   => esc_url_raw( $content['secondary_cta_url'] ),
				'style' => 'secondary',
			),
		);
	} elseif ( ! empty( $data['action']['label'] ) && ! empty( $data['action']['url'] ) ) {
		$data['actions'] = array( $data['action'] );
	}

	if ( $desktop_image_id ) {
		$data['image'] = array(
			'id'  => $desktop_image_id,
			'alt' => $heading,
		);
	}

	return $data;
}
