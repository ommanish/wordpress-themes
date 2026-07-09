<?php
/**
 * Homepage about section.
 *
 * @package Nexa_Pro
 */

$section = isset( $args['section'] ) && is_array( $args['section'] ) ? $args['section'] : array();

if ( empty( $section['id'] ) || empty( $section['heading'] ) ) {
	return;
}
?>

<section id="<?php echo esc_attr( $section['id'] ); ?>" class="homepage-section">
	<div class="nexa-pro-container homepage-split">
		<div>
			<?php nexa_pro_homepage_section_heading( $section ); ?>

			<?php if ( ! empty( $section['points'] ) && is_array( $section['points'] ) ) : ?>
				<ul class="homepage-check-list">
					<?php foreach ( $section['points'] as $point ) : ?>
						<?php if ( empty( $point ) ) : ?>
							<?php continue; ?>
						<?php endif; ?>
						<li><?php echo esc_html( $point ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<?php
		if ( ! empty( $section['image'] ) ) {
			nexa_pro_homepage_image( $section['image'], 'homepage-media' );
		}
		?>
	</div>
</section>

