<?php
/**
 * Home — Hero fiel al diseño (Módulo 9).
 *
 * Layout 2-col (1.15fr · 1fr):
 *   - Izquierda: kicker con línea + título multi-línea + copy + buscador con preview
 *     "populares ahora" (estático, server-side) + stats chips.
 *   - Derecha: stack flotante de 5 cartas (absolute + rotaciones + animación CSS).
 *
 * Los datos del stack + preview vienen de `onplay_home_get_hero_cards()` — reusan
 * el pool "recient + mayor valor" ya ordenado por `onplay_home_get_recent_cards()`.
 * El buscador combina submit nativo (`?q=` a /tienda/) + autocomplete reactivo:
 * el módulo JS `HeroSearch` llama al endpoint `onplay_search` y reemplaza las filas
 * del panel cuando el usuario teclea; cuando vacío, restaura las "Populares ahora"
 * server-side. El header del panel alterna entre "Populares ahora" y "Sugerencias".
 *
 * @package Onplay
 */

defined( 'ABSPATH' ) || exit;

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/tienda/' );

$hero_cards = function_exists( 'onplay_home_get_hero_cards' ) ? onplay_home_get_hero_cards( 9 ) : array();
$stack      = array_slice( $hero_cards, 0, 5 );
$preview    = array_slice( $hero_cards, 5, 4 );

$stats = function_exists( 'onplay_home_get_stats' ) ? onplay_home_get_stats() : array(
	'singles'  => 0,
	'sets'     => 0,
	'despacho' => '24-48h',
);

/**
 * Render helper local: una fila del preview "populares ahora".
 *
 * @param array $group Grupo (representative_id, min_price, variant_count, print_key).
 * @return void
 */
