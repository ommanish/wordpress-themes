<?php
/**
 * Legacy Nexa Pro fixed-section migration helpers.
 *
 * @package Nexa_Pro_Core
 */

namespace Nexa_Pro_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Migration service.
 */
final class Migration {
	const BACKUP_OPTION = 'nexa_pro_core_migration_backup';
	const REPORT_OPTION = 'nexa_pro_core_migration_report';

	/**
	 * Get a default migration state payload.
	 *
	 * @return array
	 */
	public static function default_state() {
		return array(
			'status'                => 'not_needed',
			'schema_version'        => Schema::current_schema_version(),
			'source_theme_version'  => self::theme_version(),
			'source_schema_version' => 1,
			'target_plugin_version' => NEXA_PRO_CORE_VERSION,
			'target_schema_version' => Schema::current_schema_version(),
			'detected_at'           => '',
			'previewed_at'          => '',
			'completed_at'          => '',
			'rolled_back_at'        => '',
			'target_page_id'        => 0,
			'components_created'    => 0,
			'fields_migrated'       => 0,
			'fields_skipped'        => 0,
			'warnings'              => array(),
			'errors'                => array(),
			'migration_hash'        => '',
			'backup_reference'      => '',
			'capabilities_version'  => '',
		);
	}

	/**
	 * Get normalized migration state.
	 *
	 * @return array
	 */
	public static function get_state() {
		$stored = \get_option( NEXA_PRO_CORE_MIGRATION_STATE_OPTION, array() );
		$stored = is_array( $stored ) ? $stored : array();
		$state  = array_merge( self::default_state(), array_intersect_key( $stored, self::default_state() ) );

		$state['status'] = self::sanitize_status( $state['status'] );

		foreach ( array( 'schema_version', 'source_schema_version', 'target_schema_version', 'target_page_id', 'components_created', 'fields_migrated', 'fields_skipped' ) as $key ) {
			$state[ $key ] = absint( $state[ $key ] );
		}

		foreach ( array( 'warnings', 'errors' ) as $key ) {
			$state[ $key ] = is_array( $state[ $key ] ) ? array_values( array_map( 'sanitize_text_field', $state[ $key ] ) ) : array();
		}

		return $state;
	}

	/**
	 * Update migration state while preserving schema/capability metadata.
	 *
	 * @param array $changes Changes.
	 * @return array
	 */
	public static function update_state( array $changes ) {
		$state = array_merge( self::get_state(), array_intersect_key( $changes, self::default_state() ) );

		$state['status']                = self::sanitize_status( $state['status'] );
		$state['schema_version']        = Schema::current_schema_version();
		$state['target_plugin_version'] = NEXA_PRO_CORE_VERSION;
		$state['target_schema_version'] = Schema::current_schema_version();
		$state['updated_at']            = Sanitizer::current_timestamp();

		\update_option( NEXA_PRO_CORE_MIGRATION_STATE_OPTION, $state, false );

		return $state;
	}

	/**
	 * Detect whether legacy settings are available for migration.
	 *
	 * @param int $target_page_id Target page ID.
	 * @return array
	 */
	public static function detect( $target_page_id = 0 ) {
		$options        = self::legacy_options();
		$target_page_id = self::normalize_target_page_id( $target_page_id );
		$warnings       = array();
		$legacy_present = self::legacy_data_is_present( $options );
		$has_builder    = $target_page_id ? Render_API::has_builder_components( $target_page_id ) : false;
		$state          = self::get_state();

		if ( ! $target_page_id ) {
			$warnings[] = __( 'Choose a valid target page before migration can be applied.', 'nexa-pro-core' );
		}

		if ( ! $legacy_present ) {
			$status = 'not_needed';
		} elseif ( 'completed' === $state['status'] ) {
			$status = 'completed';
		} else {
			$status = 'available';
		}

		if ( $has_builder ) {
			$warnings[] = __( 'The selected target page already has builder components. Merge mode preserves them; replace mode backs them up first.', 'nexa-pro-core' );
		}

		return array(
			'status'              => $status,
			'legacy_present'      => $legacy_present,
			'target_page_id'      => $target_page_id,
			'has_builder_data'    => $has_builder,
			'source_theme_version' => self::theme_version(),
			'migration_hash'      => self::migration_hash( $options ),
			'warnings'            => $warnings,
		);
	}

