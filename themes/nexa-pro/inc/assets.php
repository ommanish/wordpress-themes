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

	$primary_color = nexa_pro_get_option( 'primary_color' );
	$accent_color  = nexa_pro_get_option( 'accent_color' );
	$custom_css    = '';

	if ( $primary_color && sanitize_hex_color( $primary_color ) === $primary_color ) {
		$custom_css .= '--nexa-pro-color-primary:' . $primary_color . ';--nexa-pro-color-primary-dark:' . $primary_color . ';';
	}

	if ( $accent_color && sanitize_hex_color( $accent_color ) === $accent_color ) {
		$custom_css .= '--nexa-pro-color-accent:' . $accent_color . ';--nexa-pro-color-focus:' . $accent_color . ';';
	}

	if ( $custom_css ) {
		wp_add_inline_style( 'nexa-pro-theme', 'body{' . $custom_css . '}' );
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
		array(),
		NEXA_PRO_VERSION
	);

	wp_enqueue_media();

	wp_enqueue_script(
		'nexa-pro-admin',
		NEXA_PRO_URI . '/assets/js/admin.js',
		array(),
		NEXA_PRO_VERSION,
		true
	);
}
add_action( 'admin_enqueue_scripts', 'nexa_pro_enqueue_admin_assets' );
