<?php
/**
 * Content-product — wrapper used by the WC loop. Delegates to our product-card.
 *
 * @package Onplay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

get_template_part( 'template-parts/product-card' );
