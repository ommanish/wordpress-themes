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

				case 'homepage-order':
					nexa_pro_admin_homepage_order_field();
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
