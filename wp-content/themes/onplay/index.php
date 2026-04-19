<?php
/**
 * Fallback template.
 *
 * @package Onplay
 */

get_header();
?>

<main id="primary" class="site-main container">
	<?php if ( have_posts() ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class(); ?>>
				<header class="entry-header">
					<?php the_title( '<h1 class="entry-title d-xl">', '</h1>' ); ?>
				</header>
				<div class="entry-content">
					<?php the_content(); ?>
				</div>
			</article>
			<?php
		endwhile;
		?>
	<?php else : ?>
		<section class="no-results">
			<h1 class="d-xl"><?php esc_html_e( 'Nada por acá.', 'onplay' ); ?></h1>
			<p class="muted"><?php esc_html_e( 'La página que buscas no existe o fue movida.', 'onplay' ); ?></p>
		</section>
	<?php endif; ?>
</main>

<?php
get_footer();
