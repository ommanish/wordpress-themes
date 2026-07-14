<?php
/**
 * Conservative uninstall behavior.
 *
 * @package Nexa_Pro_Core
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( ! defined( 'NEXA_PRO_CORE_DELETE_DATA' ) || true !== NEXA_PRO_CORE_DELETE_DATA ) {
	return;
}

delete_option( 'nexa_pro_core_migration_state' );
