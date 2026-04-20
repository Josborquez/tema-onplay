<?php
/**
 * WooCommerce integration helpers and overrides.
 *
 * @package Onplay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Declare support for WooCommerce templates.
add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'woocommerce' );
	}
);

/**
 * Extract the print_key (set_code-collector_number) from a SKU.
 *
 * @param string $sku
 * @return string
 */
function onplay_print_key_from_sku( $sku ) {
	if ( ! is_string( $sku ) || '' === $sku ) {
		return '';
	}
	// SKU del manager: {SET}-{COLLECTOR}-{COND}-{LANG}.
	// El collector puede contener caracteres especiales (★, †, prefijos showcase),
	// por eso usamos explode en lugar de una regex restrictiva.
	$parts = explode( '-', $sku );
	if ( count( $parts ) < 2 || '' === $parts[0] || '' === $parts[1] ) {
		return '';
	}
	return strtoupper( $parts[0] ) . '-' . strtoupper( $parts[1] );
}

/**
 * Split SKU into components: set_code, collector_number, condition, language.
 *
 * @param string $sku
 * @return array
 */
function onplay_parse_sku( $sku ) {
	$out = array(
		'set_code'         => '',
		'collector_number' => '',
		'condition'        => '',
		'language'         => '',
	);
	if ( ! is_string( $sku ) || '' === $sku ) {
		return $out;
	}
	$parts = explode( '-', $sku );
	if ( isset( $parts[0] ) ) {
		$out['set_code'] = strtoupper( $parts[0] );
	}
	if ( isset( $parts[1] ) ) {
		$out['collector_number'] = strtoupper( $parts[1] );
	}
	if ( isset( $parts[2] ) ) {
		$out['condition'] = strtoupper( $parts[2] );
	}
	if ( isset( $parts[3] ) ) {
		$out['language'] = strtoupper( $parts[3] );
	}
	return $out;
}

/**
 * Normaliza un título de producto a "nombre canónico de carta".
 * Hoy el manager solo añade el sufijo " (Foil)" — verificado en DB
 * (SELECT DISTINCT suffix … solo aparece "(Foil)" — 45 productos).
 * Se conserva el separador "//" de cartas doble cara.
 *
 * @param string $title
 * @return string
 */
function onplay_normalize_card_name( $title ) {
	if ( ! is_string( $title ) || '' === $title ) {
		return '';
	}
	return trim( preg_replace( '/\s*\(Foil\)\s*$/i', '', $title ) );
}

/**
 * Render a single mana symbol usando mana-font (Andrew Gioia, MIT).
 *
 * Mana-font convierte `<i class="ms ms-X ms-cost ms-shadow">` en un círculo tipado
 * con el glifo + fondo oficial de la comunidad MTG. Clases para:
 *  - básicos: ms-w, ms-u, ms-b, ms-r, ms-g, ms-c (colorless)
 *  - genéricos: ms-0..ms-20, ms-x, ms-y, ms-z
 *  - snow: ms-s
 *  - híbridos: ms-wu, ms-wb, ms-ub, ms-ur, ms-br, ms-bg, ms-rg, ms-rw, ms-gw, ms-gu
 *  - phyrexian: ms-wp, ms-up, ms-bp, ms-rp, ms-gp
 *
 * @param string $symbol e.g. "W", "U", "2", "X", "W/U".
 * @param string $size   "sm" | "md" | "lg".
 * @return string HTML.
 */
function onplay_render_mana_symbol( $symbol, $size = 'md' ) {
	$symbol = strtoupper( trim( (string) $symbol ) );
	if ( '' === $symbol ) {
		return '';
	}

	// Mana-font usa minúsculas y sin `/` en los híbridos ("W/U" → "wu").
	$slug = strtolower( str_replace( '/', '', $symbol ) );

	$class = 'ms ms-' . $slug . ' ms-cost ms-shadow';
	if ( 'sm' === $size ) {
		$class .= ' mana-sm';
	} elseif ( 'lg' === $size ) {
		$class .= ' mana-lg';
	}

	return sprintf(
		'<i class="%s" title="%s" aria-label="%s"></i>',
		esc_attr( $class ),
		esc_attr( $symbol ),
		esc_attr( $symbol )
	);
}

