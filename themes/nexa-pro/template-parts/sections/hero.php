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

$design = isset( $section['design'] ) && is_array( $section['design'] ) ? $section['design'] : nexa_pro_get_hero_design();
$layout = ! empty( $design['layout'] ) ? sanitize_html_class( $design['layout'] ) : 'centered';
$alignment = ! empty( $design['content_alignment'] ) ? sanitize_html_class( $design['content_alignment'] ) : 'left';
$width = ! empty( $design['content_width'] ) ? sanitize_html_class( $design['content_width'] ) : 'standard';
$image_position = ! empty( $design['image_position'] ) ? sanitize_html_class( $design['image_position'] ) : 'right';
$heading_level = ! empty( $section['heading_level'] ) && 'h2' === sanitize_key( $section['heading_level'] ) ? 'h2' : 'h1';
$base_class = 'homepage-section homepage-hero homepage-hero--' . $layout . ' homepage-hero--align-' . $alignment . ' homepage-hero--width-' . $width . ' homepage-hero--image-position-' . $image_position;

if ( empty( $design['show_image_mobile'] ) ) {
	$base_class .= ' homepage-hero--hide-image-mobile';
}

$section_attrs = nexa_pro_homepage_section_attributes( $section, $base_class, 'background-image' === $layout );
$show_image = in_array( $layout, array( 'split-left', 'split-right', 'image-left', 'image-right' ), true ) && ( ! empty( $design['desktop_image_id'] ) || ! empty( $design['mobile_image_id'] ) );
?>

<section<?php echo $section_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="nexa-pro-container homepage-hero__inner">
		<div class="homepage-hero__content">
			<?php if ( ! empty( $section['eyebrow'] ) ) : ?>
				<p class="badge"><?php echo esc_html( $section['eyebrow'] ); ?></p>
			<?php endif; ?>

			<?php if ( 'h2' === $heading_level ) : ?>
				<h2><?php echo esc_html( $section['heading'] ); ?></h2>
			<?php else : ?>
				<h1><?php echo esc_html( $section['heading'] ); ?></h1>
			<?php endif; ?>
			<p><?php echo esc_html( $section['text'] ); ?></p>

			<?php if ( ! empty( $section['actions'] ) && is_array( $section['actions'] ) ) : ?>
				<div class="section-actions">
					<?php foreach ( $section['actions'] as $action ) : ?>
						<?php
						if ( empty( $action['label'] ) || empty( $action['url'] ) ) {
							continue;
						}

						$button_style = ! empty( $action['style'] ) ? sanitize_html_class( $action['style'] ) : 'primary';
						$button_class = 'primary' === $button_style ? 'button' : 'button button--' . $button_style;
						$action_attrs = nexa_pro_get_action_link_attributes(
							$action['url'],
							array(
								'class' => $button_class,
							)
						);

						if ( '' === $action_attrs ) {
							continue;
						}
						?>
						<a<?php echo $action_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
							<?php echo esc_html( $action['label'] ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<?php
		if ( $show_image ) {
			nexa_pro_homepage_hero_image( $design );
		}
		?>
	</div>
</section>
