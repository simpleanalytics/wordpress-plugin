<?php

namespace SimpleAnalytics;

class TrackingRules
{
    protected $settings;

    public function __construct(WordPressSettings $settings)
    {
        $this->settings = $settings;
    }

    public function hasExcludedIp(): bool
    {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];

        if (empty($ip)) return false;

        $list = $this->settings->array(SettingName::EXCLUDED_IP_ADDRESSES);

        return in_array($ip, $list);
    }

    public function hasExcludedUserRole(): bool
    {
        if (! is_user_logged_in()) return false;

        $needle = $this->settings->array(SettingName::EXCLUDED_ROLES);
        $current = wp_get_current_user()->roles;

        return array_intersect($needle, $current) !== [];
    }
}
