<?php

namespace SimpleAnalytics\Actions;

use SimpleAnalytics\Enums\Setting;

class RegisterSettings
{
    public function __invoke(): void
    {
        register_setting('simpleanalytics_settings', Setting::CUSTOM_DOMAIN);
    }
}
