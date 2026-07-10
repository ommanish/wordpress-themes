<?php
/**
 * Homepage why section.
 *
 * @package Nexa_Pro
 */

$section = isset( $args['section'] ) && is_array( $args['section'] ) ? $args['section'] : array();

if ( empty( $section['id'] ) || empty( $section['heading'] ) ) {
	return;
}

$items = isset( $section['items'] ) && is_array( $section['items'] ) ? $section['items'] : array();
?>

<section id="<?php echo esc_attr( $section['id'] ); ?>" class="homepage-section">
	<div class="nexa-pro-container homepage-split">
		<?php nexa_pro_homepage_section_heading( $section ); ?>

		<?php if ( $items ) : ?>
			<ul class="homepage-check-list">
			<?php foreach ( $items as $item ) : ?>
				<?php if ( empty( $item['title'] ) ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<li>
					<?php echo esc_html( $item['title'] ); ?>
					<?php if ( ! empty( $item['text'] ) ) : ?>
						<span><?php echo esc_html( $item['text'] ); ?></span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
