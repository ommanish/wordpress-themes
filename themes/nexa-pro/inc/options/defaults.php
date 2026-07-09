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
		'about_label'               => __( 'About the theme', 'nexa-pro' ),
		'about_heading'             => __( 'Designed for teams that need clarity before decoration.', 'nexa-pro' ),
		'about_text'                => __( 'Nexa Pro organizes core business content into readable sections, reusable cards, and focused calls to action so site owners can explain what they do without wrestling the layout.', 'nexa-pro' ),
		'services_show'             => '1',
		'services_label'            => __( 'Services', 'nexa-pro' ),
		'services_heading'          => __( 'Present services with concise, scannable cards.', 'nexa-pro' ),
		'services_text'             => __( 'Use this area to introduce the main ways a business helps its customers.', 'nexa-pro' ),
		'features_show'             => '1',
		'features_label'            => __( 'Features', 'nexa-pro' ),
		'features_heading'          => __( 'Reusable patterns for professional business pages.', 'nexa-pro' ),
		'features_text'             => '',
		'process_show'              => '1',
		'process_label'             => __( 'Process', 'nexa-pro' ),
		'process_heading'           => __( 'Show how work moves from first conversation to next step.', 'nexa-pro' ),
		'process_text'              => '',
		'why_show'                  => '1',
		'why_label'                 => __( 'Why Nexa Pro', 'nexa-pro' ),
		'why_heading'               => __( 'A calm foundation for serious business content.', 'nexa-pro' ),
		'why_text'                  => '',
		'cta_show'                  => '1',
		'cta_heading'               => __( 'Ready to shape the homepage around real content?', 'nexa-pro' ),
		'cta_text'                  => __( 'Use this foundation as the starting point, then replace the temporary defaults with verified details from the business.', 'nexa-pro' ),
		'cta_button_text'           => __( 'Start with contact details', 'nexa-pro' ),
		'cta_button_url'            => '#contact',
	);
}
