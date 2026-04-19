<?php
/**
 * Home — Trust band (Módulo 9).
 *
 * 3 pills: Retiro, Despacho, Pago seguro. El pill de pago incluye marcas de
 * tarjetas/gateways como chips de texto (sin assets binarios: texto con color
 * de marca plano — evita payload adicional y es copyright-safe).
 *
 * @package Onplay
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="home-trust" aria-label="<?php esc_attr_e( 'Beneficios y formas de pago', 'onplay' ); ?>">
	<div class="container home-trust__inner">

		<div class="home-trust__pill">
			<span class="home-trust__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
					<path d="M12 22s-7-6-7-12a7 7 0 0 1 14 0c0 6-7 12-7 12Z"/>
					<circle cx="12" cy="10" r="2.5"/>
				</svg>
			</span>
			<div class="home-trust__text">
				<div class="home-trust__title"><?php esc_html_e( 'Retiro en tienda', 'onplay' ); ?></div>
				<div class="home-trust__sub"><?php esc_html_e( 'Merced 832, Local 54 · Santiago Centro', 'onplay' ); ?></div>
			</div>
		</div>

		<div class="home-trust__pill">
			<span class="home-trust__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
					<path d="M3 7h11v9H3z"/>
					<path d="M14 10h4l3 3v3h-7"/>
					<circle cx="7" cy="18" r="1.8"/>
					<circle cx="17" cy="18" r="1.8"/>
				</svg>
			</span>
			<div class="home-trust__text">
				<div class="home-trust__title"><?php esc_html_e( 'Despacho 24-48 h', 'onplay' ); ?></div>
				<div class="home-trust__sub"><?php esc_html_e( 'A todo Chile tras confirmar pago', 'onplay' ); ?></div>
			</div>
		</div>

		<div class="home-trust__pill home-trust__pill--pay">
			<span class="home-trust__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
					<rect x="3" y="6" width="18" height="12" rx="2"/>
					<path d="M3 10h18"/>
					<path d="M7 15h4"/>
				</svg>
			</span>
			<div class="home-trust__text">
				<div class="home-trust__title"><?php esc_html_e( 'Pago 100 % seguro', 'onplay' ); ?></div>
				<div class="home-trust__brands">
					<span class="home-trust__brand"><?php esc_html_e( 'Webpay', 'onplay' ); ?></span>
					<span class="home-trust__brand"><?php esc_html_e( 'Mercado Pago', 'onplay' ); ?></span>
					<span class="home-trust__brand"><?php esc_html_e( 'Visa', 'onplay' ); ?></span>
					<span class="home-trust__brand"><?php esc_html_e( 'Mastercard', 'onplay' ); ?></span>
					<span class="home-trust__brand"><?php esc_html_e( 'Amex', 'onplay' ); ?></span>
				</div>
			</div>
		</div>

	</div>
</section>
