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
$section_attrs = nexa_pro_homepage_section_attributes( $section, 'homepage-section homepage-section--alt' );
?>

<section<?php echo $section_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
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
