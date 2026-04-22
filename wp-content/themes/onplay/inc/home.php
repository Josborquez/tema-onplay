<?php
/**
 * Home helpers (Módulo 9).
 *
 * Dos funciones consultadas desde template-parts/home/*:
 * - `onplay_home_get_featured_sets( $limit )` — sets (product_cat) cuyas cartas
 *   se añadieron más recientemente (MAX post_date por term).
 * - `onplay_home_get_recent_cards( $limit )` — cartas recién ingresadas,
 *   agrupadas por print_key (reusa el pipeline del listado).
 *
 * Ambos resultados se cachean con transient (1h) para que el hit a la home
 * no corra estas queries en cada pageview.
 *
 * @package Onplay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sets destacados por "cartas más recientes".
 *
 * Excluye categorías top-level (parent=0) porque son categorías promocionales
 * y la categoría raíz "Magic: The Gathering" (convención del manager).
 *
 * Por cada set hace una query adicional para obtener un producto representativo
 * (el más reciente del set) y extrae su `set_code` desde el SKU + `thumb` url.
 * El volumen es acotado (6 sets) y el resultado completo se cachea 1h.
 *
 * @param int $limit
 * @return array<int,array{term_id:int,name:string,slug:string,count:int,latest:string,year:string,set_code:string,thumb:string}>
 */
function onplay_home_get_featured_sets( $limit = 6 ) {
	$limit = max( 1, (int) $limit );
	$cache = get_transient( 'onplay_home_featured_sets_v2_' . $limit );
	if ( is_array( $cache ) ) {
		return $cache;
	}

	global $wpdb;
	$sql = $wpdb->prepare(
		"SELECT t.term_id, t.name, t.slug, tt.count, MAX(p.post_date) AS latest
		 FROM {$wpdb->terms} t
		 INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = t.term_id
		 INNER JOIN {$wpdb->term_relationships} tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
		 INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
		 WHERE tt.taxonomy = %s
		   AND tt.parent != 0
		   AND p.post_type = %s
		   AND p.post_status = %s
		 GROUP BY t.term_id
		 ORDER BY latest DESC
		 LIMIT %d",
		'product_cat',
		'product',
		'publish',
		$limit
	);

	$rows = $wpdb->get_results( $sql, ARRAY_A );
	if ( ! is_array( $rows ) ) {
		$rows = array();
	}

	$sets = array();
	foreach ( $rows as $r ) {
		$rep = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT p.ID, pm_sku.meta_value AS sku
				 FROM {$wpdb->posts} p
				 INNER JOIN {$wpdb->term_relationships} tr ON tr.object_id = p.ID
				 INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
				 LEFT JOIN {$wpdb->postmeta} pm_sku ON pm_sku.post_id = p.ID AND pm_sku.meta_key = '_sku'
				 WHERE tt.term_id = %d
				   AND tt.taxonomy = 'product_cat'
				   AND p.post_type = 'product'
				   AND p.post_status = 'publish'
				 ORDER BY p.post_date DESC
				 LIMIT 1",
				(int) $r['term_id']
			)
		);

		$set_code = '';
		$thumb    = '';
		if ( $rep ) {
			$pid = (int) $rep->ID;
			if ( $pid > 0 ) {
				$img_url = get_the_post_thumbnail_url( $pid, 'medium' );
				if ( is_string( $img_url ) ) {
					$thumb = $img_url;
				}
			}
			if ( ! empty( $rep->sku ) && function_exists( 'onplay_parse_sku' ) ) {
				$parts    = onplay_parse_sku( (string) $rep->sku );
				$set_code = isset( $parts['set_code'] ) ? (string) $parts['set_code'] : '';
			}
		}

		$latest = (string) $r['latest'];
		$year   = '' !== $latest ? substr( $latest, 0, 4 ) : '';

		$sets[] = array(
			'term_id'  => (int) $r['term_id'],
			'name'     => (string) $r['name'],
			'slug'     => (string) $r['slug'],
			'count'    => (int) $r['count'],
			'latest'   => $latest,
			'year'     => $year,
			'set_code' => $set_code,
			'thumb'    => $thumb,
		);
	}

	set_transient( 'onplay_home_featured_sets_v2_' . $limit, $sets, HOUR_IN_SECONDS );
	return $sets;
}

