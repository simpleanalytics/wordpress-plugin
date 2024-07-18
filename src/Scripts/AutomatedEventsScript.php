<?php

namespace SimpleAnalytics\Scripts;

use SimpleAnalytics\Enums\SettingName;
use SimpleAnalytics\Fluent\Scripts\HasAttributes;
use SimpleAnalytics\Fluent\Scripts\Script;

class AutomatedEventsScript implements Script, HasAttributes
{
    public function path(): string
    {
        return sprintf(
            "https://%s/auto-events.js",
            get_option(SettingName::CUSTOM_DOMAIN, 'scripts.simpleanalyticscdn.com'),
        );
    }

    public function handle(): string
    {
        return 'simpleanalytics_auto_events';
    }

    public function attributes(): array
    {
        return array_filter([
            'data-collect'    => get_option(SettingName::EVENT_COLLECT),
            'data-extensions' => get_option(SettingName::EVENT_EXTENSIONS),
            'data-use-title'  => get_option(SettingName::EVENT_USE_TITLE),
            'data-full-urls'  => get_option(SettingName::EVENT_FULL_URLS),
            'data-sa-global'  => get_option(SettingName::EVENT_SA_GLOBAL),
        ]);
    }
}
