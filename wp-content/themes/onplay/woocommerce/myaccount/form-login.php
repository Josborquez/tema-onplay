<?php
/**
 * My Account — login + registro.
 *
 * Layout 2-col (login | registro) cuando el registro está habilitado en WC;
 * si no, solo login centrado. Mantiene los hooks y nonces estándar de WC
 * para no romper integraciones (reCAPTCHA, social login, etc. — si el dueño
 * los instala después).
 *
 * @package Onplay
 * @version WC 9.9.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_customer_login_form' );

$registration_enabled = 'yes' === get_option( 'woocommerce_enable_myaccount_registration' );
?>
<div class="myaccount-auth <?php echo $registration_enabled ? 'myaccount-auth--split' : 'myaccount-auth--solo'; ?>">

	<section class="myaccount-auth__panel myaccount-auth__panel--login">
		<header class="myaccount-auth__head">
			<span class="myaccount-auth__kicker mono"><?php esc_html_e( 'Clientes existentes', 'onplay' ); ?></span>
			<h2 class="myaccount-auth__title"><?php esc_html_e( 'Iniciar sesión', 'onplay' ); ?></h2>
		</header>

		<form class="myaccount-auth__form woocommerce-form woocommerce-form-login login" method="post" novalidate>
			<?php do_action( 'woocommerce_login_form_start' ); ?>

			<p class="myaccount-auth__row form-row form-row-wide">
				<label for="username"><?php esc_html_e( 'Usuario o email', 'onplay' ); ?> <span class="required" aria-hidden="true">*</span></label>
				<input
					type="text"
					class="myaccount-auth__input woocommerce-Input woocommerce-Input--text input-text"
					name="username"
					id="username"
					autocomplete="username"
					value="<?php echo ( ! empty( $_POST['username'] ) && is_string( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>"
					required
					aria-required="true"
				/>
			</p>

			<p class="myaccount-auth__row form-row form-row-wide">
				<label for="password"><?php esc_html_e( 'Contraseña', 'onplay' ); ?> <span class="required" aria-hidden="true">*</span></label>
				<input
					type="password"
					class="myaccount-auth__input woocommerce-Input woocommerce-Input--text input-text"
					name="password"
					id="password"
					autocomplete="current-password"
					required
					aria-required="true"
				/>
			</p>

			<?php do_action( 'woocommerce_login_form' ); ?>

			<div class="myaccount-auth__row myaccount-auth__actions form-row">
				<label class="myaccount-auth__remember">
					<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" />
					<span><?php esc_html_e( 'Recordarme', 'onplay' ); ?></span>
				</label>
				<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
				<button type="submit" class="btn btn-primary myaccount-auth__submit" name="login" value="<?php esc_attr_e( 'Entrar', 'onplay' ); ?>">
					<?php esc_html_e( 'Entrar', 'onplay' ); ?>
				</button>
			</div>

			<p class="myaccount-auth__lost">
				<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>">
					<?php esc_html_e( '¿Olvidaste tu contraseña?', 'onplay' ); ?>
				</a>
			</p>

			<?php do_action( 'woocommerce_login_form_end' ); ?>
		</form>
	</section>

	<?php if ( $registration_enabled ) : ?>
		<section class="myaccount-auth__panel myaccount-auth__panel--register">
			<header class="myaccount-auth__head">
				<span class="myaccount-auth__kicker mono"><?php esc_html_e( 'Nuevos clientes', 'onplay' ); ?></span>
				<h2 class="myaccount-auth__title"><?php esc_html_e( 'Crear cuenta', 'onplay' ); ?></h2>
			</header>

			<form method="post" class="myaccount-auth__form woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?>>
				<?php do_action( 'woocommerce_register_form_start' ); ?>

				<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
					<p class="myaccount-auth__row form-row form-row-wide">
						<label for="reg_username"><?php esc_html_e( 'Usuario', 'onplay' ); ?> <span class="required" aria-hidden="true">*</span></label>
						<input
							type="text"
							class="myaccount-auth__input woocommerce-Input woocommerce-Input--text input-text"
							name="username"
							id="reg_username"
							autocomplete="username"
							value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>"
							required
							aria-required="true"
						/>
					</p>
				<?php endif; ?>

				<p class="myaccount-auth__row form-row form-row-wide">
					<label for="reg_email"><?php esc_html_e( 'Email', 'onplay' ); ?> <span class="required" aria-hidden="true">*</span></label>
					<input
						type="email"
						class="myaccount-auth__input woocommerce-Input woocommerce-Input--text input-text"
						name="email"
						id="reg_email"
						autocomplete="email"
						value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>"
						required
						aria-required="true"
					/>
				</p>

				<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
					<p class="myaccount-auth__row form-row form-row-wide">
						<label for="reg_password"><?php esc_html_e( 'Contraseña', 'onplay' ); ?> <span class="required" aria-hidden="true">*</span></label>
						<input
							type="password"
							class="myaccount-auth__input woocommerce-Input woocommerce-Input--text input-text"
							name="password"
							id="reg_password"
							autocomplete="new-password"
							required
							aria-required="true"
						/>
					</p>
				<?php else : ?>
					<p class="myaccount-auth__hint">
						<?php esc_html_e( 'Te enviaremos un link al email para que definas tu contraseña.', 'onplay' ); ?>
					</p>
				<?php endif; ?>

				<?php do_action( 'woocommerce_register_form' ); ?>

				<p class="myaccount-auth__row myaccount-auth__actions form-row">
					<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
					<button type="submit" class="btn btn-primary myaccount-auth__submit" name="register" value="<?php esc_attr_e( 'Crear cuenta', 'onplay' ); ?>">
						<?php esc_html_e( 'Crear cuenta', 'onplay' ); ?>
					</button>
				</p>

				<?php do_action( 'woocommerce_register_form_end' ); ?>
			</form>
		</section>
	<?php endif; ?>
</div>
<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
