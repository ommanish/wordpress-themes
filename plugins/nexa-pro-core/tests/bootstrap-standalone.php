<?php
/**
 * Shared standalone bootstrap for Nexa Pro Core validation scripts.
 *
 * @package Nexa_Pro_Core
 */

if ( defined( 'NEXA_PRO_CORE_TEST_BOOTSTRAP_LOADED' ) ) {
	return;
}

define( 'NEXA_PRO_CORE_TEST_BOOTSTRAP_LOADED', true );

$nexa_pro_core_test_plugin_dir = realpath( __DIR__ . '/..' );
$nexa_pro_core_test_wp_load    = getenv( 'NEXA_PRO_CORE_WP_LOAD' );

foreach ( $argv as $nexa_pro_core_test_arg ) {
	if ( 0 === strpos( $nexa_pro_core_test_arg, '--wp-load=' ) ) {
		$nexa_pro_core_test_wp_load = substr( $nexa_pro_core_test_arg, 10 );
	}
}

if ( $nexa_pro_core_test_wp_load && file_exists( $nexa_pro_core_test_wp_load ) ) {
	require_once $nexa_pro_core_test_wp_load;
} else {
	nexa_pro_core_test_bootstrap_standalone_wordpress( $nexa_pro_core_test_plugin_dir );
}

if ( ! defined( 'NEXA_PRO_CORE_TESTING' ) ) {
	define( 'NEXA_PRO_CORE_TESTING', true );
}

if ( ! defined( 'NEXA_PRO_VERSION' ) ) {
	define( 'NEXA_PRO_VERSION', '1.1.0-beta.1' );
}

if ( ! defined( 'NEXA_PRO_CORE_VERSION' ) ) {
	require_once $nexa_pro_core_test_plugin_dir . '/nexa-pro-core.php';
}

/**
 * Bootstrap enough WordPress behavior for standalone validation.
 *
 * @param string $plugin_dir Plugin directory.
 * @return void
 */
