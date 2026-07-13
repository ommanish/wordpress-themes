<?php
/**
 * Navigation admin screen.
 *
 * @package Nexa_Pro_Core
 */

namespace Nexa_Pro_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Navigation admin UI.
 */
final class Navigation_Admin {
	/**
	 * Render the navigation screen.
	 *
	 * @return void
	 */
	public static function render() {
		if ( ! Capabilities::current_user_can_manage_components() ) {
			\wp_die( \esc_html__( 'You do not have permission to manage generated navigation.', 'nexa-pro-core' ) );
		}

		$settings = Navigation_Settings::get_settings();
		$pages    = Builder_Admin::get_editable_pages(
			array(
				'search'     => '',
				'status'     => '',
				'configured' => '',
			)
		);
		$tree     = Navigation_Tree::get_generated_navigation( $settings );
		$warnings = Navigation_Tree::validate_navigation_tree( $tree );

		self::render_notices();
		?>
		<section class="nexa-pro-core-builder__section" aria-labelledby="nexa-pro-core-navigation-title">
			<h2 id="nexa-pro-core-navigation-title"><?php esc_html_e( 'Navigation', 'nexa-pro-core' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Choose whether the theme header uses the assigned WordPress menu, builder-generated items, or a hybrid of both. WordPress menu items are never overwritten.', 'nexa-pro-core' ); ?>
			</p>

			<div class="nexa-pro-core-builder__two-column">
				<?php self::render_settings_form( $settings, $pages ); ?>
				<?php self::render_preview( $tree, $warnings ); ?>
			</div>
		</section>
		<?php
	}

	/**
	 * Render notices.
	 *
	 * @return void
	 */
	private static function render_notices() {
		$notice = isset( $_GET[ Navigation_Actions::NOTICE_QUERY ] ) ? \sanitize_key( \wp_unslash( $_GET[ Navigation_Actions::NOTICE_QUERY ] ) ) : '';
		$error  = isset( $_GET[ Navigation_Actions::ERROR_QUERY ] ) ? \sanitize_key( \wp_unslash( $_GET[ Navigation_Actions::ERROR_QUERY ] ) ) : '';

		if ( $notice ) {
			$messages = array(
				'saved' => __( 'Navigation settings saved.', 'nexa-pro-core' ),
				'reset' => __( 'Navigation settings restored to defaults.', 'nexa-pro-core' ),
			);

			printf(
				'<div class="notice notice-success is-dismissible" role="status"><p>%s</p></div>',
				esc_html( isset( $messages[ $notice ] ) ? $messages[ $notice ] : __( 'Navigation action completed.', 'nexa-pro-core' ) )
			);
		}

		if ( $error ) {
			$messages = array(
				'nexa_pro_core_forbidden'      => __( 'You do not have permission to manage navigation.', 'nexa-pro-core' ),
				'nexa_pro_core_invalid_nonce' => __( 'The request expired. Refresh the screen and try again.', 'nexa-pro-core' ),
			);

			printf(
				'<div class="notice notice-error" role="alert"><p>%s</p></div>',
				esc_html( isset( $messages[ $error ] ) ? $messages[ $error ] : __( 'Navigation request failed.', 'nexa-pro-core' ) )
			);
		}
	}

	/**
	 * Render settings form.
	 *
	 * @param array $settings Settings.
	 * @param array $pages    Pages.
	 * @return void
	 */
	private static function render_settings_form( array $settings, array $pages ) {
		?>
		<form class="nexa-pro-core-builder__panel" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-navigation-form>
			<input type="hidden" name="action" value="nexa_pro_core_navigation_save">
			<input type="hidden" name="nexa_pro_core_navigation[generated_page_ids_submitted]" value="1">
			<?php wp_nonce_field( Navigation_Actions::nonce_action( 'save' ) ); ?>

			<?php
			self::render_select(
				'nexa-pro-core-navigation-source',
				'nexa_pro_core_navigation[navigation_source]',
				__( 'Navigation source', 'nexa-pro-core' ),
				array(
					'wordpress' => __( 'WordPress menu', 'nexa-pro-core' ),
					'generated' => __( 'Generated from builder', 'nexa-pro-core' ),
					'hybrid'    => __( 'Hybrid', 'nexa-pro-core' ),
				),
				$settings['navigation_source'],
				__( 'WordPress mode preserves the assigned Primary Menu. Generated mode uses selected pages and component navigation metadata. Hybrid mode combines both.', 'nexa-pro-core' )
			);

			self::render_page_select(
				'nexa-pro-core-primary-page',
				'nexa_pro_core_navigation[generated_primary_page_id]',
				__( 'Primary single-page page', 'nexa-pro-core' ),
				$pages,
				$settings['generated_primary_page_id'],
				__( 'When only this page is used, generated navigation links directly to eligible components on the page.', 'nexa-pro-core' )
			);

			self::render_page_checkboxes(
				$pages,
				$settings['generated_page_ids']
			);

			self::render_checkbox( 'nexa-pro-core-include-sections', 'nexa_pro_core_navigation[include_page_sections]', __( 'Include page sections', 'nexa-pro-core' ), $settings['include_page_sections'], __( 'Adds eligible builder components as generated navigation links.', 'nexa-pro-core' ) );
			self::render_checkbox( 'nexa-pro-core-active-sections', 'nexa_pro_core_navigation[active_section_enabled]', __( 'Enable active section state', 'nexa-pro-core' ), $settings['active_section_enabled'], __( 'Same-page generated links receive aria-current="location" as sections enter the viewport.', 'nexa-pro-core' ) );
			self::render_checkbox( 'nexa-pro-core-smooth-scroll', 'nexa_pro_core_navigation[smooth_scroll_enabled]', __( 'Enable smooth scrolling', 'nexa-pro-core' ), $settings['smooth_scroll_enabled'], __( 'Respects reduced-motion preferences.', 'nexa-pro-core' ) );
			self::render_number( 'nexa-pro-core-scroll-offset', 'nexa_pro_core_navigation[scroll_offset]', __( 'Additional scroll offset', 'nexa-pro-core' ), $settings['scroll_offset'], 0, 240 );

			self::render_select(
				'nexa-pro-core-hybrid-position',
				'nexa_pro_core_navigation[hybrid_insertion_position]',
				__( 'Hybrid insertion', 'nexa-pro-core' ),
				array(
					'before'              => __( 'Before WordPress menu items', 'nexa-pro-core' ),
					'after'               => __( 'After WordPress menu items', 'nexa-pro-core' ),
					'replace-placeholder' => __( 'Replace placeholder item', 'nexa-pro-core' ),
				),
				$settings['hybrid_insertion_position'],
				__( 'Placeholder replacement looks for a WordPress menu item with URL #nexa-generated-navigation by default.', 'nexa-pro-core' )
			);
			self::render_text( 'nexa-pro-core-placeholder-url', 'nexa_pro_core_navigation[hybrid_placeholder_url]', __( 'Hybrid placeholder URL', 'nexa-pro-core' ), $settings['hybrid_placeholder_url'] );
			self::render_select(
				'nexa-pro-core-generated-mobile',
				'nexa_pro_core_navigation[generated_mobile_visibility]',
				__( 'Generated mobile visibility', 'nexa-pro-core' ),
				array(
					'visible' => __( 'Visible on mobile', 'nexa-pro-core' ),
					'hidden'  => __( 'Hidden on mobile', 'nexa-pro-core' ),
				),
				$settings['generated_mobile_visibility'],
				__( 'Use this only when the WordPress menu already covers mobile navigation.', 'nexa-pro-core' )
			);
			?>

			<p>
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Save navigation settings', 'nexa-pro-core' ); ?></button>
			</p>
		</form>

		<form class="nexa-pro-core-builder__reset-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="nexa_pro_core_navigation_reset">
			<?php wp_nonce_field( Navigation_Actions::nonce_action( 'reset' ) ); ?>
			<button type="submit" class="button"><?php esc_html_e( 'Restore navigation defaults', 'nexa-pro-core' ); ?></button>
		</form>
		<?php
	}

	/**
	 * Render preview.
	 *
	 * @param array $tree     Navigation tree.
	 * @param array $warnings Warnings.
	 * @return void
	 */
	private static function render_preview( array $tree, array $warnings ) {
		?>
		<aside class="nexa-pro-core-builder__panel" aria-labelledby="nexa-pro-core-navigation-preview-title">
			<h3 id="nexa-pro-core-navigation-preview-title"><?php esc_html_e( 'Generated preview', 'nexa-pro-core' ); ?></h3>

			<?php if ( $warnings ) : ?>
				<div class="notice notice-warning inline" role="status">
					<ul>
						<?php foreach ( $warnings as $warning ) : ?>
							<li><?php echo esc_html( $warning ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( empty( $tree ) ) : ?>
				<p><?php esc_html_e( 'No generated items are available yet. Select published pages with enabled components that are shown in navigation.', 'nexa-pro-core' ); ?></p>
			<?php else : ?>
				<?php self::render_tree_list( $tree ); ?>
			<?php endif; ?>
		</aside>
		<?php
	}

	/**
	 * Render preview tree.
	 *
	 * @param array $items Items.
	 * @return void
	 */
	private static function render_tree_list( array $items ) {
		echo '<ul class="nexa-pro-core-builder__tree">';

		foreach ( $items as $item ) {
			echo '<li>';
			printf(
				'<span>%1$s</span> <code>%2$s</code>',
				esc_html( isset( $item['label'] ) ? $item['label'] : '' ),
				esc_html( isset( $item['url'] ) ? $item['url'] : '' )
			);

			if ( ! empty( $item['children'] ) && is_array( $item['children'] ) ) {
				self::render_tree_list( $item['children'] );
			}

			echo '</li>';
		}

		echo '</ul>';
	}

	/**
	 * Render a select field.
	 */
	private static function render_select( $id, $name, $label, array $options, $selected, $description = '' ) {
		printf(
			'<p><label for="%1$s">%2$s</label><select id="%1$s" name="%3$s">',
			esc_attr( $id ),
			esc_html( $label ),
			esc_attr( $name )
		);

		foreach ( $options as $value => $option_label ) {
			printf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( $value ), selected( $selected, $value, false ), esc_html( $option_label ) );
		}

		echo '</select>';

		if ( $description ) {
			printf( '<span class="description">%s</span>', esc_html( $description ) );
		}

		echo '</p>';
	}

	/**
	 * Render page select.
	 */
	private static function render_page_select( $id, $name, $label, array $pages, $selected, $description ) {
		$options = array( 0 => __( 'Choose a page', 'nexa-pro-core' ) );

		foreach ( $pages as $page ) {
			$options[ absint( $page->ID ) ] = \get_the_title( $page );
		}

		self::render_select( $id, $name, $label, $options, absint( $selected ), $description );
	}

	/**
	 * Render generated page checkboxes.
	 *
	 * @param array $pages    Pages.
	 * @param array $selected Selected IDs.
	 * @return void
	 */
	private static function render_page_checkboxes( array $pages, array $selected ) {
		?>
		<fieldset class="nexa-pro-core-builder__fieldset">
			<legend><?php esc_html_e( 'Generated multipage pages', 'nexa-pro-core' ); ?></legend>
			<p class="description"><?php esc_html_e( 'Pages appear in the submitted order. Use the visible position field to reorder without JavaScript.', 'nexa-pro-core' ); ?></p>
			<?php foreach ( $pages as $page ) : ?>
				<?php
				$page_id  = absint( $page->ID );
				$checked  = in_array( $page_id, $selected, true );
				$order    = false === array_search( $page_id, $selected, true ) ? '' : array_search( $page_id, $selected, true ) + 1;
				$field_id = 'nexa-pro-core-generated-page-' . $page_id;
				?>
				<div class="nexa-pro-core-builder__page-choice">
					<label for="<?php echo esc_attr( $field_id ); ?>">
						<input type="checkbox" id="<?php echo esc_attr( $field_id ); ?>" name="nexa_pro_core_navigation[generated_page_ids][]" value="<?php echo esc_attr( $page_id ); ?>" <?php checked( $checked ); ?>>
						<?php echo esc_html( get_the_title( $page ) ); ?>
					</label>
					<label>
						<span class="screen-reader-text">
							<?php
							printf(
								/* translators: %s: page title. */
								esc_html__( 'Generated navigation position for %s', 'nexa-pro-core' ),
								esc_html( get_the_title( $page ) )
							);
							?>
						</span>
						<input type="number" class="small-text" name="nexa_pro_core_navigation[generated_page_order][<?php echo esc_attr( $page_id ); ?>]" value="<?php echo esc_attr( '' === $order ? 999 : $order ); ?>" min="1" max="999">
					</label>
					<span class="description"><?php echo esc_html( $page->post_status ); ?></span>
					<?php if ( $checked ) : ?>
						<span class="description">
							<?php
							printf(
								/* translators: %d: order number. */
								esc_html__( 'Current position %d', 'nexa-pro-core' ),
								absint( $order )
							);
							?>
						</span>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</fieldset>
		<?php
	}

	/**
	 * Render checkbox.
	 */
	private static function render_checkbox( $id, $name, $label, $checked, $description ) {
		printf(
			'<p><input type="hidden" name="%1$s" value="0"><label for="%2$s"><input type="checkbox" id="%2$s" name="%1$s" value="1"%3$s> %4$s</label><span class="description">%5$s</span></p>',
			esc_attr( $name ),
			esc_attr( $id ),
			checked( '1', (string) $checked, false ),
			esc_html( $label ),
			esc_html( $description )
		);
	}

	/**
	 * Render number field.
	 */
	private static function render_number( $id, $name, $label, $value, $min, $max ) {
		printf(
			'<p><label for="%1$s">%2$s</label><input type="number" id="%1$s" name="%3$s" value="%4$s" min="%5$s" max="%6$s"></p>',
			esc_attr( $id ),
			esc_html( $label ),
			esc_attr( $name ),
			esc_attr( $value ),
			esc_attr( $min ),
			esc_attr( $max )
		);
	}

	/**
	 * Render text field.
	 */
	private static function render_text( $id, $name, $label, $value ) {
		printf(
			'<p><label for="%1$s">%2$s</label><input class="regular-text" type="text" id="%1$s" name="%3$s" value="%4$s"></p>',
			esc_attr( $id ),
			esc_html( $label ),
			esc_attr( $name ),
			esc_attr( $value )
		);
	}
}
