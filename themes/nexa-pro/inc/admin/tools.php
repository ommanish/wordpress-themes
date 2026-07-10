<?php
/**
 * Admin tools for settings export, import, presets, reset, and rollback.
 *
 * @package Nexa_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the settings export product identifier.
 *
 * @return string
 */
function nexa_pro_get_settings_product_id() {
	return 'nexa-pro';
}

/**
 * Get the current settings schema version.
 *
 * @return int
 */
function nexa_pro_get_settings_schema_version() {
	return 1;
}

/**
 * Get supported settings schema versions.
 *
 * @return array
 */
function nexa_pro_get_supported_settings_schema_versions() {
	return array( 1 );
}

/**
 * Get the Tools tab URL.
 *
 * @param array $args Optional query args.
 * @return string
 */
function nexa_pro_get_tools_url( $args = array() ) {
	return add_query_arg(
		array_merge(
			array(
				'page' => 'nexa-pro',
				'tab'  => 'tools',
			),
			$args
		),
		admin_url( 'themes.php' )
	);
}

/**
 * Redirect to the Tools tab with a status code.
 *
 * @param string $notice Notice code.
 * @param array  $args Additional query args.
 * @return void
 */
function nexa_pro_tools_redirect( $notice, $args = array() ) {
	wp_safe_redirect(
		nexa_pro_get_tools_url(
			array_merge(
				array(
					'nexa_pro_tools_notice' => sanitize_key( $notice ),
				),
				$args
			)
		)
	);
	exit;
}

/**
 * Require the capability used by all Tools actions.
 *
 * @return void
 */
function nexa_pro_tools_require_capability() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to manage Nexa Pro tools.', 'nexa-pro' ) );
	}
}

/**
 * Delete an uploaded temporary file when it is safe to do so.
 *
 * @param array $file Uploaded file data.
 * @return void
 */
function nexa_pro_delete_import_temp_file( $file ) {
	if ( empty( $file['tmp_name'] ) || ! is_string( $file['tmp_name'] ) ) {
		return;
	}

	if ( is_uploaded_file( $file['tmp_name'] ) ) {
		wp_delete_file( $file['tmp_name'] );
	}
}

/**
 * Get normalized settings that are safe to export.
 *
 * @return array
 */
function nexa_pro_get_exportable_settings() {
	$report = null;

	return nexa_pro_normalize_full_settings( nexa_pro_get_options(), $report );
}

/**
 * Build a settings export document.
 *
 * @return array
 */
function nexa_pro_get_settings_export_document() {
	return array(
		'product'        => nexa_pro_get_settings_product_id(),
		'schema_version' => nexa_pro_get_settings_schema_version(),
		'theme_version'  => NEXA_PRO_VERSION,
		'exported_at'    => gmdate( 'c' ),
		'settings'       => nexa_pro_get_exportable_settings(),
	);
}

/**
 * Get a safe export filename.
 *
 * @return string
 */
function nexa_pro_get_settings_export_filename() {
	return 'nexa-pro-settings-' . gmdate( 'Y-m-d' ) . '.json';
}

/**
 * Handle settings export download.
 *
 * @return void
 */
function nexa_pro_handle_export_settings() {
	nexa_pro_tools_require_capability();
	check_admin_referer( 'nexa_pro_export_settings' );

	$json = wp_json_encode( nexa_pro_get_settings_export_document(), JSON_PRETTY_PRINT );

	if ( false === $json ) {
		nexa_pro_tools_redirect( 'export-failed' );
	}

	while ( ob_get_level() ) {
		ob_end_clean();
	}

	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . nexa_pro_get_settings_export_filename() . '"' );
	header( 'Content-Length: ' . strlen( $json ) );

	echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit;
}
add_action( 'admin_post_nexa_pro_export_settings', 'nexa_pro_handle_export_settings' );

/**
 * Determine whether an array is list-like.
 *
 * @param array $array Array to check.
 * @return bool
 */
function nexa_pro_array_is_list( $array ) {
	if ( array() === $array ) {
		return true;
	}

	return array_keys( $array ) === range( 0, count( $array ) - 1 );
}

/**
 * Get the settings group for a known option key.
 *
 * @param string $key Option key.
 * @return string
 */
function nexa_pro_get_settings_group_for_key( $key ) {
	if ( in_array( $key, nexa_pro_get_global_design_option_keys(), true ) || in_array( $key, array( 'primary_color', 'accent_color', 'brand_name', 'brand_tagline' ), true ) ) {
		return __( 'Global', 'nexa-pro' );
	}

	if ( 0 === strpos( $key, 'header_' ) || in_array( $key, array( 'logo_attachment_id', 'mobile_logo_attachment_id', 'display_brand_text', 'sticky_header', 'transparent_header' ), true ) ) {
		return __( 'Header', 'nexa-pro' );
	}

	if ( 0 === strpos( $key, 'footer_' ) || 0 === strpos( $key, 'contact_' ) || 0 === strpos( $key, 'social_' ) || 0 === strpos( $key, 'schedule_' ) ) {
		return __( 'Footer and contact', 'nexa-pro' );
	}

	if ( 0 === strpos( $key, 'hero_' ) ) {
		return __( 'Hero', 'nexa-pro' );
	}

	if ( 'homepage_section_order' === $key ) {
		return __( 'Homepage order', 'nexa-pro' );
	}

	return __( 'Homepage sections', 'nexa-pro' );
}

