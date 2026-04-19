<?php
/**
 * Asset enqueueing for the Onplay theme.
 *
 * @package Onplay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'wp_enqueue_scripts',
	function () {
		$css_path = ONPLAY_THEME_DIR . '/assets/dist/style.css';
		$js_path  = ONPLAY_THEME_DIR . '/assets/dist/main.js';

		$css_ver = file_exists( $css_path ) ? filemtime( $css_path ) : ONPLAY_THEME_VERSION;
		$js_ver  = file_exists( $js_path ) ? filemtime( $js_path ) : ONPLAY_THEME_VERSION;

		wp_enqueue_style(
			'onplay-fonts',
			'https://fonts.googleapis.com/css2?family=Bebas+Neue&family=IBM+Plex+Sans:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap',
			array(),
			ONPLAY_THEME_VERSION
		);

		wp_enqueue_style(
			'onplay-main',
			ONPLAY_THEME_URI . '/assets/dist/style.css',
			array( 'onplay-fonts' ),
			$css_ver
		);

		wp_enqueue_script(
			'onplay-main',
			ONPLAY_THEME_URI . '/assets/dist/main.js',
			array(),
			$js_ver,
			true
		);
	}
);

add_action(
	'wp_head',
	function () {
		echo "<link rel=\"preconnect\" href=\"https://fonts.googleapis.com\">\n";
		echo "<link rel=\"preconnect\" href=\"https://fonts.gstatic.com\" crossorigin>\n";
	},
	1
);
