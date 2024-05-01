<?php

namespace SimpleAnalytics\Scripts;

class InactiveScript extends Script
{
    public function getPath(): string
    {
        return plugins_url('js/inactive.js', __FILE__);
    }

    public function getHandle(): string
    {
        return 'simpleanalytics_inactive';
    }
}
