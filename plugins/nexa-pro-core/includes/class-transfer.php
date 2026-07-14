<?php
/**
 * Component platform import/export helpers.
 *
 * @package Nexa_Pro_Core
 */

namespace Nexa_Pro_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Transfer service.
 */
final class Transfer {
	const EXPORT_SCHEMA_VERSION = 2;
	const MAX_JSON_BYTES        = 1048576;
	const MAX_RECORDS           = 250;
	const MAX_DEPTH             = 12;
	const PREVIEW_TRANSIENT     = 'nexa_pro_core_import_preview_';

	/**
	 * Build an export payload.
	 *
	 * @param string $scope Scope.
	 * @param array  $args  Args.
	 * @return array
	 */
	public static function build_export( $scope = 'full-site', array $args = array() ) {
		$scope   = self::sanitize_scope( $scope );
		$payload = array(
			'product'        => 'nexa-pro',
			'export_schema'  => self::EXPORT_SCHEMA_VERSION,
			'theme_version'  => defined( 'NEXA_PRO_VERSION' ) ? NEXA_PRO_VERSION : '',
			'plugin_version' => NEXA_PRO_CORE_VERSION,
			'generated_at'   => Sanitizer::current_timestamp(),
			'scope'          => $scope,
			'global_settings' => array(),
			'navigation'     => array(),
			'pages'          => array(),
			'reusable_components' => array(),
			'migration'      => array(),
		);

		if ( in_array( $scope, array( 'full-site', 'global-settings' ), true ) ) {
			$payload['global_settings'] = self::global_settings();
		}

		if ( in_array( $scope, array( 'full-site', 'navigation' ), true ) ) {
			$payload['navigation'] = Navigation_Settings::get_settings();
		}

		if ( in_array( $scope, array( 'full-site', 'one-page', 'selected-pages', 'selected-components' ), true ) ) {
			$page_ids = self::export_page_ids( $scope, $args );
			$payload['pages'] = self::page_records( $page_ids, $args );
		}

		if ( in_array( $scope, array( 'full-site', 'reusable-components' ), true ) ) {
			$payload['reusable_components'] = self::reusable_records();
		}

		if ( in_array( $scope, array( 'full-site', 'migration-report' ), true ) ) {
			$report = Migration::get_report();
			unset( $report['backup'], $report['backup_payload'] );
			$payload['migration'] = $report;
		}

		return $payload;
	}

	/**
	 * Prepare an import preview without applying it.
	 *
	 * @param string|array $raw Raw JSON or decoded payload.
	 * @return array|\WP_Error
	 */
	public static function preview_import( $raw ) {
		$payload = self::normalize_payload( $raw );

		if ( \is_wp_error( $payload ) ) {
			return $payload;
		}

		$preview = array(
			'product'              => $payload['product'],
			'export_schema'        => absint( $payload['export_schema'] ),
			'theme_version'        => isset( $payload['theme_version'] ) ? \sanitize_text_field( $payload['theme_version'] ) : '',
			'plugin_version'       => isset( $payload['plugin_version'] ) ? \sanitize_text_field( $payload['plugin_version'] ) : '',
			'scope'                => self::sanitize_scope( isset( $payload['scope'] ) ? $payload['scope'] : 'full-site' ),
			'global_settings'      => ! empty( $payload['global_settings'] ) && is_array( $payload['global_settings'] ),
			'navigation'           => ! empty( $payload['navigation'] ) && is_array( $payload['navigation'] ),
			'pages_included'       => ! empty( $payload['pages'] ) && is_array( $payload['pages'] ) ? count( $payload['pages'] ) : 0,
			'reusable_included'    => ! empty( $payload['reusable_components'] ) && is_array( $payload['reusable_components'] ) ? count( $payload['reusable_components'] ) : 0,
			'conflicts'            => self::detect_conflicts( $payload ),
			'warnings'             => self::import_warnings( $payload ),
			'unsupported_fields'   => array(),
			'valid'                => true,
		);

		return array(
			'preview' => $preview,
			'payload' => $payload,
			'token'   => self::store_preview( $payload ),
		);
	}

