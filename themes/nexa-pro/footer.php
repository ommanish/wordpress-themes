<?php
/**
 * The footer for Nexa Pro.
 *
 * @package Nexa_Pro
 */

?>
	</main>

	<footer class="site-footer" role="contentinfo">
		<div class="site-footer__inner nexa-pro-container">
			<?php
			if ( has_nav_menu( 'footer' ) ) :
				?>
				<nav class="footer-navigation" aria-label="<?php esc_attr_e( 'Footer menu', 'nexa-pro' ); ?>">
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
			<?php endif; ?>

			<p class="site-footer__credit">
				<?php
				printf(
					/* translators: %s: Site title. */
					esc_html__( '%s. All rights reserved.', 'nexa-pro' ),
					esc_html( get_bloginfo( 'name' ) )
				);
				?>
			</p>
		</div>
	</footer>
</div>

<?php wp_footer(); ?>
</body>
</html>

