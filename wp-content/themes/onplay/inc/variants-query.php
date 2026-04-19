<?php
/**
 * Variants query — agrupa productos por nombre normalizado de carta.
 *
 * El diseño define una sola tabla "Variantes disponibles" con columna
 * Set/Edición + chips de filtro Condición/Foil. Por eso agrupamos por
 * NOMBRE de carta (normalizado), no por print_key — la tabla cubre
 * todas las impresiones de la misma carta y reemplaza la sección
 * "Otras impresiones" mencionada en CLAUDE.md sec 5.3.
 *
 * @package Onplay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const ONPLAY_VARIANTS_TRANSIENT_PREFIX = 'onplay_card_';
const ONPLAY_VARIANTS_TTL              = HOUR_IN_SECONDS;

/**
 * Resuelve todas las variantes en stock de la misma carta (cualquier set,
 * cualquier condición, idioma, foil/non-foil).
 *
 * @param int $product_id Producto de referencia (la fila de su SKU se marca como current).
 * @return array<int, array{
 *     id:int, sku:string, title:string, permalink:string,
 *     price:float, stock:int, is_foil:bool,
 *     condition:string, language:string,
 *     set_code:string, set_name:string, print_key:string,
 *     is_current:bool
 * }>
 */
function onplay_get_variants_by_card( $product_id ) {
	$product_id = (int) $product_id;
	if ( $product_id <= 0 ) {
		return array();
	}

	$current_product = wc_get_product( $product_id );
	if ( ! $current_product instanceof WC_Product ) {
		return array();
	}

	$current_sku  = (string) $current_product->get_sku();
	$current_name = onplay_normalize_card_name( (string) $current_product->get_name() );
	if ( '' === $current_name ) {
		return array();
	}

	$current_print_key = onplay_print_key_from_sku( $current_sku );

	$cache_key = ONPLAY_VARIANTS_TRANSIENT_PREFIX . md5( $current_name );
	$cached    = get_transient( $cache_key );
	if ( is_array( $cached ) ) {
		return onplay_sort_and_mark_variants( $cached, $current_sku, $current_print_key );
	}

	global $wpdb;

	// REPLACE() para colapsar "(Foil)" sin tocar índices; OK a esta escala (catálogo ~410-1000 productos).
	$rows = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT p.ID
			 FROM {$wpdb->posts} p
			 WHERE p.post_type = 'product'
			   AND p.post_status = 'publish'
			   AND TRIM(REPLACE(p.post_title, ' (Foil)', '')) = %s",
			$current_name
		)
	);

	$variants = array();

	if ( ! empty( $rows ) ) {
		foreach ( $rows as $pid ) {
			$product = wc_get_product( (int) $pid );
			if ( ! $product instanceof WC_Product ) {
				continue;
			}
			if ( ! $product->is_in_stock() ) {
				continue;
			}
			$stock = (int) $product->get_stock_quantity();
			if ( $stock <= 0 ) {
				continue;
			}

			$variant_sku = (string) $product->get_sku();
			$parts       = onplay_parse_sku( $variant_sku );
			$set_info    = onplay_resolve_set_for_product( (int) $product->get_id() );

			$variants[] = array(
				'id'         => (int) $product->get_id(),
				'sku'        => $variant_sku,
				'title'      => (string) $product->get_name(),
				'permalink'  => (string) get_permalink( $product->get_id() ),
				'price'      => (float) $product->get_price(),
				'stock'      => $stock,
				'is_foil'    => onplay_is_foil( $product->get_id() ),
				'condition'  => $parts['condition'],
				'language'   => $parts['language'],
				'set_code'   => '' !== $parts['set_code'] ? $parts['set_code'] : $set_info['code'],
				'set_name'   => $set_info['name'],
				'print_key'  => onplay_print_key_from_sku( $variant_sku ),
				'is_current' => false,
			);
		}
	}

	set_transient( $cache_key, $variants, ONPLAY_VARIANTS_TTL );

	return onplay_sort_and_mark_variants( $variants, $current_sku, $current_print_key );
}

/**
 * Resuelve {code, name} del set para un producto leyendo su jerarquía
 * de categorías product_cat: "Magic: The Gathering > {Set Name}".
 *
 * @param int $product_id
 * @return array{code:string, name:string}
 */
function onplay_resolve_set_for_product( $product_id ) {
	$out   = array(
		'code' => '',
		'name' => '',
	);
	$terms = wp_get_object_terms( (int) $product_id, 'product_cat' );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return $out;
	}
	// Preferimos un término con parent != 0 (set), no la raíz "Magic: The Gathering".
	foreach ( $terms as $term ) {
		if ( (int) $term->parent > 0 ) {
			$out['name'] = (string) $term->name;
			break;
		}
	}
	if ( '' === $out['name'] && isset( $terms[0] ) ) {
		$out['name'] = (string) $terms[0]->name;
	}
	return $out;
}

/**
 * Aplica orden (print_key actual primero, luego precio asc) y marca current.
 * No se cachea — depende del producto activo del request.
 *
 * @param array  $variants
 * @param string $current_sku
 * @param string $current_print_key
 * @return array
 */
function onplay_sort_and_mark_variants( $variants, $current_sku, $current_print_key ) {
	$current_sku       = (string) $current_sku;
	$current_print_key = (string) $current_print_key;

	foreach ( $variants as &$v ) {
		$v['is_current'] = ( strcasecmp( $v['sku'], $current_sku ) === 0 );
	}
	unset( $v );

	usort(
		$variants,
		function ( $a, $b ) use ( $current_print_key ) {
			$a_same = ( '' !== $current_print_key && $a['print_key'] === $current_print_key ) ? 0 : 1;
			$b_same = ( '' !== $current_print_key && $b['print_key'] === $current_print_key ) ? 0 : 1;
			if ( $a_same !== $b_same ) {
				return $a_same - $b_same;
			}
			if ( $a['price'] === $b['price'] ) {
				return strcmp( $a['sku'], $b['sku'] );
			}
			return ( $a['price'] < $b['price'] ) ? -1 : 1;
		}
	);

	return $variants;
}

/**
 * Invalida el transient cuando se guarda un producto. La clave de cache
 * es el nombre normalizado, así que cubrir todos los foil/non-foil del
 * mismo nombre se hace borrando un solo transient.
 *
 * @param int $post_id
 */
function onplay_invalidate_variants_cache_on_save( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	if ( get_post_type( $post_id ) !== 'product' ) {
		return;
	}
	$title = (string) get_the_title( (int) $post_id );
	$name  = onplay_normalize_card_name( $title );
	if ( '' === $name ) {
		return;
	}
	delete_transient( ONPLAY_VARIANTS_TRANSIENT_PREFIX . md5( $name ) );
}
add_action( 'save_post_product', 'onplay_invalidate_variants_cache_on_save', 20 );

/**
 * También invalida cuando WC reduce stock por compra (no siempre dispara save_post_product).
 *
 * @param WC_Product $product
 */
function onplay_invalidate_variants_cache_on_stock_change( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return;
	}
	$title = (string) $product->get_name();
	$name  = onplay_normalize_card_name( $title );
	if ( '' === $name ) {
		return;
	}
	delete_transient( ONPLAY_VARIANTS_TRANSIENT_PREFIX . md5( $name ) );
}
add_action( 'woocommerce_product_set_stock', 'onplay_invalidate_variants_cache_on_stock_change', 20 );
add_action( 'woocommerce_variation_set_stock', 'onplay_invalidate_variants_cache_on_stock_change', 20 );
