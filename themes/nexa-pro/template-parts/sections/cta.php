<?php
/**
 * Homepage CTA section.
 *
 * @package Nexa_Pro
 */

$section = isset( $args['section'] ) && is_array( $args['section'] ) ? $args['section'] : array();

if ( empty( $section['heading'] ) || empty( $section['text'] ) ) {
	return;
}

$background_style = nexa_pro_homepage_background_image_style( $section );
$section_class    = 'homepage-section homepage-cta has-nexa-pro-dark-surface';

if ( $background_style ) {
	$section_class .= ' homepage-section--has-background-image';
}

$action_attrs = '';

if ( ! empty( $section['action']['label'] ) && ! empty( $section['action']['url'] ) ) {
	$action_attrs = nexa_pro_get_action_link_attributes(
		$section['action']['url'],
		array(
			'class' => 'button',
		)
	);
}
?>

<section class="<?php echo esc_attr( $section_class ); ?>"<?php echo $background_style ? ' style="' . esc_attr( $background_style ) . '"' : ''; ?>>
	<div class="nexa-pro-container homepage-cta__inner">
		<div class="section-heading">
			<h2><?php echo esc_html( $section['heading'] ); ?></h2>
			<p><?php echo esc_html( $section['text'] ); ?></p>
		</div>

		<?php if ( $action_attrs ) : ?>
			<a<?php echo $action_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<?php echo esc_html( $section['action']['label'] ); ?>
			</a>
		<?php endif; ?>
	</div>
</section>
