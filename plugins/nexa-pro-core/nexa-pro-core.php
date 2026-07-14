<?php
/**
 * Plugin Name: Nexa Pro Core
 * Plugin URI: https://github.com/ommanish/wordpress-themes
 * Description: Storage and builder administration for Nexa Pro component composition.
 * Version: 0.4.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Nexa Pro
 * Author URI: https://github.com/ommanish
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: nexa-pro-core
 * Domain Path: /languages
 *
 * @package Nexa_Pro_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
	add_action( 'admin_notices', 'nexa_pro_core_php_version_notice' );
	return;
}

define( 'NEXA_PRO_CORE_VERSION', '0.4.0' );
define( 'NEXA_PRO_CORE_SCHEMA_VERSION', 1 );
define( 'NEXA_PRO_CORE_PAGE_META_KEY', '_nexa_pro_components' );
define( 'NEXA_PRO_CORE_REUSABLE_POST_TYPE', 'nexa_component' );
define( 'NEXA_PRO_CORE_MIGRATION_STATE_OPTION', 'nexa_pro_core_migration_state' );
define( 'NEXA_PRO_CORE_REUSABLE_META_KEY', '_nexa_pro_core_component_payload' );
define( 'NEXA_PRO_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'NEXA_PRO_CORE_URL', plugin_dir_url( __FILE__ ) );
define( 'NEXA_PRO_CORE_BASENAME', plugin_basename( __FILE__ ) );

$nexa_pro_core_files = array(
	'includes/class-capabilities.php',
	'includes/class-schema.php',
	'includes/class-sanitizer.php',
	'includes/class-storage.php',
	'includes/class-reusable-components.php',
	'includes/class-render-api.php',
	'includes/class-navigation-settings.php',
	'includes/class-navigation-tree.php',
	'admin/class-navigation-actions.php',
	'admin/class-navigation-admin.php',
	'admin/class-reusable-actions.php',
	'admin/class-reusable-admin.php',
	'admin/class-builder-actions.php',
	'admin/class-builder-admin.php',
	'admin/class-admin-bootstrap.php',
	'includes/class-plugin.php',
	'includes/helpers.php',
);

foreach ( $nexa_pro_core_files as $nexa_pro_core_file ) {
	$nexa_pro_core_path = NEXA_PRO_CORE_DIR . $nexa_pro_core_file;

	if ( file_exists( $nexa_pro_core_path ) ) {
		require_once $nexa_pro_core_path;
	}
}

register_activation_hook( __FILE__, array( 'Nexa_Pro_Core\\Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Nexa_Pro_Core\\Plugin', 'deactivate' ) );

add_action( 'plugins_loaded', 'nexa_pro_core_bootstrap' );

/**
 * Display the minimum PHP version notice.
 *
 * @return void
 */
function nexa_pro_core_php_version_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	printf(
		'<div class="notice notice-error"><p>%s</p></div>',
		esc_html__( 'Nexa Pro Core requires PHP 7.4 or newer.', 'nexa-pro-core' )
	);
}

/**
 * Bootstrap the plugin.
 *
 * @return void
 */
function nexa_pro_core_bootstrap() {
	if ( class_exists( 'Nexa_Pro_Core\\Plugin' ) ) {
		Nexa_Pro_Core\Plugin::instance()->register();
	}
}
