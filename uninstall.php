<?php

defined('WP_UNINSTALL_PLUGIN') || exit;

require_once __DIR__ . '/src/SettingName.php';
require_once __DIR__ . '/src/Support/ScriptPinning.php';

$deleteOptions = static function (): void {
    foreach (SimpleAnalytics\SettingName::cases() as $key) {
        delete_option($key);
    }

    // Pinned scripts written for a custom domain.
    $uploads = wp_upload_dir(null, false);
    $directory = $uploads['basedir'] . '/' . SimpleAnalytics\Support\ScriptPinning::UPLOADS_DIRECTORY;
    if (empty($uploads['error']) && is_dir($directory)) {
        foreach (glob($directory . '/*.js') ?: [] as $file) {
            wp_delete_file($file);
        }
        if (! (new FilesystemIterator($directory))->valid()) {
            rmdir($directory);
        }
    }
};

if (is_multisite()) {
    $offset = 0;
    do {
        $siteIds = get_sites(['fields' => 'ids', 'number' => 100, 'offset' => $offset, 'orderby' => 'id', 'order' => 'ASC']);
        foreach ($siteIds as $siteId) {
            switch_to_blog($siteId);
            try {
                $deleteOptions();
            } finally {
                restore_current_blog();
            }
        }
        $offset += count($siteIds);
    } while (count($siteIds) === 100);
} else {
    $deleteOptions();
}
