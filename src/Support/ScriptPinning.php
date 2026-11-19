<?php

namespace SimpleAnalytics\Support;

defined('ABSPATH') || exit;

use SimpleAnalytics\Setting;
use SimpleAnalytics\SettingName;
use const SimpleAnalytics\PLUGIN_PATH;
use const SimpleAnalytics\PLUGIN_URL;

/**
 * Pinned mode runs the scripts of one simpleanalytics/scripts release, bundled
 * with this plugin, instead of the latest scripts from the CDN.
 */
final class ScriptPinning
{
    // The scripts release tag. DIRECTORY holds dist/v12/app.js, dist/v12/custom/app.js,
    // dist/v12/auto-events.js and the source maps of app.js and auto-events.js from that
    // release's scripts-v12.tar.gz, unmodified.
    const VERSION = 'v12';
    const DIRECTORY = 'resources/js/pinned/' . self::VERSION . '/';
    // Fixed hashes of the bundled files, updated with them and never calculated at runtime.
    const INTEGRITY = 'sha384-hW/9WnZdBVODKX3qmD+1Tbu8NAw+eqwfTa6hcnBO8jNjTQgh/lkr8adCkM5Pysqc';
    const CUSTOM_DOMAIN_INTEGRITY = 'sha384-QCld7tlMzHkNDAxbcql6iYzbOEaLUjgLGnRyR1rk3Ov82ZMA2jHSjGQlpMnMQK5x';
    const EVENTS_INTEGRITY = 'sha384-SQFrB7qW0OT1xKu17wLDikKn7a4X2l6654U0ieJhW/jn6ge9W+NECt0eHBG2uTjO';
    // custom/app.js sends data to the domain it is served from: custom-domain
    // servers write their hostname over this placeholder, and so does the plugin.
    const HOST_PLACEHOLDER = '<!--# echo var="http_host" default="" -->';
    // The custom-domain source map is valid JSON only after its server fills in the
    // hostname, so it is not bundled, and the written script drops this reference.
    const CUSTOM_DOMAIN_SOURCE_MAP = "\n//# sourceMappingURL=app.js.map";
    // Scripts written for a custom domain, inside the uploads folder.
    const UPLOADS_DIRECTORY = 'simpleanalytics';

    public static function enabled(): bool
    {
        return Setting::boolean(SettingName::PIN_SCRIPTS, false) === true;
    }

    /**
     * @return array{src: string, integrity: string}
     */
    public static function analyticsScript(): array
    {
        return self::customDomainScript() ?? [
            'src' => PLUGIN_URL . self::DIRECTORY . 'app.js',
            'integrity' => self::INTEGRITY,
        ];
    }

    /**
     * @return array{src: string, integrity: string}
     */
    public static function eventsScript(): array
    {
        return [
            'src' => PLUGIN_URL . self::DIRECTORY . 'auto-events.js',
            'integrity' => self::EVENTS_INTEGRITY,
        ];
    }

    /**
     * Explains why pinned scripts cannot use the custom domain, for administrators.
     */
    public static function problem(): ?string
    {
        if (! self::enabled() || CustomDomain::get('') === '' || self::customDomainScript() !== null) {
            return null;
        }

        return 'Simple Analytics could not save the pinned script for your custom domain in the uploads folder, so visitors load the pinned script for the default domain. Make sure WordPress can write to its uploads folder.';
    }

    public static function notice(bool $inSettings = false): void
    {
        // The settings page uses a shadow root, which hides ordinary WP notices.
        $settingsPage = isset($_GET['page']) && $_GET['page'] === 'simpleanalytics';
        if ($settingsPage !== $inSettings || ! current_user_can('manage_options')) return;
        $problem = self::problem();
        if ($problem !== null) {
            echo '<div class="notice notice-warning" role="alert" style="border-left:4px solid #dba617;background:#fff;padding:12px;margin:12px 0"><p>' . esc_html($problem) . '</p></div>';
        }
    }

    /**
     * Writes the custom-domain build with the saved domain filled in, as the
     * custom-domain server would serve it, to the uploads folder.
     *
     * @return array{src: string, integrity: string}|null
     */
    private static function customDomainScript(): ?array
    {
        $domain = CustomDomain::get('');
        if ($domain === '') return null;

        $template = file_get_contents(PLUGIN_PATH . self::DIRECTORY . 'custom/app.js');
        if (! is_string($template) || 'sha384-' . base64_encode(hash('sha384', $template, true)) !== self::CUSTOM_DOMAIN_INTEGRITY) {
            return null;
        }
        $code = str_replace([self::HOST_PLACEHOLDER, self::CUSTOM_DOMAIN_SOURCE_MAP], [$domain, ''], $template);

        $uploads = wp_upload_dir(null, false);
        if (! empty($uploads['error'])) return null;
        $directory = $uploads['basedir'] . '/' . self::UPLOADS_DIRECTORY;
        // The domain and version are in the name, so browsers can cache it for good.
        $file = self::VERSION . '-' . $domain . '.js';

        if (! is_file("$directory/$file") || file_get_contents("$directory/$file") !== $code) {
            if (! wp_mkdir_p($directory) || file_put_contents("$directory/$file", $code, LOCK_EX) !== strlen($code)) {
                return null;
            }
            foreach (glob("$directory/*.js") ?: [] as $old) {
                if (basename($old) !== $file) wp_delete_file($old);
            }
        }

        return [
            'src' => $uploads['baseurl'] . '/' . self::UPLOADS_DIRECTORY . '/' . $file,
            'integrity' => 'sha384-' . base64_encode(hash('sha384', $code, true)),
        ];
    }
}
