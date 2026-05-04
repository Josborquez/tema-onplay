<?php
/**
 * Site footer.
 *
 * @package Onplay
 */

// Helpers definidos en inc/pages.php. Si la página no existe, renderizamos el
// item como <span> inerte para no ensuciar el UI con links rotos.
$onplay_shop_url    = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/tienda/' );
$onplay_account_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/mi-cuenta/' );

/**
 * Renderiza un <li> del footer. Si la página existe → link; si no → span.
 *
 * @param string $slug   Slug de la página.
 * @param string $label  Texto visible.
 */
if ( ! function_exists( 'onplay_footer_link' ) ) {
	function onplay_footer_link( $slug, $label ) {
		$url = function_exists( 'onplay_page_url_by_slug' ) ? onplay_page_url_by_slug( $slug ) : '';
		if ( '' === $url ) {
			printf(
				'<li><span class="is-muted">%s</span></li>',
				esc_html( $label )
			);
			return;
		}
		printf(
			'<li><a href="%s">%s</a></li>',
			esc_url( $url ),
			esc_html( $label )
		);
	}
}
?>

<footer class="site-footer" role="contentinfo">
	<div class="site-footer__inner container">

		<div class="site-footer__grid">

			<div class="site-footer__brand">
				<a class="site-footer__brand-lockup" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
					<img src="<?php echo esc_url( ONPLAY_THEME_URI . '/assets/img/logo.png' ); ?>" alt="Onplay" />
					<div class="site-footer__brand-meta">
						<div class="site-footer__brand-kicker"><?php esc_html_e( 'Singles', 'onplay' ); ?></div>
						<div class="site-footer__brand-title">ONPLAY<span class="is-accent">.</span>CL</div>
					</div>
				</a>
				<p><?php esc_html_e( 'La tienda especializada en singles de TCG en Chile. Tienda oficial WPN, The Pokémon Company y Bandai, en Santiago Centro.', 'onplay' ); ?></p>
				<div class="site-footer__socials" aria-label="<?php esc_attr_e( 'Redes sociales', 'onplay' ); ?>">
					<a class="social-icon" href="#" aria-label="Instagram">IG</a>
					<a class="social-icon" href="#" aria-label="Facebook">FB</a>
					<a class="social-icon" href="#" aria-label="X / Twitter">X</a>
					<a class="social-icon" href="#" aria-label="YouTube">YT</a>
				</div>
			</div>

			<div class="site-footer__col">
				<div class="site-footer__col-title"><?php esc_html_e( 'Juegos', 'onplay' ); ?></div>
				<ul>
					<li><a href="<?php echo esc_url( $onplay_shop_url ); ?>"><?php esc_html_e( 'Magic: The Gathering', 'onplay' ); ?></a></li>
					<?php
					$onplay_op_term = get_term_by( 'slug', 'one-piece-tcg', 'product_cat' );
					$onplay_op_url  = ( $onplay_op_term && ! is_wp_error( $onplay_op_term ) ) ? get_term_link( $onplay_op_term ) : '';
					if ( $onplay_op_url && ! is_wp_error( $onplay_op_url ) ) : ?>
						<li><a href="<?php echo esc_url( $onplay_op_url ); ?>"><?php esc_html_e( 'One Piece TCG', 'onplay' ); ?></a></li>
					<?php else : ?>
						<li><span class="is-muted"><?php esc_html_e( 'One Piece · Pronto', 'onplay' ); ?></span></li>
					<?php endif; ?>
					<li><span class="is-muted"><?php esc_html_e( 'Pokémon TCG · Pronto', 'onplay' ); ?></span></li>
					<li><span class="is-muted"><?php esc_html_e( 'Riftbound · Pronto', 'onplay' ); ?></span></li>
				</ul>
			</div>

			<div class="site-footer__col">
				<div class="site-footer__col-title"><?php esc_html_e( 'Ayuda', 'onplay' ); ?></div>
				<ul>
					<?php onplay_footer_link( 'condiciones-de-carta', __( 'Condiciones de carta', 'onplay' ) ); ?>
					<?php onplay_footer_link( 'guia-de-compra', __( 'Guía de compra', 'onplay' ) ); ?>
					<?php onplay_footer_link( 'envios-y-despachos', __( 'Envíos y despachos', 'onplay' ) ); ?>
					<?php onplay_footer_link( 'cambios-y-devoluciones', __( 'Cambios y devoluciones', 'onplay' ) ); ?>
					<?php onplay_footer_link( 'preguntas-frecuentes', __( 'Preguntas frecuentes', 'onplay' ) ); ?>
				</ul>
			</div>

			<div class="site-footer__col">
				<div class="site-footer__col-title"><?php esc_html_e( 'Empresa', 'onplay' ); ?></div>
				<ul>
					<?php onplay_footer_link( 'sobre-onplay', __( 'Sobre Onplay', 'onplay' ) ); ?>
					<?php onplay_footer_link( 'tienda-fisica', __( 'Tienda física', 'onplay' ) ); ?>
					<?php onplay_footer_link( 'vende-tus-cartas', __( 'Vende tus cartas', 'onplay' ) ); ?>
					<?php onplay_footer_link( 'mayoristas', __( 'Mayoristas', 'onplay' ) ); ?>
					<?php onplay_footer_link( 'torneos', __( 'Torneos', 'onplay' ) ); ?>
				</ul>
			</div>

			<div class="site-footer__col">
				<div class="site-footer__col-title"><?php esc_html_e( 'Legal', 'onplay' ); ?></div>
				<ul>
					<?php onplay_footer_link( 'terminos-y-condiciones', __( 'Términos y condiciones', 'onplay' ) ); ?>
					<?php onplay_footer_link( 'politica-de-privacidad', __( 'Política de privacidad', 'onplay' ) ); ?>
					<?php onplay_footer_link( 'politica-de-cookies', __( 'Política de cookies', 'onplay' ) ); ?>
					<li><a href="<?php echo esc_url( $onplay_account_url ); ?>"><?php esc_html_e( 'Mi cuenta', 'onplay' ); ?></a></li>
				</ul>
			</div>

			<div class="site-footer__col">
				<div class="site-footer__col-title"><?php esc_html_e( 'Contacto', 'onplay' ); ?></div>
				<ul>
					<li><?php esc_html_e( 'Merced 832, Local 54', 'onplay' ); ?></li>
					<li><?php esc_html_e( 'Galería Casa Colorada', 'onplay' ); ?></li>
					<li><?php esc_html_e( 'Santiago Centro, Chile', 'onplay' ); ?></li>
					<li><a href="mailto:contacto@onplay.cl">contacto@onplay.cl</a></li>
					<li><a href="https://wa.me/56966826121">+56 9 6682 6121</a></li>
					<li><?php esc_html_e( 'Lun-Sáb 11:00 – 20:00', 'onplay' ); ?></li>
				</ul>
			</div>

		</div>

		<div class="site-footer__bottom line-top">
			<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> ONPLAY.CL · <?php esc_html_e( 'Operado por Comercializadora y Distribuidora BM Limitada · RUT 77.862.085-5', 'onplay' ); ?></span>
			<span><?php esc_html_e( 'Tienda oficial WPN · The Pokémon Company · Bandai. Marcas y artes son propiedad de sus titulares.', 'onplay' ); ?></span>
		</div>

	</div>
</footer>

<?php get_template_part( 'template-parts/cart-drawer' ); ?>

<?php wp_footer(); ?>
</body>
</html>
