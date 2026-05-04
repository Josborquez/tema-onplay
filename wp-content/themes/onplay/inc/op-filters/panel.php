<?php
/**
 * M-OP-filtros — Render del sidebar OP.
 *
 * No registra hooks: la entrada es `onplay_op_render_sidebar()`, llamada desde
 * el fork temprano en `template-parts/filters-sidebar.php`. Reusa el markup
 * `.shop-sidebar / .filter-group / .filter-group__head / .filter-group__body`
 * para que el JS existente (`Filters` en main.js) tome los inputs sin saber
 * que son de OP — sólo necesita que `data-filter` y los keys del state coincidan.
 *
 * @package Onplay
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render del sidebar OP — entrada principal.
 *
 * @return void
 */
function onplay_op_render_sidebar() {
	$state = array(
		'op_color' => onplay_op_get_param( 'op_color' ),
		'op_type'  => onplay_op_get_param( 'op_type' ),
		'op_alt'   => onplay_op_get_param( 'op_alt' ),
	);
	// Normalización equivalente a la del state extension (para que `checked`
	// en SSR matchee aunque la URL traiga "red" en lowercase).
	$state['op_color'] = array_map( fn( $c ) => ucfirst( strtolower( $c ) ), $state['op_color'] );
	$state['op_type']  = array_map( 'strtoupper', $state['op_type'] );
	$state['op_alt']   = array_map( 'strtolower', $state['op_alt'] );

	$ctx = array(
		'state'         => $state,
		'colors'        => onplay_op_colors(),
		'card_types'    => onplay_op_card_types(),
		'illustrations' => onplay_op_illustration_types(),
	);
	?>
	<aside class="shop-sidebar shop-sidebar--op" data-filters-sidebar data-op-filters>

		<?php
		foreach ( array( 'color', 'card-type', 'illustration' ) as $group ) {
			get_template_part( 'template-parts/op-filters/group', $group, $ctx );
		}
		?>

		<div class="filter-group" data-group="price">
			<button type="button" class="filter-group__head" aria-expanded="true">
				<span class="filter-group__title"><?php esc_html_e( 'Precio (CLP)', 'onplay' ); ?></span>
				<span class="filter-group__chev" aria-hidden="true">▼</span>
			</button>
			<div class="filter-group__body">
				<div class="price-inputs">
					<input type="number" class="input price-input price-input--min" min="0" step="100" value="0" aria-label="<?php esc_attr_e( 'Precio mínimo', 'onplay' ); ?>" data-filter="price_min" />
					<span class="price-inputs__sep">—</span>
					<input type="number" class="input price-input price-input--max" min="0" step="100" value="100000" aria-label="<?php esc_attr_e( 'Precio máximo', 'onplay' ); ?>" data-filter="price_max" />
				</div>
				<input type="range" class="price-slider" min="0" max="100000" step="500" value="100000" aria-label="<?php esc_attr_e( 'Slider precio máximo', 'onplay' ); ?>" data-price-slider />
				<div class="price-scale mono">
					<span>$0</span>
					<span>$100.000</span>
				</div>
			</div>
		</div>

		<div class="filter-group" data-group="stock">
			<button type="button" class="filter-group__head" aria-expanded="true">
				<span class="filter-group__title"><?php esc_html_e( 'Disponibilidad', 'onplay' ); ?></span>
				<span class="filter-group__chev" aria-hidden="true">▼</span>
			</button>
			<div class="filter-group__body">
				<label class="stock-toggle">
					<input type="checkbox" data-filter="in_stock" checked />
					<span class="stock-toggle__track" aria-hidden="true">
						<span class="stock-toggle__thumb"></span>
					</span>
					<span class="stock-toggle__label"><?php esc_html_e( 'Solo con stock', 'onplay' ); ?></span>
				</label>
			</div>
		</div>

	</aside>
	<?php
}