	/**
	 * Apply an import payload.
	 *
	 * @param array  $payload       Payload.
	 * @param string $conflict_mode Conflict mode.
	 * @param array  $page_map      Explicit page map.
	 * @param array  $args          Capability args.
	 * @return array|\WP_Error
	 */
	public static function apply_import( array $payload, $conflict_mode = 'skip', array $page_map = array(), $args = array() ) {
		$verified = Capabilities::verify_operation( Capabilities::IMPORT_COMPONENTS, $args );

		if ( \is_wp_error( $verified ) ) {
			return $verified;
		}

		$payload = self::normalize_payload( $payload );

		if ( \is_wp_error( $payload ) ) {
			return $payload;
		}

		$conflict_mode = self::sanitize_conflict_mode( $conflict_mode );
		$result        = array(
			'global_settings_imported' => false,
			'navigation_imported'      => false,
			'pages_imported'           => 0,
			'reusable_imported'        => 0,
			'skipped'                  => 0,
		);

		if ( ! empty( $payload['global_settings'] ) && is_array( $payload['global_settings'] ) ) {
			\update_option( 'nexa_pro_options', self::sanitize_global_settings( $payload['global_settings'] ), false );
			$result['global_settings_imported'] = true;
		}

		if ( ! empty( $payload['navigation'] ) && is_array( $payload['navigation'] ) ) {
			Navigation_Settings::save_settings( $payload['navigation'] );
			$result['navigation_imported'] = true;
		}

		$reusable_map = array();

		if ( ! empty( $payload['reusable_components'] ) && is_array( $payload['reusable_components'] ) ) {
			foreach ( $payload['reusable_components'] as $record ) {
				$created = self::import_reusable_record( $record, $args );

				if ( \is_wp_error( $created ) ) {
					$result['skipped']++;
					continue;
				}

				if ( ! empty( $record['source_reusable_id'] ) ) {
					$reusable_map[ absint( $record['source_reusable_id'] ) ] = absint( $created );
				}

				$result['reusable_imported']++;
			}
		}

		if ( ! empty( $payload['pages'] ) && is_array( $payload['pages'] ) ) {
			foreach ( $payload['pages'] as $record ) {
				$page_result = self::import_page_record( $record, $conflict_mode, $page_map, $args, $reusable_map );

				if ( \is_wp_error( $page_result ) ) {
					$result['skipped']++;
					continue;
				}

				$result['pages_imported']++;
			}
		}

		return $result;
	}

	/**
	 * Get payload stored for a preview token.
	 *
	 * @param string $token Token.
	 * @return array
	 */
	public static function get_preview_payload( $token ) {
		$token   = preg_replace( '/[^a-f0-9]/', '', strtolower( (string) $token ) );
		$payload = $token ? \get_transient( self::PREVIEW_TRANSIENT . absint( \get_current_user_id() ) . '_' . $token ) : array();

		return is_array( $payload ) ? $payload : array();
	}

