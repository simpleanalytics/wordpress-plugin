<?php
/*
 * Plugin Name: Simple Analytics
 * Version: 1.0
 * Plugin URI: https://simpleanalytics.com/
 * Description: Embed Simple Analytics script in your WordPress website
 * Author: Adriaan van Rossum
 * Author URI: https://simpleanalytics.com/
 * Requires at least: 1.0
 * Tested up to: 5.2
 *
 * Text Domain: simple-analytics
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Adriaan van Rossum
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function inject_header_simple_analytics() { echo '<script async src="https://cdn.simpleanalytics.io/hello.js"></script>'; }

function inject_footer_simple_analytics() { echo '<noscript><img src="https://api.simpleanalytics.io/hello.gif" alt=""></noscript>'; }

add_action('wp_head', 'inject_header_simple_analytics', 10);

add_action('wp_footer', 'inject_footer_simple_analytics', 10);
