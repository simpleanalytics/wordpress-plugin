<?php

namespace SimpleAnalytics;

defined('ABSPATH') || exit;

class TrackingRules
{
    /**
     * Pseudo-role in the excluded roles setting for multisite network administrators.
     */
    const SUPER_ADMIN = 'super_admin';

    protected $settings;

    public function __construct(WordPressSettings $settings)
    {
        $this->settings = $settings;
    }

    public function hasExcludedIp(): bool
    {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['REMOTE_ADDR'] ?? null);

        if (empty($ip)) return false;

        $list = $this->settings->array(SettingName::EXCLUDED_IP_ADDRESSES);

        return in_array($ip, $list);
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

        // Network administrators may have no role on the site they visit.
        if (in_array(self::SUPER_ADMIN, $needle, true) && is_multisite() && is_super_admin()) {
            return true;
        }

        $current = wp_get_current_user()->roles;

        return array_intersect($needle, $current) !== [];
    }
}