	/**
	 * Normalize an import payload.
	 *
	 * @param string|array $raw Raw payload.
	 * @return array|\WP_Error
	 */
	public static function normalize_payload( $raw ) {
		if ( is_string( $raw ) ) {
			if ( strlen( $raw ) > self::MAX_JSON_BYTES ) {
				return new \WP_Error( 'nexa_pro_core_import_too_large', __( 'Import JSON exceeds the maximum supported size.', 'nexa-pro-core' ) );
			}

			$decoded = json_decode( $raw, true, self::MAX_DEPTH );

			if ( ! is_array( $decoded ) || JSON_ERROR_NONE !== json_last_error() ) {
				return new \WP_Error( 'nexa_pro_core_import_malformed_json', __( 'Import JSON is malformed.', 'nexa-pro-core' ) );
			}

			$raw = $decoded;
		}

		if ( ! is_array( $raw ) ) {
			return new \WP_Error( 'nexa_pro_core_import_invalid_payload', __( 'Import payload must be a JSON object.', 'nexa-pro-core' ) );
		}

		$product = isset( $raw['product'] ) ? \sanitize_key( $raw['product'] ) : '';

		if ( 'nexa-pro' !== $product ) {
			return new \WP_Error( 'nexa_pro_core_import_wrong_product', __( 'Import product must be nexa-pro.', 'nexa-pro-core' ) );
		}

		$schema = isset( $raw['export_schema'] ) ? absint( $raw['export_schema'] ) : absint( isset( $raw['schema_version'] ) ? $raw['schema_version'] : 0 );

		if ( ! in_array( $schema, array( 1, self::EXPORT_SCHEMA_VERSION ), true ) ) {
			return new \WP_Error( 'nexa_pro_core_import_unsupported_schema', __( 'Import schema is not supported.', 'nexa-pro-core' ) );
		}

		$pages = ! empty( $raw['pages'] ) && is_array( $raw['pages'] ) ? $raw['pages'] : array();
		$reusable = ! empty( $raw['reusable_components'] ) && is_array( $raw['reusable_components'] ) ? $raw['reusable_components'] : array();

		if ( count( $pages ) + count( $reusable ) > self::MAX_RECORDS ) {
			return new \WP_Error( 'nexa_pro_core_import_too_many_records', __( 'Import payload contains too many records.', 'nexa-pro-core' ) );
		}

		return array(
			'product'              => 'nexa-pro',
			'export_schema'        => $schema,
			'theme_version'        => isset( $raw['theme_version'] ) ? \sanitize_text_field( $raw['theme_version'] ) : '',
			'plugin_version'       => isset( $raw['plugin_version'] ) ? \sanitize_text_field( $raw['plugin_version'] ) : '',
			'generated_at'         => isset( $raw['generated_at'] ) ? Sanitizer::sanitize_timestamp( $raw['generated_at'], '' ) : '',
			'scope'                => self::sanitize_scope( isset( $raw['scope'] ) ? $raw['scope'] : 'full-site' ),
			'global_settings'      => ! empty( $raw['global_settings'] ) && is_array( $raw['global_settings'] ) ? $raw['global_settings'] : self::old_schema_settings( $raw ),
			'navigation'           => ! empty( $raw['navigation'] ) && is_array( $raw['navigation'] ) ? $raw['navigation'] : array(),
			'pages'                => $pages,
			'reusable_components'  => $reusable,
			'migration'            => ! empty( $raw['migration'] ) && is_array( $raw['migration'] ) ? $raw['migration'] : array(),
		);
	}

	private static function sanitize_scope( $scope ) {
		$scope = \sanitize_key( $scope );
		$allowed = array( 'full-site', 'global-settings', 'navigation', 'one-page', 'selected-pages', 'selected-components', 'reusable-components', 'migration-report' );

		return in_array( $scope, $allowed, true ) ? $scope : 'full-site';
	}

	private static function sanitize_conflict_mode( $mode ) {
		$mode = \sanitize_key( $mode );

		return in_array( $mode, array( 'skip', 'merge', 'replace', 'create-new' ), true ) ? $mode : 'skip';
	}

	private static function global_settings() {
		$options = \get_option( 'nexa_pro_options', array() );

		if ( function_exists( 'nexa_pro_get_options' ) ) {
			$options = \nexa_pro_get_options();
		}

		return is_array( $options ) ? $options : array();
	}

	private static function sanitize_global_settings( array $settings ) {
		if ( function_exists( 'nexa_pro_sanitize_options' ) ) {
			return \nexa_pro_sanitize_options( $settings );
		}

		return $settings;
	}

	private static function export_page_ids( $scope, array $args ) {
		$page_ids = ! empty( $args['page_ids'] ) && is_array( $args['page_ids'] ) ? array_map( 'absint', $args['page_ids'] ) : array();

		if ( 'one-page' === $scope && ! empty( $args['page_id'] ) ) {
			$page_ids = array( absint( $args['page_id'] ) );
		}

		if ( empty( $page_ids ) && 'full-site' === $scope ) {
			$pages = \get_posts(
				array(
					'post_type'      => 'page',
					'post_status'    => array( 'publish', 'draft', 'private' ),
					'posts_per_page' => 100,
					'fields'         => 'ids',
				)
			);
			$page_ids = array_map( 'absint', $pages );
		}

		return array_values( array_filter( array_unique( $page_ids ) ) );
	}

