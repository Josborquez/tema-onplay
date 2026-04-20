<?php
/**
 * Single product content — layout 2-col (imagen | detalles).
 *
 * @package Onplay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;
if ( ! $product instanceof WC_Product ) {
	$product = wc_get_product( get_the_ID() );
}
if ( ! $product instanceof WC_Product ) {
	return;
}

$product_id   = $product->get_id();
$sku          = $product->get_sku();
$parts        = onplay_parse_sku( $sku );
$is_foil      = onplay_is_foil( $product_id );
$rarity       = onplay_get_rarity( $product_id );
$stock        = (int) $product->get_stock_quantity();
$price        = (float) $product->get_price();
$title_raw    = get_the_title( $product_id );
$title_clean  = trim( preg_replace( '/\s*\(Foil\)\s*/i', '', $title_raw ) );
$mana_cost    = (string) get_post_meta( $product_id, '_onplay_mana_cost', true );
$type_line    = (string) get_post_meta( $product_id, '_onplay_type_line', true );
$oracle_text  = (string) get_post_meta( $product_id, '_onplay_oracle_text', true );
$collector    = (string) get_post_meta( $product_id, '_onplay_collector_number', true );
$legal        = onplay_get_legal_formats( $product_id );

$rarity_class = 'badge-common';
$rarity_upper = strtoupper( $rarity ? $rarity : 'common' );
switch ( $rarity ) {
	case 'mythic':
		$rarity_class = 'badge-mythic';
		break;
	case 'rare':
		$rarity_class = 'badge-rare';
		break;
	case 'uncommon':
		$rarity_class = 'badge-uncommon';
		break;
}

$category_terms = get_the_terms( $product_id, 'product_cat' );
$set_name       = '';
$set_term_id    = 0;
if ( is_array( $category_terms ) && ! empty( $category_terms ) ) {
	foreach ( $category_terms as $t ) {
		if ( $t->parent > 0 ) {
			$set_name    = $t->name;
			$set_term_id = $t->term_id;
			break;
		}
	}
	if ( '' === $set_name ) {
		$set_name    = $category_terms[0]->name;
		$set_term_id = $category_terms[0]->term_id;
	}
}
?>
<div class="pdp-breadcrumb">
	<div class="container">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'onplay' ); ?></a>
		<span class="sep">/</span>
		<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"><?php esc_html_e( 'Magic', 'onplay' ); ?></a>
		<?php if ( $set_name && $set_term_id ) : ?>
			<span class="sep">/</span>
			<a href="<?php echo esc_url( get_term_link( $set_term_id, 'product_cat' ) ); ?>"><?php echo esc_html( $set_name ); ?></a>
		<?php endif; ?>
		<span class="sep">/</span>
		<span class="current"><?php echo esc_html( $title_clean ); ?></span>
	</div>
</div>

