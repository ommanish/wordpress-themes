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

	$shared_layout_controls = array(
		'layout',
		'container_width',
		'content_alignment',
		'content_width',
		'section_spacing',
	);

	$media_layout_controls = array_merge(
		$shared_layout_controls,
		array(
			'media_position',
		)
	);

	$grid_layout_controls = array_merge(
		$shared_layout_controls,
		array(
			'column_count',
			'card_density',
			'item_spacing',
		)
	);

	$shared_design_capabilities = array(
		'design_preset',
		'background',
		'gradient',
		'background_image',
		'overlay',
		'text_theme',
		'radius',
		'shadow',
		'button_style',
	);

	$card_design_capabilities = array_merge(
		$shared_design_capabilities,
		array(
			'card_style',
			'image_style',
		)
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
			'supported_layouts'         => array( 'centered', 'split-left', 'split-right', 'background-image', 'minimal', 'full-height' ),
			'default_layout'            => 'centered',
			'layout_controls'           => array_merge( $media_layout_controls, array( 'mobile_image_visibility' ) ),
			'content_capabilities'      => array( 'eyebrow', 'heading', 'body', 'primary_cta', 'secondary_cta', 'desktop_image', 'mobile_image' ),
			'design_capabilities'       => array_merge( $shared_design_capabilities, array( 'image_style' ) ),
			'preview'                   => array(
				'label'       => __( 'Intro layouts', 'nexa-pro' ),
				'description' => __( 'Choose between centered, split-media, background, minimal, and full-height introductions.', 'nexa-pro' ),
			),
			'responsive_notes'          => __( 'Split layouts stack on small screens; full-height uses safe viewport sizing.', 'nexa-pro' ),
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
				'supported_layouts'         => array( 'text-only', 'image-left', 'image-right', 'statistics', 'split-content' ),
				'default_layout'            => 'image-right',
				'layout_controls'           => $media_layout_controls,
				'content_capabilities'      => array( 'eyebrow', 'heading', 'body', 'points', 'foreground_image', 'statistics' ),
				'design_capabilities'       => $card_design_capabilities,
				'preview'                   => array(
					'label'       => __( 'Narrative layouts', 'nexa-pro' ),
					'description' => __( 'Use text, split media, statistics, or mixed content layouts.', 'nexa-pro' ),
				),
				'responsive_notes'          => __( 'Media and statistics stack below content on narrow screens.', 'nexa-pro' ),
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
				'supported_layouts'         => array( 'card-grid', 'icon-grid', 'alternating-rows', 'image-cards', 'compact-list' ),
				'default_layout'            => 'card-grid',
				'layout_controls'           => $grid_layout_controls,
				'content_capabilities'      => array( 'eyebrow', 'heading', 'body', 'items', 'links' ),
				'design_capabilities'       => $card_design_capabilities,
				'preview'                   => array(
					'label'       => __( 'Service presentations', 'nexa-pro' ),
					'description' => __( 'Present services as grids, rows, image cards, or compact lists.', 'nexa-pro' ),
				),
				'responsive_notes'          => __( 'Cards reflow to one column before they crowd.', 'nexa-pro' ),
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
				'supported_layouts'         => array( 'icon-grid', 'bento-grid', 'alternating', 'centered-grid', 'checklist' ),
				'default_layout'            => 'icon-grid',
				'layout_controls'           => $grid_layout_controls,
				'content_capabilities'      => array( 'eyebrow', 'heading', 'body', 'items' ),
				'design_capabilities'       => $card_design_capabilities,
				'preview'                   => array(
					'label'       => __( 'Feature structures', 'nexa-pro' ),
					'description' => __( 'Use feature grids, bento grouping, alternating copy, or checklist style.', 'nexa-pro' ),
				),
				'responsive_notes'          => __( 'Bento layouts collapse to regular cards at tablet and phone widths.', 'nexa-pro' ),
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
				'supported_layouts'         => array( 'horizontal-steps', 'vertical-timeline', 'numbered-cards', 'connected-steps' ),
				'default_layout'            => 'horizontal-steps',
				'layout_controls'           => $grid_layout_controls,
				'content_capabilities'      => array( 'eyebrow', 'heading', 'body', 'items', 'numbered_items' ),
				'design_capabilities'       => $card_design_capabilities,
				'preview'                   => array(
					'label'       => __( 'Process flow', 'nexa-pro' ),
					'description' => __( 'Show steps horizontally, vertically, as numbered cards, or connected stages.', 'nexa-pro' ),
				),
				'responsive_notes'          => __( 'Horizontal and connected layouts become vertical on mobile.', 'nexa-pro' ),
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
				'supported_layouts'         => array( 'benefit-cards', 'icon-list', 'split-media', 'statistics' ),
				'default_layout'            => 'benefit-cards',
				'layout_controls'           => $media_layout_controls,
				'content_capabilities'      => array( 'eyebrow', 'heading', 'body', 'points', 'foreground_image', 'statistics' ),
				'design_capabilities'       => $card_design_capabilities,
				'preview'                   => array(
					'label'       => __( 'Benefit layouts', 'nexa-pro' ),
					'description' => __( 'Use cards, lists, split media, or statistical proof points.', 'nexa-pro' ),
				),
				'responsive_notes'          => __( 'Media and benefit cards stack cleanly on small screens.', 'nexa-pro' ),
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
				'supported_layouts'         => array( 'grid', 'masonry-grid', 'featured-project', 'case-study-cards' ),
				'default_layout'            => 'grid',
				'layout_controls'           => $grid_layout_controls,
				'content_capabilities'      => array( 'eyebrow', 'heading', 'body', 'items', 'foreground_image' ),
				'design_capabilities'       => $card_design_capabilities,
				'preview'                   => array(
					'label'       => __( 'Portfolio layouts', 'nexa-pro' ),
					'description' => __( 'Show examples in grids, masonry-like grids, featured layouts, or case-study cards.', 'nexa-pro' ),
				),
				'responsive_notes'          => __( 'Masonry-like grids use CSS Grid and degrade to simple grids.', 'nexa-pro' ),
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
				'supported_layouts'         => array( 'grid', 'featured-quote', 'static-slider', 'logo-and-quote' ),
				'default_layout'            => 'grid',
				'layout_controls'           => $grid_layout_controls,
				'content_capabilities'      => array( 'eyebrow', 'heading', 'body', 'items', 'quotes' ),
				'design_capabilities'       => $card_design_capabilities,
				'preview'                   => array(
					'label'       => __( 'Testimonial layouts', 'nexa-pro' ),
					'description' => __( 'Use grids, featured quotes, slide-ready static rows, or logo-and-quote layouts.', 'nexa-pro' ),
				),
				'responsive_notes'          => __( 'Static sliders do not autoplay and remain keyboard scrollable.', 'nexa-pro' ),
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
				'supported_layouts'         => array( 'profile-grid', 'compact-list', 'leadership-feature', 'image-cards' ),
				'default_layout'            => 'profile-grid',
				'layout_controls'           => $grid_layout_controls,
				'content_capabilities'      => array( 'eyebrow', 'heading', 'body', 'items', 'profiles' ),
				'design_capabilities'       => $card_design_capabilities,
				'preview'                   => array(
					'label'       => __( 'Team layouts', 'nexa-pro' ),
					'description' => __( 'Present team members as profile grids, compact lists, leadership features, or image cards.', 'nexa-pro' ),
				),
				'responsive_notes'          => __( 'Profile cards keep images responsive and avoid distortion.', 'nexa-pro' ),
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
				'supported_layouts'         => array( 'accordion', 'two-column', 'categorized-list' ),
				'default_layout'            => 'accordion',
				'layout_controls'           => array_merge( $shared_layout_controls, array( 'item_spacing' ) ),
				'content_capabilities'      => array( 'eyebrow', 'heading', 'body', 'items', 'categories' ),
				'design_capabilities'       => $card_design_capabilities,
				'preview'                   => array(
					'label'       => __( 'FAQ layouts', 'nexa-pro' ),
					'description' => __( 'Use accessible accordion controls, two-column lists, or categorized groups.', 'nexa-pro' ),
				),
				'responsive_notes'          => __( 'Two-column FAQ layouts collapse before answers become cramped.', 'nexa-pro' ),
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
				'supported_layouts'         => array( 'centered', 'split', 'banner', 'image-background', 'compact' ),
				'default_layout'            => 'centered',
				'layout_controls'           => $media_layout_controls,
				'content_capabilities'      => array( 'heading', 'body', 'primary_cta', 'foreground_image' ),
				'design_capabilities'       => $shared_design_capabilities,
				'preview'                   => array(
					'label'       => __( 'Action layouts', 'nexa-pro' ),
					'description' => __( 'Use centered, split, banner, image-background, or compact calls to action.', 'nexa-pro' ),
				),
				'responsive_notes'          => __( 'CTA buttons wrap and split layouts stack on small screens.', 'nexa-pro' ),
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
				'supported_layouts'         => array( 'details-only', 'form-and-details', 'split-map-placeholder', 'cards' ),
				'default_layout'            => 'details-only',
				'layout_controls'           => $grid_layout_controls,
				'content_capabilities'      => array( 'eyebrow', 'heading', 'body', 'items', 'contact_details', 'map_placeholder' ),
				'design_capabilities'       => $card_design_capabilities,
				'preview'                   => array(
					'label'       => __( 'Contact layouts', 'nexa-pro' ),
					'description' => __( 'Use details-only, form-ready split, safe map placeholder, or contact cards.', 'nexa-pro' ),
				),
				'responsive_notes'          => __( 'Form-ready and card layouts stack without remote map embeds.', 'nexa-pro' ),
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
		'default_layout'            => isset( $definition['default_layout'] ) ? sanitize_key( $definition['default_layout'] ) : '',
		'layout_controls'           => isset( $definition['layout_controls'] ) ? nexa_pro_sanitize_component_token_list( $definition['layout_controls'] ) : array(),
		'content_capabilities'      => isset( $definition['content_capabilities'] ) ? nexa_pro_sanitize_component_token_list( $definition['content_capabilities'] ) : array(),
		'design_capabilities'       => isset( $definition['design_capabilities'] ) ? nexa_pro_sanitize_component_token_list( $definition['design_capabilities'] ) : array(),
		'preview'                   => array(
			'label'       => $label,
			'description' => '',
		),
		'responsive_notes'          => isset( $definition['responsive_notes'] ) && is_scalar( $definition['responsive_notes'] ) ? sanitize_text_field( (string) $definition['responsive_notes'] ) : '',
		'supported_design_features' => isset( $definition['supported_design_features'] ) ? nexa_pro_sanitize_component_token_list( $definition['supported_design_features'] ) : array(),
		'navigation'                => array(
			'supported'     => false,
			'default_label' => $label,
		),
		'repeatable'                => isset( $definition['repeatable'] ) ? nexa_pro_normalize_component_capability( $definition['repeatable'], 'supported', false ) : array( 'supported' => false ),
		'reusable'                  => isset( $definition['reusable'] ) ? nexa_pro_normalize_component_capability( $definition['reusable'], 'eligible', false ) : array( 'eligible' => false ),
	);

	if ( empty( $normalized['supported_layouts'] ) ) {
		$normalized['supported_layouts'] = array( 'default' );
	}

	if ( '' === $normalized['default_layout'] || ! in_array( $normalized['default_layout'], $normalized['supported_layouts'], true ) ) {
		$normalized['default_layout'] = $normalized['supported_layouts'][0];
	}

	if ( empty( $normalized['layout_controls'] ) ) {
		$normalized['layout_controls'] = array( 'layout' );
	}

	if ( isset( $definition['preview'] ) && is_array( $definition['preview'] ) ) {
		if ( isset( $definition['preview']['label'] ) && is_scalar( $definition['preview']['label'] ) ) {
			$normalized['preview']['label'] = sanitize_text_field( (string) $definition['preview']['label'] );
		}

		if ( isset( $definition['preview']['description'] ) && is_scalar( $definition['preview']['description'] ) ) {
			$normalized['preview']['description'] = sanitize_text_field( (string) $definition['preview']['description'] );
		}
	}

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

