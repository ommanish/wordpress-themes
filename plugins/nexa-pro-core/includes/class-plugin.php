<?php
/**
 * Plugin coordinator.
 *
 * @package Nexa_Pro_Core
 */

namespace Nexa_Pro_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin coordinator.
 */
final class Plugin {
	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register plugin hooks.
	 *
	 * @return void
	 */
	public function register() {
		\load_plugin_textdomain( 'nexa-pro-core', false, dirname( NEXA_PRO_CORE_BASENAME ) . '/languages' );

		\add_filter( 'map_meta_cap', array( Capabilities::class, 'map_meta_cap' ), 10, 4 );
		\add_action( 'init', array( Reusable_Components::class, 'register_post_type' ), 5 );
		\add_action( 'init', array( Schema::class, 'maybe_initialize' ), 20 );
		\add_action( 'admin_init', array( Capabilities::class, 'maybe_grant_administrator_capabilities' ), 5 );

		Admin_Bootstrap::register();
	}

	/**
	 * Plugin activation callback.
	 *
	 * @return void
	 */
	public static function activate() {
		Reusable_Components::register_post_type();
		Capabilities::grant_administrator_capabilities();
		Schema::initialize();
	}

	/**
	 * Plugin deactivation callback.
	 *
	 * @return void
	 */
	public static function deactivate() {
		// Deactivation intentionally preserves page meta and reusable components.
	}

	/**
	 * Prevent external construction.
	 */
	private function __construct() {}
}
