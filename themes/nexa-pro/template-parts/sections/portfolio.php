<?php
/**
 * Homepage portfolio section.
 *
 * @package Nexa_Pro
 */

$section = isset( $args['section'] ) && is_array( $args['section'] ) ? $args['section'] : array();

if ( empty( $section['id'] ) || empty( $section['heading'] ) || empty( $section['items'] ) || ! is_array( $section['items'] ) ) {
	return;
}

$section_attrs = nexa_pro_homepage_section_attributes( $section, 'homepage-section homepage-section--alt' );
?>

<section<?php echo $section_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="nexa-pro-container">
		<?php nexa_pro_homepage_section_heading( $section ); ?>

		<?php
		if ( ! empty( $section['image'] ) ) {
			nexa_pro_homepage_image( $section['image'], 'homepage-media' );
		}
		?>

		<div class="homepage-grid homepage-grid--three">
			<?php foreach ( $section['items'] as $item ) : ?>
				<?php if ( empty( $item['title'] ) || empty( $item['text'] ) ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<article class="card homepage-card">
					<p class="badge"><?php esc_html_e( 'Example type', 'nexa-pro' ); ?></p>
					<h3><?php echo esc_html( $item['title'] ); ?></h3>
					<p><?php echo esc_html( $item['text'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
