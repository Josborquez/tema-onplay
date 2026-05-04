<?php
/**
 * M-OP-filtros — body_class + assets condicionales.
 *
 * Aproximación A: el SCSS y el JS del módulo viven dentro del bundle principal
 * (`main.scss` + `main.js`), no se encolan archivos separados. Aquí solo
 * agregamos `onplay-op-archive` al body para que el CSS pueda hacer scope
 * (`.onplay-op-archive .filter-group ...`) y evitar tocar el panel Magic.
 *
 * @package Onplay
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'body_class',
	function ( $classes ) {
		if ( function_exists( 'onplay_op_is_archive' ) && onplay_op_is_archive() ) {
			$classes[] = 'onplay-op-archive';
		}
		return $classes;
	}
);
