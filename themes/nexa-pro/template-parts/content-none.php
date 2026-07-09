<?php
/**
 * Template part for empty states.
 *
 * @package Nexa_Pro
 */

?>

<section class="no-results not-found">
	<header class="page-header">
		<?php if ( is_search() ) : ?>
			<h2 class="page-title"><?php esc_html_e( 'Nothing Found', 'nexa-pro' ); ?></h2>
		<?php else : ?>
			<h1 class="page-title"><?php esc_html_e( 'Nothing Found', 'nexa-pro' ); ?></h1>
		<?php endif; ?>
	</header>

	<div class="page-content">
		<?php if ( is_search() ) : ?>
			<p><?php esc_html_e( 'No results matched your search. Try different keywords.', 'nexa-pro' ); ?></p>
			<?php get_search_form(); ?>
		<?php elseif ( current_user_can( 'publish_posts' ) ) : ?>
			<p>
				<?php
				echo wp_kses(
					sprintf(
						/* translators: 1: Link to new post screen. */
						__( 'Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'nexa-pro' ),
						esc_url( admin_url( 'post-new.php' ) )
					),
					array(
						'a' => array(
							'href' => array(),
						),
					)
				);
				?>
			</p>
		<?php else : ?>
			<p><?php esc_html_e( 'No content is available yet.', 'nexa-pro' ); ?></p>
			<?php get_search_form(); ?>
		<?php endif; ?>
	</div>
</section>
