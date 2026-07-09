<?php
/**
 * Search form template.
 *
 * @package Nexa_Pro
 */

$nexa_pro_unique_id = wp_unique_id( 'search-form-' );
?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="search-form__label" for="<?php echo esc_attr( $nexa_pro_unique_id ); ?>">
		<?php esc_html_e( 'Search', 'nexa-pro' ); ?>
	</label>
	<div class="search-form__controls">
		<input
			type="search"
			id="<?php echo esc_attr( $nexa_pro_unique_id ); ?>"
			class="search-field"
			placeholder="<?php echo esc_attr_x( 'Search...', 'placeholder', 'nexa-pro' ); ?>"
			value="<?php echo esc_attr( get_search_query() ); ?>"
			name="s"
		>
		<button type="submit" class="search-submit">
			<?php echo esc_html_x( 'Search', 'submit button', 'nexa-pro' ); ?>
		</button>
	</div>
</form>

