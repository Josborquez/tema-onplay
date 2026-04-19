<?php
/**
 * Checkout — campo RUT chileno (Módulo 8).
 *
 * Agrega un campo `billing_rut` al checkout de WooCommerce, valida por módulo 11
 * en PHP (backend) y se persiste como order meta `_billing_rut`.
 *
 * **MVP (Fase 1):** RUT es visible en admin (metabox del pedido) pero NO en el
 * email al cliente — se captura para Fase 2 (facturación SII) y se expone
 * internamente para operaciones.
 *
 * Meta key elegida: `_billing_rut` (no existe convención previa en la DB; se
 * prefirió este nombre por coherencia con el resto de los `_billing_*` de WC).
 *
 * El JS espejo vive en `assets/src/js/main.js` (módulo CheckoutRut) — valida
 * módulo 11 en `blur` del input y bloquea submit con RUT inválido.
 *
 * @package Onplay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normaliza un RUT: quita puntos, guiones y espacios, y pone el DV en mayúscula.
 *
 * @param string $rut
 * @return string Ej: "12345678K".
 */
function onplay_normalize_rut( $rut ) {
	$rut = strtoupper( (string) $rut );
	$rut = preg_replace( '/[^0-9K]/', '', $rut );
	return (string) $rut;
}

/**
 * Valida un RUT chileno por módulo 11.
 *
 * Acepta entradas con o sin puntos/guion ("12.345.678-5", "12345678-5",
 * "123456785"). Rechaza < 7 dígitos (personas) o > 9 dígitos.
 *
 * @param string $rut
 * @return bool
 */
function onplay_validate_rut( $rut ) {
	$clean = onplay_normalize_rut( $rut );
	if ( strlen( $clean ) < 8 || strlen( $clean ) > 9 ) {
		return false;
	}

	$dv     = substr( $clean, -1 );
	$digits = substr( $clean, 0, -1 );
	if ( ! ctype_digit( $digits ) ) {
		return false;
	}

	$sum   = 0;
	$mult  = 2;
	$rev   = strrev( $digits );
	$len   = strlen( $rev );
	for ( $i = 0; $i < $len; $i++ ) {
		$sum  += (int) $rev[ $i ] * $mult;
		$mult  = ( 7 === $mult ) ? 2 : $mult + 1;
	}
	$mod = 11 - ( $sum % 11 );
	if ( 11 === $mod ) {
		$expected = '0';
	} elseif ( 10 === $mod ) {
		$expected = 'K';
	} else {
		$expected = (string) $mod;
	}
	return $expected === $dv;
}

/**
 * Formatea un RUT canónicamente: "12.345.678-K".
 *
 * @param string $rut
 * @return string
 */
function onplay_format_rut( $rut ) {
	$clean = onplay_normalize_rut( $rut );
	if ( strlen( $clean ) < 2 ) {
		return $clean;
	}
	$dv     = substr( $clean, -1 );
	$digits = substr( $clean, 0, -1 );
	$digits = number_format( (float) $digits, 0, ',', '.' );
	return $digits . '-' . $dv;
}

/**
 * Inyecta el campo billing_rut y limpia campos default que no aplican al MVP.
 *
 * Decisión M8: quitamos billing_company, billing_state y todo el bloque
 * shipping_* (usamos ship-to-billing por defecto). Rollback: eliminar los
 * unset() y el filtro woocommerce_ship_to_different_address_checked.
 *
 * @param array $fields
 * @return array
 */
function onplay_checkout_fields( $fields ) {
	// Quitar campos default no usados en MVP.
	unset( $fields['billing']['billing_company'] );
	unset( $fields['billing']['billing_state'] );

	// Insertar billing_rut justo después de billing_email.
	$rut_field = array(
		'type'        => 'text',
		'label'       => __( 'RUT', 'onplay' ),
		'placeholder' => __( '12.345.678-5', 'onplay' ),
		'required'    => true,
		'class'       => array( 'form-row-wide', 'onplay-rut-field' ),
		'priority'    => 25,
		'custom_attributes' => array(
			'inputmode'    => 'text',
			'autocomplete' => 'off',
			'maxlength'    => '12',
			'data-onplay-rut' => '1',
		),
	);

	$fields['billing']['billing_rut'] = $rut_field;

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'onplay_checkout_fields' );

/**
 * Ship-to-billing por defecto: la checkbox "enviar a otra dirección" se rinde
 * pero arranca desmarcada (MVP — volumen bajo de casos que difieren).
 */
add_filter( 'woocommerce_ship_to_different_address_checked', '__return_false' );

/**
 * Valida el RUT en backend — corre sí o sí, incluso si el JS no está.
 *
 * @param array    $data
 * @param WP_Error $errors
 */
function onplay_validate_checkout_rut( $data, $errors ) {
	$rut = isset( $data['billing_rut'] ) ? (string) $data['billing_rut'] : '';
	if ( '' === trim( $rut ) ) {
		$errors->add( 'billing_rut_required', __( 'Tu RUT es obligatorio.', 'onplay' ) );
		return;
	}
	if ( ! onplay_validate_rut( $rut ) ) {
		$errors->add( 'billing_rut_invalid', __( 'El RUT ingresado no es válido.', 'onplay' ) );
	}
}
add_action( 'woocommerce_after_checkout_validation', 'onplay_validate_checkout_rut', 10, 2 );

/**
 * Persistir el RUT (normalizado + formateado) al crear la orden.
 *
 * @param WC_Order $order
 * @param array    $data
 */
function onplay_save_order_rut( $order, $data ) {
	$raw = isset( $_POST['billing_rut'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_rut'] ) ) : '';
	if ( '' === $raw ) {
		return;
	}
	$order->update_meta_data( '_billing_rut', onplay_format_rut( $raw ) );
}
add_action( 'woocommerce_checkout_create_order', 'onplay_save_order_rut', 10, 2 );

/**
 * Mostrar el RUT en el metabox de billing en admin (solo interno).
 *
 * @param WC_Order $order
 */
function onplay_admin_order_rut( $order ) {
	$rut = (string) $order->get_meta( '_billing_rut' );
	if ( '' === $rut ) {
		return;
	}
	printf(
		'<p><strong>%s:</strong> %s</p>',
		esc_html__( 'RUT', 'onplay' ),
		esc_html( $rut )
	);
}
add_action( 'woocommerce_admin_order_data_after_billing_address', 'onplay_admin_order_rut', 10, 1 );

/**
 * Exponer el RUT en el hook de emails INTERNOS (admin notification).
 * En MVP no aparece en el email al cliente — Fase 2 decide si sumarlo.
 *
 * @param array    $fields
 * @param bool     $sent_to_admin
 * @param WC_Order $order
 * @return array
 */
function onplay_email_order_meta_fields( $fields, $sent_to_admin, $order ) {
	if ( ! $sent_to_admin ) {
		return $fields;
	}
	$rut = (string) $order->get_meta( '_billing_rut' );
	if ( '' === $rut ) {
		return $fields;
	}
	$fields['billing_rut'] = array(
		'label' => __( 'RUT', 'onplay' ),
		'value' => $rut,
	);
	return $fields;
}
add_filter( 'woocommerce_email_order_meta_fields', 'onplay_email_order_meta_fields', 10, 3 );
