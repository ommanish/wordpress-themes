<?php
/**
 * Comments template.
 *
 * @package Nexa_Pro
 */

if ( post_password_required() ) {
	return;
}
?>

<section id="comments" class="comments-area">
	<?php if ( have_comments() ) : ?>
		<h2 class="comments-title">
			<?php
			$nexa_pro_comment_count = get_comments_number();

			if ( '1' === $nexa_pro_comment_count ) {
				esc_html_e( 'One Comment', 'nexa-pro' );
			} else {
				printf(
					/* translators: %s: Number of comments. */
					esc_html( _nx( '%s Comment', '%s Comments', $nexa_pro_comment_count, 'comments title', 'nexa-pro' ) ),
					esc_html( number_format_i18n( $nexa_pro_comment_count ) )
				);
			}
			?>
		</h2>

		<?php
		the_comments_navigation(
			array(
				'prev_text' => esc_html__( 'Older comments', 'nexa-pro' ),
				'next_text' => esc_html__( 'Newer comments', 'nexa-pro' ),
			)
		);
		?>

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'avatar_size' => 56,
					'style'       => 'ol',
					'short_ping'  => true,
				)
			);
			?>
		</ol>

		<?php
		the_comments_navigation(
			array(
				'prev_text' => esc_html__( 'Older comments', 'nexa-pro' ),
				'next_text' => esc_html__( 'Newer comments', 'nexa-pro' ),
			)
		);
		?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'nexa-pro' ); ?></p>
	<?php endif; ?>

	<?php comment_form(); ?>
</section>

