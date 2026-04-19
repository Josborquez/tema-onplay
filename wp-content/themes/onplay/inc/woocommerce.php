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
 * Render a single mana symbol as an inline SVG span.
 *
 * @param string $symbol e.g. "W", "U", "B", "R", "G", "C", "2", "X".
 * @param string $size   "sm" | "md" | "lg".
 * @return string HTML.
 */
function onplay_render_mana_symbol( $symbol, $size = 'md' ) {
	$symbol = strtoupper( trim( (string) $symbol ) );
	$class  = 'mana';
	if ( 'sm' === $size ) {
		$class .= ' mana-sm';
	} elseif ( 'lg' === $size ) {
		$class .= ' mana-lg';
	}

	$colors = array(
		'W' => array( 'bg' => '#FFFBD5' ),
		'U' => array( 'bg' => '#AAE0FA' ),
		'B' => array( 'bg' => '#CBC2BF' ),
		'R' => array( 'bg' => '#F9AA8F' ),
		'G' => array( 'bg' => '#9BD3AE' ),
		'C' => array( 'bg' => '#CCC2C0' ),
	);

	$glyphs = array(
		'W' => '<svg viewBox="0 0 24 24" width="60%" height="60%" aria-hidden="true"><path fill="currentColor" d="M12 2 L14 9 L21 9 L15.5 13 L17.5 20 L12 15.5 L6.5 20 L8.5 13 L3 9 L10 9 Z"/></svg>',
		'U' => '<svg viewBox="0 0 24 24" width="60%" height="60%" aria-hidden="true"><path fill="currentColor" d="M12 2 C12 2 4 10 4 15 C4 19.5 7.5 22 12 22 C16.5 22 20 19.5 20 15 C20 10 12 2 12 2 Z"/></svg>',
		'B' => '<svg viewBox="0 0 24 24" width="60%" height="60%" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="currentColor"/></svg>',
		'R' => '<svg viewBox="0 0 24 24" width="65%" height="65%" aria-hidden="true"><path fill="currentColor" d="M12 2 C9 8 5 9 5 14 C5 18.5 8.3 22 12 22 C15.7 22 19 18.5 19 14 C19 11 16 10 14 12 C14 9 13 5 12 2 Z"/></svg>',
		'G' => '<svg viewBox="0 0 24 24" width="65%" height="65%" aria-hidden="true"><path fill="currentColor" d="M12 2 C7 5 5 10 6 14 C4 15 3 17 4 19 C6 21 9 20 10 18 C11 20 13 22 16 21 C19 20 21 16 20 12 C19 8 16 4 12 2 Z"/></svg>',
		'C' => '<svg viewBox="0 0 24 24" width="55%" height="55%" aria-hidden="true"><path fill="currentColor" d="M12 3 L21 12 L12 21 L3 12 Z"/></svg>',
	);

	// Numeric or X → text inside generic pip.
	if ( isset( $glyphs[ $symbol ] ) ) {
		$bg = $colors[ $symbol ]['bg'];
		return sprintf(
			'<span class="%s" style="background:%s;color:#000" title="%s" aria-label="%s">%s</span>',
			esc_attr( $class ),
			esc_attr( $bg ),
			esc_attr( $symbol ),
			esc_attr( $symbol ),
			$glyphs[ $symbol ]
		);
	}

	// Numeric or special: show text in grey pip.
	$label = $symbol;
	return sprintf(
		'<span class="%s" style="background:#CCC2C0;color:#000;font-weight:700" title="%s" aria-label="%s">%s</span>',
		esc_attr( $class ),
		esc_attr( $label ),
		esc_attr( $label ),
		esc_html( $label )
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
				// Fuse mana like "W/U" → just show both separated; simple fallback.
				if ( false !== strpos( $sym, '/' ) ) {
					$sub = explode( '/', $sym );
					foreach ( $sub as $s ) {
						$html .= onplay_render_mana_symbol( $s, $size );
					}
					continue;
				}
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
 * Render a simple diamond set icon SVG.
 *
 * @param string $set_code
 * @param int    $size
 * @return string
 */
function onplay_render_set_icon( $set_code, $size = 14 ) {
	$set_code = strtoupper( (string) $set_code );
	$label    = substr( $set_code, 0, 3 );
	$size     = (int) $size;
	return sprintf(
		'<svg class="set-icon" width="%1$d" height="%1$d" viewBox="0 0 16 16" aria-hidden="true"><path d="M8 1 L15 8 L8 15 L1 8 Z" fill="#6B6B6B" stroke="rgba(0,0,0,0.5)" stroke-width="0.5"/><text x="8" y="10.5" text-anchor="middle" font-size="6" font-family="var(--body)" font-weight="700" fill="white">%2$s</text></svg>',
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
