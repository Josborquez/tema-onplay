<?php
/**
 * Product card — used by archive loop and related queries.
 *
 * @global WC_Product $product
 * @package Onplay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;
if ( ! $product instanceof WC_Product ) {
	return;
}

$product_id = $product->get_id();
$sku        = $product->get_sku();
$parts      = onplay_parse_sku( $sku );
$is_foil    = onplay_is_foil( $product_id );
$rarity     = onplay_get_rarity( $product_id );
$stock      = (int) $product->get_stock_quantity();
$price      = (float) $product->get_price();
$title      = get_the_title( $product_id );
$title_clean = trim( preg_replace( '/\s*\(Foil\)\s*/i', '', $title ) );

$rarity_class = 'badge-common';
$rarity_label = 'C';
switch ( $rarity ) {
	case 'mythic':
		$rarity_class = 'badge-mythic';
		$rarity_label = 'M';
		break;
	case 'rare':
		$rarity_class = 'badge-rare';
		$rarity_label = 'R';
		break;
	case 'uncommon':
		$rarity_class = 'badge-uncommon';
		$rarity_label = 'U';
		break;
}

$stock_class = $stock > 0 && $stock <= 3 ? 'badge-stock-low' : 'badge-stock-ok';
$stock_label = $stock > 0 && $stock <= 3
	? sprintf( __( 'Últimas %d', 'onplay' ), $stock )
	: sprintf( __( '%d disp.', 'onplay' ), $stock );

$colors_json = get_post_meta( $product_id, '_onplay_colors', true );
$colors      = $colors_json ? json_decode( $colors_json, true ) : array();
$colors      = is_array( $colors ) ? $colors : array();

$category_terms = get_the_terms( $product_id, 'product_cat' );
$set_name       = '';
if ( is_array( $category_terms ) && ! empty( $category_terms ) ) {
	// Toma el primer child term (ej. "Shadows over Innistrad Remastered"), no la raíz "Magic: The Gathering".
	foreach ( $category_terms as $t ) {
		if ( $t->parent > 0 ) {
			$set_name = $t->name;
			break;
		}
	}
	if ( '' === $set_name ) {
		$set_name = $category_terms[0]->name;
	}
}
?>
<a class="card-product" href="<?php echo esc_url( get_permalink( $product_id ) ); ?>">
	<div class="card-img-wrap">
		<?php
		if ( has_post_thumbnail( $product_id ) ) {
			echo get_the_post_thumbnail(
				$product_id,
				'medium_large',
				array(
					'class'   => 'card-img',
					'loading' => 'lazy',
					'alt'     => esc_attr( $title_clean ),
				)
			);
		} else {
			?>
			<div class="card-img-fallback">
				<div class="card-img-fallback__name"><?php echo esc_html( $title_clean ); ?></div>
				<div class="card-img-fallback__meta"><?php echo esc_html( $parts['set_code'] . ' · ' . $parts['collector_number'] ); ?></div>
			</div>
			<?php
		}
		?>
		<div class="card-product__pips-tl">
			<span class="badge <?php echo esc_attr( $rarity_class ); ?>"><?php echo esc_html( $rarity_label ); ?></span>
			<?php if ( $is_foil ) : ?>
				<span class="badge badge-foil">FOIL</span>
			<?php endif; ?>
		</div>
		<?php if ( ! empty( $colors ) ) : ?>
			<div class="card-product__colors-br">
				<?php foreach ( $colors as $c ) : ?>
					<?php echo onplay_render_mana_symbol( $c, 'sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
	<div class="card-meta">
		<div class="card-product__title-wrap">
			<div class="card-product__title"><?php echo esc_html( $title_clean ); ?></div>
			<div class="card-product__set">
				<?php echo onplay_render_set_icon( $parts['set_code'], 12 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php echo esc_html( $set_name ? $set_name : $parts['set_code'] ); ?></span>
			</div>
		</div>
		<div class="card-product__attrs">
			<?php if ( $parts['condition'] ) : ?>
				<span class="mono"><?php echo esc_html( $parts['condition'] ); ?></span>
				<span class="sep">·</span>
			<?php endif; ?>
			<?php if ( $parts['language'] ) : ?>
				<span class="mono"><?php echo esc_html( $parts['language'] ); ?></span>
				<span class="sep">·</span>
			<?php endif; ?>
			<?php if ( $stock > 0 ) : ?>
				<span class="badge <?php echo esc_attr( $stock_class ); ?>"><?php echo esc_html( $stock_label ); ?></span>
			<?php else : ?>
				<span class="badge badge-common"><?php esc_html_e( 'Agotado', 'onplay' ); ?></span>
			<?php endif; ?>
		</div>
		<div class="card-product__price-row">
			<div class="card-product__price"><?php echo esc_html( onplay_format_clp( $price ) ); ?></div>
		</div>
	</div>
</a>