	private static function page_records( array $page_ids, array $args = array() ) {
		$records = array();
		$component_ids = ! empty( $args['component_ids'] ) && is_array( $args['component_ids'] ) ? array_map( 'sanitize_text_field', $args['component_ids'] ) : array();

		foreach ( $page_ids as $page_id ) {
			$page = \get_post( $page_id );

			if ( ! $page || 'page' !== $page->post_type ) {
				continue;
			}

			$components = Storage::get_page_components( $page_id );
			$components = \is_wp_error( $components ) ? array() : $components;

			if ( $component_ids ) {
				$components = array_values(
					array_filter(
						$components,
						function ( $component ) use ( $component_ids ) {
							return ! empty( $component['instance_id'] ) && in_array( $component['instance_id'], $component_ids, true );
						}
					)
				);
			}

			$records[] = array(
				'source_page_id' => absint( $page_id ),
				'title'          => \get_the_title( $page ),
				'slug'           => $page->post_name,
				'status'         => $page->post_status,
				'components'     => $components,
			);
		}

		return $records;
	}

	private static function reusable_records() {
		if ( ! class_exists( Reusable_Components::class ) ) {
			return array();
		}

		$posts = \get_posts(
			array(
				'post_type'      => NEXA_PRO_CORE_REUSABLE_POST_TYPE,
				'post_status'    => array( 'publish', 'draft', 'private' ),
				'posts_per_page' => 100,
			)
		);
		$records = array();

		foreach ( $posts as $post ) {
			$payload = Reusable_Components::get_reusable_component( $post->ID );

			if ( \is_wp_error( $payload ) ) {
				continue;
			}

			$records[] = array(
				'source_reusable_id' => absint( $post->ID ),
				'title'              => \get_the_title( $post ),
				'payload'            => $payload,
			);
		}

		return $records;
	}

	private static function detect_conflicts( array $payload ) {
		$conflicts = array();

		foreach ( ! empty( $payload['pages'] ) && is_array( $payload['pages'] ) ? $payload['pages'] : array() as $record ) {
			$slug = ! empty( $record['slug'] ) ? \sanitize_title( $record['slug'] ) : '';

			if ( $slug && \get_page_by_path( $slug, OBJECT, 'page' ) ) {
				$conflicts[] = sprintf(
					/* translators: %s: page slug. */
					__( 'Page slug conflict: %s', 'nexa-pro-core' ),
					$slug
				);
			}
		}

		return $conflicts;
	}

	private static function import_warnings( array $payload ) {
		$warnings = array();

		if ( 1 === absint( $payload['export_schema'] ) ) {
			$warnings[] = __( 'Old schema imports can restore supported global settings but may not include page components.', 'nexa-pro-core' );
		}

		return $warnings;
	}

	private static function store_preview( array $payload ) {
		$token = substr( hash( 'sha256', \wp_json_encode( $payload ) . microtime( true ) ), 0, 16 );
		\set_transient( self::PREVIEW_TRANSIENT . absint( \get_current_user_id() ) . '_' . $token, $payload, 15 * MINUTE_IN_SECONDS );

		return $token;
	}

	private static function old_schema_settings( array $raw ) {
		if ( ! empty( $raw['settings'] ) && is_array( $raw['settings'] ) ) {
			return $raw['settings'];
		}

		return array();
	}

