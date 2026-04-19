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
 * @param int $limit
 * @return array<int,array{term_id:int,name:string,slug:string,count:int,latest:string}>
 */
function onplay_home_get_featured_sets( $limit = 6 ) {
	$limit = max( 1, (int) $limit );
	$cache = get_transient( 'onplay_home_featured_sets_' . $limit );
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
		$sets[] = array(
			'term_id' => (int) $r['term_id'],
			'name'    => (string) $r['name'],
			'slug'    => (string) $r['slug'],
			'count'   => (int) $r['count'],
			'latest'  => (string) $r['latest'],
		);
	}

	set_transient( 'onplay_home_featured_sets_' . $limit, $sets, HOUR_IN_SECONDS );
	return $sets;
}

/**
 * Cartas recién ingresadas, colapsadas por print_key.
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
		$groups = onplay_sort_groups( $groups, 'new' );
	}

	$groups = array_slice( $groups, 0, $limit );

	set_transient( 'onplay_home_recent_cards_' . $limit, $groups, HOUR_IN_SECONDS );
	return $groups;
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
			delete_transient( 'onplay_home_featured_sets_' . $i );
			delete_transient( 'onplay_home_recent_cards_' . $i );
		}
	}
);
