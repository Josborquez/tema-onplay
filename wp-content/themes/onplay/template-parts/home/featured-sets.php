<?php
/**
 * Home — Sets destacados (Módulo 9).
 *
 * Top 6 sets cuyas cartas se publicaron más recientemente. Cada card linkea a
 * `/tienda/?set=<slug>` (el listado con filtro activo). El ícono del set usa
 * el helper `onplay_render_set_icon()` con las 3 primeras letras del nombre.
 *
 * @package Onplay
 */

defined( 'ABSPATH' ) || exit;

$sets = function_exists( 'onplay_home_get_featured_sets' ) ? onplay_home_get_featured_sets( 6 ) : array();
if ( empty( $sets ) ) {
	return;
}

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/tienda/' );
?>
<section class="home-sets" aria-labelledby="home-sets-title">
	<div class="container">
		<header class="home-section-head">
			<div>
				<span class="home-section-head__kicker mono"><?php esc_html_e( 'Sets recientes', 'onplay' ); ?></span>
				<h2 id="home-sets-title" class="home-section-head__title"><?php esc_html_e( 'Últimas ediciones ingresadas', 'onplay' ); ?></h2>
			</div>
			<a class="home-section-head__link" href="<?php echo esc_url( $shop_url ); ?>">
				<?php esc_html_e( 'Ver catálogo completo', 'onplay' ); ?> &rarr;
			</a>
		</header>

		<div class="home-sets__grid">
			<?php
			foreach ( $sets as $set ) :
				$set_url  = add_query_arg( 'set', rawurlencode( $set['slug'] ), $shop_url );
				$set_code = strtoupper( substr( $set['slug'], 0, 3 ) );
				?>
				<a class="home-sets__card" href="<?php echo esc_url( $set_url ); ?>">
					<div class="home-sets__icon" aria-hidden="true">
						<?php echo onplay_render_set_icon( $set_code, 48 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div class="home-sets__info">
						<div class="home-sets__name"><?php echo esc_html( $set['name'] ); ?></div>
						<div class="home-sets__count mono">
							<?php
							printf(
								/* translators: %d: card count */
								esc_html( _n( '%d carta', '%d cartas', (int) $set['count'], 'onplay' ) ),
								(int) $set['count']
							);
							?>
						</div>
					</div>
					<span class="home-sets__arrow" aria-hidden="true">&rarr;</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
