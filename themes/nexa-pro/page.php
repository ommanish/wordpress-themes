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
		if ( ! function_exists( 'nexa_pro_render_builder_page_components' ) || ! nexa_pro_render_builder_page_components( get_the_ID() ) ) {
			get_template_part( 'template-parts/content' );
		}

		if ( nexa_pro_should_show_comments() ) {
			comments_template();
		}
	endwhile;
	?>
</div>

<?php
get_footer();
