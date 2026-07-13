<?php
/**
 * Component presentation registry.
 *
 * @package Nexa_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the default component presentation registry.
 *
 * @return array
 */
function nexa_pro_get_default_component_registry() {
	$shared_section_design = array(
		'solid-background',
		'gradient-background',
		'image-background',
		'overlay',
		'text-theme',
	);

	$section_capabilities = array(
		'repeatable' => array(
			'supported'     => true,
			'content_owner' => 'plugin',
		),
		'reusable'   => array(
			'eligible'      => true,
			'storage_owner' => 'plugin',
		),
	);

	return array(
		'hero'         => array(
			'type'                      => 'hero',
			'label'                     => __( 'Hero', 'nexa-pro' ),
			'description'               => __( 'Primary page introduction with headline, calls to action, and optional media.', 'nexa-pro' ),
			'template'                  => 'template-parts/sections/hero.php',
			'legacy_section_key'        => 'hero',
			'default_anchor'            => 'hero',
			'supported_layouts'         => array( 'content-only', 'image-left', 'image-right', 'background-image' ),
			'supported_design_features' => array_merge( $shared_section_design, array( 'foreground-image', 'content-alignment', 'content-width' ) ),
			'navigation'                => array(
				'supported'     => false,
				'default_label' => __( 'Hero', 'nexa-pro' ),
			),
			'repeatable'                => array(
				'supported'     => false,
				'content_owner' => 'theme',
			),
			'reusable'                  => array(
				'eligible'      => false,
				'storage_owner' => 'plugin',
			),
		),
		'about'        => array_merge(
			$section_capabilities,
			array(
				'type'                      => 'about',
				'label'                     => __( 'About', 'nexa-pro' ),
				'description'               => __( 'Introductory narrative section with supporting points and optional image.', 'nexa-pro' ),
				'template'                  => 'template-parts/sections/about.php',
				'legacy_section_key'        => 'about',
				'default_anchor'            => 'about',
				'supported_layouts'         => array( 'default', 'image-left', 'image-right', 'text-only' ),
				'supported_design_features' => array_merge( $shared_section_design, array( 'foreground-image' ) ),
				'navigation'                => array(
					'supported'     => true,
					'default_label' => __( 'About', 'nexa-pro' ),
				),
			)
		),
		'services'     => array_merge(
			$section_capabilities,
			array(
				'type'                      => 'services',
				'label'                     => __( 'Services', 'nexa-pro' ),
				'description'               => __( 'Service cards with optional links and configurable section design.', 'nexa-pro' ),
				'template'                  => 'template-parts/sections/services.php',
				'legacy_section_key'        => 'services',
				'default_anchor'            => 'services',
				'supported_layouts'         => array( 'card-grid', 'compact-grid', 'list' ),
				'supported_design_features' => array_merge( $shared_section_design, array( 'card-style' ) ),
				'navigation'                => array(
					'supported'     => true,
					'default_label' => __( 'Services', 'nexa-pro' ),
				),
			)
		),
		'features'     => array_merge(
			$section_capabilities,
			array(
				'type'                      => 'features',
				'label'                     => __( 'Features', 'nexa-pro' ),
				'description'               => __( 'Feature cards for highlighting differentiators or capabilities.', 'nexa-pro' ),
				'template'                  => 'template-parts/sections/features.php',
				'legacy_section_key'        => 'features',
				'default_anchor'            => 'features',
				'supported_layouts'         => array( 'card-grid', 'compact-grid', 'list' ),
				'supported_design_features' => array_merge( $shared_section_design, array( 'card-style' ) ),
				'navigation'                => array(
					'supported'     => true,
					'default_label' => __( 'Features', 'nexa-pro' ),
				),
			)
		),
		'process'      => array_merge(
			$section_capabilities,
			array(
				'type'                      => 'process',
				'label'                     => __( 'Process', 'nexa-pro' ),
				'description'               => __( 'Ordered steps that explain a workflow or engagement model.', 'nexa-pro' ),
				'template'                  => 'template-parts/sections/process.php',
				'legacy_section_key'        => 'process',
				'default_anchor'            => 'process',
				'supported_layouts'         => array( 'steps', 'timeline', 'card-grid' ),
				'supported_design_features' => array_merge( $shared_section_design, array( 'numbered-items' ) ),
				'navigation'                => array(
					'supported'     => true,
					'default_label' => __( 'Process', 'nexa-pro' ),
				),
			)
		),
		'why'          => array_merge(
			$section_capabilities,
			array(
				'type'                      => 'why',
				'label'                     => __( 'Why Choose Us', 'nexa-pro' ),
				'description'               => __( 'Supporting reasons section with optional image and value statements.', 'nexa-pro' ),
				'template'                  => 'template-parts/sections/why.php',
				'legacy_section_key'        => 'why',
				'default_anchor'            => 'why',
				'supported_layouts'         => array( 'default', 'image-left', 'image-right', 'text-only' ),
				'supported_design_features' => array_merge( $shared_section_design, array( 'foreground-image' ) ),
				'navigation'                => array(
					'supported'     => true,
					'default_label' => __( 'Why Choose Us', 'nexa-pro' ),
				),
			)
		),
		'portfolio'    => array_merge(
			$section_capabilities,
			array(
				'type'                      => 'portfolio',
				'label'                     => __( 'Portfolio', 'nexa-pro' ),
				'description'               => __( 'Neutral portfolio-style examples for engagement types or work categories.', 'nexa-pro' ),
				'template'                  => 'template-parts/sections/portfolio.php',
				'legacy_section_key'        => 'portfolio',
				'default_anchor'            => 'portfolio',
				'supported_layouts'         => array( 'card-grid', 'featured-image', 'list' ),
				'supported_design_features' => array_merge( $shared_section_design, array( 'foreground-image', 'card-style' ) ),
				'navigation'                => array(
					'supported'     => true,
					'default_label' => __( 'Portfolio', 'nexa-pro' ),
				),
			)
		),
		'testimonials' => array_merge(
			$section_capabilities,
			array(
				'type'                      => 'testimonials',
				'label'                     => __( 'Testimonials', 'nexa-pro' ),
				'description'               => __( 'Prepared presentation area for authentic customer feedback.', 'nexa-pro' ),
				'template'                  => 'template-parts/sections/testimonials.php',
				'legacy_section_key'        => 'testimonials',
				'default_anchor'            => 'testimonials',
				'supported_layouts'         => array( 'message', 'cards', 'quote-grid' ),
				'supported_design_features' => array_merge( $shared_section_design, array( 'card-style' ) ),
				'navigation'                => array(
					'supported'     => true,
					'default_label' => __( 'Testimonials', 'nexa-pro' ),
				),
			)
		),
		'team'         => array_merge(
			$section_capabilities,
			array(
				'type'                      => 'team',
				'label'                     => __( 'Team', 'nexa-pro' ),
				'description'               => __( 'Team or role presentation cards.', 'nexa-pro' ),
				'template'                  => 'template-parts/sections/team.php',
				'legacy_section_key'        => 'team',
				'default_anchor'            => 'team',
				'supported_layouts'         => array( 'card-grid', 'compact-grid', 'list' ),
				'supported_design_features' => array_merge( $shared_section_design, array( 'card-style' ) ),
				'navigation'                => array(
					'supported'     => true,
					'default_label' => __( 'Team', 'nexa-pro' ),
				),
			)
		),
		'faq'          => array_merge(
			$section_capabilities,
			array(
				'type'                      => 'faq',
				'label'                     => __( 'FAQ', 'nexa-pro' ),
				'description'               => __( 'Question and answer section using semantic disclosure markup.', 'nexa-pro' ),
				'template'                  => 'template-parts/sections/faq.php',
				'legacy_section_key'        => 'faq',
				'default_anchor'            => 'faq',
				'supported_layouts'         => array( 'accordion', 'two-column', 'stacked' ),
				'supported_design_features' => array_merge( $shared_section_design, array( 'disclosure-items' ) ),
				'navigation'                => array(
					'supported'     => true,
					'default_label' => __( 'FAQ', 'nexa-pro' ),
				),
			)
		),
		'cta'          => array_merge(
			$section_capabilities,
			array(
				'type'                      => 'cta',
				'label'                     => __( 'CTA', 'nexa-pro' ),
				'description'               => __( 'Focused call-to-action section with optional action link.', 'nexa-pro' ),
				'template'                  => 'template-parts/sections/cta.php',
				'legacy_section_key'        => 'cta',
				'default_anchor'            => 'cta',
				'supported_layouts'         => array( 'centered', 'split', 'banner' ),
				'supported_design_features' => array_merge( $shared_section_design, array( 'action-button' ) ),
				'navigation'                => array(
					'supported'     => true,
					'default_label' => __( 'CTA', 'nexa-pro' ),
				),
			)
		),
		'contact'      => array_merge(
			$section_capabilities,
			array(
				'type'                      => 'contact',
				'label'                     => __( 'Contact', 'nexa-pro' ),
				'description'               => __( 'Contact-oriented section for next steps and availability details.', 'nexa-pro' ),
				'template'                  => 'template-parts/sections/contact.php',
				'legacy_section_key'        => 'contact',
				'default_anchor'            => 'contact',
				'supported_layouts'         => array( 'default', 'split', 'details-grid' ),
				'supported_design_features' => array_merge( $shared_section_design, array( 'contact-details' ) ),
				'navigation'                => array(
					'supported'     => true,
					'default_label' => __( 'Contact', 'nexa-pro' ),
				),
			)
		),
	);
}

