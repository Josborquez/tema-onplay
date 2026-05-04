<?php
/**
 * Plugin Name: Onplay — Update Order Review Debug
 * Description: Captura fatals/warnings del endpoint wc-ajax=update_order_review (checkout 500) y los escribe a wp-content/debug-checkout.log. Pensado para uso temporal — borrarlo cuando se identifique la causa.
 * Version:     0.1.0
 * Author:      Onplay
 *
 * Cómo usar:
 *   1. Subir este archivo a /wp-content/mu-plugins/onplay-update-order-review-debug.php
 *      (crear la carpeta mu-plugins/ si no existe — sin index.php).
 *   2. Reproducir el 500 navegando a /checkout/.
 *   3. Abrir /wp-content/debug-checkout.log — ahí va a aparecer la traza
 *      con archivo + línea del fatal.
 *   4. Una vez identificada la causa, BORRAR este archivo de mu-plugins/.
 *
 * Por qué mu-plugin en lugar de WP_DEBUG en wp-config:
 *   - No requiere editar wp-config (mantiene a salvo display_errors=Off).
 *   - Solo captura el endpoint problemático, no inunda logs con ruido global.
 *   - Activación inmediata sin "registrar" el plugin (mu-plugins se cargan solos).
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'plugins_loaded',
	function () {
		// Solo actuar si la request es al endpoint update_order_review.
		// WC lo expone con dos URLs: ?wc-ajax=update_order_review (preferido)
		// y admin-ajax.php?action=woocommerce_update_order_review (legacy).
		$is_target = false;
		if ( isset( $_GET['wc-ajax'] ) && 'update_order_review' === $_GET['wc-ajax'] ) {
			$is_target = true;
		}
		if ( isset( $_REQUEST['action'] ) && 'woocommerce_update_order_review' === $_REQUEST['action'] ) {
			$is_target = true;
		}
		if ( ! $is_target ) {
			return;
		}

		$log_path = WP_CONTENT_DIR . '/debug-checkout.log';

		$write = function ( $line ) use ( $log_path ) {
			@file_put_contents( $log_path, $line, FILE_APPEND );
		};

		// Marker de inicio + contexto de la request.
		$write(
			sprintf(
				"[%s] >>> START · uri=%s · ip=%s · ua=%s\n",
				gmdate( 'Y-m-d H:i:s' ),
				isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '-',
				isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '-',
				isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 80 ) : '-'
			)
		);

		// Forzar reporting completo solo en este request.
		error_reporting( E_ALL );
		@ini_set( 'display_errors', '0' ); // por las dudas — no queremos romper el JSON con HTML.

		// Capturar warnings/notices/strict en runtime.
		set_error_handler(
			function ( $errno, $errstr, $errfile, $errline ) use ( $write ) {
				$labels = array(
					E_ERROR             => 'ERROR',
					E_WARNING           => 'WARNING',
					E_PARSE             => 'PARSE',
					E_NOTICE            => 'NOTICE',
					E_CORE_ERROR        => 'CORE_ERROR',
					E_CORE_WARNING      => 'CORE_WARNING',
					E_COMPILE_ERROR     => 'COMPILE_ERROR',
					E_COMPILE_WARNING   => 'COMPILE_WARNING',
					E_USER_ERROR        => 'USER_ERROR',
					E_USER_WARNING      => 'USER_WARNING',
					E_USER_NOTICE       => 'USER_NOTICE',
					E_STRICT            => 'STRICT',
					E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
					E_DEPRECATED        => 'DEPRECATED',
					E_USER_DEPRECATED   => 'USER_DEPRECATED',
				);
				$label = isset( $labels[ $errno ] ) ? $labels[ $errno ] : ( 'TYPE_' . (int) $errno );
				$write(
					sprintf(
						"[%s] %s: %s in %s:%d\n",
						gmdate( 'Y-m-d H:i:s' ),
						$label,
						$errstr,
						$errfile,
						$errline
					)
				);
				// Devolver false para que PHP siga su manejo normal (incluyendo
				// detener ejecución en E_ERROR si fuera el caso).
				return false;
			}
		);

		// Capturar excepciones no-handleadas.
		set_exception_handler(
			function ( $e ) use ( $write ) {
				$write(
					sprintf(
						"[%s] EXCEPTION %s: %s in %s:%d\nTrace:\n%s\n",
						gmdate( 'Y-m-d H:i:s' ),
						get_class( $e ),
						$e->getMessage(),
						$e->getFile(),
						$e->getLine(),
						$e->getTraceAsString()
					)
				);
			}
		);

		// Capturar fatal (incluye E_ERROR/E_PARSE/E_CORE_ERROR/E_COMPILE_ERROR/E_USER_ERROR).
		register_shutdown_function(
			function () use ( $write ) {
				$err = error_get_last();
				$is_fatal_types = array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR );
				if ( $err && in_array( $err['type'], $is_fatal_types, true ) ) {
					$write(
						sprintf(
							"[%s] FATAL type=%d: %s in %s:%d\n",
							gmdate( 'Y-m-d H:i:s' ),
							$err['type'],
							$err['message'],
							$err['file'],
							$err['line']
						)
					);
				}
				$write( sprintf( "[%s] <<< END\n\n", gmdate( 'Y-m-d H:i:s' ) ) );
			}
		);
	},
	0
);
