<?php

namespace SimpleAnalytics;

use SimpleAnalytics\Support\IpAddress;

class TrackingRules
{
    protected $settings;

    public function __construct(WordPressSettings $settings)
    {
        $this->settings = $settings;
    }

    public function hasExcludedIp(): bool
    {
        $ip = IpAddress::current();

        if (empty($ip)) return false;

        $list = array_map([IpAddress::class, 'normalize'], $this->settings->array(SettingName::EXCLUDED_IP_ADDRESSES));

        return in_array($ip, $list, true);
    }

    public function hasExcludedUserRole(): bool
    {
        if (! is_user_logged_in()) {
            return false;
        }

        $needle = $this->settings->array(SettingName::EXCLUDED_ROLES);

        if ($needle === []) {
            return true;
        }

        $current = wp_get_current_user()->roles;

        return array_intersect($needle, $current) !== [];
    }
}
