#!/usr/bin/env php
<?php
/**
 * Validate import/export behavior.
 *
 * @package Nexa_Pro_Core
 */

define( 'NEXA_PRO_CORE_TESTING', true );

require_once __DIR__ . '/bootstrap-standalone.php';

$failures = array();

/**
 * Assert a condition.
 *
 * @param bool   $condition Condition.
 * @param string $message   Message.
 * @return void
 */
function nexa_pro_core_transfer_assert( $condition, $message ) {
	global $failures;

	if ( ! $condition ) {
		$failures[] = $message;
	}
}

/**
 * Create a test page.
 *
 * @param string $title Title.
 * @param string $slug  Slug.
 * @return int
 */
function nexa_pro_core_transfer_create_page( $title, $slug ) {
	return absint(
		wp_insert_post(
			array(
				'post_title'  => $title,
				'post_name'   => $slug,
				'post_type'   => 'page',
				'post_status' => 'publish',
			),
			true
		)
	);
}

/**
 * Component fixture.
 *
 * @param string $id     Instance ID.
 * @param string $type   Type.
 * @param string $anchor Anchor.
 * @return array
 */
function nexa_pro_core_transfer_component( $id, $type, $anchor ) {
	return array(
		'instance_id'    => $id,
		'component_type' => $type,
		'admin_title'    => ucfirst( $type ),
		'enabled'        => '1',
		'layout'         => 'default',
		'content'        => array(
			'heading' => ucfirst( $type ) . ' Heading',
			'body'    => ucfirst( $type ) . ' body.',
		),
		'design'         => array(
			'background_type' => 'solid',
			'text_theme'      => 'automatic',
		),
		'navigation'     => array(
			'show_in_navigation' => '1',
			'navigation_label'   => ucfirst( $type ),
			'anchor_id'          => $anchor,
			'mobile_visibility'  => 'all',
		),
		'advanced'       => array(
			'semantic_element'  => 'section',
			'device_visibility' => 'all',
			'animation_preset'  => 'none',
		),
	);
}

$args        = array( 'bypass_capability_check' => true );
$source_page = nexa_pro_core_transfer_create_page( 'Source Page', 'source-page' );
$target_page = nexa_pro_core_transfer_create_page( 'Target Page', 'target-page' );

update_option( 'nexa_pro_options', array( 'brand_name' => 'Transfer Brand', 'primary_color' => '#2563eb' ), false );
Nexa_Pro_Core\Navigation_Settings::save_settings( array( 'navigation_source' => 'generated', 'generated_primary_page_id' => $source_page ) );

$saved = Nexa_Pro_Core\Storage::save_page_components(
	$source_page,
	array(
		nexa_pro_core_transfer_component( 'nexa_services_export01', 'services', 'services' ),
		nexa_pro_core_transfer_component( 'nexa_cta_export00001', 'cta', 'cta' ),
	),
	$args
);
nexa_pro_core_transfer_assert( ! is_wp_error( $saved ), 'Source components should save before export.' );

$export = Nexa_Pro_Core\Transfer::build_export( 'one-page', array( 'page_id' => $source_page ) );
nexa_pro_core_transfer_assert( 'nexa-pro' === $export['product'], 'Export product should be nexa-pro.' );
nexa_pro_core_transfer_assert( 2 === absint( $export['export_schema'] ), 'Export schema should be version 2.' );
nexa_pro_core_transfer_assert( 'one-page' === $export['scope'], 'Export scope should be one-page.' );
nexa_pro_core_transfer_assert( 1 === count( $export['pages'] ), 'One-page export should include one page record.' );
nexa_pro_core_transfer_assert( 2 === count( $export['pages'][0]['components'] ), 'Exported page should include components.' );

$preview = Nexa_Pro_Core\Transfer::preview_import( $export );
nexa_pro_core_transfer_assert( ! is_wp_error( $preview ), 'Import preview should accept a valid export.' );
nexa_pro_core_transfer_assert( ! empty( $preview['token'] ), 'Import preview should create a token.' );
nexa_pro_core_transfer_assert( 0 === count( Nexa_Pro_Core\Storage::get_page_components( $target_page ) ), 'Preview should not apply imported page data.' );

