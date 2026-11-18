<?php

require __DIR__ . '/../Support/isolated-options.php';

sa_with_isolated_options(static function () {
    $key = SimpleAnalytics\SettingName::ONLOAD_CALLBACK;
    $hashKey = SimpleAnalytics\SettingName::ONLOAD_CALLBACK_HASH;
    $script = new SimpleAnalytics\Scripts\AnalyticsScript();

    delete_option($hashKey);
    update_option($key, 'legacyCallback()');
    sa_assert(! isset($script->attributes()['onload']), 'Unverified legacy text must remain inert.');

    $admin = get_user_by('login', 'admin');
    sa_assert($admin !== false, 'The wp-env admin user must exist.');
    wp_set_current_user($admin->ID);
    $field = new SimpleAnalytics\Settings\Blocks\Fields\Input($key, 'Onload Callback');
    register_setting('simpleanalytics-advanced', $key, ['sanitize_callback' => $field->getValueSanitizer()]);
    update_option($key, 'authorizedCallback()');
    sa_assert(($script->attributes()['onload'] ?? null) === 'authorizedCallback()', 'Authorized saves must enable the callback.');

    // Simulate a multisite administrator (or DISALLOW_UNFILTERED_HTML) through
    // WordPress's real capability mapping, without changing any user records.
    $deny = static function ($caps, $cap) { return $cap === 'unfiltered_html' ? ['do_not_allow'] : $caps; };
    add_filter('map_meta_cap', $deny, 10, 2);
    $hash = get_option($hashKey);
    update_option($key, 'unauthorizedCallback()');
    sa_assert(get_option($key) === 'authorizedCallback()', 'Users without unfiltered_html must not replace the callback.');
    sa_assert(get_option($hashKey) === $hash, 'Unauthorized saves must not change the recorded hash.');
    remove_filter('map_meta_cap', $deny, 10);

    remove_filter('sanitize_option_' . $key, $field->getValueSanitizer());
    update_option($key, 'changedOutsideSettings()');
    sa_assert(! isset($script->attributes()['onload']), 'Code that differs from the verified value must remain inert.');
    register_setting('simpleanalytics-advanced', $key, ['sanitize_callback' => $field->getValueSanitizer()]);
    update_option($key, '');
    sa_assert(! isset($script->attributes()['onload']), 'Clearing the callback must remove the handler.');
});
