<?php

namespace SimpleAnalytics\Scripts;

use const SimpleAnalytics\PLUGIN_URL;

class InactiveScript implements Script
{
    public function path(): string
    {
        return PLUGIN_URL . 'assets/js/inactive.js';
    }

    public function handle(): string
    {
        return 'simpleanalytics_inactive';
    }
}
