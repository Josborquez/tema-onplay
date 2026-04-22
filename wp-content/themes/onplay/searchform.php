<?php
/**
 * Form de búsqueda global (WP nativo — posts/pages).
 *
 * El input de búsqueda del header es el de producto (apunta a la tienda con ?q=).
 * Este form es el que usa `get_search_form()` en search.php y en widgets.
 *
 * @package Onplay
 */

$unique_id = 'onplay-searchform-' . wp_rand();
?>
<form role="search" method="get" class="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="<?php echo esc_attr( $unique_id ); ?>" class="screen-reader-text">
		<?php esc_html_e( 'Buscar:', 'onplay' ); ?>
	</label>
	<div class="searchform__wrap">
		<svg class="searchform__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
			<circle cx="11" cy="11" r="8"/>
			<path d="m21 21-4.3-4.3"/>
		</svg>
		<input
			id="<?php echo esc_attr( $unique_id ); ?>"
			type="search"
			class="input searchform__input"
			placeholder="<?php esc_attr_e( 'Buscar en el sitio…', 'onplay' ); ?>"
			value="<?php echo esc_attr( get_search_query() ); ?>"
			name="s"
			autocomplete="off"
		/>
		<button type="submit" class="btn btn-primary searchform__submit">
			<?php esc_html_e( 'Buscar', 'onplay' ); ?>
		</button>
	</div>
</form>
