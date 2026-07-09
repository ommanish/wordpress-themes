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
	);
}
