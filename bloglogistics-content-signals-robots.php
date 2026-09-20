<?php
/**
 * Plugin Name:       BlogLogistics Content Signals for Robots.txt
 * Plugin URI:        https://github.com/bloglogisticsdev/bloglogistics-content-signals-robots
 * Description:       Safely manages website-use preference signals in a physical robots.txt file.
 * Version:           1.2.0
 * Requires at least: 7.0
 * Requires PHP:      8.3
 * Author:            BlogLogistics
 * Author URI:        https://www.bloglogistics.com/
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Update URI:        https://github.com/bloglogisticsdev/bloglogistics-content-signals-robots
 * Text Domain:       bloglogistics-content-signals-robots
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BLOGLOGISTICS_CSR_VERSION', '1.2.0' );
define( 'BLOGLOGISTICS_CSR_SLUG', 'bloglogistics-content-signals-robots' );
define( 'BLOGLOGISTICS_CSR_FILE', __FILE__ );
define( 'BLOGLOGISTICS_CSR_DIR', plugin_dir_path( __FILE__ ) );
define( 'BLOGLOGISTICS_CSR_REPO_URL', 'https://github.com/bloglogisticsdev/bloglogistics-content-signals-robots/' );
define( 'BLOGLOGISTICS_CSR_UPDATE_MANIFEST_URL', 'https://updates.bloglogistics.com/plugins/bloglogistics-content-signals-robots.json' );

$bloglogistics_csr_puc = BLOGLOGISTICS_CSR_DIR . 'vendor/plugin-update-checker/plugin-update-checker.php';

if ( file_exists( $bloglogistics_csr_puc ) ) {
	if ( ! class_exists( '\\YahnisElsts\\PluginUpdateChecker\\v5\\PucFactory', false ) ) {
		require_once $bloglogistics_csr_puc;
	}

	require_once BLOGLOGISTICS_CSR_DIR . 'includes/class-bloglogistics-content-signals-robots-updater.php';

	if ( class_exists( 'BlogLogistics_Content_Signals_Robots_Updater', false ) ) {
		BlogLogistics_Content_Signals_Robots_Updater::init(
			array(
				'repo_url'    => BLOGLOGISTICS_CSR_UPDATE_MANIFEST_URL,
				'plugin_file' => BLOGLOGISTICS_CSR_FILE,
				'slug'        => BLOGLOGISTICS_CSR_SLUG,
			)
		);
	}
}

/**
 * Load bundled translations.
 */
function bloglogistics_csr_load_textdomain(): void {
	load_plugin_textdomain(
		'bloglogistics-content-signals-robots',
		false,
		dirname( plugin_basename( BLOGLOGISTICS_CSR_FILE ) ) . '/languages'
	);
}
add_action( 'plugins_loaded', 'bloglogistics_csr_load_textdomain' );

require_once BLOGLOGISTICS_CSR_DIR . 'includes/class-bloglogistics-content-signals-robots.php';

register_activation_hook( BLOGLOGISTICS_CSR_FILE, array( 'BlogLogistics_Content_Signals_Robots', 'activate' ) );

new BlogLogistics_Content_Signals_Robots();
