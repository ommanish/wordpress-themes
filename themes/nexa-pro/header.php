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
	<header class="<?php echo esc_attr( nexa_pro_get_header_class_attribute() ); ?>" role="banner">
		<div class="site-header__inner nexa-pro-container">
			<?php nexa_pro_render_site_branding(); ?>

			<nav class="primary-navigation" aria-label="<?php esc_attr_e( 'Primary menu', 'nexa-pro' ); ?>">
				<button
					class="menu-toggle"
					type="button"
					aria-controls="primary-menu-panel"
					aria-expanded="false"
					aria-label="<?php esc_attr_e( 'Open primary menu', 'nexa-pro' ); ?>"
					data-aria-open="<?php esc_attr_e( 'Open primary menu', 'nexa-pro' ); ?>"
					data-aria-close="<?php esc_attr_e( 'Close primary menu', 'nexa-pro' ); ?>"
				>
					<span class="menu-toggle__bar" aria-hidden="true"></span>
					<span class="menu-toggle__text"><?php esc_html_e( 'Menu', 'nexa-pro' ); ?></span>
				</button>

				<div id="primary-menu-panel" class="primary-navigation__panel">
					<?php nexa_pro_render_primary_navigation_menu(); ?>
					<?php nexa_pro_render_header_cta( 'mobile' ); ?>
				</div>
			</nav>

			<?php nexa_pro_render_header_cta( 'desktop' ); ?>
		</div>
	</header>

	<main id="primary" class="site-main" tabindex="-1">
