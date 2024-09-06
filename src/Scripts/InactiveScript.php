<?php

namespace SimpleAnalytics\Scripts;

use SimpleAnalytics\Scripts\Contracts\Script;
use const SimpleAnalytics\PLUGIN_URL;

class InactiveScript implements Script
{
    #[\Override]
    public function path(): string
    {
        return PLUGIN_URL . 'js/inactive.js';
    }

    #[\Override]
    public function handle(): string
    {
        return 'simpleanalytics_inactive';
    }
}