/**
 * Summarize differences between current and new settings.
 *
 * @param array $new_settings New normalized settings.
 * @param array $current_settings Current normalized settings.
 * @param int   $recognized_count Recognized key count.
 * @param int   $unknown_count Unknown key count.
 * @param array $normalization_report Normalization report.
 * @return array
 */
function nexa_pro_get_settings_change_summary( $new_settings, $current_settings, $recognized_count, $unknown_count, $normalization_report ) {
	$changed_keys = array();
	$groups       = array();

	foreach ( nexa_pro_get_default_options() as $key => $default_value ) {
		$new_value     = array_key_exists( $key, $new_settings ) ? $new_settings[ $key ] : $default_value;
		$current_value = array_key_exists( $key, $current_settings ) ? $current_settings[ $key ] : $default_value;

		if ( $new_value !== $current_value ) {
			$changed_keys[] = $key;
			$groups[ nexa_pro_get_settings_group_for_key( $key ) ] = true;
		}
	}

	return array(
		'recognized_keys'  => absint( $recognized_count ),
		'unknown_keys'     => absint( $unknown_count ),
		'changed_values'   => count( $changed_keys ),
		'changed_keys'     => $changed_keys,
		'groups'           => array_keys( $groups ),
		'media_references' => isset( $normalization_report['media_references'] ) ? absint( $normalization_report['media_references'] ) : 0,
		'media_cleared'    => isset( $normalization_report['media_cleared'] ) ? absint( $normalization_report['media_cleared'] ) : 0,
	);
}

/**
 * Validate an imported settings document.
 *
 * @param mixed $document Decoded JSON document.
 * @return array|WP_Error
 */
function nexa_pro_validate_import_document( $document ) {
	if ( ! is_array( $document ) || nexa_pro_array_is_list( $document ) ) {
		return new WP_Error( 'malformed-document', __( 'The import file is not a valid Nexa Pro settings document.', 'nexa-pro' ) );
	}

	if ( ! isset( $document['product'] ) || nexa_pro_get_settings_product_id() !== (string) $document['product'] ) {
		return new WP_Error( 'wrong-product', __( 'The import file is for a different product.', 'nexa-pro' ) );
	}

	$schema_version = isset( $document['schema_version'] ) ? absint( $document['schema_version'] ) : 0;

	if ( ! in_array( $schema_version, nexa_pro_get_supported_settings_schema_versions(), true ) ) {
		return new WP_Error( 'unsupported-schema', __( 'The import file uses an unsupported settings schema version.', 'nexa-pro' ) );
	}

	if ( ! isset( $document['settings'] ) || ! is_array( $document['settings'] ) ) {
		return new WP_Error( 'missing-settings', __( 'The import file does not contain a settings object.', 'nexa-pro' ) );
	}

	$defaults        = nexa_pro_get_default_options();
	$raw_settings    = $document['settings'];
	$known_settings  = array_intersect_key( $raw_settings, $defaults );
	$recognized_keys = count( $known_settings );
	$unknown_keys    = count( array_diff_key( $raw_settings, $defaults ) );

	if ( 0 === $recognized_keys ) {
		return new WP_Error( 'no-recognized-settings', __( 'The import file does not contain any recognized Nexa Pro settings.', 'nexa-pro' ) );
	}

	$normalization_report = null;
	$normalized_settings  = nexa_pro_normalize_full_settings( $raw_settings, $normalization_report );
	$current_settings     = nexa_pro_get_exportable_settings();
	$summary              = nexa_pro_get_settings_change_summary(
		$normalized_settings,
		$current_settings,
		$recognized_keys,
		$unknown_keys,
		$normalization_report
	);

	return array(
		'schema_version'  => $schema_version,
		'theme_version'   => isset( $document['theme_version'] ) && is_scalar( $document['theme_version'] ) ? sanitize_text_field( $document['theme_version'] ) : '',
		'exported_at'     => isset( $document['exported_at'] ) && is_scalar( $document['exported_at'] ) ? sanitize_text_field( $document['exported_at'] ) : '',
		'settings'        => $normalized_settings,
		'summary'         => $summary,
	);
}

/**
 * Create an import preview transient.
 *
 * @param array $preview Preview data.
 * @return string
 */
function nexa_pro_create_import_preview( $preview ) {
	$token = sanitize_key( wp_generate_password( 24, false, false ) );
	$key   = nexa_pro_get_import_preview_transient_key( $token );

	set_transient( $key, $preview, 30 * MINUTE_IN_SECONDS );

	return $token;
}

