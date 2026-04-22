<?php
/**
 * WP-CLI commands for the Onplay theme.
 *
 * @package Onplay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

/**
 * Backfill Scryfall meta for existing products.
 *
 * ## OPTIONS
 *
 * [--force]
 * : Refetch even if the product already has _onplay_scryfall_cached_at.
 *
 * [--limit=<n>]
 * : Process at most N products (useful for smoke tests).
 *
 * [--dry-run]
 * : Do not write meta; just list what would be processed.
 *
 * ## EXAMPLES
 *
 *     wp onplay:backfill-meta
 *     wp onplay:backfill-meta --limit=10
 *     wp onplay:backfill-meta --force
 */
$onplay_cli_backfill = function ( $args, $assoc_args ) {
	$force   = isset( $assoc_args['force'] );
	$dry_run = isset( $assoc_args['dry-run'] );
	$limit   = isset( $assoc_args['limit'] ) ? (int) $assoc_args['limit'] : 0;

	$ids = get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'orderby'        => 'ID',
			'order'          => 'ASC',
		)
	);
	if ( $limit > 0 ) {
		$ids = array_slice( $ids, 0, $limit );
	}

	$total = count( $ids );
	WP_CLI::log(
		sprintf(
			'Procesando %d productos (force=%s, dry-run=%s).',
			$total,
			$force ? 'yes' : 'no',
			$dry_run ? 'yes' : 'no'
		)
	);

	$progress = \WP_CLI\Utils\make_progress_bar( 'Enriqueciendo', $total );
	$ok       = 0;
	$skip     = 0;
	$err      = 0;
	$errors   = array();

	foreach ( $ids as $pid ) {
		if ( $dry_run ) {
			$progress->tick();
			continue;
		}

		if ( ! $force ) {
			$cached_at = get_post_meta( $pid, '_onplay_scryfall_cached_at', true );
			if ( $cached_at ) {
				$skip++;
				$progress->tick();
				continue;
			}
		}

		$res = onplay_enrich_from_scryfall( (int) $pid, $force );
		if ( ! empty( $res['ok'] ) ) {
			$ok++;
		} else {
			$err++;
			$errors[] = '#' . $pid . ': ' . ( isset( $res['message'] ) ? $res['message'] : 'unknown' );
		}

		// Scryfall pide 50-100 ms entre requests; usamos 120 ms para margen.
		usleep( 120000 );
		$progress->tick();
	}
	$progress->finish();

	WP_CLI::success( sprintf( 'ok=%d skip=%d err=%d total=%d', $ok, $skip, $err, $total ) );
	if ( ! empty( $errors ) ) {
		foreach ( array_slice( $errors, 0, 20 ) as $e ) {
			WP_CLI::warning( $e );
		}
		if ( count( $errors ) > 20 ) {
			WP_CLI::log( sprintf( '... y %d errores más', count( $errors ) - 20 ) );
		}
	}
};
WP_CLI::add_command( 'onplay:backfill-meta', $onplay_cli_backfill );

/**
 * Resync tcg_* taxonomies from existing _onplay_* meta (no Scryfall fetch).
 *
 * ## EXAMPLES
 *
 *     wp onplay:resync-taxonomies
 */
$onplay_cli_resync = function () {
	$ids = get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);
	$total    = count( $ids );
	$progress = \WP_CLI\Utils\make_progress_bar( 'Resync taxonomías', $total );
	foreach ( $ids as $pid ) {
		onplay_sync_taxonomies_from_meta( (int) $pid );
		$progress->tick();
	}
	$progress->finish();
	WP_CLI::success( sprintf( 'Sincronizadas %d productos.', $total ) );
};
WP_CLI::add_command( 'onplay:resync-taxonomies', $onplay_cli_resync );

/**
 * Audit products: how many lack _onplay_scryfall_cached_at or tcg_color term.
 *
 * ## EXAMPLES
 *
 *     wp onplay:audit-enrichment
 */
