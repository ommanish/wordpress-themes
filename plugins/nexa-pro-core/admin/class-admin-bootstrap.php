<?php
/**
 * Admin bootstrap for builder screens.
 *
 * @package Nexa_Pro_Core
 */

namespace Nexa_Pro_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin bootstrap service.
 */
final class Admin_Bootstrap {
	/**
	 * Register admin hooks.
	 *
	 * @return void
	 */
	public static function register() {
		Builder_Admin::register();
		Builder_Actions::register();

		/**
		 * Fires when the Nexa Pro Core admin bootstrap is loaded.
		 *
		 * @since 0.1.0
		 */
		\do_action( 'nexa_pro_core_admin_bootstrap_loaded' );
	}
}
