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
 * ¿Estamos en contexto Magic explícito?
 *
 * Complementa onplay_op_in_op_context() con la rama "todo lo que NO es OP
 * dentro del universo Magic" — archive Magic (incluye shop sin set= o con
 * set= que NO desciende de OP) y single Magic. Home queda fuera (default
 * global) por consistencia con spec §1: "en home el buscador busca en
 * todos los TCGs".
 *
 * Útil para que el header serialice `tcg=mtg` y el endpoint de búsqueda
 * excluya simétricamente productos OP en contexto Magic.
 *
 * @return bool
 */
function onplay_op_is_magic_context() {
	if ( is_admin() ) {
		return false;
	}
	// OP gana — si estamos en OP, no estamos en Magic.
	if ( function_exists( 'onplay_op_in_op_context' ) && onplay_op_in_op_context() ) {
		return false;
	}

	// Shop con set= → si llegamos hasta acá, no es OP, así que es Magic.
	// Shop sin set= también es Magic (la convención del MVP es que el shop
	// "sin contexto" muestra Magic primario).
	if ( function_exists( 'is_shop' ) && is_shop() ) {
		return true;
	}

	// Archive nativo de product_cat — si no es OP (chequeado arriba), es Magic.
	if ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() ) {
		return true;
	}

	// Single product que no es OP → Magic (binario en MVP).
	if ( is_singular( 'product' ) ) {
		return true;
	}

	return false;
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
