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
		'header_cta_text'         => '',
		'header_cta_url'          => '',
		'hero_eyebrow'            => __( 'Nexa Pro', 'nexa-pro' ),
		'hero_heading'            => __( 'A polished WordPress foundation for professional service websites.', 'nexa-pro' ),
		'hero_text'               => __( 'Launch a clear, accessible, and conversion-ready business website with structured sections built for agencies, consultants, SaaS teams, recruiters, and service providers.', 'nexa-pro' ),
		'hero_primary_cta_text'   => __( 'Explore the sections', 'nexa-pro' ),
		'hero_primary_cta_url'    => '#services',
		'hero_secondary_cta_text' => __( 'View setup path', 'nexa-pro' ),
		'hero_secondary_cta_url'  => '#process',
	);
}

