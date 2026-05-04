<?php
/**
 * Asset enqueueing for the Onplay theme.
 *
 * @package Onplay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'wp_enqueue_scripts',
	function () {
		$css_path = ONPLAY_THEME_DIR . '/assets/dist/style.css';
		$js_path  = ONPLAY_THEME_DIR . '/assets/dist/main.js';

		$css_ver = file_exists( $css_path ) ? filemtime( $css_path ) : ONPLAY_THEME_VERSION;
		$js_ver  = file_exists( $js_path ) ? filemtime( $js_path ) : ONPLAY_THEME_VERSION;

		// Google Fonts se inyectan directamente en wp_head (ver abajo) porque
		// esc_url() de WP corta los parámetros duplicados "&family=" del CSS v2.

		wp_enqueue_style(
			'onplay-main',
			ONPLAY_THEME_URI . '/assets/dist/style.css',
			array(),
			$css_ver
		);

		wp_enqueue_script(
			'onplay-main',
			ONPLAY_THEME_URI . '/assets/dist/main.js',
			array(),
			$js_ver,
			true
		);

		// Variables AJAX para WooCommerce nativo + drawer.
		if ( function_exists( 'WC' ) ) {
			wp_localize_script(
				'onplay-main',
				'onplayWC',
				array(
					'ajaxUrl'      => esc_url_raw( WC_AJAX::get_endpoint( '%%endpoint%%' ) ),
					'cartUrl'      => esc_url_raw( wc_get_cart_url() ),
					'checkoutUrl'  => esc_url_raw( wc_get_checkout_url() ),
					'i18n'         => array(
						'addError'  => __( 'No pudimos agregar la carta. Intenta de nuevo.', 'onplay' ),
						'added'     => __( 'Agregado al carrito', 'onplay' ),
					),
				)
			);

			// Endpoint propio del carrito (drawer + /carrito/).
			wp_localize_script(
				'onplay-main',
				'onplayCart',
				array(
					'ajaxUrl' => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
					'nonce'   => wp_create_nonce( 'onplay_cart' ),
					'i18n'    => array(
						'updateError'   => __( 'No pudimos actualizar el carrito. Intenta de nuevo.', 'onplay' ),
						'removeError'   => __( 'No pudimos quitar el item. Intenta de nuevo.', 'onplay' ),
						'confirmRemove' => __( '¿Quitar este item del carrito?', 'onplay' ),
					),
				)
			);
		}

		// Endpoint del autocomplete de búsqueda.
		wp_localize_script(
			'onplay-main',
			'onplaySearch',
			array(
				'ajaxUrl' => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
				'nonce'   => wp_create_nonce( 'onplay_search' ),
				'i18n'    => array(
					'noResults'   => __( 'Sin resultados locales', 'onplay' ),
					'fromLabel'   => __( 'Desde', 'onplay' ),
					// M-OP-buscador.
					'opNoResults' => __( 'No encontramos cartas en One Piece con', 'onplay' ),
					'opSearchAll' => __( 'Buscar en todo el sitio', 'onplay' ),
					'opAltArt'    => __( 'Alt Art', 'onplay' ),
				),
			)
		);

		// Endpoint de filtros del listado (Módulo 6).
		wp_localize_script(
			'onplay-main',
			'onplayFilters',
			array(
				'ajaxUrl' => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
				'nonce'   => wp_create_nonce( 'onplay_filter' ),
			)
		);
	}
);

add_action(
	'wp_head',
	function () {
		$fonts   = 'https://fonts.googleapis.com/css2?family=Bebas+Neue&family=IBM+Plex+Sans:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&family=Playfair+Display:ital,wght@1,700&display=swap';
		$keyrune = 'https://cdn.jsdelivr.net/npm/keyrune@latest/css/keyrune.min.css';
		$mana    = 'https://cdn.jsdelivr.net/npm/mana-font@latest/css/mana.min.css';

		echo "<link rel=\"preconnect\" href=\"https://fonts.googleapis.com\">\n";
		echo "<link rel=\"preconnect\" href=\"https://fonts.gstatic.com\" crossorigin>\n";
		echo "<link rel=\"preconnect\" href=\"https://cdn.jsdelivr.net\" crossorigin>\n";

		// Carga no-bloqueante: preload→onload swap a stylesheet, con noscript de respaldo.
		// Baja TTFB de render. `display=swap` ya evita FOIT.
		printf(
			'<link rel="preload" as="style" href="%1$s" onload="this.rel=\'stylesheet\'">' . "\n"
			. '<noscript><link rel="stylesheet" href="%1$s"></noscript>' . "\n",
			esc_url( $fonts )
		);
		printf(
			'<link rel="preload" as="style" href="%1$s" onload="this.rel=\'stylesheet\'">' . "\n"
			. '<noscript><link rel="stylesheet" href="%1$s"></noscript>' . "\n",
			esc_url( $keyrune )
		);
		printf(
			'<link rel="preload" as="style" href="%1$s" onload="this.rel=\'stylesheet\'">' . "\n"
			. '<noscript><link rel="stylesheet" href="%1$s"></noscript>' . "\n",
			esc_url( $mana )
		);

		// Preload de la imagen principal del PDP (LCP candidate en ficha).
		if ( function_exists( 'is_product' ) && is_product() ) {
			$pid = get_queried_object_id();
			if ( $pid && has_post_thumbnail( $pid ) ) {
				$src = wp_get_attachment_image_src( get_post_thumbnail_id( $pid ), 'full' );
				if ( is_array( $src ) && ! empty( $src[0] ) ) {
					printf( '<link rel="preload" as="image" href="%s" fetchpriority="high">' . "\n", esc_url( $src[0] ) );
				}
			}
		}
	},
	1
);

/**
 * Defer del main.js (hoy está en footer, pero defer permite al parser seguir
 * ejecutando y respetar orden de ejecución si más scripts se suman después).
 */
add_filter(
	'script_loader_tag',
	function ( $tag, $handle ) {
		if ( 'onplay-main' !== $handle ) {
			return $tag;
		}
		if ( false !== strpos( $tag, ' defer' ) ) {
			return $tag;
		}
		return str_replace( ' src=', ' defer src=', $tag );
	},
	10,
	2
);
