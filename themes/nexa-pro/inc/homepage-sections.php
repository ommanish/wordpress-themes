<?php
/**
 * Homepage section data and rendering.
 *
 * @package Nexa_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get fixed homepage section data.
 *
 * @return array
 */
function nexa_pro_get_homepage_sections() {
	$sections = array(
		'hero'         => array(
			'template' => 'hero',
			'eyebrow'  => nexa_pro_get_option( 'hero_eyebrow' ),
			'heading'  => nexa_pro_get_option( 'hero_heading' ),
			'text'     => nexa_pro_get_option( 'hero_text' ),
			'actions'  => array(
				array(
					'label' => nexa_pro_get_option( 'hero_primary_cta_text' ),
					'url'   => nexa_pro_get_option( 'hero_primary_cta_url' ),
					'style' => 'primary',
				),
				array(
					'label' => nexa_pro_get_option( 'hero_secondary_cta_text' ),
					'url'   => nexa_pro_get_option( 'hero_secondary_cta_url' ),
					'style' => 'secondary',
				),
			),
			'image'    => array(
				'id'  => nexa_pro_get_image_attachment_id( 'about_image_id' ),
				'url' => '',
				'alt' => '',
			),
		),
		'trust'        => array(
			'template' => 'trust',
			'heading'  => __( 'Built around practical publishing needs.', 'nexa-pro' ),
			'items'    => array(
				__( 'Accessible structure from the first screen.', 'nexa-pro' ),
				__( 'Responsive layouts for service-focused content.', 'nexa-pro' ),
				__( 'Editor-aligned design tokens for consistent publishing.', 'nexa-pro' ),
			),
		),
		'about'        => array(
			'template' => 'about',
			'id'       => 'about',
			'eyebrow'  => nexa_pro_get_raw_option( 'about_label' ),
			'heading'  => nexa_pro_get_raw_option( 'about_heading' ),
			'text'     => nexa_pro_get_raw_option( 'about_text' ),
			'points'   => array(
				__( 'Semantic templates for strong content hierarchy.', 'nexa-pro' ),
				__( 'Design tokens shared across front end and editor.', 'nexa-pro' ),
				__( 'Flexible sections prepared for future settings controls.', 'nexa-pro' ),
			),
			'image'    => array(
				'id'  => 0,
				'url' => '',
				'alt' => '',
			),
		),
		'services'     => array(
			'template' => 'services',
			'id'       => 'services',
			'eyebrow'  => nexa_pro_get_raw_option( 'services_label' ),
			'heading'  => nexa_pro_get_raw_option( 'services_heading' ),
			'text'     => nexa_pro_get_raw_option( 'services_text' ),
			'items'    => nexa_pro_get_services_items(),
			'background_image_id' => nexa_pro_get_image_attachment_id( 'services_background_image_id' ),
		),
		'features'     => array(
			'template' => 'features',
			'eyebrow'  => nexa_pro_get_raw_option( 'features_label' ),
			'heading'  => nexa_pro_get_raw_option( 'features_heading' ),
			'text'     => nexa_pro_get_raw_option( 'features_text' ),
			'items'    => nexa_pro_get_features_items(),
			'background_image_id' => nexa_pro_get_image_attachment_id( 'features_background_image_id' ),
		),
		'process'      => array(
			'template' => 'process',
			'id'       => 'process',
			'eyebrow'  => nexa_pro_get_raw_option( 'process_label' ),
			'heading'  => nexa_pro_get_raw_option( 'process_heading' ),
			'text'     => nexa_pro_get_raw_option( 'process_text' ),
			'items'    => nexa_pro_get_process_items(),
			'background_image_id' => nexa_pro_get_image_attachment_id( 'process_background_image_id' ),
		),
		'why'          => array(
			'template' => 'why',
			'id'       => 'why',
			'eyebrow'  => nexa_pro_get_raw_option( 'why_label' ),
			'heading'  => nexa_pro_get_raw_option( 'why_heading' ),
			'text'     => nexa_pro_get_raw_option( 'why_text' ),
			'items'    => nexa_pro_get_why_items(),
			'image'    => array(
				'id'  => nexa_pro_get_image_attachment_id( 'why_image_id' ),
				'url' => '',
				'alt' => '',
			),
		),
		'portfolio'    => array(
			'template' => 'portfolio',
			'id'       => 'portfolio',
			'eyebrow'  => __( 'Portfolio', 'nexa-pro' ),
			'heading'  => __( 'Frame example engagement types without implying client outcomes.', 'nexa-pro' ),
			'items'    => array(
				array(
					'title' => __( 'Strategy engagement', 'nexa-pro' ),
					'text'  => __( 'A neutral example for planning, positioning, or operational strategy work.', 'nexa-pro' ),
				),
				array(
					'title' => __( 'Product experience', 'nexa-pro' ),
					'text'  => __( 'A neutral example for product, service, or digital experience presentation.', 'nexa-pro' ),
				),
				array(
					'title' => __( 'Growth program', 'nexa-pro' ),
					'text'  => __( 'A neutral example for ongoing campaigns, enablement, or improvement programs.', 'nexa-pro' ),
				),
			),
			'image'    => array(
				'id'  => nexa_pro_get_image_attachment_id( 'portfolio_image_id' ),
				'url' => '',
				'alt' => '',
			),
		),
		'testimonials' => array(
			'template' => 'testimonials',
			'id'       => 'testimonials',
			'eyebrow'  => __( 'Testimonials', 'nexa-pro' ),
			'heading'  => __( 'Add real customer feedback when the site is ready.', 'nexa-pro' ),
			'message'  => __( 'This section is prepared for testimonials. Replace this setup note with authentic customer feedback before launch.', 'nexa-pro' ),
			'background_image_id' => nexa_pro_get_image_attachment_id( 'testimonials_background_image_id' ),
		),
		'team'         => array(
			'template' => 'team',
			'id'       => 'team',
			'eyebrow'  => __( 'Team', 'nexa-pro' ),
			'heading'  => __( 'Introduce the people behind the work.', 'nexa-pro' ),
			'items'    => array(
				array(
					'title' => __( 'Leadership', 'nexa-pro' ),
					'text'  => __( 'Use this card for the person responsible for direction and client relationships.', 'nexa-pro' ),
				),
				array(
					'title' => __( 'Delivery', 'nexa-pro' ),
					'text'  => __( 'Use this card for the people responsible for implementation and quality.', 'nexa-pro' ),
				),
				array(
					'title' => __( 'Support', 'nexa-pro' ),
					'text'  => __( 'Use this card for operations, onboarding, or customer support roles.', 'nexa-pro' ),
				),
			),
			'background_image_id' => nexa_pro_get_image_attachment_id( 'team_background_image_id' ),
		),
		'faq'          => array(
			'template' => 'faq',
			'id'       => 'faq',
			'eyebrow'  => __( 'FAQ', 'nexa-pro' ),
			'heading'  => __( 'Answer common questions before the first contact.', 'nexa-pro' ),
			'items'    => array(
				array(
					'question' => __( 'Can these sections be customized later?', 'nexa-pro' ),
					'answer'   => __( 'Yes. This architecture keeps section data separate from templates so future settings can control content without rewriting markup.', 'nexa-pro' ),
				),
				array(
					'question' => __( 'Does this add plugin functionality?', 'nexa-pro' ),
					'answer'   => __( 'No. These are theme presentation sections only. Custom post types and persistent business content belong in the companion plugin.', 'nexa-pro' ),
				),
				array(
					'question' => __( 'Should sample content ship unchanged?', 'nexa-pro' ),
					'answer'   => __( 'No. Site owners should replace the temporary defaults with accurate organization-specific content before launch.', 'nexa-pro' ),
				),
			),
		),
		'contact'      => array(
			'template' => 'contact',
			'id'       => 'contact',
			'eyebrow'  => __( 'Contact', 'nexa-pro' ),
			'heading'  => __( 'Create a clear next step for qualified conversations.', 'nexa-pro' ),
			'text'     => __( 'Use this section to describe how visitors should start a conversation. Replace the neutral contact details with accurate information before launch.', 'nexa-pro' ),
			'items'    => array(
				__( 'Response window: add your preferred timeframe.', 'nexa-pro' ),
				__( 'Location: add your service area or office details.', 'nexa-pro' ),
				__( 'Contact method: add a verified form, phone number, or email address.', 'nexa-pro' ),
			),
			'background_image_id' => nexa_pro_get_image_attachment_id( 'contact_background_image_id' ),
		),
		'cta'          => array(
			'template' => 'cta',
			'heading'  => nexa_pro_get_raw_option( 'cta_heading' ),
			'text'     => nexa_pro_get_raw_option( 'cta_text' ),
			'action'   => array(
				'label' => nexa_pro_get_raw_option( 'cta_button_text' ),
				'url'   => nexa_pro_get_valid_homepage_url_option( 'cta_button_url' ),
			),
			'background_image_id' => nexa_pro_get_image_attachment_id( 'cta_background_image_id' ),
		),
	);

	return nexa_pro_filter_visible_homepage_sections( nexa_pro_order_homepage_sections( $sections ) );
}

