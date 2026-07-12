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

	if ( 'single-page' === nexa_pro_get_navigation_settings()['mode'] ) {
		$classes[] = 'nexa-pro-single-page-navigation';
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
 * Get escaped attributes for a theme action link.
 *
 * Explicit schedule modal targets become progressive-enhancement triggers with
 * a usable no-JavaScript fallback. Other URLs remain ordinary links.
 *
 * @param string $url Link URL.
 * @param array  $attributes Extra link attributes.
 * @return string
 */
function nexa_pro_get_action_link_attributes( $url, $attributes = array() ) {
	$url        = trim( (string) $url );
	$attributes = is_array( $attributes ) ? $attributes : array();

	if ( '' === $url ) {
		return '';
	}

	if ( nexa_pro_is_schedule_modal_target( $url ) ) {
		$modal = nexa_pro_get_schedule_modal_data();

		if ( empty( $modal['enabled'] ) || empty( $modal['has_actions'] ) || empty( $modal['fallback_url'] ) ) {
			return '';
		}

		$attributes['href']                        = $modal['fallback_url'];
		$attributes['data-nexa-pro-modal-trigger'] = 'schedule';
		$attributes['data-nexa-pro-modal-target']  = '#nexa-pro-schedule';
		$attributes['aria-controls']               = 'nexa-pro-schedule';
		$attributes['aria-haspopup']               = 'dialog';
	} else {
		if ( '' === esc_url( $url ) ) {
			return '';
		}

		$attributes['href'] = $url;
	}

	$markup = '';

	foreach ( $attributes as $name => $value ) {
		if ( '' === (string) $value ) {
			continue;
		}

		$name = sanitize_key( $name );

		if ( '' === $name ) {
			continue;
		}

		$escaped_value = 'href' === $name ? esc_url( $value ) : esc_attr( $value );
		$markup       .= sprintf( ' %1$s="%2$s"', $name, $escaped_value );
	}

	return $markup;
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

	if ( ! nexa_pro_is_option_enabled( $enabled_key ) || '' === $text || '' === $url ) {
		return false;
	}

	if ( nexa_pro_is_schedule_modal_target( $url ) ) {
		$modal = nexa_pro_get_schedule_modal_data();

		return ! empty( $modal['enabled'] ) && ! empty( $modal['has_actions'] );
	}

	return '' !== esc_url( $url );
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
	$attrs = nexa_pro_get_action_link_attributes(
		nexa_pro_get_option( 'header_cta_url', '' ),
		array(
			'class' => $class,
		)
	);

	if ( '' === $attrs ) {
		return;
	}

	?>
	<a<?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<?php echo esc_html( nexa_pro_get_option( 'header_cta_text', '' ) ); ?>
	</a>
	<?php
}

/**
 * Render footer branding.
 *
 * @return void
 */
function nexa_pro_render_footer_branding() {
	$identity = nexa_pro_get_footer_identity();

	if ( empty( $identity['has_identity'] ) ) {
		return;
	}

	?>
	<div class="site-footer__brand">
		<a class="site-footer__brand-link" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( $identity['brand_text'] ); ?>">
			<?php if ( ! empty( $identity['logo_id'] ) ) : ?>
				<span class="site-footer__logo">
					<?php echo nexa_pro_get_logo_image( $identity['logo_id'], 'site-footer__logo-image', $identity['brand_text'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</span>
			<?php endif; ?>

			<?php if ( ! empty( $identity['show_text'] ) ) : ?>
				<span class="site-footer__brand-text"><?php echo esc_html( $identity['brand_text'] ); ?></span>
			<?php endif; ?>
		</a>

		<?php if ( ! empty( $identity['description'] ) ) : ?>
			<p class="site-footer__description"><?php echo esc_html( $identity['description'] ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Determine whether the footer has a menu to render.
 *
 * @return bool
 */
function nexa_pro_footer_has_menu() {
	return nexa_pro_is_option_enabled( 'footer_menu_enabled' ) && has_nav_menu( 'footer' );
}

/**
 * Render footer navigation.
 *
 * @return void
 */
function nexa_pro_render_footer_menu() {
	if ( ! nexa_pro_footer_has_menu() ) {
		return;
	}

	?>
	<nav class="site-footer__section footer-navigation" aria-label="<?php esc_attr_e( 'Footer menu', 'nexa-pro' ); ?>">
		<p class="site-footer__section-title"><?php esc_html_e( 'Navigation', 'nexa-pro' ); ?></p>
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'footer',
				'menu_class'     => 'footer-menu',
				'container'      => false,
				'depth'          => 1,
			)
		);
		?>
	</nav>
	<?php
}

/**
 * Render configured footer contact details.
 *
 * @return void
 */
function nexa_pro_render_footer_contact_details() {
	$details = nexa_pro_get_contact_details();

	if ( empty( $details['email'] ) && empty( $details['phone'] ) && empty( $details['address'] ) && empty( $details['business_hours'] ) ) {
		return;
	}

	?>
	<div class="site-footer__section site-footer__contact">
		<p class="site-footer__section-title"><?php esc_html_e( 'Contact', 'nexa-pro' ); ?></p>
		<ul class="site-footer__contact-list">
			<?php if ( ! empty( $details['email'] ) ) : ?>
				<li>
					<span><?php esc_html_e( 'Email', 'nexa-pro' ); ?></span>
					<a href="<?php echo esc_url( 'mailto:' . $details['email'] ); ?>"><?php echo esc_html( $details['email'] ); ?></a>
				</li>
			<?php endif; ?>

			<?php if ( ! empty( $details['phone'] ) ) : ?>
				<li>
					<span><?php esc_html_e( 'Phone', 'nexa-pro' ); ?></span>
					<?php if ( ! empty( $details['phone_href'] ) ) : ?>
						<a href="<?php echo esc_url( $details['phone_href'] ); ?>"><?php echo esc_html( $details['phone'] ); ?></a>
					<?php else : ?>
						<span><?php echo esc_html( $details['phone'] ); ?></span>
					<?php endif; ?>
				</li>
			<?php endif; ?>

			<?php if ( ! empty( $details['address'] ) ) : ?>
				<li>
					<span><?php esc_html_e( 'Address', 'nexa-pro' ); ?></span>
					<span><?php echo wp_kses_post( nl2br( esc_html( $details['address'] ) ) ); ?></span>
				</li>
			<?php endif; ?>

			<?php if ( ! empty( $details['business_hours'] ) ) : ?>
				<li>
					<span><?php esc_html_e( 'Hours', 'nexa-pro' ); ?></span>
					<span><?php echo wp_kses_post( nl2br( esc_html( $details['business_hours'] ) ) ); ?></span>
				</li>
			<?php endif; ?>
		</ul>
	</div>
	<?php
}

/**
 * Render footer social links.
 *
 * @return void
 */
function nexa_pro_render_footer_social_links() {
	$links = nexa_pro_get_social_links();

	if ( empty( $links ) ) {
		return;
	}

	?>
	<nav class="site-footer__section site-footer__social" aria-label="<?php esc_attr_e( 'Social profiles', 'nexa-pro' ); ?>">
		<p class="site-footer__section-title"><?php esc_html_e( 'Social', 'nexa-pro' ); ?></p>
		<ul class="site-footer__social-list">
			<?php foreach ( $links as $link ) : ?>
				<li>
					<a href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['label'] ); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>
	<?php
}

/**
 * Determine whether the footer schedule action can render.
 *
 * @return bool
 */
function nexa_pro_footer_has_schedule_action() {
	$modal = nexa_pro_get_schedule_modal_data();

	return ! empty( $modal['enabled'] ) && ! empty( $modal['has_actions'] );
}

/**
 * Render the footer schedule action.
 *
 * @return void
 */
function nexa_pro_render_footer_schedule_action() {
	if ( ! nexa_pro_footer_has_schedule_action() ) {
		return;
	}

	$modal = nexa_pro_get_schedule_modal_data();
	$attrs = nexa_pro_get_action_link_attributes(
		'#nexa-pro-schedule',
		array(
			'class' => 'site-footer__schedule button',
		)
	);

	if ( '' === $attrs ) {
		return;
	}

	?>
	<div class="site-footer__section site-footer__actions">
		<p class="site-footer__section-title"><?php esc_html_e( 'Next step', 'nexa-pro' ); ?></p>
		<a<?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php echo esc_html( $modal['title'] ); ?>
		</a>
	</div>
	<?php
}

/**
 * Render optional footer sections without empty wrappers.
 *
 * @return void
 */
function nexa_pro_render_footer_sections() {
	$contact_details = nexa_pro_get_contact_details();
	$social_links    = nexa_pro_get_social_links();
	$has_sections = nexa_pro_footer_has_menu()
		|| $contact_details['email']
		|| $contact_details['phone']
		|| $contact_details['address']
		|| $contact_details['business_hours']
		|| ! empty( $social_links )
		|| nexa_pro_footer_has_schedule_action();

	if ( ! $has_sections ) {
		return;
	}

	?>
	<div class="site-footer__sections">
		<?php
		nexa_pro_render_footer_menu();
		nexa_pro_render_footer_contact_details();
		nexa_pro_render_footer_social_links();
		nexa_pro_render_footer_schedule_action();
		?>
	</div>
	<?php
}

/**
 * Render footer legal links and copyright.
 *
 * @return void
 */
function nexa_pro_render_footer_legal() {
	$legal_links = nexa_pro_get_footer_legal_links();
	$copyright   = nexa_pro_expand_footer_copyright();

	?>
	<div class="site-footer__bottom">
		<?php if ( ! empty( $legal_links ) ) : ?>
			<nav class="site-footer__legal" aria-label="<?php esc_attr_e( 'Legal links', 'nexa-pro' ); ?>">
				<ul class="site-footer__legal-list">
					<?php foreach ( $legal_links as $link ) : ?>
						<?php
						$link_attributes = array(
							'href' => $link['url'],
						);

						if ( ! empty( $link['behavior'] ) && 'modal' === $link['behavior'] && ! empty( $link['modal_id'] ) ) {
							$link_attributes['data-nexa-pro-modal-trigger'] = 'legal';
							$link_attributes['data-nexa-pro-modal-target']  = '#' . $link['modal_id'];
							$link_attributes['aria-controls']               = $link['modal_id'];
							$link_attributes['aria-haspopup']               = 'dialog';
						}
						?>
						<li>
							<a<?php echo nexa_pro_get_escaped_attributes( $link_attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
								<?php echo esc_html( $link['label'] ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>
		<?php endif; ?>

		<?php if ( '' !== $copyright ) : ?>
			<p class="site-footer__credit"><?php echo esc_html( $copyright ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Get escaped HTML attributes.
 *
 * @param array $attributes Attribute map.
 * @return string
 */
function nexa_pro_get_escaped_attributes( $attributes ) {
	$markup = '';

	foreach ( (array) $attributes as $name => $value ) {
		if ( '' === (string) $value ) {
			continue;
		}

		$name = sanitize_key( $name );

		if ( '' === $name ) {
			continue;
		}

		$escaped_value = 'href' === $name ? esc_url( $value ) : esc_attr( $value );
		$markup       .= sprintf( ' %1$s="%2$s"', $name, $escaped_value );
	}

	return $markup;
}

/**
 * Render the schedule modal once per page when configured.
 *
 * @return void
 */
function nexa_pro_render_schedule_modal() {
	$modal = nexa_pro_get_schedule_modal_data();

	if ( empty( $modal['enabled'] ) || empty( $modal['has_actions'] ) ) {
		return;
	}

	?>
	<dialog id="nexa-pro-schedule" class="nexa-pro-modal" aria-labelledby="nexa-pro-schedule-title" aria-hidden="true" tabindex="-1" data-nexa-pro-modal>
		<div class="nexa-pro-modal__dialog" role="document">
			<button type="button" class="nexa-pro-modal__close" data-nexa-pro-modal-close aria-label="<?php esc_attr_e( 'Close schedule dialog', 'nexa-pro' ); ?>">
				<span aria-hidden="true">&times;</span>
			</button>

			<div class="nexa-pro-modal__content">
				<h2 id="nexa-pro-schedule-title"><?php echo esc_html( $modal['title'] ); ?></h2>
				<?php if ( ! empty( $modal['text'] ) ) : ?>
					<p><?php echo esc_html( $modal['text'] ); ?></p>
				<?php endif; ?>

				<div class="nexa-pro-modal__actions">
					<?php if ( ! empty( $modal['actions']['email'] ) ) : ?>
						<a class="button" href="<?php echo esc_url( $modal['actions']['email']['url'] ); ?>">
							<?php echo esc_html( $modal['actions']['email']['label'] ); ?>
						</a>
					<?php endif; ?>

					<?php if ( ! empty( $modal['actions']['calendar'] ) ) : ?>
						<a class="button button--secondary" href="<?php echo esc_url( $modal['actions']['calendar']['url'] ); ?>">
							<?php echo esc_html( $modal['actions']['calendar']['label'] ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</dialog>
	<?php
}

/**
 * Render configured Privacy and Terms modals.
 *
 * @return void
 */
function nexa_pro_render_legal_modals() {
	foreach ( array( 'privacy', 'terms' ) as $type ) {
		$modal = nexa_pro_get_legal_modal_data( $type );

		if ( empty( $modal['enabled'] ) ) {
			continue;
		}

		$title_id = $modal['id'] . '-title';
		?>
		<dialog id="<?php echo esc_attr( $modal['id'] ); ?>" class="nexa-pro-modal nexa-pro-modal--legal" aria-labelledby="<?php echo esc_attr( $title_id ); ?>" aria-hidden="true" tabindex="-1" data-nexa-pro-modal>
			<div class="nexa-pro-modal__dialog" role="document">
				<button type="button" class="nexa-pro-modal__close" data-nexa-pro-modal-close aria-label="<?php echo esc_attr( sprintf( __( 'Close %s dialog', 'nexa-pro' ), $modal['label'] ) ); ?>">
					<span aria-hidden="true">&times;</span>
				</button>

				<div class="nexa-pro-modal__content">
					<h2 id="<?php echo esc_attr( $title_id ); ?>"><?php echo esc_html( $modal['title'] ); ?></h2>
					<div class="nexa-pro-modal__legal-content">
						<?php echo wp_kses_post( wpautop( $modal['content'] ) ); ?>
					</div>
				</div>
			</div>
		</dialog>
		<?php
	}
}

/**
 * Determine whether comments should be displayed for the current post.
 *
 * @return bool
 */
function nexa_pro_should_show_comments() {
	return comments_open() || get_comments_number();
}
