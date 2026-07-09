<?php
/**
 * Template helper functions.
 *
 * @package Nexa_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add contextual classes to the body element.
 *
 * @param array $classes Body classes.
 * @return array
 */
function nexa_pro_body_classes( $classes ) {
	if ( ! is_singular() ) {
		$classes[] = 'nexa-pro-list-view';
	}

	if ( is_singular() && ! has_post_thumbnail() ) {
		$classes[] = 'nexa-pro-no-featured-image';
	}

	if ( is_front_page() ) {
		$classes[] = 'nexa-pro-front-page';
	}

	return $classes;
}
add_filter( 'body_class', 'nexa_pro_body_classes' );

/**
 * Check whether a theme option toggle is enabled.
 *
 * @param string $key Option key.
 * @return bool
 */
function nexa_pro_is_option_enabled( $key ) {
	return '1' === (string) nexa_pro_get_option( $key, '0' );
}

/**
 * Determine whether the homepage hero section is available.
 *
 * @return bool
 */
function nexa_pro_has_homepage_hero() {
	if ( ! is_front_page() || ! function_exists( 'nexa_pro_get_homepage_sections' ) ) {
		return false;
	}

	$sections = nexa_pro_get_homepage_sections();

	return ! empty( $sections['hero'] ) && is_array( $sections['hero'] );
}

/**
 * Determine whether the transparent header can be used for the current view.
 *
 * @return bool
 */
function nexa_pro_should_use_transparent_header() {
	return nexa_pro_is_option_enabled( 'transparent_header' ) && nexa_pro_has_homepage_hero();
}

/**
 * Get semantic header classes.
 *
 * @return string
 */
function nexa_pro_get_header_class_attribute() {
	$layout  = nexa_pro_get_option( 'header_layout', 'standard' );
	$classes = array(
		'site-header',
		'nexa-pro-header',
		'nexa-pro-header--' . ( 'centered' === $layout ? 'centered' : 'standard' ),
	);

	if ( nexa_pro_is_option_enabled( 'sticky_header' ) ) {
		$classes[] = 'nexa-pro-header--sticky';
	}

	if ( nexa_pro_should_use_transparent_header() ) {
		$classes[] = 'nexa-pro-header--transparent';
	}

	return implode( ' ', array_map( 'sanitize_html_class', $classes ) );
}

/**
 * Return a renderable attachment ID for a logo context.
 *
 * @param string $context Logo context, desktop or mobile.
 * @return int
 */
function nexa_pro_get_logo_attachment_id( $context = 'desktop' ) {
	$native_logo_id  = absint( get_theme_mod( 'custom_logo' ) );
	$desktop_logo_id = absint( nexa_pro_get_option( 'logo_attachment_id', 0 ) );
	$mobile_logo_id  = absint( nexa_pro_get_option( 'mobile_logo_attachment_id', 0 ) );
	$candidates      = array();

	if ( 'mobile' === $context ) {
		$candidates = array( $mobile_logo_id, $desktop_logo_id, $native_logo_id );
	} else {
		$candidates = array( $desktop_logo_id, $native_logo_id );
	}

	foreach ( $candidates as $attachment_id ) {
		if ( $attachment_id && wp_get_attachment_image( $attachment_id, 'full' ) ) {
			return $attachment_id;
		}
	}

	return 0;
}

/**
 * Get escaped logo image markup for a valid attachment ID.
 *
 * @param int    $attachment_id Attachment ID.
 * @param string $class Image class.
 * @param string $alt Alt text.
 * @return string
 */
function nexa_pro_get_logo_image( $attachment_id, $class, $alt ) {
	$attachment_id = absint( $attachment_id );

	if ( ! $attachment_id ) {
		return '';
	}

	return wp_get_attachment_image(
		$attachment_id,
		'full',
		false,
		array(
			'class' => $class,
			'alt'   => $alt,
		)
	);
}

/**
 * Render site branding with logo and brand-text fallbacks.
 *
 * @return void
 */
function nexa_pro_render_site_branding() {
	$brand_name      = nexa_pro_get_brand_name();
	$brand_tagline   = nexa_pro_get_brand_tagline();
	$desktop_logo_id = nexa_pro_get_logo_attachment_id( 'desktop' );
	$mobile_logo_id  = nexa_pro_get_logo_attachment_id( 'mobile' );
	$has_desktop_logo = (bool) $desktop_logo_id;
	$show_text      = nexa_pro_is_option_enabled( 'display_brand_text' ) || ! $has_desktop_logo;
	$classes         = array( 'site-branding' );

	if ( $has_desktop_logo ) {
		$classes[] = 'site-branding--has-logo';
	}

	if ( $mobile_logo_id && $mobile_logo_id !== $desktop_logo_id ) {
		$classes[] = 'site-branding--has-mobile-logo';
	}

	if ( $show_text ) {
		$classes[] = 'site-branding--has-text';
	}

	?>
	<div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $classes ) ) ); ?>">
		<a class="site-branding__link" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( $brand_name ); ?>">
			<?php if ( $desktop_logo_id ) : ?>
				<span class="site-branding__logo site-branding__logo--desktop">
					<?php echo nexa_pro_get_logo_image( $desktop_logo_id, 'site-branding__logo-image', $brand_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</span>
			<?php endif; ?>

			<?php if ( $mobile_logo_id && $mobile_logo_id !== $desktop_logo_id ) : ?>
				<span class="site-branding__logo site-branding__logo--mobile">
					<?php echo nexa_pro_get_logo_image( $mobile_logo_id, 'site-branding__logo-image', $brand_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</span>
			<?php endif; ?>

			<?php if ( $show_text ) : ?>
				<span class="site-branding__text">
					<span class="site-title"><?php echo esc_html( $brand_name ); ?></span>
					<?php if ( $brand_tagline ) : ?>
						<span class="site-description"><?php echo esc_html( $brand_tagline ); ?></span>
					<?php endif; ?>
				</span>
			<?php endif; ?>
		</a>
	</div>
	<?php
}

/**
 * Determine whether the header CTA has enough data to render.
 *
 * @param string $context CTA context, desktop or mobile.
 * @return bool
 */
function nexa_pro_should_render_header_cta( $context = 'desktop' ) {
	$enabled_key = 'mobile' === $context ? 'mobile_cta_enabled' : 'header_cta_enabled';
	$text        = nexa_pro_get_option( 'header_cta_text', '' );
	$url         = nexa_pro_get_option( 'header_cta_url', '' );

	return nexa_pro_is_option_enabled( $enabled_key ) && '' !== $text && '' !== $url && '' !== esc_url( $url );
}

/**
 * Render the header CTA link.
 *
 * @param string $context CTA context.
 * @return void
 */
function nexa_pro_render_header_cta( $context = 'desktop' ) {
	if ( ! nexa_pro_should_render_header_cta( $context ) ) {
		return;
	}

	$class = 'mobile' === $context ? 'header-cta header-cta--mobile button' : 'header-cta header-cta--desktop button';

	?>
	<a class="<?php echo esc_attr( $class ); ?>" href="<?php echo esc_url( nexa_pro_get_option( 'header_cta_url', '' ) ); ?>">
		<?php echo esc_html( nexa_pro_get_option( 'header_cta_text', '' ) ); ?>
	</a>
	<?php
}

/**
 * Determine whether comments should be displayed for the current post.
 *
 * @return bool
 */
function nexa_pro_should_show_comments() {
	return comments_open() || get_comments_number();
}
