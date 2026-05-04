<?php
/**
 * SEO: schemas JSON-LD, Open Graph y Twitter Cards.
 *
 * Emite desde el tema:
 *   - Organization (Comercializadora y Distribuidora BM Limitada — razón social real,
 *     RUT 77.862.085-5, email contacto@onplay.cl, dirección física Local 54 Galería
 *     Casa Colorada). `name` = marca pública ("Onplay"); `legalName` = razón social.
 *   - WebSite + SearchAction (solo en home)
 *   - BreadcrumbList (archive + single)
 *   - Product con precio CLP, SKU, availability (single-product)
 *   - OG tags + Twitter Card summary_large_image
 *
 * Si más adelante se instala Rank Math / Yoast, desactivar en su config los
 * schemas Product / Organization / BreadcrumbList / WebSite para evitar
 * duplicados. Rank Math queda responsable solo de sitemap + <title> + meta
 * description + canonical.
 *
 * @package Onplay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Devuelve URL del logo del sitio (custom logo → fallback a /assets/img/logo.png).
 */
function onplay_seo_logo_url() {
	$logo_id = (int) get_theme_mod( 'custom_logo' );
	if ( $logo_id > 0 ) {
		$src = wp_get_attachment_image_src( $logo_id, 'full' );
		if ( is_array( $src ) && ! empty( $src[0] ) ) {
			return $src[0];
		}
	}
	return ONPLAY_THEME_URI . '/assets/img/logo.png';
}

/**
 * Organization — se emite en todas las páginas.
 */
function onplay_seo_organization_schema() {
	return array(
		'@context'  => 'https://schema.org',
		'@type'     => 'Organization',
		'@id'       => home_url( '/#organization' ),
		'name'      => 'Onplay',
		'legalName' => 'Comercializadora y Distribuidora BM Limitada',
		'taxID'     => '77.862.085-5',
		'url'       => home_url( '/' ),
		'logo'      => onplay_seo_logo_url(),
		'email'     => 'contacto@onplay.cl',
		'telephone' => '+56966826121',
		'address'   => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => 'Merced 832, Local 54, Galería Casa Colorada',
			'addressLocality' => 'Santiago Centro',
			'addressRegion'   => 'Región Metropolitana',
			'addressCountry'  => 'CL',
		),
	);
}

/**
 * WebSite + SearchAction — solo en home.
 */
function onplay_seo_website_schema() {
	$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/tienda/' );
	return array(
		'@context'        => 'https://schema.org',
		'@type'           => 'WebSite',
		'@id'             => home_url( '/#website' ),
		'url'             => home_url( '/' ),
		'name'            => get_bloginfo( 'name' ),
		'description'     => get_bloginfo( 'description' ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => trailingslashit( $shop_url ) . '?q={search_term_string}',
			),
			'query-input' => 'required name=search_term_string',
		),
	);
}

/**
 * BreadcrumbList para el archive (tienda + product_cat).
 */
function onplay_seo_archive_breadcrumb_schema() {
	$items = array(
		array(
			'@type'    => 'ListItem',
			'position' => 1,
			'name'     => __( 'Inicio', 'onplay' ),
			'item'     => home_url( '/' ),
		),
	);

	$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/tienda/' );

	// TCG-aware: el 2º crumb refleja "One Piece" o "Magic" según contexto.
	$is_op = function_exists( 'onplay_op_is_archive' ) && onplay_op_is_archive();
	$items[] = array(
		'@type'    => 'ListItem',
		'position' => 2,
		'name'     => $is_op ? __( 'One Piece', 'onplay' ) : __( 'Magic', 'onplay' ),
		'item'     => $is_op ? add_query_arg( 'set', 'one-piece-tcg', $shop_url ) : $shop_url,
	);

	if ( is_tax( 'product_cat' ) ) {
		$term = get_queried_object();
		if ( $term && isset( $term->name ) ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => 3,
				'name'     => $term->name,
				'item'     => get_term_link( $term ),
			);
		}
	}

	return array(
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $items,
	);
}