/**
 * Order homepage sections while keeping hero and trust fixed.
 *
 * @param array $sections Homepage section data.
 * @return array
 */
function nexa_pro_order_homepage_sections( $sections ) {
	$ordered        = array();
	$fixed_sections = array( 'hero', 'trust' );

	foreach ( $fixed_sections as $section_key ) {
		if ( isset( $sections[ $section_key ] ) ) {
			$ordered[ $section_key ] = $sections[ $section_key ];
		}
	}

	foreach ( nexa_pro_get_homepage_section_order() as $section_key ) {
		if ( isset( $sections[ $section_key ] ) && ! isset( $ordered[ $section_key ] ) ) {
			$ordered[ $section_key ] = $sections[ $section_key ];
		}
	}

	foreach ( $sections as $section_key => $section ) {
		if ( ! isset( $ordered[ $section_key ] ) ) {
			$ordered[ $section_key ] = $section;
		}
	}

	return $ordered;
}

/**
 * Determine whether a homepage section is enabled.
 *
 * @param string $section Section key.
 * @return bool
 */
function nexa_pro_homepage_section_is_enabled( $section ) {
	$visibility_options = array(
		'about'    => 'about_show',
		'services' => 'services_show',
		'features' => 'features_show',
		'process'  => 'process_show',
		'why'      => 'why_show',
		'cta'      => 'cta_show',
	);

	if ( ! isset( $visibility_options[ $section ] ) ) {
		return true;
	}

	return '1' === (string) nexa_pro_get_option( $visibility_options[ $section ], '1' );
}

