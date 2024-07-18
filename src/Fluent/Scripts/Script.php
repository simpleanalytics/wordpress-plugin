<?php

namespace SimpleAnalytics\Fluent\Scripts;

interface Script
{
    public function path(): string;

    function handle(): string;
}
