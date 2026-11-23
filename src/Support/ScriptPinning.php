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
    // release's scripts-v12.tar.gz, unmodified. Directories of earlier releases stay in
    // the plugin, so pages cached before a plugin update keep loading their scripts.
    const VERSION = 'v12';
    const DIRECTORY = 'resources/js/pinned/' . self::VERSION . '/';
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

    public static function analyticsScript(): string
    {
        return self::customDomainScript() ?? PLUGIN_URL . self::DIRECTORY . 'app.js';
    }

    public static function eventsScript(): string
    {
        return PLUGIN_URL . self::DIRECTORY . 'auto-events.js';
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
     */
    private static function customDomainScript(): ?string
    {
        $domain = CustomDomain::get('');
        if ($domain === '') return null;

        $template = file_get_contents(PLUGIN_PATH . self::DIRECTORY . 'custom/app.js');
        if (! is_string($template)) return null;
        $code = str_replace([self::HOST_PLACEHOLDER, self::CUSTOM_DOMAIN_SOURCE_MAP], [$domain, ''], $template);

        $uploads = wp_upload_dir(null, false);
        if (! empty($uploads['error'])) return null;
        $directory = $uploads['basedir'] . '/' . self::UPLOADS_DIRECTORY;
        // The version and domain are in the name, so browsers can cache it for good.
        $file = self::VERSION . '-' . $domain . '.js';

        if (! is_file("$directory/$file") || file_get_contents("$directory/$file") !== $code) {
            if (! wp_mkdir_p($directory) || file_put_contents("$directory/$file", $code, LOCK_EX) !== strlen($code)) {
                return null;
            }
            // Remove the scripts of earlier domains. Earlier versions for this domain stay,
            // so pages cached before a plugin update keep loading their script.
            foreach (glob("$directory/*.js") ?: [] as $old) {
                if (preg_match('/^v\d+-' . preg_quote($domain, '/') . '\.js$/', basename($old)) !== 1) wp_delete_file($old);
            }
        }

        return $uploads['baseurl'] . '/' . self::UPLOADS_DIRECTORY . '/' . $file;
    }
}
