#!/usr/bin/env php
<?php
/**
 * Validate Phase 6D navigation behavior.
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
	fwrite( STDERR, "A WordPress wp-load.php path is required for navigation validation.\n" );
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
function nexa_pro_core_navigation_assert( $condition, $message ) {
	global $failures;

	if ( ! $condition ) {
		$failures[] = $message;
	}
}

/**
 * Create test page.
 *
 * @param string $title  Title.
 * @param string $status Status.
 * @return int
 */
function nexa_pro_core_navigation_page( $title, $status = 'publish' ) {
	global $cleanup;

	$page_id = wp_insert_post(
		array(
			'post_title'  => $title,
			'post_type'   => 'page',
			'post_status' => $status,
		),
		true
	);

	if ( is_wp_error( $page_id ) ) {
		return 0;
	}

	$cleanup[] = absint( $page_id );

	return absint( $page_id );
}

/**
 * Component fixture.
 *
 * @param string $type   Type.
 * @param string $anchor Anchor.
 * @param int    $order  Order.
 * @return array
 */
function nexa_pro_core_navigation_component( $type, $anchor, $order ) {
	return array(
		'component_type' => $type,
		'admin_title'    => ucfirst( $type ),
		'enabled'        => '1',
		'order'          => $order,
		'layout'         => 'default',
		'content'        => array( 'heading' => ucfirst( $type ) ),
		'navigation'     => array(
			'show_in_navigation' => '1',
			'navigation_label'   => ucfirst( $type ),
			'anchor_id'          => $anchor,
			'mobile_visibility'  => 'all',
		),
	);
}

$args       = array( 'bypass_capability_check' => true );
$home_id    = nexa_pro_core_navigation_page( 'Nexa Navigation Test Home' );
$about_id   = nexa_pro_core_navigation_page( 'Nexa Navigation Test About' );
$draft_id   = nexa_pro_core_navigation_page( 'Nexa Navigation Test Draft', 'draft' );
$home_first = nexa_pro_core_add_page_component( $home_id, nexa_pro_core_navigation_component( 'about', 'about', 0 ), $args );
$home_second = nexa_pro_core_add_page_component( $home_id, nexa_pro_core_navigation_component( 'services', 'services', 1 ), $args );

nexa_pro_core_add_page_component(
	$home_id,
	array_merge(
		nexa_pro_core_navigation_component( 'cta', 'cta', 2 ),
		array(
			'enabled'    => '0',
			'navigation' => array(
				'show_in_navigation' => '1',
				'navigation_label'   => 'CTA',
				'anchor_id'          => 'cta',
			),
		)
	),
	$args
);

nexa_pro_core_add_page_component( $about_id, nexa_pro_core_navigation_component( 'team', 'team', 0 ), $args );

$sanitized = Nexa_Pro_Core\Navigation_Settings::sanitize_settings(
	array(
		'navigation_source'          => 'bad',
		'generated_primary_page_id' => $home_id,
		'generated_page_ids'        => array( $home_id, $home_id, $draft_id ),
	)
);
nexa_pro_core_navigation_assert( 'wordpress' === $sanitized['navigation_source'], 'Invalid source should fall back safely.' );
nexa_pro_core_navigation_assert( array( $home_id, $draft_id ) === $sanitized['generated_page_ids'], 'Generated page IDs should be unique and valid pages.' );

$single_tree = nexa_pro_core_get_generated_navigation(
	array(
		'navigation_source'          => 'generated',
		'generated_primary_page_id' => $home_id,
		'generated_page_ids'        => array( $home_id ),
		'include_page_sections'     => '1',
	)
);
nexa_pro_core_navigation_assert( 2 === count( $single_tree ), 'Single-page generated tree should flatten eligible components.' );
nexa_pro_core_navigation_assert( '#about' === $single_tree[0]['url'], 'Single-page component links should use same-page anchors.' );

$multipage_tree = nexa_pro_core_get_generated_navigation(
	array(
		'navigation_source'          => 'generated',
		'generated_primary_page_id' => $home_id,
		'generated_page_ids'        => array( $home_id, $draft_id, $about_id ),
		'include_page_sections'     => '1',
	)
);
nexa_pro_core_navigation_assert( 2 === count( $multipage_tree ), 'Draft pages should be excluded by default.' );
nexa_pro_core_navigation_assert( ! empty( $multipage_tree[0]['children'] ), 'Multipage generated tree should include child section links.' );
nexa_pro_core_navigation_assert( false !== strpos( $multipage_tree[0]['children'][0]['url'], '#about' ), 'Multipage child links should include page URL and anchor.' );

$updated = nexa_pro_core_update_page_component(
	$home_id,
	$home_second['instance_id'],
	array(
		'navigation' => array(
			'show_in_navigation' => '1',
			'navigation_label'   => 'Services',
			'anchor_id'          => 'services',
			'order_override'     => 0,
			'mobile_visibility'  => 'desktop',
		),
	),
	$args
);
$ordered = nexa_pro_core_get_page_navigation_items( $home_id, array( 'same_page' => true ) );
nexa_pro_core_navigation_assert( ! is_wp_error( $updated ), 'Order override update should save.' );
nexa_pro_core_navigation_assert( 'services' === $ordered[0]['anchor_id'], 'Order override should affect generated item order.' );
nexa_pro_core_navigation_assert( 'desktop' === $ordered[0]['mobile_visibility'], 'Mobile visibility should be preserved.' );

$warnings = nexa_pro_core_validate_navigation_tree(
	array(
		array( 'id' => 'one', 'url' => '#one', 'children' => array() ),
		array( 'id' => 'one', 'url' => '#one', 'children' => array() ),
	)
);
nexa_pro_core_navigation_assert( ! empty( $warnings ), 'Validation should warn on duplicate IDs and URLs.' );

foreach ( $cleanup as $page_id ) {
	wp_delete_post( $page_id, true );
}

if ( $failures ) {
	foreach ( $failures as $failure ) {
		fwrite( STDERR, "FAIL: {$failure}\n" );
	}

	exit( 1 );
}

echo "Nexa Pro Core navigation validation passed.\n";
