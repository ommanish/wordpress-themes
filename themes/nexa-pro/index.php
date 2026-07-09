<?php
/**
 * The main template file.
 *
 * @package Nexa_Pro
 */

get_header();
?>

<div class="nexa-pro-container content-layout">
	<?php if ( have_posts() ) : ?>
		<?php if ( is_home() && ! is_front_page() ) : ?>
			<header class="page-header">
				<h1 class="page-title"><?php single_post_title(); ?></h1>
			</header>
		<?php endif; ?>

		<div class="post-list">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry-card' ); ?>>
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
						?>
					</header>

					<?php if ( has_post_thumbnail() ) : ?>
						<a class="entry-thumbnail" href="<?php echo esc_url( get_permalink() ); ?>" aria-hidden="true" tabindex="-1">
							<?php the_post_thumbnail( 'large' ); ?>
						</a>
					<?php endif; ?>

					<div class="entry-summary">
						<?php the_excerpt(); ?>
					</div>
				</article>
			<?php endwhile; ?>
		</div>

		<?php
		the_posts_pagination(
			array(
				'mid_size'           => 1,
				'prev_text'          => esc_html__( 'Previous', 'nexa-pro' ),
				'next_text'          => esc_html__( 'Next', 'nexa-pro' ),
				'screen_reader_text' => esc_html__( 'Posts navigation', 'nexa-pro' ),
			)
		);
		?>
	<?php else : ?>
		<section class="no-results not-found">
			<header class="page-header">
				<h1 class="page-title"><?php esc_html_e( 'Nothing Found', 'nexa-pro' ); ?></h1>
			</header>

			<div class="page-content">
				<p><?php esc_html_e( 'No content is available yet.', 'nexa-pro' ); ?></p>
				<?php get_search_form(); ?>
			</div>
		</section>
	<?php endif; ?>
</div>

<?php
get_footer();

