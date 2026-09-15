<?php

require __DIR__ . '/../Support/isolated-options.php';

sa_with_isolated_options(static function () {
    $plugin = SimpleAnalytics\PLUGIN_BASENAME;
    $values = [
        SimpleAnalytics\SettingName::CUSTOM_DOMAIN => 'stats.example.test',
        SimpleAnalytics\SettingName::EXCLUDED_IP_ADDRESSES => ['203.0.113.10'],
        SimpleAnalytics\SettingName::EXCLUDED_ROLES => ['administrator'],
        SimpleAnalytics\SettingName::MANUAL_COLLECT => '1',
    ];
    foreach ($values as $key => $value) update_option($key, $value);

    deactivate_plugins($plugin, false, false);
    sa_assert(! is_plugin_active($plugin), 'The plugin must be deactivated for this test.');
    foreach ($values as $key => $value) {
        sa_assert(get_option($key) === $value, 'Deactivation deleted ' . $key);
    }

    // The plugin is already loaded in this process; fire WordPress's activation
    // hook directly to verify that activation retains the existing options.
    do_action('activate_' . $plugin, false);
    foreach ($values as $key => $value) {
        sa_assert(get_option($key) === $value, 'Reactivation changed ' . $key);
    }

    update_option('sa_unrelated_test_option', 'keep');
    uninstall_plugin($plugin);
    foreach (SimpleAnalytics\SettingName::cases() as $key) {
        sa_assert(get_option($key, 'missing') === 'missing', 'Uninstall did not remove ' . $key);
    }
    sa_assert(get_option('sa_unrelated_test_option') === 'keep', 'Uninstall must retain unrelated options.');
});
