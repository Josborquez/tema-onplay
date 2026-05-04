<?php
/**
 * Endpoint AJAX de autocomplete para el buscador del header.
 *
 * Comportamiento por defecto (Magic, M5): busca por post_title OR _sku
 * (dos LIKE unificados) y agrupa por nombre normalizado + print_key.
 * Devuelve hasta 8 resultados con representante = SKU más barato in-stock
 * del print_key.
 *
 * Comportamiento con `tcg=op` (M-OP-buscador): restringe el universo a
 * productos descendientes de `one-piece-tcg`, busca por title OR _sku OR
 * _card_number (este último con LIKE para que `EB04-` matchee la familia)
 * y agrupa por `_card_number`. Devuelve la misma estructura JSON con
 * `print_key` reusado como id-de-grupo (== card_number) para que el JS
 * exitente del autocomplete no requiera cambios destructivos.
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

	$tcg = isset( $_GET['tcg'] ) ? sanitize_key( wp_unslash( (string) $_GET['tcg'] ) ) : '';
	if ( ! in_array( $tcg, array( 'op', 'mtg' ), true ) ) {
		$tcg = '';
	}

	if ( strlen( $q ) < ONPLAY_SEARCH_MIN_CHARS ) {
		wp_send_json_success(
			array(
				'results' => array(),
				'context' => $tcg ? $tcg : 'global',
			)
		);
	}

	if ( 'op' === $tcg ) {
		$results = onplay_search_products_op( $q, ONPLAY_SEARCH_MAX_RESULTS );
		$ctx     = 'op';
	} elseif ( 'mtg' === $tcg ) {
		// Rama simétrica a OP: excluye productos descendientes de one-piece-tcg.
		$results = onplay_search_products( $q, ONPLAY_SEARCH_MAX_RESULTS, array( 'exclude_op' => true ) );
		$ctx     = 'mtg';
	} else {
		$results = onplay_search_products( $q, ONPLAY_SEARCH_MAX_RESULTS );
		$ctx     = 'global';
	}

	wp_send_json_success(
		array(
			'results' => $results,
			'context' => $ctx,
		)
	);
}
add_action( 'wp_ajax_onplay_search', 'onplay_ajax_search_handler' );
add_action( 'wp_ajax_nopriv_onplay_search', 'onplay_ajax_search_handler' );

/**
 * Busca productos por título o SKU, agrupa por (nombre normalizado + print_key)
 * y devuelve hasta $limit representantes ordenados por precio asc del representante.
 *
 * Comportamiento Magic / catálogo completo (M5).
 *
 * @param string $q
 * @param int    $limit
 * @param array  $opts  ['exclude_op' => bool]  Si true, filtra fuera productos
 *                      descendientes de one-piece-tcg (rama Magic-strict).
 * @return array<int, array{name:string,set_name:string,set_code:string,print_key:string,min_price:float,min_price_formatted:string,thumb:string,permalink:string,context:string}>
 */
