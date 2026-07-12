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
	$value    = nexa_pro_get_design_color( $key );
	$defaults = nexa_pro_get_default_global_design_options();
	$default  = isset( $defaults[ $key ] ) ? $defaults[ $key ] : $value;
	$field_id = 'nexa-pro-' . str_replace( '_', '-', $key );

	?>
	<tr>
		<th scope="row">
			<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $label ); ?></label>
		</th>
		<td>
			<input
				type="text"
				id="<?php echo esc_attr( $field_id ); ?>"
				name="nexa_pro_options[<?php echo esc_attr( $key ); ?>]"
				value="<?php echo esc_attr( $value ); ?>"
				class="regular-text nexa-pro-color-field"
				data-nexa-pro-color-field
				data-nexa-pro-design-default="<?php echo esc_attr( $default ); ?>"
			>
			<button type="button" class="button button-secondary" data-nexa-pro-field-reset>
				<?php esc_html_e( 'Reset', 'nexa-pro' ); ?>
			</button>
			<?php if ( $description ) : ?>
				<p class="description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
			<p class="description">
				<?php
				printf(
					/* translators: %s: Default color value. */
					esc_html__( 'Hex colors only. Default: %s.', 'nexa-pro' ),
					esc_html( $default )
				);
				?>
			</p>
		</td>
	</tr>
	<?php
}

/**
 * Get conditional admin row attributes.
 *
 * @param string $controller_key Controller option key.
 * @param array  $values Values that should show the row.
 * @return string
 */
function nexa_pro_admin_conditional_row_attributes( $controller_key, $values ) {
	if ( '' === $controller_key || empty( $values ) ) {
		return '';
	}

	return sprintf(
		' class="nexa-pro-admin-conditional-row" data-nexa-pro-conditional-field data-nexa-pro-conditional-key="%1$s" data-nexa-pro-conditional-values="%2$s"',
		esc_attr( $controller_key ),
		esc_attr( implode( ',', array_map( 'sanitize_key', $values ) ) )
	);
}

/**
 * Render a strict hex color field for non-global design options.
 *
 * @param string $key Option key.
 * @param string $label Field label.
 * @param string $description Field description.
 * @param string $controller_key Conditional controller option key.
 * @param array  $controller_values Conditional controller values.
 * @return void
 */
function nexa_pro_admin_option_color_field( $key, $label, $description = '', $controller_key = '', $controller_values = array() ) {
	$defaults = nexa_pro_get_default_options();
	$default  = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '#ffffff';
	$value    = sanitize_hex_color( nexa_pro_get_raw_option( $key, $default ) );
	$value    = $value ? $value : $default;
	$field_id = 'nexa-pro-' . str_replace( '_', '-', $key );
	$row_attrs = nexa_pro_admin_conditional_row_attributes( $controller_key, $controller_values );

	?>
	<tr<?php echo $row_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<th scope="row">
			<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $label ); ?></label>
		</th>
		<td>
			<input
				type="text"
				id="<?php echo esc_attr( $field_id ); ?>"
				name="nexa_pro_options[<?php echo esc_attr( $key ); ?>]"
				value="<?php echo esc_attr( $value ); ?>"
				class="regular-text nexa-pro-color-field"
				data-nexa-pro-color-field
				data-nexa-pro-design-default="<?php echo esc_attr( $default ); ?>"
			>
			<button type="button" class="button button-secondary" data-nexa-pro-field-reset>
				<?php esc_html_e( 'Reset', 'nexa-pro' ); ?>
			</button>
			<?php if ( $description ) : ?>
				<p class="description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</td>
	</tr>
	<?php
}

/**
 * Render a generic number field.
 *
 * @param string $key Option key.
 * @param string $label Field label.
 * @param string $description Field description.
 * @param string $min Minimum value.
 * @param string $max Maximum value.
 * @param string $step Input step.
 * @param string $controller_key Conditional controller option key.
 * @param array  $controller_values Conditional controller values.
 * @return void
 */
function nexa_pro_admin_number_field( $key, $label, $description, $min, $max, $step, $controller_key = '', $controller_values = array() ) {
	$defaults  = nexa_pro_get_default_options();
	$default   = isset( $defaults[ $key ] ) ? $defaults[ $key ] : 0;
	$value     = nexa_pro_get_option( $key, $default );
	$field_id  = 'nexa-pro-' . str_replace( '_', '-', $key );
	$row_attrs = nexa_pro_admin_conditional_row_attributes( $controller_key, $controller_values );

	?>
	<tr<?php echo $row_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<th scope="row">
			<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $label ); ?></label>
		</th>
		<td>
			<input
				type="number"
				id="<?php echo esc_attr( $field_id ); ?>"
				name="nexa_pro_options[<?php echo esc_attr( $key ); ?>]"
				value="<?php echo esc_attr( $value ); ?>"
				min="<?php echo esc_attr( $min ); ?>"
				max="<?php echo esc_attr( $max ); ?>"
				step="<?php echo esc_attr( $step ); ?>"
				class="small-text"
				data-nexa-pro-design-default="<?php echo esc_attr( $default ); ?>"
			>
			<button type="button" class="button button-secondary" data-nexa-pro-field-reset>
				<?php esc_html_e( 'Reset', 'nexa-pro' ); ?>
			</button>
			<?php if ( $description ) : ?>
				<p class="description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</td>
	</tr>
	<?php
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
 * @param string $controller_key Conditional controller option key.
 * @param array  $controller_values Conditional controller values.
 * @return void
 */