/**
 * Remove disabled homepage sections from the fixed render order.
 *
 * @param array $sections Homepage section data.
 * @return array
 */
function nexa_pro_filter_visible_homepage_sections( $sections ) {
	foreach ( array_keys( $sections ) as $section_key ) {
		if ( ! nexa_pro_homepage_section_is_enabled( $section_key ) ) {
			unset( $sections[ $section_key ] );
		}
	}

	return $sections;
}

/**
 * Get a homepage URL option only when it can render as a safe href.
 *
 * @param string $key Option key.
 * @return string
 */
function nexa_pro_get_valid_homepage_url_option( $key ) {
	$url = nexa_pro_get_raw_option( $key, '' );

	return '' !== esc_url( $url ) ? $url : '';
}

/**
 * Get a safe inline style value for a homepage background image.
 *
 * @param array $section Section data.
 * @return string
 */
function nexa_pro_homepage_background_image_style( $section ) {
	if ( empty( $section['background_image_id'] ) ) {
		return '';
	}

	$image_id = absint( $section['background_image_id'] );

	if ( ! nexa_pro_is_valid_image_attachment_id( $image_id ) ) {
		return '';
	}

	$image_url = wp_get_attachment_image_url( $image_id, 'large' );

	if ( ! $image_url ) {
		return '';
	}

	return '--nexa-pro-section-background-image: url("' . esc_url( $image_url ) . '");';
}

/**
 * Render homepage sections in their fixed order.
 *
 * @return void
 */
function nexa_pro_render_homepage_sections() {
	$sections = nexa_pro_get_homepage_sections();

	foreach ( $sections as $section ) {
		if ( empty( $section['template'] ) ) {
			continue;
		}

		get_template_part(
			'template-parts/sections/' . sanitize_key( $section['template'] ),
			null,
			array(
				'section' => $section,
			)
		);
	}
}

/**
 * Render an optional homepage section heading.
 *
 * @param array $section Section data.
 * @return void
 */
function nexa_pro_homepage_section_heading( $section ) {
	if ( empty( $section['heading'] ) ) {
		return;
	}

	?>
	<div class="section-heading">
		<?php if ( ! empty( $section['eyebrow'] ) ) : ?>
			<p class="section-heading__eyebrow"><?php echo esc_html( $section['eyebrow'] ); ?></p>
		<?php endif; ?>
		<h2><?php echo esc_html( $section['heading'] ); ?></h2>
		<?php if ( ! empty( $section['text'] ) ) : ?>
			<p><?php echo esc_html( $section['text'] ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Render an optional homepage image.
 *
 * @param array  $image Image data.
 * @param string $class Image wrapper class.
 * @param bool   $lazy Whether URL images should lazy-load.
 * @return void
 */
function nexa_pro_homepage_image( $image, $class = 'homepage-media', $lazy = true ) {
	if ( empty( $image ) || ! is_array( $image ) ) {
		return;
	}

	if ( ! empty( $image['id'] ) ) {
		$image_html = wp_get_attachment_image(
			absint( $image['id'] ),
			'large',
			false,
			array(
				'class'   => $class . '__image',
				'loading' => $lazy ? 'lazy' : 'eager',
			)
		);

		if ( $image_html ) {
			printf( '<figure class="%1$s">%2$s</figure>', esc_attr( $class ), $image_html );
		}

		return;
	}

	if ( empty( $image['url'] ) || empty( $image['alt'] ) ) {
		return;
	}

	?>
	<figure class="<?php echo esc_attr( $class ); ?>">
		<img
			class="<?php echo esc_attr( $class . '__image' ); ?>"
			src="<?php echo esc_url( $image['url'] ); ?>"
			alt="<?php echo esc_attr( $image['alt'] ); ?>"
			<?php echo $lazy ? 'loading="lazy"' : ''; ?>
		>
	</figure>
	<?php
}
