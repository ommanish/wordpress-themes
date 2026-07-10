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
			<div class="site-footer__main">
				<?php nexa_pro_render_footer_branding(); ?>
				<?php nexa_pro_render_footer_sections(); ?>
			</div>

			<?php nexa_pro_render_footer_legal(); ?>
		</div>
	</footer>

	<?php nexa_pro_render_schedule_modal(); ?>
</div>

<?php wp_footer(); ?>
</body>
</html>
