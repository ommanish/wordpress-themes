<?php
/**
 * Theme setup.
 *
 * @package Nexa_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Configure theme supports, menus, and localization.
 *
 * @return void
 */
function nexa_pro_setup() {
	load_theme_textdomain( 'nexa-pro', NEXA_PRO_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );

	add_theme_support(
		'html5',
		array(
			'caption',
			'comment-form',
			'comment-list',
			'gallery',
			'navigation-widgets',
			'script',
			'search-form',
			'style',
		)
	);

	add_theme_support(
		'custom-logo',
		array(
			'height'      => 80,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	register_nav_menus(
		array(
			'primary' => esc_html__( 'Primary Menu', 'nexa-pro' ),
			'footer'  => esc_html__( 'Footer Menu', 'nexa-pro' ),
		)
	);
}
add_action( 'after_setup_theme', 'nexa_pro_setup' );

/**
 * Set the content width for embeds and media.
 *
 * @return void
 */
function nexa_pro_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'nexa_pro_content_width', 760 );
}
add_action( 'after_setup_theme', 'nexa_pro_content_width', 0 );

/**
 * Render a simple page-list fallback for the primary menu.
 *
 * @param array $args Fallback menu arguments.
 * @return void
 */
function nexa_pro_primary_menu_fallback( $args = array() ) {
	unset( $args );

	?>
	<ul id="primary-menu" class="primary-menu">
		<?php
		wp_list_pages(
			array(
				'title_li' => '',
				'depth'    => 1,
			)
		);
		?>
	</ul>
	<?php
}
