<?php

defined('ABSPATH') || exit;

require __DIR__ . '/../Support/isolated-options.php';

use SimpleAnalytics\SettingName;
use SimpleAnalytics\Support\ScriptPinning;

sa_with_isolated_options(static function () {
    // Fixed hashes of every bundled scripts release, so a changed byte fails here.
    // Add the hashes of a new release from its files; never calculate them in this test.
    $releases = [
        'v12' => [
            'app.js' => 'sha384-hW/9WnZdBVODKX3qmD+1Tbu8NAw+eqwfTa6hcnBO8jNjTQgh/lkr8adCkM5Pysqc',
            'custom/app.js' => 'sha384-QCld7tlMzHkNDAxbcql6iYzbOEaLUjgLGnRyR1rk3Ov82ZMA2jHSjGQlpMnMQK5x',
            'auto-events.js' => 'sha384-SQFrB7qW0OT1xKu17wLDikKn7a4X2l6654U0ieJhW/jn6ge9W+NECt0eHBG2uTjO',
        ],
    ];
    $pinned = SimpleAnalytics\PLUGIN_PATH . 'resources/js/pinned/';
    $sri = static function (string $bytes): string {
        return 'sha384-' . base64_encode(hash('sha384', $bytes, true));
    };
    $bundled = static function (string $file, string $version = ScriptPinning::VERSION) use ($pinned): string {
        return (string) file_get_contents("$pinned$version/$file");
    };
    $uploads = wp_upload_dir(null, false);
    $written = $uploads['basedir'] . '/' . ScriptPinning::UPLOADS_DIRECTORY . '/' . ScriptPinning::VERSION . '-stats.example.test.js';

    foreach ([SettingName::PIN_SCRIPTS, SettingName::CUSTOM_DOMAIN] as $key) delete_option($key);
    $analytics = new SimpleAnalytics\Scripts\AnalyticsScript();
    $events = new SimpleAnalytics\Scripts\AutomatedEventsScript();
    sa_assert(! ScriptPinning::enabled(), 'Pinning must be opt-in.');
    sa_assert($analytics->path() === 'https://scripts.simpleanalyticscdn.com/latest.js', 'Default loading must remain unchanged.');

    // Every bundled release is unmodified, including earlier ones kept for cached pages.
    $directories = array_map('basename', glob($pinned . '*', GLOB_ONLYDIR) ?: []);
    sort($directories);
    $listed = array_keys($releases);
    sort($listed);
    sa_assert($directories === $listed, 'Every bundled release must have fixed hashes in this test.');
    sa_assert(isset($releases[ScriptPinning::VERSION]), 'The pinned release must be bundled.');
    foreach ($releases as $version => $hashes) {
        foreach ($hashes as $file => $hash) {
            sa_assert($sri($bundled($file, $version)) === $hash, "$version/$file must match its fixed hash.");
        }
        // Browsers request the source map a script names, so each bundled reference must resolve.
        foreach (['app.js', 'auto-events.js'] as $file) {
            sa_assert(preg_match('~\n//# sourceMappingURL=([^/\s]+)$~', $bundled($file, $version), $match) === 1, "$version/$file must end with a source map reference.");
            $map = json_decode($bundled($match[1], $version), true);
            sa_assert(is_array($map) && ($map['version'] ?? null) === 3, "$version/$file must have its source map bundled.");
        }
    }
    sa_assert(substr_count($bundled('custom/app.js'), ScriptPinning::HOST_PLACEHOLDER) === 1, 'The custom-domain build must have one domain placeholder.');
    sa_assert(substr($bundled('custom/app.js'), -strlen(ScriptPinning::CUSTOM_DOMAIN_SOURCE_MAP)) === ScriptPinning::CUSTOM_DOMAIN_SOURCE_MAP, 'The custom-domain build must end with the source map reference the plugin removes.');
    foreach (['app.js' => 'cdn_latest_12', 'custom/app.js' => 'custom_app_12', 'auto-events.js' => '; v12)'] as $file => $marker) {
        sa_assert(strpos($bundled($file), $marker) !== false, "$file must come from the " . ScriptPinning::VERSION . ' release.');
    }

    update_option(SettingName::PIN_SCRIPTS, 1);
    $base = SimpleAnalytics\PLUGIN_URL . ScriptPinning::DIRECTORY;
    sa_assert($analytics->path() === $base . 'app.js', 'Pinned mode must load the bundled script.');
    sa_assert(! isset($analytics->attributes()['integrity']) && ! isset($analytics->attributes()['crossorigin']), 'The site serves pinned scripts itself, so they need no integrity hash.');
    sa_assert($events->path() === $base . 'auto-events.js', 'Pinned events must be bundled.');
    sa_assert(! isset($events->attributes()['integrity']), 'Pinned events need no integrity hash.');
    sa_assert(ScriptPinning::problem() === null, 'Pinning without a custom domain needs no warning.');

    // A custom domain gets the custom-domain build with its domain filled in.
    update_option(SettingName::CUSTOM_DOMAIN, 'stats.example.test');
    $expected = str_replace([ScriptPinning::HOST_PLACEHOLDER, ScriptPinning::CUSTOM_DOMAIN_SOURCE_MAP], ['stats.example.test', ''], $bundled('custom/app.js'));
    sa_assert($analytics->path() === $uploads['baseurl'] . '/' . ScriptPinning::UPLOADS_DIRECTORY . '/' . ScriptPinning::VERSION . '-stats.example.test.js', 'A custom domain must load its script from uploads.');
    sa_assert(file_get_contents($written) === $expected, 'The written script must be the custom-domain build for this domain.');
    sa_assert(strpos($expected, '"stats.example.test"') !== false && strpos($expected, '<!--#') === false, 'The script must send data to the custom domain.');
    sa_assert(strpos($expected, 'sourceMappingURL') === false, 'The written script must not name a source map that does not exist.');
    sa_assert($events->path() === $base . 'auto-events.js', 'Events stay bundled with a custom domain.');
    wp_set_current_user(0);
    sa_assert($analytics->path() === $uploads['baseurl'] . '/' . ScriptPinning::UPLOADS_DIRECTORY . '/' . ScriptPinning::VERSION . '-stats.example.test.js', 'Visitors must get the same script.');

    file_put_contents($written, 'window.changed = true;');
    $analytics->path();
    sa_assert(file_get_contents($written) === $expected, 'A changed or stale file must be written again.');

    // A script written by an earlier release stays for pages cached before the update.
    $earlier = dirname($written) . '/v11-stats.example.test.js';
    file_put_contents($earlier, 'window.earlier = true;');
    file_put_contents($written, 'window.changed = true;');
    $analytics->path();
    sa_assert(file_exists($earlier), 'The earlier release script for this domain must stay.');

    update_option(SettingName::CUSTOM_DOMAIN, 'other.example.test');
    sa_assert(strpos($analytics->path(), ScriptPinning::VERSION . '-other.example.test.js') !== false, 'A new domain must get its own file.');
    sa_assert(! file_exists($written) && ! file_exists($earlier), 'The files for the previous domain must be removed.');

    // Without a writable uploads folder, keep tracking with the bundled default script.
    $broken = static function ($dirs) { $dirs['error'] = 'Unable to create directory.'; return $dirs; };
    add_filter('upload_dir', $broken);
    sa_assert($analytics->path() === $base . 'app.js', 'Without uploads, fall back to the bundled default script.');
    sa_assert(is_string(ScriptPinning::problem()), 'Administrators must learn why the custom domain is not used.');
    remove_filter('upload_dir', $broken);

    update_option(SettingName::PIN_SCRIPTS, 0);
    sa_assert($analytics->path() === 'https://other.example.test/latest.js', 'Without pinning, custom domains load their latest script.');

    foreach (glob($uploads['basedir'] . '/' . ScriptPinning::UPLOADS_DIRECTORY . '/*.js') ?: [] as $file) wp_delete_file($file);
});
