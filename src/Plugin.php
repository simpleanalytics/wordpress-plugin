<?php

namespace SimpleAnalytics;

use SimpleAnalytics\Enums\Setting;

class Plugin
{
    public function __construct()
    {
        new SettingsRegistry();
        new Admin\SettingsPage();
        new Admin\SettingsForm();

        add_action('init', [$this, 'initialize']);
    }

    public function initialize(): void
    {
        new ScriptInjector($this->shouldCollectAnalytics());
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