	/**
	 * Build a migration preview.
	 *
	 * @param int    $target_page_id Target page ID.
	 * @param string $mode           merge or replace-builder-data.
	 * @return array
	 */
	public static function preview( $target_page_id = 0, $mode = 'merge' ) {
		$options        = self::legacy_options();
		$target_page_id = self::normalize_target_page_id( $target_page_id );
		$mode           = self::sanitize_mode( $mode );
		$components     = self::map_legacy_options_to_components( $options );
		$existing       = $target_page_id ? Storage::get_page_components( $target_page_id ) : array();
		$existing       = \is_wp_error( $existing ) ? array() : $existing;
		$unsupported    = self::unsupported_sections( $options );
		$field_counts   = self::count_component_fields( $components );
		$hash           = self::migration_hash( $options );
		$state          = self::get_state();
		$warnings       = array();

		if ( $state['migration_hash'] && $state['migration_hash'] !== $hash && in_array( $state['status'], array( 'previewed', 'completed', 'rolled_back', 'failed' ), true ) ) {
			$warnings[] = __( 'The legacy source data changed since the last migration preview or apply.', 'nexa-pro-core' );
		}

		if ( empty( $components ) ) {
			$warnings[] = __( 'No supported legacy sections were detected.', 'nexa-pro-core' );
		}

		if ( $unsupported ) {
			$warnings[] = __( 'Unsupported legacy sections will remain in legacy settings and are not converted to builder components.', 'nexa-pro-core' );
		}

		$preview = array(
			'status'                => empty( $components ) ? 'not_needed' : 'previewed',
			'source_theme_version'  => self::theme_version(),
			'source_schema_version' => 1,
			'target_plugin_version' => NEXA_PRO_CORE_VERSION,
			'target_schema_version' => Schema::current_schema_version(),
			'target_page_id'        => $target_page_id,
			'mode'                  => $mode,
			'legacy_order'          => self::legacy_order( $options ),
			'components'            => $components,
			'components_created'    => count( $components ),
			'fields_migrated'       => $field_counts['migrated'],
			'fields_skipped'        => $field_counts['skipped'] + count( $unsupported ),
			'unsupported_sections'  => $unsupported,
			'existing_components'   => count( $existing ),
			'migration_hash'        => $hash,
			'warnings'              => $warnings,
			'errors'                => $target_page_id ? array() : array( __( 'A valid target page is required.', 'nexa-pro-core' ) ),
		);

		self::update_state(
			array(
				'status'             => empty( $preview['errors'] ) && ! empty( $components ) ? 'previewed' : $preview['status'],
				'previewed_at'       => Sanitizer::current_timestamp(),
				'target_page_id'     => $target_page_id,
				'components_created' => count( $components ),
				'fields_migrated'    => $preview['fields_migrated'],
				'fields_skipped'     => $preview['fields_skipped'],
				'warnings'           => $warnings,
				'errors'             => $preview['errors'],
				'migration_hash'     => $hash,
			)
		);

		return $preview;
	}

