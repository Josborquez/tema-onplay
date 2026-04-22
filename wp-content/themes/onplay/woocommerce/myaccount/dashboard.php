<?php
/**
 * My Account — dashboard.
 *
 * Resumen con último pedido + accesos rápidos (pedidos, direcciones,
 * datos de cuenta). Usamos `wc_get_customer_last_order()` para la tarjeta
 * destacada; si no hay pedidos se muestra un empty state con CTA al shop.
 *
 * @package Onplay
 * @version WC 4.4.0
 */

defined( 'ABSPATH' ) || exit;

$user_id    = get_current_user_id();
$last_order = function_exists( 'wc_get_customer_last_order' ) ? wc_get_customer_last_order( $user_id ) : false;

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/tienda/' );
?>
<div class="myaccount-dashboard">
	<p class="myaccount-dashboard__lede">
		<?php
		printf(
			/* translators: 1: orders url, 2: address url, 3: account url */
			wp_kses(
				__( 'Desde aquí puedes revisar tus <a href="%1$s">pedidos</a>, administrar tus <a href="%2$s">direcciones</a> y <a href="%3$s">editar tus datos de cuenta</a>.', 'onplay' ),
				array( 'a' => array( 'href' => array() ) )
			),
			esc_url( wc_get_endpoint_url( 'orders' ) ),
			esc_url( wc_get_endpoint_url( 'edit-address' ) ),
			esc_url( wc_get_endpoint_url( 'edit-account' ) )
		);
		?>
	</p>

	<?php if ( $last_order ) : ?>
		<article class="myaccount-card myaccount-card--last-order">
			<header class="myaccount-card__head">
				<span class="myaccount-card__kicker mono"><?php esc_html_e( 'Último pedido', 'onplay' ); ?></span>
				<a class="myaccount-card__link" href="<?php echo esc_url( $last_order->get_view_order_url() ); ?>">
					<?php esc_html_e( 'Ver detalle', 'onplay' ); ?> &rarr;
				</a>
			</header>
			<div class="myaccount-card__body">
				<div class="myaccount-card__meta">
					<span class="myaccount-card__order-num mono">#<?php echo esc_html( $last_order->get_order_number() ); ?></span>
					<time class="myaccount-card__order-date" datetime="<?php echo esc_attr( $last_order->get_date_created()->date( 'c' ) ); ?>">
						<?php echo esc_html( wc_format_datetime( $last_order->get_date_created() ) ); ?>
					</time>
				</div>
				<div class="myaccount-card__status-row">
					<span class="myaccount-card__status myaccount-card__status--<?php echo esc_attr( $last_order->get_status() ); ?>">
						<?php echo esc_html( wc_get_order_status_name( $last_order->get_status() ) ); ?>
					</span>
					<span class="myaccount-card__total"><?php echo wp_kses_post( $last_order->get_formatted_order_total() ); ?></span>
				</div>
			</div>
		</article>
	<?php else : ?>
		<article class="myaccount-card myaccount-card--empty">
			<div class="myaccount-card__empty">
				<span class="myaccount-card__kicker mono"><?php esc_html_e( 'Sin pedidos aún', 'onplay' ); ?></span>
				<p class="myaccount-card__empty-text">
					<?php esc_html_e( 'Cuando compres tu primera carta, podrás seguirla desde aquí.', 'onplay' ); ?>
				</p>
				<a class="btn btn-primary" href="<?php echo esc_url( $shop_url ); ?>">
					<?php esc_html_e( 'Explorar catálogo', 'onplay' ); ?>
				</a>
			</div>
		</article>
	<?php endif; ?>

	<div class="myaccount-dashboard__grid">
		<a class="myaccount-tile" href="<?php echo esc_url( wc_get_endpoint_url( 'orders' ) ); ?>">
			<span class="myaccount-tile__kicker mono"><?php esc_html_e( 'Historial', 'onplay' ); ?></span>
			<span class="myaccount-tile__title"><?php esc_html_e( 'Mis pedidos', 'onplay' ); ?></span>
			<span class="myaccount-tile__cta"><?php esc_html_e( 'Ver todos', 'onplay' ); ?> &rarr;</span>
		</a>
		<a class="myaccount-tile" href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address' ) ); ?>">
			<span class="myaccount-tile__kicker mono"><?php esc_html_e( 'Envíos', 'onplay' ); ?></span>
			<span class="myaccount-tile__title"><?php esc_html_e( 'Direcciones', 'onplay' ); ?></span>
			<span class="myaccount-tile__cta"><?php esc_html_e( 'Administrar', 'onplay' ); ?> &rarr;</span>
		</a>
		<a class="myaccount-tile" href="<?php echo esc_url( wc_get_endpoint_url( 'edit-account' ) ); ?>">
			<span class="myaccount-tile__kicker mono"><?php esc_html_e( 'Perfil', 'onplay' ); ?></span>
			<span class="myaccount-tile__title"><?php esc_html_e( 'Datos de cuenta', 'onplay' ); ?></span>
			<span class="myaccount-tile__cta"><?php esc_html_e( 'Editar', 'onplay' ); ?> &rarr;</span>
		</a>
	</div>
</div>
<?php
do_action( 'woocommerce_account_dashboard' );
do_action( 'woocommerce_before_my_account' );
do_action( 'woocommerce_after_my_account' );
