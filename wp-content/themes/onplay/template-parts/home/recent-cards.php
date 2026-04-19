<?php
/**
 * Home — Recién ingresadas (Módulo 9).
 *
 * 8 cartas más nuevas en stock, agrupadas por print_key (1 card por impresión,
 * consistente con listado y autocomplete). Reusa `template-parts/product-card`
 * vía el mismo patrón de globals que el archive — los `GLOBALS` se limpian al
 * final con `wp_reset_postdata()`.
 *
 * @package Onplay
 */

defined( 'ABSPATH' ) || exit;

$groups = function_exists( 'onplay_home_get_recent_cards' ) ? onplay_home_get_recent_cards( 8 ) : array();
if ( empty( $groups ) ) {
	return;
}

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/tienda/' );
?>
<section class="home-recent" aria-labelledby="home-recent-title">
	<div class="container">
		<header class="home-section-head">
			<div>
				<span class="home-section-head__kicker mono"><?php esc_html_e( 'Recién ingresado', 'onplay' ); ?></span>
				<h2 id="home-recent-title" class="home-section-head__title"><?php esc_html_e( 'Las últimas cartas en stock', 'onplay' ); ?></h2>
			</div>
			<a class="home-section-head__link" href="<?php echo esc_url( add_query_arg( 'sort', 'new', $shop_url ) ); ?>">
				<?php esc_html_e( 'Ver todas las novedades', 'onplay' ); ?> &rarr;
			</a>
		</header>

		<div class="shop-grid home-recent__grid">
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
				get_template_part( 'template-parts/product-card' );
			endforeach;
			wp_reset_postdata();
			unset( $GLOBALS['onplay_card_group'] );
			?>
		</div>
	</div>
</section>
