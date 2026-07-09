<?php
/**
 * Homepage process section.
 *
 * @package Nexa_Pro
 */

$section = isset( $args['section'] ) && is_array( $args['section'] ) ? $args['section'] : array();

if ( empty( $section['id'] ) || empty( $section['heading'] ) || empty( $section['items'] ) || ! is_array( $section['items'] ) ) {
	return;
}
?>

<section id="<?php echo esc_attr( $section['id'] ); ?>" class="homepage-section homepage-section--alt">
	<div class="nexa-pro-container">
		<?php nexa_pro_homepage_section_heading( $section ); ?>

		<ol class="homepage-grid homepage-grid--three homepage-steps">
			<?php foreach ( $section['items'] as $item ) : ?>
				<?php if ( empty( $item['title'] ) || empty( $item['text'] ) ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<li class="card homepage-card">
					<h3><?php echo esc_html( $item['title'] ); ?></h3>
					<p><?php echo esc_html( $item['text'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>

