<?php
/**
 * Nexa Pro theme bootstrap.
 *
 * @package Nexa_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NEXA_PRO_VERSION', '1.0.0' );
define( 'NEXA_PRO_DIR', get_template_directory() );
define( 'NEXA_PRO_URI', get_template_directory_uri() );

require_once NEXA_PRO_DIR . '/inc/setup.php';
require_once NEXA_PRO_DIR . '/inc/options/defaults.php';
require_once NEXA_PRO_DIR . '/inc/options/getters.php';
require_once NEXA_PRO_DIR . '/inc/options/sanitization.php';
require_once NEXA_PRO_DIR . '/inc/presets.php';
require_once NEXA_PRO_DIR . '/inc/assets.php';
require_once NEXA_PRO_DIR . '/inc/template-functions.php';
require_once NEXA_PRO_DIR . '/inc/template-tags.php';
require_once NEXA_PRO_DIR . '/inc/homepage-sections.php';
require_once NEXA_PRO_DIR . '/inc/admin/fields.php';
require_once NEXA_PRO_DIR . '/inc/admin/tools.php';
require_once NEXA_PRO_DIR . '/inc/admin/page.php';
require_once NEXA_PRO_DIR . '/inc/admin/menu.php';
