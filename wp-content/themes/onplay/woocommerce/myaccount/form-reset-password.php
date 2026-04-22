<?php
/**
 * My Account — definir nueva contraseña (después del link del email).
 *
 * @package Onplay
 * @version WC 9.2.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_reset_password_form' );
?>
<form method="post" class="myaccount-form myaccount-form--narrow woocommerce-ResetPassword lost_reset_password">
	<header class="myaccount-form__head">
		<span class="myaccount-form__kicker mono"><?php esc_html_e( 'Nueva contraseña', 'onplay' ); ?></span>
		<h2 class="myaccount-form__title"><?php esc_html_e( 'Define tu contraseña', 'onplay' ); ?></h2>
	</header>

	<p class="myaccount-form__lede">
		<?php echo apply_filters( 'woocommerce_reset_password_message', esc_html__( 'Ingresa una nueva contraseña a continuación.', 'onplay' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</p>

	<div class="myaccount-form__grid myaccount-form__grid--2">
		<p class="myaccount-form__row form-row">
			<label for="password_1"><?php esc_html_e( 'Nueva contraseña', 'onplay' ); ?> <span class="required" aria-hidden="true">*</span></label>
			<input
				type="password"
				class="myaccount-form__input woocommerce-Input woocommerce-Input--text input-text"
				name="password_1"
				id="password_1"
				autocomplete="new-password"
				required
				aria-required="true"
			/>
		</p>
		<p class="myaccount-form__row form-row">
			<label for="password_2"><?php esc_html_e( 'Confirmar nueva', 'onplay' ); ?> <span class="required" aria-hidden="true">*</span></label>
			<input
				type="password"
				class="myaccount-form__input woocommerce-Input woocommerce-Input--text input-text"
				name="password_2"
				id="password_2"
				autocomplete="new-password"
				required
				aria-required="true"
			/>
		</p>
	</div>

	<input type="hidden" name="reset_key" value="<?php echo esc_attr( $args['key'] ); ?>" />
	<input type="hidden" name="reset_login" value="<?php echo esc_attr( $args['login'] ); ?>" />

	<?php do_action( 'woocommerce_resetpassword_form' ); ?>

	<div class="myaccount-form__actions">
		<input type="hidden" name="wc_reset_password" value="true" />
		<button type="submit" class="btn btn-primary" value="<?php esc_attr_e( 'Guardar', 'onplay' ); ?>">
			<?php esc_html_e( 'Guardar', 'onplay' ); ?>
		</button>
	</div>

	<?php wp_nonce_field( 'reset_password', 'woocommerce-reset-password-nonce' ); ?>
</form>
<?php do_action( 'woocommerce_after_reset_password_form' ); ?>