/**
 * BreadcrumbList para single-product.
 */
function onplay_seo_single_breadcrumb_schema( $product ) {
	$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/tienda/' );

	// TCG-aware: detectar si el producto es OP (descendiente de one-piece-tcg).
	$is_op    = false;
	$cats     = get_the_terms( $product->get_id(), 'product_cat' );
	if ( is_array( $cats ) ) {
		foreach ( $cats as $t ) {
			if ( function_exists( 'onplay_op_term_descends_from' ) && onplay_op_term_descends_from( $t, 'one-piece-tcg' ) ) {
				$is_op = true;
				break;
			}
		}
	}

	$items = array(
		array(
			'@type'    => 'ListItem',
			'position' => 1,
			'name'     => __( 'Inicio', 'onplay' ),
			'item'     => home_url( '/' ),
		),
		array(
			'@type'    => 'ListItem',
			'position' => 2,
			'name'     => $is_op ? __( 'One Piece', 'onplay' ) : __( 'Magic', 'onplay' ),
			'item'     => $is_op ? add_query_arg( 'set', 'one-piece-tcg', $shop_url ) : $shop_url,
		),
	);

	$set_term = null;
	if ( is_array( $cats ) ) {
		foreach ( $cats as $t ) {
			if ( $t->parent > 0 ) {
				$set_term = $t;
				break;
			}
		}
	}

	$position = 3;
	if ( $set_term ) {
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $position,
			'name'     => $set_term->name,
			'item'     => get_term_link( $set_term ),
		);
		$position++;
	}

	$title_raw   = get_the_title( $product->get_id() );
	$title_clean = trim( (string) preg_replace( '/\s*\(Foil\)\s*/i', '', $title_raw ) );

	$items[] = array(
		'@type'    => 'ListItem',
		'position' => $position,
		'name'     => $title_clean,
		'item'     => get_permalink( $product->get_id() ),
	);

	return array(
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $items,
	);
}

/**
 * Product schema para single-product.
 */
function onplay_seo_product_schema( $product ) {
	$product_id = $product->get_id();
	$sku        = $product->get_sku();
	$price      = (float) $product->get_price();
	$stock      = (int) $product->get_stock_quantity();

	$availability = $product->is_in_stock()
		? 'https://schema.org/InStock'
		: 'https://schema.org/OutOfStock';

	$image_url = '';
	if ( has_post_thumbnail( $product_id ) ) {
		$src = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'full' );
		if ( is_array( $src ) && ! empty( $src[0] ) ) {
			$image_url = $src[0];
		}
	}

	$set_name = '';
	$cats     = get_the_terms( $product_id, 'product_cat' );
	if ( is_array( $cats ) ) {
		foreach ( $cats as $t ) {
			if ( $t->parent > 0 ) {
				$set_name = $t->name;
				break;
			}
		}
	}

	$oracle      = (string) get_post_meta( $product_id, '_onplay_oracle_text', true );
	$type_line   = (string) get_post_meta( $product_id, '_onplay_type_line', true );
	$description = trim( $type_line . "\n\n" . $oracle );
	if ( '' === $description ) {
		$description = wp_strip_all_tags( (string) $product->get_short_description() );
	}

	$schema = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Product',
		'@id'         => get_permalink( $product_id ) . '#product',
		'name'        => get_the_title( $product_id ),
		'url'         => get_permalink( $product_id ),
		'sku'         => $sku,
		'description' => $description,
		'offers'      => array(
			'@type'          => 'Offer',
			'url'            => get_permalink( $product_id ),
			'priceCurrency'  => get_woocommerce_currency(),
			'price'          => number_format( $price, 0, '.', '' ),
			'availability'   => $availability,
			'itemCondition'  => 'https://schema.org/NewCondition',
			'seller'         => array(
				'@type'     => 'Organization',
				'name'      => 'Onplay',
				'legalName' => 'Comercializadora y Distribuidora BM Limitada',
				'taxID'     => '77.862.085-5',
			),
		),
	);

	if ( '' !== $image_url ) {
		$schema['image'] = $image_url;
	}

	if ( '' !== $set_name ) {
		$schema['brand'] = array(
			'@type' => 'Brand',
			'name'  => $set_name,
		);
	}

	return $schema;
}

