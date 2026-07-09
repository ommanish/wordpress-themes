<?php
/**
 * Asset registration and enqueueing.
 *
 * @package Nexa_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue front-end assets.
 *
 * @return void
 */
function nexa_pro_enqueue_assets() {
	wp_enqueue_style(
		'nexa-pro-theme',
		NEXA_PRO_URI . '/assets/css/theme.css',
		array(),
		NEXA_PRO_VERSION
	);

	wp_enqueue_script(
		'nexa-pro-theme',
		NEXA_PRO_URI . '/assets/js/theme.js',
		array(),
		NEXA_PRO_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'nexa_pro_enqueue_assets' );

