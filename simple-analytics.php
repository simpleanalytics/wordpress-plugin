<?php
/*
 * Plugin Name: Simple Analytics Official Plugin
 * Version: 1.2
 * Plugin URI: https://docs.simpleanalytics.com/install-simple-analytics-on-wordpress
 * Description: Embed Simple Analytics script in your WordPress website
 * Author: Simple Analytics
 * Author URI: https://simpleanalytics.com/
 * Requires at least: 2.0.0
 * Tested up to: 5.2
 *
 * Text Domain: simple-analytics
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Simple Analytics
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function inject_header_simple_analytics() {
  if (current_user_can('editor') || current_user_can('administrator')) {
    echo '<script>console.warn(\'Simple Analytics: Not logging requests from admins.\')</script>';
  } else {
    echo '<script async src="https://cdn.simpleanalytics.io/hello.js"></script>';
  }
}

function inject_footer_simple_analytics() {
  if (current_user_can('editor') || current_user_can('administrator')) {
    // Nothing...
  } else {
    echo '<noscript><img src="https://api.simpleanalytics.io/hello.gif" alt=""></noscript>';
  }
}

add_action('wp_head', 'inject_header_simple_analytics', 10);

add_action('wp_footer', 'inject_footer_simple_analytics', 10);
