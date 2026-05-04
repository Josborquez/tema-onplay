<?php
/**
 * Archive product (shop / product_cat / product_tag).
 *
 * Módulo 6 — listado con filtros facetados.
 * Render server-side inicial coherente con el estado por defecto del sidebar
 * (in_stock=ON, foil=all, sin filtros). La JS de filters.js se hace cargo de
 * subsiguientes interacciones vía AJAX.
 *
 * @package Onplay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header( 'shop' );

// Estado inicial = defaults + filtros que vengan en la URL (para shareable).
$initial_state = onplay_filters_parse_request( $_GET );

// Si la URL trae filtros (no es solo defaults), respetarlos. Si llega a
// /tienda/ sin params, in_stock=true por default.
$initial = onplay_filters_run( $initial_state );

// Contexto de TCG para que el shop-header refleje dónde está el usuario.
// is_op = navegando dentro de One Piece (set=one-piece-tcg o descendiente).
$onplay_shop_is_op = function_exists( 'onplay_op_is_archive' ) && onplay_op_is_archive();

?>
<main class="shop-main">
	<div class="container">

		<nav class="shop-breadcrumb" aria-label="<?php esc_attr_e( 'Migas de pan', 'onplay' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'onplay' ); ?></a>
			<span class="shop-breadcrumb__sep" aria-hidden="true">/</span>
			<?php if ( $onplay_shop_is_op ) : ?>
				<span><?php esc_html_e( 'One Piece Card Game', 'onplay' ); ?></span>
				<span class="shop-breadcrumb__sep" aria-hidden="true">/</span>
				<span class="shop-breadcrumb__current"><?php esc_html_e( 'Singles', 'onplay' ); ?></span>
			<?php else : ?>
				<span><?php esc_html_e( 'Magic: The Gathering', 'onplay' ); ?></span>
				<span class="shop-breadcrumb__sep" aria-hidden="true">/</span>
				<span class="shop-breadcrumb__current"><?php esc_html_e( 'Singles', 'onplay' ); ?></span>
			<?php endif; ?>
		</nav>

		<header class="shop-header<?php echo $onplay_shop_is_op ? ' shop-header--op' : ''; ?>">
			<div class="shop-header__lead">
				<div class="shop-header__kicker">
					<?php
					if ( $onplay_shop_is_op ) {
						echo esc_html__( 'Bandai · TCG', 'onplay' );
					} elseif ( is_product_category() ) {
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
					if ( $onplay_shop_is_op ) {
						esc_html_e( 'Singles · One Piece', 'onplay' );
					} elseif ( is_shop() ) {
						esc_html_e( 'Singles · Magic: The Gathering', 'onplay' );
					} else {
						echo esc_html( wp_strip_all_tags( woocommerce_page_title( false ) ) );
					}
					?>
				</h1>
				<div class="shop-header__meta">
					<span class="mono shop-header__count" data-shop-count><?php echo (int) $initial['total_groups']; ?></span>
					<span><?php esc_html_e( 'cartas', 'onplay' ); ?></span>
				</div>
			</div>

			<div class="shop-header__tools">
				<label class="shop-sort">
					<span class="screen-reader-text"><?php esc_html_e( 'Ordenar por', 'onplay' ); ?></span>
					<select class="input" data-shop-sort>
						<option value="price-desc"<?php selected( $initial_state['sort'], 'price-desc' ); ?>><?php esc_html_e( 'Precio: mayor a menor', 'onplay' ); ?></option>
						<option value="price-asc"<?php selected( $initial_state['sort'], 'price-asc' ); ?>><?php esc_html_e( 'Precio: menor a mayor', 'onplay' ); ?></option>
						<option value="name"<?php selected( $initial_state['sort'], 'name' ); ?>><?php esc_html_e( 'Nombre A-Z', 'onplay' ); ?></option>
						<option value="new"<?php selected( $initial_state['sort'], 'new' ); ?>><?php esc_html_e( 'Recién ingresado', 'onplay' ); ?></option>
					</select>
				</label>
			</div>
		</header>

		<div class="shop-layout">
			<?php get_template_part( 'template-parts/filters-sidebar' ); ?>

			<section class="shop-results" data-shop-results>
				<div class="shop-results__chips" data-shop-chips>
					<?php echo $initial['chips_html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>

				<div class="shop-results__grid" data-shop-grid>
					<?php echo $initial['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>

				<div class="shop-results__pagination" data-shop-pagination>
					<?php echo $initial['pagination_html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>

				<div class="shop-results__loading" data-shop-loading hidden aria-live="polite">
					<span class="shop-spinner" aria-hidden="true"></span>
					<span><?php esc_html_e( 'Actualizando…', 'onplay' ); ?></span>
				</div>
			</section>
		</div>

	</div>
</main>
<?php
get_footer( 'shop' );
