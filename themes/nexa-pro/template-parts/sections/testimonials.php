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
?>

<section id="<?php echo esc_attr( $section['id'] ); ?>" class="homepage-section">
	<div class="nexa-pro-container">
		<?php nexa_pro_homepage_section_heading( $section ); ?>

		<div class="card homepage-note">
			<p><?php echo esc_html( $section['message'] ); ?></p>
		</div>
	</div>
</section>

