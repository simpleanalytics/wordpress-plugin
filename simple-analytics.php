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

defined('\\ABSPATH') || exit;

require __DIR__ . '/src/SettingsPage.php';
require __DIR__ . '/src/Plugin.php';
require __DIR__ . '/src/Fields/AbstractField.php';
require __DIR__ . '/src/Fields/ExcludedIpAddressesField.php';
require __DIR__ . '/src/Fields/ExcludedRolesField.php';
require __DIR__ . '/src/Fields/CustomDomainField.php';

new SimpleAnalytics\Plugin();
new SimpleAnalytics\SettingsPage();