/**
 * Parse a Scryfall mana_cost string ("{2}{U}{U}") into HTML.
 *
 * @param string $cost
 * @param string $size
 * @return string
 */
function onplay_render_mana_cost( $cost, $size = 'md' ) {
	if ( ! is_string( $cost ) || '' === $cost ) {
		return '';
	}
	// Doble cara viene como "{R} // {3}{G}"
	$parts = preg_split( '/\s*\/\/\s*/', $cost );
	$html  = '';
	foreach ( $parts as $idx => $part ) {
		if ( $idx > 0 ) {
			$html .= '<span class="mana-sep">//</span>';
		}
		if ( preg_match_all( '/\{([^\}]+)\}/', $part, $m ) ) {
			foreach ( $m[1] as $sym ) {
				// Híbridos (W/U) y phyrexianos (W/P) se pasan combinados —
				// mana-font los renderiza como un único símbolo (ms-wu, ms-wp).
				$html .= onplay_render_mana_symbol( $sym, $size );
			}
		}
	}
	return $html;
}

/**
 * Render oracle text, replacing inline {X} symbols with mana SVGs,
 * preserving paragraph breaks.
 *
 * @param string $text
 * @return string HTML.
 */
function onplay_render_oracle_text( $text ) {
	if ( ! is_string( $text ) || '' === $text ) {
		return '';
	}
	$text = wp_strip_all_tags( $text );
	// Split paragraphs on double newlines.
	$paragraphs = preg_split( "/\n\s*\n/", $text );
	$out        = '';
	foreach ( $paragraphs as $p ) {
		$p = trim( $p );
		if ( '' === $p ) {
			continue;
		}
		$rendered = preg_replace_callback(
			'/\{([^\}]+)\}/',
			function ( $match ) {
				return onplay_render_mana_symbol( $match[1], 'sm' );
			},
			esc_html( $p )
		);
		// preserve single newlines within paragraph as <br>.
		$rendered = str_replace( "\n", '<br>', $rendered );
		$out     .= '<p>' . $rendered . '</p>';
	}
	return $out;
}

/**
 * Render Keyrune set icon (webfont oficial MTG).
 *
 * Recibe un código de set (ej. "MH2", "BLB") y lo renderiza como `<i class="ss ss-mh2">`.
 * Keyrune cubre ~500 sets históricos + recientes con un único font load (ver inc/enqueue.php).
 * Para casos donde NO se tiene el código del set (sólo nombre o slug de categoría), usar
 * `onplay_render_set_badge()` — diamante monograma.
 *
 * @param string $set_code Código de set (case-insensitive). Ej. "MH2".
 * @param int    $size Tamaño en px.
 * @return string HTML del `<i>` de Keyrune.
 */
function onplay_render_set_icon( $set_code, $size = 14 ) {
	$set_code_lower = strtolower( (string) $set_code );
	$set_code_upper = strtoupper( (string) $set_code );
	$size           = (int) $size;
	return sprintf(
		'<i class="ss ss-%1$s ss-fw set-icon" style="font-size:%2$dpx" aria-label="%3$s" title="%3$s"></i>',
		esc_attr( $set_code_lower ),
		$size,
		esc_attr( $set_code_upper )
	);
}

/**
 * Render diamante monograma (fallback cuando no hay código de set).
 *
 * Usado en sidebar de filtros y home/featured-sets, donde sólo tenemos nombre/slug
 * de categoría — no el código del set de la DB del manager (el código vive en el SKU).
 * Migración futura: guardar `_onplay_set_code` como term_meta al sincronizar y pasar a
 * `onplay_render_set_icon()` (Keyrune).
 *
 * @param string $label Texto corto (2-3 caracteres).
 * @param int    $size
 * @return string
 */
