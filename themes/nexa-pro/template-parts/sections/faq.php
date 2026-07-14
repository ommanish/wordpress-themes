<?php
/**
 * Homepage FAQ section.
 *
 * @package Nexa_Pro
 */

$section = isset( $args['section'] ) && is_array( $args['section'] ) ? $args['section'] : array();

if ( empty( $section['id'] ) || empty( $section['heading'] ) || empty( $section['items'] ) || ! is_array( $section['items'] ) ) {
	return;
}

$layout      = ! empty( $section['layout'] ) ? sanitize_key( $section['layout'] ) : 'accordion';
$section_id  = sanitize_html_class( $section['id'] );
$section_attrs = nexa_pro_homepage_section_attributes( $section, 'homepage-section homepage-faq-section homepage-faq-section--' . sanitize_html_class( $layout ) );
?>

<section<?php echo $section_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="nexa-pro-container">
		<?php nexa_pro_homepage_section_heading( $section ); ?>

		<div class="homepage-faq homepage-faq--<?php echo esc_attr( sanitize_html_class( $layout ) ); ?>">
			<?php foreach ( $section['items'] as $index => $item ) : ?>
				<?php if ( empty( $item['question'] ) || empty( $item['answer'] ) ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<?php
				$item_index = absint( $index ) + 1;
				$panel_id   = $section_id . '-faq-panel-' . $item_index;
				$button_id  = $section_id . '-faq-button-' . $item_index;
				?>
				<div class="card homepage-faq__item">
					<button id="<?php echo esc_attr( $button_id ); ?>" class="homepage-faq__question" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $panel_id ); ?>" data-nexa-pro-faq-toggle>
						<?php echo esc_html( $item['question'] ); ?>
					</button>
					<div id="<?php echo esc_attr( $panel_id ); ?>" class="homepage-faq__answer" role="region" aria-labelledby="<?php echo esc_attr( $button_id ); ?>">
						<p><?php echo esc_html( $item['answer'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
