<?php

namespace SimpleAnalytics\Actions;

use SimpleAnalytics\Enums\SettingName;
use SimpleAnalytics\ScriptCollection;
use SimpleAnalytics\Scripts\{AnalyticsScript, AutomatedEventsScript, InactiveScript};
use SimpleAnalytics\TrackingPolicy;

class AnalyticsCode extends Action
{
    protected string $hook = 'init';

    public function __construct(
        protected TrackingPolicy $trackingPolicy = new TrackingPolicy,
    ) {
    }

    public function handle(): void
    {
        $collect = $this->trackingPolicy->shouldCollectAnalytics();
        $this->addScripts($collect);
        FooterContents::register($collect);
    }

    protected function addScripts(bool $collect): void
    {
        $scripts = new ScriptCollection;

        if ($collect) {
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
