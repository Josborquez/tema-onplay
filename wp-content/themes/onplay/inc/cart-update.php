<?php
/**
 * Cart update endpoint (Módulo 7).
 *
 * Endpoint propio para operaciones de carrito desde el drawer y la página /carrito/.
 * Se prefirió este endpoint sobre los wc-ajax nativos (update_cart, remove_from_cart)
 * para:
 *   1. Tener un único contrato consistente (action + cart_item_key + qty) en lugar
 *      de payloads heterogéneos de WC.
 *   2. Responder SIEMPRE los tres fragments del tema (badge, drawer body, drawer
 *      footer) además del payload plano del carrito, para que el JS aplique el
 *      mismo reemplazo de nodos que hace tras `added_to_cart`.
 *   3. Localizar mensajes de error al español del sitio en vez de los strings WC.
 *
 * Contrato:
 *   POST admin-ajax.php?action=onplay_cart_update
 *     nonce          : wp_create_nonce('onplay_cart')
 *     cart_action    : increase | decrease | set | remove
 *     cart_item_key  : clave del item en WC()->cart->get_cart()
 *     qty            : entero >= 0 (solo para 'set'; ignorado en otros)
 *
 * Respuesta success (200):
 *   {
 *     success: true,
 *     data: {
 *       fragments: {
 *         "[data-onplay-cart-count]":  "<span…></span>",
 *         "[data-onplay-cart-body]":   "<div…></div>",
 *         "[data-onplay-cart-footer]": "<div…></div>",
 *       },
 *       cart: {
 *         item_count: 3,
 *         subtotal:   "$12.500",
 *         items: { "<key>": { qty: 2, subtotal: "$3.000" }, … }
 *       }
 *     }
 *   }
 *
 * Respuesta error (4xx):
 *   { success: false, data: { message: "…" } }
 *
 * @package Onplay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle cart update request.
 *
 * Registrado tanto en `wp_ajax_*` como en `wp_ajax_nopriv_*` porque los
 * visitantes anónimos también operan su carrito.
 */
function onplay_cart_update_handler() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error(
			array( 'message' => __( 'Carrito no disponible.', 'onplay' ) ),
			500
		);
	}

	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'onplay_cart' ) ) {
		wp_send_json_error(
			array( 'message' => __( 'Sesión expirada. Recarga la página.', 'onplay' ) ),
			403
		);
	}

	$action = isset( $_POST['cart_action'] ) ? sanitize_key( wp_unslash( $_POST['cart_action'] ) ) : '';
	$key    = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) ) : '';

	if ( '' === $key ) {
		wp_send_json_error(
			array( 'message' => __( 'Item no especificado.', 'onplay' ) ),
			400
		);
	}

	$cart = WC()->cart;
	$item = $cart->get_cart_item( $key );
	if ( empty( $item ) ) {
		wp_send_json_error(
			array( 'message' => __( 'Este item ya no está en el carrito.', 'onplay' ) ),
			404
		);
	}

	$current_qty = isset( $item['quantity'] ) ? (int) $item['quantity'] : 0;

	switch ( $action ) {
		case 'increase':
			$new_qty = $current_qty + 1;
			$result  = onplay_cart_set_quantity_guarded( $cart, $key, $item, $new_qty );
			break;

		case 'decrease':
			$new_qty = max( 0, $current_qty - 1 );
			$result  = onplay_cart_set_quantity_guarded( $cart, $key, $item, $new_qty );
			break;

		case 'set':
			$raw_qty = isset( $_POST['qty'] ) ? (int) $_POST['qty'] : -1;
			if ( $raw_qty < 0 ) {
				wp_send_json_error(
					array( 'message' => __( 'Cantidad inválida.', 'onplay' ) ),
					400
				);
			}
			$result = onplay_cart_set_quantity_guarded( $cart, $key, $item, $raw_qty );
			break;

		case 'remove':
			$removed = $cart->remove_cart_item( $key );
			$result  = $removed
				? array( 'ok' => true )
				: array(
					'ok'      => false,
					'message' => __( 'No pudimos quitar el item.', 'onplay' ),
				);
			break;

		default:
			wp_send_json_error(
				array( 'message' => __( 'Acción desconocida.', 'onplay' ) ),
				400
			);
	}

	if ( empty( $result['ok'] ) ) {
		wp_send_json_error(
			array( 'message' => ! empty( $result['message'] ) ? $result['message'] : __( 'Error al actualizar.', 'onplay' ) ),
			400
		);
	}

	$cart->calculate_totals();

	wp_send_json_success(
		array(
			'fragments' => onplay_cart_fragments( array() ),
			'cart'      => onplay_cart_payload(),
		)
	);
}
add_action( 'wp_ajax_onplay_cart_update', 'onplay_cart_update_handler' );
add_action( 'wp_ajax_nopriv_onplay_cart_update', 'onplay_cart_update_handler' );

/**
 * Set quantity with stock guard.
 *
 * WC()->cart->set_quantity() NO valida stock por sí solo; acepta cualquier
 * cantidad y luego deja que checkout reviente. Acá capamos a get_stock_quantity()
 * si el producto maneja stock, antes de delegar al core.
 *
 * @param WC_Cart $cart
 * @param string  $key
 * @param array   $item
 * @param int     $new_qty
 * @return array{ok:bool,message?:string}
 */
function onplay_cart_set_quantity_guarded( $cart, $key, $item, $new_qty ) {
	$product = isset( $item['data'] ) ? $item['data'] : null;
	if ( ! $product instanceof WC_Product ) {
		return array(
			'ok'      => false,
			'message' => __( 'Producto no encontrado.', 'onplay' ),
		);
	}

	if ( $new_qty > 0 && $product->managing_stock() ) {
		$stock = (int) $product->get_stock_quantity();
		if ( $stock > 0 && $new_qty > $stock ) {
			$new_qty = $stock;
		}
	}

	$set = $cart->set_quantity( $key, $new_qty, true );
	if ( false === $set ) {
		return array(
			'ok'      => false,
			'message' => __( 'No pudimos actualizar la cantidad.', 'onplay' ),
		);
	}
	return array( 'ok' => true );
}

/**
 * Build the flat cart payload used by JS to update per-row UI (qty + subtotal).
 *
 * Los fragments reemplazan el drawer entero, pero la página /carrito/ actualiza
 * filas puntuales sin re-renderizar toda la lista; para eso alcanza con este
 * payload plano.
 *
 * @return array
 */
function onplay_cart_payload() {
	$cart     = WC()->cart;
	$items    = array();
	$raw_cart = $cart->get_cart();

	foreach ( $raw_cart as $key => $item ) {
		$product = isset( $item['data'] ) ? $item['data'] : null;
		if ( ! $product instanceof WC_Product ) {
			continue;
		}
		$qty = isset( $item['quantity'] ) ? (int) $item['quantity'] : 0;
		$sub = (float) $product->get_price() * $qty;

		$items[ $key ] = array(
			'qty'          => $qty,
			'subtotal'     => onplay_format_clp( $sub ),
			'subtotal_raw' => (int) round( $sub ),
		);
	}

	return array(
		'item_count'   => (int) $cart->get_cart_contents_count(),
		'subtotal'     => onplay_format_clp( (float) $cart->get_subtotal() ),
		'subtotal_raw' => (int) round( (float) $cart->get_subtotal() ),
		'items'        => $items,
	);
}
