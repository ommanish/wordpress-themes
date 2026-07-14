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

$base_class = 'homepage-section homepage-cta';

if ( empty( $section['component_classes'] ) ) {
	$base_class .= ' has-nexa-pro-dark-surface';
}

$section_attrs = nexa_pro_homepage_section_attributes( $section, $base_class );

$action_attrs = '';

if ( ! empty( $section['action']['label'] ) && ! empty( $section['action']['url'] ) ) {
	$action_attrs = nexa_pro_get_action_link_attributes(
		$section['action']['url'],
		array(
			'class' => ! empty( $section['action']['style'] ) && 'primary' !== $section['action']['style'] ? 'button button--' . sanitize_html_class( $section['action']['style'] ) : 'button',
		)
	);
}
?>

<section<?php echo $section_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
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
