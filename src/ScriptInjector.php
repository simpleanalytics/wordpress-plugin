<?php

namespace SimpleAnalytics;

use SimpleAnalytics\Enums\Setting;

defined('\\ABSPATH') || exit;

class ScriptInjector
{
    const ANALYTICS_SCRIPT_HANDLE = 'simpleanalytics_script';
    const EVENTS_SCRIPT_HANDLE = 'simpleanalytics_events_script';
    const INACTIVE_SCRIPT_HANDLE = 'simpleanalytics_inactive';

    public bool $shouldCollectAnalytics;

    public function __construct(bool $shouldCollectAnalytics)
    {
        $this->shouldCollectAnalytics = $shouldCollectAnalytics;

        add_action('init', [$this, 'initialize']);
    }

    public function initialize(): void
    {
        add_action('wp_footer', [$this, 'insertFooterContents']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueAnalyticsScript']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueEventsScript']);
    }

    public function insertFooterContents(): void
    {
        if (! $this->shouldCollectAnalytics) {
            echo "<!-- Simple Analytics: Not logging requests from admins -->\n";
        } else {
            echo '<noscript><img src="https://' . get_option(Setting::CUSTOM_DOMAIN, 'queue.simpleanalyticscdn.com') . '/noscript.gif" alt="" referrerpolicy="no-referrer-when-downgrade"></noscript>' . "\n";
        }
    }

    public function enqueueAnalyticsScript(): void
    {
        if (! $this->shouldCollectAnalytics) {
            wp_enqueue_script(self::INACTIVE_SCRIPT_HANDLE, plugins_url('js/inactive.js', __FILE__), [], null, true);
            return;
        }

        wp_enqueue_script(self::ANALYTICS_SCRIPT_HANDLE, sprintf("https://%s/latest.js", get_option(Setting::CUSTOM_DOMAIN, 'scripts.simpleanalyticscdn.com')), [], null, true);

        add_filter('wp_script_attributes', [$this, 'addAnalyticsScriptAttributes']);
    }

    public function addAnalyticsScriptAttributes($attrs): array
    {
        if ($attrs['id'] !== self::ANALYTICS_SCRIPT_HANDLE) {
            return $attrs;
        }

        return array_merge($attrs, array_filter([
            'data-mode'         => get_option(Setting::MODE),
            'data-collect-dnt'  => get_option(Setting::COLLECT_DNT),
            'data-ignore-pages' => get_option(Setting::IGNORE_PAGES),
            'data-auto-collect' => get_option(Setting::AUTO_COLLECT),
            'data-onload'       => get_option(Setting::ONLOAD_CALLBACK),
            'data-hostname'     => get_option(Setting::CUSTOM_DOMAIN),
            'data-sa-global'    => get_option(Setting::SA_GLOBAL),
        ]));
    }

    public function enqueueEventsScript(): void
    {
        if (! $this->shouldCollectAnalytics) {
            return;
        }

        wp_enqueue_script(self::EVENTS_SCRIPT_HANDLE, sprintf("https://%s/auto-events.js", get_option(Setting::CUSTOM_DOMAIN, 'scripts.simpleanalyticscdn.com')), [], null, true);

        add_filter('wp_script_attributes', [$this, 'addEventsScriptAttributes']);
    }

    public function addEventsScriptAttributes($attrs): array
    {
        if ($attrs['id'] !== self::EVENTS_SCRIPT_HANDLE) {
            return $attrs;
        }

        return array_merge($attrs, array_filter([
            'data-collect'    => get_option(Setting::EVENT_COLLECT),
            'data-extensions' => get_option(Setting::EVENT_EXTENSIONS),
            'data-use-title'  => get_option(Setting::EVENT_USE_TITLE),
            'data-full-urls'  => get_option(Setting::EVENT_FULL_URLS),
            'data-sa-global'  => get_option(Setting::EVENT_SA_GLOBAL),
        ]));
    }
}
