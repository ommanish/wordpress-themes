<?php
/**
 * Nexa Pro theme bootstrap.
 *
 * @package Nexa_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NEXA_PRO_VERSION', '0.1.0' );
define( 'NEXA_PRO_DIR', get_template_directory() );
define( 'NEXA_PRO_URI', get_template_directory_uri() );

require_once NEXA_PRO_DIR . '/inc/setup.php';
require_once NEXA_PRO_DIR . '/inc/assets.php';
require_once NEXA_PRO_DIR . '/inc/template-functions.php';
require_once NEXA_PRO_DIR . '/inc/template-tags.php';
require_once NEXA_PRO_DIR . '/inc/homepage-sections.php';
