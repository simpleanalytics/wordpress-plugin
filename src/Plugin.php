<?php

namespace SimpleAnalytics;

defined('\\ABSPATH') || exit;

class Plugin
{
    public bool $shouldCollectAnalytics;

    public function __construct()
    {
        add_action('init', [$this, 'initialize']);
    }

    public function initialize(): void
    {
        $this->shouldCollectAnalytics = ! is_user_logged_in();

        add_action('wp_footer', [$this, 'insertFooterContents']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
    }

    public function insertFooterContents(): void
    {
        if (! $this->shouldCollectAnalytics) {
            echo "<!-- Simple Analytics: Not logging requests from admins -->\n";
        } else {
            echo '<noscript><img src="https://' . $this->getDomain('queue.simpleanalyticscdn.com') . '/noscript.gif" alt="" referrerpolicy="no-referrer-when-downgrade"></noscript>' . "\n";
        }
    }

    public function enqueueScripts(): void
    {
        if (! $this->shouldCollectAnalytics) {
            wp_enqueue_script('simpleanalytics_inactive', plugins_url('js/inactive.js', __FILE__), [], null, true);
        } else {
            wp_enqueue_script('simpleanalytics_script', "https://" . $this->getDomain('scripts.simpleanalyticscdn.com') . "/latest.js", [], null, true);
        }
    }

    public function getDomain(string $default): string
    {
        return get_option('simpleanalytics_custom_domain', $default);
    }
}
