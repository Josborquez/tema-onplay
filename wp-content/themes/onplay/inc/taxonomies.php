<?php
/**
 * Custom TCG taxonomies for faceted filters.
 *
 * @package Onplay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'init',
	function () {
		$base = array(
			'public'            => true,
			'publicly_queryable' => true,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'hierarchical'      => false,
			'rewrite'           => false,
			'query_var'         => true,
		);

		register_taxonomy(
			'tcg_color',
			array( 'product' ),
			array_merge(
				$base,
				array(
					'labels' => array(
						'name'          => __( 'Colores', 'onplay' ),
						'singular_name' => __( 'Color', 'onplay' ),
					),
				)
			)
		);

		register_taxonomy(
			'tcg_rarity',
			array( 'product' ),
			array_merge(
				$base,
				array(
					'labels' => array(
						'name'          => __( 'Rarezas', 'onplay' ),
						'singular_name' => __( 'Rareza', 'onplay' ),
					),
				)
			)
		);

		register_taxonomy(
			'tcg_type',
			array( 'product' ),
			array_merge(
				$base,
				array(
					'labels' => array(
						'name'          => __( 'Tipos', 'onplay' ),
						'singular_name' => __( 'Tipo', 'onplay' ),
					),
				)
			)
		);

		register_taxonomy(
			'tcg_format_legal',
			array( 'product' ),
			array_merge(
				$base,
				array(
					'labels' => array(
						'name'          => __( 'Formatos legales', 'onplay' ),
						'singular_name' => __( 'Formato legal', 'onplay' ),
					),
				)
			)
		);

		register_taxonomy(
			'tcg_foil',
			array( 'product' ),
			array_merge(
				$base,
				array(
					'labels' => array(
						'name'          => __( 'Foil', 'onplay' ),
						'singular_name' => __( 'Foil', 'onplay' ),
					),
				)
			)
		);
	}
);
