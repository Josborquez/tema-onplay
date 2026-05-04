<?php
/**
 * Plugin Name: Onplay — wptautop() shim (hotfix wc-customer-wallet)
 * Description: El plugin wc-customer-wallet tiene un typo en línea 325 (wptautop en lugar de wpautop). PHP 8 fatalea por "undefined function" → 500 en update_order_review. Este shim define wptautop como alias de wpautop. TEMPORAL — la solución correcta es desactivar wc-customer-wallet (ver CLAUDE.md §2).
 * Version:     0.1.0
 * Author:      Onplay
 *
 * Cómo usar:
 *   1. Subir a /wp-content/mu-plugins/onplay-wptautop-shim.php
 *   2. El 500 desaparece de inmediato (los mu-plugins se cargan antes que
 *      los plugins regulares, así que el alias está disponible cuando
 *      wc-customer-wallet ejecuta línea 325).
 *   3. Borrar este archivo cuando se desactive wc-customer-wallet
 *      (que es lo que conviene hacer — ese plugin no recibe mantenimiento).
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wptautop' ) ) {
	/**
	 * Alias del wpautop() nativo de WP. Existe solo porque
	 * wc-customer-wallet/wc-customer-wallet.php:325 llama wptautop()
	 * (typo no corregido). wpautop() vive en wp-includes/formatting.php.
	 *
	 * @param string $pee
	 * @param bool   $br
	 * @return string
	 */
	function wptautop( $pee, $br = true ) {
		return function_exists( 'wpautop' ) ? wpautop( $pee, $br ) : (string) $pee;
	}
}
