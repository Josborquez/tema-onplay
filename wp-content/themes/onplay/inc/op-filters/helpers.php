<?php
/**
 * M-OP-buscador — Helpers de detección de contexto.
 *
 * Complemento de `query.php` (M-OP-filtros): aquí van detectores que el
 * buscador del header consume tanto en SSR (header.php) como en JS
 * (data-tcg lo refleja).
 *
 * @package Onplay
 */

defined( 'ABSPATH' ) || exit;

/**
 * ¿La request actual es la ficha de un producto descendiente de One Piece TCG?
 *
 * Usa `is_singular('product')` y luego inspecciona `product_cat` del producto.
 * Re-utiliza `onplay_op_term_descends_from()` de query.php.
 *
 * @return bool
 */
function onplay_op_is_single() {
	if ( is_admin() || ! function_exists( 'is_singular' ) ) {
		return false;
	}
	if ( ! is_singular( 'product' ) ) {
		return false;
	}
	$post_id = get_queried_object_id();
	if ( ! $post_id ) {
		return false;
	}
	$cats = get_the_terms( $post_id, 'product_cat' );
	if ( empty( $cats ) || is_wp_error( $cats ) ) {
		return false;
	}
	foreach ( $cats as $cat ) {
		if ( onplay_op_term_descends_from( $cat, ONPLAY_OP_ROOT_SLUG ) ) {
			return true;
		}
	}
	return false;
}

/**
 * ¿Estamos navegando dentro del universo One Piece TCG?
 *
 * Combina archive (filters) + single (esta función). El buscador usa este
 * resultado para decidir si serializa `tcg=op` o no.
 *
 * NO incluye páginas como `/carrito/`, `/checkout/`, `/mi-cuenta/` aunque el
 * carrito tenga ítems OP — esas son páginas de transacción/cuenta, no de
 * navegación de catálogo. El buscador en esos contextos queda global.
 *
 * @return bool
 */
function onplay_op_in_op_context() {
	return onplay_op_is_archive() || onplay_op_is_single();
}

/**
 * IDs de term_taxonomy de la categoría OP raíz + descendientes.
 *
 * Útil para queries SQL crudas que necesitan el listado completo (ej. el
 * INNER JOIN del endpoint de búsqueda OP). Cacheado por request via static.
 *
 * @return int[]
 */
function onplay_op_get_descendant_tt_ids() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$root = get_term_by( 'slug', ONPLAY_OP_ROOT_SLUG, 'product_cat' );
	if ( ! $root instanceof WP_Term ) {
		$cache = array();
		return $cache;
	}
	$ids = array( (int) $root->term_taxonomy_id );
	$children = get_term_children( $root->term_id, 'product_cat' );
	if ( ! is_wp_error( $children ) ) {
		foreach ( $children as $cid ) {
			$t = get_term( $cid, 'product_cat' );
			if ( $t instanceof WP_Term ) {
				$ids[] = (int) $t->term_taxonomy_id;
			}
		}
	}
	$cache = array_values( array_unique( array_map( 'intval', $ids ) ) );
	return $cache;
}
