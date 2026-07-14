#!/usr/bin/env php
<?php
/**
 * Validate Phase 6D reusable workflows.
 *
 * @package Nexa_Pro_Core
 */

define( 'NEXA_PRO_CORE_TESTING', true );

$plugin_dir = realpath( __DIR__ . '/..' );
$wp_load    = getenv( 'NEXA_PRO_CORE_WP_LOAD' );
$failures   = array();
$cleanup    = array();

foreach ( $argv as $arg ) {
	if ( 0 === strpos( $arg, '--wp-load=' ) ) {
		$wp_load = substr( $arg, 10 );
	}
}

if ( ! $wp_load || ! file_exists( $wp_load ) ) {
	fwrite( STDERR, "A WordPress wp-load.php path is required for reusable validation.\n" );
	exit( 1 );
}

require_once $wp_load;

if ( ! defined( 'NEXA_PRO_CORE_VERSION' ) ) {
	require_once $plugin_dir . '/nexa-pro-core.php';
}

if ( class_exists( 'Nexa_Pro_Core\\Plugin' ) ) {
	Nexa_Pro_Core\Plugin::activate();
	Nexa_Pro_Core\Plugin::instance()->register();
}

/**
 * Assert helper.
 *
 * @param bool   $condition Condition.
 * @param string $message   Message.
 * @return void
 */
function nexa_pro_core_reusable_assert( $condition, $message ) {
	global $failures;

	if ( ! $condition ) {
		$failures[] = $message;
	}
}

/**
 * Create test page.
 *
 * @param string $title Title.
 * @return int
 */
function nexa_pro_core_reusable_page( $title ) {
	global $cleanup;

	$page_id = wp_insert_post(
		array(
			'post_title'  => $title,
			'post_type'   => 'page',
			'post_status' => 'publish',
		),
		true
	);

	if ( is_wp_error( $page_id ) ) {
		return 0;
	}

	$cleanup['pages'][] = absint( $page_id );

	return absint( $page_id );
}

/**
 * Component fixture.
 *
 * @param string $title Title.
 * @return array
 */
function nexa_pro_core_reusable_component( $title = 'Reusable CTA' ) {
	return array(
		'component_type' => 'cta',
		'admin_title'    => $title,
		'enabled'        => '1',
		'layout'         => 'default',
		'content'        => array(
			'heading'           => $title,
			'body'              => 'Reusable body',
			'primary_cta_label' => 'Start',
			'primary_cta_url'   => '#contact',
		),
		'navigation'     => array(
			'show_in_navigation' => '1',
			'navigation_label'   => $title,
			'anchor_id'          => 'reusable-cta',
		),
	);
}

$cleanup['pages']     = array();
$cleanup['reusables'] = array();
$args                 = array( 'bypass_capability_check' => true );
$page_a               = nexa_pro_core_reusable_page( 'Nexa Reusable Test A' );
$page_b               = nexa_pro_core_reusable_page( 'Nexa Reusable Test B' );
$reusable_id          = nexa_pro_core_create_reusable_component( nexa_pro_core_reusable_component(), $args );

nexa_pro_core_reusable_assert( ! is_wp_error( $reusable_id ) && $reusable_id > 0, 'Reusable component should be created.' );
$cleanup['reusables'][] = absint( $reusable_id );

$duplicate_id = nexa_pro_core_duplicate_reusable_component( $reusable_id, $args );
nexa_pro_core_reusable_assert( ! is_wp_error( $duplicate_id ) && $duplicate_id !== $reusable_id, 'Reusable duplicate should be created.' );
$cleanup['reusables'][] = absint( $duplicate_id );

$linked = nexa_pro_core_add_page_component(
	$page_a,
	array_merge(
		nexa_pro_core_reusable_component(),
		array(
			'inheritance_mode'      => 'linked',
			'reusable_component_id' => $reusable_id,
		)
	),
	$args
);
$local = nexa_pro_core_add_page_component( $page_b, nexa_pro_core_reusable_component( 'Local CTA' ), $args );

nexa_pro_core_reusable_assert( ! is_wp_error( $linked ), 'Linked instance should be inserted.' );
nexa_pro_core_reusable_assert( ! is_wp_error( $local ), 'Local instance should be inserted.' );
nexa_pro_core_reusable_assert( 1 === nexa_pro_core_get_reusable_usage_count( $reusable_id ), 'Usage count should include linked instance only.' );

$updated = nexa_pro_core_update_reusable_component(
	$reusable_id,
	array(
		'content' => array(
			'heading' => 'Updated source heading',
			'body'    => 'Updated body',
		),
	),
	$args
);
nexa_pro_core_reusable_assert( ! is_wp_error( $updated ), 'Reusable source should update.' );

$components = nexa_pro_core_get_page_components( $page_a );
$resolved   = nexa_pro_core_resolve_component_instance( $components[0] );
nexa_pro_core_reusable_assert( 'Updated source heading' === $resolved['content']['heading'], 'Linked instance should resolve updated source content.' );

$local_components = nexa_pro_core_get_page_components( $page_b );
nexa_pro_core_reusable_assert( 'Local CTA' === $local_components[0]['admin_title'], 'Local copy should remain independent.' );

$delete_blocked = nexa_pro_core_delete_reusable_component( $reusable_id, $args );
nexa_pro_core_reusable_assert( is_wp_error( $delete_blocked ), 'Delete should be blocked while linked.' );

$detached = nexa_pro_core_detach_reusable_component( $page_a, $linked['instance_id'], $args );
nexa_pro_core_reusable_assert( ! is_wp_error( $detached ) && 'local' === $detached['inheritance_mode'], 'Detach should convert linked instance to local.' );
nexa_pro_core_reusable_assert( 0 === nexa_pro_core_get_reusable_usage_count( $reusable_id ), 'Usage count should update after detach.' );

$second_link = nexa_pro_core_add_page_component(
	$page_a,
	array_merge(
		nexa_pro_core_reusable_component(),
		array(
			'navigation'            => array(
				'anchor_id'          => 'second-reusable',
				'navigation_label'   => 'Second reusable',
				'show_in_navigation' => '1',
			),
			'inheritance_mode'      => 'linked',
			'reusable_component_id' => $reusable_id,
		)
	),
	$args
);
nexa_pro_core_reusable_assert( ! is_wp_error( $second_link ), 'Second linked instance should be inserted.' );

$detached_count = nexa_pro_core_detach_all_reusable_instances( $reusable_id, $args );
nexa_pro_core_reusable_assert( 1 === $detached_count, 'Detach-all should detach linked instances.' );

$deleted = nexa_pro_core_delete_reusable_component( $reusable_id, $args );
nexa_pro_core_reusable_assert( ! is_wp_error( $deleted ), 'Reusable should delete after detach-all.' );
$cleanup['reusables'] = array_values( array_diff( $cleanup['reusables'], array( absint( $reusable_id ) ) ) );

$recursive = Nexa_Pro_Core\Reusable_Components::would_create_recursive_reference( $duplicate_id, $duplicate_id );
nexa_pro_core_reusable_assert( $recursive, 'Self reusable references should be rejected.' );

foreach ( $cleanup['reusables'] as $post_id ) {
	wp_delete_post( $post_id, true );
}

foreach ( $cleanup['pages'] as $page_id ) {
	wp_delete_post( $page_id, true );
}

if ( $failures ) {
	foreach ( $failures as $failure ) {
		fwrite( STDERR, "FAIL: {$failure}\n" );
	}

	exit( 1 );
}

echo "Nexa Pro Core reusable validation passed.\n";
