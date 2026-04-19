<?php
/**
 * Empty cart page — estado vacío con CTA clara.
 *
 * @package Onplay
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="cart-page cart-page--empty">
	<div class="container">

		<div class="cart-empty">
			<div class="cart-empty__icon" aria-hidden="true">
				<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
					<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
					<path d="M3 6h18"/>
					<path d="M16 10a4 4 0 0 1-8 0"/>
				</svg>
			</div>
			<h1 class="cart-empty__title"><?php esc_html_e( 'Tu carrito está vacío', 'onplay' ); ?></h1>
			<p class="cart-empty__text">
				<?php esc_html_e( 'Busca tus cartas favoritas o explora los sets más recientes.', 'onplay' ); ?>
			</p>
			<div class="cart-empty__ctas">
				<a class="btn btn-primary" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
					<?php esc_html_e( 'Explorar catálogo', 'onplay' ); ?>
				</a>
			</div>
		</div>

	</div>
</div>
