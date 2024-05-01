<?php

namespace SimpleAnalytics;

use SimpleAnalytics\Actions\RegisterSettings;
use SimpleAnalytics\Actions\InjectScripts;
use SimpleAnalytics\Admin\SettingsForm;
use SimpleAnalytics\Admin\SettingsPage;
use SimpleAnalytics\Enums\Setting;

class Plugin
{
    public function __construct()
    {
        add_action('admin_init', new RegisterSettings);

        (new SettingsPage())->register();
        (new SettingsForm())->register();

        add_action('init', new InjectScripts($this->getScrips()));
    }

    /** @return Scripts\Script[] */
    protected function getScrips(): array
    {
        $scripts = [];

        if ($this->shouldCollectAnalytics()) {
            $scripts[] = new Scripts\AnalyticsScript();
        } else {
            $scripts[] = new Scripts\InactiveScript();
        }

        if (get_option(Setting::EVENT_COLLECT, false)) {
            $scripts[] = new Scripts\AutomatedEventsScript();
        }

        return $scripts;
    }

    protected function shouldCollectAnalytics(): bool
    {
        if (! get_option(Setting::ENABLED, true)) {
            return false;
        }

        if ($this->clientIpExcluded($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'])) {
            return false;
        }

        if (! $user = wp_get_current_user()) {
            return true;
        }

        return ! $this->containsExcludedRole($user->roles);
    }

    protected function clientIpExcluded(string $ip): bool
    {
        return str_contains(get_option(Setting::EXCLUDED_IP_ADDRESSES, ''), $ip);
    }

    protected function containsExcludedRole(array $roles): bool
    {
        return array_intersect(get_option(Setting::EXCLUDED_ROLES, []), $roles) !== [];
    }
}