$render_preview_row = function ( $group ) {
	$pid = (int) $group['representative_id'];
	if ( $pid <= 0 ) {
		return;
	}
	$product = wc_get_product( $pid );
	if ( ! $product ) {
		return;
	}
	$title  = trim( preg_replace( '/\s*\(Foil\)\s*/i', '', (string) get_the_title( $pid ) ) );
	$sku    = $product->get_sku();
	$parts  = onplay_parse_sku( $sku );
	$price  = (float) ( isset( $group['min_price'] ) ? $group['min_price'] : $product->get_price() );
	$perma  = get_permalink( $pid );
	$thumb  = get_the_post_thumbnail_url( $pid, 'thumbnail' );

	$category_terms = get_the_terms( $pid, 'product_cat' );
	$set_name       = '';
	if ( is_array( $category_terms ) ) {
		foreach ( $category_terms as $t ) {
			if ( $t->parent > 0 ) {
				$set_name = $t->name;
				break;
			}
		}
	}
	?>
	<a class="home-hero__sugg" href="<?php echo esc_url( $perma ); ?>">
		<?php if ( $thumb ) : ?>
			<img class="home-hero__sugg-img" src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy" />
		<?php else : ?>
			<span class="home-hero__sugg-img home-hero__sugg-img--ph" aria-hidden="true"></span>
		<?php endif; ?>
		<span class="home-hero__sugg-body">
			<span class="home-hero__sugg-name"><?php echo esc_html( $title ); ?></span>
			<span class="home-hero__sugg-meta">
				<?php echo onplay_render_set_icon( $parts['set_code'], 11 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php echo esc_html( $set_name ? $set_name : $parts['set_code'] ); ?></span>
				<?php if ( $parts['condition'] || $parts['language'] ) : ?>
					<span class="sep">·</span>
					<span class="mono"><?php echo esc_html( trim( $parts['condition'] . ' ' . $parts['language'] ) ); ?></span>
				<?php endif; ?>
			</span>
		</span>
		<span class="home-hero__sugg-price"><?php echo esc_html( onplay_format_clp( $price ) ); ?></span>
	</a>
	<?php
};

/**
 * Render helper local: una carta flotante del stack derecho.
 *
 * @param array  $group
 * @param string $pos   Claves de posición: x, y, r, z, size (sm|md|lg), featured.
 * @return void
 */
$render_floating_card = function ( $group, $pos ) {
	$pid = (int) $group['representative_id'];
	if ( $pid <= 0 ) {
		return;
	}
	$title  = trim( preg_replace( '/\s*\(Foil\)\s*/i', '', (string) get_the_title( $pid ) ) );
	$thumb  = get_the_post_thumbnail_url( $pid, 'medium_large' );
	$perma  = get_permalink( $pid );
	$price  = (float) ( isset( $group['min_price'] ) ? $group['min_price'] : 0 );
	$feat   = ! empty( $pos['featured'] );
	$size   = isset( $pos['size'] ) ? $pos['size'] : 'md';
	$style  = sprintf(
		'left:%dpx;top:%dpx;transform:rotate(%ddeg);z-index:%d;animation-delay:%ss;',
		(int) $pos['x'],
		(int) $pos['y'],
		(int) $pos['r'],
		(int) $pos['z'],
		(string) $pos['delay']
	);
	$classes = 'home-hero__float home-hero__float--' . esc_attr( $size );
	if ( $feat ) {
		$classes .= ' is-featured';
	}
	?>
	<a class="<?php echo esc_attr( $classes ); ?>" href="<?php echo esc_url( $perma ); ?>" style="<?php echo esc_attr( $style ); ?>" aria-label="<?php echo esc_attr( $title ); ?>">
		<?php if ( $thumb ) : ?>
			<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" />
		<?php endif; ?>
		<?php if ( $feat ) : ?>
			<div class="home-hero__float-meta">
				<div class="home-hero__float-kicker mono"><?php esc_html_e( 'Destacado', 'onplay' ); ?></div>
				<div class="home-hero__float-name"><?php echo esc_html( $title ); ?></div>
				<div class="home-hero__float-price"><?php echo esc_html( onplay_format_clp( $price ) ); ?></div>
			</div>
		<?php endif; ?>
	</a>
	<?php
};

$positions = array(
	array( 'x' => 60,  'y' => 20,  'r' => -8,  'z' => 1, 'delay' => '0',   'size' => 'md' ),
	array( 'x' => 200, 'y' => 80,  'r' => 4,   'z' => 3, 'delay' => '0.4', 'size' => 'lg', 'featured' => true ),
	array( 'x' => 0,   'y' => 240, 'r' => -14, 'z' => 2, 'delay' => '0.8', 'size' => 'md' ),
	array( 'x' => 280, 'y' => 320, 'r' => 10,  'z' => 1, 'delay' => '1.2', 'size' => 'md' ),
	array( 'x' => 140, 'y' => 380, 'r' => -2,  'z' => 2, 'delay' => '1.6', 'size' => 'sm' ),
);
?>
<section class="home-hero screen-enter" aria-labelledby="home-hero-title">
	<div class="home-hero__glow" aria-hidden="true"></div>

	<div class="container home-hero__inner">
		<div class="home-hero__left">
			<div class="home-hero__kicker">
				<span class="home-hero__kicker-line" aria-hidden="true"></span>
				<span class="home-hero__kicker-text mono"><?php esc_html_e( 'Singles · Chile · desde 2019', 'onplay' ); ?></span>
			</div>

			<h1 id="home-hero-title" class="home-hero__title d-xxl">
				<?php esc_html_e( 'La carta', 'onplay' ); ?><br/>
				<span class="home-hero__title-accent"><?php esc_html_e( 'exacta', 'onplay' ); ?></span>,<br/>
				<span class="home-hero__title-tag"><?php esc_html_e( 'sin rodeos.', 'onplay' ); ?></span>
			</h1>

			<p class="home-hero__sub">
				<?php
				printf(
					/* translators: %s: emphasised brand name */
					wp_kses_post( __( 'Miles de singles de %s clasificados por condición, set, idioma y foil. Envíos a todo Chile y retiro gratis en nuestra tienda de Merced 832.', 'onplay' ) ),
					'<b>' . esc_html__( 'Magic: The Gathering', 'onplay' ) . '</b>'
				);
				?>
			</p>

			<form
				class="home-hero__search"
				role="search"
				action="<?php echo esc_url( $shop_url ); ?>"
				method="get"
				data-onplay-hero-search
			>
				<label for="home-hero-q" class="screen-reader-text"><?php esc_html_e( 'Buscar cartas', 'onplay' ); ?></label>
				<svg class="home-hero__search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
					<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
				</svg>
				<input
					type="search"
					id="home-hero-q"
					name="q"
					class="home-hero__input"
					placeholder="<?php esc_attr_e( 'Lightning Bolt, Ragavan, Force of Negation…', 'onplay' ); ?>"
					autocomplete="off"
					data-onplay-hero-input
				/>
				<button type="submit" class="btn btn-primary home-hero__btn">
					<?php esc_html_e( 'Buscar', 'onplay' ); ?>
				</button>
			</form>

			<?php if ( ! empty( $preview ) ) : ?>
				<div class="home-hero__suggs" aria-label="<?php esc_attr_e( 'Populares ahora', 'onplay' ); ?>" data-onplay-hero-panel>
					<div
						class="home-hero__suggs-head mono"
						data-onplay-hero-head
						data-label-popular="<?php esc_attr_e( 'Populares ahora', 'onplay' ); ?>"
						data-label-suggestions="<?php esc_attr_e( 'Sugerencias', 'onplay' ); ?>"
					><?php esc_html_e( 'Populares ahora', 'onplay' ); ?></div>
					<div class="home-hero__suggs-body" data-onplay-hero-body>
						<?php
						foreach ( $preview as $g ) {
							$render_preview_row( $g );
						}
						?>
					</div>
				</div>
			<?php endif; ?>

			<div class="home-hero__stats">
				<div class="home-hero__stat">
					<span class="home-hero__stat-num d-md"><?php echo esc_html( '+' . number_format_i18n( (int) $stats['singles'] ) ); ?></span>
					<span class="home-hero__stat-label"><?php esc_html_e( 'Singles en stock', 'onplay' ); ?></span>
				</div>
				<div class="home-hero__stat">
					<span class="home-hero__stat-num d-md"><?php echo esc_html( $stats['sets'] . '+' ); ?></span>
					<span class="home-hero__stat-label"><?php esc_html_e( 'Sets disponibles', 'onplay' ); ?></span>
				</div>
				<div class="home-hero__stat">
					<span class="home-hero__stat-num d-md"><?php echo esc_html( $stats['despacho'] ); ?></span>
					<span class="home-hero__stat-label"><?php esc_html_e( 'Despacho típico', 'onplay' ); ?></span>
				</div>
			</div>
		</div>

		<div class="home-hero__right" aria-hidden="true">
			<?php
			foreach ( $stack as $i => $g ) {
				if ( ! isset( $positions[ $i ] ) ) {
					continue;
				}
				$render_floating_card( $g, $positions[ $i ] );
			}
			?>
		</div>
	</div>
</section>