	/**
	 * Apply a migration.
	 *
	 * @param int    $target_page_id Target page ID.
	 * @param string $mode           Migration mode.
	 * @param bool   $switch_mode    Whether to switch frontend to builder mode.
	 * @param array  $args           Capability args.
	 * @return array|\WP_Error
	 */
	public static function apply( $target_page_id, $mode = 'merge', $switch_mode = false, $args = array() ) {
		$target_page_id = Storage::validate_page_id( $target_page_id );

		if ( \is_wp_error( $target_page_id ) ) {
			return $target_page_id;
		}

		$verified = Capabilities::verify_page_write( $target_page_id, $args );

		if ( \is_wp_error( $verified ) ) {
			return $verified;
		}

		$preview = self::preview( $target_page_id, $mode );

		if ( ! empty( $preview['errors'] ) ) {
			return new \WP_Error( 'nexa_pro_core_migration_preview_invalid', implode( ' ', $preview['errors'] ) );
		}

		$existing = Storage::get_page_components( $target_page_id );

		if ( \is_wp_error( $existing ) ) {
			return $existing;
		}

		$backup_reference = self::create_backup( $target_page_id, $existing );
		$mode             = self::sanitize_mode( $mode );
		$components       = $preview['components'];
		$merged           = 'merge' === $mode ? self::merge_components( $existing, $components ) : $components;
		$saved            = Storage::save_page_components( $target_page_id, $merged, $args );

		if ( \is_wp_error( $saved ) ) {
			self::update_state(
				array(
					'status' => 'failed',
					'errors' => array( $saved->get_error_message() ),
				)
			);

			return $saved;
		}

		if ( $switch_mode ) {
			Compatibility_Mode::set_mode( 'builder' );
		}

		$state = self::update_state(
			array(
				'status'             => 'completed',
				'completed_at'       => Sanitizer::current_timestamp(),
				'target_page_id'     => $target_page_id,
				'components_created' => count( $components ),
				'fields_migrated'    => $preview['fields_migrated'],
				'fields_skipped'     => $preview['fields_skipped'],
				'warnings'           => $preview['warnings'],
				'errors'             => array(),
				'migration_hash'     => $preview['migration_hash'],
				'backup_reference'   => $backup_reference,
			)
		);

		\update_option( self::REPORT_OPTION, self::migration_report( $preview, $state ), false );

		return $state;
	}

	/**
	 * Roll back the most recent migration.
	 *
	 * @param array $args Capability args.
	 * @return array|\WP_Error
	 */
	public static function rollback( $args = array() ) {
		$backup = \get_option( self::BACKUP_OPTION, array() );

		if ( ! is_array( $backup ) || empty( $backup['target_page_id'] ) ) {
			return new \WP_Error( 'nexa_pro_core_no_migration_backup', __( 'No migration backup is available.', 'nexa-pro-core' ) );
		}

		$page_id  = absint( $backup['target_page_id'] );
		$verified = Capabilities::verify_page_write( $page_id, $args );

		if ( \is_wp_error( $verified ) ) {
			return $verified;
		}

		$components = ! empty( $backup['components'] ) && is_array( $backup['components'] ) ? $backup['components'] : array();
		$restored   = Storage::save_page_components( $page_id, $components, $args );

		if ( \is_wp_error( $restored ) ) {
			return $restored;
		}

		if ( isset( $backup['navigation_settings'] ) && is_array( $backup['navigation_settings'] ) ) {
			Navigation_Settings::save_settings( $backup['navigation_settings'] );
		}

		if ( ! empty( $backup['compatibility_mode'] ) ) {
			Compatibility_Mode::set_mode( $backup['compatibility_mode'] );
		}

		return self::update_state(
			array(
				'status'         => 'rolled_back',
				'rolled_back_at' => Sanitizer::current_timestamp(),
				'target_page_id' => $page_id,
			)
		);
	}

	/**
	 * Get stored report.
	 *
	 * @return array
	 */
	public static function get_report() {
		$report = \get_option( self::REPORT_OPTION, array() );

		return is_array( $report ) ? $report : array();
	}

	/**
	 * Get legacy theme options.
	 *
	 * @return array
	 */
	public static function legacy_options() {
		$options = \get_option( 'nexa_pro_options', array() );

		if ( function_exists( 'nexa_pro_get_options' ) ) {
			$options = \nexa_pro_get_options();
		}

		return is_array( $options ) ? $options : array();
	}

