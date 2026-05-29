<?php
/**
 * Uninstall cleanup for BlogLogistics Content Signals for Robots.txt.
 *
 * Removes plugin settings and plugin-created backup files.
 * The current robots.txt file is left as-is.
 *
 * @package BlogLogistics_Content_Signals_Robots
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'bloglogistics_csr_options' );

$robots_path = trailingslashit( ABSPATH ) . 'robots.txt';
$patterns    = array(
	$robots_path . '.bloglogistics-content-signals-backup-*',
	$robots_path . '.bloglogistics-backup-*',
);

foreach ( $patterns as $pattern ) {
	$files = glob( $pattern );

	if ( false === $files ) {
		continue;
	}

	foreach ( $files as $file ) {
		if ( is_file( $file ) ) {
			@unlink( $file );
		}
	}
}
