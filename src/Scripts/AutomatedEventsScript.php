<?php

namespace SimpleAnalytics\Scripts;

use SimpleAnalytics\Enums\Setting;

class AutomatedEventsScript extends Script
{
    public function getPath(): string
    {
        return sprintf(
            "https://%s/auto-events.js",
            get_option(Setting::CUSTOM_DOMAIN, 'scripts.simpleanalyticscdn.com'),
        );
    }

    public function getHandle(): string
    {
        return 'simpleanalytics_auto_events';
    }

    public function getAttributes(): array
    {
        return array_filter([
            'data-collect'    => get_option(Setting::EVENT_COLLECT),
            'data-extensions' => get_option(Setting::EVENT_EXTENSIONS),
            'data-use-title'  => get_option(Setting::EVENT_USE_TITLE),
            'data-full-urls'  => get_option(Setting::EVENT_FULL_URLS),
            'data-sa-global'  => get_option(Setting::EVENT_SA_GLOBAL),
        ]);
    }
}