function nexa_pro_core_test_bootstrap_standalone_wordpress( $plugin_dir ) {
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', dirname( $plugin_dir, 2 ) . '/' );
	}

	if ( ! defined( 'OBJECT' ) ) {
		define( 'OBJECT', 'OBJECT' );
	}

	if ( ! defined( 'MINUTE_IN_SECONDS' ) ) {
		define( 'MINUTE_IN_SECONDS', 60 );
	}

	$GLOBALS['nexa_pro_core_test_posts']      = array();
	$GLOBALS['nexa_pro_core_test_post_meta']  = array();
	$GLOBALS['nexa_pro_core_test_options']    = array();
	$GLOBALS['nexa_pro_core_test_transients'] = array();
	$GLOBALS['nexa_pro_core_test_post_types'] = array();
	$GLOBALS['nexa_pro_core_test_hooks']      = array();
	$GLOBALS['nexa_pro_core_test_next_id']    = 1000;

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

	if ( ! function_exists( '_n' ) ) {
		function _n( $single, $plural, $number ) {
			return 1 === absint( $number ) ? $single : $plural;
		}
	}

	if ( ! function_exists( 'esc_html__' ) ) {
		function esc_html__( $text ) {
			return $text;
		}
	}

	if ( ! function_exists( 'esc_attr__' ) ) {
		function esc_attr__( $text ) {
			return $text;
		}
	}

	if ( ! function_exists( 'esc_html' ) ) {
		function esc_html( $text ) {
			return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
		}
	}

	if ( ! function_exists( 'esc_attr' ) ) {
		function esc_attr( $text ) {
			return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
		}
	}

	if ( ! function_exists( 'esc_url' ) ) {
		function esc_url( $url ) {
			return esc_url_raw( $url );
		}
	}

	if ( ! function_exists( 'esc_url_raw' ) ) {
		function esc_url_raw( $url ) {
			$url = trim( (string) $url );

			if ( '' === $url ) {
				return '';
			}

			if ( '#' === $url[0] ) {
				return preg_match( '/^#[A-Za-z0-9_-]+$/', $url ) ? $url : '';
			}

			return filter_var( $url, FILTER_VALIDATE_URL ) ? $url : '';
		}
	}

	if ( ! function_exists( 'sanitize_key' ) ) {
		function sanitize_key( $key ) {
			return preg_replace( '/[^a-z0-9_-]/', '', strtolower( (string) $key ) );
		}
	}

	if ( ! function_exists( 'sanitize_text_field' ) ) {
		function sanitize_text_field( $text ) {
			return trim( wp_strip_all_tags( (string) $text ) );
		}
	}

	if ( ! function_exists( 'sanitize_file_name' ) ) {
		function sanitize_file_name( $name ) {
			return preg_replace( '/[^A-Za-z0-9._-]/', '-', basename( (string) $name ) );
		}
	}

	if ( ! function_exists( 'sanitize_title' ) ) {
		function sanitize_title( $title ) {
			$title = strtolower( wp_strip_all_tags( (string) $title ) );
			$title = preg_replace( '/[^a-z0-9]+/', '-', $title );
			return trim( $title, '-' );
		}
	}

	if ( ! function_exists( 'sanitize_html_class' ) ) {
		function sanitize_html_class( $class ) {
			return preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $class );
		}
	}

	if ( ! function_exists( 'sanitize_hex_color' ) ) {
		function sanitize_hex_color( $color ) {
			$color = trim( (string) $color );
			return preg_match( '/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $color ) ? strtolower( $color ) : '';
		}
	}

	if ( ! function_exists( 'wp_strip_all_tags' ) ) {
		function wp_strip_all_tags( $text ) {
			return strip_tags( (string) $text );
		}
	}

	if ( ! function_exists( 'wp_kses_post' ) ) {
		function wp_kses_post( $text ) {
			return strip_tags( (string) $text, '<p><br><strong><em><ul><ol><li><a>' );
		}
	}

	if ( ! function_exists( 'wp_unslash' ) ) {
		function wp_unslash( $value ) {
			return is_array( $value ) ? array_map( 'wp_unslash', $value ) : stripslashes( (string) $value );
		}
	}

	if ( ! function_exists( 'absint' ) ) {
		function absint( $value ) {
			return abs( (int) $value );
		}
	}

	if ( ! function_exists( 'wp_json_encode' ) ) {
		function wp_json_encode( $data, $options = 0 ) {
			return json_encode( $data, $options );
		}
	}

	if ( ! function_exists( 'wp_rand' ) ) {
		function wp_rand( $min = 0, $max = PHP_INT_MAX ) {
			return random_int( $min, $max );
		}
	}

	if ( ! function_exists( 'wp_generate_password' ) ) {
		function wp_generate_password( $length = 12 ) {
			return substr( str_repeat( 'abcdef1234567890', 4 ), 0, absint( $length ) );
		}
	}

	if ( ! function_exists( 'wp_attachment_is_image' ) ) {
		function wp_attachment_is_image( $attachment_id ) {
			return absint( $attachment_id ) > 0;
		}
	}

	if ( ! function_exists( 'wp_list_pluck' ) ) {
		function wp_list_pluck( $list, $field ) {
			$values = array();

			foreach ( $list as $item ) {
				if ( is_array( $item ) && array_key_exists( $field, $item ) ) {
					$values[] = $item[ $field ];
				} elseif ( is_object( $item ) && isset( $item->{$field} ) ) {
					$values[] = $item->{$field};
				}
			}

			return $values;
		}
	}

	if ( ! function_exists( 'get_option' ) ) {
		function get_option( $name, $default = false ) {
			return array_key_exists( $name, $GLOBALS['nexa_pro_core_test_options'] ) ? $GLOBALS['nexa_pro_core_test_options'][ $name ] : $default;
		}
	}

	if ( ! function_exists( 'update_option' ) ) {
		function update_option( $name, $value ) {
			$GLOBALS['nexa_pro_core_test_options'][ $name ] = $value;
			return true;
		}
	}

	if ( ! function_exists( 'delete_option' ) ) {
		function delete_option( $name ) {
			unset( $GLOBALS['nexa_pro_core_test_options'][ $name ] );
			return true;
		}
	}

	if ( ! function_exists( 'set_transient' ) ) {
		function set_transient( $name, $value ) {
			$GLOBALS['nexa_pro_core_test_transients'][ $name ] = $value;
			return true;
		}
	}

	if ( ! function_exists( 'get_transient' ) ) {
		function get_transient( $name ) {
			return array_key_exists( $name, $GLOBALS['nexa_pro_core_test_transients'] ) ? $GLOBALS['nexa_pro_core_test_transients'][ $name ] : false;
		}
	}

	if ( ! function_exists( 'delete_transient' ) ) {
		function delete_transient( $name ) {
			unset( $GLOBALS['nexa_pro_core_test_transients'][ $name ] );
			return true;
		}
	}

	if ( ! function_exists( 'wp_insert_post' ) ) {
		function wp_insert_post( $postarr, $wp_error = false ) {
			$id = ++$GLOBALS['nexa_pro_core_test_next_id'];
			$GLOBALS['nexa_pro_core_test_posts'][ $id ] = (object) array(
				'ID'          => $id,
				'post_title'  => isset( $postarr['post_title'] ) ? $postarr['post_title'] : 'Test page',
				'post_type'   => isset( $postarr['post_type'] ) ? $postarr['post_type'] : 'page',
				'post_status' => isset( $postarr['post_status'] ) ? $postarr['post_status'] : 'draft',
				'post_name'   => ! empty( $postarr['post_name'] ) ? sanitize_title( $postarr['post_name'] ) : sanitize_title( isset( $postarr['post_title'] ) ? $postarr['post_title'] : 'test-page' ),
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

	if ( ! function_exists( 'get_posts' ) ) {
		function get_posts( $args = array() ) {
			$posts = array_values( $GLOBALS['nexa_pro_core_test_posts'] );

			if ( ! empty( $args['post_type'] ) ) {
				$posts = array_filter(
					$posts,
					function ( $post ) use ( $args ) {
						return $post->post_type === $args['post_type'];
					}
				);
			}

			if ( ! empty( $args['fields'] ) && 'ids' === $args['fields'] ) {
				return array_map(
					function ( $post ) {
						return $post->ID;
					},
					$posts
				);
			}

			return array_values( $posts );
		}
	}

	if ( ! function_exists( 'get_page_by_path' ) ) {
		function get_page_by_path( $path, $output = OBJECT, $post_type = 'page' ) {
			$path = sanitize_title( $path );

			foreach ( $GLOBALS['nexa_pro_core_test_posts'] as $post ) {
				if ( $post->post_type === $post_type && $post->post_name === $path ) {
					return $post;
				}
			}

			return null;
		}
	}

	if ( ! function_exists( 'get_the_title' ) ) {
		function get_the_title( $post = 0 ) {
			$post = is_object( $post ) ? $post : get_post( $post );
			return $post ? $post->post_title : '';
		}
	}

	if ( ! function_exists( 'get_post_meta' ) ) {
		function get_post_meta( $post_id, $key = '', $single = false ) {
			$post_id = absint( $post_id );

			if ( '' === $key ) {
				return isset( $GLOBALS['nexa_pro_core_test_post_meta'][ $post_id ] ) ? $GLOBALS['nexa_pro_core_test_post_meta'][ $post_id ] : array();
			}

			return isset( $GLOBALS['nexa_pro_core_test_post_meta'][ $post_id ][ $key ] ) ? $GLOBALS['nexa_pro_core_test_post_meta'][ $post_id ][ $key ] : ( $single ? '' : array() );
		}
	}

	if ( ! function_exists( 'update_post_meta' ) ) {
		function update_post_meta( $post_id, $key, $value ) {
			$GLOBALS['nexa_pro_core_test_post_meta'][ absint( $post_id ) ][ $key ] = $value;
			return true;
		}
	}

	if ( ! function_exists( 'current_user_can' ) ) {
		function current_user_can() {
			return true;
		}
	}

	if ( ! function_exists( 'get_current_user_id' ) ) {
		function get_current_user_id() {
			return 1;
		}
	}

	if ( ! function_exists( 'wp_verify_nonce' ) ) {
		function wp_verify_nonce() {
			return true;
		}
	}

	if ( ! function_exists( 'apply_filters' ) ) {
		function apply_filters( $hook, $value ) {
			return $value;
		}
	}

	if ( ! function_exists( 'add_action' ) ) {
		function add_action() {
			return true;
		}
	}

	if ( ! function_exists( 'add_filter' ) ) {
		function add_filter() {
			return true;
		}
	}

	if ( ! function_exists( 'do_action' ) ) {
		function do_action() {
			return true;
		}
	}

	if ( ! function_exists( 'register_activation_hook' ) ) {
		function register_activation_hook() {
			return true;
		}
	}

	if ( ! function_exists( 'register_deactivation_hook' ) ) {
		function register_deactivation_hook() {
			return true;
		}
	}

	if ( ! function_exists( 'plugin_dir_path' ) ) {
		function plugin_dir_path( $file ) {
			return rtrim( dirname( $file ), '/' ) . '/';
		}
	}

	if ( ! function_exists( 'plugin_dir_url' ) ) {
		function plugin_dir_url() {
			return '';
		}
	}

	if ( ! function_exists( 'plugin_basename' ) ) {
		function plugin_basename( $file ) {
			return basename( dirname( $file ) ) . '/' . basename( $file );
		}
	}

	if ( ! function_exists( 'load_plugin_textdomain' ) ) {
		function load_plugin_textdomain() {
			return true;
		}
	}
}
