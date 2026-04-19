<?php
/**
 * Bridge meta `_onplay_*` and Scryfall data to TCG taxonomies.
 *
 * @package Onplay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sync tcg_* taxonomies from enriched meta for a single product.
 *
 * @param int $product_id
 * @return void
 */
function onplay_sync_taxonomies_from_meta( $product_id ) {
	$product_id = (int) $product_id;
	if ( $product_id <= 0 || 'product' !== get_post_type( $product_id ) ) {
		return;
	}

	// Colors.
	$colors_json = get_post_meta( $product_id, '_onplay_colors', true );
	$colors      = $colors_json ? json_decode( $colors_json, true ) : array();
	$colors      = is_array( $colors ) ? $colors : array();

	$map   = array(
		'W' => 'White',
		'U' => 'Blue',
		'B' => 'Black',
		'R' => 'Red',
		'G' => 'Green',
	);
	$terms = array();
	if ( empty( $colors ) ) {
		$terms[] = 'Colorless';
	} else {
		foreach ( $colors as $c ) {
			if ( isset( $map[ $c ] ) ) {
				$terms[] = $map[ $c ];
			}
		}
		if ( count( $colors ) >= 2 ) {
			$terms[] = 'Multicolor';
		}
	}
	wp_set_object_terms( $product_id, $terms, 'tcg_color', false );

	// Rarity.
	$rarity = get_post_meta( $product_id, '_onplay_rarity', true );
	if ( ! $rarity ) {
		$rarity = get_post_meta( $product_id, '_rarity', true );
	}
	if ( $rarity ) {
		wp_set_object_terms(
			$product_id,
			array( ucfirst( strtolower( (string) $rarity ) ) ),
			'tcg_rarity',
			false
		);
	}

	// Type (parse supertypes/types from left side of type_line, before em-dash).
	$type_line = (string) get_post_meta( $product_id, '_onplay_type_line', true );
	if ( $type_line ) {
		$parts = preg_split( '/\s—\s|\s-\s/u', $type_line, 2 );
		$left  = $parts ? trim( $parts[0] ) : $type_line;
		// Doble cara: "Instant // Sorcery" → tomar ambos.
		$segments  = preg_split( '/\s*\/\/\s*/', $left );
		$supertype = array( 'Basic', 'Legendary', 'Snow', 'World', 'Ongoing', 'Tribal', 'Elite', 'Host' );
		$types     = array();
		foreach ( $segments as $seg ) {
			foreach ( preg_split( '/\s+/', trim( $seg ) ) as $w ) {
				$w = trim( $w );
				if ( '' === $w || in_array( $w, $supertype, true ) ) {
					continue;
				}
				$types[] = $w;
			}
		}
		$types = array_values( array_unique( $types ) );
		if ( ! empty( $types ) ) {
			wp_set_object_terms( $product_id, $types, 'tcg_type', false );
		}
	}

	// Format legality.
	$leg_json = get_post_meta( $product_id, '_onplay_legalities', true );
	$leg      = $leg_json ? json_decode( $leg_json, true ) : array();
	$wanted   = array( 'standard', 'pioneer', 'modern', 'legacy', 'vintage', 'commander', 'premodern', 'pauper' );
	$legal    = array();
	if ( is_array( $leg ) ) {
		foreach ( $wanted as $format ) {
			if ( isset( $leg[ $format ] ) && in_array( $leg[ $format ], array( 'legal', 'restricted' ), true ) ) {
				$legal[] = ucfirst( $format );
			}
		}
	}
	wp_set_object_terms( $product_id, $legal, 'tcg_format_legal', false );

	// Foil: prefiere meta del manager (_is_foil), fallback a inferir del título o slug.
	$is_foil_meta = get_post_meta( $product_id, '_is_foil', true );
	if ( '' !== $is_foil_meta ) {
		$is_foil = ( 'yes' === $is_foil_meta );
	} else {
		$title   = (string) get_the_title( $product_id );
		$slug    = (string) get_post_field( 'post_name', $product_id );
		$is_foil = ( false !== stripos( $title, '(Foil)' ) ) || ( false !== strpos( $slug, '-foil' ) );
	}
	wp_set_object_terms( $product_id, array( $is_foil ? 'yes' : 'no' ), 'tcg_foil', false );
}

add_action(
	'save_post_product',
	function ( $post_id, $post, $update ) {
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}
		if ( ! $post || 'publish' !== $post->post_status ) {
			return;
		}
		onplay_sync_taxonomies_from_meta( $post_id );
	},
	30,
	3
);
