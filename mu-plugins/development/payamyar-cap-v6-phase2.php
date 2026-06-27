<?php
/**
 * Payamyar phase-2 cap — v6d
 *
 * دو کار:
 * ۱) همه ~۷۰۰ دسته در UI پیام‌یار بدون timeout (سبک‌کردن کوئری دسته در admin).
 * ۲) رفع Fatal ارسال خودکار با چند روبات هم‌زمان (باگ PHP8 خود افزونه).
 *
 * باگ ارسال: تابع kw_get_category_id_list افزونه، get_the_terms را با
 * آرایه به‌جای رشته برای taxonomy صدا می‌زند. روبات اول کش را زیر کلید جعلی
 * «Array_relationships» می‌ریزد؛ روبات دوم از کش WP_Error می‌گیرد ->
 * wp_list_pluck = null -> array_intersect(null,...) -> Fatal.
 * فیکس: هر وقت taxonomy آرایه بود، با رشتهٔ درست دوباره می‌گیریم.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'PY_V6_LOADED' ) ) {
	return;
}
define( 'PY_V6_LOADED', true );

function py_v6_from_payamyar() {
	foreach ( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 30 ) as $f ) {
		if ( ! empty( $f['file'] ) && strpos( $f['file'], 'wp-content/plugins/wp-payamyar' ) !== false ) {
			return true;
		}
	}
	return false;
}

// فیکس ارسال خودکار — همه‌جا فعال (publish ممکن است خارج از admin هم رخ دهد).
add_filter(
	'get_the_terms',
	function ( $terms, $post_id, $taxonomy ) {
		if ( ! is_array( $taxonomy ) ) {
			return $terms;
		}
		$results = array();
		foreach ( $taxonomy as $tax ) {
			$t = get_the_terms( $post_id, (string) $tax );
			if ( is_array( $t ) ) {
				$results = array_merge( $results, $t );
			}
		}
		return $results;
	},
	1,
	3
);

add_action(
	'admin_init',
	function () {

		if ( isset( $_GET['page'] ) && 'wp_payamyar_admin_page' === $_GET['page'] ) {
			if ( function_exists( 'wp_raise_memory_limit' ) ) {
				wp_raise_memory_limit( 'admin' );
			}
			@set_time_limit( 120 );
		}

		// کوئری دسته را سبک کن تا ۷۰۰ دسته بدون timeout لود شود.
		add_filter(
			'get_terms_args',
			function ( $args, $taxonomies ) {
				if ( ! is_admin() || ! py_v6_from_payamyar() ) {
					return $args;
				}
				if ( ! in_array( 'category', (array) $taxonomies, true ) ) {
					return $args;
				}
				$args['pad_counts']             = false;
				$args['update_term_meta_cache'] = false;
				return $args;
			},
			999,
			2
		);

		// کوئری‌های سنگین پست را محدود کن، ولی هرگز آرایه خالی برنگردان.
		add_action(
			'pre_get_posts',
			function ( $q ) {
				if ( ! is_admin() || ! py_v6_from_payamyar() ) {
					return;
				}
				$pp = $q->get( 'posts_per_page' );
				if ( $pp === -1 || $pp === '-1' || empty( $pp ) || ( is_numeric( $pp ) && (int) $pp > 300 ) ) {
					$q->set( 'posts_per_page', 300 );
					$q->set( 'no_found_rows', true );
					$q->set( 'update_post_meta_cache', false );
					$q->set( 'update_post_term_cache', false );
					$q->set( 'ignore_sticky_posts', true );
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
	},
	1
);
