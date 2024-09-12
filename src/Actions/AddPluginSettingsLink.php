<?php

namespace SimpleAnalytics\Actions;

use const SimpleAnalytics\PLUGIN_BASENAME;

/**
 * Adds a "Settings" link to the installed plugin list page.
 */
class AddPluginSettingsLink
{
    use Action;

    protected string $hook = 'plugin_action_links_' . PLUGIN_BASENAME;

    public function handle($links): array
    {
        $link = '<a href="options-general.php?page=simpleanalytics">' . __('Settings', 'simpleanalytics') . '</a>';

        return [$link] + $links;
    }
}