function nexa_pro_admin_select_field( $key, $label, $choices, $description = '', $controller_key = '', $controller_values = array() ) {
	$value     = nexa_pro_get_option( $key, '' );
	$field_id  = 'nexa-pro-' . str_replace( '_', '-', $key );
	$row_attrs = nexa_pro_admin_conditional_row_attributes( $controller_key, $controller_values );

	?>
	<tr<?php echo $row_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
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
 * Render an admin field group heading.
 *
 * @param string $heading Group heading.
 * @param string $description Group description.
 * @return void
 */
function nexa_pro_admin_field_group( $heading, $description = '' ) {
	?>
	<tr class="nexa-pro-admin-field-group">
		<td colspan="2">
			<h2><?php echo esc_html( $heading ); ?></h2>
			<?php if ( $description ) : ?>
				<p class="description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</td>
	</tr>
	<?php
}

/**
 * Render a design select field with reset metadata.
 *
 * @param string $key Option key.
 * @param string $label Field label.
 * @param array  $choices Select choices.
 * @param string $description Field description.
 * @return void
 */
function nexa_pro_admin_design_select_field( $key, $label, $choices, $description = '' ) {
	if ( in_array( $key, array( 'font_body', 'font_heading' ), true ) ) {
		$value = nexa_pro_get_font_choice( $key );
	} elseif ( in_array( $key, array( 'font_weight_heading', 'font_weight_button' ), true ) ) {
		$value = nexa_pro_get_design_font_weight( $key );
	} else {
		$value = nexa_pro_get_option( $key, '' );
	}

	$defaults = nexa_pro_get_default_global_design_options();
	$default  = isset( $defaults[ $key ] ) ? $defaults[ $key ] : $value;
	$field_id = 'nexa-pro-' . str_replace( '_', '-', $key );

	?>
	<tr>
		<th scope="row">
			<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $label ); ?></label>
		</th>
		<td>
			<select
				id="<?php echo esc_attr( $field_id ); ?>"
				name="nexa_pro_options[<?php echo esc_attr( $key ); ?>]"
				data-nexa-pro-design-default="<?php echo esc_attr( $default ); ?>"
			>
				<?php foreach ( $choices as $choice_value => $choice_label ) : ?>
					<option value="<?php echo esc_attr( $choice_value ); ?>" <?php selected( $value, $choice_value ); ?>>
						<?php echo esc_html( $choice_label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<button type="button" class="button button-secondary" data-nexa-pro-field-reset>
				<?php esc_html_e( 'Reset', 'nexa-pro' ); ?>
			</button>
			<?php if ( $description ) : ?>
				<p class="description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</td>
	</tr>
	<?php
}

/**
 * Render a numeric global design field.
 *
 * @param string $key Option key.
 * @param string $label Field label.
 * @param string $description Field description.
 * @param string $min Minimum value.
 * @param string $max Maximum value.
 * @param string $step Input step.
 * @return void
 */
function nexa_pro_admin_design_number_field( $key, $label, $description, $min, $max, $step ) {
	$value    = nexa_pro_get_design_number( $key );
	$defaults = nexa_pro_get_default_global_design_options();
	$default  = isset( $defaults[ $key ] ) ? $defaults[ $key ] : $value;
	$field_id = 'nexa-pro-' . str_replace( '_', '-', $key );

	?>
	<tr>
		<th scope="row">
			<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $label ); ?></label>
		</th>
		<td>
			<input
				type="number"
				id="<?php echo esc_attr( $field_id ); ?>"
				name="nexa_pro_options[<?php echo esc_attr( $key ); ?>]"
				value="<?php echo esc_attr( $value ); ?>"
				min="<?php echo esc_attr( $min ); ?>"
				max="<?php echo esc_attr( $max ); ?>"
				step="<?php echo esc_attr( $step ); ?>"
				class="small-text"
				data-nexa-pro-design-default="<?php echo esc_attr( $default ); ?>"
			>
			<button type="button" class="button button-secondary" data-nexa-pro-field-reset>
				<?php esc_html_e( 'Reset', 'nexa-pro' ); ?>
			</button>
			<?php if ( $description ) : ?>
				<p class="description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
			<p class="description">
				<?php
				printf(
					/* translators: 1: Minimum value. 2: Maximum value. 3: Default value. */
					esc_html__( 'Allowed range: %1$s to %2$s. Default: %3$s.', 'nexa-pro' ),
					esc_html( $min ),
					esc_html( $max ),
					esc_html( $default )
				);
				?>
			</p>
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
 * @param bool   $show_id_input Whether to show a numeric attachment ID input.
 * @param bool   $validate_image Whether to validate the value as an image attachment.
 * @param string $row_attributes Optional escaped row attributes.
 * @return void
 */
function nexa_pro_admin_media_field( $key, $label, $description = '', $show_id_input = true, $validate_image = false, $row_attributes = '' ) {
	$value       = $validate_image ? nexa_pro_get_image_attachment_id( $key ) : absint( nexa_pro_get_option( $key, 0 ) );
	$field_id    = 'nexa-pro-' . str_replace( '_', '-', $key );
	$wrapper_id  = $field_id . '-media-field';
	$preview_id  = $field_id . '-preview';
	$status_id   = $field_id . '-status';
	$preview     = $value ? wp_get_attachment_image( $value, 'medium', false, array( 'class' => 'nexa-pro-admin-media__image' ) ) : '';
	$has_preview = '' !== $preview;
	$select_text = $value ? __( 'Replace image', 'nexa-pro' ) : __( 'Select image', 'nexa-pro' );
	$input_type  = $show_id_input ? 'number' : 'hidden';
	$input_class = $show_id_input ? 'small-text' : '';
	/* translators: %s: Media field label. */
	$select_label = sprintf( __( 'Select image for %s', 'nexa-pro' ), $label );
	/* translators: %s: Media field label. */
	$replace_label = sprintf( __( 'Replace image for %s', 'nexa-pro' ), $label );
	/* translators: %s: Media field label. */
	$remove_label = sprintf( __( 'Remove image for %s', 'nexa-pro' ), $label );
	/* translators: %s: Media field label. */
	$selected_status = sprintf( __( 'Image selected for %s.', 'nexa-pro' ), $label );
	/* translators: %s: Media field label. */
	$removed_status = sprintf( __( 'Image removed for %s.', 'nexa-pro' ), $label );

	?>
	<tr<?php echo $row_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<th scope="row">
			<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $label ); ?></label>
		</th>
		<td>
			<div
				id="<?php echo esc_attr( $wrapper_id ); ?>"
				class="nexa-pro-admin-media"
				data-nexa-pro-media-field
				data-nexa-pro-media-selected-status="<?php echo esc_attr( $selected_status ); ?>"
				data-nexa-pro-media-removed-status="<?php echo esc_attr( $removed_status ); ?>"
			>
				<div
					id="<?php echo esc_attr( $preview_id ); ?>"
					class="nexa-pro-admin-media__preview"
					data-nexa-pro-media-preview
				>
					<?php echo $preview; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<input
					type="<?php echo esc_attr( $input_type ); ?>"
					<?php if ( $show_id_input ) : ?>
						min="0"
						step="1"
					<?php endif; ?>
					id="<?php echo esc_attr( $field_id ); ?>"
					name="nexa_pro_options[<?php echo esc_attr( $key ); ?>]"
					value="<?php echo esc_attr( $value ? $value : '' ); ?>"
					<?php if ( $input_class ) : ?>
						class="<?php echo esc_attr( $input_class ); ?>"
					<?php endif; ?>
					aria-describedby="<?php echo esc_attr( $status_id ); ?>"
					data-nexa-pro-media-input
				>
				<button
					type="button"
					class="button"
					data-nexa-pro-media-select
					data-nexa-pro-media-select-text="<?php esc_attr_e( 'Select image', 'nexa-pro' ); ?>"
					data-nexa-pro-media-replace-text="<?php esc_attr_e( 'Replace image', 'nexa-pro' ); ?>"
					data-nexa-pro-media-select-label="<?php echo esc_attr( $select_label ); ?>"
					data-nexa-pro-media-replace-label="<?php echo esc_attr( $replace_label ); ?>"
					aria-label="<?php echo esc_attr( $value ? $replace_label : $select_label ); ?>"
					aria-controls="<?php echo esc_attr( $preview_id ); ?>"
					aria-describedby="<?php echo esc_attr( $status_id ); ?>"
				>
					<?php echo esc_html( $select_text ); ?>
				</button>
				<button
					type="button"
					class="button"
					data-nexa-pro-media-remove
					aria-label="<?php echo esc_attr( $remove_label ); ?>"
					aria-controls="<?php echo esc_attr( $preview_id ); ?>"
					aria-describedby="<?php echo esc_attr( $status_id ); ?>"
					<?php disabled( ! $value ); ?>
					<?php if ( ! $value ) : ?>
						hidden
					<?php endif; ?>
				>
					<?php esc_html_e( 'Remove image', 'nexa-pro' ); ?>
				</button>
				<span
					id="<?php echo esc_attr( $status_id ); ?>"
					class="screen-reader-text"
					data-nexa-pro-media-status
					aria-live="polite"
				></span>
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
 * Get repeater items for an admin field.
 *
 * @param string $key Repeater option key.
 * @return array
 */
function nexa_pro_admin_get_repeater_items( $key ) {
	switch ( $key ) {
		case 'services_items':
			return nexa_pro_get_services_items();

		case 'features_items':
			return nexa_pro_get_features_items();

		case 'process_items':
			return nexa_pro_get_process_items();

		case 'why_items':
			return nexa_pro_get_why_items();
	}

	return array();
}

/**
 * Render a homepage repeater row.
 *
 * @param string  $key Repeater option key.
 * @param string  $row_key Row key used in field names.
 * @param array   $item Item data.
 * @param string  $item_label Singular item label.
 * @param bool    $is_template Whether this row is rendered inside a template element.
 * @param int     $position One-based row position for server-rendered labels.
 * @return void
 */
function nexa_pro_admin_repeater_row( $key, $row_key, $item, $item_label, $is_template = false, $position = 0 ) {
	$field_base = 'nexa_pro_options[' . $key . '][' . $row_key . ']';
	$id_base    = 'nexa-pro-' . str_replace( '_', '-', $key ) . '-' . $row_key;
	$title      = isset( $item['title'] ) ? $item['title'] : '';
	$text       = isset( $item['text'] ) ? $item['text'] : '';
	$item_id    = isset( $item['id'] ) ? $item['id'] : '';
	$link_text  = isset( $item['link_text'] ) ? $item['link_text'] : '';
	$link_url   = isset( $item['link_url'] ) ? $item['link_url'] : '';
	$row_class  = 'nexa-pro-repeater__item';

	if ( $is_template ) {
		$row_class .= ' is-template';
	}

	?>
	<div class="<?php echo esc_attr( $row_class ); ?>" data-nexa-pro-repeater-row data-nexa-pro-row-key="<?php echo esc_attr( $row_key ); ?>">
		<div class="nexa-pro-repeater__item-header">
			<h4 data-nexa-pro-repeater-row-title>
				<?php
				if ( $position ) {
					printf(
						/* translators: 1: Repeater item label. 2: Item number. */
						esc_html__( '%1$s %2$d', 'nexa-pro' ),
						esc_html( $item_label ),
						absint( $position )
					);
				} else {
					printf(
						/* translators: %s: Repeater item label. */
						esc_html__( '%s item', 'nexa-pro' ),
						esc_html( $item_label )
					);
				}
				?>
			</h4>
			<div class="nexa-pro-repeater__actions">
				<button type="button" class="button" data-nexa-pro-repeater-move="up" hidden>
					<?php esc_html_e( 'Move up', 'nexa-pro' ); ?>
				</button>
				<button type="button" class="button" data-nexa-pro-repeater-move="down" hidden>
					<?php esc_html_e( 'Move down', 'nexa-pro' ); ?>
				</button>
				<button type="button" class="button" data-nexa-pro-repeater-remove hidden>
					<?php esc_html_e( 'Remove', 'nexa-pro' ); ?>
				</button>
				<button type="button" class="button hidden" data-nexa-pro-repeater-undo hidden>
					<?php esc_html_e( 'Undo remove', 'nexa-pro' ); ?>
				</button>
			</div>
		</div>

		<input
			type="hidden"
			name="<?php echo esc_attr( $field_base ); ?>[id]"
			value="<?php echo esc_attr( $item_id ); ?>"
			data-nexa-pro-repeater-id
		>

		<p class="nexa-pro-repeater__field">
			<label for="<?php echo esc_attr( $id_base ); ?>-title">
				<?php esc_html_e( 'Title', 'nexa-pro' ); ?>
			</label>
			<input
				type="text"
				id="<?php echo esc_attr( $id_base ); ?>-title"
				name="<?php echo esc_attr( $field_base ); ?>[title]"
				value="<?php echo esc_attr( $title ); ?>"
				class="regular-text"
				data-nexa-pro-repeater-title
			>
		</p>

		<p class="nexa-pro-repeater__field">
			<label for="<?php echo esc_attr( $id_base ); ?>-text">
				<?php esc_html_e( 'Description', 'nexa-pro' ); ?>
			</label>
			<textarea
				id="<?php echo esc_attr( $id_base ); ?>-text"
				name="<?php echo esc_attr( $field_base ); ?>[text]"
				class="large-text"
				rows="3"
			><?php echo esc_textarea( $text ); ?></textarea>
		</p>

		<?php if ( 'services_items' === $key ) : ?>
			<p class="nexa-pro-repeater__field">
				<label for="<?php echo esc_attr( $id_base ); ?>-link-text">
					<?php esc_html_e( 'Link text', 'nexa-pro' ); ?>
				</label>
				<input
					type="text"
					id="<?php echo esc_attr( $id_base ); ?>-link-text"
					name="<?php echo esc_attr( $field_base ); ?>[link_text]"
					value="<?php echo esc_attr( $link_text ); ?>"
					class="regular-text"
				>
			</p>
			<p class="nexa-pro-repeater__field">
				<label for="<?php echo esc_attr( $id_base ); ?>-link-url">
					<?php esc_html_e( 'Link URL', 'nexa-pro' ); ?>
				</label>
				<input
					type="text"
					id="<?php echo esc_attr( $id_base ); ?>-link-url"
					name="<?php echo esc_attr( $field_base ); ?>[link_url]"
					value="<?php echo esc_attr( $link_url ); ?>"
					class="regular-text"
				>
			</p>
		<?php endif; ?>

		<p class="nexa-pro-repeater__remove">
			<label>
				<input
					type="checkbox"
					name="<?php echo esc_attr( $field_base ); ?>[_remove]"
					value="1"
					data-nexa-pro-repeater-remove-checkbox
				>
				<?php esc_html_e( 'Remove this item', 'nexa-pro' ); ?>
			</label>
		</p>
	</div>
	<?php
}

/**
 * Render a homepage repeater field.
 *
 * @param string $key Repeater option key.
 * @param string $heading Repeater heading.
 * @param string $item_label Singular item label.
 * @param string $description Repeater description.
 * @return void
 */
function nexa_pro_admin_repeater_field( $key, $heading, $item_label, $description = '' ) {
	$items     = nexa_pro_admin_get_repeater_items( $key );
	$field_id  = 'nexa-pro-' . str_replace( '_', '-', $key );
	$row_keys  = array();
	$row_index = 0;

	?>
	<tr>
		<th scope="row">
			<?php echo esc_html( $heading ); ?>
		</th>
		<td>
			<div
				class="nexa-pro-repeater"
				id="<?php echo esc_attr( $field_id ); ?>"
				data-nexa-pro-repeater
				data-nexa-pro-repeater-key="<?php echo esc_attr( $key ); ?>"
				data-nexa-pro-item-label="<?php echo esc_attr( $item_label ); ?>"
			>
				<input type="hidden" name="nexa_pro_options[<?php echo esc_attr( $key ); ?>_submitted]" value="1">
				<h3 class="nexa-pro-repeater__heading"><?php echo esc_html( $heading ); ?></h3>
				<?php if ( $description ) : ?>
					<p class="description"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
				<div class="nexa-pro-repeater__status screen-reader-text" aria-live="polite" data-nexa-pro-repeater-status></div>
				<div class="nexa-pro-repeater__items" data-nexa-pro-repeater-items>
					<?php foreach ( $items as $item ) : ?>
						<?php
						$row_key = ! empty( $item['id'] ) ? sanitize_key( $item['id'] ) : 'row-' . $row_index;

						if ( '' === $row_key || isset( $row_keys[ $row_key ] ) ) {
							$row_key = 'row-' . $row_index;
						}

						$row_keys[ $row_key ] = true;
						$row_index++;
						nexa_pro_admin_repeater_row( $key, $row_key, $item, $item_label, false, $row_index );
						?>
					<?php endforeach; ?>
				</div>
				<p class="nexa-pro-repeater__empty" data-nexa-pro-repeater-empty <?php echo $items ? 'hidden' : ''; ?>>
					<?php esc_html_e( 'No items are currently configured. Save this tab to keep this repeater empty.', 'nexa-pro' ); ?>
				</p>
				<button type="button" class="button" data-nexa-pro-repeater-add hidden>
					<?php
					printf(
						/* translators: %s: Repeater item label. */
						esc_html__( 'Add %s', 'nexa-pro' ),
						esc_html( $item_label )
					);
					?>
				</button>
				<template data-nexa-pro-repeater-template>
					<?php
					nexa_pro_admin_repeater_row(
						$key,
						'__index__',
						array(
							'id'        => '',
							'title'     => '',
							'text'      => '',
							'link_text' => '',
							'link_url'  => '',
						),
						$item_label,
						true
					);
					?>
				</template>
			</div>
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

	switch ( $prefix ) {
		case 'about':
			nexa_pro_admin_media_field(
				'about_image_id',
				__( 'About image', 'nexa-pro' ),
				__( 'Select an optional image displayed beside the About section content.', 'nexa-pro' ),
				false,
				true
			);
			break;

		case 'services':
			nexa_pro_admin_media_field(
				'services_background_image_id',
				__( 'Services background image', 'nexa-pro' ),
				__( 'Select an optional decorative background image for the Services section.', 'nexa-pro' ),
				false,
				true,
				nexa_pro_admin_conditional_row_attributes( 'services_background_type', array( 'image' ) )
			);
			break;

		case 'features':
			nexa_pro_admin_media_field(
				'features_background_image_id',
				__( 'Features background image', 'nexa-pro' ),
				__( 'Select an optional decorative background image for the Features section.', 'nexa-pro' ),
				false,
				true,
				nexa_pro_admin_conditional_row_attributes( 'features_background_type', array( 'image' ) )
			);
			break;

		case 'process':
			nexa_pro_admin_media_field(
				'process_background_image_id',
				__( 'Process background image', 'nexa-pro' ),
				__( 'Select an optional decorative background image for the Process section.', 'nexa-pro' ),
				false,
				true,
				nexa_pro_admin_conditional_row_attributes( 'process_background_type', array( 'image' ) )
			);
			break;

		case 'why':
			nexa_pro_admin_media_field(
				'why_image_id',
				__( 'Why Choose Us image', 'nexa-pro' ),
				__( 'Select an optional image displayed beside the Why Choose Us content.', 'nexa-pro' ),
				false,
				true
			);
			break;

		case 'cta':
			nexa_pro_admin_media_field(
				'cta_background_image_id',
				__( 'CTA background image', 'nexa-pro' ),
				__( 'Select an optional decorative background image for the CTA section.', 'nexa-pro' ),
				false,
				true,
				nexa_pro_admin_conditional_row_attributes( 'cta_background_type', array( 'image' ) )
			);
			break;
	}

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
			__( 'Use a full absolute URL, a same-page fragment such as #contact, or #nexa-pro-schedule for the schedule modal. Update links manually if their target section or action is disabled.', 'nexa-pro' )
		);
	}

	switch ( $prefix ) {
		case 'services':
			nexa_pro_admin_repeater_field(
				'services_items',
				__( 'Service items', 'nexa-pro' ),
				__( 'Service', 'nexa-pro' ),
				__( 'Add, edit, remove, and reorder the service cards shown in this section.', 'nexa-pro' )
			);
			break;

		case 'features':
			nexa_pro_admin_repeater_field(
				'features_items',
				__( 'Feature items', 'nexa-pro' ),
				__( 'Feature', 'nexa-pro' ),
				__( 'Add, edit, remove, and reorder the feature cards shown in this section.', 'nexa-pro' )
			);
			break;

		case 'process':
			nexa_pro_admin_repeater_field(
				'process_items',
				__( 'Process items', 'nexa-pro' ),
				__( 'Process step', 'nexa-pro' ),
				__( 'Add, edit, remove, and reorder the process steps shown in this section.', 'nexa-pro' )
			);
			break;

		case 'why':
			nexa_pro_admin_repeater_field(
				'why_items',
				__( 'Why Choose Us items', 'nexa-pro' ),
				__( 'Why item', 'nexa-pro' ),
				__( 'Add, edit, remove, and reorder the points shown in this section.', 'nexa-pro' )
			);
			break;
	}

	nexa_pro_admin_section_design_fields(
		$prefix,
		$label,
		! in_array( $prefix, array( 'services', 'features', 'process', 'cta' ), true )
	);
}

