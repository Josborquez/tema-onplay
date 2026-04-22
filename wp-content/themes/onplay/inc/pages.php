<?php
/**
 * Páginas institucionales + helpers.
 *
 * Fuente de verdad de los slugs/títulos de las páginas legales y de empresa.
 * El footer consulta el permalink por slug; el comando `wp onplay:seed-pages`
 * crea las páginas con contenido placeholder (el dueño pega el contenido real
 * generado por los prompts 05-xx-PROMPT-*.md).
 *
 * @package Onplay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Catálogo canónico de páginas institucionales.
 *
 * @return array<string, array{title:string, section:string, subtitle:string, placeholder:string}>
 */
function onplay_pages_catalog() {
	return array(
		// ----- Legal -----
		'terminos-y-condiciones'  => array(
			'title'       => 'Términos y Condiciones',
			'section'     => 'legal',
			'subtitle'    => 'Reglas de uso del sitio y del servicio de compra.',
			'placeholder' => 'Contenido pendiente. El contenido real se genera con el prompt 05-PROMPT-TERMINOS-CONDICIONES.md (v2.1) y se pega desde el editor.',
		),
		'politica-de-privacidad'  => array(
			'title'       => 'Política de Privacidad',
			'section'     => 'legal',
			'subtitle'    => 'Qué datos personales usamos y cómo los protegemos.',
			'placeholder' => 'Contenido pendiente. El contenido real se genera con el prompt 06-PROMPT-POLITICA-PRIVACIDAD.md.',
		),
		'politica-de-cookies'     => array(
			'title'       => 'Política de Cookies',
			'section'     => 'legal',
			'subtitle'    => 'Qué cookies usamos, para qué y cómo desactivarlas.',
			'placeholder' => 'Contenido pendiente. El contenido real se genera con el prompt 07-PROMPT-POLITICA-COOKIES.md.',
		),

		// ----- Soporte / compra -----
		'envios-y-despachos'      => array(
			'title'       => 'Envíos y Despachos',
			'section'     => 'ayuda',
			'subtitle'    => 'Retiro en tienda y despachos a todo Chile.',
			'placeholder' => 'Contenido pendiente. Describe retiro en Merced 832 Local 54 y despacho Chilexpress.',
		),
		'cambios-y-devoluciones'  => array(
			'title'       => 'Cambios y Devoluciones',
			'section'     => 'ayuda',
			'subtitle'    => 'Política de cambio y devolución bajo Ley 19.496.',
			'placeholder' => 'Contenido pendiente. Describe plazos y condiciones conforme a la Ley del Consumidor.',
		),
		'preguntas-frecuentes'    => array(
			'title'       => 'Preguntas Frecuentes',
			'section'     => 'ayuda',
			'subtitle'    => 'Las dudas más comunes, respondidas.',
			'placeholder' => 'Contenido pendiente. Bloque de acordeón con FAQ.',
		),
		'condiciones-de-carta'    => array(
			'title'       => 'Condiciones de Carta',
			'section'     => 'ayuda',
			'subtitle'    => 'Escala de estado: Near Mint, Lightly Played, Moderately Played, Heavily Played, Damaged.',
			'placeholder' => 'Contenido pendiente. Tabla de condiciones con ejemplos visuales.',
		),
		'guia-de-compra'          => array(
			'title'       => 'Guía de Compra',
			'section'     => 'ayuda',
			'subtitle'    => 'Cómo comprar paso a paso en Onplay.cl.',
			'placeholder' => 'Contenido pendiente. Walk-through del flujo: buscar → agregar → checkout.',
		),
		'ayuda'                   => array(
			'title'       => 'Ayuda',
			'section'     => 'ayuda',
			'subtitle'    => 'Centro de ayuda y contacto.',
			'placeholder' => 'Contenido pendiente. Hub con links a guía, FAQ, envíos, devoluciones y contacto.',
		),

		// ----- Empresa -----
		'sobre-onplay'            => array(
			'title'       => 'Sobre Onplay',
			'section'     => 'empresa',
			'subtitle'    => 'Quiénes somos y por qué hacemos lo que hacemos.',
			'placeholder' => 'Contenido pendiente. Historia, misión, equipo.',
		),
		'tienda-fisica'           => array(
			'title'       => 'Tienda Física',
			'section'     => 'empresa',
			'subtitle'    => 'Merced 832, Local 54 — Galería Casa Colorada, Santiago Centro.',
			'placeholder' => 'Contenido pendiente. Horario, mapa, teléfono, cómo llegar.',
		),
		'vende-tus-cartas'        => array(
			'title'       => 'Vende tus Cartas',
			'section'     => 'empresa',
			'subtitle'    => 'Compramos colecciones y singles individuales.',
			'placeholder' => 'Contenido pendiente. Proceso de buy-list, formulario de contacto.',
		),
		'mayoristas'              => array(
			'title'       => 'Mayoristas',
			'section'     => 'empresa',
			'subtitle'    => 'Ventas mayoristas para tiendas aliadas.',
			'placeholder' => 'Contenido pendiente. Condiciones comerciales, contacto B2B.',
		),
		'torneos'                 => array(
			'title'       => 'Torneos',
			'section'     => 'empresa',
			'subtitle'    => 'Calendario de torneos y eventos WPN.',
			'placeholder' => 'Contenido pendiente. Vinculado al programa oficial Wizards Play Network.',
		),
	);
}

