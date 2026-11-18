<?php

namespace SimpleAnalytics\Support;

use SimpleAnalytics\SettingName;

class OnloadCallback
{
    public static function sanitize($value): string
    {
        if (! current_user_can('unfiltered_html')) {
            $current = get_option(SettingName::ONLOAD_CALLBACK, '');
            return is_string($current) ? $current : '';
        }

        $value = sanitize_text_field($value);
        // This private option is not registered as an editable setting. It
        // records that a user allowed to save JavaScript saved this exact code.
        update_option(SettingName::ONLOAD_CALLBACK_HASH, hash_hmac('sha256', $value, wp_salt('auth')));
        return $value;
    }

    public static function get(): ?string
    {
        $value = get_option(SettingName::ONLOAD_CALLBACK, '');
        $hash = get_option(SettingName::ONLOAD_CALLBACK_HASH, '');

        // Older releases accepted callback text without the capability check.
        // Keep it inert until an authorized administrator saves it again.
        if (! is_string($value) || $value === '' || ! is_string($hash)) return null;
        return hash_equals($hash, hash_hmac('sha256', $value, wp_salt('auth'))) ? $value : null;
    }
}
