<?php
/**
 * My Account — wrapper (Módulo "M-cuenta").
 *
 * Layout 2-col en desktop: nav lateral + contenido. En mobile la nav pasa
 * arriba del contenido. Delegamos en los hooks estándar de WC
 * (`woocommerce_account_navigation`, `woocommerce_account_content`) para
 * mantener compatibilidad con plugins que inyectan endpoints.
 *
 * @package Onplay
 * @version WC 3.5.0
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="myaccount screen-enter">
	<div class="container">
		<header class="myaccount__header">
			<div>
				<span class="myaccount__kicker mono"><?php esc_html_e( 'Mi cuenta', 'onplay' ); ?></span>
				<h1 class="myaccount__title">
					<?php
					$current_user = wp_get_current_user();
					if ( $current_user && $current_user->exists() ) {
						$name = $current_user->first_name ? $current_user->first_name : $current_user->display_name;
						printf(
							/* translators: %s: display name */
							esc_html__( 'Hola, %s', 'onplay' ),
							esc_html( $name )
						);
					} else {
						esc_html_e( 'Ingresa a tu cuenta', 'onplay' );
					}
					?>
				</h1>
			</div>
			<?php if ( is_user_logged_in() ) : ?>
				<a class="myaccount__header-link" href="<?php echo esc_url( wc_logout_url() ); ?>">
					<?php esc_html_e( 'Cerrar sesión', 'onplay' ); ?> &rarr;
				</a>
			<?php endif; ?>
		</header>

		<div class="myaccount__layout">
			<?php
			/**
			 * My Account navigation.
			 *
			 * @since 2.6.0
			 */
			do_action( 'woocommerce_account_navigation' );
			?>

			<div class="myaccount__content woocommerce-MyAccount-content">
				<?php
				/**
				 * My Account content.
				 *
				 * @since 2.6.0
				 */
				do_action( 'woocommerce_account_content' );
				?>
			</div>
		</div>
	</div>
</section>
