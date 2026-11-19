<?php

defined('ABSPATH') || exit;

require __DIR__ . '/../Support/isolated-options.php';

use SimpleAnalytics\SettingName;
use SimpleAnalytics\Support\ScriptPinning;

sa_with_isolated_options(static function () {
    $sri = static function (string $bytes): string {
        return 'sha384-' . base64_encode(hash('sha384', $bytes, true));
    };
    $bundled = static function (string $file): string {
        return (string) file_get_contents(SimpleAnalytics\PLUGIN_PATH . ScriptPinning::DIRECTORY . $file);
    };
    $uploads = wp_upload_dir(null, false);
    $written = $uploads['basedir'] . '/' . ScriptPinning::UPLOADS_DIRECTORY . '/' . ScriptPinning::VERSION . '-stats.example.test.js';

    foreach ([SettingName::PIN_SCRIPTS, SettingName::CUSTOM_DOMAIN] as $key) delete_option($key);
    $analytics = new SimpleAnalytics\Scripts\AnalyticsScript();
    $events = new SimpleAnalytics\Scripts\AutomatedEventsScript();
    sa_assert(! ScriptPinning::enabled(), 'Pinning must be opt-in.');
    sa_assert($analytics->path() === 'https://scripts.simpleanalyticscdn.com/latest.js', 'Default loading must remain unchanged.');
    sa_assert(! isset($analytics->attributes()['integrity']), 'Default mode must not pin a changing script.');

    // The bundled files are the scripts release, and their fixed hashes match their bytes.
    sa_assert($sri($bundled('app.js')) === ScriptPinning::INTEGRITY, 'app.js must match its fixed hash.');
    sa_assert($sri($bundled('custom/app.js')) === ScriptPinning::CUSTOM_DOMAIN_INTEGRITY, 'custom/app.js must match its fixed hash.');
    sa_assert($sri($bundled('auto-events.js')) === ScriptPinning::EVENTS_INTEGRITY, 'auto-events.js must match its fixed hash.');
    sa_assert(substr_count($bundled('custom/app.js'), ScriptPinning::HOST_PLACEHOLDER) === 1, 'The custom-domain build must have one domain placeholder.');
    sa_assert(substr($bundled('custom/app.js'), -strlen(ScriptPinning::CUSTOM_DOMAIN_SOURCE_MAP)) === ScriptPinning::CUSTOM_DOMAIN_SOURCE_MAP, 'The custom-domain build must end with the source map reference the plugin removes.');
    // Browsers request the source map a script names, so each bundled reference must resolve.
    foreach (['app.js', 'auto-events.js'] as $file) {
        sa_assert(preg_match('~\n//# sourceMappingURL=([^/\s]+)$~', $bundled($file), $match) === 1, "$file must end with a source map reference.");
        $map = json_decode($bundled(dirname($file) . '/' . $match[1]), true);
        sa_assert(is_array($map) && ($map['version'] ?? null) === 3, "$file must have its source map bundled.");
    }
    foreach (['app.js' => 'cdn_latest_12', 'custom/app.js' => 'custom_app_12', 'auto-events.js' => '; v12)'] as $file => $marker) {
        sa_assert(strpos($bundled($file), $marker) !== false, "$file must come from the " . ScriptPinning::VERSION . ' release.');
    }

    update_option(SettingName::PIN_SCRIPTS, 1);
    $base = SimpleAnalytics\PLUGIN_URL . ScriptPinning::DIRECTORY;
    sa_assert($analytics->path() === $base . 'app.js', 'Pinned mode must load the bundled script.');
    sa_assert($analytics->attributes()['integrity'] === ScriptPinning::INTEGRITY, 'The bundled script must carry its hash.');
    sa_assert($analytics->attributes()['crossorigin'] === 'anonymous', 'Integrity checks need CORS when assets are served from another origin.');
    sa_assert($events->path() === $base . 'auto-events.js', 'Pinned events must be bundled.');
    sa_assert($events->attributes()['integrity'] === ScriptPinning::EVENTS_INTEGRITY, 'Pinned events must carry their hash.');
    sa_assert(ScriptPinning::problem() === null, 'Pinning without a custom domain needs no warning.');

    // A custom domain gets the custom-domain build with its domain filled in.
    update_option(SettingName::CUSTOM_DOMAIN, 'stats.example.test');
    $expected = str_replace([ScriptPinning::HOST_PLACEHOLDER, ScriptPinning::CUSTOM_DOMAIN_SOURCE_MAP], ['stats.example.test', ''], $bundled('custom/app.js'));
    sa_assert($analytics->path() === $uploads['baseurl'] . '/' . ScriptPinning::UPLOADS_DIRECTORY . '/' . ScriptPinning::VERSION . '-stats.example.test.js', 'A custom domain must load its script from uploads.');
    sa_assert(file_get_contents($written) === $expected, 'The written script must be the custom-domain build for this domain.');
    sa_assert(strpos($expected, '"stats.example.test"') !== false && strpos($expected, '<!--#') === false, 'The script must send data to the custom domain.');
    sa_assert(strpos($expected, 'sourceMappingURL') === false, 'The written script must not name a source map that does not exist.');
    sa_assert($analytics->attributes()['integrity'] === $sri($expected), 'The written script must carry its own hash.');
    sa_assert($events->path() === $base . 'auto-events.js', 'Events stay bundled with a custom domain.');
    wp_set_current_user(0);
    sa_assert($analytics->path() === $uploads['baseurl'] . '/' . ScriptPinning::UPLOADS_DIRECTORY . '/' . ScriptPinning::VERSION . '-stats.example.test.js', 'Visitors must get the same script.');

    file_put_contents($written, 'window.changed = true;');
    $analytics->path();
    sa_assert(file_get_contents($written) === $expected, 'A changed or stale file must be written again.');

    update_option(SettingName::CUSTOM_DOMAIN, 'other.example.test');
    sa_assert(strpos($analytics->path(), ScriptPinning::VERSION . '-other.example.test.js') !== false, 'A new domain must get its own file.');
    sa_assert(! file_exists($written), 'The file for the previous domain must be removed.');

    // Without a writable uploads folder, keep tracking with the bundled default script.
    $broken = static function ($dirs) { $dirs['error'] = 'Unable to create directory.'; return $dirs; };
    add_filter('upload_dir', $broken);
    sa_assert($analytics->path() === $base . 'app.js', 'Without uploads, fall back to the bundled default script.');
    sa_assert($analytics->attributes()['integrity'] === ScriptPinning::INTEGRITY, 'The fallback must keep its hash.');
    sa_assert(is_string(ScriptPinning::problem()), 'Administrators must learn why the custom domain is not used.');
    remove_filter('upload_dir', $broken);

    update_option(SettingName::PIN_SCRIPTS, 0);
    sa_assert($analytics->path() === 'https://other.example.test/latest.js', 'Without pinning, custom domains load their latest script.');

    foreach (glob($uploads['basedir'] . '/' . ScriptPinning::UPLOADS_DIRECTORY . '/*.js') ?: [] as $file) wp_delete_file($file);
});
