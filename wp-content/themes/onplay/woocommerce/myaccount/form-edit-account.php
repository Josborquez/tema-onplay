<?php
/**
 * My Account — datos de cuenta (nombre, email, password).
 *
 * El form queda en 1 columna con secciones: datos personales + email,
 * y fieldset de cambio de contraseña (actual + nueva + confirmar).
 *
 * @package Onplay
 * @version WC 10.5.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_edit_account_form' );
?>
<form class="myaccount-form myaccount-form--account woocommerce-EditAccountForm edit-account" action="" method="post" <?php do_action( 'woocommerce_edit_account_form_tag' ); ?>>
	<header class="myaccount-form__head">
		<span class="myaccount-form__kicker mono"><?php esc_html_e( 'Perfil', 'onplay' ); ?></span>
		<h2 class="myaccount-form__title"><?php esc_html_e( 'Datos de cuenta', 'onplay' ); ?></h2>
	</header>

	<?php do_action( 'woocommerce_edit_account_form_start' ); ?>

	<div class="myaccount-form__fields">
		<div class="myaccount-form__grid myaccount-form__grid--2">
			<p class="myaccount-form__row form-row">
				<label for="account_first_name"><?php esc_html_e( 'Nombre', 'onplay' ); ?> <span class="required" aria-hidden="true">*</span></label>
				<input
					type="text"
					class="myaccount-form__input woocommerce-Input woocommerce-Input--text input-text"
					name="account_first_name"
					id="account_first_name"
					autocomplete="given-name"
					value="<?php echo esc_attr( $user->first_name ); ?>"
					aria-required="true"
				/>
			</p>
			<p class="myaccount-form__row form-row">
				<label for="account_last_name"><?php esc_html_e( 'Apellido', 'onplay' ); ?> <span class="required" aria-hidden="true">*</span></label>
				<input
					type="text"
					class="myaccount-form__input woocommerce-Input woocommerce-Input--text input-text"
					name="account_last_name"
					id="account_last_name"
					autocomplete="family-name"
					value="<?php echo esc_attr( $user->last_name ); ?>"
					aria-required="true"
				/>
			</p>
		</div>

		<p class="myaccount-form__row form-row">
			<label for="account_display_name"><?php esc_html_e( 'Nombre visible', 'onplay' ); ?> <span class="required" aria-hidden="true">*</span></label>
			<input
				type="text"
				class="myaccount-form__input woocommerce-Input woocommerce-Input--text input-text"
				name="account_display_name"
				id="account_display_name"
				aria-describedby="account_display_name_description"
				value="<?php echo esc_attr( $user->display_name ); ?>"
				aria-required="true"
			/>
			<span id="account_display_name_description" class="myaccount-form__hint">
				<?php esc_html_e( 'Así se mostrará tu nombre en la cuenta.', 'onplay' ); ?>
			</span>
		</p>

		<p class="myaccount-form__row form-row">
			<label for="account_email"><?php esc_html_e( 'Email', 'onplay' ); ?> <span class="required" aria-hidden="true">*</span></label>
			<input
				type="email"
				class="myaccount-form__input woocommerce-Input woocommerce-Input--email input-text"
				name="account_email"
				id="account_email"
				autocomplete="email"
				value="<?php echo esc_attr( $user->user_email ); ?>"
				aria-required="true"
			/>
		</p>

		<?php do_action( 'woocommerce_edit_account_form_fields' ); ?>

		<fieldset class="myaccount-form__fieldset">
			<legend class="myaccount-form__legend"><?php esc_html_e( 'Cambio de contraseña', 'onplay' ); ?></legend>

			<p class="myaccount-form__row form-row">
				<label for="password_current"><?php esc_html_e( 'Contraseña actual', 'onplay' ); ?></label>
				<input
					type="password"
					class="myaccount-form__input woocommerce-Input woocommerce-Input--password input-text"
					name="password_current"
					id="password_current"
					autocomplete="current-password"
				/>
				<span class="myaccount-form__hint"><?php esc_html_e( 'Déjalo en blanco para no cambiarla.', 'onplay' ); ?></span>
			</p>

			<div class="myaccount-form__grid myaccount-form__grid--2">
				<p class="myaccount-form__row form-row">
					<label for="password_1"><?php esc_html_e( 'Nueva contraseña', 'onplay' ); ?></label>
					<input
						type="password"
						class="myaccount-form__input woocommerce-Input woocommerce-Input--password input-text"
						name="password_1"
						id="password_1"
						autocomplete="new-password"
					/>
				</p>
				<p class="myaccount-form__row form-row">
					<label for="password_2"><?php esc_html_e( 'Confirmar nueva', 'onplay' ); ?></label>
					<input
						type="password"
						class="myaccount-form__input woocommerce-Input woocommerce-Input--password input-text"
						name="password_2"
						id="password_2"
						autocomplete="new-password"
					/>
				</p>
			</div>
		</fieldset>

		<?php do_action( 'woocommerce_edit_account_form' ); ?>

		<div class="myaccount-form__actions">
			<?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
			<button type="submit" class="btn btn-primary" name="save_account_details" value="<?php esc_attr_e( 'Guardar cambios', 'onplay' ); ?>">
				<?php esc_html_e( 'Guardar cambios', 'onplay' ); ?>
			</button>
			<input type="hidden" name="action" value="save_account_details" />
		</div>
	</div>

	<?php do_action( 'woocommerce_edit_account_form_end' ); ?>
</form>
<?php do_action( 'woocommerce_after_edit_account_form' ); ?>
