<?php
/**
 * Grupo de filtros — Illustration Type (Normal / Alternate Art).
 *
 * 2 botones binarios. NO es radio — multi-select para soportar `?op_alt=normal,alt`
 * que equivale a no filtrar. CA-4 lo cubre.
 *
 * @package Onplay
 *
 * @var array $args  { state: { op_alt: string[] }, illustrations: array<string,string> }
 */

defined( 'ABSPATH' ) || exit;

$active = isset( $args['state']['op_alt'] ) ? (array) $args['state']['op_alt'] : array();
$opts   = isset( $args['illustrations'] ) ? $args['illustrations'] : array();
?>
<div class="filter-group" data-group="op_alt">
	<button type="button" class="filter-group__head" aria-expanded="true">
		<span class="filter-group__title"><?php esc_html_e( 'Tipo de ilustración', 'onplay' ); ?></span>
		<span class="filter-group__chev" aria-hidden="true">▼</span>
	</button>
	<div class="filter-group__body">
		<div class="op-filters__grid op-filters__grid--cols-2">
			<?php foreach ( $opts as $code => $label ) :
				$is_active = in_array( $code, $active, true );
				?>
				<button
					type="button"
					class="op-filters__pill<?php echo $is_active ? ' is-active' : ''; ?>"
					data-filter="op_alt"
					data-value="<?php echo esc_attr( $code ); ?>"
					aria-pressed="<?php echo $is_active ? 'true' : 'false'; ?>"
				><?php echo esc_html( $label ); ?></button>
			<?php endforeach; ?>
		</div>
	</div>
</div>
