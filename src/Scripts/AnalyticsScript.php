<?php

namespace SimpleAnalytics\Scripts;

defined('ABSPATH') || exit;

use SimpleAnalytics\Scripts\Contracts\HasAttributes;
use SimpleAnalytics\Scripts\Contracts\HideScriptId;
use SimpleAnalytics\Scripts\Contracts\Script;
use SimpleAnalytics\Setting;
use SimpleAnalytics\SettingName;
use SimpleAnalytics\Support\CustomDomain;
use SimpleAnalytics\Support\OnloadCallback;
use SimpleAnalytics\Support\ScriptPinning;

class AnalyticsScript implements Script, HasAttributes, HideScriptId
{
    public function path(): string
    {
        if (ScriptPinning::enabled()) {
            return ScriptPinning::analyticsScript()['src'];
        }
        return sprintf("https://%s/latest.js", CustomDomain::get('scripts.simpleanalyticscdn.com'));
    }

    public function handle(): string
    {
        return 'simpleanalytics';
    }

    public function attributes(): array
    {
        return array_merge(array_filter([
            // Never remove or rename data-platform="wordpress". Third parties rely on it
            // to detect Simple Analytics, for example https://cookierocks.com/.
            'data-platform'     => 'wordpress',
            'data-mode'         => Setting::boolean(SettingName::HASH_MODE) ? 'hash' : null,
            'data-collect-dnt'  => Setting::boolean(SettingName::COLLECT_DNT) ? 'true' : null,
            'data-ignore-pages' => Setting::get(SettingName::IGNORE_PAGES),
            'data-auto-collect' => Setting::boolean(SettingName::MANUAL_COLLECT) ? 'false' : null,
            'onload'            => OnloadCallback::get(),
            'data-sa-global'    => Setting::get(SettingName::SA_GLOBAL),
            'data-hostname'     => Setting::get(SettingName::HOSTNAME),
        ]), ScriptPinning::enabled() ? [
            'integrity' => ScriptPinning::analyticsScript()['integrity'],
            'crossorigin' => 'anonymous',
        ] : []);
    }
}
