<?php
/**
 * Homepage process section.
 *
 * @package Nexa_Pro
 */

$section = isset( $args['section'] ) && is_array( $args['section'] ) ? $args['section'] : array();

if ( empty( $section['id'] ) || empty( $section['heading'] ) ) {
	return;
}

$items = isset( $section['items'] ) && is_array( $section['items'] ) ? $section['items'] : array();
$section_attrs = nexa_pro_homepage_section_attributes( $section, 'homepage-section homepage-section--alt' );
?>

<section<?php echo $section_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="nexa-pro-container">
		<?php nexa_pro_homepage_section_heading( $section ); ?>

		<?php if ( $items ) : ?>
			<ol class="homepage-grid homepage-grid--three homepage-steps">
			<?php foreach ( $items as $item ) : ?>
				<?php if ( empty( $item['title'] ) ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<li class="card homepage-card">
					<h3><?php echo esc_html( $item['title'] ); ?></h3>
					<?php if ( ! empty( $item['text'] ) ) : ?>
						<p><?php echo esc_html( $item['text'] ); ?></p>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
			</ol>
		<?php endif; ?>
	</div>
</section>
