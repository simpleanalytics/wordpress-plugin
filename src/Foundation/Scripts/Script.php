<?php

namespace SimpleAnalytics\Foundation\Scripts;

interface Script
{
    public function path(): string;

    function handle(): string;
}
