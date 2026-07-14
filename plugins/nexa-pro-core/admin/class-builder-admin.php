<?php
/**
 * Builder admin screen.
 *
 * @package Nexa_Pro_Core
 */

namespace Nexa_Pro_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builder admin screen service.
 */
final class Builder_Admin {
	const PAGE_SLUG = 'nexa-pro-builder';

	/**
	 * Builder hook suffix.
	 *
	 * @var string
	 */
	private static $hook_suffix = '';

	/**
	 * Register admin hooks.
	 *
	 * @return void
	 */
	public static function register() {
		\add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		\add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	/**
	 * Register the builder under Appearance.
	 *
	 * @return void
	 */
	public static function register_menu() {
		self::$hook_suffix = \add_theme_page(
			\__( 'Nexa Pro Builder', 'nexa-pro-core' ),
			\__( 'Nexa Pro Builder', 'nexa-pro-core' ),
			Capabilities::MANAGE_COMPONENTS,
			self::PAGE_SLUG,
			array( __CLASS__, 'render_screen' )
		);
	}

	/**
	 * Enqueue builder-only assets.
	 *
	 * @param string $hook_suffix Current admin hook suffix.
	 * @return void
	 */
	public static function enqueue_assets( $hook_suffix ) {
		if ( self::$hook_suffix !== $hook_suffix ) {
			return;
		}

		$css_path = NEXA_PRO_CORE_DIR . 'assets/css/builder-admin.css';
		$js_path  = NEXA_PRO_CORE_DIR . 'assets/js/builder-admin.js';

		if ( file_exists( $css_path ) ) {
			\wp_enqueue_style(
				'nexa-pro-core-builder-admin',
				NEXA_PRO_CORE_URL . 'assets/css/builder-admin.css',
				array(),
				(string) filemtime( $css_path )
			);
		}

		if ( file_exists( $js_path ) ) {
			\wp_enqueue_script(
				'nexa-pro-core-builder-admin',
				NEXA_PRO_CORE_URL . 'assets/js/builder-admin.js',
				array(),
				(string) filemtime( $js_path ),
				true
			);
		}
	}

	/**
	 * Render the builder screen.
	 *
	 * @return void
	 */
	public static function render_screen() {
		$view = self::get_current_view();

		if ( 'reusable' === $view && ! class_exists( Reusable_Admin::class ) ) {
			$view = 'components';
		}

		if ( 'reusable' === $view && ! Capabilities::current_user_can_manage_reusable_components() ) {
			\wp_die( \esc_html__( 'You do not have permission to manage reusable components.', 'nexa-pro-core' ) );
		}

		if ( 'reusable' !== $view && ! Capabilities::current_user_can_manage_components() ) {
			\wp_die( \esc_html__( 'You do not have permission to manage Nexa Pro components.', 'nexa-pro-core' ) );
		}

		if ( 'navigation' === $view ) {
			?>
			<div class="wrap nexa-pro-core-builder">
				<h1><?php esc_html_e( 'Nexa Pro Builder', 'nexa-pro-core' ); ?></h1>
				<?php self::render_view_tabs( $view ); ?>
				<?php Navigation_Admin::render(); ?>
			</div>
			<?php
			return;
		}

		if ( 'migration' === $view ) {
			?>
			<div class="wrap nexa-pro-core-builder">
				<h1><?php esc_html_e( 'Nexa Pro Builder', 'nexa-pro-core' ); ?></h1>
				<?php self::render_view_tabs( $view ); ?>
				<?php self::render_migration_screen(); ?>
			</div>
			<?php
			return;
		}

		if ( 'transfer' === $view ) {
			?>
			<div class="wrap nexa-pro-core-builder">
				<h1><?php esc_html_e( 'Nexa Pro Builder', 'nexa-pro-core' ); ?></h1>
				<?php self::render_view_tabs( $view ); ?>
				<?php self::render_transfer_screen(); ?>
			</div>
			<?php
			return;
		}

		if ( 'reusable' === $view && class_exists( Reusable_Admin::class ) ) {
			?>
			<div class="wrap nexa-pro-core-builder">
				<h1><?php esc_html_e( 'Nexa Pro Builder', 'nexa-pro-core' ); ?></h1>
				<?php self::render_view_tabs( $view ); ?>
				<?php Reusable_Admin::render(); ?>
			</div>
			<?php
			return;
		}

		$filters       = self::get_filters();
		$pages         = self::get_editable_pages( $filters );
		$selected_page = self::get_selected_page( $pages );
		$components    = $selected_page ? Storage::get_page_components( $selected_page->ID ) : array();

		if ( \is_wp_error( $components ) ) {
			$components = array();
		}

		$registry = self::get_component_registry();
		?>
		<div class="wrap nexa-pro-core-builder">
			<h1><?php esc_html_e( 'Nexa Pro Builder', 'nexa-pro-core' ); ?></h1>
			<?php self::render_view_tabs( $view ); ?>
			<p class="nexa-pro-core-builder__intro">
				<?php esc_html_e( 'Provided by Nexa Pro Core. Compose page-level component instances without replacing page content or legacy theme rendering.', 'nexa-pro-core' ); ?>
			</p>

			<?php self::render_registry_notice(); ?>
			<?php self::render_notices(); ?>

			<div class="nexa-pro-core-builder__layout">
				<?php self::render_page_panel( $pages, $selected_page, $filters ); ?>

				<main class="nexa-pro-core-builder__main" id="nexa-pro-core-builder-main">
					<?php
					if ( $selected_page ) {
						self::render_component_stack( $selected_page, $pages, $components, $registry );
					} else {
						self::render_empty_pages_state();
					}
					?>
				</main>

				<aside class="nexa-pro-core-builder__editor" aria-labelledby="nexa-pro-core-builder-editor-title">
					<?php
					if ( $selected_page ) {
						self::render_editor_panel( $selected_page, $components, $registry );
					} else {
						echo '<h2 id="nexa-pro-core-builder-editor-title">' . esc_html__( 'Component editor', 'nexa-pro-core' ) . '</h2>';
						echo '<p>' . esc_html__( 'Create or select a page before editing components.', 'nexa-pro-core' ) . '</p>';
					}
					?>
				</aside>
			</div>
		</div>
		<?php
	}

	/**
	 * Get current builder view.
	 *
	 * @return string
	 */
	private static function get_current_view() {
		$view = isset( $_GET['builder_view'] ) ? \sanitize_key( \wp_unslash( $_GET['builder_view'] ) ) : 'components';

		return in_array( $view, array( 'components', 'navigation', 'reusable', 'migration', 'transfer' ), true ) ? $view : 'components';
	}

	/**
	 * Render view tabs.
	 *
	 * @param string $current Current view.
	 * @return void
	 */
	private static function render_view_tabs( $current ) {
		$tabs = array(
			'components' => __( 'Pages & Components', 'nexa-pro-core' ),
			'navigation' => __( 'Navigation', 'nexa-pro-core' ),
			'reusable'   => __( 'Reusable Components', 'nexa-pro-core' ),
			'migration'  => __( 'Migration', 'nexa-pro-core' ),
			'transfer'   => __( 'Import/Export', 'nexa-pro-core' ),
		);
		?>
		<nav class="nav-tab-wrapper nexa-pro-core-builder__tabs" aria-label="<?php esc_attr_e( 'Nexa Pro Builder sections', 'nexa-pro-core' ); ?>">
			<?php foreach ( $tabs as $view => $label ) : ?>
				<?php
				if ( 'reusable' === $view && ( ! class_exists( Reusable_Admin::class ) || ! Capabilities::current_user_can_manage_reusable_components() ) ) {
					continue;
				}

				if ( 'reusable' !== $view && ! Capabilities::current_user_can_manage_components() ) {
					continue;
				}

				$url = \add_query_arg(
					array(
						'page'         => self::PAGE_SLUG,
						'builder_view' => $view,
					),
					\admin_url( 'themes.php' )
				);
				?>
				<a class="nav-tab <?php echo $current === $view ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( $url ); ?>" <?php echo $current === $view ? 'aria-current="page"' : ''; ?>>
					<?php echo esc_html( $label ); ?>
				</a>
			<?php endforeach; ?>
		</nav>
		<?php
	}

	/**
	 * Render migration screen.
	 *
	 * @return void
	 */
	private static function render_migration_screen() {
		$pages          = self::get_editable_pages();
		$target_page_id = isset( $_GET['target_page_id'] ) ? absint( \wp_unslash( $_GET['target_page_id'] ) ) : 0;
		$target_page_id = Migration::normalize_target_page_id( $target_page_id );
		$mode           = isset( $_GET['migration_mode'] ) ? Migration::sanitize_mode( \wp_unslash( $_GET['migration_mode'] ) ) : 'merge';
		$detection      = Migration::detect( $target_page_id );
		$state          = Migration::get_state();
		$preview        = array();

		if ( ! empty( $_GET['migration_preview'] ) ) {
			$preview = Migration::preview( $target_page_id, $mode );
		}

		Migration_Actions::render_notices();
		?>
		<div class="nexa-pro-core-builder__section">
			<p class="nexa-pro-core-builder__intro">
				<?php esc_html_e( 'Preview and migrate Nexa Pro 1.0 fixed-section settings into plugin-owned page components. Legacy theme options are never deleted.', 'nexa-pro-core' ); ?>
			</p>

			<div class="nexa-pro-core-builder__summary-grid" role="list">
				<?php
				self::render_summary_card( __( 'Migration status', 'nexa-pro-core' ), self::status_label_for_migration( $state['status'] ) );
				self::render_summary_card( __( 'Compatibility mode', 'nexa-pro-core' ), Compatibility_Mode::get_mode() );
				self::render_summary_card( __( 'Source hash', 'nexa-pro-core' ), $detection['migration_hash'] ? $detection['migration_hash'] : __( 'No source data', 'nexa-pro-core' ) );
				self::render_summary_card( __( 'Existing builder data', 'nexa-pro-core' ), ! empty( $detection['has_builder_data'] ) ? __( 'Detected', 'nexa-pro-core' ) : __( 'None detected', 'nexa-pro-core' ) );
				?>
			</div>

			<?php self::render_notice_list( isset( $detection['warnings'] ) ? $detection['warnings'] : array(), 'warning' ); ?>

			<div class="nexa-pro-core-builder__two-column">
				<section class="nexa-pro-core-builder__panel" aria-labelledby="nexa-pro-core-migration-preview-title">
					<h2 id="nexa-pro-core-migration-preview-title"><?php esc_html_e( 'Migration preview', 'nexa-pro-core' ); ?></h2>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="nexa_pro_core_migration_preview">
						<?php wp_nonce_field( Migration_Actions::nonce_action( 'preview' ) ); ?>
						<p>
							<label for="nexa-pro-core-migration-page"><?php esc_html_e( 'Target page', 'nexa-pro-core' ); ?></label>
							<select id="nexa-pro-core-migration-page" name="target_page_id">
								<?php self::render_page_options( $pages, $target_page_id ); ?>
							</select>
						</p>
						<p>
							<label for="nexa-pro-core-migration-mode"><?php esc_html_e( 'Migration mode', 'nexa-pro-core' ); ?></label>
							<select id="nexa-pro-core-migration-mode" name="migration_mode">
								<?php self::render_options( self::migration_modes(), $mode ); ?>
							</select>
						</p>
						<p class="description"><?php esc_html_e( 'Merge preserves existing builder components. Replace mode overwrites only plugin-managed page component data after backup and explicit confirmation.', 'nexa-pro-core' ); ?></p>
						<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Preview migration', 'nexa-pro-core' ); ?></button></p>
					</form>
				</section>

				<section class="nexa-pro-core-builder__panel" aria-labelledby="nexa-pro-core-compatibility-title">
					<h2 id="nexa-pro-core-compatibility-title"><?php esc_html_e( 'Compatibility mode', 'nexa-pro-core' ); ?></h2>
					<p><?php esc_html_e( 'Legacy mode keeps the Nexa Pro 1.0 fixed-section frontend active. Builder mode lets the theme render plugin-managed components when a page has valid builder data.', 'nexa-pro-core' ); ?></p>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="nexa_pro_core_compatibility_mode_save">
						<?php wp_nonce_field( Migration_Actions::nonce_action( 'mode' ) ); ?>
						<label for="nexa-pro-core-compatibility-mode"><?php esc_html_e( 'Frontend mode', 'nexa-pro-core' ); ?></label>
						<select id="nexa-pro-core-compatibility-mode" name="compatibility_mode">
							<?php self::render_options( array( 'legacy' => __( 'Legacy fixed sections', 'nexa-pro-core' ), 'builder' => __( 'Builder components', 'nexa-pro-core' ) ), Compatibility_Mode::get_mode() ); ?>
						</select>
						<p><button type="submit" class="button"><?php esc_html_e( 'Save mode', 'nexa-pro-core' ); ?></button></p>
					</form>
				</section>
			</div>

			<?php if ( $preview ) : ?>
				<?php self::render_migration_preview_summary( $preview ); ?>
			<?php endif; ?>

			<div class="nexa-pro-core-builder__two-column">
				<section class="nexa-pro-core-builder__panel" aria-labelledby="nexa-pro-core-migration-apply-title">
					<h2 id="nexa-pro-core-migration-apply-title"><?php esc_html_e( 'Apply migration', 'nexa-pro-core' ); ?></h2>
					<p><?php esc_html_e( 'Applying migration writes plugin-owned builder data for the selected target page. A rollback backup is created first.', 'nexa-pro-core' ); ?></p>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="nexa_pro_core_migration_apply">
						<input type="hidden" name="target_page_id" value="<?php echo esc_attr( $target_page_id ); ?>">
						<input type="hidden" name="migration_mode" value="<?php echo esc_attr( $mode ); ?>">
						<?php wp_nonce_field( Migration_Actions::nonce_action( 'apply' ) ); ?>
						<p>
							<label>
								<input type="checkbox" name="switch_builder_mode" value="1">
								<?php esc_html_e( 'Switch frontend compatibility mode to builder after migration', 'nexa-pro-core' ); ?>
							</label>
						</p>
						<p>
							<label>
								<input type="checkbox" name="confirm_migration" value="1" required>
								<?php esc_html_e( 'I understand this writes builder data while preserving legacy settings.', 'nexa-pro-core' ); ?>
							</label>
						</p>
						<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Apply migration', 'nexa-pro-core' ); ?></button></p>
					</form>
				</section>

				<section class="nexa-pro-core-builder__panel" aria-labelledby="nexa-pro-core-migration-rollback-title">
					<h2 id="nexa-pro-core-migration-rollback-title"><?php esc_html_e( 'Rollback and report', 'nexa-pro-core' ); ?></h2>
					<p><?php esc_html_e( 'Rollback restores the latest migration backup for page components, navigation settings, and compatibility mode. Legacy options remain untouched.', 'nexa-pro-core' ); ?></p>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="nexa_pro_core_migration_rollback">
						<?php wp_nonce_field( Migration_Actions::nonce_action( 'rollback' ) ); ?>
						<p>
							<label>
								<input type="checkbox" name="confirm_rollback" value="1" required>
								<?php esc_html_e( 'I understand rollback restores the latest migration backup.', 'nexa-pro-core' ); ?>
							</label>
						</p>
						<p><button type="submit" class="button"><?php esc_html_e( 'Roll back migration', 'nexa-pro-core' ); ?></button></p>
					</form>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="nexa_pro_core_migration_report_download">
						<?php wp_nonce_field( Migration_Actions::nonce_action( 'report' ) ); ?>
						<p><button type="submit" class="button"><?php esc_html_e( 'Download migration report', 'nexa-pro-core' ); ?></button></p>
					</form>
				</section>
			</div>
		</div>
		<?php
	}

	/**
	 * Render transfer screen.
	 *
	 * @return void
	 */
	private static function render_transfer_screen() {
		$pages         = self::get_editable_pages();
		$token         = isset( $_GET['import_token'] ) ? \sanitize_text_field( \wp_unslash( $_GET['import_token'] ) ) : '';
		$conflict_mode = isset( $_GET['conflict_mode'] ) ? \sanitize_key( \wp_unslash( $_GET['conflict_mode'] ) ) : 'skip';
		$payload       = $token ? Transfer::get_preview_payload( $token ) : array();
		$preview       = $payload ? Transfer::preview_import( $payload ) : array();

		Transfer_Actions::render_notices();
		?>
		<div class="nexa-pro-core-builder__section">
			<p class="nexa-pro-core-builder__intro">
				<?php esc_html_e( 'Export and import plugin-owned builder data with a preview-first flow. Imports validate product, schema, record limits, and component data before anything is applied.', 'nexa-pro-core' ); ?>
			</p>

			<div class="nexa-pro-core-builder__two-column">
				<section class="nexa-pro-core-builder__panel" aria-labelledby="nexa-pro-core-export-title">
					<h2 id="nexa-pro-core-export-title"><?php esc_html_e( 'Export', 'nexa-pro-core' ); ?></h2>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="nexa_pro_core_export">
						<?php wp_nonce_field( Transfer_Actions::nonce_action( 'export' ) ); ?>
						<p>
							<label for="nexa-pro-core-export-scope"><?php esc_html_e( 'Export scope', 'nexa-pro-core' ); ?></label>
							<select id="nexa-pro-core-export-scope" name="export_scope">
								<?php self::render_options( self::export_scopes(), 'full-site' ); ?>
							</select>
						</p>
						<p>
							<label for="nexa-pro-core-export-page"><?php esc_html_e( 'Page for one-page export', 'nexa-pro-core' ); ?></label>
							<select id="nexa-pro-core-export-page" name="export_page_id">
								<option value="0"><?php esc_html_e( 'Choose a page when needed', 'nexa-pro-core' ); ?></option>
								<?php self::render_page_options( $pages, 0 ); ?>
							</select>
						</p>
						<p class="description"><?php esc_html_e( 'Full-site exports include global settings, navigation, pages, reusable components, and migration metadata without private backup payloads.', 'nexa-pro-core' ); ?></p>
						<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Download export', 'nexa-pro-core' ); ?></button></p>
					</form>
				</section>

				<section class="nexa-pro-core-builder__panel" aria-labelledby="nexa-pro-core-import-title">
					<h2 id="nexa-pro-core-import-title"><?php esc_html_e( 'Import preview', 'nexa-pro-core' ); ?></h2>
					<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="nexa_pro_core_import_preview">
						<?php wp_nonce_field( Transfer_Actions::nonce_action( 'import_preview' ) ); ?>
						<p>
							<label for="nexa-pro-core-import-file"><?php esc_html_e( 'Import JSON file', 'nexa-pro-core' ); ?></label>
							<input id="nexa-pro-core-import-file" type="file" name="import_file" accept="application/json,.json">
						</p>
						<p>
							<label for="nexa-pro-core-import-json"><?php esc_html_e( 'Or paste JSON', 'nexa-pro-core' ); ?></label>
							<textarea id="nexa-pro-core-import-json" name="import_json" rows="8"></textarea>
						</p>
						<p>
							<label for="nexa-pro-core-conflict-mode"><?php esc_html_e( 'Conflict handling', 'nexa-pro-core' ); ?></label>
							<select id="nexa-pro-core-conflict-mode" name="conflict_mode">
								<?php self::render_options( self::conflict_modes(), $conflict_mode ); ?>
							</select>
						</p>
						<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Preview import', 'nexa-pro-core' ); ?></button></p>
					</form>
				</section>
			</div>

			<?php if ( is_array( $preview ) && ! \is_wp_error( $preview ) && ! empty( $preview['preview'] ) ) : ?>
				<?php self::render_import_preview_summary( $preview['preview'], $token, $conflict_mode, $pages, $payload ); ?>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render a compact summary card.
	 *
	 * @param string $label Label.
	 * @param string $value Value.
	 * @return void
	 */
	private static function render_summary_card( $label, $value ) {
		?>
		<div class="nexa-pro-core-builder__summary-card" role="listitem">
			<strong><?php echo esc_html( $label ); ?></strong>
			<span><?php echo esc_html( $value ); ?></span>
		</div>
		<?php
	}

	/**
	 * Render notice list.
	 *
	 * @param array  $messages Messages.
	 * @param string $type     Type.
	 * @return void
	 */
	private static function render_notice_list( array $messages, $type = 'warning' ) {
		if ( empty( $messages ) ) {
			return;
		}

		$class = 'error' === $type ? 'notice-error' : 'notice-warning';
		?>
		<div class="notice <?php echo esc_attr( $class ); ?>" role="<?php echo 'error' === $type ? 'alert' : 'status'; ?>">
			<ul>
				<?php foreach ( $messages as $message ) : ?>
					<li><?php echo esc_html( $message ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}

	/**
	 * Render page options.
	 *
	 * @param array $pages    Pages.
	 * @param int   $selected Selected page ID.
	 * @return void
	 */
	private static function render_page_options( array $pages, $selected ) {
		foreach ( $pages as $page ) {
			printf(
				'<option value="%1$d" %2$s>%3$s</option>',
				absint( $page->ID ),
				selected( absint( $selected ), absint( $page->ID ), false ),
				esc_html( get_the_title( $page ) )
			);
		}
	}

	/**
	 * Migration mode labels.
	 *
	 * @return array
	 */
	private static function migration_modes() {
		return array(
			'merge'                => __( 'Merge with existing builder data', 'nexa-pro-core' ),
			'replace-builder-data' => __( 'Replace existing builder data after backup', 'nexa-pro-core' ),
		);
	}

	/**
	 * Export scope labels.
	 *
	 * @return array
	 */
	private static function export_scopes() {
		return array(
			'full-site'           => __( 'Full site configuration', 'nexa-pro-core' ),
			'global-settings'     => __( 'Global visual settings', 'nexa-pro-core' ),
			'navigation'          => __( 'Navigation settings', 'nexa-pro-core' ),
			'one-page'            => __( 'One page', 'nexa-pro-core' ),
			'selected-pages'      => __( 'Selected pages', 'nexa-pro-core' ),
			'selected-components' => __( 'Selected components', 'nexa-pro-core' ),
			'reusable-components' => __( 'Reusable components', 'nexa-pro-core' ),
			'migration-report'    => __( 'Migration report', 'nexa-pro-core' ),
		);
	}

	/**
	 * Import conflict labels.
	 *
	 * @return array
	 */
	private static function conflict_modes() {
		return array(
			'skip'       => __( 'Skip pages with existing builder data', 'nexa-pro-core' ),
			'merge'      => __( 'Merge into mapped pages', 'nexa-pro-core' ),
			'replace'    => __( 'Replace mapped page builder data', 'nexa-pro-core' ),
			'create-new' => __( 'Create new draft pages when unmapped', 'nexa-pro-core' ),
		);
	}

	/**
	 * Migration status label.
	 *
	 * @param string $status Status.
	 * @return string
	 */
	private static function status_label_for_migration( $status ) {
		$labels = array(
			'not_needed'  => __( 'Not needed', 'nexa-pro-core' ),
			'available'   => __( 'Available', 'nexa-pro-core' ),
			'previewed'   => __( 'Previewed', 'nexa-pro-core' ),
			'completed'   => __( 'Completed', 'nexa-pro-core' ),
			'failed'      => __( 'Failed', 'nexa-pro-core' ),
			'rolled_back' => __( 'Rolled back', 'nexa-pro-core' ),
		);

		return isset( $labels[ $status ] ) ? $labels[ $status ] : $status;
	}

	/**
	 * Render migration preview details.
	 *
	 * @param array $preview Preview.
	 * @return void
	 */
	private static function render_migration_preview_summary( array $preview ) {
		$components = ! empty( $preview['components'] ) && is_array( $preview['components'] ) ? $preview['components'] : array();
		?>
		<section class="nexa-pro-core-builder__panel" aria-labelledby="nexa-pro-core-migration-preview-summary-title">
			<h2 id="nexa-pro-core-migration-preview-summary-title"><?php esc_html_e( 'Preview summary', 'nexa-pro-core' ); ?></h2>
			<div class="nexa-pro-core-builder__summary-grid" role="list">
				<?php
				self::render_summary_card( __( 'Mode', 'nexa-pro-core' ), isset( $preview['mode'] ) ? $preview['mode'] : 'merge' );
				self::render_summary_card( __( 'Components to create', 'nexa-pro-core' ), (string) count( $components ) );
				self::render_summary_card( __( 'Fields mapped', 'nexa-pro-core' ), isset( $preview['fields_migrated'] ) ? (string) absint( $preview['fields_migrated'] ) : '0' );
				self::render_summary_card( __( 'Existing target components', 'nexa-pro-core' ), isset( $preview['existing_components'] ) ? (string) absint( $preview['existing_components'] ) : '0' );
				?>
			</div>
			<?php self::render_notice_list( ! empty( $preview['warnings'] ) && is_array( $preview['warnings'] ) ? $preview['warnings'] : array(), 'warning' ); ?>
			<?php self::render_notice_list( ! empty( $preview['errors'] ) && is_array( $preview['errors'] ) ? $preview['errors'] : array(), 'error' ); ?>

			<?php if ( ! empty( $preview['legacy_order'] ) && is_array( $preview['legacy_order'] ) ) : ?>
				<p><strong><?php esc_html_e( 'Legacy order:', 'nexa-pro-core' ); ?></strong> <?php echo esc_html( implode( ', ', array_map( 'sanitize_key', $preview['legacy_order'] ) ) ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $preview['unsupported_sections'] ) && is_array( $preview['unsupported_sections'] ) ) : ?>
				<p><strong><?php esc_html_e( 'Unsupported sections preserved in legacy settings:', 'nexa-pro-core' ); ?></strong> <?php echo esc_html( implode( ', ', array_map( 'sanitize_key', $preview['unsupported_sections'] ) ) ); ?></p>
			<?php endif; ?>

			<?php if ( $components ) : ?>
				<table class="widefat striped nexa-pro-core-builder__table">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Order', 'nexa-pro-core' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Component', 'nexa-pro-core' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Anchor', 'nexa-pro-core' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Fields', 'nexa-pro-core' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $components as $component ) : ?>
							<?php
							$field_count = 0;
							foreach ( array( 'content', 'design', 'navigation', 'advanced' ) as $group ) {
								$field_count += ! empty( $component[ $group ] ) && is_array( $component[ $group ] ) ? count( $component[ $group ] ) : 0;
							}
							?>
							<tr>
								<td><?php echo esc_html( isset( $component['order'] ) ? (string) absint( $component['order'] ) : '0' ); ?></td>
								<td>
									<strong><?php echo esc_html( isset( $component['admin_title'] ) ? $component['admin_title'] : '' ); ?></strong><br>
									<code><?php echo esc_html( isset( $component['component_type'] ) ? $component['component_type'] : '' ); ?></code>
								</td>
								<td><code><?php echo esc_html( isset( $component['navigation']['anchor_id'] ) ? $component['navigation']['anchor_id'] : '' ); ?></code></td>
								<td><?php echo esc_html( (string) $field_count ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</section>
		<?php
	}

	/**
	 * Render import preview details.
	 *
	 * @param array  $preview       Preview.
	 * @param string $token         Token.
	 * @param string $conflict_mode Conflict mode.
	 * @param array  $pages         Editable pages.
	 * @param array  $payload       Payload.
	 * @return void
	 */
	private static function render_import_preview_summary( array $preview, $token, $conflict_mode, array $pages, array $payload ) {
		$page_records = ! empty( $payload['pages'] ) && is_array( $payload['pages'] ) ? $payload['pages'] : array();
		?>
		<section class="nexa-pro-core-builder__panel" aria-labelledby="nexa-pro-core-import-preview-title">
			<h2 id="nexa-pro-core-import-preview-title"><?php esc_html_e( 'Import preview summary', 'nexa-pro-core' ); ?></h2>
			<div class="nexa-pro-core-builder__summary-grid" role="list">
				<?php
				self::render_summary_card( __( 'Schema', 'nexa-pro-core' ), isset( $preview['export_schema'] ) ? (string) absint( $preview['export_schema'] ) : '' );
				self::render_summary_card( __( 'Scope', 'nexa-pro-core' ), isset( $preview['scope'] ) ? $preview['scope'] : '' );
				self::render_summary_card( __( 'Pages', 'nexa-pro-core' ), isset( $preview['pages_included'] ) ? (string) absint( $preview['pages_included'] ) : '0' );
				self::render_summary_card( __( 'Reusable components', 'nexa-pro-core' ), isset( $preview['reusable_included'] ) ? (string) absint( $preview['reusable_included'] ) : '0' );
				?>
			</div>
			<?php self::render_notice_list( ! empty( $preview['warnings'] ) && is_array( $preview['warnings'] ) ? $preview['warnings'] : array(), 'warning' ); ?>
			<?php self::render_notice_list( ! empty( $preview['conflicts'] ) && is_array( $preview['conflicts'] ) ? $preview['conflicts'] : array(), 'warning' ); ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="nexa_pro_core_import_apply">
				<input type="hidden" name="import_token" value="<?php echo esc_attr( $token ); ?>">
				<input type="hidden" name="conflict_mode" value="<?php echo esc_attr( $conflict_mode ); ?>">
				<?php wp_nonce_field( Transfer_Actions::nonce_action( 'import_apply' ) ); ?>

				<?php if ( $page_records ) : ?>
					<h3><?php esc_html_e( 'Page mapping', 'nexa-pro-core' ); ?></h3>
					<p class="description"><?php esc_html_e( 'Map imported pages explicitly when you do not want slug matching or create-new behavior.', 'nexa-pro-core' ); ?></p>
					<table class="widefat striped nexa-pro-core-builder__table">
						<thead>
							<tr>
								<th scope="col"><?php esc_html_e( 'Imported page', 'nexa-pro-core' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Target page', 'nexa-pro-core' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $page_records as $record ) : ?>
								<?php $source_id = ! empty( $record['source_page_id'] ) ? absint( $record['source_page_id'] ) : 0; ?>
								<tr>
									<td>
										<strong><?php echo esc_html( ! empty( $record['title'] ) ? $record['title'] : __( 'Untitled page', 'nexa-pro-core' ) ); ?></strong><br>
										<code><?php echo esc_html( ! empty( $record['slug'] ) ? $record['slug'] : '' ); ?></code>
									</td>
									<td>
										<label class="screen-reader-text" for="<?php echo esc_attr( 'nexa-pro-core-page-map-' . $source_id ); ?>"><?php esc_html_e( 'Target page', 'nexa-pro-core' ); ?></label>
										<select id="<?php echo esc_attr( 'nexa-pro-core-page-map-' . $source_id ); ?>" name="page_map[<?php echo esc_attr( $source_id ); ?>]">
											<option value="0"><?php esc_html_e( 'Use slug matching or create-new mode', 'nexa-pro-core' ); ?></option>
											<?php self::render_page_options( $pages, 0 ); ?>
										</select>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>

				<p>
					<label>
						<input type="checkbox" name="confirm_import" value="1" required>
						<?php esc_html_e( 'I reviewed the preview and want to apply this import.', 'nexa-pro-core' ); ?>
					</label>
				</p>
				<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Apply import', 'nexa-pro-core' ); ?></button></p>
			</form>
		</section>
		<?php
	}

	/**
	 * Get filters from the current request.
	 *
	 * @return array
	 */
	private static function get_filters() {
		return array(
			'search'     => isset( $_GET['builder_search'] ) ? \sanitize_text_field( \wp_unslash( $_GET['builder_search'] ) ) : '',
			'status'     => isset( $_GET['builder_status'] ) ? \sanitize_key( \wp_unslash( $_GET['builder_status'] ) ) : '',
			'configured' => isset( $_GET['builder_configured'] ) ? \sanitize_key( \wp_unslash( $_GET['builder_configured'] ) ) : '',
		);
	}

	/**
	 * Get pages the current user can edit.
	 *
	 * @param array $filters Page filters.
	 * @return array
	 */
	public static function get_editable_pages( $filters = array() ) {
		$filters = array_merge(
			array(
				'search'     => '',
				'status'     => '',
				'configured' => '',
			),
			is_array( $filters ) ? $filters : array()
		);

		$statuses = array( 'publish', 'draft', 'pending', 'private', 'future' );
		$status   = isset( $filters['status'] ) ? \sanitize_key( $filters['status'] ) : '';

		if ( '' !== $status && in_array( $status, $statuses, true ) ) {
			$statuses = array( $status );
		}

		$args = array(
			'post_type'      => 'page',
			'post_status'    => $statuses,
			'posts_per_page' => 100,
			'orderby'        => 'title',
			'order'          => 'ASC',
		);

		if ( ! empty( $filters['search'] ) ) {
			$args['s'] = $filters['search'];
		}

		$posts = \get_posts( $args );
		$pages = array();

		foreach ( $posts as $page ) {
			if ( ! \current_user_can( 'edit_post', $page->ID ) ) {
				continue;
			}

			$count = Storage::count_page_components( $page->ID );
			$count = \is_wp_error( $count ) ? 0 : absint( $count );

			if ( 'configured' === $filters['configured'] && 0 === $count ) {
				continue;
			}

			if ( 'unconfigured' === $filters['configured'] && 0 !== $count ) {
				continue;
			}

			$page->nexa_pro_core_component_count = $count;
			$pages[]                            = $page;
		}

		return $pages;
	}

	/**
	 * Get the selected page.
	 *
	 * @param array $pages Editable pages.
	 * @return \WP_Post|null
	 */
	private static function get_selected_page( array $pages ) {
		if ( empty( $pages ) ) {
			return null;
		}

		$requested = isset( $_GET['builder_page_id'] ) ? absint( \wp_unslash( $_GET['builder_page_id'] ) ) : 0;

		foreach ( $pages as $page ) {
			if ( $requested === absint( $page->ID ) ) {
				return $page;
			}
		}

		return $pages[0];
	}

	/**
	 * Render notices.
	 *
	 * @return void
	 */
	private static function render_notices() {
		$notice = isset( $_GET[ Builder_Actions::NOTICE_QUERY ] ) ? \sanitize_key( \wp_unslash( $_GET[ Builder_Actions::NOTICE_QUERY ] ) ) : '';
		$error  = isset( $_GET[ Builder_Actions::ERROR_QUERY ] ) ? \sanitize_key( \wp_unslash( $_GET[ Builder_Actions::ERROR_QUERY ] ) ) : '';

		if ( $notice ) {
			printf(
				'<div class="notice notice-success is-dismissible" role="status"><p>%s</p></div>',
				esc_html( self::get_notice_message( $notice ) )
			);
		}

		if ( $error ) {
			$message = \get_transient( Builder_Actions::error_transient_key() );

			if ( ! is_string( $message ) || '' === $message ) {
				$message = self::get_error_message( $error );
			}

			\delete_transient( Builder_Actions::error_transient_key() );

			printf(
				'<div class="notice notice-error" role="alert"><p>%s</p></div>',
				esc_html( $message )
			);
		}

		$deleted = Builder_Actions::get_recent_deleted_component();

		if ( ! empty( $deleted['component']['admin_title'] ) && ! empty( $deleted['page_id'] ) ) {
			$page_id = absint( $deleted['page_id'] );
			?>
			<div class="notice notice-warning nexa-pro-core-builder__undo" role="status">
				<p>
					<?php
					printf(
						/* translators: %s: component title. */
						esc_html__( 'Deleted "%s".', 'nexa-pro-core' ),
						esc_html( $deleted['component']['admin_title'] )
					);
					?>
				</p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="nexa_pro_core_builder_undo_delete">
					<input type="hidden" name="builder_page_id" value="<?php echo esc_attr( $page_id ); ?>">
					<?php wp_nonce_field( Builder_Actions::nonce_action( 'undo_delete', $page_id ) ); ?>
					<button type="submit" class="button"><?php esc_html_e( 'Undo delete', 'nexa-pro-core' ); ?></button>
				</form>
			</div>
			<?php
		}
	}

	/**
	 * Render a compatibility notice when theme registry metadata is unavailable.
	 *
	 * @return void
	 */
	private static function render_registry_notice() {
		if ( function_exists( 'nexa_pro_get_component_registry' ) ) {
			return;
		}

		?>
		<div class="notice notice-warning">
			<p><?php esc_html_e( 'Nexa Pro Core is using fallback component definitions because the Nexa Pro theme registry is unavailable. Stored builder data remains editable, but presentation metadata may be limited.', 'nexa-pro-core' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Render the page panel.
	 *
	 * @param array         $pages         Pages.
	 * @param \WP_Post|null $selected_page Selected page.
	 * @param array         $filters       Filters.
	 * @return void
	 */
	private static function render_page_panel( array $pages, $selected_page, array $filters ) {
		?>
		<aside class="nexa-pro-core-builder__pages" aria-labelledby="nexa-pro-core-builder-pages-title">
			<h2 id="nexa-pro-core-builder-pages-title"><?php esc_html_e( 'Pages', 'nexa-pro-core' ); ?></h2>
			<form class="nexa-pro-core-builder__filters" method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr( self::PAGE_SLUG ); ?>">
				<label for="nexa-pro-core-page-search"><?php esc_html_e( 'Search pages', 'nexa-pro-core' ); ?></label>
				<input type="search" id="nexa-pro-core-page-search" name="builder_search" value="<?php echo esc_attr( $filters['search'] ); ?>">

				<label for="nexa-pro-core-page-status"><?php esc_html_e( 'Status', 'nexa-pro-core' ); ?></label>
				<select id="nexa-pro-core-page-status" name="builder_status">
					<?php self::render_options( self::page_status_options(), $filters['status'] ); ?>
				</select>

				<label for="nexa-pro-core-page-configured"><?php esc_html_e( 'Configuration', 'nexa-pro-core' ); ?></label>
				<select id="nexa-pro-core-page-configured" name="builder_configured">
					<?php
					self::render_options(
						array(
							''             => __( 'All pages', 'nexa-pro-core' ),
							'configured'   => __( 'Configured', 'nexa-pro-core' ),
							'unconfigured' => __( 'Unconfigured', 'nexa-pro-core' ),
						),
						$filters['configured']
					);
					?>
				</select>
				<button type="submit" class="button"><?php esc_html_e( 'Filter', 'nexa-pro-core' ); ?></button>
			</form>

			<ul class="nexa-pro-core-builder__page-list" aria-label="<?php esc_attr_e( 'Editable pages', 'nexa-pro-core' ); ?>">
				<?php foreach ( $pages as $page ) : ?>
					<?php $is_selected = $selected_page && absint( $selected_page->ID ) === absint( $page->ID ); ?>
					<li>
						<a class="<?php echo $is_selected ? 'is-selected' : ''; ?>" <?php echo $is_selected ? 'aria-current="page"' : ''; ?> href="<?php echo esc_url( self::builder_url( $page->ID, $filters ) ); ?>">
							<span class="nexa-pro-core-builder__page-title"><?php echo esc_html( get_the_title( $page ) ); ?></span>
							<span class="nexa-pro-core-builder__page-meta">
								<?php echo esc_html( self::status_label( $page->post_status ) ); ?>
								<?php if ( absint( get_option( 'page_on_front' ) ) === absint( $page->ID ) ) : ?>
									<span class="nexa-pro-core-builder__front-badge"><?php esc_html_e( 'Front page', 'nexa-pro-core' ); ?></span>
								<?php endif; ?>
							</span>
							<span class="nexa-pro-core-builder__page-count">
								<?php
								printf(
									/* translators: %d: component count. */
									esc_html( _n( '%d component', '%d components', $page->nexa_pro_core_component_count, 'nexa-pro-core' ) ),
									absint( $page->nexa_pro_core_component_count )
								);
								?>
							</span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</aside>
		<?php
	}

	/**
	 * Render the component stack.
	 *
	 * @param \WP_Post $page       Selected page.
	 * @param array    $pages      Editable pages.
	 * @param array    $components Components.
	 * @param array    $registry   Component registry.
	 * @return void
	 */
	private static function render_component_stack( $page, array $pages, array $components, array $registry ) {
		$edit_url    = get_edit_post_link( $page->ID, '' );
		$preview_url = get_preview_post_link( $page );
		?>
		<header class="nexa-pro-core-builder__stack-header">
			<div>
				<h2><?php echo esc_html( get_the_title( $page ) ); ?></h2>
				<p>
					<?php echo esc_html( self::status_label( $page->post_status ) ); ?>
					<span aria-hidden="true"> | </span>
					<?php
					printf(
						/* translators: %d: component count. */
						esc_html( _n( '%d component', '%d components', count( $components ), 'nexa-pro-core' ) ),
						count( $components )
					);
					?>
				</p>
			</div>
			<div class="nexa-pro-core-builder__stack-links">
				<a class="button button-primary" href="<?php echo esc_url( self::builder_url( $page->ID, array(), array( 'builder_mode' => 'add' ) ) ); ?>#nexa-pro-core-builder-editor-title">
					<?php esc_html_e( 'Add component', 'nexa-pro-core' ); ?>
				</a>
				<?php if ( $edit_url ) : ?>
					<a class="button" href="<?php echo esc_url( $edit_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Edit page', 'nexa-pro-core' ); ?></a>
				<?php endif; ?>
				<?php if ( $preview_url ) : ?>
					<a class="button" href="<?php echo esc_url( $preview_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Preview page', 'nexa-pro-core' ); ?></a>
				<?php endif; ?>
			</div>
		</header>

		<?php if ( empty( $components ) ) : ?>
			<div class="nexa-pro-core-builder__empty">
				<h3><?php esc_html_e( 'No components yet', 'nexa-pro-core' ); ?></h3>
				<p><?php esc_html_e( 'Add a component to start composing this page. The theme frontend remains unchanged until builder mode is explicitly enabled on the Migration screen.', 'nexa-pro-core' ); ?></p>
			</div>
		<?php else : ?>
			<form class="nexa-pro-core-builder__order-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="nexa_pro_core_builder_save_order">
				<input type="hidden" name="builder_page_id" value="<?php echo esc_attr( $page->ID ); ?>">
				<?php wp_nonce_field( Builder_Actions::nonce_action( 'save_order', $page->ID ) ); ?>
				<div class="nexa-pro-core-builder__order-inputs" data-order-inputs>
					<?php foreach ( $components as $component ) : ?>
						<input type="hidden" name="ordered_ids[]" value="<?php echo esc_attr( $component['instance_id'] ); ?>">
					<?php endforeach; ?>
				</div>
				<button type="submit" class="button"><?php esc_html_e( 'Save order', 'nexa-pro-core' ); ?></button>
				<p class="description"><?php esc_html_e( 'Drag cards to change order with JavaScript, or use Move up and Move down for keyboard and no-JavaScript control.', 'nexa-pro-core' ); ?></p>
			</form>

			<ol class="nexa-pro-core-builder__stack" data-component-stack aria-label="<?php esc_attr_e( 'Page component stack', 'nexa-pro-core' ); ?>">
				<?php foreach ( $components as $index => $component ) : ?>
					<?php self::render_component_card( $page, $pages, $component, $registry, $index, count( $components ) ); ?>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>
		<div class="screen-reader-text" aria-live="polite" data-builder-live-region></div>
		<?php
	}

	/**
	 * Render a component card.
	 *
	 * @param \WP_Post $page       Page.
	 * @param array    $pages      Editable pages.
	 * @param array    $component  Component.
	 * @param array    $registry   Registry.
	 * @param int      $index      Index.
	 * @param int      $total      Total.
	 * @return void
	 */
	private static function render_component_card( $page, array $pages, array $component, array $registry, $index, $total ) {
		$type       = isset( $component['component_type'] ) ? $component['component_type'] : '';
		$definition = isset( $registry[ $type ] ) ? $registry[ $type ] : array();
		$title      = ! empty( $component['admin_title'] ) ? $component['admin_title'] : Sanitizer::default_admin_title( $type );
		$is_enabled = ! empty( $component['enabled'] );
		$status     = self::get_reusable_status( $component );
		?>
		<li class="nexa-pro-core-builder__card <?php echo $is_enabled ? '' : 'is-disabled'; ?>" data-component-card data-component-id="<?php echo esc_attr( $component['instance_id'] ); ?>" draggable="true">
			<div class="nexa-pro-core-builder__card-main">
				<div class="nexa-pro-core-builder__drag-handle" aria-hidden="true">Move</div>
				<div>
					<h3><?php echo esc_html( $title ); ?></h3>
					<p class="nexa-pro-core-builder__card-meta">
						<span><?php echo esc_html( ! empty( $definition['label'] ) ? $definition['label'] : $type ); ?></span>
						<span><?php echo esc_html( ! empty( $component['layout'] ) ? $component['layout'] : 'default' ); ?></span>
						<span class="nexa-pro-core-builder__status <?php echo $is_enabled ? 'is-enabled' : 'is-disabled'; ?>">
							<?php echo $is_enabled ? esc_html__( 'Enabled', 'nexa-pro-core' ) : esc_html__( 'Disabled', 'nexa-pro-core' ); ?>
						</span>
						<?php if ( ! empty( $component['navigation']['navigation_label'] ) ) : ?>
							<span><?php echo esc_html( $component['navigation']['navigation_label'] ); ?></span>
						<?php endif; ?>
						<span class="nexa-pro-core-builder__status <?php echo esc_attr( $status['class'] ); ?>"><?php echo esc_html( $status['label'] ); ?></span>
					</p>
					<?php self::render_component_warnings( $component ); ?>
				</div>
			</div>

			<div class="nexa-pro-core-builder__card-actions">
				<a class="button" href="<?php echo esc_url( self::builder_url( $page->ID, array(), array( 'builder_mode' => 'edit', 'component_id' => $component['instance_id'] ) ) ); ?>#nexa-pro-core-builder-editor-title"><?php esc_html_e( 'Edit', 'nexa-pro-core' ); ?></a>
				<?php self::render_action_form( 'duplicate_component', $page->ID, $component['instance_id'], __( 'Duplicate', 'nexa-pro-core' ) ); ?>
				<?php self::render_toggle_form( $page->ID, $component ); ?>
				<?php self::render_order_form( $page->ID, $component['instance_id'], 'up', 0 === $index ); ?>
				<?php self::render_order_form( $page->ID, $component['instance_id'], 'down', $index >= $total - 1 ); ?>
				<?php self::render_move_page_form( $page, $pages, $component ); ?>
				<?php self::render_save_as_reusable_form( $page->ID, $component ); ?>
				<?php self::render_detach_form( $page->ID, $component ); ?>
				<?php self::render_open_source_link( $component ); ?>
				<?php self::render_delete_form( $page->ID, $component ); ?>
			</div>
		</li>
		<?php
	}

	/**
	 * Render the editor panel.
	 *
	 * @param \WP_Post $page       Page.
	 * @param array    $components Components.
	 * @param array    $registry   Registry.
	 * @return void
	 */
	private static function render_editor_panel( $page, array $components, array $registry ) {
		$mode         = isset( $_GET['builder_mode'] ) ? \sanitize_key( \wp_unslash( $_GET['builder_mode'] ) ) : '';
		$component_id = isset( $_GET['component_id'] ) ? \sanitize_text_field( \wp_unslash( $_GET['component_id'] ) ) : '';
		$add_type     = isset( $_GET['add_type'] ) ? \sanitize_key( \wp_unslash( $_GET['add_type'] ) ) : '';
		$draft        = Builder_Actions::get_error_draft();

		echo '<h2 id="nexa-pro-core-builder-editor-title">' . esc_html__( 'Component editor', 'nexa-pro-core' ) . '</h2>';

		if ( 'add' === $mode && '' === $add_type ) {
			self::render_component_library( $page, $registry );
			return;
		}

		if ( 'add' === $mode && isset( $registry[ $add_type ] ) ) {
			$component = array(
				'component_type' => $add_type,
				'admin_title'    => Sanitizer::default_admin_title( $add_type ),
				'enabled'        => true,
				'layout'         => self::first_layout( $registry[ $add_type ] ),
				'content'        => array(),
				'design'         => array(),
				'navigation'     => array(
					'navigation_label' => ! empty( $registry[ $add_type ]['navigation']['default_label'] ) ? $registry[ $add_type ]['navigation']['default_label'] : '',
					'anchor_id'        => ! empty( $registry[ $add_type ]['default_anchor'] ) ? $registry[ $add_type ]['default_anchor'] : $add_type,
				),
				'advanced'       => array(),
			);

			if ( ! empty( $draft['component'] ) && absint( $draft['page_id'] ) === absint( $page->ID ) && $add_type === $draft['component']['component_type'] ) {
				$component = array_merge( $component, $draft['component'] );
			}

			self::render_component_form( $page, $component, $registry[ $add_type ], true );
			return;
		}

		if ( 'edit' === $mode && '' !== $component_id ) {
			$component = Builder_Actions::get_component_by_id( $components, $component_id );

			if ( ! empty( $draft['component'] ) && absint( $draft['page_id'] ) === absint( $page->ID ) && $component_id === $draft['component']['instance_id'] ) {
				$component = array_merge( $component, $draft['component'] );
			}

			if ( ! empty( $component ) && isset( $registry[ $component['component_type'] ] ) ) {
				self::render_component_form( $page, $component, $registry[ $component['component_type'] ], false );
				return;
			}
		}

		self::render_component_library( $page, $registry );
	}

	/**
	 * Render component library.
	 *
	 * @param \WP_Post $page     Page.
	 * @param array    $registry Registry.
	 * @return void
	 */
	private static function render_component_library( $page, array $registry ) {
		?>
		<p><?php esc_html_e( 'Choose a component type to add to the selected page.', 'nexa-pro-core' ); ?></p>
		<div class="nexa-pro-core-builder__library">
			<?php foreach ( $registry as $type => $definition ) : ?>
				<article class="nexa-pro-core-builder__library-card">
					<div class="nexa-pro-core-builder__library-icon" aria-hidden="true"><?php echo esc_html( strtoupper( substr( $type, 0, 1 ) ) ); ?></div>
					<h3><?php echo esc_html( $definition['label'] ); ?></h3>
					<p><?php echo esc_html( $definition['description'] ); ?></p>
					<p class="description">
						<?php
						printf(
							/* translators: %s: layout list. */
							esc_html__( 'Layouts: %s', 'nexa-pro-core' ),
							esc_html( implode( ', ', $definition['supported_layouts'] ) )
						);
						?>
					</p>
					<p class="description">
						<?php echo ! empty( $definition['repeatable']['supported'] ) ? esc_html__( 'Repeatable', 'nexa-pro-core' ) : esc_html__( 'Single-use recommended', 'nexa-pro-core' ); ?>
						<span aria-hidden="true"> | </span>
						<?php echo ! empty( $definition['reusable']['eligible'] ) ? esc_html__( 'Reusable eligible', 'nexa-pro-core' ) : esc_html__( 'Local only', 'nexa-pro-core' ); ?>
					</p>
					<a class="button" href="<?php echo esc_url( self::builder_url( $page->ID, array(), array( 'builder_mode' => 'add', 'add_type' => $type ) ) ); ?>#nexa-pro-core-builder-editor-title">
						<?php
						printf(
							/* translators: %s: component label. */
							esc_html__( 'Add %s', 'nexa-pro-core' ),
							esc_html( $definition['label'] )
						);
						?>
					</a>
				</article>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Render component form.
	 *
	 * @param \WP_Post $page       Page.
	 * @param array    $component  Component.
	 * @param array    $definition Definition.
	 * @param bool     $is_new     Whether adding.
	 * @return void
	 */
	private static function render_component_form( $page, array $component, array $definition, $is_new ) {
		$form_title = $is_new ? __( 'Add component', 'nexa-pro-core' ) : __( 'Edit component', 'nexa-pro-core' );
		?>
		<form class="nexa-pro-core-builder__component-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-builder-editor-form>
			<input type="hidden" name="action" value="nexa_pro_core_builder_save_component">
			<input type="hidden" name="builder_page_id" value="<?php echo esc_attr( $page->ID ); ?>">
			<input type="hidden" name="component[instance_id]" value="<?php echo esc_attr( isset( $component['instance_id'] ) ? $component['instance_id'] : '' ); ?>">
			<input type="hidden" name="component[component_type]" value="<?php echo esc_attr( $definition['type'] ); ?>">
			<input type="hidden" name="component[inheritance_mode]" value="<?php echo esc_attr( ! empty( $component['inheritance_mode'] ) ? $component['inheritance_mode'] : 'local' ); ?>">
			<input type="hidden" name="component[reusable_component_id]" value="<?php echo esc_attr( ! empty( $component['reusable_component_id'] ) ? absint( $component['reusable_component_id'] ) : 0 ); ?>">
			<?php wp_nonce_field( Builder_Actions::nonce_action( 'save_component', $page->ID ) ); ?>

			<h3><?php echo esc_html( $form_title ); ?>: <?php echo esc_html( $definition['label'] ); ?></h3>
			<p class="description"><?php echo esc_html( $definition['description'] ); ?></p>
			<p class="notice notice-info inline"><?php esc_html_e( 'Builder output is stored by Nexa Pro Core. It does not replace the current theme frontend until builder mode is explicitly enabled on the Migration screen.', 'nexa-pro-core' ); ?></p>

			<details open>
				<summary><?php esc_html_e( 'Content', 'nexa-pro-core' ); ?></summary>
				<?php
				self::render_text_field( 'component[admin_title]', 'nexa-pro-core-admin-title', __( 'Admin title', 'nexa-pro-core' ), isset( $component['admin_title'] ) ? $component['admin_title'] : '', true );
				self::render_checkbox_field( 'component[enabled]', 'nexa-pro-core-enabled', __( 'Enabled', 'nexa-pro-core' ), ! empty( $component['enabled'] ) );
				self::render_text_field( 'component[content][eyebrow]', 'nexa-pro-core-eyebrow', __( 'Eyebrow', 'nexa-pro-core' ), self::nested_value( $component, array( 'content', 'eyebrow' ) ) );
				self::render_text_field( 'component[content][heading]', 'nexa-pro-core-heading', __( 'Heading', 'nexa-pro-core' ), self::nested_value( $component, array( 'content', 'heading' ) ) );
				self::render_text_field( 'component[content][subtitle]', 'nexa-pro-core-subtitle', __( 'Subtitle', 'nexa-pro-core' ), self::nested_value( $component, array( 'content', 'subtitle' ) ) );
				self::render_textarea_field( 'component[content][body]', 'nexa-pro-core-body', __( 'Body', 'nexa-pro-core' ), self::nested_value( $component, array( 'content', 'body' ) ) );
				self::render_text_field( 'component[content][primary_cta_label]', 'nexa-pro-core-primary-cta-label', __( 'Primary CTA label', 'nexa-pro-core' ), self::nested_value( $component, array( 'content', 'primary_cta_label' ) ) );
				self::render_url_field( 'component[content][primary_cta_url]', 'nexa-pro-core-primary-cta-url', __( 'Primary CTA URL', 'nexa-pro-core' ), self::nested_value( $component, array( 'content', 'primary_cta_url' ) ) );
				self::render_text_field( 'component[content][secondary_cta_label]', 'nexa-pro-core-secondary-cta-label', __( 'Secondary CTA label', 'nexa-pro-core' ), self::nested_value( $component, array( 'content', 'secondary_cta_label' ) ) );
				self::render_url_field( 'component[content][secondary_cta_url]', 'nexa-pro-core-secondary-cta-url', __( 'Secondary CTA URL', 'nexa-pro-core' ), self::nested_value( $component, array( 'content', 'secondary_cta_url' ) ) );
				self::render_number_field( 'component[content][desktop_image_id]', 'nexa-pro-core-desktop-image-id', __( 'Desktop image attachment ID', 'nexa-pro-core' ), self::nested_value( $component, array( 'content', 'desktop_image_id' ) ), 0, 999999 );
				self::render_number_field( 'component[content][mobile_image_id]', 'nexa-pro-core-mobile-image-id', __( 'Mobile image attachment ID', 'nexa-pro-core' ), self::nested_value( $component, array( 'content', 'mobile_image_id' ) ), 0, 999999 );
				?>
			</details>

			<details>
				<summary><?php esc_html_e( 'Layout', 'nexa-pro-core' ); ?></summary>
				<?php
				self::render_layout_preview( $definition );

				if ( self::supports_layout_control( $definition, 'layout' ) ) {
					self::render_select_field( 'component[layout]', 'nexa-pro-core-layout', __( 'Layout variation', 'nexa-pro-core' ), array_combine( $definition['supported_layouts'], $definition['supported_layouts'] ), isset( $component['layout'] ) ? $component['layout'] : self::first_layout( $definition ) );
				}

				if ( self::supports_layout_control( $definition, 'container_width' ) ) {
					self::render_select_field( 'component[design][container_width]', 'nexa-pro-core-container-width', __( 'Container width', 'nexa-pro-core' ), self::container_width_options(), self::nested_value( $component, array( 'design', 'container_width' ), 'inherit' ) );
				}

				if ( self::supports_layout_control( $definition, 'content_alignment' ) ) {
					self::render_select_field( 'component[design][content_alignment]', 'nexa-pro-core-content-alignment', __( 'Content alignment', 'nexa-pro-core' ), self::alignment_options(), self::nested_value( $component, array( 'design', 'content_alignment' ), 'inherit' ) );
				}

				if ( self::supports_layout_control( $definition, 'content_width' ) ) {
					self::render_select_field( 'component[design][content_width]', 'nexa-pro-core-content-width', __( 'Content width', 'nexa-pro-core' ), self::content_width_options(), self::nested_value( $component, array( 'design', 'content_width' ), 'inherit' ) );
				}

				if ( self::supports_layout_control( $definition, 'media_position' ) ) {
					self::render_select_field( 'component[design][media_position]', 'nexa-pro-core-media-position', __( 'Media position', 'nexa-pro-core' ), self::media_position_options(), self::nested_value( $component, array( 'design', 'media_position' ), 'inherit' ) );
				}

				if ( self::supports_layout_control( $definition, 'column_count' ) ) {
					self::render_number_field( 'component[design][column_count]', 'nexa-pro-core-column-count', __( 'Column count', 'nexa-pro-core' ), self::nested_value( $component, array( 'design', 'column_count' ), 3 ), 1, 6 );
				}

				if ( self::supports_layout_control( $definition, 'card_density' ) ) {
					self::render_select_field( 'component[design][card_density]', 'nexa-pro-core-card-density', __( 'Card density', 'nexa-pro-core' ), self::card_density_options(), self::nested_value( $component, array( 'design', 'card_density' ), 'inherit' ) );
				}

				if ( self::supports_layout_control( $definition, 'section_spacing' ) ) {
					self::render_select_field( 'component[design][section_spacing]', 'nexa-pro-core-section-spacing', __( 'Section spacing', 'nexa-pro-core' ), self::spacing_options(), self::nested_value( $component, array( 'design', 'section_spacing' ), 'inherit' ) );
				}

				if ( self::supports_layout_control( $definition, 'item_spacing' ) ) {
					self::render_select_field( 'component[design][item_spacing]', 'nexa-pro-core-item-spacing', __( 'Item spacing', 'nexa-pro-core' ), self::item_spacing_options(), self::nested_value( $component, array( 'design', 'item_spacing' ), 'inherit' ) );
				}
				?>
			</details>

			<details>
				<summary><?php esc_html_e( 'Design', 'nexa-pro-core' ); ?></summary>
				<?php
				if ( self::supports_design_capability( $definition, 'design_preset' ) ) {
					self::render_select_field( 'component[design][preset]', 'nexa-pro-core-design-preset', __( 'Design preset', 'nexa-pro-core' ), self::design_preset_options(), self::nested_value( $component, array( 'design', 'preset' ), 'inherit' ) );
				}

				if ( self::supports_design_capability( $definition, 'background' ) ) {
					$background_type = self::nested_value( $component, array( 'design', 'background_type' ), 'inherit' );
					self::render_select_field( 'component[design][background_type]', 'nexa-pro-core-background-type', __( 'Background type', 'nexa-pro-core' ), self::background_options(), $background_type );
					self::render_color_field( 'component[design][background_color]', 'nexa-pro-core-background-color', __( 'Solid/background color', 'nexa-pro-core' ), self::nested_value( $component, array( 'design', 'background_color' ) ) );

					if ( 'gradient' === $background_type && self::supports_design_capability( $definition, 'gradient' ) ) {
						self::render_color_field( 'component[design][gradient_start]', 'nexa-pro-core-gradient-start', __( 'Gradient start', 'nexa-pro-core' ), self::nested_value( $component, array( 'design', 'gradient_start' ) ) );
						self::render_color_field( 'component[design][gradient_end]', 'nexa-pro-core-gradient-end', __( 'Gradient end', 'nexa-pro-core' ), self::nested_value( $component, array( 'design', 'gradient_end' ) ) );
						self::render_select_field( 'component[design][gradient_direction]', 'nexa-pro-core-gradient-direction', __( 'Gradient direction', 'nexa-pro-core' ), self::gradient_direction_options(), self::nested_value( $component, array( 'design', 'gradient_direction' ), 'to-bottom' ) );
					}

					if ( 'image' === $background_type && self::supports_design_capability( $definition, 'background_image' ) ) {
						self::render_number_field( 'component[design][background_image_id]', 'nexa-pro-core-background-image-id', __( 'Background image attachment ID', 'nexa-pro-core' ), self::nested_value( $component, array( 'design', 'background_image_id' ) ), 0, 999999 );

						if ( self::supports_design_capability( $definition, 'overlay' ) ) {
							self::render_checkbox_field( 'component[design][overlay_enabled]', 'nexa-pro-core-overlay-enabled', __( 'Enable overlay', 'nexa-pro-core' ), (bool) self::nested_value( $component, array( 'design', 'overlay_enabled' ) ) );
							self::render_color_field( 'component[design][overlay_color]', 'nexa-pro-core-overlay-color', __( 'Overlay color', 'nexa-pro-core' ), self::nested_value( $component, array( 'design', 'overlay_color' ) ) );
							self::render_number_field( 'component[design][overlay_opacity]', 'nexa-pro-core-overlay-opacity', __( 'Overlay opacity', 'nexa-pro-core' ), self::nested_value( $component, array( 'design', 'overlay_opacity' ), 40 ), 0, 100 );
						}
					}
				}

				if ( self::supports_design_capability( $definition, 'text_theme' ) ) {
					self::render_select_field( 'component[design][text_theme]', 'nexa-pro-core-text-theme', __( 'Text theme', 'nexa-pro-core' ), self::text_theme_options(), self::nested_value( $component, array( 'design', 'text_theme' ), 'inherit' ) );
				}

				if ( self::supports_design_capability( $definition, 'card_style' ) ) {
					self::render_select_field( 'component[design][card_style]', 'nexa-pro-core-card-style', __( 'Card style', 'nexa-pro-core' ), self::card_style_options(), self::nested_value( $component, array( 'design', 'card_style' ), 'inherit' ) );
				}

				if ( self::supports_design_capability( $definition, 'radius' ) ) {
					self::render_select_field( 'component[design][radius]', 'nexa-pro-core-radius', __( 'Radius token', 'nexa-pro-core' ), self::radius_options(), self::nested_value( $component, array( 'design', 'radius' ), 'inherit' ) );
				}

				if ( self::supports_design_capability( $definition, 'shadow' ) ) {
					self::render_select_field( 'component[design][shadow]', 'nexa-pro-core-shadow', __( 'Shadow token', 'nexa-pro-core' ), self::shadow_options(), self::nested_value( $component, array( 'design', 'shadow' ), 'inherit' ) );
				}

				if ( self::supports_design_capability( $definition, 'image_style' ) ) {
					self::render_select_field( 'component[design][image_style]', 'nexa-pro-core-image-style', __( 'Image style', 'nexa-pro-core' ), self::image_style_options(), self::nested_value( $component, array( 'design', 'image_style' ), 'inherit' ) );
				}

				if ( self::supports_design_capability( $definition, 'button_style' ) ) {
					self::render_select_field( 'component[design][button_style]', 'nexa-pro-core-button-style', __( 'Button style', 'nexa-pro-core' ), self::button_style_options(), self::nested_value( $component, array( 'design', 'button_style' ), 'inherit' ) );
				}
				?>
			</details>

			<details>
				<summary><?php esc_html_e( 'Navigation', 'nexa-pro-core' ); ?></summary>
				<?php
				self::render_checkbox_field( 'component[navigation][show_in_navigation]', 'nexa-pro-core-show-in-navigation', __( 'Show in generated navigation', 'nexa-pro-core' ), (bool) self::nested_value( $component, array( 'navigation', 'show_in_navigation' ) ) );
				self::render_text_field( 'component[navigation][navigation_label]', 'nexa-pro-core-navigation-label', __( 'Navigation label', 'nexa-pro-core' ), self::nested_value( $component, array( 'navigation', 'navigation_label' ) ) );
				self::render_text_field( 'component[navigation][anchor_id]', 'nexa-pro-core-anchor-id', __( 'Anchor ID', 'nexa-pro-core' ), self::nested_value( $component, array( 'navigation', 'anchor_id' ) ) );
				self::render_checkbox_field( 'component[navigation][highlight_as_cta]', 'nexa-pro-core-highlight-as-cta', __( 'Highlight as CTA', 'nexa-pro-core' ), (bool) self::nested_value( $component, array( 'navigation', 'highlight_as_cta' ) ) );
				self::render_select_field( 'component[navigation][mobile_visibility]', 'nexa-pro-core-mobile-visibility', __( 'Mobile visibility', 'nexa-pro-core' ), self::visibility_options(), self::nested_value( $component, array( 'navigation', 'mobile_visibility' ), 'all' ) );
				self::render_number_field( 'component[navigation][order_override]', 'nexa-pro-core-navigation-order', __( 'Navigation order override', 'nexa-pro-core' ), self::nested_value( $component, array( 'navigation', 'order_override' ) ), 0, 999 );
				?>
			</details>

			<details>
				<summary><?php esc_html_e( 'Advanced', 'nexa-pro-core' ); ?></summary>
				<?php
				self::render_text_field( 'component[advanced][custom_css_class]', 'nexa-pro-core-custom-css-class', __( 'Custom CSS classes', 'nexa-pro-core' ), self::nested_value( $component, array( 'advanced', 'custom_css_class' ) ) );
				self::render_text_field( 'component[advanced][aria_label]', 'nexa-pro-core-aria-label', __( 'ARIA label', 'nexa-pro-core' ), self::nested_value( $component, array( 'advanced', 'aria_label' ) ) );
				self::render_select_field( 'component[advanced][semantic_element]', 'nexa-pro-core-semantic-element', __( 'Semantic element', 'nexa-pro-core' ), self::semantic_options(), self::nested_value( $component, array( 'advanced', 'semantic_element' ), 'section' ) );
				self::render_select_field( 'component[advanced][device_visibility]', 'nexa-pro-core-device-visibility', __( 'Device visibility', 'nexa-pro-core' ), self::visibility_options(), self::nested_value( $component, array( 'advanced', 'device_visibility' ), 'all' ) );
				self::render_select_field( 'component[advanced][animation_preset]', 'nexa-pro-core-animation-preset', __( 'Animation preset', 'nexa-pro-core' ), self::animation_options(), self::nested_value( $component, array( 'advanced', 'animation_preset' ), 'none' ) );
				?>
			</details>

			<div class="nexa-pro-core-builder__save-bar">
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Save component', 'nexa-pro-core' ); ?></button>
				<a class="button" href="<?php echo esc_url( self::builder_url( $page->ID ) ); ?>"><?php esc_html_e( 'Cancel', 'nexa-pro-core' ); ?></a>
			</div>
		</form>
		<?php
	}

	/**
	 * Get normalized registry for builder use.
	 *
	 * @return array
	 */
	public static function get_component_registry() {
		if ( function_exists( 'nexa_pro_get_component_registry' ) ) {
			$registry = \nexa_pro_get_component_registry();
		} else {
			$registry = array();

			foreach ( Sanitizer::fallback_component_types() as $type ) {
				$layouts = Sanitizer::allowed_layouts_for_type( $type );
				$registry[ $type ] = array(
					'type'                      => $type,
					'label'                     => Sanitizer::default_admin_title( $type ),
					'description'               => __( 'Fallback component definition. Activate Nexa Pro for full presentation metadata.', 'nexa-pro-core' ),
					'default_anchor'            => $type,
					'supported_layouts'         => $layouts,
					'default_layout'            => ! empty( $layouts[0] ) ? $layouts[0] : 'default',
					'layout_controls'           => array( 'layout', 'container_width', 'content_alignment', 'section_spacing' ),
					'content_capabilities'      => array(),
					'design_capabilities'       => array( 'design_preset', 'background', 'text_theme', 'radius', 'shadow' ),
					'preview'                   => array(
						'label'       => Sanitizer::default_admin_title( $type ),
						'description' => __( 'Fallback layout preview.', 'nexa-pro-core' ),
					),
					'responsive_notes'          => '',
					'supported_design_features' => array(),
					'navigation'                => array(
						'supported'     => true,
						'default_label' => Sanitizer::default_admin_title( $type ),
					),
					'repeatable'                => array( 'supported' => true ),
					'reusable'                  => array( 'eligible' => true ),
				);
			}
		}

		$normalized = array();

		foreach ( $registry as $type => $definition ) {
			$type = \sanitize_key( $type );

			if ( '' === $type ) {
				continue;
			}

			$normalized[ $type ] = array(
				'type'                      => $type,
				'label'                     => isset( $definition['label'] ) ? \sanitize_text_field( $definition['label'] ) : Sanitizer::default_admin_title( $type ),
				'description'               => isset( $definition['description'] ) ? \sanitize_text_field( $definition['description'] ) : '',
				'default_anchor'            => isset( $definition['default_anchor'] ) ? \sanitize_key( $definition['default_anchor'] ) : $type,
				'supported_layouts'         => ! empty( $definition['supported_layouts'] ) && is_array( $definition['supported_layouts'] ) ? array_values( array_map( 'sanitize_key', $definition['supported_layouts'] ) ) : array( 'default' ),
				'default_layout'            => ! empty( $definition['default_layout'] ) ? \sanitize_key( $definition['default_layout'] ) : '',
				'layout_controls'           => ! empty( $definition['layout_controls'] ) && is_array( $definition['layout_controls'] ) ? array_values( array_map( 'sanitize_key', $definition['layout_controls'] ) ) : array( 'layout' ),
				'content_capabilities'      => ! empty( $definition['content_capabilities'] ) && is_array( $definition['content_capabilities'] ) ? array_values( array_map( 'sanitize_key', $definition['content_capabilities'] ) ) : array(),
				'design_capabilities'       => ! empty( $definition['design_capabilities'] ) && is_array( $definition['design_capabilities'] ) ? array_values( array_map( 'sanitize_key', $definition['design_capabilities'] ) ) : array(),
				'preview'                   => ! empty( $definition['preview'] ) && is_array( $definition['preview'] ) ? array(
					'label'       => isset( $definition['preview']['label'] ) ? \sanitize_text_field( $definition['preview']['label'] ) : '',
					'description' => isset( $definition['preview']['description'] ) ? \sanitize_text_field( $definition['preview']['description'] ) : '',
				) : array(),
				'responsive_notes'          => isset( $definition['responsive_notes'] ) ? \sanitize_text_field( $definition['responsive_notes'] ) : '',
				'supported_design_features' => ! empty( $definition['supported_design_features'] ) && is_array( $definition['supported_design_features'] ) ? array_values( array_map( 'sanitize_key', $definition['supported_design_features'] ) ) : array(),
				'navigation'                => isset( $definition['navigation'] ) && is_array( $definition['navigation'] ) ? $definition['navigation'] : array( 'supported' => true, 'default_label' => Sanitizer::default_admin_title( $type ) ),
				'repeatable'                => isset( $definition['repeatable'] ) && is_array( $definition['repeatable'] ) ? $definition['repeatable'] : array( 'supported' => true ),
				'reusable'                  => isset( $definition['reusable'] ) && is_array( $definition['reusable'] ) ? $definition['reusable'] : array( 'eligible' => true ),
			);

			if ( '' === $normalized[ $type ]['default_layout'] || ! in_array( $normalized[ $type ]['default_layout'], $normalized[ $type ]['supported_layouts'], true ) ) {
				$normalized[ $type ]['default_layout'] = $normalized[ $type ]['supported_layouts'][0];
			}
		}

		return $normalized;
	}

	/**
	 * Render a generic action form.
	 *
	 * @param string $operation   Operation key.
	 * @param int    $page_id     Page ID.
	 * @param string $instance_id Instance ID.
	 * @param string $label       Button label.
	 * @return void
	 */
	private static function render_action_form( $operation, $page_id, $instance_id, $label ) {
		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="nexa_pro_core_builder_<?php echo esc_attr( $operation ); ?>">
			<input type="hidden" name="builder_page_id" value="<?php echo esc_attr( $page_id ); ?>">
			<input type="hidden" name="component_id" value="<?php echo esc_attr( $instance_id ); ?>">
			<?php wp_nonce_field( Builder_Actions::nonce_action( $operation, $page_id ) ); ?>
			<button type="submit" class="button"><?php echo esc_html( $label ); ?></button>
		</form>
		<?php
	}

	/**
	 * Render toggle form.
	 *
	 * @param int   $page_id   Page ID.
	 * @param array $component Component.
	 * @return void
	 */
	private static function render_toggle_form( $page_id, array $component ) {
		$is_enabled = ! empty( $component['enabled'] );
		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="nexa_pro_core_builder_toggle_component">
			<input type="hidden" name="builder_page_id" value="<?php echo esc_attr( $page_id ); ?>">
			<input type="hidden" name="component_id" value="<?php echo esc_attr( $component['instance_id'] ); ?>">
			<input type="hidden" name="enabled" value="<?php echo $is_enabled ? '0' : '1'; ?>">
			<?php wp_nonce_field( Builder_Actions::nonce_action( 'toggle_component', $page_id ) ); ?>
			<button type="submit" class="button"><?php echo $is_enabled ? esc_html__( 'Disable', 'nexa-pro-core' ) : esc_html__( 'Enable', 'nexa-pro-core' ); ?></button>
		</form>
		<?php
	}

	/**
	 * Render an order movement form.
	 *
	 * @param int    $page_id     Page ID.
	 * @param string $instance_id Instance ID.
	 * @param string $direction   Direction.
	 * @param bool   $disabled    Disabled.
	 * @return void
	 */
	private static function render_order_form( $page_id, $instance_id, $direction, $disabled ) {
		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="nexa_pro_core_builder_move_order">
			<input type="hidden" name="builder_page_id" value="<?php echo esc_attr( $page_id ); ?>">
			<input type="hidden" name="component_id" value="<?php echo esc_attr( $instance_id ); ?>">
			<input type="hidden" name="direction" value="<?php echo esc_attr( $direction ); ?>">
			<?php wp_nonce_field( Builder_Actions::nonce_action( 'move_order', $page_id ) ); ?>
			<button type="submit" class="button" <?php disabled( $disabled ); ?>><?php echo 'up' === $direction ? esc_html__( 'Move up', 'nexa-pro-core' ) : esc_html__( 'Move down', 'nexa-pro-core' ); ?></button>
		</form>
		<?php
	}

	/**
	 * Render move-to-page form.
	 *
	 * @param \WP_Post $page      Current page.
	 * @param array    $pages     Editable pages.
	 * @param array    $component Component.
	 * @return void
	 */
	private static function render_move_page_form( $page, array $pages, array $component ) {
		$select_id = 'nexa-pro-core-move-' . $component['instance_id'];
		?>
		<form class="nexa-pro-core-builder__move-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="nexa_pro_core_builder_move_page">
			<input type="hidden" name="builder_page_id" value="<?php echo esc_attr( $page->ID ); ?>">
			<input type="hidden" name="component_id" value="<?php echo esc_attr( $component['instance_id'] ); ?>">
			<?php wp_nonce_field( Builder_Actions::nonce_action( 'move_page', $page->ID ) ); ?>
			<label for="<?php echo esc_attr( $select_id ); ?>" class="screen-reader-text"><?php esc_html_e( 'Move component to page', 'nexa-pro-core' ); ?></label>
			<select id="<?php echo esc_attr( $select_id ); ?>" name="target_page_id">
				<option value=""><?php esc_html_e( 'Move to...', 'nexa-pro-core' ); ?></option>
				<?php foreach ( $pages as $target_page ) : ?>
					<?php if ( absint( $target_page->ID ) === absint( $page->ID ) ) : ?>
						<?php continue; ?>
					<?php endif; ?>
					<option value="<?php echo esc_attr( $target_page->ID ); ?>"><?php echo esc_html( get_the_title( $target_page ) ); ?></option>
				<?php endforeach; ?>
			</select>
			<button type="submit" class="button"><?php esc_html_e( 'Move', 'nexa-pro-core' ); ?></button>
		</form>
		<?php
	}

	/**
	 * Render delete form.
	 *
	 * @param int   $page_id   Page ID.
	 * @param array $component Component.
	 * @return void
	 */
	private static function render_delete_form( $page_id, array $component ) {
		$confirm_id = 'nexa-pro-core-delete-' . $component['instance_id'];
		?>
		<form class="nexa-pro-core-builder__delete-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="nexa_pro_core_builder_delete_component">
			<input type="hidden" name="builder_page_id" value="<?php echo esc_attr( $page_id ); ?>">
			<input type="hidden" name="component_id" value="<?php echo esc_attr( $component['instance_id'] ); ?>">
			<?php wp_nonce_field( Builder_Actions::nonce_action( 'delete_component', $page_id ) ); ?>
			<label for="<?php echo esc_attr( $confirm_id ); ?>">
				<input id="<?php echo esc_attr( $confirm_id ); ?>" type="checkbox" name="confirm_delete" value="1" required>
				<?php esc_html_e( 'Confirm', 'nexa-pro-core' ); ?>
			</label>
			<button type="submit" class="button button-link-delete"><?php esc_html_e( 'Delete', 'nexa-pro-core' ); ?></button>
		</form>
		<?php
	}

	/**
	 * Render save-as-reusable form.
	 *
	 * @param int   $page_id   Page ID.
	 * @param array $component Component.
	 * @return void
	 */
	private static function render_save_as_reusable_form( $page_id, array $component ) {
		if ( ! class_exists( Reusable_Actions::class ) || ! Capabilities::current_user_can_manage_reusable_components() ) {
			return;
		}

		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="nexa_pro_core_reusable_save_from_instance">
			<input type="hidden" name="builder_page_id" value="<?php echo esc_attr( $page_id ); ?>">
			<input type="hidden" name="component_id" value="<?php echo esc_attr( $component['instance_id'] ); ?>">
			<input type="hidden" name="save_mode" value="linked">
			<?php wp_nonce_field( Reusable_Actions::nonce_action( 'save_from_instance', $page_id ) ); ?>
			<button type="submit" class="button"><?php esc_html_e( 'Save as reusable', 'nexa-pro-core' ); ?></button>
		</form>
		<?php
	}

	/**
	 * Render detach form for linked instances.
	 *
	 * @param int   $page_id   Page ID.
	 * @param array $component Component.
	 * @return void
	 */
	private static function render_detach_form( $page_id, array $component ) {
		if ( ! class_exists( Reusable_Actions::class ) || 'linked' !== ( isset( $component['inheritance_mode'] ) ? $component['inheritance_mode'] : 'local' ) ) {
			return;
		}

		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="nexa_pro_core_reusable_detach_instance">
			<input type="hidden" name="builder_page_id" value="<?php echo esc_attr( $page_id ); ?>">
			<input type="hidden" name="component_id" value="<?php echo esc_attr( $component['instance_id'] ); ?>">
			<?php wp_nonce_field( Reusable_Actions::nonce_action( 'detach_instance', $page_id ) ); ?>
			<button type="submit" class="button"><?php esc_html_e( 'Detach to local', 'nexa-pro-core' ); ?></button>
		</form>
		<?php
	}

	/**
	 * Render reusable source link.
	 *
	 * @param array $component Component.
	 * @return void
	 */
	private static function render_open_source_link( array $component ) {
		$post_id = ! empty( $component['reusable_component_id'] ) ? absint( $component['reusable_component_id'] ) : 0;

		if ( ! $post_id ) {
			return;
		}

		$url = \add_query_arg(
			array(
				'page'          => self::PAGE_SLUG,
				'builder_view'  => 'reusable',
				'reusable_mode' => 'edit',
				'reusable_id'   => $post_id,
			),
			\admin_url( 'themes.php' )
		);
		?>
		<a class="button" href="<?php echo esc_url( $url ); ?>#nexa-pro-core-reusable-editor"><?php esc_html_e( 'Open source', 'nexa-pro-core' ); ?></a>
		<?php
	}

	/**
	 * Get reusable display status.
	 *
	 * @param array $component Component.
	 * @return array
	 */
	private static function get_reusable_status( array $component ) {
		$post_id = ! empty( $component['reusable_component_id'] ) ? absint( $component['reusable_component_id'] ) : 0;

		if ( 'linked' !== ( isset( $component['inheritance_mode'] ) ? $component['inheritance_mode'] : 'local' ) || ! $post_id ) {
			return array(
				'label' => __( 'Local', 'nexa-pro-core' ),
				'class' => 'is-local',
			);
		}

		$post = \get_post( $post_id );

		if ( ! $post || NEXA_PRO_CORE_REUSABLE_POST_TYPE !== $post->post_type ) {
			return array(
				'label' => __( 'Reusable source missing', 'nexa-pro-core' ),
				'class' => 'is-warning',
			);
		}

		if ( 'draft' === $post->post_status ) {
			return array(
				'label' => __( 'Archived source', 'nexa-pro-core' ),
				'class' => 'is-warning',
			);
		}

		return array(
			'label' => __( 'Linked', 'nexa-pro-core' ),
			'class' => 'is-linked',
		);
	}

	/**
	 * Render component validation warnings.
	 *
	 * @param array $component Component.
	 * @return void
	 */
	private static function render_component_warnings( array $component ) {
		$warnings = array();

		if ( empty( $component['admin_title'] ) ) {
			$warnings[] = __( 'Missing admin title.', 'nexa-pro-core' );
		}

		if ( ! empty( $component['navigation']['show_in_navigation'] ) && empty( $component['navigation']['navigation_label'] ) ) {
			$warnings[] = __( 'Navigation is enabled without a label.', 'nexa-pro-core' );
		}

		if ( empty( $warnings ) ) {
			return;
		}

		echo '<ul class="nexa-pro-core-builder__warnings">';
		foreach ( $warnings as $warning ) {
			echo '<li>' . esc_html( $warning ) . '</li>';
		}
		echo '</ul>';
	}

	/**
	 * Render empty page state.
	 *
	 * @return void
	 */
	private static function render_empty_pages_state() {
		?>
		<div class="nexa-pro-core-builder__empty">
			<h2><?php esc_html_e( 'No editable pages found', 'nexa-pro-core' ); ?></h2>
			<p><?php esc_html_e( 'Create a WordPress page before composing components.', 'nexa-pro-core' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Build a builder URL.
	 *
	 * @param int   $page_id Page ID.
	 * @param array $filters Filters.
	 * @param array $extra   Extra query args.
	 * @return string
	 */
	private static function builder_url( $page_id = 0, $filters = array(), $extra = array() ) {
		$args = array(
			'page'            => self::PAGE_SLUG,
			'builder_page_id' => absint( $page_id ),
		);

		foreach ( array( 'search', 'status', 'configured' ) as $filter_key ) {
			if ( ! empty( $filters[ $filter_key ] ) ) {
				$args[ 'builder_' . $filter_key ] = $filters[ $filter_key ];
			}
		}

		return \add_query_arg( array_merge( $args, $extra ), \admin_url( 'themes.php' ) );
	}

	/**
	 * Get a nested component value.
	 *
	 * @param array $array   Array.
	 * @param array $path    Path.
	 * @param mixed $default Default.
	 * @return mixed
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
	 * Get first supported layout.
	 *
	 * @param array $definition Definition.
	 * @return string
	 */
	private static function first_layout( array $definition ) {
		if ( ! empty( $definition['default_layout'] ) ) {
			return $definition['default_layout'];
		}

		return ! empty( $definition['supported_layouts'][0] ) ? $definition['supported_layouts'][0] : 'default';
	}

	/**
	 * Determine whether a component definition supports a layout control.
	 *
	 * @param array  $definition Definition.
	 * @param string $control    Control key.
	 * @return bool
	 */
	private static function supports_layout_control( array $definition, $control ) {
		$controls = ! empty( $definition['layout_controls'] ) && is_array( $definition['layout_controls'] ) ? $definition['layout_controls'] : array( 'layout' );

		return in_array( \sanitize_key( $control ), $controls, true );
	}

	/**
	 * Determine whether a component definition supports a design capability.
	 *
	 * @param array  $definition Definition.
	 * @param string $capability Capability key.
	 * @return bool
	 */
	private static function supports_design_capability( array $definition, $capability ) {
		$capabilities = ! empty( $definition['design_capabilities'] ) && is_array( $definition['design_capabilities'] ) ? $definition['design_capabilities'] : array();

		return in_array( \sanitize_key( $capability ), $capabilities, true );
	}

	/**
	 * Render a lightweight layout preview.
	 *
	 * @param array $definition Definition.
	 * @return void
	 */
	private static function render_layout_preview( array $definition ) {
		$preview = ! empty( $definition['preview'] ) && is_array( $definition['preview'] ) ? $definition['preview'] : array();
		$label   = ! empty( $preview['label'] ) ? $preview['label'] : $definition['label'];
		$text    = ! empty( $preview['description'] ) ? $preview['description'] : $definition['description'];
		?>
		<div class="nexa-pro-core-builder__layout-preview" aria-label="<?php echo esc_attr( $label ); ?>">
			<span class="nexa-pro-core-builder__layout-preview-media" aria-hidden="true"></span>
			<span class="nexa-pro-core-builder__layout-preview-lines" aria-hidden="true">
				<span></span>
				<span></span>
				<span></span>
			</span>
			<p><strong><?php echo esc_html( $label ); ?></strong></p>
			<?php if ( $text ) : ?>
				<p class="description"><?php echo esc_html( $text ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $definition['responsive_notes'] ) ) : ?>
				<p class="description"><?php echo esc_html( $definition['responsive_notes'] ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render a text field.
	 */
	private static function render_text_field( $name, $id, $label, $value, $required = false ) {
		printf(
			'<p><label for="%1$s">%2$s</label><input class="regular-text" type="text" id="%1$s" name="%3$s" value="%4$s"%5$s></p>',
			esc_attr( $id ),
			esc_html( $label ),
			esc_attr( $name ),
			esc_attr( $value ),
			$required ? ' required' : ''
		);
	}

	/**
	 * Render a URL field.
	 */
	private static function render_url_field( $name, $id, $label, $value ) {
		printf(
			'<p><label for="%1$s">%2$s</label><input class="regular-text" type="url" id="%1$s" name="%3$s" value="%4$s"></p>',
			esc_attr( $id ),
			esc_html( $label ),
			esc_attr( $name ),
			esc_attr( $value )
		);
	}

	/**
	 * Render a textarea field.
	 */
	private static function render_textarea_field( $name, $id, $label, $value ) {
		printf(
			'<p><label for="%1$s">%2$s</label><textarea class="large-text" rows="4" id="%1$s" name="%3$s">%4$s</textarea></p>',
			esc_attr( $id ),
			esc_html( $label ),
			esc_attr( $name ),
			esc_textarea( $value )
		);
	}

	/**
	 * Render a number field.
	 */
	private static function render_number_field( $name, $id, $label, $value, $min, $max ) {
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
	 * Render a color field.
	 */
	private static function render_color_field( $name, $id, $label, $value ) {
		printf(
			'<p><label for="%1$s">%2$s</label><input type="text" pattern="^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$" placeholder="#ffffff" id="%1$s" name="%3$s" value="%4$s"></p>',
			esc_attr( $id ),
			esc_html( $label ),
			esc_attr( $name ),
			esc_attr( $value )
		);
	}

	/**
	 * Render a checkbox field.
	 */
	private static function render_checkbox_field( $name, $id, $label, $checked ) {
		printf(
			'<p><input type="hidden" name="%1$s" value="0"><label for="%2$s"><input type="checkbox" id="%2$s" name="%1$s" value="1"%3$s> %4$s</label></p>',
			esc_attr( $name ),
			esc_attr( $id ),
			checked( $checked, true, false ),
			esc_html( $label )
		);
	}

	/**
	 * Render a select field.
	 */
	private static function render_select_field( $name, $id, $label, array $options, $selected ) {
		printf(
			'<p><label for="%1$s">%2$s</label><select id="%1$s" name="%3$s">',
			esc_attr( $id ),
			esc_html( $label ),
			esc_attr( $name )
		);
		self::render_options( $options, $selected );
		echo '</select></p>';
	}

	/**
	 * Render options.
	 */
	private static function render_options( array $options, $selected ) {
		foreach ( $options as $value => $label ) {
			printf(
				'<option value="%1$s"%2$s>%3$s</option>',
				esc_attr( $value ),
				selected( (string) $selected, (string) $value, false ),
				esc_html( $label )
			);
		}
	}

	/**
	 * Get a status label.
	 */
	private static function status_label( $status ) {
		$labels = self::page_status_options();

		return isset( $labels[ $status ] ) ? $labels[ $status ] : ucfirst( $status );
	}

	/**
	 * Options.
	 */
	private static function page_status_options() {
		return array(
			''        => __( 'All statuses', 'nexa-pro-core' ),
			'publish' => __( 'Published', 'nexa-pro-core' ),
			'draft'   => __( 'Draft', 'nexa-pro-core' ),
			'pending' => __( 'Pending', 'nexa-pro-core' ),
			'private' => __( 'Private', 'nexa-pro-core' ),
			'future'  => __( 'Scheduled', 'nexa-pro-core' ),
		);
	}

	private static function alignment_options() {
		return array( 'inherit' => __( 'Inherit', 'nexa-pro-core' ), 'left' => __( 'Left', 'nexa-pro-core' ), 'center' => __( 'Center', 'nexa-pro-core' ), 'right' => __( 'Right', 'nexa-pro-core' ) );
	}

	private static function media_position_options() {
		return array( 'inherit' => __( 'Inherit', 'nexa-pro-core' ), 'left' => __( 'Left', 'nexa-pro-core' ), 'right' => __( 'Right', 'nexa-pro-core' ), 'top' => __( 'Top', 'nexa-pro-core' ), 'bottom' => __( 'Bottom', 'nexa-pro-core' ), 'background' => __( 'Background', 'nexa-pro-core' ) );
	}

	private static function container_width_options() {
		return array( 'inherit' => __( 'Inherit', 'nexa-pro-core' ), 'narrow' => __( 'Narrow', 'nexa-pro-core' ), 'standard' => __( 'Standard', 'nexa-pro-core' ), 'wide' => __( 'Wide', 'nexa-pro-core' ), 'full' => __( 'Full width', 'nexa-pro-core' ) );
	}

	private static function content_width_options() {
		return array( 'inherit' => __( 'Inherit', 'nexa-pro-core' ), 'narrow' => __( 'Narrow', 'nexa-pro-core' ), 'standard' => __( 'Standard', 'nexa-pro-core' ), 'wide' => __( 'Wide', 'nexa-pro-core' ) );
	}

	private static function spacing_options() {
		return array( 'inherit' => __( 'Inherit', 'nexa-pro-core' ), 'none' => __( 'None', 'nexa-pro-core' ), 'compact' => __( 'Compact', 'nexa-pro-core' ), 'standard' => __( 'Standard', 'nexa-pro-core' ), 'spacious' => __( 'Spacious', 'nexa-pro-core' ), 'extra-spacious' => __( 'Extra spacious', 'nexa-pro-core' ) );
	}

	private static function item_spacing_options() {
		return array( 'inherit' => __( 'Inherit', 'nexa-pro-core' ), 'compact' => __( 'Compact', 'nexa-pro-core' ), 'standard' => __( 'Standard', 'nexa-pro-core' ), 'spacious' => __( 'Spacious', 'nexa-pro-core' ) );
	}

	private static function card_density_options() {
		return array( 'inherit' => __( 'Inherit', 'nexa-pro-core' ), 'compact' => __( 'Compact', 'nexa-pro-core' ), 'comfortable' => __( 'Comfortable', 'nexa-pro-core' ), 'spacious' => __( 'Spacious', 'nexa-pro-core' ) );
	}

	private static function background_options() {
		return array( 'inherit' => __( 'Inherit', 'nexa-pro-core' ), 'solid' => __( 'Solid', 'nexa-pro-core' ), 'gradient' => __( 'Gradient', 'nexa-pro-core' ), 'image' => __( 'Image', 'nexa-pro-core' ) );
	}

	private static function gradient_direction_options() {
		return array( 'to-bottom' => __( 'Top to bottom', 'nexa-pro-core' ), 'to-right' => __( 'Left to right', 'nexa-pro-core' ), 'to-bottom-right' => __( 'Diagonal right', 'nexa-pro-core' ), 'to-bottom-left' => __( 'Diagonal left', 'nexa-pro-core' ) );
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

	private static function image_style_options() {
		return array( 'inherit' => __( 'Inherit', 'nexa-pro-core' ), 'square' => __( 'Square', 'nexa-pro-core' ), 'soft' => __( 'Soft', 'nexa-pro-core' ), 'rounded' => __( 'Rounded', 'nexa-pro-core' ), 'pill' => __( 'Pill', 'nexa-pro-core' ), 'circle' => __( 'Circle', 'nexa-pro-core' ) );
	}

	private static function button_style_options() {
		return array( 'inherit' => __( 'Inherit', 'nexa-pro-core' ), 'primary' => __( 'Primary', 'nexa-pro-core' ), 'secondary' => __( 'Secondary', 'nexa-pro-core' ), 'outline' => __( 'Outline', 'nexa-pro-core' ), 'ghost' => __( 'Ghost', 'nexa-pro-core' ), 'text' => __( 'Text', 'nexa-pro-core' ) );
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

	private static function animation_options() {
		return array( 'none' => __( 'None', 'nexa-pro-core' ), 'fade' => __( 'Fade', 'nexa-pro-core' ), 'slide-up' => __( 'Slide up', 'nexa-pro-core' ) );
	}

	/**
	 * Notice message.
	 */
	private static function get_notice_message( $notice ) {
		$messages = array(
			'component_added'           => __( 'Component added.', 'nexa-pro-core' ),
			'component_updated'         => __( 'Component saved.', 'nexa-pro-core' ),
			'component_duplicated'      => __( 'Component duplicated.', 'nexa-pro-core' ),
			'component_toggled'         => __( 'Component status updated.', 'nexa-pro-core' ),
			'component_reordered'       => __( 'Component order updated.', 'nexa-pro-core' ),
			'component_order_saved'     => __( 'Component order saved.', 'nexa-pro-core' ),
			'component_order_unchanged' => __( 'Component is already at that boundary.', 'nexa-pro-core' ),
			'component_moved'           => __( 'Component moved to the selected page.', 'nexa-pro-core' ),
			'component_deleted'         => __( 'Component deleted. Undo is available for a short time.', 'nexa-pro-core' ),
			'component_restored'        => __( 'Deleted component restored.', 'nexa-pro-core' ),
		);

		return isset( $messages[ $notice ] ) ? $messages[ $notice ] : __( 'Builder action completed.', 'nexa-pro-core' );
	}

	/**
	 * Error fallback message.
	 */
	private static function get_error_message( $error ) {
		$messages = array(
			'nexa_pro_core_invalid_nonce'       => __( 'The request expired. Refresh the screen and try again.', 'nexa-pro-core' ),
			'nexa_pro_core_forbidden'           => __( 'You do not have permission to manage components.', 'nexa-pro-core' ),
			'nexa_pro_core_page_forbidden'      => __( 'You do not have permission to edit this page.', 'nexa-pro-core' ),
			'nexa_pro_core_delete_unconfirmed'  => __( 'Confirm deletion before removing a component.', 'nexa-pro-core' ),
			'nexa_pro_core_duplicate_anchor'    => __( 'Each component anchor must be unique on a page.', 'nexa-pro-core' ),
			'nexa_pro_core_invalid_layout'      => __( 'Choose a supported layout for this component.', 'nexa-pro-core' ),
			'nexa_pro_core_invalid_component'   => __( 'Component data is invalid.', 'nexa-pro-core' ),
			'nexa_pro_core_invalid_reorder'     => __( 'The submitted order did not include every component exactly once.', 'nexa-pro-core' ),
			'nexa_pro_core_missing_component'   => __( 'The requested component could not be found.', 'nexa-pro-core' ),
			'nexa_pro_core_invalid_page'        => __( 'Choose a valid WordPress page.', 'nexa-pro-core' ),
			'nexa_pro_core_invalid_component_type' => __( 'The selected component type is not supported.', 'nexa-pro-core' ),
		);

		return isset( $messages[ $error ] ) ? $messages[ $error ] : __( 'The builder request could not be completed.', 'nexa-pro-core' );
	}
}
