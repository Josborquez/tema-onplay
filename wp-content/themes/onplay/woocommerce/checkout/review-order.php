<?php
/**
 * Review order — override del tema (Módulo 8).
 *
 * Renderiza el resumen sticky del checkout: items, subtotal, shipping, cupones,
 * total + gateways de pago + botón "Realizar pedido".
 *
 * Gateways los renderiza WC según los plugins configurados (Webpay, MP, etc.).
 *
 * @package Onplay
 */

defined( 'ABSPATH' ) || exit;

$cart = WC()->cart;
?>

<div class="checkout-review">

	<div class="checkout-review__items">
		<ul class="checkout-review__list">
			<?php
			do_action( 'woocommerce_review_order_before_cart_contents' );

			foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
				$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

				if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 || ! apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
					continue;
				}

				$qty       = (int) $cart_item['quantity'];
				$thumb_id  = (int) $_product->get_image_id();
				$thumb     = $thumb_id ? wp_get_attachment_image( $thumb_id, array( 48, 67 ), false, array( 'class' => 'checkout-review__img' ) ) : '';
				$title_raw = (string) $_product->get_name();
				$title     = trim( preg_replace( '/\s*\(Foil\)\s*/i', '', $title_raw ) );
				$parts     = onplay_parse_sku( (string) $_product->get_sku() );
				$is_foil   = onplay_is_foil( $_product->get_id() );
				$line_sub  = (float) $_product->get_price() * $qty;

				$variant_bits = array();
				if ( $parts['condition'] ) {
					$variant_bits[] = $parts['condition'];
				}
				if ( $parts['language'] ) {
					$variant_bits[] = $parts['language'];
				}
				if ( $is_foil ) {
					$variant_bits[] = __( 'Foil', 'onplay' );
				}
				?>
				<li class="checkout-review__row">
					<div class="checkout-review__thumb">
						<span class="checkout-review__qty mono"><?php echo (int) $qty; ?></span>
						<?php echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div class="checkout-review__info">
						<div class="checkout-review__title"><?php echo esc_html( $title ); ?></div>
						<?php if ( ! empty( $variant_bits ) ) : ?>
							<div class="checkout-review__variant mono"><?php echo esc_html( implode( ' · ', $variant_bits ) ); ?></div>
						<?php endif; ?>
					</div>
					<div class="checkout-review__sub mono">
						<?php echo esc_html( onplay_format_clp( $line_sub ) ); ?>
					</div>
				</li>
				<?php
			}

			do_action( 'woocommerce_review_order_after_cart_contents' );
			?>
		</ul>
	</div>

	<div class="checkout-review__totals">
		<div class="checkout-review__row checkout-review__row--total">
			<span><?php esc_html_e( 'Subtotal', 'onplay' ); ?></span>
			<span class="mono"><?php wc_cart_totals_subtotal_html(); ?></span>
		</div>

		<?php foreach ( $cart->get_coupons() as $code => $coupon ) : ?>
			<div class="checkout-review__row checkout-review__row--discount">
				<span><?php wc_cart_totals_coupon_label( $coupon ); ?></span>
				<span class="mono"><?php wc_cart_totals_coupon_html( $coupon ); ?></span>
			</div>
		<?php endforeach; ?>

		<?php if ( $cart->needs_shipping() && $cart->show_shipping() ) : ?>
			<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>
			<div class="checkout-review__shipping">
				<?php wc_cart_totals_shipping_html(); ?>
			</div>
			<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>
		<?php endif; ?>

		<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<div class="checkout-review__row">
				<span><?php echo esc_html( $fee->name ); ?></span>
				<span class="mono"><?php wc_cart_totals_fee_html( $fee ); ?></span>
			</div>
		<?php endforeach; ?>

		<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
			<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
				<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
					<div class="checkout-review__row">
						<span><?php echo esc_html( $tax->label ); ?></span>
						<span class="mono"><?php echo wp_kses_post( $tax->formatted_amount ); ?></span>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="checkout-review__row">
					<span><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></span>
					<span class="mono"><?php wc_cart_totals_taxes_total_html(); ?></span>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

		<div class="checkout-review__row checkout-review__row--grand">
			<span><?php esc_html_e( 'Total', 'onplay' ); ?></span>
			<span class="mono"><?php wc_cart_totals_order_total_html(); ?></span>
		</div>

		<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>
	</div>

	<div class="checkout-review__payment">
		<?php do_action( 'woocommerce_review_order_before_payment' ); ?>

		<div id="payment" class="woocommerce-checkout-payment">
			<?php if ( $cart->needs_payment() ) : ?>
				<h3 class="checkout-review__payment-title"><?php esc_html_e( 'Método de pago', 'onplay' ); ?></h3>
				<ul class="wc_payment_methods payment_methods methods">
					<?php
					$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
					if ( ! empty( $available_gateways ) ) {
						foreach ( $available_gateways as $gateway ) {
							wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
						}
					} else {
						echo '<li class="woocommerce-notice woocommerce-notice--info woocommerce-info">';
						echo esc_html( apply_filters( 'woocommerce_no_available_payment_methods_message', WC()->customer->get_billing_country() ? __( 'Lamentablemente no hay métodos de pago disponibles para tu país.', 'onplay' ) : __( 'Ingresa tu dirección para ver métodos de pago.', 'onplay' ) ) );
						echo '</li>';
					}
					?>
				</ul>
			<?php endif; ?>

			<div class="form-row place-order">
				<noscript>
					<?php esc_html_e( 'Ya que tu navegador no soporta JavaScript, o está desactivado, asegurate de hacer click en el botón Actualizar total antes de realizar el pedido.', 'onplay' ); ?>
					<br/>
					<button type="submit" class="button alt<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="woocommerce_checkout_update_totals" value="<?php esc_attr_e( 'Actualizar total', 'onplay' ); ?>"><?php esc_html_e( 'Actualizar total', 'onplay' ); ?></button>
				</noscript>

				<?php wc_get_template( 'checkout/terms.php' ); ?>

				<?php do_action( 'woocommerce_review_order_before_submit' ); ?>

				<?php echo apply_filters( 'woocommerce_order_button_html', '<button type="submit" class="btn btn-primary btn-block checkout-review__submit" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . esc_html( $order_button_text ) . '</button>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

				<?php do_action( 'woocommerce_review_order_after_submit' ); ?>

				<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
			</div>
		</div>

		<?php do_action( 'woocommerce_review_order_after_payment' ); ?>
	</div>

</div>