/**
 * Render a homepage media-only section tab.
 *
 * @param string $key Option key.
 * @param string $label Field label.
 * @param string $description Field description.
 * @param string $row_attributes Optional escaped row attributes.
 * @return void
 */
function nexa_pro_admin_homepage_media_only_fields( $key, $label, $description, $row_attributes = '' ) {
	nexa_pro_admin_media_field( $key, $label, $description, false, true, $row_attributes );
}

/**
 * Get background type choices.
 *
 * @return array
 */
function nexa_pro_admin_background_type_choices() {
	return array(
		'default'  => __( 'Default theme surface', 'nexa-pro' ),
		'solid'    => __( 'Solid color', 'nexa-pro' ),
		'gradient' => __( 'Gradient', 'nexa-pro' ),
		'image'    => __( 'Background image', 'nexa-pro' ),
	);
}

/**
 * Get gradient direction choices.
 *
 * @return array
 */
function nexa_pro_admin_gradient_direction_choices() {
	return array(
		'to-bottom'       => __( 'Top to bottom', 'nexa-pro' ),
		'to-top'          => __( 'Bottom to top', 'nexa-pro' ),
		'to-right'        => __( 'Left to right', 'nexa-pro' ),
		'to-left'         => __( 'Right to left', 'nexa-pro' ),
		'to-bottom-right' => __( 'Top left to bottom right', 'nexa-pro' ),
		'to-bottom-left'  => __( 'Top right to bottom left', 'nexa-pro' ),
		'to-top-right'    => __( 'Bottom left to top right', 'nexa-pro' ),
		'to-top-left'     => __( 'Bottom right to top left', 'nexa-pro' ),
	);
}

