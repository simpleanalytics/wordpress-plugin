<?php

namespace SimpleAnalytics\Scripts;

use SimpleAnalytics\Enums\SettingName;
use SimpleAnalytics\Setting;

class AutomatedEventsScript implements Script, HasAttributes, HideScriptId
{
    #[\Override]
    public function path(): string
    {
        return sprintf(
            "https://%s/auto-events.js",
            Setting::get(SettingName::CUSTOM_DOMAIN, 'scripts.simpleanalyticscdn.com'),
        );
    }

    #[\Override]
    public function handle(): string
    {
        return 'simpleanalytics_auto_events';
    }

    #[\Override]
    public function attributes(): array
    {
        return array_filter([
            'data-collect'    => Setting::get(SettingName::EVENT_COLLECT_DOWNLOADS),
            'data-extensions' => Setting::get(SettingName::EVENT_EXTENSIONS),
            'data-use-title'  => Setting::get(SettingName::EVENT_USE_TITLE),
            'data-full-urls'  => Setting::get(SettingName::EVENT_FULL_URLS),
            'data-sa-global'  => Setting::get(SettingName::EVENT_SA_GLOBAL),
        ]);
    }
}
