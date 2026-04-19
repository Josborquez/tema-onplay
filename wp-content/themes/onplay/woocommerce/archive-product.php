<?php
/**
 * Archive product (shop / product_cat / product_tag).
 *
 * @package Onplay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header( 'shop' );

?>
<main class="shop-main">
	<div class="container">
		<header class="shop-header">
			<div>
				<div class="shop-header__kicker">
					<?php
					if ( is_product_category() ) {
						echo esc_html__( 'Categoría', 'onplay' );
					} elseif ( is_shop() ) {
						echo esc_html__( 'Catálogo', 'onplay' );
					} else {
						echo esc_html__( 'Resultados', 'onplay' );
					}
					?>
				</div>
				<h1 class="shop-header__title">
					<?php
					if ( is_shop() ) {
						esc_html_e( 'Magic: The Gathering', 'onplay' );
					} else {
						echo esc_html( woocommerce_page_title( false ) );
					}
					?>
				</h1>
			</div>
			<?php
			global $wp_query;
			$total = isset( $wp_query->found_posts ) ? (int) $wp_query->found_posts : 0;
			?>
			<div class="shop-header__count">
				<?php echo esc_html( sprintf( _n( '%d carta', '%d cartas', $total, 'onplay' ), $total ) ); ?>
			</div>
		</header>

		<?php if ( woocommerce_product_loop() ) : ?>

			<div class="shop-grid">
				<?php
				if ( wc_get_loop_prop( 'total' ) ) {
					while ( have_posts() ) :
						the_post();
						wc_get_template_part( 'content', 'product' );
					endwhile;
				}
				?>
			</div>

			<div class="shop-pagination">
				<?php
				woocommerce_pagination();
				?>
			</div>

		<?php else : ?>

			<div class="shop-empty">
				<?php esc_html_e( 'No hay productos que coincidan con tu búsqueda.', 'onplay' ); ?>
			</div>

		<?php endif; ?>
	</div>
</main>
<?php
get_footer( 'shop' );