$applied = Nexa_Pro_Core\Transfer::apply_import( $export, 'replace', array( $source_page => $target_page ), $args );
nexa_pro_core_transfer_assert( ! is_wp_error( $applied ), 'Mapped replace import should apply.' );
nexa_pro_core_transfer_assert( 1 === absint( $applied['pages_imported'] ), 'Mapped replace import should report imported page count.' );
nexa_pro_core_transfer_assert( 2 === count( Nexa_Pro_Core\Storage::get_page_components( $target_page ) ), 'Mapped target page should receive imported components.' );

$existing = Nexa_Pro_Core\Storage::save_page_components(
	$target_page,
	array( nexa_pro_core_transfer_component( 'nexa_about_existing01', 'about', 'services' ) ),
	$args
);
nexa_pro_core_transfer_assert( ! is_wp_error( $existing ), 'Existing target component should save for merge conflict test.' );

$merged = Nexa_Pro_Core\Transfer::apply_import( $export, 'merge', array( $source_page => $target_page ), $args );
nexa_pro_core_transfer_assert( ! is_wp_error( $merged ), 'Merge import should repair duplicate anchors instead of failing.' );
$merged_components = Nexa_Pro_Core\Storage::get_page_components( $target_page );
$merged_anchors    = wp_list_pluck( wp_list_pluck( $merged_components, 'navigation' ), 'anchor_id' );
nexa_pro_core_transfer_assert( count( $merged_anchors ) === count( array_unique( $merged_anchors ) ), 'Merged import should leave unique anchors.' );

$same_slug_before = count( $GLOBALS['nexa_pro_core_test_posts'] );
$created          = Nexa_Pro_Core\Transfer::apply_import( $export, 'create-new', array(), $args );
$same_slug_after  = count( $GLOBALS['nexa_pro_core_test_posts'] );
nexa_pro_core_transfer_assert( ! is_wp_error( $created ), 'Create-new import should apply without explicit mapping.' );
nexa_pro_core_transfer_assert( $same_slug_after > $same_slug_before, 'Create-new mode should create a draft page instead of silently mapping by slug.' );

$old_schema = array(
	'product'        => 'nexa-pro',
	'schema_version' => 1,
	'settings'       => array(
		'brand_name'    => 'Old Export Brand',
		'primary_color' => '#0f766e',
	),
);
$old_preview = Nexa_Pro_Core\Transfer::preview_import( $old_schema );
nexa_pro_core_transfer_assert( ! is_wp_error( $old_preview ), 'Old schema settings export should preview.' );
nexa_pro_core_transfer_assert( ! empty( $old_preview['preview']['warnings'] ), 'Old schema preview should report limitations.' );

$bad_json = Nexa_Pro_Core\Transfer::preview_import( '{bad json' );
nexa_pro_core_transfer_assert( is_wp_error( $bad_json ), 'Malformed JSON should be rejected.' );

$wrong_product = Nexa_Pro_Core\Transfer::preview_import( array( 'product' => 'other-product', 'export_schema' => 2 ) );
nexa_pro_core_transfer_assert( is_wp_error( $wrong_product ), 'Wrong product should be rejected.' );

$unsupported = Nexa_Pro_Core\Transfer::preview_import( array( 'product' => 'nexa-pro', 'export_schema' => 999 ) );
nexa_pro_core_transfer_assert( is_wp_error( $unsupported ), 'Unsupported schema should be rejected.' );

$large = Nexa_Pro_Core\Transfer::preview_import( str_repeat( 'x', Nexa_Pro_Core\Transfer::MAX_JSON_BYTES + 1 ) );
nexa_pro_core_transfer_assert( is_wp_error( $large ), 'Oversized import JSON should be rejected.' );

$too_many = array(
	'product'       => 'nexa-pro',
	'export_schema' => 2,
	'pages'         => array_fill( 0, Nexa_Pro_Core\Transfer::MAX_RECORDS + 1, array( 'title' => 'Too many' ) ),
);
$too_many_preview = Nexa_Pro_Core\Transfer::preview_import( $too_many );
nexa_pro_core_transfer_assert( is_wp_error( $too_many_preview ), 'Record count limit should be enforced.' );

if ( $failures ) {
	foreach ( $failures as $failure ) {
		fwrite( STDERR, "FAIL: {$failure}\n" );
	}

	exit( 1 );
}

echo "PASS: Nexa Pro Core transfer validation completed successfully.\n";
