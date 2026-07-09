<?php
/**
 * Admin field renderers.
 *
 * @package Nexa_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render a text-like admin field.
 *
 * @param string $key Option key.
 * @param string $label Field label.
 * @param string $description Field description.
 * @param string $type Input type.
 * @return void
 */
function nexa_pro_admin_text_field( $key, $label, $description = '', $type = 'text' ) {
	$value    = nexa_pro_get_option( $key, '' );
	$field_id = 'nexa-pro-' . str_replace( '_', '-', $key );

	?>
	<tr>
		<th scope="row">
			<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $label ); ?></label>
		</th>
		<td>
			<input
				type="<?php echo esc_attr( $type ); ?>"
				id="<?php echo esc_attr( $field_id ); ?>"
				name="nexa_pro_options[<?php echo esc_attr( $key ); ?>]"
				value="<?php echo esc_attr( $value ); ?>"
				class="regular-text"
			>
			<?php if ( $description ) : ?>
				<p class="description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</td>
	</tr>
	<?php
}

/**
 * Render a textarea admin field.
 *
 * @param string $key Option key.
 * @param string $label Field label.
 * @param string $description Field description.
 * @return void
 */
function nexa_pro_admin_textarea_field( $key, $label, $description = '' ) {
	$value    = nexa_pro_get_option( $key, '' );
	$field_id = 'nexa-pro-' . str_replace( '_', '-', $key );

	?>
	<tr>
		<th scope="row">
			<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $label ); ?></label>
		</th>
		<td>
			<textarea
				id="<?php echo esc_attr( $field_id ); ?>"
				name="nexa_pro_options[<?php echo esc_attr( $key ); ?>]"
				class="large-text"
				rows="4"
			><?php echo esc_textarea( $value ); ?></textarea>
			<?php if ( $description ) : ?>
				<p class="description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</td>
	</tr>
	<?php
}

/**
 * Render a color admin field.
 *
 * @param string $key Option key.
 * @param string $label Field label.
 * @param string $description Field description.
 * @return void
 */
function nexa_pro_admin_color_field( $key, $label, $description = '' ) {
	nexa_pro_admin_text_field( $key, $label, $description, 'text' );
}

/**
 * Render admin fields for a tab.
 *
 * @param string $tab Active tab.
 * @return void
 */
function nexa_pro_render_admin_fields( $tab ) {
	?>
	<table class="form-table" role="presentation">
		<tbody>
			<?php
			switch ( $tab ) {
				case 'global':
					nexa_pro_admin_text_field(
						'brand_name',
						__( 'Brand name', 'nexa-pro' ),
						__( 'Appears in theme-controlled brand areas when intentionally populated. Falls back to the WordPress site title.', 'nexa-pro' )
					);
					nexa_pro_admin_text_field(
						'brand_tagline',
						__( 'Brand tagline', 'nexa-pro' ),
						__( 'Appears in theme-controlled brand areas when intentionally populated. Falls back to the WordPress tagline.', 'nexa-pro' )
					);
					nexa_pro_admin_color_field(
						'primary_color',
						__( 'Primary color', 'nexa-pro' ),
						__( 'Use a hex color such as #2563eb. This controls the primary front-end color token.', 'nexa-pro' )
					);
					nexa_pro_admin_color_field(
						'accent_color',
						__( 'Accent color', 'nexa-pro' ),
						__( 'Use a hex color such as #0f766e. This controls the accent front-end color token.', 'nexa-pro' )
					);
					break;

				case 'header':
					nexa_pro_admin_text_field(
						'header_cta_text',
						__( 'Header CTA text', 'nexa-pro' ),
						__( 'Prepared for the header call-to-action area. Leave blank to hide until the header layout supports it.', 'nexa-pro' )
					);
					nexa_pro_admin_text_field(
						'header_cta_url',
						__( 'Header CTA URL', 'nexa-pro' ),
						__( 'Use a full absolute URL or a same-page fragment such as #contact.', 'nexa-pro' )
					);
					break;

				case 'hero':
					nexa_pro_admin_text_field(
						'hero_eyebrow',
						__( 'Hero eyebrow', 'nexa-pro' ),
						__( 'Appears above the homepage hero heading.', 'nexa-pro' )
					);
					nexa_pro_admin_text_field(
						'hero_heading',
						__( 'Hero heading', 'nexa-pro' ),
						__( 'Appears as the homepage H1. Keep it concise to preserve the page hierarchy.', 'nexa-pro' )
					);
					nexa_pro_admin_textarea_field(
						'hero_text',
						__( 'Hero text', 'nexa-pro' ),
						__( 'Appears below the homepage hero heading.', 'nexa-pro' )
					);
					nexa_pro_admin_text_field(
						'hero_primary_cta_text',
						__( 'Primary CTA text', 'nexa-pro' ),
						__( 'Appears as the first homepage hero button.', 'nexa-pro' )
					);
					nexa_pro_admin_text_field(
						'hero_primary_cta_url',
						__( 'Primary CTA URL', 'nexa-pro' ),
						__( 'Use a full absolute URL or a same-page fragment such as #services.', 'nexa-pro' )
					);
					nexa_pro_admin_text_field(
						'hero_secondary_cta_text',
						__( 'Secondary CTA text', 'nexa-pro' ),
						__( 'Appears as the second homepage hero button.', 'nexa-pro' )
					);
					nexa_pro_admin_text_field(
						'hero_secondary_cta_url',
						__( 'Secondary CTA URL', 'nexa-pro' ),
						__( 'Use a full absolute URL or a same-page fragment such as #process.', 'nexa-pro' )
					);
					break;
			}
			?>
		</tbody>
	</table>
	<?php
}
