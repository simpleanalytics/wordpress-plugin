<?php

require __DIR__ . '/../Support/isolated-options.php';

sa_with_isolated_options(static function () {
    $key = SimpleAnalytics\SettingName::EXCLUDED_IP_ADDRESSES;
    $rules = new SimpleAnalytics\TrackingRules(new SimpleAnalytics\WordPressSettings());
    update_option($key, ['203.0.113.10', '2001:0DB8:0:0:0:0:0:1']);

    $_SERVER['REMOTE_ADDR'] = '203.0.113.10';
    $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.200, 10.0.0.1';
    sa_assert($rules->hasExcludedIp(), 'A forwarded chain must not override the address shown by Add Current IP.');
    $_SERVER['REMOTE_ADDR'] = '203.0.113.20';
    $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.10';
    sa_assert(! $rules->hasExcludedIp(), 'An untrusted header must not spoof an excluded visitor.');

    $_SERVER['REMOTE_ADDR'] = '2001:db8::1';
    sa_assert($rules->hasExcludedIp(), 'Equivalent IPv6 addresses must match.');
    $field = new SimpleAnalytics\Settings\Blocks\Fields\IpList($key, 'IP addresses');
    ob_start();
    $field->render();
    $html = ob_get_clean();
    sa_assert(strpos($html, 'Add Current IP (2001:db8::1)') !== false, 'The UI must use the same normalized address.');

    $override = static function ($ip) { return '203.0.113.10'; };
    add_filter('simpleanalytics_client_ip', $override);
    $_SERVER['REMOTE_ADDR'] = '10.0.0.1';
    sa_assert($rules->hasExcludedIp(), 'A deployment-specific IP resolver must apply to tracking.');
    ob_start();
    $field->render();
    $html = ob_get_clean();
    sa_assert(strpos($html, 'Add Current IP (203.0.113.10)') !== false, 'The same filter must apply to the UI.');
    remove_filter('simpleanalytics_client_ip', $override);

    foreach (['invalid', '203.0.113.10, 10.0.0.1', '', null, []] as $invalid) {
        $_SERVER['REMOTE_ADDR'] = $invalid;
        sa_assert(! $rules->hasExcludedIp(), 'Invalid addresses must not match.');
    }
    unset($_SERVER['REMOTE_ADDR']);
    sa_assert(! $rules->hasExcludedIp(), 'Missing addresses must not match.');
    ob_start();
    $field->render();
    $html = ob_get_clean();
    sa_assert(strpos($html, 'Add Current IP') === false, 'Hide the add button when the address is unavailable.');
});
