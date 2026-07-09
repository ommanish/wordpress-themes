<?php
/**
 * Homepage contact section.
 *
 * @package Nexa_Pro
 */

$section = isset( $args['section'] ) && is_array( $args['section'] ) ? $args['section'] : array();

if ( empty( $section['id'] ) || empty( $section['heading'] ) || empty( $section['items'] ) || ! is_array( $section['items'] ) ) {
	return;
}
?>

<section id="<?php echo esc_attr( $section['id'] ); ?>" class="homepage-section homepage-section--alt">
	<div class="nexa-pro-container homepage-split">
		<?php nexa_pro_homepage_section_heading( $section ); ?>

		<div class="card">
			<h3><?php esc_html_e( 'Setup checklist', 'nexa-pro' ); ?></h3>
			<ul class="homepage-check-list">
				<?php foreach ( $section['items'] as $item ) : ?>
					<?php if ( empty( $item ) ) : ?>
						<?php continue; ?>
					<?php endif; ?>
					<li><?php echo esc_html( $item ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</section>

