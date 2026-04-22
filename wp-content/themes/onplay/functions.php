<?php
/**
 * Onplay theme bootstrap.
 *
 * @package Onplay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ONPLAY_THEME_VERSION', '0.1.0' );
define( 'ONPLAY_THEME_DIR', get_template_directory() );
define( 'ONPLAY_THEME_URI', get_template_directory_uri() );

require_once ONPLAY_THEME_DIR . '/inc/setup.php';
require_once ONPLAY_THEME_DIR . '/inc/security.php';
require_once ONPLAY_THEME_DIR . '/inc/seo.php';
require_once ONPLAY_THEME_DIR . '/inc/enqueue.php';
require_once ONPLAY_THEME_DIR . '/inc/taxonomies.php';
require_once ONPLAY_THEME_DIR . '/inc/meta-to-taxonomy.php';
require_once ONPLAY_THEME_DIR . '/inc/scryfall-enrich.php';
require_once ONPLAY_THEME_DIR . '/inc/pages.php';
require_once ONPLAY_THEME_DIR . '/inc/cli.php';
require_once ONPLAY_THEME_DIR . '/inc/woocommerce.php';
require_once ONPLAY_THEME_DIR . '/inc/variants-query.php';
require_once ONPLAY_THEME_DIR . '/inc/cart-drawer.php';
require_once ONPLAY_THEME_DIR . '/inc/cart-update.php';
require_once ONPLAY_THEME_DIR . '/inc/checkout-rut.php';
require_once ONPLAY_THEME_DIR . '/inc/search.php';
require_once ONPLAY_THEME_DIR . '/inc/listing-grouping.php';
require_once ONPLAY_THEME_DIR . '/inc/filters-ajax.php';
require_once ONPLAY_THEME_DIR . '/inc/home.php';