/**
 * Sanitize a component token list.
 *
 * @param mixed $values Raw token list.
 * @return array
 */
function nexa_pro_sanitize_component_token_list( $values ) {
	if ( ! is_array( $values ) ) {
		return array();
	}

	$tokens = array();

	foreach ( $values as $value ) {
		$token = sanitize_key( $value );

		if ( '' === $token || in_array( $token, $tokens, true ) ) {
			continue;
		}

		$tokens[] = $token;
	}

	return $tokens;
}

/**
 * Get a validated component template path.
 *
 * @param mixed $template Raw template path.
 * @return string
 */
function nexa_pro_normalize_component_template( $template ) {
	if ( ! is_string( $template ) || '' === $template ) {
		return '';
	}

	$template = str_replace( '\\', '/', $template );

	if ( '/' === substr( $template, 0, 1 ) ) {
		return '';
	}

	$template = ltrim( $template, '/' );

	if ( false !== strpos( $template, '..' ) || false !== strpos( $template, '://' ) ) {
		return '';
	}

	if ( 0 !== strpos( $template, 'template-parts/sections/' ) || '.php' !== substr( $template, -4 ) ) {
		return '';
	}

	$template_root = realpath( NEXA_PRO_DIR . '/template-parts/sections' );
	$template_file = realpath( NEXA_PRO_DIR . '/' . $template );

	if ( ! $template_root || ! $template_file ) {
		return '';
	}

	$template_root = rtrim( str_replace( '\\', '/', $template_root ), '/' ) . '/';
	$template_file = str_replace( '\\', '/', $template_file );

	if ( 0 !== strpos( $template_file, $template_root ) ) {
		return '';
	}

	return $template;
}

