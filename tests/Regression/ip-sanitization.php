<?php

require __DIR__ . '/../Support/isolated-options.php';

sa_with_isolated_options(static function () {
    $key = SimpleAnalytics\SettingName::EXCLUDED_IP_ADDRESSES;
    $field = new SimpleAnalytics\Settings\Blocks\Fields\IpList($key, 'IP addresses');
    $sanitize = $field->getValueSanitizer();
    register_setting('simpleanalytics-ignore-rules', $key, [
        'type' => $field->getValueType(),
        'sanitize_callback' => $sanitize,
    ]);

    delete_option($key);
    update_option($key, "203.0.113.10\n2001:db8::1");
    $expected = ['203.0.113.10', '2001:db8::1'];
    sa_assert(get_option($key) === $expected, 'First save must retain addresses after double sanitization.');
    sa_assert($sanitize($expected) === $expected, 'Sanitizing an array must preserve valid addresses.');
    sa_assert($sanitize($sanitize($expected)) === $expected, 'Sanitization must be idempotent.');
    sa_assert($sanitize([' 203.0.113.10 ', '203.0.113.10', 'invalid', [], null, new stdClass()]) === ['203.0.113.10'], 'Reject malformed items and remove duplicates.');
    foreach ([null, false, 123, new stdClass(), ''] as $invalid) {
        sa_assert($sanitize($invalid) === [], 'Malformed or empty input must produce an empty list.');
    }
    update_option($key, "203.0.113.20\r\ninvalid\r\n203.0.113.20");
    sa_assert(get_option($key) === ['203.0.113.20'], 'Existing options must still accept textarea updates.');
});
