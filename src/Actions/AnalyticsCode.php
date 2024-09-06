<?php

namespace SimpleAnalytics\Actions;

use SimpleAnalytics\ScriptCollection;
use SimpleAnalytics\Scripts\{AnalyticsScript, AutomatedEventsScript, InactiveScript};
use SimpleAnalytics\Setting;
use SimpleAnalytics\SettingName;
use SimpleAnalytics\TrackingPolicy;

class AnalyticsCode extends Action
{
    protected string $hook = 'init';

    public function __construct(
        protected TrackingPolicy $trackingPolicy = new TrackingPolicy,
    ) {
    }

    #[\Override]
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

        if (Setting::boolean(SettingName::AUTOMATED_EVENTS)) {
            $scripts->add(new AutomatedEventsScript);
        }

        $scripts->register();
    }
}