function onplay_search_products( $q, $limit, $opts = array() ) {
	global $wpdb;

	$like  = '%' . $wpdb->esc_like( $q ) . '%';
	// Traemos un pool amplio (limit * 6) para que la agrupación por print_key
	// no deje al usuario con 1 resultado cuando hay 40 SKUs del mismo título.
	$pool  = max( (int) $limit * 6, 48 );

	// Magic-strict: excluir productos cuyo product_cat sea OP raíz o descendiente.
	$exclude_op = ! empty( $opts['exclude_op'] ) && function_exists( 'onplay_op_get_descendant_tt_ids' );
	$op_tt_ids  = $exclude_op ? onplay_op_get_descendant_tt_ids() : array();
	if ( $exclude_op && ! empty( $op_tt_ids ) ) {
		$op_in = implode( ',', array_map( 'intval', $op_tt_ids ) );
		// NOT EXISTS subquery — más eficiente que LEFT JOIN + WHERE NULL para
		// exclusión cuando hay muchos terms.
		$sql = $wpdb->prepare(
			"SELECT DISTINCT p.ID
			 FROM {$wpdb->posts} p
			 LEFT JOIN {$wpdb->postmeta} pm_sku ON pm_sku.post_id = p.ID AND pm_sku.meta_key = '_sku'
			 LEFT JOIN {$wpdb->postmeta} pm_stock ON pm_stock.post_id = p.ID AND pm_stock.meta_key = '_stock_status'
			 WHERE p.post_type = 'product'
			   AND p.post_status = 'publish'
			   AND ( p.post_title LIKE %s OR pm_sku.meta_value LIKE %s )
			   AND pm_stock.meta_value = 'instock'
			   AND NOT EXISTS (
			     SELECT 1 FROM {$wpdb->term_relationships} tr_op
			     WHERE tr_op.object_id = p.ID
			       AND tr_op.term_taxonomy_id IN ($op_in)
			   )
			 ORDER BY p.post_title ASC
			 LIMIT %d",
			$like,
			$like,
			$pool
		); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_col( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
	} else {
		$rows = $wpdb->get_col(
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
	}

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
				'context'             => 'global',
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

/**
 * Variante One Piece TCG del autocomplete.
 *
 * Restringe el universo a productos descendientes de `one-piece-tcg` mediante
 * INNER JOIN con `wp_term_relationships`. Busca en title, _sku, _card_number
 * — el último con LIKE para soportar prefijos de set (ej. "EB04-" matchea
 * todas las cartas del set EB04).
 *
 * Agrupa por `_card_number` (el "card number" es la identidad lógica de la
 * carta en OP — distintos products con el mismo number son alt arts/foils).
 * Si un grupo tiene N productos donde alguno NO es alt art, ese gana como
 * representante; si todos son alt art, marca el grupo `is_alt_art=true`.
 *
 * Reusa el campo `print_key` del payload para mantener la forma con la
 * versión Magic (el JS no necesita branches destructivos), aunque su
 * contenido sea el card_number en vez del SKU truncado.
 *
 * @param string $q
 * @param int    $limit
 * @return array<int, array{name:string,set_name:string,set_code:string,print_key:string,card_number:string,min_price:float,min_price_formatted:string,thumb:string,permalink:string,is_alt_art:bool,context:string}>
 */
function onplay_search_products_op( $q, $limit ) {
	global $wpdb;

	$tt_ids = function_exists( 'onplay_op_get_descendant_tt_ids' ) ? onplay_op_get_descendant_tt_ids() : array();
	if ( empty( $tt_ids ) ) {
		return array();
	}
	$tt_in = implode( ',', array_map( 'intval', $tt_ids ) );

	$like = '%' . $wpdb->esc_like( $q ) . '%';
	$pool = max( (int) $limit * 6, 48 );

	// SQL crudo: title OR _sku OR _card_number, JOIN con term_relationships
	// limitando a tt_ids OP. DISTINCT por p.ID para evitar duplicados cuando
	// un producto está en más de un term descendiente.
	$sql = $wpdb->prepare(
		"SELECT DISTINCT p.ID
		 FROM {$wpdb->posts} p
		 INNER JOIN {$wpdb->term_relationships} tr ON tr.object_id = p.ID AND tr.term_taxonomy_id IN ($tt_in)
		 LEFT JOIN {$wpdb->postmeta} pm_sku ON pm_sku.post_id = p.ID AND pm_sku.meta_key = '_sku'
		 LEFT JOIN {$wpdb->postmeta} pm_card ON pm_card.post_id = p.ID AND pm_card.meta_key = '_card_number'
		 LEFT JOIN {$wpdb->postmeta} pm_stock ON pm_stock.post_id = p.ID AND pm_stock.meta_key = '_stock_status'
		 WHERE p.post_type = 'product'
		   AND p.post_status = 'publish'
		   AND ( p.post_title LIKE %s OR pm_sku.meta_value LIKE %s OR pm_card.meta_value LIKE %s )
		   AND pm_stock.meta_value = 'instock'
		 ORDER BY p.post_title ASC
		 LIMIT %d",
		$like,
		$like,
		$like,
		$pool
	); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	$rows = $wpdb->get_col( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared

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

		$card_number = (string) get_post_meta( $product->get_id(), '_card_number', true );
		if ( '' === $card_number ) {
			// Producto OP sin card_number — degenerado, lo saltamos para no
			// confundir la agrupación.
			continue;
		}

		$set_full_code = (string) get_post_meta( $product->get_id(), '_set_full_code', true );
		$is_alt_art    = 'yes' === (string) get_post_meta( $product->get_id(), '_is_alt_art', true );
		$price         = (float) $product->get_price();
		$name          = onplay_normalize_card_name( (string) $product->get_name() );

		$key = $card_number;

		if ( ! isset( $grouped[ $key ] ) ) {
			// Primera vez que vemos este card_number — se vuelve representante.
			$thumb_id  = (int) get_post_thumbnail_id( $product->get_id() );
			$thumb_src = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'thumbnail' ) : '';
			if ( ! $thumb_src && function_exists( 'wc_placeholder_img_src' ) ) {
				$thumb_src = wc_placeholder_img_src( 'thumbnail' );
			}
			$set_info = onplay_resolve_set_for_product( (int) $product->get_id() );

			$grouped[ $key ] = array(
				'name'                => $name,
				'set_name'            => $set_info['name'],
				'set_code'            => '' !== $set_full_code ? $set_full_code : $set_info['code'],
				'print_key'           => $card_number,
				'card_number'         => $card_number,
				'min_price'           => $price,
				'min_price_formatted' => onplay_format_clp( $price ),
				'thumb'               => (string) $thumb_src,
				'permalink'           => (string) get_permalink( $product->get_id() ),
				'is_alt_art'          => $is_alt_art,
				'context'             => 'op',
			);
		} else {
			// Mismo card_number ya visto — actualizar si este producto es más
			// barato o si es la versión "regular" cuando el actual es alt art.
			$current = $grouped[ $key ];
			$replace = false;
			if ( $current['is_alt_art'] && ! $is_alt_art ) {
				// Preferir la versión regular como representante.
				$replace = true;
			} elseif ( $current['is_alt_art'] === $is_alt_art && $price < $current['min_price'] ) {
				$replace = true;
			}

			if ( $replace ) {
				$thumb_id  = (int) get_post_thumbnail_id( $product->get_id() );
				$thumb_src = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'thumbnail' ) : '';
				if ( ! $thumb_src && function_exists( 'wc_placeholder_img_src' ) ) {
					$thumb_src = wc_placeholder_img_src( 'thumbnail' );
				}
				$grouped[ $key ]['name']                = $name;
				$grouped[ $key ]['set_code']            = '' !== $set_full_code ? $set_full_code : $grouped[ $key ]['set_code'];
				$grouped[ $key ]['min_price']           = $price;
				$grouped[ $key ]['min_price_formatted'] = onplay_format_clp( $price );
				$grouped[ $key ]['thumb']               = (string) $thumb_src;
				$grouped[ $key ]['permalink']           = (string) get_permalink( $product->get_id() );
				$grouped[ $key ]['is_alt_art']          = $is_alt_art;
			} elseif ( $price < $current['min_price'] ) {
				// El alt-art es más barato pero mantenemos representante regular,
				// solo bajamos el min_price para reflejar precio "desde".
				$grouped[ $key ]['min_price']           = $price;
				$grouped[ $key ]['min_price_formatted'] = onplay_format_clp( $price );
			}
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
