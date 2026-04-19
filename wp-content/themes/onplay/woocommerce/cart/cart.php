<?php
/**
 * Cart page override — layout 2 columnas (items / resumen sticky).
 *
 * Se sobrescribe el cart.php core de WooCommerce. Re-usamos la lógica del
 * carrito (WC()->cart) pero el marcado es propio del tema para poder:
 *   - Mostrar stepper (−/input/+) en lugar de la caja de qty nativa.
 *   - Exponer data-cart-row / data-cart-item-key para el JS de onplay_cart_update.
 *   - Layout 2-col sticky en desktop.
 *
 * Coupons: usamos el form coupon nativo de WC (soporte MVP).
 *
 * @package Onplay
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' ); ?>

<div class="cart-page">
	<div class="container">

		<header class="cart-page__header">
			<h1 class="cart-page__title"><?php esc_html_e( 'Tu carrito', 'onplay' ); ?></h1>
			<div class="cart-page__count mono">
				<span data-cart-global-count><?php echo (int) WC()->cart->get_cart_contents_count(); ?></span>
				<?php esc_html_e( 'items', 'onplay' ); ?>
			</div>
		</header>

		<div class="cart-page__layout">

			<section class="cart-page__items" aria-label="<?php esc_attr_e( 'Items del carrito', 'onplay' ); ?>">

				<?php do_action( 'woocommerce_before_cart_table' ); ?>

				<ul class="cart-list">
					<?php
					do_action( 'woocommerce_before_cart_contents' );

					foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
						$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
						$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

						if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 || ! apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
							continue;
						}

						$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
						$thumb_id          = (int) $_product->get_image_id();
						$thumb             = $thumb_id ? wp_get_attachment_image( $thumb_id, array( 80, 112 ), false, array( 'class' => 'cart-row__img' ) ) : '';
						$qty               = (int) $cart_item['quantity'];
						$stock_max         = $_product->managing_stock() ? (int) $_product->get_stock_quantity() : 0;
						$line_sub          = (float) $_product->get_price() * $qty;

						$sku       = (string) $_product->get_sku();
						$parts     = onplay_parse_sku( $sku );
						$is_foil   = onplay_is_foil( $_product->get_id() );
						$title_raw = (string) $_product->get_name();
						$title     = trim( preg_replace( '/\s*\(Foil\)\s*/i', '', $title_raw ) );

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
						<li class="cart-row" data-cart-row data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>">
							<div class="cart-row__thumb">
								<?php if ( $product_permalink ) : ?>
									<a href="<?php echo esc_url( $product_permalink ); ?>">
										<?php echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									</a>
								<?php else : ?>
									<?php echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<?php endif; ?>
							</div>
							<div class="cart-row__info">
								<div class="cart-row__head">
									<div class="cart-row__title">
										<?php if ( $product_permalink ) : ?>
											<a href="<?php echo esc_url( $product_permalink ); ?>"><?php echo esc_html( $title ); ?></a>
										<?php else : ?>
											<?php echo esc_html( $title ); ?>
										<?php endif; ?>
									</div>
									<div class="cart-row__price mono">
										<?php echo esc_html( onplay_format_clp( (float) $_product->get_price() ) ); ?>
										<span class="cart-row__price-unit">/ <?php esc_html_e( 'c/u', 'onplay' ); ?></span>
									</div>
								</div>
								<?php if ( ! empty( $variant_bits ) ) : ?>
									<div class="cart-row__variant mono"><?php echo esc_html( implode( ' · ', $variant_bits ) ); ?></div>
								<?php endif; ?>
								<?php if ( $sku ) : ?>
									<div class="cart-row__sku mono">SKU <?php echo esc_html( $sku ); ?></div>
								<?php endif; ?>
							</div>
							<div class="cart-row__actions">
								<div class="cart-row__stepper" role="group" aria-label="<?php esc_attr_e( 'Cantidad', 'onplay' ); ?>">
									<button
										type="button"
										class="cart-row__stepper-btn"
										data-cart-action="decrease"
										aria-label="<?php esc_attr_e( 'Disminuir cantidad', 'onplay' ); ?>"
										<?php disabled( $qty, 1 ); ?>
									>&minus;</button>
									<input
										type="number"
										class="cart-row__stepper-input mono"
										data-cart-qty
										value="<?php echo (int) $qty; ?>"
										min="1"
										<?php if ( $stock_max > 0 ) : ?>max="<?php echo (int) $stock_max; ?>"<?php endif; ?>
										inputmode="numeric"
										aria-label="<?php esc_attr_e( 'Cantidad', 'onplay' ); ?>"
									/>
									<button
										type="button"
										class="cart-row__stepper-btn"
										data-cart-action="increase"
										aria-label="<?php esc_attr_e( 'Aumentar cantidad', 'onplay' ); ?>"
										<?php if ( $stock_max > 0 && $qty >= $stock_max ) echo 'disabled'; ?>
									>+</button>
								</div>
								<div class="cart-row__sub" data-cart-line-sub>
									<?php echo esc_html( onplay_format_clp( $line_sub ) ); ?>
								</div>
								<button
									type="button"
									class="cart-row__remove"
									data-cart-action="remove"
									aria-label="<?php esc_attr_e( 'Quitar del carrito', 'onplay' ); ?>"
								>
									<?php esc_html_e( 'Quitar', 'onplay' ); ?>
								</button>
							</div>
						</li>
					<?php } ?>
					<?php do_action( 'woocommerce_cart_contents' ); ?>
					<?php do_action( 'woocommerce_after_cart_contents' ); ?>
				</ul>

				<?php if ( wc_coupons_enabled() ) : ?>
					<div class="cart-page__coupon">
						<label for="onplay-coupon-code" class="cart-page__coupon-label">
							<?php esc_html_e( '¿Tienes un cupón?', 'onplay' ); ?>
						</label>
						<form class="cart-page__coupon-form" method="post">
							<input
								type="text"
								id="onplay-coupon-code"
								name="coupon_code"
								class="input cart-page__coupon-input"
								placeholder="<?php esc_attr_e( 'Código del cupón', 'onplay' ); ?>"
								value=""
							/>
							<button type="submit" class="btn btn-secondary" name="apply_coupon" value="1">
								<?php esc_html_e( 'Aplicar', 'onplay' ); ?>
							</button>
							<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
						</form>
					</div>
				<?php endif; ?>

				<?php do_action( 'woocommerce_after_cart_table' ); ?>

			</section>

			<aside class="cart-page__summary" aria-label="<?php esc_attr_e( 'Resumen del pedido', 'onplay' ); ?>">
				<div class="cart-summary">
					<h2 class="cart-summary__title"><?php esc_html_e( 'Resumen', 'onplay' ); ?></h2>

					<div class="cart-summary__rows">
						<div class="cart-summary__row">
							<span><?php esc_html_e( 'Subtotal', 'onplay' ); ?></span>
							<span class="cart-summary__amount" data-cart-global-sub>
								<?php echo esc_html( onplay_format_clp( (float) WC()->cart->get_subtotal() ) ); ?>
							</span>
						</div>

						<?php if ( WC()->cart->get_applied_coupons() ) : ?>
							<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
								<div class="cart-summary__row is-discount">
									<span><?php echo esc_html( sprintf( __( 'Cupón: %s', 'onplay' ), $code ) ); ?></span>
									<span class="cart-summary__amount">
										− <?php echo esc_html( onplay_format_clp( (float) WC()->cart->get_coupon_discount_amount( $code ) ) ); ?>
									</span>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>

					<div class="cart-summary__hint">
						<?php esc_html_e( 'Despacho y descuentos se calculan en el checkout.', 'onplay' ); ?>
					</div>

					<div class="cart-summary__ctas">
						<a class="btn btn-primary btn-block" href="<?php echo esc_url( wc_get_checkout_url() ); ?>">
							<?php esc_html_e( 'Ir a pagar', 'onplay' ); ?>
						</a>
						<a class="btn btn-ghost btn-block" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
							<?php esc_html_e( 'Seguir comprando', 'onplay' ); ?>
						</a>
					</div>
				</div>
			</aside>

		</div>

	</div>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
