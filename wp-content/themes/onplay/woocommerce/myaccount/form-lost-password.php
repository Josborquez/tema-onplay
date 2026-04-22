<?php
/**
 * My Account — solicitar reset de contraseña.
 *
 * @package Onplay
 * @version WC 9.2.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_lost_password_form' );
?>
<form method="post" class="myaccount-form myaccount-form--narrow woocommerce-ResetPassword lost_reset_password">
	<header class="myaccount-form__head">
		<span class="myaccount-form__kicker mono"><?php esc_html_e( 'Recuperar acceso', 'onplay' ); ?></span>
		<h2 class="myaccount-form__title"><?php esc_html_e( 'Restablecer contraseña', 'onplay' ); ?></h2>
	</header>

	<p class="myaccount-form__lede">
		<?php echo apply_filters( 'woocommerce_lost_password_message', esc_html__( 'Ingresa tu usuario o email y te enviaremos un link para definir una nueva contraseña.', 'onplay' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</p>

	<p class="myaccount-form__row form-row">
		<label for="user_login"><?php esc_html_e( 'Usuario o email', 'onplay' ); ?> <span class="required" aria-hidden="true">*</span></label>
		<input
			class="myaccount-form__input woocommerce-Input woocommerce-Input--text input-text"
			type="text"
			name="user_login"
			id="user_login"
			autocomplete="username"
			required
			aria-required="true"
		/>
	</p>

	<?php do_action( 'woocommerce_lostpassword_form' ); ?>

	<div class="myaccount-form__actions">
		<input type="hidden" name="wc_reset_password" value="true" />
		<button type="submit" class="btn btn-primary" value="<?php esc_attr_e( 'Enviar link', 'onplay' ); ?>">
			<?php esc_html_e( 'Enviar link', 'onplay' ); ?>
		</button>
	</div>

	<?php wp_nonce_field( 'lost_password', 'woocommerce-lost-password-nonce' ); ?>
</form>
<?php do_action( 'woocommerce_after_lost_password_form' ); ?>
