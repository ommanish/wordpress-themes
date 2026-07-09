<?php
/**
 * Homepage CTA section.
 *
 * @package Nexa_Pro
 */

$section = isset( $args['section'] ) && is_array( $args['section'] ) ? $args['section'] : array();

if ( empty( $section['heading'] ) || empty( $section['text'] ) ) {
	return;
}
?>

<section class="homepage-section homepage-cta has-nexa-pro-dark-surface">
	<div class="nexa-pro-container homepage-cta__inner">
		<div class="section-heading">
			<h2><?php echo esc_html( $section['heading'] ); ?></h2>
			<p><?php echo esc_html( $section['text'] ); ?></p>
		</div>

		<?php if ( ! empty( $section['action']['label'] ) && ! empty( $section['action']['url'] ) ) : ?>
			<a class="button" href="<?php echo esc_url( $section['action']['url'] ); ?>">
				<?php echo esc_html( $section['action']['label'] ); ?>
			</a>
		<?php endif; ?>
	</div>
</section>