/**
 * Cartas recién ingresadas, ordenadas por precio DESC (mayor valor primero).
 *
 * Pool: 150 SKUs más recientes en stock; colapsados por print_key; ordenados por
 * `min_price` desc para que el home destaque las cartas de mayor valor dentro de
 * las recién ingresadas (criterio del dueño).
 *
 * @param int $limit Grupos (impresiones únicas) a devolver.
 * @return array Lista de grupos compatibles con el render del archive.
 */
function onplay_home_get_recent_cards( $limit = 8 ) {
	$limit = max( 1, (int) $limit );
	$cache = get_transient( 'onplay_home_recent_cards_' . $limit );
	if ( is_array( $cache ) ) {
		return $cache;
	}

	// Buffer generoso: 8 grupos distintos pueden requerir bastantes más SKUs
	// si varias comparten print_key. 150 cubre con holgura el catálogo actual.
	$args  = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 150,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => array(
			array(
				'key'   => '_stock_status',
				'value' => 'instock',
			),
		),
	);
	$query = new WP_Query( $args );
	$ids   = array_map( 'intval', $query->posts );

	$groups = function_exists( 'onplay_collapse_products_by_print_key' )
		? onplay_collapse_products_by_print_key( $ids )
		: array();

	if ( function_exists( 'onplay_sort_groups' ) ) {
		$groups = onplay_sort_groups( $groups, 'price-desc' );
	}

	$groups = array_slice( $groups, 0, $limit );

	set_transient( 'onplay_home_recent_cards_' . $limit, $groups, HOUR_IN_SECONDS );
	return $groups;
}

/**
 * Cartas destacadas para el hero (stack flotante + preview "populares ahora").
 *
 * Reusa el pool de recent cards (ya cacheado) y devuelve los primeros `$limit`
 * grupos — que ya vienen ordenados por valor desc. El hero los usa para:
 *   - Floating card stack (5 cartas grandes rotadas).
 *   - Preview "populares ahora" (4 filas compactas bajo el input de búsqueda).
 *
 * @param int $limit
 * @return array
 */
function onplay_home_get_hero_cards( $limit = 9 ) {
	$pool = onplay_home_get_recent_cards( max( 9, (int) $limit ) );
	return array_slice( $pool, 0, (int) $limit );
}

/**
 * Stats numéricos mostrados en el hero (singles en stock, sets, despacho).
 *
 * @return array{singles:int,sets:int,despacho:string}
 */
function onplay_home_get_stats() {
	$cache = get_transient( 'onplay_home_stats_v1' );
	if ( is_array( $cache ) ) {
		return $cache;
	}

	global $wpdb;

	$singles = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = %s
			 WHERE p.post_type = %s AND p.post_status = %s AND m.meta_value = %s",
			'_stock_status',
			'product',
			'publish',
			'instock'
		)
	);

	$sets = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(DISTINCT tt.term_id) FROM {$wpdb->term_taxonomy} tt
			 WHERE tt.taxonomy = %s AND tt.parent != 0 AND tt.count > 0",
			'product_cat'
		)
	);

	$stats = array(
		'singles'  => $singles,
		'sets'     => $sets,
		'despacho' => '24-48h',
	);

	set_transient( 'onplay_home_stats_v1', $stats, HOUR_IN_SECONDS );
	return $stats;
}

/**
 * Invalidar los transients al publicar/editar un producto.
 * El volumen de home es alto y los datos cambian al sincronizar inventario,
 * así que purgamos ambos caches en cualquier save_post_product.
 */
add_action(
	'save_post_product',
	function () {
		for ( $i = 1; $i <= 12; $i++ ) {
			delete_transient( 'onplay_home_featured_sets_v2_' . $i );
			delete_transient( 'onplay_home_recent_cards_' . $i );
		}
		delete_transient( 'onplay_home_stats_v1' );
	}
);