/**
 * Devuelve el permalink de una página por slug, o '' si no existe / no está publicada.
 *
 * @param string $slug Slug de la página.
 * @return string URL absoluta o cadena vacía.
 */
function onplay_page_url_by_slug( $slug ) {
	$slug = sanitize_title( (string) $slug );
	if ( '' === $slug ) {
		return '';
	}

	// Cache por request — el footer llama esto 13 veces por hit.
	static $cache = array();
	if ( isset( $cache[ $slug ] ) ) {
		return $cache[ $slug ];
	}

	$page = get_page_by_path( $slug, OBJECT, 'page' );
	if ( $page && 'publish' === $page->post_status ) {
		$cache[ $slug ] = (string) get_permalink( $page );
	} else {
		$cache[ $slug ] = '';
	}
	return $cache[ $slug ];
}

/**
 * Contenido Gutenberg placeholder para una página stub.
 *
 * @param array $entry Entrada de onplay_pages_catalog().
 * @return string HTML con bloques Gutenberg.
 */
function onplay_pages_render_placeholder( $entry ) {
	$title       = isset( $entry['title'] ) ? (string) $entry['title'] : '';
	$subtitle    = isset( $entry['subtitle'] ) ? (string) $entry['subtitle'] : '';
	$placeholder = isset( $entry['placeholder'] ) ? (string) $entry['placeholder'] : '';

	$lines   = array();
	$lines[] = '<!-- wp:paragraph -->';
	$lines[] = '<p><strong>' . esc_html( $subtitle ) . '</strong></p>';
	$lines[] = '<!-- /wp:paragraph -->';
	$lines[] = '';
	$lines[] = '<!-- wp:paragraph -->';
	$lines[] = '<p>' . esc_html( $placeholder ) . '</p>';
	$lines[] = '<!-- /wp:paragraph -->';
	$lines[] = '';
	$lines[] = '<!-- wp:heading -->';
	$lines[] = '<h2>' . esc_html( $title ) . '</h2>';
	$lines[] = '<!-- /wp:heading -->';
	$lines[] = '';
	$lines[] = '<!-- wp:paragraph -->';
	$lines[] = '<p>Este contenido es un placeholder generado automáticamente por <code>wp onplay:seed-pages</code>. Reemplázalo con el texto definitivo antes del lanzamiento.</p>';
	$lines[] = '<!-- /wp:paragraph -->';

	return implode( "\n", $lines );
}
