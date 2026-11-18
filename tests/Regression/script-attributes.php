<?php

defined('ABSPATH') || exit;

require __DIR__ . '/../Support/isolated-options.php';

sa_with_isolated_options(static function () {
    $registry = new SimpleAnalytics\ScriptRegistry();
    $registry->push(new SimpleAnalytics\Scripts\AnalyticsScript());
    $registry->register();

    set_error_handler(static function ($severity, $message, $file, $line) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    });
    try {
        // Themes and other plugins may print script tags without an id.
        $tag = wp_get_script_tag(['src' => 'https://example.test/other.js']);
    } finally {
        restore_error_handler();
    }
    sa_assert(strpos($tag, 'data-platform') === false, 'Unrelated scripts must not receive our attributes.');

    // Third parties such as https://cookierocks.com/ detect Simple Analytics by data-platform="wordpress".
    $tag = wp_get_script_tag(['src' => 'https://example.test/latest.js', 'id' => 'simpleanalytics-js']);
    sa_assert(strpos($tag, 'data-platform="wordpress"') !== false, 'Our script must still receive its attributes.');
});