<div class="container pdp">

	<div class="pdp__image">
		<?php
		if ( has_post_thumbnail( $product_id ) ) {
			// Imagen full + sizes attribute para que WP genere srcset responsivo.
			// TCGPlayer usa slide de ~493px; definimos 500px desktop, 100vw mobile.
			echo get_the_post_thumbnail(
				$product_id,
				'full',
				array(
					'alt'   => esc_attr( $title_clean ),
					'sizes' => '(max-width: 640px) 100vw, (max-width: 960px) 60vw, 500px',
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
		<div class="pdp__image-rarity">
			<span class="badge <?php echo esc_attr( $rarity_class ); ?>"><?php echo esc_html( $rarity_upper ); ?></span>
			<?php if ( $is_foil ) : ?>
				<span class="badge badge-foil">FOIL</span>
			<?php endif; ?>
		</div>
		<div class="pdp__image-zoom-hint"><?php esc_html_e( 'Hover para zoom', 'onplay' ); ?></div>
	</div>

	<div class="pdp__details">

		<div class="pdp__kicker">
			<?php echo onplay_render_set_icon( $parts['set_code'], 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<span>
				<?php echo esc_html( $set_name ? $set_name : $parts['set_code'] ); ?>
				<?php if ( $collector || $parts['collector_number'] ) : ?>
					· #<?php echo esc_html( $collector ? $collector : $parts['collector_number'] ); ?>
				<?php endif; ?>
			</span>
		</div>

		<h1 class="pdp__title"><?php echo esc_html( $title_clean ); ?></h1>

		<div class="pdp__meta-row">
			<?php if ( $mana_cost ) : ?>
				<span class="mana-cost"><?php echo onplay_render_mana_cost( $mana_cost, 'md' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<?php endif; ?>
			<?php if ( $type_line ) : ?>
				<span class="divider"></span>
				<span class="pdp__meta-row-type"><?php echo esc_html( $type_line ); ?></span>
			<?php endif; ?>
			<?php if ( $rarity ) : ?>
				<span class="badge <?php echo esc_attr( $rarity_class ); ?>"><?php echo esc_html( $rarity_upper ); ?></span>
			<?php endif; ?>
		</div>

		<?php if ( $oracle_text || ! empty( $legal ) ) : ?>
			<div class="pdp__oracle">
				<?php if ( $oracle_text ) : ?>
					<?php echo onplay_render_oracle_text( $oracle_text ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endif; ?>
				<?php if ( ! empty( $legal ) ) : ?>
					<div class="pdp__formats">
						<span class="pdp__formats-label"><?php esc_html_e( 'Legal en:', 'onplay' ); ?></span>
						<?php foreach ( $legal as $format ) :
							$term = get_term_by( 'name', $format, 'tcg_format_legal' );
							$url  = $term ? get_term_link( $term ) : '#';
							?>
							<a class="pdp__format" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $format ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="pdp__price-card">
			<div>
				<div class="pdp__price-label"><?php esc_html_e( 'Precio', 'onplay' ); ?></div>
				<div class="pdp__price-amount"><?php echo esc_html( onplay_format_clp( $price ) ); ?></div>
			</div>
			<div class="pdp__stock">
				<?php if ( $stock > 0 ) : ?>
					<div><span class="mono"><?php echo (int) $stock; ?></span> <?php esc_html_e( 'disponibles', 'onplay' ); ?></div>
					<div><?php echo esc_html( sprintf( '%s · %s', $parts['condition'], $parts['language'] ) ); ?></div>
				<?php else : ?>
					<div class="mono"><?php esc_html_e( 'Agotado', 'onplay' ); ?></div>
				<?php endif; ?>
			</div>
		</div>

		<div class="pdp__actions">
			<?php woocommerce_template_single_add_to_cart(); ?>
		</div>

		<?php get_template_part( 'template-parts/variant-table' ); ?>

		<dl class="pdp__attrs">
			<div>
				<dt><?php esc_html_e( 'SKU', 'onplay' ); ?></dt>
				<dd class="mono"><?php echo esc_html( $sku ); ?></dd>
			</div>
			<?php if ( $parts['condition'] ) : ?>
				<div>
					<dt><?php esc_html_e( 'Condición', 'onplay' ); ?></dt>
					<dd class="mono"><?php echo esc_html( $parts['condition'] ); ?></dd>
				</div>
			<?php endif; ?>
			<?php if ( $parts['language'] ) : ?>
				<div>
					<dt><?php esc_html_e( 'Idioma', 'onplay' ); ?></dt>
					<dd class="mono"><?php echo esc_html( $parts['language'] ); ?></dd>
				</div>
			<?php endif; ?>
			<div>
				<dt><?php esc_html_e( 'Foil', 'onplay' ); ?></dt>
				<dd><?php echo $is_foil ? esc_html__( 'Sí', 'onplay' ) : esc_html__( 'No', 'onplay' ); ?></dd>
			</div>
			<?php $artist = get_post_meta( $product_id, '_onplay_artist', true ); ?>
			<?php if ( $artist ) : ?>
				<div>
					<dt><?php esc_html_e( 'Artista', 'onplay' ); ?></dt>
					<dd><?php echo esc_html( $artist ); ?></dd>
				</div>
			<?php endif; ?>
		</dl>

	</div>
</div>
