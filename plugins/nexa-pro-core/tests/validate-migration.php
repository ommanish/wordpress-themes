#!/usr/bin/env php
<?php
/**
 * Validate migration and compatibility behavior.
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
function nexa_pro_core_migration_assert( $condition, $message ) {
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
function nexa_pro_core_migration_create_page( $title, $slug ) {
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
 * Legacy options fixture.
 *
 * @return array
 */
function nexa_pro_core_migration_legacy_options() {
	return array(
		'homepage_section_order' => array( 'about', 'services', 'process', 'why', 'cta' ),
		'hero_eyebrow'          => 'Migration',
		'hero_heading'          => 'Legacy Hero',
		'hero_text'             => 'Legacy hero copy.',
		'hero_primary_cta_text' => 'Start',
		'hero_primary_cta_url'  => '#contact',
		'about_show'            => '1',
		'about_label'           => 'About',
		'about_heading'         => 'About Heading',
		'about_text'            => 'About text.',
		'about_image_id'        => 123,
		'services_show'         => '1',
		'services_heading'      => 'Services Heading',
		'services_text'         => 'Services text.',
		'services_items'        => array(
			array(
				'id'          => 'service-one',
				'title'       => 'Service One',
				'description' => 'Service one description.',
				'link_text'   => 'Learn more',
				'link_url'    => '#services',
			),
		),
		'process_show'          => '1',
		'process_heading'       => 'Process Heading',
		'why_show'              => '1',
		'why_heading'           => 'Why Heading',
		'why_image_id'          => 456,
		'cta_show'              => '1',
		'cta_heading'           => 'CTA Heading',
		'cta_text'              => 'CTA text.',
		'cta_button_text'       => 'Contact',
		'cta_button_url'        => '#contact',
	);
}

$args    = array( 'bypass_capability_check' => true );
$page_id = nexa_pro_core_migration_create_page( 'Migration Target', 'migration-target' );

update_option( 'page_on_front', $page_id, false );
update_option( 'nexa_pro_options', nexa_pro_core_migration_legacy_options(), false );

$detect = Nexa_Pro_Core\Migration::detect( $page_id );
nexa_pro_core_migration_assert( 'available' === $detect['status'], 'Legacy data should be detected as available for migration.' );
nexa_pro_core_migration_assert( true === $detect['legacy_present'], 'Legacy detection should report source data.' );
nexa_pro_core_migration_assert( ! $detect['has_builder_data'], 'Fresh target page should not have builder data.' );

$preview_a = Nexa_Pro_Core\Migration::preview( $page_id, 'merge' );
$preview_b = Nexa_Pro_Core\Migration::preview( $page_id, 'merge' );

nexa_pro_core_migration_assert( empty( $preview_a['errors'] ), 'Migration preview should not report errors for a valid page.' );
nexa_pro_core_migration_assert( $preview_a['migration_hash'] === $preview_b['migration_hash'], 'Migration hash should be stable for unchanged source data.' );
nexa_pro_core_migration_assert( wp_list_pluck( $preview_a['components'], 'instance_id' ) === wp_list_pluck( $preview_b['components'], 'instance_id' ), 'Migration-generated IDs should be stable across previews.' );
nexa_pro_core_migration_assert( array( 'hero', 'about', 'services', 'process', 'why', 'cta', 'features', 'portfolio', 'testimonials', 'team', 'faq', 'contact' ) === wp_list_pluck( $preview_a['components'], 'component_type' ), 'Preview should map saved order first and append missing supported sections.' );

$apply = Nexa_Pro_Core\Migration::apply( $page_id, 'merge', false, $args );
nexa_pro_core_migration_assert( ! is_wp_error( $apply ), 'Migration apply should succeed.' );
nexa_pro_core_migration_assert( 'completed' === $apply['status'], 'Migration state should be completed after apply.' );
nexa_pro_core_migration_assert( 'legacy' === Nexa_Pro_Core\Compatibility_Mode::get_mode(), 'Apply should not switch builder mode unless requested.' );
nexa_pro_core_migration_assert( nexa_pro_core_migration_legacy_options() === get_option( 'nexa_pro_options' ), 'Legacy theme options should remain preserved after migration.' );

$component_count = count( Nexa_Pro_Core\Storage::get_page_components( $page_id ) );
$apply_again     = Nexa_Pro_Core\Migration::apply( $page_id, 'merge', true, $args );
$after_count     = count( Nexa_Pro_Core\Storage::get_page_components( $page_id ) );

nexa_pro_core_migration_assert( ! is_wp_error( $apply_again ), 'Migration should be safely repeatable.' );
nexa_pro_core_migration_assert( $component_count === $after_count, 'Repeated migration apply should not duplicate migrated components.' );
nexa_pro_core_migration_assert( 'builder' === Nexa_Pro_Core\Compatibility_Mode::get_mode(), 'Explicit switch should enable builder mode.' );
nexa_pro_core_migration_assert( Nexa_Pro_Core\Compatibility_Mode::should_render_builder_page( $page_id ), 'Builder mode should allow rendering pages with builder data.' );

$rollback = Nexa_Pro_Core\Migration::rollback( $args );
nexa_pro_core_migration_assert( ! is_wp_error( $rollback ), 'Rollback should succeed.' );
nexa_pro_core_migration_assert( 'rolled_back' === $rollback['status'], 'Rollback state should be recorded.' );
nexa_pro_core_migration_assert( $component_count === count( Nexa_Pro_Core\Storage::get_page_components( $page_id ) ), 'Rollback should restore the latest pre-apply builder backup.' );
nexa_pro_core_migration_assert( 'legacy' === Nexa_Pro_Core\Compatibility_Mode::get_mode(), 'Rollback should restore the previous compatibility mode.' );

$reapply = Nexa_Pro_Core\Migration::apply( $page_id, 'replace-builder-data', true, $args );
nexa_pro_core_migration_assert( ! is_wp_error( $reapply ), 'Reapply after rollback should succeed.' );
nexa_pro_core_migration_assert( 'completed' === $reapply['status'], 'Reapply should complete migration again.' );

$changed_options                  = nexa_pro_core_migration_legacy_options();
$changed_options['hero_heading']  = 'Changed Hero';
$changed_options['trust_show']    = '1';
$changed_options['trust_items']   = array( array( 'title' => 'Unsupported trust item' ) );
update_option( 'nexa_pro_options', $changed_options, false );

$changed_preview = Nexa_Pro_Core\Migration::preview( $page_id, 'merge' );
nexa_pro_core_migration_assert( in_array( 'trust', $changed_preview['unsupported_sections'], true ), 'Unsupported legacy trust section should be reported.' );
nexa_pro_core_migration_assert( ! empty( $changed_preview['warnings'] ), 'Changed source data should produce a warning after prior migration state.' );

update_option( 'nexa_pro_options', array(), false );
$no_legacy = Nexa_Pro_Core\Migration::detect( $page_id );
nexa_pro_core_migration_assert( 'not_needed' === $no_legacy['status'], 'No legacy data should report migration as not needed.' );

if ( $failures ) {
	foreach ( $failures as $failure ) {
		fwrite( STDERR, "FAIL: {$failure}\n" );
	}

	exit( 1 );
}

echo "PASS: Nexa Pro Core migration validation completed successfully.\n";
