<?php
/**
 * Template part for displaying posts and pages.
 *
 * @package Nexa_Pro
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class( is_singular() ? 'entry entry-single' : 'entry entry-card' ); ?>>
	<header class="entry-header">
		<?php
		if ( is_singular() ) {
			the_title( '<h1 class="entry-title">', '</h1>' );
		} else {
			the_title(
				'<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">',
				'</a></h2>'
			);
		}

		nexa_pro_entry_meta();
		?>
	</header>

	<?php nexa_pro_post_thumbnail( 'large', ! is_singular() ); ?>

	<?php if ( is_singular() ) : ?>
		<div class="entry-content">
			<?php
			the_content();

			wp_link_pages(
				array(
					'before' => '<nav class="page-links" aria-label="' . esc_attr__( 'Page', 'nexa-pro' ) . '">',
					'after'  => '</nav>',
				)
			);
			?>
		</div>
	<?php else : ?>
		<div class="entry-summary">
			<?php the_excerpt(); ?>
		</div>
	<?php endif; ?>

	<?php nexa_pro_entry_footer(); ?>
</article>

