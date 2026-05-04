<?php
/**
 * Grupo de filtros — Color (One Piece TCG).
 *
 * Diseño de referencia: docs/design-onplay-cl/project/listing-onepiece.jsx
 * (botones 2-col con swatch circular + letra + label, borde carmesí cuando
 * está activo).
 *
 * @package Onplay
 *
 * @var array $args  { state: { op_color: string[] }, colors: array }
 */

defined( 'ABSPATH' ) || exit;

/** @var array $args */
$active = isset( $args['state']['op_color'] ) ? (array) $args['state']['op_color'] : array();
/** @var array $colors */
$colors = isset( $args['colors'] ) ? $args['colors'] : array();
?>
<div class="filter-group" data-group="op_color">
	<button type="button" class="filter-group__head" aria-expanded="true">
		<span class="filter-group__title"><?php esc_html_e( 'Color', 'onplay' ); ?></span>
		<span class="filter-group__chev" aria-hidden="true">▼</span>
	</button>
	<div class="filter-group__body">
		<div class="op-filters__grid op-filters__grid--cols-2">
			<?php foreach ( $colors as $code => $meta ) :
				$is_active = in_array( $code, $active, true );
				$bg        = isset( $meta['bg'] ) ? $meta['bg'] : '#444';
				$fg        = isset( $meta['fg'] ) ? $meta['fg'] : '#fff';
				$letter    = isset( $meta['letter'] ) ? $meta['letter'] : '?';
				$style     = sprintf( 'background:%s;color:%s;', $bg, $fg );
				if ( ! empty( $meta['border'] ) ) {
					$style .= sprintf( 'border-color:%s;', $meta['border'] );
				}
				?>
				<button
					type="button"
					class="op-filters__option<?php echo $is_active ? ' is-active' : ''; ?>"
					data-filter="op_color"
					data-value="<?php echo esc_attr( $code ); ?>"
					aria-pressed="<?php echo $is_active ? 'true' : 'false'; ?>"
				>
					<span class="op-filters__swatch" style="<?php echo esc_attr( $style ); ?>" aria-hidden="true"><?php echo esc_html( $letter ); ?></span>
					<span class="op-filters__label"><?php echo esc_html( $meta['label'] ); ?></span>
				</button>
			<?php endforeach; ?>
		</div>
	</div>
</div>
