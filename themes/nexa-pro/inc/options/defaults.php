<?php
/**
 * Theme option defaults.
 *
 * @package Nexa_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the default order for movable homepage sections.
 *
 * Hero and trust are fixed sections and intentionally excluded.
 *
 * @return array
 */
function nexa_pro_get_default_homepage_section_order() {
	return array(
		'about',
		'services',
		'features',
		'process',
		'why',
		'portfolio',
		'testimonials',
		'team',
		'faq',
		'contact',
		'cta',
	);
}

/**
 * Get the default Nexa Pro options.
 *
 * @return array
 */
function nexa_pro_get_default_options() {
	return array(
		'brand_name'              => '',
		'brand_tagline'           => '',
		'primary_color'           => '#2563eb',
		'accent_color'            => '#0f766e',
		'logo_attachment_id'        => 0,
		'mobile_logo_attachment_id' => 0,
		'display_brand_text'        => '1',
		'sticky_header'             => '0',
		'transparent_header'        => '0',
		'header_layout'             => 'standard',
		'header_cta_enabled'        => '0',
		'header_cta_text'           => '',
		'header_cta_url'            => '',
		'mobile_cta_enabled'        => '0',
		'hero_eyebrow'              => __( 'Nexa Pro', 'nexa-pro' ),
		'hero_heading'              => __( 'A polished WordPress foundation for professional service websites.', 'nexa-pro' ),
		'hero_text'                 => __( 'Launch a clear, accessible, and conversion-ready business website with structured sections built for agencies, consultants, SaaS teams, recruiters, and service providers.', 'nexa-pro' ),
		'hero_primary_cta_text'     => __( 'Explore the sections', 'nexa-pro' ),
		'hero_primary_cta_url'      => '#services',
		'hero_secondary_cta_text'   => __( 'View setup path', 'nexa-pro' ),
		'hero_secondary_cta_url'    => '#process',
		'about_show'                => '1',
		'about_image_id'            => 0,
		'about_label'               => __( 'About the theme', 'nexa-pro' ),
		'about_heading'             => __( 'Designed for teams that need clarity before decoration.', 'nexa-pro' ),
		'about_text'                => __( 'Nexa Pro organizes core business content into readable sections, reusable cards, and focused calls to action so site owners can explain what they do without wrestling the layout.', 'nexa-pro' ),
		'services_show'             => '1',
		'services_background_image_id' => 0,
		'services_label'            => __( 'Services', 'nexa-pro' ),
		'services_heading'          => __( 'Present services with concise, scannable cards.', 'nexa-pro' ),
		'services_text'             => __( 'Use this area to introduce the main ways a business helps its customers.', 'nexa-pro' ),
		'services_items'            => array(
			array(
				'id'        => 'services-advisory-services',
				'title'     => __( 'Advisory services', 'nexa-pro' ),
				'text'      => __( 'Frame expert guidance, planning, audits, or retained consulting offers.', 'nexa-pro' ),
				'link_text' => '',
				'link_url'  => '',
			),
			array(
				'id'        => 'services-implementation-support',
				'title'     => __( 'Implementation support', 'nexa-pro' ),
				'text'      => __( 'Describe hands-on delivery, setup, migration, or operational support.', 'nexa-pro' ),
				'link_text' => '',
				'link_url'  => '',
			),
			array(
				'id'        => 'services-ongoing-optimization',
				'title'     => __( 'Ongoing optimization', 'nexa-pro' ),
				'text'      => __( 'Explain recurring improvement, reporting, enablement, or growth services.', 'nexa-pro' ),
				'link_text' => '',
				'link_url'  => '',
			),
		),
		'features_show'             => '1',
		'features_background_image_id' => 0,
		'features_label'            => __( 'Features', 'nexa-pro' ),
		'features_heading'          => __( 'Reusable patterns for professional business pages.', 'nexa-pro' ),
		'features_text'             => '',
		'features_items'            => array(
			array(
				'id'    => 'features-structured-sections',
				'title' => __( 'Structured sections', 'nexa-pro' ),
				'text'  => __( 'Clear section boundaries help visitors move from context to action.', 'nexa-pro' ),
			),
			array(
				'id'    => 'features-flexible-cards',
				'title' => __( 'Flexible cards', 'nexa-pro' ),
				'text'  => __( 'Card grids work for services, benefits, team profiles, and resource previews.', 'nexa-pro' ),
			),
			array(
				'id'    => 'features-accessible-defaults',
				'title' => __( 'Accessible defaults', 'nexa-pro' ),
				'text'  => __( 'Headings, links, focus states, and layout foundations are designed with accessibility in mind.', 'nexa-pro' ),
			),
		),
		'process_show'              => '1',
		'process_background_image_id' => 0,
		'process_label'             => __( 'Process', 'nexa-pro' ),
		'process_heading'           => __( 'Show how work moves from first conversation to next step.', 'nexa-pro' ),
		'process_text'              => '',
		'process_items'             => array(
			array(
				'id'    => 'process-discover',
				'title' => __( 'Discover', 'nexa-pro' ),
				'text'  => __( 'Clarify goals, audience needs, constraints, and the most important user journeys.', 'nexa-pro' ),
			),
			array(
				'id'    => 'process-shape',
				'title' => __( 'Shape', 'nexa-pro' ),
				'text'  => __( 'Organize content, page structure, calls to action, and proof points.', 'nexa-pro' ),
			),
			array(
				'id'    => 'process-launch',
				'title' => __( 'Launch', 'nexa-pro' ),
				'text'  => __( 'Publish a focused experience and prepare the next round of improvements.', 'nexa-pro' ),
			),
		),
		'why_show'                  => '1',
		'why_image_id'              => 0,
		'why_label'                 => __( 'Why Nexa Pro', 'nexa-pro' ),
		'why_heading'               => __( 'A calm foundation for serious business content.', 'nexa-pro' ),
		'why_text'                  => '',
		'why_items'                 => array(
			array(
				'id'    => 'why-built-with-reusable-wordpress-template-parts',
				'title' => __( 'Built with reusable WordPress template parts.', 'nexa-pro' ),
				'text'  => '',
			),
			array(
				'id'    => 'why-prepared-for-future-homepage-settings',
				'title' => __( 'Prepared for future homepage settings without coupling content to templates.', 'nexa-pro' ),
				'text'  => '',
			),
			array(
				'id'    => 'why-uses-the-existing-design-token-system',
				'title' => __( 'Uses the existing design-token system for consistent spacing, color, and type.', 'nexa-pro' ),
				'text'  => '',
			),
		),
		'cta_show'                  => '1',
		'cta_heading'               => __( 'Ready to shape the homepage around real content?', 'nexa-pro' ),
		'cta_text'                  => __( 'Use this foundation as the starting point, then replace the temporary defaults with verified details from the business.', 'nexa-pro' ),
		'cta_button_text'           => __( 'Start with contact details', 'nexa-pro' ),
		'cta_button_url'            => '#contact',
		'portfolio_image_id'        => 0,
		'testimonials_background_image_id' => 0,
		'team_background_image_id'  => 0,
		'cta_background_image_id'   => 0,
		'contact_background_image_id' => 0,
		'homepage_section_order'    => nexa_pro_get_default_homepage_section_order(),
	);
}
