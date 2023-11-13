<?php
/*
 * Plugin Name: Simple Analytics Official
 * Version: 1.16
 * Plugin URI: https://docs.simpleanalytics.com/install-simple-analytics-on-wordpress
 * Description: Embed Simple Analytics script in your WordPress website
 * Author: Simple Analytics
 * Author URI: https://simpleanalytics.com/
 * Requires at least: 2.0.0
 * Tested up to: 6.4.1
 *
 * Text Domain: simple-analytics
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Simple Analytics
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function simpleanalytics_noscript() {
	$domain = get_option( 'simpleanalytics_custom_domain' ) ?: 'queue.simpleanalyticscdn.com';

	echo '<noscript><img src="https://' . $domain . '/noscript.gif" alt="" referrerpolicy="no-referrer-when-downgrade"></noscript>' . "\n";
}

function simpleanalytics_init() {
	if ( current_user_can( 'editor' ) || current_user_can( 'administrator' ) ) {
		require_once plugin_dir_path( __FILE__ ) . 'includes/admin.php';
	} else {
		$domain = get_option( 'simpleanalytics_custom_domain' ) ?: 'scripts.simpleanalyticscdn.com';

		wp_enqueue_script( 'simpleanalytics_script', "https://$domain/latest.js", array(), null, true );
		add_action( 'wp_footer', 'simpleanalytics_noscript', 10 );
	}
}

add_action( 'init', 'simpleanalytics_init' );
