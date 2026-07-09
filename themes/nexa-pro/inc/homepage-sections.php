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
	return array(
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
				'id'  => 0,
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
			'eyebrow'  => __( 'About the theme', 'nexa-pro' ),
			'heading'  => __( 'Designed for teams that need clarity before decoration.', 'nexa-pro' ),
			'text'     => __( 'Nexa Pro organizes core business content into readable sections, reusable cards, and focused calls to action so site owners can explain what they do without wrestling the layout.', 'nexa-pro' ),
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
			'eyebrow'  => __( 'Services', 'nexa-pro' ),
			'heading'  => __( 'Present services with concise, scannable cards.', 'nexa-pro' ),
			'text'     => __( 'Use this area to introduce the main ways a business helps its customers.', 'nexa-pro' ),
			'items'    => array(
				array(
					'title' => __( 'Advisory services', 'nexa-pro' ),
					'text'  => __( 'Frame expert guidance, planning, audits, or retained consulting offers.', 'nexa-pro' ),
				),
				array(
					'title' => __( 'Implementation support', 'nexa-pro' ),
					'text'  => __( 'Describe hands-on delivery, setup, migration, or operational support.', 'nexa-pro' ),
				),
				array(
					'title' => __( 'Ongoing optimization', 'nexa-pro' ),
					'text'  => __( 'Explain recurring improvement, reporting, enablement, or growth services.', 'nexa-pro' ),
				),
			),
		),
		'features'     => array(
			'template' => 'features',
			'eyebrow'  => __( 'Features', 'nexa-pro' ),
			'heading'  => __( 'Reusable patterns for professional business pages.', 'nexa-pro' ),
			'items'    => array(
				array(
					'title' => __( 'Structured sections', 'nexa-pro' ),
					'text'  => __( 'Clear section boundaries help visitors move from context to action.', 'nexa-pro' ),
				),
				array(
					'title' => __( 'Flexible cards', 'nexa-pro' ),
					'text'  => __( 'Card grids work for services, benefits, team profiles, and resource previews.', 'nexa-pro' ),
				),
				array(
					'title' => __( 'Accessible defaults', 'nexa-pro' ),
					'text'  => __( 'Headings, links, focus states, and layout foundations are designed with accessibility in mind.', 'nexa-pro' ),
				),
			),
		),
		'process'      => array(
			'template' => 'process',
			'id'       => 'process',
			'eyebrow'  => __( 'Process', 'nexa-pro' ),
			'heading'  => __( 'Show how work moves from first conversation to next step.', 'nexa-pro' ),
			'items'    => array(
				array(
					'title' => __( 'Discover', 'nexa-pro' ),
					'text'  => __( 'Clarify goals, audience needs, constraints, and the most important user journeys.', 'nexa-pro' ),
				),
				array(
					'title' => __( 'Shape', 'nexa-pro' ),
					'text'  => __( 'Organize content, page structure, calls to action, and proof points.', 'nexa-pro' ),
				),
				array(
					'title' => __( 'Launch', 'nexa-pro' ),
					'text'  => __( 'Publish a focused experience and prepare the next round of improvements.', 'nexa-pro' ),
				),
			),
		),
		'why'          => array(
			'template' => 'why',
			'id'       => 'why',
			'eyebrow'  => __( 'Why Nexa Pro', 'nexa-pro' ),
			'heading'  => __( 'A calm foundation for serious business content.', 'nexa-pro' ),
			'items'    => array(
				__( 'Built with reusable WordPress template parts.', 'nexa-pro' ),
				__( 'Prepared for future homepage settings without coupling content to templates.', 'nexa-pro' ),
				__( 'Uses the existing design-token system for consistent spacing, color, and type.', 'nexa-pro' ),
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
		),
		'testimonials' => array(
			'template' => 'testimonials',
			'id'       => 'testimonials',
			'eyebrow'  => __( 'Testimonials', 'nexa-pro' ),
			'heading'  => __( 'Add real customer feedback when the site is ready.', 'nexa-pro' ),
			'message'  => __( 'This section is prepared for testimonials. Replace this setup note with authentic customer feedback before launch.', 'nexa-pro' ),
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
		),
		'cta'          => array(
			'template' => 'cta',
			'heading'  => __( 'Ready to shape the homepage around real content?', 'nexa-pro' ),
			'text'     => __( 'Use this foundation as the starting point, then replace the temporary defaults with verified details from the business.', 'nexa-pro' ),
			'action'   => array(
				'label' => __( 'Start with contact details', 'nexa-pro' ),
				'url'   => '#contact',
			),
		),
	);
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
