<?php
/**
 * Thankyou page — override del tema (Módulo 8).
 *
 * Pantalla post-pago. Se ejecuta el action `woocommerce_thankyou` para que los
 * gateways (Webpay, MP) puedan inyectar su recibo/redirect si corresponde.
 *
 * @package Onplay
 *
 * @var WC_Order|false $order
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="thankyou-page">
	<div class="container">

		<?php if ( $order ) : ?>

			<?php if ( $order->has_status( 'failed' ) ) : ?>

				<div class="thankyou-page__badge thankyou-page__badge--fail" aria-hidden="true">
					<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<circle cx="12" cy="12" r="10"/>
						<line x1="15" y1="9" x2="9" y2="15"/>
						<line x1="9" y1="9" x2="15" y2="15"/>
					</svg>
				</div>
				<h1 class="thankyou-page__title"><?php esc_html_e( 'Pago no completado', 'onplay' ); ?></h1>
				<p class="thankyou-page__text">
					<?php esc_html_e( 'Lamentablemente tu pedido no pudo procesarse. Por favor intenta nuevamente o contáctanos si el problema persiste.', 'onplay' ); ?>
				</p>

				<div class="thankyou-page__ctas">
					<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="btn btn-primary">
						<?php esc_html_e( 'Reintentar pago', 'onplay' ); ?>
					</a>
					<?php if ( is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email() ) : ?>
						<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="btn btn-ghost">
							<?php esc_html_e( 'Ir a mi cuenta', 'onplay' ); ?>
						</a>
					<?php endif; ?>
				</div>

			<?php else : ?>

				<div class="thankyou-page__badge" aria-hidden="true">
					<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
						<path d="M20 6 9 17l-5-5"/>
					</svg>
				</div>

				<h1 class="thankyou-page__title"><?php esc_html_e( '¡Gracias por tu pedido!', 'onplay' ); ?></h1>
				<p class="thankyou-page__text">
					<?php
					printf(
						/* translators: %s: email address */
						esc_html__( 'Te enviamos la confirmación a %s. Te avisaremos apenas tu pedido esté listo para despacho o retiro.', 'onplay' ),
						'<strong>' . esc_html( $order->get_billing_email() ) . '</strong>'
					);
					?>
				</p>

				<div class="thankyou-page__order">
					<ul class="thankyou-page__overview">
						<li>
							<span><?php esc_html_e( 'Pedido', 'onplay' ); ?></span>
							<strong>#<?php echo esc_html( $order->get_order_number() ); ?></strong>
						</li>
						<li>
							<span><?php esc_html_e( 'Fecha', 'onplay' ); ?></span>
							<strong><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></strong>
						</li>
						<li>
							<span><?php esc_html_e( 'Total', 'onplay' ); ?></span>
							<strong><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong>
						</li>
						<?php if ( $order->get_payment_method_title() ) : ?>
							<li>
								<span><?php esc_html_e( 'Pago', 'onplay' ); ?></span>
								<strong><?php echo esc_html( $order->get_payment_method_title() ); ?></strong>
							</li>
						<?php endif; ?>
					</ul>

					<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
					<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>
				</div>

				<div class="thankyou-page__ctas">
					<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn btn-primary">
						<?php esc_html_e( 'Seguir comprando', 'onplay' ); ?>
					</a>
					<?php if ( is_user_logged_in() ) : ?>
						<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="btn btn-ghost">
							<?php esc_html_e( 'Ver mis pedidos', 'onplay' ); ?>
						</a>
					<?php endif; ?>
				</div>

			<?php endif; ?>

		<?php else : ?>

			<h1 class="thankyou-page__title"><?php esc_html_e( '¡Gracias!', 'onplay' ); ?></h1>
			<p class="thankyou-page__text">
				<?php esc_html_e( 'Tu pedido fue recibido.', 'onplay' ); ?>
			</p>
			<div class="thankyou-page__ctas">
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn btn-primary">
					<?php esc_html_e( 'Seguir comprando', 'onplay' ); ?>
				</a>
			</div>

		<?php endif; ?>

	</div>
</div>
