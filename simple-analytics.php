<?php
/*
 * Plugin Name: Simple Analytics Official
 * Version: 1.18
 * Plugin URI: https://docs.simpleanalytics.com/install-simple-analytics-on-wordpress
 * Description: Embed Simple Analytics script in your WordPress website
 * Author: Simple Analytics
 * Author URI: https://simpleanalytics.com/
 * Requires at least: 5.2.0
 * Tested up to: 6.4.3
 *
 * Text Domain: simple-analytics
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Simple Analytics
 * @since 1.0.0
 */

namespace SimpleAnalytics;

defined('ABSPATH') || exit;

function should_collect_analytics(): bool
{
    return ! is_user_logged_in();
}

function get_analytics_domain(string $default): string
{
    return get_option('simpleanalytics_custom_domain', $default);
}

function insert_footer_contents(): void
{
    if (! should_collect_analytics()) {
        echo "<!-- Simple Analytics: Not logging requests from admins -->\n";
    } else {
        echo '<noscript><img src="https://' . get_analytics_domain('queue.simpleanalyticscdn.com') . '/noscript.gif" alt="" referrerpolicy="no-referrer-when-downgrade"></noscript>' . "\n";
    }
}

function enqueue_scripts(): void
{
    if (! should_collect_analytics()) {
        wp_enqueue_script('simpleanalytics_inactive', plugins_url('js/inactive.js', __FILE__), [], null, true);
    } else {
        wp_enqueue_script('simpleanalytics_script', "https://" . get_analytics_domain('scripts.simpleanalyticscdn.com') . "/latest.js", [], null, true);
    }
}

add_action('wp_footer', 'SimpleAnalytics\insert_footer_contents');
add_action('wp_enqueue_scripts', 'SimpleAnalytics\enqueue_scripts');

require __DIR__ . '/includes/AbstractField.php';
require __DIR__ . '/includes/CustomDomainField.php';
require __DIR__ . '/includes/ExcludedRolesField.php';
require __DIR__ . '/includes/ExcludedIpAddressesField.php';
require __DIR__ . '/includes/settings.php';

new SettingsPage();
