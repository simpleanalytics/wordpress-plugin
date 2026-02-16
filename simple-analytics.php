<?php
/*
 * Plugin Name: Simple Analytics Official
 * Version: 1.27
 * Plugin URI: https://docs.simpleanalytics.com/install-simple-analytics-on-wordpress
 * Description: Embed Simple Analytics script in your WordPress website
 * Author: Simple Analytics
 * Author URI: https://simpleanalytics.com/
 * Requires at least: 5.2.0
 * Tested up to: 6.7
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
define('SimpleAnalytics\\PLUGIN_BASENAME', plugin_basename(__FILE__));
define('SimpleAnalytics\\ENTRYPOINT_FILE', __FILE__);

/**
 * @note Manual loading rather than Composer to avoid potential conflict with plugins/themes that ship older autoloader.
 */
require __DIR__ . '/src/Support/SvgIcon.php';
require __DIR__ . '/helpers.php';
require __DIR__ . '/src/Plugin.php';
require __DIR__ . '/src/WordPressHooks.php';
require __DIR__ . '/src/WordPressSettings.php';
require __DIR__ . '/src/Setting.php';
require __DIR__ . '/src/SettingName.php';
require __DIR__ . '/src/TrackingRules.php';
require __DIR__ . '/src/ScriptRegistry.php';
require __DIR__ . '/src/Scripts/Contracts/Script.php';
require __DIR__ . '/src/Scripts/Contracts/HideScriptId.php';
require __DIR__ . '/src/Scripts/Contracts/HasAttributes.php';
require __DIR__ . '/src/Scripts/AnalyticsScript.php';
require __DIR__ . '/src/Scripts/AutomatedEventsScript.php';
require __DIR__ . '/src/Scripts/InactiveScript.php';
require __DIR__ . '/src/Actions/Action.php';
require __DIR__ . '/src/Actions/AddInactiveComment.php';
require __DIR__ . '/src/Actions/AddNoScriptTag.php';
require __DIR__ . '/src/Actions/AddPluginSettingsLink.php';
require __DIR__ . '/src/Support/Str.php';
require __DIR__ . '/src/Settings/Block.php';
require __DIR__ . '/src/Settings/Blocks/CalloutBlock.php';
require __DIR__ . '/src/Settings/Concerns/HasDocs.php';
require __DIR__ . '/src/Settings/Concerns/HasPlaceholder.php';
require __DIR__ . '/src/Settings/Concerns/ManagesBlocks.php';
require __DIR__ . '/src/Settings/Concerns/WordPressPageIntegration.php';
require __DIR__ . '/src/Settings/AdminPage.php';
require __DIR__ . '/src/Settings/Blocks/Fields/Field.php';
require __DIR__ . '/src/Settings/Blocks/Fields/Input.php';
require __DIR__ . '/src/Settings/Blocks/Fields/Checkboxes.php';
require __DIR__ . '/src/Settings/Blocks/Fields/Checkbox.php';
require __DIR__ . '/src/Settings/Blocks/Fields/IpList.php';
require __DIR__ . '/src/Settings/Tab.php';
require __DIR__ . '/src/UI/LabelComponent.php';
require __DIR__ . '/src/UI/TabListComponent.php';
require __DIR__ . '/src/UI/PageLayoutComponent.php';

use SimpleAnalytics\SettingName;
use SimpleAnalytics\Settings\Tab;
use function SimpleAnalytics\get_icon;

$settings = new SimpleAnalytics\WordPressSettings();
$hooks = new SimpleAnalytics\WordPressHooks();
$rules = new SimpleAnalytics\TrackingRules($settings);
$scripts = new SimpleAnalytics\ScriptRegistry(/*TODO TAKE $hooks WordPressHooks as arg/dependency*/);

