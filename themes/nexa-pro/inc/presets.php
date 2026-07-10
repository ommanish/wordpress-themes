<?php
/**
 * Built-in Nexa Pro settings presets.
 *
 * @package Nexa_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get option keys that presets must preserve from the current site.
 *
 * @return array
 */
function nexa_pro_get_preset_preserved_option_keys() {
	return array_merge(
		nexa_pro_get_media_option_keys(),
		array(
			'brand_name',
			'brand_tagline',
			'footer_brand_text',
			'footer_description',
			'footer_copyright',
			'footer_privacy_label',
			'footer_privacy_url',
			'footer_terms_label',
			'footer_terms_url',
			'contact_email',
			'contact_phone',
			'contact_address',
			'contact_business_hours',
			'social_linkedin_url',
			'social_github_url',
			'social_x_url',
			'social_facebook_url',
			'social_instagram_url',
			'schedule_calendar_url',
		)
	);
}

/**
 * Get the built-in preset registry.
 *
 * @return array
 */
function nexa_pro_get_presets() {
	return array(
		'professional' => array(
			'label'       => __( 'Professional', 'nexa-pro' ),
			'description' => __( 'A balanced consulting-style setup with calm colors, structured sections, and clear service positioning.', 'nexa-pro' ),
			'settings'    => array(
				'color_primary'                   => '#2563eb',
				'color_secondary'                 => '#1d4ed8',
				'color_accent'                    => '#0f766e',
				'color_background'                => '#ffffff',
				'color_surface'                   => '#ffffff',
				'color_text'                      => '#15171a',
				'color_text_muted'                => '#5d6673',
				'color_heading'                   => '#15171a',
				'color_border'                    => '#dce2e8',
				'color_button_primary_background' => '#2563eb',
				'color_button_primary_text'       => '#ffffff',
				'color_button_primary_hover'      => '#1d4ed8',
				'font_body'                       => 'system-ui',
				'font_heading'                    => 'system-ui',
				'font_size_base'                  => 16,
				'line_height_body'                => 1.6,
				'line_height_heading'             => 1.2,
				'font_weight_heading'             => '700',
				'font_weight_button'              => '700',
				'header_layout'                   => 'standard',
				'sticky_header'                   => '1',
				'transparent_header'              => '0',
				'hero_eyebrow'                    => __( 'Professional services', 'nexa-pro' ),
				'hero_heading'                    => __( 'Build trust with a focused business homepage.', 'nexa-pro' ),
				'hero_text'                       => __( 'Present your offer, process, proof points, and next step with a clean structure built for service-led organizations.', 'nexa-pro' ),
				'hero_primary_cta_text'           => __( 'Explore services', 'nexa-pro' ),
				'hero_primary_cta_url'            => '#services',
				'hero_secondary_cta_text'         => __( 'See the process', 'nexa-pro' ),
				'hero_secondary_cta_url'          => '#process',
				'about_label'                     => __( 'About', 'nexa-pro' ),
				'about_heading'                   => __( 'A practical foundation for clear positioning.', 'nexa-pro' ),
				'about_text'                      => __( 'Use this section to explain the organization, audience, and value proposition in plain language.', 'nexa-pro' ),
				'services_heading'                => __( 'Organize services around buyer needs.', 'nexa-pro' ),
				'services_text'                   => __( 'Group core services into concise cards so visitors can quickly understand how to move forward.', 'nexa-pro' ),
				'features_heading'                => __( 'Support the offer with helpful details.', 'nexa-pro' ),
				'process_heading'                 => __( 'Make the path from inquiry to delivery easy to follow.', 'nexa-pro' ),
				'why_heading'                     => __( 'Show why the approach is credible and repeatable.', 'nexa-pro' ),
				'cta_heading'                     => __( 'Ready to turn the page into a working homepage?', 'nexa-pro' ),
				'cta_text'                        => __( 'Replace the starter copy with verified business details, then connect the final call to action to a real next step.', 'nexa-pro' ),
				'cta_button_text'                 => __( 'Start with contact details', 'nexa-pro' ),
				'cta_button_url'                  => '#contact',
				'homepage_section_order'          => nexa_pro_get_default_homepage_section_order(),
			),
		),
		'modern'       => array(
			'label'       => __( 'Modern', 'nexa-pro' ),
			'description' => __( 'A sharper SaaS-ready setup with stronger contrast, centered header layout, and concise product-style messaging.', 'nexa-pro' ),
			'settings'    => array(
				'color_primary'                   => '#0f766e',
				'color_secondary'                 => '#115e59',
				'color_accent'                    => '#2563eb',
				'color_background'                => '#f8fafc',
				'color_surface'                   => '#ffffff',
				'color_text'                      => '#111827',
				'color_text_muted'                => '#4b5563',
				'color_heading'                   => '#0f172a',
				'color_border'                    => '#cbd5e1',
				'color_button_primary_background' => '#0f766e',
				'color_button_primary_text'       => '#ffffff',
				'color_button_primary_hover'      => '#115e59',
				'color_button_secondary_background' => '#ffffff',
				'color_button_secondary_text'     => '#0f172a',
				'color_button_secondary_border'   => '#cbd5e1',
				'font_body'                       => 'trebuchet-ms',
				'font_heading'                    => 'system-ui',
				'font_size_base'                  => 17,
				'line_height_body'                => 1.55,
				'line_height_heading'             => 1.15,
				'font_weight_heading'             => '800',
				'font_weight_button'              => '700',
				'header_layout'                   => 'centered',
				'sticky_header'                   => '1',
				'transparent_header'              => '1',
				'hero_eyebrow'                    => __( 'Modern growth teams', 'nexa-pro' ),
				'hero_heading'                    => __( 'Turn complex offers into a confident homepage flow.', 'nexa-pro' ),
				'hero_text'                       => __( 'Use concise sections, flexible cards, and visible next steps to help visitors understand value without extra noise.', 'nexa-pro' ),
				'services_heading'                => __( 'Package the work into clear service paths.', 'nexa-pro' ),
				'features_heading'                => __( 'Highlight the platform, process, or delivery advantages.', 'nexa-pro' ),
				'process_heading'                 => __( 'Show how momentum builds from discovery to launch.', 'nexa-pro' ),
				'why_heading'                     => __( 'Keep the experience focused, flexible, and ready to extend.', 'nexa-pro' ),
				'cta_heading'                     => __( 'Create a homepage that is ready for real conversion paths.', 'nexa-pro' ),
				'homepage_section_order'          => array( 'services', 'features', 'about', 'process', 'why', 'portfolio', 'testimonials', 'team', 'faq', 'contact', 'cta' ),
			),
		),
		'minimal'      => array(
			'label'       => __( 'Minimal', 'nexa-pro' ),
			'description' => __( 'A restrained editorial setup with fewer active sections, quiet typography, and a lighter content footprint.', 'nexa-pro' ),
			'settings'    => array(
				'color_primary'                   => '#334155',
				'color_secondary'                 => '#475569',
				'color_accent'                    => '#0f766e',
				'color_background'                => '#ffffff',
				'color_surface'                   => '#f8fafc',
				'color_text'                      => '#1f2937',
				'color_text_muted'                => '#64748b',
				'color_heading'                   => '#111827',
				'color_border'                    => '#e2e8f0',
				'color_button_primary_background' => '#334155',
				'color_button_primary_text'       => '#ffffff',
				'color_button_primary_hover'      => '#1f2937',
				'font_body'                       => 'georgia',
				'font_heading'                    => 'georgia',
				'font_size_base'                  => 16,
				'line_height_body'                => 1.7,
				'line_height_heading'             => 1.25,
				'font_weight_heading'             => '700',
				'font_weight_button'              => '600',
				'header_layout'                   => 'standard',
				'sticky_header'                   => '0',
				'transparent_header'              => '0',
				'features_show'                   => '0',
				'why_show'                        => '0',
				'hero_eyebrow'                    => __( 'Simple service homepage', 'nexa-pro' ),
				'hero_heading'                    => __( 'A quieter layout for focused business content.', 'nexa-pro' ),
				'hero_text'                       => __( 'Lead with a clear promise, support it with essential details, and give visitors one practical next step.', 'nexa-pro' ),
				'about_heading'                   => __( 'Keep the message direct and useful.', 'nexa-pro' ),
				'services_heading'                => __( 'Show the core ways people can work with you.', 'nexa-pro' ),
				'process_heading'                 => __( 'Outline the engagement path in a few clear steps.', 'nexa-pro' ),
				'cta_heading'                     => __( 'Make the next step easy to understand.', 'nexa-pro' ),
				'cta_button_text'                 => __( 'Review contact options', 'nexa-pro' ),
				'cta_button_url'                  => '#contact',
				'homepage_section_order'          => array( 'about', 'services', 'process', 'portfolio', 'testimonials', 'team', 'faq', 'contact', 'cta', 'features', 'why' ),
			),
		),
	);
}

/**
 * Get a single preset by ID.
 *
 * @param string $preset_id Preset ID.
 * @return array|null
 */
function nexa_pro_get_preset( $preset_id ) {
	$presets   = nexa_pro_get_presets();
	$preset_id = sanitize_key( $preset_id );

	return isset( $presets[ $preset_id ] ) ? $presets[ $preset_id ] : null;
}
