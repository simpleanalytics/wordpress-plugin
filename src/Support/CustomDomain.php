<?php

namespace SimpleAnalytics\Support;

defined('ABSPATH') || exit;

use SimpleAnalytics\SettingName;

class CustomDomain
{
    public static function sanitize($value): string
    {
        $current = get_option(SettingName::CUSTOM_DOMAIN, '');
        $current = is_string($current) ? $current : '';

        if (! current_user_can('unfiltered_html')) {
            return $current;
        }

        $value = is_string($value) ? strtolower(trim($value)) : null;
        if ($value === null || ($value !== '' && ! self::isHostname($value))) {
            add_settings_error(SettingName::CUSTOM_DOMAIN, 'invalid_custom_domain', 'Enter a hostname such as stats.example.com, without a URL scheme, path, port, or query.');
            return $current;
        }

        if ($value !== $current) {
            ScriptPinning::domainChanged();
        }

        // This option is deliberately not registered as an editable setting.
        update_option(SettingName::CUSTOM_DOMAIN_HASH, self::approvalHash($value));
        return $value;
    }

    public static function get(string $default): string
    {
        $value = get_option(SettingName::CUSTOM_DOMAIN, '');
        $hash = get_option(SettingName::CUSTOM_DOMAIN_HASH, '');

        // Legacy values stay inert until saved by someone allowed to add scripts.
        if (! is_string($value) || ! self::isHostname($value) || ! is_string($hash)) {
            return $default;
        }

        return hash_equals($hash, self::approvalHash($value)) ? $value : $default;
    }

    private static function isHostname(string $value): bool
    {
        return strlen($value) <= 253
            && strpos($value, '.') !== false
            && ! filter_var($value, FILTER_VALIDATE_IP)
            && preg_match('/\A[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)+\z/', $value) === 1;
    }

    private static function approvalHash(string $value): string
    {
        return hash_hmac('sha256', 'custom-domain:' . get_current_blog_id() . ':' . $value, wp_salt('auth'));
    }
}
