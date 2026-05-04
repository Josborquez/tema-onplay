<?php
/**
 * Template 404 — página no encontrada.
 *
 * @package Onplay
 */

get_header();

$shop_url    = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/tienda/' );
$account_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/mi-cuenta/' );
?>

<main id="primary" class="static-page static-page--404 screen-enter" role="main">
	<div class="container static-page__wrap">

		<section class="static-404">
			<span class="static-404__kicker mono"><?php esc_html_e( '404 · Carta no encontrada', 'onplay' ); ?></span>
			<h1 class="static-404__title d-xxl">
				<span class="is-accent">404</span>
			</h1>
			<p class="static-404__lede">
				<?php esc_html_e( 'La carta que buscas se salió del mazo. Revisa la URL, busca por nombre o vuelve a la tienda.', 'onplay' ); ?>
			</p>

			<form
				role="search"
				method="get"
				class="static-404__search"
				action="<?php echo esc_url( $shop_url ); ?>"
			>
				<label class="screen-reader-text" for="onplay-404-search"><?php esc_html_e( 'Buscar cartas', 'onplay' ); ?></label>
				<input
					id="onplay-404-search"
					type="search"
					name="q"
					class="input static-404__search-input"
					placeholder="<?php esc_attr_e( 'Ej: Lightning Bolt, MH3, Ragavan…', 'onplay' ); ?>"
					autocomplete="off"
				/>
				<button type="submit" class="btn btn-primary"><?php esc_html_e( 'Buscar', 'onplay' ); ?></button>
			</form>

			<div class="static-404__actions">
				<a class="btn btn-secondary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php esc_html_e( 'Volver al inicio', 'onplay' ); ?>
				</a>
				<a class="btn btn-ghost" href="<?php echo esc_url( $shop_url ); ?>">
					<?php esc_html_e( 'Explorar catálogo', 'onplay' ); ?>
				</a>
				<a class="btn btn-ghost" href="<?php echo esc_url( $account_url ); ?>">
					<?php esc_html_e( 'Mi cuenta', 'onplay' ); ?>
				</a>
			</div>
		</section>

	</div>
</main>

<?php
get_footer();