	private static function import_page_record( array $record, $conflict_mode, array $page_map, array $args, array $reusable_map ) {
		$source_id = ! empty( $record['source_page_id'] ) ? absint( $record['source_page_id'] ) : 0;
		$page_id   = ! empty( $page_map[ $source_id ] ) ? absint( $page_map[ $source_id ] ) : 0;
		$slug      = ! empty( $record['slug'] ) ? \sanitize_title( $record['slug'] ) : '';

		if ( ! $page_id && $slug && 'create-new' !== $conflict_mode ) {
			$page = \get_page_by_path( $slug, OBJECT, 'page' );
			$page_id = $page ? absint( $page->ID ) : 0;
		}

		if ( ! $page_id && 'create-new' === $conflict_mode ) {
			$page_id = \wp_insert_post(
				array(
					'post_type'   => 'page',
					'post_status' => 'draft',
					'post_title'  => ! empty( $record['title'] ) ? \sanitize_text_field( $record['title'] ) : __( 'Imported Nexa Pro Page', 'nexa-pro-core' ),
					'post_name'   => $slug,
				),
				true
			);

			if ( \is_wp_error( $page_id ) ) {
				return $page_id;
			}
		}

		$page_id = absint( $page_id );

		if ( ! $page_id ) {
			return new \WP_Error( 'nexa_pro_core_import_page_unmapped', __( 'Page import requires an explicit or safe page mapping.', 'nexa-pro-core' ) );
		}

		$components = ! empty( $record['components'] ) && is_array( $record['components'] ) ? $record['components'] : array();
		$existing   = Storage::get_page_components( $page_id );
		$existing   = \is_wp_error( $existing ) ? array() : $existing;

		if ( 'skip' === $conflict_mode && ! empty( $existing ) ) {
			return new \WP_Error( 'nexa_pro_core_import_page_skipped', __( 'Page already has components and skip mode is active.', 'nexa-pro-core' ) );
		}

		$components = self::remap_reusable_ids( $components, $reusable_map );
		$incoming   = Sanitizer::sanitize_component_collection( $components );

		if ( \is_wp_error( $incoming ) ) {
			return $incoming;
		}

		$save = 'merge' === $conflict_mode ? self::merge_components( $existing, $incoming ) : $incoming;

		return Storage::save_page_components( $page_id, $save, $args );
	}

	/**
	 * Remap imported reusable IDs to newly created local IDs.
	 *
	 * @param array $components   Components.
	 * @param array $reusable_map Old-to-new reusable map.
	 * @return array
	 */
	private static function remap_reusable_ids( array $components, array $reusable_map ) {
		foreach ( $components as $index => $component ) {
			if ( empty( $component['reusable_component_id'] ) ) {
				continue;
			}

			$old_id = absint( $component['reusable_component_id'] );

			if ( isset( $reusable_map[ $old_id ] ) ) {
				$components[ $index ]['reusable_component_id'] = absint( $reusable_map[ $old_id ] );
			}
		}

		return $components;
	}

	/**
	 * Merge imported components while avoiding duplicate anchors.
	 *
	 * @param array $existing Existing.
	 * @param array $incoming Incoming.
	 * @return array
	 */
	private static function merge_components( array $existing, array $incoming ) {
		$anchors = array();

		foreach ( $existing as $component ) {
			if ( ! empty( $component['navigation']['anchor_id'] ) ) {
				$anchors[] = $component['navigation']['anchor_id'];
			}
		}

		foreach ( $incoming as $index => $component ) {
			if ( empty( $component['navigation']['anchor_id'] ) ) {
				continue;
			}

			$anchor = Sanitizer::sanitize_anchor_id( $component['navigation']['anchor_id'] );

			if ( in_array( $anchor, $anchors, true ) ) {
				$base   = $anchor . '-imported';
				$suffix = 2;
				$anchor = $base;

				while ( in_array( $anchor, $anchors, true ) ) {
					$anchor = $base . '-' . $suffix;
					$suffix++;
				}
			}

			$incoming[ $index ]['navigation']['anchor_id'] = $anchor;
			$anchors[] = $anchor;
		}

		return array_merge( $existing, $incoming );
	}

	private static function import_reusable_record( array $record, array $args ) {
		if ( empty( $record['payload'] ) || ! is_array( $record['payload'] ) ) {
			return new \WP_Error( 'nexa_pro_core_import_reusable_invalid', __( 'Reusable component payload is invalid.', 'nexa-pro-core' ) );
		}

		return Reusable_Components::create_reusable_component( $record['payload'], $args );
	}
}
