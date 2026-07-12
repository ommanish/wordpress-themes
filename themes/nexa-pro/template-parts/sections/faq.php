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

$section_attrs = nexa_pro_homepage_section_attributes( $section, 'homepage-section' );
?>

<section<?php echo $section_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="nexa-pro-container">
		<?php nexa_pro_homepage_section_heading( $section ); ?>

		<div class="homepage-faq">
			<?php foreach ( $section['items'] as $item ) : ?>
				<?php if ( empty( $item['question'] ) || empty( $item['answer'] ) ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<details class="card homepage-faq__item">
					<summary><?php echo esc_html( $item['question'] ); ?></summary>
					<p><?php echo esc_html( $item['answer'] ); ?></p>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>
