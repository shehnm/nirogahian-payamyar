<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'PY_V5C_LOADED' ) ) {
	return;
}
define( 'PY_V5C_LOADED', true );

if ( function_exists( 'py_log' ) || function_exists( 'py_is_from_payamyar' ) ) {
	return;
}

define( 'PY_V5_LOG_FILE', WP_CONTENT_DIR . '/py-profiler.log' );
define( 'PY_V5_CAT_TRANSIENT', 'py_v5_all_categories' );

function py_v5_log( $m ) {
	@error_log( '[' . date( 'Y-m-d H:i:s' ) . '] ' . $m . PHP_EOL, 3, PY_V5_LOG_FILE );
}

function py_v5_from_payamyar() {
	foreach ( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 40 ) as $f ) {
		if ( ! empty( $f['file'] ) && strpos( $f['file'], 'wp-content/plugins/wp-payamyar' ) !== false ) {
			return true;
		}
	}
	return false;
}

function py_v5_get_cached_terms() {
	static $memory = null;

	if ( is_array( $memory ) && ! empty( $memory ) ) {
		return $memory;
	}

	$cached = get_transient( PY_V5_CAT_TRANSIENT );
	if ( is_array( $cached ) && ! empty( $cached ) ) {
		$memory = $cached;
		return $cached;
	}

	return null;
}

function py_v5_store_terms( $terms ) {
	static $memory = null;

	if ( ! is_array( $terms ) || empty( $terms ) ) {
		return;
	}

	$memory = $terms;
	set_transient( PY_V5_CAT_TRANSIENT, $terms, 12 * HOUR_IN_SECONDS );
	py_v5_log( 'v5c terms stored count=' . count( $terms ) );
}

add_filter(
	'get_terms_args',
	function ( $args, $taxonomies ) {
		if ( ! is_admin() || ! py_v5_from_payamyar() ) {
			return $args;
		}
		$taxonomies = (array) $taxonomies;
		if ( ! in_array( 'category', $taxonomies, true ) ) {
			return $args;
		}

		if ( is_array( py_v5_get_cached_terms() ) ) {
			return $args;
		}

		$args['hide_empty']             = false;
		$args['number']                 = 0;
		$args['orderby']                = 'name';
		$args['order']                  = 'ASC';
		$args['pad_counts']             = false;
		$args['update_term_meta_cache'] = false;
		$args['cache_results']          = true;

		py_v5_log( 'v5c terms query start' );
		return $args;
	},
	999,
	2
);

add_filter(
	'get_terms',
	function ( $terms, $taxonomies, $args ) {
		if ( ! is_admin() || ! py_v5_from_payamyar() || is_wp_error( $terms ) ) {
			return $terms;
		}
		$taxonomies = (array) $taxonomies;
		if ( ! in_array( 'category', $taxonomies, true ) ) {
			return $terms;
		}
		if ( is_array( $terms ) && ! empty( $terms ) ) {
			py_v5_store_terms( $terms );
		}
		return $terms;
	},
	999,
	3
);

add_filter(
	'terms_pre_query',
	function ( $null, $query, $taxonomies ) {
		if ( ! is_admin() || ! py_v5_from_payamyar() ) {
			return $null;
		}
		$taxonomies = (array) $taxonomies;
		if ( ! in_array( 'category', $taxonomies, true ) ) {
			return $null;
		}

		$cached = py_v5_get_cached_terms();
		if ( is_array( $cached ) ) {
			py_v5_log( 'v5c cache hit count=' . count( $cached ) );
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
		if ( ! is_admin() || ! py_v5_from_payamyar() ) {
			return;
		}
		$pp = $q->get( 'posts_per_page' );
		if ( $pp === -1 || $pp === '-1' || ( is_numeric( $pp ) && (int) $pp > 200 ) ) {
			$q->set( 'posts_per_page', 200 );
			$q->set( 'fields', 'ids' );
			$q->set( 'no_found_rows', true );
			$q->set( 'update_post_meta_cache', false );
			$q->set( 'update_post_term_cache', false );
			$q->set( 'ignore_sticky_posts', true );
			py_v5_log( 'v5c cap posts ppp=200' );
		}
	},
	999
);
