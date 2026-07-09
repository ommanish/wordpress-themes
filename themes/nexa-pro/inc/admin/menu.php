<?php
/**
 * Admin menu and settings registration.
 *
 * @package Nexa_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Nexa Pro admin page.
 *
 * @return void
 */
function nexa_pro_register_admin_menu() {
	add_theme_page(
		esc_html__( 'Nexa Pro', 'nexa-pro' ),
		esc_html__( 'Nexa Pro', 'nexa-pro' ),
		'edit_theme_options',
		'nexa-pro',
		'nexa_pro_render_admin_page'
	);
}
add_action( 'admin_menu', 'nexa_pro_register_admin_menu' );

/**
 * Register theme settings.
 *
 * @return void
 */
function nexa_pro_register_settings() {
	register_setting(
		'nexa_pro_options_group',
		'nexa_pro_options',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'nexa_pro_sanitize_options',
			'default'           => nexa_pro_get_default_options(),
		)
	);
}
add_action( 'admin_init', 'nexa_pro_register_settings' );

