<?php
/**
 * 404 template.
 *
 * @package Nexa_Pro
 */

get_header();
?>

<section class="error-404 not-found nexa-pro-container content-layout">
	<header class="page-header">
		<h1 class="page-title"><?php esc_html_e( 'Page Not Found', 'nexa-pro' ); ?></h1>
	</header>

	<div class="page-content">
		<p><?php esc_html_e( 'The page you are looking for could not be found. Try searching or return to the homepage.', 'nexa-pro' ); ?></p>
		<?php get_search_form(); ?>
		<p>
			<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php esc_html_e( 'Return Home', 'nexa-pro' ); ?>
			</a>
		</p>
	</div>
</section>

<?php
get_footer();

