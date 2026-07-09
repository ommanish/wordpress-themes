<?php
/**
 * Homepage hero section.
 *
 * @package Nexa_Pro
 */

$section = isset( $args['section'] ) && is_array( $args['section'] ) ? $args['section'] : array();

if ( empty( $section['heading'] ) || empty( $section['text'] ) ) {
	return;
}
?>

<section class="homepage-section homepage-hero">
	<div class="nexa-pro-container homepage-hero__inner">
		<div class="homepage-hero__content">
			<?php if ( ! empty( $section['eyebrow'] ) ) : ?>
				<p class="badge"><?php echo esc_html( $section['eyebrow'] ); ?></p>
			<?php endif; ?>

			<h1><?php echo esc_html( $section['heading'] ); ?></h1>
			<p><?php echo esc_html( $section['text'] ); ?></p>

			<?php if ( ! empty( $section['actions'] ) && is_array( $section['actions'] ) ) : ?>
				<div class="section-actions">
					<?php foreach ( $section['actions'] as $action ) : ?>
						<?php
						if ( empty( $action['label'] ) || empty( $action['url'] ) ) {
							continue;
						}

						$button_class = ! empty( $action['style'] ) && 'secondary' === $action['style'] ? 'button button--secondary' : 'button';
						?>
						<a class="<?php echo esc_attr( $button_class ); ?>" href="<?php echo esc_url( $action['url'] ); ?>">
							<?php echo esc_html( $action['label'] ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<?php
		if ( ! empty( $section['image'] ) ) {
			nexa_pro_homepage_image( $section['image'], 'homepage-media', false );
		}
		?>
	</div>
</section>

