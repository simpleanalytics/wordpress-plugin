<?php

namespace SimpleAnalytics\Scripts;

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
