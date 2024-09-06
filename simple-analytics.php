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

define('SimpleAnalytics\\PLUGIN_URL', plugin_dir_url(__FILE__));
define('SimpleAnalytics\\PLUGIN_PATH', plugin_dir_path(__FILE__));

/**
 * @note Manual loading rather than Composer to avoid potential conflict with plugins/themes that ship older autoloader.
 */
require __DIR__ . '/src/Support/SvgIcon.php';
require __DIR__ . '/helpers.php';
require __DIR__ . '/src/Plugin.php';
require __DIR__ . '/src/Setting.php';
require __DIR__ . '/src/Enums/SettingName.php';
require __DIR__ . '/src/TrackingPolicy.php';
require __DIR__ . '/src/ScriptCollection.php';
require __DIR__ . '/src/Scripts/Script.php';
require __DIR__ . '/src/Scripts/HideScriptId.php';
require __DIR__ . '/src/Scripts/HasAttributes.php';
require __DIR__ . '/src/Scripts/AnalyticsScript.php';
require __DIR__ . '/src/Scripts/AutomatedEventsScript.php';
require __DIR__ . '/src/Scripts/InactiveScript.php';
require __DIR__ . '/src/Actions/Action.php';
require __DIR__ . '/src/Actions/FooterContents.php';
require __DIR__ . '/src/Actions/AnalyticsCode.php';
require __DIR__ . '/src/Support/Str.php';
require __DIR__ . '/src/Settings/Block.php';
require __DIR__ . '/src/Settings/Blocks/CalloutBlock.php';
require __DIR__ . '/src/Settings/Blocks/Fields/Concerns/HasDocs.php';
require __DIR__ . '/src/Settings/Blocks/Fields/Concerns/HasPlaceholder.php';
require __DIR__ . '/src/Settings/Concerns/ManagesBlocks.php';
require __DIR__ . '/src/Settings/Concerns/WordPressPageIntegration.php';
require __DIR__ . '/src/Settings/Page.php';
require __DIR__ . '/src/Settings/Blocks/Fields/Field.php';
require __DIR__ . '/src/Settings/Blocks/Fields/Input.php';
require __DIR__ . '/src/Settings/Blocks/Fields/Checkboxes.php';
require __DIR__ . '/src/Settings/Blocks/Fields/Checkbox.php';
require __DIR__ . '/src/Settings/Blocks/Fields/IpList.php';
require __DIR__ . '/src/Settings/Tab.php';
require __DIR__ . '/src/UI/LabelComponent.php';
require __DIR__ . '/src/UI/TabListComponent.php';
require __DIR__ . '/src/UI/PageComponent.php';

use SimpleAnalytics\Plugin;

(new Plugin)->register();
