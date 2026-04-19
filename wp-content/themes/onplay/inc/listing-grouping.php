<?php
/**
 * Listing grouping — colapsa productos por print_key para el archive.
 *
 * Estrategia: cargamos los IDs que matchean los filtros (WP_Query), traemos
 * SKU + price + stock_status + post_date en una sola consulta SQL, y agrupamos
 * en PHP quedándonos con el representante = SKU más barato in-stock por
 * print_key.
 *
 * Decisión registrada en CLAUDE.md sec 11 (2026-04-19): el colapso se hace en
 * PHP. Apto para catálogo ≤ ~1000 productos. Si crece o se vuelve lento, mover
 * a SQL con GROUP BY (rollback documentado en sec 11).
 *
 * @package Onplay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Colapsa una lista de IDs de productos por print_key.
 *
 * Devuelve un array de "grupos", donde cada grupo es:
 *   - representative_id: ID del producto representante (SKU más barato in-stock).
 *   - print_key:         set_code-collector_number.
 *   - min_price:         precio mínimo (float, CLP).
 *   - variant_count:     número de variantes en stock del print_key.
 *   - ids:               array de todos los IDs del grupo.
 *   - latest_added:      timestamp del más reciente (para sort=new).
 *
 * @param int[] $post_ids
 * @return array<int, array{
 *     representative_id:int, print_key:string, min_price:float,
 *     variant_count:int, ids:int[], latest_added:int
 * }>
 */
function onplay_collapse_products_by_print_key( $post_ids ) {
	if ( empty( $post_ids ) ) {
		return array();
	}

	global $wpdb;

	$post_ids = array_map( 'intval', $post_ids );
	$post_ids = array_values( array_filter( $post_ids, function ( $v ) { return $v > 0; } ) );
	if ( empty( $post_ids ) ) {
		return array();
	}

	$placeholders = implode( ',', array_fill( 0, count( $post_ids ), '%d' ) );

	// Una sola query que trae ID + SKU + price + stock_status + post_date.
	// LEFT JOIN porque productos sin _price o _stock_status no deben hacer drop del row.
	$sql = $wpdb->prepare(
		"SELECT p.ID, p.post_date,
		        sku.meta_value   AS sku,
		        price.meta_value AS price,
		        stock.meta_value AS stock_status
		 FROM {$wpdb->posts} p
		 LEFT JOIN {$wpdb->postmeta} sku   ON sku.post_id   = p.ID AND sku.meta_key   = '_sku'
		 LEFT JOIN {$wpdb->postmeta} price ON price.post_id = p.ID AND price.meta_key = '_price'
		 LEFT JOIN {$wpdb->postmeta} stock ON stock.post_id = p.ID AND stock.meta_key = '_stock_status'
		 WHERE p.ID IN ($placeholders)
		   AND p.post_status = 'publish'",
		$post_ids
	);

	$rows = $wpdb->get_results( $sql );
	if ( empty( $rows ) ) {
		return array();
	}

	$groups = array();

	foreach ( $rows as $row ) {
		$pid       = (int) $row->ID;
		$sku       = (string) $row->sku;
		$price     = (float) $row->price;
		$stock_ok  = ( 'instock' === $row->stock_status );
		$post_ts   = strtotime( (string) $row->post_date );
		$print_key = onplay_print_key_from_sku( $sku );

		if ( '' === $print_key ) {
			continue;
		}

		if ( ! isset( $groups[ $print_key ] ) ) {
			$groups[ $print_key ] = array(
				'representative_id' => 0,
				'print_key'         => $print_key,
				'min_price'         => 0.0,
				'variant_count'     => 0,
				'ids'               => array(),
				'latest_added'      => 0,
				'_has_instock'      => false,
			);
		}
		$g =& $groups[ $print_key ];

		$g['ids'][]       = $pid;
		$g['latest_added'] = max( $g['latest_added'], (int) $post_ts );

		if ( $stock_ok ) {
			$g['variant_count']++;
			if ( ! $g['_has_instock'] || $price < $g['min_price'] ) {
				$g['_has_instock']      = true;
				$g['representative_id'] = $pid;
				$g['min_price']         = $price;
			}
		}
		unset( $g );
	}

	// Si un grupo no tiene ningún SKU in-stock, su representante queda en el más
	// barato general (fallback por si in_stock=false está activo en filtros).
	foreach ( $groups as $print_key => &$g ) {
		if ( $g['representative_id'] === 0 ) {
			$cheapest_id    = $g['ids'][0];
			$cheapest_price = PHP_FLOAT_MAX;
			foreach ( $rows as $row ) {
				$pid = (int) $row->ID;
				if ( ! in_array( $pid, $g['ids'], true ) ) {
					continue;
				}
				$pp = (float) $row->price;
				if ( $pp < $cheapest_price ) {
					$cheapest_price = $pp;
					$cheapest_id    = $pid;
				}
			}
			$g['representative_id'] = $cheapest_id;
			$g['min_price']         = $cheapest_price === PHP_FLOAT_MAX ? 0.0 : $cheapest_price;
		}
		unset( $g['_has_instock'] );
	}
	unset( $g );

	return array_values( $groups );
}

