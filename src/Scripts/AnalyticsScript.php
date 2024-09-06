<?php

namespace SimpleAnalytics\Scripts;

use SimpleAnalytics\Enums\SettingName;
use SimpleAnalytics\Setting;

class AnalyticsScript implements Script, HasAttributes, HideScriptId
{
    #[\Override]
    public function path(): string
    {
        return sprintf(
            "https://%s/latest.js",
            Setting::get(SettingName::CUSTOM_DOMAIN, 'scripts.simpleanalyticscdn.com'),
        );
    }

    #[\Override]
    public function handle(): string
    {
        return 'simpleanalytics';
    }

    #[\Override]
    public function attributes(): array
    {
        return array_filter([
            'data-mode'         => Setting::boolean(SettingName::HASH_MODE) ? 'hash' : null,
            'data-collect-dnt'  => Setting::boolean(SettingName::COLLECT_DNT) ? 'true' : null,
            'data-ignore-pages' => Setting::get(SettingName::IGNORE_PAGES),
            'data-auto-collect' => Setting::get(SettingName::MANUAL_COLLECT) ? 'true' : null,
            'data-onload'       => Setting::get(SettingName::ONLOAD_CALLBACK),
            'data-sa-global'    => Setting::get(SettingName::SA_GLOBAL),
            'data-hostname'     => Setting::get(SettingName::HOSTNAME),
        ]);
    }
}
