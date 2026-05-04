<?php
/**
 * Endpoint AJAX para filtros del listado.
 *
 * Contrato (request, GET):
 *   action=onplay_filter
 *   nonce=...
 *   set[]=slug,...        product_cat slugs (ediciones)
 *   color[]=W|U|B|R|G|C
 *   rarity[]=mythic|rare|uncommon|common
 *   condition[]=NM|LP|SP|MP|HP|DMG
 *   foil=all|regular|foil
 *   lang[]=EN|ES|JA|ZH|PT|IT
 *   price_min=0
 *   price_max=500000
 *   in_stock=1|0           (default 1)
 *   sort=price-desc|price-asc|name|new
 *   page=1
 *
 * Contrato (response):
 *   { success, data: { html, pagination_html, chips_html,
 *                      total_groups, total_pages, current_page } }
 *
 * @package Onplay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const ONPLAY_LISTING_PER_PAGE = 24;

/**
 * Sanitiza el request en una estructura tipada.
 *
 * @param array $src $_GET (o $_POST).
 * @return array
 */
function onplay_filters_parse_request( $src ) {
	$as_array = function ( $v ) {
		if ( is_array( $v ) ) {
			return array_values( array_filter( array_map( 'sanitize_text_field', wp_unslash( $v ) ), 'strlen' ) );
		}
		if ( is_string( $v ) && '' !== $v ) {
			return array( sanitize_text_field( wp_unslash( $v ) ) );
		}
		return array();
	};

	$state = array(
		'set'       => isset( $src['set'] ) ? $as_array( $src['set'] ) : array(),
		'color'     => isset( $src['color'] ) ? array_map( 'strtoupper', $as_array( $src['color'] ) ) : array(),
		'rarity'    => isset( $src['rarity'] ) ? array_map( 'strtolower', $as_array( $src['rarity'] ) ) : array(),
		'condition' => isset( $src['condition'] ) ? array_map( 'strtoupper', $as_array( $src['condition'] ) ) : array(),
		'lang'      => isset( $src['lang'] ) ? array_map( 'strtoupper', $as_array( $src['lang'] ) ) : array(),
		'foil'      => isset( $src['foil'] ) ? sanitize_text_field( wp_unslash( $src['foil'] ) ) : 'all',
		'price_min' => isset( $src['price_min'] ) ? max( 0, (int) $src['price_min'] ) : 0,
		'price_max' => isset( $src['price_max'] ) ? max( 0, (int) $src['price_max'] ) : 0,
		'in_stock'  => isset( $src['in_stock'] ) ? ( (int) $src['in_stock'] === 1 ) : true,
		'sort'      => isset( $src['sort'] ) ? sanitize_key( wp_unslash( $src['sort'] ) ) : 'price-desc',
		'page'      => isset( $src['page'] ) ? max( 1, (int) $src['page'] ) : 1,
		'q'         => isset( $src['q'] ) ? sanitize_text_field( wp_unslash( $src['q'] ) ) : '',
	);

	if ( ! in_array( $state['foil'], array( 'all', 'regular', 'foil' ), true ) ) {
		$state['foil'] = 'all';
	}
	if ( ! in_array( $state['sort'], array( 'price-desc', 'price-asc', 'name', 'new' ), true ) ) {
		$state['sort'] = 'price-desc';
	}

	/**
	 * Permite a módulos add-on (M-OP-filtros) inyectar claves de estado
	 * adicionales después del parseo del request. El receptor recibe el state
	 * tipado y la fuente original.
	 */
	$state = apply_filters( 'onplay_filters_state_after_parse', $state, $src );

	return $state;
}

/**
 * Construye la WP_Query a partir del estado de filtros y devuelve los IDs
 * que matchean (sin paginar — la paginación se hace post-colapso).
 *
 * @param array $state
 * @return int[]
 */
