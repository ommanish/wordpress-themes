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

$background_style = nexa_pro_homepage_background_image_style( $section );
$section_class    = 'homepage-section';

if ( $background_style ) {
	$section_class .= ' homepage-section--has-background-image';
}
?>

<section id="<?php echo esc_attr( $section['id'] ); ?>" class="<?php echo esc_attr( $section_class ); ?>"<?php echo $background_style ? ' style="' . esc_attr( $background_style ) . '"' : ''; ?>>
	<div class="nexa-pro-container">
		<?php nexa_pro_homepage_section_heading( $section ); ?>

		<div class="card homepage-note">
			<p><?php echo esc_html( $section['message'] ); ?></p>
		</div>
	</div>
</section>
