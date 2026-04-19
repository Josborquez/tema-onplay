<?php
/**
 * Front page — placeholder. La home real se implementa en el Módulo 9.
 *
 * @package Onplay
 */

get_header();
?>

<main id="primary" class="site-main">
	<section class="home-placeholder container screen-enter">
		<div>
			<div class="home-placeholder__kicker"><?php esc_html_e( 'Módulo 1 · Fundación', 'onplay' ); ?></div>
			<h1 class="home-placeholder__title">
				Home <span class="is-accent">coming soon</span>
			</h1>
			<p class="home-placeholder__desc">
				<?php esc_html_e( 'La fundación del tema está lista. La home definitiva (hero híbrido, recién ingresados, sets, trust band) se implementa en el Módulo 9.', 'onplay' ); ?>
			</p>
		</div>
	</section>
</main>

<?php
get_footer();
