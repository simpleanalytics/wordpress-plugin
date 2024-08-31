<?php

namespace SimpleAnalytics\Actions;

use SimpleAnalytics\Enums\SettingName;
use SimpleAnalytics\Foundation\Scripts\ScriptCollection;
use SimpleAnalytics\Scripts\AnalyticsScript;
use SimpleAnalytics\Scripts\AutomatedEventsScript;
use SimpleAnalytics\Scripts\InactiveScript;

class Scripts extends Action
{
    protected string $hook = 'init';

    public function __construct(protected bool $shouldCollect)
    {
    }

    public function handle(): void
    {
        $scripts = new ScriptCollection;

        if ($this->shouldCollect) {
            $scripts->add(new AnalyticsScript);
        } else {
            $scripts->add(new InactiveScript);
        }

        if (get_option(SettingName::EVENT_COLLECT)) {
            $scripts->add(new AutomatedEventsScript);
        }

        $scripts->register();
    }
}
