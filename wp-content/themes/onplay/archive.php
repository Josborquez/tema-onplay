<?php
/**
 * Template — archive genérico (category, tag, author, date).
 *
 * No está en el MVP pero WP lo busca por fallback si aparecen posts.
 *
 * @package Onplay
 */

get_header();
?>

<main id="primary" class="static-page static-page--archive screen-enter" role="main">

	<header class="static-page__hero">
		<div class="container">
			<nav class="static-page__breadcrumbs mono" aria-label="<?php esc_attr_e( 'Ruta', 'onplay' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'onplay' ); ?></a>
				<span aria-hidden="true">/</span>
				<span><?php esc_html_e( 'Archivo', 'onplay' ); ?></span>
			</nav>

			<span class="static-page__kicker mono"><?php esc_html_e( 'Colección', 'onplay' ); ?></span>
			<h1 class="static-page__title d-xl"><?php the_archive_title(); ?></h1>
			<?php
			$desc = get_the_archive_description();
			if ( $desc ) :
				?>
				<div class="static-page__lede"><?php echo wp_kses_post( $desc ); ?></div>
			<?php endif; ?>
		</div>
	</header>

	<div class="container static-page__body">

		<?php if ( have_posts() ) : ?>

			<ul class="archive-list">
				<?php while ( have_posts() ) : the_post(); ?>
					<li <?php post_class( 'archive-list__item' ); ?>>
						<a class="archive-list__link" href="<?php the_permalink(); ?>">
							<span class="archive-list__meta mono"><?php echo esc_html( get_the_date() ); ?></span>
							<h2 class="archive-list__title d-md"><?php the_title(); ?></h2>
							<?php
							$excerpt = get_the_excerpt();
							if ( $excerpt ) :
								?>
								<p class="archive-list__excerpt"><?php echo esc_html( wp_trim_words( $excerpt, 30, '…' ) ); ?></p>
							<?php endif; ?>
							<span class="archive-list__cta mono"><?php esc_html_e( 'Leer →', 'onplay' ); ?></span>
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
				<p class="static-empty__lede"><?php esc_html_e( 'Sin entradas por acá.', 'onplay' ); ?></p>
				<div class="static-empty__actions">
					<a class="btn btn-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<?php esc_html_e( 'Volver al inicio', 'onplay' ); ?>
					</a>
				</div>
			</div>

		<?php endif; ?>

	</div>
</main>

<?php
get_footer();
