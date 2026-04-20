<?php
/**
 * Home — Recién ingresadas (Módulo 9).
 *
 * Carrusel horizontal con scroll-snap, botones ←/→ y cartas del pool "más recientes en
 * stock" ordenadas por valor (min_price desc). Reusa `template-parts/product-card` vía
 * globals como el archive. Los 12 grupos iniciales permiten que el scroll tenga cuerpo
 * — se cargan todos y el viewport pagina horizontalmente.
 *
 * @package Onplay
 */

defined( 'ABSPATH' ) || exit;

$groups = function_exists( 'onplay_home_get_recent_cards' ) ? onplay_home_get_recent_cards( 12 ) : array();
if ( empty( $groups ) ) {
	return;
}

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/tienda/' );
?>
<section class="home-recent" aria-labelledby="home-recent-title">
	<div class="container">
		<header class="home-section-head">
			<div>
				<span class="home-section-head__kicker mono"><?php esc_html_e( 'Nuevos en stock', 'onplay' ); ?></span>
				<h2 id="home-recent-title" class="home-section-head__title"><?php esc_html_e( 'Singles recién ingresados', 'onplay' ); ?></h2>
			</div>
			<div class="home-recent__controls">
				<button type="button" class="btn btn-secondary home-recent__arrow" data-onplay-recent-prev aria-label="<?php esc_attr_e( 'Anterior', 'onplay' ); ?>">&larr;</button>
				<button type="button" class="btn btn-secondary home-recent__arrow" data-onplay-recent-next aria-label="<?php esc_attr_e( 'Siguiente', 'onplay' ); ?>">&rarr;</button>
				<a class="btn btn-ghost home-recent__see-all" href="<?php echo esc_url( add_query_arg( 'sort', 'new', $shop_url ) ); ?>">
					<?php esc_html_e( 'Ver todos', 'onplay' ); ?> &rarr;
				</a>
			</div>
		</header>

		<div class="home-recent__scroller" data-onplay-recent-scroller>
			<?php
			foreach ( $groups as $group ) :
				$pid = (int) $group['representative_id'];
				if ( $pid <= 0 ) {
					continue;
				}
				$GLOBALS['product']           = wc_get_product( $pid );
				$GLOBALS['post']              = get_post( $pid );
				$GLOBALS['onplay_card_group'] = $group;
				setup_postdata( $GLOBALS['post'] );
				?>
				<div class="home-recent__slide">
					<?php get_template_part( 'template-parts/product-card' ); ?>
				</div>
				<?php
			endforeach;
			wp_reset_postdata();
			unset( $GLOBALS['onplay_card_group'] );
			?>
		</div>
	</div>
</section>