/**
 * Get an import preview transient key.
 *
 * @param string $token Preview token.
 * @return string
 */
function nexa_pro_get_import_preview_transient_key( $token ) {
	return 'nexa_pro_import_' . get_current_user_id() . '_' . sanitize_key( $token );
}

/**
 * Get an import preview by token.
 *
 * @param string $token Preview token.
 * @return array|null
 */
function nexa_pro_get_import_preview( $token ) {
	$preview = get_transient( nexa_pro_get_import_preview_transient_key( $token ) );

	return is_array( $preview ) ? $preview : null;
}

/**
 * Delete an import preview by token.
 *
 * @param string $token Preview token.
 * @return void
 */
function nexa_pro_delete_import_preview( $token ) {
	delete_transient( nexa_pro_get_import_preview_transient_key( $token ) );
}

/**
 * Handle import upload and preview creation.
 *
 * @return void
 */
function nexa_pro_handle_import_settings_preview() {
	nexa_pro_tools_require_capability();
	check_admin_referer( 'nexa_pro_import_settings_preview' );

	if ( empty( $_FILES['nexa_pro_import_file'] ) || ! is_array( $_FILES['nexa_pro_import_file'] ) ) {
		nexa_pro_tools_redirect( 'upload-error' );
	}

	$file = $_FILES['nexa_pro_import_file']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	if ( ! empty( $file['error'] ) ) {
		nexa_pro_delete_import_temp_file( $file );
		nexa_pro_tools_redirect( 'upload-error' );
	}

	$size = isset( $file['size'] ) ? absint( $file['size'] ) : 0;

	if ( ! $size || $size > 1024 * 1024 ) {
		nexa_pro_delete_import_temp_file( $file );
		nexa_pro_tools_redirect( 'upload-too-large' );
	}

	$name      = isset( $file['name'] ) ? sanitize_file_name( wp_unslash( $file['name'] ) ) : '';
	$tmp_name  = isset( $file['tmp_name'] ) ? $file['tmp_name'] : '';
	$extension = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );

	if ( 'json' !== $extension || ! is_uploaded_file( $tmp_name ) ) {
		nexa_pro_delete_import_temp_file( $file );
		nexa_pro_tools_redirect( 'invalid-json-file' );
	}

	$json = file_get_contents( $tmp_name ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	nexa_pro_delete_import_temp_file( $file );

	if ( false === $json || '' === trim( $json ) ) {
		nexa_pro_tools_redirect( 'malformed-json' );
	}

	$document = json_decode( $json, true );

	if ( JSON_ERROR_NONE !== json_last_error() ) {
		nexa_pro_tools_redirect( 'malformed-json' );
	}

	$preview = nexa_pro_validate_import_document( $document );

	if ( is_wp_error( $preview ) ) {
		nexa_pro_tools_redirect( $preview->get_error_code() );
	}

	$token = nexa_pro_create_import_preview( $preview );

	nexa_pro_tools_redirect(
		'import-preview-ready',
		array(
			'nexa_pro_import_preview' => $token,
		)
	);
}
add_action( 'admin_post_nexa_pro_import_settings_preview', 'nexa_pro_handle_import_settings_preview' );

/**
 * Create a one-snapshot rollback point.
 *
 * @param string $action Action type.
 * @return void
 */
function nexa_pro_create_rollback_snapshot( $action ) {
	update_option(
		'nexa_pro_rollback_snapshot',
		array(
			'product'        => nexa_pro_get_settings_product_id(),
			'schema_version' => nexa_pro_get_settings_schema_version(),
			'action'         => sanitize_key( $action ),
			'created_at'     => time(),
			'settings'       => nexa_pro_get_exportable_settings(),
		),
		false
	);
}

/**
 * Get a validated rollback snapshot.
 *
 * @return array|null
 */
function nexa_pro_get_rollback_snapshot() {
	$snapshot = get_option( 'nexa_pro_rollback_snapshot', array() );

	if ( ! is_array( $snapshot ) || empty( $snapshot['settings'] ) || ! is_array( $snapshot['settings'] ) ) {
		return null;
	}

	if ( empty( $snapshot['product'] ) || nexa_pro_get_settings_product_id() !== $snapshot['product'] ) {
		return null;
	}

	$schema_version = isset( $snapshot['schema_version'] ) ? absint( $snapshot['schema_version'] ) : 0;

	if ( ! in_array( $schema_version, nexa_pro_get_supported_settings_schema_versions(), true ) ) {
		return null;
	}

	$allowed_actions = array( 'import', 'preset', 'reset' );
	$action          = isset( $snapshot['action'] ) ? sanitize_key( $snapshot['action'] ) : '';

	if ( ! in_array( $action, $allowed_actions, true ) ) {
		return null;
	}

	return array(
		'action'     => $action,
		'created_at' => isset( $snapshot['created_at'] ) ? absint( $snapshot['created_at'] ) : 0,
		'settings'   => $snapshot['settings'],
	);
}

