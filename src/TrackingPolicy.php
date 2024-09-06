<?php

namespace SimpleAnalytics;

use SimpleAnalytics\Enums\SettingName;

class TrackingPolicy
{
    public function shouldCollectAnalytics(): bool
    {
        if (Setting::get(SettingName::ENABLED) === false) {
            return false;
        }

        if ($this->clientIpExcluded($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'])) {
            return false;
        }

        if (! is_user_logged_in()) {
            return true;
        }

        return ! $this->containsExcludedRole(wp_get_current_user()->roles);
    }

    protected function clientIpExcluded(string $ip): bool
    {
        return str_contains(Setting::get(SettingName::EXCLUDED_IP_ADDRESSES, ''), $ip);
    }

    protected function containsExcludedRole(array $roles): bool
    {
        return array_intersect(Setting::array(SettingName::EXCLUDED_ROLES), $roles) !== [];
    }
}
