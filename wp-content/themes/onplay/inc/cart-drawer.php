<?php
/**
 * Cart drawer — render helpers + WooCommerce fragments.
 * Versión mínima para Módulo 4. Operaciones full quedan para Módulo 7.
 *
 * Cada render helper devuelve UN elemento raíz que ES el target del fragment WC.
 * La plantilla los echo-ea directamente (sin wrapper extra) para que la primera
 * carga y los reemplazos AJAX produzcan exactamente el mismo DOM.
 *
 * @package Onplay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the cart count badge (used in header + as WC fragment).
 *
 * @return string
 */
function onplay_render_cart_count_badge() {
	$count = ( function_exists( 'WC' ) && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;
	if ( $count > 0 ) {
		return sprintf(
			'<span class="site-header__cart-badge" data-onplay-cart-count>%d</span>',
			$count
		);
	}
	return '<span class="site-header__cart-badge is-hidden" data-onplay-cart-count hidden>0</span>';
}

/**
 * Render the drawer body — wrapper div con la lista de items o estado vacío.
 *
 * @return string
 */
function onplay_render_cart_drawer_body() {
	$has_cart = function_exists( 'WC' ) && WC()->cart;
	$items    = $has_cart ? WC()->cart->get_cart() : array();

	ob_start();
	?>
	<div class="cart-drawer__body" data-onplay-cart-body>
		<?php if ( empty( $items ) ) : ?>
			<div class="cart-drawer__empty">
				<?php esc_html_e( 'Tu carrito está vacío.', 'onplay' ); ?>
			</div>
		<?php else : ?>
			<ul class="cart-drawer__items">
				<?php foreach ( $items as $key => $item ) :
					$product = isset( $item['data'] ) ? $item['data'] : null;
					if ( ! $product instanceof WC_Product ) {
						continue;
					}
					$qty       = isset( $item['quantity'] ) ? (int) $item['quantity'] : 0;
					$sku       = (string) $product->get_sku();
					$parts     = onplay_parse_sku( $sku );
					$is_foil   = onplay_is_foil( $product->get_id() );
					$thumb_id  = (int) $product->get_image_id();
					$thumb     = $thumb_id ? wp_get_attachment_image( $thumb_id, array( 60, 84 ), false, array( 'class' => 'cart-drawer__item-img' ) ) : '';
					$line_sub  = (float) $product->get_price() * $qty;
					$title_raw = (string) $product->get_name();
					$title     = trim( preg_replace( '/\s*\(Foil\)\s*/i', '', $title_raw ) );
					$stock_max = $product->managing_stock() ? (int) $product->get_stock_quantity() : 0;

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
					<li class="cart-drawer__item" data-cart-row data-cart-item-key="<?php echo esc_attr( $key ); ?>">
						<div class="cart-drawer__item-thumb">
							<?php
							if ( $thumb ) {
								echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
							?>
						</div>
						<div class="cart-drawer__item-meta">
							<div class="cart-drawer__item-head">
								<div class="cart-drawer__item-title"><?php echo esc_html( $title ); ?></div>
								<button
									type="button"
									class="cart-drawer__item-remove"
									data-cart-action="remove"
									aria-label="<?php esc_attr_e( 'Quitar del carrito', 'onplay' ); ?>"
								>&times;</button>
							</div>
							<?php if ( ! empty( $variant_bits ) ) : ?>
								<div class="cart-drawer__item-variant mono"><?php echo esc_html( implode( ' · ', $variant_bits ) ); ?></div>
							<?php endif; ?>
							<div class="cart-drawer__item-line">
								<div class="cart-drawer__stepper" role="group" aria-label="<?php esc_attr_e( 'Cantidad', 'onplay' ); ?>">
									<button
										type="button"
										class="cart-drawer__stepper-btn"
										data-cart-action="decrease"
										aria-label="<?php esc_attr_e( 'Disminuir cantidad', 'onplay' ); ?>"
										<?php disabled( $qty, 1 ); ?>
									>&minus;</button>
									<input
										type="number"
										class="cart-drawer__stepper-input mono"
										data-cart-qty
										value="<?php echo (int) $qty; ?>"
										min="1"
										<?php if ( $stock_max > 0 ) : ?>max="<?php echo (int) $stock_max; ?>"<?php endif; ?>
										inputmode="numeric"
										aria-label="<?php esc_attr_e( 'Cantidad', 'onplay' ); ?>"
									/>
									<button
										type="button"
										class="cart-drawer__stepper-btn"
										data-cart-action="increase"
										aria-label="<?php esc_attr_e( 'Aumentar cantidad', 'onplay' ); ?>"
										<?php if ( $stock_max > 0 && $qty >= $stock_max ) echo 'disabled'; ?>
									>+</button>
								</div>
								<span class="cart-drawer__item-sub" data-cart-line-sub><?php echo esc_html( onplay_format_clp( $line_sub ) ); ?></span>
							</div>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Render the drawer footer — wrapper div con totales + CTAs (o estado vacío).
 *
 * @return string
 */
function onplay_render_cart_drawer_footer() {
	$has_cart = function_exists( 'WC' ) && WC()->cart;
	$count    = $has_cart ? (int) WC()->cart->get_cart_contents_count() : 0;

	ob_start();
	?>
	<div class="cart-drawer__footer" data-onplay-cart-footer>
		<?php if ( $count <= 0 ) : ?>
			<div class="cart-drawer__footer-empty">
				<?php esc_html_e( 'Agrega cartas para empezar.', 'onplay' ); ?>
			</div>
		<?php else :
			$subtotal  = (float) WC()->cart->get_subtotal();
			$cart_url  = wc_get_cart_url();
			$check_url = wc_get_checkout_url();
			?>
			<div class="cart-drawer__totals">
				<div class="cart-drawer__subtotal-row">
					<span><?php esc_html_e( 'Subtotal', 'onplay' ); ?></span>
					<span class="cart-drawer__subtotal-amount"><?php echo esc_html( onplay_format_clp( $subtotal ) ); ?></span>
				</div>
				<div class="cart-drawer__hint">
					<?php esc_html_e( 'Despacho y descuentos se calculan en el checkout.', 'onplay' ); ?>
				</div>
				<div class="cart-drawer__ctas">
					<a class="btn btn-secondary" href="<?php echo esc_url( $cart_url ); ?>">
						<?php esc_html_e( 'Ver carrito', 'onplay' ); ?>
					</a>
					<a class="btn btn-primary" href="<?php echo esc_url( $check_url ); ?>">
						<?php esc_html_e( 'Ir a pagar', 'onplay' ); ?>
					</a>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Register WooCommerce add-to-cart fragments so AJAX add updates drawer + counter.
 *
 * @param array $fragments
 * @return array
 */
function onplay_cart_fragments( $fragments ) {
	$fragments['[data-onplay-cart-count]']  = onplay_render_cart_count_badge();
	$fragments['[data-onplay-cart-body]']   = onplay_render_cart_drawer_body();
	$fragments['[data-onplay-cart-footer]'] = onplay_render_cart_drawer_footer();
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'onplay_cart_fragments' );
