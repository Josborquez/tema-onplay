<?php
/**
 * M-OP-filtros — Detección de contexto, parsing de params y hooks de query.
 *
 * Aproximación A: en lugar de implementar un endpoint AJAX paralelo, se
 * extiende `inc/filters-ajax.php` mediante dos hooks documentados ahí:
 *
 *   - apply_filters( 'onplay_filters_state_after_parse', $state, $src )
 *     Permite agregar claves al estado parseado desde la request.
 *
 *   - apply_filters( 'onplay_filters_meta_query', $meta_query, $state )
 *     Permite agregar clausulas al meta_query antes de la WP_Query del listing.
 *
 * Más un `pre_get_posts` defensivo para casos donde la main query (canonical,
 * schemas, etc.) reciba params op_*. NO afecta el grid renderizado, porque el
 * listing usa su propio WP_Query — los hooks de arriba son la vía real.
 *
 * @package Onplay
 */

defined( 'ABSPATH' ) || exit;

/**
 * Catálogo cerrado de colores One Piece.
 *
 * Letra ('l') sigue convención del binder: R/G/B/P/K (Black)/Y, M para Multicolor.
 * Multicolor es marcador de duales — el filtro real captura duales con LIKE
 * por color individual, así que MULTICOLOR como opción se omite del MVP.
 *
 * @return array<string,array{label:string,letter:string,bg:string,fg:string,border?:string}>
 */
function onplay_op_colors() {
	return array(
		'Red'    => array( 'label' => __( 'Red', 'onplay' ),    'letter' => 'R', 'bg' => '#D32027', 'fg' => '#fff' ),
		'Green'  => array( 'label' => __( 'Green', 'onplay' ),  'letter' => 'G', 'bg' => '#1F8B3F', 'fg' => '#fff' ),
		'Blue'   => array( 'label' => __( 'Blue', 'onplay' ),   'letter' => 'B', 'bg' => '#1E5DB0', 'fg' => '#fff' ),
		'Purple' => array( 'label' => __( 'Purple', 'onplay' ), 'letter' => 'P', 'bg' => '#6E3FA3', 'fg' => '#fff' ),
		'Black'  => array( 'label' => __( 'Black', 'onplay' ),  'letter' => 'K', 'bg' => '#1A1A1A', 'fg' => '#fff', 'border' => '#444' ),
		'Yellow' => array( 'label' => __( 'Yellow', 'onplay' ), 'letter' => 'Y', 'bg' => '#E8B04B', 'fg' => '#000' ),
	);
}

/**
 * Tipos de carta One Piece. Valores en UPPERCASE igual que el Binder OP.
 *
 * @return array<string,string> code => label.
 */
function onplay_op_card_types() {
	return array(
		'LEADER'    => __( 'Leader', 'onplay' ),
		'CHARACTER' => __( 'Character', 'onplay' ),
		'EVENT'     => __( 'Event', 'onplay' ),
		'STAGE'     => __( 'Stage', 'onplay' ),
	);
}

/**
 * Opciones del filtro Illustration Type.
 *
 * @return array<string,string> code => label.
 */
function onplay_op_illustration_types() {
	return array(
		'normal' => __( 'Normal', 'onplay' ),
		'alt'    => __( 'Alternate Art', 'onplay' ),
	);
}

/**
 * Slug raíz de la categoría One Piece TCG.
 */
const ONPLAY_OP_ROOT_SLUG = 'one-piece-tcg';

/**
 * ¿El term recibido desciende (o es) la categoría root indicada?
 *
 * @param WP_Term $term       Término a chequear.
 * @param string  $root_slug  Slug raíz.
 * @return bool
 */
function onplay_op_term_descends_from( $term, $root_slug ) {
	if ( ! ( $term instanceof WP_Term ) ) {
		return false;
	}
	if ( $term->slug === $root_slug ) {
		return true;
	}
	$ancestors = get_ancestors( $term->term_id, $term->taxonomy );
	foreach ( $ancestors as $aid ) {
		$a = get_term( $aid, $term->taxonomy );
		if ( $a instanceof WP_Term && $a->slug === $root_slug ) {
			return true;
		}
	}
	return false;
}

/**
 * Detecta si la request actual es un archive de One Piece TCG.
 *
 * Cubre los 3 caminos por los que la categoría OP llega al listing:
 *   1. /tienda/?set=one-piece-tcg (o cualquier slug descendiente) — vía redirect
 *      de inc/woocommerce.php que mapea taxonomy archives a la shop page.
 *   2. is_product_taxonomy() directo — defensivo por si algún día se desactiva
 *      el redirect, o por canonical/schema que dispara la main query del archive.
 *   3. is_shop() con cualquier param op_* — fallback útil para deep-linking.
 *
 * @return bool
 */
