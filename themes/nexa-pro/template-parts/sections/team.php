<?php
/**
 * Homepage team section.
 *
 * @package Nexa_Pro
 */

$section = isset( $args['section'] ) && is_array( $args['section'] ) ? $args['section'] : array();

if ( empty( $section['id'] ) || empty( $section['heading'] ) || empty( $section['items'] ) || ! is_array( $section['items'] ) ) {
	return;
}

$background_style = nexa_pro_homepage_background_image_style( $section );
$section_class    = 'homepage-section homepage-section--alt';

if ( $background_style ) {
	$section_class .= ' homepage-section--has-background-image';
}
?>

<section id="<?php echo esc_attr( $section['id'] ); ?>" class="<?php echo esc_attr( $section_class ); ?>"<?php echo $background_style ? ' style="' . esc_attr( $background_style ) . '"' : ''; ?>>
	<div class="nexa-pro-container">
		<?php nexa_pro_homepage_section_heading( $section ); ?>

		<div class="homepage-grid homepage-grid--three">
			<?php foreach ( $section['items'] as $item ) : ?>
				<?php if ( empty( $item['title'] ) || empty( $item['text'] ) ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<article class="card homepage-card">
					<h3><?php echo esc_html( $item['title'] ); ?></h3>
					<p><?php echo esc_html( $item['text'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
