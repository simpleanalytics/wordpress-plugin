<?php

namespace SimpleAnalytics\Actions;

class AnalyticsCode extends Action
{
    protected string $hook = 'init';

    public function __construct(protected bool $shouldCollect)
    {
    }

    public function handle(): void
    {
        Scripts::register($this->shouldCollect);
        FooterContents::register($this->shouldCollect);
    }
}
