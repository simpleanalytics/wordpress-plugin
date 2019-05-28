<?php
/*
 * Plugin Name: Simple Analytics Official
 * Version: 1.3
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

if (!defined('ABSPATH')) exit;

function simpleanalytics_warn_not_logging() {
  global $is_running;
  if (!$is_running) {
    echo '<script>console.warn(\'Simple Analytics: Not logging requests from admins\')</script>';
  }
}

function simpleanalytics_inject_footer() {
  global $is_running;
  if ($is_running) {
    echo '<noscript><img src="https://api.simpleanalytics.io/hello.gif" alt=""></noscript>';
  }
}

function simpleanalytics_init() {
  global $is_running;
  $is_running = !current_user_can('editor') && !current_user_can('administrator');
  if ($is_running) {
    wp_enqueue_script('simpleanalytics_script', 'https://cdn.simpleanalytics.io/hello.js', array(), null, true);
    add_action('wp_footer', 'simpleanalytics_inject_footer', 10);
  } else {
    add_action('wp_head', 'simpleanalytics_warn_not_logging', 10);
  }
}

add_action('init', 'simpleanalytics_init');
