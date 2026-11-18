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

        // options.php passes null when the form has no field for this setting.
        if ($value === null) {
            return $current;
        }

        $value = is_string($value) ? self::normalize($value) : null;
        if ($value === self::normalize($current)) {
            return $current;
        }

        // The domain decides where the analytics script is loaded from, so
        // changing it requires the same permission as adding a script tag.
        if (! current_user_can('unfiltered_html')) {
            add_settings_error(SettingName::CUSTOM_DOMAIN, 'custom_domain_not_allowed', 'The custom domain was not changed. Only users who may add unfiltered HTML can change it, such as network administrators on multisite.');
            return $current;
        }

        if ($value === null || ($value !== '' && ! self::isHostname($value))) {
            add_settings_error(SettingName::CUSTOM_DOMAIN, 'invalid_custom_domain', 'The custom domain was not changed. Enter a hostname such as stats.example.com, without a path, port, or query.');
            return $current;
        }

        ScriptPinning::domainChanged();

        return $value;
    }

    public static function get(string $default): string
    {
        $value = get_option(SettingName::CUSTOM_DOMAIN, '');
        $value = is_string($value) ? self::normalize($value) : '';

        return self::isHostname($value) ? $value : $default;
    }

    /**
     * Explains why a saved custom domain is not used, so the settings page can show it.
     */
    public static function problem(): ?string
    {
        $value = get_option(SettingName::CUSTOM_DOMAIN, '');
        if ($value === '' || $value === false || $value === null || self::get('') !== '') {
            return null;
        }

        return 'Visitors load scripts from the default domain because the saved custom domain is not a valid hostname. Enter a hostname such as stats.example.com.';
    }

    // Accept what people paste: surrounding spaces, capitals, a URL scheme, or a trailing slash.
    private static function normalize(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('#\Ahttps?://#', '', $value);

        return rtrim($value, '/');
    }

    private static function isHostname(string $value): bool
    {
        return strlen($value) <= 253
            && strpos($value, '.') !== false
            && ! filter_var($value, FILTER_VALIDATE_IP)
            && preg_match('/\A[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)+\z/', $value) === 1;
    }
}
