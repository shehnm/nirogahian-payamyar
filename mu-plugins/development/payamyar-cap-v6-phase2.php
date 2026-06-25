<?php
/**
 * Payamyar phase-2 cap — v6
 * Goal: all categories + auto-publish (no HARD_CUTOFF empty queries).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'PY_V6_LOADED' ) ) {
	return;
}
define( 'PY_V6_LOADED', true );

define( 'PY_V6_LOG', WP_CONTENT_DIR . '/py-profiler.log' );
define( 'PY_V6_CAT_TRANSIENT', 'py_v6_all_categories' );

function py_v6_log( $m ) {
	@error_log( '[' . date( 'Y-m-d H:i:s' ) . '] v6 ' . $m . PHP_EOL, 3, PY_V6_LOG );
}

function py_v6_from_payamyar() {
	foreach ( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 40 ) as $f ) {
		if ( ! empty( $f['file'] ) && strpos( $f['file'], 'wp-content/plugins/wp-payamyar' ) !== false ) {
			return true;
		}
	}
	return false;
}

function py_v6_cached_terms() {
	static $mem = null;

	if ( is_array( $mem ) && ! empty( $mem ) ) {
		return $mem;
	}

	$t = get_transient( PY_V6_CAT_TRANSIENT );
	if ( is_array( $t ) && ! empty( $t ) ) {
		$mem = $t;
		return $t;
	}

	return null;
}

function py_v6_store_terms( $terms ) {
	static $mem = null;

	if ( ! is_array( $terms ) || empty( $terms ) ) {
		return;
	}

	$mem = $terms;
	set_transient( PY_V6_CAT_TRANSIENT, $terms, DAY_IN_SECONDS );
	py_v6_log( 'terms stored count=' . count( $terms ) );
}

add_action(
	'admin_init',
	function () {
		if ( ! isset( $_GET['page'] ) || 'wp_payamyar_admin_page' !== $_GET['page'] ) {
			return;
		}
		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 180 );
		}
	},
	1
);

add_filter(
	'get_terms_args',
	function ( $args, $taxonomies ) {
		if ( ! is_admin() || ! py_v6_from_payamyar() ) {
			return $args;
		}
		if ( ! in_array( 'category', (array) $taxonomies, true ) ) {
			return $args;
		}
		if ( is_array( py_v6_cached_terms() ) ) {
			return $args;
		}

		$args['taxonomy']               = 'category';
		$args['hide_empty']             = false;
		$args['number']                 = 0;
		$args['orderby']                = 'name';
		$args['order']                  = 'ASC';
		$args['pad_counts']             = false;
		$args['update_term_meta_cache'] = false;
		$args['cache_results']          = true;

		py_v6_log( 'terms query all' );
		return $args;
	},
	999,
	2
);

add_filter(
	'get_terms',
	function ( $terms, $taxonomies, $args ) {
		if ( ! is_admin() || ! py_v6_from_payamyar() || is_wp_error( $terms ) ) {
			return $terms;
		}
		if ( ! in_array( 'category', (array) $taxonomies, true ) ) {
			return $terms;
		}
		if ( is_array( $terms ) && ! empty( $terms ) ) {
			py_v6_store_terms( $terms );
		}
		return $terms;
	},
	999,
	3
);

add_filter(
	'terms_pre_query',
	function ( $null, $query, $taxonomies ) {
		if ( ! is_admin() || ! py_v6_from_payamyar() ) {
			return $null;
		}
		if ( ! in_array( 'category', (array) $taxonomies, true ) ) {
			return $null;
		}

		$cached = py_v6_cached_terms();
		if ( is_array( $cached ) ) {
			py_v6_log( 'terms cache hit count=' . count( $cached ) );
			return $cached;
		}

		return $null;
	},
	10,
	3
);

add_action(
	'pre_get_posts',
	function ( $q ) {
		if ( ! is_admin() || ! py_v6_from_payamyar() ) {
			return;
		}

		$pp = $q->get( 'posts_per_page' );
		if ( $pp === -1 || $pp === '-1' || empty( $pp ) || ( is_numeric( $pp ) && (int) $pp > 200 ) ) {
			$q->set( 'posts_per_page', 200 );
			$q->set( 'fields', 'ids' );
			$q->set( 'no_found_rows', true );
			$q->set( 'update_post_meta_cache', false );
			$q->set( 'update_post_term_cache', false );
			$q->set( 'ignore_sticky_posts', true );
			py_v6_log( 'posts cap 200 ids' );
		}
	},
	999
);

add_action(
	'pre_user_query',
	function ( $uq ) {
		if ( ! is_admin() || ! py_v6_from_payamyar() ) {
			return;
		}
		$n = isset( $uq->query_vars['number'] ) ? (int) $uq->query_vars['number'] : 0;
		if ( $n <= 0 || $n > 200 ) {
			$uq->query_vars['number'] = 100;
		}
	},
	999
);
