<?php
/**
 * Payamyar mu-plugin cap — v6 phase 2 candidate.
 *
 * Deploy only on staging first. Replace STABLE on production after TEST-CHECKLIST passes.
 *
 * Changes vs STABLE-4624:
 * - All categories: number=0, pad_counts=false, transient cache (no HARD_CUTOFF terms)
 * - Auto-publish safe: soft post cap only (no posts_pre_query => [])
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'PY_V6_LOADED' ) ) {
	return;
}
define( 'PY_V6_LOADED', true );

if ( function_exists( 'py_log' ) || function_exists( 'py_is_from_payamyar' ) ) {
	return;
}

define( 'PY_V6_LOG_FILE', WP_CONTENT_DIR . '/py-profiler.log' );
define( 'PY_V6_CAT_TRANSIENT', 'py_v6_all_categories' );
define( 'PY_V6_CAT_TTL', 12 * HOUR_IN_SECONDS );

function py_v6_log( $m ) {
	@error_log( '[' . date( 'Y-m-d H:i:s' ) . '] v6 ' . $m . PHP_EOL, 3, PY_V6_LOG_FILE );
}

function py_v6_from_payamyar() {
	foreach ( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 40 ) as $f ) {
		if ( ! empty( $f['file'] ) && strpos( $f['file'], 'wp-content/plugins/wp-payamyar' ) !== false ) {
			return true;
		}
	}
	return false;
}

function py_v6_get_cached_terms() {
	static $memory = null;

	if ( is_array( $memory ) ) {
		return $memory;
	}

	$cached = get_transient( PY_V6_CAT_TRANSIENT );
	if ( is_array( $cached ) ) {
		$memory = $cached;
		return $cached;
	}

	return null;
}

function py_v6_store_terms( $terms ) {
	static $memory = null;

	if ( ! is_array( $terms ) || empty( $terms ) ) {
		return;
	}

	$memory = $terms;
	set_transient( PY_V6_CAT_TRANSIENT, $terms, PY_V6_CAT_TTL );
	py_v6_log( 'terms stored count=' . count( $terms ) );
}

function py_v6_bust_category_cache() {
	delete_transient( PY_V6_CAT_TRANSIENT );
	py_v6_log( 'category cache busted' );
}

add_action(
	'admin_init',
	function () {
		if ( isset( $_GET['page'] ) && 'wp_payamyar_admin_page' === $_GET['page'] ) {
			py_v6_bust_category_cache();
		}

		add_filter(
			'get_terms_args',
			function ( $args, $taxonomies ) {
				if ( ! is_admin() || ! py_v6_from_payamyar() ) {
					return $args;
				}

				$taxonomies = (array) $taxonomies;
				if ( ! in_array( 'category', $taxonomies, true ) ) {
					return $args;
				}

				if ( is_array( py_v6_get_cached_terms() ) && ! empty( py_v6_get_cached_terms() ) ) {
					return $args;
				}

				$args['hide_empty']             = false;
				$args['number']                 = 0;
				$args['orderby']                = 'name';
				$args['order']                  = 'ASC';
				$args['pad_counts']             = false;
				$args['update_term_meta_cache'] = false;
				$args['cache_results']          = true;

				py_v6_log( 'terms query start number=0 pad_counts=false' );
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

				$taxonomies = (array) $taxonomies;
				if ( ! in_array( 'category', $taxonomies, true ) ) {
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
				static $serving = false;

				if ( $serving || ! is_admin() || ! py_v6_from_payamyar() ) {
					return $null;
				}

				$taxonomies = (array) $taxonomies;
				if ( ! in_array( 'category', $taxonomies, true ) ) {
					return $null;
				}

				$cached = py_v6_get_cached_terms();
				if ( ! is_array( $cached ) ) {
					return $null;
				}

				$serving = true;
				py_v6_log( 'cache hit count=' . count( $cached ) );
				$serving = false;

				return $cached;
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

				$pt = $q->get( 'post_type' );
				$mk = $q->get( 'meta_key' );
				$pp = $q->get( 'posts_per_page' );

				$is_target_meta = in_array( $mk, array( 'kw_published_in_rubika', 'kw_published_in_eitaa' ), true );
				$is_unbounded   = ( $pp === -1 || $pp === '-1' || empty( $pp ) || ( is_numeric( $pp ) && (int) $pp > 200 ) );

				if ( 'post' !== $pt || ( ! $is_target_meta && ! $is_unbounded ) ) {
					return;
				}

				$q->set( 'posts_per_page', 200 );
				$q->set( 'fields', 'ids' );
				$q->set( 'no_found_rows', true );
				$q->set( 'update_post_meta_cache', false );
				$q->set( 'update_post_term_cache', false );
				$q->set( 'post_status', array( 'publish' ) );
				$q->set( 'ignore_sticky_posts', true );
				py_v6_log( "cap posts pt={$pt} mk={$mk} ppp=200 fields=ids" );
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
					$uq->query_vars['number'] = 200;
					py_v6_log( 'cap users number=200' );
				}
			},
			999
		);
	},
	1
);

py_v6_log( 'loaded' );
