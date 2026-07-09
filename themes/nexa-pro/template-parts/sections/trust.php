<?php
/**
 * Homepage trust section.
 *
 * @package Nexa_Pro
 */

$section = isset( $args['section'] ) && is_array( $args['section'] ) ? $args['section'] : array();

if ( empty( $section['heading'] ) || empty( $section['items'] ) || ! is_array( $section['items'] ) ) {
	return;
}
?>

<section class="homepage-section homepage-section--compact">
	<div class="nexa-pro-container">
		<h2 class="screen-reader-text"><?php echo esc_html( $section['heading'] ); ?></h2>
		<ul class="homepage-inline-list" aria-label="<?php echo esc_attr( $section['heading'] ); ?>">
			<?php foreach ( $section['items'] as $item ) : ?>
				<?php if ( empty( $item ) ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<li><?php echo esc_html( $item ); ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

