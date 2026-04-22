<?php
/**
 * Security hardening for the Onplay theme.
 *
 * - Envía cabeceras de seguridad estándar (X-Frame-Options, X-Content-Type-Options,
 *   Referrer-Policy, Permissions-Policy, HSTS).
 * - Oculta la versión de WordPress (meta generator + query strings ?ver=).
 * - Desactiva XML-RPC y el link pingback.
 * - Desactiva la edición de archivos desde el admin a nivel de tema (sirve de
 *   respaldo si wp-config.php no define DISALLOW_FILE_EDIT).
 *
 * @package Onplay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Security headers.
 *
 * Se envían en send_headers (HTTP response real). CSP queda comentado: se habilita
 * después de auditar scripts inyectados por Transbank / Mercado Pago en checkout.
 */
add_action(
	'send_headers',
	function () {
		if ( is_admin() ) {
			return;
		}

		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Frame-Options: SAMEORIGIN' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
		header( 'Permissions-Policy: camera=(), microphone=(), geolocation=(), interest-cohort=()' );
		header( 'X-XSS-Protection: 0' );

		if ( is_ssl() ) {
			header( 'Strict-Transport-Security: max-age=31536000' );
		}

		// CSP — placeholder. Habilitar tras auditar gateways de pago (M8).
		// header( "Content-Security-Policy: default-src 'self'; ..." );
	}
);

/**
 * Oculta la versión de WordPress del meta generator y de los enlaces wlwmanifest / rsd.
 */
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );

add_filter( 'the_generator', '__return_empty_string' );

/**
 * Quita ?ver=X.Y.Z de los scripts y estilos para no filtrar versiones de WP ni plugins.
 * Se mantiene cuando la versión fue explícitamente seteada (sí, la nuestra — filemtime).
 */
add_filter(
	'style_loader_src',
	function ( $src ) {
		if ( is_string( $src ) && str_contains( $src, 'ver=' . get_bloginfo( 'version' ) ) ) {
			$src = remove_query_arg( 'ver', $src );
		}
		return $src;
	},
	10,
	1
);

add_filter(
	'script_loader_src',
	function ( $src ) {
		if ( is_string( $src ) && str_contains( $src, 'ver=' . get_bloginfo( 'version' ) ) ) {
			$src = remove_query_arg( 'ver', $src );
		}
		return $src;
	},
	10,
	1
);

/**
 * Desactiva XML-RPC (vector de ataques de fuerza bruta). Si se necesita para
 * Jetpack o apps móviles, remover este bloque.
 */
add_filter( 'xmlrpc_enabled', '__return_false' );

add_filter(
	'wp_headers',
	function ( $headers ) {
		unset( $headers['X-Pingback'] );
		return $headers;
	}
);

remove_action( 'wp_head', 'rest_output_link_wp_head' );

/**
 * Backup de DISALLOW_FILE_EDIT. La config oficial va en wp-config.php; esto es
 * red de seguridad para evitar edición de templates desde el admin por descuido.
 */
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}
