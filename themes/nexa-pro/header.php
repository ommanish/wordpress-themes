<?php
/**
 * The header for Nexa Pro.
 *
 * @package Nexa_Pro
 */

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'nexa-pro' ); ?></a>

<div class="site">
	<header class="site-header" role="banner">
		<div class="site-header__inner nexa-pro-container">
			<div class="site-branding">
				<?php
				if ( has_custom_logo() ) {
					the_custom_logo();
				}

				if ( is_front_page() && is_home() ) :
					?>
					<h1 class="site-title">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></a>
					</h1>
					<?php
				else :
					?>
					<p class="site-title">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></a>
					</p>
					<?php
				endif;

				$nexa_pro_description = get_bloginfo( 'description', 'display' );
				if ( $nexa_pro_description || is_customize_preview() ) :
					?>
					<p class="site-description"><?php echo esc_html( $nexa_pro_description ); ?></p>
				<?php endif; ?>
			</div>

			<nav class="primary-navigation" aria-label="<?php esc_attr_e( 'Primary menu', 'nexa-pro' ); ?>">
				<button class="menu-toggle" type="button" aria-controls="primary-menu" aria-expanded="false">
					<span class="menu-toggle__bar" aria-hidden="true"></span>
					<span class="menu-toggle__text"><?php esc_html_e( 'Menu', 'nexa-pro' ); ?></span>
				</button>

				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'menu_id'        => 'primary-menu',
						'menu_class'     => 'primary-menu',
						'container'      => false,
						'fallback_cb'    => 'nexa_pro_primary_menu_fallback',
					)
				);
				?>
			</nav>
		</div>
	</header>

	<main id="primary" class="site-main">
