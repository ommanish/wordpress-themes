#!/usr/bin/env php
<?php
/**
 * Validate Nexa Pro Core storage behavior.
 *
 * @package Nexa_Pro_Core
 */

define( 'NEXA_PRO_CORE_TESTING', true );

$root_dir   = realpath( __DIR__ . '/../../..' );
$plugin_dir = realpath( __DIR__ . '/..' );
$wp_load    = getenv( 'NEXA_PRO_CORE_WP_LOAD' );
$failures   = array();
$cleanup    = array();

foreach ( $argv as $arg ) {
	if ( 0 === strpos( $arg, '--wp-load=' ) ) {
		$wp_load = substr( $arg, 10 );
	}
}

if ( $wp_load && file_exists( $wp_load ) ) {
	require_once $wp_load;
} else {
	nexa_pro_core_storage_validation_bootstrap_standalone_wordpress( $plugin_dir );
}

if ( ! defined( 'NEXA_PRO_CORE_VERSION' ) ) {
	require_once $plugin_dir . '/nexa-pro-core.php';
}

if ( class_exists( 'Nexa_Pro_Core\\Plugin' ) ) {
	Nexa_Pro_Core\Plugin::activate();
	Nexa_Pro_Core\Plugin::instance()->register();
}

if ( class_exists( 'Nexa_Pro_Core\\Reusable_Components' ) ) {
	Nexa_Pro_Core\Reusable_Components::register_post_type();
}

/**
 * Add a failure when a condition is false.
 *
 * @param bool   $condition Condition.
 * @param string $message   Failure message.
 * @return void
 */
function nexa_pro_core_storage_assert( $condition, $message ) {
	global $failures;

	if ( ! $condition ) {
		$failures[] = $message;
	}
}

/**
 * Create a temporary page.
 *
 * @param string $title Page title.
 * @return int
 */