/**
 * Apply a full settings payload after creating a rollback snapshot.
 *
 * @param array  $settings Settings payload.
 * @param string $action Action type.
 * @return array
 */
function nexa_pro_apply_settings_payload( $settings, $action ) {
	$report = null;
	$settings = nexa_pro_normalize_full_settings( $settings, $report );

	nexa_pro_create_rollback_snapshot( $action );
	update_option( 'nexa_pro_options', $settings );

	return array(
		'settings' => $settings,
		'report'   => $report,
	);
}

/**
 * Handle confirmed import application.
 *
 * @return void
 */
function nexa_pro_handle_confirm_import_settings() {
	nexa_pro_tools_require_capability();
	check_admin_referer( 'nexa_pro_confirm_import_settings' );

	$token = isset( $_POST['nexa_pro_import_token'] ) ? sanitize_key( wp_unslash( $_POST['nexa_pro_import_token'] ) ) : '';
	$confirmed = isset( $_POST['nexa_pro_confirm_import'] ) && '1' === (string) wp_unslash( $_POST['nexa_pro_confirm_import'] );

	if ( '' === $token ) {
		nexa_pro_tools_redirect( 'preview-expired' );
	}

	if ( ! $confirmed ) {
		nexa_pro_tools_redirect(
			'confirmation-required',
			array(
				'nexa_pro_import_preview' => $token,
			)
		);
	}

	$preview = nexa_pro_get_import_preview( $token );

	if ( ! $preview ) {
		nexa_pro_tools_redirect( 'preview-expired' );
	}

	nexa_pro_apply_settings_payload( $preview['settings'], 'import' );
	nexa_pro_delete_import_preview( $token );

	$summary = isset( $preview['summary'] ) && is_array( $preview['summary'] ) ? $preview['summary'] : array();

	nexa_pro_tools_redirect(
		'import-applied',
		array(
			'changed'       => isset( $summary['changed_values'] ) ? absint( $summary['changed_values'] ) : 0,
			'unknown'       => isset( $summary['unknown_keys'] ) ? absint( $summary['unknown_keys'] ) : 0,
			'media_cleared' => isset( $summary['media_cleared'] ) ? absint( $summary['media_cleared'] ) : 0,
		)
	);
}
add_action( 'admin_post_nexa_pro_confirm_import_settings', 'nexa_pro_handle_confirm_import_settings' );

/**
 * Build a full preset payload while preserving site-specific values.
 *
 * @param string $preset_id Preset ID.
 * @return array|WP_Error
 */
function nexa_pro_build_preset_payload( $preset_id ) {
	$preset = nexa_pro_get_preset( $preset_id );

	if ( ! $preset ) {
		return new WP_Error( 'invalid-preset', __( 'The selected preset is not available.', 'nexa-pro' ) );
	}

	$current = nexa_pro_get_exportable_settings();
	$payload = array_merge( nexa_pro_get_default_options(), $preset['settings'] );

	foreach ( nexa_pro_get_preset_preserved_option_keys() as $key ) {
		if ( array_key_exists( $key, $current ) ) {
			$payload[ $key ] = $current[ $key ];
		}
	}

	return $payload;
}

/**
 * Handle preset preview request.
 *
 * @return void
 */
function nexa_pro_handle_preview_preset() {
	nexa_pro_tools_require_capability();
	check_admin_referer( 'nexa_pro_preview_preset' );

	$preset_id = isset( $_POST['nexa_pro_preset_id'] ) ? sanitize_key( wp_unslash( $_POST['nexa_pro_preset_id'] ) ) : '';

	if ( ! nexa_pro_get_preset( $preset_id ) ) {
		nexa_pro_tools_redirect( 'invalid-preset' );
	}

	nexa_pro_tools_redirect(
		'preset-preview-ready',
		array(
			'nexa_pro_preset_preview' => $preset_id,
		)
	);
}
add_action( 'admin_post_nexa_pro_preview_preset', 'nexa_pro_handle_preview_preset' );

/**
 * Handle confirmed preset application.
 *
 * @return void
 */
