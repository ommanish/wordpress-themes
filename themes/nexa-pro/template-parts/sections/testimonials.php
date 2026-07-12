<?php
/**
 * Homepage testimonials section.
 *
 * @package Nexa_Pro
 */

$section = isset( $args['section'] ) && is_array( $args['section'] ) ? $args['section'] : array();

if ( empty( $section['id'] ) || empty( $section['heading'] ) || empty( $section['message'] ) ) {
	return;
}

$section_attrs = nexa_pro_homepage_section_attributes( $section, 'homepage-section' );
?>

<section<?php echo $section_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="nexa-pro-container">
		<?php nexa_pro_homepage_section_heading( $section ); ?>

		<div class="card homepage-note">
			<p><?php echo esc_html( $section['message'] ); ?></p>
		</div>
	</div>
</section>
