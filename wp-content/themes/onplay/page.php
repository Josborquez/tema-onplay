<?php
/**
 * Template — Página estática (WP page).
 *
 * Se usa para páginas legales y de contenido (Términos, Privacidad, Envíos,
 * Devoluciones, FAQ, Sobre, etc.). Layout narrow + prose styles.
 *
 * Excepción: páginas propias de WooCommerce (cart, checkout, my-account) se
 * renderizan sin el hero/breadcrumbs del static-page porque ya traen su propio
 * layout desde los templates de `woocommerce/`.
 *
 * @package Onplay
 */

$is_wc_page = function_exists( 'is_cart' ) && (
	is_cart()
	|| is_checkout()
	|| ( function_exists( 'is_account_page' ) && is_account_page() )
);

get_header();
?>

<?php if ( $is_wc_page ) : ?>

	<main id="primary" class="site-main" role="main">
		<?php while ( have_posts() ) : the_post(); ?>
			<?php the_content(); ?>
		<?php endwhile; ?>
	</main>

<?php else : ?>

	<main id="primary" class="static-page screen-enter" role="main">
		<?php while ( have_posts() ) : the_post(); ?>

			<article <?php post_class( 'static-page__article' ); ?>>

				<header class="static-page__hero">
					<div class="container">
						<?php
						$parent_id = wp_get_post_parent_id( get_the_ID() );
						if ( $parent_id ) :
							$parent_title = get_the_title( $parent_id );
							$parent_link  = get_permalink( $parent_id );
							?>
							<nav class="static-page__breadcrumbs mono" aria-label="<?php esc_attr_e( 'Ruta', 'onplay' ); ?>">
								<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'onplay' ); ?></a>
								<span aria-hidden="true">/</span>
								<a href="<?php echo esc_url( $parent_link ); ?>"><?php echo esc_html( $parent_title ); ?></a>
								<span aria-hidden="true">/</span>
								<span><?php the_title(); ?></span>
							</nav>
						<?php else : ?>
							<nav class="static-page__breadcrumbs mono" aria-label="<?php esc_attr_e( 'Ruta', 'onplay' ); ?>">
								<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'onplay' ); ?></a>
								<span aria-hidden="true">/</span>
								<span><?php the_title(); ?></span>
							</nav>
						<?php endif; ?>

						<span class="static-page__kicker mono"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
						<?php the_title( '<h1 class="static-page__title d-xl">', '</h1>' ); ?>

						<?php
						$subtitle = get_post_meta( get_the_ID(), '_onplay_page_subtitle', true );
						if ( $subtitle ) :
							?>
							<p class="static-page__lede"><?php echo esc_html( $subtitle ); ?></p>
						<?php endif; ?>

						<?php if ( get_the_modified_date( 'U' ) ) : ?>
							<p class="static-page__meta mono">
								<?php
								printf(
									/* translators: %s: fecha de actualización */
									esc_html__( 'Última actualización: %s', 'onplay' ),
									esc_html( get_the_modified_date( get_option( 'date_format' ) ) )
								);
								?>
							</p>
						<?php endif; ?>
					</div>
				</header>

				<div class="container static-page__body">
					<div class="prose">
						<?php the_content(); ?>
					</div>

					<?php
					wp_link_pages(
						array(
							'before'      => '<nav class="static-page__pagination mono">' . esc_html__( 'Páginas:', 'onplay' ),
							'after'       => '</nav>',
							'link_before' => '<span>',
							'link_after'  => '</span>',
						)
					);
					?>
				</div>

			</article>

		<?php endwhile; ?>
	</main>

<?php endif; ?>

<?php
get_footer();
