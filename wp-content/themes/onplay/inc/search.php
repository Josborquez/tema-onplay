<?php
/**
 * Endpoint AJAX de autocomplete para el buscador del header.
 *
 * Busca por post_title OR _sku (dos LIKE unificados) y agrupa por
 * nombre normalizado + print_key. Devuelve hasta 8 resultados con
 * representante = SKU más barato in-stock del print_key.
 *
 * @package Onplay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const ONPLAY_SEARCH_MAX_RESULTS = 8;
const ONPLAY_SEARCH_MIN_CHARS   = 2;

/**
 * Handler del endpoint wp_ajax(_nopriv)_onplay_search.
 */
function onplay_ajax_search_handler() {
	check_ajax_referer( 'onplay_search', 'nonce' );

	$q = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['q'] ) ) : '';
	$q = trim( $q );

	if ( strlen( $q ) < ONPLAY_SEARCH_MIN_CHARS ) {
		wp_send_json_success( array( 'results' => array() ) );
	}

	$results = onplay_search_products( $q, ONPLAY_SEARCH_MAX_RESULTS );

	wp_send_json_success( array( 'results' => $results ) );
}
add_action( 'wp_ajax_onplay_search', 'onplay_ajax_search_handler' );
add_action( 'wp_ajax_nopriv_onplay_search', 'onplay_ajax_search_handler' );

/**
 * Busca productos por título o SKU, agrupa por (nombre normalizado + print_key)
 * y devuelve hasta $limit representantes ordenados por precio asc del representante.
 *
 * @param string $q
 * @param int    $limit
 * @return array<int, array{name:string,set_name:string,set_code:string,print_key:string,min_price:float,min_price_formatted:string,thumb:string,permalink:string}>
 */
function onplay_search_products( $q, $limit ) {
	global $wpdb;

	$like  = '%' . $wpdb->esc_like( $q ) . '%';
	// Traemos un pool amplio (limit * 6) para que la agrupación por print_key
	// no deje al usuario con 1 resultado cuando hay 40 SKUs del mismo título.
	$pool  = max( (int) $limit * 6, 48 );
	$rows  = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT DISTINCT p.ID
			 FROM {$wpdb->posts} p
			 LEFT JOIN {$wpdb->postmeta} pm_sku ON pm_sku.post_id = p.ID AND pm_sku.meta_key = '_sku'
			 LEFT JOIN {$wpdb->postmeta} pm_stock ON pm_stock.post_id = p.ID AND pm_stock.meta_key = '_stock_status'
			 WHERE p.post_type = 'product'
			   AND p.post_status = 'publish'
			   AND ( p.post_title LIKE %s OR pm_sku.meta_value LIKE %s )
			   AND pm_stock.meta_value = 'instock'
			 ORDER BY p.post_title ASC
			 LIMIT %d",
			$like,
			$like,
			$pool
		)
	);

	if ( empty( $rows ) ) {
		return array();
	}

	$grouped = array();

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

		$sku       = (string) $product->get_sku();
		$print_key = onplay_print_key_from_sku( $sku );
		if ( '' === $print_key ) {
			continue;
		}
		$name = onplay_normalize_card_name( (string) $product->get_name() );
		$key  = $name . '|' . $print_key;
		$price = (float) $product->get_price();

		if ( ! isset( $grouped[ $key ] ) || $price < $grouped[ $key ]['min_price'] ) {
			$set_info   = onplay_resolve_set_for_product( (int) $product->get_id() );
			$thumb_id   = (int) get_post_thumbnail_id( $product->get_id() );
			$thumb_src  = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'thumbnail' ) : '';
			if ( ! $thumb_src && function_exists( 'wc_placeholder_img_src' ) ) {
				$thumb_src = wc_placeholder_img_src( 'thumbnail' );
			}

			$grouped[ $key ] = array(
				'name'                => $name,
				'set_name'            => $set_info['name'],
				'set_code'            => '' !== onplay_parse_sku( $sku )['set_code'] ? onplay_parse_sku( $sku )['set_code'] : $set_info['code'],
				'print_key'           => $print_key,
				'min_price'           => $price,
				'min_price_formatted' => onplay_format_clp( $price ),
				'thumb'               => (string) $thumb_src,
				'permalink'           => (string) get_permalink( $product->get_id() ),
			);
		}
	}

	if ( empty( $grouped ) ) {
		return array();
	}

	usort(
		$grouped,
		function ( $a, $b ) {
			if ( $a['min_price'] === $b['min_price'] ) {
				return strcasecmp( $a['name'], $b['name'] );
			}
			return ( $a['min_price'] < $b['min_price'] ) ? -1 : 1;
		}
	);

	return array_slice( array_values( $grouped ), 0, (int) $limit );
}
