<?php
/**
 * Checkout form — override del tema (Módulo 8).
 *
 * Layout 2 columnas (campos a la izquierda, resumen sticky a la derecha).
 * Secciones verticales: Contacto / Despacho / Pago.
 *
 * Las secciones de WC se renderizan via action hooks del core (sin parsear
 * manualmente los fields) para respetar los gateways y plugins instalados.
 *
 * @package Onplay
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_checkout_form', $checkout );

// Si el checkout requiere login y el usuario no está autenticado.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'Debes iniciar sesión para finalizar la compra.', 'onplay' ) ) );
	return;
}
?>

<div class="checkout-page">
	<div class="container">

		<header class="checkout-page__header">
			<h1 class="checkout-page__title"><?php esc_html_e( 'Finalizar compra', 'onplay' ); ?></h1>
			<a class="checkout-page__back" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
				&larr; <?php esc_html_e( 'Volver al carrito', 'onplay' ); ?>
			</a>
		</header>

		<form
			name="checkout"
			method="post"
			class="checkout woocommerce-checkout checkout-form"
			action="<?php echo esc_url( wc_get_checkout_url() ); ?>"
			enctype="multipart/form-data"
			aria-label="<?php esc_attr_e( 'Formulario de checkout', 'onplay' ); ?>"
		>

			<div class="checkout-layout">

				<div class="checkout-layout__main">

					<?php if ( $checkout->get_checkout_fields() ) : ?>

						<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

						<section class="checkout-section" id="onplay-section-contact">
							<header class="checkout-section__head">
								<span class="checkout-section__step mono">01</span>
								<h2 class="checkout-section__title"><?php esc_html_e( 'Contacto y facturación', 'onplay' ); ?></h2>
							</header>
							<div class="checkout-section__body">
								<div class="woocommerce-billing-fields">
									<?php do_action( 'woocommerce_before_checkout_billing_form', $checkout ); ?>

									<div class="woocommerce-billing-fields__field-wrapper">
										<?php
										$fields = $checkout->get_checkout_fields( 'billing' );
										foreach ( $fields as $key => $field ) {
											woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
										}
										?>
									</div>

									<?php do_action( 'woocommerce_after_checkout_billing_form', $checkout ); ?>
								</div>

								<?php if ( ! is_user_logged_in() && $checkout->is_registration_enabled() ) : ?>
									<div class="woocommerce-account-fields">
										<?php if ( ! $checkout->is_registration_required() ) : ?>
											<p class="form-row form-row-wide create-account">
												<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
													<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" <?php checked( ( true === $checkout->get_value( 'createaccount' ) || ( true === apply_filters( 'woocommerce_create_account_default_checked', false ) ) ), true ); ?> type="checkbox" name="createaccount" value="1" />
													<span><?php esc_html_e( 'Crear una cuenta (opcional)', 'onplay' ); ?></span>
												</label>
											</p>
										<?php endif; ?>

										<?php do_action( 'woocommerce_before_checkout_registration_form', $checkout ); ?>

										<?php if ( $checkout->get_checkout_fields( 'account' ) ) : ?>
											<div class="create-account">
												<?php foreach ( $checkout->get_checkout_fields( 'account' ) as $key => $field ) : ?>
													<?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
												<?php endforeach; ?>
											</div>
										<?php endif; ?>

										<?php do_action( 'woocommerce_after_checkout_registration_form', $checkout ); ?>
									</div>
								<?php endif; ?>
							</div>
						</section>

						<section class="checkout-section" id="onplay-section-shipping">
							<header class="checkout-section__head">
								<span class="checkout-section__step mono">02</span>
								<h2 class="checkout-section__title"><?php esc_html_e( 'Despacho', 'onplay' ); ?></h2>
							</header>
							<div class="checkout-section__body">
								<?php
								// Checkbox "enviar a dirección distinta" (arranca unchecked por filtro).
								if ( true === WC()->cart->needs_shipping_address() ) :
									?>
									<div class="woocommerce-shipping-fields">
										<h3 class="woocommerce-shipping-fields__title">
											<label for="ship-to-different-address-checkbox" class="checkbox">
												<input id="ship-to-different-address-checkbox" class="input-checkbox" <?php checked( apply_filters( 'woocommerce_ship_to_different_address_checked', 0 ), 1 ); ?> type="checkbox" name="ship_to_different_address" value="1" />
												<span><?php esc_html_e( 'Enviar a una dirección distinta', 'onplay' ); ?></span>
											</label>
										</h3>

										<div class="shipping_address">
											<?php do_action( 'woocommerce_before_checkout_shipping_form', $checkout ); ?>

											<div class="woocommerce-shipping-fields__field-wrapper">
												<?php
												$fields = $checkout->get_checkout_fields( 'shipping' );
												foreach ( $fields as $key => $field ) {
													woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
												}
												?>
											</div>

											<?php do_action( 'woocommerce_after_checkout_shipping_form', $checkout ); ?>
										</div>
									</div>
								<?php endif; ?>

								<div class="checkout-section__hint">
									<?php esc_html_e( 'Los costos y métodos de despacho se calculan según tu dirección en el resumen a la derecha.', 'onplay' ); ?>
								</div>
							</div>
						</section>

						<section class="checkout-section" id="onplay-section-notes">
							<header class="checkout-section__head">
								<span class="checkout-section__step mono">03</span>
								<h2 class="checkout-section__title"><?php esc_html_e( 'Notas del pedido', 'onplay' ); ?></h2>
							</header>
							<div class="checkout-section__body">
								<div class="woocommerce-additional-fields">
									<?php do_action( 'woocommerce_before_order_notes', $checkout ); ?>

									<?php if ( apply_filters( 'woocommerce_enable_order_notes_field', 'yes' === get_option( 'woocommerce_enable_order_comments', 'yes' ) ) ) : ?>
										<div class="woocommerce-additional-fields__field-wrapper">
											<?php
											foreach ( $checkout->get_checkout_fields( 'order' ) as $key => $field ) {
												woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
											}
											?>
										</div>
									<?php endif; ?>

									<?php do_action( 'woocommerce_after_order_notes', $checkout ); ?>
								</div>
							</div>
						</section>

						<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

					<?php endif; ?>

				</div>

				<aside class="checkout-layout__summary" aria-label="<?php esc_attr_e( 'Resumen del pedido', 'onplay' ); ?>">
					<section class="checkout-section checkout-section--summary" id="onplay-section-summary">
						<header class="checkout-section__head">
							<span class="checkout-section__step mono">04</span>
							<h2 class="checkout-section__title"><?php esc_html_e( 'Resumen y pago', 'onplay' ); ?></h2>
						</header>
						<div class="checkout-section__body">
							<h3 id="order_review_heading" class="screen-reader-text"><?php esc_html_e( 'Tu pedido', 'onplay' ); ?></h3>
							<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>
							<div id="order_review" class="woocommerce-checkout-review-order">
								<?php do_action( 'woocommerce_checkout_order_review' ); ?>
							</div>
							<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
						</div>
					</section>
				</aside>

			</div>

		</form>

	</div>
</div>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
