<?php

namespace SimpleAnalytics\Scripts;

use SimpleAnalytics\Scripts\Contracts\Script;
use const SimpleAnalytics\PLUGIN_URL;

class InactiveScript implements Script
{
    public function path(): string
    {
        return PLUGIN_URL . 'resources/js/inactive.js';
    }

    public function handle(): string
    {
        return 'simpleanalytics_inactive';
    }
}
