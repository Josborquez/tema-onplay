<?php
/**
 * Filters sidebar — sidebar facetado del listado.
 *
 * Render server-side inicial. La JS se hace cargo del estado a partir de los
 * inputs (sin re-render server salvo cambio de página/sort).
 *
 * @package Onplay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// M-OP-filtros: el sidebar de OP es estructuralmente distinto (sin sets, rareza
// ni idioma — esos no aplican al modelo de datos del Binder OP). Bail temprano
// y delegamos al render del módulo, que produce un <aside> equivalente.
if ( function_exists( 'onplay_op_is_archive' ) && onplay_op_is_archive() ) {
	onplay_op_render_sidebar();
	return;
}

$set_facets   = onplay_get_set_facet_counts();
$lang_terms   = onplay_get_attribute_terms( 'pa_idioma' );
$cond_terms   = onplay_get_attribute_terms( 'pa_estado' );

// Conditions: el diseño expone NM/LP/SP/MP/HP/DMG aunque DB hoy solo tenga NM.
$condition_codes = array( 'NM', 'LP', 'SP', 'MP', 'HP', 'DMG' );
$active_conds    = array();
foreach ( $cond_terms as $t ) {
	$active_conds[ strtoupper( $t['name'] ) ] = $t['count'];
}

// Idiomas: el diseño expone EN/ES/JP, pero en DB pueden existir más (JA, ZH, PT, IT).
// Mostramos los presentes en DB ordenados por count desc.
?>
<aside class="shop-sidebar" data-filters-sidebar>

	<div class="filter-group" data-group="color">
		<button type="button" class="filter-group__head" aria-expanded="true">
			<span class="filter-group__title"><?php esc_html_e( 'Colores', 'onplay' ); ?></span>
			<span class="filter-group__chev" aria-hidden="true">▼</span>
		</button>
		<div class="filter-group__body">
			<div class="color-chips">
				<?php
				$colors = array(
					'W' => __( 'White', 'onplay' ),
					'U' => __( 'Blue', 'onplay' ),
					'B' => __( 'Black', 'onplay' ),
					'R' => __( 'Red', 'onplay' ),
					'G' => __( 'Green', 'onplay' ),
					'C' => __( 'Incoloro', 'onplay' ),
				);
				foreach ( $colors as $code => $label ) :
					?>
					<button
						type="button"
						class="color-chip"
						data-filter="color"
						data-value="<?php echo esc_attr( $code ); ?>"
						aria-pressed="false"
					>
						<?php echo onplay_render_mana_symbol( $code, 'sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span class="color-chip__label"><?php echo esc_html( 'C' === $code ? $label : $code ); ?></span>
					</button>
					<?php
				endforeach;
				?>
			</div>
		</div>
	</div>

	<div class="filter-group" data-group="set">
		<button type="button" class="filter-group__head" aria-expanded="true">
			<span class="filter-group__title"><?php esc_html_e( 'Set (edición)', 'onplay' ); ?></span>
			<span class="filter-group__chev" aria-hidden="true">▼</span>
		</button>
		<div class="filter-group__body">
			<input
				type="search"
				class="input filter-set-search"
				placeholder="<?php esc_attr_e( 'Buscar set…', 'onplay' ); ?>"
				autocomplete="off"
				data-set-search
			/>
			<div class="filter-set-list" data-set-list>
				<?php foreach ( $set_facets as $slug => $facet ) : ?>
					<label class="filter-checkbox" data-set-item data-set-name="<?php echo esc_attr( strtolower( $facet['name'] ) ); ?>">
						<input
							type="checkbox"
							data-filter="set"
							value="<?php echo esc_attr( $slug ); ?>"
						/>
						<?php echo onplay_render_set_badge( substr( $facet['name'], 0, 3 ), 12 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span class="filter-checkbox__label"><?php echo esc_html( $facet['name'] ); ?></span>
						<span class="filter-checkbox__count mono"><?php echo esc_html( (string) $facet['count'] ); ?></span>
					</label>
				<?php endforeach; ?>
				<?php if ( empty( $set_facets ) ) : ?>
					<p class="filter-empty"><?php esc_html_e( 'Sin sets disponibles aún.', 'onplay' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div class="filter-group" data-group="rarity">
		<button type="button" class="filter-group__head" aria-expanded="true">
			<span class="filter-group__title"><?php esc_html_e( 'Rareza', 'onplay' ); ?></span>
			<span class="filter-group__chev" aria-hidden="true">▼</span>
		</button>
		<div class="filter-group__body">
			<?php
			$rarities = array(
				'mythic'   => __( 'Mythic Rare', 'onplay' ),
				'rare'     => __( 'Rare', 'onplay' ),
				'uncommon' => __( 'Uncommon', 'onplay' ),
				'common'   => __( 'Common', 'onplay' ),
			);
			foreach ( $rarities as $slug => $label ) :
				?>
				<label class="filter-checkbox">
					<input type="checkbox" data-filter="rarity" value="<?php echo esc_attr( $slug ); ?>" />
					<span class="filter-checkbox__label"><?php echo esc_html( $label ); ?></span>
				</label>
				<?php
			endforeach;
			?>
		</div>
	</div>

	<div class="filter-group" data-group="condition">
		<button type="button" class="filter-group__head" aria-expanded="true">
			<span class="filter-group__title"><?php esc_html_e( 'Condición', 'onplay' ); ?></span>
			<span class="filter-group__chev" aria-hidden="true">▼</span>
		</button>
		<div class="filter-group__body">
			<div class="condition-grid">
				<?php foreach ( $condition_codes as $code ) :
					$disabled = ! isset( $active_conds[ $code ] );
					?>
					<button
						type="button"
						class="cond-chip<?php echo $disabled ? ' is-disabled' : ''; ?>"
						data-filter="condition"
						data-value="<?php echo esc_attr( $code ); ?>"
						aria-pressed="false"
						<?php disabled( $disabled, true ); ?>
						<?php if ( $disabled ) : ?>title="<?php esc_attr_e( 'Sin productos en esta condición todavía', 'onplay' ); ?>"<?php endif; ?>
					>
						<?php echo esc_html( $code ); ?>
					</button>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<div class="filter-group" data-group="foil">
		<button type="button" class="filter-group__head" aria-expanded="true">
			<span class="filter-group__title"><?php esc_html_e( 'Foil', 'onplay' ); ?></span>
			<span class="filter-group__chev" aria-hidden="true">▼</span>
		</button>
		<div class="filter-group__body">
			<div class="foil-toggle" role="radiogroup">
				<?php
				$foils = array(
					'all'     => __( 'Todo', 'onplay' ),
					'regular' => __( 'Regular', 'onplay' ),
					'foil'    => __( 'Foil', 'onplay' ),
				);
				foreach ( $foils as $val => $label ) :
					$is_default = ( 'all' === $val );
					?>
					<button
						type="button"
						class="foil-toggle__btn<?php echo $is_default ? ' is-active' : ''; ?>"
						data-filter="foil"
						data-value="<?php echo esc_attr( $val ); ?>"
						aria-pressed="<?php echo $is_default ? 'true' : 'false'; ?>"
						role="radio"
						aria-checked="<?php echo $is_default ? 'true' : 'false'; ?>"
					><?php echo esc_html( $label ); ?></button>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<div class="filter-group" data-group="lang">
		<button type="button" class="filter-group__head" aria-expanded="true">
			<span class="filter-group__title"><?php esc_html_e( 'Idioma', 'onplay' ); ?></span>
			<span class="filter-group__chev" aria-hidden="true">▼</span>
		</button>
		<div class="filter-group__body">
			<div class="lang-grid">
				<?php if ( ! empty( $lang_terms ) ) : ?>
					<?php foreach ( $lang_terms as $term ) : ?>
						<button
							type="button"
							class="lang-chip"
							data-filter="lang"
							data-value="<?php echo esc_attr( strtoupper( $term['name'] ) ); ?>"
							aria-pressed="false"
						><?php echo esc_html( strtoupper( $term['name'] ) ); ?></button>
					<?php endforeach; ?>
				<?php else : ?>
					<p class="filter-empty"><?php esc_html_e( 'Sin idiomas indexados.', 'onplay' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div class="filter-group" data-group="price">
		<button type="button" class="filter-group__head" aria-expanded="true">
			<span class="filter-group__title"><?php esc_html_e( 'Precio (CLP)', 'onplay' ); ?></span>
			<span class="filter-group__chev" aria-hidden="true">▼</span>
		</button>
		<div class="filter-group__body">
			<div class="price-inputs">
				<input
					type="number"
					class="input price-input price-input--min"
					min="0"
					step="100"
					value="0"
					aria-label="<?php esc_attr_e( 'Precio mínimo', 'onplay' ); ?>"
					data-filter="price_min"
				/>
				<span class="price-inputs__sep">—</span>
				<input
					type="number"
					class="input price-input price-input--max"
					min="0"
					step="100"
					value="500000"
					aria-label="<?php esc_attr_e( 'Precio máximo', 'onplay' ); ?>"
					data-filter="price_max"
				/>
			</div>
			<input
				type="range"
				class="price-slider"
				min="0"
				max="500000"
				step="1000"
				value="500000"
				aria-label="<?php esc_attr_e( 'Slider precio máximo', 'onplay' ); ?>"
				data-price-slider
			/>
			<div class="price-scale mono">
				<span>$0</span>
				<span>$500.000</span>
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
				<input
					type="checkbox"
					data-filter="in_stock"
					checked
				/>
				<span class="stock-toggle__track" aria-hidden="true">
					<span class="stock-toggle__thumb"></span>
				</span>
				<span class="stock-toggle__label"><?php esc_html_e( 'Solo con stock', 'onplay' ); ?></span>
			</label>
		</div>
	</div>

</aside>
