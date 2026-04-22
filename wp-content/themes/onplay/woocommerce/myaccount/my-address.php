<?php
/**
 * My Account — direcciones guardadas.
 *
 * Grid de 2 tarjetas (facturación | envío) cuando shipping está habilitado
 * y no ship-to-billing-only; si no, una sola. Cada tarjeta muestra la
 * dirección formateada (`wc_get_account_formatted_address`) o un empty
 * state con CTA a "Añadir dirección".
 *
 * @package Onplay
 * @version WC 9.3.0
 */

defined( 'ABSPATH' ) || exit;

$customer_id = get_current_user_id();

if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) {
	$get_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array(
			'billing'  => __( 'Dirección de facturación', 'onplay' ),
			'shipping' => __( 'Dirección de envío', 'onplay' ),
		),
		$customer_id
	);
} else {
	$get_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array(
			'billing' => __( 'Dirección de facturación', 'onplay' ),
		),
		$customer_id
	);
}

$split = count( $get_addresses ) > 1;
?>
<p class="myaccount-addresses__lede">
	<?php esc_html_e( 'Estas direcciones se usarán por defecto en el checkout.', 'onplay' ); ?>
</p>

<div class="myaccount-addresses <?php echo $split ? 'myaccount-addresses--split' : ''; ?>">
	<?php foreach ( $get_addresses as $name => $address_title ) : ?>
		<?php $address = wc_get_account_formatted_address( $name ); ?>
		<article class="myaccount-address <?php echo $address ? 'myaccount-address--filled' : 'myaccount-address--empty'; ?>">
			<header class="myaccount-address__head">
				<span class="myaccount-address__kicker mono">
					<?php echo 'billing' === $name ? esc_html__( 'Facturación', 'onplay' ) : esc_html__( 'Envío', 'onplay' ); ?>
				</span>
				<h3 class="myaccount-address__title"><?php echo esc_html( $address_title ); ?></h3>
			</header>

			<address class="myaccount-address__body">
				<?php
				if ( $address ) {
					echo wp_kses_post( $address );
				} else {
					echo '<span class="myaccount-address__empty-text">' . esc_html__( 'Aún no agregas esta dirección.', 'onplay' ) . '</span>';
				}
				do_action( 'woocommerce_my_account_after_my_address', $name );
				?>
			</address>

			<a
				class="btn btn-ghost myaccount-address__action"
				href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', $name ) ); ?>"
			>
				<?php
				echo esc_html(
					$address
						/* translators: %s: address title */
						? sprintf( __( 'Editar %s', 'onplay' ), strtolower( $address_title ) )
						/* translators: %s: address title */
						: sprintf( __( 'Añadir %s', 'onplay' ), strtolower( $address_title ) )
				);
				?>
			</a>
		</article>
	<?php endforeach; ?>
</div>
