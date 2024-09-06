<?php

namespace SimpleAnalytics;

class TrackingPolicy
{
    public function shouldCollectAnalytics(): bool
    {
        if (Setting::boolean(SettingName::ENABLED) === false) {
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
        return in_array($ip, Setting::array(SettingName::EXCLUDED_IP_ADDRESSES), true);
    }

    protected function containsExcludedRole(array $roles): bool
    {
        return array_intersect(Setting::array(SettingName::EXCLUDED_ROLES), $roles) !== [];
    }
}
