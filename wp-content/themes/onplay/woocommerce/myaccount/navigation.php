<?php
/**
 * My Account — navegación lateral.
 *
 * Lista de endpoints de WC con iconos SVG inline por key. Los endpoints
 * ausentes (ej. `downloads`, `payment-methods`) caen al fallback sin icono.
 *
 * @package Onplay
 * @version WC 9.3.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_navigation' );

$items = wc_get_account_menu_items();

$icons = array(
	'dashboard'       => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>',
	'orders'          => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
	'downloads'       => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>',
	'edit-address'    => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>',
	'payment-methods' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>',
	'edit-account'    => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
	'customer-logout' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
);
?>
<nav class="myaccount__nav woocommerce-MyAccount-navigation" aria-label="<?php esc_attr_e( 'Páginas de la cuenta', 'onplay' ); ?>">
	<ul class="myaccount__nav-list">
		<?php foreach ( $items as $endpoint => $label ) : ?>
			<?php
			$is_current = wc_is_current_account_menu_item( $endpoint );
			$classes    = 'myaccount__nav-item ' . wc_get_account_menu_item_classes( $endpoint );
			if ( $is_current ) {
				$classes .= ' is-current';
			}
			$icon = isset( $icons[ $endpoint ] ) ? $icons[ $endpoint ] : '';
			?>
			<li class="<?php echo esc_attr( $classes ); ?>">
				<a
					class="myaccount__nav-link"
					href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"
					<?php echo $is_current ? 'aria-current="page"' : ''; ?>
				>
					<?php if ( $icon ) : ?>
						<span class="myaccount__nav-icon" aria-hidden="true"><?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<?php endif; ?>
					<span class="myaccount__nav-label"><?php echo esc_html( $label ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
<?php do_action( 'woocommerce_after_account_navigation' ); ?>
