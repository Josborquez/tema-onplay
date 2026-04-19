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
