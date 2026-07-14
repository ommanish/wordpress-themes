<?php
/**
 * Reusable component admin screen.
 *
 * @package Nexa_Pro_Core
 */

namespace Nexa_Pro_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reusable component admin UI.
 */
final class Reusable_Admin {
	/**
	 * Render reusable screen.
	 *
	 * @return void
	 */
	public static function render() {
		if ( ! Capabilities::current_user_can_manage_reusable_components() ) {
			\wp_die( \esc_html__( 'You do not have permission to manage reusable components.', 'nexa-pro-core' ) );
		}

		$mode     = isset( $_GET['reusable_mode'] ) ? \sanitize_key( \wp_unslash( $_GET['reusable_mode'] ) ) : '';
		$post_id  = isset( $_GET['reusable_id'] ) ? absint( \wp_unslash( $_GET['reusable_id'] ) ) : 0;
		$registry = Builder_Admin::get_component_registry();

		self::render_notices();
		?>
		<section class="nexa-pro-core-builder__section" aria-labelledby="nexa-pro-core-reusable-title">
			<header class="nexa-pro-core-builder__stack-header">
				<div>
					<h2 id="nexa-pro-core-reusable-title"><?php esc_html_e( 'Reusable Components', 'nexa-pro-core' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Create source components once, insert linked instances into pages, or create independent local copies.', 'nexa-pro-core' ); ?></p>
				</div>
				<a class="button button-primary" href="<?php echo esc_url( self::url( array( 'reusable_mode' => 'new' ) ) ); ?>#nexa-pro-core-reusable-editor">
					<?php esc_html_e( 'Create reusable component', 'nexa-pro-core' ); ?>
				</a>
			</header>

			<div class="nexa-pro-core-builder__two-column">
				<div>
					<?php self::render_table(); ?>
					<?php self::render_insert_panel(); ?>
				</div>
				<aside class="nexa-pro-core-builder__panel" id="nexa-pro-core-reusable-editor">
					<?php
					if ( 'new' === $mode ) {
						self::render_editor_form( 0, self::empty_component( $registry ), $registry );
					} elseif ( 'edit' === $mode && $post_id ) {
						$component = Reusable_Components::get_reusable_component( $post_id );

						if ( \is_wp_error( $component ) ) {
							echo '<p>' . esc_html( $component->get_error_message() ) . '</p>';
						} else {
							self::render_editor_form( $post_id, $component, $registry );
						}
					} else {
						echo '<h3>' . esc_html__( 'Editor', 'nexa-pro-core' ) . '</h3>';
						echo '<p>' . esc_html__( 'Create or select a reusable component to edit its source content, layout, design, navigation defaults, and advanced settings.', 'nexa-pro-core' ) . '</p>';
					}
					?>
				</aside>
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
		$notice = isset( $_GET[ Reusable_Actions::NOTICE_QUERY ] ) ? \sanitize_key( \wp_unslash( $_GET[ Reusable_Actions::NOTICE_QUERY ] ) ) : '';
		$error  = isset( $_GET[ Reusable_Actions::ERROR_QUERY ] ) ? \sanitize_key( \wp_unslash( $_GET[ Reusable_Actions::ERROR_QUERY ] ) ) : '';

		if ( $notice ) {
			$messages = array(
				'created'    => __( 'Reusable component created.', 'nexa-pro-core' ),
				'saved'      => __( 'Reusable component saved.', 'nexa-pro-core' ),
				'duplicated' => __( 'Reusable component duplicated.', 'nexa-pro-core' ),
				'archived'   => __( 'Reusable component archived.', 'nexa-pro-core' ),
				'restored'   => __( 'Reusable component restored.', 'nexa-pro-core' ),
				'deleted'    => __( 'Reusable component deleted.', 'nexa-pro-core' ),
			);

			printf(
				'<div class="notice notice-success is-dismissible" role="status"><p>%s</p></div>',
				esc_html( isset( $messages[ $notice ] ) ? $messages[ $notice ] : __( 'Reusable action completed.', 'nexa-pro-core' ) )
			);
		}

		if ( $error ) {
			$messages = array(
				'nexa_pro_core_reusable_in_use'  => __( 'This reusable component is still linked. Archive it, or detach all instances before deleting.', 'nexa-pro-core' ),
				'nexa_pro_core_delete_unconfirmed' => __( 'Confirm deletion before deleting a reusable component.', 'nexa-pro-core' ),
				'nexa_pro_core_invalid_nonce'    => __( 'The request expired. Refresh the screen and try again.', 'nexa-pro-core' ),
				'nexa_pro_core_forbidden'        => __( 'You do not have permission to manage reusable components.', 'nexa-pro-core' ),
				'nexa_pro_core_missing_component' => __( 'The requested component could not be found.', 'nexa-pro-core' ),
			);

			printf(
				'<div class="notice notice-error" role="alert"><p>%s</p></div>',
				esc_html( isset( $messages[ $error ] ) ? $messages[ $error ] : __( 'Reusable request failed.', 'nexa-pro-core' ) )
			);
		}
	}

	/**
	 * Render reusable list.
	 *
	 * @return void
	 */
	private static function render_table() {
		$items = self::get_reusable_posts();
		?>
		<div class="nexa-pro-core-builder__panel">
			<h3><?php esc_html_e( 'Library', 'nexa-pro-core' ); ?></h3>
			<?php if ( empty( $items ) ) : ?>
				<p><?php esc_html_e( 'No reusable components have been created yet.', 'nexa-pro-core' ); ?></p>
			<?php else : ?>
				<table class="widefat striped nexa-pro-core-builder__table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Title', 'nexa-pro-core' ); ?></th>
							<th><?php esc_html_e( 'Type', 'nexa-pro-core' ); ?></th>
							<th><?php esc_html_e( 'Status', 'nexa-pro-core' ); ?></th>
							<th><?php esc_html_e( 'Linked uses', 'nexa-pro-core' ); ?></th>
							<th><?php esc_html_e( 'Pages using it', 'nexa-pro-core' ); ?></th>
							<th><?php esc_html_e( 'Updated', 'nexa-pro-core' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'nexa-pro-core' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $items as $post ) : ?>
							<?php self::render_table_row( $post ); ?>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render one table row.
	 *
	 * @param \WP_Post $post Post.
	 * @return void
	 */
	private static function render_table_row( $post ) {
		$payload = Reusable_Components::get_reusable_component( $post->ID );
		$type    = \is_wp_error( $payload ) ? '' : $payload['component_type'];
		$usage   = Reusable_Components::list_linked_page_instances( $post->ID );
		?>
		<tr>
			<td><?php echo esc_html( get_the_title( $post ) ); ?></td>
			<td><?php echo esc_html( $type ? Sanitizer::default_admin_title( $type ) : __( 'Invalid payload', 'nexa-pro-core' ) ); ?></td>
			<td><?php echo 'draft' === $post->post_status ? esc_html__( 'Archived', 'nexa-pro-core' ) : esc_html__( 'Active', 'nexa-pro-core' ); ?></td>
			<td><?php echo esc_html( count( $usage ) ); ?></td>
			<td><?php self::render_usage_pages( $usage ); ?></td>
			<td><?php echo esc_html( get_date_from_gmt( $post->post_modified_gmt, get_option( 'date_format' ) ) ); ?></td>
			<td class="nexa-pro-core-builder__table-actions">
				<a class="button" href="<?php echo esc_url( self::url( array( 'reusable_mode' => 'edit', 'reusable_id' => $post->ID ) ) ); ?>#nexa-pro-core-reusable-editor"><?php esc_html_e( 'Edit', 'nexa-pro-core' ); ?></a>
				<?php self::render_row_form( 'duplicate', 'nexa_pro_core_reusable_duplicate', $post->ID, __( 'Duplicate', 'nexa-pro-core' ) ); ?>
				<?php if ( 'draft' === $post->post_status ) : ?>
					<?php self::render_row_form( 'restore', 'nexa_pro_core_reusable_restore', $post->ID, __( 'Restore', 'nexa-pro-core' ) ); ?>
				<?php else : ?>
					<?php self::render_row_form( 'archive', 'nexa_pro_core_reusable_archive', $post->ID, __( 'Archive', 'nexa-pro-core' ) ); ?>
				<?php endif; ?>
				<?php self::render_delete_form( $post->ID, count( $usage ) ); ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render usage list.
	 *
	 * @param array $usage Usage entries.
	 * @return void
	 */
	private static function render_usage_pages( array $usage ) {
		if ( empty( $usage ) ) {
			esc_html_e( 'None', 'nexa-pro-core' );
			return;
		}

		echo '<ul class="nexa-pro-core-builder__usage-list">';
		foreach ( $usage as $entry ) {
			$page_id = absint( $entry['page_id'] );
			echo '<li>' . esc_html( get_the_title( $page_id ) ) . '</li>';
		}
		echo '</ul>';
	}

	/**
	 * Render a reusable editor form.
	 *
	 * @param int   $post_id   Reusable ID.
	 * @param array $component Component.
	 * @param array $registry  Registry.
	 * @return void
	 */
	private static function render_editor_form( $post_id, array $component, array $registry ) {
		$type       = ! empty( $component['component_type'] ) ? $component['component_type'] : self::first_reusable_type( $registry );
		if ( ! $post_id && ( empty( $registry[ $type ]['reusable']['eligible'] ) ) ) {
			$type = self::first_reusable_type( $registry );
		}
		$definition = isset( $registry[ $type ] ) ? $registry[ $type ] : reset( $registry );
		$usage      = $post_id ? Reusable_Components::list_linked_page_instances( $post_id ) : array();
		?>
		<h3><?php echo $post_id ? esc_html__( 'Edit reusable component', 'nexa-pro-core' ) : esc_html__( 'Create reusable component', 'nexa-pro-core' ); ?></h3>
		<?php if ( $usage ) : ?>
			<div class="notice notice-warning inline" role="status">
				<p>
					<?php
					printf(
						/* translators: %d: linked count. */
						esc_html__( 'Saving this source updates %d linked instance(s). Page-local navigation and placement are preserved.', 'nexa-pro-core' ),
						count( $usage )
					);
					?>
				</p>
			</div>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="nexa-pro-core-builder__component-form" data-builder-editor-form>
			<input type="hidden" name="action" value="nexa_pro_core_reusable_save">
			<input type="hidden" name="reusable_id" value="<?php echo esc_attr( $post_id ); ?>">
			<input type="hidden" name="component[instance_id]" value="<?php echo esc_attr( isset( $component['instance_id'] ) ? $component['instance_id'] : '' ); ?>">
			<input type="hidden" name="component[inheritance_mode]" value="local">
			<input type="hidden" name="component[reusable_component_id]" value="0">
			<?php wp_nonce_field( Reusable_Actions::nonce_action( 'save', $post_id ) ); ?>

			<details open>
				<summary><?php esc_html_e( 'Content', 'nexa-pro-core' ); ?></summary>
				<?php self::render_component_type_select( $registry, $type ); ?>
				<?php self::render_text( 'component[admin_title]', 'nexa-pro-core-reusable-title-field', __( 'Reusable title', 'nexa-pro-core' ), isset( $component['admin_title'] ) ? $component['admin_title'] : '' ); ?>
				<?php self::render_checkbox( 'component[enabled]', 'nexa-pro-core-reusable-enabled', __( 'Enabled by default', 'nexa-pro-core' ), ! empty( $component['enabled'] ) ); ?>
				<?php self::render_text( 'component[content][eyebrow]', 'nexa-pro-core-reusable-eyebrow', __( 'Eyebrow', 'nexa-pro-core' ), self::nested_value( $component, array( 'content', 'eyebrow' ) ) ); ?>
				<?php self::render_text( 'component[content][heading]', 'nexa-pro-core-reusable-heading', __( 'Heading', 'nexa-pro-core' ), self::nested_value( $component, array( 'content', 'heading' ) ) ); ?>
				<?php self::render_textarea( 'component[content][body]', 'nexa-pro-core-reusable-body', __( 'Body', 'nexa-pro-core' ), self::nested_value( $component, array( 'content', 'body' ) ) ); ?>
				<?php self::render_text( 'component[content][primary_cta_label]', 'nexa-pro-core-reusable-primary-label', __( 'Primary CTA label', 'nexa-pro-core' ), self::nested_value( $component, array( 'content', 'primary_cta_label' ) ) ); ?>
				<?php self::render_text( 'component[content][primary_cta_url]', 'nexa-pro-core-reusable-primary-url', __( 'Primary CTA URL', 'nexa-pro-core' ), self::nested_value( $component, array( 'content', 'primary_cta_url' ) ) ); ?>
			</details>

			<details>
				<summary><?php esc_html_e( 'Layout', 'nexa-pro-core' ); ?></summary>
				<?php
				self::render_select( 'component[layout]', 'nexa-pro-core-reusable-layout', __( 'Layout variation', 'nexa-pro-core' ), array_combine( $definition['supported_layouts'], $definition['supported_layouts'] ), isset( $component['layout'] ) ? $component['layout'] : self::first_layout( $definition ) );

				if ( self::supports_layout_control( $definition, 'container_width' ) ) {
					self::render_select( 'component[design][container_width]', 'nexa-pro-core-reusable-container-width', __( 'Container width', 'nexa-pro-core' ), self::container_width_options(), self::nested_value( $component, array( 'design', 'container_width' ), 'inherit' ) );
				}

				if ( self::supports_layout_control( $definition, 'content_alignment' ) ) {
					self::render_select( 'component[design][content_alignment]', 'nexa-pro-core-reusable-content-alignment', __( 'Content alignment', 'nexa-pro-core' ), self::alignment_options(), self::nested_value( $component, array( 'design', 'content_alignment' ), 'inherit' ) );
				}

				if ( self::supports_layout_control( $definition, 'section_spacing' ) ) {
					self::render_select( 'component[design][section_spacing]', 'nexa-pro-core-reusable-section-spacing', __( 'Section spacing', 'nexa-pro-core' ), self::spacing_options(), self::nested_value( $component, array( 'design', 'section_spacing' ), 'inherit' ) );
				}
				?>
			</details>

			<details>
				<summary><?php esc_html_e( 'Design', 'nexa-pro-core' ); ?></summary>
				<?php
				if ( self::supports_design_capability( $definition, 'design_preset' ) ) {
					self::render_select( 'component[design][preset]', 'nexa-pro-core-reusable-preset', __( 'Design preset', 'nexa-pro-core' ), self::design_preset_options(), self::nested_value( $component, array( 'design', 'preset' ), 'inherit' ) );
				}

				if ( self::supports_design_capability( $definition, 'background' ) ) {
					$background_type = self::nested_value( $component, array( 'design', 'background_type' ), 'inherit' );
					self::render_select( 'component[design][background_type]', 'nexa-pro-core-reusable-background-type', __( 'Background type', 'nexa-pro-core' ), self::background_options(), $background_type );
					self::render_text( 'component[design][background_color]', 'nexa-pro-core-reusable-background-color', __( 'Background color', 'nexa-pro-core' ), self::nested_value( $component, array( 'design', 'background_color' ) ) );
				}

				if ( self::supports_design_capability( $definition, 'text_theme' ) ) {
					self::render_select( 'component[design][text_theme]', 'nexa-pro-core-reusable-text-theme', __( 'Text theme', 'nexa-pro-core' ), self::text_theme_options(), self::nested_value( $component, array( 'design', 'text_theme' ), 'inherit' ) );
				}

				if ( self::supports_design_capability( $definition, 'card_style' ) ) {
					self::render_select( 'component[design][card_style]', 'nexa-pro-core-reusable-card-style', __( 'Card style', 'nexa-pro-core' ), self::card_style_options(), self::nested_value( $component, array( 'design', 'card_style' ), 'inherit' ) );
				}

				if ( self::supports_design_capability( $definition, 'radius' ) ) {
					self::render_select( 'component[design][radius]', 'nexa-pro-core-reusable-radius', __( 'Radius', 'nexa-pro-core' ), self::radius_options(), self::nested_value( $component, array( 'design', 'radius' ), 'inherit' ) );
				}

				if ( self::supports_design_capability( $definition, 'shadow' ) ) {
					self::render_select( 'component[design][shadow]', 'nexa-pro-core-reusable-shadow', __( 'Shadow', 'nexa-pro-core' ), self::shadow_options(), self::nested_value( $component, array( 'design', 'shadow' ), 'inherit' ) );
				}
				?>
			</details>

			<details>
				<summary><?php esc_html_e( 'Navigation defaults', 'nexa-pro-core' ); ?></summary>
				<?php self::render_checkbox( 'component[navigation][show_in_navigation]', 'nexa-pro-core-reusable-show-nav', __( 'Show in generated navigation by default', 'nexa-pro-core' ), (bool) self::nested_value( $component, array( 'navigation', 'show_in_navigation' ) ) ); ?>
				<?php self::render_text( 'component[navigation][navigation_label]', 'nexa-pro-core-reusable-nav-label', __( 'Navigation label', 'nexa-pro-core' ), self::nested_value( $component, array( 'navigation', 'navigation_label' ) ) ); ?>
				<?php self::render_text( 'component[navigation][anchor_id]', 'nexa-pro-core-reusable-anchor', __( 'Anchor ID', 'nexa-pro-core' ), self::nested_value( $component, array( 'navigation', 'anchor_id' ) ) ); ?>
				<?php self::render_checkbox( 'component[navigation][highlight_as_cta]', 'nexa-pro-core-reusable-cta-nav', __( 'Highlight as CTA', 'nexa-pro-core' ), (bool) self::nested_value( $component, array( 'navigation', 'highlight_as_cta' ) ) ); ?>
				<?php self::render_select( 'component[navigation][mobile_visibility]', 'nexa-pro-core-reusable-mobile-visibility', __( 'Mobile visibility', 'nexa-pro-core' ), self::visibility_options(), self::nested_value( $component, array( 'navigation', 'mobile_visibility' ), 'all' ) ); ?>
			</details>

			<details>
				<summary><?php esc_html_e( 'Advanced', 'nexa-pro-core' ); ?></summary>
				<?php self::render_text( 'component[advanced][custom_css_class]', 'nexa-pro-core-reusable-class', __( 'Custom CSS classes', 'nexa-pro-core' ), self::nested_value( $component, array( 'advanced', 'custom_css_class' ) ) ); ?>
				<?php self::render_text( 'component[advanced][aria_label]', 'nexa-pro-core-reusable-aria', __( 'ARIA label', 'nexa-pro-core' ), self::nested_value( $component, array( 'advanced', 'aria_label' ) ) ); ?>
				<?php self::render_select( 'component[advanced][semantic_element]', 'nexa-pro-core-reusable-semantic', __( 'Semantic element', 'nexa-pro-core' ), self::semantic_options(), self::nested_value( $component, array( 'advanced', 'semantic_element' ), 'section' ) ); ?>
			</details>

			<p>
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Save reusable component', 'nexa-pro-core' ); ?></button>
			</p>
		</form>
		<?php
	}

	/**
	 * Render insert panel.
	 *
	 * @return void
	 */
	private static function render_insert_panel() {
		$reusable = self::get_reusable_posts( array( 'private' ) );
		$pages    = Builder_Admin::get_editable_pages();

		if ( empty( $reusable ) || empty( $pages ) ) {
			return;
		}
		?>
		<div class="nexa-pro-core-builder__panel">
			<h3><?php esc_html_e( 'Insert into page', 'nexa-pro-core' ); ?></h3>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="nexa-pro-core-builder__filters">
				<input type="hidden" name="action" value="nexa_pro_core_reusable_insert">
				<?php wp_nonce_field( Reusable_Actions::nonce_action( 'insert', 0 ) ); ?>
				<label for="nexa-pro-core-insert-reusable"><?php esc_html_e( 'Reusable component', 'nexa-pro-core' ); ?></label>
				<select id="nexa-pro-core-insert-reusable" name="reusable_id">
					<?php foreach ( $reusable as $post ) : ?>
						<option value="<?php echo esc_attr( $post->ID ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></option>
					<?php endforeach; ?>
				</select>
				<label for="nexa-pro-core-insert-page"><?php esc_html_e( 'Target page', 'nexa-pro-core' ); ?></label>
				<select id="nexa-pro-core-insert-page" name="builder_page_id">
					<?php foreach ( $pages as $page ) : ?>
						<option value="<?php echo esc_attr( $page->ID ); ?>"><?php echo esc_html( get_the_title( $page ) ); ?></option>
					<?php endforeach; ?>
				</select>
				<label for="nexa-pro-core-insert-mode"><?php esc_html_e( 'Insertion mode', 'nexa-pro-core' ); ?></label>
				<select id="nexa-pro-core-insert-mode" name="insert_mode">
					<option value="linked"><?php esc_html_e( 'Linked instance', 'nexa-pro-core' ); ?></option>
					<option value="local"><?php esc_html_e( 'Independent local copy', 'nexa-pro-core' ); ?></option>
				</select>
				<button type="submit" class="button"><?php esc_html_e( 'Insert component', 'nexa-pro-core' ); ?></button>
			</form>
		</div>
		<?php
	}

	/**
	 * Get reusable posts.
	 *
	 * @param array $statuses Statuses.
	 * @return array
	 */
	private static function get_reusable_posts( $statuses = array( 'private', 'draft' ) ) {
		return \get_posts(
			array(
				'post_type'      => NEXA_PRO_CORE_REUSABLE_POST_TYPE,
				'post_status'    => $statuses,
				'posts_per_page' => 100,
				'orderby'        => 'modified',
				'order'          => 'DESC',
			)
		);
	}

	/**
	 * Create an empty component fixture.
	 *
	 * @param array $registry Registry.
	 * @return array
	 */
	private static function empty_component( array $registry ) {
		$type = self::first_reusable_type( $registry );

		return array(
			'component_type' => $type,
			'admin_title'    => Sanitizer::default_admin_title( $type ),
			'enabled'        => true,
			'layout'         => self::first_layout( isset( $registry[ $type ] ) ? $registry[ $type ] : array() ),
			'content'        => array(),
			'design'         => array(),
			'navigation'     => array(),
			'advanced'       => array(),
		);
	}

	/**
	 * Render a row action form.
	 */
	private static function render_row_form( $operation, $action, $post_id, $label ) {
		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="<?php echo esc_attr( $action ); ?>">
			<input type="hidden" name="reusable_id" value="<?php echo esc_attr( $post_id ); ?>">
			<?php wp_nonce_field( Reusable_Actions::nonce_action( $operation, $post_id ) ); ?>
			<button type="submit" class="button"><?php echo esc_html( $label ); ?></button>
		</form>
		<?php
	}

	/**
	 * Render delete form.
	 */
	private static function render_delete_form( $post_id, $usage_count ) {
		$confirm_id = 'nexa-pro-core-reusable-delete-' . absint( $post_id );
		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="nexa-pro-core-builder__delete-form">
			<input type="hidden" name="action" value="nexa_pro_core_reusable_delete">
			<input type="hidden" name="reusable_id" value="<?php echo esc_attr( $post_id ); ?>">
			<?php wp_nonce_field( Reusable_Actions::nonce_action( 'delete', $post_id ) ); ?>
			<?php if ( $usage_count ) : ?>
				<label>
					<select name="delete_mode">
						<option value="block"><?php esc_html_e( 'Keep linked instances', 'nexa-pro-core' ); ?></option>
						<option value="detach-all"><?php esc_html_e( 'Detach all then delete', 'nexa-pro-core' ); ?></option>
					</select>
				</label>
			<?php endif; ?>
			<label for="<?php echo esc_attr( $confirm_id ); ?>">
				<input id="<?php echo esc_attr( $confirm_id ); ?>" type="checkbox" name="confirm_delete" value="1" required>
				<?php esc_html_e( 'Confirm', 'nexa-pro-core' ); ?>
			</label>
			<button type="submit" class="button button-link-delete"><?php esc_html_e( 'Delete', 'nexa-pro-core' ); ?></button>
		</form>
		<?php
	}

	/**
	 * Render component type select.
	 */
	private static function render_component_type_select( array $registry, $selected ) {
		$options = array();

		foreach ( $registry as $type => $definition ) {
			if ( ! empty( $definition['reusable']['eligible'] ) ) {
				$options[ $type ] = $definition['label'];
			}
		}

		self::render_select( 'component[component_type]', 'nexa-pro-core-reusable-component-type', __( 'Component type', 'nexa-pro-core' ), $options, $selected );
	}

	/**
	 * Get the first reusable-eligible component type.
	 *
	 * @param array $registry Registry.
	 * @return string
	 */
	private static function first_reusable_type( array $registry ) {
		foreach ( $registry as $type => $definition ) {
			if ( ! empty( $definition['reusable']['eligible'] ) ) {
				return $type;
			}
		}

		return (string) key( $registry );
	}

	/**
	 * Build URL for reusable view.
	 */
	private static function url( $extra = array() ) {
		return \add_query_arg(
			array_merge(
				array(
					'page'         => Builder_Admin::PAGE_SLUG,
					'builder_view' => 'reusable',
				),
				$extra
			),
			\admin_url( 'themes.php' )
		);
	}

	/**
	 * Nested scalar helper.
	 */
	private static function nested_value( array $array, array $path, $default = '' ) {
		$value = $array;

		foreach ( $path as $part ) {
			if ( ! is_array( $value ) || ! array_key_exists( $part, $value ) ) {
				return $default;
			}

			$value = $value[ $part ];
		}

		return is_scalar( $value ) ? $value : $default;
	}

	/**
	 * Field helpers.
	 */
	private static function render_text( $name, $id, $label, $value ) {
		printf( '<p><label for="%1$s">%2$s</label><input class="regular-text" type="text" id="%1$s" name="%3$s" value="%4$s"></p>', esc_attr( $id ), esc_html( $label ), esc_attr( $name ), esc_attr( $value ) );
	}

	private static function render_textarea( $name, $id, $label, $value ) {
		printf( '<p><label for="%1$s">%2$s</label><textarea class="large-text" rows="4" id="%1$s" name="%3$s">%4$s</textarea></p>', esc_attr( $id ), esc_html( $label ), esc_attr( $name ), esc_textarea( $value ) );
	}

	private static function render_checkbox( $name, $id, $label, $checked ) {
		printf( '<p><input type="hidden" name="%1$s" value="0"><label for="%2$s"><input type="checkbox" id="%2$s" name="%1$s" value="1"%3$s> %4$s</label></p>', esc_attr( $name ), esc_attr( $id ), checked( $checked, true, false ), esc_html( $label ) );
	}

	private static function render_select( $name, $id, $label, array $options, $selected ) {
		printf( '<p><label for="%1$s">%2$s</label><select id="%1$s" name="%3$s">', esc_attr( $id ), esc_html( $label ), esc_attr( $name ) );

		foreach ( $options as $value => $option_label ) {
			printf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( $value ), selected( $selected, $value, false ), esc_html( $option_label ) );
		}

		echo '</select></p>';
	}

	private static function first_layout( array $definition ) {
		return ! empty( $definition['default_layout'] ) ? $definition['default_layout'] : ( ! empty( $definition['supported_layouts'][0] ) ? $definition['supported_layouts'][0] : 'default' );
	}

	private static function supports_layout_control( array $definition, $control ) {
		$controls = ! empty( $definition['layout_controls'] ) && is_array( $definition['layout_controls'] ) ? $definition['layout_controls'] : array( 'layout' );

		return in_array( \sanitize_key( $control ), $controls, true );
	}

	private static function supports_design_capability( array $definition, $capability ) {
		$capabilities = ! empty( $definition['design_capabilities'] ) && is_array( $definition['design_capabilities'] ) ? $definition['design_capabilities'] : array();

		return in_array( \sanitize_key( $capability ), $capabilities, true );
	}

	private static function alignment_options() {
		return array( 'inherit' => __( 'Inherit', 'nexa-pro-core' ), 'left' => __( 'Left', 'nexa-pro-core' ), 'center' => __( 'Center', 'nexa-pro-core' ), 'right' => __( 'Right', 'nexa-pro-core' ) );
	}

	private static function container_width_options() {
		return array( 'inherit' => __( 'Inherit', 'nexa-pro-core' ), 'narrow' => __( 'Narrow', 'nexa-pro-core' ), 'standard' => __( 'Standard', 'nexa-pro-core' ), 'wide' => __( 'Wide', 'nexa-pro-core' ), 'full' => __( 'Full width', 'nexa-pro-core' ) );
	}

	private static function spacing_options() {
		return array( 'inherit' => __( 'Inherit', 'nexa-pro-core' ), 'none' => __( 'None', 'nexa-pro-core' ), 'compact' => __( 'Compact', 'nexa-pro-core' ), 'standard' => __( 'Standard', 'nexa-pro-core' ), 'spacious' => __( 'Spacious', 'nexa-pro-core' ), 'extra-spacious' => __( 'Extra spacious', 'nexa-pro-core' ) );
	}

	private static function background_options() {
		return array( 'inherit' => __( 'Inherit', 'nexa-pro-core' ), 'solid' => __( 'Solid', 'nexa-pro-core' ), 'gradient' => __( 'Gradient', 'nexa-pro-core' ), 'image' => __( 'Image', 'nexa-pro-core' ) );
	}

	private static function text_theme_options() {
		return array( 'inherit' => __( 'Inherit', 'nexa-pro-core' ), 'automatic' => __( 'Automatic', 'nexa-pro-core' ), 'light' => __( 'Light', 'nexa-pro-core' ), 'dark' => __( 'Dark', 'nexa-pro-core' ) );
	}

	private static function card_style_options() {
		return array( 'inherit' => __( 'Inherit', 'nexa-pro-core' ), 'flat' => __( 'Flat', 'nexa-pro-core' ), 'bordered' => __( 'Bordered', 'nexa-pro-core' ), 'elevated' => __( 'Elevated', 'nexa-pro-core' ), 'glass' => __( 'Glass', 'nexa-pro-core' ), 'minimal' => __( 'Minimal', 'nexa-pro-core' ) );
	}

	private static function radius_options() {
		return array( 'inherit' => __( 'Inherit', 'nexa-pro-core' ), 'none' => __( 'None', 'nexa-pro-core' ), 'small' => __( 'Small', 'nexa-pro-core' ), 'medium' => __( 'Medium', 'nexa-pro-core' ), 'large' => __( 'Large', 'nexa-pro-core' ), 'pill' => __( 'Pill', 'nexa-pro-core' ) );
	}

	private static function shadow_options() {
		return array( 'inherit' => __( 'Inherit', 'nexa-pro-core' ), 'none' => __( 'None', 'nexa-pro-core' ), 'subtle' => __( 'Subtle', 'nexa-pro-core' ), 'medium' => __( 'Medium', 'nexa-pro-core' ), 'strong' => __( 'Strong', 'nexa-pro-core' ) );
	}

	private static function design_preset_options() {
		$options = array();

		if ( function_exists( 'nexa_pro_get_component_design_presets' ) ) {
			foreach ( \nexa_pro_get_component_design_presets() as $key => $preset ) {
				$options[ \sanitize_key( $key ) ] = ! empty( $preset['label'] ) ? $preset['label'] : $key;
			}
		}

		if ( empty( $options ) ) {
			foreach ( Sanitizer::allowed_design_presets() as $key ) {
				$options[ $key ] = ucwords( str_replace( '-', ' ', $key ) );
			}
		}

		return $options;
	}

	private static function visibility_options() {
		return array( 'all' => __( 'All devices', 'nexa-pro-core' ), 'mobile' => __( 'Mobile only', 'nexa-pro-core' ), 'desktop' => __( 'Desktop only', 'nexa-pro-core' ), 'hidden' => __( 'Hidden', 'nexa-pro-core' ) );
	}

	private static function semantic_options() {
		return array( 'section' => __( 'Section', 'nexa-pro-core' ), 'div' => __( 'Div', 'nexa-pro-core' ), 'article' => __( 'Article', 'nexa-pro-core' ), 'aside' => __( 'Aside', 'nexa-pro-core' ), 'header' => __( 'Header', 'nexa-pro-core' ), 'footer' => __( 'Footer', 'nexa-pro-core' ) );
	}
}
