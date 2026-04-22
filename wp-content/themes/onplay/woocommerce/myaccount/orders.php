<?php
/**
 * My Account — listado de pedidos.
 *
 * Lista de pedidos como tarjetas (en vez de la tabla nativa de WC) para que
 * sea legible en mobile sin responsive-table hacks. Empty state con CTA al
 * shop. Respeta los hooks nativos (`woocommerce_my_account_my_orders_actions`,
 * `woocommerce_before/after_account_orders*`).
 *
 * @package Onplay
 * @version WC 9.5.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_orders', $has_orders );
?>

<?php if ( $has_orders ) : ?>
	<ul class="myaccount-orders woocommerce-orders-table woocommerce-MyAccount-orders">
		<?php
		foreach ( $customer_orders->orders as $customer_order ) :
			$order       = wc_get_order( $customer_order );
			$item_count  = $order->get_item_count() - $order->get_item_count_refunded();
			$status_slug = $order->get_status();
			$actions     = wc_get_account_orders_actions( $order );
			?>
			<li class="myaccount-order woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr( $status_slug ); ?>">
				<div class="myaccount-order__head">
					<div class="myaccount-order__id">
						<span class="myaccount-order__kicker mono"><?php esc_html_e( 'Pedido', 'onplay' ); ?></span>
						<a class="myaccount-order__num" href="<?php echo esc_url( $order->get_view_order_url() ); ?>">
							#<?php echo esc_html( $order->get_order_number() ); ?>
						</a>
					</div>
					<span class="myaccount-order__status myaccount-order__status--<?php echo esc_attr( $status_slug ); ?>">
						<?php echo esc_html( wc_get_order_status_name( $status_slug ) ); ?>
					</span>
				</div>

				<div class="myaccount-order__meta">
					<div class="myaccount-order__meta-item">
						<span class="myaccount-order__meta-label mono"><?php esc_html_e( 'Fecha', 'onplay' ); ?></span>
						<time class="myaccount-order__meta-value" datetime="<?php echo esc_attr( $order->get_date_created()->date( 'c' ) ); ?>">
							<?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?>
						</time>
					</div>
					<div class="myaccount-order__meta-item">
						<span class="myaccount-order__meta-label mono"><?php esc_html_e( 'Items', 'onplay' ); ?></span>
						<span class="myaccount-order__meta-value"><?php echo esc_html( (string) $item_count ); ?></span>
					</div>
					<div class="myaccount-order__meta-item">
						<span class="myaccount-order__meta-label mono"><?php esc_html_e( 'Total', 'onplay' ); ?></span>
						<span class="myaccount-order__meta-value myaccount-order__meta-value--total">
							<?php echo wp_kses_post( $order->get_formatted_order_total() ); ?>
						</span>
					</div>
				</div>

				<?php if ( ! empty( $actions ) ) : ?>
					<div class="myaccount-order__actions">
						<?php
						foreach ( $actions as $key => $action ) {
							$aria = empty( $action['aria-label'] )
								/* translators: 1: action name, 2: order number */
								? sprintf( esc_attr__( '%1$s pedido %2$s', 'onplay' ), $action['name'], $order->get_order_number() )
								: $action['aria-label'];
							printf(
								'<a href="%1$s" class="btn btn-ghost myaccount-order__action myaccount-order__action--%2$s" aria-label="%3$s">%4$s</a>',
								esc_url( $action['url'] ),
								esc_attr( sanitize_html_class( $key ) ),
								esc_attr( $aria ),
								esc_html( $action['name'] )
							);
						}
						?>
					</div>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>

	<?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>

	<?php if ( 1 < $customer_orders->max_num_pages ) : ?>
		<nav class="myaccount-orders__pagination" aria-label="<?php esc_attr_e( 'Paginación', 'onplay' ); ?>">
			<?php if ( 1 !== $current_page ) : ?>
				<a class="btn btn-ghost" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page - 1 ) ); ?>">
					&larr; <?php esc_html_e( 'Anterior', 'onplay' ); ?>
				</a>
			<?php endif; ?>
			<?php if ( (int) $customer_orders->max_num_pages !== $current_page ) : ?>
				<a class="btn btn-ghost" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page + 1 ) ); ?>">
					<?php esc_html_e( 'Siguiente', 'onplay' ); ?> &rarr;
				</a>
			<?php endif; ?>
		</nav>
	<?php endif; ?>

<?php else : ?>

	<div class="myaccount-empty">
		<span class="myaccount-empty__kicker mono"><?php esc_html_e( 'Historial vacío', 'onplay' ); ?></span>
		<h3 class="myaccount-empty__title"><?php esc_html_e( 'Todavía no tienes pedidos', 'onplay' ); ?></h3>
		<p class="myaccount-empty__text"><?php esc_html_e( 'Cuando compres tu primera carta aparecerá aquí.', 'onplay' ); ?></p>
		<a class="btn btn-primary" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
			<?php esc_html_e( 'Explorar catálogo', 'onplay' ); ?>
		</a>
	</div>

<?php endif; ?>

<?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>