/**
 * Get text theme choices.
 *
 * @return array
 */
function nexa_pro_admin_text_theme_choices() {
	return array(
		'automatic' => __( 'Automatic', 'nexa-pro' ),
		'dark'      => __( 'Dark text', 'nexa-pro' ),
		'light'     => __( 'Light text', 'nexa-pro' ),
	);
}

/**
 * Render reusable section design controls.
 *
 * @param string $section Section key.
 * @param string $label Section label.
 * @param bool   $show_background_image_field Whether to render the section background image media field.
 * @return void
 */
function nexa_pro_admin_section_design_fields( $section, $label, $show_background_image_field = true ) {
	$section   = sanitize_key( $section );
	$prefix    = $section . '_';
	$image_key = nexa_pro_get_section_background_image_option_key( $section );
	$types     = array( 'solid', 'image' );
	$overlay_types = array( 'solid', 'gradient', 'image' );

	if ( ! in_array( $section, nexa_pro_get_section_design_sections(), true ) ) {
		return;
	}

	nexa_pro_admin_field_group(
		sprintf(
			/* translators: %s: Section label. */
			__( '%s design', 'nexa-pro' ),
			$label
		),
		__( 'Control this section surface without changing the section content. If JavaScript is disabled, all fields remain visible and save normally.', 'nexa-pro' )
	);

	nexa_pro_admin_select_field(
		$prefix . 'background_type',
		__( 'Background type', 'nexa-pro' ),
		nexa_pro_admin_background_type_choices(),
		__( 'Default keeps the current theme surface. Solid, gradient, and image modes use the fields below.', 'nexa-pro' )
	);

	nexa_pro_admin_option_color_field(
		$prefix . 'background_color',
		__( 'Background color', 'nexa-pro' ),
		__( 'Used by solid background mode and as the fallback color behind background images.', 'nexa-pro' ),
		$prefix . 'background_type',
		$types
	);

	nexa_pro_admin_option_color_field(
		$prefix . 'gradient_start',
		__( 'Gradient start color', 'nexa-pro' ),
		__( 'Used by gradient background mode.', 'nexa-pro' ),
		$prefix . 'background_type',
		array( 'gradient' )
	);

	nexa_pro_admin_option_color_field(
		$prefix . 'gradient_end',
		__( 'Gradient end color', 'nexa-pro' ),
		__( 'Used by gradient background mode.', 'nexa-pro' ),
		$prefix . 'background_type',
		array( 'gradient' )
	);

	nexa_pro_admin_select_field(
		$prefix . 'gradient_direction',
		__( 'Gradient direction', 'nexa-pro' ),
		nexa_pro_admin_gradient_direction_choices(),
		__( 'Controls the gradient angle for this section.', 'nexa-pro' ),
		$prefix . 'background_type',
		array( 'gradient' )
	);

	if ( $show_background_image_field && $image_key ) {
		nexa_pro_admin_media_field(
			$image_key,
			__( 'Background image', 'nexa-pro' ),
			__( 'Used only when Background type is set to Background image. The attachment ID is stored, not the image URL.', 'nexa-pro' ),
			false,
			true,
			nexa_pro_admin_conditional_row_attributes( $prefix . 'background_type', array( 'image' ) )
		);
	}

	nexa_pro_admin_checkbox_field(
		$prefix . 'overlay_enabled',
		__( 'Background overlay', 'nexa-pro' ),
		__( 'Adds a color overlay for readability on solid, gradient, and image backgrounds.', 'nexa-pro' )
	);

	nexa_pro_admin_option_color_field(
		$prefix . 'overlay_color',
		__( 'Overlay color', 'nexa-pro' ),
		__( 'Used when Background overlay is enabled.', 'nexa-pro' ),
		$prefix . 'background_type',
		$overlay_types
	);

	nexa_pro_admin_number_field(
		$prefix . 'overlay_opacity',
		__( 'Overlay opacity', 'nexa-pro' ),
		__( 'Percentage value from 0 to 90. Higher values make the overlay stronger.', 'nexa-pro' ),
		'0',
		'90',
		'1',
		$prefix . 'background_type',
		$overlay_types
	);

	nexa_pro_admin_select_field(
		$prefix . 'text_theme',
		__( 'Text theme', 'nexa-pro' ),
		nexa_pro_admin_text_theme_choices(),
		__( 'Choose light or dark text manually when a custom background needs stronger contrast.', 'nexa-pro' )
	);
}

