<?php
/**
 * Template — single post (blog). No está en el MVP pero WP lo busca como fallback.
 *
 * @package Onplay
 */

get_header();
?>

<main id="primary" class="static-page static-page--single screen-enter" role="main">

	<?php while ( have_posts() ) : the_post(); ?>

		<article <?php post_class( 'static-page__article' ); ?>>

			<header class="static-page__hero">
				<div class="container">
					<nav class="static-page__breadcrumbs mono" aria-label="<?php esc_attr_e( 'Ruta', 'onplay' ); ?>">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'onplay' ); ?></a>
						<span aria-hidden="true">/</span>
						<span><?php esc_html_e( 'Artículo', 'onplay' ); ?></span>
					</nav>

					<?php
					$cats      = get_the_category();
					$first_cat = $cats && is_array( $cats ) ? $cats[0] : null;
					if ( $first_cat ) :
						?>
						<span class="static-page__kicker mono"><?php echo esc_html( $first_cat->name ); ?></span>
					<?php endif; ?>

					<?php the_title( '<h1 class="static-page__title d-xl">', '</h1>' ); ?>

					<p class="static-page__meta mono">
						<?php
						printf(
							/* translators: 1: autor, 2: fecha */
							esc_html__( 'Por %1$s · %2$s', 'onplay' ),
							esc_html( get_the_author() ),
							esc_html( get_the_date() )
						);
						?>
					</p>
				</div>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<div class="container static-page__thumb">
					<?php the_post_thumbnail( 'large', array( 'loading' => 'lazy' ) ); ?>
				</div>
			<?php endif; ?>

			<div class="container static-page__body">
				<div class="prose">
					<?php the_content(); ?>
				</div>

				<?php
				$tag_list = get_the_tag_list( '<ul class="static-page__tags mono"><li>', '</li><li>', '</li></ul>' );
				if ( $tag_list ) {
					echo $tag_list; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				?>
			</div>

			<?php if ( comments_open() || get_comments_number() ) : ?>
				<div class="container static-page__comments">
					<?php comments_template(); ?>
				</div>
			<?php endif; ?>

		</article>

	<?php endwhile; ?>

</main>

<?php
get_footer();