/**
 * Get the default layout for a component type.
 *
 * @param string $type Component type.
 * @return string
 */
function nexa_pro_get_component_default_layout( $type ) {
	$definition = nexa_pro_get_component_definition( $type );

	return ! empty( $definition['default_layout'] ) ? $definition['default_layout'] : 'default';
}

/**
 * Validate a component layout against its registry definition.
 *
 * @param string $type   Component type.
 * @param string $layout Layout token.
 * @return string
 */
function nexa_pro_get_component_layout_or_default( $type, $layout ) {
	$type    = sanitize_key( $type );
	$layout  = sanitize_key( $layout );
	$layouts = nexa_pro_get_component_supported_layouts( $type );

	if ( in_array( $layout, $layouts, true ) ) {
		return $layout;
	}

	return nexa_pro_get_component_default_layout( $type );
}

/**
 * Get layout controls declared for a component type.
 *
 * @param string $type Component type.
 * @return array
 */
function nexa_pro_get_component_layout_controls( $type ) {
	$definition = nexa_pro_get_component_definition( $type );

	return ! empty( $definition['layout_controls'] ) && is_array( $definition['layout_controls'] ) ? $definition['layout_controls'] : array( 'layout' );
}

/**
 * Get design capabilities declared for a component type.
 *
 * @param string $type Component type.
 * @return array
 */
