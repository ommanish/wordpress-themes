<?php
/**
 * Page template.
 *
 * @package Nexa_Pro
 */

get_header();
?>

<div class="nexa-pro-container content-layout">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry-page' ); ?>>
			<header class="entry-header">
				<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<div class="entry-thumbnail">
					<?php the_post_thumbnail( 'large' ); ?>
				</div>
			<?php endif; ?>

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
		</article>
	<?php endwhile; ?>
</div>

<?php
get_footer();

