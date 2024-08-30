<?php

namespace SimpleAnalytics\Actions;

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

        Scripts::register($collect);
        FooterContents::register($collect);
    }
}
