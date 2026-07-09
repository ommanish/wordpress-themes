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
?>

<section id="<?php echo esc_attr( $section['id'] ); ?>" class="homepage-section">
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

