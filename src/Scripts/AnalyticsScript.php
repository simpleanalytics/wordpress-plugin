<?php

namespace SimpleAnalytics\Scripts;

use SimpleAnalytics\Scripts\Contracts\HasAttributes;
use SimpleAnalytics\Scripts\Contracts\HideScriptId;
use SimpleAnalytics\Scripts\Contracts\Script;
use SimpleAnalytics\Setting;
use SimpleAnalytics\SettingName;

class AnalyticsScript implements Script, HasAttributes, HideScriptId
{
    public function path(): string
    {
        return sprintf("https://%s/latest.js", Setting::get(SettingName::CUSTOM_DOMAIN, 'scripts.simpleanalyticscdn.com'));
    }

    public function handle(): string
    {
        return 'simpleanalytics';
    }

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