function nexa_pro_handle_confirm_preset() {
	nexa_pro_tools_require_capability();
	check_admin_referer( 'nexa_pro_confirm_preset' );

	$preset_id = isset( $_POST['nexa_pro_preset_id'] ) ? sanitize_key( wp_unslash( $_POST['nexa_pro_preset_id'] ) ) : '';
	$confirmed = isset( $_POST['nexa_pro_confirm_preset'] ) && '1' === (string) wp_unslash( $_POST['nexa_pro_confirm_preset'] );
	$payload   = nexa_pro_build_preset_payload( $preset_id );

	if ( is_wp_error( $payload ) ) {
		nexa_pro_tools_redirect( $payload->get_error_code() );
	}

	if ( ! $confirmed ) {
		nexa_pro_tools_redirect( 'confirmation-required', array( 'nexa_pro_preset_preview' => $preset_id ) );
	}

	$current = nexa_pro_get_exportable_settings();
	$report  = null;
	$settings = nexa_pro_normalize_full_settings( $payload, $report );
	$summary  = nexa_pro_get_settings_change_summary( $settings, $current, count( array_intersect_key( $payload, nexa_pro_get_default_options() ) ), 0, $report );

	nexa_pro_apply_settings_payload( $payload, 'preset' );

	nexa_pro_tools_redirect(
		'preset-applied',
		array(
			'changed' => absint( $summary['changed_values'] ),
		)
	);
}
add_action( 'admin_post_nexa_pro_confirm_preset', 'nexa_pro_handle_confirm_preset' );

/**
 * Handle reset to defaults.
 *
 * @return void
 */
function nexa_pro_handle_reset_settings() {
	nexa_pro_tools_require_capability();
	check_admin_referer( 'nexa_pro_reset_settings' );

	$confirmed = isset( $_POST['nexa_pro_confirm_reset'] ) && '1' === (string) wp_unslash( $_POST['nexa_pro_confirm_reset'] );

	if ( ! $confirmed ) {
		nexa_pro_tools_redirect( 'confirmation-required' );
	}

	nexa_pro_apply_settings_payload( nexa_pro_get_default_options(), 'reset' );
	nexa_pro_tools_redirect( 'reset-complete' );
}
add_action( 'admin_post_nexa_pro_reset_settings', 'nexa_pro_handle_reset_settings' );

/**
 * Handle rollback restore.
 *
 * @return void
 */
function nexa_pro_handle_rollback_settings() {
	nexa_pro_tools_require_capability();
	check_admin_referer( 'nexa_pro_rollback_settings' );

	$confirmed = isset( $_POST['nexa_pro_confirm_rollback'] ) && '1' === (string) wp_unslash( $_POST['nexa_pro_confirm_rollback'] );

	if ( ! $confirmed ) {
		nexa_pro_tools_redirect( 'confirmation-required' );
	}

	$snapshot = nexa_pro_get_rollback_snapshot();

	if ( ! $snapshot ) {
		nexa_pro_tools_redirect( 'rollback-unavailable' );
	}

	$report   = null;
	$settings = nexa_pro_normalize_full_settings( $snapshot['settings'], $report );

	update_option( 'nexa_pro_options', $settings );
	delete_option( 'nexa_pro_rollback_snapshot' );

	nexa_pro_tools_redirect( 'rollback-complete' );
}
add_action( 'admin_post_nexa_pro_rollback_settings', 'nexa_pro_handle_rollback_settings' );

/**
 * Render a tool notice.
 *
 * @return void
 */
function nexa_pro_render_tools_notices() {
	$notice = isset( $_GET['nexa_pro_tools_notice'] ) ? sanitize_key( wp_unslash( $_GET['nexa_pro_tools_notice'] ) ) : '';

	if ( '' === $notice ) {
		return;
	}

	$changed       = isset( $_GET['changed'] ) ? absint( $_GET['changed'] ) : 0;
	$unknown       = isset( $_GET['unknown'] ) ? absint( $_GET['unknown'] ) : 0;
	$media_cleared = isset( $_GET['media_cleared'] ) ? absint( $_GET['media_cleared'] ) : 0;
	$type          = 'notice-error';
	$message       = '';

	switch ( $notice ) {
		case 'import-preview-ready':
			$type    = 'notice-info';
			$message = __( 'Import file validated. Review the preview before applying settings.', 'nexa-pro' );
			break;
		case 'preset-preview-ready':
			$type    = 'notice-info';
			$message = __( 'Preset preview ready. Review the changes before applying it.', 'nexa-pro' );
			break;
		case 'import-applied':
			$type    = 'notice-success';
			$message = sprintf(
				/* translators: 1: Changed values. 2: Unknown keys. 3: Media references cleared. */
				__( 'Import applied. Changed values: %1$d. Unknown keys discarded: %2$d. Media references cleared: %3$d.', 'nexa-pro' ),
				$changed,
				$unknown,
				$media_cleared
			);
			break;
		case 'preset-applied':
			$type    = 'notice-success';
			$message = sprintf(
				/* translators: %d: Changed values. */
				__( 'Preset applied. Changed values: %d.', 'nexa-pro' ),
				$changed
			);
			break;
		case 'reset-complete':
			$type    = 'notice-success';
			$message = __( 'Nexa Pro settings reset to defaults. A rollback snapshot is available.', 'nexa-pro' );
			break;
		case 'rollback-complete':
			$type    = 'notice-success';
			$message = __( 'Rollback restored the previous Nexa Pro settings snapshot.', 'nexa-pro' );
			break;
		case 'export-failed':
			$message = __( 'The settings export could not be generated.', 'nexa-pro' );
			break;
		case 'upload-too-large':
			$message = __( 'The import file is too large. Use a JSON file smaller than 1 MB.', 'nexa-pro' );
			break;
		case 'invalid-json-file':
			$message = __( 'Please upload a JSON settings file.', 'nexa-pro' );
			break;
		case 'malformed-json':
			$message = __( 'The uploaded file does not contain valid JSON.', 'nexa-pro' );
			break;
		case 'wrong-product':
			$message = __( 'The uploaded file is not a Nexa Pro settings export.', 'nexa-pro' );
			break;
		case 'unsupported-schema':
			$message = __( 'The uploaded settings schema is not supported by this version of Nexa Pro.', 'nexa-pro' );
			break;
		case 'no-recognized-settings':
			$message = __( 'The import file did not contain recognized Nexa Pro settings.', 'nexa-pro' );
			break;
		case 'preview-expired':
			$message = __( 'The import preview expired. Upload the JSON file again.', 'nexa-pro' );
			break;
		case 'invalid-preset':
			$message = __( 'The selected starter preset is not available.', 'nexa-pro' );
			break;
		case 'confirmation-required':
			$message = __( 'Please confirm the action before continuing.', 'nexa-pro' );
			break;
		case 'rollback-unavailable':
			$message = __( 'No valid rollback snapshot is available.', 'nexa-pro' );
			break;
		default:
			$message = __( 'The requested tools action could not be completed.', 'nexa-pro' );
			break;
	}

	?>
	<div class="notice <?php echo esc_attr( $type ); ?>">
		<p><?php echo esc_html( $message ); ?></p>
	</div>
	<?php
}