function nexa_pro_get_component_design_capabilities( $type ) {
	$definition = nexa_pro_get_component_definition( $type );

	return ! empty( $definition['design_capabilities'] ) && is_array( $definition['design_capabilities'] ) ? $definition['design_capabilities'] : array();
}

/**
 * Get safe design token options for component rendering and storage.
 *
 * @return array
 */
function nexa_pro_get_component_design_tokens() {
	return array(
		'container_width'    => array( 'inherit', 'narrow', 'standard', 'wide', 'full' ),
		'content_width'      => array( 'inherit', 'narrow', 'standard', 'wide' ),
		'content_alignment'  => array( 'inherit', 'left', 'center', 'right' ),
		'media_position'     => array( 'inherit', 'left', 'right', 'top', 'bottom', 'background' ),
		'section_spacing'    => array( 'inherit', 'none', 'compact', 'standard', 'spacious', 'extra-spacious' ),
		'content_spacing'    => array( 'inherit', 'compact', 'standard', 'spacious' ),
		'item_spacing'       => array( 'inherit', 'compact', 'standard', 'spacious' ),
		'card_density'       => array( 'inherit', 'compact', 'comfortable', 'spacious' ),
		'background_type'    => array( 'inherit', 'solid', 'gradient', 'image' ),
		'gradient_direction' => array( 'to-bottom', 'to-right', 'to-bottom-right', 'to-bottom-left' ),
		'text_theme'         => array( 'inherit', 'automatic', 'light', 'dark' ),
		'card_style'         => array( 'inherit', 'flat', 'bordered', 'elevated', 'glass', 'minimal' ),
		'radius'             => array( 'inherit', 'none', 'small', 'medium', 'large', 'pill' ),
		'shadow'             => array( 'inherit', 'none', 'subtle', 'medium', 'strong' ),
		'image_style'        => array( 'inherit', 'square', 'soft', 'rounded', 'pill', 'circle' ),
		'button_style'       => array( 'inherit', 'primary', 'secondary', 'outline', 'ghost', 'text' ),
		'column_count'       => array( 1, 2, 3, 4, 5, 6 ),
	);
}

