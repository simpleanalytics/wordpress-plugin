<?php

namespace SimpleAnalytics;

defined('ABSPATH') || exit;

class SettingsPage
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_plugin_page']);
        add_action('admin_init', [$this, 'page_init']);
    }

    public function add_plugin_page()
    {
        add_options_page(
            'Simple Analytics Settings',
            'Simple Analytics',
            'manage_options',
            'simple-analytics-settings',
            [$this, 'render_admin_page']
        );
    }

    public function render_admin_page()
    {
        $active_tab = $_GET['tab'] ?? 'general';
        ?>
        <div class="wrap">
            <h2>Simple Analytics Settings</h2>
            <h2 class="nav-tab-wrapper">
                <a href="?page=simple-analytics-settings&tab=general"
                   class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : '' ?>">General</a>
                <a href="?page=simple-analytics-settings&tab=advanced"
                   class="nav-tab <?php echo $active_tab == 'advanced' ? 'nav-tab-active' : '' ?>">
                    Advanced</a>
                <a href="?page=simple-analytics-settings&tab=events"
                   class="nav-tab <?php echo $active_tab == 'events' ? 'nav-tab-active' : '' ?>">Events</a>
            </h2>
            <form method="post" action="options.php">
                <?php
                settings_fields("simple_analytics_{$active_tab}_settings");
                do_settings_sections('simple-analytics-settings-' . $active_tab);
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function page_init(): void
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
