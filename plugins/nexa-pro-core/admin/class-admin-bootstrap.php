<?php
/**
 * Admin bootstrap for future builder screens.
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
	 * Phase 6B intentionally exposes no visible builder UI.
	 *
	 * @return void
	 */
	public static function register() {
		/**
		 * Fires when the Phase 6B admin bootstrap is loaded.
		 *
		 * No visible builder UI is registered in Phase 6B.
		 */
		\do_action( 'nexa_pro_core_admin_bootstrap_loaded' );
	}
}
