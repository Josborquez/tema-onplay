<?php
/**
 * Home — Vende tus cartas (Editorial band).
 *
 * Sección 2-col del bundle Claude Design (`home.jsx EditorialBand`):
 * kicker mono → h2 display → párrafo → 2 CTAs | panel derecho con grid de
 * cartas rotado -4° y radial gradient carmesí.
 *
 * @package Onplay
 */

defined( 'ABSPATH' ) || exit;

$sell_url   = function_exists( 'onplay_page_url_by_slug' ) ? onplay_page_url_by_slug( 'vende-tus-cartas' ) : home_url( '/vende-tus-cartas/' );
$how_url    = $sell_url . '#como-funciona';
$panel_pool = function_exists( 'onplay_home_get_recent_cards' ) ? onplay_home_get_recent_cards( 12 ) : array();
?>
<section class="home-sell" aria-labelledby="home-sell-title">
	<div class="container">
		<div class="home-sell__grid">

			<div class="home-sell__copy">
				<span class="home-sell__kicker mono"><?php esc_html_e( 'Vende tus cartas', 'onplay' ); ?></span>
				<h2 id="home-sell-title" class="home-sell__title d-xl">
					<?php
					/* translators: el <br> es intencional para que rompa visualmente. */
					echo wp_kses(
						__( '¿Tienes cartas<br>que no usas?', 'onplay' ),
						array( 'br' => array() )
					);
					?>
				</h2>
				<p class="home-sell__lede">
					<?php esc_html_e( 'Te compramos tu colección de Magic: singles, Commander decks o binders completos. Tasación justa basada en precios actualizados y pago vía transferencia o crédito en tienda (con bonus de 20%).', 'onplay' ); ?>
				</p>
				<div class="home-sell__cta">
					<a class="btn btn-primary btn-lg" href="<?php echo esc_url( $sell_url ); ?>">
						<?php esc_html_e( 'Solicitar tasación', 'onplay' ); ?>
					</a>
					<a class="btn btn-secondary btn-lg" href="<?php echo esc_url( $how_url ); ?>">
						<?php esc_html_e( 'Cómo funciona', 'onplay' ); ?>
					</a>
				</div>
			</div>

			<div class="home-sell__panel" aria-hidden="true">
				<div class="home-sell__panel-glow"></div>
				<div class="home-sell__panel-grid">
					<?php
					if ( ! empty( $panel_pool ) ) {
						foreach ( $panel_pool as $group ) {
							$pid = isset( $group['representative_id'] ) ? (int) $group['representative_id'] : 0;
							if ( $pid <= 0 ) {
								continue;
							}
							$src = get_the_post_thumbnail_url( $pid, 'woocommerce_thumbnail' );
							if ( ! $src && function_exists( 'wc_placeholder_img_src' ) ) {
								$src = wc_placeholder_img_src( 'woocommerce_thumbnail' );
							}
							if ( $src ) {
								printf(
									'<img class="home-sell__card" src="%s" alt="" loading="lazy" decoding="async" />',
									esc_url( $src )
								);
							}
						}
					}
					?>
				</div>
			</div>

		</div>
	</div>
</section>
