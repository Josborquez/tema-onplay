<?php
/**
 * Home — Hero con búsqueda (Módulo 9).
 *
 * Sección minimalista: kicker + título + subtítulo + input de búsqueda que
 * submite a `/tienda/?q=<term>`. El autocomplete vive en el input del header
 * (ver assets/src/js/main.js → módulo Search) — para no duplicar el listener
 * sobre `querySelector` único, el hero hace submit-to-shop sin dropdown.
 *
 * @package Onplay
 */

defined( 'ABSPATH' ) || exit;

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/tienda/' );
?>
<section class="home-hero screen-enter" aria-labelledby="home-hero-title">
	<div class="container home-hero__inner">
		<span class="home-hero__kicker mono"><?php esc_html_e( 'Magic: The Gathering · Singles', 'onplay' ); ?></span>
		<h1 id="home-hero-title" class="home-hero__title">
			<?php
			printf(
				/* translators: %s: accent word */
				esc_html__( 'Encuentra tu %s', 'onplay' ),
				'<span class="is-accent">' . esc_html__( 'próxima carta', 'onplay' ) . '</span>'
			);
			?>
		</h1>
		<p class="home-hero__sub">
			<?php esc_html_e( 'Catálogo completo de singles de MTG. Despacho 24-48h y retiro en Merced 832 Local 54, Santiago Centro.', 'onplay' ); ?>
		</p>

		<form class="home-hero__search" role="search" action="<?php echo esc_url( $shop_url ); ?>" method="get">
			<label for="home-hero-q" class="screen-reader-text"><?php esc_html_e( 'Buscar cartas', 'onplay' ); ?></label>
			<input
				type="search"
				id="home-hero-q"
				name="q"
				class="home-hero__input"
				placeholder="<?php esc_attr_e( 'Busca por nombre de carta, set o colección…', 'onplay' ); ?>"
				autocomplete="off"
			/>
			<button type="submit" class="btn btn-primary home-hero__btn">
				<?php esc_html_e( 'Buscar', 'onplay' ); ?>
			</button>
		</form>
	</div>
</section>
