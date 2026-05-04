<?php
/**
 * Grupo de filtros — Tipo de carta (One Piece TCG).
 *
 * 4 botones en grid 2-col, mono-uppercase, mismo tratamiento que las píldoras
 * de condición Magic.
 *
 * @package Onplay
 *
 * @var array $args  { state: { op_type: string[] }, card_types: array<string,string> }
 */

defined( 'ABSPATH' ) || exit;

$active = isset( $args['state']['op_type'] ) ? (array) $args['state']['op_type'] : array();
$types  = isset( $args['card_types'] ) ? $args['card_types'] : array();
?>
<div class="filter-group" data-group="op_type">
	<button type="button" class="filter-group__head" aria-expanded="true">
		<span class="filter-group__title"><?php esc_html_e( 'Tipo de carta', 'onplay' ); ?></span>
		<span class="filter-group__chev" aria-hidden="true">▼</span>
	</button>
	<div class="filter-group__body">
		<div class="op-filters__grid op-filters__grid--cols-2">
			<?php foreach ( $types as $code => $label ) :
				$is_active = in_array( $code, $active, true );
				?>
				<button
					type="button"
					class="op-filters__pill<?php echo $is_active ? ' is-active' : ''; ?>"
					data-filter="op_type"
					data-value="<?php echo esc_attr( $code ); ?>"
					aria-pressed="<?php echo $is_active ? 'true' : 'false'; ?>"
				><?php echo esc_html( $label ); ?></button>
			<?php endforeach; ?>
		</div>
	</div>
</div>
