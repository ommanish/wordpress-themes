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

	$custom_css = nexa_pro_get_global_design_css();

	if ( $custom_css ) {
		wp_add_inline_style( 'nexa-pro-theme', $custom_css );
	}

	wp_enqueue_script(
		'nexa-pro-theme',
		NEXA_PRO_URI . '/assets/js/theme.js',
		array(),
		NEXA_PRO_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'nexa_pro_enqueue_assets' );

/**
 * Enqueue dynamic editor design styles.
 *
 * @return void
 */
function nexa_pro_enqueue_editor_design_assets() {
	$custom_css = nexa_pro_get_global_design_css( '.editor-styles-wrapper' );

	if ( ! $custom_css ) {
		return;
	}

	wp_register_style(
		'nexa-pro-editor-design',
		false,
		array(),
		NEXA_PRO_VERSION
	);

	wp_enqueue_style( 'nexa-pro-editor-design' );
	wp_add_inline_style( 'nexa-pro-editor-design', $custom_css );
}
add_action( 'enqueue_block_editor_assets', 'nexa_pro_enqueue_editor_design_assets' );

/**
 * Enqueue admin assets for the Nexa Pro settings page.
 *
 * @param string $hook_suffix Current admin page hook suffix.
 * @return void
 */
function nexa_pro_enqueue_admin_assets( $hook_suffix ) {
	if ( 'appearance_page_nexa-pro' !== $hook_suffix ) {
		return;
	}

	wp_enqueue_style(
		'nexa-pro-admin',
		NEXA_PRO_URI . '/assets/css/admin.css',
		array( 'wp-color-picker' ),
		NEXA_PRO_VERSION
	);

	wp_enqueue_media();

	wp_enqueue_script(
		'nexa-pro-admin',
		NEXA_PRO_URI . '/assets/js/admin.js',
		array( 'wp-color-picker' ),
		NEXA_PRO_VERSION,
		true
	);
}
add_action( 'admin_enqueue_scripts', 'nexa_pro_enqueue_admin_assets' );
