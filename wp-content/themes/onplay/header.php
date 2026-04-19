<?php
/**
 * Site header.
 *
 * @package Onplay
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Saltar al contenido', 'onplay' ); ?></a>

<header class="site-header" role="banner">

	<div class="site-header__announce">
		<div class="container">
			<span>🏪 <?php esc_html_e( 'Retiro gratis en tienda · Merced 832, Galería Casa Colorada, Santiago', 'onplay' ); ?></span>
			<div class="site-header__announce-links">
				<span><?php esc_html_e( 'Despacho Chile vía Chilexpress', 'onplay' ); ?></span>
				<a href="<?php echo esc_url( home_url( '/ayuda/' ) ); ?>"><?php esc_html_e( 'Ayuda', 'onplay' ); ?></a>
				<a class="is-strong" href="<?php echo esc_url( home_url( '/mi-cuenta/' ) ); ?>"><?php esc_html_e( 'Mi cuenta', 'onplay' ); ?></a>
			</div>
		</div>
	</div>

	<div class="site-header__main">
		<div class="container">

			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-header__brand" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<img src="<?php echo esc_url( ONPLAY_THEME_URI . '/assets/img/logo.png' ); ?>" alt="Onplay Games" />
				<?php endif; ?>
				<div class="site-header__brand-meta">
					<div class="site-header__brand-kicker"><?php esc_html_e( 'Singles', 'onplay' ); ?></div>
					<div class="site-header__brand-title">ONPLAY<span class="is-accent">.</span>CL</div>
				</div>
			</a>

			<?php
			$onplay_shop_url  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
			$onplay_search_q  = '';
			if ( isset( $_GET['q'] ) ) {
				$onplay_search_q = sanitize_text_field( wp_unslash( $_GET['q'] ) );
			}
			?>
			<form
				role="search"
				method="get"
				class="site-header__search"
				action="<?php echo esc_url( $onplay_shop_url ); ?>"
				data-onplay-search
			>
				<svg class="site-header__search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
					<circle cx="11" cy="11" r="8"/>
					<path d="m21 21-4.3-4.3"/>
				</svg>
				<label class="screen-reader-text" for="onplay-search"><?php esc_html_e( 'Buscar cartas', 'onplay' ); ?></label>
				<input
					id="onplay-search"
					type="search"
					name="q"
					class="input"
					placeholder="<?php esc_attr_e( 'Buscar cartas, sets, ediciones…  (ej: Lightning Bolt, MH3)', 'onplay' ); ?>"
					value="<?php echo esc_attr( $onplay_search_q ); ?>"
					autocomplete="off"
					role="combobox"
					aria-expanded="false"
					aria-autocomplete="list"
					aria-controls="onplay-search-results"
					aria-haspopup="listbox"
					data-onplay-search-input
				/>
				<span class="site-header__search-hint" aria-hidden="true">⌘K</span>
				<div
					id="onplay-search-results"
					class="site-header__search-results"
					role="listbox"
					aria-label="<?php esc_attr_e( 'Resultados de búsqueda', 'onplay' ); ?>"
					data-onplay-search-results
					hidden
				></div>
			</form>

			<nav class="site-header__nav" aria-label="<?php esc_attr_e( 'Juegos', 'onplay' ); ?>">
				<?php
				$has_menu = has_nav_menu( 'primary' );
				if ( $has_menu ) {
					wp_nav_menu(
						array(
							'theme_location' => 'primary',
							'container'      => false,
							'menu_class'     => 'site-header__nav-list',
							'fallback_cb'    => '__return_empty_string',
							'depth'          => 1,
						)
					);
				} else {
					$magic_url = function_exists( 'wc_get_page_id' ) && wc_get_page_id( 'shop' ) > 0
						? get_permalink( wc_get_page_id( 'shop' ) )
						: home_url( '/tienda/' );
					?>
					<a href="<?php echo esc_url( $magic_url ); ?>" class="nav-item is-active">Magic</a>
					<span class="nav-item is-soon">One Piece <span class="badge badge-proximamente">Pronto</span></span>
					<span class="nav-item is-soon">Pokémon <span class="badge badge-proximamente">Pronto</span></span>
					<span class="nav-item is-soon">Riftbound <span class="badge badge-proximamente">Pronto</span></span>
					<?php
				}
				?>
			</nav>

			<div class="site-header__actions">
				<a class="btn btn-ghost" href="<?php echo esc_url( home_url( '/mi-cuenta/' ) ); ?>" aria-label="<?php esc_attr_e( 'Mi cuenta', 'onplay' ); ?>">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<circle cx="12" cy="8" r="4"/>
						<path d="M4 21v-2a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v2"/>
					</svg>
				</a>
				<?php
				$cart_url   = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/carrito/' );
				$cart_count = function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
				?>
				<a class="btn btn-secondary" href="<?php echo esc_url( $cart_url ); ?>" aria-label="<?php esc_attr_e( 'Carrito', 'onplay' ); ?>" data-onplay-cart-trigger>
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
						<path d="M3 6h18"/>
						<path d="M16 10a4 4 0 0 1-8 0"/>
					</svg>
					<?php esc_html_e( 'Carrito', 'onplay' ); ?>
					<?php if ( $cart_count > 0 ) : ?>
						<span class="site-header__cart-badge" data-onplay-cart-count><?php echo (int) $cart_count; ?></span>
					<?php else : ?>
						<span class="site-header__cart-badge is-hidden" data-onplay-cart-count hidden>0</span>
					<?php endif; ?>
				</a>
			</div>

		</div>
	</div>

</header>