/**
 * Get labels for movable homepage sections.
 *
 * @return array
 */
function nexa_pro_get_homepage_order_section_labels() {
	return array(
		'about'        => __( 'About', 'nexa-pro' ),
		'services'     => __( 'Services', 'nexa-pro' ),
		'features'     => __( 'Features', 'nexa-pro' ),
		'process'      => __( 'Process', 'nexa-pro' ),
		'why'          => __( 'Why Choose Us', 'nexa-pro' ),
		'portfolio'    => __( 'Portfolio', 'nexa-pro' ),
		'testimonials' => __( 'Testimonials', 'nexa-pro' ),
		'team'         => __( 'Team', 'nexa-pro' ),
		'faq'          => __( 'FAQ', 'nexa-pro' ),
		'contact'      => __( 'Contact', 'nexa-pro' ),
		'cta'          => __( 'CTA', 'nexa-pro' ),
	);
}

/**
 * Render homepage section order controls.
 *
 * @return void
 */
function nexa_pro_admin_homepage_order_field() {
	$labels        = nexa_pro_get_homepage_order_section_labels();
	$order         = nexa_pro_get_homepage_section_order();
	$default_order = nexa_pro_get_default_homepage_section_order();
	$order_count   = count( $order );
	$row_index     = 0;

	?>
	<tr>
		<td colspan="2">
			<div
				class="nexa-pro-homepage-order"
				data-nexa-pro-homepage-order
				data-nexa-pro-default-order="<?php echo esc_attr( implode( ',', $default_order ) ); ?>"
			>
				<h2><?php esc_html_e( 'Homepage Order', 'nexa-pro' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'Arrange the movable homepage sections. Hero and Trust always remain first, and hidden sections keep their saved position for when they are re-enabled.', 'nexa-pro' ); ?>
				</p>
				<input type="hidden" name="nexa_pro_options[homepage_section_order_reset]" value="0" data-nexa-pro-homepage-order-reset>
				<div class="nexa-pro-homepage-order__status screen-reader-text" aria-live="polite" data-nexa-pro-homepage-order-status></div>
				<ol class="nexa-pro-homepage-order__list" data-nexa-pro-homepage-order-list>
					<?php foreach ( $order as $section_key ) : ?>
						<?php
						if ( ! isset( $labels[ $section_key ] ) ) {
							continue;
						}

						$label    = $labels[ $section_key ];
						$field_id = 'nexa-pro-homepage-order-' . $section_key;
						$row_index++;
						/* translators: %s: Homepage section label. */
						$move_up_label = sprintf( __( 'Move %s up', 'nexa-pro' ), $label );
						/* translators: %s: Homepage section label. */
						$move_down_label = sprintf( __( 'Move %s down', 'nexa-pro' ), $label );
						?>
						<li class="nexa-pro-homepage-order__item" data-nexa-pro-homepage-order-row data-nexa-pro-section-key="<?php echo esc_attr( $section_key ); ?>">
							<div class="nexa-pro-homepage-order__item-inner">
								<span id="<?php echo esc_attr( $field_id ); ?>" class="nexa-pro-homepage-order__label" data-nexa-pro-homepage-order-label>
									<?php echo esc_html( $label ); ?>
								</span>
								<input type="hidden" name="nexa_pro_options[homepage_section_order][]" value="<?php echo esc_attr( $section_key ); ?>">
								<div class="nexa-pro-homepage-order__actions">
									<button
										type="button"
										class="button"
										data-nexa-pro-homepage-order-move="up"
										aria-label="<?php echo esc_attr( $move_up_label ); ?>"
										hidden
										<?php disabled( 1 === $row_index ); ?>
									>
										<?php esc_html_e( 'Move up', 'nexa-pro' ); ?>
									</button>
									<button
										type="button"
										class="button"
										data-nexa-pro-homepage-order-move="down"
										aria-label="<?php echo esc_attr( $move_down_label ); ?>"
										hidden
										<?php disabled( $order_count === $row_index ); ?>
									>
										<?php esc_html_e( 'Move down', 'nexa-pro' ); ?>
									</button>
								</div>
							</div>
						</li>
					<?php endforeach; ?>
				</ol>
				<button
					type="button"
					class="button button-secondary"
					data-nexa-pro-homepage-order-reset-button
					data-nexa-pro-reset-confirm="<?php esc_attr_e( 'Reset the homepage section order to the default order?', 'nexa-pro' ); ?>"
					hidden
				>
					<?php esc_html_e( 'Reset to default', 'nexa-pro' ); ?>
				</button>
			</div>
		</td>
	</tr>
	<?php
}

/**
 * Render global design reset controls.
 *
 * @return void
 */
function nexa_pro_admin_global_design_reset_field() {
	?>
	<tr class="nexa-pro-admin-global-reset">
		<th scope="row"><?php esc_html_e( 'Reset design settings', 'nexa-pro' ); ?></th>
		<td>
			<input type="hidden" name="nexa_pro_options[global_design_reset]" value="0" data-nexa-pro-global-design-reset>
			<button
				type="button"
				class="button button-secondary"
				data-nexa-pro-global-design-reset-button
				data-nexa-pro-reset-confirm="<?php esc_attr_e( 'Reset global colors and typography to the Nexa Pro defaults?', 'nexa-pro' ); ?>"
			>
				<?php esc_html_e( 'Reset global design settings', 'nexa-pro' ); ?>
			</button>
			<p class="description">
				<?php esc_html_e( 'Resets only the color and typography fields on this tab. Header, Hero, homepage content, repeaters, section order, visibility, and media settings are preserved.', 'nexa-pro' ); ?>
			</p>
			<div class="screen-reader-text" aria-live="polite" data-nexa-pro-global-design-reset-status></div>
		</td>
	</tr>
	<?php
}

/**
 * Get font weight field choices.
 *
 * @param array $weights Allowed weights.
 * @return array
 */
function nexa_pro_admin_font_weight_choices( $weights ) {
	$choices = array();

	foreach ( $weights as $weight ) {
		$choices[ $weight ] = $weight;
	}

	return $choices;
}

/**
 * Render footer settings fields.
 *
 * @return void
 */
