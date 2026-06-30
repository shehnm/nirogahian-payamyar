<?php
/**
 * ابزار تشخیص موقت — بعد از استفاده حتما پاک شود.
 * نمایش:   nirogahian.ir/nirogahian-diag.php?k=peecha-2026
 * خالی‌کردن لاگ‌ها: nirogahian.ir/nirogahian-diag.php?k=peecha-2026&truncate=1
 */
$key = 'peecha-2026';
if ( ! isset( $_GET['k'] ) || $_GET['k'] !== $key ) {
	http_response_code( 404 );
	exit;
}

header( 'Content-Type: text/plain; charset=utf-8' );

$logs = array(
	__DIR__ . '/wp-content/debug.log',
	__DIR__ . '/wp-content/py-profiler.log',
);

if ( isset( $_GET['truncate'] ) ) {
	foreach ( $logs as $log ) {
		if ( is_file( $log ) ) {
			$fh = fopen( $log, 'w' );
			if ( $fh ) {
				fclose( $fh );
			}
			echo 'truncated: ' . basename( $log ) . ' -> ' . filesize( $log ) . " bytes\n";
		}
	}
	echo "\nحالا یک‌بار صفحهٔ پیام‌یار را باز کن، بعد همین آدرس را بدون truncate باز کن.\n";
	exit;
}

echo "== PHP ==\n";
echo 'php_version      = ' . PHP_VERSION . "\n";
echo 'memory_limit     = ' . ini_get( 'memory_limit' ) . "\n";
echo 'max_execution    = ' . ini_get( 'max_execution_time' ) . "\n";
echo 'set_time_limit   = ';
echo ( @set_time_limit( 120 ) ? 'OK' : 'BLOCKED' ) . "\n";

foreach ( $logs as $log ) {
	echo "\n== TAIL " . basename( $log ) . " ==\n";
	if ( ! is_file( $log ) ) {
		echo "(not found)\n";
		continue;
	}
	$size = filesize( $log );
	echo 'size = ' . round( $size / 1048576, 2 ) . " MB\n\n";
	$want = 60 * 1024;
	$fh   = fopen( $log, 'rb' );
	if ( $fh ) {
		if ( $size > $want ) {
			fseek( $fh, -$want, SEEK_END );
			fgets( $fh );
		}
		echo stream_get_contents( $fh );
		fclose( $fh );
	}
}
