<?php

namespace SimpleAnalytics\Scripts;

use SimpleAnalytics\Fluent\Scripts\Script;

class InactiveScript implements Script
{
    public function path(): string
    {
        return SIMPLEANALYTICS_PLUGIN_URL. 'assets/js/inactive.js';
    }

    public function handle(): string
    {
        return 'simpleanalytics_inactive';
    }
}
