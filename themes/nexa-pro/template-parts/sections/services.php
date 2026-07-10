<?php
/**
 * Homepage services section.
 *
 * @package Nexa_Pro
 */

$section = isset( $args['section'] ) && is_array( $args['section'] ) ? $args['section'] : array();

if ( empty( $section['id'] ) || empty( $section['heading'] ) ) {
	return;
}

$items = isset( $section['items'] ) && is_array( $section['items'] ) ? $section['items'] : array();
$background_style = nexa_pro_homepage_background_image_style( $section );
$section_class    = 'homepage-section homepage-section--alt';

if ( $background_style ) {
	$section_class .= ' homepage-section--has-background-image';
}
?>

<section id="<?php echo esc_attr( $section['id'] ); ?>" class="<?php echo esc_attr( $section_class ); ?>"<?php echo $background_style ? ' style="' . esc_attr( $background_style ) . '"' : ''; ?>>
	<div class="nexa-pro-container">
		<?php nexa_pro_homepage_section_heading( $section ); ?>

		<?php if ( $items ) : ?>
			<div class="homepage-grid homepage-grid--three">
			<?php foreach ( $items as $item ) : ?>
				<?php if ( empty( $item['title'] ) ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<article class="card homepage-card">
					<h3><?php echo esc_html( $item['title'] ); ?></h3>
					<?php if ( ! empty( $item['text'] ) ) : ?>
						<p><?php echo esc_html( $item['text'] ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $item['link_text'] ) && ! empty( $item['link_url'] ) && '' !== esc_url( $item['link_url'] ) ) : ?>
						<p><a href="<?php echo esc_url( $item['link_url'] ); ?>"><?php echo esc_html( $item['link_text'] ); ?></a></p>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
