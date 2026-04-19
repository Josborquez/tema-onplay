<?php
/**
 * Scryfall enrichment.
 *
 * El manager no guarda mana_cost, oracle_text, tipos, colores ni legalidades.
 * Este módulo fetchea y cachea esos datos como meta `_onplay_*`, y programa
 * la enriquecimiento async (wp_schedule_single_event) cuando entran productos
 * nuevos.
 *
 * @package Onplay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'ONPLAY_SCRYFALL_API' ) ) {
	define( 'ONPLAY_SCRYFALL_API', 'https://api.scryfall.com' );
}
if ( ! defined( 'ONPLAY_SCRYFALL_UA' ) ) {
	define( 'ONPLAY_SCRYFALL_UA', 'Onplay.cl/0.1 (+https://onplay.cl)' );
}

/**
 * Parse set_code + collector_number from the SKU prefix.
 *
 * @param string $sku e.g. "THB-262-NM-EN".
 * @return array|null
 */
function onplay_parse_sku_prefix( $sku ) {
	if ( ! is_string( $sku ) || '' === $sku ) {
		return null;
	}
	if ( ! preg_match( '/^([A-Za-z0-9]+)-([A-Za-z0-9]+)/', $sku, $m ) ) {
		return null;
	}
	return array(
		'set_code'         => strtolower( $m[1] ),
		'collector_number' => strtolower( $m[2] ),
	);
}

/**
 * Fetch and cache Scryfall data for a product.
 *
 * @param int  $product_id
 * @param bool $force
 * @return array{ok:bool,message:string,data?:array|null}
 */
function onplay_enrich_from_scryfall( $product_id, $force = false ) {
	$product_id = (int) $product_id;
	if ( $product_id <= 0 ) {
		return array(
			'ok'      => false,
			'message' => 'Invalid product ID',
		);
	}

	if ( ! $force ) {
		$cached_at = get_post_meta( $product_id, '_onplay_scryfall_cached_at', true );
		if ( $cached_at ) {
			return array(
				'ok'      => true,
				'message' => 'Already cached',
			);
		}
	}

	$sku   = get_post_meta( $product_id, '_sku', true );
	$parts = onplay_parse_sku_prefix( $sku );
	if ( ! $parts ) {
		return array(
			'ok'      => false,
			'message' => 'Cannot parse SKU: ' . (string) $sku,
		);
	}

	$url = ONPLAY_SCRYFALL_API . '/cards/' . rawurlencode( $parts['set_code'] ) . '/' . rawurlencode( $parts['collector_number'] );

	$response = wp_remote_get(
		$url,
		array(
			'timeout'     => 10,
			'redirection' => 3,
			'headers'     => array(
				'Accept'     => 'application/json',
				'User-Agent' => ONPLAY_SCRYFALL_UA,
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		return array(
			'ok'      => false,
			'message' => 'HTTP error: ' . $response->get_error_message(),
		);
	}

	$code = (int) wp_remote_retrieve_response_code( $response );
	if ( 200 !== $code ) {
		return array(
			'ok'      => false,
			'message' => 'Scryfall HTTP ' . $code . ' for ' . $url,
		);
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $data ) ) {
		return array(
			'ok'      => false,
			'message' => 'Invalid JSON',
		);
	}

	$mana_cost   = isset( $data['mana_cost'] ) ? (string) $data['mana_cost'] : '';
	$type_line   = isset( $data['type_line'] ) ? (string) $data['type_line'] : '';
	$oracle_text = isset( $data['oracle_text'] ) ? (string) $data['oracle_text'] : '';
	$colors      = ( isset( $data['colors'] ) && is_array( $data['colors'] ) ) ? $data['colors'] : array();

	// Doble-cara: unir caras si el root viene vacío.
	if ( ! empty( $data['card_faces'] ) && is_array( $data['card_faces'] ) ) {
		if ( '' === $mana_cost ) {
			$parts_cost = array();
			foreach ( $data['card_faces'] as $face ) {
				if ( ! empty( $face['mana_cost'] ) ) {
					$parts_cost[] = $face['mana_cost'];
				}
			}
			$mana_cost = implode( ' // ', $parts_cost );
		}
		if ( '' === $oracle_text ) {
			$parts_text = array();
			foreach ( $data['card_faces'] as $face ) {
				if ( ! empty( $face['oracle_text'] ) ) {
					$parts_text[] = $face['oracle_text'];
				}
			}
			$oracle_text = implode( "\n\n//\n\n", $parts_text );
		}
		if ( empty( $colors ) ) {
			$merged = array();
			foreach ( $data['card_faces'] as $face ) {
				if ( ! empty( $face['colors'] ) && is_array( $face['colors'] ) ) {
					$merged = array_merge( $merged, $face['colors'] );
				}
			}
			$colors = array_values( array_unique( $merged ) );
		}
	}

	$meta = array(
		'_onplay_scryfall_id'        => isset( $data['id'] ) ? (string) $data['id'] : '',
		'_onplay_mana_cost'          => $mana_cost,
		'_onplay_cmc'                => isset( $data['cmc'] ) ? (float) $data['cmc'] : 0,
		'_onplay_type_line'          => $type_line,
		'_onplay_oracle_text'        => $oracle_text,
		'_onplay_colors'             => wp_json_encode( $colors ),
		'_onplay_color_identity'     => wp_json_encode( isset( $data['color_identity'] ) && is_array( $data['color_identity'] ) ? $data['color_identity'] : array() ),
		'_onplay_legalities'         => wp_json_encode( isset( $data['legalities'] ) && is_array( $data['legalities'] ) ? $data['legalities'] : array() ),
		'_onplay_rarity'             => isset( $data['rarity'] ) ? (string) $data['rarity'] : '',
		'_onplay_artist'             => isset( $data['artist'] ) ? (string) $data['artist'] : '',
		'_onplay_scryfall_uri'       => isset( $data['scryfall_uri'] ) ? (string) $data['scryfall_uri'] : '',
		'_onplay_set_code'           => isset( $data['set'] ) ? (string) $data['set'] : $parts['set_code'],
		'_onplay_collector_number'   => isset( $data['collector_number'] ) ? (string) $data['collector_number'] : $parts['collector_number'],
		'_onplay_scryfall_cached_at' => time(),
	);

	foreach ( $meta as $key => $value ) {
		update_post_meta( $product_id, $key, $value );
	}

	if ( function_exists( 'onplay_sync_taxonomies_from_meta' ) ) {
		onplay_sync_taxonomies_from_meta( $product_id );
	}

	return array(
		'ok'      => true,
		'message' => 'Enriched',
		'data'    => $data,
	);
}

// Programar enriquecimiento async al crear/actualizar producto sin caché.
add_action(
	'save_post_product',
	function ( $post_id, $post, $update ) {
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}
		if ( ! $post || 'publish' !== $post->post_status ) {
			return;
		}
		if ( get_post_meta( $post_id, '_onplay_scryfall_cached_at', true ) ) {
			return;
		}
		if ( ! wp_next_scheduled( 'onplay_enrich_product_event', array( $post_id ) ) ) {
			wp_schedule_single_event( time() + 60, 'onplay_enrich_product_event', array( $post_id ) );
		}
	},
	20,
	3
);

add_action(
	'onplay_enrich_product_event',
	function ( $product_id ) {
		onplay_enrich_from_scryfall( (int) $product_id, false );
	}
);
