<?php
/**
 * Variant table — todas las variantes en stock de la misma carta
 * (cualquier set, condición, idioma, foil/non-foil).
 *
 * Espera $product (WC_Product) en el contexto del PDP.
 *
 * @package Onplay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;
if ( ! $product instanceof WC_Product ) {
	return;
}

$variants = onplay_get_variants_by_card( (int) $product->get_id() );

if ( empty( $variants ) ) {
	return;
}

$conditions     = array( 'NM', 'LP', 'SP', 'MP', 'HP' );
$present_conds  = array();
$has_foil       = false;
$has_regular    = false;
foreach ( $variants as $v ) {
	if ( '' !== $v['condition'] ) {
		$present_conds[ $v['condition'] ] = true;
	}
	if ( $v['is_foil'] ) {
		$has_foil = true;
	} else {
		$has_regular = true;
	}
}
?>
<section class="variant-table" aria-labelledby="variant-table-title">

	<header class="variant-table__header">
		<div>
			<h2 id="variant-table-title" class="variant-table__title">
				<?php esc_html_e( 'Selecciona tu versión', 'onplay' ); ?>
			</h2>
			<p class="variant-table__subtitle">
				<?php esc_html_e( 'Variantes disponibles', 'onplay' ); ?>
				<span class="variant-table__count">
					·
					<?php
					echo esc_html(
						sprintf(
							/* translators: %d: número de variantes en stock */
							_n( '%d variante en stock', '%d variantes en stock', count( $variants ), 'onplay' ),
							count( $variants )
						)
					);
					?>
				</span>
			</p>
		</div>
	</header>

	<div class="variant-table__filters" role="group" aria-label="<?php esc_attr_e( 'Filtrar variantes', 'onplay' ); ?>">

		<div class="variant-table__filter-group" data-filter-group="condition">
			<span class="variant-table__filter-label"><?php esc_html_e( 'Condición', 'onplay' ); ?></span>
			<?php foreach ( $conditions as $cond ) :
				$is_present = isset( $present_conds[ $cond ] );
				?>
				<button
					type="button"
					class="chip<?php echo $is_present ? '' : ' is-disabled'; ?>"
					data-filter-condition="<?php echo esc_attr( $cond ); ?>"
					aria-pressed="false"
					<?php disabled( ! $is_present ); ?>
				>
					<?php echo esc_html( $cond ); ?>
				</button>
			<?php endforeach; ?>
		</div>

		<div class="variant-table__filter-group" data-filter-group="foil">
			<span class="variant-table__filter-label"><?php esc_html_e( 'Foil', 'onplay' ); ?></span>
			<button type="button" class="chip is-active" data-filter-foil="all" aria-pressed="true">
				<?php esc_html_e( 'Todo', 'onplay' ); ?>
			</button>
			<button type="button" class="chip<?php echo $has_regular ? '' : ' is-disabled'; ?>" data-filter-foil="regular" aria-pressed="false" <?php disabled( ! $has_regular ); ?>>
				<?php esc_html_e( 'Regular', 'onplay' ); ?>
			</button>
			<button type="button" class="chip<?php echo $has_foil ? '' : ' is-disabled'; ?>" data-filter-foil="foil" aria-pressed="false" <?php disabled( ! $has_foil ); ?>>
				<?php esc_html_e( 'Foil', 'onplay' ); ?>
			</button>
		</div>

	</div>

	<div class="variant-table__scroll">
		<table class="variant-table__table" data-variant-table>
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Set / Edición', 'onplay' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Cond.', 'onplay' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Idioma', 'onplay' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Foil', 'onplay' ); ?></th>
					<th scope="col" class="is-num"><?php esc_html_e( 'Precio', 'onplay' ); ?></th>
					<th scope="col" class="is-num"><?php esc_html_e( 'Stock', 'onplay' ); ?></th>
					<th scope="col" class="is-num"><?php esc_html_e( 'Cant.', 'onplay' ); ?></th>
					<th scope="col"><span class="screen-reader-text"><?php esc_html_e( 'Acción', 'onplay' ); ?></span></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $variants as $v ) : ?>
					<tr
						class="variant-row<?php echo $v['is_current'] ? ' is-current' : ''; ?>"
						data-product-id="<?php echo (int) $v['id']; ?>"
						data-condition="<?php echo esc_attr( $v['condition'] ); ?>"
						data-foil="<?php echo $v['is_foil'] ? 'foil' : 'regular'; ?>"
						data-set="<?php echo esc_attr( $v['set_code'] ); ?>"
						data-print-key="<?php echo esc_attr( $v['print_key'] ); ?>"
					>
						<td data-label="<?php esc_attr_e( 'Set / Edición', 'onplay' ); ?>">
							<div class="variant-row__set">
								<?php echo onplay_render_set_icon( $v['set_code'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<div class="variant-row__set-meta">
									<span class="variant-row__set-name"><?php echo esc_html( $v['set_name'] ? $v['set_name'] : $v['set_code'] ); ?></span>
									<span class="variant-row__set-code mono"><?php echo esc_html( $v['print_key'] ); ?></span>
								</div>
							</div>
						</td>
						<td data-label="<?php esc_attr_e( 'Cond.', 'onplay' ); ?>"><span class="mono"><?php echo esc_html( $v['condition'] ? $v['condition'] : '—' ); ?></span></td>
						<td data-label="<?php esc_attr_e( 'Idioma', 'onplay' ); ?>"><span class="mono"><?php echo esc_html( $v['language'] ? $v['language'] : '—' ); ?></span></td>
						<td data-label="<?php esc_attr_e( 'Foil', 'onplay' ); ?>">
							<?php if ( $v['is_foil'] ) : ?>
								<span class="badge badge-foil"><?php esc_html_e( 'Foil', 'onplay' ); ?></span>
							<?php else : ?>
								<span class="variant-row__dim"><?php esc_html_e( 'No', 'onplay' ); ?></span>
							<?php endif; ?>
						</td>
						<td class="is-num" data-label="<?php esc_attr_e( 'Precio', 'onplay' ); ?>">
							<span class="variant-row__price"><?php echo esc_html( onplay_format_clp( $v['price'] ) ); ?></span>
						</td>
						<td class="is-num" data-label="<?php esc_attr_e( 'Stock', 'onplay' ); ?>">
							<span class="mono"><?php echo (int) $v['stock']; ?></span>
						</td>
						<td class="is-num" data-label="<?php esc_attr_e( 'Cant.', 'onplay' ); ?>">
							<input
								class="variant-row__qty input"
								type="number"
								min="1"
								max="<?php echo (int) $v['stock']; ?>"
								step="1"
								value="1"
								inputmode="numeric"
								aria-label="<?php esc_attr_e( 'Cantidad', 'onplay' ); ?>"
							/>
						</td>
						<td>
							<button
								type="button"
								class="btn btn-primary variant-row__add"
								data-product-id="<?php echo (int) $v['id']; ?>"
								data-sku="<?php echo esc_attr( $v['sku'] ); ?>"
							>
								<?php esc_html_e( 'Agregar', 'onplay' ); ?>
							</button>
							<a class="variant-row__view" href="<?php echo esc_url( $v['permalink'] ); ?>" aria-label="<?php esc_attr_e( 'Ver ficha de esta variante', 'onplay' ); ?>" title="<?php esc_attr_e( 'Ver ficha', 'onplay' ); ?>">
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
									<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/>
									<circle cx="12" cy="12" r="3"/>
								</svg>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<p class="variant-table__empty" data-variant-table-empty hidden>
			<?php esc_html_e( 'Ninguna variante coincide con los filtros seleccionados.', 'onplay' ); ?>
		</p>
	</div>

</section>
