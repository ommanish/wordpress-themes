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

$section_attrs = nexa_pro_homepage_section_attributes( $section, 'homepage-section' );
?>

<section<?php echo $section_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
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