/**
 * Render a summary list.
 *
 * @param array $summary Summary data.
 * @return void
 */
function nexa_pro_render_tools_summary( $summary ) {
	$groups           = ! empty( $summary['groups'] ) && is_array( $summary['groups'] ) ? $summary['groups'] : array();
	$recognized_keys  = isset( $summary['recognized_keys'] ) ? absint( $summary['recognized_keys'] ) : 0;
	$unknown_keys     = isset( $summary['unknown_keys'] ) ? absint( $summary['unknown_keys'] ) : 0;
	$changed_values   = isset( $summary['changed_values'] ) ? absint( $summary['changed_values'] ) : 0;
	$media_references = isset( $summary['media_references'] ) ? absint( $summary['media_references'] ) : 0;
	$media_cleared    = isset( $summary['media_cleared'] ) ? absint( $summary['media_cleared'] ) : 0;

	?>
	<ul class="nexa-pro-tools-summary">
		<li><span><?php esc_html_e( 'Recognized keys', 'nexa-pro' ); ?></span><strong><?php echo esc_html( $recognized_keys ); ?></strong></li>
		<li><span><?php esc_html_e( 'Unknown keys discarded', 'nexa-pro' ); ?></span><strong><?php echo esc_html( $unknown_keys ); ?></strong></li>
		<li><span><?php esc_html_e( 'Changed values', 'nexa-pro' ); ?></span><strong><?php echo esc_html( $changed_values ); ?></strong></li>
		<li><span><?php esc_html_e( 'Media references detected', 'nexa-pro' ); ?></span><strong><?php echo esc_html( $media_references ); ?></strong></li>
		<li><span><?php esc_html_e( 'Media references cleared', 'nexa-pro' ); ?></span><strong><?php echo esc_html( $media_cleared ); ?></strong></li>
		<li>
			<span><?php esc_html_e( 'Groups affected', 'nexa-pro' ); ?></span>
			<strong><?php echo esc_html( $groups ? implode( ', ', $groups ) : __( 'No settings will change', 'nexa-pro' ) ); ?></strong>
		</li>
	</ul>
	<?php
}

/**
 * Render an import preview panel.
 *
 * @return void
 */
