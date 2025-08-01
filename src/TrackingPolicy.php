<?php

namespace SimpleAnalytics;

class TrackingPolicy
{
    public function shouldCollectAnalytics(): bool
    {
        if ($this->isClientIpExcluded()) {
            return false;
        }

        if (! is_user_logged_in()) {
            return true;
        }

        return ! $this->containsExcludedRole();
    }

    protected function isClientIpExcluded(): bool
    {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];

        if (empty($ip)) return false;

        return in_array($ip, Setting::array(SettingName::EXCLUDED_IP_ADDRESSES));
    }

    protected function containsExcludedRole(): bool
    {
        $currentRoles = wp_get_current_user()->roles;
        $excludedRoles = Setting::array(SettingName::EXCLUDED_ROLES);

        return array_intersect($excludedRoles, $currentRoles) !== [];
    }
}