function nexa_pro_admin_footer_fields() {
	nexa_pro_admin_field_group(
		__( 'Footer branding', 'nexa-pro' ),
		__( 'Controls the footer identity area. If no footer logo or text is available, the WordPress site title remains the fallback.', 'nexa-pro' )
	);
	nexa_pro_admin_media_field(
		'footer_logo_id',
		__( 'Footer logo attachment ID', 'nexa-pro' ),
		__( 'Select an optional footer logo. Leave empty to use the native WordPress custom logo or brand text fallback.', 'nexa-pro' ),
		true,
		true
	);
	nexa_pro_admin_checkbox_field(
		'footer_show_brand_text',
		__( 'Show footer brand text', 'nexa-pro' ),
		__( 'Shows footer brand text beside the logo. If disabled and no valid logo exists, brand text still displays so the footer identity is not empty.', 'nexa-pro' )
	);
	nexa_pro_admin_text_field(
		'footer_brand_text',
		__( 'Footer brand text', 'nexa-pro' ),
		__( 'Optional footer-specific brand text. Leave blank to use the Global brand name or the WordPress site title.', 'nexa-pro' )
	);
	nexa_pro_admin_textarea_field(
		'footer_description',
		__( 'Footer description', 'nexa-pro' ),
		__( 'Optional short description shown in the footer branding area.', 'nexa-pro' )
	);

	nexa_pro_admin_field_group(
		__( 'Footer navigation', 'nexa-pro' ),
		__( 'Uses the existing Footer menu location when a menu is assigned in Appearance > Menus.', 'nexa-pro' )
	);
	nexa_pro_admin_checkbox_field(
		'footer_menu_enabled',
		__( 'Show footer menu', 'nexa-pro' ),
		__( 'Disables only the footer menu output. The registered WordPress menu location remains unchanged.', 'nexa-pro' )
	);

	nexa_pro_admin_field_group(
		__( 'Legal and copyright', 'nexa-pro' ),
		__( 'Legal items can link to pages, open theme modals, or stay hidden. Page URLs may be absolute, site-relative paths, or same-page fragments.', 'nexa-pro' )
	);
	nexa_pro_admin_textarea_field(
		'footer_copyright',
		__( 'Copyright text', 'nexa-pro' ),
		__( 'Supports {year} and {site_name}. Leave blank to use the default copyright text.', 'nexa-pro' )
	);
	nexa_pro_admin_text_field(
		'footer_privacy_label',
		__( 'Privacy link label', 'nexa-pro' ),
		__( 'Appears in the footer legal links when a privacy URL is also provided.', 'nexa-pro' )
	);
	nexa_pro_admin_text_field(
		'footer_privacy_url',
		__( 'Privacy link URL', 'nexa-pro' ),
		__( 'Use an absolute URL, a site-relative path such as /privacy-policy, or a same-page fragment. In modal mode, this becomes the no-JavaScript fallback when provided.', 'nexa-pro' )
	);
	nexa_pro_admin_select_field(
		'footer_privacy_behavior',
		__( 'Privacy behavior', 'nexa-pro' ),
		array(
			'link'   => __( 'Link to page', 'nexa-pro' ),
			'modal'  => __( 'Open modal', 'nexa-pro' ),
			'hidden' => __( 'Hidden', 'nexa-pro' ),
		),
		__( 'Choose whether the privacy item links to a page, opens a modal, or is hidden.', 'nexa-pro' )
	);
	nexa_pro_admin_text_field(
		'footer_privacy_modal_title',
		__( 'Privacy modal title', 'nexa-pro' ),
		__( 'Used as the accessible heading when Privacy behavior is Open modal.', 'nexa-pro' )
	);
	nexa_pro_admin_raw_textarea_field(
		'footer_privacy_modal_content',
		__( 'Privacy modal content', 'nexa-pro' ),
		__( 'Limited safe HTML is allowed. Add real legal copy before using modal behavior on a public site.', 'nexa-pro' )
	);
	nexa_pro_admin_text_field(
		'footer_terms_label',
		__( 'Terms link label', 'nexa-pro' ),
		__( 'Appears in the footer legal links when a terms URL is also provided.', 'nexa-pro' )
	);
	nexa_pro_admin_text_field(
		'footer_terms_url',
		__( 'Terms link URL', 'nexa-pro' ),
		__( 'Use an absolute URL, a site-relative path such as /terms, or a same-page fragment. In modal mode, this becomes the no-JavaScript fallback when provided.', 'nexa-pro' )
	);
	nexa_pro_admin_select_field(
		'footer_terms_behavior',
		__( 'Terms behavior', 'nexa-pro' ),
		array(
			'link'   => __( 'Link to page', 'nexa-pro' ),
			'modal'  => __( 'Open modal', 'nexa-pro' ),
			'hidden' => __( 'Hidden', 'nexa-pro' ),
		),
		__( 'Choose whether the terms item links to a page, opens a modal, or is hidden.', 'nexa-pro' )
	);
	nexa_pro_admin_text_field(
		'footer_terms_modal_title',
		__( 'Terms modal title', 'nexa-pro' ),
		__( 'Used as the accessible heading when Terms behavior is Open modal.', 'nexa-pro' )
	);
	nexa_pro_admin_raw_textarea_field(
		'footer_terms_modal_content',
		__( 'Terms modal content', 'nexa-pro' ),
		__( 'Limited safe HTML is allowed. Add real legal copy before using modal behavior on a public site.', 'nexa-pro' )
	);

	nexa_pro_admin_field_group(
		__( 'Contact details', 'nexa-pro' ),
		__( 'These details can appear in the footer and support the schedule modal email action.', 'nexa-pro' )
	);
	nexa_pro_admin_text_field(
		'contact_email',
		__( 'Contact email', 'nexa-pro' ),
		__( 'Used for footer email links and the schedule modal email action. Invalid email addresses save as empty.', 'nexa-pro' ),
		'email'
	);
	nexa_pro_admin_text_field(
		'contact_phone',
		__( 'Contact phone', 'nexa-pro' ),
		__( 'Shown in the footer. A tel link is created only when usable digits are present.', 'nexa-pro' )
	);
	nexa_pro_admin_textarea_field(
		'contact_address',
		__( 'Contact address', 'nexa-pro' ),
		__( 'Optional address or service-area text shown in the footer.', 'nexa-pro' )
	);
	nexa_pro_admin_textarea_field(
		'contact_business_hours',
		__( 'Business hours', 'nexa-pro' ),
		__( 'Optional business-hours text shown in the footer.', 'nexa-pro' )
	);

	nexa_pro_admin_field_group(
		__( 'Social profiles', 'nexa-pro' ),
		__( 'Social links render only for valid absolute HTTP or HTTPS URLs.', 'nexa-pro' )
	);
	nexa_pro_admin_text_field(
		'social_linkedin_url',
		__( 'LinkedIn URL', 'nexa-pro' ),
		__( 'Use a full LinkedIn profile or company URL.', 'nexa-pro' )
	);
	nexa_pro_admin_text_field(
		'social_github_url',
		__( 'GitHub URL', 'nexa-pro' ),
		__( 'Use a full GitHub profile or organization URL.', 'nexa-pro' )
	);
	nexa_pro_admin_text_field(
		'social_x_url',
		__( 'X URL', 'nexa-pro' ),
		__( 'Use a full X profile URL.', 'nexa-pro' )
	);
	nexa_pro_admin_text_field(
		'social_facebook_url',
		__( 'Facebook URL', 'nexa-pro' ),
		__( 'Use a full Facebook page or profile URL.', 'nexa-pro' )
	);
	nexa_pro_admin_text_field(
		'social_instagram_url',
		__( 'Instagram URL', 'nexa-pro' ),
		__( 'Use a full Instagram profile URL.', 'nexa-pro' )
	);
}

/**
 * Render contact action settings fields.
 *
 * @return void
 */