/**
 * Normalize a component capability declaration.
 *
 * @param mixed  $capability Raw capability declaration.
 * @param string $flag_key   Boolean flag key.
 * @param bool   $default    Default flag value.
 * @return array
 */
function nexa_pro_normalize_component_capability( $capability, $flag_key, $default ) {
	$normalized = array(
		$flag_key => (bool) $default,
	);

	if ( is_bool( $capability ) ) {
		$normalized[ $flag_key ] = $capability;

		return $normalized;
	}

	if ( ! is_array( $capability ) ) {
		return $normalized;
	}

	if ( isset( $capability[ $flag_key ] ) ) {
		$normalized[ $flag_key ] = (bool) $capability[ $flag_key ];
	}

	foreach ( array( 'content_owner', 'storage_owner', 'notes' ) as $detail_key ) {
		if ( isset( $capability[ $detail_key ] ) && is_scalar( $capability[ $detail_key ] ) ) {
			$normalized[ $detail_key ] = sanitize_text_field( (string) $capability[ $detail_key ] );
		}
	}

	return $normalized;
}

/**
 * Normalize one component definition.
 *
 * @param mixed  $definition Raw component definition.
 * @param string $type       Registry key.
 * @return array
 */
function nexa_pro_normalize_component_definition( $definition, $type = '' ) {
	if ( ! is_array( $definition ) ) {
		return array();
	}

	$type = '' !== $type ? sanitize_key( $type ) : '';

	if ( '' === $type && isset( $definition['type'] ) ) {
		$type = sanitize_key( $definition['type'] );
	}

	if ( '' === $type ) {
		return array();
	}

	$template = isset( $definition['template'] ) ? nexa_pro_normalize_component_template( $definition['template'] ) : '';

	if ( '' === $template ) {
		return array();
	}

	$label = isset( $definition['label'] ) && is_scalar( $definition['label'] ) ? sanitize_text_field( (string) $definition['label'] ) : $type;

	$normalized = array(
		'type'                      => $type,
		'label'                     => $label,
		'description'               => isset( $definition['description'] ) && is_scalar( $definition['description'] ) ? sanitize_text_field( (string) $definition['description'] ) : '',
		'template'                  => $template,
		'legacy_section_key'        => isset( $definition['legacy_section_key'] ) ? sanitize_key( $definition['legacy_section_key'] ) : $type,
		'default_anchor'            => isset( $definition['default_anchor'] ) ? sanitize_key( $definition['default_anchor'] ) : $type,
		'supported_layouts'         => isset( $definition['supported_layouts'] ) ? nexa_pro_sanitize_component_token_list( $definition['supported_layouts'] ) : array(),
		'supported_design_features' => isset( $definition['supported_design_features'] ) ? nexa_pro_sanitize_component_token_list( $definition['supported_design_features'] ) : array(),
		'navigation'                => array(
			'supported'     => false,
			'default_label' => $label,
		),
		'repeatable'                => isset( $definition['repeatable'] ) ? nexa_pro_normalize_component_capability( $definition['repeatable'], 'supported', false ) : array( 'supported' => false ),
		'reusable'                  => isset( $definition['reusable'] ) ? nexa_pro_normalize_component_capability( $definition['reusable'], 'eligible', false ) : array( 'eligible' => false ),
	);

	if ( isset( $definition['navigation'] ) && is_array( $definition['navigation'] ) ) {
		$normalized['navigation']['supported'] = ! empty( $definition['navigation']['supported'] );

		if ( isset( $definition['navigation']['default_label'] ) && is_scalar( $definition['navigation']['default_label'] ) ) {
			$normalized['navigation']['default_label'] = sanitize_text_field( (string) $definition['navigation']['default_label'] );
		}
	}

	return $normalized;
}

