<?php

namespace SimpleAnalytics\Scripts;

use SimpleAnalytics\Foundation\Scripts\Script;

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
