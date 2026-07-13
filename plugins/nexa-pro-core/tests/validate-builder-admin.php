#!/usr/bin/env php
<?php
/**
 * Validate Nexa Pro Core builder admin behavior.
 *
 * @package Nexa_Pro_Core
 */

define( 'NEXA_PRO_CORE_TESTING', true );

$plugin_dir = realpath( __DIR__ . '/..' );
$wp_load    = getenv( 'NEXA_PRO_CORE_WP_LOAD' );
$failures   = array();
$cleanup    = array();
$run_id     = substr( md5( microtime( true ) . getmypid() ), 0, 8 );

foreach ( $argv as $arg ) {
	if ( 0 === strpos( $arg, '--wp-load=' ) ) {
		$wp_load = substr( $arg, 10 );
	}
}

if ( $wp_load && file_exists( $wp_load ) ) {
	require_once $wp_load;

	if ( function_exists( 'get_users' ) && function_exists( 'wp_set_current_user' ) ) {
		$admins = get_users(
			array(
				'role'   => 'administrator',
				'number' => 1,
				'fields' => 'ID',
			)
		);

		if ( $admins ) {
			wp_set_current_user( absint( $admins[0] ) );
		}
	}
} else {
	nexa_pro_core_builder_validation_bootstrap_standalone_wordpress( $plugin_dir );
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
 * Add a validation failure when a condition is false.
 *
 * @param bool   $condition Condition.
 * @param string $message   Message.
 * @return void
 */
function nexa_pro_core_builder_assert( $condition, $message ) {
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
function nexa_pro_core_builder_create_test_page( $title ) {
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
 * Build component form values.
 *
 * @param string $type   Component type.
 * @param string $anchor Anchor.
 * @param string $title  Title.
 * @return array
 */
function nexa_pro_core_builder_component_values( $type = 'hero', $anchor = 'builder-hero', $title = 'Builder Hero' ) {
	$layout = 'hero' === $type ? 'content-only' : 'card-grid';

	if ( in_array( $type, array( 'cta', 'contact' ), true ) ) {
		$layout = 'centered';
	}

	if ( 'faq' === $type ) {
		$layout = 'accordion';
	}

	return array(
		'component_type' => $type,
		'admin_title'    => $title,
		'enabled'        => '1',
		'layout'         => $layout,
		'content'        => array(
			'eyebrow'           => 'Builder',
			'heading'           => 'Builder Heading',
			'subtitle'          => 'Builder Subtitle',
			'body'              => 'Builder body text.',
			'primary_cta_label' => 'Start',
			'primary_cta_url'   => 'https://github.com/ommanish/wordpress-themes',
			'desktop_image_id'  => '12',
		),
		'design'         => array(
			'background_type'   => 'solid',
			'background_color'  => '#ffffff',
			'gradient_direction' => 'to-bottom',
			'overlay_enabled'   => '0',
			'overlay_opacity'   => '20',
			'text_theme'        => 'automatic',
			'content_alignment' => 'left',
			'media_position'    => 'right',
			'container_width'   => 'default',
			'column_count'      => '3',
			'card_style'        => 'default',
			'section_spacing'   => 'default',
		),
		'navigation'     => array(
			'show_in_navigation' => '1',
			'navigation_label'   => $title,
			'anchor_id'          => $anchor,
			'highlight_as_cta'   => '0',
			'mobile_visibility'  => 'all',
		),
		'advanced'       => array(
			'custom_css_class' => 'builder-test',
			'aria_label'       => 'Builder component',
			'semantic_element' => 'section',
			'device_visibility' => 'all',
			'animation_preset' => 'none',
		),
	);
}

/**
 * Post a builder action.
 *
 * @param string $operation Operation.
 * @param int    $page_id   Page ID.
 * @param array  $extra     Extra POST values.
 * @param bool   $valid     Whether the nonce is valid.
 * @return string
 */
function nexa_pro_core_builder_post_action( $operation, $page_id, array $extra = array(), $valid = true ) {
	$_POST = array_merge(
		array(
			'builder_page_id' => $page_id,
			'_wpnonce'        => $valid ? wp_create_nonce( Nexa_Pro_Core\Builder_Actions::nonce_action( $operation, $page_id ) ) : 'invalid',
		),
		$extra
	);

	switch ( $operation ) {
		case 'save_component':
			return Nexa_Pro_Core\Builder_Actions::handle_save_component();
		case 'duplicate_component':
			return Nexa_Pro_Core\Builder_Actions::handle_duplicate_component();
		case 'toggle_component':
			return Nexa_Pro_Core\Builder_Actions::handle_toggle_component();
		case 'move_order':
			return Nexa_Pro_Core\Builder_Actions::handle_move_order();
		case 'save_order':
			return Nexa_Pro_Core\Builder_Actions::handle_save_order();
		case 'move_page':
			return Nexa_Pro_Core\Builder_Actions::handle_move_page();
		case 'delete_component':
			return Nexa_Pro_Core\Builder_Actions::handle_delete_component();
		case 'undo_delete':
			return Nexa_Pro_Core\Builder_Actions::handle_undo_delete();
	}

	return '';
}

do_action( 'admin_menu' );

nexa_pro_core_builder_assert( '0.3.0' === NEXA_PRO_CORE_VERSION, 'Plugin version should be 0.3.0.' );
nexa_pro_core_builder_assert( function_exists( 'nexa_pro_core_get_renderable_page_components' ), 'Renderable page component helper should exist.' );
nexa_pro_core_builder_assert( function_exists( 'nexa_pro_core_has_builder_components' ), 'Builder component presence helper should exist.' );

if ( isset( $GLOBALS['nexa_pro_core_test_theme_pages'] ) ) {
	$menu = end( $GLOBALS['nexa_pro_core_test_theme_pages'] );
	nexa_pro_core_builder_assert( $menu && 'manage_nexa_pro_components' === $menu['capability'], 'Builder menu should use the component capability.' );
	nexa_pro_core_builder_assert( 'nexa-pro-builder' === $menu['menu_slug'], 'Builder menu slug should be stable.' );

	Nexa_Pro_Core\Builder_Admin::enqueue_assets( 'not-the-builder-screen' );
	nexa_pro_core_builder_assert( empty( $GLOBALS['nexa_pro_core_test_enqueued_scripts'] ), 'Builder assets should not enqueue on unrelated screens.' );

	Nexa_Pro_Core\Builder_Admin::enqueue_assets( $menu['hook'] );
	nexa_pro_core_builder_assert( in_array( 'nexa-pro-core-builder-admin', $GLOBALS['nexa_pro_core_test_enqueued_scripts'], true ), 'Builder script should enqueue on the builder screen.' );
	nexa_pro_core_builder_assert( in_array( 'nexa-pro-core-builder-admin', $GLOBALS['nexa_pro_core_test_enqueued_styles'], true ), 'Builder style should enqueue on the builder screen.' );
}

$page_a_title = 'Nexa Builder Test A ' . $run_id;
$page_b_title = 'Nexa Builder Test B ' . $run_id;
$page_a       = nexa_pro_core_builder_create_test_page( $page_a_title );
$page_b       = nexa_pro_core_builder_create_test_page( $page_b_title );

nexa_pro_core_builder_assert( $page_a > 0 && $page_b > 0, 'Temporary builder test pages should be created.' );

$pages = Nexa_Pro_Core\Builder_Admin::get_editable_pages(
	array(
		'search'     => $page_a_title,
		'status'     => 'draft',
		'configured' => '',
	)
);
nexa_pro_core_builder_assert( in_array( $page_a, wp_list_pluck( $pages, 'ID' ), true ), 'Page list filtering should find the selected draft page.' );

$add_url = nexa_pro_core_builder_post_action(
	'save_component',
	$page_a,
	array( 'component' => nexa_pro_core_builder_component_values( 'hero', 'builder-hero', 'Builder Hero' ) )
);
$components = nexa_pro_core_get_page_components( $page_a );
nexa_pro_core_builder_assert( false !== strpos( $add_url, 'component_added' ), 'Add flow should redirect with a success notice.' );
nexa_pro_core_builder_assert( is_array( $components ) && 1 === count( $components ), 'Add flow should store one component.' );

$hero_id = $components[0]['instance_id'];
nexa_pro_core_builder_assert( 'Builder Hero' === $components[0]['admin_title'], 'Add flow should preserve the admin title.' );

$edit_values                         = nexa_pro_core_builder_component_values( 'hero', 'builder-hero', 'Builder Hero Updated' );
$edit_values['instance_id']          = $hero_id;
$edit_values['content']['heading']   = 'Updated Heading';
$edit_values['design']['card_style'] = 'elevated';
nexa_pro_core_builder_post_action( 'save_component', $page_a, array( 'component' => $edit_values ) );
$components = nexa_pro_core_get_page_components( $page_a );
nexa_pro_core_builder_assert( 'Builder Hero Updated' === $components[0]['admin_title'], 'Edit flow should update the component title.' );
nexa_pro_core_builder_assert( 'Updated Heading' === $components[0]['content']['heading'], 'Edit flow should update content fields.' );
nexa_pro_core_builder_assert( 'elevated' === $components[0]['design']['card_style'], 'Edit flow should preserve safe design fields.' );

nexa_pro_core_builder_post_action(
	'save_component',
	$page_a,
	array( 'component' => nexa_pro_core_builder_component_values( 'services', 'builder-services', 'Builder Services' ) )
);
$components = nexa_pro_core_get_page_components( $page_a );
nexa_pro_core_builder_assert( 2 === count( $components ), 'Second component should be addable.' );

$duplicate_url = nexa_pro_core_builder_post_action(
	'duplicate_component',
	$page_a,
	array( 'component_id' => $components[0]['instance_id'] )
);
$components    = nexa_pro_core_get_page_components( $page_a );
$anchors       = wp_list_pluck( wp_list_pluck( $components, 'navigation' ), 'anchor_id' );
nexa_pro_core_builder_assert( false !== strpos( $duplicate_url, 'component_duplicated' ), 'Duplicate flow should redirect with success.' );
nexa_pro_core_builder_assert( 3 === count( $components ), 'Duplicate flow should add a component.' );
nexa_pro_core_builder_assert( 3 === count( array_unique( wp_list_pluck( $components, 'instance_id' ) ) ), 'Duplicate flow should create a new instance ID.' );
nexa_pro_core_builder_assert( 3 === count( array_unique( $anchors ) ), 'Duplicate flow should create a unique anchor.' );

$disable_id = $components[0]['instance_id'];
nexa_pro_core_builder_post_action(
	'toggle_component',
	$page_a,
	array(
		'component_id' => $disable_id,
		'enabled'      => '0',
	)
);
$components = nexa_pro_core_get_page_components( $page_a );
nexa_pro_core_builder_assert( empty( $components[0]['enabled'] ), 'Disable flow should persist disabled state.' );

$renderable = nexa_pro_core_get_renderable_page_components( $page_a );
nexa_pro_core_builder_assert( count( $renderable ) === count( $components ) - 1, 'Renderable helper should exclude disabled components.' );
nexa_pro_core_builder_assert( nexa_pro_core_has_builder_components( $page_a ), 'Builder presence helper should detect stored components.' );

nexa_pro_core_builder_post_action(
	'toggle_component',
	$page_a,
	array(
		'component_id' => $disable_id,
		'enabled'      => '1',
	)
);
$components = nexa_pro_core_get_page_components( $page_a );
nexa_pro_core_builder_assert( ! empty( $components[0]['enabled'] ), 'Enable flow should persist enabled state.' );

$first_order = wp_list_pluck( $components, 'instance_id' );
nexa_pro_core_builder_post_action(
	'move_order',
	$page_a,
	array(
		'component_id' => $first_order[0],
		'direction'    => 'down',
	)
);
$components = nexa_pro_core_get_page_components( $page_a );
nexa_pro_core_builder_assert( $first_order[0] === $components[1]['instance_id'], 'Move down should reorder components.' );

$reversed = array_reverse( wp_list_pluck( $components, 'instance_id' ) );
nexa_pro_core_builder_post_action( 'save_order', $page_a, array( 'ordered_ids' => $reversed ) );
$components = nexa_pro_core_get_page_components( $page_a );
nexa_pro_core_builder_assert( $reversed === wp_list_pluck( $components, 'instance_id' ), 'Save order should preserve submitted array order.' );

$move_id = $components[0]['instance_id'];
nexa_pro_core_builder_post_action(
	'move_page',
	$page_a,
	array(
		'component_id'    => $move_id,
		'target_page_id' => $page_b,
	)
);
$page_a_components = nexa_pro_core_get_page_components( $page_a );
$page_b_components = nexa_pro_core_get_page_components( $page_b );
nexa_pro_core_builder_assert( 2 === count( $page_a_components ) && 1 === count( $page_b_components ), 'Move to page should update source and target counts.' );
nexa_pro_core_builder_assert( $move_id === $page_b_components[0]['instance_id'], 'Move to page should preserve ID when no collision exists.' );

$invalid_layout = nexa_pro_core_builder_component_values( 'hero', 'builder-invalid-layout', 'Invalid Layout' );
$invalid_layout['layout'] = 'unsupported-layout';
$invalid_layout_url       = nexa_pro_core_builder_post_action( 'save_component', $page_a, array( 'component' => $invalid_layout ) );
nexa_pro_core_builder_assert( false !== strpos( $invalid_layout_url, 'nexa_pro_core_invalid_layout' ), 'Builder should reject unsupported layouts.' );

$unsafe = nexa_pro_core_builder_component_values( 'cta', 'builder-unsafe', 'Unsafe Component' );
$unsafe['content']['heading']         = '<script>window.bad=1</script>Safe heading';
$unsafe['content']['primary_cta_url'] = 'javascript:alert(1)';
nexa_pro_core_builder_post_action( 'save_component', $page_a, array( 'component' => $unsafe ) );
$page_a_components = nexa_pro_core_get_page_components( $page_a );
$unsafe_saved      = end( $page_a_components );
nexa_pro_core_builder_assert( false === strpos( $unsafe_saved['content']['heading'], '<script' ), 'Builder save should strip unsafe script markup.' );
nexa_pro_core_builder_assert( '' === $unsafe_saved['content']['primary_cta_url'], 'Builder save should reject JavaScript URLs.' );

$page_a_components = nexa_pro_core_get_page_components( $page_a );
$existing_anchor   = '';

foreach ( $page_a_components as $page_a_component ) {
	if ( ! empty( $page_a_component['navigation']['anchor_id'] ) ) {
		$existing_anchor = $page_a_component['navigation']['anchor_id'];
		break;
	}
}

$existing_anchor   = '' !== $existing_anchor ? $existing_anchor : 'builder-hero';
$duplicate_anchor  = nexa_pro_core_builder_component_values( 'services', $existing_anchor, 'Duplicate Anchor' );
$duplicate_anchor_url = nexa_pro_core_builder_post_action( 'save_component', $page_a, array( 'component' => $duplicate_anchor ) );
nexa_pro_core_builder_assert(
	false !== strpos( $duplicate_anchor_url, 'nexa_pro_core_duplicate_anchor' ),
	'Duplicate anchors should be rejected by builder save.'
);

$bad_nonce_url = nexa_pro_core_builder_post_action(
	'save_component',
	$page_a,
	array( 'component' => nexa_pro_core_builder_component_values( 'contact', 'bad-nonce', 'Bad Nonce' ) ),
	false
);
nexa_pro_core_builder_assert( false !== strpos( $bad_nonce_url, 'nexa_pro_core_invalid_nonce' ), 'Invalid nonce should be rejected.' );

$previous_caps = isset( $GLOBALS['nexa_pro_core_builder_test_caps'] ) ? $GLOBALS['nexa_pro_core_builder_test_caps'] : null;
if ( isset( $GLOBALS['nexa_pro_core_builder_test_caps'] ) ) {
	$GLOBALS['nexa_pro_core_builder_test_caps'] = array();
}
if ( function_exists( 'wp_set_current_user' ) && ! isset( $GLOBALS['nexa_pro_core_builder_test_caps'] ) ) {
	wp_set_current_user( 0 );
}
$forbidden_url = nexa_pro_core_builder_post_action(
	'save_component',
	$page_a,
	array( 'component' => nexa_pro_core_builder_component_values( 'contact', 'forbidden', 'Forbidden' ) )
);
nexa_pro_core_builder_assert( false !== strpos( $forbidden_url, 'nexa_pro_core_forbidden' ), 'Unauthorized writes should be rejected.' );
if ( null !== $previous_caps ) {
	$GLOBALS['nexa_pro_core_builder_test_caps'] = $previous_caps;
} elseif ( function_exists( 'get_users' ) && function_exists( 'wp_set_current_user' ) ) {
	$admins = get_users(
		array(
			'role'   => 'administrator',
			'number' => 1,
			'fields' => 'ID',
		)
	);

	if ( $admins ) {
		wp_set_current_user( absint( $admins[0] ) );
	}
}

$delete_components = nexa_pro_core_get_page_components( $page_a );
$delete_id         = $delete_components[0]['instance_id'];
$unconfirmed_url   = nexa_pro_core_builder_post_action(
	'delete_component',
	$page_a,
	array( 'component_id' => $delete_id )
);
nexa_pro_core_builder_assert( false !== strpos( $unconfirmed_url, 'nexa_pro_core_delete_unconfirmed' ), 'Delete should require confirmation.' );

nexa_pro_core_builder_post_action(
	'delete_component',
	$page_a,
	array(
		'component_id'    => $delete_id,
		'confirm_delete' => '1',
	)
);
$after_delete = nexa_pro_core_get_page_components( $page_a );
nexa_pro_core_builder_assert( count( $after_delete ) === count( $delete_components ) - 1, 'Delete should remove the component.' );

nexa_pro_core_builder_post_action( 'undo_delete', $page_a );
$after_undo = nexa_pro_core_get_page_components( $page_a );
nexa_pro_core_builder_assert( count( $after_undo ) === count( $delete_components ), 'Undo should restore the deleted component.' );

$fallback_registry = Nexa_Pro_Core\Builder_Admin::get_component_registry();
nexa_pro_core_builder_assert( isset( $fallback_registry['hero'] ), 'Builder should expose component registry definitions.' );

foreach ( $cleanup as $post_id ) {
	wp_delete_post( $post_id, true );
}

if ( $failures ) {
	foreach ( $failures as $failure ) {
		fwrite( STDERR, "FAIL: {$failure}\n" );
	}

	exit( 1 );
}

echo "PASS: Nexa Pro Core builder admin validation completed successfully.\n";

/**
 * Bootstrap a small WordPress-like runtime for standalone validation.
 *
 * @param string $plugin_dir Plugin directory.
 * @return void
 */
function nexa_pro_core_builder_validation_bootstrap_standalone_wordpress( $plugin_dir ) {
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', dirname( $plugin_dir, 2 ) . '/' );
	}

	if ( ! defined( 'MINUTE_IN_SECONDS' ) ) {
		define( 'MINUTE_IN_SECONDS', 60 );
	}

	$GLOBALS['nexa_pro_core_builder_test_posts']     = array();
	$GLOBALS['nexa_pro_core_builder_test_meta']      = array();
	$GLOBALS['nexa_pro_core_builder_test_options']   = array();
	$GLOBALS['nexa_pro_core_builder_test_transients'] = array();
	$GLOBALS['nexa_pro_core_builder_test_hooks']     = array();
	$GLOBALS['nexa_pro_core_builder_test_roles']     = array();
	$GLOBALS['nexa_pro_core_builder_test_post_types'] = array();
	$GLOBALS['nexa_pro_core_builder_test_next_id']   = 2000;
	$GLOBALS['nexa_pro_core_builder_test_caps']      = array(
		'manage_nexa_pro_components'          => true,
		'manage_nexa_pro_reusable_components' => true,
		'import_nexa_pro_components'          => true,
		'export_nexa_pro_components'          => true,
		'edit_posts'                          => true,
	);
	$GLOBALS['nexa_pro_core_test_theme_pages']       = array();
	$GLOBALS['nexa_pro_core_test_enqueued_scripts']  = array();
	$GLOBALS['nexa_pro_core_test_enqueued_styles']   = array();

	if ( ! class_exists( 'WP_Error' ) ) {
		class WP_Error {
			public $code;
			public $message;

			public function __construct( $code = '', $message = '' ) {
				$this->code    = $code;
				$this->message = $message;
			}

			public function get_error_code() {
				return $this->code;
			}

			public function get_error_message() {
				return $this->message;
			}
		}
	}

	if ( ! class_exists( 'Nexa_Pro_Core_Builder_Test_Role' ) ) {
		class Nexa_Pro_Core_Builder_Test_Role {
			public $caps = array();

			public function add_cap( $cap ) {
				$this->caps[ $cap ] = true;
				$GLOBALS['nexa_pro_core_builder_test_caps'][ $cap ] = true;
			}

			public function has_cap( $cap ) {
				return ! empty( $this->caps[ $cap ] ) || ! empty( $GLOBALS['nexa_pro_core_builder_test_caps'][ $cap ] );
			}
		}
	}

	$GLOBALS['nexa_pro_core_builder_test_roles']['administrator'] = new Nexa_Pro_Core_Builder_Test_Role();

	$functions = array(
		'is_wp_error' => function ( $thing ) {
			return $thing instanceof WP_Error;
		},
		'__' => function ( $text ) {
			return $text;
		},
		'_n' => function ( $single, $plural, $number ) {
			return 1 === (int) $number ? $single : $plural;
		},
		'esc_html__' => function ( $text ) {
			return $text;
		},
		'esc_html_e' => function ( $text ) {
			echo esc_html( $text );
		},
		'esc_attr_e' => function ( $text ) {
			echo esc_attr( $text );
		},
		'esc_html' => function ( $text ) {
			return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
		},
		'esc_attr' => function ( $text ) {
			return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
		},
		'esc_url' => function ( $url ) {
			return esc_attr( $url );
		},
		'add_action' => function ( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
			$GLOBALS['nexa_pro_core_builder_test_hooks'][ $hook ][ $priority ][] = array(
				'callback'      => $callback,
				'accepted_args' => absint( $accepted_args ),
			);
			return true;
		},
		'add_filter' => function ( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
			$GLOBALS['nexa_pro_core_builder_test_hooks'][ $hook ][ $priority ][] = array(
				'callback'      => $callback,
				'accepted_args' => absint( $accepted_args ),
			);
			return true;
		},
		'apply_filters' => function ( $hook, $value, ...$args ) {
			if ( empty( $GLOBALS['nexa_pro_core_builder_test_hooks'][ $hook ] ) ) {
				return $value;
			}

			ksort( $GLOBALS['nexa_pro_core_builder_test_hooks'][ $hook ] );

			foreach ( $GLOBALS['nexa_pro_core_builder_test_hooks'][ $hook ] as $callbacks ) {
				foreach ( $callbacks as $callback_data ) {
					$accepted_args = max( 1, $callback_data['accepted_args'] );
					$filter_args   = array_slice( array_merge( array( $value ), $args ), 0, $accepted_args );
					$value         = call_user_func_array( $callback_data['callback'], $filter_args );
				}
			}

			return $value;
		},
		'do_action' => function ( $hook, ...$args ) {
			if ( empty( $GLOBALS['nexa_pro_core_builder_test_hooks'][ $hook ] ) ) {
				return;
			}

			ksort( $GLOBALS['nexa_pro_core_builder_test_hooks'][ $hook ] );

			foreach ( $GLOBALS['nexa_pro_core_builder_test_hooks'][ $hook ] as $callbacks ) {
				foreach ( $callbacks as $callback_data ) {
					$accepted_args = max( 0, $callback_data['accepted_args'] );
					call_user_func_array( $callback_data['callback'], array_slice( $args, 0, $accepted_args ) );
				}
			}
		},
		'load_plugin_textdomain' => function () {
			return true;
		},
		'plugin_dir_path' => function ( $file ) {
			return rtrim( dirname( $file ), '/' ) . '/';
		},
		'plugin_dir_url' => function () {
			return 'http://wordpress.local/wp-content/plugins/nexa-pro-core/';
		},
		'plugin_basename' => function ( $file ) {
			return basename( dirname( $file ) ) . '/' . basename( $file );
		},
		'register_activation_hook' => function () {},
		'register_deactivation_hook' => function () {},
		'add_theme_page' => function ( $page_title, $menu_title, $capability, $menu_slug, $callback ) {
			$hook = 'appearance_page_' . $menu_slug;
			$GLOBALS['nexa_pro_core_test_theme_pages'][] = compact( 'page_title', 'menu_title', 'capability', 'menu_slug', 'callback', 'hook' );
			return $hook;
		},
		'wp_enqueue_style' => function ( $handle ) {
			$GLOBALS['nexa_pro_core_test_enqueued_styles'][] = $handle;
		},
		'wp_enqueue_script' => function ( $handle ) {
			$GLOBALS['nexa_pro_core_test_enqueued_scripts'][] = $handle;
		},
		'current_user_can' => function ( $capability, $object_id = 0 ) {
			if ( 'edit_post' === $capability ) {
				return ! empty( $GLOBALS['nexa_pro_core_builder_test_caps']['edit_posts'] ) && isset( $GLOBALS['nexa_pro_core_builder_test_posts'][ absint( $object_id ) ] );
			}

			return ! empty( $GLOBALS['nexa_pro_core_builder_test_caps'][ $capability ] );
		},
		'get_role' => function ( $role ) {
			return isset( $GLOBALS['nexa_pro_core_builder_test_roles'][ $role ] ) ? $GLOBALS['nexa_pro_core_builder_test_roles'][ $role ] : null;
		},
		'wp_insert_post' => function ( $postarr ) {
			$id = ++$GLOBALS['nexa_pro_core_builder_test_next_id'];
			$GLOBALS['nexa_pro_core_builder_test_posts'][ $id ] = (object) array(
				'ID'          => $id,
				'post_title'  => isset( $postarr['post_title'] ) ? $postarr['post_title'] : '',
				'post_type'   => isset( $postarr['post_type'] ) ? $postarr['post_type'] : 'post',
				'post_status' => isset( $postarr['post_status'] ) ? $postarr['post_status'] : 'draft',
			);

			return $id;
		},
		'get_post' => function ( $post_id ) {
			$post_id = absint( $post_id );
			return isset( $GLOBALS['nexa_pro_core_builder_test_posts'][ $post_id ] ) ? $GLOBALS['nexa_pro_core_builder_test_posts'][ $post_id ] : null;
		},
		'wp_delete_post' => function ( $post_id ) {
			unset( $GLOBALS['nexa_pro_core_builder_test_posts'][ $post_id ], $GLOBALS['nexa_pro_core_builder_test_meta'][ $post_id ] );
			return true;
		},
		'get_posts' => function ( $args ) {
			$posts = array();

			foreach ( $GLOBALS['nexa_pro_core_builder_test_posts'] as $post ) {
				if ( ! empty( $args['post_type'] ) && $post->post_type !== $args['post_type'] ) {
					continue;
				}

				if ( ! empty( $args['post_status'] ) && is_array( $args['post_status'] ) && ! in_array( $post->post_status, $args['post_status'], true ) ) {
					continue;
				}

				if ( ! empty( $args['s'] ) && false === stripos( $post->post_title, $args['s'] ) ) {
					continue;
				}

				$posts[] = $post;
			}

			usort(
				$posts,
				function ( $a, $b ) {
					return strcmp( $a->post_title, $b->post_title );
				}
			);

			return $posts;
		},
		'get_the_title' => function ( $post ) {
			$post = is_object( $post ) ? $post : get_post( $post );
			return $post ? $post->post_title : '';
		},
		'get_edit_post_link' => function ( $post_id ) {
			return 'http://wordpress.local/wp-admin/post.php?post=' . absint( $post_id ) . '&action=edit';
		},
		'get_preview_post_link' => function ( $post ) {
			return 'http://wordpress.local/?p=' . absint( is_object( $post ) ? $post->ID : $post );
		},
		'get_post_meta' => function ( $post_id, $key ) {
			return isset( $GLOBALS['nexa_pro_core_builder_test_meta'][ $post_id ][ $key ] ) ? $GLOBALS['nexa_pro_core_builder_test_meta'][ $post_id ][ $key ] : '';
		},
		'update_post_meta' => function ( $post_id, $key, $value ) {
			$GLOBALS['nexa_pro_core_builder_test_meta'][ $post_id ][ $key ] = $value;
			return true;
		},
		'get_option' => function ( $key, $default = false ) {
			return array_key_exists( $key, $GLOBALS['nexa_pro_core_builder_test_options'] ) ? $GLOBALS['nexa_pro_core_builder_test_options'][ $key ] : $default;
		},
		'update_option' => function ( $key, $value ) {
			$GLOBALS['nexa_pro_core_builder_test_options'][ $key ] = $value;
			return true;
		},
		'register_post_type' => function ( $post_type, $args ) {
			$GLOBALS['nexa_pro_core_builder_test_post_types'][ $post_type ] = (object) $args;
		},
		'get_post_type_object' => function ( $post_type ) {
			return isset( $GLOBALS['nexa_pro_core_builder_test_post_types'][ $post_type ] ) ? $GLOBALS['nexa_pro_core_builder_test_post_types'][ $post_type ] : null;
		},
		'wp_create_nonce' => function ( $action ) {
			return 'nonce_' . md5( $action );
		},
		'wp_verify_nonce' => function ( $nonce, $action ) {
			return $nonce === wp_create_nonce( $action );
		},
		'admin_url' => function ( $path = '' ) {
			return 'http://wordpress.local/wp-admin/' . ltrim( $path, '/' );
		},
		'add_query_arg' => function ( $args, $url ) {
			return $url . ( false === strpos( $url, '?' ) ? '?' : '&' ) . http_build_query( $args );
		},
		'wp_safe_redirect' => function ( $url ) {
			$GLOBALS['nexa_pro_core_builder_last_redirect'] = $url;
		},
		'set_transient' => function ( $key, $value ) {
			$GLOBALS['nexa_pro_core_builder_test_transients'][ $key ] = $value;
			return true;
		},
		'get_transient' => function ( $key ) {
			return array_key_exists( $key, $GLOBALS['nexa_pro_core_builder_test_transients'] ) ? $GLOBALS['nexa_pro_core_builder_test_transients'][ $key ] : false;
		},
		'delete_transient' => function ( $key ) {
			unset( $GLOBALS['nexa_pro_core_builder_test_transients'][ $key ] );
			return true;
		},
		'get_current_user_id' => function () {
			return 1;
		},
		'wp_unslash' => function ( $value ) {
			return $value;
		},
		'wp_nonce_field' => function () {},
		'selected' => function ( $selected, $current, $echo = true ) {
			$result = (string) $selected === (string) $current ? ' selected="selected"' : '';
			if ( $echo ) {
				echo $result;
			}
			return $result;
		},
		'checked' => function ( $checked, $current = true, $echo = true ) {
			$result = $checked == $current ? ' checked="checked"' : '';
			if ( $echo ) {
				echo $result;
			}
			return $result;
		},
		'disabled' => function ( $disabled, $current = true, $echo = true ) {
			$result = $disabled == $current ? ' disabled="disabled"' : '';
			if ( $echo ) {
				echo $result;
			}
			return $result;
		},
		'sanitize_key' => function ( $key ) {
			return strtolower( preg_replace( '/[^a-z0-9_-]/', '', (string) $key ) );
		},
		'sanitize_text_field' => function ( $text ) {
			return trim( strip_tags( (string) $text ) );
		},
		'sanitize_html_class' => function ( $class ) {
			return preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $class );
		},
		'sanitize_hex_color' => function ( $color ) {
			return preg_match( '/^#([A-Fa-f0-9]{3}){1,2}$/', (string) $color ) ? strtolower( $color ) : '';
		},
		'esc_url_raw' => function ( $url ) {
			$url = trim( (string) $url );

			if ( 0 === stripos( $url, 'javascript:' ) ) {
				return '';
			}

			return filter_var( $url, FILTER_VALIDATE_URL ) ? $url : '';
		},
		'wp_kses_post' => function ( $html ) {
			$html = preg_replace( '#<(script|style)\b[^>]*>.*?</\1>#is', '', (string) $html );
			$html = preg_replace( '/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html );
			$html = preg_replace( '/javascript\s*:/i', '', $html );

			return $html;
		},
		'absint' => function ( $value ) {
			return abs( (int) $value );
		},
		'wp_generate_password' => function ( $length = 12 ) {
			return substr( bin2hex( random_bytes( max( 1, (int) ceil( $length / 2 ) ) ) ), 0, $length );
		},
		'wp_rand' => function () {
			return random_int( 0, PHP_INT_MAX );
		},
		'wp_json_encode' => function ( $data ) {
			return json_encode( $data );
		},
		'wp_list_pluck' => function ( $list, $field ) {
			$values = array();

			foreach ( $list as $item ) {
				if ( is_array( $item ) && array_key_exists( $field, $item ) ) {
					$values[] = $item[ $field ];
				} elseif ( is_object( $item ) && isset( $item->{$field} ) ) {
					$values[] = $item->{$field};
				}
			}

			return $values;
		},
	);

	foreach ( $functions as $name => $callback ) {
		if ( ! function_exists( $name ) ) {
			eval( 'function ' . $name . '(...$args) { return $GLOBALS["nexa_pro_core_builder_function_map"]["' . $name . '"](...$args); }' );
		}
	}

	$GLOBALS['nexa_pro_core_builder_function_map'] = $functions;
}
