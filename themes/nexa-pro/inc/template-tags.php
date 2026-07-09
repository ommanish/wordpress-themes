<?php
/**
 * Template tag helpers.
 *
 * @package Nexa_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Print posted-on date markup.
 *
 * @return void
 */
function nexa_pro_posted_on() {
	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';

	if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
		$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
	}

	$time_string = sprintf(
		$time_string,
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date() ),
		esc_attr( get_the_modified_date( DATE_W3C ) ),
		esc_html( get_the_modified_date() )
	);

	printf(
		'<span class="posted-on">%1$s <a href="%2$s" rel="bookmark">%3$s</a></span>',
		esc_html__( 'Posted on', 'nexa-pro' ),
		esc_url( get_permalink() ),
		wp_kses_post( $time_string )
	);
}

/**
 * Print author markup.
 *
 * @return void
 */
function nexa_pro_posted_by() {
	printf(
		'<span class="byline">%1$s <span class="author vcard"><a class="url fn n" href="%2$s">%3$s</a></span></span>',
		esc_html__( 'by', 'nexa-pro' ),
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		esc_html( get_the_author() )
	);
}

/**
 * Print categories and tags for posts.
 *
 * @return void
 */
function nexa_pro_entry_taxonomies() {
	if ( 'post' !== get_post_type() ) {
		return;
	}

	$categories_list = get_the_category_list( esc_html__( ', ', 'nexa-pro' ) );

	if ( $categories_list ) {
		printf(
			'<span class="cat-links">%1$s %2$s</span>',
			esc_html__( 'Filed under', 'nexa-pro' ),
			wp_kses_post( $categories_list )
		);
	}

	$tags_list = get_the_tag_list( '', esc_html__( ', ', 'nexa-pro' ) );

	if ( $tags_list ) {
		printf(
			'<span class="tags-links">%1$s %2$s</span>',
			esc_html__( 'Tagged', 'nexa-pro' ),
			wp_kses_post( $tags_list )
		);
	}
}

/**
 * Print post metadata.
 *
 * @return void
 */
function nexa_pro_entry_meta() {
	if ( 'post' !== get_post_type() ) {
		return;
	}

	echo '<div class="entry-meta">';
	nexa_pro_posted_on();
	nexa_pro_posted_by();
	echo '</div>';
}

/**
 * Print post footer metadata.
 *
 * @return void
 */
function nexa_pro_entry_footer() {
	if ( 'post' !== get_post_type() ) {
		return;
	}

	echo '<footer class="entry-footer">';
	nexa_pro_entry_taxonomies();
	echo '</footer>';
}

/**
 * Print a featured image for the current post.
 *
 * @param string $size Image size.
 * @param bool   $link Whether to link the image to the post.
 * @return void
 */
function nexa_pro_post_thumbnail( $size = 'large', $link = true ) {
	if ( post_password_required() || is_attachment() || ! has_post_thumbnail() ) {
		return;
	}

	if ( $link ) {
		?>
		<a class="entry-thumbnail" href="<?php echo esc_url( get_permalink() ); ?>" aria-hidden="true" tabindex="-1">
			<?php the_post_thumbnail( $size ); ?>
		</a>
		<?php
		return;
	}

	?>
	<div class="entry-thumbnail">
		<?php the_post_thumbnail( $size ); ?>
	</div>
	<?php
}

/**
 * Print accessible posts pagination.
 *
 * @return void
 */
function nexa_pro_posts_pagination() {
	the_posts_pagination(
		array(
			'mid_size'           => 1,
			'prev_text'          => esc_html__( 'Previous', 'nexa-pro' ),
			'next_text'          => esc_html__( 'Next', 'nexa-pro' ),
			'screen_reader_text' => esc_html__( 'Posts navigation', 'nexa-pro' ),
		)
	);
}

/**
 * Print accessible single post navigation.
 *
 * @return void
 */
function nexa_pro_post_navigation() {
	the_post_navigation(
		array(
			'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous post', 'nexa-pro' ) . '</span><span class="nav-title">%title</span>',
			'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next post', 'nexa-pro' ) . '</span><span class="nav-title">%title</span>',
		)
	);
}
