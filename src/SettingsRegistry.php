<?php

namespace SimpleAnalytics;

use SimpleAnalytics\Enums\Setting;

defined('\\ABSPATH') || exit;

class SettingsRegistry
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'registerSettings']);
    }

    public function registerSettings(): void
    {
        register_setting('simpleanalytics_settings', Setting::CUSTOM_DOMAIN);
    }
}