/**
 * Emite los schemas JSON-LD en wp_head.
 */
add_action(
	'wp_head',
	function () {
		$schemas = array();

		$schemas[] = onplay_seo_organization_schema();

		if ( is_front_page() ) {
			$schemas[] = onplay_seo_website_schema();
		}

		if ( ( function_exists( 'is_shop' ) && is_shop() ) || is_tax( 'product_cat' ) ) {
			$schemas[] = onplay_seo_archive_breadcrumb_schema();
		}

		if ( function_exists( 'is_product' ) && is_product() ) {
			$product = wc_get_product( get_queried_object_id() );
			if ( $product instanceof WC_Product ) {
				$schemas[] = onplay_seo_single_breadcrumb_schema( $product );
				$schemas[] = onplay_seo_product_schema( $product );
			}
		}

		foreach ( $schemas as $schema ) {
			echo "\n<script type=\"application/ld+json\">\n";
			echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
			echo "\n</script>\n";
		}
	},
	5
);

/**
 * OG + Twitter Cards. Se emite en todas las páginas públicas.
 */
add_action(
	'wp_head',
	function () {
		if ( is_admin() ) {
			return;
		}

		$site_name = get_bloginfo( 'name' );
		$title     = wp_get_document_title();
		$url       = is_singular() ? get_permalink() : ( is_tax( 'product_cat' ) ? get_term_link( get_queried_object() ) : home_url( $_SERVER['REQUEST_URI'] ?? '/' ) );
		$image     = onplay_seo_logo_url();
		$desc      = get_bloginfo( 'description' );
		$type      = 'website';

		if ( function_exists( 'is_product' ) && is_product() ) {
			$product = wc_get_product( get_queried_object_id() );
			if ( $product instanceof WC_Product ) {
				$type = 'product';
				if ( has_post_thumbnail( $product->get_id() ) ) {
					$src = wp_get_attachment_image_src( get_post_thumbnail_id( $product->get_id() ), 'full' );
					if ( is_array( $src ) && ! empty( $src[0] ) ) {
						$image = $src[0];
					}
				}
				$oracle = (string) get_post_meta( $product->get_id(), '_onplay_oracle_text', true );
				if ( '' !== $oracle ) {
					$desc = wp_trim_words( $oracle, 30, '…' );
				}
			}
		}

		$url = is_string( $url ) ? $url : home_url( '/' );

		printf( '<meta property="og:type" content="%s">' . "\n", esc_attr( $type ) );
		printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( $site_name ) );
		printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
		printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $desc ) );
		printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );
		printf( '<meta property="og:locale" content="%s">' . "\n", esc_attr( str_replace( '-', '_', get_locale() ) ) );

		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
		printf( '<meta name="twitter:title" content="%s">' . "\n", esc_attr( $title ) );
		printf( '<meta name="twitter:description" content="%s">' . "\n", esc_attr( $desc ) );
		printf( '<meta name="twitter:image" content="%s">' . "\n", esc_url( $image ) );
	},
	6
);

/**
 * Canonical mínimo. Rank Math/Yoast lo sobrescriben cuando están activos.
 * Emitimos solo en casos donde WP core no lo hace (home / archive).
 */
add_action(
	'wp_head',
	function () {
		if ( is_admin() || is_singular() ) {
			return; // WP core emite canonical en is_singular().
		}

		$url = '';
		if ( is_front_page() ) {
			$url = home_url( '/' );
		} elseif ( is_tax( 'product_cat' ) ) {
			$term = get_queried_object();
			if ( $term ) {
				$url = get_term_link( $term );
			}
		} elseif ( function_exists( 'is_shop' ) && is_shop() ) {
			$url = wc_get_page_permalink( 'shop' );
		}

		if ( is_string( $url ) && '' !== $url ) {
			printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $url ) );
		}
	},
	4
);