function nexa_pro_render_import_preview_panel() {
	$token = isset( $_GET['nexa_pro_import_preview'] ) ? sanitize_key( wp_unslash( $_GET['nexa_pro_import_preview'] ) ) : '';

	if ( '' === $token ) {
		return;
	}

	$preview = nexa_pro_get_import_preview( $token );

	if ( ! $preview ) {
		?>
		<div class="notice notice-error">
			<p><?php esc_html_e( 'The import preview is no longer available. Upload the JSON file again.', 'nexa-pro' ); ?></p>
		</div>
		<?php
		return;
	}

	?>
	<section class="nexa-pro-tools__panel nexa-pro-tools__panel--warning">
		<h2><?php esc_html_e( 'Import Preview', 'nexa-pro' ); ?></h2>
		<p><?php esc_html_e( 'This import will replace known Nexa Pro settings with the normalized file values. Missing known keys fall back to the current theme defaults.', 'nexa-pro' ); ?></p>
		<ul class="nexa-pro-tools-meta">
			<li><strong><?php esc_html_e( 'Schema version:', 'nexa-pro' ); ?></strong> <?php echo esc_html( absint( $preview['schema_version'] ) ); ?></li>
			<li><strong><?php esc_html_e( 'Source theme version:', 'nexa-pro' ); ?></strong> <?php echo esc_html( $preview['theme_version'] ? $preview['theme_version'] : __( 'Not provided', 'nexa-pro' ) ); ?></li>
			<li><strong><?php esc_html_e( 'Export date:', 'nexa-pro' ); ?></strong> <?php echo esc_html( $preview['exported_at'] ? $preview['exported_at'] : __( 'Not provided', 'nexa-pro' ) ); ?></li>
		</ul>
		<?php nexa_pro_render_tools_summary( $preview['summary'] ); ?>
		<?php if ( ! empty( $preview['summary']['unknown_keys'] ) || ! empty( $preview['summary']['media_cleared'] ) ) : ?>
			<div class="nexa-pro-tools-warning">
				<?php esc_html_e( 'Warnings: unknown keys will be discarded, and media attachment IDs that do not resolve to local images will be cleared.', 'nexa-pro' ); ?>
			</div>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'nexa_pro_confirm_import_settings' ); ?>
			<input type="hidden" name="action" value="nexa_pro_confirm_import_settings">
			<input type="hidden" name="nexa_pro_import_token" value="<?php echo esc_attr( $token ); ?>">
			<label class="nexa-pro-tools-confirm">
				<input type="checkbox" name="nexa_pro_confirm_import" value="1">
				<?php esc_html_e( 'I understand this import will replace known Nexa Pro settings after validation.', 'nexa-pro' ); ?>
			</label>
			<?php submit_button( __( 'Apply Imported Settings', 'nexa-pro' ), 'primary', 'submit', false ); ?>
		</form>
	</section>
	<?php
}

/**
 * Render a preset preview panel.
 *
 * @return void
 */
function nexa_pro_render_preset_preview_panel() {
	$preset_id = isset( $_GET['nexa_pro_preset_preview'] ) ? sanitize_key( wp_unslash( $_GET['nexa_pro_preset_preview'] ) ) : '';
	$preset    = $preset_id ? nexa_pro_get_preset( $preset_id ) : null;

	if ( ! $preset ) {
		return;
	}

	$payload = nexa_pro_build_preset_payload( $preset_id );

	if ( is_wp_error( $payload ) ) {
		return;
	}

	$current  = nexa_pro_get_exportable_settings();
	$report   = null;
	$settings = nexa_pro_normalize_full_settings( $payload, $report );
	$summary  = nexa_pro_get_settings_change_summary( $settings, $current, count( array_intersect_key( $payload, nexa_pro_get_default_options() ) ), 0, $report );

	?>
	<section class="nexa-pro-tools__panel nexa-pro-tools__panel--warning">
		<h2><?php esc_html_e( 'Preset Preview', 'nexa-pro' ); ?></h2>
		<h3><?php echo esc_html( $preset['label'] ); ?></h3>
		<p><?php echo esc_html( $preset['description'] ); ?></p>
		<?php nexa_pro_render_tools_summary( $summary ); ?>
		<div class="nexa-pro-tools-warning">
			<?php esc_html_e( 'Current media attachment IDs, contact details, social links, legal URLs, calendar URL, and site identity fields will be preserved.', 'nexa-pro' ); ?>
		</div>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'nexa_pro_confirm_preset' ); ?>
			<input type="hidden" name="action" value="nexa_pro_confirm_preset">
			<input type="hidden" name="nexa_pro_preset_id" value="<?php echo esc_attr( $preset_id ); ?>">
			<label class="nexa-pro-tools-confirm">
				<input type="checkbox" name="nexa_pro_confirm_preset" value="1">
				<?php esc_html_e( 'I understand this preset will replace visual and homepage structure settings while preserving site-specific values.', 'nexa-pro' ); ?>
			</label>
			<?php submit_button( __( 'Apply Preset', 'nexa-pro' ), 'primary', 'submit', false ); ?>
		</form>
	</section>
	<?php
}

/**
 * Render the Tools tab.
 *
 * @return void
 */