function nexa_pro_core_storage_create_test_page( $title ) {
	global $cleanup;

	$page_id = wp_insert_post(
		array(
			'post_title'  => $title,
			'post_type'   => 'page',
			'post_status' => 'draft',
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
 * Build a valid component fixture.
 *
 * @param string $type  Component type.
 * @param int    $order Order.
 * @return array
 */
function nexa_pro_core_storage_component_fixture( $type = 'services', $order = 0 ) {
	return array(
		'component_type' => $type,
		'admin_title'    => ucfirst( $type ) . ' Component',
		'enabled'        => '1',
		'order'          => $order,
		'layout'         => 'default',
		'content'        => array(
			'heading' => 'Storage heading',
			'text'    => 'Storage text',
			'url'     => 'https://github.com/ommanish/wordpress-themes',
		),
		'design'         => array(
			'background_type'  => 'solid',
			'background_color' => '#ffffff',
			'overlay_opacity'  => 40,
			'text_theme'       => 'automatic',
		),
		'navigation'     => array(
			'show_in_navigation' => '1',
			'navigation_label'   => 'Storage Link',
			'anchor_id'          => 'storage-section-' . absint( $order ),
			'mobile_visibility'  => 'all',
		),
		'advanced'       => array(
			'custom_css_class' => 'storage-test',
			'aria_label'       => 'Storage component',
			'semantic_element' => 'section',
			'device_visibility' => 'all',
			'animation_preset' => 'none',
		),
	);
}

$args   = array( 'bypass_capability_check' => true );
$page_a = nexa_pro_core_storage_create_test_page( 'Nexa Storage Test A' );
$page_b = nexa_pro_core_storage_create_test_page( 'Nexa Storage Test B' );

nexa_pro_core_storage_assert( function_exists( 'nexa_pro_core_get_page_components' ), 'Plugin bootstrap should load helper APIs.' );
nexa_pro_core_storage_assert( '0.3.0' === NEXA_PRO_CORE_VERSION, 'Plugin version should be 0.3.0.' );
nexa_pro_core_storage_assert( 1 === NEXA_PRO_CORE_SCHEMA_VERSION, 'Schema version should be 1.' );
nexa_pro_core_storage_assert( '_nexa_pro_components' === NEXA_PRO_CORE_PAGE_META_KEY, 'Page meta key should match the contract.' );
nexa_pro_core_storage_assert( in_array( 'manage_nexa_pro_components', nexa_pro_core_get_capabilities(), true ), 'Required component capability should be registered.' );
nexa_pro_core_storage_assert( in_array( 'manage_nexa_pro_reusable_components', nexa_pro_core_get_capabilities(), true ), 'Required reusable capability should be registered.' );

$administrator = get_role( 'administrator' );
nexa_pro_core_storage_assert( $administrator && $administrator->has_cap( 'manage_nexa_pro_components' ), 'Administrator should receive component management capability.' );

$required_types = array( 'hero', 'about', 'services', 'features', 'process', 'why', 'portfolio', 'testimonials', 'team', 'faq', 'cta', 'contact' );

foreach ( $required_types as $required_type ) {
	nexa_pro_core_storage_assert( nexa_pro_core_component_type_is_allowed( $required_type ), "Required component type should be accepted: {$required_type}" );
}

nexa_pro_core_storage_assert( ! nexa_pro_core_component_type_is_allowed( 'unknown-type' ), 'Unknown component types should be rejected.' );

$first_id  = Nexa_Pro_Core\Sanitizer::generate_instance_id( 'services' );
$second_id = Nexa_Pro_Core\Sanitizer::generate_instance_id( 'services' );
nexa_pro_core_storage_assert( $first_id !== $second_id, 'Generated instance IDs should be unique.' );

$stable_id = 'nexa_services_abc123def4';
$stable    = Nexa_Pro_Core\Sanitizer::sanitize_component_instance(
	array_merge(
		nexa_pro_core_storage_component_fixture( 'services' ),
		array( 'instance_id' => $stable_id )
	)
);
nexa_pro_core_storage_assert( ! is_wp_error( $stable ) && $stable_id === $stable['instance_id'], 'Existing valid instance IDs should remain stable.' );

$invalid_page = nexa_pro_core_get_page_components( 99999999 );
nexa_pro_core_storage_assert( is_wp_error( $invalid_page ), 'Invalid page IDs should be rejected.' );

$added = nexa_pro_core_add_page_component( $page_a, nexa_pro_core_storage_component_fixture( 'services', 0 ), $args );
nexa_pro_core_storage_assert( ! is_wp_error( $added ), 'Page component should be added.' );
nexa_pro_core_storage_assert( isset( $added['instance_id'] ) && Nexa_Pro_Core\Sanitizer::is_valid_instance_id( $added['instance_id'] ), 'Added component should receive a valid instance ID.' );

$read = nexa_pro_core_get_page_components( $page_a );
nexa_pro_core_storage_assert( is_array( $read ) && 1 === count( $read ), 'Read helper should return normalized component data.' );

$duplicate = nexa_pro_core_duplicate_page_component( $page_a, $added['instance_id'], $args );
nexa_pro_core_storage_assert( ! is_wp_error( $duplicate ), 'Duplicate component should succeed.' );
nexa_pro_core_storage_assert( $duplicate['instance_id'] !== $added['instance_id'], 'Duplicate component should receive a new instance ID.' );

$after_duplicate = nexa_pro_core_get_page_components( $page_a );
nexa_pro_core_storage_assert( 2 === count( $after_duplicate ), 'Page should contain two components after duplication.' );
nexa_pro_core_storage_assert( array( 0, 1 ) === wp_list_pluck( $after_duplicate, 'order' ), 'Component order should be contiguous after duplication.' );

$duplicate_id_components = array(
	array_merge( nexa_pro_core_storage_component_fixture( 'about', 1 ), array( 'instance_id' => $stable_id, 'navigation' => array( 'anchor_id' => 'one' ) ) ),
	array_merge( nexa_pro_core_storage_component_fixture( 'about', 2 ), array( 'instance_id' => $stable_id, 'navigation' => array( 'anchor_id' => 'two' ) ) ),
);
$duplicate_id_saved      = nexa_pro_core_save_page_components( $page_b, $duplicate_id_components, $args );
nexa_pro_core_storage_assert( ! is_wp_error( $duplicate_id_saved ), 'Duplicate IDs should be normalized safely during save.' );
nexa_pro_core_storage_assert( 2 === count( array_unique( wp_list_pluck( $duplicate_id_saved, 'instance_id' ) ) ), 'Normalized page data should not contain duplicate instance IDs.' );

$ordered_ids = array_reverse( wp_list_pluck( $after_duplicate, 'instance_id' ) );
$reordered   = nexa_pro_core_reorder_page_components( $page_a, $ordered_ids, $args );
nexa_pro_core_storage_assert( ! is_wp_error( $reordered ), 'Reorder should succeed with a complete ID list.' );
nexa_pro_core_storage_assert( $ordered_ids === wp_list_pluck( $reordered, 'instance_id' ), 'Reorder should preserve submitted order.' );

$bad_reorder = nexa_pro_core_reorder_page_components( $page_a, array( $ordered_ids[0], 'missing-id' ), $args );
nexa_pro_core_storage_assert( is_wp_error( $bad_reorder ), 'Reorder should reject missing or unknown IDs.' );

$moved = nexa_pro_core_move_page_component( $page_a, $page_b, $ordered_ids[0], $args );
nexa_pro_core_storage_assert( ! is_wp_error( $moved ), 'Move between pages should succeed.' );
nexa_pro_core_storage_assert( 'services' === $moved['component_type'], 'Move should preserve component data.' );

$page_a_count = nexa_pro_core_count_page_components( $page_a );
$page_b_count = nexa_pro_core_count_page_components( $page_b );
nexa_pro_core_storage_assert( 1 === $page_a_count && 3 === $page_b_count, 'Move should update source and target page counts.' );

$unsafe = Nexa_Pro_Core\Sanitizer::sanitize_component_instance(
	array_merge(
		nexa_pro_core_storage_component_fixture( 'features' ),
		array(
			'content'    => array(
				'heading' => '<script>alert(1)</script><strong>Safe</strong>',
				'url'     => 'javascript:alert(1)',
			),
			'design'     => array(
				'background_color' => '#000000',
				'raw_css'          => 'body{display:none}',
			),
			'navigation' => array(
				'anchor_id' => 'Unsafe Anchor!!!',
			),
			'advanced'   => array(
				'custom_css_class' => 'safe-class <bad>',
				'raw_script'       => 'alert(1)',
			),
		)
	)
);
nexa_pro_core_storage_assert( ! is_wp_error( $unsafe ), 'Unsafe component fixture should sanitize rather than fatal.' );
nexa_pro_core_storage_assert( false === strpos( $unsafe['content']['heading'], '<script' ), 'Unsafe script tags should be stripped from content.' );
nexa_pro_core_storage_assert( '' === $unsafe['content']['url'], 'JavaScript URLs should be rejected.' );
nexa_pro_core_storage_assert( ! isset( $unsafe['design']['raw_css'] ), 'Arbitrary CSS should be rejected from design settings.' );
nexa_pro_core_storage_assert( ! isset( $unsafe['advanced']['raw_script'] ), 'Arbitrary scripts should be rejected from advanced settings.' );
nexa_pro_core_storage_assert( 'unsafe-anchor' === $unsafe['navigation']['anchor_id'], 'Navigation anchors should be sanitized.' );

$duplicate_anchor = nexa_pro_core_save_page_components(
	$page_a,
	array(
		array_merge( nexa_pro_core_storage_component_fixture( 'services', 0 ), array( 'navigation' => array( 'anchor_id' => 'duplicate' ) ) ),
		array_merge( nexa_pro_core_storage_component_fixture( 'features', 1 ), array( 'navigation' => array( 'anchor_id' => 'duplicate' ) ) ),
	),
	$args
);
nexa_pro_core_storage_assert( is_wp_error( $duplicate_anchor ), 'Duplicate anchors should be prevented within a page.' );

$reusable_id = nexa_pro_core_create_reusable_component(
	array_merge(
		nexa_pro_core_storage_component_fixture( 'cta' ),
		array(
			'admin_title' => 'Reusable CTA',
			'navigation'  => array( 'anchor_id' => 'reusable-cta' ),
		)
	),
	$args
);
nexa_pro_core_storage_assert( ! is_wp_error( $reusable_id ) && $reusable_id > 0, 'Reusable component creation should work.' );

$reusable = nexa_pro_core_get_reusable_component( $reusable_id );
nexa_pro_core_storage_assert( ! is_wp_error( $reusable ) && 'cta' === $reusable['component_type'], 'Reusable component read should work.' );

$updated_reusable = nexa_pro_core_update_reusable_component( $reusable_id, array( 'admin_title' => 'Updated Reusable CTA' ), $args );
nexa_pro_core_storage_assert( ! is_wp_error( $updated_reusable ) && 'Updated Reusable CTA' === $updated_reusable['admin_title'], 'Reusable component update should work.' );

$linked = nexa_pro_core_add_page_component(
	$page_a,
	array_merge(
		nexa_pro_core_storage_component_fixture( 'cta', 4 ),
		array(
			'inheritance_mode'      => 'linked',
			'reusable_component_id' => $reusable_id,
			'navigation'            => array( 'anchor_id' => 'linked-cta' ),
		)
	),
	$args
);
nexa_pro_core_storage_assert( ! is_wp_error( $linked ), 'Linked page component should be addable.' );
nexa_pro_core_storage_assert( 1 === nexa_pro_core_get_reusable_usage_count( $reusable_id ), 'Linked usage count should find page usage.' );

$resolved = nexa_pro_core_resolve_component_instance( $linked );
nexa_pro_core_storage_assert( ! is_wp_error( $resolved ) && $reusable_id === $resolved['resolved_from_id'], 'Linked component should resolve reusable data.' );

$recursive = nexa_pro_core_update_reusable_component(
	$reusable_id,
	array(
		'inheritance_mode'      => 'linked',
		'reusable_component_id' => $reusable_id,
	),
	$args
);
nexa_pro_core_storage_assert( is_wp_error( $recursive ), 'Self-referential reusable components should be rejected.' );

$detached = nexa_pro_core_detach_reusable_component( $page_a, $linked['instance_id'], $args );
nexa_pro_core_storage_assert( ! is_wp_error( $detached ), 'Detach should create an independent local copy.' );
nexa_pro_core_storage_assert( 'local' === $detached['inheritance_mode'] && 0 === $detached['reusable_component_id'], 'Detached component should be local.' );

$schema_before = nexa_pro_core_get_stored_schema_version();
Nexa_Pro_Core\Schema::initialize();
$schema_after = nexa_pro_core_get_stored_schema_version();
nexa_pro_core_storage_assert( $schema_before === $schema_after && 1 === $schema_after, 'Schema initialization should be idempotent.' );

$post_type = get_post_type_object( NEXA_PRO_CORE_REUSABLE_POST_TYPE );
nexa_pro_core_storage_assert( $post_type && false === $post_type->public, 'Reusable component CPT should not be public.' );
nexa_pro_core_storage_assert( $post_type && false === $post_type->publicly_queryable, 'Reusable component CPT should not be publicly queryable.' );
nexa_pro_core_storage_assert( $post_type && false === $post_type->rewrite, 'Reusable component CPT should not register public rewrite rules.' );

foreach ( $cleanup as $post_id ) {
	wp_delete_post( $post_id, true );
}

if ( $failures ) {
	foreach ( $failures as $failure ) {
		fwrite( STDERR, "FAIL: {$failure}\n" );
	}

	exit( 1 );
}

echo "PASS: Nexa Pro Core storage validation completed successfully.\n";

/**
 * Bootstrap enough WordPress-like behavior for standalone validation.
 *
 * @param string $plugin_dir Plugin directory.
 * @return void
 */
function nexa_pro_core_storage_validation_bootstrap_standalone_wordpress( $plugin_dir ) {
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', dirname( $plugin_dir, 2 ) . '/' );
	}

	$GLOBALS['nexa_pro_core_test_posts']      = array();
	$GLOBALS['nexa_pro_core_test_post_meta']  = array();
	$GLOBALS['nexa_pro_core_test_options']    = array();
	$GLOBALS['nexa_pro_core_test_post_types'] = array();
	$GLOBALS['nexa_pro_core_test_roles']      = array();
	$GLOBALS['nexa_pro_core_test_hooks']      = array();
	$GLOBALS['nexa_pro_core_test_next_id']    = 1000;

	if ( ! class_exists( 'WP_Error' ) ) {
		class WP_Error {
			/**
			 * Error code.
			 *
			 * @var string
			 */
			public $code;

			/**
			 * Error message.
			 *
			 * @var string
			 */
			public $message;

			/**
			 * Constructor.
			 *
			 * @param string $code    Code.
			 * @param string $message Message.
			 */
			public function __construct( $code = '', $message = '' ) {
				$this->code    = $code;
				$this->message = $message;
			}

			/**
			 * Get error code.
			 *
			 * @return string
			 */
			public function get_error_code() {
				return $this->code;
			}

			/**
			 * Get error message.
			 *
			 * @return string
			 */
			public function get_error_message() {
				return $this->message;
			}
		}
	}

	if ( ! class_exists( 'Nexa_Pro_Core_Test_Role' ) ) {
		class Nexa_Pro_Core_Test_Role {
			/**
			 * Capabilities.
			 *
			 * @var array
			 */
			public $caps = array();

			/**
			 * Add a capability.
			 *
			 * @param string $cap Capability.
			 * @return void
			 */
			public function add_cap( $cap ) {
				$this->caps[ $cap ] = true;
			}

			/**
			 * Check a capability.
			 *
			 * @param string $cap Capability.
			 * @return bool
			 */
			public function has_cap( $cap ) {
				return ! empty( $this->caps[ $cap ] );
			}
		}
	}

	$GLOBALS['nexa_pro_core_test_roles']['administrator'] = new Nexa_Pro_Core_Test_Role();

	if ( ! function_exists( 'is_wp_error' ) ) {
		function is_wp_error( $thing ) {
			return $thing instanceof WP_Error;
		}
	}

	if ( ! function_exists( '__' ) ) {
		function __( $text ) {
			return $text;
		}
	}

	if ( ! function_exists( 'esc_html__' ) ) {
		function esc_html__( $text ) {
			return $text;
		}
	}

	if ( ! function_exists( 'esc_html' ) ) {
		function esc_html( $text ) {
			return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
		}
	}

	if ( ! function_exists( 'add_action' ) ) {
		function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
			return add_filter( $hook, $callback, $priority, $accepted_args );
		}
	}

	if ( ! function_exists( 'add_filter' ) ) {
		function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
			$GLOBALS['nexa_pro_core_test_hooks'][ $hook ][ $priority ][] = array(
				'callback'      => $callback,
				'accepted_args' => $accepted_args,
			);

			return true;
		}
	}

	if ( ! function_exists( 'apply_filters' ) ) {
		function apply_filters( $hook, $value, ...$args ) {
			if ( empty( $GLOBALS['nexa_pro_core_test_hooks'][ $hook ] ) ) {
				return $value;
			}

			ksort( $GLOBALS['nexa_pro_core_test_hooks'][ $hook ] );

			foreach ( $GLOBALS['nexa_pro_core_test_hooks'][ $hook ] as $callbacks ) {
				foreach ( $callbacks as $registered ) {
					$value = call_user_func_array( $registered['callback'], array_slice( array_merge( array( $value ), $args ), 0, $registered['accepted_args'] ) );
				}
			}

			return $value;
		}
	}

	if ( ! function_exists( 'do_action' ) ) {
		function do_action( $hook, ...$args ) {
			apply_filters( $hook, null, ...$args );
		}
	}

	if ( ! function_exists( 'register_activation_hook' ) ) {
		function register_activation_hook( $file = '', $callback = null ) {
			return true;
		}
	}

	if ( ! function_exists( 'register_deactivation_hook' ) ) {
		function register_deactivation_hook( $file = '', $callback = null ) {
			return true;
		}
	}

	if ( ! function_exists( 'plugin_dir_path' ) ) {
		function plugin_dir_path( $file ) {
			return rtrim( dirname( $file ), '/' ) . '/';
		}
	}

	if ( ! function_exists( 'plugin_dir_url' ) ) {
		function plugin_dir_url( $file = '' ) {
			return '';
		}
	}

	if ( ! function_exists( 'plugin_basename' ) ) {
		function plugin_basename( $file ) {
			return basename( dirname( $file ) ) . '/' . basename( $file );
		}
	}

	if ( ! function_exists( 'load_plugin_textdomain' ) ) {
		function load_plugin_textdomain( $domain = '', $deprecated = false, $plugin_rel_path = false ) {
			return true;
		}
	}

	if ( ! function_exists( 'get_role' ) ) {
		function get_role( $role ) {
			return isset( $GLOBALS['nexa_pro_core_test_roles'][ $role ] ) ? $GLOBALS['nexa_pro_core_test_roles'][ $role ] : null;
		}
	}

	if ( ! function_exists( 'current_user_can' ) ) {
		function current_user_can() {
			return true;
		}
	}

	if ( ! function_exists( 'wp_verify_nonce' ) ) {
		function wp_verify_nonce() {
			return true;
		}
	}

	if ( ! function_exists( 'sanitize_key' ) ) {
		function sanitize_key( $key ) {
			return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) );
		}
	}

	if ( ! function_exists( 'sanitize_text_field' ) ) {
		function sanitize_text_field( $text ) {
			return trim( preg_replace( '/[\r\n\t ]+/', ' ', wp_strip_all_tags( (string) $text ) ) );
		}
	}

	if ( ! function_exists( 'sanitize_html_class' ) ) {
		function sanitize_html_class( $class ) {
			return preg_replace( '/[^A-Za-z0-9_\-]/', '', (string) $class );
		}
	}

	if ( ! function_exists( 'wp_strip_all_tags' ) ) {
		function wp_strip_all_tags( $text ) {
			return strip_tags( $text );
		}
	}

	if ( ! function_exists( 'wp_kses_post' ) ) {
		function wp_kses_post( $text ) {
			$text = preg_replace( '#<script\b[^>]*>.*?</script>#is', '', (string) $text );
			$text = preg_replace( '#<style\b[^>]*>.*?</style>#is', '', $text );
			$text = preg_replace( '/\son[a-z]+\s*=\s*(["\']).*?\1/i', '', $text );
			$text = preg_replace( '/javascript\s*:/i', '', $text );

			return $text;
		}
	}

	if ( ! function_exists( 'sanitize_hex_color' ) ) {
		function sanitize_hex_color( $color ) {
			return is_string( $color ) && preg_match( '/^#[0-9a-fA-F]{6}$/', $color ) ? strtolower( $color ) : '';
		}
	}

	if ( ! function_exists( 'esc_url_raw' ) ) {
		function esc_url_raw( $url ) {
			$url = trim( (string) $url );

			if ( preg_match( '/^\s*javascript:/i', $url ) ) {
				return '';
			}

			return filter_var( $url, FILTER_VALIDATE_URL ) ? $url : '';
		}
	}

	if ( ! function_exists( 'absint' ) ) {
		function absint( $value ) {
			return abs( (int) $value );
		}
	}

	if ( ! function_exists( 'wp_generate_password' ) ) {
		function wp_generate_password( $length = 12 ) {
			return substr( bin2hex( random_bytes( max( 1, (int) ceil( $length / 2 ) ) ) ), 0, $length );
		}
	}

	if ( ! function_exists( 'wp_json_encode' ) ) {
		function wp_json_encode( $data ) {
			return json_encode( $data );
		}
	}

	if ( ! function_exists( 'wp_list_pluck' ) ) {
		function wp_list_pluck( $list, $field ) {
			$values = array();

			foreach ( $list as $item ) {
				if ( is_array( $item ) && isset( $item[ $field ] ) ) {
					$values[] = $item[ $field ];
				} elseif ( is_object( $item ) && isset( $item->{$field} ) ) {
					$values[] = $item->{$field};
				}
			}

			return $values;
		}
	}

	if ( ! function_exists( 'register_post_type' ) ) {
		function register_post_type( $post_type, $args ) {
			$args                              = (object) $args;
			$args->name                        = $post_type;
			$GLOBALS['nexa_pro_core_test_post_types'][ $post_type ] = $args;

			return $args;
		}
	}

	if ( ! function_exists( 'get_post_type_object' ) ) {
		function get_post_type_object( $post_type ) {
			return isset( $GLOBALS['nexa_pro_core_test_post_types'][ $post_type ] ) ? $GLOBALS['nexa_pro_core_test_post_types'][ $post_type ] : null;
		}
	}

	if ( ! function_exists( 'wp_insert_post' ) ) {
		function wp_insert_post( $postarr, $wp_error = false ) {
			$id = ++$GLOBALS['nexa_pro_core_test_next_id'];

			$GLOBALS['nexa_pro_core_test_posts'][ $id ] = (object) array(
				'ID'          => $id,
				'post_title'  => isset( $postarr['post_title'] ) ? $postarr['post_title'] : '',
				'post_type'   => isset( $postarr['post_type'] ) ? $postarr['post_type'] : 'post',
				'post_status' => isset( $postarr['post_status'] ) ? $postarr['post_status'] : 'draft',
			);

			return $id;
		}
	}

	if ( ! function_exists( 'get_post' ) ) {
		function get_post( $post_id ) {
			$post_id = absint( $post_id );

			return isset( $GLOBALS['nexa_pro_core_test_posts'][ $post_id ] ) ? $GLOBALS['nexa_pro_core_test_posts'][ $post_id ] : null;
		}
	}

	if ( ! function_exists( 'wp_update_post' ) ) {
		function wp_update_post( $postarr, $wp_error = false ) {
			$id = isset( $postarr['ID'] ) ? absint( $postarr['ID'] ) : 0;

			if ( ! isset( $GLOBALS['nexa_pro_core_test_posts'][ $id ] ) ) {
				return new WP_Error( 'missing_post', 'Missing post.' );
			}

			foreach ( $postarr as $key => $value ) {
				if ( 'ID' !== $key ) {
					$GLOBALS['nexa_pro_core_test_posts'][ $id ]->{$key} = $value;
				}
			}

			return $id;
		}
	}

	if ( ! function_exists( 'wp_delete_post' ) ) {
		function wp_delete_post( $post_id, $force_delete = false ) {
			$post_id = absint( $post_id );
			unset( $GLOBALS['nexa_pro_core_test_posts'][ $post_id ], $GLOBALS['nexa_pro_core_test_post_meta'][ $post_id ] );

			return true;
		}
	}

	if ( ! function_exists( 'get_posts' ) ) {
		function get_posts( $args ) {
			$ids = array();

			foreach ( $GLOBALS['nexa_pro_core_test_posts'] as $id => $post ) {
				if ( isset( $args['post_type'] ) && $post->post_type !== $args['post_type'] ) {
					continue;
				}

				if ( isset( $args['meta_key'] ) && empty( $GLOBALS['nexa_pro_core_test_post_meta'][ $id ][ $args['meta_key'] ] ) ) {
					continue;
				}

				$ids[] = 'ids' === ( isset( $args['fields'] ) ? $args['fields'] : '' ) ? $id : $post;
			}

			return $ids;
		}
	}

	if ( ! function_exists( 'get_post_meta' ) ) {
		function get_post_meta( $post_id, $key, $single = false ) {
			$post_id = absint( $post_id );

			if ( ! isset( $GLOBALS['nexa_pro_core_test_post_meta'][ $post_id ][ $key ] ) ) {
				return $single ? '' : array();
			}

			return $GLOBALS['nexa_pro_core_test_post_meta'][ $post_id ][ $key ];
		}
	}

	if ( ! function_exists( 'update_post_meta' ) ) {
		function update_post_meta( $post_id, $key, $value ) {
			$post_id = absint( $post_id );
			$GLOBALS['nexa_pro_core_test_post_meta'][ $post_id ][ $key ] = $value;

			return true;
		}
	}

	if ( ! function_exists( 'get_option' ) ) {
		function get_option( $key, $default = false ) {
			return array_key_exists( $key, $GLOBALS['nexa_pro_core_test_options'] ) ? $GLOBALS['nexa_pro_core_test_options'][ $key ] : $default;
		}
	}

	if ( ! function_exists( 'update_option' ) ) {
		function update_option( $key, $value ) {
			$GLOBALS['nexa_pro_core_test_options'][ $key ] = $value;

			return true;
		}
	}
}
