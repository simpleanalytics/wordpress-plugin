<?php

namespace SimpleAnalytics\Scripts;

use SimpleAnalytics\Enums\Setting;

class AnalyticsScript extends Script
{
    public function getPath(): string
    {
        return sprintf(
            "https://%s/latest.js",
            get_option(Setting::CUSTOM_DOMAIN, 'scripts.simpleanalyticscdn.com'),
        );
    }

    public function getHandle(): string
    {
        return 'simpleanalytics_script';
    }

    public function getAttributes(): array
    {
        return array_filter([
            'data-mode'         => get_option(Setting::MODE),
            'data-collect-dnt'  => get_option(Setting::COLLECT_DNT),
            'data-ignore-pages' => get_option(Setting::IGNORE_PAGES),
            'data-auto-collect' => get_option(Setting::AUTO_COLLECT),
            'data-onload'       => get_option(Setting::ONLOAD_CALLBACK),
            'data-sa-global'    => get_option(Setting::SA_GLOBAL),
        ]);
    }
}
