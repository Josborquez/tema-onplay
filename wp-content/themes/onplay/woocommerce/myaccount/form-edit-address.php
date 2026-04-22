<?php
/**
 * My Account — editar dirección.
 *
 * Cuando `$load_address` es vacío se delega a `my-address.php` (landing).
 * Si está seteado, render del form con los fields que WC pasa en `$address`.
 *
 * @package Onplay
 * @version WC 9.3.0
 */

defined( 'ABSPATH' ) || exit;

$page_title = ( 'billing' === $load_address ) ? esc_html__( 'Dirección de facturación', 'onplay' ) : esc_html__( 'Dirección de envío', 'onplay' );

do_action( 'woocommerce_before_edit_account_address_form' );
?>

<?php if ( ! $load_address ) : ?>
	<?php wc_get_template( 'myaccount/my-address.php' ); ?>
<?php else : ?>

	<form class="myaccount-form" method="post" novalidate>
		<header class="myaccount-form__head">
			<span class="myaccount-form__kicker mono"><?php esc_html_e( 'Editar dirección', 'onplay' ); ?></span>
			<h2 class="myaccount-form__title">
				<?php echo esc_html( apply_filters( 'woocommerce_my_account_edit_address_title', $page_title, $load_address ) ); ?>
			</h2>
		</header>

		<div class="myaccount-form__fields woocommerce-address-fields">
			<?php do_action( "woocommerce_before_edit_address_form_{$load_address}" ); ?>

			<div class="myaccount-form__grid woocommerce-address-fields__field-wrapper">
				<?php
				foreach ( $address as $key => $field ) {
					woocommerce_form_field( $key, $field, wc_get_post_data_by_key( $key, $field['value'] ) );
				}
				?>
			</div>

			<?php do_action( "woocommerce_after_edit_address_form_{$load_address}" ); ?>

			<div class="myaccount-form__actions">
				<button type="submit" class="btn btn-primary" name="save_address" value="<?php esc_attr_e( 'Guardar dirección', 'onplay' ); ?>">
					<?php esc_html_e( 'Guardar dirección', 'onplay' ); ?>
				</button>
				<?php wp_nonce_field( 'woocommerce-edit_address', 'woocommerce-edit-address-nonce' ); ?>
				<input type="hidden" name="action" value="edit_address" />
			</div>
		</div>
	</form>

<?php endif; ?>

<?php do_action( 'woocommerce_after_edit_account_address_form' ); ?>