function onplay_filters_query_ids( $state ) {
	$args = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);

	$tax_query = array( 'relation' => 'AND' );

	if ( ! empty( $state['set'] ) ) {
		$tax_query[] = array(
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => $state['set'],
			'operator' => 'IN',
		);
	}

	if ( ! empty( $state['color'] ) ) {
		// Si C ("colorless") está en el filtro, lo manejamos al final via post-filter
		// porque el bridge meta→tax mapea Colorless como término "Colorless".
		$color_terms = array();
		$color_map   = array(
			'W' => 'White',
			'U' => 'Blue',
			'B' => 'Black',
			'R' => 'Red',
			'G' => 'Green',
			'C' => 'Colorless',
		);
		foreach ( $state['color'] as $c ) {
			if ( isset( $color_map[ $c ] ) ) {
				$color_terms[] = $color_map[ $c ];
			}
		}
		if ( ! empty( $color_terms ) ) {
			$tax_query[] = array(
				'taxonomy' => 'tcg_color',
				'field'    => 'name',
				'terms'    => $color_terms,
				'operator' => 'IN',
			);
		}
	}

	if ( ! empty( $state['rarity'] ) ) {
		$tax_query[] = array(
			'taxonomy' => 'tcg_rarity',
			'field'    => 'slug',
			'terms'    => $state['rarity'],
			'operator' => 'IN',
		);
	}

	if ( ! empty( $state['condition'] ) ) {
		$tax_query[] = array(
			'taxonomy' => 'pa_estado',
			'field'    => 'name',
			'terms'    => $state['condition'],
			'operator' => 'IN',
		);
	}

	if ( ! empty( $state['lang'] ) ) {
		$tax_query[] = array(
			'taxonomy' => 'pa_idioma',
			'field'    => 'name',
			'terms'    => $state['lang'],
			'operator' => 'IN',
		);
	}

	if ( 'foil' === $state['foil'] ) {
		$tax_query[] = array(
			'taxonomy' => 'tcg_foil',
			'field'    => 'slug',
			'terms'    => array( 'yes' ),
		);
	} elseif ( 'regular' === $state['foil'] ) {
		$tax_query[] = array(
			'taxonomy' => 'tcg_foil',
			'field'    => 'slug',
			'terms'    => array( 'yes' ),
			'operator' => 'NOT IN',
		);
	}

	if ( count( $tax_query ) > 1 ) {
		$args['tax_query'] = $tax_query;
	}

	$meta_query = array( 'relation' => 'AND' );

	if ( $state['in_stock'] ) {
		$meta_query[] = array(
			'key'   => '_stock_status',
			'value' => 'instock',
		);
	}

	if ( $state['price_min'] > 0 || $state['price_max'] > 0 ) {
		$max = $state['price_max'] > 0 ? $state['price_max'] : 999999999;
		$min = $state['price_min'];
		$meta_query[] = array(
			'key'     => '_price',
			'value'   => array( $min, $max ),
			'type'    => 'NUMERIC',
			'compare' => 'BETWEEN',
		);
	}

	/**
	 * Permite a módulos add-on (M-OP-filtros) agregar clausulas al meta_query
	 * antes de la WP_Query. El receptor recibe el meta_query parcial y el state.
	 */
	$meta_query = apply_filters( 'onplay_filters_meta_query', $meta_query, $state );

	if ( count( $meta_query ) > 1 ) {
		$args['meta_query'] = $meta_query;
	}

	if ( '' !== $state['q'] ) {
		$args['s'] = $state['q'];
	}

	$q = new WP_Query( $args );
	return array_map( 'intval', $q->posts );
}

/**
 * Render del grid (HTML) para una página de grupos colapsados.
 *
 * @param array $groups Slice paginado de grupos colapsados.
 * @return string
 */
function onplay_filters_render_grid( $groups ) {
	if ( empty( $groups ) ) {
		return '<div class="shop-empty">' . esc_html__( 'No hay productos que coincidan con tus filtros.', 'onplay' ) . '</div>';
	}

	ob_start();
	echo '<div class="shop-grid">';
	foreach ( $groups as $group ) {
		$pid = (int) $group['representative_id'];
		if ( $pid <= 0 ) {
			continue;
		}
		$GLOBALS['product']               = wc_get_product( $pid );
		$GLOBALS['post']                  = get_post( $pid );
		$GLOBALS['onplay_card_group']     = $group;
		setup_postdata( $GLOBALS['post'] );

		get_template_part( 'template-parts/product-card' );
	}
	echo '</div>';
	wp_reset_postdata();
	unset( $GLOBALS['onplay_card_group'] );
	return ob_get_clean();
}

/**
 * Render de paginación numerada (1 2 3 … N).
 *
 * @param int $current
 * @param int $total
 * @return string
 */
function onplay_filters_render_pagination( $current, $total ) {
	if ( $total <= 1 ) {
		return '';
	}
	$current = max( 1, min( $current, $total ) );

	$window = 2;
	$pages  = array();
	for ( $i = 1; $i <= $total; $i++ ) {
		if ( $i === 1 || $i === $total || abs( $i - $current ) <= $window ) {
			$pages[] = $i;
		}
	}

	ob_start();
	echo '<nav class="shop-pagination" aria-label="' . esc_attr__( 'Paginación', 'onplay' ) . '">';
	if ( $current > 1 ) {
		echo '<button type="button" class="shop-pagination__btn" data-page="' . esc_attr( $current - 1 ) . '" aria-label="' . esc_attr__( 'Anterior', 'onplay' ) . '">&larr;</button>';
	}
	$prev = 0;
	foreach ( $pages as $p ) {
		if ( $prev && $p - $prev > 1 ) {
			echo '<span class="shop-pagination__gap">…</span>';
		}
		$cls = 'shop-pagination__btn' . ( $p === $current ? ' is-current' : '' );
		echo '<button type="button" class="' . esc_attr( $cls ) . '" data-page="' . esc_attr( $p ) . '" aria-current="' . esc_attr( $p === $current ? 'page' : 'false' ) . '">' . esc_html( $p ) . '</button>';
		$prev = $p;
	}
	if ( $current < $total ) {
		echo '<button type="button" class="shop-pagination__btn" data-page="' . esc_attr( $current + 1 ) . '" aria-label="' . esc_attr__( 'Siguiente', 'onplay' ) . '">&rarr;</button>';
	}
	echo '</nav>';
	return ob_get_clean();
}

