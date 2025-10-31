<?php

namespace SimpleAnalytics;

class TrackingRules
{
    public function excludedIp(): bool
    {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
        $list = Setting::array(SettingName::EXCLUDED_IP_ADDRESSES);

        return in_array($ip, $list);
    }

    public function excludedUserRole(): bool
    {
        if (! is_user_logged_in()) return false;

        $needle = Setting::array(SettingName::EXCLUDED_ROLES);
        $current = wp_get_current_user()->roles;

        return array_intersect($needle, $current) !== [];
    }
}
