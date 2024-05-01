<?php

namespace SimpleAnalytics\Admin;

defined('\\ABSPATH') || exit;

class SettingsPage
{
    public function register(): void
    {
        add_action('admin_menu', [$this, 'addOptionsPage']);
    }

    public function addOptionsPage(): void
    {
        add_options_page(
            'Simple Analytics Settings',
            'Simple Analytics',
            'manage_options',
            'simple-analytics-settings',
            [$this, 'render']
        );
    }

    public function render(): void
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
}
