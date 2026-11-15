<?php

defined('ABSPATH') || exit;

require __DIR__ . '/../Support/isolated-options.php';

sa_with_isolated_options(static function () {
    $key = SimpleAnalytics\SettingName::CUSTOM_DOMAIN;
    $hashKey = SimpleAnalytics\SettingName::CUSTOM_DOMAIN_HASH;
    $field = new SimpleAnalytics\Settings\Blocks\Fields\Input($key, 'Custom Domain');
    $sanitize = $field->getValueSanitizer();
    $analytics = new SimpleAnalytics\Scripts\AnalyticsScript();
    $events = new SimpleAnalytics\Scripts\AutomatedEventsScript();
    $noscript = new SimpleAnalytics\Actions\AddNoScriptTag();
    $default = 'https://scripts.simpleanalyticscdn.com/latest.js';

    delete_option($hashKey);
    update_option($key, 'legacy.example.test');
    sa_assert($analytics->path() === $default, 'Unapproved legacy domains must fall back to the CDN.');

    wp_set_current_user(get_user_by('login', 'admin')->ID);
    register_setting('simpleanalytics-advanced', $key, ['sanitize_callback' => $sanitize]);
    update_option($key, ' Stats.Example.Test ');
    sa_assert(get_option($key) === 'stats.example.test', 'Normalize a valid hostname.');
    sa_assert($analytics->path() === 'https://stats.example.test/latest.js', 'Authorized domains must work for visitors.');
    sa_assert($events->path() === 'https://stats.example.test/auto-events.js', 'Events must use the approved domain.');
    sa_assert($noscript->getCustomDomain() === 'https://stats.example.test/noscript.gif', 'Noscript must use the approved domain.');

    $hash = get_option($hashKey);
    foreach (['evil.example/x.js?', 'https://evil.example', 'evil.example:443', 'user@evil.example', 'evil.example#x', 'evil.example?x', '-evil.example', 'evil..example', '127.0.0.1', ['evil.example'], null] as $invalid) {
        sa_assert($sanitize($invalid) === 'stats.example.test', 'Malformed domains must preserve the previous value.');
        sa_assert(get_option($hashKey) === $hash, 'Invalid input must not change approval.');
    }

    $deny = static function ($caps, $cap) { return $cap === 'unfiltered_html' ? ['do_not_allow'] : $caps; };
    add_filter('map_meta_cap', $deny, 10, 2);
    update_option($key, 'evil.example');
    sa_assert(get_option($key) === 'stats.example.test', 'Restricted admins must not replace a script origin.');
    sa_assert(get_option($hashKey) === $hash, 'Restricted admins must not change approval.');
    remove_filter('map_meta_cap', $deny, 10);

    $originalBlogId = $GLOBALS['blog_id'];
    $GLOBALS['blog_id'] = 99999;
    sa_assert($analytics->path() === $default, 'Approval from another site must not be accepted.');
    $GLOBALS['blog_id'] = $originalBlogId;

    wp_set_current_user(0);
    sa_assert($analytics->path() === 'https://stats.example.test/latest.js', 'Approval belongs to the saved value, not the visitor.');
    remove_filter('sanitize_option_' . $key, $sanitize);
    update_option($key, 'evil.example');
    sa_assert($analytics->path() === $default, 'Changing an approved value must invalidate approval.');
    update_option($key, ['evil.example']);
    sa_assert($analytics->path() === $default, 'Malformed stored values must not cause a fatal error.');

    wp_set_current_user(get_user_by('login', 'admin')->ID);
    register_setting('simpleanalytics-advanced', $key, ['sanitize_callback' => $sanitize]);
    update_option($key, '');
    sa_assert($analytics->path() === $default, 'Clearing the domain restores the script CDN.');
    sa_assert($noscript->getCustomDomain() === 'https://queue.simpleanalyticscdn.com/noscript.gif', 'Clearing the domain restores the image CDN.');
});
