<?php

namespace SimpleAnalytics\Scripts;

use SimpleAnalytics\Enums\SettingName;
use SimpleAnalytics\Setting;

class AnalyticsScript implements Script, HasAttributes, HiddenScriptId
{
    public function path(): string
    {
        return sprintf(
            "https://%s/latest.js",
            Setting::get(SettingName::CUSTOM_DOMAIN, 'scripts.simpleanalyticscdn.com'),
        );
    }

    public function handle(): string
    {
        return 'simpleanalytics';
    }

    public function attributes(): array
    {
        return array_filter([
            'data-mode'         => Setting::get(SettingName::HASH_MODE),
            'data-collect-dnt'  => Setting::get(SettingName::COLLECT_DNT),
            'data-ignore-pages' => Setting::get(SettingName::IGNORE_PAGES),
            'data-auto-collect' => Setting::get(SettingName::MANUAL_COLLECT),
            'data-onload'       => Setting::get(SettingName::ONLOAD_CALLBACK),
            'data-sa-global'    => Setting::get(SettingName::SA_GLOBAL),
        ]);
    }
}