function nexa_pro_admin_contact_action_fields() {
	nexa_pro_admin_field_group(
		__( 'Schedule modal', 'nexa-pro' ),
		__( 'Use #nexa-pro-schedule as a Header or CTA URL to make that button open the modal. If no valid email or calendar action exists, schedule triggers will not render.', 'nexa-pro' )
	);
	nexa_pro_admin_checkbox_field(
		'schedule_modal_enabled',
		__( 'Enable schedule modal', 'nexa-pro' ),
		__( 'Shows schedule triggers only when at least one valid action is configured.', 'nexa-pro' )
	);
	nexa_pro_admin_text_field(
		'schedule_modal_title',
		__( 'Modal title', 'nexa-pro' ),
		__( 'Appears as the accessible heading inside the schedule modal.', 'nexa-pro' )
	);
	nexa_pro_admin_textarea_field(
		'schedule_modal_text',
		__( 'Modal description', 'nexa-pro' ),
		__( 'Optional supporting text shown before the modal actions.', 'nexa-pro' )
	);

	nexa_pro_admin_field_group(
		__( 'Email action', 'nexa-pro' ),
		__( 'Uses the Contact email from the Footer tab as the recipient. No email is sent by the theme; this creates a mailto link only.', 'nexa-pro' )
	);
	nexa_pro_admin_text_field(
		'schedule_email_label',
		__( 'Email action label', 'nexa-pro' ),
		__( 'Shown on the modal email button when Contact email is valid.', 'nexa-pro' )
	);
	nexa_pro_admin_text_field(
		'schedule_email_subject',
		__( 'Email subject', 'nexa-pro' ),
		__( 'Used as the subject in the generated mailto link.', 'nexa-pro' )
	);
	nexa_pro_admin_textarea_field(
		'schedule_email_body',
		__( 'Email body', 'nexa-pro' ),
		__( 'Used as the body in the generated mailto link.', 'nexa-pro' )
	);

	nexa_pro_admin_field_group(
		__( 'Calendar action', 'nexa-pro' ),
		__( 'Use a valid absolute HTTP or HTTPS booking URL such as a calendar scheduling page. The theme does not connect to external booking APIs.', 'nexa-pro' )
	);
	nexa_pro_admin_text_field(
		'schedule_calendar_label',
		__( 'Calendar action label', 'nexa-pro' ),
		__( 'Shown on the modal calendar button when the calendar URL is valid.', 'nexa-pro' )
	);
	nexa_pro_admin_text_field(
		'schedule_calendar_url',
		__( 'Calendar URL', 'nexa-pro' ),
		__( 'Use a full absolute HTTP or HTTPS booking URL. Invalid URLs save as empty.', 'nexa-pro' )
	);
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

					nexa_pro_admin_field_group(
						__( 'Navigation behavior', 'nexa-pro' ),
						__( 'Single-page mode enhances same-page section links on the front page. Multipage mode keeps navigation links fully native.', 'nexa-pro' )
					);
					nexa_pro_admin_select_field(
						'navigation_mode',
						__( 'Navigation mode', 'nexa-pro' ),
						array(
							'multipage'   => __( 'Multipage', 'nexa-pro' ),
							'single-page' => __( 'Single-page homepage', 'nexa-pro' ),
						),
						__( 'Single-page mode scrolls to visible homepage section anchors when the target exists on the current page.', 'nexa-pro' )
					);
					nexa_pro_admin_checkbox_field(
						'single_page_smooth_scroll',
						__( 'Smooth scroll', 'nexa-pro' ),
						__( 'Enhances same-page navigation with smooth scrolling unless the visitor prefers reduced motion.', 'nexa-pro' )
					);
					nexa_pro_admin_checkbox_field(
						'single_page_active_state',
						__( 'Active section state', 'nexa-pro' ),
						__( 'Updates aria-current on navigation links while visitors move through homepage sections.', 'nexa-pro' )
					);
					nexa_pro_admin_number_field(
						'single_page_scroll_offset',
						__( 'Additional scroll offset', 'nexa-pro' ),
						__( 'Optional pixel offset added to the sticky header height for single-page scrolling.', 'nexa-pro' ),
						'0',
						'240',
						'1'
					);

					nexa_pro_admin_field_group(
						__( 'Brand colors', 'nexa-pro' ),
						__( 'Use readable hex colors. These values drive the global theme tokens used across the front end and editor.', 'nexa-pro' )
					);
					nexa_pro_admin_color_field(
						'color_primary',
						__( 'Primary color', 'nexa-pro' ),
						__( 'Primary brand color used by emphasis states and related theme tokens.', 'nexa-pro' )
					);
					nexa_pro_admin_color_field(
						'color_secondary',
						__( 'Secondary color', 'nexa-pro' ),
						__( 'Secondary brand color available to the global token system.', 'nexa-pro' )
					);
					nexa_pro_admin_color_field(
						'color_accent',
						__( 'Accent color', 'nexa-pro' ),
						__( 'Accent color used for focus and supporting emphasis. Check contrast when pairing with light surfaces.', 'nexa-pro' )
					);

					nexa_pro_admin_field_group(
						__( 'Text and surface colors', 'nexa-pro' ),
						__( 'These values control page background, text, headings, cards, and borders. Review body text and headings against the selected background for readable contrast.', 'nexa-pro' )
					);
					nexa_pro_admin_color_field(
						'color_background',
						__( 'Page background color', 'nexa-pro' ),
						__( 'Applies to the main page canvas and default body background.', 'nexa-pro' )
					);
					nexa_pro_admin_color_field(
						'color_surface',
						__( 'Surface and card background', 'nexa-pro' ),
						__( 'Applies to cards and raised content surfaces that use the global surface token.', 'nexa-pro' )
					);
					nexa_pro_admin_color_field(
						'color_text',
						__( 'Main text color', 'nexa-pro' ),
						__( 'Applies to primary body copy and inherited text tokens.', 'nexa-pro' )
					);
					nexa_pro_admin_color_field(
						'color_text_muted',
						__( 'Muted text color', 'nexa-pro' ),
						__( 'Applies to descriptions, metadata, and supporting text.', 'nexa-pro' )
					);
					nexa_pro_admin_color_field(
						'color_heading',
						__( 'Heading color', 'nexa-pro' ),
						__( 'Applies to front-end and editor headings.', 'nexa-pro' )
					);
					nexa_pro_admin_color_field(
						'color_border',
						__( 'Border color', 'nexa-pro' ),
						__( 'Applies to controls, cards, navigation panels, and other tokenized borders.', 'nexa-pro' )
					);

					nexa_pro_admin_field_group(
						__( 'Buttons and links', 'nexa-pro' ),
						__( 'Button and link colors should preserve strong contrast, especially primary button text against its background and links against the page background.', 'nexa-pro' )
					);
					nexa_pro_admin_color_field(
						'color_button_primary_background',
						__( 'Primary button background', 'nexa-pro' ),
						__( 'Applies to primary theme buttons and submit buttons.', 'nexa-pro' )
					);
					nexa_pro_admin_color_field(
						'color_button_primary_text',
						__( 'Primary button text', 'nexa-pro' ),
						__( 'Applies to text on primary buttons.', 'nexa-pro' )
					);
					nexa_pro_admin_color_field(
						'color_button_primary_hover',
						__( 'Primary button hover background', 'nexa-pro' ),
						__( 'Applies when primary buttons are hovered or focused.', 'nexa-pro' )
					);
					nexa_pro_admin_color_field(
						'color_button_secondary_background',
						__( 'Secondary button background', 'nexa-pro' ),
						__( 'Applies to secondary theme buttons.', 'nexa-pro' )
					);
					nexa_pro_admin_color_field(
						'color_button_secondary_text',
						__( 'Secondary button text', 'nexa-pro' ),
						__( 'Applies to text on secondary buttons.', 'nexa-pro' )
					);
					nexa_pro_admin_color_field(
						'color_button_secondary_border',
						__( 'Secondary button border', 'nexa-pro' ),
						__( 'Applies to secondary button borders.', 'nexa-pro' )
					);
					nexa_pro_admin_color_field(
						'color_link',
						__( 'Link color', 'nexa-pro' ),
						__( 'Applies to standard front-end and editor links.', 'nexa-pro' )
					);
					nexa_pro_admin_color_field(
						'color_link_hover',
						__( 'Link hover color', 'nexa-pro' ),
						__( 'Applies to link hover and focus color states.', 'nexa-pro' )
					);

					nexa_pro_admin_field_group(
						__( 'Typography', 'nexa-pro' ),
						__( 'Uses local system-safe font stacks only. No external font requests are loaded.', 'nexa-pro' )
					);
					nexa_pro_admin_design_select_field(
						'font_body',
						__( 'Body font family', 'nexa-pro' ),
						nexa_pro_get_font_family_choices(),
						__( 'Applies to body copy, forms, and most interface text.', 'nexa-pro' )
					);
					nexa_pro_admin_design_select_field(
						'font_heading',
						__( 'Heading font family', 'nexa-pro' ),
						nexa_pro_get_font_family_choices(),
						__( 'Applies to headings in the front end and editor.', 'nexa-pro' )
					);
					nexa_pro_admin_design_number_field(
						'font_size_base',
						__( 'Base font size', 'nexa-pro' ),
						__( 'Integer pixel value used for the global base text size when changed from the default.', 'nexa-pro' ),
						'14',
						'22',
						'1'
					);
					nexa_pro_admin_design_number_field(
						'line_height_body',
						__( 'Body line height', 'nexa-pro' ),
						__( 'Decimal value for readable body text spacing.', 'nexa-pro' ),
						'1.2',
						'2.0',
						'0.05'
					);
					nexa_pro_admin_design_number_field(
						'line_height_heading',
						__( 'Heading line height', 'nexa-pro' ),
						__( 'Decimal value for heading spacing.', 'nexa-pro' ),
						'1.0',
						'1.6',
						'0.05'
					);
					nexa_pro_admin_design_select_field(
						'font_weight_heading',
						__( 'Heading font weight', 'nexa-pro' ),
						nexa_pro_admin_font_weight_choices( nexa_pro_get_allowed_font_weights( 'font_weight_heading' ) ),
						__( 'Applies to global heading weight.', 'nexa-pro' )
					);
					nexa_pro_admin_design_select_field(
						'font_weight_button',
						__( 'Button font weight', 'nexa-pro' ),
						nexa_pro_admin_font_weight_choices( nexa_pro_get_allowed_font_weights( 'font_weight_button' ) ),
						__( 'Applies to primary and secondary theme buttons.', 'nexa-pro' )
					);
					nexa_pro_admin_global_design_reset_field();
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
						__( 'Use a full absolute URL, a same-page fragment such as #contact, or #nexa-pro-schedule for the schedule modal.', 'nexa-pro' )
					);
					nexa_pro_admin_checkbox_field(
						'mobile_cta_enabled',
						__( 'Mobile menu CTA', 'nexa-pro' ),
						__( 'Shows the same CTA inside the mobile menu only when CTA text and URL are valid.', 'nexa-pro' )
					);
					break;

				case 'footer':
					nexa_pro_admin_footer_fields();
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

				case 'portfolio':
					nexa_pro_admin_homepage_media_only_fields(
						'portfolio_image_id',
						__( 'Portfolio image', 'nexa-pro' ),
						__( 'Select an optional image displayed with the Portfolio section.', 'nexa-pro' )
					);
					nexa_pro_admin_section_design_fields( 'portfolio', __( 'Portfolio', 'nexa-pro' ) );
					break;

				case 'testimonials':
					nexa_pro_admin_homepage_media_only_fields(
						'testimonials_background_image_id',
						__( 'Testimonials background image', 'nexa-pro' ),
						__( 'Select an optional decorative background image for the Testimonials section.', 'nexa-pro' ),
						nexa_pro_admin_conditional_row_attributes( 'testimonials_background_type', array( 'image' ) )
					);
					nexa_pro_admin_section_design_fields( 'testimonials', __( 'Testimonials', 'nexa-pro' ), false );
					break;

				case 'team':
					nexa_pro_admin_homepage_media_only_fields(
						'team_background_image_id',
						__( 'Team background image', 'nexa-pro' ),
						__( 'Select an optional decorative background image for the Team section.', 'nexa-pro' ),
						nexa_pro_admin_conditional_row_attributes( 'team_background_type', array( 'image' ) )
					);
					nexa_pro_admin_section_design_fields( 'team', __( 'Team', 'nexa-pro' ), false );
					break;

				case 'faq':
					nexa_pro_admin_section_design_fields( 'faq', __( 'FAQ', 'nexa-pro' ) );
					break;

				case 'contact':
					nexa_pro_admin_homepage_media_only_fields(
						'contact_background_image_id',
						__( 'Contact background image', 'nexa-pro' ),
						__( 'Select an optional decorative background image for the Contact section.', 'nexa-pro' ),
						nexa_pro_admin_conditional_row_attributes( 'contact_background_type', array( 'image' ) )
					);
					nexa_pro_admin_section_design_fields( 'contact', __( 'Contact', 'nexa-pro' ), false );
					break;

				case 'cta':
					nexa_pro_admin_homepage_section_fields( 'cta', __( 'CTA', 'nexa-pro' ) );
					break;

				case 'contact-actions':
					nexa_pro_admin_contact_action_fields();
					break;

				case 'homepage-order':
					nexa_pro_admin_homepage_order_field();
					break;

				case 'hero':
					nexa_pro_admin_field_group(
						__( 'Hero content', 'nexa-pro' ),
						__( 'The hero heading remains the only H1 on the homepage.', 'nexa-pro' )
					);
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
						__( 'Use a full absolute URL, a same-page fragment such as #process, or #nexa-pro-schedule for the schedule modal.', 'nexa-pro' )
					);

					nexa_pro_admin_field_group(
						__( 'Hero layout and images', 'nexa-pro' ),
						__( 'Hero image controls use attachment IDs only. The mobile image falls back to the desktop image when left empty.', 'nexa-pro' )
					);
					nexa_pro_admin_select_field(
						'hero_layout',
						__( 'Hero layout', 'nexa-pro' ),
						array(
							'content-only'     => __( 'Content only', 'nexa-pro' ),
							'image-left'       => __( 'Image left', 'nexa-pro' ),
							'image-right'      => __( 'Image right', 'nexa-pro' ),
							'background-image' => __( 'Background image', 'nexa-pro' ),
						),
						__( 'Content only preserves the current default. Image layouts render a foreground image; background image uses the desktop hero image behind the content.', 'nexa-pro' )
					);
					nexa_pro_admin_media_field(
						'hero_image_id',
						__( 'Desktop hero image', 'nexa-pro' ),
						__( 'Used by image and background-image hero layouts.', 'nexa-pro' ),
						false,
						true
					);
					nexa_pro_admin_media_field(
						'hero_mobile_image_id',
						__( 'Mobile hero image', 'nexa-pro' ),
						__( 'Optional mobile-specific hero image. Leave empty to use the desktop hero image.', 'nexa-pro' ),
						false,
						true
					);
					nexa_pro_admin_checkbox_field(
						'hero_show_image_mobile',
						__( 'Show hero image on mobile', 'nexa-pro' ),
						__( 'Disable to hide the hero image or background image on narrow screens.', 'nexa-pro' )
					);
					nexa_pro_admin_select_field(
						'hero_image_position',
						__( 'Hero image position', 'nexa-pro' ),
						array(
							'left'   => __( 'Left', 'nexa-pro' ),
							'center' => __( 'Center', 'nexa-pro' ),
							'right'  => __( 'Right', 'nexa-pro' ),
						),
						__( 'Controls foreground image placement and background image emphasis.', 'nexa-pro' )
					);
					nexa_pro_admin_select_field(
						'hero_image_object_position',
						__( 'Image focal position', 'nexa-pro' ),
						array(
							'center center' => __( 'Center', 'nexa-pro' ),
							'top center'    => __( 'Top', 'nexa-pro' ),
							'bottom center' => __( 'Bottom', 'nexa-pro' ),
							'left center'   => __( 'Left', 'nexa-pro' ),
							'right center'  => __( 'Right', 'nexa-pro' ),
						),
						__( 'Controls how foreground hero images crop inside their frame.', 'nexa-pro' )
					);
					nexa_pro_admin_select_field(
						'hero_content_alignment',
						__( 'Hero content alignment', 'nexa-pro' ),
						array(
							'left'   => __( 'Left', 'nexa-pro' ),
							'center' => __( 'Center', 'nexa-pro' ),
							'right'  => __( 'Right', 'nexa-pro' ),
						),
						__( 'Aligns hero eyebrow, heading, text, and buttons.', 'nexa-pro' )
					);
					nexa_pro_admin_select_field(
						'hero_content_width',
						__( 'Hero content width', 'nexa-pro' ),
						array(
							'narrow'   => __( 'Narrow', 'nexa-pro' ),
							'standard' => __( 'Standard', 'nexa-pro' ),
							'wide'     => __( 'Wide', 'nexa-pro' ),
						),
						__( 'Controls the maximum width of hero copy in content-only and background-image layouts.', 'nexa-pro' )
					);
					nexa_pro_admin_section_design_fields( 'hero', __( 'Hero', 'nexa-pro' ), false );
					break;
			}
			?>
		</tbody>
	</table>
	<?php
}
