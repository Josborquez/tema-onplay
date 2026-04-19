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
		}

		// Endpoint del autocomplete de búsqueda.
		wp_localize_script(
			'onplay-main',
			'onplaySearch',
			array(
				'ajaxUrl' => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
				'nonce'   => wp_create_nonce( 'onplay_search' ),
				'i18n'    => array(
					'noResults' => __( 'Sin resultados locales', 'onplay' ),
					'fromLabel' => __( 'Desde', 'onplay' ),
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
		echo "<link rel=\"preconnect\" href=\"https://fonts.googleapis.com\">\n";
		echo "<link rel=\"preconnect\" href=\"https://fonts.gstatic.com\" crossorigin>\n";
		echo "<link rel=\"stylesheet\" href=\"https://fonts.googleapis.com/css2?family=Bebas+Neue&family=IBM+Plex+Sans:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap\">\n";
	},
	1
);