	/**
	 * Normalize a target page ID.
	 *
	 * @param int $target_page_id Page ID.
	 * @return int
	 */
	public static function normalize_target_page_id( $target_page_id = 0 ) {
		$target_page_id = absint( $target_page_id );

		if ( $target_page_id && ! \is_wp_error( Storage::validate_page_id( $target_page_id ) ) ) {
			return $target_page_id;
		}

		$front_page_id = absint( \get_option( 'page_on_front' ) );

		if ( $front_page_id && ! \is_wp_error( Storage::validate_page_id( $front_page_id ) ) ) {
			return $front_page_id;
		}

		$pages = \get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => array( 'publish', 'draft', 'private' ),
				'posts_per_page' => 1,
				'orderby'        => 'ID',
				'order'          => 'ASC',
			)
		);

		return ! empty( $pages[0]->ID ) ? absint( $pages[0]->ID ) : 0;
	}

	/**
	 * Build components from legacy options.
	 *
	 * @param array $options Legacy options.
	 * @return array
	 */
	public static function map_legacy_options_to_components( array $options ) {
		$order      = self::legacy_order( $options );
		$components = array();
		$index      = 0;

		foreach ( $order as $type ) {
			$type = \sanitize_key( $type );

			if ( ! Sanitizer::component_type_is_allowed( $type ) ) {
				continue;
			}

			if ( ! self::section_enabled( $type, $options ) ) {
				continue;
			}

			$component = self::map_section( $type, $options, $index );

			if ( $component ) {
				$components[] = $component;
				$index++;
			}
		}

		return $components;
	}

	/**
	 * Get safe migration mode.
	 *
	 * @param string $mode Mode.
	 * @return string
	 */
	public static function sanitize_mode( $mode ) {
		$mode = \sanitize_key( $mode );

		return in_array( $mode, array( 'merge', 'replace-builder-data' ), true ) ? $mode : 'merge';
	}

	/**
	 * Get a stable migration hash.
	 *
	 * @param array $options Options.
	 * @return string
	 */
	public static function migration_hash( array $options ) {
		$source = array_intersect_key( $options, array_flip( self::legacy_source_keys() ) );

		return substr( hash( 'sha256', \wp_json_encode( $source ) ), 0, 24 );
	}

	/**
	 * Get known source keys.
	 *
	 * @return array
	 */
	private static function legacy_source_keys() {
		$keys = array(
			'homepage_section_order',
			'hero_eyebrow',
			'hero_heading',
			'hero_text',
			'hero_primary_cta_text',
			'hero_primary_cta_url',
			'hero_secondary_cta_text',
			'hero_secondary_cta_url',
			'hero_layout',
			'hero_image_id',
			'hero_mobile_image_id',
			'about_show',
			'about_label',
			'about_heading',
			'about_text',
			'about_image_id',
			'services_show',
			'services_label',
			'services_heading',
			'services_text',
			'services_items',
			'features_show',
			'features_label',
			'features_heading',
			'features_text',
			'features_items',
			'process_show',
			'process_label',
			'process_heading',
			'process_text',
			'process_items',
			'why_show',
			'why_label',
			'why_heading',
			'why_text',
			'why_items',
			'cta_show',
			'cta_heading',
			'cta_text',
			'cta_button_text',
			'cta_button_url',
		);

		if ( function_exists( 'nexa_pro_get_default_options' ) ) {
			$defaults = \nexa_pro_get_default_options();

			if ( is_array( $defaults ) ) {
				$keys = array_merge( $keys, array_keys( $defaults ) );
			}
		}

		return array_values( array_unique( array_map( 'sanitize_key', $keys ) ) );
	}

	/**
	 * Determine if any legacy data exists.
	 *
	 * @param array $options Options.
	 * @return bool
	 */
	private static function legacy_data_is_present( array $options ) {
		$raw_options = \get_option( 'nexa_pro_options', null );

		if ( ! is_array( $raw_options ) || empty( $raw_options ) ) {
			return false;
		}

		foreach ( self::legacy_source_keys() as $key ) {
			if ( array_key_exists( $key, $options ) && '' !== $options[ $key ] && array() !== $options[ $key ] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get migration order.
	 *
	 * @param array $options Options.
	 * @return array
	 */
	private static function legacy_order( array $options ) {
		$order = array( 'hero' );
		$saved = ! empty( $options['homepage_section_order'] ) && is_array( $options['homepage_section_order'] ) ? $options['homepage_section_order'] : array();
		$default = array( 'about', 'services', 'features', 'process', 'why', 'portfolio', 'testimonials', 'team', 'faq', 'contact', 'cta' );

		foreach ( array_merge( $saved, $default ) as $type ) {
			$type = \sanitize_key( $type );

			if ( ! in_array( $type, $order, true ) ) {
				$order[] = $type;
			}
		}

		return $order;
	}

	/**
	 * Determine if a section is enabled.
	 *
	 * @param string $type    Type.
	 * @param array  $options Options.
	 * @return bool
	 */
	private static function section_enabled( $type, array $options ) {
		if ( 'hero' === $type ) {
			return true;
		}

		$key = $type . '_show';

		return ! isset( $options[ $key ] ) || '0' !== (string) $options[ $key ];
	}

	/**
	 * Map one section to a component.
	 *
	 * @param string $type    Type.
	 * @param array  $options Options.
	 * @param int    $order   Order.
	 * @return array
	 */
	private static function map_section( $type, array $options, $order ) {
		$hash    = substr( self::migration_hash( $options ), 0, 10 );
		$content = array(
			'eyebrow' => self::option_text( $options, $type . '_label' ),
			'heading' => self::option_text( $options, $type . '_heading' ),
			'body'    => self::option_text( $options, $type . '_text' ),
		);
		$design  = self::map_section_design( $type, $options );
		$layout  = self::default_layout_for_type( $type );

		if ( 'hero' === $type ) {
			$content = array(
				'eyebrow'             => self::option_text( $options, 'hero_eyebrow' ),
				'heading'             => self::option_text( $options, 'hero_heading' ),
				'body'                => self::option_text( $options, 'hero_text' ),
				'primary_cta_label'   => self::option_text( $options, 'hero_primary_cta_text' ),
				'primary_cta_url'     => self::option_text( $options, 'hero_primary_cta_url' ),
				'secondary_cta_label' => self::option_text( $options, 'hero_secondary_cta_text' ),
				'secondary_cta_url'   => self::option_text( $options, 'hero_secondary_cta_url' ),
				'desktop_image_id'    => self::option_absint( $options, 'hero_image_id' ),
				'mobile_image_id'     => self::option_absint( $options, 'hero_mobile_image_id' ),
			);
			$layout  = self::map_hero_layout( self::option_text( $options, 'hero_layout' ) );
		} elseif ( in_array( $type, array( 'services', 'features', 'process', 'why' ), true ) ) {
			$items = ! empty( $options[ $type . '_items' ] ) && is_array( $options[ $type . '_items' ] ) ? $options[ $type . '_items' ] : array();

			if ( $items ) {
				$content['items'] = $items;
			}
		}

		foreach ( array( 'about_image_id', 'why_image_id', 'portfolio_image_id' ) as $image_key ) {
			if ( 0 === strpos( $image_key, $type . '_' ) && ! empty( $options[ $image_key ] ) ) {
				$content['desktop_image_id'] = absint( $options[ $image_key ] );
			}
		}

		if ( 'cta' === $type ) {
			$content['primary_cta_label'] = self::option_text( $options, 'cta_button_text' );
			$content['primary_cta_url']   = self::option_text( $options, 'cta_button_url' );
		}

		$component = array(
			'instance_id'    => 'nexa_' . $type . '_' . substr( hash( 'sha256', $type . '|' . $hash ), 0, 10 ),
			'component_type' => $type,
			'admin_title'    => self::component_title( $type, $content ),
			'enabled'        => true,
			'order'          => absint( $order ),
			'layout'         => $layout,
			'content'        => $content,
			'design'         => $design,
			'navigation'     => array(
				'show_in_navigation' => true,
				'navigation_label'   => self::component_title( $type, $content ),
				'anchor_id'          => self::anchor_for_type( $type ),
				'mobile_visibility'  => 'all',
			),
			'advanced'       => array(
				'semantic_element' => 'section',
				'device_visibility' => 'all',
				'animation_preset' => 'none',
			),
		);

		return Sanitizer::sanitize_component_instance( $component );
	}

	/**
	 * Map section design values.
	 *
	 * @param string $type    Type.
	 * @param array  $options Options.
	 * @return array
	 */
	private static function map_section_design( $type, array $options ) {
		$design = array();
		$prefix = $type . '_';

		$field_map = array(
			'background_type',
			'background_color',
			'gradient_start',
			'gradient_end',
			'gradient_direction',
			'overlay_enabled',
			'overlay_color',
			'overlay_opacity',
			'text_theme',
			'background_image_id',
		);

		foreach ( $field_map as $field ) {
			$key = $prefix . $field;

			if ( array_key_exists( $key, $options ) ) {
				$design[ $field ] = $options[ $key ];
			}
		}

		if ( 'hero' === $type && ! empty( $options['hero_image_id'] ) && 'background-image' === self::map_hero_layout( self::option_text( $options, 'hero_layout' ) ) ) {
			$design['background_image_id'] = absint( $options['hero_image_id'] );
			$design['background_type']     = 'image';
		}

		return $design;
	}

	/**
	 * Create a backup before migration.
	 *
	 * @param int   $target_page_id Target page.
	 * @param array $components     Components.
	 * @return string
	 */
	private static function create_backup( $target_page_id, array $components ) {
		$reference = 'migration-' . gmdate( 'YmdHis' );
		$backup    = array(
			'reference'           => $reference,
			'created_at'          => Sanitizer::current_timestamp(),
			'target_page_id'      => absint( $target_page_id ),
			'components'          => $components,
			'navigation_settings' => Navigation_Settings::get_settings(),
			'compatibility_mode'  => Compatibility_Mode::get_mode(),
		);

		\update_option( self::BACKUP_OPTION, $backup, false );

		return $reference;
	}

	/**
	 * Merge migration output into existing components idempotently.
	 *
	 * @param array $existing Existing components.
	 * @param array $incoming Incoming components.
	 * @return array
	 */
	private static function merge_components( array $existing, array $incoming ) {
		$by_id = array();
		$anchors_by_id = array();

		foreach ( $existing as $component ) {
			if ( ! empty( $component['instance_id'] ) ) {
				$by_id[ $component['instance_id'] ] = $component;

				if ( ! empty( $component['navigation']['anchor_id'] ) ) {
					$anchors_by_id[ $component['instance_id'] ] = $component['navigation']['anchor_id'];
				}
			}
		}

		foreach ( $incoming as $component ) {
			if ( ! empty( $component['instance_id'] ) && ! empty( $component['navigation']['anchor_id'] ) ) {
				$component['navigation']['anchor_id'] = self::unique_anchor_for_merge(
					$component['navigation']['anchor_id'],
					$component['instance_id'],
					$anchors_by_id
				);
				$anchors_by_id[ $component['instance_id'] ] = $component['navigation']['anchor_id'];
			}

			$by_id[ $component['instance_id'] ] = $component;
		}

		return array_values( $by_id );
	}

	/**
	 * Preserve idempotency while avoiding duplicate anchors in merge mode.
	 *
	 * @param string $anchor_id    Incoming anchor.
	 * @param string $instance_id  Incoming instance ID.
	 * @param array  $anchors_by_id Existing anchors keyed by instance ID.
	 * @return string
	 */
	private static function unique_anchor_for_merge( $anchor_id, $instance_id, array $anchors_by_id ) {
		$anchor_id = Sanitizer::sanitize_anchor_id( $anchor_id );

		if ( '' === $anchor_id ) {
			return '';
		}

		foreach ( $anchors_by_id as $existing_id => $existing_anchor ) {
			if ( $instance_id === $existing_id ) {
				continue;
			}

			if ( $anchor_id === $existing_anchor ) {
				$suffix = 2;
				$base   = $anchor_id;

				do {
					$anchor_id = $base . '-' . $suffix;
					$suffix++;
				} while ( in_array( $anchor_id, $anchors_by_id, true ) );

				break;
			}
		}

		return $anchor_id;
	}

	/**
	 * Build report.
	 *
	 * @param array $preview Preview.
	 * @param array $state   State.
	 * @return array
	 */
	private static function migration_report( array $preview, array $state ) {
		return array(
			'generated_at' => Sanitizer::current_timestamp(),
			'preview'      => array_diff_key( $preview, array( 'components' => true ) ),
			'state'        => $state,
			'components'   => array_map(
				function ( $component ) {
					return array(
						'instance_id'    => $component['instance_id'],
						'component_type' => $component['component_type'],
						'admin_title'    => $component['admin_title'],
						'anchor_id'      => isset( $component['navigation']['anchor_id'] ) ? $component['navigation']['anchor_id'] : '',
					);
				},
				$preview['components']
			),
		);
	}

	/**
	 * Count mapped fields.
	 *
	 * @param array $components Components.
	 * @return array
	 */
	private static function count_component_fields( array $components ) {
		$count = 0;

		foreach ( $components as $component ) {
			foreach ( array( 'content', 'design', 'navigation', 'advanced' ) as $group ) {
				if ( ! empty( $component[ $group ] ) && is_array( $component[ $group ] ) ) {
					$count += count( $component[ $group ] );
				}
			}
		}

		return array(
			'migrated' => $count,
			'skipped'  => 0,
		);
	}

	/**
	 * Get unsupported legacy sections.
	 *
	 * @param array $options Options.
	 * @return array
	 */
	private static function unsupported_sections( array $options ) {
		$unsupported = array();

		foreach ( array( 'trust' ) as $section ) {
			if ( ! empty( $options[ $section . '_show' ] ) || ! empty( $options[ $section . '_items' ] ) ) {
				$unsupported[] = $section;
			}
		}

		return $unsupported;
	}

	/**
	 * Get theme version.
	 *
	 * @return string
	 */
	private static function theme_version() {
		return defined( 'NEXA_PRO_VERSION' ) ? NEXA_PRO_VERSION : '';
	}

	/**
	 * Sanitize state status.
	 *
	 * @param string $status Status.
	 * @return string
	 */
	private static function sanitize_status( $status ) {
		$status = \sanitize_key( $status );

		return in_array( $status, array( 'not_needed', 'available', 'previewed', 'completed', 'failed', 'rolled_back' ), true ) ? $status : 'not_needed';
	}

	private static function option_text( array $options, $key ) {
		return isset( $options[ $key ] ) && is_scalar( $options[ $key ] ) ? \sanitize_text_field( $options[ $key ] ) : '';
	}

	private static function option_absint( array $options, $key ) {
		return isset( $options[ $key ] ) ? absint( $options[ $key ] ) : 0;
	}

	private static function default_layout_for_type( $type ) {
		return Sanitizer::default_layout_for_type( $type );
	}

	private static function map_hero_layout( $layout ) {
		$layout = \sanitize_key( $layout );
		$map    = array(
			'content-only'     => 'centered',
			'image-left'       => 'split-left',
			'image-right'      => 'split-right',
			'background-image' => 'background-image',
		);

		return isset( $map[ $layout ] ) ? $map[ $layout ] : 'centered';
	}

	private static function component_title( $type, array $content ) {
		if ( ! empty( $content['heading'] ) ) {
			return \sanitize_text_field( $content['heading'] );
		}

		return Sanitizer::default_admin_title( $type );
	}

	private static function anchor_for_type( $type ) {
		if ( function_exists( 'nexa_pro_get_component_default_anchor' ) ) {
			$anchor = \nexa_pro_get_component_default_anchor( $type );

			if ( $anchor ) {
				return Sanitizer::sanitize_anchor_id( $anchor );
			}
		}

		return Sanitizer::sanitize_anchor_id( $type );
	}
}
