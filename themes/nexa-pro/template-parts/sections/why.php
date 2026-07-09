<?php
/**
 * Homepage why section.
 *
 * @package Nexa_Pro
 */

$section = isset( $args['section'] ) && is_array( $args['section'] ) ? $args['section'] : array();

if ( empty( $section['id'] ) || empty( $section['heading'] ) || empty( $section['items'] ) || ! is_array( $section['items'] ) ) {
	return;
}
?>

<section id="<?php echo esc_attr( $section['id'] ); ?>" class="homepage-section">
	<div class="nexa-pro-container homepage-split">
		<?php nexa_pro_homepage_section_heading( $section ); ?>

		<ul class="homepage-check-list">
			<?php foreach ( $section['items'] as $item ) : ?>
				<?php if ( empty( $item ) ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<li><?php echo esc_html( $item ); ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

