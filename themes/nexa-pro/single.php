<?php
/**
 * Single post template.
 *
 * @package Nexa_Pro
 */

get_header();
?>

<div class="nexa-pro-container content-layout">
	<?php
	while ( have_posts() ) :
		the_post();
		get_template_part( 'template-parts/content' );
		nexa_pro_post_navigation();

		if ( nexa_pro_should_show_comments() ) {
			comments_template();
		}
	endwhile;
	?>
</div>

<?php
get_footer();

