<?php

defined('WP_UNINSTALL_PLUGIN') || exit;

require_once __DIR__ . '/src/SettingName.php';

$deleteOptions = static function (): void {
    foreach (SimpleAnalytics\SettingName::cases() as $key) {
        delete_option($key);
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
