<?php

namespace SimpleAnalytics\Scripts\Contracts;

interface Script
{
    public function path(): string;

    function handle(): string;
}
