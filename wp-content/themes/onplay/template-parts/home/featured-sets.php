<?php
/**
 * Home — Sets destacados (Módulo 9, rework).
 *
 * Cards "editoriales" con imagen de una carta representativa del set como
 * fondo (rotada 8deg, opacity 0.45), gradiente lineal carbón→transparente
 * al 90°, diamante SVG con el código del set en color deterministico por
 * hash, y pie con count + "Explorar →".
 *
 * Source de datos: `onplay_home_get_featured_sets()` (incluye `set_code`,
 * `year`, `thumb`). Fallback al diamante monograma cuando el SKU no da
 * set_code.
 *
 * @package Onplay
 */

defined( 'ABSPATH' ) || exit;

$sets = function_exists( 'onplay_home_get_featured_sets' ) ? onplay_home_get_featured_sets( 6 ) : array();
if ( empty( $sets ) ) {
	return;
}

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/tienda/' );

/**
 * Paleta fija del diseño; el color por set es determinista vía hash del set_code.
 * Alineado con los tonos del design file (carmesí variantes + gold + green + purple).
 */
$palette = array( '#C84E3C', '#6A8F4A', '#7B5EA8', '#E94B7B', '#C06B3A', '#D6A843' );
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
				$set_code = $set['set_code'] ? $set['set_code'] : strtoupper( substr( $set['slug'], 0, 3 ) );
				$color    = $palette[ abs( crc32( $set_code ) ) % count( $palette ) ];
				$year     = $set['year'];
				$kicker   = $set_code . ( $year ? ' · ' . $year : '' );
				?>
				<a class="home-sets__card" href="<?php echo esc_url( $set_url ); ?>">
					<?php if ( $set['thumb'] ) : ?>
						<img class="home-sets__card-bg" src="<?php echo esc_url( $set['thumb'] ); ?>" alt="" loading="lazy" />
					<?php endif; ?>
					<div class="home-sets__card-gradient" aria-hidden="true"></div>

					<div class="home-sets__card-inner">
						<div class="home-sets__card-top">
							<svg class="home-sets__card-diamond" width="24" height="24" viewBox="0 0 16 16" aria-hidden="true">
								<path d="M8 1 L15 8 L8 15 L1 8 Z" fill="<?php echo esc_attr( $color ); ?>" stroke="rgba(0,0,0,0.5)" stroke-width="0.5"/>
								<text x="8" y="10.5" text-anchor="middle" font-size="6" font-family="var(--body)" font-weight="700" fill="white"><?php echo esc_html( $set_code ); ?></text>
							</svg>
							<div class="home-sets__card-kicker mono"><?php echo esc_html( $kicker ); ?></div>
						</div>
						<div class="home-sets__card-bottom">
							<div class="home-sets__card-name d-lg"><?php echo esc_html( $set['name'] ); ?></div>
							<div class="home-sets__card-meta">
								<span class="home-sets__card-count mono">
									<?php
									printf(
										/* translators: %s: formatted number of singles */
										esc_html__( '%s singles', 'onplay' ),
										esc_html( number_format_i18n( (int) $set['count'] ) )
									);
									?>
								</span>
								<span class="home-sets__card-cta"><?php esc_html_e( 'Explorar', 'onplay' ); ?> &rarr;</span>
							</div>
						</div>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