/**
 * Render de chips de filtros activos (server-side).
 *
 * @param array $state
 * @return string
 */
function onplay_filters_render_chips( $state ) {
	$chips = array();

	$set_facets = onplay_get_set_facet_counts();
	foreach ( $state['set'] as $slug ) {
		$label = isset( $set_facets[ $slug ] ) ? $set_facets[ $slug ]['name'] : $slug;
		$chips[] = array( 'group' => 'set', 'value' => $slug, 'label' => $label );
	}
	$color_map = array( 'W' => 'White', 'U' => 'Blue', 'B' => 'Black', 'R' => 'Red', 'G' => 'Green', 'C' => 'Colorless' );
	foreach ( $state['color'] as $c ) {
		$chips[] = array( 'group' => 'color', 'value' => $c, 'label' => isset( $color_map[ $c ] ) ? $color_map[ $c ] : $c );
	}
	$rarity_map = array( 'mythic' => 'Mythic Rare', 'rare' => 'Rare', 'uncommon' => 'Uncommon', 'common' => 'Common' );
	foreach ( $state['rarity'] as $r ) {
		$chips[] = array( 'group' => 'rarity', 'value' => $r, 'label' => isset( $rarity_map[ $r ] ) ? $rarity_map[ $r ] : ucfirst( $r ) );
	}
	foreach ( $state['condition'] as $c ) {
		$chips[] = array( 'group' => 'condition', 'value' => $c, 'label' => $c );
	}
	foreach ( $state['lang'] as $l ) {
		$chips[] = array( 'group' => 'lang', 'value' => $l, 'label' => $l );
	}
	if ( 'all' !== $state['foil'] ) {
		$chips[] = array( 'group' => 'foil', 'value' => $state['foil'], 'label' => ( 'foil' === $state['foil'] ? 'Foil' : 'Regular' ) );
	}

	if ( empty( $chips ) ) {
		return '';
	}

	ob_start();
	echo '<div class="shop-active-chips" role="list" aria-label="' . esc_attr__( 'Filtros activos', 'onplay' ) . '">';
	foreach ( $chips as $chip ) {
		echo '<button type="button" class="chip chip-active" role="listitem" data-chip-group="' . esc_attr( $chip['group'] ) . '" data-chip-value="' . esc_attr( $chip['value'] ) . '">';
		echo '<span class="chip__label">' . esc_html( $chip['label'] ) . '</span>';
		echo '<span class="chip__close" aria-hidden="true">×</span>';
		echo '<span class="screen-reader-text">' . esc_html__( 'Quitar filtro', 'onplay' ) . '</span>';
		echo '</button>';
	}
	echo '<button type="button" class="shop-active-chips__clear" data-chip-clear>' . esc_html__( 'Limpiar todo', 'onplay' ) . '</button>';
	echo '</div>';
	return ob_get_clean();
}

/**
 * Pipeline completo: state → IDs → colapso → orden → paginación → render.
 *
 * @param array $state
 * @return array{html:string,pagination_html:string,chips_html:string,total_groups:int,total_pages:int,current_page:int,representative_ids:int[]}
 */
function onplay_filters_run( $state ) {
	$ids        = onplay_filters_query_ids( $state );
	$groups     = onplay_collapse_products_by_print_key( $ids );
	$groups     = onplay_sort_groups( $groups, $state['sort'] );

	$total_groups = count( $groups );
	$per_page     = ONPLAY_LISTING_PER_PAGE;
	$total_pages  = max( 1, (int) ceil( $total_groups / $per_page ) );
	$current_page = min( max( 1, (int) $state['page'] ), $total_pages );
	$offset       = ( $current_page - 1 ) * $per_page;
	$page_groups  = array_slice( $groups, $offset, $per_page );

	$html            = onplay_filters_render_grid( $page_groups );
	$pagination_html = onplay_filters_render_pagination( $current_page, $total_pages );
	$chips_html      = onplay_filters_render_chips( $state );

	return array(
		'html'               => $html,
		'pagination_html'    => $pagination_html,
		'chips_html'         => $chips_html,
		'total_groups'       => $total_groups,
		'total_pages'        => $total_pages,
		'current_page'       => $current_page,
		'representative_ids' => array_map( function ( $g ) { return (int) $g['representative_id']; }, $page_groups ),
	);
}

/**
 * Handler AJAX.
 */
function onplay_ajax_filter_handler() {
	check_ajax_referer( 'onplay_filter', 'nonce' );

	$state  = onplay_filters_parse_request( $_GET );
	$result = onplay_filters_run( $state );

	wp_send_json_success(
		array(
			'html'            => $result['html'],
			'pagination_html' => $result['pagination_html'],
			'chips_html'      => $result['chips_html'],
			'total_groups'    => (int) $result['total_groups'],
			'total_pages'     => (int) $result['total_pages'],
			'current_page'    => (int) $result['current_page'],
		)
	);
}
add_action( 'wp_ajax_onplay_filter', 'onplay_ajax_filter_handler' );
add_action( 'wp_ajax_nopriv_onplay_filter', 'onplay_ajax_filter_handler' );