function onplay_render_set_badge( $label, $size = 14 ) {
	$label = strtoupper( substr( (string) $label, 0, 3 ) );
	$size  = (int) $size;
	return sprintf(
		'<svg class="set-badge" width="%1$d" height="%1$d" viewBox="0 0 16 16" aria-hidden="true"><path d="M8 1 L15 8 L8 15 L1 8 Z" fill="#6B6B6B" stroke="rgba(0,0,0,0.5)" stroke-width="0.5"/><text x="8" y="10.5" text-anchor="middle" font-size="6" font-family="var(--body)" font-weight="700" fill="white">%2$s</text></svg>',
		$size,
		esc_html( $label )
	);
}

/**
 * Get legal formats for a product from the tcg_format_legal taxonomy.
 *
 * @param int $product_id
 * @return string[] E.g. ["Modern","Legacy",...].
 */
function onplay_get_legal_formats( $product_id ) {
	$terms = wp_get_object_terms( (int) $product_id, 'tcg_format_legal', array( 'fields' => 'names' ) );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}
	return $terms;
}

/**
 * Get rarity slug for a product.
 *
 * @param int $product_id
 * @return string e.g. "rare", "mythic".
 */
function onplay_get_rarity( $product_id ) {
	$r = get_post_meta( (int) $product_id, '_onplay_rarity', true );
	if ( $r ) {
		return strtolower( (string) $r );
	}
	return '';
}

/**
 * Is the product foil?
 *
 * @param int $product_id
 * @return bool
 */
function onplay_is_foil( $product_id ) {
	$terms = wp_get_object_terms( (int) $product_id, 'tcg_foil', array( 'fields' => 'names' ) );
	if ( is_array( $terms ) && in_array( 'yes', $terms, true ) ) {
		return true;
	}
	$meta = get_post_meta( (int) $product_id, '_is_foil', true );
	if ( 'yes' === $meta ) {
		return true;
	}
	$title = (string) get_the_title( $product_id );
	if ( false !== stripos( $title, '(Foil)' ) ) {
		return true;
	}
	return false;
}

/**
 * Format CLP price integer as "$1.500".
 *
 * @param int|float $amount
 * @return string
 */
function onplay_format_clp( $amount ) {
	return '$' . number_format( (float) $amount, 0, ',', '.' );
}

/**
 * Redirigir `/product-category/<slug>/` → `/tienda/?set=<slug>`.
 *
 * El listado del tema lee filtros solo de `$_GET` (ver `onplay_filters_parse_request`).
 * Sin este redirect, al entrar desde breadcrumbs o links externos al archive nativo
 * de `product_cat`, el state queda vacío y se muestra el catálogo completo en vez
 * de la categoría pedida. Unificar en `/tienda/?set=` mantiene una sola URL shareable
 * y deja el chip del set coherente con la UI. 302 (no 301) para permitir cambiar
 * de estrategia en M10 si revisamos canonical por SEO.
 */
add_action(
	'template_redirect',
	function () {
		if ( ! is_product_category() ) {
			return;
		}
		$term = get_queried_object();
		if ( ! $term || empty( $term->slug ) ) {
			return;
		}
		$shop_url = wc_get_page_permalink( 'shop' );
		if ( ! $shop_url ) {
			return;
		}
		$target = add_query_arg( 'set', rawurlencode( $term->slug ), $shop_url );
		wp_safe_redirect( $target, 302 );
		exit;
	}
);

/**
 * Remove WC's default loop wrappers — we control markup fully.
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
add_action(
	'woocommerce_before_main_content',
	function () {
		echo '<main id="primary" class="site-main">';
	},
	10
);
add_action(
	'woocommerce_after_main_content',
	function () {
		echo '</main>';
	},
	10
);
