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
 * Get global design color option keys.
 *
 * @return array
 */
function nexa_pro_get_design_color_option_keys() {
	return array(
		'color_primary',
		'color_secondary',
		'color_accent',
		'color_background',
		'color_surface',
		'color_text',
		'color_text_muted',
		'color_heading',
		'color_border',
		'color_button_primary_background',
		'color_button_primary_text',
		'color_button_primary_hover',
		'color_button_secondary_background',
		'color_button_secondary_text',
		'color_button_secondary_border',
		'color_link',
		'color_link_hover',
	);
}

/**
 * Get global typography option keys.
 *
 * @return array
 */
function nexa_pro_get_design_typography_option_keys() {
	return array(
		'font_body',
		'font_heading',
		'font_size_base',
		'line_height_body',
		'line_height_heading',
		'font_weight_heading',
		'font_weight_button',
	);
}

/**
 * Get all global design option keys.
 *
 * @return array
 */
function nexa_pro_get_global_design_option_keys() {
	return array_merge(
		nexa_pro_get_design_color_option_keys(),
		nexa_pro_get_design_typography_option_keys()
	);
}

/**
 * Get default global design options.
 *
 * Values mirror the current CSS token defaults so an unsaved installation does
 * not visually change.
 *
 * @return array
 */
function nexa_pro_get_default_global_design_options() {
	return array(
		'color_primary'                     => '#2563eb',
		'color_secondary'                   => '#1d4ed8',
		'color_accent'                      => '#0f766e',
		'color_background'                  => '#ffffff',
		'color_surface'                     => '#ffffff',
		'color_text'                        => '#15171a',
		'color_text_muted'                  => '#5d6673',
		'color_heading'                     => '#15171a',
		'color_border'                      => '#dce2e8',
		'color_button_primary_background'   => '#2563eb',
		'color_button_primary_text'         => '#ffffff',
		'color_button_primary_hover'        => '#1d4ed8',
		'color_button_secondary_background' => '#ffffff',
		'color_button_secondary_text'       => '#15171a',
		'color_button_secondary_border'     => '#dce2e8',
		'color_link'                        => '#2563eb',
		'color_link_hover'                  => '#1d4ed8',
		'font_body'                         => 'system-ui',
		'font_heading'                      => 'system-ui',
		'font_size_base'                    => 16,
		'line_height_body'                  => 1.6,
		'line_height_heading'               => 1.2,
		'font_weight_heading'               => '700',
		'font_weight_button'                => '700',
	);
}

/**
 * Get the default Nexa Pro options.
 *
 * @return array
 */
function nexa_pro_get_default_options() {
	$defaults = array(
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
		'footer_logo_id'            => 0,
		'footer_show_brand_text'    => '1',
		'footer_brand_text'         => '',
		'footer_description'        => '',
		'footer_menu_enabled'       => '1',
		'footer_copyright'          => __( '© {year} {site_name}. All rights reserved.', 'nexa-pro' ),
		'footer_privacy_label'      => __( 'Privacy Policy', 'nexa-pro' ),
		'footer_privacy_url'        => '',
		'footer_terms_label'        => __( 'Terms', 'nexa-pro' ),
		'footer_terms_url'          => '',
		'contact_email'             => '',
		'contact_phone'             => '',
		'contact_address'           => '',
		'contact_business_hours'    => '',
		'social_linkedin_url'       => '',
		'social_github_url'         => '',
		'social_x_url'              => '',
		'social_facebook_url'       => '',
		'social_instagram_url'      => '',
		'schedule_modal_enabled'    => '0',
		'schedule_modal_title'      => __( 'Schedule a Conversation', 'nexa-pro' ),
		'schedule_modal_text'       => __( 'Choose the contact option that works best for your next conversation.', 'nexa-pro' ),
		'schedule_email_label'      => __( 'Email us', 'nexa-pro' ),
		'schedule_email_subject'    => __( 'Conversation request', 'nexa-pro' ),
		'schedule_email_body'       => __( "Hello,\n\nI would like to schedule a conversation.", 'nexa-pro' ),
		'schedule_calendar_label'   => __( 'Book a time', 'nexa-pro' ),
		'schedule_calendar_url'     => '',
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

	return array_merge(
		$defaults,
		nexa_pro_get_default_global_design_options()
	);
}
