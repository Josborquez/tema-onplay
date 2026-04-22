<?php
/**
 * Template — Resultados de búsqueda WP (posts/pages).
 *
 * La búsqueda de productos vive en la tienda con ?q=. Este template atiende la
 * búsqueda nativa de WP (post_type = post/page) cuando el usuario cae en ?s=.
 *
 * @package Onplay
 */

get_header();

$search_query = get_search_query();
$shop_url     = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/tienda/' );
?>

<main id="primary" class="static-page static-page--search screen-enter" role="main">

	<header class="static-page__hero">
		<div class="container">
			<nav class="static-page__breadcrumbs mono" aria-label="<?php esc_attr_e( 'Ruta', 'onplay' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'onplay' ); ?></a>
				<span aria-hidden="true">/</span>
				<span><?php esc_html_e( 'Búsqueda', 'onplay' ); ?></span>
			</nav>

			<span class="static-page__kicker mono"><?php esc_html_e( 'Resultados', 'onplay' ); ?></span>
			<h1 class="static-page__title d-xl">
				<?php
				printf(
					/* translators: %s: término buscado */
					esc_html__( '“%s”', 'onplay' ),
					esc_html( $search_query )
				);
				?>
			</h1>
			<p class="static-page__lede">
				<?php
				if ( have_posts() ) {
					global $wp_query;
					printf(
						/* translators: %d: cantidad de resultados */
						esc_html( _n( '%d coincidencia en artículos y páginas.', '%d coincidencias en artículos y páginas.', (int) $wp_query->found_posts, 'onplay' ) ),
						(int) $wp_query->found_posts
					);
				} else {
					esc_html_e( 'Sin resultados en artículos ni páginas.', 'onplay' );
				}
				?>
			</p>
			<p class="static-page__lede">
				<?php
				printf(
					/* translators: %s: link a la tienda con el término como query */
					wp_kses(
						__( '¿Buscabas una carta? <a href="%s">Buscá en el catálogo</a>.', 'onplay' ),
						array( 'a' => array( 'href' => array() ) )
					),
					esc_url( add_query_arg( 'q', $search_query, $shop_url ) )
				);
				?>
			</p>
		</div>
	</header>

	<div class="container static-page__body">

		<?php if ( have_posts() ) : ?>

			<ul class="search-results">
				<?php while ( have_posts() ) : the_post(); ?>
					<li <?php post_class( 'search-results__item' ); ?>>
						<a class="search-results__link" href="<?php the_permalink(); ?>">
							<span class="search-results__type mono"><?php echo esc_html( get_post_type() ); ?></span>
							<h2 class="search-results__title d-md"><?php the_title(); ?></h2>
							<?php
							$excerpt = get_the_excerpt();
							if ( $excerpt ) :
								?>
								<p class="search-results__excerpt"><?php echo esc_html( wp_trim_words( $excerpt, 40, '…' ) ); ?></p>
							<?php endif; ?>
							<span class="search-results__cta mono"><?php esc_html_e( 'Leer →', 'onplay' ); ?></span>
						</a>
					</li>
				<?php endwhile; ?>
			</ul>

			<nav class="static-page__pagination mono" aria-label="<?php esc_attr_e( 'Paginación', 'onplay' ); ?>">
				<?php
				the_posts_pagination(
					array(
						'mid_size'  => 2,
						'prev_text' => '← ' . esc_html__( 'Anterior', 'onplay' ),
						'next_text' => esc_html__( 'Siguiente', 'onplay' ) . ' →',
					)
				);
				?>
			</nav>

		<?php else : ?>

			<div class="static-empty">
				<p class="static-empty__lede"><?php esc_html_e( 'No encontramos artículos ni páginas con ese término.', 'onplay' ); ?></p>
				<?php get_search_form(); ?>
				<div class="static-empty__actions">
					<a class="btn btn-primary" href="<?php echo esc_url( add_query_arg( 'q', $search_query, $shop_url ) ); ?>">
						<?php esc_html_e( 'Buscar en el catálogo', 'onplay' ); ?>
					</a>
					<a class="btn btn-ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<?php esc_html_e( 'Volver al inicio', 'onplay' ); ?>
					</a>
				</div>
			</div>

		<?php endif; ?>

	</div>
</main>

<?php
get_footer();