$onplay_cli_audit = function () {
	global $wpdb;

	$total = (int) $wpdb->get_var(
		"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='product' AND post_status='publish'"
	);
	$with_cache = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key=%s",
			'_onplay_scryfall_cached_at'
		)
	);
	$with_color = (int) $wpdb->get_var(
		"SELECT COUNT(DISTINCT tr.object_id) FROM {$wpdb->term_relationships} tr
		 JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id=tr.term_taxonomy_id
		 WHERE tt.taxonomy='tcg_color'"
	);
	WP_CLI::log( sprintf( 'Productos publish:          %d', $total ) );
	WP_CLI::log( sprintf( 'Con _onplay_*_cached_at:    %d', $with_cache ) );
	WP_CLI::log( sprintf( 'Con término tcg_color:      %d', $with_color ) );
	WP_CLI::success( sprintf( 'Cobertura: cache=%d%% color=%d%%',
		$total > 0 ? round( $with_cache * 100 / $total ) : 0,
		$total > 0 ? round( $with_color * 100 / $total ) : 0
	) );
};
WP_CLI::add_command( 'onplay:audit-enrichment', $onplay_cli_audit );

/**
 * Seed de páginas institucionales (legales + empresa).
 *
 * Crea las páginas definidas en `onplay_pages_catalog()` con contenido
 * placeholder Gutenberg y meta `_onplay_page_subtitle`. Idempotente: si una
 * página con ese slug ya existe, la omite (salvo que se pase `--force`).
 *
 * ## OPTIONS
 *
 * [--force]
 * : Reemplazar el contenido de páginas existentes. **Destructivo** — úsalo solo
 *   si sabes que la página no tiene contenido real todavía.
 *
 * [--dry-run]
 * : No crear/actualizar nada; solo reportar qué haría.
 *
 * ## EXAMPLES
 *
 *     wp onplay:seed-pages --dry-run
 *     wp onplay:seed-pages
 *     wp onplay:seed-pages --force
 */
$onplay_cli_seed_pages = function ( $args, $assoc_args ) {
	$force   = isset( $assoc_args['force'] );
	$dry_run = isset( $assoc_args['dry-run'] );

	if ( ! function_exists( 'onplay_pages_catalog' ) ) {
		WP_CLI::error( 'inc/pages.php no cargado.' );
		return;
	}

	$catalog = onplay_pages_catalog();
	$total   = count( $catalog );
	WP_CLI::log(
		sprintf(
			'Seed de %d páginas (force=%s, dry-run=%s).',
			$total,
			$force ? 'yes' : 'no',
			$dry_run ? 'yes' : 'no'
		)
	);

	$created = 0;
	$updated = 0;
	$skipped = 0;

	foreach ( $catalog as $slug => $entry ) {
		$existing = get_page_by_path( $slug, OBJECT, 'page' );
		$content  = onplay_pages_render_placeholder( $entry );

		if ( $existing ) {
			if ( ! $force ) {
				WP_CLI::log( sprintf( '  skip  %-28s #%d (ya existe)', $slug, $existing->ID ) );
				$skipped++;
				continue;
			}

			if ( $dry_run ) {
				WP_CLI::log( sprintf( '  would-update %-20s #%d', $slug, $existing->ID ) );
				$updated++;
				continue;
			}

			$res = wp_update_post(
				array(
					'ID'           => $existing->ID,
					'post_title'   => $entry['title'],
					'post_content' => $content,
					'post_status'  => 'publish',
				),
				true
			);
			if ( is_wp_error( $res ) ) {
				WP_CLI::warning( sprintf( '  error  %s: %s', $slug, $res->get_error_message() ) );
				continue;
			}
			update_post_meta( $existing->ID, '_onplay_page_subtitle', $entry['subtitle'] );
			update_post_meta( $existing->ID, '_onplay_page_section', $entry['section'] );
			WP_CLI::log( sprintf( '  updated %-27s #%d', $slug, $existing->ID ) );
			$updated++;
			continue;
		}

		if ( $dry_run ) {
			WP_CLI::log( sprintf( '  would-create %-20s (%s)', $slug, $entry['section'] ) );
			$created++;
			continue;
		}

		$pid = wp_insert_post(
			array(
				'post_title'   => $entry['title'],
				'post_name'    => $slug,
				'post_content' => $content,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			),
			true
		);
		if ( is_wp_error( $pid ) ) {
			WP_CLI::warning( sprintf( '  error  %s: %s', $slug, $pid->get_error_message() ) );
			continue;
		}
		update_post_meta( $pid, '_onplay_page_subtitle', $entry['subtitle'] );
		update_post_meta( $pid, '_onplay_page_section', $entry['section'] );
		WP_CLI::log( sprintf( '  created %-27s #%d', $slug, $pid ) );
		$created++;
	}

	WP_CLI::success(
		sprintf(
			'created=%d updated=%d skipped=%d total=%d',
			$created,
			$updated,
			$skipped,
			$total
		)
	);
};
WP_CLI::add_command( 'onplay:seed-pages', $onplay_cli_seed_pages );
