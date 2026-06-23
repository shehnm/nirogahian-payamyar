<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'PY_HARD_CUTOFF' ) ) {
	define( 'PY_HARD_CUTOFF', false );
}

if ( ! defined( 'PY_LOG' ) ) {
	define( 'PY_LOG', WP_CONTENT_DIR . '/py-profiler.log' );
}

function py_log( $m ) {
	@error_log( '[' . date( 'Y-m-d H:i:s' ) . '] ' . $m . PHP_EOL, 3, PY_LOG );
}

function py_is_from_payamyar() {
	$bt = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 60 );
	foreach ( $bt as $f ) {
		if ( ! empty( $f['file'] ) && strpos( $f['file'], 'wp-content/plugins/wp-payamyar' ) !== false ) {
			return true;
		}
	}
	return false;
}

add_action(
	'admin_init',
	function () {

		if ( isset( $_GET['page'] ) && 'wp_payamyar_admin_page' === $_GET['page'] ) {
			wp_cache_delete( 'categories', 'py_payamyar_cap' );
		}

		$GLOBALS['py_terms_calls'] = 0;
		$GLOBALS['py_posts_calls'] = 0;
		$GLOBALS['py_users_calls'] = 0;

		add_filter(
			'get_terms',
			function ( $terms, $taxonomies, $args ) {
				if ( ! is_admin() || ! py_is_from_payamyar() || is_wp_error( $terms ) ) {
					return $terms;
				}
				if ( ! in_array( 'category', (array) $taxonomies, true ) ) {
					return $terms;
				}
				if ( is_array( $terms ) && ! empty( $terms ) ) {
					wp_cache_set( 'categories', $terms, 'py_payamyar_cap' );
					py_log( 'CACHE_STORE terms count=' . count( $terms ) );
				}
				return $terms;
			},
			999,
			3
		);

		add_action(
			'pre_get_posts',
			function ( $q ) {
				if ( ! is_admin() || ! py_is_from_payamyar() ) {
					return;
				}

				$pt = $q->get( 'post_type' );
				$mk = $q->get( 'meta_key' );
				$pp = $q->get( 'posts_per_page' );

				$is_target_meta = in_array( $mk, array( 'kw_published_in_rubika', 'kw_published_in_eitaa' ), true );
				$is_unbounded   = ( $pp === -1 || $pp === '-1' || empty( $pp ) || ( is_numeric( $pp ) && (int) $pp > 500 ) );

				if ( $pt === 'post' && ( $is_target_meta || $is_unbounded ) ) {
					$GLOBALS['py_posts_calls']++;

					if ( PY_HARD_CUTOFF || $GLOBALS['py_posts_calls'] > 3 ) {
						add_filter(
							'posts_pre_query',
							function ( $null ) use ( $pt, $mk ) {
								py_log( "HARD_CUTOFF posts pt={$pt} mk={$mk}" );
								return array();
							},
							9999
						);
						return;
					}

					$q->set( 'posts_per_page', 100 );
					$q->set( 'fields', 'ids' );
					$q->set( 'no_found_rows', true );
					$q->set( 'update_post_meta_cache', false );
					$q->set( 'update_post_term_cache', false );
					$q->set( 'post_status', array( 'publish' ) );
					$q->set( 'ignore_sticky_posts', true );
					py_log( "CAP posts pt={$pt} mk={$mk}" );
				}
			},
			999
		);

		add_filter(
			'get_terms_args',
			function ( $args, $taxonomies ) {
				if ( ! is_admin() || ! py_is_from_payamyar() ) {
					return $args;
				}

				$taxonomies = (array) $taxonomies;
				if ( ! in_array( 'category', $taxonomies, true ) ) {
					return $args;
				}

				$GLOBALS['py_terms_calls']++;

				if ( PY_HARD_CUTOFF || $GLOBALS['py_terms_calls'] > 3 ) {
					add_filter(
						'terms_pre_query',
						function ( $null ) {
							$cached = wp_cache_get( 'categories', 'py_payamyar_cap' );
							if ( is_array( $cached ) && ! empty( $cached ) ) {
								py_log( 'CACHE_HIT terms count=' . count( $cached ) );
								return $cached;
							}
							if ( is_array( $cached ) && empty( $cached ) ) {
								wp_cache_delete( 'categories', 'py_payamyar_cap' );
							}
							py_log( 'HARD_CUTOFF terms empty-fallback' );
							return array();
						},
						9999
					);
					return $args;
				}

				$n = isset( $args['number'] ) ? (int) $args['number'] : 0;
				if ( $n <= 0 || $n > 500 ) {
					$args['number'] = 100;
				}

				$args['hide_empty']             = false;
				$args['orderby']                = 'name';
				$args['order']                  = 'ASC';
				$args['cache_results']          = false;
				$args['update_term_meta_cache'] = false;

				py_log( 'CAP terms number=' . $args['number'] );
				return $args;
			},
			999,
			2
		);

		add_action(
			'pre_user_query',
			function ( $uq ) {
				if ( ! is_admin() || ! py_is_from_payamyar() ) {
					return;
				}

				$GLOBALS['py_users_calls']++;
				$n = isset( $uq->query_vars['number'] ) ? (int) $uq->query_vars['number'] : 0;

				if ( PY_HARD_CUTOFF || $GLOBALS['py_users_calls'] > 3 ) {
					$uq->query_vars['number'] = 1;
					py_log( 'HARD_LIMIT users number=1' );
					return;
				}

				if ( $n <= 0 || $n > 500 ) {
					$uq->query_vars['number'] = 100;
					py_log( 'CAP users number=100' );
				}
			},
			999
		);

	},
	1
);
