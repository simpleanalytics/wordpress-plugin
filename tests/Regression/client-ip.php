<?php

defined('ABSPATH') || exit;

require __DIR__ . '/../Support/isolated-options.php';

sa_with_isolated_options(static function () {
    $key = SimpleAnalytics\SettingName::EXCLUDED_IP_ADDRESSES;
    $rules = new SimpleAnalytics\TrackingRules(new SimpleAnalytics\WordPressSettings());
    update_option($key, ['203.0.113.10', '2001:0DB8:0:0:0:0:0:1']);

    // Existing installations prefer a forwarded visitor over the proxy peer.
    $_SERVER['REMOTE_ADDR'] = '10.0.0.1';
    $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.10';
    sa_assert($rules->hasExcludedIp(), 'Existing forwarded visitor exclusions must keep working.');
    $_SERVER['HTTP_X_FORWARDED_FOR'] = ' 203.0.113.10, 10.0.0.2';
    sa_assert($rules->hasExcludedIp(), 'A proxy chain must resolve to its first visitor address.');

    // An office or local proxy reports the visitor's private address first;
    // the first public address is the one that identifies the visitor.
    foreach (['192.168.1.20, 203.0.113.10', '10.1.2.3, 127.0.0.1, 203.0.113.10', 'fd00::1, 203.0.113.10, 198.51.100.7', 'unknown, 203.0.113.10', ', 203.0.113.10'] as $chain) {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = $chain;
        sa_assert($rules->hasExcludedIp(), 'Skip private and invalid entries before the first public address: ' . $chain);
    }
    update_option($key, ['192.168.1.20']);
    $_SERVER['HTTP_X_FORWARDED_FOR'] = '192.168.1.20, 10.0.0.2';
    sa_assert($rules->hasExcludedIp(), 'Without a public address, the first private address still matches.');
    update_option($key, ['203.0.113.10', '2001:0DB8:0:0:0:0:0:1']);
    $_SERVER['HTTP_X_FORWARDED_FOR'] = '10.1.2.3, 203.0.113.10';

    $field = new SimpleAnalytics\Settings\Blocks\Fields\IpList($key, 'IP addresses');
    ob_start();
    $field->render();
    $html = ob_get_clean();
    sa_assert(strpos($html, 'Add Current IP (203.0.113.10)') !== false, 'The UI must offer the forwarded visitor used by tracking.');

    // Old UI saves may contain the proxy address. Never broaden those rules
    // to all visitors who share that proxy or a later public address in the chain.
    $_SERVER['REMOTE_ADDR'] = '203.0.113.10';
    $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.20, 203.0.113.10';
    sa_assert(! $rules->hasExcludedIp(), 'A shared proxy must not exclude an unrelated visitor.');
    $_SERVER['HTTP_X_FORWARDED_FOR'] = '10.1.2.3, 203.0.113.20, 203.0.113.10';
    sa_assert(! $rules->hasExcludedIp(), 'Only the first public address may match.');
    foreach (['invalid', '', ' , ', 'unknown, 10.0.0.300', []] as $invalid) {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = $invalid;
        sa_assert(! $rules->hasExcludedIp(), 'An invalid forwarded value must not fall back to an excluded proxy.');
    }

    unset($_SERVER['HTTP_X_FORWARDED_FOR']);
    sa_assert($rules->hasExcludedIp(), 'Direct visitor exclusions must keep working without a forwarded header.');

    $_SERVER['REMOTE_ADDR'] = '2001:db8::1';
    sa_assert($rules->hasExcludedIp(), 'Equivalent IPv6 addresses must match.');
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
