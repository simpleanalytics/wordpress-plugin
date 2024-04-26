<?php

namespace SimpleAnalytics\Admin;

use SimpleAnalytics\Fields\CustomDomainField;
use SimpleAnalytics\Fields\ExcludedIpAddressesField;
use SimpleAnalytics\Fields\ExcludedRolesField;

defined('\\ABSPATH') || exit;

class SettingsForm
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'registerFields']);
    }

    public function registerFields(): void
    {
        // General Settings
        add_settings_section(
            'simple_analytics_general_settings',
            null,
            '__return_null',
            'simple-analytics-settings-general'
        );

        new CustomDomainField(
            'simple_analytics_general_settings',
            'simple-analytics-settings-general',
            'simple_analytics_general_settings'
        );

        // Advanced Settings
        add_settings_section(
            'simple_analytics_advanced_settings',
            null,
            '__return_null',
            'simple-analytics-settings-advanced'
        );

        new ExcludedRolesField(
            'simple_analytics_advanced_settings',
            'simple-analytics-settings-advanced',
            'simple_analytics_advanced_settings'
        );
        new ExcludedIpAddressesField(
            'simple-analytics-settings-advanced',
            'simple_analytics_advanced_settings',
            'simple_analytics_advanced_settings'
        );
    }
}
