<?php
/*
 * Plugin Name: Simple Analytics Official
 * Version: 1.18
 * Plugin URI: https://docs.simpleanalytics.com/install-simple-analytics-on-wordpress
 * Description: Embed Simple Analytics script in your WordPress website
 * Author: Simple Analytics
 * Author URI: https://simpleanalytics.com/
 * Requires at least: 5.2.0
 * Tested up to: 6.5.2
 *
 * Text Domain: simple-analytics
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Simple Analytics
 * @since 1.0.0
 */

defined('\\ABSPATH') || exit;

require __DIR__ . '/src/Actions/RegisterSettings.php';
require __DIR__ . '/src/Actions/InjectScripts.php';
require __DIR__ . '/src/Plugin.php';
require __DIR__ . '/src/Enums/Setting.php';
require __DIR__ . '/src/Scripts/Script.php';
require __DIR__ . '/src/Scripts/AnalyticsScript.php';
require __DIR__ . '/src/Scripts/AutomatedEventsScript.php';
require __DIR__ . '/src/Scripts/InactiveScript.php';
require __DIR__ . '/src/Admin/SettingsPage.php';
require __DIR__ . '/src/Admin/Fields/Field.php';
require __DIR__ . '/src/Admin/Fields/Input.php';
require __DIR__ . '/src/Admin/Fields/CheckboxSelect.php';
require __DIR__ . '/src/Admin/Form.php';
require __DIR__ . '/src/Admin/SettingsForm.php';

new SimpleAnalytics\Plugin();
