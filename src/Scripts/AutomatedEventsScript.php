<?php

namespace SimpleAnalytics\Scripts;

defined('ABSPATH') || exit;

use SimpleAnalytics\Scripts\Contracts\HasAttributes;
use SimpleAnalytics\Scripts\Contracts\HideScriptId;
use SimpleAnalytics\Scripts\Contracts\Script;
use SimpleAnalytics\Setting;
use SimpleAnalytics\SettingName;
use SimpleAnalytics\Support\CustomDomain;
use SimpleAnalytics\Support\ScriptPinning;

class AutomatedEventsScript implements Script, HasAttributes, HideScriptId
{
    public function path(): string
    {
        if (ScriptPinning::enabled()) {
            return ScriptPinning::eventsScript()['src'];
        }
        return sprintf("https://%s/auto-events.js", CustomDomain::get('scripts.simpleanalyticscdn.com'));
    }

    public function handle(): string
    {
        return 'simpleanalytics_auto_events';
    }

    public function attributes(): array
    {
        return array_merge(array_filter([
            'data-collect'    => Setting::get(SettingName::EVENT_COLLECT_DOWNLOADS),
            'data-extensions' => Setting::get(SettingName::EVENT_EXTENSIONS),
            'data-use-title'  => Setting::boolean(SettingName::EVENT_USE_TITLE) ? 'true' : 'false',
            'data-full-urls'  => Setting::boolean(SettingName::EVENT_FULL_URLS) ? 'true' : 'false',
            'data-sa-global'  => Setting::get(SettingName::EVENT_SA_GLOBAL),
        ]), ScriptPinning::enabled() ? [
            'integrity' => ScriptPinning::eventsScript()['integrity'],
            'crossorigin' => 'anonymous',
        ] : []);
    }
}
