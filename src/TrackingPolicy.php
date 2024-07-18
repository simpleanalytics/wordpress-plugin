<?php

namespace SimpleAnalytics;

use SimpleAnalytics\Enums\SettingName;

class TrackingPolicy
{
    public function shouldCollectAnalytics(): bool
    {
        if (! get_option(SettingName::ENABLED)) {
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
        return str_contains(get_option(SettingName::EXCLUDED_IP_ADDRESSES, ''), $ip);
    }

    protected function containsExcludedRole(array $roles): bool
    {
        return array_intersect(get_option(SettingName::EXCLUDED_ROLES, []), $roles) !== [];
    }
}
