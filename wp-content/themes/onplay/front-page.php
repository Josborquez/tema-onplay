<?php
/**
 * Front page — Home del tema (Módulo 9).
 *
 * Orden vertical: Hero → Trust band → Sets recientes → Recién ingresadas.
 * Cada sección vive en `template-parts/home/*.php` para que mañana se puedan
 * convertir en bloques Gutenberg sin mover lógica de render.
 *
 * @package Onplay
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="primary" class="site-main home-main screen-enter">

	<?php get_template_part( 'template-parts/home/hero' ); ?>

	<?php get_template_part( 'template-parts/home/trust-band' ); ?>

	<?php get_template_part( 'template-parts/home/featured-sets' ); ?>

	<?php get_template_part( 'template-parts/home/recent-cards' ); ?>

	<?php get_template_part( 'template-parts/home/sell-cards' ); ?>

</main>

<?php
get_footer();
