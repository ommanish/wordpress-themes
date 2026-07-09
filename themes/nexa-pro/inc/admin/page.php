<?php
/**
 * Admin page rendering.
 *
 * @package Nexa_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get admin tabs.
 *
 * @return array
 */
function nexa_pro_get_admin_tabs() {
	return array(
		'dashboard' => __( 'Dashboard', 'nexa-pro' ),
		'global'    => __( 'Global', 'nexa-pro' ),
		'header'    => __( 'Header', 'nexa-pro' ),
		'hero'      => __( 'Hero', 'nexa-pro' ),
	);
}

/**
 * Get the active admin tab from the request.
 *
 * @return string
 */
function nexa_pro_get_active_admin_tab() {
	$tabs = nexa_pro_get_admin_tabs();
	$tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'dashboard';

	return array_key_exists( $tab, $tabs ) ? $tab : 'dashboard';
}

/**
 * Render admin tabs.
 *
 * @param string $active_tab Active tab.
 * @return void
 */
function nexa_pro_render_admin_tabs( $active_tab ) {
	$tabs = nexa_pro_get_admin_tabs();

	?>
	<nav class="nav-tab-wrapper" aria-label="<?php esc_attr_e( 'Nexa Pro settings sections', 'nexa-pro' ); ?>">
		<?php foreach ( $tabs as $tab => $label ) : ?>
			<?php
			$url   = add_query_arg(
				array(
					'page' => 'nexa-pro',
					'tab'  => $tab,
				),
				admin_url( 'themes.php' )
			);
			$class = 'nav-tab';

			if ( $active_tab === $tab ) {
				$class .= ' nav-tab-active';
			}
			?>
			<a class="<?php echo esc_attr( $class ); ?>" href="<?php echo esc_url( $url ); ?>">
				<?php echo esc_html( $label ); ?>
			</a>
		<?php endforeach; ?>
	</nav>
	<?php
}

/**
 * Render the dashboard tab.
 *
 * @return void
 */
function nexa_pro_render_dashboard_tab() {
	?>
	<div class="nexa-pro-admin__panel">
		<h2><?php esc_html_e( 'Theme settings foundation', 'nexa-pro' ); ?></h2>
		<p><?php esc_html_e( 'Use these settings to configure the global brand, header branding and navigation behavior, and homepage hero content. More controls will be added in later tickets.', 'nexa-pro' ); ?></p>
		<ul>
			<li><?php esc_html_e( 'Global: brand labels and color tokens.', 'nexa-pro' ); ?></li>
			<li><?php esc_html_e( 'Header: logos, brand text display, sticky or transparent state, layout, and call-to-action behavior.', 'nexa-pro' ); ?></li>
			<li><?php esc_html_e( 'Hero: homepage hero text and button links.', 'nexa-pro' ); ?></li>
		</ul>
	</div>
	<?php
}

/**
 * Render the Nexa Pro admin page.
 *
 * @return void
 */
function nexa_pro_render_admin_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'nexa-pro' ) );
	}

	$active_tab = nexa_pro_get_active_admin_tab();

	?>
	<div class="wrap nexa-pro-admin">
		<h1><?php esc_html_e( 'Nexa Pro', 'nexa-pro' ); ?></h1>
		<?php settings_errors(); ?>
		<?php nexa_pro_render_admin_tabs( $active_tab ); ?>

		<?php if ( 'dashboard' === $active_tab ) : ?>
			<?php nexa_pro_render_dashboard_tab(); ?>
		<?php else : ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" class="nexa-pro-admin__form">
				<?php settings_fields( 'nexa_pro_options_group' ); ?>
				<input type="hidden" name="nexa_pro_active_tab" value="<?php echo esc_attr( $active_tab ); ?>">
				<?php nexa_pro_render_admin_fields( $active_tab ); ?>
				<div class="nexa-pro-admin__save">
					<?php submit_button( __( 'Save Settings', 'nexa-pro' ), 'primary', 'submit', false ); ?>
				</div>
			</form>
		<?php endif; ?>
	</div>
	<?php
}