$adminPage = SimpleAnalytics\Settings\AdminPage::title('Simple Analytics')
    ->slug('simpleanalytics')
    ->tab('General', function (Tab $tab) {
        $tab->input(SettingName::CUSTOM_DOMAIN, 'Custom Domain')
            ->placeholder('Enter your custom domain or leave it empty.')
            ->description('E.g. api.example.com. Leave empty to use the default domain (most users).')
            ->docs('https://docs.simpleanalytics.com/bypass-ad-blockers');
    })
    ->tab('Ignore Rules', function (Tab $tab) {
        $tab->icon(get_icon('eye-slash'));

        $tab->input(SettingName::IGNORE_PAGES, 'Ignore Pages')
            ->description('Comma separated list of pages to ignore. E.g. /contact, /about')
            ->placeholder('Example: /page1, /page2, /category/*')
            ->docs('https://docs.simpleanalytics.com/ignore-pages');

        $tab->callout('IP and role exclusion only works when there is no page caching.');

        $tab->multiCheckbox(SettingName::EXCLUDED_ROLES, 'Specific User Roles to Exclude')
            ->description('When none selected, all authenticated users will be excluded from tracking.')
            ->options(function () {
                return wp_roles()->get_names();
            });

        $tab->ipList(SettingName::EXCLUDED_IP_ADDRESSES, 'Exclude IP Addresses')
            ->placeholder("127.0.0.1\n192.168.0.1")
            ->description('IP addresses to exclude from tracking.');
    })
    ->tab('Advanced', function (Tab $tab) {
        $tab->icon(get_icon('cog'));

        $tab->checkbox(SettingName::COLLECT_DNT, 'Collect Do Not Track')
            ->description('If you want to collect visitors with Do Not Track enabled, turn this on.')
            ->docs('https://docs.simpleanalytics.com/dnt');

        $tab->checkbox(SettingName::HASH_MODE, 'Hash mode')
            ->description('If your website uses hash (#) navigation, turn this on. On most WordPress websites this is not relevant.')
            ->docs('https://docs.simpleanalytics.com/hash-mode');

        $tab->checkbox(SettingName::MANUAL_COLLECT, 'Manually collect page views')
            ->description('In case you don’t want to auto collect page views, but via `sa_pageview` function in JavaScript.')
            ->docs('https://docs.simpleanalytics.com/trigger-custom-page-views#use-custom-collection-anyway');

        $tab->checkbox(SettingName::NOSCRIPT, 'Support no JavaScript mode')
            ->description('Collect analytics from visitors with disabled or no JavaScript.');

        $tab->input(SettingName::ONLOAD_CALLBACK, 'Onload Callback')
            ->description('JavaScript function to call when the script is loaded.')
            ->placeholder('Example: sa_event("My event")')
            ->docs('https://docs.simpleanalytics.com/trigger-custom-page-views#use-custom-collection-anyway');

        $tab->input(SettingName::HOSTNAME, 'Overwrite domain name')
            ->description('Override the domain that is sent to Simple Analytics. Useful for multi-domain setups.')
            ->placeholder('Example: example.com')
            ->docs('https://docs.simpleanalytics.com/overwrite-domain-name');

        $tab->input(SettingName::SA_GLOBAL, 'Global variable name')
            ->description('Change the global variable name of Simple Analytics. Default is `sa_event`.')
            ->placeholder('Example: ba_event')
            ->docs('https://docs.simpleanalytics.com/events#the-variable-sa_event-is-already-used');
    })
    ->tab('Events', function (Tab $tab) {
        $tab->title('Automated events')
            ->icon(get_icon('events'))
            ->description("It will track outbound links, email addresses clicks,
                                            and amount of downloads for common files (pdf, csv, docx, xIsx).
                                            Events will appear on your events page on simpleanalytics.com");

        $tab->checkbox(SettingName::AUTOMATED_EVENTS, 'Collect automated events');

        $tab->input(SettingName::EVENT_COLLECT_DOWNLOADS, 'Auto collect downloads')
            ->placeholder('Example: outbound,emails,downloads')
            ->docs('https://docs.simpleanalytics.com/automated-events');

        $tab->input(SettingName::EVENT_EXTENSIONS, 'Download file extensions')
            ->description('Comma separated list of file extensions to track as downloads. E.g. pdf, zip')
            ->placeholder('Example: pdf, zip')
            ->docs('https://docs.simpleanalytics.com/automated-events');

        $tab->checkbox(SettingName::EVENT_USE_TITLE, 'Use titles of page')
            ->description('Use the title of the page as the event name. Default is the URL.')
            ->docs('https://docs.simpleanalytics.com/automated-events');

        $tab->checkbox(SettingName::EVENT_FULL_URLS, 'Use full URLs')
            ->description('Use full URLs instead of the path. Default is the path.')
            ->docs('https://docs.simpleanalytics.com/automated-events');

        $tab->input(SettingName::EVENT_SA_GLOBAL, 'Override global')
            ->description('Override the global variable name of Simple Analytics. Default is `sa_event`.')
            ->placeholder('Example: ba_event')
            ->docs('https://docs.simpleanalytics.com/events#the-variable-sa_event-is-already-used');
    });

(new SimpleAnalytics\Plugin($hooks, $settings, $rules, $scripts, $adminPage))->boot();
