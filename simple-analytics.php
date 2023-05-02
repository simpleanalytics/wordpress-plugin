<?php
/*
 * Plugin Name: Simple Analytics Official
 * Version: 1.11
 * Plugin URI: https://docs.simpleanalytics.com/install-simple-analytics-on-wordpress
 * Description: Embed Simple Analytics script in your WordPress website
 * Author: Simple Analytics
 * Author URI: https://simpleanalytics.com/
 * Requires at least: 2.0.0
 * Tested up to: 6.2
 *
 * Text Domain: simple-analytics
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Simple Analytics
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

$simpleanalytics_script = 'https://scripts.simpleanalyticscdn.com/latest.js';

function simpleanalytics_warn_not_logging() {
  echo "<!-- Simple Analytics: Not logging requests from admins -->\n";
}

function simpleanalytics_noscript() {
  echo '<noscript><img src="https://queue.simpleanalyticscdn.com/noscript.gif" alt="" referrerpolicy="no-referrer-when-downgrade"></noscript>' . "\n";
}

function simpleanalytics_init() {
  global $simpleanalytics_script;
  $is_running = !current_user_can('editor') && !current_user_can('administrator');
  if ($is_running) {
    wp_enqueue_script('simpleanalytics_script', $simpleanalytics_script, array(), null, true);
    add_action('wp_footer', 'simpleanalytics_noscript', 10);
  } else {
    wp_enqueue_script('simpleanalytics_admins', plugins_url('public/js/admins.js', __FILE__), array(), null, true);
    add_action('wp_footer', 'simpleanalytics_warn_not_logging', 10);
  }
}

add_action('init', 'simpleanalytics_init');
