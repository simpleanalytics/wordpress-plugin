<?php

namespace SimpleAnalytics\Scripts;

use SimpleAnalytics\Enums\SettingName;
use SimpleAnalytics\Fluent\Scripts\HasAttributes;
use SimpleAnalytics\Fluent\Scripts\Script;

class AnalyticsScript implements Script, HasAttributes
{
    public function path(): string
    {
        return sprintf(
            "https://%s/latest.js",
            get_option(SettingName::CUSTOM_DOMAIN, 'scripts.simpleanalyticscdn.com'),
        );
    }

    public function handle(): string
    {
        return 'simpleanalytics_script';
    }

    public function attributes(): array
    {
        return array_filter([
            'data-mode'         => get_option(SettingName::MODE),
            'data-collect-dnt'  => get_option(SettingName::COLLECT_DNT),
            'data-ignore-pages' => get_option(SettingName::IGNORE_PAGES),
            'data-auto-collect' => get_option(SettingName::MANUAL_COLLECT),
            'data-onload'       => get_option(SettingName::ONLOAD_CALLBACK),
            'data-sa-global'    => get_option(SettingName::SA_GLOBAL),
        ]);
    }
}
