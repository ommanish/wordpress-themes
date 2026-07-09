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
 * Render a textarea field that can display an intentionally saved empty value.
 *
 * @param string $key Option key.
 * @param string $label Field label.
 * @param string $description Field description.
 * @return void
 */
function nexa_pro_admin_raw_textarea_field( $key, $label, $description = '' ) {
	$value    = nexa_pro_get_raw_option( $key, '' );
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
 * Render a checkbox admin field.
 *
 * @param string $key Option key.
 * @param string $label Field label.
 * @param string $description Field description.
 * @return void
 */
function nexa_pro_admin_checkbox_field( $key, $label, $description = '' ) {
	$value    = nexa_pro_get_option( $key, '0' );
	$field_id = 'nexa-pro-' . str_replace( '_', '-', $key );

	?>
	<tr>
		<th scope="row"><?php echo esc_html( $label ); ?></th>
		<td>
			<input type="hidden" name="nexa_pro_options[<?php echo esc_attr( $key ); ?>]" value="0">
			<label for="<?php echo esc_attr( $field_id ); ?>">
				<input
					type="checkbox"
					id="<?php echo esc_attr( $field_id ); ?>"
					name="nexa_pro_options[<?php echo esc_attr( $key ); ?>]"
					value="1"
					<?php checked( '1', $value ); ?>
				>
				<?php esc_html_e( 'Enabled', 'nexa-pro' ); ?>
			</label>
			<?php if ( $description ) : ?>
				<p class="description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</td>
	</tr>
	<?php
}

/**
 * Render a select admin field.
 *
 * @param string $key Option key.
 * @param string $label Field label.
 * @param array  $choices Select choices.
 * @param string $description Field description.
 * @return void
 */
function nexa_pro_admin_select_field( $key, $label, $choices, $description = '' ) {
	$value    = nexa_pro_get_option( $key, '' );
	$field_id = 'nexa-pro-' . str_replace( '_', '-', $key );

	?>
	<tr>
		<th scope="row">
			<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $label ); ?></label>
		</th>
		<td>
			<select id="<?php echo esc_attr( $field_id ); ?>" name="nexa_pro_options[<?php echo esc_attr( $key ); ?>]">
				<?php foreach ( $choices as $choice_value => $choice_label ) : ?>
					<option value="<?php echo esc_attr( $choice_value ); ?>" <?php selected( $value, $choice_value ); ?>>
						<?php echo esc_html( $choice_label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<?php if ( $description ) : ?>
				<p class="description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</td>
	</tr>
	<?php
}

/**
 * Render a media attachment ID field.
 *
 * @param string $key Option key.
 * @param string $label Field label.
 * @param string $description Field description.
 * @return void
 */
function nexa_pro_admin_media_field( $key, $label, $description = '' ) {
	$value       = absint( nexa_pro_get_option( $key, 0 ) );
	$field_id    = 'nexa-pro-' . str_replace( '_', '-', $key );
	$preview     = $value ? wp_get_attachment_image( $value, 'medium', false, array( 'class' => 'nexa-pro-admin-media__image' ) ) : '';
	$has_preview = '' !== $preview;

	?>
	<tr>
		<th scope="row">
			<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $label ); ?></label>
		</th>
		<td>
			<div class="nexa-pro-admin-media" data-nexa-pro-media-field>
				<div class="nexa-pro-admin-media__preview" data-nexa-pro-media-preview>
					<?php echo $preview; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<input
					type="number"
					min="0"
					step="1"
					id="<?php echo esc_attr( $field_id ); ?>"
					name="nexa_pro_options[<?php echo esc_attr( $key ); ?>]"
					value="<?php echo esc_attr( $value ); ?>"
					class="small-text"
					data-nexa-pro-media-input
				>
				<button type="button" class="button" data-nexa-pro-media-select>
					<?php esc_html_e( 'Select image', 'nexa-pro' ); ?>
				</button>
				<button type="button" class="button" data-nexa-pro-media-remove <?php disabled( ! $value ); ?>>
					<?php esc_html_e( 'Remove image', 'nexa-pro' ); ?>
				</button>
				<?php if ( $value && ! $has_preview ) : ?>
					<p class="description"><?php esc_html_e( 'This attachment ID could not render a preview. It will fall back on the front end until a valid image is selected.', 'nexa-pro' ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( $description ) : ?>
				<p class="description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</td>
	</tr>
	<?php
}

/**
 * Render homepage section settings fields.
 *
 * @param string $prefix Section option prefix.
 * @param string $label Section label.
 * @return void
 */
function nexa_pro_admin_homepage_section_fields( $prefix, $label ) {
	nexa_pro_admin_checkbox_field(
		$prefix . '_show',
		sprintf(
			/* translators: %s: Homepage section label. */
			__( 'Show %s section', 'nexa-pro' ),
			$label
		),
		__( 'Disable this to remove the entire section and its anchor from the homepage. Update any menu, hero, header, or button links that point to disabled sections.', 'nexa-pro' )
	);

	if ( 'cta' !== $prefix ) {
		nexa_pro_admin_text_field(
			$prefix . '_label',
			__( 'Section label', 'nexa-pro' ),
			__( 'Appears as the small label above the section heading.', 'nexa-pro' )
		);
	}

	nexa_pro_admin_text_field(
		$prefix . '_heading',
		__( 'Section heading', 'nexa-pro' ),
		__( 'Required for this section. Empty submissions preserve the existing heading.', 'nexa-pro' )
	);

	nexa_pro_admin_raw_textarea_field(
		$prefix . '_text',
		__( 'Section description', 'nexa-pro' ),
		__( 'Appears below the section heading when provided.', 'nexa-pro' )
	);

	if ( 'cta' === $prefix ) {
		nexa_pro_admin_text_field(
			'cta_button_text',
			__( 'Button text', 'nexa-pro' ),
			__( 'Appears in the CTA button when both button text and URL are provided.', 'nexa-pro' )
		);
		nexa_pro_admin_text_field(
			'cta_button_url',
			__( 'Button URL', 'nexa-pro' ),
			__( 'Use a full absolute URL or a same-page fragment such as #contact. Update links manually if their target section is disabled.', 'nexa-pro' )
		);
	}
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
					nexa_pro_admin_media_field(
						'logo_attachment_id',
						__( 'Desktop logo attachment ID', 'nexa-pro' ),
						__( 'Select a media-library image for the desktop header logo. Leave empty to use the native WordPress custom logo or brand text fallback.', 'nexa-pro' )
					);
					nexa_pro_admin_media_field(
						'mobile_logo_attachment_id',
						__( 'Mobile logo attachment ID', 'nexa-pro' ),
						__( 'Select an optional mobile-specific logo. Leave empty to use the desktop logo, native WordPress custom logo, or brand text fallback.', 'nexa-pro' )
					);
					nexa_pro_admin_checkbox_field(
						'display_brand_text',
						__( 'Display brand text', 'nexa-pro' ),
						__( 'Shows the brand name and optional tagline beside the logo. If disabled and no valid logo exists, the brand name still displays.', 'nexa-pro' )
					);
					nexa_pro_admin_select_field(
						'header_layout',
						__( 'Header layout', 'nexa-pro' ),
						array(
							'standard' => __( 'Standard', 'nexa-pro' ),
							'centered' => __( 'Centered', 'nexa-pro' ),
						),
						__( 'Standard places brand left with navigation and CTA right. Centered places the brand above centered navigation on wide screens.', 'nexa-pro' )
					);
					nexa_pro_admin_checkbox_field(
						'sticky_header',
						__( 'Sticky header', 'nexa-pro' ),
						__( 'Keeps the header visible with CSS position sticky and accounts for the WordPress admin bar.', 'nexa-pro' )
					);
					nexa_pro_admin_checkbox_field(
						'transparent_header',
						__( 'Transparent homepage header', 'nexa-pro' ),
						__( 'Applies only on the front page when the hero section is present. Other pages use a solid header.', 'nexa-pro' )
					);
					nexa_pro_admin_checkbox_field(
						'header_cta_enabled',
						__( 'Desktop header CTA', 'nexa-pro' ),
						__( 'Shows one desktop header button when CTA text and URL are also provided.', 'nexa-pro' )
					);
					nexa_pro_admin_text_field(
						'header_cta_text',
						__( 'Header CTA text', 'nexa-pro' ),
						__( 'Appears in the optional header CTA button. Leave blank to hide the CTA.', 'nexa-pro' )
					);
					nexa_pro_admin_text_field(
						'header_cta_url',
						__( 'Header CTA URL', 'nexa-pro' ),
						__( 'Use a full absolute URL or a same-page fragment such as #contact.', 'nexa-pro' )
					);
					nexa_pro_admin_checkbox_field(
						'mobile_cta_enabled',
						__( 'Mobile menu CTA', 'nexa-pro' ),
						__( 'Shows the same CTA inside the mobile menu only when CTA text and URL are valid.', 'nexa-pro' )
					);
					break;

				case 'about':
					nexa_pro_admin_homepage_section_fields( 'about', __( 'About', 'nexa-pro' ) );
					break;

				case 'services':
					nexa_pro_admin_homepage_section_fields( 'services', __( 'Services', 'nexa-pro' ) );
					break;

				case 'features':
					nexa_pro_admin_homepage_section_fields( 'features', __( 'Features', 'nexa-pro' ) );
					break;

				case 'process':
					nexa_pro_admin_homepage_section_fields( 'process', __( 'Process', 'nexa-pro' ) );
					break;

				case 'why':
					nexa_pro_admin_homepage_section_fields( 'why', __( 'Why Choose Us', 'nexa-pro' ) );
					break;

				case 'cta':
					nexa_pro_admin_homepage_section_fields( 'cta', __( 'CTA', 'nexa-pro' ) );
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
