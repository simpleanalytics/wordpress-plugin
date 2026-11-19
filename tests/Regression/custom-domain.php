<?php

defined('ABSPATH') || exit;

require __DIR__ . '/../Support/isolated-options.php';

sa_with_isolated_options(static function () {
    global $wp_settings_errors;

    $key = SimpleAnalytics\SettingName::CUSTOM_DOMAIN;
    $field = new SimpleAnalytics\Settings\Blocks\Fields\Input($key, 'Custom Domain');
    $sanitize = $field->getValueSanitizer();
    $analytics = new SimpleAnalytics\Scripts\AnalyticsScript();
    $events = new SimpleAnalytics\Scripts\AutomatedEventsScript();
    $noscript = new SimpleAnalytics\Actions\AddNoScriptTag();
    $default = 'https://scripts.simpleanalyticscdn.com/latest.js';
    $errors = static function () use ($key) {
        return array_column(get_settings_errors($key), 'code');
    };

    // Values saved by earlier releases, or with WP-CLI, keep working after the upgrade.
    update_option($key, 'legacy.example.test');
    sa_assert($analytics->path() === 'https://legacy.example.test/latest.js', 'Existing custom domains must keep working.');
    update_option($key, ' Legacy.Example.Test ');
    sa_assert($analytics->path() === 'https://legacy.example.test/latest.js', 'Existing custom domains with capitals or spaces must keep working.');
    sa_assert(SimpleAnalytics\Support\CustomDomain::problem() === null, 'A usable custom domain needs no warning.');
    update_option($key, 'legacy.example.test/x.js?');
    sa_assert($analytics->path() === $default, 'A stored value that is not a hostname must not build a script URL.');
    sa_assert(is_string(SimpleAnalytics\Support\CustomDomain::problem()), 'An unusable stored value must explain the fallback.');

    wp_set_current_user(get_user_by('login', 'admin')->ID);
    register_setting('simpleanalytics-advanced', $key, ['sanitize_callback' => $sanitize]);
    update_option($key, ' https://Stats.Example.Test/ ');
    sa_assert(get_option($key) === 'stats.example.test', 'Normalize a pasted URL to its hostname.');
    sa_assert($analytics->path() === 'https://stats.example.test/latest.js', 'Saved domains must work for visitors.');
    sa_assert($events->path() === 'https://stats.example.test/auto-events.js', 'Events must use the custom domain.');
    sa_assert($noscript->getCustomDomain() === 'https://stats.example.test/noscript.gif', 'Noscript must use the custom domain.');
    sa_assert($errors() === [], 'A valid domain must not report an error.');

    foreach (['evil.example/x.js?', 'evil.example:443', 'user@evil.example', 'evil.example#x', 'evil.example?x', '-evil.example', 'evil..example', '127.0.0.1', ['evil.example']] as $invalid) {
        $wp_settings_errors = [];
        sa_assert($sanitize($invalid) === 'stats.example.test', 'Malformed domains must preserve the previous value.');
        sa_assert($errors() === ['invalid_custom_domain'], 'Malformed domains must explain why they were not saved.');
    }

    $wp_settings_errors = [];
    sa_assert($sanitize(null) === 'stats.example.test', 'A form without the field must keep the domain.');
    sa_assert($errors() === [], 'A form without the field must not report an error.');

    $deny = static function ($caps, $cap) { return $cap === 'unfiltered_html' ? ['do_not_allow'] : $caps; };
    add_filter('map_meta_cap', $deny, 10, 2);
    $wp_settings_errors = [];
    update_option($key, 'evil.example');
    sa_assert(get_option($key) === 'stats.example.test', 'Restricted admins must not replace a script origin.');
    sa_assert($errors() === ['custom_domain_not_allowed'], 'Restricted admins must learn why the domain was not changed.');
    $wp_settings_errors = [];
    sa_assert($sanitize('Stats.Example.Test') === 'stats.example.test', 'Restricted admins may save other settings without changing the domain.');
    sa_assert($errors() === [], 'Saving an unchanged domain must not report an error.');
    remove_filter('map_meta_cap', $deny, 10);

    wp_set_current_user(0);
    sa_assert($analytics->path() === 'https://stats.example.test/latest.js', 'The domain does not depend on the visitor.');

    wp_set_current_user(get_user_by('login', 'admin')->ID);
    update_option($key, ['evil.example']);
    sa_assert(get_option($key) === 'stats.example.test', 'Arrays must not replace the domain.');
    remove_filter('sanitize_option_' . $key, $sanitize);
    update_option($key, ['evil.example']);
    sa_assert($analytics->path() === $default, 'Malformed stored values must not cause a fatal error.');

    register_setting('simpleanalytics-advanced', $key, ['sanitize_callback' => $sanitize]);
    update_option($key, '');
    sa_assert($analytics->path() === $default, 'Clearing the domain restores the script CDN.');
    sa_assert($noscript->getCustomDomain() === 'https://queue.simpleanalyticscdn.com/noscript.gif', 'Clearing the domain restores the image CDN.');
    sa_assert(SimpleAnalytics\Support\CustomDomain::problem() === null, 'An empty domain needs no warning.');
});