function nexa_pro_render_tools_tab() {
	nexa_pro_render_tools_notices();

	if ( ! current_user_can( 'manage_options' ) ) {
		?>
		<div class="nexa-pro-admin__panel">
			<p><?php esc_html_e( 'You need manage_options permission to use Nexa Pro tools.', 'nexa-pro' ); ?></p>
		</div>
		<?php
		return;
	}

	$rollback = nexa_pro_get_rollback_snapshot();
	$presets  = nexa_pro_get_presets();

	?>
	<div class="nexa-pro-tools">
		<?php nexa_pro_render_import_preview_panel(); ?>
		<?php nexa_pro_render_preset_preview_panel(); ?>

		<section class="nexa-pro-tools__panel">
			<h2><?php esc_html_e( 'Export settings', 'nexa-pro' ); ?></h2>
			<p><?php esc_html_e( 'Download the complete validated Nexa Pro option set as a JSON file. The export contains only known Nexa Pro settings.', 'nexa-pro' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'nexa_pro_export_settings' ); ?>
				<input type="hidden" name="action" value="nexa_pro_export_settings">
				<?php submit_button( __( 'Download Settings JSON', 'nexa-pro' ), 'secondary', 'submit', false ); ?>
			</form>
		</section>

		<section class="nexa-pro-tools__panel">
			<h2><?php esc_html_e( 'Import settings', 'nexa-pro' ); ?></h2>
			<p><?php esc_html_e( 'Upload a Nexa Pro settings JSON file. The file is validated first, then you must confirm before settings are applied. This is a full replace mode: missing known keys fall back to current theme defaults.', 'nexa-pro' ); ?></p>
			<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'nexa_pro_import_settings_preview' ); ?>
				<input type="hidden" name="action" value="nexa_pro_import_settings_preview">
				<label for="nexa-pro-import-file"><?php esc_html_e( 'Settings JSON file', 'nexa-pro' ); ?></label>
				<input id="nexa-pro-import-file" type="file" name="nexa_pro_import_file" accept="application/json,.json" required>
				<p class="description"><?php esc_html_e( 'Maximum file size: 1 MB. JSON is validated server-side; MIME type is not trusted.', 'nexa-pro' ); ?></p>
				<?php submit_button( __( 'Upload and Preview', 'nexa-pro' ), 'secondary', 'submit', false ); ?>
			</form>
		</section>

		<section class="nexa-pro-tools__panel">
			<h2><?php esc_html_e( 'Starter presets', 'nexa-pro' ); ?></h2>
			<p><?php esc_html_e( 'Preview a built-in preset before applying it. Presets avoid media, menus, private contact details, and site-specific URLs.', 'nexa-pro' ); ?></p>
			<div class="nexa-pro-tools-presets">
				<?php foreach ( $presets as $preset_id => $preset ) : ?>
					<div class="nexa-pro-tools-preset">
						<h3><?php echo esc_html( $preset['label'] ); ?></h3>
						<p><?php echo esc_html( $preset['description'] ); ?></p>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<?php wp_nonce_field( 'nexa_pro_preview_preset' ); ?>
							<input type="hidden" name="action" value="nexa_pro_preview_preset">
							<input type="hidden" name="nexa_pro_preset_id" value="<?php echo esc_attr( $preset_id ); ?>">
							<?php submit_button( __( 'Preview Preset', 'nexa-pro' ), 'secondary', 'submit', false ); ?>
						</form>
					</div>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="nexa-pro-tools__panel nexa-pro-tools__panel--danger">
			<h2><?php esc_html_e( 'Reset settings', 'nexa-pro' ); ?></h2>
			<p><?php esc_html_e( 'Reset only Nexa Pro theme settings to defaults. Menus, media, pages, posts, users, widgets, and plugin settings are not changed. A rollback snapshot is created first.', 'nexa-pro' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'nexa_pro_reset_settings' ); ?>
				<input type="hidden" name="action" value="nexa_pro_reset_settings">
				<label class="nexa-pro-tools-confirm">
					<input type="checkbox" name="nexa_pro_confirm_reset" value="1">
					<?php esc_html_e( 'I understand this will replace current Nexa Pro settings with defaults.', 'nexa-pro' ); ?>
				</label>
				<?php submit_button( __( 'Reset Nexa Pro Settings', 'nexa-pro' ), 'delete', 'submit', false ); ?>
			</form>
		</section>

		<section class="nexa-pro-tools__panel">
			<h2><?php esc_html_e( 'Rollback last import, preset, or reset', 'nexa-pro' ); ?></h2>
			<?php if ( $rollback ) : ?>
				<p>
					<?php
					printf(
						/* translators: 1: Rollback action. 2: Snapshot date. */
						esc_html__( 'A rollback snapshot is available from the last %1$s action on %2$s.', 'nexa-pro' ),
						esc_html( $rollback['action'] ),
						esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $rollback['created_at'] ) )
					);
					?>
				</p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'nexa_pro_rollback_settings' ); ?>
					<input type="hidden" name="action" value="nexa_pro_rollback_settings">
					<label class="nexa-pro-tools-confirm">
						<input type="checkbox" name="nexa_pro_confirm_rollback" value="1">
						<?php esc_html_e( 'I understand rollback restores only the saved Nexa Pro settings snapshot.', 'nexa-pro' ); ?>
					</label>
					<?php submit_button( __( 'Restore Rollback Snapshot', 'nexa-pro' ), 'secondary', 'submit', false ); ?>
				</form>
			<?php else : ?>
				<p><?php esc_html_e( 'No rollback snapshot is currently available.', 'nexa-pro' ); ?></p>
			<?php endif; ?>
		</section>
	</div>
	<?php
}
