<?php

defined('ABSPATH') || exit;

require __DIR__ . '/../Support/isolated-options.php';

use SimpleAnalytics\SettingName;
use SimpleAnalytics\Support\CustomDomain;
use SimpleAnalytics\Support\ScriptPinning;

sa_with_isolated_options(static function () {
    // Each approval below models a separate settings request.
    $newRequest = static function () {
        $property = new ReflectionProperty(ScriptPinning::class, 'domainChanged');
        $property->setAccessible(true);
        $property->setValue(null, false);
    };
    wp_set_current_user(get_user_by('login', 'admin')->ID);
    foreach ([SettingName::PIN_SCRIPTS, SettingName::CUSTOM_DOMAIN, SettingName::CUSTOM_SCRIPT_INTEGRITY, SettingName::CUSTOM_SCRIPT_APPROVAL] as $key) delete_option($key);
    $analytics = new SimpleAnalytics\Scripts\AnalyticsScript();
    $events = new SimpleAnalytics\Scripts\AutomatedEventsScript();
    sa_assert(! ScriptPinning::enabled(), 'Pinning must be opt-in.');
    sa_assert($analytics->path() === 'https://scripts.simpleanalyticscdn.com/latest.js', 'Default loading must remain unchanged.');
    sa_assert(! isset($analytics->attributes()['integrity']), 'Default mode must not pin a changing script.');
    update_option(SettingName::PIN_SCRIPTS, 1);
    sa_assert(ScriptPinning::ready(), 'The official CDN requires no user hash.');
    sa_assert($analytics->path() === 'https://scripts.simpleanalyticscdn.com/sri/v12.js', 'Pinned mode must use the versioned endpoint.');
    sa_assert($analytics->attributes()['integrity'] === ScriptPinning::INTEGRITY, 'Main script must use the release hash.');
    sa_assert($analytics->attributes()['crossorigin'] === 'anonymous', 'Cross-origin integrity needs CORS.');
    sa_assert($events->path() === SimpleAnalytics\PLUGIN_URL . ScriptPinning::EVENTS_PATH, 'Pinned events must be bundled.');
    sa_assert('sha384-' . base64_encode(hash_file('sha384', SimpleAnalytics\PLUGIN_PATH . ScriptPinning::EVENTS_PATH, true)) === $events->attributes()['integrity'], 'The bundled bytes must match their fixed release hash.');

    $field = new SimpleAnalytics\Settings\Blocks\Fields\Input(SettingName::CUSTOM_SCRIPT_INTEGRITY, 'test');
    register_setting('test', SettingName::CUSTOM_SCRIPT_INTEGRITY, ['sanitize_callback' => $field->getValueSanitizer()]);
    register_setting('test', SettingName::CUSTOM_DOMAIN, ['sanitize_callback' => [CustomDomain::class, 'sanitize']]);
    update_option(SettingName::CUSTOM_DOMAIN, 'stats.example.test');
    $hash = 'sha384-' . base64_encode(hash('sha384', 'approved script', true));
    update_option(SettingName::CUSTOM_SCRIPT_INTEGRITY, $hash);
    sa_assert(! ScriptPinning::ready(), 'Changing a domain and approving a hash requires separate saves.');
    $newRequest();
    update_option(SettingName::CUSTOM_SCRIPT_INTEGRITY, $hash);
    sa_assert(ScriptPinning::ready(), 'An authorized hash must allow tracking.');
    sa_assert($analytics->attributes()['integrity'] === $hash, 'The custom domain must use its own hash.');
    sa_assert($analytics->path() === 'https://stats.example.test/sri/v12.js', 'Custom domains use the documented versioned route.');
    wp_set_current_user(0);
    sa_assert(ScriptPinning::ready(), 'Saved approval must work for anonymous visitors.');
    wp_set_current_user(get_user_by('login', 'admin')->ID);
    foreach ([[], null, 'sha256-' . str_repeat('A', 64), 'sha384-bad', $hash . ' ' . $hash, '<script>'] as $invalid) {
        sa_assert(ScriptPinning::sanitizeIntegrity($invalid) === $hash, 'Malformed hash input must preserve the current approval.');
    }
    $deny = static function ($caps, $cap) { return $cap === 'unfiltered_html' ? ['do_not_allow'] : $caps; };
    add_filter('map_meta_cap', $deny, 10, 2);
    update_option(SettingName::CUSTOM_SCRIPT_INTEGRITY, 'sha384-' . str_repeat('A', 64));
    sa_assert(ScriptPinning::customIntegrity() === $hash, 'Restricted administrators must not replace approved code.');
    remove_filter('map_meta_cap', $deny, 10);
    $blogId = $GLOBALS['blog_id'];
    $GLOBALS['blog_id'] = 99999;
    sa_assert(! ScriptPinning::ready(), 'Approval must not transfer to another site.');
    $GLOBALS['blog_id'] = $blogId;
    update_option(SettingName::CUSTOM_SCRIPT_APPROVAL, hash_hmac('sha256', 'script-integrity:' . get_current_blog_id() . ':stats.example.test:v11:' . $hash, wp_salt('auth')));
    sa_assert(! ScriptPinning::ready(), 'Approval must not transfer to another version.');
    ScriptPinning::sanitizeIntegrity($hash);
    sa_assert(! ScriptPinning::ready(), 'An unrelated settings save must not reapprove a stale hash.');

    wp_set_current_user(0);
    update_option(SettingName::AUTOMATED_EVENTS, 1);
    update_option(SettingName::NOSCRIPT, 1);
    do_action('wp_enqueue_scripts');
    sa_assert(! wp_script_is('simpleanalytics', 'enqueued') && ! wp_script_is('simpleanalytics_auto_events', 'enqueued'), 'Invalid approval must suppress both scripts without a fallback.');

    wp_set_current_user(get_user_by('login', 'admin')->ID);
    update_option(SettingName::CUSTOM_SCRIPT_INTEGRITY, '');
    update_option(SettingName::CUSTOM_SCRIPT_INTEGRITY, $hash);
    sa_assert(ScriptPinning::ready(), 'Clearing then explicitly approving must restore tracking.');
    update_option(SettingName::CUSTOM_DOMAIN, 'other.example.test');
    sa_assert(! ScriptPinning::ready(), 'Changing domains must invalidate approval.');
    sa_assert(get_option(SettingName::CUSTOM_SCRIPT_INTEGRITY, '') === '', 'Changing domains must clear the old hash.');
    $newRequest();
    update_option(SettingName::CUSTOM_SCRIPT_INTEGRITY, $hash);
    sa_assert(ScriptPinning::ready(), 'A new domain can be explicitly approved.');
    remove_filter('sanitize_option_' . SettingName::CUSTOM_SCRIPT_INTEGRITY, $field->getValueSanitizer());
    update_option(SettingName::CUSTOM_SCRIPT_INTEGRITY, 'sha384-' . str_repeat('B', 64));
    sa_assert(! ScriptPinning::ready(), 'Direct option tampering must not authorize another hash.');
    update_option(SettingName::CUSTOM_DOMAIN, '');
    sa_assert(ScriptPinning::ready(), 'Clearing a custom domain must restore the pinned CDN.');
});
