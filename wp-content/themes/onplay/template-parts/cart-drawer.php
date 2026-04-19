<?php
/**
 * Cart drawer — versión mínima para Módulo 4.
 * Operaciones full (sumar/restar/eliminar) se implementan en Módulo 7.
 *
 * @package Onplay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'WC' ) ) {
	return;
}
?>
<aside
	id="onplay-cart-drawer"
	class="cart-drawer"
	role="dialog"
	aria-modal="true"
	aria-labelledby="onplay-cart-drawer-title"
	aria-hidden="true"
	hidden
>
	<div class="cart-drawer__backdrop" data-onplay-cart-close></div>

	<div class="cart-drawer__panel">

		<header class="cart-drawer__header">
			<h2 id="onplay-cart-drawer-title" class="cart-drawer__title">
				<?php esc_html_e( 'Tu carrito', 'onplay' ); ?>
			</h2>
			<button
				type="button"
				class="cart-drawer__close"
				data-onplay-cart-close
				aria-label="<?php esc_attr_e( 'Cerrar carrito', 'onplay' ); ?>"
			>
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
					<path d="M18 6 6 18"/><path d="m6 6 12 12"/>
				</svg>
			</button>
		</header>

		<?php
		// Cada helper devuelve su propio wrapper con data-onplay-cart-body / -footer:
		// son targets directos de los fragments WC.
		echo onplay_render_cart_drawer_body();   // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo onplay_render_cart_drawer_footer(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>

	</div>
</aside>