/**
 * Ordena un array de grupos colapsados según el criterio elegido.
 *
 * @param array  $groups
 * @param string $sort price-desc|price-asc|name|new
 * @return array
 */
function onplay_sort_groups( $groups, $sort = 'price-desc' ) {
	if ( empty( $groups ) ) {
		return $groups;
	}

	switch ( $sort ) {
		case 'price-asc':
			usort(
				$groups,
				function ( $a, $b ) {
					return $a['min_price'] <=> $b['min_price'];
				}
			);
			break;

		case 'name':
			usort(
				$groups,
				function ( $a, $b ) {
					$na = onplay_normalize_card_name( (string) get_the_title( $a['representative_id'] ) );
					$nb = onplay_normalize_card_name( (string) get_the_title( $b['representative_id'] ) );
					return strcasecmp( $na, $nb );
				}
			);
			break;

		case 'new':
			usort(
				$groups,
				function ( $a, $b ) {
					return $b['latest_added'] <=> $a['latest_added'];
				}
			);
			break;

		case 'price-desc':
		default:
			usort(
				$groups,
				function ( $a, $b ) {
					return $b['min_price'] <=> $a['min_price'];
				}
			);
			break;
	}

	return $groups;
}

/**
 * Counts del catálogo total in-stock por slug de set (product_cat con parent>0).
 * Cacheado por 1 hora; invalidado en save_post_product.
 *
 * Decisión sec 11: counts solo en filtro de Set, calculados sobre el catálogo
 * total (no facetados Amazon-style).
 *
 * @return array<string, array{slug:string, name:string, count:int}>
 */
function onplay_get_set_facet_counts() {
	$cache_key = 'onplay_set_facet_counts';
	$cached    = get_transient( $cache_key );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'parent'     => 0,
		)
	);
	$root_id = 0;
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $t ) {
			if ( 'magic-the-gathering' === $t->slug || stripos( $t->name, 'magic' ) !== false ) {
				$root_id = (int) $t->term_id;
				break;
			}
		}
	}

	$args = array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
	);
	if ( $root_id > 0 ) {
		$args['parent'] = $root_id;
	}

	$set_terms = get_terms( $args );
	$out       = array();
	if ( is_wp_error( $set_terms ) || empty( $set_terms ) ) {
		set_transient( $cache_key, $out, HOUR_IN_SECONDS );
		return $out;
	}

	global $wpdb;

	foreach ( $set_terms as $term ) {
		$count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT p.ID)
				 FROM {$wpdb->posts} p
				 INNER JOIN {$wpdb->term_relationships} tr ON tr.object_id = p.ID
				 INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
				 INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = '_stock_status'
				 WHERE p.post_type = 'product'
				   AND p.post_status = 'publish'
				   AND tt.taxonomy = 'product_cat'
				   AND tt.term_id = %d
				   AND pm.meta_value = 'instock'",
				(int) $term->term_id
			)
		);

		$out[ $term->slug ] = array(
			'slug'    => (string) $term->slug,
			'name'    => (string) $term->name,
			'count'   => $count,
			'term_id' => (int) $term->term_id,
		);
	}

	uasort(
		$out,
		function ( $a, $b ) {
			return strcasecmp( $a['name'], $b['name'] );
		}
	);

	set_transient( $cache_key, $out, HOUR_IN_SECONDS );
	return $out;
}

/**
 * Invalida el cache de counts cuando un producto cambia.
 *
 * @param int $post_id
 */
function onplay_invalidate_set_counts_on_save( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	if ( get_post_type( $post_id ) !== 'product' ) {
		return;
	}
	delete_transient( 'onplay_set_facet_counts' );
}
add_action( 'save_post_product', 'onplay_invalidate_set_counts_on_save', 30 );
add_action( 'woocommerce_product_set_stock', function ( $product ) {
	delete_transient( 'onplay_set_facet_counts' );
}, 30 );

/**
 * Devuelve los términos de un atributo global (pa_idioma, pa_estado) ordenados
 * por count desc para el sidebar.
 *
 * @param string $taxonomy
 * @return array<string, array{slug:string, name:string, count:int}>
 */
function onplay_get_attribute_terms( $taxonomy ) {
	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
		)
	);
	$out = array();
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return $out;
	}
	foreach ( $terms as $t ) {
		$out[ $t->slug ] = array(
			'slug'  => (string) $t->slug,
			'name'  => (string) $t->name,
			'count' => (int) $t->count,
		);
	}
	return $out;
}