/**
 * Get the component registry.
 *
 * @return array
 */
function nexa_pro_get_component_registry() {
	$registry = nexa_pro_get_default_component_registry();

	/**
	 * Filter the component presentation registry.
	 *
	 * Custom storage, custom post types, and reusable-instance ownership should
	 * remain in the companion plugin. Theme definitions are presentation
	 * metadata and approved template mappings only.
	 *
	 * @param array $registry Component definitions keyed by component type.
	 */
	$registry = apply_filters( 'nexa_pro_component_registry', $registry );

	if ( ! is_array( $registry ) ) {
		return array();
	}

	$normalized = array();

	foreach ( $registry as $type => $definition ) {
		$type       = sanitize_key( $type );
		$definition = nexa_pro_normalize_component_definition( $definition, $type );

		if ( empty( $definition ) ) {
			continue;
		}

		$normalized[ $definition['type'] ] = $definition;
	}

	return $normalized;
}

/**
 * Get one component definition.
 *
 * @param string $type Component type.
 * @return array
 */
function nexa_pro_get_component_definition( $type ) {
	$type = sanitize_key( $type );

	if ( '' === $type ) {
		return array();
	}

	$registry = nexa_pro_get_component_registry();

	if ( empty( $registry[ $type ] ) ) {
		return array();
	}

	$definition = $registry[ $type ];

	/**
	 * Filter one normalized component definition before it is consumed.
	 *
	 * @param array  $definition Normalized component definition.
	 * @param string $type       Component type.
	 */
	$definition = apply_filters( 'nexa_pro_component_definition', $definition, $type );

	return nexa_pro_normalize_component_definition( $definition, $type );
}

/**
 * Get registered component types.
 *
 * @return array
 */
function nexa_pro_get_component_types() {
	return array_keys( nexa_pro_get_component_registry() );
}

/**
 * Determine whether a component type exists.
 *
 * @param string $type Component type.
 * @return bool
 */
function nexa_pro_component_type_exists( $type ) {
	return ! empty( nexa_pro_get_component_definition( $type ) );
}

/**
 * Get a registered component template path.
 *
 * @param string $type Component type.
 * @return string
 */
function nexa_pro_get_component_template( $type ) {
	$definition = nexa_pro_get_component_definition( $type );

	return ! empty( $definition['template'] ) ? $definition['template'] : '';
}

/**
 * Get a registered component default anchor.
 *
 * @param string $type Component type.
 * @return string
 */
function nexa_pro_get_component_default_anchor( $type ) {
	$definition = nexa_pro_get_component_definition( $type );

	return ! empty( $definition['default_anchor'] ) ? $definition['default_anchor'] : '';
}

/**
 * Get registered component supported layouts.
 *
 * @param string $type Component type.
 * @return array
 */
function nexa_pro_get_component_supported_layouts( $type ) {
	$definition = nexa_pro_get_component_definition( $type );

	return ! empty( $definition['supported_layouts'] ) && is_array( $definition['supported_layouts'] ) ? $definition['supported_layouts'] : array();
}
