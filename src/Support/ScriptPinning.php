<?php

namespace SimpleAnalytics\Support;

defined('ABSPATH') || exit;

use SimpleAnalytics\Setting;
use SimpleAnalytics\SettingName;

final class ScriptPinning
{
    const VERSION = 'v12';
    // The bundled automated-events script comes from the same scripts release as VERSION.
    const EVENTS_PATH = 'resources/js/pinned/' . self::VERSION . '/auto-events.js';
    const INTEGRITY = 'sha384-VVOeJV8okLf6owAyITVK14p3AX78fPOStksa5tYEruAngS4yv5EYM26KP9bvcZ/L';
    // Updated with the reviewed bundled file, never calculated from a remote response.
    const EVENTS_INTEGRITY = 'sha384-20qK/TgV9cFZ42dQDiHEOO3XQbjygfKLks99D0CT9hZM/6cXxKQZbEyZNPlTpmrP';

    private static $domainChanged = false;

    public static function enabled(): bool
    {
        return Setting::boolean(SettingName::PIN_SCRIPTS, false) === true;
    }

    public static function domainChanged(): void
    {
        self::$domainChanged = true;
        delete_option(SettingName::CUSTOM_SCRIPT_APPROVAL);
        delete_option(SettingName::CUSTOM_SCRIPT_INTEGRITY);
    }

    public static function sanitizeIntegrity($value): string
    {
        $current = get_option(SettingName::CUSTOM_SCRIPT_INTEGRITY, '');
        $current = is_string($current) ? $current : '';
        if (! current_user_can('unfiltered_html')) {
            return $current;
        }
        // options.php saves the domain before this field. Do not approve a hash
        // copied from the previous domain in the same form submission.
        if (self::$domainChanged) {
            if ($value !== '' && $value !== null) {
                add_settings_error(SettingName::CUSTOM_SCRIPT_INTEGRITY, 'domain_changed', 'Custom domain changed. Save its SHA-384 hash in a separate save after reviewing the versioned script.');
            }
            return '';
        }
        $value = is_string($value) ? trim($value) : null;
        if ($value === '') {
            delete_option(SettingName::CUSTOM_SCRIPT_APPROVAL);
            return '';
        }
        $domain = CustomDomain::get('');
        if ($value === null || preg_match('/\Asha384-[A-Za-z0-9+\/]{64}\z/', $value) !== 1 || $domain === '') {
            add_settings_error(SettingName::CUSTOM_SCRIPT_INTEGRITY, 'invalid_integrity', 'Save a valid custom domain first, then enter its reviewed script hash: sha384- followed by 64 base64 characters.');
            return $current;
        }
        // Saving unrelated settings after a version change must not reapprove
        // a stale hash. Clear it first if deliberately approving identical bytes.
        if ($value === $current && self::customIntegrity() === null) {
            add_settings_error(SettingName::CUSTOM_SCRIPT_INTEGRITY, 'stale_integrity', 'This hash approval has expired. Enter the new version’s hash, or clear and save this field before approving the same hash again.');
            return $current;
        }
        update_option(SettingName::CUSTOM_SCRIPT_APPROVAL, self::approvalHash($domain, $value));
        return $value;
    }

    public static function customIntegrity(): ?string
    {
        $domain = CustomDomain::get('');
        $value = get_option(SettingName::CUSTOM_SCRIPT_INTEGRITY, '');
        $approval = get_option(SettingName::CUSTOM_SCRIPT_APPROVAL, '');
        if ($domain === '' || ! is_string($value) || preg_match('/\Asha384-[A-Za-z0-9+\/]{64}\z/', $value) !== 1 || ! is_string($approval)) {
            return null;
        }
        return hash_equals(self::approvalHash($domain, $value), $approval) ? $value : null;
    }

    public static function ready(): bool
    {
        if (! self::enabled()) return true;
        $domain = get_option(SettingName::CUSTOM_DOMAIN, '');
        // A malformed or unapproved configured domain must not silently change
        // the tracking destination when pinning is enabled.
        return $domain === '' || $domain === false || $domain === null || self::customIntegrity() !== null;
    }

    public static function attributes(): array
    {
        if (! self::enabled()) return [];
        return [
            'integrity' => CustomDomain::get('') !== '' ? self::customIntegrity() : self::INTEGRITY,
            'crossorigin' => 'anonymous',
        ];
    }

    public static function notice(bool $inSettings = false): void
    {
        // The settings page uses a shadow root, which hides ordinary WP notices.
        $settingsPage = isset($_GET['page']) && $_GET['page'] === 'simpleanalytics';
        if ($settingsPage !== $inSettings) return;
        if (self::enabled() && ! self::ready() && current_user_can('manage_options')) {
            echo '<div class="notice notice-error" role="alert" style="border-left:4px solid #b32d2e;background:#fff;padding:12px;margin:12px 0"><p>' . esc_html('Simple Analytics tracking is paused: approve the SHA-384 hash for your custom domain’s ' . self::VERSION . ' script under Settings → Simple Analytics → Advanced. No unpinned script will be loaded.') . '</p></div>';
        }
    }

    private static function approvalHash(string $domain, string $integrity): string
    {
        return hash_hmac('sha256', 'script-integrity:' . get_current_blog_id() . ':' . $domain . ':' . self::VERSION . ':' . $integrity, wp_salt('auth'));
    }
}
