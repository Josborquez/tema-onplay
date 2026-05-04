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

			// M-OP-buscador: si el usuario está navegando OP (archive descendiente
			// o single product OP), restringimos el buscador a ese universo.
			$onplay_op_ctx = function_exists( 'onplay_op_in_op_context' ) && onplay_op_in_op_context();
			if ( $onplay_op_ctx ) {
				// Submit form va al shop con set=one-piece-tcg para mantener el
				// contexto al aterrizar en el listado (archive-product.php parsea
				// el param `q` para búsqueda libre).
				$onplay_search_action = add_query_arg( 'set', 'one-piece-tcg', $onplay_shop_url );
			} else {
				$onplay_search_action = $onplay_shop_url;
			}
			?>
			<form
				role="search"
				method="get"
				class="site-header__search<?php echo $onplay_op_ctx ? ' site-header__search--op' : ''; ?>"
				action="<?php echo esc_url( $onplay_search_action ); ?>"
				data-onplay-search
				data-tcg="<?php echo $onplay_op_ctx ? 'op' : 'global'; ?>"
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
				<?php if ( $onplay_op_ctx ) : ?>
					<input type="hidden" name="set" value="one-piece-tcg" />
					<input type="hidden" name="tcg" value="op" />
					<span
						class="site-header__search-context"
						data-onplay-search-context
						aria-live="polite"
					><?php esc_html_e( 'Buscando en One Piece', 'onplay' ); ?></span>
				<?php endif; ?>
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

					// Determinar TCG activo: si onplay_op_in_op_context() es true,
					// "One Piece" se resalta. Si es archive Magic, single Magic, o
					// shop sin contexto OP, "Magic" se resalta. En home/cuenta/checkout
					// queda Magic activo (default del MVP — tienda primaria es Magic).
					$onplay_active_tcg = ( function_exists( 'onplay_op_in_op_context' ) && onplay_op_in_op_context() )
						? 'op'
						: 'magic';
					?>
					<a
						href="<?php echo esc_url( $magic_url ); ?>"
						class="nav-item<?php echo 'magic' === $onplay_active_tcg ? ' is-active' : ''; ?>"
					>Magic</a>
					<?php
					$onepiece_term = get_term_by( 'slug', 'one-piece-tcg', 'product_cat' );
					$onepiece_url  = '';
					if ( $onepiece_term && ! is_wp_error( $onepiece_term ) ) {
						// Forzar el link al shop con set= para que el redirect 302 de
						// /product-category/<slug>/ aterrice limpio en /shop/?set=...
						// y el contexto OP quede inmediatamente activo.
						$onepiece_url = add_query_arg( 'set', $onepiece_term->slug, $magic_url );
					}
					if ( $onepiece_url ) : ?>
						<a
							href="<?php echo esc_url( $onepiece_url ); ?>"
							class="nav-item<?php echo 'op' === $onplay_active_tcg ? ' is-active' : ''; ?>"
						>One Piece</a>
					<?php else : ?>
						<span class="nav-item is-soon">One Piece <span class="badge badge-proximamente">Pronto</span></span>
					<?php endif; ?>
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
