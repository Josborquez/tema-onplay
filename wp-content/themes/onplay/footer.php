<?php
/**
 * Site footer.
 *
 * @package Onplay
 */

?>

<footer class="site-footer" role="contentinfo">
	<div class="site-footer__inner container">

		<div class="site-footer__grid">

			<div class="site-footer__brand">
				<a class="site-footer__brand-lockup" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
					<img src="<?php echo esc_url( ONPLAY_THEME_URI . '/assets/img/logo.png' ); ?>" alt="Onplay Games" />
					<div class="site-footer__brand-meta">
						<div class="site-footer__brand-kicker"><?php esc_html_e( 'Singles', 'onplay' ); ?></div>
						<div class="site-footer__brand-title">ONPLAY<span class="is-accent">.</span>CL</div>
					</div>
				</a>
				<p><?php esc_html_e( 'La tienda especializada en singles de TCG en Chile. Operada por Onplay Games desde Santiago Centro.', 'onplay' ); ?></p>
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
					<li><a href="<?php echo esc_url( home_url( '/tienda/' ) ); ?>"><?php esc_html_e( 'Magic: The Gathering', 'onplay' ); ?></a></li>
					<li><?php esc_html_e( 'One Piece · Pronto', 'onplay' ); ?></li>
					<li><?php esc_html_e( 'Pokémon TCG · Pronto', 'onplay' ); ?></li>
					<li><?php esc_html_e( 'Riftbound · Pronto', 'onplay' ); ?></li>
				</ul>
			</div>

			<div class="site-footer__col">
				<div class="site-footer__col-title"><?php esc_html_e( 'Ayuda', 'onplay' ); ?></div>
				<ul>
					<li><a href="#"><?php esc_html_e( 'Condiciones de carta', 'onplay' ); ?></a></li>
					<li><a href="#"><?php esc_html_e( 'Guía de compra', 'onplay' ); ?></a></li>
					<li><a href="#"><?php esc_html_e( 'Despachos', 'onplay' ); ?></a></li>
					<li><a href="#"><?php esc_html_e( 'Devoluciones', 'onplay' ); ?></a></li>
					<li><a href="#"><?php esc_html_e( 'Preguntas frecuentes', 'onplay' ); ?></a></li>
				</ul>
			</div>

			<div class="site-footer__col">
				<div class="site-footer__col-title"><?php esc_html_e( 'Empresa', 'onplay' ); ?></div>
				<ul>
					<li><a href="#"><?php esc_html_e( 'Sobre Onplay', 'onplay' ); ?></a></li>
					<li><a href="#"><?php esc_html_e( 'Tienda física', 'onplay' ); ?></a></li>
					<li><a href="#"><?php esc_html_e( 'Vende tus cartas', 'onplay' ); ?></a></li>
					<li><a href="#"><?php esc_html_e( 'Mayoristas', 'onplay' ); ?></a></li>
					<li><a href="#"><?php esc_html_e( 'Torneos', 'onplay' ); ?></a></li>
				</ul>
			</div>

			<div class="site-footer__col">
				<div class="site-footer__col-title"><?php esc_html_e( 'Contacto', 'onplay' ); ?></div>
				<ul>
					<li><?php esc_html_e( 'Merced 832, Galería Casa Colorada', 'onplay' ); ?></li>
					<li><?php esc_html_e( 'Santiago Centro', 'onplay' ); ?></li>
					<li><a href="mailto:contacto@onplay.cl">contacto@onplay.cl</a></li>
					<li>+56 9 XXXX XXXX</li>
					<li><?php esc_html_e( 'Lun-Sáb 11:00 – 20:00', 'onplay' ); ?></li>
				</ul>
			</div>

		</div>

		<div class="site-footer__bottom line-top">
			<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> ONPLAY.CL · <?php esc_html_e( 'OPERADO POR ONPLAY GAMES SPA', 'onplay' ); ?></span>
			<span><?php esc_html_e( 'MAGIC: THE GATHERING ES MARCA REGISTRADA DE WIZARDS OF THE COAST', 'onplay' ); ?></span>
		</div>

	</div>
</footer>

<?php get_template_part( 'template-parts/cart-drawer' ); ?>

<?php wp_footer(); ?>
</body>
</html>