/**
 * Get built-in component design presets.
 *
 * @return array
 */
function nexa_pro_get_component_design_presets() {
	return array(
		'inherit'       => array(
			'label'  => __( 'Inherit', 'nexa-pro' ),
			'design' => array(),
		),
		'light'         => array(
			'label'  => __( 'Light', 'nexa-pro' ),
			'design' => array(
				'background_type' => 'solid',
				'background_color' => '#ffffff',
				'text_theme'      => 'dark',
				'section_spacing' => 'standard',
				'card_style'      => 'bordered',
				'radius'          => 'medium',
				'shadow'          => 'none',
			),
		),
		'dark'          => array(
			'label'  => __( 'Dark', 'nexa-pro' ),
			'design' => array(
				'background_type' => 'solid',
				'background_color' => '#15171a',
				'text_theme'      => 'light',
				'section_spacing' => 'spacious',
				'card_style'      => 'minimal',
				'radius'          => 'medium',
				'shadow'          => 'none',
			),
		),
		'brand'         => array(
			'label'  => __( 'Brand', 'nexa-pro' ),
			'design' => array(
				'background_type' => 'solid',
				'background_color' => '#2563eb',
				'text_theme'      => 'light',
				'section_spacing' => 'spacious',
				'card_style'      => 'glass',
				'radius'          => 'large',
				'shadow'          => 'subtle',
				'button_style'    => 'secondary',
			),
		),
		'accent'        => array(
			'label'  => __( 'Accent', 'nexa-pro' ),
			'design' => array(
				'background_type' => 'solid',
				'background_color' => '#0f766e',
				'text_theme'      => 'light',
				'section_spacing' => 'spacious',
				'card_style'      => 'glass',
				'radius'          => 'large',
				'shadow'          => 'subtle',
			),
		),
		'minimal'       => array(
			'label'  => __( 'Minimal', 'nexa-pro' ),
			'design' => array(
				'background_type' => 'inherit',
				'text_theme'      => 'inherit',
				'section_spacing' => 'compact',
				'card_style'      => 'minimal',
				'radius'          => 'small',
				'shadow'          => 'none',
				'button_style'    => 'text',
			),
		),
		'elevated'      => array(
			'label'  => __( 'Elevated', 'nexa-pro' ),
			'design' => array(
				'background_type' => 'solid',
				'background_color' => '#f7f9fc',
				'text_theme'      => 'dark',
				'section_spacing' => 'spacious',
				'card_style'      => 'elevated',
				'radius'          => 'large',
				'shadow'          => 'medium',
			),
		),
		'image-overlay' => array(
			'label'  => __( 'Image overlay', 'nexa-pro' ),
			'design' => array(
				'background_type' => 'image',
				'overlay_enabled' => true,
				'overlay_color'   => '#15171a',
				'overlay_opacity' => 62,
				'text_theme'      => 'light',
				'section_spacing' => 'extra-spacious',
				'card_style'      => 'glass',
				'radius'          => 'large',
				'shadow'          => 'subtle',
			),
		),
	);
}

/**
 * Get valid design preset keys.
 *
 * @return array
 */
function nexa_pro_get_component_design_preset_keys() {
	return array_keys( nexa_pro_get_component_design_presets() );
}