function onplay_op_is_archive() {
	if ( is_admin() ) {
		return false;
	}
	if ( ! function_exists( 'is_product_taxonomy' ) ) {
		return false;
	}

	// Camino 2: archive nativo de product_cat (rara vez se llega — el redirect
	// de inc/woocommerce.php mapea a /tienda/?set= antes de renderizar).
	if ( is_product_taxonomy() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term && onplay_op_term_descends_from( $term, ONPLAY_OP_ROOT_SLUG ) ) {
			return true;
		}
	}

	// Camino 1: /tienda/?set=<slug-OP-o-descendiente>.
	if ( function_exists( 'is_shop' ) && is_shop() ) {
		$set = isset( $_GET['set'] ) ? wp_unslash( $_GET['set'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( '' !== $set ) {
			// `set` puede venir como string o como array de slugs.
			$slugs = is_array( $set ) ? $set : array( $set );
			foreach ( $slugs as $slug ) {
				$slug = sanitize_title( (string) $slug );
				if ( '' === $slug ) {
					continue;
				}
				$term = get_term_by( 'slug', $slug, 'product_cat' );
				if ( $term instanceof WP_Term && onplay_op_term_descends_from( $term, ONPLAY_OP_ROOT_SLUG ) ) {
					return true;
				}
			}
		}

		// Camino 3: deep-link con op_* sin set= explícito. Útil para QA y para
		// URLs de prueba; en producción siempre habrá set= si la nav es real.
		if ( ! empty( $_GET['op_color'] ) || ! empty( $_GET['op_type'] ) || ! empty( $_GET['op_alt'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true;
		}
	}

	return false;
}

/**
 * Lee un param op_* y lo devuelve como array de strings sanitizados.
 *
 * Acepta tanto comma-separated (`?op_color=red,green`, formato preferido) como
 * array notation (`?op_color[]=red&op_color[]=green`, compat con el JS legacy
 * de Filters por si alguna URL así llega).
 *
 * @param string $param  Nombre del param (op_color, op_type, op_alt).
 * @param array  $src    Source ($_GET por defecto en runtime).
 * @return string[]      Lista deduplicada, valores trimmed, sin vacíos.
 */
function onplay_op_get_param( $param, $src = null ) {
	if ( null === $src ) {
		$src = $_GET; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
	if ( empty( $src[ $param ] ) ) {
		return array();
	}
	$raw = $src[ $param ];
	$raw = is_array( $raw ) ? $raw : explode( ',', (string) $raw );
	$out = array();
	foreach ( $raw as $v ) {
		$v = sanitize_text_field( wp_unslash( (string) $v ) );
		$v = trim( $v );
		if ( '' !== $v ) {
			$out[] = $v;
		}
	}
	return array_values( array_unique( $out ) );
}

/**
 * Hook 1 — extender el state parseado del listing.
 *
 * Agrega `op_color`, `op_type`, `op_alt` al state que arma `inc/filters-ajax.php`,
 * para que viajen por todo el pipeline (run → ids → render → chips).
 *
 * @param array $state  State ya parseado por `onplay_filters_parse_request`.
 * @param array $src    Request original ($_GET o $_POST).
 * @return array
 */
function onplay_op_extend_state( $state, $src ) {
	$state['op_color'] = onplay_op_get_param( 'op_color', $src );
	$state['op_type']  = onplay_op_get_param( 'op_type', $src );
	$state['op_alt']   = onplay_op_get_param( 'op_alt', $src );

	// Normalizar valores: type a UPPERCASE, alt a lowercase, color con first-letter
	// upper para matchear el contrato del Binder OP.
	$state['op_type']  = array_map( 'strtoupper', $state['op_type'] );
	$state['op_alt']   = array_map( 'strtolower', $state['op_alt'] );
	$state['op_color'] = array_map(
		function ( $c ) {
			return ucfirst( strtolower( $c ) );
		},
		$state['op_color']
	);

	return $state;
}
add_filter( 'onplay_filters_state_after_parse', 'onplay_op_extend_state', 10, 2 );

/**
 * Hook 2 — agregar clausulas meta_query del módulo OP al pipeline del listing.
 *
 * Se aplica cuando el state contiene op_*. No depende de `onplay_op_is_archive()`
 * porque si el state trae op_*, el filtro debe respetarse independiente de la URL
 * (caso AJAX donde `is_shop()` puede no ser true).
 *
 * @param array $meta_query  Meta_query actual armado por filters-ajax.
 * @param array $state       State del listing.
 * @return array
 */
function onplay_op_apply_meta_query( $meta_query, $state ) {
	$has = ! empty( $state['op_color'] ) || ! empty( $state['op_type'] ) || ! empty( $state['op_alt'] );
	if ( ! $has ) {
		return $meta_query;
	}

	// Color — LIKE para soportar duales como "Red/Yellow" o "Red/Purple".
	if ( ! empty( $state['op_color'] ) ) {
		$clause = array( 'relation' => 'OR' );
		foreach ( $state['op_color'] as $c ) {
			$clause[] = array(
				'key'     => '_color',
				'value'   => $c,
				'compare' => 'LIKE',
			);
		}
		$meta_query[] = $clause;
	}

	// Card type — IN, ya viene en UPPERCASE por extend_state.
	if ( ! empty( $state['op_type'] ) ) {
		$meta_query[] = array(
			'key'     => '_card_type',
			'value'   => array_values( $state['op_type'] ),
			'compare' => 'IN',
		);
	}

	// Illustration — alt → "yes", normal → "no". `op_alt=normal,alt` ⇒ ambos
	// (equivalente a no filtrar, lo cubrimos rapidito sin clausula extra).
	if ( ! empty( $state['op_alt'] ) ) {
		$alt_values = array();
		if ( in_array( 'normal', $state['op_alt'], true ) ) {
			$alt_values[] = 'no';
		}
		if ( in_array( 'alt', $state['op_alt'], true ) ) {
			$alt_values[] = 'yes';
		}
		// Si están los dos, no agregamos clausula (filtro inerte).
		if ( count( $alt_values ) === 1 ) {
			$meta_query[] = array(
				'key'   => '_is_alt_art',
				'value' => $alt_values[0],
			);
		}
	}

	return $meta_query;
}
add_filter( 'onplay_filters_meta_query', 'onplay_op_apply_meta_query', 10, 2 );

/**
 * Hook 2.b — exclusión simétrica para Magic-strict en el listing.
 *
 * Cuando state['tcg'] === 'mtg' (search submit con ?tcg=mtg desde header
 * Magic), excluye productos descendientes de one-piece-tcg del tax_query
 * del listing. Inverso del filtro op_color/op_type que SOLO incluye OP.
 *
 * @param array $tax_query
 * @param array $state
 * @return array
 */
function onplay_op_apply_tax_exclusion( $tax_query, $state ) {
	if ( empty( $state['tcg'] ) ) {
		return $tax_query;
	}

	// Resolución de conflicto: si el usuario hizo una elección explícita de
	// set=, esa gana sobre el tcg= (que es un default de contexto). Sin esto,
	// `?tcg=mtg&set=one-piece-tcg` da 0 (mtg excluye OP, set fuerza OP, AND
	// vacío). Idem `?tcg=op&set=khans` en sentido inverso.
	if ( ! empty( $state['set'] ) ) {
		return $tax_query;
	}

	if ( 'mtg' === $state['tcg'] ) {
		// Magic-strict: excluir descendientes de one-piece-tcg.
		$tax_query[] = array(
			'taxonomy'         => 'product_cat',
			'field'            => 'slug',
			'terms'            => array( ONPLAY_OP_ROOT_SLUG ),
			'include_children' => true,
			'operator'         => 'NOT IN',
		);
	} elseif ( 'op' === $state['tcg'] ) {
		// OP-only: incluir solo descendientes de one-piece-tcg.
		$tax_query[] = array(
			'taxonomy'         => 'product_cat',
			'field'            => 'slug',
			'terms'            => array( ONPLAY_OP_ROOT_SLUG ),
			'include_children' => true,
			'operator'         => 'IN',
		);
	}
	return $tax_query;
}
add_filter( 'onplay_filters_tax_query', 'onplay_op_apply_tax_exclusion', 10, 2 );

/**
 * Hook 3 — defensivo. La main query del archive no rendea el grid del listing
 * (eso lo hace filters-ajax mediante WP_Query propio), pero algunos consumidores
 * sí la usan (canonical, schemas SEO, BreadcrumbList). Mantener consistencia.
 *
 * @param WP_Query $query
 */
function onplay_op_pre_get_posts( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( ! onplay_op_is_archive() ) {
		return;
	}

	// Reusamos la lógica de meta_query del hook 2 con el state ya parseado.
	$state      = onplay_op_extend_state( array( 'op_color' => array(), 'op_type' => array(), 'op_alt' => array() ), $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$meta_query = (array) $query->get( 'meta_query' );
	$meta_query = onplay_op_apply_meta_query( $meta_query, $state );

	if ( ! empty( $meta_query ) ) {
		// Forzar relation AND si hay multiples clausulas top-level.
		if ( count( $meta_query ) > 1 && ! isset( $meta_query['relation'] ) ) {
			$meta_query['relation'] = 'AND';
		}
		$query->set( 'meta_query', $meta_query );
	}
}
add_action( 'pre_get_posts', 'onplay_op_pre_get_posts' );
